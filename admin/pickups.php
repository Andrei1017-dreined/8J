<?php
$page_title = "Online Orders & Pick-Up Queue";
require_once __DIR__ . '/../config/db.php';
require_role('admin', 'staff');

$message = '';

// Handle Fulfillment Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $order_id = (int)($_POST['order_id'] ?? 0);

    if ($_POST['action'] === 'mark_ready') {
        $stmt = $pdo->prepare("UPDATE online_orders SET pickup_status = 'ready_for_pickup' WHERE id = ?");
        $stmt->execute([$order_id]);
        $message = "Order marked as Ready for In-Store Pick-Up!";
    } elseif ($_POST['action'] === 'claim_order') {
        $entered_code = trim($_POST['claim_code'] ?? '');
        
        $chk = $pdo->prepare("SELECT * FROM online_orders WHERE id = ?");
        $chk->execute([$order_id]);
        $order = $chk->fetch();

        if ($order && strtoupper($order['claim_verification_code']) === strtoupper($entered_code)) {
            $staff_id = !empty($current_user['id']) ? $current_user['id'] : ($_SESSION['user']['id'] ?? 1);
            $stmt = $pdo->prepare("UPDATE online_orders SET pickup_status = 'claimed', claimed_at = NOW(), handled_by_staff_id = ? WHERE id = ?");
            $stmt->execute([$staff_id, $order_id]);
            $message = "Success: Claim code verified! Order has been released to customer.";
        } else {
            $message = "Error: Invalid claim verification code! Please check the customer's pickup claim ticket.";
        }
    }
}

// Fetch Orders
$orders = $pdo->query("SELECT o.*, u.fullname as customer_name, u.contact_number 
                       FROM online_orders o 
                       LEFT JOIN users u ON o.customer_id = u.id 
                       ORDER BY o.id DESC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h2 class="h3 fw-bold mb-1"><i class="fa-solid fa-store text-primary me-2"></i>Store Pick-Up Fulfillment Queue</h2>
                    <p class="text-muted small mb-0">Manage customer online reservations, claim code verifications, and in-store collections</p>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert <?= strpos($message, 'Error') !== false ? 'alert-danger' : 'alert-success' ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card card-custom">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Order Reference</th>
                                    <th>Customer</th>
                                    <th>Target Pick-Up Date</th>
                                    <th class="text-end">Amount Due</th>
                                    <th>Claim Code</th>
                                    <th>Status</th>
                                    <th class="text-center">Fulfillment Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($orders)): ?>
                                    <?php foreach ($orders as $ord): ?>
                                        <tr>
                                            <td class="fw-bold text-primary">
                                                <?= htmlspecialchars($ord['order_code']) ?>
                                                <div class="small text-muted"><?= date('M d, Y H:i', strtotime($ord['created_at'])) ?></div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold"><?= htmlspecialchars($ord['customer_name']) ?></div>
                                                <small class="text-muted"><i class="fa-solid fa-phone me-1"></i><?= htmlspecialchars($ord['contact_number']) ?></small>
                                            </td>
                                            <td>
                                                <i class="fa-regular fa-calendar-days me-1 text-muted"></i>
                                                <?= date('M d, Y', strtotime($ord['pickup_target_date'])) ?>
                                            </td>
                                            <td class="text-end fw-extrabold text-primary">
                                                <?= format_currency($ord['total_amount']) ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-dark font-monospace fs-6 px-2"><?= htmlspecialchars($ord['claim_verification_code']) ?></span>
                                            </td>
                                            <td>
                                                <?php if ($ord['pickup_status'] === 'claimed'): ?>
                                                    <span class="badge bg-dark text-white"><i class="fa-solid fa-check me-1"></i> Claimed</span>
                                                    <div class="small text-muted" style="font-size: 0.68rem;"><?= date('M d, H:i', strtotime($ord['claimed_at'])) ?></div>
                                                <?php elseif ($ord['pickup_status'] === 'ready_for_pickup'): ?>
                                                    <span class="badge bg-light text-dark border border-dark"><i class="fa-solid fa-bell me-1"></i> Ready for Pick-Up</span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-secondary border"><i class="fa-solid fa-clock me-1"></i> Pending Prep</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($ord['pickup_status'] === 'pending_confirmation'): ?>
                                                    <form method="POST" action="" style="display:inline;">
                                                        <input type="hidden" name="action" value="mark_ready">
                                                        <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3">Mark Ready</button>
                                                    </form>
                                                <?php elseif ($ord['pickup_status'] === 'ready_for_pickup'): ?>
                                                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3" onclick="openClaimModal(<?= $ord['id'] ?>, '<?= $ord['order_code'] ?>')">
                                                        <i class="fa-solid fa-check me-1"></i> Verify & Release
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted small"><i class="fa-solid fa-circle-check text-success me-1"></i>Completed</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No online pick-up reservations found.</td>
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

<!-- Modal: Claim Verification -->
<div class="modal fade" id="claimModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="action" value="claim_order">
                <input type="hidden" name="order_id" id="claimOrderId">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="claimModalTitle">Release Pick-Up Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">Ask the customer for their Claim Verification Code shown on their phone screen or order receipt.</p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Enter Claim Verification Code</label>
                        <input type="text" name="claim_code" class="form-control form-control-lg text-center font-monospace fw-bold" placeholder="e.g. 8J-7492" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold">Verify Code & Release Items</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openClaimModal(id, code) {
    document.getElementById('claimOrderId').value = id;
    document.getElementById('claimModalTitle').innerText = 'Verify Claim: ' + code;
    new bootstrap.Modal(document.getElementById('claimModal')).show();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
