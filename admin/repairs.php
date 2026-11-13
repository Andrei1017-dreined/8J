<?php
$page_title = "Repair & Service Job Orders";
require_once __DIR__ . '/../config/db.php';
require_role('admin', 'staff', 'technician');

$message = '';

// Handle New Job Order Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_job_order') {
    $customer_name = sanitize($_POST['customer_name'] ?? '');
    $customer_contact = sanitize($_POST['customer_contact'] ?? '');
    $device_brand = sanitize($_POST['device_brand'] ?? '');
    $device_model = sanitize($_POST['device_model'] ?? '');
    $imei = sanitize($_POST['imei'] ?? '');
    $passcode = sanitize($_POST['passcode'] ?? '');
    $issues = sanitize($_POST['reported_issues'] ?? '');
    $estimated_cost = max(0, (float)($_POST['estimated_cost'] ?? 0));
    $deposit_paid = max(0, (float)($_POST['deposit_paid'] ?? 0));
    $technician_id = !empty($_POST['technician_id']) ? (int)$_POST['technician_id'] : null;
    $customer_contact = preg_replace('/[\s-]/', '', $customer_contact);

    if ($customer_name === '' || $device_brand === '' || $device_model === '' || $issues === '') {
        $message = "Error: Customer name, device and the reported problem are required.";
    } elseif (!preg_match('/^(09\d{9}|\+639\d{9})$/', $customer_contact)) {
        $message = "Error: Please enter the customer's 11-digit mobile number (it's used to track the repair).";
    } elseif ($estimated_cost > 0 && $deposit_paid > $estimated_cost) {
        $message = "Error: The deposit can't be more than the estimated cost.";
    } else {
        // 6 hex characters, same as online bookings, so job numbers can't be guessed on the tracking page
        $job_order_no = '8J-REP-' . date('ym') . '-' . strtoupper(bin2hex(random_bytes(3)));
        try {
            // Link to the customer's account when the mobile number matches one, so it shows in their My Orders
            $acct = $pdo->prepare("SELECT id FROM users WHERE role = 'customer' AND " . phone_key_sql('contact_number') . " = ? LIMIT 1");
            $acct->execute([phone_key($customer_contact)]);
            $customer_id = $acct->fetchColumn() ?: null;

            $stmt = $pdo->prepare("INSERT INTO repair_job_orders
                (job_order_no, customer_id, customer_name, customer_contact, device_brand, device_model, device_imei_or_serial, passcode_or_pattern, reported_issues, estimated_cost, deposit_paid, technician_id, status, scheduled_date)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'received', CURDATE())");
            $stmt->execute([
                $job_order_no, $customer_id, $customer_name, $customer_contact, $device_brand, $device_model,
                $imei, $passcode, $issues, $estimated_cost, $deposit_paid, $technician_id
            ]);
            $new_job_id = $pdo->lastInsertId();
            audit_log('Repair Job Created', 'Repairs', "Job {$job_order_no} for {$customer_name}: {$device_brand} {$device_model}");
            $message = "Repair Job Order {$job_order_no} created successfully!";
        } catch (Exception $e) {
            error_log('Create job error: ' . $e->getMessage());
            $message = "Error: The job order could not be created. Please try again.";
        }
    }
}

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $job_id = (int)($_POST['job_id'] ?? 0);
    $new_status = $_POST['status'] ?? '';
    $diagnostic_notes = sanitize($_POST['diagnostic_notes'] ?? '');
    $final_cost = max(0, (float)($_POST['final_cost'] ?? 0));
    $valid_statuses = ['scheduled', 'received', 'diagnosing', 'waiting_parts', 'repairing', 'ready_for_pickup', 'completed', 'unrepairable', 'cancelled'];

    $cur = $pdo->prepare("SELECT * FROM repair_job_orders WHERE id = ?");
    $cur->execute([$job_id]);
    $job_now = $cur->fetch();

    if (!$job_now) {
        $message = "Error: That job order no longer exists.";
    } elseif (!in_array($new_status, $valid_statuses, true)) {
        $message = "Error: Please choose a valid status.";
    } else {
        try {
            // The warranty starts once, when the phone is released, using the job's own warranty length.
            // Editing notes later no longer restarts it; reopening a job clears it.
            if ($new_status === 'completed') {
                $completed_at = $job_now['completed_at'] ?: date('Y-m-d H:i:s');
                $warranty_expiry = $job_now['warranty_expiry'] ?: date('Y-m-d', strtotime($completed_at . ' +' . max(0, (int)($job_now['warranty_days'] ?? 30)) . ' days'));
            } else {
                $completed_at = null;
                $warranty_expiry = null;
            }

            // Technician reassignment (admins/staff only; a technician can't move jobs between people)
            $technician_id = $job_now['technician_id'];
            if (has_role('admin', 'staff') && array_key_exists('technician_id', $_POST)) {
                $technician_id = $_POST['technician_id'] !== '' ? (int)$_POST['technician_id'] : null;
            } elseif (has_role('technician') && empty($job_now['technician_id'])) {
                $technician_id = (int)$current_user['id']; // a technician who works an unassigned job takes it
            }

            $stmt = $pdo->prepare("UPDATE repair_job_orders SET
                status = ?, diagnostic_notes = ?, final_cost = ?, warranty_expiry = ?, completed_at = ?, technician_id = ?
                WHERE id = ?");
            $stmt->execute([$new_status, $diagnostic_notes, $final_cost, $warranty_expiry, $completed_at, $technician_id, $job_id]);
            $status_label = ucwords(str_replace('_', ' ', $new_status));
            audit_log('Repair Job Updated', 'Repairs', "Job {$job_now['job_order_no']}: {$job_now['status']} → {$new_status}" . ($final_cost > 0 ? ', bill ' . format_currency($final_cost) : ''));
            $message = "Job {$job_now['job_order_no']} updated: {$status_label}";
        } catch (Exception $e) {
            error_log('Update job error: ' . $e->getMessage());
            $message = "Error: The update could not be saved. Please try again.";
        }
    }
}

// Fetch technicians
$technicians = $pdo->query("SELECT id, fullname FROM users WHERE role IN ('technician', 'admin')")->fetchAll();

// Fetch job orders: status filter + "my jobs" + search
$status_filter = $_GET['status'] ?? '';
$search = trim((string)($_GET['q'] ?? ''));
$mine = isset($_GET['mine']);
$where = [];
$params = [];
if ($status_filter === 'active') {
    $where[] = "r.status IN ('scheduled','received','diagnosing','waiting_parts','repairing','ready_for_pickup')";
} elseif ($status_filter !== '') {
    $where[] = "r.status = ?";
    $params[] = $status_filter;
}
if ($mine) {
    $where[] = "r.technician_id = ?";
    $params[] = (int)$current_user['id'];
}
if ($search !== '') {
    $where[] = "(r.job_order_no LIKE ? OR r.customer_name LIKE ? OR r.device_model LIKE ?" . (phone_key($search) !== '' ? " OR " . phone_key_sql('r.customer_contact') . " = ?" : "") . ")";
    $like = '%' . $search . '%';
    array_push($params, $like, $like, $like);
    if (phone_key($search) !== '') $params[] = phone_key($search);
}
$sql = "SELECT r.*, t.fullname as tech_name FROM repair_job_orders r LEFT JOIN users t ON r.technician_id = t.id"
     . ($where ? " WHERE " . implode(' AND ', $where) : '')
     . " ORDER BY FIELD(r.status, 'ready_for_pickup', 'scheduled', 'received', 'diagnosing', 'waiting_parts', 'repairing') = 0,
                FIELD(r.status, 'ready_for_pickup', 'scheduled', 'received', 'diagnosing', 'waiting_parts', 'repairing'),
                r.id DESC
        LIMIT 300";
$st = $pdo->prepare($sql);
$st->execute($params);
$job_orders = $st->fetchAll();

$status_counts = $pdo->query("SELECT status, COUNT(*) c FROM repair_job_orders GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$active_count = array_sum(array_intersect_key($status_counts, array_flip(['scheduled', 'received', 'diagnosing', 'waiting_parts', 'repairing', 'ready_for_pickup'])));

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h2 class="h3 fw-bold mb-1"><i class="fa-solid fa-screwdriver-wrench text-dark me-2"></i>Repair & Service Scheduling</h2>
                    <p class="text-muted small mb-0">Job order tracking, technician assignment, diagnostic logs, and service warranty management</p>
                </div>
                <button class="btn btn-dark fw-bold btn-sm rounded-pill px-3 shadow-sm btn-tactile" data-bs-toggle="modal" data-bs-target="#newJobModal">
                    <i class="fa-solid fa-plus me-1"></i> New Repair Job Order
                </button>
            </div>

            <?php if ($message): ?>
                <div class="alert <?= strpos($message, 'Error') !== false ? 'alert-danger' : 'alert-success' ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <?php if (!empty($new_job_id)): ?>
                        <a href="<?= BASE_URL ?>print_job_order.php?id=<?= (int)$new_job_id ?>" target="_blank" class="btn btn-sm btn-dark rounded-pill ms-2"><i class="fa-solid fa-print me-1"></i> Print claim stub</a>
                    <?php endif; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Status Filter Pills (with counts) + search -->
            <?php
            $pills = [
                ''                 => ['All', array_sum($status_counts)],
                'active'           => ['Active', $active_count],
                'scheduled'        => ['Booked online', $status_counts['scheduled'] ?? 0],
                'received'         => ['Received', $status_counts['received'] ?? 0],
                'diagnosing'       => ['Diagnosing', $status_counts['diagnosing'] ?? 0],
                'waiting_parts'    => ['Waiting parts', $status_counts['waiting_parts'] ?? 0],
                'repairing'        => ['Repairing', $status_counts['repairing'] ?? 0],
                'ready_for_pickup' => ['Ready for pick-up', $status_counts['ready_for_pickup'] ?? 0],
                'completed'        => ['Released', $status_counts['completed'] ?? 0],
                'unrepairable'     => ['Unrepairable', $status_counts['unrepairable'] ?? 0],
                'cancelled'        => ['Cancelled', $status_counts['cancelled'] ?? 0],
            ];
            $keep = ($mine ? '&mine=1' : '') . ($search !== '' ? '&q=' . urlencode($search) : '');
            ?>
            <div class="d-flex flex-wrap gap-2 mb-3">
                <?php foreach ($pills as $key => [$label, $n]): ?>
                    <?php if ($n === 0 && !in_array($key, ['', 'active', $status_filter], true)) continue; ?>
                    <a href="<?= BASE_URL ?>admin/repairs.php?status=<?= $key ?><?= $keep ?>" class="btn btn-sm <?= $status_filter === $key ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill">
                        <?= $label ?> <span class="ms-1 opacity-75"><?= (int)$n ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
            <form method="GET" class="d-flex flex-wrap gap-2 mb-4" style="max-width: 640px;">
                <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
                <input type="search" name="q" value="<?= htmlspecialchars($search) ?>" class="form-control form-control-sm" style="flex: 1 1 260px;" placeholder="Search job #, customer name, phone or model">
                <?php if (has_role('technician', 'admin')): ?>
                    <div class="form-check form-switch d-flex align-items-center gap-2 mb-0">
                        <input class="form-check-input" type="checkbox" name="mine" value="1" id="mineSwitch" <?= $mine ? 'checked' : '' ?> onchange="this.form.submit()">
                        <label class="form-check-label small" for="mineSwitch">My jobs only</label>
                    </div>
                <?php endif; ?>
                <button class="btn btn-sm btn-dark rounded-pill px-3">Search</button>
                <?php if ($search !== '' || $mine): ?><a href="<?= BASE_URL ?>admin/repairs.php?status=<?= htmlspecialchars($status_filter) ?>" class="btn btn-sm btn-link">Clear</a><?php endif; ?>
            </form>

            <!-- Job Orders Table -->
            <div class="card card-custom">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Job Order #</th>
                                    <th>Customer Info</th>
                                    <th>Device</th>
                                    <th>Reported Issues</th>
                                    <th>Assigned Tech</th>
                                    <th>Est. / Final Cost</th>
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($job_orders)): ?>
                                    <?php foreach ($job_orders as $job): ?>
                                        <tr>
                                            <td class="fw-bold text-primary">
                                                <?= htmlspecialchars($job['job_order_no']) ?>
                                                <div class="small text-muted"><?= date('M d, Y', strtotime($job['created_at'])) ?></div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold"><?= htmlspecialchars($job['customer_name']) ?></div>
                                                <small class="text-muted"><i class="fa-solid fa-phone me-1"></i><?= htmlspecialchars($job['customer_contact']) ?></small>
                                            </td>
                                            <td>
                                                <div class="fw-bold"><?= htmlspecialchars($job['device_brand'] . ' ' . $job['device_model']) ?></div>
                                                <small class="text-muted">SN: <?= htmlspecialchars($job['device_imei_or_serial'] ?? 'N/A') ?></small>
                                            </td>
                                            <td style="max-width: 220px;">
                                                <div class="expandable-issue text-truncate" 
                                                     onclick="toggleIssueText(this)" 
                                                     title="Click to view full issue details" 
                                                     style="cursor: pointer; transition: all 0.2s ease;">
                                                    <?= htmlspecialchars($job['reported_issues']) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    <i class="fa-solid fa-user-gear me-1 text-primary"></i>
                                                    <?= htmlspecialchars($job['tech_name'] ?? 'Unassigned') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark">
                                                    <?= $job['final_cost'] > 0 ? format_currency($job['final_cost']) : format_currency($job['estimated_cost']) ?>
                                                </div>
                                                <?php if ($job['deposit_paid'] > 0): ?>
                                                    <small class="text-success">Paid Dep: <?= format_currency($job['deposit_paid']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $badge_map = [
                                                    'scheduled' => 'bg-info-subtle text-info',
                                                    'received' => 'bg-secondary-subtle text-secondary',
                                                    'diagnosing' => 'bg-warning-subtle text-warning',
                                                    'waiting_parts' => 'bg-danger-subtle text-danger',
                                                    'repairing' => 'bg-primary-subtle text-primary',
                                                    'ready_for_pickup' => 'bg-success-subtle text-success fw-bold',
                                                    'completed' => 'bg-success text-white',
                                                    'unrepairable' => 'bg-dark-subtle text-dark',
                                                    'cancelled' => 'bg-light text-muted text-decoration-line-through',
                                                ];
                                                $cls = $badge_map[$job['status']] ?? 'bg-light text-dark';
                                                ?>
                                                <span class="badge <?= $cls ?> border">
                                                    <?= ucwords(str_replace('_', ' ', $job['status'])) ?>
                                                </span>
                                                <?php if ($job['status'] === 'completed' && $job['warranty_expiry']): ?>
                                                    <div class="small text-success" style="font-size: 0.68rem;">
                                                        <i class="fa-solid fa-shield me-1"></i>Warranty: <?= date('M d, Y', strtotime($job['warranty_expiry'])) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" onclick="openUpdateModal(<?= htmlspecialchars(json_encode($job)) ?>)">
                                                        Update
                                                    </button>
                                                    <a href="<?= BASE_URL ?>print_job_order.php?id=<?= $job['id'] ?>" target="_blank" class="btn btn-outline-secondary" title="Print Claim Stub">
                                                        <i class="fa-solid fa-print"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No repair tickets found.</td>
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

<!-- Modal: New Repair Job -->
<div class="modal fade" id="newJobModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="action" value="create_job_order">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Create Repair Job Order Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Customer Full Name</label>
                            <input type="text" name="customer_name" class="form-control" required placeholder="e.g. Carlo Reyes">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Contact Number</label>
                            <input type="text" name="customer_contact" class="form-control" required placeholder="09xxxxxxxxx">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <label class="form-label small fw-semibold">Device Brand</label>
                            <input type="text" name="device_brand" class="form-control" required placeholder="e.g. Apple / Samsung / Oppo">
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold">Device Model</label>
                            <input type="text" name="device_model" class="form-control" required placeholder="e.g. iPhone 12 Pro">
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold">IMEI / Serial (Optional)</label>
                            <input type="text" name="imei" class="form-control" placeholder="15 digits">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Screen Lock / Passcode (For technician diagnostics)</label>
                            <input type="text" name="passcode" class="form-control" placeholder="Customer provided passcode">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Assign Technician</label>
                            <select name="technician_id" class="form-select">
                                <option value="">-- Unassigned --</option>
                                <?php foreach ($technicians as $t): ?>
                                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['fullname']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Reported Hardware/Software Issues</label>
                        <textarea name="reported_issues" class="form-control" rows="2" required placeholder="e.g. Broken screen glass, touch not responsive, battery draining quickly"></textarea>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Estimated Repair Cost (₱)</label>
                            <input type="number" step="0.01" name="estimated_cost" class="form-control" value="0.00">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Initial Deposit Paid (₱)</label>
                            <input type="number" step="0.01" name="deposit_paid" class="form-control" value="0.00">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold">Issue Job Order Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Update Status -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="job_id" id="modalJobId">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalTitle">Update Job Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Job Order Status</label>
                        <select name="status" id="modalStatusSelect" class="form-select" required>
                            <option value="scheduled">Booked – waiting for drop-off</option>
                            <option value="received">Received / In Queue</option>
                            <option value="diagnosing">Diagnosing & Testing</option>
                            <option value="waiting_parts">Waiting for Spare Parts</option>
                            <option value="repairing">Repair In Progress</option>
                            <option value="ready_for_pickup">Ready for Customer Pick-Up</option>
                            <option value="completed">Completed & Released (starts the warranty)</option>
                            <option value="unrepairable">Unrepairable / Returned</option>
                            <option value="cancelled">Cancelled (no-show / customer cancelled)</option>
                        </select>
                    </div>
                    <?php if (has_role('admin', 'staff')): ?>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Assigned technician</label>
                            <select name="technician_id" id="modalTechSelect" class="form-select">
                                <option value="">-- Unassigned --</option>
                                <?php foreach ($technicians as $t): ?>
                                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['fullname']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Diagnostic Notes & Actions Done</label>
                        <textarea name="diagnostic_notes" id="modalNotes" class="form-control" rows="3" placeholder="e.g. Replaced LCD assembly, tested touch response and TrueTone"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Final Total Bill Amount (₱)</label>
                        <input type="number" step="0.01" name="final_cost" id="modalFinalCost" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Job Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleIssueText(el) {
    if (el.classList.contains('text-truncate')) {
        el.classList.remove('text-truncate');
        el.style.whiteSpace = 'normal';
        el.style.backgroundColor = '#fff0f5';
        el.style.padding = '4px 8px';
        el.style.borderRadius = '6px';
        el.style.border = '1px solid #f472b6';
        el.title = 'Click to collapse';
    } else {
        el.classList.add('text-truncate');
        el.style.whiteSpace = '';
        el.style.backgroundColor = '';
        el.style.padding = '';
        el.style.borderRadius = '';
        el.style.border = '';
        el.title = 'Click to view full issue details';
    }
}

function openUpdateModal(job) {
    document.getElementById('modalJobId').value = job.id;
    document.getElementById('modalTitle').innerText = 'Update Ticket ' + job.job_order_no;
    document.getElementById('modalStatusSelect').value = job.status;
    document.getElementById('modalNotes').value = job.diagnostic_notes || '';
    document.getElementById('modalFinalCost').value = (parseFloat(job.final_cost) > 0) ? job.final_cost : job.estimated_cost;
    const techSel = document.getElementById('modalTechSelect');
    if (techSel) techSel.value = job.technician_id || '';
    new bootstrap.Modal(document.getElementById('updateStatusModal')).show();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
