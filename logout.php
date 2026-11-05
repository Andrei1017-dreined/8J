<?php
require_once __DIR__ . '/config/db.php';

// Destroy server session & session cookie
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logging Out...</title>
    <script>
        // Clear browser customer shopping cart on logout
        try {
            localStorage.removeItem('8j_cart');
        } catch(e) {}
        window.location.replace("<?= BASE_URL ?>login.php");
    </script>
</head>
<body style="background-color: #fff5f8; font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0;">
    <div style="text-align: center; color: #db2777;">
        <p style="font-weight: 600; font-size: 16px;">Logging out and clearing session...</p>
    </div>
</body>
</html>
