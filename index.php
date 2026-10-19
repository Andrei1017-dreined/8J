<?php
$page_title = "Home - 8J Cellphone Accessories";
require_once __DIR__ . '/includes/header.php';

// Fetch featured products
$featured_products = [];
$featured_units = [];
try {
    $stmt = $pdo->query("SELECT * FROM products WHERE status = 'available' ORDER BY id DESC LIMIT 4");
    $featured_products = $stmt->fetchAll();

    $stmt2 = $pdo->query("SELECT * FROM phone_units WHERE status = 'in_stock' ORDER BY id DESC LIMIT 4");
    $featured_units = $stmt2->fetchAll();
} catch (Exception $e) {
    // Handled gracefully
}
?>

<div class="container-fluid px-0">
    <!-- Hero Section -->
    <div class="text-white py-5 position-relative" style="background: linear-gradient(135deg, #09090b 0%, #18181b 100%); border-bottom: 1px solid #27272a;">
        <div class="container py-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <span class="badge bg-light text-dark px-3 py-2 rounded-pill mb-3 text-uppercase fw-bold" style="letter-spacing: 1px; font-size: 0.72rem;">
                        <i class="fa-solid fa-store me-1"></i> Tanauan City, Batangas
                    </span>
                    <h1 class="display-4 fw-extrabold mb-3">
                        Your Trusted Hub for <span class="text-light">Cellphone Accessories</span> & <span class="text-light border-bottom border-2 pb-1">Phone Trading</span>
                    </h1>
                    <p class="lead text-light mb-4" style="opacity: 0.85;">
                        Shop top accessories, trade in or sell your smartphone with real-time fair appraisal, and book fast repair services with store pick-up convenience.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="<?= BASE_URL ?>catalog.php" class="btn btn-light btn-lg rounded-pill px-4 fw-bold shadow">
                            <i class="fa-solid fa-bag-shopping me-2"></i> Browse Catalog
                        </a>
                        <a href="<?= BASE_URL ?>book_repair.php" class="btn btn-outline-light btn-lg rounded-pill px-4">
                            <i class="fa-solid fa-wrench me-2"></i> Book Repair Service
                        </a>
                        <a href="<?= BASE_URL ?>track_repair.php" class="btn btn-outline-secondary btn-lg rounded-pill px-4 text-white">
                            <i class="fa-solid fa-magnifying-glass me-2"></i> Track Job Order
                        </a>
                    </div>
                </div>
                <div class="col-lg-5 text-center">
                    <div class="p-4 bg-white text-dark rounded-4 shadow-lg border">
                        <h4 class="fw-bold mb-2 d-flex align-items-center justify-content-center gap-2">
                            <img src="<?= BASE_URL ?>assets/img/icons/trade-in.png" style="width: 28px; height: 28px; object-fit: contain;" alt="Trade"> Trade Your Phone Today
                        </h4>
                        <p class="text-muted small">Want to upgrade? Get an instant valuation of your old unit and pay only the difference!</p>
                        
                        <div class="p-3 bg-light rounded-3 text-start mb-3 border">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="fa-solid fa-check-circle text-success"></i>
                                <span class="small fw-semibold">Step 1: Transparent Condition Checklist</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="fa-solid fa-check-circle text-success"></i>
                                <span class="small fw-semibold">Step 2: Instant Algorithmic Valuation</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-check-circle text-success"></i>
                                <span class="small fw-semibold">Step 3: Direct Cash-Out or Unit Swap</span>
                            </div>
                        </div>

                        <a href="<?= BASE_URL ?>catalog.php?type=units" class="btn btn-warning w-100 rounded-pill fw-bold text-dark">
                            View Available Phones for Swap <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Core Services Banner -->
    <div class="container my-5">
        <div class="row g-4 text-center">
            <div class="col-md-3">
                <div class="card h-100 card-custom p-4 stat-card">
                    <div class="mb-3"><img src="<?= BASE_URL ?>assets/img/icons/shipping.png" style="width: 55px; height: 55px; object-fit: contain;" alt="Pick-Up"></div>
                    <h5 class="fw-bold">Fast In-Store Pick-Up</h5>
                    <p class="text-muted small mb-0">Reserve items online and conveniently pick them up with your claim code.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 card-custom p-4 stat-card">
                    <div class="mb-3"><img src="<?= BASE_URL ?>assets/img/icons/search.png" style="width: 55px; height: 55px; object-fit: contain;" alt="Appraisal"></div>
                    <h5 class="fw-bold">Fair Unit Appraisal</h5>
                    <p class="text-muted small mb-0">Accurate grading for screens, battery health, and components for swap or sale.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 card-custom p-4 stat-card">
                    <div class="mb-3"><img src="<?= BASE_URL ?>assets/img/icons/deals.png" style="width: 55px; height: 55px; object-fit: contain;" alt="Buyout"></div>
                    <h5 class="fw-bold">Certified Phone Buyouts</h5>
                    <p class="text-muted small mb-0">We buy pre-owned phones safely with full Anti-Fencing compliance.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 card-custom p-4 stat-card">
                    <div class="mb-3"><img src="<?= BASE_URL ?>assets/img/icons/support.png" style="width: 55px; height: 55px; object-fit: contain;" alt="Repair"></div>
                    <h5 class="fw-bold">Expert Repairs & Warranty</h5>
                    <p class="text-muted small mb-0">Quality LCD and battery replacements backed with service warranties.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Featured Accessories -->
    <div class="container mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0">Featured Accessories</h3>
                <p class="text-muted small mb-0">Chargers, tempered glass, cables, and cases</p>
            </div>
            <a href="<?= BASE_URL ?>catalog.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                View All <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php if (!empty($featured_products)): ?>
                <?php foreach ($featured_products as $prod): ?>
                    <div class="col-6 col-md-3">
                        <div class="card h-100 card-custom stat-card overflow-hidden">
                            <div class="bg-light p-4 text-center">
                                <i class="fa-solid fa-plug-circle-bolt fs-1 text-primary opacity-50"></i>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <div class="badge bg-light text-secondary border align-self-start mb-2" style="font-size: 0.7rem;">
                                    <?= htmlspecialchars($prod['brand'] ?? 'Accessory') ?>
                                </div>
                                <h6 class="fw-bold mb-2 text-dark text-truncate" title="<?= htmlspecialchars($prod['name']) ?>">
                                    <?= htmlspecialchars($prod['name']) ?>
                                </h6>
                                <div class="mt-auto pt-2 d-flex justify-content-between align-items-center">
                                    <span class="fw-extrabold text-primary fs-5"><?= format_currency($prod['selling_price']) ?></span>
                                    <button class="btn btn-sm btn-primary rounded-circle" onclick="addToCart(<?= $prod['id'] ?>, '<?= addslashes(htmlspecialchars($prod['name'])) ?>', <?= $prod['selling_price'] ?>, 'product')">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">Start XAMPP MySQL and visit <code>/database/seed.php</code> to see sample products.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Featured Pre-Owned & Brand New Handsets -->
    <div class="container mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0">Smartphones Available in Store</h3>
                <p class="text-muted small mb-0">Ready for instant purchase or swap trade-in</p>
            </div>
            <a href="<?= BASE_URL ?>catalog.php?type=units" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                View All Phones <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php if (!empty($featured_units)): ?>
                <?php foreach ($featured_units as $unit): ?>
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100 card-custom stat-card border">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-info-subtle text-info-emphasis border"><?= htmlspecialchars($unit['brand']) ?></span>
                                    <span class="badge bg-success-subtle text-success-emphasis border"><?= htmlspecialchars($unit['unit_condition']) ?></span>
                                </div>
                                <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($unit['model_name']) ?></h5>
                                <div class="text-muted small mb-2">
                                    <span><?= htmlspecialchars($unit['storage_capacity']) ?></span> • 
                                    <span><?= htmlspecialchars($unit['color']) ?></span>
                                    <?php if ($unit['battery_health']): ?>
                                        • <span class="text-success"><i class="fa-solid fa-battery-three-quarters me-1"></i><?= $unit['battery_health'] ?>%</span>
                                    <?php endif; ?>
                                </div>
                                <div class="p-2 bg-light rounded text-muted small mb-3">
                                    <i class="fa-solid fa-barcode me-1"></i> IMEI: <code><?= substr($unit['imei_1'], 0, 8) ?>******</code>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <div>
                                        <div class="text-muted" style="font-size: 0.75rem;">Selling Price</div>
                                        <div class="fw-bolder text-primary fs-5"><?= format_currency($unit['selling_price']) ?></div>
                                    </div>
                                    <a href="<?= BASE_URL ?>catalog.php?unit_id=<?= $unit['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function addToCart(id, name, price, type) {
    let cart = JSON.parse(localStorage.getItem('8j_cart') || '[]');
    let existing = cart.find(item => item.id === id && item.type === type);
    if (existing) {
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
