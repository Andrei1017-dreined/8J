<?php
/**
 * 8J Cellphone IMS - Apply HD Apple Pictures to Catalog & Inventory
 */
require_once __DIR__ . '/../config/db.php';

echo "Updating Apple Product & Unit Images...\n";

// 1. Update existing phone unit images to HD renders
$pdo->prepare("UPDATE phone_units SET image = 'iphone_13_midnight.png', color = 'Midnight Black' WHERE id = 1")->execute();
$pdo->prepare("UPDATE phone_units SET image = 'iphone_11_white.png', color = 'Starlight White' WHERE id = 2")->execute();
$pdo->prepare("UPDATE phone_units SET image = 'iphone_11_purple.png', color = 'Lilac Purple' WHERE id = 11")->execute();

// 2. Update existing accessory image
$pdo->prepare("UPDATE products SET image = 'iphone_15_pro_clear_case.png' WHERE id = 5 OR sku = 'ACC-CSE-005'")->execute();

// 3. New Apple Units to Insert
$new_units = [
    [
        'brand' => 'Apple',
        'model_name' => 'iPhone 15 Pro Max',
        'storage_capacity' => '256GB',
        'color' => 'Natural Titanium',
        'imei_1' => '359182736451921',
        'imei_2' => '359182736451922',
        'unit_condition' => 'Pre-Owned Grade A',
        'battery_health' => 98,
        'buy_price' => 48000.00,
        'selling_price' => 56500.00,
        'status' => 'in_stock',
        'source' => 'customer_trade',
        'image' => 'iphone_15_pro_natural.png',
        'notes' => 'Grade A+ pristine condition. Aerospace titanium design, A17 Pro chip, 5x telephoto optical zoom. Original box and USB-C cable included.'
    ],
    [
        'brand' => 'Apple',
        'model_name' => 'iPhone 15 Pro',
        'storage_capacity' => '128GB',
        'color' => 'Blue Titanium',
        'imei_1' => '358920183746193',
        'imei_2' => '358920183746194',
        'unit_condition' => 'Pre-Owned Grade A',
        'battery_health' => 95,
        'buy_price' => 41000.00,
        'selling_price' => 48990.00,
        'status' => 'in_stock',
        'source' => 'customer_buyout',
        'image' => 'iphone_15_pro_blue.png',
        'notes' => '100% genuine Apple display & battery, Action Button, 48MP main camera, openline to all SIMs.'
    ],
    [
        'brand' => 'Apple',
        'model_name' => 'iPhone 15',
        'storage_capacity' => '128GB',
        'color' => 'Pastel Pink',
        'imei_1' => '357281903847291',
        'imei_2' => '357281903847292',
        'unit_condition' => 'Pre-Owned Grade A',
        'battery_health' => 97,
        'buy_price' => 32000.00,
        'selling_price' => 38500.00,
        'status' => 'in_stock',
        'source' => 'supplier',
        'image' => 'iphone_15_pink.png',
        'notes' => 'Dynamic Island, color-infused back glass, A16 Bionic chip, 48MP camera with 2x telephoto.'
    ],
    [
        'brand' => 'Apple',
        'model_name' => 'iPhone 14 Pro',
        'storage_capacity' => '256GB',
        'color' => 'Deep Purple',
        'imei_1' => '356281920384721',
        'imei_2' => '356281920384722',
        'unit_condition' => 'Pre-Owned Grade A',
        'battery_health' => 91,
        'buy_price' => 34000.00,
        'selling_price' => 41800.00,
        'status' => 'in_stock',
        'source' => 'customer_trade',
        'image' => 'iphone_14_pro_purple.png',
        'notes' => 'Always-On Super Retina XDR display, ProMotion 120Hz, Dynamic Island, flawless condition with 30-day store warranty.'
    ],
    [
        'brand' => 'Apple',
        'model_name' => 'iPhone 14',
        'storage_capacity' => '128GB',
        'color' => 'Midnight',
        'imei_1' => '355819203847192',
        'imei_2' => '355819203847193',
        'unit_condition' => 'Pre-Owned Grade A',
        'battery_health' => 89,
        'buy_price' => 24500.00,
        'selling_price' => 29800.00,
        'status' => 'in_stock',
        'source' => 'customer_buyout',
        'image' => 'iphone_14_midnight.png',
        'notes' => 'Cinematic mode 4K, all-day battery life, smooth back and frame, verified clean iCloud.'
    ],
    [
        'brand' => 'Apple',
        'model_name' => 'iPhone 13 Pro',
        'storage_capacity' => '128GB',
        'color' => 'Sierra Blue',
        'imei_1' => '354928103948271',
        'imei_2' => '354928103948272',
        'unit_condition' => 'Pre-Owned Grade A',
        'battery_health' => 87,
        'buy_price' => 27000.00,
        'selling_price' => 33500.00,
        'status' => 'in_stock',
        'source' => 'customer_trade',
        'image' => 'iphone_13_pro_sierra.png',
        'notes' => 'ProMotion 120Hz, 3x optical zoom, stainless steel frame, 100% functional, 3uTools verified.'
    ],
    [
        'brand' => 'Apple',
        'model_name' => 'iPhone 12 Pro',
        'storage_capacity' => '128GB',
        'color' => 'Pacific Blue',
        'imei_1' => '353819203847191',
        'imei_2' => '353819203847192',
        'unit_condition' => 'Pre-Owned Grade A',
        'battery_health' => 85,
        'buy_price' => 19500.00,
        'selling_price' => 24500.00,
        'status' => 'in_stock',
        'source' => 'supplier',
        'image' => 'iphone_12_pro_pacific.png',
        'notes' => 'Ceramic Shield front, textured matte glass back, LiDAR scanner, night mode portraits.'
    ],
    [
        'brand' => 'Apple',
        'model_name' => 'iPhone 12',
        'storage_capacity' => '128GB',
        'color' => 'Purple',
        'imei_1' => '352819203847191',
        'imei_2' => '352819203847192',
        'unit_condition' => 'Pre-Owned Grade A',
        'battery_health' => 86,
        'buy_price' => 16000.00,
        'selling_price' => 20500.00,
        'status' => 'in_stock',
        'source' => 'customer_buyout',
        'image' => 'iphone_12_purple.png',
        'notes' => 'Super Retina XDR OLED, A14 Bionic, MagSafe compatible, smooth edge bezels.'
    ]
];

$u_stmt = $pdo->prepare("INSERT INTO `phone_units` 
    (`brand`, `model_name`, `storage_capacity`, `color`, `imei_1`, `imei_2`, `unit_condition`, `battery_health`, `buy_price`, `selling_price`, `status`, `source`, `image`, `notes`) 
    VALUES (:brand, :model_name, :storage_capacity, :color, :imei_1, :imei_2, :unit_condition, :battery_health, :buy_price, :selling_price, :status, :source, :image, :notes)
    ON DUPLICATE KEY UPDATE `selling_price`=VALUES(`selling_price`), `image`=VALUES(`image`), `status`='in_stock'");

foreach ($new_units as $u) {
    $u_stmt->execute($u);
    echo " + Phone Unit: {$u['brand']} {$u['model_name']} ({$u['color']}) -> {$u['image']}\n";
}

// 4. New Apple Accessories to Insert
$new_products = [
    [
        'category_id' => 5, // Audio
        'sku' => 'ACC-AUD-008',
        'name' => 'Apple AirPods Pro (2nd Generation) with MagSafe Case (USB-C)',
        'brand' => 'Apple',
        'cost_price' => 1400.00,
        'selling_price' => 2450.00,
        'stock_quantity' => 15,
        'reorder_level' => 4,
        'image' => 'airpods_pro_2.png',
        'description' => 'Up to 2x more Active Noise Cancellation, Adaptive Audio, Transparency mode, and Personalized Spatial Audio with dynamic head tracking.'
    ],
    [
        'category_id' => 5, // Audio
        'sku' => 'ACC-AUD-009',
        'name' => 'Apple AirPods (3rd Generation) Wireless Earphones',
        'brand' => 'Apple',
        'cost_price' => 1100.00,
        'selling_price' => 1850.00,
        'stock_quantity' => 18,
        'reorder_level' => 5,
        'image' => 'airpods_3.png',
        'description' => 'Personalized Spatial Audio with dynamic head tracking, contoured design with force sensor controls, IPX4 sweat and water resistance.'
    ],
    [
        'category_id' => 2, // Chargers
        'sku' => 'ACC-CHG-010',
        'name' => 'Apple MagSafe Fast Wireless Charger (15W Magnetic Puck)',
        'brand' => 'Apple',
        'cost_price' => 380.00,
        'selling_price' => 780.00,
        'stock_quantity' => 25,
        'reorder_level' => 8,
        'image' => 'apple_magsafe_charger.png',
        'description' => 'Perfect magnetic alignment for iPhone 12, 13, 14, 15 series. Fast wireless charging up to 15W with integrated 1-meter USB-C cable.'
    ],
    [
        'category_id' => 4, // Powerbanks
        'sku' => 'ACC-PWR-011',
        'name' => 'Apple MagSafe Magnetic Battery Pack 5000mAh',
        'brand' => 'Apple',
        'cost_price' => 620.00,
        'selling_price' => 1250.00,
        'stock_quantity' => 14,
        'reorder_level' => 4,
        'image' => 'apple_magsafe_battery.png',
        'description' => 'Compact, intuitive design snaps on automatically for easy on-the-go wireless charging. Safe pass-through charging with 20W adapter.'
    ],
    [
        'category_id' => 1, // Cases & Accessories / Wearables
        'sku' => 'ACC-DEV-012',
        'name' => 'Apple Watch Ultra 2 GPS + Cellular 49mm Titanium',
        'brand' => 'Apple',
        'cost_price' => 28000.00,
        'selling_price' => 34500.00,
        'stock_quantity' => 3,
        'reorder_level' => 1,
        'image' => 'apple_watch_ultra.png',
        'description' => 'Rugged 49mm aerospace titanium case, 3000-nit bright always-on display, dual-frequency precision GPS, and up to 72 hours in low power mode.'
    ]
];

$p_stmt = $pdo->prepare("INSERT INTO `products` 
    (`category_id`, `sku`, `name`, `brand`, `cost_price`, `selling_price`, `stock_quantity`, `reorder_level`, `image`, `description`) 
    VALUES (:category_id, :sku, :name, :brand, :cost_price, :selling_price, :stock_quantity, :reorder_level, :image, :description)
    ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `selling_price`=VALUES(`selling_price`), `image`=VALUES(`image`), `stock_quantity`=VALUES(`stock_quantity`)");

foreach ($new_products as $p) {
    $p_stmt->execute($p);
    echo " + Accessory: {$p['name']} -> {$p['image']}\n";
}

echo "✅ All HD Apple products and units successfully applied to database!\n";
