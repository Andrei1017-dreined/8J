<?php
$page_title = "Access Denied";
require_once __DIR__ . '/includes/header.php';
?>
<div class="container py-5 text-center">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card card-custom p-5">
                <div class="text-danger fs-1 mb-3"><i class="fa-solid fa-shield-virus"></i></div>
                <h3 class="fw-bold">Access Restricted</h3>
                <p class="text-muted">You do not have administrative privileges to access this management module.</p>
                <div class="mt-4">
                    <a href="<?= BASE_URL ?>" class="btn btn-primary rounded-pill px-4">Return to Home</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
