<?php
$page_title = "Buying of Units (Buyout Module)";
require_once __DIR__ . '/../config/db.php';
require_role('admin', 'staff');

$message = '';
$prefill_appraisal_id = (int)($_GET['appraisal_id'] ?? 0);

// Fetch pending appraisals
$eligible_appraisals = $pdo->query("SELECT * FROM appraisal_records WHERE status IN ('pending', 'accepted_for_buyout') ORDER BY id DESC LIMIT 20")->fetchAll();

// Handle Buyout Execution
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'execute_buyout') {
    $appraisal_id = (int)($_POST['appraisal_id'] ?? 0);
    $seller_name = sanitize($_POST['seller_name'] ?? '');
    $seller_contact = sanitize($_POST['seller_contact'] ?? '');
    $seller_address = sanitize($_POST['seller_address'] ?? '');
    $valid_id_type = sanitize($_POST['valid_id_type'] ?? '');
    $valid_id_number = sanitize($_POST['valid_id_number'] ?? '');
    $payment_method = sanitize($_POST['payment_method'] ?? 'cash');
    $anti_fencing_agreed = isset($_POST['anti_fencing_agreed']) ? 1 : 0;

    if ($anti_fencing_agreed !== 1) {
        $message = "Error: Anti-Fencing legal declaration must be acknowledged by the seller.";
    } else {
        try {
            $pdo->beginTransaction();

            // 1. Fetch appraisal
            $stmt = $pdo->prepare("SELECT * FROM appraisal_records WHERE id = ? FOR UPDATE");
            $stmt->execute([$appraisal_id]);
            $app = $stmt->fetch();

            if ($app) {
                $amount_paid = (float)$app['final_appraised_value'];

                // 2. Add phone to inventory as Pre-Owned
                $ins_unit = $pdo->prepare("INSERT INTO phone_units 
                    (brand, model_name, storage_capacity, color, imei_1, unit_condition, battery_health, buy_price, selling_price, status, source, notes)
                    VALUES (?, ?, ?, 'Standard', ?, ?, ?, ?, ?, 'in_stock', 'customer_buyout', ?)");

                $selling_price = $amount_paid * 1.30; // 30% standard margin
                $buyout_imei = !empty($app['imei']) ? $app['imei'] : 'BUY-' . time();
                $notes = "Acquired from {$seller_name} via Direct Buyout. Valid ID: {$valid_id_type} ({$valid_id_number})";

                $ins_unit->execute([
                    $app['brand'], $app['model_name'], $app['storage_capacity'], $buyout_imei,
                    $app['cosmetic_grade'], $app['battery_health'], $amount_paid, $selling_price, $notes
                ]);
                $phone_unit_id = $pdo->lastInsertId();

                // 3. Mark appraisal as accepted_for_buyout
                $upd_app = $pdo->prepare("UPDATE appraisal_records SET status = 'accepted_for_buyout' WHERE id = ?");
                $upd_app->execute([$appraisal_id]);

                // 4. Record Buyout transaction
                $trans_no = '8J-BUY-' . strtoupper(bin2hex(random_bytes(3)));
                $ins_buyout = $pdo->prepare("INSERT INTO unit_buyouts 
                    (transaction_no, seller_name, seller_contact, seller_address, valid_id_type, valid_id_number, appraisal_record_id, phone_unit_id, amount_paid, payment_method, anti_fencing_declaration_agreed, handled_by_staff_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $ins_buyout->execute([
                    $trans_no, $seller_name, $seller_contact, $seller_address, $valid_id_type, $valid_id_number,
                    $appraisal_id, $phone_unit_id, $amount_paid, $payment_method, 1, $current_user['id']
                ]);

                // 5. Audit log
                $log = $pdo->prepare("INSERT INTO audit_logs (user_id, action, module, details) VALUES (?, 'Completed Unit Buyout', 'Buying', ?)");
                $log->execute([$current_user['id'], "Buyout {$trans_no}: Purchased {$app['brand']} {$app['model_name']} for {$amount_paid}"]);

                $pdo->commit();
                $message = "Unit buyout processed successfully! Transaction No: " . $trans_no . ". Phone is now in stock.";
            } else {
                $pdo->rollBack();
                $message = "Error: Invalid appraisal record selected.";
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $message = "Buyout processing error: " . $e->getMessage();
        }
    }
}

// Fetch recent buyouts
$recent_buyouts = $pdo->query("SELECT b.*, u.brand, u.model_name, u.storage_capacity, u.imei_1 
                               FROM unit_buyouts b
                               LEFT JOIN phone_units u ON b.phone_unit_id = u.id
                               ORDER BY b.id DESC LIMIT 10")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h2 class="h3 fw-bold mb-1"><i class="fa-solid fa-hand-holding-dollar text-primary me-2"></i>Buying of Units (Customer Buyouts)</h2>
                    <p class="text-muted small mb-0">Direct phone purchases from walk-in customers with Anti-Fencing Law (P.D. 1612) identity verification</p>
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
                <!-- Buyout Form -->
                <div class="col-lg-7">
                    <div class="card card-custom p-4">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">Unit Purchase Documentation</h5>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="execute_buyout">

                            <!-- Select Appraisal -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">1. Select Appraised Unit</label>
                                <select name="appraisal_id" id="appraisalSelect" class="form-select" required onchange="updateBuyoutInfo()">
                                    <option value="" disabled <?= $prefill_appraisal_id == 0 ? 'selected' : '' ?>>-- Choose Appraisal Record --</option>
                                    <?php foreach ($eligible_appraisals as $app): ?>
                                        <option value="<?= $app['id'] ?>" 
                                            data-val="<?= $app['final_appraised_value'] ?>"
                                            data-name="<?= htmlspecialchars($app['customer_name']) ?>"
                                            data-contact="<?= htmlspecialchars($app['contact_number']) ?>"
                                            data-device="<?= htmlspecialchars($app['brand'] . ' ' . $app['model_name'] . ' ' . $app['storage_capacity']) ?>"
                                            <?= $prefill_appraisal_id == $app['id'] ? 'selected' : '' ?>>
                                            #<?= $app['id'] ?> - <?= htmlspecialchars($app['customer_name']) ?> | <?= htmlspecialchars($app['brand'] . ' ' . $app['model_name']) ?> (Offer: <?= format_currency($app['final_appraised_value']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Seller Personal Info -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Seller Full Name</label>
                                    <input type="text" name="seller_name" id="sellerName" class="form-control" placeholder="Juan Dela Cruz" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Seller Contact Number</label>
                                    <input type="text" name="seller_contact" id="sellerContact" class="form-control" placeholder="09xxxxxxxxx" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Seller Permanent / Residential Address</label>
                                <textarea name="seller_address" class="form-control" rows="2" placeholder="Barangay, City, Province" required></textarea>
                            </div>

                            <!-- Valid ID Verification -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Government Valid ID Presented</label>
                                    <select name="valid_id_type" class="form-select" required>
                                        <option value="" disabled selected>-- Select ID Type --</option>
                                        <option value="Philippine National ID (PhilSys)">Philippine National ID (PhilSys)</option>
                                        <option value="Driver's License">Driver's License</option>
                                        <option value="UMID / SSS">UMID / SSS</option>
                                        <option value="Passport">Passport</option>
                                        <option value="Voter's ID">Voter's ID</option>
                                        <option value="Postal ID">Postal ID</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Valid ID Number</label>
                                    <input type="text" name="valid_id_number" class="form-control" placeholder="e.g. 1234-5678-9012" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Disbursement Payment Method</label>
                                <select name="payment_method" class="form-select">
                                    <option value="cash">Cash on Hand</option>
                                    <option value="gcash">GCash Transfer</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>

                            <!-- Anti-Fencing Legal Declaration -->
                            <div class="p-3 bg-light rounded-3 mb-4 border">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="anti_fencing_agreed" id="antiFencingCheck" required>
                                    <label class="form-check-label small" for="antiFencingCheck">
                                        <strong>Anti-Fencing Law (P.D. 1612) Compliance:</strong> The seller solemnly declares that the smartphone unit is their legal property, fully paid, free of any liens, and not obtained through theft, robbery, or illegal acquisition.
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-warning w-100 py-2 fw-bold text-dark rounded-pill shadow-sm">
                                <i class="fa-solid fa-file-signature me-1"></i> Disburse Payout & Acquire to Inventory
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Buyout Payout Preview -->
                <div class="col-lg-5">
                    <div class="card card-custom p-4 sticky-top" style="top: 80px;">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">Buyout Financial Summary</h5>

                        <div class="p-3 bg-light rounded-3 mb-3 border text-center">
                            <span class="text-muted small text-uppercase fw-semibold d-block">Cash Disbursed to Seller</span>
                            <h2 class="display-6 fw-extrabold text-success mb-0" id="liveBuyoutPrice">₱0.00</h2>
                            <small class="text-muted" id="liveDeviceLabel">Select an appraised phone</small>
                        </div>

                        <div class="p-3 bg-light rounded-3 small mb-3 border">
                            <div class="fw-bold text-dark mb-1"><i class="fa-solid fa-boxes-packing text-primary me-1"></i>Automatic Stock Intake</div>
                            <p class="text-muted mb-0">Upon disbursement, this phone is immediately categorized into inventory under <strong>Pre-Owned</strong> and will be visible in the catalog and POS register.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Buyouts Table -->
            <div class="card card-custom mt-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Recent Unit Acquisitions (Buyouts)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Transaction #</th>
                                    <th>Seller</th>
                                    <th>Valid ID Verified</th>
                                    <th>Purchased Phone</th>
                                    <th>IMEI</th>
                                    <th class="text-end">Amount Paid</th>
                                    <th>Date</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recent_buyouts)): ?>
                                    <?php foreach ($recent_buyouts as $buy): ?>
                                        <tr>
                                            <td class="fw-bold text-primary"><?= htmlspecialchars($buy['transaction_no']) ?></td>
                                            <td>
                                                <div><?= htmlspecialchars($buy['seller_name']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($buy['seller_contact']) ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?= htmlspecialchars($buy['valid_id_type']) ?></span>
                                                <div class="small text-muted"><?= htmlspecialchars($buy['valid_id_number']) ?></div>
                                            </td>
                                            <td class="fw-semibold"><?= htmlspecialchars($buy['brand'] . ' ' . $buy['model_name'] . ' ' . $buy['storage_capacity']) ?></td>
                                            <td><code><?= substr($buy['imei_1'], 0, 8) ?>******</code></td>
                                            <td class="text-end fw-extrabold text-success"><?= format_currency($buy['amount_paid']) ?></td>
                                            <td class="text-muted small"><?= date('M d, Y H:i', strtotime($buy['created_at'])) ?></td>
                                            <td class="text-center">
                                                <a href="<?= BASE_URL ?>print_buyout.php?id=<?= $buy['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Print Deed of Sale">
                                                    <i class="fa-solid fa-print"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No buyout transactions recorded yet.</td>
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
function updateBuyoutInfo() {
    const select = document.getElementById('appraisalSelect');
    const opt = select.options[select.selectedIndex];
    if (!opt || !opt.dataset.val) return;

    const val = parseFloat(opt.dataset.val);
    document.getElementById('liveBuyoutPrice').innerText = '₱' + val.toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('liveDeviceLabel').innerText = opt.dataset.device || '';

    if (opt.dataset.name && !document.getElementById('sellerName').value) {
        document.getElementById('sellerName').value = opt.dataset.name;
    }
    if (opt.dataset.contact && !document.getElementById('sellerContact').value) {
        document.getElementById('sellerContact').value = opt.dataset.contact;
    }
}
document.addEventListener('DOMContentLoaded', updateBuyoutInfo);
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
