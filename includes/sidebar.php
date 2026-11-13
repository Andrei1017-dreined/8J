<?php
/**
 * Admin side navigation.
 * - Grouped by task, in plain words
 * - Each role only sees what it is allowed to open (no dead-end "Access denied" links)
 * - Live counts show what is waiting
 * - On phones it collapses behind a "Menu" button
 */
$current_admin_page = basename($_SERVER['PHP_SELF']);
$is_admin = has_role('admin');
$is_staff_or_admin = has_role('admin', 'staff');
$is_tech = has_role('technician');

// What's waiting (one round-trip; never breaks the page)
$nav_counts = ['pick_prep' => 0, 'pick_ready' => 0, 'rep_booked' => 0, 'rep_ready' => 0, 'rep_mine' => 0, 'low_stock' => 0, 'appraisals' => 0];
try {
    $row = $pdo->prepare("SELECT
        (SELECT COUNT(*) FROM online_orders WHERE pickup_status = 'pending_confirmation') AS pick_prep,
        (SELECT COUNT(*) FROM online_orders WHERE pickup_status = 'ready_for_pickup') AS pick_ready,
        (SELECT COUNT(*) FROM repair_job_orders WHERE status = 'scheduled') AS rep_booked,
        (SELECT COUNT(*) FROM repair_job_orders WHERE status = 'ready_for_pickup') AS rep_ready,
        (SELECT COUNT(*) FROM repair_job_orders WHERE technician_id = ? AND status IN ('received','diagnosing','waiting_parts','repairing')) AS rep_mine,
        (SELECT COUNT(*) FROM products WHERE stock_quantity <= reorder_level AND status <> 'archived') AS low_stock,
        (SELECT COUNT(*) FROM appraisal_records WHERE status = 'pending') AS appraisals");
    $row->execute([(int)($current_user['id'] ?? 0)]);
    $nav_counts = array_map('intval', $row->fetch() ?: $nav_counts);
} catch (Exception $e) {}

/** One sidebar link. $badge: number to show; $tone: 'alert' (needs action) or 'muted'. */
if (!function_exists('admin_nav_link')) {
function admin_nav_link($file, $icon, $label, $badge = 0, $tone = 'alert', $hint = '', $step = null) {
    global $current_admin_page;
    $active = $current_admin_page === basename(parse_url($file, PHP_URL_PATH));
    // Links to a filtered list (e.g. repairs.php?status=scheduled) are only "active" on that filter
    if ($active && ($q = parse_url($file, PHP_URL_QUERY))) {
        parse_str($q, $want);
        $active = ($want['status'] ?? '') === ($_GET['status'] ?? '');
    }
    $hint = $hint ?: strip_tags($label);
    $html = '<li class="nav-item"><a class="nav-link d-flex align-items-center gap-2' . ($active ? ' active' : '') . '" href="' . BASE_URL . 'admin/' . $file . '"' . ($active ? ' aria-current="page"' : '') . ' title="' . htmlspecialchars($hint) . '">';
    $html .= $step !== null
        ? '<span class="nav-step">' . $step . '</span>'
        : '<i class="fa-solid ' . $icon . ' nav-ico"></i>';
    $html .= '<span class="flex-grow-1">' . $label . '</span>';
    if ($badge > 0) {
        $html .= '<span class="nav-count ' . ($tone === 'alert' ? 'is-alert' : '') . '">' . $badge . '</span>';
    }
    return $html . '</a></li>';
}
}

$page_names = [
    'index.php' => 'Dashboard', 'pos.php' => 'New sale', 'pickups.php' => 'Pick-up orders', 'repairs.php' => 'Repair jobs',
    'appraisal.php' => 'Appraise a phone', 'trading.php' => 'Trade-in / swap', 'buying.php' => 'Buy a phone',
    'inventory.php' => 'Inventory', 'categories.php' => 'Categories', 'customers.php' => 'Customers',
    'reports.php' => 'Sales reports', 'audit.php' => 'Activity log', 'users.php' => 'Staff accounts', 'help.php' => 'Help & guides',
];
?>
<style>
    .sidebar .nav-ico { width: 18px; text-align: center; font-size: .9rem; opacity: .75; flex: none; }
    .sidebar .nav-link .flex-grow-1 { min-width: 0; line-height: 1.25; }
    @media (min-width: 768px) and (max-width: 1199.98px) {
        .sidebar .nav-link { padding: .55rem .7rem !important; font-size: .82rem; gap: .45rem !important; }
        .sidebar .nav-section-title { padding-left: .8rem !important; padding-right: .8rem !important; }
    }
    .sidebar .nav-link.active .nav-ico, .sidebar .nav-link:hover .nav-ico { opacity: 1; }
    .sidebar .nav-step {
        width: 18px; height: 18px; border-radius: 50%; display: inline-grid; place-items: center;
        font-size: .65rem; font-weight: 800; background: rgba(255,255,255,.12); color: #fbcfe8; flex: none;
    }
    .sidebar .nav-link.active .nav-step { background: #db2777; color: #fff; }
    .sidebar .nav-count {
        min-width: 22px; padding: 1px 7px; border-radius: 999px; font-size: .7rem; font-weight: 700; text-align: center;
        background: rgba(255,255,255,.12); color: #e4e4e7;
    }
    .sidebar .nav-count.is-alert { background: #db2777; color: #fff; }
    .sidebar .nav-help { margin: 8px 12px 18px; padding: 12px 14px; border-radius: 14px; background: rgba(255,255,255,.06); color: #d4d4d8; font-size: .78rem; }
    .sidebar .nav-help a { color: #f9a8d4; font-weight: 700; text-decoration: none; }
    .admin-mobile-bar {
        position: sticky; top: 64px; z-index: 1010; margin: 0 -12px 8px; padding: 8px 12px;
        background: rgba(255,255,255,.92); backdrop-filter: blur(8px); border-bottom: 1px solid #fce7f3;
    }
    @media (max-width: 767.98px) {
        .sidebar { height: auto !important; position: static !important; border-radius: 16px; margin-bottom: 12px; padding-bottom: 4px; }
    }
</style>

<!-- Phones: the menu is behind this button -->
<div class="col-12 d-md-none admin-mobile-bar">
    <button class="btn btn-dark btn-sm rounded-pill px-3 d-inline-flex align-items-center gap-2" type="button"
            data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false">
        <i class="fa-solid fa-bars"></i> Menu
    </button>
    <span class="ms-2 small fw-semibold text-muted"><?= htmlspecialchars($page_names[$current_admin_page] ?? '') ?></span>
    <?php $mobile_waiting = $nav_counts['pick_prep'] + $nav_counts['rep_booked'] + $nav_counts['rep_ready']; ?>
    <?php if ($mobile_waiting && !$is_tech): ?>
        <span class="badge rounded-pill ms-1" style="background:#db2777;"><?= $mobile_waiting ?> waiting</span>
    <?php endif; ?>
</div>

<nav class="sidebar col-md-3 col-lg-2 d-md-block collapse" id="sidebarMenu" aria-label="Management menu">
    <div class="position-sticky pt-2">
        <ul class="nav flex-column mb-2">
            <?= admin_nav_link('index.php', 'fa-gauge-high', 'Dashboard') ?>
        </ul>

        <?php if ($is_staff_or_admin): ?>
            <div class="nav-section-title">Sell</div>
            <ul class="nav flex-column mb-2">
                <?= admin_nav_link('pos.php', 'fa-cash-register', 'New sale (POS)', 0, 'alert', 'Ring up a walk-in sale and print the receipt') ?>
                <?= admin_nav_link('pickups.php', 'fa-bag-shopping', 'Pick-up orders', $nav_counts['pick_prep'] + $nav_counts['pick_ready'], $nav_counts['pick_prep'] ? 'alert' : 'muted',
                    'Online reservations to prepare and release') ?>
            </ul>
        <?php endif; ?>

        <div class="nav-section-title">Repairs</div>
        <ul class="nav flex-column mb-2">
            <?php if ($is_tech): ?>
                <?= admin_nav_link('repairs.php?mine=1&status=active', 'fa-screwdriver-wrench', 'My repair jobs', $nav_counts['rep_mine'], 'alert') ?>
                <?= admin_nav_link('repairs.php?status=scheduled', 'fa-calendar-check', 'Booked online', $nav_counts['rep_booked'], 'muted') ?>
            <?php else: ?>
                <?= admin_nav_link('repairs.php', 'fa-screwdriver-wrench', 'Repair jobs', $nav_counts['rep_booked'] + $nav_counts['rep_ready'], 'alert',
                    'Booked online: ' . $nav_counts['rep_booked'] . ' · Ready for pick-up: ' . $nav_counts['rep_ready']) ?>
            <?php endif; ?>
        </ul>

        <div class="nav-section-title">Buy &amp; trade-in</div>
        <ul class="nav flex-column mb-2">
            <?= admin_nav_link('appraisal.php', '', 'Appraise a phone', 0, 'alert', 'Step 1: check the phone and get its value', 1) ?>
            <?php if ($is_staff_or_admin): ?>
                <?= admin_nav_link('trading.php', '', 'Trade-in / swap', $nav_counts['appraisals'], 'muted', 'Step 2a: swap the appraised phone for one of ours', 2) ?>
                <?= admin_nav_link('buying.php', '', 'Buy a phone (cash)', 0, 'alert', 'Step 2b: pay cash for the appraised phone', 2) ?>
            <?php endif; ?>
        </ul>

        <?php if ($is_staff_or_admin): ?>
            <div class="nav-section-title">Stock &amp; customers</div>
            <ul class="nav flex-column mb-2">
                <?= admin_nav_link('inventory.php', 'fa-boxes-stacked', 'Inventory', $nav_counts['low_stock'], 'muted', $nav_counts['low_stock'] . ' accessories are low on stock') ?>
                <?= admin_nav_link('categories.php', 'fa-tags', 'Categories') ?>
                <?= admin_nav_link('customers.php', 'fa-users', 'Customers') ?>
            </ul>
        <?php endif; ?>

        <?php if ($is_admin): ?>
            <div class="nav-section-title">Owner</div>
            <ul class="nav flex-column mb-2">
                <?= admin_nav_link('reports.php', 'fa-chart-line', 'Sales reports') ?>
                <?= admin_nav_link('audit.php', 'fa-clock-rotate-left', 'Activity log', 0, 'alert', 'Who did what, and when') ?>
                <?= admin_nav_link('users.php', 'fa-user-gear', 'Staff accounts') ?>
            </ul>
        <?php endif; ?>

        <ul class="nav flex-column mb-2">
            <?= admin_nav_link('help.php', 'fa-circle-question', 'Help & guides') ?>
        </ul>
        <div class="nav-help d-none d-md-block">
            <i class="fa-solid fa-lightbulb me-1" style="color:#f9a8d4;"></i>
            Pink numbers mean something is waiting for you. <a href="<?= BASE_URL ?>admin/help.php">How things work</a>
        </div>
    </div>
</nav>
