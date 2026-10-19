<?php
$page_title = "Customer Registration";
require_once __DIR__ . '/config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = sanitize($_POST['fullname'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $username = sanitize($_POST['username'] ?? '');
    $contact = sanitize($_POST['contact_number'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($fullname) || empty($email) || empty($username) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        try {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (fullname, email, username, password, role, contact_number) VALUES (?, ?, ?, ?, 'customer', ?)");
            $stmt->execute([$fullname, $email, $username, $hashed, $contact]);
            $success = "Registration successful! You can now log in.";
        } catch (Exception $e) {
            $error = "Username or email is already taken.";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card card-custom p-4 p-md-5 shadow-sm">
                <div class="text-center mb-4">
                    <h4 class="fw-bold">Create Customer Account</h4>
                    <p class="text-muted small">Register to track your online pick-ups and repair appointments</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success py-2 small">
                        <?= htmlspecialchars($success) ?>
                        <div class="mt-2"><a href="<?= BASE_URL ?>login.php" class="fw-bold text-success">Click here to Log In</a></div>
                    </div>
                <?php else: ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Full Name</label>
                            <input type="text" name="fullname" class="form-control" required placeholder="Juan Dela Cruz">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control" required placeholder="juan@gmail.com">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Contact No.</label>
                                <input type="text" name="contact_number" class="form-control" placeholder="09xxxxxxxxx">
                            </div>
                        </div>
                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-pill">
                            Sign Up
                        </button>
                    </form>
                <?php endif; ?>

                <div class="text-center mt-4 text-muted small">
                    Already have an account? <a href="<?= BASE_URL ?>login.php" class="text-primary fw-semibold">Log In</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
