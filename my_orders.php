<?php
$page_title = "My Customer Dashboard";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/order_helpers.php';
require_login();

$user_id = $current_user['id'];
$message = '';
$message_type = 'success';

expire_overdue_pickups($pdo);

// Customer cancels their own open pick-up reservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel_order') {
    $oid = (int)($_POST['order_id'] ?? 0);
    $own = $pdo->prepare("SELECT order_code FROM online_orders WHERE id = ? AND customer_id = ?");
    $own->execute([$oid, $user_id]);
    $code = $own->fetchColumn();
    try {
        if ($code && close_pickup_order($pdo, $oid, 'cancelled', 'Cancelled by the customer online')) {
            audit_log('Pick-up Cancelled', 'Pickups', "Customer cancelled order {$code}");
            $message = "Reservation {$code} was cancelled.";
        } else {
            $message = "That reservation can no longer be cancelled.";
            $message_type = 'danger';
        }
    } catch (Exception $e) {
        $message = "We couldn't cancel the reservation right now. Please try again.";
        $message_type = 'danger';
    }
}

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $fullname = sanitize($_POST['fullname'] ?? '');
    $contact = preg_replace('/[\s-]/', '', sanitize($_POST['contact_number'] ?? ''));
    $address = sanitize($_POST['address'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $current_password = $_POST['current_password'] ?? '';

    $message_type = 'danger';
    if ($fullname === '') {
        $message = "Please enter your full name.";
    } elseif (!preg_match('/^(09\d{9}|\+639\d{9})$/', $contact)) {
        $message = "Please enter an 11-digit mobile number starting with 09.";
    } elseif ($new_password !== '' && strlen($new_password) < 8) {
        $message = "Your new password must be at least 8 characters.";
    } else {
        try {
            $row = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $row->execute([$user_id]);
            $hash = $row->fetchColumn();

            if ($new_password !== '' && !password_verify($current_password, (string)$hash)) {
                $message = "Your current password is incorrect, so the password was not changed.";
            } else {
                if ($new_password !== '') {
                    $stmt = $pdo->prepare("UPDATE users SET fullname = ?, contact_number = ?, address = ?, password = ? WHERE id = ?");
                    $stmt->execute([$fullname, $contact, $address, password_hash($new_password, PASSWORD_DEFAULT), $user_id]);
                    audit_log('Password Changed', 'Account', "Customer #{$user_id} changed their password");
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET fullname = ?, contact_number = ?, address = ? WHERE id = ?");
                    $stmt->execute([$fullname, $contact, $address, $user_id]);
                }
                // Keep the session (navbar, lookups below) in step with the new details
                $_SESSION['user']['fullname'] = $fullname;
                $_SESSION['user']['contact_number'] = $contact;
                $current_user = get_current_user_data();
                $message = "Profile details updated successfully!";
                $message_type = 'success';
            }
        } catch (Exception $e) {
            error_log('Profile update error: ' . $e->getMessage());
            $message = "We couldn't save your profile right now. Please try again.";
        }
    }
}

// The profile form needs the address, which isn't kept in the session
$profile_stmt = $pdo->prepare("SELECT fullname, contact_number, address FROM users WHERE id = ?");
$profile_stmt->execute([$user_id]);
$profile = $profile_stmt->fetch() ?: ['fullname' => $current_user['fullname'], 'contact_number' => $current_user['contact_number'], 'address' => ''];

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

// 2. Customer repair orders: booked on this account, or under this account's mobile number.
//    (Matching by name used to show other people with the same name their repairs.)
$my_phone = phone_key($current_user['contact_number'] ?? '');
$rep_stmt = $pdo->prepare("SELECT r.*, t.fullname as tech_name
                           FROM repair_job_orders r
                           LEFT JOIN users t ON r.technician_id = t.id
                           WHERE r.customer_id = ? OR (? <> '' AND " . phone_key_sql('r.customer_contact') . " = ?)
                           ORDER BY r.id DESC");
$rep_stmt->execute([$user_id, $my_phone, $my_phone]);
$repairs = $rep_stmt->fetchAll();

// 3. Customer trade-ins & appraisals (by mobile number only)
$appraisals = [];
if ($my_phone !== '') {
    $app_stmt = $pdo->prepare("SELECT * FROM appraisal_records WHERE " . phone_key_sql('contact_number') . " = ? ORDER BY id DESC");
    $app_stmt->execute([$my_phone]);
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
                                        <?php [$bl, $bc, $bi] = pickup_status_badge($ord['pickup_status']); ?>
                                        <span class="badge <?= $bc ?>"><i class="fa-solid <?= $bi ?> me-1"></i> <?= $bl ?></span>
                                    </div>
                                </div>

                                <?php $ord_open = in_array($ord['pickup_status'], ['pending_confirmation', 'ready_for_pickup'], true); ?>
                                <?php if ($ord_open): ?>
                                    <!-- Claim Code Ticket Voucher -->
                                    <div class="p-3 bg-light rounded-3 text-center my-2 border">
                                        <span class="small text-muted text-uppercase fw-semibold d-block">Claim Verification Code</span>
                                        <h2 class="display-6 fw-extrabold text-dark font-monospace mb-0"><?= htmlspecialchars($ord['claim_verification_code']) ?></h2>
                                        <small class="text-muted">Show this code at the 8J counter. Held until <?= date('M j', strtotime($ord['pickup_target_date'] . ' +' . PICKUP_HOLD_DAYS . ' days')) ?>.</small>
                                    </div>
                                <?php elseif ($ord['pickup_status'] === 'claimed'): ?>
                                    <div class="small text-muted my-2"><i class="fa-solid fa-circle-check text-success me-1"></i>Collected <?= !empty($ord['claimed_at']) ? date('M j, Y g:i A', strtotime($ord['claimed_at'])) : '' ?></div>
                                <?php else: ?>
                                    <div class="small text-muted my-2"><i class="fa-solid fa-circle-info me-1"></i><?= $ord['pickup_status'] === 'expired' ? 'This reservation expired because it wasn\'t collected in time.' : 'This reservation was cancelled.' ?></div>
                                <?php endif; ?>

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
                                        <span class="text-muted small"><?= $ord['pickup_status'] === 'claimed' ? 'Paid:' : 'Total Due Upon Claim:' ?></span>
                                        <span class="fw-extrabold text-dark fs-5 ms-1"><?= format_currency($ord['total_amount']) ?></span>
                                    </div>
                                    <?php if ($ord_open): ?>
                                        <div class="d-flex gap-2">
                                            <form method="POST" action="" onsubmit="return confirm('Cancel reservation <?= htmlspecialchars($ord['order_code']) ?>? Reserved phones go back on sale.');">
                                                <input type="hidden" name="action" value="cancel_order">
                                                <input type="hidden" name="order_id" value="<?= (int)$ord['id'] ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">Cancel</button>
                                            </form>
                                            <button class="btn btn-outline-dark btn-sm rounded-pill px-3 btn-tactile" onclick="window.print()">
                                                <i class="fa-solid fa-print me-1"></i> Print Slip
                                            </button>
                                        </div>
                                    <?php endif; ?>
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
                                    <a href="<?= BASE_URL ?>track_repair.php?q=<?= urlencode($rep['job_order_no']) ?>" class="btn btn-outline-dark btn-sm rounded-pill px-3 btn-tactile">
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
                                    <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars(html_entity_decode($profile['fullname'] ?? '', ENT_QUOTES)) ?>" required maxlength="120" autocomplete="name">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Mobile Contact Number</label>
                                    <input type="tel" name="contact_number" class="form-control" value="<?= htmlspecialchars($profile['contact_number'] ?? '') ?>" placeholder="09xxxxxxxxx" inputmode="tel" autocomplete="tel" required>
                                    <div class="form-text small">Your repairs and trade-ins are linked to this number.</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Delivery / Residential Address</label>
                                <textarea name="address" class="form-control" rows="2" placeholder="Barangay, City, Province"><?= htmlspecialchars(html_entity_decode($profile['address'] ?? '', ENT_QUOTES)) ?></textarea>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">New password <span class="text-muted fw-normal">(leave blank to keep current)</span></label>
                                    <input type="password" name="new_password" id="pfNewPw" class="form-control" minlength="8" autocomplete="new-password" placeholder="At least 8 characters">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Current password <span class="text-muted fw-normal">(needed to change it)</span></label>
                                    <input type="password" name="current_password" id="pfCurPw" class="form-control" autocomplete="current-password">
                                </div>
                            </div>
                            <script>
                                // Ask for the current password only when a new one is being set
                                document.getElementById('pfNewPw')?.addEventListener('input', e => {
                                    document.getElementById('pfCurPw').required = e.target.value.length > 0;
                                });
                            </script>

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
