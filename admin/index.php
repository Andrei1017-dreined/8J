<?php
$page_title = "Admin Dashboard";
require_once __DIR__ . '/../config/db.php';
require_role('admin', 'staff', 'technician');

// Gather Dashboard Metrics
$stats = [
    'today_sales' => 0.00,
    'units_in_stock' => 0,
    'low_stock_products' => 0,
    'active_repairs' => 0,
    'pending_pickups' => 0,
    'total_trades' => 0
];

try {
    // 1. Today's sales
    $stmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM sales WHERE DATE(created_at) = CURDATE()");
    $stats['today_sales'] = (float)$stmt->fetchColumn();

    // 2. Units in stock
    $stmt = $pdo->query("SELECT COUNT(*) FROM phone_units WHERE status = 'in_stock'");
    $stats['units_in_stock'] = (int)$stmt->fetchColumn();

    // 3. Low stock accessories
    $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= reorder_level AND status != 'archived'");
    $stats['low_stock_products'] = (int)$stmt->fetchColumn();

    // 4. Active repairs
    $stmt = $pdo->query("SELECT COUNT(*) FROM repair_job_orders WHERE status IN ('scheduled', 'received', 'diagnosing', 'waiting_parts', 'repairing')");
    $stats['active_repairs'] = (int)$stmt->fetchColumn();

    // 5. Pending pickups
    $stmt = $pdo->query("SELECT COUNT(*) FROM online_orders WHERE pickup_status IN ('pending_confirmation', 'ready_for_pickup')");
    $stats['pending_pickups'] = (int)$stmt->fetchColumn();

    // 6. Total Trades / Swaps
    $stmt = $pdo->query("SELECT COUNT(*) FROM trading_transactions WHERE status = 'completed'");
    $stats['total_trades'] = (int)$stmt->fetchColumn();

    // Recent Sales
    $recent_sales = $pdo->query("SELECT * FROM sales ORDER BY id DESC LIMIT 5")->fetchAll();

    // Recent Repairs
    $recent_repairs = $pdo->query("SELECT * FROM repair_job_orders ORDER BY id DESC LIMIT 5")->fetchAll();

} catch (Exception $e) {
    // Graceful error display
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="row">
        <!-- Sidebar Navigation -->
        <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

        <!-- Main Content Area -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-3 border-bottom">
                <div>
                    <h2 class="h3 fw-bold mb-1">Operations Overview</h2>
                    <p class="text-muted small mb-0">Welcome back, <?= htmlspecialchars($current_user['fullname']) ?> (<?= ucfirst($current_user['role']) ?>)</p>
                </div>
                <div class="btn-toolbar mb-2 mb-md-0 gap-2">
                    <a href="<?= BASE_URL ?>admin/pos.php" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                        <i class="fa-solid fa-cash-register me-1"></i> Open POS Cashier
                    </a>
                    <a href="<?= BASE_URL ?>admin/appraisal.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                        <i class="fa-solid fa-calculator me-1"></i> New Phone Appraisal
                    </a>
                </div>
            </div>

            <!-- Top Metric Cards -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="card card-custom stat-card p-3 border-start border-primary border-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small fw-semibold text-uppercase">Today's Sales</div>
                                <h4 class="fw-bolder mb-0 text-primary"><?= format_currency($stats['today_sales']) ?></h4>
                            </div>
                            <div class="bg-primary-subtle text-primary p-3 rounded-circle fs-4">
                                <i class="fa-solid fa-coins"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card card-custom stat-card p-3 border-start border-success border-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small fw-semibold text-uppercase">Phone Units In Stock</div>
                                <h4 class="fw-bolder mb-0 text-success"><?= $stats['units_in_stock'] ?> Units</h4>
                            </div>
                            <div class="bg-success-subtle text-success p-3 rounded-circle fs-4">
                                <i class="fa-solid fa-mobile-screen"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card card-custom stat-card p-3 border-start border-warning border-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small fw-semibold text-uppercase">Active Repairs</div>
                                <h4 class="fw-bolder mb-0 text-warning"><?= $stats['active_repairs'] ?> In-Progress</h4>
                            </div>
                            <div class="bg-warning-subtle text-warning p-3 rounded-circle fs-4">
                                <i class="fa-solid fa-screwdriver-wrench"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card card-custom stat-card p-3 border-start border-info border-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small fw-semibold text-uppercase">Store Pick-Ups Waiting</div>
                                <h4 class="fw-bolder mb-0 text-info"><?= $stats['pending_pickups'] ?> Orders</h4>
                            </div>
                            <div class="bg-info-subtle text-info p-3 rounded-circle fs-4">
                                <i class="fa-solid fa-store"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two Column Table Views -->
            <div class="row g-4">
                <!-- Recent POS Sales -->
                <div class="col-lg-6">
                    <div class="card card-custom h-100">
                        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0"><i class="fa-solid fa-receipt me-2 text-primary"></i>Recent Sales Invoices</h6>
                            <a href="<?= BASE_URL ?>admin/reports.php" class="btn btn-sm btn-link text-decoration-none">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Invoice #</th>
                                            <th>Customer</th>
                                            <th>Payment</th>
                                            <th class="text-end">Total</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($recent_sales)): ?>
                                            <?php foreach ($recent_sales as $sale): ?>
                                                <tr>
                                                    <td class="fw-bold text-primary"><?= htmlspecialchars($sale['invoice_no']) ?></td>
                                                    <td><?= htmlspecialchars($sale['customer_name']) ?></td>
                                                    <td><span class="badge bg-secondary text-uppercase" style="font-size:0.68rem;"><?= $sale['payment_method'] ?></span></td>
                                                    <td class="text-end fw-bold"><?= format_currency($sale['total_amount']) ?></td>
                                                    <td class="text-muted small"><?= date('M d, H:i', strtotime($sale['created_at'])) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">No sales records yet today.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Repair Orders -->
                <div class="col-lg-6">
                    <div class="card card-custom h-100">
                        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0"><i class="fa-solid fa-wrench me-2 text-warning"></i>Recent Repair Job Orders</h6>
                            <a href="<?= BASE_URL ?>admin/repairs.php" class="btn btn-sm btn-link text-decoration-none">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Job Order #</th>
                                            <th>Device</th>
                                            <th>Reported Issue</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($recent_repairs)): ?>
                                            <?php foreach ($recent_repairs as $rep): ?>
                                                <tr>
                                                    <td class="fw-bold text-dark"><?= htmlspecialchars($rep['job_order_no']) ?></td>
                                                    <td><?= htmlspecialchars($rep['device_brand'] . ' ' . $rep['device_model']) ?></td>
                                                    <td class="text-truncate" style="max-width: 140px;"><?= htmlspecialchars($rep['reported_issues']) ?></td>
                                                    <td>
                                                        <span class="badge bg-primary-subtle text-primary border">
                                                            <?= ucwords(str_replace('_', ' ', $rep['status'])) ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted">No repair tickets in queue.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
