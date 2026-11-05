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

    // 4. Sales Daily Trend (Timeline Graph)
    $stmt_trend = $pdo->prepare("SELECT DATE(created_at) as sale_day, COALESCE(SUM(total_amount), 0) as daily_total, COUNT(*) as daily_count 
                                FROM sales 
                                WHERE DATE(created_at) BETWEEN ? AND ? 
                                GROUP BY DATE(created_at) 
                                ORDER BY sale_day ASC");
    $stmt_trend->execute([$start_date, $end_date]);
    $sales_trend_data = $stmt_trend->fetchAll(PDO::FETCH_ASSOC);

    // 5. Payment Methods Breakdown (Pie/Doughnut Graph)
    $stmt_pm = $pdo->prepare("SELECT payment_method, COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total 
                              FROM sales 
                              WHERE DATE(created_at) BETWEEN ? AND ? 
                              GROUP BY payment_method 
                              ORDER BY total DESC");
    $stmt_pm->execute([$start_date, $end_date]);
    $payment_methods_data = $stmt_pm->fetchAll(PDO::FETCH_ASSOC);

    // 6. Top Selling Products & Phones Breakdown (Horizontal Bar Graph)
    $stmt_top = $pdo->prepare("SELECT si.item_name, si.item_type, SUM(si.quantity) as total_qty, SUM(si.subtotal) as total_sales 
                              FROM sale_items si 
                              JOIN sales s ON si.sale_id = s.id 
                              WHERE DATE(s.created_at) BETWEEN ? AND ? 
                              GROUP BY si.item_name, si.item_type 
                              ORDER BY total_sales DESC 
                              LIMIT 6");
    $stmt_top->execute([$start_date, $end_date]);
    $top_selling_data = $stmt_top->fetchAll(PDO::FETCH_ASSOC);

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

            <!-- Interactive Sales Visual Graphs Section -->
            <div class="row g-4 mb-4">
                <!-- 1. Daily Sales Trend Line Graph -->
                <div class="col-lg-8">
                    <div class="card card-custom h-100 shadow-sm">
                        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0">
                                <i class="fa-solid fa-chart-line me-2 text-primary"></i>Daily Sales Revenue Trend
                            </h6>
                            <span class="badge bg-light text-dark border">
                                <?= date('M d', strtotime($start_date)) ?> – <?= date('M d, Y', strtotime($end_date)) ?>
                            </span>
                        </div>
                        <div class="card-body p-3">
                            <div style="position: relative; height: 280px; width: 100%;">
                                <canvas id="salesTrendChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Payment Method Breakdown Doughnut Chart -->
                <div class="col-lg-4">
                    <div class="card card-custom h-100 shadow-sm">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="fw-bold mb-0">
                                <i class="fa-solid fa-chart-pie me-2 text-primary"></i>Payment Mode Share
                            </h6>
                        </div>
                        <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center">
                            <div style="position: relative; height: 210px; width: 100%;">
                                <canvas id="paymentMethodChart"></canvas>
                            </div>
                            <div class="mt-2 text-center small text-muted">
                                Cash vs GCash vs Maya / Card transactions
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Top Revenue Generating Items Bar Chart -->
                <div class="col-12">
                    <div class="card card-custom shadow-sm">
                        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0">
                                <i class="fa-solid fa-ranking-star me-2 text-primary"></i>Top Revenue Generating Products &amp; Phone Units
                            </h6>
                            <small class="text-muted">Ranked by total gross sales in selected period</small>
                        </div>
                        <div class="card-body p-3">
                            <div style="position: relative; height: 220px; width: 100%;">
                                <canvas id="topItemsChart"></canvas>
                            </div>
                        </div>
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
                                    <th class="text-center" style="width: 140px;">Receipt Actions</th>
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
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?= BASE_URL ?>print_receipt.php?invoice=<?= urlencode($s['invoice_no']) ?>&mode=thermal" target="_blank" class="btn btn-outline-dark" title="Print 80mm Thermal Slip">
                                                        <i class="fa-solid fa-receipt"></i>
                                                    </a>
                                                    <a href="<?= BASE_URL ?>print_receipt.php?invoice=<?= urlencode($s['invoice_no']) ?>&mode=a4" target="_blank" class="btn btn-outline-secondary" title="Print Full A4 Tax Invoice">
                                                        <i class="fa-solid fa-file-invoice"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No sales recorded for the selected period.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- System Audit Trail & Security Activity Log (Collapsible Section) -->
            <div class="card card-custom">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center" 
                     style="cursor: pointer; user-select: none;" 
                     data-bs-toggle="collapse" 
                     data-bs-target="#auditTrailCollapse" 
                     aria-expanded="false" 
                     aria-controls="auditTrailCollapse" 
                     id="auditHeading"
                     onclick="toggleAuditIcon()">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-chevron-right text-muted transition-transform" id="auditChevron" style="transition: transform 0.2s ease;"></i>
                        <div>
                            <h5 class="fw-bold mb-0 d-inline-flex align-items-center gap-2">
                                <i class="fa-solid fa-shield-halved text-primary"></i>
                                System Security &amp; Activity Audit Trail
                            </h5>
                            <span class="badge bg-light text-secondary border ms-2 small">Minimized</span>
                            <div class="text-muted small" style="font-size: 0.78rem;">Click this bar to expand full accountability log tracking cashier checkouts, appraisals, inventory updates, and buyouts</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-dark border">Recent <?= count($audit_logs) ?> Events</span>
                        <span class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1" style="font-size: 0.78rem;">
                            <span id="auditToggleText">Show Full Log</span>
                        </span>
                    </div>
                </div>
                
                <div class="collapse" id="auditTrailCollapse">
                    <div class="card-body p-0 border-top">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Timestamp</th>
                                        <th>User / Staff</th>
                                        <th>Module</th>
                                        <th>Action Performed</th>
                                        <th>Details &amp; Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($audit_logs)): ?>
                                        <?php foreach ($audit_logs as $log): ?>
                                            <tr>
                                                <td class="text-muted small font-monospace text-nowrap"><?= date('M d, Y H:i:s', strtotime($log['created_at'])) ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($log['fullname'] ?? 'System User') ?></strong>
                                                    <span class="badge bg-light text-secondary ms-1 small"><?= ucfirst($log['role'] ?? 'Staff') ?></span>
                                                </td>
                                                <td><span class="badge bg-secondary text-uppercase" style="font-size:0.68rem;"><?= htmlspecialchars($log['module'] ?? 'General') ?></span></td>
                                                <td class="fw-semibold text-dark"><?= htmlspecialchars($log['action']) ?></td>
                                                <td style="max-width: 320px;">
                                                    <div class="expandable-audit-detail text-truncate" 
                                                         onclick="toggleAuditDetail(this)" 
                                                         title="Click to view full detail" 
                                                         style="cursor: pointer;">
                                                        <?= htmlspecialchars($log['details'] ?? 'N/A') ?>
                                                    </div>
                                                </td>
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
            </div>
        </main>
    </div>
</div>

<!-- Chart.js Library -->
<script src="<?= BASE_URL ?>assets/js/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Primary aesthetic theme colors matching 8J soft pink / dark rose palette
    const rosePrimary = '#db2777';
    const roseLight = '#f472b6';
    const roseSoft = 'rgba(244, 114, 182, 0.2)';
    const darkRose = '#2d1122';

    // 1. Sales Trend Line Chart
    const trendData = <?= json_encode($sales_trend_data) ?>;
    const trendLabels = trendData.length > 0 ? trendData.map(d => d.sale_day) : ['No Data'];
    const trendValues = trendData.length > 0 ? trendData.map(d => parseFloat(d.daily_total)) : [0];
    const trendCounts = trendData.length > 0 ? trendData.map(d => parseInt(d.daily_count)) : [0];

    const ctxTrend = document.getElementById('salesTrendChart');
    if (ctxTrend) {
        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Gross Sales (₱)',
                    data: trendValues,
                    borderColor: rosePrimary,
                    backgroundColor: roseSoft,
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: rosePrimary,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return 'Sales: ₱' + Number(ctx.raw).toLocaleString('en-PH', {minimumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(val) {
                                return '₱' + Number(val).toLocaleString();
                            },
                            font: { size: 11 }
                        },
                        grid: { color: '#f1f5f9' }
                    },
                    x: {
                        ticks: { font: { size: 11 } },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // 2. Payment Method Doughnut Chart
    const pmData = <?= json_encode($payment_methods_data) ?>;
    const pmLabels = pmData.length > 0 ? pmData.map(d => d.payment_method.toUpperCase()) : ['No Sales'];
    const pmValues = pmData.length > 0 ? pmData.map(d => parseFloat(d.total)) : [1];

    const ctxPm = document.getElementById('paymentMethodChart');
    if (ctxPm) {
        new Chart(ctxPm, {
            type: 'doughnut',
            data: {
                labels: pmLabels,
                datasets: [{
                    data: pmValues,
                    backgroundColor: [
                        '#db2777', // Cash (Rose)
                        '#0284c7', // GCash (Sky Blue)
                        '#8b5cf6', // Maya / Card (Purple)
                        '#10b981', // Split / Other
                        '#f59e0b'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { size: 11 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ' ' + ctx.label + ': ₱' + Number(ctx.raw).toLocaleString('en-PH', {minimumFractionDigits: 2});
                            }
                        }
                    }
                },
                cutout: '68%'
            }
        });
    }

    // 3. Top Items Horizontal Bar Chart
    const topData = <?= json_encode($top_selling_data) ?>;
    const topLabels = topData.length > 0 ? topData.map(d => d.item_name.length > 32 ? d.item_name.substring(0, 30) + '...' : d.item_name) : ['No Data'];
    const topSales = topData.length > 0 ? topData.map(d => parseFloat(d.total_sales)) : [0];

    const ctxTop = document.getElementById('topItemsChart');
    if (ctxTop) {
        new Chart(ctxTop, {
            type: 'bar',
            data: {
                labels: topLabels,
                datasets: [{
                    label: 'Revenue (₱)',
                    data: topSales,
                    backgroundColor: topData.map(d => d.item_type === 'phone_unit' ? '#2d1122' : '#f472b6'),
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return 'Gross Revenue: ₱' + Number(ctx.raw).toLocaleString('en-PH', {minimumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(val) {
                                return '₱' + Number(val).toLocaleString();
                            },
                            font: { size: 10 }
                        },
                        grid: { color: '#f1f5f9' }
                    },
                    y: {
                        ticks: { font: { size: 11 } },
                        grid: { display: false }
                    }
                }
            }
        });
    }
});

function toggleAuditIcon() {
    const chevron = document.getElementById('auditChevron');
    const label = document.getElementById('auditToggleText');
    const collapseEl = document.getElementById('auditTrailCollapse');
    
    // Check if opening or closing
    setTimeout(() => {
        if (collapseEl.classList.contains('show')) {
            chevron.style.transform = 'rotate(90deg)';
            label.innerText = 'Hide Log';
        } else {
            chevron.style.transform = 'rotate(0deg)';
            label.innerText = 'Show Full Log';
        }
    }, 350);
}

function toggleAuditDetail(el) {
    if (el.classList.contains('text-truncate')) {
        el.classList.remove('text-truncate');
        el.style.whiteSpace = 'normal';
        el.style.backgroundColor = '#fdf2f8';
        el.style.padding = '4px 8px';
        el.style.borderRadius = '4px';
        el.style.border = '1px solid #f472b6';
        el.title = 'Click to minimize';
    } else {
        el.classList.add('text-truncate');
        el.style.whiteSpace = '';
        el.style.backgroundColor = '';
        el.style.padding = '';
        el.style.borderRadius = '';
        el.style.border = '';
        el.title = 'Click to view full detail';
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
