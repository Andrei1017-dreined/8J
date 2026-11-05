<?php
/**
 * 8J Cellphone Accessories Integrated Management System
 * Database Connection & Global Configuration
 */

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
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
    $n = strtolower(trim((string)$name));
    $b = strtolower(trim((string)$brand));
    $hay = $b . ' ' . $n;

    // 1. HONOR (Official Global Launch & Introduction Films)
    if ($b === 'honor' || strpos($hay, 'honor') !== false) {
        if (strpos($n, '200') !== false) {
            return [
                'type'        => 'youtube',
                'youtube_id'  => 'HzjJa9fqqC8',
                'hd'          => 'https://www.youtube-nocookie.com/embed/HzjJa9fqqC8?autoplay=1&mute=0&controls=1&rel=0&modestbranding=1',
                'hover'       => '',
                'label'       => 'HONOR 200 5G • Studio Harcourt Portrait Master Film'
            ];
        }
        if (strpos($n, 'x9b') !== false || strpos($n, 'x9c') !== false || strpos($n, 'x9') !== false) {
            return [
                'type'        => 'youtube',
                'youtube_id'  => 'GhF1yXpbeU8',
                'hd'          => 'https://www.youtube-nocookie.com/embed/GhF1yXpbeU8?autoplay=1&mute=0&controls=1&rel=0&modestbranding=1',
                'hover'       => '',
                'label'       => 'HONOR X9 Series • All-Angle Ultra Tough Film'
            ];
        }
        return [
            'type'        => 'youtube',
            'youtube_id'  => 'HzjJa9fqqC8',
            'hd'          => 'https://www.youtube-nocookie.com/embed/HzjJa9fqqC8?autoplay=1&mute=0&controls=1&rel=0&modestbranding=1',
            'hover'       => '',
            'label'       => 'HONOR Official Flagship Commercial'
        ];
    }

    // 2. INFINIX (Official Gaming & Hardware Launch Films)
    if ($b === 'infinix' || strpos($hay, 'infinix') !== false) {
        if (strpos($n, 'gt 20') !== false || strpos($n, 'gt 10') !== false || strpos($n, 'gt') !== false) {
            return [
                'type'        => 'youtube',
                'youtube_id'  => 'FAH0ZtCh1ls',
                'hd'          => 'https://www.youtube-nocookie.com/embed/FAH0ZtCh1ls?autoplay=1&mute=0&controls=1&rel=0&modestbranding=1',
                'hover'       => '',
                'label'       => 'Infinix GT 20 Pro • Official Esports Gaming Film'
            ];
        }
        return [
            'type'        => 'youtube',
            'youtube_id'  => 'Kjm-TnAQf_I',
            'hd'          => 'https://www.youtube-nocookie.com/embed/Kjm-TnAQf_I?autoplay=1&mute=0&controls=1&rel=0&modestbranding=1',
            'hover'       => '',
            'label'       => 'Infinix Official Hardware Film'
        ];
    }

    // 3. REALME (Official Launch Films)
    if ($b === 'realme' || strpos($hay, 'realme') !== false) {
        return [
            'type'        => 'youtube',
            'youtube_id'  => 'k9WvfT-VFmQ',
            'hd'          => 'https://www.youtube-nocookie.com/embed/k9WvfT-VFmQ?autoplay=1&mute=0&controls=1&rel=0&modestbranding=1',
            'hover'       => '',
            'label'       => 'realme 13 Series 5G • Official Film'
        ];
    }

    // 4. SAMSUNG GALAXY (Official Commercials)
    if ($b === 'samsung' || strpos($hay, 'samsung') !== false || strpos($n, 'galaxy') !== false) {
        return [
            'type'        => 'youtube',
            'youtube_id'  => 'iNeKOMMZYRw',
            'hd'          => 'https://www.youtube-nocookie.com/embed/iNeKOMMZYRw?autoplay=1&mute=0&controls=1&rel=0&modestbranding=1',
            'hover'       => '',
            'label'       => 'Samsung Galaxy • Official Commercial'
        ];
    }

    // 5. XIAOMI / REDMI (Official Commercials)
    if ($b === 'xiaomi' || strpos($hay, 'xiaomi') !== false || strpos($n, 'redmi') !== false || strpos($n, 'poco') !== false) {
        return [
            'type'        => 'youtube',
            'youtube_id'  => 'l54w5Igi95U',
            'hd'          => 'https://www.youtube-nocookie.com/embed/l54w5Igi95U?autoplay=1&mute=0&controls=1&rel=0&modestbranding=1',
            'hover'       => '',
            'label'       => 'Redmi Note 12 Series • Official Film'
        ];
    }

    // 6. OPPO (Official Commercials)
    if ($b === 'oppo' || strpos($hay, 'oppo') !== false) {
        return [
            'type'        => 'youtube',
            'youtube_id'  => 'OLFZRXJF-fE',
            'hd'          => 'https://www.youtube-nocookie.com/embed/OLFZRXJF-fE?autoplay=1&mute=0&controls=1&rel=0&modestbranding=1',
            'hover'       => '',
            'label'       => 'OPPO Reno Series • Official Film'
        ];
    }

    // 7. ITEL / TECHLIFE (Official Commercials)
    if ($b === 'itel' || strpos($hay, 'itel') !== false || $b === 'techlife' || strpos($hay, 'techlife') !== false) {
        return [
            'type'        => 'youtube',
            'youtube_id'  => 'KBSTju7uHjY',
            'hd'          => 'https://www.youtube-nocookie.com/embed/KBSTju7uHjY?autoplay=1&mute=0&controls=1&rel=0&modestbranding=1',
            'hover'       => '',
            'label'       => 'itel Mobile • Official Commercial'
        ];
    }

    // 8. LENOVO AUDIO (Thinkplus TWS Showcase)
    if (strpos($hay, 'lenovo') !== false || strpos($hay, 'lp40') !== false || strpos($hay, 'gm2') !== false) {
        return [
            'type'        => 'youtube',
            'youtube_id'  => 'Aww_2uRovR4',
            'hd'          => 'https://www.youtube-nocookie.com/embed/Aww_2uRovR4?autoplay=1&mute=0&controls=1&rel=0&modestbranding=1',
            'hover'       => '',
            'label'       => 'Lenovo Thinkplus TWS Earbuds • Showcase'
        ];
    }

    // 9. APPLE IPHONES & APPLE CERTIFIED ACCESSORIES ONLY
    $isApple = ($b === 'apple' || strpos($hay, 'iphone') !== false || strpos($hay, 'airpods') !== false || strpos($hay, 'magsafe') !== false || strpos($hay, 'apple watch') !== false);

    if ($isApple) {
        if (strpos($n, '15 pro max') !== false) {
            return [
                'type'       => 'mp4',
                'hover'      => 'https://www.apple.com/105/media/us/iphone-15-pro/2023/2f337511-a940-4b57-b89c-1512b7507777/anim/hero/medium.mp4',
                'hover_next' => 'https://www.apple.com/105/media/us/iphone-15-pro/2023/2f337511-a940-4b57-b89c-1512b7507777/anim/titanium/medium.mp4',
                'hd'         => 'https://www.apple.com/105/media/us/iphone-15-pro/2023/2f337511-a940-4b57-b89c-1512b7507777/anim/hero/large.mp4',
                'label'      => 'iPhone 15 Pro Max • Titanium Ad'
            ];
        }
        if (strpos($n, '15 pro') !== false && strpos($n, 'case') === false) {
            return [
                'type'       => 'mp4',
                'hover'      => 'https://www.apple.com/105/media/us/iphone-15-pro/2023/2f337511-a940-4b57-b89c-1512b7507777/anim/titanium/medium.mp4',
                'hover_next' => 'https://www.apple.com/105/media/us/iphone-15-pro/2023/2f337511-a940-4b57-b89c-1512b7507777/anim/action-button/medium.mp4',
                'hd'         => 'https://www.apple.com/105/media/us/iphone-15-pro/2023/2f337511-a940-4b57-b89c-1512b7507777/anim/titanium/large.mp4',
                'label'      => 'iPhone 15 Pro • Aerospace Titanium Ad'
            ];
        }
        if (strpos($n, 'iphone 15') !== false) {
            return [
                'type'       => 'mp4',
                'hover'      => 'https://www.apple.com/105/media/us/iphone-15-pro/2023/2f337511-a940-4b57-b89c-1512b7507777/anim/action-button/medium.mp4',
                'hover_next' => 'https://www.apple.com/105/media/us/iphone-15-pro/2023/2f337511-a940-4b57-b89c-1512b7507777/anim/hero/medium.mp4',
                'hd'         => 'https://www.apple.com/105/media/us/iphone-15-pro/2023/2f337511-a940-4b57-b89c-1512b7507777/anim/action-button/large.mp4',
                'label'      => 'iPhone 15 Series • Precision Design Ad'
            ];
        }
        if (strpos($n, 'iphone 14') !== false) {
            return [
                'type'       => 'mp4',
                'hover'      => 'https://www.apple.com/105/media/us/iphone-14-pro/2023/b094f6e4-dcdb-494f-bd72-45d659126dcd/anim/hero/medium.mp4',
                'hover_next' => 'https://www.apple.com/105/media/us/iphone-15-pro/2023/2f337511-a940-4b57-b89c-1512b7507777/anim/action-button/medium.mp4',
                'hd'         => 'https://www.apple.com/105/media/us/iphone-14-pro/2023/b094f6e4-dcdb-494f-bd72-45d659126dcd/anim/hero/large.mp4',
                'label'      => 'iPhone 14 Series • Dynamic Island Ad'
            ];
        }
        if (strpos($n, 'iphone 13') !== false) {
            return [
                'type'       => 'mp4',
                'hover'      => 'https://www.apple.com/105/media/us/iphone-13-pro/2021/404b23a8-f9c5-466c-b0e6-3d36705b959d/anim/hero/medium.mp4',
                'hover_next' => 'https://www.apple.com/105/media/us/iphone-14-pro/2023/b094f6e4-dcdb-494f-bd72-45d659126dcd/anim/hero/medium.mp4',
                'hd'         => 'https://www.apple.com/105/media/us/iphone-13-pro/2021/404b23a8-f9c5-466c-b0e6-3d36705b959d/anim/hero/large.mp4',
                'label'      => 'iPhone 13 Series • ProMotion Ad'
            ];
        }
        if (strpos($n, 'airpods') !== false) {
            return [
                'type'       => 'mp4',
                'hover'      => 'https://www.apple.com/105/media/us/airpods-pro/2022/d2deeb8e-83eb-48ea-9721-f567cf0fffa8/anim/spatial-audio/medium.mp4',
                'hover_next' => 'https://www.apple.com/105/media/us/airpods-pro/2022/d2deeb8e-83eb-48ea-9721-f567cf0fffa8/anim/case/medium.mp4',
                'hd'         => 'https://www.apple.com/105/media/us/airpods-pro/2022/d2deeb8e-83eb-48ea-9721-f567cf0fffa8/anim/spatial-audio/large.mp4',
                'label'      => 'Apple AirPods Pro • Spatial Audio Ad'
            ];
        }
        if (strpos($n, 'magsafe') !== false) {
            return [
                'type'       => 'mp4',
                'hover'      => 'https://www.apple.com/105/media/us/airpods-pro/2022/d2deeb8e-83eb-48ea-9721-f567cf0fffa8/anim/case/medium.mp4',
                'hover_next' => 'https://www.apple.com/105/media/us/iphone-15-pro/2023/2f337511-a940-4b57-b89c-1512b7507777/anim/battery/medium.mp4',
                'hd'         => 'https://www.apple.com/105/media/us/airpods-pro/2022/d2deeb8e-83eb-48ea-9721-f567cf0fffa8/anim/case/large.mp4',
                'label'      => 'Apple MagSafe Ecosystem Ad'
            ];
        }
        // iPhone 12, 11, Apple Watch, etc.
        return [
            'type'       => 'mp4',
            'hover'      => 'https://www.apple.com/105/media/us/iphone-12-pro/2020/e70ffbd8-50f1-40f3-ac36-0f03a15ac314/anim/hero/small.mp4',
            'hover_next' => 'https://www.apple.com/105/media/us/iphone-13-pro/2021/404b23a8-f9c5-466c-b0e6-3d36705b959d/anim/hero/medium.mp4',
            'hd'         => 'https://www.apple.com/105/media/us/iphone-12-pro/2020/e70ffbd8-50f1-40f3-ac36-0f03a15ac314/anim/hero/large.mp4',
            'label'      => 'iPhone Official Commercial'
        ];
    }

    // 10. Default: Return null for generic unverified accessories (never show an Apple ad on an unmapped item!)
    return null;
}
