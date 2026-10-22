<?php
$page_title = "User & Staff Management";
require_once __DIR__ . '/../config/db.php';
require_role('admin');

$message = '';
$me = (int)($_SESSION['user']['id'] ?? 0);
$valid_roles = ['admin', 'staff', 'technician', 'customer'];

// ---------------------------------------------------------------------
// POST handlers (all protected by CSRF)
// ---------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if (!csrf_valid()) {
        $message = "Error: Security token expired. Please reload the page and try again.";
    } else {
        try {
            if ($action === 'add_user') {
                $fullname = sanitize($_POST['fullname'] ?? '');
                $email    = sanitize($_POST['email'] ?? '');
                $username = sanitize($_POST['username'] ?? '');
                $plain    = $_POST['password'] ?? '';
                $role     = in_array($_POST['role'] ?? '', $valid_roles, true) ? $_POST['role'] : 'staff';
                $contact  = sanitize($_POST['contact_number'] ?? '');
                $address  = sanitize($_POST['address'] ?? '');

                if ($fullname === '' || $username === '' || !filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Full name, username and a valid email are required.");
                }
                if (strlen($plain) < 8) {
                    throw new Exception("Password must be at least 8 characters.");
                }

                $stmt = $pdo->prepare("INSERT INTO users (fullname, email, username, password, role, contact_number, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$fullname, $email, $username, password_hash($plain, PASSWORD_DEFAULT), $role, $contact, $address]);
                audit_log('Created User Account', 'Users', "Created {$role} account '{$username}'");
                $message = "User account created successfully!";

            } elseif ($action === 'edit_user') {
                $id       = (int)($_POST['user_id'] ?? 0);
                $fullname = sanitize($_POST['fullname'] ?? '');
                $email    = sanitize($_POST['email'] ?? '');
                $contact  = sanitize($_POST['contact_number'] ?? '');
                $address  = sanitize($_POST['address'] ?? '');
                $role     = in_array($_POST['role'] ?? '', $valid_roles, true) ? $_POST['role'] : 'staff';

                if ($id <= 0 || $fullname === '' || !filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Full name and a valid email are required.");
                }
                if ($id === $me && $role !== 'admin') {
                    throw new Exception("You cannot remove your own administrator role.");
                }

                $stmt = $pdo->prepare("UPDATE users SET fullname = ?, email = ?, contact_number = ?, address = ?, role = ? WHERE id = ?");
                $stmt->execute([$fullname, $email, $contact, $address, $role, $id]);
                audit_log('Updated User Account', 'Users', "Updated user #{$id} ({$fullname}), role: {$role}");
                $message = "Account updated successfully!";

            } elseif ($action === 'toggle_status') {
                $id = (int)($_POST['user_id'] ?? 0);
                if ($id === $me) {
                    throw new Exception("You cannot deactivate your own account.");
                }
                $pdo->prepare("UPDATE users SET status = IF(status = 'active', 'inactive', 'active') WHERE id = ?")->execute([$id]);
                $new = $pdo->prepare("SELECT username, status FROM users WHERE id = ?");
                $new->execute([$id]);
                $row = $new->fetch();
                audit_log('Changed Account Status', 'Users', "Account '{$row['username']}' is now {$row['status']}");
                $message = "Account '{$row['username']}' is now {$row['status']}.";

            } elseif ($action === 'reset_password') {
                $id    = (int)($_POST['user_id'] ?? 0);
                $plain = $_POST['new_password'] ?? '';
                if (strlen($plain) < 8) {
                    throw new Exception("New password must be at least 8 characters.");
                }
                $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([password_hash($plain, PASSWORD_DEFAULT), $id]);
                audit_log('Reset User Password', 'Users', "Password reset for user #{$id}");
                $message = "Password reset successfully.";
            }
        } catch (PDOException $e) {
            $message = ($e->getCode() == 23000)
                ? "Error: That username or email is already in use."
                : "Error: " . $e->getMessage();
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}

// ---------------------------------------------------------------------
// Filters
// ---------------------------------------------------------------------
$q      = trim($_GET['q'] ?? '');
$f_role = $_GET['role'] ?? '';
$f_stat = $_GET['status'] ?? '';

$where = []; $params = [];
if ($q !== '') {
    $where[] = "(fullname LIKE ? OR username LIKE ? OR email LIKE ? OR contact_number LIKE ?)";
    array_push($params, "%$q%", "%$q%", "%$q%", "%$q%");
}
if (in_array($f_role, $valid_roles, true)) { $where[] = "role = ?"; $params[] = $f_role; }
if (in_array($f_stat, ['active', 'inactive'], true)) { $where[] = "status = ?"; $params[] = $f_stat; }

$stmt = $pdo->prepare("SELECT * FROM users" . ($where ? " WHERE " . implode(' AND ', $where) : "") . " ORDER BY id DESC");
$stmt->execute($params);
$users = $stmt->fetchAll();

$counts = ['all' => 0, 'admin' => 0, 'staff' => 0, 'technician' => 0, 'customer' => 0, 'inactive' => 0];
foreach ($pdo->query("SELECT role, status, COUNT(*) c FROM users GROUP BY role, status")->fetchAll() as $r) {
    $counts['all'] += $r['c'];
    $counts[$r['role']] += $r['c'];
    if ($r['status'] === 'inactive') $counts['inactive'] += $r['c'];
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h2 class="h3 fw-bold mb-1"><i class="fa-solid fa-users-gear text-primary me-2"></i>User &amp; Staff Accounts</h2>
                    <p class="text-muted small mb-0">Role-based access control for Admins, Cashier Staff, Technicians, and Registered Customers</p>
                </div>
                <button class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fa-solid fa-user-plus me-1"></i> Add Account
                </button>
            </div>

            <?php if ($message): ?>
                <div class="alert <?= strpos($message, 'Error') === 0 ? 'alert-danger' : 'alert-success' ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Summary chips -->
            <div class="row g-3 mb-4">
                <?php
                $chips = [
                    ['Total Accounts', $counts['all'], 'fa-users', 'primary'],
                    ['Admins', $counts['admin'], 'fa-crown', 'dark'],
                    ['Staff / Cashiers', $counts['staff'], 'fa-cash-register', 'success'],
                    ['Technicians', $counts['technician'], 'fa-wrench', 'warning'],
                    ['Customers', $counts['customer'], 'fa-user', 'info'],
                    ['Deactivated', $counts['inactive'], 'fa-user-slash', 'danger'],
                ];
                foreach ($chips as [$label, $val, $icon, $color]): ?>
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="card card-custom p-3 border-start border-<?= $color ?> border-4">
                            <div class="text-muted small fw-semibold text-uppercase" style="font-size:0.68rem;"><?= $label ?></div>
                            <div class="h4 fw-bolder mb-0"><i class="fa-solid <?= $icon ?> me-2 text-<?= $color ?>" style="font-size:0.9rem;"></i><?= (int)$val ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Search & filters -->
            <form method="GET" class="card card-custom p-3 mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label small fw-semibold mb-1">Search</label>
                        <input type="text" name="q" class="form-control form-control-sm" placeholder="Name, username, email or contact" value="<?= htmlspecialchars($q) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold mb-1">Role</label>
                        <select name="role" class="form-select form-select-sm">
                            <option value="">All roles</option>
                            <?php foreach ($valid_roles as $r): ?>
                                <option value="<?= $r ?>" <?= $f_role === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Any</option>
                            <option value="active" <?= $f_stat === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $f_stat === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-primary btn-sm flex-fill" type="submit"><i class="fa-solid fa-magnifying-glass me-1"></i>Filter</button>
                        <a href="users.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                    </div>
                </div>
            </form>

            <div class="card card-custom">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Full Name</th>
                                    <th>Username / Email</th>
                                    <th>Role</th>
                                    <th>Contact</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$users): ?>
                                    <tr><td colspan="7" class="text-center py-4 text-muted">No accounts match your filters.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($users as $u): $is_me = ((int)$u['id'] === $me); ?>
                                    <tr class="<?= $u['status'] === 'inactive' ? 'table-secondary' : '' ?>">
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($u['fullname']) ?><?= $is_me ? ' <span class="badge bg-primary-subtle text-primary border">You</span>' : '' ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($u['address'] ?: 'Tanauan City') ?></small>
                                        </td>
                                        <td>
                                            <code><?= htmlspecialchars($u['username']) ?></code>
                                            <div class="small text-muted"><?= htmlspecialchars($u['email']) ?></div>
                                        </td>
                                        <td>
                                            <?php if ($u['role'] === 'admin'): ?>
                                                <span class="badge bg-dark text-white"><i class="fa-solid fa-crown me-1"></i> Admin</span>
                                            <?php elseif ($u['role'] === 'staff'): ?>
                                                <span class="badge bg-light text-dark border border-dark"><i class="fa-solid fa-cash-register me-1"></i> Cashier Staff</span>
                                            <?php elseif ($u['role'] === 'technician'): ?>
                                                <span class="badge bg-dark text-white"><i class="fa-solid fa-wrench me-1"></i> Technician</span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-secondary border"><i class="fa-solid fa-user me-1"></i> Customer</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($u['contact_number'] ?: 'N/A') ?></td>
                                        <td>
                                            <span class="badge <?= $u['status'] === 'active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?> border">
                                                <?= ucfirst($u['status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-muted small"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                                        <td class="text-end text-nowrap">
                                            <button class="btn btn-outline-primary btn-sm py-0 px-2" title="Edit"
                                                data-bs-toggle="modal" data-bs-target="#editUserModal"
                                                data-user='<?= htmlspecialchars(json_encode([
                                                    'id' => (int)$u['id'], 'fullname' => $u['fullname'], 'email' => $u['email'],
                                                    'role' => $u['role'], 'contact' => $u['contact_number'], 'address' => $u['address'],
                                                    'is_me' => $is_me
                                                ]), ENT_QUOTES) ?>'>
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                            <button class="btn btn-outline-secondary btn-sm py-0 px-2" title="Reset password"
                                                data-bs-toggle="modal" data-bs-target="#resetPwModal"
                                                data-id="<?= (int)$u['id'] ?>" data-name="<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>">
                                                <i class="fa-solid fa-key"></i>
                                            </button>
                                            <?php if (!$is_me): ?>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('<?= $u['status'] === 'active' ? 'Deactivate' : 'Reactivate' ?> this account?');">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="action" value="toggle_status">
                                                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                                    <button class="btn btn-sm py-0 px-2 <?= $u['status'] === 'active' ? 'btn-outline-danger' : 'btn-outline-success' ?>"
                                                        title="<?= $u['status'] === 'active' ? 'Deactivate' : 'Reactivate' ?>">
                                                        <i class="fa-solid <?= $u['status'] === 'active' ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
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

<!-- Modal: Add User -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add_user">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Create New Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Full Name</label>
                        <input type="text" name="fullname" class="form-control" required placeholder="e.g. Maria Santos">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Role</label>
                            <select name="role" class="form-select" required>
                                <option value="staff">Staff / Cashier</option>
                                <option value="technician">Repair Technician</option>
                                <option value="admin">Administrator / Owner</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Password <span class="text-muted fw-normal">(min 8)</span></label>
                            <input type="password" name="password" class="form-control" minlength="8" required autocomplete="new-password">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Mobile Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" placeholder="09xxxxxxxxx">
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-semibold">Address</label>
                        <input type="text" name="address" class="form-control" placeholder="Optional">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit User -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="edit_user">
                <input type="hidden" name="user_id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Full Name</label>
                        <input type="text" name="fullname" id="edit_fullname" class="form-control" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="form-label small fw-semibold">Email Address</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                        <div class="col-5">
                            <label class="form-label small fw-semibold">Role</label>
                            <select name="role" id="edit_role" class="form-select">
                                <option value="staff">Staff / Cashier</option>
                                <option value="technician">Repair Technician</option>
                                <option value="admin">Administrator</option>
                                <option value="customer">Customer</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Mobile Contact Number</label>
                        <input type="text" name="contact_number" id="edit_contact" class="form-control">
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-semibold">Address</label>
                        <input type="text" name="address" id="edit_address" class="form-control">
                    </div>
                    <div id="edit_self_note" class="form-text text-warning d-none mt-2"><i class="fa-solid fa-circle-info me-1"></i>This is your own account, so the role is locked to Administrator.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Reset Password -->
<div class="modal fade" id="resetPwModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form method="POST" action="">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="user_id" id="reset_id">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted">Set a new password for <strong id="reset_name"></strong>.</p>
                    <input type="password" name="new_password" class="form-control" minlength="8" required placeholder="New password (min 8)" autocomplete="new-password">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Reset</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('editUserModal').addEventListener('show.bs.modal', function (e) {
    const u = JSON.parse(e.relatedTarget.getAttribute('data-user'));
    document.getElementById('edit_id').value = u.id;
    document.getElementById('edit_fullname').value = u.fullname;
    document.getElementById('edit_email').value = u.email;
    document.getElementById('edit_role').value = u.role;
    document.getElementById('edit_contact').value = u.contact || '';
    document.getElementById('edit_address').value = u.address || '';
    document.getElementById('edit_role').disabled = u.is_me;
    document.getElementById('edit_self_note').classList.toggle('d-none', !u.is_me);
    // Disabled selects are not submitted; keep admin role for self-edits
    let hidden = document.getElementById('edit_role_hidden');
    if (u.is_me) {
        if (!hidden) {
            hidden = document.createElement('input');
            hidden.type = 'hidden'; hidden.name = 'role'; hidden.id = 'edit_role_hidden';
            document.getElementById('edit_role').form.appendChild(hidden);
        }
        hidden.value = 'admin';
    } else if (hidden) {
        hidden.remove();
    }
});
document.getElementById('resetPwModal').addEventListener('show.bs.modal', function (e) {
    document.getElementById('reset_id').value = e.relatedTarget.getAttribute('data-id');
    document.getElementById('reset_name').textContent = e.relatedTarget.getAttribute('data-name');
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
