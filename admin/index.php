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
$kpi = ['month_sales' => 0, 'month_repairs' => 0, 'inventory_value' => 0, 'ready_repairs' => 0, 'overdue_pickups' => 0, 'warranty_expiring' => 0];
$trend = [];
$low_stock_list = [];
$top_items = [];
$recent_sales = [];
$recent_repairs = [];

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

    // --- Extended admin KPIs ---
    $kpi['month_sales'] = (float)$pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM sales WHERE YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE())")->fetchColumn();
    $kpi['month_repairs'] = (float)$pdo->query("SELECT COALESCE(SUM(final_cost),0) FROM repair_job_orders WHERE status='completed' AND YEAR(completed_at)=YEAR(CURDATE()) AND MONTH(completed_at)=MONTH(CURDATE())")->fetchColumn();
    $kpi['inventory_value'] = (float)$pdo->query("SELECT COALESCE((SELECT SUM(buy_price) FROM phone_units WHERE status='in_stock'),0) + COALESCE((SELECT SUM(cost_price*stock_quantity) FROM products WHERE status!='archived'),0)")->fetchColumn();
    $kpi['ready_repairs'] = (int)$pdo->query("SELECT COUNT(*) FROM repair_job_orders WHERE status='ready_for_pickup'")->fetchColumn();
    $kpi['overdue_pickups'] = (int)$pdo->query("SELECT COUNT(*) FROM online_orders WHERE pickup_status IN ('pending_confirmation','ready_for_pickup') AND pickup_target_date < CURDATE()")->fetchColumn();
    $kpi['warranty_expiring'] = (int)$pdo->query("SELECT COUNT(*) FROM repair_job_orders WHERE status='completed' AND warranty_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)")->fetchColumn();

    // Today's to-do counts (each links to the exact filtered list)
    $todo = $pdo->query("SELECT
        (SELECT COUNT(*) FROM online_orders WHERE pickup_status = 'pending_confirmation') AS pick_prep,
        (SELECT COUNT(*) FROM online_orders WHERE pickup_status = 'ready_for_pickup') AS pick_ready,
        (SELECT COUNT(*) FROM repair_job_orders WHERE status = 'scheduled') AS rep_booked,
        (SELECT COUNT(*) FROM repair_job_orders WHERE status = 'scheduled' AND scheduled_date = CURDATE()) AS rep_booked_today,
        (SELECT COUNT(*) FROM repair_job_orders WHERE status = 'ready_for_pickup') AS rep_ready,
        (SELECT COUNT(*) FROM repair_job_orders WHERE technician_id IS NULL AND status IN ('received','diagnosing')) AS rep_unassigned,
        (SELECT COUNT(*) FROM appraisal_records WHERE status = 'pending') AS appraisals_open")->fetch();

    // Technician's own queue
    $my_jobs = [];
    if (has_role('technician')) {
        $mj = $pdo->prepare("SELECT * FROM repair_job_orders WHERE technician_id = ? AND status IN ('received','diagnosing','waiting_parts','repairing') ORDER BY FIELD(status,'repairing','diagnosing','received','waiting_parts'), id LIMIT 15");
        $mj->execute([(int)$current_user['id']]);
        $my_jobs = $mj->fetchAll();
    }

    // 7-day sales trend (fill missing days with zero)
    $trend_raw = $pdo->query("SELECT DATE(created_at) d, SUM(total_amount) t FROM sales WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(created_at)")->fetchAll(PDO::FETCH_KEY_PAIR);
    for ($i = 6; $i >= 0; $i--) {
        $day = date('Y-m-d', strtotime("-$i day"));
        $trend[$day] = (float)($trend_raw[$day] ?? 0);
    }

    // Low-stock alert list
    $low_stock_list = $pdo->query("SELECT name, sku, stock_quantity, reorder_level FROM products WHERE stock_quantity <= reorder_level AND status != 'archived' ORDER BY stock_quantity ASC, name LIMIT 6")->fetchAll();

    // Top sellers (last 30 days)
    $top_items = $pdo->query("SELECT si.item_name, SUM(si.quantity) qty, SUM(si.subtotal) revenue FROM sale_items si JOIN sales s ON s.id = si.sale_id WHERE s.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY si.item_name ORDER BY qty DESC, revenue DESC LIMIT 5")->fetchAll();

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
            <?php
            $hour = (int)date('G');
            $greet = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
            $first_name = explode(' ', trim($current_user['fullname'] ?? ''))[0];
            $todo = $todo ?? [];
            $is_tech_view = has_role('technician');
            ?>
            <style>
                .todo-card { display: flex; gap: 12px; align-items: center; padding: 14px 16px; border-radius: 16px; background: #fff; border: 1px solid #fce7f3; text-decoration: none; color: #2b1120; height: 100%; transition: transform .15s, box-shadow .15s, border-color .15s; }
                .todo-card:hover { transform: translateY(-2px); box-shadow: 0 12px 26px -18px rgba(131,24,67,.45); border-color: #f9a8d4; color: #2b1120; }
                .todo-card .n { font-size: 1.6rem; font-weight: 800; line-height: 1; min-width: 34px; }
                .todo-card .t { font-weight: 700; font-size: .88rem; }
                .todo-card .s { font-size: .74rem; color: #6b5563; }
                .todo-card.hot { border-color: #f9a8d4; background: #fff5f9; }
                .todo-card.hot .n { color: #db2777; }
                .todo-card.calm .n { color: #a1a1aa; }
                .quick-act { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; padding: 14px 8px; border-radius: 16px; border: 1px dashed #f9a8d4; background: rgba(255,255,255,.7); text-decoration: none; color: #831843; font-weight: 700; font-size: .8rem; text-align: center; height: 100%; transition: background .15s; }
                .quick-act i { font-size: 1.2rem; color: #db2777; }
                .quick-act:hover { background: #fff; color: #831843; border-style: solid; }
            </style>

            <div class="d-flex justify-content-between flex-wrap align-items-center gap-2 pt-2 pb-3 mb-3 border-bottom">
                <div>
                    <h2 class="h3 fw-bold mb-1"><?= $greet ?>, <?= htmlspecialchars($first_name ?: 'there') ?></h2>
                    <p class="text-muted small mb-0"><?= date('l, F j') ?> · signed in as <?= ucfirst($current_user['role']) ?></p>
                </div>
            </div>

            <!-- Today's to-do: what needs doing, each card opens the exact list -->
            <h6 class="fw-bold text-uppercase text-muted mb-2" style="font-size:.72rem; letter-spacing:.08em;">To do now</h6>
            <div class="row g-3 mb-4">
                <?php
                $cards = [];
                if ($is_tech_view) {
                    $cards[] = [count($my_jobs), 'My repair jobs', 'Assigned to you and not finished', 'repairs.php?mine=1&status=active', 'fa-screwdriver-wrench'];
                    $cards[] = [(int)($todo['rep_unassigned'] ?? 0), 'Unassigned repairs', 'Received but no technician yet', 'repairs.php?status=received', 'fa-user-plus'];
                    $cards[] = [(int)($todo['rep_booked'] ?? 0), 'Booked online', ($todo['rep_booked_today'] ?? 0) . ' coming in today', 'repairs.php?status=scheduled', 'fa-calendar-check'];
                } else {
                    $cards[] = [(int)($todo['pick_prep'] ?? 0), 'Pick-ups to prepare', 'New online reservations', 'pickups.php?view=open', 'fa-bag-shopping'];
                    $cards[] = [(int)($todo['pick_ready'] ?? 0), 'Waiting for customer', 'Prepared, ready to release', 'pickups.php?view=open', 'fa-bell'];
                    $cards[] = [(int)($todo['rep_booked'] ?? 0), 'Repairs booked online', ($todo['rep_booked_today'] ?? 0) . ' coming in today', 'repairs.php?status=scheduled', 'fa-calendar-check'];
                    $cards[] = [(int)($todo['rep_ready'] ?? 0), 'Repairs ready to release', 'Customer can collect', 'repairs.php?status=ready_for_pickup', 'fa-circle-check'];
                }
                foreach ($cards as [$n, $title, $sub, $href, $icon]): ?>
                    <div class="col-6 col-xl-3">
                        <a class="todo-card <?= $n > 0 ? 'hot' : 'calm' ?>" href="<?= BASE_URL ?>admin/<?= $href ?>">
                            <span class="n"><?= $n ?></span>
                            <span>
                                <span class="t d-block"><i class="fa-solid <?= $icon ?> me-1 opacity-75"></i><?= $title ?></span>
                                <span class="s d-block"><?= $n > 0 ? htmlspecialchars($sub) : 'All clear' ?></span>
                            </span>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Quick actions -->
            <h6 class="fw-bold text-uppercase text-muted mb-2" style="font-size:.72rem; letter-spacing:.08em;">Quick actions</h6>
            <div class="row g-2 mb-4">
                <?php
                $actions = $is_tech_view
                    ? [['repairs.php?open=newJobModal', 'fa-plus', 'New repair job'], ['appraisal.php', 'fa-magnifying-glass-dollar', 'Appraise a phone'], ['help.php', 'fa-circle-question', 'How-to guides']]
                    : [['pos.php', 'fa-cash-register', 'New sale'], ['repairs.php?open=newJobModal', 'fa-screwdriver-wrench', 'New repair job'], ['appraisal.php', 'fa-magnifying-glass-dollar', 'Appraise / buy a phone'],
                       ['inventory.php?open=addPhoneModal', 'fa-mobile-screen', 'Add phone to stock'], ['inventory.php?open=addAccessoryModal', 'fa-box-open', 'Add accessory'], ['help.php', 'fa-circle-question', 'How-to guides']];
                foreach ($actions as [$href, $icon, $label]): ?>
                    <div class="col-4 col-md-2">
                        <a class="quick-act" href="<?= BASE_URL ?>admin/<?= $href ?>"><i class="fa-solid <?= $icon ?>"></i><?= $label ?></a>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($is_tech_view): ?>
                <!-- Technician: their own queue first -->
                <div class="card card-custom mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0"><i class="fa-solid fa-screwdriver-wrench me-2 text-primary"></i>My repair queue</h6>
                        <a href="<?= BASE_URL ?>admin/repairs.php?mine=1&status=active" class="btn btn-sm btn-link text-decoration-none">Open in Repairs</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size:.88rem;">
                            <thead class="table-light"><tr><th>Job #</th><th>Device</th><th>Problem</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php if (!$my_jobs): ?>
                                    <tr><td colspan="4" class="text-center text-muted py-4">Nothing assigned to you right now. Check <a href="<?= BASE_URL ?>admin/repairs.php?status=received">unassigned repairs</a>.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($my_jobs as $j): ?>
                                    <tr>
                                        <td class="fw-bold font-monospace"><?= htmlspecialchars($j['job_order_no']) ?></td>
                                        <td><?= htmlspecialchars($j['device_brand'] . ' ' . $j['device_model']) ?></td>
                                        <td class="text-truncate" style="max-width:260px;"><?= htmlspecialchars($j['reported_issues']) ?></td>
                                        <td><span class="badge bg-primary-subtle text-primary border"><?= ucwords(str_replace('_', ' ', $j['status'])) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <h6 class="fw-bold text-uppercase text-muted mb-2" style="font-size:.72rem; letter-spacing:.08em;">At a glance</h6>

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

            <?php if (has_role('admin') || has_role('staff')): ?>
            <!-- Business KPIs -->
            <div class="row g-3 mt-1 mb-2">
                <div class="col-sm-6 col-xl-3">
                    <div class="card card-custom p-3">
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size:0.68rem;">Sales This Month</div>
                        <div class="h5 fw-bolder mb-0"><?= format_currency($kpi['month_sales']) ?></div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card card-custom p-3">
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size:0.68rem;">Repair Revenue This Month</div>
                        <div class="h5 fw-bolder mb-0"><?= format_currency($kpi['month_repairs']) ?></div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card card-custom p-3">
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size:0.68rem;">Inventory Value (at cost)</div>
                        <div class="h5 fw-bolder mb-0"><?= format_currency($kpi['inventory_value']) ?></div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card card-custom p-3 <?= ($kpi['overdue_pickups'] + $kpi['ready_repairs'] + $kpi['warranty_expiring']) ? 'border border-warning' : '' ?>">
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size:0.68rem;">Needs Attention</div>
                        <div class="small mt-1">
                            <a href="<?= BASE_URL ?>admin/pickups.php?view=open" class="text-decoration-none text-dark d-block"><i class="fa-solid fa-clock text-danger me-1"></i><strong><?= $kpi['overdue_pickups'] ?></strong> overdue pick-ups</a>
                            <a href="<?= BASE_URL ?>admin/repairs.php?status=ready_for_pickup" class="text-decoration-none text-dark d-block"><i class="fa-solid fa-box text-info me-1"></i><strong><?= $kpi['ready_repairs'] ?></strong> repairs awaiting claim</a>
                            <a href="<?= BASE_URL ?>admin/repairs.php?status=completed" class="text-decoration-none text-dark d-block"><i class="fa-solid fa-shield-halved text-warning me-1"></i><strong><?= $kpi['warranty_expiring'] ?></strong> warranties expiring (7d)</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trend, Low stock, Top sellers -->
            <div class="row g-4 mb-4">
                <div class="col-lg-5">
                    <div class="card card-custom h-100">
                        <div class="card-header bg-white py-3"><h6 class="fw-bold mb-0"><i class="fa-solid fa-chart-column me-2 text-primary"></i>Sales – Last 7 Days</h6></div>
                        <div class="card-body">
                            <?php $trend_max = max(1, max($trend ?: [0])); ?>
                            <div class="d-flex align-items-end justify-content-between gap-2" style="height: 160px;">
                                <?php foreach ($trend as $day => $amt): $h = max(3, round(($amt / $trend_max) * 100)); ?>
                                    <div class="d-flex flex-column align-items-center justify-content-end flex-fill h-100" title="<?= date('M d', strtotime($day)) ?>: <?= format_currency($amt) ?>">
                                        <div class="small text-muted" style="font-size:0.62rem;"><?= $amt > 0 ? '₱' . number_format($amt) : '' ?></div>
                                        <div class="w-100 rounded-top" style="height: <?= $h ?>%; background: <?= $day === date('Y-m-d') ? '#db2777' : '#f9a8d4' ?>;"></div>
                                        <div class="small text-muted mt-1" style="font-size:0.68rem;"><?= date('D', strtotime($day)) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card card-custom h-100">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0"><i class="fa-solid fa-triangle-exclamation me-2 text-warning"></i>Low Stock Alerts</h6>
                            <a href="<?= BASE_URL ?>admin/inventory.php" class="btn btn-sm btn-link text-decoration-none">Manage</a>
                        </div>
                        <ul class="list-group list-group-flush" style="font-size:0.85rem;">
                            <?php if (!$low_stock_list): ?>
                                <li class="list-group-item text-muted text-center py-4">All accessories are well stocked.</li>
                            <?php endif; ?>
                            <?php foreach ($low_stock_list as $ls): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="text-truncate me-2"><div class="fw-semibold text-truncate"><?= htmlspecialchars($ls['name']) ?></div><small class="text-muted"><?= htmlspecialchars($ls['sku']) ?></small></div>
                                    <span class="badge <?= $ls['stock_quantity'] == 0 ? 'bg-danger' : 'bg-warning text-dark' ?>"><?= (int)$ls['stock_quantity'] ?> left</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card card-custom h-100">
                        <div class="card-header bg-white py-3"><h6 class="fw-bold mb-0"><i class="fa-solid fa-ranking-star me-2 text-success"></i>Top Sellers (30d)</h6></div>
                        <ul class="list-group list-group-flush" style="font-size:0.85rem;">
                            <?php if (!$top_items): ?>
                                <li class="list-group-item text-muted text-center py-4">No sales data yet.</li>
                            <?php endif; ?>
                            <?php foreach ($top_items as $i => $ti): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="text-truncate me-2"><span class="text-muted me-1">#<?= $i + 1 ?></span><?= htmlspecialchars($ti['item_name']) ?></div>
                                    <span class="badge bg-light text-dark border"><?= (int)$ti['qty'] ?> sold</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Two Column Table Views -->
            <div class="row g-4">
                <?php if (!$is_tech_view): ?>
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
                                            <th class="text-center" style="width: 80px;">Action</th>
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
                                                    <td class="text-center">
                                                        <a href="<?= BASE_URL ?>print_receipt.php?invoice=<?= urlencode($sale['invoice_no']) ?>&mode=thermal" target="_blank" class="btn btn-outline-dark btn-sm p-1 px-2" title="Print Receipt">
                                                            <i class="fa-solid fa-receipt"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-muted">No sales records yet today.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <?php endif; ?>
                <!-- Recent Repair Orders -->
                <div class="<?= $is_tech_view ? 'col-12' : 'col-lg-6' ?>">
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
                                                        <?php
                                                        $rep_badge = [
                                                            'scheduled' => 'bg-info-subtle text-info', 'received' => 'bg-secondary-subtle text-secondary',
                                                            'diagnosing' => 'bg-warning-subtle text-warning', 'waiting_parts' => 'bg-danger-subtle text-danger',
                                                            'repairing' => 'bg-primary-subtle text-primary', 'ready_for_pickup' => 'bg-success-subtle text-success',
                                                            'completed' => 'bg-success text-white', 'unrepairable' => 'bg-dark-subtle text-dark', 'cancelled' => 'bg-light text-muted',
                                                        ][$rep['status']] ?? 'bg-light text-dark';
                                                        $rep_label = ['scheduled' => 'Booked', 'completed' => 'Released', 'ready_for_pickup' => 'Ready for pick-up'][$rep['status']] ?? ucwords(str_replace('_', ' ', $rep['status']));
                                                        ?>
                                                        <span class="badge <?= $rep_badge ?> border"><?= $rep_label ?></span>
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
