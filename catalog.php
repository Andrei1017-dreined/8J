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
    $p_sql .= " AND (p.name LIKE " . $pdo->quote("%{$search}%") . " OR p.brand LIKE " . $pdo->quote("%{$search}%") . " OR p.description LIKE " . $pdo->quote("%{$search}%") . ")";
}
$p_sql .= " ORDER BY (p.brand = 'Apple') DESC, p.id DESC";
$products = $pdo->query($p_sql)->fetchAll();

// Phone Units Query
$u_sql = "SELECT * FROM phone_units WHERE status = 'in_stock'";
if (!empty($search)) {
    $u_sql .= " AND (brand LIKE " . $pdo->quote("%{$search}%") . " OR model_name LIKE " . $pdo->quote("%{$search}%") . " OR color LIKE " . $pdo->quote("%{$search}%") . " OR storage_capacity LIKE " . $pdo->quote("%{$search}%") . ")";
}
$u_sql .= " ORDER BY (brand = 'Apple') DESC, id DESC";
$phone_units = $pdo->query($u_sql)->fetchAll();
?>

<div class="container py-4">
    <!-- Header Banner -->
    <div class="card card-custom p-4 mb-4 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #2d1122 0%, #4a1534 65%, #831843 100%); border: 1px solid #831843;">
        <div class="row align-items-center position-relative" style="z-index: 2;">
            <div class="col-md-8">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2" style="background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2);">
                    <span class="pulse-indicator green"></span>
                    <span class="small fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Live Store Inventory • Zero Reservation Deposit</span>
                </div>
                <h3 class="fw-extrabold mb-1">
                    <img src="<?= BASE_URL ?>assets/img/icons/shop.png" style="width: 28px; height: 28px; object-fit: contain; filter: brightness(0) invert(1);" alt="Catalog" class="me-2">Online Store Catalog
                </h3>
                <p class="mb-0 text-white-50 small">Reserve genuine Apple &amp; Android accessories or 25-point certified smartphones online for same-day pick-up at Victory Mall Tanauan!</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="<?= BASE_URL ?>cart.php" class="btn btn-light fw-bold rounded-pill px-4 shadow-sm" style="background-color: #fce7f3 !important; color: #831843 !important; border-color: #f472b6 !important;">
                    <img src="<?= BASE_URL ?>assets/img/icons/cart.png" style="width: 18px; height: 18px; object-fit: contain;" alt="Cart" class="me-1"> View Pick-Up Cart
                </a>
            </div>
        </div>
    </div>

    <!-- Interactive Live Filter, Search & Sort Control Bar -->
    <div class="card card-custom p-3 p-md-4 mb-4">
        <form method="GET" action="" id="catalogFilterForm" class="row g-2 align-items-center">
            <div class="col-lg-4 col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-primary"></i></span>
                    <input type="text" name="q" id="liveCatalogSearch" class="form-control border-start-0" placeholder="Instant search iPhone 15, AirPods, MagSafe, Infinix... (/)" value="<?= htmlspecialchars($search) ?>" autocomplete="off">
                    <?php if (!empty($search)): ?>
                        <a href="<?= BASE_URL ?>catalog.php" class="btn btn-outline-secondary border-start-0" title="Clear search"><i class="fa-solid fa-xmark"></i></a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-2 col-md-3 col-6">
                <select name="type" class="form-select" onchange="this.form.submit()">
                    <option value="all" <?= $type_filter === 'all' ? 'selected' : '' ?>>All Inventory</option>
                    <option value="accessories" <?= $type_filter === 'accessories' ? 'selected' : '' ?>>Accessories Only</option>
                    <option value="units" <?= $type_filter === 'units' ? 'selected' : '' ?>>Smartphones Only</option>
                </select>
            </div>
            <div class="col-lg-3 col-md-3 col-6">
                <select name="category" class="form-select" onchange="this.form.submit()">
                    <option value="0">All Accessory Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $cat_filter == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-2 col-md-8 col-8">
                <select id="liveCatalogSort" class="form-select" onchange="applyLiveCatalogFilter()">
                    <option value="featured">Sort: Featured &amp; Apple First</option>
                    <option value="price_asc">Price: Low to High</option>
                    <option value="price_desc">Price: High to Low</option>
                    <option value="name_asc">Name: A to Z</option>
                </select>
            </div>
            <div class="col-lg-1 col-md-4 col-4">
                <button type="submit" class="btn btn-primary w-100 rounded fw-bold">Go</button>
            </div>
        </form>

        <!-- Quick Brand Shortcuts -->
        <div class="d-flex flex-wrap gap-2 align-items-center mt-3 pt-3 border-top">
            <span class="small text-muted fw-semibold me-1"><i class="fa-solid fa-sliders me-1 text-primary"></i>Brands:</span>
            <a href="<?= BASE_URL ?>catalog.php" class="btn btn-sm rounded-pill px-3 <?= (empty($search) && $type_filter === 'all') ? 'btn-dark' : 'btn-outline-secondary' ?>" style="font-size: 0.78rem;">
                All Items
            </a>
            <a href="<?= BASE_URL ?>catalog.php?q=Apple" class="btn btn-sm rounded-pill px-3 <?= (strcasecmp($search, 'Apple') === 0) ? 'btn-primary' : 'btn-outline-primary' ?>" style="font-size: 0.78rem;">
                <i class="fa-brands fa-apple me-1"></i> Apple / iPhone
            </a>
            <a href="<?= BASE_URL ?>catalog.php?q=Samsung" class="btn btn-sm rounded-pill px-3 <?= (strcasecmp($search, 'Samsung') === 0) ? 'btn-primary' : 'btn-outline-secondary' ?>" style="font-size: 0.78rem;">
                Samsung
            </a>
            <a href="<?= BASE_URL ?>catalog.php?q=Infinix" class="btn btn-sm rounded-pill px-3 <?= (strcasecmp($search, 'Infinix') === 0) ? 'btn-primary' : 'btn-outline-secondary' ?>" style="font-size: 0.78rem;">
                Infinix
            </a>
            <a href="<?= BASE_URL ?>catalog.php?q=Tecno" class="btn btn-sm rounded-pill px-3 <?= (strcasecmp($search, 'Tecno') === 0) ? 'btn-primary' : 'btn-outline-secondary' ?>" style="font-size: 0.78rem;">
                Tecno
            </a>
            <a href="<?= BASE_URL ?>catalog.php?q=Xiaomi" class="btn btn-sm rounded-pill px-3 <?= (strcasecmp($search, 'Xiaomi') === 0) ? 'btn-primary' : 'btn-outline-secondary' ?>" style="font-size: 0.78rem;">
                Xiaomi
            </a>
            <a href="<?= BASE_URL ?>catalog.php?type=units" class="btn btn-sm rounded-pill px-3 ms-auto <?= ($type_filter === 'units') ? 'btn-dark' : 'btn-outline-dark' ?>" style="font-size: 0.78rem;">
                <i class="fa-solid fa-mobile-screen me-1"></i> Phones Only
            </a>
            <a href="<?= BASE_URL ?>catalog.php?type=accessories" class="btn btn-sm rounded-pill px-3 <?= ($type_filter === 'accessories') ? 'btn-dark' : 'btn-outline-dark' ?>" style="font-size: 0.78rem;">
                <i class="fa-solid fa-headphones me-1"></i> Accessories Only
            </a>
        </div>
    </div>

    <!-- ACCESSORIES SECTION -->
    <?php if ($type_filter !== 'units'): ?>
        <div class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h4 class="fw-bold mb-0"><i class="fa-solid fa-headphones me-2 text-primary"></i>Accessories &amp; Charging Essentials</h4>
                <span class="badge bg-light text-dark border rounded-pill px-3 py-1.5" id="accCountBadge"><?= count($products) ?> items</span>
            </div>
            <div class="row g-3" id="catalogAccessoriesGrid">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $idx => $p): 
                        $p_img = '';
                        if (!empty($p['image']) && file_exists(__DIR__ . '/assets/img/products/' . $p['image'])) {
                            $p_img = BASE_URL . 'assets/img/products/' . $p['image'];
                        }
                        $p_brand = $p['brand'] ?? 'Accessory';
                        $p_ad = get_product_ad_video($p['name'], $p_brand);
                    ?>
                        <div class="col-6 col-md-4 col-lg-3 catalog-card-col"
                             data-name="<?= htmlspecialchars(strtolower($p['name'] . ' ' . $p_brand . ' ' . ($p['description'] ?? ''))) ?>"
                             data-price="<?= (float)$p['selling_price'] ?>"
                             data-order="<?= $idx ?>">
                            <div class="card h-100 card-custom stat-card overflow-hidden">
                                <a href="<?= BASE_URL ?>product_detail.php?id=<?= $p['id'] ?>" class="bg-light p-3 text-center position-relative d-flex align-items-center justify-content-center text-decoration-none overflow-hidden" style="height: 165px; background: radial-gradient(circle, #ffffff 55%, #fdf2f8 100%);">
                                    <!-- Outsourced Commercial Video Ad (Streams on Hover) -->
                                    <video class="product-hover-video" muted playsinline preload="none" data-ad-src="<?= htmlspecialchars($p_ad['hover']) ?>" data-ad-next="<?= htmlspecialchars($p_ad['hover_next'] ?? '') ?>"></video>

                                    <?php if (strcasecmp($p_brand, 'Apple') === 0): ?>
                                        <span class="badge bg-dark text-white position-absolute top-0 start-0 m-2 px-2 py-1 rounded-pill" style="font-size: 0.65rem; z-index: 4;">
                                            <i class="fa-brands fa-apple me-1"></i>Apple
                                        </span>
                                    <?php endif; ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle position-absolute top-0 end-0 m-2 px-2 py-0.5 rounded-pill" style="font-size: 0.65rem; z-index: 4;">
                                        <?= (int)$p['stock_quantity'] ?> in stock
                                    </span>
                                    <?php if (!empty($p_img)): ?>
                                        <img src="<?= $p_img ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy" decoding="async" class="product-thumb" style="max-height: 130px; max-width: 100%; object-fit: contain; transition: transform 0.3s ease, opacity 0.3s ease; filter: drop-shadow(0 8px 14px rgba(0,0,0,0.08));">
                                    <?php else: ?>
                                        <i class="fa-solid fa-plug-circle-bolt fs-2 text-primary opacity-50"></i>
                                    <?php endif; ?>
                                </a>
                                <div class="card-body d-flex flex-column">
                                    <div class="badge bg-light text-secondary border align-self-start mb-2" style="font-size: 0.68rem;">
                                        <?= htmlspecialchars($p_brand) ?>
                                    </div>
                                    <h6 class="fw-bold mb-1 text-dark">
                                        <a href="<?= BASE_URL ?>product_detail.php?id=<?= $p['id'] ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($p['name']) ?></a>
                                    </h6>
                                    <p class="text-muted small mb-2 text-truncate" title="<?= htmlspecialchars($p['description']) ?>">
                                        <?= htmlspecialchars($p['description'] ?? 'Quality accessory') ?>
                                    </p>
                                    <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center gap-1">
                                        <span class="fw-extrabold text-primary fs-5"><?= format_currency($p['selling_price']) ?></span>
                                        <div class="d-flex gap-1">
                                            <a href="<?= BASE_URL ?>product_detail.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-dark rounded-pill px-2.5" title="View Full Specifications">
                                                <i class="fa-solid fa-circle-info"></i>
                                            </a>
                                            <?php if ($p['stock_quantity'] > 0): ?>
                                                <button class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold" onclick="addToCart(<?= $p['id'] ?>, '<?= addslashes(htmlspecialchars($p['name'])) ?>', <?= $p['selling_price'] ?>, 'product')">
                                                    <i class="fa-solid fa-plus me-1"></i> Add
                                                </button>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Out of Stock</span>
                                            <?php endif; ?>
                                        </div>
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
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h4 class="fw-bold mb-0"><i class="fa-solid fa-mobile-screen me-2 text-primary"></i>Available Smartphones &amp; Certified Units</h4>
                <span class="badge bg-light text-dark border rounded-pill px-3 py-1.5" id="phoneCountBadge"><?= count($phone_units) ?> units</span>
            </div>
            <div class="row g-3" id="catalogPhonesGrid">
                <?php if (!empty($phone_units)): ?>
                    <?php foreach ($phone_units as $idx => $u): 
                        $u_img = '';
                        if (!empty($u['image']) && file_exists(__DIR__ . '/assets/img/products/' . $u['image'])) {
                            $u_img = BASE_URL . 'assets/img/products/' . $u['image'];
                        }
                        $u_ad = get_product_ad_video($u['model_name'], $u['brand']);
                    ?>
                        <div class="col-md-6 col-lg-4 catalog-card-col"
                             data-name="<?= htmlspecialchars(strtolower($u['brand'] . ' ' . $u['model_name'] . ' ' . $u['storage_capacity'] . ' ' . $u['color'] . ' ' . ($u['notes'] ?? ''))) ?>"
                             data-price="<?= (float)$u['selling_price'] ?>"
                             data-order="<?= $idx ?>">
                            <div class="card h-100 card-custom stat-card border overflow-hidden">
                                <a href="<?= BASE_URL ?>product_detail.php?type=unit&id=<?= $u['id'] ?>" class="bg-light p-3 text-center position-relative d-flex align-items-center justify-content-center text-decoration-none overflow-hidden" style="height: 175px; background: radial-gradient(circle, #ffffff 55%, #fdf2f8 100%);">
                                    <!-- Outsourced Commercial Video Ad (Streams on Hover) -->
                                    <video class="product-hover-video" muted playsinline preload="none" data-ad-src="<?= htmlspecialchars($u_ad['hover']) ?>" data-ad-next="<?= htmlspecialchars($u_ad['hover_next'] ?? '') ?>"></video>

                                    <?php if (strcasecmp($u['brand'], 'Apple') === 0): ?>
                                        <span class="badge bg-dark text-white position-absolute top-0 start-0 m-2 px-2.5 py-1 rounded-pill" style="font-size: 0.65rem; z-index: 4;">
                                            <i class="fa-brands fa-apple me-1"></i>iPhone
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($u_img)): ?>
                                        <img src="<?= $u_img ?>" alt="<?= htmlspecialchars($u['model_name']) ?>" loading="lazy" decoding="async" class="product-thumb" style="max-height: 145px; max-width: 100%; object-fit: contain; transition: transform 0.3s ease, opacity 0.3s ease; filter: drop-shadow(0 10px 18px rgba(0,0,0,0.1));">
                                    <?php else: ?>
                                        <i class="fa-solid fa-mobile-screen fs-1 text-primary opacity-50"></i>
                                    <?php endif; ?>
                                </a>
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge bg-info-subtle text-info-emphasis border"><?= htmlspecialchars($u['brand']) ?></span>
                                        <span class="badge bg-success-subtle text-success-emphasis border"><?= htmlspecialchars($u['unit_condition']) ?></span>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-1">
                                        <a href="<?= BASE_URL ?>product_detail.php?type=unit&id=<?= $u['id'] ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($u['model_name']) ?></a>
                                    </h5>
                                    <div class="text-muted small mb-2">
                                        <span class="fw-semibold text-dark"><?= htmlspecialchars($u['storage_capacity']) ?></span> • 
                                        <span><?= htmlspecialchars($u['color']) ?></span>
                                        <?php if ($u['battery_health']): ?>
                                            • <span class="text-success fw-semibold"><i class="fa-solid fa-battery-three-quarters me-1"></i><?= $u['battery_health'] ?>%</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-muted small mb-3"><?= htmlspecialchars($u['notes'] ?? 'Fully tested and inspected unit.') ?></p>
                                    <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center gap-1">
                                        <div>
                                            <span class="text-muted small d-block" style="font-size:0.72rem;">Store Pick-Up Price</span>
                                            <span class="fw-extrabold text-primary fs-5"><?= format_currency($u['selling_price']) ?></span>
                                        </div>
                                        <div class="d-flex gap-1">
                                            <a href="<?= BASE_URL ?>product_detail.php?type=unit&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-dark rounded-pill px-2.5" title="View Full Diagnostic & Specs">
                                                <i class="fa-solid fa-circle-info"></i>
                                            </a>
                                            <button class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold" onclick="addToCart(<?= $u['id'] ?>, '<?= addslashes(htmlspecialchars($u['brand'] . ' ' . $u['model_name'] . ' ' . $u['storage_capacity'])) ?>', <?= $u['selling_price'] ?>, 'phone_unit')">
                                                <i class="fa-solid fa-cart-arrow-down me-1"></i> Reserve
                                            </button>
                                        </div>
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

function applyLiveCatalogFilter() {
    const searchInput = document.getElementById('liveCatalogSearch');
    const sortSelect = document.getElementById('liveCatalogSort');
    const query = (searchInput ? searchInput.value : '').trim().toLowerCase();
    const sortBy = sortSelect ? sortSelect.value : 'featured';

    [
        { gridId: 'catalogAccessoriesGrid', badgeId: 'accCountBadge', suffix: ' items' },
        { gridId: 'catalogPhonesGrid', badgeId: 'phoneCountBadge', suffix: ' units' }
    ].forEach(section => {
        const grid = document.getElementById(section.gridId);
        if (!grid) return;

        const cols = Array.from(grid.querySelectorAll('.catalog-card-col'));
        let visibleCount = 0;

        cols.forEach(col => {
            const hay = col.dataset.name || '';
            const match = !query || hay.includes(query);
            col.style.display = match ? '' : 'none';
            if (match) visibleCount++;
        });

        cols.sort((a, b) => {
            if (sortBy === 'price_asc') {
                return (parseFloat(a.dataset.price) || 0) - (parseFloat(b.dataset.price) || 0);
            } else if (sortBy === 'price_desc') {
                return (parseFloat(b.dataset.price) || 0) - (parseFloat(a.dataset.price) || 0);
            } else if (sortBy === 'name_asc') {
                return (a.dataset.name || '').localeCompare(b.dataset.name || '');
            }
            return (parseInt(a.dataset.order) || 0) - (parseInt(b.dataset.order) || 0);
        });

        cols.forEach(col => grid.appendChild(col));

        const badge = document.getElementById(section.badgeId);
        if (badge) {
            badge.textContent = visibleCount + section.suffix;
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('liveCatalogSearch');
    if (searchInput) {
        searchInput.addEventListener('input', applyLiveCatalogFilter);
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
