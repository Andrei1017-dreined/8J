<?php
$current_admin_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    .sidebar-icon {
        width: 18px;
        height: 18px;
        object-fit: contain;
        filter: brightness(0) invert(0.7);
        transition: all 0.2s ease;
    }
    .sidebar .nav-link:hover .sidebar-icon,
    .sidebar .nav-link.active .sidebar-icon {
        filter: brightness(0) invert(1);
        transform: scale(1.1);
    }
</style>
<div class="sidebar col-md-3 col-lg-2 d-md-block collapse" id="sidebarMenu">
    <div class="position-sticky">
        <div class="nav-section-title">Core Operations</div>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link <?= $current_admin_page == 'index.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/index.php">
                    <img src="<?= BASE_URL ?>assets/img/icons/home.png" class="sidebar-icon" alt="Home"> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $current_admin_page == 'pos.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/pos.php">
                    <img src="<?= BASE_URL ?>assets/img/icons/cart.png" class="sidebar-icon" alt="POS"> POS & Billing
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $current_admin_page == 'inventory.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/inventory.php">
                    <img src="<?= BASE_URL ?>assets/img/icons/shop.png" class="sidebar-icon" alt="Inventory"> Inventory (IMEI)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $current_admin_page == 'categories.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/categories.php">
                    <img src="<?= BASE_URL ?>assets/img/icons/settings.png" class="sidebar-icon" alt="Categories"> Categories
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $current_admin_page == 'customers.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/customers.php">
                    <img src="<?= BASE_URL ?>assets/img/icons/favorites.png" class="sidebar-icon" alt="Customers"> Customers
                </a>
            </li>
        </ul>

        <div class="nav-section-title">Trade-In, Swap &amp; Buyouts</div>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link <?= $current_admin_page == 'appraisal.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/appraisal.php">
                    <img src="<?= BASE_URL ?>assets/img/icons/search.png" class="sidebar-icon" alt="Appraisal"> Unit Appraisal
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $current_admin_page == 'trading.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/trading.php">
                    <img src="<?= BASE_URL ?>assets/img/icons/trade-in.png" class="sidebar-icon" alt="Trading"> Phone Trade-In &amp; Swap
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $current_admin_page == 'buying.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/buying.php">
                    <img src="<?= BASE_URL ?>assets/img/icons/deals.png" class="sidebar-icon" alt="Buyouts"> Buying of Units (Cash)
                </a>
            </li>
        </ul>

        <div class="nav-section-title">Services & Orders</div>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link <?= $current_admin_page == 'repairs.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/repairs.php">
                    <img src="<?= BASE_URL ?>assets/img/icons/support.png" class="sidebar-icon" alt="Repairs"> Repair Orders
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $current_admin_page == 'pickups.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/pickups.php">
                    <img src="<?= BASE_URL ?>assets/img/icons/shipping.png" class="sidebar-icon" alt="Pickups"> Pick-Up Queue
                </a>
            </li>
        </ul>

        <?php if (has_role('admin')): ?>
            <div class="nav-section-title">Administration</div>
            <ul class="nav flex-column mb-3">
                <li class="nav-item">
                    <a class="nav-link <?= $current_admin_page == 'reports.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/reports.php">
                        <img src="<?= BASE_URL ?>assets/img/icons/orders.png" class="sidebar-icon" alt="Reports"> Sales Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_admin_page == 'audit.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/audit.php">
                        <img src="<?= BASE_URL ?>assets/img/icons/support.png" class="sidebar-icon" alt="Audit"> Audit Trail
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_admin_page == 'users.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/users.php">
                        <img src="<?= BASE_URL ?>assets/img/icons/account.png" class="sidebar-icon" alt="Users"> User Accounts
                    </a>
                </li>
            </ul>
        <?php endif; ?>
    </div>
</div>
