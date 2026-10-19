<?php
$page_title = "My Customer Dashboard";
require_once __DIR__ . '/config/db.php';
require_login();

$user_id = $current_user['id'];
$message = '';
$message_type = 'success';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $fullname = sanitize($_POST['fullname'] ?? '');
    $contact = sanitize($_POST['contact_number'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $new_password = $_POST['new_password'] ?? '';

    try {
        if (!empty($new_password)) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET fullname = ?, contact_number = ?, address = ?, password = ? WHERE id = ?");
            $stmt->execute([$fullname, $contact, $address, $hashed, $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET fullname = ?, contact_number = ?, address = ? WHERE id = ?");
            $stmt->execute([$fullname, $contact, $address, $user_id]);
        }
        $current_user = get_current_user_data();
        $message = "Profile details updated successfully!";
    } catch (Exception $e) {
        $message = "Update failed: " . $e->getMessage();
        $message_type = 'danger';
    }
}

// 1. Fetch In-Store Pick-Up Orders with Items
$ord_stmt = $pdo->prepare("SELECT * FROM online_orders WHERE customer_id = ? ORDER BY id DESC");
$ord_stmt->execute([$user_id]);
$my_orders = $ord_stmt->fetchAll();

// Attach items to each order
foreach ($my_orders as &$o) {
    $item_stmt = $pdo->prepare("SELECT * FROM online_order_items WHERE order_id = ?");
    $item_stmt->execute([$o['id']]);
    $o['items'] = $item_stmt->fetchAll();
}
unset($o);

// 2. Fetch Customer Repair Orders (By Contact No. or Fullname)
$repairs = [];
if (!empty($current_user['contact_number']) || !empty($current_user['fullname'])) {
    $rep_stmt = $pdo->prepare("SELECT r.*, t.fullname as tech_name 
                               FROM repair_job_orders r 
                               LEFT JOIN users t ON r.technician_id = t.id 
                               WHERE r.customer_contact = ? OR r.customer_name = ? 
                               ORDER BY r.id DESC");
    $rep_stmt->execute([$current_user['contact_number'], $current_user['fullname']]);
    $repairs = $rep_stmt->fetchAll();
}

// 3. Fetch Customer Trade-Ins & Appraisals
$appraisals = [];
if (!empty($current_user['contact_number']) || !empty($current_user['fullname'])) {
    $app_stmt = $pdo->prepare("SELECT * FROM appraisal_records WHERE contact_number = ? OR customer_name = ? ORDER BY id DESC");
    $app_stmt->execute([$current_user['contact_number'], $current_user['fullname']]);
    $appraisals = $app_stmt->fetchAll();
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <!-- Top Account Banner -->
    <div class="card card-custom p-4 mb-4 border shadow-sm animate-fade-in-up">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; font-size: 1.4rem;">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($current_user['fullname']) ?></h3>
                    <div class="text-muted small">
                        <span class="badge bg-light text-dark border me-1">Customer Account</span>
                        <i class="fa-solid fa-phone me-1"></i><?= htmlspecialchars($current_user['contact_number'] ?: 'No contact registered') ?>
                        <span class="mx-1">•</span>
                        <i class="fa-solid fa-envelope me-1"></i><?= htmlspecialchars($current_user['email']) ?>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>catalog.php" class="btn btn-outline-dark btn-sm rounded-pill px-3 btn-tactile">
                    <i class="fa-solid fa-store me-1"></i> Browse Catalog
                </a>
                <a href="<?= BASE_URL ?>book_repair.php" class="btn btn-primary btn-sm rounded-pill px-3 btn-tactile">
                    <i class="fa-solid fa-wrench me-1"></i> Book Device Repair
                </a>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show mb-4 rounded-3" role="alert">
            <i class="fa-solid <?= $message_type === 'danger' ? 'fa-circle-exclamation' : 'fa-circle-check' ?> me-2"></i>
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills mb-4 gap-2" id="customerTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active fw-bold" id="pickups-tab" data-bs-toggle="pill" data-bs-target="#pickups-pane">
                <i class="fa-solid fa-bag-shopping me-2"></i>Pick-Up Reservations (<?= count($my_orders) ?>)
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold" id="repairs-tab" data-bs-toggle="pill" data-bs-target="#repairs-pane">
                <i class="fa-solid fa-screwdriver-wrench me-2"></i>Repair Job Tickets (<?= count($repairs) ?>)
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold" id="trades-tab" data-bs-toggle="pill" data-bs-target="#trades-pane">
                <i class="fa-solid fa-repeat me-2"></i>Trade-Ins & Appraisals (<?= count($appraisals) ?>)
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold" id="profile-tab" data-bs-toggle="pill" data-bs-target="#profile-pane">
                <i class="fa-solid fa-id-badge me-2"></i>Account Settings
            </button>
        </li>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content" id="customerTabsContent">

        <!-- 1. Pick-Up Reservations Pane -->
        <div class="tab-pane fade show active" id="pickups-pane" role="tabpanel">
            <?php if (!empty($my_orders)): ?>
                <div class="row g-4">
                    <?php foreach ($my_orders as $ord): ?>
                        <div class="col-md-6 col-lg-6">
                            <div class="card card-custom p-4 border stat-card h-100 animate-scale-in">
                                <div class="d-flex justify-content-between align-items-start mb-3 border-bottom pb-2">
                                    <div>
                                        <span class="badge bg-light text-secondary border font-monospace mb-1"><?= htmlspecialchars($ord['order_code']) ?></span>
                                        <div class="small text-muted">Pick-Up Date: <strong class="text-dark"><?= date('F d, Y', strtotime($ord['pickup_target_date'])) ?></strong></div>
                                    </div>
                                    <div>
                                        <?php if ($ord['pickup_status'] === 'claimed'): ?>
                                            <span class="badge bg-dark text-white"><i class="fa-solid fa-check me-1"></i> Claimed</span>
                                        <?php elseif ($ord['pickup_status'] === 'ready_for_pickup'): ?>
                                            <span class="badge bg-light text-dark border border-dark"><i class="fa-solid fa-bell me-1"></i> Ready for Pick-Up</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-secondary border"><i class="fa-solid fa-clock me-1"></i> Preparing</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Claim Code Ticket Voucher -->
                                <div class="p-3 bg-light rounded-3 text-center my-2 border">
                                    <span class="small text-muted text-uppercase fw-semibold d-block">Claim Verification Code</span>
                                    <h2 class="display-6 fw-extrabold text-dark font-monospace mb-0"><?= htmlspecialchars($ord['claim_verification_code']) ?></h2>
                                    <small class="text-muted">Present this code at Tanauan store counter</small>
                                </div>

                                <!-- Items List -->
                                <?php if (!empty($ord['items'])): ?>
                                    <div class="my-3 small">
                                        <div class="fw-semibold text-muted text-uppercase mb-1" style="font-size: 0.72rem;">Reserved Items:</div>
                                        <ul class="list-group list-group-flush rounded border">
                                            <?php foreach ($ord['items'] as $item): ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                                    <div>
                                                        <span class="fw-semibold text-dark"><?= htmlspecialchars($item['item_name']) ?></span>
                                                        <small class="text-muted d-block">Qty: <?= $item['quantity'] ?> × <?= format_currency($item['unit_price']) ?></small>
                                                    </div>
                                                    <strong class="text-dark"><?= format_currency($item['subtotal']) ?></strong>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                                <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                                    <div>
                                        <span class="text-muted small">Total Due Upon Claim:</span>
                                        <span class="fw-extrabold text-dark fs-5 ms-1"><?= format_currency($ord['total_amount']) ?></span>
                                    </div>
                                    <button class="btn btn-outline-dark btn-sm rounded-pill px-3 btn-tactile" onclick="window.print()">
                                        <i class="fa-solid fa-print me-1"></i> Print Slip
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card card-custom p-5 text-center text-muted">
                    <i class="fa-solid fa-basket-shopping fs-1 opacity-25 mb-3"></i>
                    <h5 class="fw-bold text-dark">No Active Pick-Up Reservations</h5>
                    <p class="small">Reserve accessories or certified smartphones online and pick them up at our Tanauan branch.</p>
                    <a href="<?= BASE_URL ?>catalog.php" class="btn btn-primary rounded-pill px-4 mx-auto btn-tactile">Browse Products</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- 2. Repair Job Tickets Pane -->
        <div class="tab-pane fade" id="repairs-pane" role="tabpanel">
            <?php if (!empty($repairs)): ?>
                <div class="row g-4">
                    <?php foreach ($repairs as $rep): ?>
                        <div class="col-md-6 col-lg-6">
                            <div class="card card-custom p-4 border stat-card h-100">
                                <div class="d-flex justify-content-between align-items-start mb-3 border-bottom pb-2">
                                    <div>
                                        <span class="badge bg-light text-secondary border font-monospace mb-1"><?= htmlspecialchars($rep['job_order_no']) ?></span>
                                        <h5 class="fw-bold text-dark mb-0"><?= htmlspecialchars($rep['device_brand'] . ' ' . $rep['device_model']) ?></h5>
                                    </div>
                                    <div>
                                        <span class="badge bg-dark text-white border">
                                            <?= ucwords(str_replace('_', ' ', $rep['status'])) ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="small mb-3">
                                    <div class="text-muted fw-semibold">Reported Issue:</div>
                                    <p class="mb-2 text-dark"><?= htmlspecialchars($rep['reported_issues']) ?></p>

                                    <?php if (!empty($rep['diagnostic_notes'])): ?>
                                        <div class="p-2 bg-light rounded border text-muted small mb-2">
                                            <strong>Technician Notes:</strong> <?= htmlspecialchars($rep['diagnostic_notes']) ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="row g-2 text-muted">
                                        <div class="col-6">Assigned Tech: <strong class="text-dark"><?= htmlspecialchars($rep['tech_name'] ?? 'In Queue') ?></strong></div>
                                        <div class="col-6 text-end">Est. Cost: <strong class="text-dark"><?= format_currency($rep['final_cost'] > 0 ? $rep['final_cost'] : $rep['estimated_cost']) ?></strong></div>
                                    </div>
                                </div>

                                <?php if ($rep['warranty_expiry']): ?>
                                    <div class="alert alert-dark py-2 px-3 small d-flex justify-content-between align-items-center mb-3">
                                        <span><i class="fa-solid fa-shield-halved me-1"></i> 30-Day Service Warranty Active</span>
                                        <strong>Until <?= date('M d, Y', strtotime($rep['warranty_expiry'])) ?></strong>
                                    </div>
                                <?php endif; ?>

                                <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                                    <a href="<?= BASE_URL ?>track_repair.php?ticket=<?= urlencode($rep['job_order_no']) ?>" class="btn btn-outline-dark btn-sm rounded-pill px-3 btn-tactile">
                                        <i class="fa-solid fa-magnifying-glass me-1"></i> Live Tracking
                                    </a>
                                    <a href="<?= BASE_URL ?>print_job_order.php?id=<?= $rep['id'] ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                        <i class="fa-solid fa-print me-1"></i> Claim Stub
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card card-custom p-5 text-center text-muted">
                    <i class="fa-solid fa-wrench fs-1 opacity-25 mb-3"></i>
                    <h5 class="fw-bold text-dark">No Repair Tickets Found</h5>
                    <p class="small">Have a cracked screen, dying battery, or charging issue? Schedule a diagnostic appointment today.</p>
                    <a href="<?= BASE_URL ?>book_repair.php" class="btn btn-primary rounded-pill px-4 mx-auto btn-tactile">Book a Repair</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- 3. Trade-Ins & Appraisals Pane -->
        <div class="tab-pane fade" id="trades-pane" role="tabpanel">
            <?php if (!empty($appraisals)): ?>
                <div class="row g-4">
                    <?php foreach ($appraisals as $app): ?>
                        <div class="col-md-6">
                            <div class="card card-custom p-4 border stat-card h-100">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="badge bg-light text-secondary border font-monospace">Appraisal #<?= $app['id'] ?></span>
                                        <h5 class="fw-bold text-dark mt-1 mb-0"><?= htmlspecialchars($app['brand'] . ' ' . $app['model_name'] . ' ' . $app['storage_capacity']) ?></h5>
                                    </div>
                                    <span class="badge bg-dark text-white"><?= ucwords(str_replace('_', ' ', $app['status'])) ?></span>
                                </div>

                                <div class="p-3 bg-light rounded-3 my-3 border text-center">
                                    <span class="text-muted small text-uppercase fw-semibold d-block">Official Appraised Value</span>
                                    <h3 class="fw-extrabold text-dark font-monospace mb-0"><?= format_currency($app['final_appraised_value']) ?></h3>
                                    <small class="text-muted">Grade: <?= htmlspecialchars($app['cosmetic_grade']) ?> | Battery: <?= $app['battery_health'] ?>%</small>
                                </div>

                                <div class="small text-muted">
                                    Screen: <strong class="text-dark"><?= htmlspecialchars($app['screen_condition']) ?></strong> • 
                                    Evaluated on: <strong><?= date('M d, Y', strtotime($app['created_at'])) ?></strong>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card card-custom p-5 text-center text-muted">
                    <i class="fa-solid fa-repeat fs-1 opacity-25 mb-3"></i>
                    <h5 class="fw-bold text-dark">No Trade-Ins or Appraisals Recorded</h5>
                    <p class="small">Want to swap your smartphone or trade up to a newer model? Bring your device in for live evaluation.</p>
                    <a href="<?= BASE_URL ?>index.php#tradeEstimator" class="btn btn-primary rounded-pill px-4 mx-auto btn-tactile">Learn More About Swaps</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- 4. Account Profile Settings Pane -->
        <div class="tab-pane fade" id="profile-pane" role="tabpanel">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card card-custom p-4">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">Edit Account Profile</h5>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="update_profile">

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Full Name</label>
                                    <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($current_user['fullname']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Mobile Contact Number</label>
                                    <input type="text" name="contact_number" class="form-control" value="<?= htmlspecialchars($current_user['contact_number']) ?>" placeholder="09xxxxxxxxx" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Delivery / Residential Address</label>
                                <textarea name="address" class="form-control" rows="2" placeholder="Barangay, City, Province"><?= htmlspecialchars($current_user['address'] ?? '') ?></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-semibold">Change Password (leave blank to keep current)</label>
                                <input type="password" name="new_password" class="form-control" placeholder="Enter new password (optional)">
                            </div>

                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold btn-tactile">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
