<?php
require_once __DIR__ . '/../config/db.php';
$current_user = get_current_user_data();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' - ' : '' ?>8J Cellphone Accessories IMS</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Award-Winning UI Transitions & Animations -->
    <link href="<?= BASE_URL ?>assets/css/animations.css?v=<?= time() ?>" rel="stylesheet">
    <style>
        :root {
            --primary-color: #f472b6;       /* Rose pink */
            --primary-dark: #db2777;        /* Deeper pink / magenta for contrast */
            --primary-light: #fce7f3;       /* Pastel light pink */
            --sidebar-bg: #2d1122;          /* Elegant dark rose */
            --sidebar-hover: #451a35;
            --accent-color: #fbcfe8;        /* Soft petal pink */
            --surface-bg: #fff5f8;          /* Light pink tinted background */
            --card-border: #fce7f3;         /* Subtle soft pink border */
            --text-main: #2b1120;           /* High contrast dark rose-charcoal */
        }
        html {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--surface-bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        .global-page-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('<?= BASE_URL ?>assets/img/hero_bg_tech.jpg?v=<?= filemtime(__DIR__ . '/../assets/img/hero_bg_tech.jpg') ?>');
            background-repeat: no-repeat;
            background-position: center top;
            background-size: cover;
            z-index: -9999;
            pointer-events: none;
            transform: translateZ(0);
            backface-visibility: hidden;
        }
        .site-wrapper {
            flex: 1 0 auto;
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        /* Light Pink Palette Overrides */
        .btn-primary, .btn-success {
            background-color: #f472b6 !important;
            border-color: #f472b6 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 10px rgba(244, 114, 182, 0.35);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-primary:hover, .btn-primary:focus,
        .btn-success:hover, .btn-success:focus {
            background-color: #db2777 !important;
            border-color: #db2777 !important;
            color: #ffffff !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(219, 39, 119, 0.4);
        }

        .btn-dark {
            background-color: #db2777 !important;
            border-color: #db2777 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(219, 39, 119, 0.25);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-dark:hover, .btn-dark:focus {
            background-color: #be185d !important;
            border-color: #be185d !important;
            color: #ffffff !important;
            transform: translateY(-1px);
        }

        .btn-outline-primary, .btn-outline-success {
            color: #db2777 !important;
            border-color: #f472b6 !important;
            background-color: transparent !important;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-outline-primary:hover, .btn-outline-primary:focus,
        .btn-outline-success:hover, .btn-outline-success:focus {
            background-color: #fce7f3 !important;
            color: #db2777 !important;
            border-color: #f472b6 !important;
            transform: translateY(-1px);
        }

        .btn-outline-dark {
            color: #9d174d !important;
            border-color: #fbcfe8 !important;
            background-color: transparent !important;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-outline-dark:hover, .btn-outline-dark:focus {
            background-color: #fce7f3 !important;
            color: #831843 !important;
            border-color: #f472b6 !important;
            transform: translateY(-1px);
        }

        .badge.bg-primary, .badge.bg-success {
            background-color: #f472b6 !important;
            color: #ffffff !important;
            font-weight: 600;
        }
        .badge.bg-dark {
            background-color: #db2777 !important;
            color: #ffffff !important;
            font-weight: 600;
        }
        .badge.bg-light {
            background-color: #fdf2f8 !important;
            color: #be185d !important;
            border: 1px solid #fce7f3 !important;
        }
        .badge.bg-secondary, .badge.bg-warning, .badge.bg-info {
            background-color: #fbcfe8 !important;
            color: #9d174d !important;
        }

        .text-primary, .text-success, .text-info {
            color: #db2777 !important;
        }
        .bg-primary-subtle, .bg-success-subtle, .bg-info-subtle, .bg-warning-subtle {
            background-color: #fdf2f8 !important;
            color: #db2777 !important;
        }
        .border-primary, .border-success, .border-info {
            border-color: #fbcfe8 !important;
        }

        .nav-pills .nav-link {
            color: #885d73 !important;
            border-radius: 9999px;
            padding: 0.5rem 1.25rem;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .nav-pills .nav-link:hover {
            color: #db2777 !important;
            background-color: #fce7f3 !important;
        }
        .nav-pills .nav-link.active {
            background-color: #f472b6 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(244, 114, 182, 0.3);
        }
        .pagination .page-item.active .page-link {
            background-color: #f472b6 !important;
            border-color: #f472b6 !important;
            color: #ffffff !important;
        }
        .pagination .page-link {
            color: #db2777 !important;
        }

        .form-control:focus, .form-select:focus {
            border-color: #f472b6 !important;
            box-shadow: 0 0 0 3px rgba(244, 114, 182, 0.25) !important;
        }

        .alert-success, .alert-info, .alert-warning {
            background-color: #fdf2f8 !important;
            color: #831843 !important;
            border: 1px solid #fbcfe8 !important;
        }
        .alert-danger {
            background-color: #fff1f2 !important;
            color: #9f1239 !important;
            border: 1px solid #fecdd3 !important;
        }

        .card {
            border-color: #fce7f3 !important;
        }

        .navbar-custom {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 1px 3px 0 rgba(244, 114, 182, 0.08);
            border-bottom: 1px solid #fce7f3 !important;
        }
        .sidebar {
            background-color: var(--sidebar-bg);
            height: calc(100vh - 65px);
            position: sticky;
            top: 65px;
            overflow-y: auto;
            color: #a1a1aa;
            padding-top: 1.25rem;
            padding-bottom: 2rem;
            border-right: 1px solid #18181b;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.2) transparent;
            z-index: 100;
        }
        .sidebar .nav-link {
            color: #a1a1aa;
            padding: 0.75rem 1.25rem;
            font-weight: 500;
            border-radius: 8px;
            margin: 0.2rem 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .sidebar .nav-link:hover {
            color: #ffffff;
            background-color: var(--sidebar-hover);
            transform: translateX(3px);
        }
        .sidebar .nav-link.active {
            color: #ffffff;
            background-color: #27272a;
            font-weight: 600;
            border: 1px solid #3f3f46;
        }
        .sidebar .nav-section-title {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #52525b;
            font-weight: 700;
            padding: 1rem 1.25rem 0.4rem;
        }
        .badge-status {
            font-weight: 600;
            padding: 0.35em 0.65em;
            border-radius: 6px;
        }
        .card-custom {
            border: 1px solid var(--card-border);
            border-radius: 14px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            background: #ffffff;
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .card-custom:hover {
            box-shadow: 0 6px 16px rgba(0,0,0,0.06);
        }
        .stat-card {
            border-radius: 14px;
            padding: 1.25rem;
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        }
        .ui-icon {
            width: 20px;
            height: 20px;
            object-fit: contain;
            vertical-align: middle;
            transition: transform 0.2s ease;
        }
        .nav-link:hover .ui-icon, .btn:hover .ui-icon {
            transform: scale(1.15);
        }
        /* Classes used across the site that Bootstrap 5 doesn't define (they silently did nothing,
           which is why "extrabold" headings rendered at the thin 300 display weight) */
        .fw-extrabold { font-weight: 800 !important; }
        .py-0\.5 { padding-top: .125rem !important; padding-bottom: .125rem !important; }
        .px-2\.5 { padding-left: .625rem !important; padding-right: .625rem !important; }
        .py-1\.5 { padding-top: .375rem !important; padding-bottom: .375rem !important; }
        .py-2\.5 { padding-top: .625rem !important; padding-bottom: .625rem !important; }
        .mt-2\.5 { margin-top: .625rem !important; }
        .me-1\.5 { margin-right: .375rem !important; }
        .ms-1\.5 { margin-left: .375rem !important; }
        .display-5, .display-6 { letter-spacing: -0.025em; line-height: 1.12; }

        /* Calm the circuit wallpaper so content reads first */
        .global-page-bg::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(255, 245, 248, 0.72) 0%, rgba(255, 245, 248, 0.86) 100%);
        }

        /* Wide tables scroll inside their card instead of stretching the whole page on phones */
        main, .row > [class*="col"] { min-width: 0; }
        .table-responsive { max-width: 100%; }

        /* Navbar: never wrap labels or push past the screen edge */
        .navbar-custom .nav-link, .navbar-custom .btn { white-space: nowrap; }
        .nav-user-meta .fw-semibold { max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        #cart-counter { background: #db2777 !important; border: 2px solid #fff; }
        @media (max-width: 1699.98px) {
            .nav-counter-badge { display: none !important; }
            .nav-user-meta { display: none; }
        }
        @media (min-width: 1200px) and (max-width: 1399.98px) {
            .navbar-custom .nav-link.px-3 { padding-left: .7rem !important; padding-right: .7rem !important; }
        }
        @media (max-width: 1199.98px) {
            .navbar-custom .navbar-collapse { padding: .75rem 0 1rem; }
            .nav-actions { flex-direction: row; flex-wrap: wrap; align-items: center; }
        }
        @media (max-width: 575.98px) {
            .navbar-custom .container-fluid { padding-left: 14px !important; padding-right: 14px !important; }
            .navbar-custom .navbar-brand { font-size: .95rem; max-width: calc(100% - 64px); }
            .navbar-custom .navbar-brand small { display: none !important; }
        }
        .table thead th {
            background-color: #f4f4f5;
            color: #18181b;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            border-bottom: 1px solid var(--card-border);
        }
    </style>
    <script>window.BASE_URL_JS = '<?= BASE_URL ?>';</script>
</head>
<body>
    <!-- Persistent GPU-Accelerated Fixed Wallpaper Layer -->
    <div class="global-page-bg" aria-hidden="true"></div>
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-xl navbar-custom sticky-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?= BASE_URL ?>">
                <img src="<?= BASE_URL ?>assets/img/logo.png" style="width: 42px; height: 42px; object-fit: contain;" alt="8J Logo">
                <div>
                    <span class="text-dark fw-bolder">8J</span> Cellphone & Accessories
                    <small class="d-block text-muted" style="font-size: 0.68rem; font-weight: 600; letter-spacing: 0.5px;">VICTORY MALL TANAUAN • STALL #S1</small>
                </div>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNav" aria-controls="topNav" aria-expanded="false" aria-label="Open menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <?php $admin_area = is_staff() && strpos(str_replace('\\', '/', $_SERVER['PHP_SELF'] ?? ''), '/admin/') !== false; ?>
            <div class="collapse navbar-collapse" id="topNav">
                <?php if ($admin_area): ?>
                <!-- Staff working in the management area: no shopping menu, just a way back to the dashboard and the live store -->
                <ul class="navbar-nav me-auto mb-2 mb-xl-0 ms-xl-3 gap-xl-1">
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-3 <?= $current_page == 'index.php' ? 'active fw-bold bg-light' : '' ?>" href="<?= BASE_URL ?>admin/index.php">
                            <i class="fa-solid fa-gauge-high me-1 text-primary" style="font-size: 0.85rem;"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-3" href="<?= BASE_URL ?>" target="_blank" rel="noopener">
                            <i class="fa-solid fa-store me-1 text-primary" style="font-size: 0.85rem;"></i> View store <i class="fa-solid fa-arrow-up-right-from-square ms-1" style="font-size: 0.65rem;"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-3 <?= $current_page == 'help.php' ? 'active fw-bold bg-light' : '' ?>" href="<?= BASE_URL ?>admin/help.php">
                            <i class="fa-solid fa-circle-question me-1 text-primary" style="font-size: 0.85rem;"></i> Help
                        </a>
                    </li>
                </ul>
                <?php else: ?>
                <ul class="navbar-nav me-auto mb-2 mb-xl-0 ms-xl-3 gap-xl-1">
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-3 <?= $current_page == 'index.php' ? 'active fw-bold bg-light' : '' ?>" href="<?= BASE_URL ?>">
                            <i class="fa-solid fa-house me-1 text-primary" style="font-size: 0.85rem;"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-3 <?= $current_page == 'catalog.php' ? 'active fw-bold bg-light' : '' ?>" href="<?= BASE_URL ?>catalog.php">
                            <img src="<?= BASE_URL ?>assets/img/icons/shop.png" class="ui-icon me-1" alt="Shop"> Catalog
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-3 <?= $current_page == 'book_repair.php' ? 'active fw-bold bg-light' : '' ?>" href="<?= BASE_URL ?>book_repair.php">
                            <img src="<?= BASE_URL ?>assets/img/icons/support.png" class="ui-icon me-1" alt="Repair"> Book Repair
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-3 <?= $current_page == 'track_repair.php' ? 'active fw-bold bg-light' : '' ?>" href="<?= BASE_URL ?>track_repair.php">
                            <img src="<?= BASE_URL ?>assets/img/icons/search.png" class="ui-icon me-1" alt="Track"> Track Order
                        </a>
                    </li>
                </ul>

                <!-- Global Quick Search Form -->
                <form action="<?= BASE_URL ?>catalog.php" method="GET" class="d-none d-xxl-flex align-items-center me-3" style="width: 230px;">
                    <div class="input-group input-group-sm rounded-pill overflow-hidden border" style="background: #fff5f8; border-color: #fbcfe8 !important;">
                        <span class="input-group-text border-0 bg-transparent ps-3 pe-1 text-muted">
                            <i class="fa-solid fa-magnifying-glass" style="font-size: 0.78rem; color: #db2777;"></i>
                        </span>
                        <input type="text" name="q" id="globalNavSearch" class="form-control border-0 bg-transparent shadow-none small" placeholder="Search iPhone, AirPods... (/)" style="font-size: 0.8rem;" value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
                    </div>
                </form>
                <?php endif; ?>

                <ul class="navbar-nav ms-auto align-items-xl-center gap-2 nav-actions">
                    <?php if (!$admin_area): ?>
                    <li class="nav-item d-none d-xxl-block nav-counter-badge">
                        <span class="badge bg-light text-dark border rounded-pill px-3 py-1.5 small d-inline-flex align-items-center gap-1" style="font-size: 0.74rem;">
                            <span class="pulse-indicator green"></span> Counter Open • 1-Hr Pick-Up
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-dark btn-sm rounded-circle d-flex align-items-center justify-content-center" href="https://www.facebook.com/profile.php?id=100063760157450" target="_blank" rel="noopener noreferrer" title="Visit 8J on Facebook" style="width: 32px; height: 32px; border-color: #e4e4e7;">
                            <i class="fa-brands fa-facebook-f" style="color: #1877f2; font-size: 0.85rem;"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-primary btn-sm rounded-pill px-3 position-relative d-inline-flex align-items-center gap-1 fw-semibold" href="<?= BASE_URL ?>cart.php">
                            <img src="<?= BASE_URL ?>assets/img/icons/cart.png" class="ui-icon" alt="Cart"> Pick-Up Cart
                            <span id="cart-counter" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none;">0</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item dropdown ms-xl-2">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px; font-weight: 700;">
                                    <?= htmlspecialchars(mb_strtoupper(mb_substr($current_user['fullname'] ?? 'U', 0, 1))) ?>
                                </div>
                                <div class="nav-user-meta">
                                    <div class="fw-semibold text-dark" style="font-size:0.85rem;"><?= htmlspecialchars($current_user['fullname']) ?></div>
                                    <div class="badge bg-secondary" style="font-size:0.65rem;"><?= ucfirst($current_user['role']) ?></div>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                <?php if (has_role('admin', 'staff', 'technician')): ?>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/index.php"><i class="fa-solid fa-gauge me-2 text-primary"></i> Management Dashboard</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php else: ?>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>my_orders.php"><i class="fa-solid fa-box me-2 text-primary"></i> My Pick-Up Orders</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i> Log Out</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-2">
                            <a class="btn btn-primary btn-sm px-3 rounded-pill" href="<?= BASE_URL ?>login.php">
                                <i class="fa-solid fa-user me-1"></i> Log In
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="site-wrapper">
