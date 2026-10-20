<?php
/**
 * Database Seeder for 8J IMS
 * Populates realistic data for thesis presentation and testing
 */

require_once __DIR__ . '/../config/db.php';

try {
    // 1. Create Default Users (admin, staff, tech, customer)
    $password_hash = password_hash('password123', PASSWORD_DEFAULT);
    
    $users = [
        ['Johanne Gabriel (Admin)', 'admin@8jcellphone.com', 'admin', $password_hash, 'admin', '09171234567', 'Tanauan City, Batangas'],
        ['Gigi Genil (Manager)', 'gigi@8jcellphone.com', 'manager', $password_hash, 'admin', '09181234567', 'Tanauan City, Batangas'],
        ['Mark Cashier', 'cashier@8jcellphone.com', 'staff', $password_hash, 'staff', '09191234567', 'Tanauan City, Batangas'],
        ['Dave Technician', 'tech@8jcellphone.com', 'tech', $password_hash, 'technician', '09201234567', 'Tanauan City, Batangas'],
        ['Juan Dela Cruz', 'customer@gmail.com', 'customer', $password_hash, 'customer', '09211234567', 'Brgy. Darasa, Tanauan City']
    ];

    $stmt = $pdo->prepare("INSERT INTO `users` (`fullname`, `email`, `username`, `password`, `role`, `contact_number`, `address`) 
                           VALUES (?, ?, ?, ?, ?, ?, ?) 
                           ON DUPLICATE KEY UPDATE `fullname`=VALUES(`fullname`)");
    foreach ($users as $u) {
        $stmt->execute($u);
    }

    // 2. Categories
    $categories = [
        ['Cases & Protective Covers', 'accessory', 'Shockproof, silicone, clear, and leather phone cases'],
        ['Chargers & Fast Cables', 'accessory', 'Type-C, Lightning, 20W/33W/65W fast chargers'],
        ['Tempered Glass & Protectors', 'accessory', '9D curved, privacy, matte, and clear screen protectors'],
        ['Powerbanks & Portable Batteries', 'accessory', '10,000mAh to 30,000mAh high capacity battery packs'],
        ['Audio & Earphones', 'accessory', 'TWS wireless earbuds, wired 3.5mm and Type-C earphones'],
        ['Smartphones & Handheld Units', 'unit', 'Brand new, pre-owned, and refurbished mobile devices'],
        ['Replacement LCDs & Spare Parts', 'part', 'OEM and Original replacement parts for repairs']
    ];

    $stmt = $pdo->prepare("INSERT INTO `categories` (`name`, `type`, `description`) 
                           VALUES (?, ?, ?) 
                           ON DUPLICATE KEY UPDATE `description`=VALUES(`description`)");
    foreach ($categories as $cat) {
        $stmt->execute($cat);
    }

    // 3. Products (Accessories)
    $products = [
        [2, 'ACC-CHG-001', 'Apple 20W USB-C Power Adapter (OEM)', 'Apple', 250.00, 550.00, 35, 10, 'apple_adapter.jpg', 'Fast charging wall plug compatible with iPhone 12 to 15 series.'],
        [2, 'ACC-CAB-002', 'Bavin Type-C to Lightning Fast Cable 1.2M', 'Bavin', 90.00, 220.00, 50, 15, 'bavin_cable.jpg', 'Braided nylon heavy duty cable for iOS devices.'],
        [3, 'ACC-TMP-003', 'iPhone 13 / 14 9D Full Privacy Tempered Glass', 'WK Design', 50.00, 180.00, 60, 20, 'screen_protector.jpg', 'Anti-spy privacy curved edge tempered glass with oleophobic coating.'],
        [3, 'ACC-TMP-004', 'Samsung Galaxy A54 Ceramic Matte Screen Protector', 'KingKong', 45.00, 150.00, 40, 10, 'screen_protector.jpg', 'Matte gaming finish, unbreakable flexible ceramic film.'],
        [1, 'ACC-CSE-005', 'MagSafe Clear Shockproof Hybrid Case iPhone 15 Pro', 'OEM', 120.00, 320.00, 25, 8, 'magsafe_case.jpg', 'Strong magnetic ring, non-yellowing acrylic backplate.'],
        [4, 'ACC-PWR-006', 'Baseus 20000mAh 22.5W Fast Charging Powerbank', 'Baseus', 650.00, 1150.00, 18, 5, 'baseus_powerbank.jpg', 'Dual USB-A and Type-C PD fast charging with LED battery display.'],
        [5, 'ACC-AUD-007', 'Lenovo LP40 Pro Wireless Bluetooth Earbuds', 'Lenovo', 320.00, 680.00, 22, 6, 'lenovo_earbuds.jpg', 'HiFi stereo sound, low latency gaming mode, 250mAh case.']
    ];

    $stmt = $pdo->prepare("INSERT INTO `products` (`category_id`, `sku`, `name`, `brand`, `cost_price`, `selling_price`, `stock_quantity`, `reorder_level`, `image`, `description`) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?) 
                           ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `selling_price`=VALUES(`selling_price`), `image`=VALUES(`image`)");
    foreach ($products as $p) {
        $stmt->execute($p);
    }

    // 4. Pre-Owned & Brand New Phone Units (with IMEI)
    $units = [
        ['Apple', 'iPhone 13', '128GB', 'Midnight Blue', '359284112938471', '359284112938472', 'Pre-Owned Grade A', 89, 21000.00, 26500.00, 'in_stock', 'customer_buyout', 'iphone_13.jpg', 'Smooth 98% condition, original screen and battery, factory unlocked.'],
        ['Apple', 'iPhone 11', '64GB', 'White', '354829104829103', '354829104829104', 'Pre-Owned Grade B', 82, 11500.00, 14800.00, 'in_stock', 'customer_trade', 'iphone_11.jpg', 'Minimal signs of usage on bezels, 100% functional, 3Utools normal.'],
        ['Samsung', 'Galaxy S22 5G', '128GB', 'Phantom Black', '358192039481726', '358192039481727', 'Pre-Owned Grade A', 92, 17500.00, 22000.00, 'in_stock', 'supplier', 'galaxy_s22.png', 'Snapdragon chipset, pristine condition, dual SIM.'],
        ['Xiaomi', 'Redmi Note 12', '128GB', 'Ice Blue', '864728192039481', '864728192039482', 'Brand New', 100, 6800.00, 8499.00, 'in_stock', 'supplier', 'redmi_note_12.jpg', 'Sealed box with official warranty.']
    ];

    $stmt = $pdo->prepare("INSERT INTO `phone_units` (`brand`, `model_name`, `storage_capacity`, `color`, `imei_1`, `imei_2`, `unit_condition`, `battery_health`, `buy_price`, `selling_price`, `status`, `source`, `image`, `notes`) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) 
                           ON DUPLICATE KEY UPDATE `selling_price`=VALUES(`selling_price`), `image`=VALUES(`image`)");
    foreach ($units as $u) {
        $stmt->execute($u);
    }

    // 5. Appraisal Valuation Matrix
    $matrices = [
        ['Apple', 'iPhone 11', '64GB', 14000.00, 100.00, 82.00, 65.00, 600.00, 2200.00, 1000.00, 1200.00, 1200.00],
        ['Apple', 'iPhone 11', '128GB', 16500.00, 100.00, 82.00, 65.00, 600.00, 2200.00, 1000.00, 1200.00, 1200.00],
        ['Apple', 'iPhone 12', '128GB', 21500.00, 100.00, 85.00, 68.00, 800.00, 2800.00, 1200.00, 1500.00, 1500.00],
        ['Apple', 'iPhone 13', '128GB', 27000.00, 100.00, 85.00, 70.00, 1000.00, 3500.00, 1400.00, 1800.00, 1800.00],
        ['Apple', 'iPhone 14', '128GB', 33000.00, 100.00, 87.00, 72.00, 1200.00, 4200.00, 1600.00, 2000.00, 2000.00],
        ['Samsung', 'Galaxy S21 5G', '128GB', 15000.00, 100.00, 80.00, 65.00, 700.00, 2500.00, 1000.00, 1400.00, 1400.00],
        ['Samsung', 'Galaxy S22 5G', '128GB', 22000.00, 100.00, 82.00, 68.00, 900.00, 3000.00, 1200.00, 1600.00, 1600.00]
    ];

    $stmt = $pdo->prepare("INSERT INTO `appraisal_matrix` (`brand`, `model_name`, `storage_capacity`, `base_market_value`, `grade_a_pct`, `grade_b_pct`, `grade_c_pct`, `screen_scratch_deduction`, `screen_crack_deduction`, `battery_low_deduction`, `camera_issue_deduction`, `biometrics_issue_deduction`) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($matrices as $m) {
        $stmt->execute($m);
    }

    echo "<h3>✅ Database Seeded Successfully!</h3>";
    echo "<p>Default login accounts created:</p>";
    echo "<ul>
            <li><strong>Admin:</strong> username: <code>admin</code> | password: <code>password123</code></li>
            <li><strong>Staff:</strong> username: <code>staff</code> | password: <code>password123</code></li>
            <li><strong>Technician:</strong> username: <code>tech</code> | password: <code>password123</code></li>
            <li><strong>Customer:</strong> username: <code>customer</code> | password: <code>password123</code></li>
          </ul>";

} catch (Exception $e) {
    echo "<h3>Error during seeding:</h3> " . htmlspecialchars($e->getMessage());
}
