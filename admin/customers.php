<?php
$page_title = "Customers";
require_once __DIR__ . '/../config/db.php';
require_role('admin', 'staff');

$q = trim($_GET['q'] ?? '');
$params = [];
$where = "WHERE u.role = 'customer'";
if ($q !== '') {
    $where .= " AND (u.fullname LIKE ? OR u.email LIKE ? OR u.contact_number LIKE ? OR u.username LIKE ?)";
    $params = ["%$q%", "%$q%", "%$q%", "%$q%"];
}

$sql = "SELECT u.id, u.fullname, u.email, u.username, u.contact_number, u.status, u.created_at,
            (SELECT COUNT(*) FROM sales s WHERE s.customer_id = u.id) AS purchases,
            (SELECT COALESCE(SUM(s.total_amount),0) FROM sales s WHERE s.customer_id = u.id) AS lifetime_spend,
            (SELECT COUNT(*) FROM online_orders o WHERE o.customer_id = u.id) AS pickup_orders,
            (SELECT COUNT(*) FROM repair_job_orders r WHERE r.customer_id = u.id) AS repairs
        FROM users u $where ORDER BY lifetime_spend DESC, u.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll();

// CSV export
if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=8J_Customers_' . date('Ymd') . '.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Name', 'Username', 'Email', 'Contact', 'Purchases', 'Lifetime Spend (PHP)', 'Pick-up Orders', 'Repairs', 'Joined']);
    foreach ($customers as $c) {
        fputcsv($out, [$c['fullname'], $c['username'], $c['email'], $c['contact_number'], $c['purchases'], number_format($c['lifetime_spend'], 2, '.', ''), $c['pickup_orders'], $c['repairs'], $c['created_at']]);
    }
    fclose($out);
    exit;
}

$total_spend = array_sum(array_column($customers, 'lifetime_spend'));

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h2 class="h3 fw-bold mb-1"><i class="fa-solid fa-address-book text-primary me-2"></i>Customers</h2>
                    <p class="text-muted small mb-0">Registered customers with purchase, pick-up and repair history</p>
                </div>
                <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'csv'])) ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-file-csv me-1"></i> Export CSV
                </a>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="card card-custom p-3 border-start border-primary border-4">
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size:0.68rem;">Registered Customers</div>
                        <div class="h4 fw-bolder mb-0"><?= count($customers) ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-custom p-3 border-start border-success border-4">
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size:0.68rem;">Combined Lifetime Spend</div>
                        <div class="h4 fw-bolder mb-0"><?= format_currency($total_spend) ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <form method="GET" class="card card-custom p-3 h-100 justify-content-center">
                        <div class="input-group input-group-sm">
                            <input type="text" name="q" class="form-control" placeholder="Search name, email, contact" value="<?= htmlspecialchars($q) ?>">
                            <button class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i></button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card card-custom">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size:0.88rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Customer</th><th>Contact</th>
                                    <th class="text-center">Purchases</th>
                                    <th class="text-end">Lifetime Spend</th>
                                    <th class="text-center">Pick-ups</th>
                                    <th class="text-center">Repairs</th>
                                    <th>Status</th><th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$customers): ?>
                                    <tr><td colspan="8" class="text-center py-4 text-muted">No customers found.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($customers as $c): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($c['fullname']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($c['email']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($c['contact_number'] ?: 'N/A') ?></td>
                                        <td class="text-center"><?= (int)$c['purchases'] ?></td>
                                        <td class="text-end fw-bold"><?= format_currency($c['lifetime_spend']) ?></td>
                                        <td class="text-center"><?= (int)$c['pickup_orders'] ?></td>
                                        <td class="text-center"><?= (int)$c['repairs'] ?></td>
                                        <td><span class="badge <?= $c['status'] === 'active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?> border"><?= ucfirst($c['status']) ?></span></td>
                                        <td class="text-muted small"><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
