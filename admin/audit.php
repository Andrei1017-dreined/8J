<?php
$page_title = "Audit Trail";
require_once __DIR__ . '/../config/db.php';
require_role('admin');

// Filters
$q       = trim($_GET['q'] ?? '');
$module  = $_GET['module'] ?? '';
$user_id = (int)($_GET['user_id'] ?? 0);
$from    = $_GET['from'] ?? '';
$to      = $_GET['to'] ?? '';

$where = []; $params = [];
if ($q !== '')      { $where[] = "(a.action LIKE ? OR a.details LIKE ?)"; array_push($params, "%$q%", "%$q%"); }
if ($module !== '') { $where[] = "a.module = ?"; $params[] = $module; }
if ($user_id > 0)   { $where[] = "a.user_id = ?"; $params[] = $user_id; }
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) { $where[] = "DATE(a.created_at) >= ?"; $params[] = $from; }
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))   { $where[] = "DATE(a.created_at) <= ?"; $params[] = $to; }
$where_sql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

$base_sql = "FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id" . $where_sql;

// CSV export
if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=8J_Audit_Trail_' . date('Ymd_His') . '.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Date & Time', 'User', 'Role', 'Module', 'Action', 'Details', 'IP Address']);
    $stmt = $pdo->prepare("SELECT a.*, u.fullname, u.role $base_sql ORDER BY a.id DESC");
    $stmt->execute($params);
    while ($r = $stmt->fetch()) {
        fputcsv($out, [$r['id'], $r['created_at'], $r['fullname'] ?? 'System', $r['role'] ?? '', $r['module'], $r['action'], $r['details'], $r['ip_address']]);
    }
    fclose($out);
    exit;
}

// Pagination
$per_page = 25;
$page = max(1, (int)($_GET['page'] ?? 1));
$cnt = $pdo->prepare("SELECT COUNT(*) $base_sql");
$cnt->execute($params);
$total = (int)$cnt->fetchColumn();
$pages = max(1, (int)ceil($total / $per_page));
$page = min($page, $pages);
$offset = ($page - 1) * $per_page;

$stmt = $pdo->prepare("SELECT a.*, u.fullname, u.role $base_sql ORDER BY a.id DESC LIMIT $per_page OFFSET $offset");
$stmt->execute($params);
$logs = $stmt->fetchAll();

$modules = $pdo->query("SELECT DISTINCT module FROM audit_logs ORDER BY module")->fetchAll(PDO::FETCH_COLUMN);
$users   = $pdo->query("SELECT id, fullname FROM users WHERE role != 'customer' ORDER BY fullname")->fetchAll();

$qs = $_GET; unset($qs['page']);
$qs_str = http_build_query($qs);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h2 class="h3 fw-bold mb-1"><i class="fa-solid fa-clipboard-list text-primary me-2"></i>Audit Trail</h2>
                    <p class="text-muted small mb-0">Accountability log of sign-ins, account changes, transactions and settings</p>
                </div>
                <a href="?<?= htmlspecialchars($qs_str . ($qs_str ? '&' : '') . 'export=csv') ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-file-csv me-1"></i> Export CSV
                </a>
            </div>

            <form method="GET" class="card card-custom p-3 mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold mb-1">Search</label>
                        <input type="text" name="q" class="form-control form-control-sm" placeholder="Action or details" value="<?= htmlspecialchars($q) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold mb-1">Module</label>
                        <select name="module" class="form-select form-select-sm">
                            <option value="">All</option>
                            <?php foreach ($modules as $m): ?>
                                <option <?= $module === $m ? 'selected' : '' ?>><?= htmlspecialchars($m) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold mb-1">User</label>
                        <select name="user_id" class="form-select form-select-sm">
                            <option value="0">Anyone</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= (int)$u['id'] ?>" <?= $user_id === (int)$u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['fullname']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold mb-1">From</label>
                        <input type="date" name="from" class="form-control form-control-sm" value="<?= htmlspecialchars($from) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold mb-1">To</label>
                        <input type="date" name="to" class="form-control form-control-sm" value="<?= htmlspecialchars($to) ?>">
                    </div>
                    <div class="col-md-1 d-grid">
                        <button class="btn btn-primary btn-sm" type="submit">Go</button>
                    </div>
                </div>
            </form>

            <div class="card card-custom">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">Events</h6>
                    <span class="badge bg-light text-dark border"><?= number_format($total) ?> record<?= $total === 1 ? '' : 's' ?></span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="table-light">
                                <tr><th>When</th><th>User</th><th>Module</th><th>Action</th><th>Details</th><th>IP</th></tr>
                            </thead>
                            <tbody>
                                <?php if (!$logs): ?>
                                    <tr><td colspan="6" class="text-center py-4 text-muted">No audit events found.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($logs as $l): ?>
                                    <tr>
                                        <td class="text-nowrap text-muted small"><?= date('M d, Y H:i', strtotime($l['created_at'])) ?></td>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars($l['fullname'] ?? 'System') ?></div>
                                            <small class="text-muted"><?= htmlspecialchars(ucfirst($l['role'] ?? '')) ?></small>
                                        </td>
                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($l['module']) ?></span></td>
                                        <td class="fw-semibold"><?= htmlspecialchars($l['action']) ?></td>
                                        <td class="text-muted"><?= htmlspecialchars($l['details'] ?? '') ?></td>
                                        <td class="small text-muted"><?= htmlspecialchars($l['ip_address'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if ($pages > 1): ?>
                    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                        <small class="text-muted">Page <?= $page ?> of <?= $pages ?></small>
                        <div class="btn-group btn-group-sm">
                            <a class="btn btn-outline-secondary <?= $page <= 1 ? 'disabled' : '' ?>" href="?<?= htmlspecialchars($qs_str . ($qs_str ? '&' : '') . 'page=' . ($page - 1)) ?>">&laquo; Prev</a>
                            <a class="btn btn-outline-secondary <?= $page >= $pages ? 'disabled' : '' ?>" href="?<?= htmlspecialchars($qs_str . ($qs_str ? '&' : '') . 'page=' . ($page + 1)) ?>">Next &raquo;</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
