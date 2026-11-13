<?php
$page_title = "Access Denied";
require_once __DIR__ . '/includes/header.php';
?>
<div class="container py-5 text-center">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card card-custom p-5">
                <div class="text-danger fs-1 mb-3"><i class="fa-solid fa-shield-virus"></i></div>
                <h3 class="fw-bold">This page isn't part of your role</h3>
                <p class="text-muted">
                    <?php if (is_staff()): ?>
                        Your account (<?= htmlspecialchars(ucfirst($current_user['role'])) ?>) can't open that page. If you need it, ask the owner to change your role in <em>Staff accounts</em>.
                    <?php else: ?>
                        That page is only for 8J staff.
                    <?php endif; ?>
                </p>
                <div class="mt-4 d-flex flex-wrap gap-2 justify-content-center">
                    <?php if (is_staff()): ?>
                        <a href="<?= BASE_URL ?>admin/index.php" class="btn btn-primary rounded-pill px-4">Back to dashboard</a>
                    <?php elseif (is_logged_in()): ?>
                        <a href="<?= BASE_URL ?>my_orders.php" class="btn btn-primary rounded-pill px-4">My orders</a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>" class="btn btn-outline-dark rounded-pill px-4">Store home</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
