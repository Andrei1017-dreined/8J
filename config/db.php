<?php
/**
 * 8J Cellphone Accessories Integrated Management System
 * Database Connection & Global Configuration
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = '8j_ims_db';

try {
    // Attempt connecting to database
    $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // If database does not exist, attempt to auto-create and load schema
    try {
        $init_pdo = new PDO("mysql:host={$db_host};charset=utf8mb4", $db_user, $db_pass);
        $init_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $init_pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        
        $schema_file = __DIR__ . '/../database/schema.sql';
        if (file_exists($schema_file)) {
            $sql = file_get_contents($schema_file);
            $pdo->exec($sql);
        }
    } catch (PDOException $ex) {
        die("<div style='font-family:sans-serif;padding:25px;background:#ffebee;color:#c62828;border-radius:8px;max-width:600px;margin:40px auto;border:1px solid #ef9a9a;'>
            <h3>Database Connection Notice</h3>
            <p>Could not connect to MySQL. Please ensure <strong>Apache</strong> and <strong>MySQL</strong> are started in your XAMPP Control Panel.</p>
            <p><small>Error details: " . htmlspecialchars($ex->getMessage()) . "</small></p>
        </div>");
    }
}

// Auto-seed if products table is empty
try {
    $product_count = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    if ($product_count === 0 && file_exists(__DIR__ . '/../database/seed.php')) {
        ob_start();
        include __DIR__ . '/../database/seed.php';
        ob_end_clean();
    }
} catch (Exception $e) {
    // If table doesn't exist yet, it will be handled on next request
}

// Define root URL paths
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('BASE_URL', $protocol . $host . '/8j_ims/');

// Helper function for user auth checks
function get_current_user_data() {
    return $_SESSION['user'] ?? null;
}

function is_logged_in() {
    return isset($_SESSION['user']);
}

function has_role(...$allowed_roles) {
    if (!isset($_SESSION['user']['role'])) return false;
    return in_array($_SESSION['user']['role'], $allowed_roles);
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: " . BASE_URL . "login.php");
        exit;
    }
}

function require_role(...$allowed_roles) {
    require_login();
    if (!has_role(...$allowed_roles)) {
        header("Location: " . BASE_URL . "unauthorized.php");
        exit;
    }
}

function format_currency($amount) {
    return '₱' . number_format((float)$amount, 2);
}

function sanitize($input) {
    return htmlspecialchars(trim($input ?? ''), ENT_QUOTES, 'UTF-8');
}
