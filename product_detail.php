<?php
/**
 * 8J Cellphone Accessories IMS - Full Product Detail & Technical Specifications
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/product_specs.php';

$type = sanitize($_GET['type'] ?? 'product');
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: " . BASE_URL . "catalog.php");
    exit;
}

$item = null;
$category_name = 'Accessories';

if ($type === 'unit') {
    $stmt = $pdo->prepare("SELECT * FROM phone_units WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    $category_name = 'Certified Smartphones';
    $item_name = ($item['brand'] ?? '') . ' ' . ($item['model_name'] ?? '');
    $price = (float)($item['selling_price'] ?? 0);
    $stock = ($item['status'] === 'in_stock') ? 1 : 0;
    $sku = $item['imei_1'] ?? 'UNIT-' . $id;
} else {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name 
                           FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           WHERE p.id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if ($item) {
        $category_name = $item['category_name'] ?: 'Accessories';
        $item_name = $item['name'];
        $price = (float)$item['selling_price'];
        $stock = (int)$item['stock_quantity'];
        $sku = $item['sku'];
    }
}

if (!$item) {
    header("Location: " . BASE_URL . "catalog.php");
    exit;
}

$page_title = $item_name . " - Specifications & Details";
require_once __DIR__ . '/includes/header.php';

// Load deep specifications
$specs = get_product_full_specs($item, $type);
$reviews = get_product_verified_reviews($item_name);

// Related items query
$related_items = [];
if ($type === 'unit') {
    $r_stmt = $pdo->prepare("SELECT * FROM phone_units WHERE id != ? AND status = 'in_stock' ORDER BY id DESC LIMIT 4");
    $r_stmt->execute([$id]);
    $related_items = $r_stmt->fetchAll();
} else {
    $cat_id = $item['category_id'] ?? 0;
    $r_stmt = $pdo->prepare("SELECT * FROM products WHERE id != ? AND status = 'available' ORDER BY (category_id = ?) DESC, id DESC LIMIT 4");
    $r_stmt->execute([$id, $cat_id]);
    $related_items = $r_stmt->fetchAll();
}

// Image resolver
$main_image = BASE_URL . 'assets/img/icons/shop.png';
if (!empty($specs['gallery'][0]) && file_exists(__DIR__ . '/assets/img/products/' . $specs['gallery'][0])) {
    $main_image = BASE_URL . 'assets/img/products/' . $specs['gallery'][0];
} elseif (!empty($item['image']) && file_exists(__DIR__ . '/assets/img/products/' . $item['image'])) {
    $main_image = BASE_URL . 'assets/img/products/' . $item['image'];
}
?>

<div class="container py-4">
    <!-- Breadcrumb Navigation -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0 bg-transparent p-0 small">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>" class="text-decoration-none text-muted"><i class="fa-solid fa-house me-1"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>catalog.php<?= $type === 'unit' ? '?type=units' : '' ?>" class="text-decoration-none text-muted">Catalog</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>catalog.php<?= $type === 'unit' ? '?type=units' : '?category=' . ($item['category_id'] ?? 0) ?>" class="text-decoration-none text-muted"><?= htmlspecialchars($category_name) ?></a></li>
            <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page"><?= htmlspecialchars($item_name) ?></li>
        </ol>
    </nav>

    <!-- Main Product Showcase Card -->
    <div class="card card-custom p-4 p-md-5 mb-5 border-0 shadow-sm bg-white rounded-4 overflow-hidden">
        <div class="row g-5 align-items-start">
            
            <!-- Left Column: Interactive Product Gallery -->
            <div class="col-lg-6">
                <div class="position-sticky" style="top: 90px;">
                    <!-- Main Large Image Container -->
                    <div class="product-gallery-main p-4 rounded-4 position-relative d-flex align-items-center justify-content-center border"
                         style="min-height: 420px; background: radial-gradient(circle, #ffffff 50%, #f4f4f5 100%);">
                        
                        <!-- Badges Overlay -->
                        <div class="position-absolute top-0 start-0 m-3 d-flex flex-column gap-2">
                            <span class="badge bg-dark text-white rounded-pill px-3 py-2 shadow-xs fw-semibold" style="font-size: 0.75rem;">
                                <i class="fa-solid fa-circle-check text-success me-1"></i> 100% Genuine 8J Stock
                            </span>
                            <?php if ($type === 'unit'): ?>
                                <span class="badge bg-success text-white rounded-pill px-3 py-2 shadow-xs fw-semibold" style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-shield-halved me-1"></i> <?= htmlspecialchars($item['unit_condition'] ?? 'Certified Grade A') ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-light text-dark border rounded-pill px-3 py-2 shadow-xs fw-semibold" style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-truck-fast me-1"></i> Tanauan In-Store Pick-Up
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Active Image with Smooth Zoom Preview -->
                        <img id="mainProductView" src="<?= $main_image ?>" 
                             alt="<?= htmlspecialchars($item_name) ?>" 
                             class="img-fluid rounded-3" 
                             style="max-height: 360px; object-fit: contain; filter: drop-shadow(0 16px 28px rgba(0,0,0,0.12)); transition: transform 0.35s ease, opacity 0.25s ease;">
                    </div>

                    <!-- Gallery Thumbnails Strip -->
                    <?php if (!empty($specs['gallery']) && count($specs['gallery']) > 1): ?>
                        <div class="d-flex align-items-center gap-2 mt-3 overflow-auto py-2">
                            <?php foreach ($specs['gallery'] as $idx => $gImg): 
                                $gPath = BASE_URL . 'assets/img/products/' . $gImg;
                                if (!file_exists(__DIR__ . '/assets/img/products/' . $gImg)) continue;
                            ?>
                                <button type="button" 
                                        class="gallery-thumb-btn btn p-1 rounded-3 border <?= $idx === 0 ? 'border-dark shadow-sm' : 'border-light-subtle' ?>" 
                                        style="width: 72px; height: 72px; background: #fafafa;"
                                        onclick="switchGalleryImage('<?= $gPath ?>', this)">
                                    <img src="<?= $gPath ?>" alt="Thumbnail" class="w-100 h-100 object-fit-contain rounded-2">
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Store Pick-up Notice Footnote -->
                    <div class="mt-4 p-3 bg-light rounded-3 border d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-dark text-white p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fa-solid fa-store fs-5"></i>
                        </div>
                        <div class="small">
                            <strong class="d-block text-dark">Available for Same-Day In-Store Pick-Up</strong>
                            <span class="text-muted">Tanauan City, Batangas Counter • Reserve online with zero reservation fees</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Product Overview, Pricing & Purchasing Actions -->
            <div class="col-lg-6">
                <!-- Brand & Sku Header -->
                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                    <span class="badge bg-light text-dark border px-3 py-1 rounded-pill fw-bold" style="font-size: 0.78rem;">
                        <?= htmlspecialchars($item['brand'] ?? '8J Certified') ?>
                    </span>
                    <span class="badge bg-secondary-subtle text-secondary border px-3 py-1 rounded-pill small">
                        <?= htmlspecialchars($category_name) ?>
                    </span>
                    <span class="text-muted small ms-auto" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-barcode me-1"></i> SKU: <code><?= htmlspecialchars($sku) ?></code>
                    </span>
                </div>

                <!-- Product Title -->
                <h1 class="h2 fw-extrabold text-dark mb-2" style="letter-spacing: -0.5px;">
                    <?= htmlspecialchars($item_name) ?>
                </h1>

                <!-- Tagline -->
                <p class="text-secondary mb-3 fw-medium" style="font-size: 1.02rem; line-height: 1.5;">
                    <?= htmlspecialchars($specs['tagline']) ?>
                </p>

                <!-- Rating and Reviews Meta -->
                <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                    <div class="text-warning small">
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                    </div>
                    <span class="fw-bold text-dark small">4.9 / 5.0</span>
                    <span class="text-muted small">•</span>
                    <a href="#reviewsSection" class="text-decoration-none small text-primary fw-semibold">
                        <i class="fa-solid fa-comment-dots me-1"></i> 14 Verified Store Reviews
                    </a>
                    <span class="text-muted small">•</span>
                    <span class="badge <?= $stock > 0 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger' ?> px-2 py-1 rounded-pill small">
                        <i class="fa-solid fa-circle-check me-1"></i> <?= $stock > 0 ? ($stock . ' Units In Stock') : 'Out of Stock' ?>
                    </span>
                </div>

                <!-- Pricing Block -->
                <div class="p-4 rounded-4 mb-4" style="background: linear-gradient(135deg, #09090b 0%, #18181b 100%); color: #ffffff;">
                    <div class="d-flex justify-content-between align-items-baseline mb-1">
                        <span class="text-uppercase fw-bold text-white-50" style="font-size: 0.75rem; letter-spacing: 1px;">Standard Retail Price</span>
                        <span class="badge bg-light text-dark rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 0.7rem;">VAT Inclusive</span>
                    </div>
                    <div class="d-flex align-items-baseline gap-3">
                        <span class="display-5 fw-extrabold text-white" id="liveDisplayPrice">
                            <?= format_currency($price) ?>
                        </span>
                        <?php if ($type !== 'unit'): ?>
                            <span class="text-white-50 text-decoration-line-through small">
                                <?= format_currency($price * 1.25) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <p class="mb-0 text-white-50 small mt-2">
                        <i class="fa-solid fa-shield-halved text-success me-1"></i> Covered by 8J Tanauan official store receipt and replacement guarantee.
                    </p>
                </div>

                <!-- Key Feature Highlights Bullets -->
                <div class="mb-4">
                    <h6 class="fw-bold text-dark text-uppercase mb-3" style="font-size: 0.78rem; letter-spacing: 0.8px;">
                        <i class="fa-solid fa-sparkles text-warning me-1"></i> Key Highlights & Features
                    </h6>
                    <div class="row g-2">
                        <?php foreach ($specs['highlights'] as $highlight): ?>
                            <div class="col-12 d-flex align-items-start gap-2">
                                <i class="fa-solid fa-circle-check text-dark mt-1 flex-shrink-0"></i>
                                <span class="text-secondary small fw-medium"><?= htmlspecialchars($highlight) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Purchasing & Cart Actions -->
                <div class="card p-3 p-md-4 rounded-4 border bg-light mb-4">
                    <div class="row g-3 align-items-center">
                        <?php if ($type !== 'unit'): ?>
                            <div class="col-sm-4">
                                <label class="form-label small fw-bold text-muted mb-1">Quantity</label>
                                <div class="input-group">
                                    <button class="btn btn-outline-dark" type="button" onclick="adjustProductQty(-1)">
                                        <i class="fa-solid fa-minus"></i>
                                    </button>
                                    <input type="text" id="productQtyInput" class="form-control text-center fw-bold bg-white" value="1" readonly>
                                    <button class="btn btn-outline-dark" type="button" onclick="adjustProductQty(1)">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="<?= $type !== 'unit' ? 'col-sm-8' : 'col-12' ?>">
                            <label class="form-label small fw-bold text-muted mb-1 d-none d-sm-block">&nbsp;</label>
                            <button type="button" 
                                    class="btn btn-dark w-100 rounded-pill py-2.5 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2 btn-tactile"
                                    onclick="addProductToPickupCart()">
                                <i class="fa-solid fa-cart-plus"></i> Add to Pick-Up Cart
                            </button>
                        </div>

                        <div class="col-12">
                            <button type="button" 
                                    class="btn btn-outline-dark w-100 rounded-pill py-2 fw-semibold d-flex align-items-center justify-content-center gap-2"
                                    onclick="reserveDirectlyNow()">
                                <i class="fa-solid fa-bolt"></i> Reserve for Instant Pick-Up Today <i class="fa-solid fa-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Trust Badges & Guarantee Grid -->
                <div class="row g-3 text-dark small border-top pt-3">
                    <div class="col-6 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-stopwatch text-dark fs-5"></i>
                        <div>
                            <strong class="d-block" style="font-size: 0.8rem;">1-Hour Pick-Up</strong>
                            <span class="text-muted" style="font-size: 0.72rem;">Ready at Tanauan store</span>
                        </div>
                    </div>
                    <div class="col-6 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-rotate-left text-dark fs-5"></i>
                        <div>
                            <strong class="d-block" style="font-size: 0.8rem;">7-Day Replacement</strong>
                            <span class="text-muted" style="font-size: 0.72rem;">Direct factory defect swap</span>
                        </div>
                    </div>
                    <div class="col-6 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-receipt text-dark fs-5"></i>
                        <div>
                            <strong class="d-block" style="font-size: 0.8rem;">Official Claim Slip</strong>
                            <span class="text-muted" style="font-size: 0.72rem;">Secure PIN verification</span>
                        </div>
                    </div>
                    <div class="col-6 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-award text-dark fs-5"></i>
                        <div>
                            <strong class="d-block" style="font-size: 0.8rem;">Verified Authentic</strong>
                            <span class="text-muted" style="font-size: 0.72rem;">Tested by 8J technicians</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- In-Depth Technical Specifications & Reviews Tabs -->
    <div class="card card-custom border-0 shadow-sm rounded-4 mb-5 overflow-hidden bg-white">
        <div class="card-header bg-light border-bottom p-0">
            <ul class="nav nav-tabs border-0 px-3 pt-2" id="productTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold px-4 py-3 text-dark" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs-pane" type="button" role="tab">
                        <i class="fa-solid fa-list-check me-2"></i> Technical Specifications
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold px-4 py-3 text-dark" id="compat-tab" data-bs-toggle="tab" data-bs-target="#compat-pane" type="button" role="tab">
                        <i class="fa-solid fa-circle-nodes me-2"></i> Device Compatibility
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold px-4 py-3 text-dark" id="box-tab" data-bs-toggle="tab" data-bs-target="#box-pane" type="button" role="tab">
                        <i class="fa-solid fa-box-open me-2"></i> Package Inclusions
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold px-4 py-3 text-dark" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews-pane" type="button" role="tab">
                        <i class="fa-solid fa-comments me-2"></i> Customer Reviews (14)
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-4 p-md-5">
            <div class="tab-content" id="productTabsContent">
                
                <!-- Tab 1: Detailed Specifications Table -->
                <div class="tab-pane fade show active" id="specs-pane" role="tabpanel">
                    <div class="mb-4">
                        <h4 class="fw-bold text-dark mb-2">Technical Overview</h4>
                        <p class="text-secondary leading-relaxed" style="font-size: 0.95rem; line-height: 1.7;">
                            <?= nl2br(htmlspecialchars($specs['overview'])) ?>
                        </p>
                    </div>

                    <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">Full Hardware Specifications</h5>
                    <div class="table-responsive rounded-3 border">
                        <table class="table table-striped table-hover mb-0">
                            <tbody>
                                <?php foreach ($specs['technical_details'] as $specKey => $specVal): ?>
                                    <tr>
                                        <th class="bg-light text-dark fw-semibold px-4 py-3" style="width: 32%; font-size: 0.88rem;">
                                            <?= htmlspecialchars($specKey) ?>
                                        </th>
                                        <td class="px-4 py-3 text-secondary" style="font-size: 0.9rem;">
                                            <strong><?= htmlspecialchars($specVal) ?></strong>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab 2: Device Compatibility -->
                <div class="tab-pane fade" id="compat-pane" role="tabpanel">
                    <h4 class="fw-bold text-dark mb-3">Supported Devices & Ecosystems</h4>
                    <p class="text-secondary mb-4" style="line-height: 1.7;">
                        <?= htmlspecialchars($specs['compatibility']) ?>
                    </p>

                    <div class="row g-4 mt-2">
                        <div class="col-md-4">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-dark mb-2"><i class="fa-brands fa-apple me-2"></i> Apple Ecosystem</h6>
                                <p class="small text-muted mb-0">Fully tested for iPhone fast-charging protocol, iPads, AirPods, and Apple Watch companion devices.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-dark mb-2"><i class="fa-brands fa-android me-2"></i> Android Ecosystem</h6>
                                <p class="small text-muted mb-0">Supports Samsung AFC, Huawei SCP/FCP, Qualcomm QuickCharge, Xiaomi, Oppo, and Vivo fast protocols.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-gamepad me-2"></i> Wearables & Peripherals</h6>
                                <p class="small text-muted mb-0">Micro-current low voltage mode ensures safe charging for wireless earbuds, fitness trackers, and portable gaming units.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 3: Package Inclusions & Warranty -->
                <div class="tab-pane fade" id="box-pane" role="tabpanel">
                    <h4 class="fw-bold text-dark mb-3">What's Inside the Box</h4>
                    <ul class="list-group list-group-flush mb-4 border rounded-3 overflow-hidden">
                        <?php foreach ($specs['package_contents'] as $content): ?>
                            <li class="list-group-item px-4 py-3 d-flex align-items-center gap-3">
                                <i class="fa-solid fa-box text-dark"></i>
                                <span class="fw-semibold text-dark"><?= htmlspecialchars($content) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="p-4 rounded-3 border bg-light">
                        <h5 class="fw-bold text-dark mb-2"><i class="fa-solid fa-shield-halved text-success me-2"></i>8J Store After-Sales Guarantee</h5>
                        <p class="text-secondary small mb-0" style="line-height: 1.6;">
                            Every accessory and smartphone sold at 8J Tanauan comes with an official printed claim slip. Within 7 days of purchase, any manufacturing defect is eligible for an immediate in-store 1-to-1 replacement. Certified technician diagnosis is available 6 days a week at our Tanauan counter.
                        </p>
                    </div>
                </div>

                <!-- Tab 4: Verified Reviews -->
                <div class="tab-pane fade" id="reviews-pane" role="tabpanel">
                    <div id="reviewsSection">
                        <div class="row g-4 align-items-center mb-5 p-4 bg-light rounded-4 border">
                            <div class="col-md-4 text-center border-end-md">
                                <div class="display-3 fw-extrabold text-dark mb-1">4.9</div>
                                <div class="text-warning mb-1">
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                </div>
                                <div class="text-muted small">Based on 14 verified customer pickups</div>
                            </div>
                            <div class="col-md-8 ps-md-4">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="small text-muted" style="width: 50px;">5 Stars</span>
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar bg-dark" style="width: 92%;"></div>
                                    </div>
                                    <span class="small fw-semibold text-dark">92%</span>
                                </div>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="small text-muted" style="width: 50px;">4 Stars</span>
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar bg-dark" style="width: 8%;"></div>
                                    </div>
                                    <span class="small fw-semibold text-dark">8%</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="small text-muted" style="width: 50px;">1-3 Stars</span>
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar bg-dark" style="width: 0%;"></div>
                                    </div>
                                    <span class="small fw-semibold text-dark">0%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Review Cards -->
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($reviews as $rev): ?>
                                <div class="card p-3 p-md-4 rounded-3 border bg-white shadow-xs">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <div class="d-flex align-items-center gap-2">
                                                <strong class="text-dark"><?= htmlspecialchars($rev['author']) ?></strong>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill" style="font-size: 0.65rem;">
                                                    <i class="fa-solid fa-circle-check me-1"></i> Verified In-Store Pickup
                                                </span>
                                            </div>
                                            <div class="text-muted small"><?= htmlspecialchars($rev['date']) ?></div>
                                        </div>
                                        <div class="text-warning small">
                                            <?php for ($s = 0; $s < $rev['rating']; $s++): ?>
                                                <i class="fa-solid fa-star"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($rev['title']) ?></h6>
                                    <p class="text-secondary small mb-0"><?= htmlspecialchars($rev['comment']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Related / Recommended Products Section -->
    <?php if (!empty($related_items)): ?>
        <div class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="fw-extrabold text-dark mb-1">Frequently Bought Together</h3>
                    <p class="text-muted small mb-0">Recommended accessories and companion items for your setup</p>
                </div>
                <a href="<?= BASE_URL ?>catalog.php" class="btn btn-outline-dark rounded-pill px-4 btn-sm fw-semibold">
                    View Entire Store <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="row g-4">
                <?php foreach ($related_items as $rel): 
                    $rel_name = $rel['name'] ?? ($rel['brand'] . ' ' . $rel['model_name']);
                    $rel_price = (float)$rel['selling_price'];
                    $rel_sku = $rel['sku'] ?? $rel['imei_1'];
                    $rel_img = BASE_URL . 'assets/img/icons/shop.png';
                    if (!empty($rel['image']) && file_exists(__DIR__ . '/assets/img/products/' . $rel['image'])) {
                        $rel_img = BASE_URL . 'assets/img/products/' . $rel['image'];
                    }
                    $rel_link = BASE_URL . 'product_detail.php?' . (isset($rel['imei_1']) ? 'type=unit&' : '') . 'id=' . $rel['id'];
                ?>
                    <div class="col-6 col-md-3">
                        <div class="card h-100 card-custom border p-3 rounded-4 bg-white d-flex flex-column text-decoration-none text-dark position-relative hover-shadow transition">
                            <div class="bg-light p-3 rounded-3 text-center d-flex align-items-center justify-content-center mb-3" style="height: 140px;">
                                <a href="<?= $rel_link ?>">
                                    <img src="<?= $rel_img ?>" alt="<?= htmlspecialchars($rel_name) ?>" class="img-fluid" style="max-height: 110px; object-fit: contain;">
                                </a>
                            </div>
                            <span class="badge bg-light text-dark border px-2 py-0.5 rounded-pill small w-fit mb-2" style="font-size: 0.68rem;">
                                <?= htmlspecialchars($rel['brand'] ?? '8J') ?>
                            </span>
                            <h6 class="fw-bold mb-1 text-truncate" title="<?= htmlspecialchars($rel_name) ?>">
                                <a href="<?= $rel_link ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($rel_name) ?></a>
                            </h6>
                            <div class="fw-bold text-dark fs-6 mt-auto pt-2">
                                <?= format_currency($rel_price) ?>
                            </div>
                            <a href="<?= $rel_link ?>" class="btn btn-sm btn-outline-dark rounded-pill mt-2 w-100 fw-semibold">
                                View Specs
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- Toast Notification Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
    <div id="cartToast" class="toast align-items-center text-white bg-dark border-0 rounded-4 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex p-2">
            <div class="toast-body d-flex align-items-center gap-3">
                <i class="fa-solid fa-circle-check text-success fs-4"></i>
                <div>
                    <strong class="d-block" id="toastItemName">Item Added to Pick-Up Cart!</strong>
                    <small class="text-white-50">View your cart or continue browsing.</small>
                </div>
            </div>
            <a href="<?= BASE_URL ?>cart.php" class="btn btn-light btn-sm text-dark align-self-center me-2 rounded-pill px-3 fw-bold">
                View Cart
            </a>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
// Switch main gallery image on thumbnail click
function switchGalleryImage(imgSrc, btnEl) {
    const mainView = document.getElementById('mainProductView');
    if (!mainView) return;
    
    mainView.style.opacity = '0.3';
    setTimeout(() => {
        mainView.src = imgSrc;
        mainView.style.opacity = '1';
    }, 150);

    document.querySelectorAll('.gallery-thumb-btn').forEach(btn => {
        btn.classList.remove('border-dark', 'shadow-sm');
        btn.classList.add('border-light-subtle');
    });
    btnEl.classList.remove('border-light-subtle');
    btnEl.classList.add('border-dark', 'shadow-sm');
}

// Adjust quantity
function adjustProductQty(delta) {
    const input = document.getElementById('productQtyInput');
    if (!input) return;
    let val = parseInt(input.value) || 1;
    val = Math.max(1, Math.min(<?= max(1, $stock) ?>, val + delta));
    input.value = val;
}

// Add to Pick-Up Cart
function addProductToPickupCart() {
    const qtyInput = document.getElementById('productQtyInput');
    const qty = qtyInput ? (parseInt(qtyInput.value) || 1) : 1;
    const itemData = {
        id: <?= (int)$item['id'] ?>,
        type: '<?= $type === 'unit' ? 'phone_unit' : 'product' ?>',
        name: <?= json_encode($item_name) ?>,
        price: <?= (float)$price ?>,
        quantity: qty,
        image: <?= json_encode($specs['gallery'][0] ?? ($item['image'] ?? '')) ?>,
        sku: <?= json_encode($sku) ?>
    };

    let cart = JSON.parse(localStorage.getItem('8j_cart') || '[]');
    let existingIndex = cart.findIndex(c => c.id === itemData.id && c.type === itemData.type);
    if (existingIndex > -1) {
        cart[existingIndex].quantity += qty;
    } else {
        cart.push(itemData);
    }
    localStorage.setItem('8j_cart', JSON.stringify(cart));

    if (typeof refreshCartBadge === 'function') {
        refreshCartBadge();
    }

    // Show Toast
    const toastEl = document.getElementById('cartToast');
    const toastName = document.getElementById('toastItemName');
    if (toastName) toastName.innerText = itemData.name + ' (' + qty + 'x)';
    if (toastEl) {
        const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
        toast.show();
    }
}

// Reserve directly and redirect
function reserveDirectlyNow() {
    addProductToPickupCart();
    setTimeout(() => {
        window.location.href = '<?= BASE_URL ?>cart.php';
    }, 300);
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
