<?php
$page_title = "Online Catalog - Accessories & Phones";
require_once __DIR__ . '/includes/header.php';

$type_filter = sanitize($_GET['type'] ?? 'all');
$cat_filter = (int)($_GET['category'] ?? 0);
$search = sanitize($_GET['q'] ?? '');

// Fetch Categories
$categories = $pdo->query("SELECT * FROM categories WHERE type = 'accessory'")->fetchAll();

// Products Query
$p_sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status != 'archived'";
if ($cat_filter > 0) {
    $p_sql .= " AND p.category_id = {$cat_filter}";
}
if (!empty($search)) {
    $p_sql .= " AND (p.name LIKE " . $pdo->quote("%{$search}%") . " OR p.brand LIKE " . $pdo->quote("%{$search}%") . ")";
}
$p_sql .= " ORDER BY p.id DESC";
$products = $pdo->query($p_sql)->fetchAll();

// Phone Units Query
$u_sql = "SELECT * FROM phone_units WHERE status = 'in_stock'";
if (!empty($search)) {
    $u_sql .= " AND (brand LIKE " . $pdo->quote("%{$search}%") . " OR model_name LIKE " . $pdo->quote("%{$search}%") . ")";
}
$u_sql .= " ORDER BY id DESC";
$phone_units = $pdo->query($u_sql)->fetchAll();
?>

<div class="container py-4">
    <!-- Header banner -->
    <div class="card card-custom p-4 mb-4 text-white" style="background: linear-gradient(135deg, #09090b 0%, #18181b 100%); border: 1px solid #27272a;">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3 class="fw-bold mb-1"><img src="<?= BASE_URL ?>assets/img/icons/shop.png" style="width: 28px; height: 28px; object-fit: contain; filter: brightness(0) invert(1);" alt="Catalog" class="me-2">Online Store Catalog</h3>
                <p class="mb-0 text-white-50">Reserve premium accessories and certified smartphones online for fast store pick-up at Tanauan City!</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="<?= BASE_URL ?>cart.php" class="btn btn-light fw-bold text-dark rounded-pill px-4 shadow-sm">
                    <img src="<?= BASE_URL ?>assets/img/icons/cart.png" style="width: 18px; height: 18px; object-fit: contain;" alt="Cart" class="me-1"> View Pick-Up Cart
                </a>
            </div>
        </div>
    </div>

    <!-- Filter and Search Bar -->
    <div class="card card-custom p-3 mb-4">
        <form method="GET" action="" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" name="q" class="form-control border-start-0" placeholder="Search brands, accessories, or phone models..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="type" class="form-select" onchange="this.form.submit()">
                    <option value="all" <?= $type_filter === 'all' ? 'selected' : '' ?>>All Inventory</option>
                    <option value="accessories" <?= $type_filter === 'accessories' ? 'selected' : '' ?>>Accessories Only</option>
                    <option value="units" <?= $type_filter === 'units' ? 'selected' : '' ?>>Smartphones Only</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="category" class="form-select" onchange="this.form.submit()">
                    <option value="0">All Accessory Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $cat_filter == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100 rounded">Filter</button>
            </div>
        </form>
    </div>

    <!-- ACCESSORIES SECTION -->
    <?php if ($type_filter !== 'units'): ?>
        <div class="mb-5">
            <h4 class="fw-bold mb-3"><i class="fa-solid fa-headphones me-2 text-primary"></i>Accessories & Charging Essentials</h4>
            <div class="row g-3">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $p): 
                        $p_img = '';
                        if (!empty($p['image']) && file_exists(__DIR__ . '/assets/img/products/' . $p['image'])) {
                            $p_img = BASE_URL . 'assets/img/products/' . $p['image'];
                        }
                    ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="card h-100 card-custom stat-card overflow-hidden">
                                <div class="bg-light p-3 text-center d-flex align-items-center justify-content-center" style="height: 140px; background: radial-gradient(circle, #ffffff 60%, #f4f4f5 100%);">
                                    <?php if (!empty($p_img)): ?>
                                        <img src="<?= $p_img ?>" alt="<?= htmlspecialchars($p['name']) ?>" style="max-height: 120px; max-width: 100%; object-fit: contain;">
                                    <?php else: ?>
                                        <i class="fa-solid fa-plug-circle-bolt fs-2 text-primary opacity-50"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <div class="badge bg-light text-secondary border align-self-start mb-2" style="font-size: 0.68rem;">
                                        <?= htmlspecialchars($p['brand'] ?? 'Accessory') ?>
                                    </div>
                                    <h6 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($p['name']) ?></h6>
                                    <p class="text-muted small mb-2 text-truncate" title="<?= htmlspecialchars($p['description']) ?>">
                                        <?= htmlspecialchars($p['description'] ?? 'Quality accessory') ?>
                                    </p>
                                    <div class="mt-auto pt-2 d-flex justify-content-between align-items-center">
                                        <span class="fw-extrabold text-primary fs-5"><?= format_currency($p['selling_price']) ?></span>
                                        <?php if ($p['stock_quantity'] > 0): ?>
                                            <button class="btn btn-sm btn-primary rounded-pill px-3" onclick="addToCart(<?= $p['id'] ?>, '<?= addslashes(htmlspecialchars($p['name'])) ?>', <?= $p['selling_price'] ?>, 'product')">
                                                <i class="fa-solid fa-plus me-1"></i> Add
                                            </button>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Out of Stock</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12"><div class="alert alert-light border text-center py-4 text-muted">No accessories match your filter criteria.</div></div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- PHONE UNITS SECTION -->
    <?php if ($type_filter !== 'accessories'): ?>
        <div>
            <h4 class="fw-bold mb-3"><i class="fa-solid fa-mobile-screen me-2 text-primary"></i>Available Smartphones & Certified Units</h4>
            <div class="row g-3">
                <?php if (!empty($phone_units)): ?>
                    <?php foreach ($phone_units as $u): 
                        $u_img = '';
                        if (!empty($u['image']) && file_exists(__DIR__ . '/assets/img/products/' . $u['image'])) {
                            $u_img = BASE_URL . 'assets/img/products/' . $u['image'];
                        }
                    ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 card-custom stat-card border overflow-hidden">
                                <div class="bg-light p-3 text-center d-flex align-items-center justify-content-center" style="height: 160px; background: radial-gradient(circle, #ffffff 60%, #f4f4f5 100%);">
                                    <?php if (!empty($u_img)): ?>
                                        <img src="<?= $u_img ?>" alt="<?= htmlspecialchars($u['model_name']) ?>" style="max-height: 140px; max-width: 100%; object-fit: contain;">
                                    <?php else: ?>
                                        <i class="fa-solid fa-mobile-screen fs-1 text-primary opacity-50"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge bg-info-subtle text-info-emphasis border"><?= htmlspecialchars($u['brand']) ?></span>
                                        <span class="badge bg-success-subtle text-success-emphasis border"><?= htmlspecialchars($u['unit_condition']) ?></span>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($u['model_name']) ?></h5>
                                    <div class="text-muted small mb-2">
                                        <span><?= htmlspecialchars($u['storage_capacity']) ?></span> • 
                                        <span><?= htmlspecialchars($u['color']) ?></span>
                                        <?php if ($u['battery_health']): ?>
                                            • <span class="text-success"><i class="fa-solid fa-battery-half me-1"></i><?= $u['battery_health'] ?>%</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-muted small mb-3"><?= htmlspecialchars($u['notes'] ?? 'Fully tested and inspected unit.') ?></p>
                                    <div class="mt-auto pt-2 d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="text-muted small d-block" style="font-size:0.75rem;">Price</span>
                                            <span class="fw-extrabold text-primary fs-5"><?= format_currency($u['selling_price']) ?></span>
                                        </div>
                                        <button class="btn btn-sm btn-primary rounded-pill px-3" onclick="addToCart(<?= $u['id'] ?>, '<?= addslashes(htmlspecialchars($u['brand'] . ' ' . $u['model_name'] . ' ' . $u['storage_capacity'])) ?>', <?= $u['selling_price'] ?>, 'phone_unit')">
                                            <i class="fa-solid fa-cart-arrow-down me-1"></i> Reserve for Pick-Up
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12"><div class="alert alert-light border text-center py-4 text-muted">No phone units currently in stock for this filter.</div></div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function addToCart(id, name, price, type) {
    let cart = JSON.parse(localStorage.getItem('8j_cart') || '[]');
    let existing = cart.find(item => item.id === id && item.type === type);
    if (existing) {
        if (type === 'phone_unit') {
            showToast('Item already in your Pick-Up cart!');
            return;
        }
        existing.quantity += 1;
    } else {
        cart.push({ id: id, name: name, price: price, type: type, quantity: 1 });
    }
    localStorage.setItem('8j_cart', JSON.stringify(cart));
    refreshCartBadge(true);
    showToast('Added ' + name + ' to Pick-Up Cart!');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
