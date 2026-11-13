<?php
$page_title = "Online Orders & Pick-Up Queue";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/order_helpers.php';
require_role('admin', 'staff');

$message = '';
$message_ok = true;
$released_invoice = null;

// Reservations not collected within the hold period go back on sale automatically
$expired_now = expire_overdue_pickups($pdo);
if ($expired_now > 0) {
    audit_log('Pick-ups Expired', 'Pickups', "{$expired_now} uncollected reservation(s) expired and released");
}

$staff_id = (int)($current_user['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $action = $_POST['action'];

    if ($action === 'mark_ready') {
        $stmt = $pdo->prepare("UPDATE online_orders SET pickup_status = 'ready_for_pickup', handled_by_staff_id = ? WHERE id = ? AND pickup_status = 'pending_confirmation'");
        $stmt->execute([$staff_id, $order_id]);
        if ($stmt->rowCount()) {
            audit_log('Pick-up Ready', 'Pickups', "Order #{$order_id} prepared and marked ready");
            $message = "Order marked as ready for pick-up.";
        } else {
            $message = "That order is no longer waiting to be prepared.";
            $message_ok = false;
        }

    } elseif ($action === 'cancel_order') {
        $reason = trim(sanitize($_POST['reason'] ?? '')) ?: 'Cancelled by staff';
        try {
            if (close_pickup_order($pdo, $order_id, 'cancelled', $reason . ' (' . ($current_user['fullname'] ?? 'staff') . ')')) {
                audit_log('Pick-up Cancelled', 'Pickups', "Order #{$order_id} cancelled: {$reason}");
                $message = "Order cancelled. Any reserved phones are back on sale.";
            } else {
                $message = "That order is already closed.";
                $message_ok = false;
            }
        } catch (Exception $e) {
            error_log('Pick-up cancel error: ' . $e->getMessage());
            $message = "The order couldn't be cancelled. Please try again.";
            $message_ok = false;
        }

    } elseif ($action === 'claim_order') {
        $entered_code = strtoupper(trim($_POST['claim_code'] ?? ''));
        if ($entered_code !== '' && strpos($entered_code, '8J-') !== 0) $entered_code = '8J-' . $entered_code; // staff may type just the 4 digits
        $payment_method = in_array($_POST['payment_method'] ?? '', ['cash', 'gcash', 'card'], true) ? $_POST['payment_method'] : 'cash';
        $payment_ref = mb_substr(sanitize($_POST['payment_reference'] ?? ''), 0, 80);

        try {
            $pdo->beginTransaction();
            $chk = $pdo->prepare("SELECT o.*, u.fullname AS customer_name FROM online_orders o LEFT JOIN users u ON u.id = o.customer_id WHERE o.id = ? FOR UPDATE");
            $chk->execute([$order_id]);
            $order = $chk->fetch();

            if (!$order || !in_array($order['pickup_status'], ['pending_confirmation', 'ready_for_pickup'], true)) {
                throw new StockChangedException("This order was already released, cancelled or expired.");
            }
            if (!hash_equals(strtoupper($order['claim_verification_code']), $entered_code)) {
                throw new StockChangedException("Invalid claim code. Check the code on the customer's phone or printed slip.");
            }

            $item_stmt = $pdo->prepare("SELECT * FROM online_order_items WHERE order_id = ?");
            $item_stmt->execute([$order_id]);
            $order_items = $item_stmt->fetchAll();

            // 1. Record the sale, so pick-up revenue shows in Reports and a receipt can be printed
            $invoice_no = '8J-INV-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
            $total = (float)$order['total_amount'];
            $pdo->prepare("INSERT INTO sales (invoice_no, customer_id, customer_name, subtotal, discount_amount, total_amount, amount_tendered, change_due, payment_method, payment_reference, cashier_id, notes)
                           VALUES (?, ?, ?, ?, 0, ?, ?, 0, ?, ?, ?, ?)")
                ->execute([$invoice_no, $order['customer_id'], $order['customer_name'] ?: 'Online Customer', $total, $total, $total,
                           $payment_method, $payment_ref, $staff_id, "Online pick-up order {$order['order_code']}"]);
            $sale_id = $pdo->lastInsertId();

            // 2. Items: take stock / mark phones sold, refusing if stock ran out since the reservation
            $sale_item = $pdo->prepare("INSERT INTO sale_items (sale_id, item_type, product_id, phone_unit_id, item_name, unit_price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $take_stock = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?");
            $sell_phone = $pdo->prepare("UPDATE phone_units SET status = 'sold' WHERE id = ? AND status IN ('reserved', 'in_stock')");
            foreach ($order_items as $item) {
                $sale_item->execute([$sale_id, $item['item_type'], $item['product_id'], $item['phone_unit_id'], $item['item_name'], $item['unit_price'], $item['quantity'], $item['subtotal']]);
                if ($item['item_type'] === 'phone_unit' && !empty($item['phone_unit_id'])) {
                    $sell_phone->execute([$item['phone_unit_id']]);
                    if ($sell_phone->rowCount() !== 1) throw new StockChangedException("{$item['item_name']} is no longer available (sold or traded).");
                } elseif ($item['item_type'] === 'product' && !empty($item['product_id'])) {
                    $take_stock->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
                    if ($take_stock->rowCount() !== 1) throw new StockChangedException("Not enough stock left of {$item['item_name']}. Restock it or cancel the order.");
                }
            }

            // 3. Close the order
            $pdo->prepare("UPDATE online_orders SET pickup_status = 'claimed', claimed_at = NOW(), handled_by_staff_id = ? WHERE id = ?")
                ->execute([$staff_id, $order_id]);

            audit_log('Pick-up Claimed', 'Pickups', "Order {$order['order_code']} released; invoice {$invoice_no}, " . format_currency($total) . " via " . strtoupper($payment_method));
            $pdo->commit();
            $released_invoice = $invoice_no;
            $message = "Code verified. Order {$order['order_code']} released and recorded as invoice {$invoice_no}.";
        } catch (StockChangedException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $message = $e->getMessage();
            $message_ok = false;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('Pick-up claim error: ' . $e->getMessage());
            $message = "The order couldn't be released. Nothing was changed. Please try again.";
            $message_ok = false;
        }
    }
}

// Filter tabs
$view = $_GET['view'] ?? 'open';
$views = [
    'open'    => ["pickup_status IN ('pending_confirmation','ready_for_pickup')", 'Open'],
    'claimed' => ["pickup_status = 'claimed'", 'Collected'],
    'closed'  => ["pickup_status IN ('cancelled','expired')", 'Cancelled & expired'],
    'all'     => ["1=1", 'All'],
];
if (!isset($views[$view])) $view = 'open';
$counts = [];
foreach ($views as $k => [$where]) {
    $counts[$k] = (int)$pdo->query("SELECT COUNT(*) FROM online_orders WHERE $where")->fetchColumn();
}

$orders = $pdo->query("SELECT o.*, u.fullname as customer_name, u.contact_number,
                              s.fullname AS staff_name,
                              (SELECT invoice_no FROM sales WHERE notes = CONCAT('Online pick-up order ', o.order_code) LIMIT 1) AS invoice_no
                       FROM online_orders o
                       LEFT JOIN users u ON o.customer_id = u.id
                       LEFT JOIN users s ON o.handled_by_staff_id = s.id
                       WHERE {$views[$view][0]}
                       ORDER BY (o.pickup_status IN ('pending_confirmation','ready_for_pickup')) DESC, o.pickup_target_date ASC, o.id DESC
                       LIMIT 200")->fetchAll();

$items_by_order = [];
if ($orders) {
    $ids = implode(',', array_map('intval', array_column($orders, 'id')));
    foreach ($pdo->query("SELECT * FROM online_order_items WHERE order_id IN ($ids) ORDER BY id") as $it) {
        $items_by_order[$it['order_id']][] = $it;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<style>
.pk-tabs .btn { border-radius: 999px; }
.pk-tabs .count { display: inline-block; min-width: 20px; padding: 0 6px; margin-left: 4px; border-radius: 999px; background: rgba(0,0,0,.08); font-size: .72rem; }
.pk-tabs .btn-primary .count { background: rgba(255,255,255,.25); }
.pk-item { font-size: .8rem; color: #52525b; }
.pk-item + .pk-item { margin-top: 2px; }
tr.pk-overdue td:first-child { box-shadow: inset 3px 0 0 #f59e0b; }
</style>

<div class="container-fluid px-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 border-bottom pb-3">
                <div>
                    <h2 class="h3 fw-bold mb-1"><i class="fa-solid fa-store text-primary me-2"></i>Store Pick-Up Queue</h2>
                    <p class="text-muted small mb-0">Prepare online reservations, verify the customer's claim code, and release. Uncollected orders expire <?= PICKUP_HOLD_DAYS ?> days after the pick-up date.</p>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert <?= $message_ok ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show d-flex flex-wrap align-items-center gap-2" role="alert">
                    <span><?= htmlspecialchars($message) ?></span>
                    <?php if ($released_invoice): ?>
                        <a class="btn btn-sm btn-dark rounded-pill ms-md-auto" target="_blank" href="<?= BASE_URL ?>print_receipt.php?invoice=<?= urlencode($released_invoice) ?>&mode=thermal"><i class="fa-solid fa-print me-1"></i> Print receipt</a>
                    <?php endif; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if ($expired_now > 0): ?>
                <div class="alert alert-warning small"><i class="fa-solid fa-hourglass-end me-1"></i> <?= $expired_now ?> uncollected reservation<?= $expired_now === 1 ? '' : 's' ?> expired and the reserved phones are back on sale.</div>
            <?php endif; ?>

            <div class="pk-tabs d-flex flex-wrap gap-2 mb-3">
                <?php foreach ($views as $k => [, $label]): ?>
                    <a href="?view=<?= $k ?>" class="btn btn-sm <?= $view === $k ? 'btn-primary' : 'btn-outline-secondary' ?>"><?= $label ?><span class="count"><?= $counts[$k] ?></span></a>
                <?php endforeach; ?>
            </div>

            <div class="card card-custom">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Order</th>
                                    <th>Customer</th>
                                    <th>Items to prepare</th>
                                    <th>Pick-up date</th>
                                    <th class="text-end">Amount due</th>
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($orders)): ?>
                                    <?php foreach ($orders as $ord):
                                        $open = in_array($ord['pickup_status'], ['pending_confirmation', 'ready_for_pickup'], true);
                                        $overdue = $open && $ord['pickup_target_date'] < date('Y-m-d');
                                        [$bl, $bc, $bi] = pickup_status_badge($ord['pickup_status']);
                                    ?>
                                        <tr class="<?= $overdue ? 'pk-overdue' : '' ?>">
                                            <td class="fw-bold text-primary">
                                                <?= htmlspecialchars($ord['order_code']) ?>
                                                <div class="small text-muted fw-normal">Placed <?= date('M d, g:i A', strtotime($ord['created_at'])) ?></div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold"><?= htmlspecialchars($ord['customer_name'] ?? '—') ?></div>
                                                <?php if (!empty($ord['contact_number'])): ?>
                                                    <a class="small text-muted text-decoration-none" href="tel:<?= htmlspecialchars($ord['contact_number']) ?>"><i class="fa-solid fa-phone me-1"></i><?= htmlspecialchars($ord['contact_number']) ?></a>
                                                <?php endif; ?>
                                            </td>
                                            <td style="min-width: 200px;">
                                                <?php foreach ($items_by_order[$ord['id']] ?? [] as $it): ?>
                                                    <div class="pk-item"><strong><?= (int)$it['quantity'] ?>×</strong> <?= htmlspecialchars($it['item_name']) ?></div>
                                                <?php endforeach; ?>
                                            </td>
                                            <td>
                                                <?= date('M d, Y', strtotime($ord['pickup_target_date'])) ?>
                                                <?php if ($overdue): ?><div class="small text-warning fw-semibold"><i class="fa-solid fa-triangle-exclamation me-1"></i>Overdue</div><?php endif; ?>
                                            </td>
                                            <td class="text-end fw-bold"><?= format_currency($ord['total_amount']) ?></td>
                                            <td>
                                                <span class="badge <?= $bc ?>"><i class="fa-solid <?= $bi ?> me-1"></i><?= $bl ?></span>
                                                <?php if ($ord['pickup_status'] === 'claimed' && $ord['claimed_at']): ?>
                                                    <div class="small text-muted" style="font-size: .7rem;"><?= date('M d, g:i A', strtotime($ord['claimed_at'])) ?><?= $ord['staff_name'] ? ' · ' . htmlspecialchars($ord['staff_name']) : '' ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center text-nowrap">
                                                <?php if ($ord['pickup_status'] === 'pending_confirmation'): ?>
                                                    <form method="POST" action="" class="d-inline">
                                                        <input type="hidden" name="action" value="mark_ready">
                                                        <input type="hidden" name="order_id" value="<?= (int)$ord['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3">Mark ready</button>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if ($open): ?>
                                                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3" onclick="openClaimModal(<?= (int)$ord['id'] ?>, <?= htmlspecialchars(json_encode($ord['order_code']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode(format_currency($ord['total_amount'])), ENT_QUOTES) ?>)">
                                                        <i class="fa-solid fa-check me-1"></i> Release
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-link text-danger" onclick="openCancelModal(<?= (int)$ord['id'] ?>, <?= htmlspecialchars(json_encode($ord['order_code']), ENT_QUOTES) ?>)">Cancel</button>
                                                <?php elseif (!empty($ord['invoice_no'])): ?>
                                                    <a class="btn btn-sm btn-outline-dark rounded-pill px-3" target="_blank" href="<?= BASE_URL ?>print_receipt.php?invoice=<?= urlencode($ord['invoice_no']) ?>&mode=thermal"><i class="fa-solid fa-receipt me-1"></i> Receipt</a>
                                                <?php else: ?>
                                                    <span class="text-muted small">—</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <?= $view === 'open' ? 'No reservations waiting. New online orders will appear here.' : 'Nothing here yet.' ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal: Verify & release -->
<div class="modal fade" id="claimModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="action" value="claim_order">
                <input type="hidden" name="order_id" id="claimOrderId">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="claimModalTitle">Release order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">Ask the customer for the claim code on their phone or printed slip, then collect payment of <strong id="claimAmount"></strong>.</p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold" for="claimCodeInput">Claim code</label>
                        <input type="text" name="claim_code" id="claimCodeInput" class="form-control form-control-lg text-center font-monospace fw-bold" placeholder="8J-0000" required autocomplete="off" autocapitalize="characters">
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Paid with</label>
                            <select name="payment_method" class="form-select">
                                <option value="cash">Cash</option>
                                <option value="gcash">GCash</option>
                                <option value="card">Card / Maya</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Reference # <span class="text-muted fw-normal">(GCash/card)</span></label>
                            <input type="text" name="payment_reference" class="form-control" maxlength="80">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
                    <button type="submit" class="btn btn-success fw-bold">Verify, release &amp; record sale</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Cancel -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="action" value="cancel_order">
                <input type="hidden" name="order_id" id="cancelOrderId">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="cancelModalTitle">Cancel order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Reserved phones go back on sale straight away.</p>
                    <label class="form-label small fw-semibold" for="cancelReason">Reason</label>
                    <select name="reason" id="cancelReason" class="form-select">
                        <option>Customer asked to cancel</option>
                        <option>Item unavailable / damaged</option>
                        <option>Customer unreachable</option>
                        <option>Duplicate order</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep order</button>
                    <button type="submit" class="btn btn-danger fw-bold">Cancel order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openClaimModal(id, code, amount) {
    document.getElementById('claimOrderId').value = id;
    document.getElementById('claimModalTitle').innerText = 'Release ' + code;
    document.getElementById('claimAmount').innerText = amount;
    document.getElementById('claimCodeInput').value = '';
    const m = new bootstrap.Modal(document.getElementById('claimModal'));
    document.getElementById('claimModal').addEventListener('shown.bs.modal', () => document.getElementById('claimCodeInput').focus(), { once: true });
    m.show();
}
function openCancelModal(id, code) {
    document.getElementById('cancelOrderId').value = id;
    document.getElementById('cancelModalTitle').innerText = 'Cancel ' + code + '?';
    new bootstrap.Modal(document.getElementById('cancelModal')).show();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
