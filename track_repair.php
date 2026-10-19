<?php
$page_title = "Track Repair Job Order";
require_once __DIR__ . '/includes/header.php';

$search_query = sanitize($_GET['ticket'] ?? '');
$job = null;
$searched = false;

if (!empty($search_query)) {
    $searched = true;
    $stmt = $pdo->prepare("SELECT r.*, t.fullname as tech_name 
                           FROM repair_job_orders r 
                           LEFT JOIN users t ON r.technician_id = t.id 
                           WHERE r.job_order_no = ? OR r.customer_contact = ? 
                           ORDER BY r.id DESC LIMIT 1");
    $stmt->execute([$search_query, $search_query]);
    $job = $stmt->fetch();
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-custom p-4 p-md-5 mb-4 shadow-sm text-center animate-fade-in-up">
                <div class="bg-dark text-white d-inline-flex p-3 rounded-circle mb-3 fs-3 mx-auto shadow-sm">
                    <img src="<?= BASE_URL ?>assets/img/icons/search.png" style="width: 28px; height: 28px; filter: invert(1);" alt="Search">
                </div>
                <h3 class="fw-bold mb-1">Track Device Repair Status</h3>
                <p class="text-muted small">Enter your Job Order Number (e.g. 8J-REP-...) or your registered mobile number</p>

                <form method="GET" action="" class="mt-3">
                    <div class="input-group input-group-lg max-w-md mx-auto" style="max-width: 500px;">
                        <input type="text" name="ticket" class="form-control text-center font-monospace" placeholder="Enter Job Order # or Mobile No." value="<?= htmlspecialchars($search_query) ?>" required>
                        <button type="submit" class="btn btn-primary px-4 fw-bold btn-tactile">Search</button>
                    </div>
                </form>
            </div>

            <?php if ($searched): ?>
                <?php if ($job): ?>
                    <div class="card card-custom p-4 p-md-5 shadow-sm animate-scale-in">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                            <div>
                                <span class="badge bg-light text-secondary border mb-1">Job Order Ticket</span>
                                <h4 class="fw-bold text-dark mb-0 font-monospace"><?= htmlspecialchars($job['job_order_no']) ?></h4>
                            </div>
                            <div class="text-end">
                                <div class="text-muted small">Device Model</div>
                                <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($job['device_brand'] . ' ' . $job['device_model']) ?></h5>
                            </div>
                        </div>

                        <!-- Progress Step Indicators -->
                        <?php
                        $steps = [
                            'scheduled' => 1,
                            'received' => 2,
                            'diagnosing' => 3,
                            'repairing' => 4,
                            'ready_for_pickup' => 5,
                            'completed' => 6
                        ];
                        $cur_step = $steps[$job['status']] ?? 2;
                        ?>
                        <div class="p-3 bg-light rounded-3 mb-4 border">
                            <div class="row text-center g-2 small">
                                <div class="col-4 col-md-2">
                                    <div class="p-2 rounded <?= $cur_step >= 1 ? 'bg-dark text-white fw-bold shadow-sm' : 'bg-white text-muted border' ?>">
                                        <i class="fa-solid fa-calendar-check d-block mb-1"></i> Booked
                                    </div>
                                </div>
                                <div class="col-4 col-md-2">
                                    <div class="p-2 rounded <?= $cur_step >= 2 ? 'bg-dark text-white fw-bold shadow-sm' : 'bg-white text-muted border' ?>">
                                        <i class="fa-solid fa-inbox d-block mb-1"></i> Received
                                    </div>
                                </div>
                                <div class="col-4 col-md-2">
                                    <div class="p-2 rounded <?= $cur_step >= 3 ? 'bg-dark text-white fw-bold shadow-sm' : 'bg-white text-muted border' ?>">
                                        <i class="fa-solid fa-stethoscope d-block mb-1"></i> Diagnosing
                                    </div>
                                </div>
                                <div class="col-4 col-md-2">
                                    <div class="p-2 rounded <?= $cur_step >= 4 ? 'bg-dark text-white fw-bold shadow-sm' : 'bg-white text-muted border' ?>">
                                        <i class="fa-solid fa-wrench d-block mb-1"></i> Repairing
                                    </div>
                                </div>
                                <div class="col-4 col-md-2">
                                    <div class="p-2 rounded <?= $cur_step >= 5 ? 'bg-dark text-white fw-bold shadow-sm' : 'bg-white text-muted border' ?>">
                                        <i class="fa-solid fa-bell d-block mb-1"></i> Ready
                                    </div>
                                </div>
                                <div class="col-4 col-md-2">
                                    <div class="p-2 rounded <?= $cur_step >= 6 ? 'bg-dark text-white fw-bold shadow-sm' : 'bg-white text-muted border' ?>">
                                        <i class="fa-solid fa-shield-halved d-block mb-1"></i> Released
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ticket Detailed Breakdown -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded border h-100">
                                    <span class="text-muted small fw-bold text-uppercase d-block mb-2">Reported Symptoms</span>
                                    <p class="mb-0 small text-dark"><?= htmlspecialchars($job['reported_issues']) ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded border h-100">
                                    <span class="text-muted small fw-bold text-uppercase d-block mb-2">Technician Notes & Resolution</span>
                                    <p class="mb-0 small text-dark"><?= htmlspecialchars($job['diagnostic_notes'] ?? 'Pending diagnostic report.') ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap justify-content-between align-items-center bg-light p-3 rounded-3 border">
                            <div>
                                <span class="text-muted small d-block">Total Service Fee / Parts</span>
                                <h4 class="fw-extrabold text-dark mb-0">
                                    <?= $job['final_cost'] > 0 ? format_currency($job['final_cost']) : format_currency($job['estimated_cost']) ?>
                                </h4>
                            </div>
                            <?php if ($job['warranty_expiry']): ?>
                                <div class="text-end">
                                    <span class="badge bg-dark text-white p-2 fs-6">
                                        <i class="fa-solid fa-shield-halved me-1"></i> Service Warranty Active
                                    </span>
                                    <div class="small text-muted mt-1">Valid until: <strong><?= date('F d, Y', strtotime($job['warranty_expiry'])) ?></strong></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning text-center py-4">
                        <i class="fa-solid fa-circle-exclamation me-1"></i>
                        No active repair job order found matching "<strong><?= htmlspecialchars($search_query) ?></strong>". Please verify your ticket reference number.
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
