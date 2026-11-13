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

    $contact = preg_replace('/[\s-]/', '', $contact);

    if (empty($fullname) || empty($email) || empty($username) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var(html_entity_decode($email, ENT_QUOTES), FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (!preg_match('/^[A-Za-z0-9_.]{3,30}$/', $username)) {
        $error = "Username must be 3–30 characters: letters, numbers, dots or underscores.";
    } elseif ($contact !== '' && !preg_match('/^(09\d{9}|\+639\d{9})$/', $contact)) {
        $error = "Please enter an 11-digit mobile number starting with 09.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        try {
            $dupe = $pdo->prepare("SELECT username = ? AS u, email = ? AS e FROM users WHERE username = ? OR email = ? LIMIT 1");
            $dupe->execute([$username, $email, $username, $email]);
            $hit = $dupe->fetch();
            if ($hit) {
                $error = $hit['u'] ? "That username is already taken." : "An account with that email already exists. Try signing in instead.";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (fullname, email, username, password, role, contact_number) VALUES (?, ?, ?, ?, 'customer', ?)");
                $stmt->execute([$fullname, $email, $username, $hashed, $contact]);
                audit_log('Customer Registered', 'Auth', "New customer account '{$username}'");
                $success = "Registration successful! You can now log in.";
            }
        } catch (Exception $e) {
            error_log('Register error: ' . $e->getMessage());
            $error = "We couldn't create your account right now. Please try again.";
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
                            <input type="text" name="fullname" class="form-control" required maxlength="120" autocomplete="name" placeholder="Juan Dela Cruz" value="<?= $_SERVER['REQUEST_METHOD'] === 'POST' ? ($fullname ?? '') : '' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control" required maxlength="120" autocomplete="email" placeholder="juan@gmail.com" value="<?= $_SERVER['REQUEST_METHOD'] === 'POST' ? ($email ?? '') : '' ?>">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Username</label>
                                <input type="text" name="username" class="form-control" required minlength="3" maxlength="30" pattern="[A-Za-z0-9_.]{3,30}" title="3–30 letters, numbers, dots or underscores" autocomplete="username" value="<?= $_SERVER['REQUEST_METHOD'] === 'POST' ? ($username ?? '') : '' ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Mobile No.</label>
                                <input type="tel" name="contact_number" class="form-control" inputmode="tel" autocomplete="tel" placeholder="09xxxxxxxxx" value="<?= $_SERVER['REQUEST_METHOD'] === 'POST' ? htmlspecialchars($contact ?? '') : '' ?>">
                            </div>
                        </div>
                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Password</label>
                                <input type="password" name="password" class="form-control" required minlength="8" autocomplete="new-password">
                                <div class="form-text small">At least 8 characters</div>
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
