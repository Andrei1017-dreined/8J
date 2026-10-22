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

// Global variable for current authenticated user across all scripts and handlers
$current_user = get_current_user_data();

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


// ---------------------------------------------------------------------
// Admin helpers: CSRF protection & audit trail
// ---------------------------------------------------------------------

/** Returns the session CSRF token (created on first use). */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Hidden input to embed inside every POST form. */
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

/** Validates the CSRF token on the current POST request. */
function csrf_valid() {
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

/** Writes an entry to the audit_logs table. Never throws. */
function audit_log($action, $module, $details = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, module, details, ip_address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user']['id'] ?? null,
            $action,
            $module,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    } catch (Exception $e) {
        // Logging must never break the main workflow
    }
}

/**
 * Returns outsourced official CDN commercial video URLs (lightweight hover loop + HD stream)
 * matched to the product or smartphone model. Zero local storage footprint.
 */
function get_product_ad_video($name, $brand = '') {
    $hay = strtolower($name . ' ' . $brand);

    // 1. iPhone 15 Pro Max
    if (strpos($hay, '15 pro max') !== false) {
        return [
            'hover'      => 'https://www.apple.com/105/media/us/iphone-15-pro/2023/2f337511-a940-4b57-b89c-1512b7507777/anim/hero/medium.mp4',
            'hover_next' => 'https://www.apple.com/105/media/us/iphone-15-pro/2023/2f337511-a940-4b57-b89c-1512b7507777/anim/titanium/medium.mp4',
            'hd'         => 'https://www.apple.com/105/media/us/iphone-15-pro/2023/2f337511-a940-4b57-b89c-1512b7507777/anim/hero/large.mp4',
            'label'      => 'iPhone 15 Pro Max • Titanium Ad'
        ];
    }

    // 2. iPhone 15 Pro (or iPhone 15 Pro Clear Case)
    if (strpos($hay, '15 pro') !== false && strpos($hay, 'case') === false) {
        return [
            'hover'      => 'https://www.apple.com/105/media/us/iphone-15-pro/2023/2f337511-a940-4b57-b89c-1512b7507777/anim/titanium/medium.mp4',
            'hover_next' => 'https://www.apple.com/105/media/us/iphone-15-pro/2023/2f337511-a940-4b57-b89c-1512b7507777/anim/action-button/medium.mp4',
            'hd'         => 'https://www.apple.com/105/media/us/iphone-15-pro/2023/2f337511-a940-4b57-b89c-1512b7507777/anim/titanium/large.mp4',
            'label'      => 'iPhone 15 Pro • Aerospace Titanium Ad'
        ];
    }

    // 3. iPhone 15 / Cases / Action Button
    if (strpos($hay, 'iphone 15') !== false || strpos($hay, 'case') !== false || strpos($hay, 'shockproof') !== false || strpos($hay, 'glass') !== false) {
        return [
            'hover'      => 'https://www.apple.com/105/media/us/iphone-15-pro/2023/2f337511-a940-4b57-b89c-1512b7507777/anim/action-button/medium.mp4',
            'hover_next' => 'https://www.apple.com/105/media/us/iphone-15-pro/2023/2f337511-a940-4b57-b89c-1512b7507777/anim/hero/medium.mp4',
            'hd'         => 'https://www.apple.com/105/media/us/iphone-15-pro/2023/2f337511-a940-4b57-b89c-1512b7507777/anim/action-button/large.mp4',
            'label'      => 'Precision Design & Protection Ad'
        ];
    }

    // 4. iPhone 14 Pro / iPhone 14
    if (strpos($hay, 'iphone 14') !== false) {
        return [
            'hover'      => 'https://www.apple.com/105/media/us/iphone-14-pro/2023/b094f6e4-dcdb-494f-bd72-45d659126dcd/anim/hero/medium.mp4',
            'hover_next' => 'https://www.apple.com/105/media/us/iphone-15-pro/2023/2f337511-a940-4b57-b89c-1512b7507777/anim/action-button/medium.mp4',
            'hd'         => 'https://www.apple.com/105/media/us/iphone-14-pro/2023/b094f6e4-dcdb-494f-bd72-45d659126dcd/anim/hero/large.mp4',
            'label'      => 'iPhone 14 Series • Dynamic Island Ad'
        ];
    }

    // 5. iPhone 13 Pro / iPhone 13
    if (strpos($hay, 'iphone 13') !== false) {
        return [
            'hover'      => 'https://www.apple.com/105/media/us/iphone-13-pro/2021/404b23a8-f9c5-466c-b0e6-3d36705b959d/anim/hero/medium.mp4',
            'hover_next' => 'https://www.apple.com/105/media/us/iphone-14-pro/2023/b094f6e4-dcdb-494f-bd72-45d659126dcd/anim/hero/medium.mp4',
            'hd'         => 'https://www.apple.com/105/media/us/iphone-13-pro/2021/404b23a8-f9c5-466c-b0e6-3d36705b959d/anim/hero/large.mp4',
            'label'      => 'iPhone 13 Series • ProMotion Ad'
        ];
    }

    // 6. AirPods / Earbuds / Earphones / Audio
    if (strpos($hay, 'airpods') !== false || strpos($hay, 'earphone') !== false || strpos($hay, 'earbud') !== false || strpos($hay, 'audio') !== false || strpos($hay, 'headphone') !== false) {
        return [
            'hover'      => 'https://www.apple.com/105/media/us/airpods-pro/2022/d2deeb8e-83eb-48ea-9721-f567cf0fffa8/anim/spatial-audio/medium.mp4',
            'hover_next' => 'https://www.apple.com/105/media/us/airpods-pro/2022/d2deeb8e-83eb-48ea-9721-f567cf0fffa8/anim/case/medium.mp4',
            'hd'         => 'https://www.apple.com/105/media/us/airpods-pro/2022/d2deeb8e-83eb-48ea-9721-f567cf0fffa8/anim/spatial-audio/large.mp4',
            'label'      => 'Spatial Audio & ANC Commercial'
        ];
    }

    // 7. MagSafe Charger / Cables / Chargers
    if (strpos($hay, 'magsafe') !== false && strpos($hay, 'battery') === false || strpos($hay, 'charger') !== false || strpos($hay, 'cable') !== false) {
        return [
            'hover'      => 'https://www.apple.com/105/media/us/airpods-pro/2022/d2deeb8e-83eb-48ea-9721-f567cf0fffa8/anim/case/medium.mp4',
            'hover_next' => 'https://www.apple.com/105/media/us/iphone-15-pro/2023/2f337511-a940-4b57-b89c-1512b7507777/anim/battery/medium.mp4',
            'hd'         => 'https://www.apple.com/105/media/us/airpods-pro/2022/d2deeb8e-83eb-48ea-9721-f567cf0fffa8/anim/case/large.mp4',
            'label'      => 'MagSafe & Fast Charging Ad'
        ];
    }

    // 8. Power Banks / Battery Pack / Apple Watch Ultra
    if (strpos($hay, 'powerbank') !== false || strpos($hay, 'power bank') !== false || strpos($hay, 'battery') !== false || strpos($hay, 'watch') !== false) {
        return [
            'hover'      => 'https://www.apple.com/105/media/us/iphone-15-pro/2023/2f337511-a940-4b57-b89c-1512b7507777/anim/battery/medium.mp4',
            'hover_next' => 'https://www.apple.com/105/media/us/iphone-15-pro/2023/2f337511-a940-4b57-b89c-1512b7507777/anim/hero/medium.mp4',
            'hd'         => 'https://www.apple.com/105/media/us/iphone-15-pro/2023/2f337511-a940-4b57-b89c-1512b7507777/anim/battery/large.mp4',
            'label'      => 'All-Day High-Endurance Power Ad'
        ];
    }

    // 9. iPhone 12 / iPhone 11 / Other Smartphones & Default
    return [
        'hover'      => 'https://www.apple.com/105/media/us/iphone-12-pro/2020/e70ffbd8-50f1-40f3-ac36-0f03a15ac314/anim/hero/small.mp4',
        'hover_next' => 'https://www.apple.com/105/media/us/iphone-13-pro/2021/404b23a8-f9c5-466c-b0e6-3d36705b959d/anim/hero/medium.mp4',
        'hd'         => 'https://www.apple.com/105/media/us/iphone-12-pro/2020/e70ffbd8-50f1-40f3-ac36-0f03a15ac314/anim/hero/large.mp4',
        'label'      => 'Flagship Smartphone Commercial'
    ];
}
