<?php
$page_title = "Cellphone Trading (Swap) Module";
require_once __DIR__ . '/../config/db.php';
require_role('admin', 'staff');

$message = '';
$prefill_appraisal_id = (int)($_GET['appraisal_id'] ?? 0);

// Fetch available units in shop inventory
$available_shop_units = $pdo->query("SELECT * FROM phone_units WHERE status = 'in_stock' ORDER BY brand, model_name")->fetchAll();

// Fetch pending or eligible appraisals
$eligible_appraisals = $pdo->query("SELECT * FROM appraisal_records WHERE status IN ('pending', 'accepted_for_trade') ORDER BY id DESC")->fetchAll();

// Handle Swap Execution
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'execute_trade') {
    $appraisal_id = (int)($_POST['appraisal_id'] ?? 0);
    $outgoing_unit_id = (int)($_POST['outgoing_unit_id'] ?? 0);
    $cash_tendered = (float)($_POST['cash_tendered'] ?? 0.00);
    $remarks = sanitize($_POST['remarks'] ?? '');

    try {
        $pdo->beginTransaction();

        // 1. Fetch appraisal
        $stmt = $pdo->prepare("SELECT * FROM appraisal_records WHERE id = ? FOR UPDATE");
        $stmt->execute([$appraisal_id]);
        $app = $stmt->fetch();

        // 2. Fetch outgoing shop unit
        $stmt = $pdo->prepare("SELECT * FROM phone_units WHERE id = ? AND status = 'in_stock' FOR UPDATE");
        $stmt->execute([$outgoing_unit_id]);
        $shop_unit = $stmt->fetch();

        if ($app && $shop_unit) {
            $trade_val = (float)$app['final_appraised_value'];
            $target_price = (float)$shop_unit['selling_price'];
            $top_up_diff = $target_price - $trade_val;

            $cash_received = ($top_up_diff > 0) ? $top_up_diff : 0.00;
            $cash_paid_out = ($top_up_diff < 0) ? abs($top_up_diff) : 0.00;

            // 3. Mark outgoing unit as traded
            $upd = $pdo->prepare("UPDATE phone_units SET status = 'traded' WHERE id = ?");
            $upd->execute([$outgoing_unit_id]);

            // 4. Create incoming unit into inventory
            $ins_unit = $pdo->prepare("INSERT INTO phone_units 
                (brand, model_name, storage_capacity, color, imei_1, unit_condition, battery_health, buy_price, selling_price, status, source, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'in_stock', 'customer_trade', ?)");
            
            $incoming_notes = "Traded in by {$app['customer_name']}. Original appraisal #{$app['id']}.";
            $incoming_selling_price = $trade_val * 1.25; // standard shop markup
            $ins_unit->execute([
                $app['brand'], $app['model_name'], $app['storage_capacity'], 'Standard',
                !empty($app['imei']) ? $app['imei'] : 'TRD-' . time(),
                $app['cosmetic_grade'], $app['battery_health'], $trade_val, $incoming_selling_price, $incoming_notes
            ]);
            $incoming_unit_id = $pdo->lastInsertId();

            // 5. Update appraisal status
            $upd_app = $pdo->prepare("UPDATE appraisal_records SET status = 'accepted_for_trade' WHERE id = ?");
            $upd_app->execute([$appraisal_id]);

            // 6. Record trade transaction
            $trade_code = '8J-TRD-' . strtoupper(bin2hex(random_bytes(3)));
            $ins_trade = $pdo->prepare("INSERT INTO trading_transactions 
                (trade_code, customer_name, customer_contact, trade_in_appraisal_id, incoming_unit_id, outgoing_unit_id, appraised_trade_value, target_unit_price, top_up_difference, cash_received_from_customer, cash_paid_to_customer, handled_by_staff_id, remarks)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $staff_id = !empty($current_user['id']) ? $current_user['id'] : ($_SESSION['user']['id'] ?? 1);
            $ins_trade->execute([
                $trade_code, $app['customer_name'], $app['contact_number'], $appraisal_id,
                $incoming_unit_id, $outgoing_unit_id, $trade_val, $target_price, $top_up_diff,
                $cash_received, $cash_paid_out, $staff_id, $remarks
            ]);

            // 7. Audit Log
            $log = $pdo->prepare("INSERT INTO audit_logs (user_id, action, module, details) VALUES (?, 'Completed Phone Trade', 'Trading', ?)");
            $log->execute([$staff_id, "Trade {$trade_code}: {$shop_unit['model_name']} swapped with {$app['model_name']}"]);

            $pdo->commit();
            $message = "Phone trade completed successfully! Transaction Reference: " . $trade_code;
        } else {
            $pdo->rollBack();
            $message = "Error: Invalid appraisal record or target shop phone is no longer in stock.";
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $message = "Trade processing error: " . $e->getMessage();
    }
}

// Fetch recent completed trades
$recent_trades = $pdo->query("SELECT t.*, u_out.model_name as out_model, u_out.brand as out_brand, u_in.model_name as in_model, u_in.brand as in_brand 
                              FROM trading_transactions t
                              LEFT JOIN phone_units u_out ON t.outgoing_unit_id = u_out.id
                              LEFT JOIN phone_units u_in ON t.incoming_unit_id = u_in.id
                              ORDER BY t.id DESC LIMIT 10")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h2 class="h3 fw-bold mb-1"><i class="fa-solid fa-repeat text-primary me-2"></i>Cellphone Trading (Swap) System</h2>
                    <p class="text-muted small mb-0">Exchange customer's pre-owned phone for a store unit with automated top-up valuation balance</p>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert <?= strpos($message, 'Error') !== false ? 'alert-danger' : 'alert-success' ?> alert-dismissible fade show" role="alert">
                    <i class="fa-solid <?= strpos($message, 'Error') !== false ? 'fa-circle-xmark' : 'fa-circle-check' ?> me-2"></i>
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Swap Transaction Wizard Form -->
                <div class="col-lg-7">
                    <div class="card card-custom p-4">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">Create Swap Transaction</h5>
                        <form method="POST" action="" id="tradeForm">
                            <input type="hidden" name="action" value="execute_trade">

                            <!-- Step 1: Select Trade-In Phone (Appraisal Record) -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">1. Select Customer's Appraised Phone (Trade-In Unit)</label>
                                <div class="input-group mb-2">
                                    <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                                    <input type="search" id="appraisalSearch" class="form-control" placeholder="Search by IMEI, customer name, or model..." autocomplete="off">
                                </div>
                                <select name="appraisal_id" id="appraisalSelect" class="form-select" required onchange="updateTradeCalculation()">
                                    <option value="" disabled <?= $prefill_appraisal_id == 0 ? 'selected' : '' ?>>-- Choose Customer Appraisal --</option>
                                    <?php foreach ($eligible_appraisals as $app): ?>
                                        <option value="<?= $app['id'] ?>" data-search="<?= htmlspecialchars(strtolower($app['imei'] . ' ' . $app['customer_name'] . ' ' . $app['contact_number'] . ' ' . $app['brand'] . ' ' . $app['model_name'] . ' #' . $app['id'])) ?>" 
                                            data-val="<?= $app['final_appraised_value'] ?>"
                                            data-customer="<?= htmlspecialchars($app['customer_name']) ?>"
                                            data-device="<?= htmlspecialchars($app['brand'] . ' ' . $app['model_name'] . ' ' . $app['storage_capacity']) ?>"
                                            <?= $prefill_appraisal_id == $app['id'] ? 'selected' : '' ?>>
                                            #<?= $app['id'] ?> - <?= htmlspecialchars($app['customer_name']) ?> | <?= htmlspecialchars($app['brand'] . ' ' . $app['model_name']) ?><?= !empty($app['imei']) ? ' | IMEI ' . htmlspecialchars($app['imei']) : '' ?> (Trade Val: <?= format_currency($app['final_appraised_value']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text small">
                                    Don't see the phone? <a href="<?= BASE_URL ?>admin/appraisal.php" class="fw-bold text-primary">Run a new appraisal first</a>.
                                </div>
                            </div>

                            <!-- Step 2: Select Desired Shop Unit -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">2. Select Desired Shop Unit (Customer Wants to Receive)</label>
                                <div class="input-group mb-2">
                                    <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                                    <input type="search" id="shopUnitSearch" class="form-control" placeholder="Search shop phone by IMEI, brand, model, color..." autocomplete="off">
                                </div>
                                <select name="outgoing_unit_id" id="shopUnitSelect" class="form-select" required onchange="updateTradeCalculation()">
                                    <option value="" disabled selected>-- Choose Shop Smartphone --</option>
                                    <?php foreach ($available_shop_units as $unit): ?>
                                        <option value="<?= $unit['id'] ?>" 
                                            data-search="<?= htmlspecialchars(strtolower($unit['imei_1'] . ' ' . $unit['brand'] . ' ' . $unit['model_name'] . ' ' . $unit['storage_capacity'] . ' ' . $unit['color'])) ?>"
                                            data-price="<?= $unit['selling_price'] ?>"
                                            data-device="<?= htmlspecialchars($unit['brand'] . ' ' . $unit['model_name'] . ' ' . $unit['storage_capacity']) ?>"
                                            data-imei="<?= $unit['imei_1'] ?>">
                                            <?= htmlspecialchars($unit['brand'] . ' ' . $unit['model_name'] . ' ' . $unit['storage_capacity']) ?> [<?= $unit['unit_condition'] ?>] - Price: <?= format_currency($unit['selling_price']) ?> (IMEI: <?= htmlspecialchars($unit['imei_1']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Transaction Notes / Swap Warranty Terms</label>
                                <textarea name="remarks" class="form-control" rows="2" placeholder="e.g. 7-day replacement warranty on shop unit, charger included"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-pill shadow-sm" id="submitTradeBtn">
                                <i class="fa-solid fa-check-double me-1"></i> Confirm & Finalize Swap Transaction
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Swap Financial Breakdown Preview -->
                <div class="col-lg-5">
                    <div class="card card-custom p-4 sticky-top" style="top: 80px;">
                        <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="fa-solid fa-scale-balanced me-2 text-primary"></i>Swap Balance Summary</h5>

                        <div class="p-3 bg-light rounded-3 mb-3 border text-center">
                            <span class="text-muted small text-uppercase fw-semibold d-block" id="balanceStatusLabel">Balance Computation</span>
                            <h2 class="display-6 fw-extrabold mb-0" id="liveBalanceDiff">₱0.00</h2>
                            <small class="fw-semibold" id="balanceActionNotice">Select trade-in and shop phone</small>
                        </div>

                        <div class="list-group list-group-flush small mb-3">
                            <div class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Customer Trade-In Value:</span>
                                <strong class="text-success" id="tradeInValLabel">₱0.00</strong>
                            </div>
                            <div class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Target Shop Unit Price:</span>
                                <strong class="text-primary" id="targetPriceLabel">₱0.00</strong>
                            </div>
                            <div class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Net Difference (Formula):</span>
                                <span class="text-muted fst-italic">Target Price - Trade Value</span>
                            </div>
                        </div>

                        <div class="p-3 rounded-3 bg-primary-subtle text-primary border small">
                            <i class="fa-solid fa-circle-check me-1"></i>
                            <strong>Automated Inventory Action:</strong>
                            <ul class="mb-0 ps-3 mt-1">
                                <li>Shop unit will be marked as <code>Traded</code>.</li>
                                <li>Customer unit is automatically added to Inventory as <code>Pre-Owned</code> ready for resale.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Completed Swaps -->
            <div class="card card-custom mt-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Completed Swap Records</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Trade Code</th>
                                    <th>Customer</th>
                                    <th>Unit Received (Shop)</th>
                                    <th>Unit Traded In (Customer)</th>
                                    <th class="text-end">Top-Up Paid</th>
                                    <th>Date</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recent_trades)): ?>
                                    <?php foreach ($recent_trades as $tr): ?>
                                        <tr>
                                            <td class="fw-bold text-primary"><?= htmlspecialchars($tr['trade_code']) ?></td>
                                            <td><?= htmlspecialchars($tr['customer_name']) ?></td>
                                            <td class="fw-semibold text-success">
                                                <i class="fa-solid fa-arrow-right-from-bracket me-1"></i>
                                                <?= htmlspecialchars($tr['out_brand'] . ' ' . $tr['out_model']) ?>
                                            </td>
                                            <td class="fw-semibold text-secondary">
                                                <i class="fa-solid fa-arrow-right-to-bracket me-1"></i>
                                                <?= htmlspecialchars($tr['in_brand'] . ' ' . $tr['in_model']) ?>
                                            </td>
                                            <td class="text-end fw-extrabold">
                                                <?php if ($tr['top_up_difference'] > 0): ?>
                                                    <span class="text-primary">+<?= format_currency($tr['cash_received_from_customer']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-danger">-<?= format_currency($tr['cash_paid_to_customer']) ?> (Cashback)</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-muted small"><?= date('M d, Y H:i', strtotime($tr['created_at'])) ?></td>
                                            <td class="text-center">
                                                <a href="<?= BASE_URL ?>print_trade.php?id=<?= $tr['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Print Swap Agreement">
                                                    <i class="fa-solid fa-print"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No completed trades yet.</td>
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

<script>
// Live search over appraisals (IMEI / customer / contact / model / #id)
(function () {
    const search = document.getElementById('appraisalSearch');
    const select = document.getElementById('appraisalSelect');
    if (!search || !select) return;
    search.addEventListener('input', function () {
        const terms = search.value.toLowerCase().trim().split(/\s+/).filter(Boolean);
        let visible = [];
        Array.from(select.options).forEach(function (opt) {
            if (!opt.value) return;
            const hay = opt.dataset.search || '';
            const match = terms.every(function (t) { return hay.indexOf(t) !== -1; });
            opt.hidden = !match;
            opt.disabled = !match;
            if (match) visible.push(opt);
        });
        if (terms.length && visible.length === 1) {
            select.value = visible[0].value;
            select.dispatchEvent(new Event('change'));
        } else if (select.selectedOptions[0] && select.selectedOptions[0].hidden) {
            select.selectedIndex = 0;
            select.dispatchEvent(new Event('change'));
        }
        search.classList.toggle('is-invalid', terms.length > 0 && visible.length === 0);
    });
})();
// Live search over shop units (IMEI / brand / model / storage / color)
(function () {
    const search = document.getElementById('shopUnitSearch');
    const select = document.getElementById('shopUnitSelect');
    if (!search || !select) return;
    search.addEventListener('input', function () {
        const terms = search.value.toLowerCase().trim().split(/\s+/).filter(Boolean);
        let visible = [];
        Array.from(select.options).forEach(function (opt) {
            if (!opt.value) return;
            const hay = opt.dataset.search || '';
            const match = terms.every(function (t) { return hay.indexOf(t) !== -1; });
            opt.hidden = !match;
            opt.disabled = !match;
            if (match) visible.push(opt);
        });
        if (terms.length && visible.length === 1) {
            select.value = visible[0].value;
            select.dispatchEvent(new Event('change'));
        } else if (select.selectedOptions[0] && select.selectedOptions[0].hidden) {
            select.selectedIndex = 0;
            select.dispatchEvent(new Event('change'));
        }
        search.classList.toggle('is-invalid', terms.length > 0 && visible.length === 0);
    });
})();
</script>
<script>
function updateTradeCalculation() {
    const appSelect = document.getElementById('appraisalSelect');
    const appOpt = appSelect.options[appSelect.selectedIndex];
    const shopSelect = document.getElementById('shopUnitSelect');
    const shopOpt = shopSelect.options[shopSelect.selectedIndex];

    const tradeVal = appOpt && appOpt.dataset.val ? parseFloat(appOpt.dataset.val) : 0;
    const targetPrice = shopOpt && shopOpt.dataset.price ? parseFloat(shopOpt.dataset.price) : 0;

    document.getElementById('tradeInValLabel').innerText = '₱' + tradeVal.toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('targetPriceLabel').innerText = '₱' + targetPrice.toLocaleString('en-US', {minimumFractionDigits: 2});

    if (tradeVal > 0 && targetPrice > 0) {
        const diff = targetPrice - tradeVal;
        const diffElem = document.getElementById('liveBalanceDiff');
        const noticeElem = document.getElementById('balanceActionNotice');
        
        diffElem.className = 'display-6 fw-extrabold mb-0 text-dark font-monospace';

        if (typeof animateNumber === 'function' && window._lastTradeDiff !== undefined) {
            animateNumber(diffElem, window._lastTradeDiff, diff, 350, (diff >= 0 ? '+₱' : '-₱'));
        } else {
            diffElem.innerText = (diff >= 0 ? '+₱' : '-₱') + Math.abs(diff).toLocaleString('en-US', {minimumFractionDigits: 2});
        }
        window._lastTradeDiff = diff;

        if (diff > 0) {
            noticeElem.className = 'fw-bold text-dark';
            noticeElem.innerText = 'Customer pays cash top-up difference';
        } else if (diff < 0) {
            noticeElem.className = 'fw-bold text-dark';
            noticeElem.innerText = 'Store pays customer cash back (Downgrade Swap)';
        } else {
            noticeElem.className = 'fw-bold text-dark';
            noticeElem.innerText = 'Straight Swap — No cash required';
        }
    }
}
document.addEventListener('DOMContentLoaded', updateTradeCalculation);
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
