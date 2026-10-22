<?php
$page_title = "Log In";
require_once __DIR__ . '/config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active' LIMIT 1");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'fullname' => $user['fullname'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'contact_number' => $user['contact_number']
                ];
                audit_log('User Login', 'Auth', "'{$user['username']}' signed in as {$user['role']}");

                if (in_array($user['role'], ['admin', 'staff', 'technician'])) {
                    header("Location: " . BASE_URL . "admin/index.php");
                } else {
                    header("Location: " . BASE_URL . "index.php");
                }
                exit;
            } else {
                $error = "Invalid username or password.";
            }
        } catch (Exception $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card card-custom p-4 p-md-5 shadow-sm">
                <div class="text-center mb-4">
                    <img src="<?= BASE_URL ?>assets/img/logo.png" style="width: 72px; height: 72px; object-fit: contain; margin-bottom: 12px;" alt="8J Logo">
                    <h4 class="fw-bold">Sign In to 8J IMS</h4>
                    <p class="text-muted small">Access POS, Inventory, Trading, Appraisal & Services</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Username or Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-user text-muted"></i></span>
                            <input type="text" name="username" id="usernameField" class="form-control" placeholder="Enter username" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-key text-muted"></i></span>
                            <input type="password" name="password" id="passwordField" class="form-control" placeholder="Enter password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold rounded-pill">
                        Log In <i class="fa-solid fa-arrow-right-to-bracket ms-1"></i>
                    </button>
                </form>

                <!-- Defense Quick Fill Helper -->
                <div class="mt-4 pt-3 border-top">
                    <label class="small text-muted fw-bold mb-2 d-block text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">
                        <i class="fa-solid fa-wand-magic-sparkles me-1 text-warning"></i> Thesis Demo Quick Login:
                    </label>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="quickFill('admin')">Admin</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="quickFill('staff')">Staff</button>
                        <button type="button" class="btn btn-sm btn-outline-info" onclick="quickFill('tech')">Tech</button>
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="quickFill('customer')">Customer</button>
                    </div>
                </div>

                <div class="text-center mt-4 text-muted small">
                    Need an account? <a href="<?= BASE_URL ?>register.php" class="text-primary fw-semibold">Sign up</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function quickFill(role) {
    document.getElementById('usernameField').value = role;
    document.getElementById('passwordField').value = 'password123';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
