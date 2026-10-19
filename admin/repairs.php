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
    $estimated_cost = (float)($_POST['estimated_cost'] ?? 0);
    $deposit_paid = (float)($_POST['deposit_paid'] ?? 0);
    $technician_id = !empty($_POST['technician_id']) ? (int)$_POST['technician_id'] : null;

    $job_order_no = '8J-REP-' . date('ym') . '-' . strtoupper(bin2hex(random_bytes(2)));

    try {
        $stmt = $pdo->prepare("INSERT INTO repair_job_orders 
            (job_order_no, customer_name, customer_contact, device_brand, device_model, device_imei_or_serial, passcode_or_pattern, reported_issues, estimated_cost, deposit_paid, technician_id, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'received')");
        
        $stmt->execute([
            $job_order_no, $customer_name, $customer_contact, $device_brand, $device_model,
            $imei, $passcode, $issues, $estimated_cost, $deposit_paid, $technician_id
        ]);
        $message = "Repair Job Order {$job_order_no} created successfully!";
    } catch (Exception $e) {
        $message = "Error creating job order: " . $e->getMessage();
    }
}

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $job_id = (int)($_POST['job_id'] ?? 0);
    $new_status = sanitize($_POST['status'] ?? '');
    $diagnostic_notes = sanitize($_POST['diagnostic_notes'] ?? '');
    $final_cost = (float)($_POST['final_cost'] ?? 0);

    try {
        $warranty_expiry = ($new_status === 'completed') ? date('Y-m-d', strtotime('+30 days')) : null;
        $completed_at = ($new_status === 'completed') ? date('Y-m-d H:i:s') : null;

        $stmt = $pdo->prepare("UPDATE repair_job_orders SET 
            status = ?, diagnostic_notes = ?, final_cost = ?, warranty_expiry = ?, completed_at = ?
            WHERE id = ?");
        $stmt->execute([$new_status, $diagnostic_notes, $final_cost, $warranty_expiry, $completed_at, $job_id]);
        $message = "Job Order #{$job_id} updated to status: " . ucwords(str_replace('_', ' ', $new_status));
    } catch (Exception $e) {
        $message = "Update failed: " . $e->getMessage();
    }
}

// Fetch technicians
$technicians = $pdo->query("SELECT id, fullname FROM users WHERE role IN ('technician', 'admin')")->fetchAll();

// Fetch job orders
$status_filter = sanitize($_GET['status'] ?? '');
$sql = "SELECT r.*, t.fullname as tech_name FROM repair_job_orders r LEFT JOIN users t ON r.technician_id = t.id";
if (!empty($status_filter)) {
    $sql .= " WHERE r.status = " . $pdo->quote($status_filter);
}
$sql .= " ORDER BY r.id DESC";
$job_orders = $pdo->query($sql)->fetchAll();

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
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Status Filter Pills -->
            <div class="d-flex flex-wrap gap-2 mb-4">
                <a href="<?= BASE_URL ?>admin/repairs.php" class="btn btn-sm <?= empty($status_filter) ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill">All</a>
                <a href="<?= BASE_URL ?>admin/repairs.php?status=received" class="btn btn-sm <?= $status_filter == 'received' ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill">Received</a>
                <a href="<?= BASE_URL ?>admin/repairs.php?status=diagnosing" class="btn btn-sm <?= $status_filter == 'diagnosing' ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill">Diagnosing</a>
                <a href="<?= BASE_URL ?>admin/repairs.php?status=waiting_parts" class="btn btn-sm <?= $status_filter == 'waiting_parts' ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill">Waiting Parts</a>
                <a href="<?= BASE_URL ?>admin/repairs.php?status=repairing" class="btn btn-sm <?= $status_filter == 'repairing' ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill">Repairing</a>
                <a href="<?= BASE_URL ?>admin/repairs.php?status=ready_for_pickup" class="btn btn-sm <?= $status_filter == 'ready_for_pickup' ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill">Ready for Pick-Up</a>
                <a href="<?= BASE_URL ?>admin/repairs.php?status=completed" class="btn btn-sm <?= $status_filter == 'completed' ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill">Completed</a>
            </div>

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
                                            <td style="max-width: 180px;" class="text-truncate" title="<?= htmlspecialchars($job['reported_issues']) ?>">
                                                <?= htmlspecialchars($job['reported_issues']) ?>
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
                            <option value="received">Received / In Queue</option>
                            <option value="diagnosing">Diagnosing & Testing</option>
                            <option value="waiting_parts">Waiting for Spare Parts</option>
                            <option value="repairing">Repair In Progress</option>
                            <option value="ready_for_pickup">Ready for Customer Pick-Up</option>
                            <option value="completed">Completed & Released (Applies 30-day Warranty)</option>
                            <option value="unrepairable">Unrepairable / Returned</option>
                        </select>
                    </div>
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
function openUpdateModal(job) {
    document.getElementById('modalJobId').value = job.id;
    document.getElementById('modalTitle').innerText = 'Update Ticket ' + job.job_order_no;
    document.getElementById('modalStatusSelect').value = job.status;
    document.getElementById('modalNotes').value = job.diagnostic_notes || '';
    document.getElementById('modalFinalCost').value = (job.final_cost > 0) ? job.final_cost : job.estimated_cost;
    new bootstrap.Modal(document.getElementById('updateStatusModal')).show();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
