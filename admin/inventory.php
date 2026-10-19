<?php
$page_title = "Inventory Management (Accessories & IMEI)";
require_once __DIR__ . '/../config/db.php';
require_role('admin', 'staff');

$message = '';

// Handle Add Accessory
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_product') {
    $category_id = (int)($_POST['category_id'] ?? 1);
    $sku = sanitize($_POST['sku'] ?? 'ACC-' . time());
    $name = sanitize($_POST['name'] ?? '');
    $brand = sanitize($_POST['brand'] ?? '');
    $cost_price = (float)($_POST['cost_price'] ?? 0);
    $selling_price = (float)($_POST['selling_price'] ?? 0);
    $stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
    $reorder_level = (int)($_POST['reorder_level'] ?? 5);
    $description = sanitize($_POST['description'] ?? '');

    try {
        $stmt = $pdo->prepare("INSERT INTO products (category_id, sku, name, brand, cost_price, selling_price, stock_quantity, reorder_level, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$category_id, $sku, $name, $brand, $cost_price, $selling_price, $stock_quantity, $reorder_level, $description]);
        $message = "Accessory added successfully!";
    } catch (Exception $e) {
        $message = "Error adding product: " . $e->getMessage();
    }
}

// Handle Add Phone Unit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_unit') {
    $brand = sanitize($_POST['brand'] ?? '');
    $model_name = sanitize($_POST['model_name'] ?? '');
    $storage = sanitize($_POST['storage_capacity'] ?? '128GB');
    $color = sanitize($_POST['color'] ?? 'Black');
    $imei_1 = sanitize($_POST['imei_1'] ?? '');
    $imei_2 = sanitize($_POST['imei_2'] ?? '');
    $condition = sanitize($_POST['unit_condition'] ?? 'Brand New');
    $battery_health = (int)($_POST['battery_health'] ?? 100);
    $buy_price = (float)($_POST['buy_price'] ?? 0);
    $selling_price = (float)($_POST['selling_price'] ?? 0);
    $notes = sanitize($_POST['notes'] ?? '');

    try {
        $stmt = $pdo->prepare("INSERT INTO phone_units (brand, model_name, storage_capacity, color, imei_1, imei_2, unit_condition, battery_health, buy_price, selling_price, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$brand, $model_name, $storage, $color, $imei_1, $imei_2, $condition, $battery_health, $buy_price, $selling_price, $notes]);
        $message = "Phone unit with IMEI registered successfully!";
    } catch (Exception $e) {
        $message = "Error adding phone unit: " . $e->getMessage();
    }
}

// Handle Stock Quick Adjustment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'adjust_stock') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $qty_change = (int)($_POST['quantity_change'] ?? 0);
    if ($product_id > 0 && $qty_change != 0) {
        try {
            $upd = $pdo->prepare("UPDATE products SET stock_quantity = GREATEST(0, stock_quantity + ?) WHERE id = ?");
            $upd->execute([$qty_change, $product_id]);
            $message = "Stock level updated successfully (" . ($qty_change > 0 ? "+{$qty_change}" : "{$qty_change}") . " units)!";
        } catch (Exception $e) {
            $message = "Error adjusting stock: " . $e->getMessage();
        }
    }
}

// Fetch categories
$categories = $pdo->query("SELECT * FROM categories WHERE type = 'accessory'")->fetchAll();

// Fetch products
$products = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC")->fetchAll();

// Fetch phone units
$phone_units = $pdo->query("SELECT * FROM phone_units ORDER BY id DESC")->fetchAll();

$low_stock_accessories = array_filter($products, fn($p) => $p['stock_quantity'] <= $p['reorder_level']);
$low_stock_count = count($low_stock_accessories);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h2 class="h3 fw-bold mb-1"><i class="fa-solid fa-boxes-stacked text-primary me-2"></i>Inventory Control & Tracking</h2>
                    <p class="text-muted small mb-0">Manage retail accessories stock levels and individually serial/IMEI tracked phone units</p>
                </div>
                <div class="btn-group">
                    <button class="btn btn-primary btn-sm rounded-pill px-3 me-2" data-bs-toggle="modal" data-bs-target="#addAccessoryModal">
                        <i class="fa-solid fa-plus me-1"></i> Add Accessory
                    </button>
                    <button class="btn btn-outline-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addPhoneModal">
                        <i class="fa-solid fa-mobile me-1"></i> Add Phone (IMEI)
                    </button>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert <?= strpos($message, 'Error') !== false ? 'alert-danger' : 'alert-success' ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($low_stock_count > 0): ?>
                <div class="alert alert-dark d-flex justify-content-between align-items-center mb-4 p-3 rounded-3 shadow-sm border border-dark animate-fade-in-up">
                    <div>
                        <i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>
                        <strong>Inventory Reorder Notice:</strong> 
                        <span><?= $low_stock_count ?> accessory items are at or below minimum reorder threshold.</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3 fw-bold" onclick="filterLowStock()">
                        Filter Low Stock
                    </button>
                </div>
            <?php endif; ?>

            <!-- Navigation Tabs -->
            <ul class="nav nav-pills mb-4" id="inventoryTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active fw-bold" id="accessories-tab" data-bs-toggle="pill" data-bs-target="#accessories-pane">
                        <i class="fa-solid fa-headphones me-2"></i>Accessories & Parts (<?= count($products) ?>)
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold" id="phones-tab" data-bs-toggle="pill" data-bs-target="#phones-pane">
                        <i class="fa-solid fa-mobile-screen me-2"></i>Smartphones & Units (<?= count($phone_units) ?>)
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="inventoryTabContent">
                <!-- PANE 1: ACCESSORIES -->
                <div class="tab-pane fade show active" id="accessories-pane">
                    <div class="card card-custom p-3 mb-3">
                        <div class="row g-2">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                                    <input type="text" id="accSearchInput" class="form-control border-start-0" placeholder="Search accessories by name, brand, or SKU..." onkeyup="filterAccessories()">
                                </div>
                            </div>
                            <div class="col-md-4 text-md-end d-flex gap-2 justify-content-md-end">
                                <button class="btn btn-outline-dark btn-sm rounded-pill px-3" onclick="resetAccFilter()">All Items</button>
                                <button class="btn btn-dark btn-sm rounded-pill px-3" onclick="filterLowStock()">Low Stock (<?= $low_stock_count ?>)</button>
                            </div>
                        </div>
                    </div>

                    <div class="card card-custom">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="accessoriesTable" style="font-size: 0.88rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th>SKU</th>
                                            <th>Product Name</th>
                                            <th>Category</th>
                                            <th class="text-end">Cost</th>
                                            <th class="text-end">Selling Price</th>
                                            <th class="text-center">Stock</th>
                                            <th>Status</th>
                                            <th class="text-center">Restock (+Qty)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($products as $prod): ?>
                                            <tr class="acc-row" data-name="<?= strtolower(htmlspecialchars($prod['name'] . ' ' . $prod['brand'] . ' ' . $prod['sku'])) ?>" data-low="<?= ($prod['stock_quantity'] <= $prod['reorder_level']) ? '1' : '0' ?>">
                                                <td><code><?= htmlspecialchars($prod['sku']) ?></code></td>
                                                <td>
                                                    <div class="fw-bold"><?= htmlspecialchars($prod['name']) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($prod['brand']) ?></small>
                                                </td>
                                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($prod['category_name'] ?? 'General') ?></span></td>
                                                <td class="text-end text-muted"><?= format_currency($prod['cost_price']) ?></td>
                                                <td class="text-end fw-bold text-dark"><?= format_currency($prod['selling_price']) ?></td>
                                                <td class="text-center">
                                                    <?php if ($prod['stock_quantity'] <= $prod['reorder_level']): ?>
                                                        <span class="badge bg-dark text-white fw-bold fs-6 px-2"><?= $prod['stock_quantity'] ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-dark border fw-bold fs-6 px-2"><?= $prod['stock_quantity'] ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($prod['stock_quantity'] == 0): ?>
                                                        <span class="badge bg-dark text-white">Out of Stock</span>
                                                    <?php elseif ($prod['stock_quantity'] <= $prod['reorder_level']): ?>
                                                        <span class="badge bg-light text-dark border border-dark">Low Stock (≤ <?= $prod['reorder_level'] ?>)</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-dark border">In Stock</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm">
                                                        <form method="POST" style="display:inline;">
                                                            <input type="hidden" name="action" value="adjust_stock">
                                                            <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                                                            <input type="hidden" name="quantity_change" value="5">
                                                            <button type="submit" class="btn btn-outline-dark btn-sm py-0 px-2" title="Restock +5 units">+5</button>
                                                        </form>
                                                        <form method="POST" style="display:inline;">
                                                            <input type="hidden" name="action" value="adjust_stock">
                                                            <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                                                            <input type="hidden" name="quantity_change" value="10">
                                                            <button type="submit" class="btn btn-outline-dark btn-sm py-0 px-2" title="Restock +10 units">+10</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PANE 2: PHONE UNITS (WITH IMEI) -->
                <div class="tab-pane fade" id="phones-pane">
                    <div class="card card-custom p-3 mb-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                            <input type="text" id="phoneSearchInput" class="form-control border-start-0" placeholder="Search phone units by model, brand, IMEI or condition..." onkeyup="filterPhoneUnits()">
                        </div>
                    </div>

                    <div class="card card-custom">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="phoneUnitsTable" style="font-size: 0.88rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Device Details</th>
                                            <th>IMEI 1 / Serial</th>
                                            <th>Condition</th>
                                            <th>Battery</th>
                                            <th class="text-end">Buy In Cost</th>
                                            <th class="text-end">Selling Price</th>
                                            <th>Source</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($phone_units as $unit): ?>
                                            <tr class="phone-row" data-name="<?= strtolower(htmlspecialchars($unit['brand'] . ' ' . $unit['model_name'] . ' ' . $unit['imei_1'] . ' ' . $unit['unit_condition'])) ?>">
                                                <td>
                                                    <div class="fw-bold"><?= htmlspecialchars($unit['brand'] . ' ' . $unit['model_name']) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($unit['storage_capacity']) ?> • <?= htmlspecialchars($unit['color']) ?></small>
                                                </td>
                                                <td>
                                                    <code><?= htmlspecialchars($unit['imei_1']) ?></code>
                                                    <?php if (!empty($unit['imei_2'])): ?>
                                                        <br><small class="text-muted">Sim 2: <?= htmlspecialchars($unit['imei_2']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($unit['unit_condition']) ?></span></td>
                                                <td>
                                                    <?php if ($unit['battery_health']): ?>
                                                        <span class="badge bg-light text-dark border"><i class="fa-solid fa-battery-half me-1"></i><?= $unit['battery_health'] ?>%</span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end text-muted"><?= format_currency($unit['buy_price']) ?></td>
                                                <td class="text-end fw-bold text-dark"><?= format_currency($unit['selling_price']) ?></td>
                                                <td><span class="badge bg-light text-secondary border"><?= ucwords(str_replace('_', ' ', $unit['source'])) ?></span></td>
                                                <td>
                                                    <?php if ($unit['status'] === 'in_stock'): ?>
                                                        <span class="badge bg-dark text-white">In Stock</span>
                                                    <?php elseif ($unit['status'] === 'traded'): ?>
                                                        <span class="badge bg-light text-dark border">Traded</span>
                                                    <?php elseif ($unit['status'] === 'sold'): ?>
                                                        <span class="badge bg-light text-secondary border">Sold</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-dark border"><?= ucfirst($unit['status']) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal: Add Accessory -->
<div class="modal fade" id="addAccessoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="action" value="add_product">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add Retail Accessory</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Category</label>
                        <select name="category_id" class="form-select" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">SKU / Barcode</label>
                            <input type="text" name="sku" class="form-control" value="ACC-<?= rand(100, 999) ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Brand</label>
                            <input type="text" name="brand" class="form-control" placeholder="e.g. Bavin / Baseus">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Product Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. 20W Fast Charger Adapter" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Cost Price (₱)</label>
                            <input type="number" step="0.01" name="cost_price" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Selling Price (₱)</label>
                            <input type="number" step="0.01" name="selling_price" class="form-control" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Initial Stock</label>
                            <input type="number" name="stock_quantity" class="form-control" value="10" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Reorder Alert Level</label>
                            <input type="number" name="reorder_level" class="form-control" value="5" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Accessory</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Add Phone Unit -->
<div class="modal fade" id="addPhoneModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="action" value="add_unit">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Register Smartphone Unit (IMEI Tracking)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <label class="form-label small fw-semibold">Brand</label>
                            <input type="text" name="brand" class="form-control" placeholder="Apple, Samsung, Xiaomi" required>
                        </div>
                        <div class="col-5">
                            <label class="form-label small fw-semibold">Model Name</label>
                            <input type="text" name="model_name" class="form-control" placeholder="iPhone 13 / S22" required>
                        </div>
                        <div class="col-3">
                            <label class="form-label small fw-semibold">Storage</label>
                            <input type="text" name="storage_capacity" class="form-control" placeholder="128GB" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">IMEI 1 (Primary)</label>
                            <input type="text" name="imei_1" class="form-control" placeholder="15-digit hardware IMEI" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">IMEI 2 (Optional / Dual SIM)</label>
                            <input type="text" name="imei_2" class="form-control" placeholder="Secondary IMEI">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <label class="form-label small fw-semibold">Condition</label>
                            <select name="unit_condition" class="form-select">
                                <option value="Brand New">Brand New</option>
                                <option value="Pre-Owned Grade A">Pre-Owned Grade A</option>
                                <option value="Pre-Owned Grade B">Pre-Owned Grade B</option>
                                <option value="Pre-Owned Grade C">Pre-Owned Grade C</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold">Color</label>
                            <input type="text" name="color" class="form-control" placeholder="Midnight / Black">
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold">Battery Health (%)</label>
                            <input type="number" name="battery_health" class="form-control" value="95" min="50" max="100">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Buy Price / Supplier Cost (₱)</label>
                            <input type="number" step="0.01" name="buy_price" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Selling Price (₱)</label>
                            <input type="number" step="0.01" name="selling_price" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Register Unit to Inventory</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function filterAccessories() {
    let q = (document.getElementById('accSearchInput').value || '').toLowerCase();
    document.querySelectorAll('.acc-row').forEach(row => {
        let text = row.dataset.name || '';
        row.style.display = text.includes(q) ? '' : 'none';
    });
}

function filterLowStock() {
    document.querySelectorAll('.acc-row').forEach(row => {
        row.style.display = (row.dataset.low === '1') ? '' : 'none';
    });
    const accTab = document.getElementById('accessories-tab');
    if (accTab) {
        const tab = new bootstrap.Tab(accTab);
        tab.show();
    }
}

function resetAccFilter() {
    document.getElementById('accSearchInput').value = '';
    document.querySelectorAll('.acc-row').forEach(row => {
        row.style.display = '';
    });
}

function filterPhoneUnits() {
    let q = (document.getElementById('phoneSearchInput').value || '').toLowerCase();
    document.querySelectorAll('.phone-row').forEach(row => {
        let text = row.dataset.name || '';
        row.style.display = text.includes(q) ? '' : 'none';
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
