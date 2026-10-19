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
    <link href="<?= BASE_URL ?>assets/css/animations.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #09090b;
            --primary-dark: #18181b;
            --sidebar-bg: #09090b;
            --sidebar-hover: #18181b;
            --accent-color: #27272a;
            --surface-bg: #fafafa;
            --card-border: #e4e4e7;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--surface-bg);
            color: #09090b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .site-wrapper {
            flex: 1 0 auto;
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        /* Black & White Monochrome Overrides */
        .btn-primary, .btn-success, .btn-dark {
            background-color: #09090b !important;
            border-color: #09090b !important;
            color: #ffffff !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.08);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-primary:hover, .btn-primary:focus,
        .btn-success:hover, .btn-success:focus,
        .btn-dark:hover, .btn-dark:focus {
            background-color: #27272a !important;
            border-color: #27272a !important;
            color: #ffffff !important;
            transform: translateY(-1px);
        }
        .btn-outline-primary, .btn-outline-success, .btn-outline-dark {
            color: #09090b !important;
            border-color: #09090b !important;
            background-color: transparent !important;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-outline-primary:hover, .btn-outline-primary:focus,
        .btn-outline-success:hover, .btn-outline-success:focus,
        .btn-outline-dark:hover, .btn-outline-dark:focus {
            background-color: #09090b !important;
            color: #ffffff !important;
            border-color: #09090b !important;
            transform: translateY(-1px);
        }

        .badge.bg-primary, .badge.bg-success, .badge.bg-dark {
            background-color: #09090b !important;
            color: #ffffff !important;
            font-weight: 600;
        }
        .badge.bg-light {
            background-color: #f4f4f5 !important;
            color: #18181b !important;
            border: 1px solid #e4e4e7 !important;
        }
        .badge.bg-secondary, .badge.bg-warning, .badge.bg-info {
            background-color: #27272a !important;
            color: #ffffff !important;
        }

        .text-primary, .text-success, .text-info {
            color: #09090b !important;
        }
        .bg-primary-subtle, .bg-success-subtle, .bg-info-subtle, .bg-warning-subtle {
            background-color: #f4f4f5 !important;
            color: #09090b !important;
        }
        .border-primary, .border-success, .border-info {
            border-color: #18181b !important;
        }

        .nav-pills .nav-link {
            color: #71717a !important;
            border-radius: 9999px;
            padding: 0.5rem 1.25rem;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .nav-pills .nav-link:hover {
            color: #09090b !important;
            background-color: #f4f4f5 !important;
        }
        .nav-pills .nav-link.active {
            background-color: #09090b !important;
            color: #ffffff !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .pagination .page-item.active .page-link {
            background-color: #09090b !important;
            border-color: #09090b !important;
            color: #ffffff !important;
        }
        .pagination .page-link {
            color: #09090b !important;
        }

        .form-control:focus, .form-select:focus {
            border-color: #09090b !important;
            box-shadow: 0 0 0 3px rgba(9, 9, 11, 0.12) !important;
        }

        .alert-success, .alert-info, .alert-warning {
            background-color: #f4f4f5 !important;
            color: #09090b !important;
            border: 1px solid #18181b !important;
        }
        .alert-danger {
            background-color: #fafafa !important;
            color: #09090b !important;
            border: 1px solid #09090b !important;
        }

        .navbar-custom {
            background: #ffffff;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            border-bottom: 1px solid var(--card-border);
        }
        .sidebar {
            background-color: var(--sidebar-bg);
            min-height: calc(100vh - 65px);
            color: #a1a1aa;
            padding-top: 1.5rem;
            border-right: 1px solid #18181b;
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
</head>
<body>
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?= BASE_URL ?>">
                <img src="<?= BASE_URL ?>assets/img/logo.png" style="width: 42px; height: 42px; object-fit: contain;" alt="8J Logo">
                <div>
                    <span class="text-dark fw-bolder">8J</span> Cellphone & Accessories
                    <small class="d-block text-muted" style="font-size: 0.68rem; font-weight: 600; letter-spacing: 0.5px;">INTEGRATED MANAGEMENT SYSTEM</small>
                </div>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="topNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page == 'catalog.php' ? 'active fw-bold' : '' ?>" href="<?= BASE_URL ?>catalog.php">
                            <img src="<?= BASE_URL ?>assets/img/icons/shop.png" class="ui-icon me-1" alt="Shop"> Catalog
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page == 'book_repair.php' ? 'active fw-bold' : '' ?>" href="<?= BASE_URL ?>book_repair.php">
                            <img src="<?= BASE_URL ?>assets/img/icons/support.png" class="ui-icon me-1" alt="Repair"> Book Repair
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page == 'track_repair.php' ? 'active fw-bold' : '' ?>" href="<?= BASE_URL ?>track_repair.php">
                            <img src="<?= BASE_URL ?>assets/img/icons/search.png" class="ui-icon me-1" alt="Track"> Track Repair
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    <li class="nav-item">
                        <a class="btn btn-outline-dark btn-sm rounded-circle d-flex align-items-center justify-content-center" href="https://www.facebook.com/profile.php?id=100063760157450" target="_blank" rel="noopener noreferrer" title="Visit 8J on Facebook" style="width: 32px; height: 32px; border-color: #e4e4e7;">
                            <i class="fa-brands fa-facebook-f" style="color: #1877f2; font-size: 0.85rem;"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-primary btn-sm rounded-pill px-3 position-relative d-inline-flex align-items-center gap-1" href="<?= BASE_URL ?>cart.php">
                            <img src="<?= BASE_URL ?>assets/img/icons/cart.png" class="ui-icon" alt="Cart"> Pick-Up Cart
                            <span id="cart-counter" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none;">0</span>
                        </a>
                    </li>
                    
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item dropdown ms-2">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; font-weight: 700;">
                                    <?= strtoupper(substr($current_user['fullname'] ?? 'U', 0, 1)) ?>
                                </div>
                                <div>
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
