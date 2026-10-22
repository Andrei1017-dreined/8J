<?php
$page_title = "Sales & Analytics Reports";
require_once __DIR__ . '/../config/db.php';
require_role('admin');

// Date filters
$start_date = sanitize($_GET['start_date'] ?? date('Y-m-01'));
$end_date = sanitize($_GET['end_date'] ?? date('Y-m-d'));

// CSV Export Handler
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=8J_Sales_Report_' . $start_date . '_to_' . $end_date . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Invoice No', 'Customer Name', 'Payment Method', 'Subtotal (PHP)', 'Discount (PHP)', 'Total Amount (PHP)', 'Date & Time']);
    $stmt = $pdo->prepare("SELECT invoice_no, customer_name, payment_method, subtotal, discount_amount, total_amount, created_at FROM sales WHERE DATE(created_at) BETWEEN ? AND ? ORDER BY id DESC");
    $stmt->execute([$start_date, $end_date]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['invoice_no'],
            $row['customer_name'],
            $row['payment_method'],
            number_format($row['subtotal'], 2),
            number_format($row['discount_amount'], 2),
            number_format($row['total_amount'], 2),
            $row['created_at']
        ]);
    }
    fclose($output);
    exit;
}

// Summary Metrics
$sales_total = 0;
$sales_count = 0;
$repair_revenue = 0;
$buyout_disbursed = 0;
$sales_list = [];
$audit_logs = [];

try {
    // 1. Sales revenue
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0), COUNT(*) FROM sales WHERE DATE(created_at) BETWEEN ? AND ?");
    $stmt->execute([$start_date, $end_date]);
    [$sales_total, $sales_count] = $stmt->fetch(PDO::FETCH_NUM);

    // 2. Repair revenue
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(final_cost), 0) FROM repair_job_orders WHERE status = 'completed' AND DATE(completed_at) BETWEEN ? AND ?");
    $stmt->execute([$start_date, $end_date]);
    $repair_revenue = (float)$stmt->fetchColumn();

    // 3. Buyouts disbursed
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount_paid), 0) FROM unit_buyouts WHERE DATE(created_at) BETWEEN ? AND ?");
    $stmt->execute([$start_date, $end_date]);
    $buyout_disbursed = (float)$stmt->fetchColumn();

    // Sales items list
    $stmt = $pdo->prepare("SELECT * FROM sales WHERE DATE(created_at) BETWEEN ? AND ? ORDER BY id DESC");
    $stmt->execute([$start_date, $end_date]);
    $sales_list = $stmt->fetchAll();

    // Audit logs
    $stmt = $pdo->query("SELECT a.*, u.fullname, u.role FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.id DESC LIMIT 50");
    $audit_logs = $stmt->fetchAll();

} catch (Exception $e) {
    // Handled
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h2 class="h3 fw-bold mb-1"><i class="fa-solid fa-chart-line text-primary me-2"></i>Sales & Financial Analytics</h2>
                    <p class="text-muted small mb-0">Auditing and revenue reports for retail sales, phone trades, buyouts, and repair service revenues</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="?export=csv&start_date=<?= $start_date ?>&end_date=<?= $end_date ?>" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                        <i class="fa-solid fa-file-csv me-1 text-success"></i> Export to CSV
                    </a>
                    <button class="btn btn-outline-dark btn-sm rounded-pill px-3" onclick="window.print()">
                        <i class="fa-solid fa-print me-1"></i> Print / Save PDF
                    </button>
                </div>
            </div>

            <!-- Date Filter -->
            <div class="card card-custom p-3 mb-4">
                <form method="GET" action="" class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Start Date</label>
                        <input type="date" name="start_date" class="form-control form-control-sm" value="<?= $start_date ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">End Date</label>
                        <input type="date" name="end_date" class="form-control form-control-sm" value="<?= $end_date ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 w-100">
                            <i class="fa-solid fa-filter me-1"></i> Apply Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Financial Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card card-custom p-4 border stat-card">
                        <span class="text-muted small text-uppercase fw-semibold">Gross POS Retail Sales</span>
                        <h3 class="fw-extrabold text-dark mb-0 font-monospace"><?= format_currency($sales_total) ?></h3>
                        <small class="text-muted"><?= $sales_count ?> sales receipts recorded</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-custom p-4 border stat-card">
                        <span class="text-muted small text-uppercase fw-semibold">Service Repair Revenues</span>
                        <h3 class="fw-extrabold text-dark mb-0 font-monospace"><?= format_currency($repair_revenue) ?></h3>
                        <small class="text-muted">From completed device repair tickets</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-custom p-4 border stat-card">
                        <span class="text-muted small text-uppercase fw-semibold">Phone Buyout Payouts</span>
                        <h3 class="fw-extrabold text-dark mb-0 font-monospace"><?= format_currency($buyout_disbursed) ?></h3>
                        <small class="text-muted">Capital disbursed for customer units</small>
                    </div>
                </div>
            </div>

            <!-- Detailed Sales Table -->
            <div class="card card-custom mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Sales Transactions Breakdown</h5>
                    <span class="badge bg-light text-dark border"><?= count($sales_list) ?> Records</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Customer Name</th>
                                    <th>Payment Mode</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-end">Discount</th>
                                    <th class="text-end">Total Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($sales_list)): ?>
                                    <?php foreach ($sales_list as $s): ?>
                                        <tr>
                                            <td class="fw-bold text-dark font-monospace"><?= htmlspecialchars($s['invoice_no']) ?></td>
                                            <td><?= htmlspecialchars($s['customer_name']) ?></td>
                                            <td><span class="badge bg-light text-dark border text-uppercase"><?= $s['payment_method'] ?></span></td>
                                            <td class="text-end text-muted"><?= format_currency($s['subtotal']) ?></td>
                                            <td class="text-end text-muted"><?= format_currency($s['discount_amount']) ?></td>
                                            <td class="text-end fw-extrabold text-dark"><?= format_currency($s['total_amount']) ?></td>
                                            <td class="text-muted small"><?= date('M d, Y H:i', strtotime($s['created_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No sales recorded for the selected period.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- System Audit Trail & Security Activity Log -->
            <div class="card card-custom">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0"><i class="fa-solid fa-shield-halved text-primary me-2"></i>System Security & Activity Audit Trail</h5>
                        <small class="text-muted">Accountability log tracking cashier checkouts, appraisals, inventory updates, and buyouts</small>
                    </div>
                    <span class="badge bg-light text-dark border">Recent <?= count($audit_logs) ?> Events</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Timestamp</th>
                                    <th>User / Staff</th>
                                    <th>Module</th>
                                    <th>Action Performed</th>
                                    <th>Details & Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($audit_logs)): ?>
                                    <?php foreach ($audit_logs as $log): ?>
                                        <tr>
                                            <td class="text-muted small font-monospace"><?= date('M d, Y H:i:s', strtotime($log['created_at'])) ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($log['fullname'] ?? 'System User') ?></strong>
                                                <span class="badge bg-light text-secondary ms-1 small"><?= ucfirst($log['role'] ?? 'Staff') ?></span>
                                            </td>
                                            <td><span class="badge bg-secondary text-uppercase" style="font-size:0.68rem;"><?= htmlspecialchars($log['module'] ?? 'General') ?></span></td>
                                            <td class="fw-semibold text-dark"><?= htmlspecialchars($log['action']) ?></td>
                                            <td class="text-muted small"><?= htmlspecialchars($log['details'] ?? 'N/A') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No security events logged yet. (All buyouts and trades automatically record here).</td>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
