<?php
$page_title = "User & Staff Management";
require_once __DIR__ . '/../config/db.php';
require_role('admin');

$message = '';

// Handle Add User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_user') {
    $fullname = sanitize($_POST['fullname'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $username = sanitize($_POST['username'] ?? '');
    $password = password_hash($_POST['password'] ?? 'password123', PASSWORD_DEFAULT);
    $role = sanitize($_POST['role'] ?? 'staff');
    $contact = sanitize($_POST['contact_number'] ?? '');
    $address = sanitize($_POST['address'] ?? '');

    try {
        $stmt = $pdo->prepare("INSERT INTO users (fullname, email, username, password, role, contact_number, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$fullname, $email, $username, $password, $role, $contact, $address]);
        $message = "User account created successfully!";
    } catch (Exception $e) {
        $message = "Error creating user: " . $e->getMessage();
    }
}

// Fetch users
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h2 class="h3 fw-bold mb-1"><i class="fa-solid fa-users-gear text-primary me-2"></i>User & Staff Accounts</h2>
                    <p class="text-muted small mb-0">Role-based access control for Admins, Cashier Staff, Technicians, and Registered Customers</p>
                </div>
                <button class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fa-solid fa-user-plus me-1"></i> Add Account
                </button>
            </div>

            <?php if ($message): ?>
                <div class="alert <?= strpos($message, 'Error') !== false ? 'alert-danger' : 'alert-success' ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

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
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($u['fullname']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($u['address'] ?? 'Tanauan City') ?></small>
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
                                        <td><?= htmlspecialchars($u['contact_number'] ?? 'N/A') ?></td>
                                        <td>
                                            <span class="badge bg-light text-dark border"><?= ucfirst($u['status']) ?></span>
                                        </td>
                                        <td class="text-muted small"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
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
                            <label class="form-label small fw-semibold">Password</label>
                            <input type="password" name="password" class="form-control" value="password123" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Mobile Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" placeholder="09xxxxxxxxx">
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
