<?php
$page_title = "Sales & Analytics Reports";
require_once __DIR__ . '/../config/db.php';
require_role('admin');

// Date filters
$start_date = sanitize($_GET['start_date'] ?? date('Y-m-01'));
$end_date = sanitize($_GET['end_date'] ?? date('Y-m-d'));

// Summary Metrics
$sales_total = 0;
$sales_count = 0;
$repair_revenue = 0;
$buyout_disbursed = 0;

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
                <div>
                    <button class="btn btn-outline-dark btn-sm rounded-pill px-3" onclick="window.print()">
                        <i class="fa-solid fa-print me-1"></i> Print / Export Report
                    </button>
                </div>
            </div>

            <!-- Date Filter Filter -->
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
            <div class="card card-custom">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="fw-bold mb-0">Sales Transactions Breakdown</h5>
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
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
