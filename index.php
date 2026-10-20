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
                <?php foreach ($featured_products as $prod): 
                    $prod_img = '';
                    if (!empty($prod['image']) && file_exists(__DIR__ . '/assets/img/products/' . $prod['image'])) {
                        $prod_img = BASE_URL . 'assets/img/products/' . $prod['image'];
                    }
                ?>
                    <div class="col-6 col-md-3">
                        <div class="card h-100 card-custom stat-card overflow-hidden accessory-card position-relative"
                             role="button"
                             tabindex="0"
                             data-id="<?= $prod['id'] ?>"
                             data-name="<?= htmlspecialchars($prod['name']) ?>"
                             data-brand="<?= htmlspecialchars($prod['brand'] ?? 'Accessory') ?>"
                             data-price="<?= $prod['selling_price'] ?>"
                             data-formatted-price="<?= format_currency($prod['selling_price']) ?>"
                             data-stock="<?= $prod['stock_quantity'] ?>"
                             data-sku="<?= htmlspecialchars($prod['sku']) ?>"
                             data-description="<?= htmlspecialchars($prod['description'] ?? 'Quality cellphone accessory from 8J.') ?>"
                             data-image="<?= htmlspecialchars($prod_img) ?>"
                             style="cursor: pointer;">
                            
                            <!-- 2-3s Hover Progress Countdown Indicator -->
                            <div class="hover-progress-bar"></div>

                            <div class="bg-light p-3 text-center position-relative d-flex align-items-center justify-content-center" style="height: 165px; overflow: hidden; background: radial-gradient(circle, #ffffff 60%, #f4f4f5 100%);">
                                <?php if (!empty($prod_img)): ?>
                                    <img src="<?= $prod_img ?>" alt="<?= htmlspecialchars($prod['name']) ?>" class="product-thumb" style="max-height: 140px; max-width: 100%; object-fit: contain; transition: transform 0.35s ease;">
                                <?php else: ?>
                                    <i class="fa-solid fa-plug-circle-bolt fs-1 text-primary opacity-50"></i>
                                <?php endif; ?>
                                <span class="hover-preview-pill position-absolute bottom-0 start-50 translate-middle-x mb-2 px-2 py-1 rounded-pill bg-dark bg-opacity-75 text-white small" style="font-size: 0.65rem; pointer-events: none; opacity: 0; transform: translateY(4px); transition: all 0.25s ease;">
                                    <i class="fa-solid fa-expand me-1"></i> Hold or Click to Preview
                                </span>
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
                                    <button class="btn btn-sm btn-primary rounded-circle add-cart-btn" 
                                            title="Add to Pick-Up Cart"
                                            onclick="event.stopPropagation(); addToCart(<?= $prod['id'] ?>, '<?= addslashes(htmlspecialchars($prod['name'])) ?>', <?= $prod['selling_price'] ?>, 'product');"
                                            style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
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
                <?php foreach ($featured_units as $unit): 
                    $unit_img = '';
                    if (!empty($unit['image']) && file_exists(__DIR__ . '/assets/img/products/' . $unit['image'])) {
                        $unit_img = BASE_URL . 'assets/img/products/' . $unit['image'];
                    }
                ?>
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100 card-custom stat-card overflow-hidden phone-card position-relative"
                             role="button"
                             tabindex="0"
                             data-id="<?= $unit['id'] ?>"
                             data-name="<?= htmlspecialchars($unit['brand'] . ' ' . $unit['model_name']) ?>"
                             data-brand="<?= htmlspecialchars($unit['brand']) ?>"
                             data-model="<?= htmlspecialchars($unit['model_name']) ?>"
                             data-storage="<?= htmlspecialchars($unit['storage_capacity']) ?>"
                             data-color="<?= htmlspecialchars($unit['color']) ?>"
                             data-condition="<?= htmlspecialchars($unit['unit_condition']) ?>"
                             data-battery="<?= $unit['battery_health'] ?>"
                             data-imei="<?= htmlspecialchars($unit['imei_1']) ?>"
                             data-price="<?= $unit['selling_price'] ?>"
                             data-formatted-price="<?= format_currency($unit['selling_price']) ?>"
                             data-notes="<?= htmlspecialchars($unit['notes'] ?? 'Certified phone unit from 8J, fully inspected and tested.') ?>"
                             data-image="<?= htmlspecialchars($unit_img) ?>"
                             style="cursor: pointer;">
                            
                            <!-- 2-3s Hover Progress Countdown Indicator -->
                            <div class="hover-progress-bar"></div>

                            <div class="bg-light p-3 text-center position-relative d-flex align-items-center justify-content-center" style="height: 165px; overflow: hidden; background: radial-gradient(circle, #ffffff 60%, #f4f4f5 100%);">
                                <?php if (!empty($unit_img)): ?>
                                    <img src="<?= $unit_img ?>" alt="<?= htmlspecialchars($unit['model_name']) ?>" class="product-thumb" style="max-height: 140px; max-width: 100%; object-fit: contain; transition: transform 0.35s ease;">
                                <?php else: ?>
                                    <i class="fa-solid fa-mobile-screen fs-1 text-primary opacity-50"></i>
                                <?php endif; ?>
                                <span class="hover-preview-pill position-absolute bottom-0 start-50 translate-middle-x mb-2 px-2 py-1 rounded-pill bg-dark bg-opacity-75 text-white small" style="font-size: 0.65rem; pointer-events: none; opacity: 0; transform: translateY(4px); transition: all 0.25s ease;">
                                    <i class="fa-solid fa-expand me-1"></i> Hold or Click to Preview
                                </span>
                            </div>

                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-info-subtle text-info-emphasis border"><?= htmlspecialchars($unit['brand']) ?></span>
                                    <span class="badge bg-success-subtle text-success-emphasis border"><?= htmlspecialchars($unit['unit_condition']) ?></span>
                                </div>
                                <h5 class="fw-bold text-dark mb-1 text-truncate" title="<?= htmlspecialchars($unit['model_name']) ?>"><?= htmlspecialchars($unit['model_name']) ?></h5>
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
                                <div class="d-flex justify-content-between align-items-center mt-auto pt-2">
                                    <div>
                                        <div class="text-muted" style="font-size: 0.72rem;">Selling Price</div>
                                        <div class="fw-bolder text-primary fs-5"><?= format_currency($unit['selling_price']) ?></div>
                                    </div>
                                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3" 
                                            onclick="event.stopPropagation(); openPhoneModalFromCard(this.closest('.phone-card'), bootstrap.Modal.getInstance(document.getElementById('phonePreviewModal')) || new bootstrap.Modal(document.getElementById('phonePreviewModal')));"
                                            style="white-space: nowrap; font-size: 0.78rem;">
                                        <i class="fa-regular fa-eye me-1"></i> Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Centered Product Preview Modal with Item Picture (Accessories) -->
<div class="modal fade" id="itemPreviewModal" tabindex="-1" aria-labelledby="itemPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden" style="box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.4);">
            <div class="modal-header border-0 pb-0 pt-3 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span id="modalBrandBadge" class="badge bg-light text-dark border px-3 py-1 rounded-pill fw-semibold" style="font-size: 0.75rem;"></span>
                    <span id="modalStockBadge" class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill small"></span>
                </div>
                <button type="button" class="btn-close rounded-circle p-2 bg-light shadow-sm" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4 align-items-center">
                    <!-- Item Picture Centered in Column -->
                    <div class="col-md-5 text-center">
                        <div class="p-3 bg-light rounded-4 d-flex align-items-center justify-content-center shadow-inner" style="min-height: 270px; max-height: 310px; background: radial-gradient(circle, #ffffff 60%, #e9ecef 100%);">
                            <img id="modalProductImage" src="" alt="Product Picture" class="img-fluid rounded-3" style="max-height: 250px; object-fit: contain; filter: drop-shadow(0 12px 20px rgba(0,0,0,0.12)); transition: transform 0.35s ease;">
                            <div id="modalProductFallbackIcon" class="d-none">
                                <i class="fa-solid fa-plug-circle-bolt display-1 text-primary opacity-50"></i>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2" style="font-size: 0.72rem;">
                            <i class="fa-solid fa-circle-check text-success me-1"></i> 100% Guaranteed Authentic • Store Tested
                        </small>
                    </div>

                    <!-- Item Details & Pick-Up Action -->
                    <div class="col-md-7">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-dark text-white rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">Featured Accessory</span>
                            <span class="text-muted small" id="modalProductSku" style="font-size: 0.75rem;"></span>
                        </div>
                        <h4 id="modalProductName" class="fw-bold text-dark mb-2"></h4>
                        <div class="d-flex align-items-baseline gap-2 mb-3">
                            <span id="modalProductPrice" class="display-6 fw-extrabold text-primary"></span>
                            <span class="text-muted small">Standard Retail Price</span>
                        </div>
                        <p id="modalProductDescription" class="text-secondary small mb-3" style="line-height: 1.6;"></p>

                        <!-- Key Benefits / Store Perks -->
                        <div class="p-3 rounded-3 mb-3 border bg-white shadow-xs">
                            <div class="row g-2 text-dark small" style="font-size: 0.78rem;">
                                <div class="col-6 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-bolt text-warning"></i> Fast Same-Day Pick-up
                                </div>
                                <div class="col-6 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-shield-halved text-success"></i> 7-Day Replacement Warranty
                                </div>
                                <div class="col-6 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-receipt text-info"></i> Official Claim Slip
                                </div>
                                <div class="col-6 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-location-dot text-danger"></i> Tanauan City Counter
                                </div>
                            </div>
                        </div>

                        <!-- Quantity Selector & Cart CTA -->
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <div class="input-group" style="width: 120px;">
                                <button class="btn btn-outline-secondary btn-sm" type="button" onclick="adjustPreviewQty(-1)">
                                    <i class="fa-solid fa-minus"></i>
                                </button>
                                <input type="text" id="modalPreviewQty" class="form-control form-control-sm text-center fw-bold" value="1" readonly>
                                <button class="btn btn-outline-secondary btn-sm" type="button" onclick="adjustPreviewQty(1)">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                            <button id="modalAddCartBtn" class="btn btn-primary rounded-pill px-4 flex-grow-1 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm py-2">
                                <i class="fa-solid fa-cart-plus"></i> Add to Pick-Up Cart
                            </button>
                        </div>
                        <div class="mt-3 pt-2 border-top">
                            <a id="modalViewFullSpecsBtn" href="#" class="btn btn-outline-dark rounded-pill w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                                <i class="fa-solid fa-circle-info"></i> View Full Specifications & Technical Details <i class="fa-solid fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Centered Smartphone Unit Preview Modal with Picture -->
<div class="modal fade" id="phonePreviewModal" tabindex="-1" aria-labelledby="phonePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden" style="box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.4);">
            <div class="modal-header border-0 pb-0 pt-3 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span id="modalPhoneBrand" class="badge bg-light text-dark border px-3 py-1 rounded-pill fw-semibold" style="font-size: 0.75rem;"></span>
                    <span id="modalPhoneCondition" class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill small fw-semibold"></span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill small">
                        <i class="fa-solid fa-shield-halved me-1"></i> 8J Certified
                    </span>
                </div>
                <button type="button" class="btn-close rounded-circle p-2 bg-light shadow-sm" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4 align-items-center">
                    <!-- Phone Picture Centered in Column -->
                    <div class="col-md-5 text-center">
                        <div class="p-3 bg-light rounded-4 d-flex align-items-center justify-content-center shadow-inner" style="min-height: 290px; max-height: 330px; background: radial-gradient(circle, #ffffff 60%, #e9ecef 100%);">
                            <img id="modalPhoneImage" src="" alt="Phone Picture" class="img-fluid rounded-3" style="max-height: 270px; object-fit: contain; filter: drop-shadow(0 15px 25px rgba(0,0,0,0.15)); transition: transform 0.35s ease;">
                            <div id="modalPhoneFallbackIcon" class="d-none">
                                <i class="fa-solid fa-mobile-screen display-1 text-primary opacity-50"></i>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2" style="font-size: 0.72rem;">
                            <i class="fa-solid fa-circle-check text-success me-1"></i> Clean IMEI & Accounts • Anti-Fencing Compliant
                        </small>
                    </div>

                    <!-- Phone Specifications & Action -->
                    <div class="col-md-7">
                        <h3 id="modalPhoneName" class="fw-bold text-dark mb-1"></h3>
                        <div class="text-muted small mb-3" id="modalPhoneSpecs" style="font-size: 0.85rem;"></div>

                        <div class="d-flex align-items-baseline gap-2 mb-3">
                            <span id="modalPhonePrice" class="display-6 fw-extrabold text-primary"></span>
                            <span class="text-muted small">Store Cash / Pick-Up Price</span>
                        </div>

                        <!-- Hardware Checklist / Specs Grid -->
                        <div class="p-3 rounded-3 mb-3 border bg-white shadow-xs">
                            <div class="row g-2 text-dark small" style="font-size: 0.8rem;">
                                <div class="col-6 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-database text-primary"></i> <span id="modalSpecStorage"></span>
                                </div>
                                <div class="col-6 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-palette text-secondary"></i> <span id="modalSpecColor"></span>
                                </div>
                                <div class="col-6 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-battery-three-quarters text-success"></i> <span id="modalSpecBattery"></span>
                                </div>
                                <div class="col-6 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-lock-open text-info"></i> Clean iCloud / Google
                                </div>
                                <div class="col-12 mt-2 pt-2 border-top text-muted" style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-barcode me-1 text-dark"></i> Validated IMEI: <code id="modalSpecImei" class="text-dark fw-bold"></code>
                                </div>
                            </div>
                        </div>

                        <p id="modalPhoneNotes" class="text-secondary small mb-3" style="line-height: 1.5;"></p>

                        <!-- Action Buttons -->
                        <div class="d-flex flex-wrap gap-2">
                            <button id="modalPhoneReserveBtn" class="btn btn-primary rounded-pill px-4 flex-grow-1 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm py-2">
                                <i class="fa-solid fa-cart-arrow-down"></i> Reserve for Pick-Up
                            </button>
                            <a href="<?= BASE_URL ?>admin/trading.php" class="btn btn-outline-dark rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.85rem;">
                                <i class="fa-solid fa-arrows-rotate text-primary"></i> Trade-In Swap
                            </a>
                        </div>
                        <div class="mt-3 pt-2 border-top">
                            <a id="modalPhoneViewFullSpecsBtn" href="#" class="btn btn-outline-dark rounded-pill w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                                <i class="fa-solid fa-circle-info"></i> View Full 25-Point Diagnostic & Specifications <i class="fa-solid fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Hover progress bar for 2-3s countdown */
.accessory-card, .phone-card {
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    border: 1px solid #e4e4e7;
    position: relative;
}
.accessory-card:hover, .phone-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.12);
    border-color: #09090b;
}
.accessory-card .hover-progress-bar, .phone-card .hover-progress-bar {
    position: absolute;
    top: 0;
    left: 0;
    height: 3px;
    width: 0%;
    background: linear-gradient(90deg, #10b981, #09090b);
    transition: width 2.2s linear;
    z-index: 10;
    border-top-left-radius: inherit;
    border-top-right-radius: inherit;
}
.accessory-card.hover-active .hover-progress-bar, .phone-card.hover-active .hover-progress-bar {
    width: 100%;
}
.accessory-card:hover .product-thumb, .phone-card:hover .product-thumb {
    transform: scale(1.06);
}
.accessory-card:hover .hover-preview-pill, .phone-card:hover .hover-preview-pill {
    opacity: 1 !important;
    transform: translate(-50%, 0) !important;
}

/* Modal pop-in animation centered */
#itemPreviewModal .modal-content, #phonePreviewModal .modal-content {
    animation: modalPopIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes modalPopIn {
    0% {
        opacity: 0;
        transform: scale(0.85);
    }
    100% {
        opacity: 1;
        transform: scale(1);
    }
}
#modalProductImage:hover, #modalPhoneImage:hover {
    transform: scale(1.05);
}
</style>

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

let currentPreviewProduct = null;
let currentPreviewPhone = null;
let hoverTimer = null;
const HOVER_TRIGGER_DELAY = 2200; // 2.2 seconds (matches 2-3 seconds user requirement)

document.addEventListener('DOMContentLoaded', function () {
    const previewModalEl = document.getElementById('itemPreviewModal');
    const phoneModalEl = document.getElementById('phonePreviewModal');
    const bsModal = previewModalEl ? new bootstrap.Modal(previewModalEl) : null;
    const bsPhoneModal = phoneModalEl ? new bootstrap.Modal(phoneModalEl) : null;

    // 1. Wire up accessory cards
    document.querySelectorAll('.accessory-card').forEach(card => {
        card.addEventListener('mouseenter', function () {
            if ((previewModalEl && previewModalEl.classList.contains('show')) || 
                (phoneModalEl && phoneModalEl.classList.contains('show'))) return;

            card.classList.add('hover-active');
            clearTimeout(hoverTimer);

            hoverTimer = setTimeout(() => {
                card.classList.remove('hover-active');
                if (bsModal) openModalFromCard(card, bsModal);
            }, HOVER_TRIGGER_DELAY);
        });

        card.addEventListener('mouseleave', function () {
            card.classList.remove('hover-active');
            clearTimeout(hoverTimer);
        });

        card.addEventListener('click', function (e) {
            if (e.target.closest('.add-cart-btn')) return;
            card.classList.remove('hover-active');
            clearTimeout(hoverTimer);
            if (bsModal) openModalFromCard(card, bsModal);
        });
    });

    // 2. Wire up smartphone cards
    document.querySelectorAll('.phone-card').forEach(card => {
        card.addEventListener('mouseenter', function () {
            if ((previewModalEl && previewModalEl.classList.contains('show')) || 
                (phoneModalEl && phoneModalEl.classList.contains('show'))) return;

            card.classList.add('hover-active');
            clearTimeout(hoverTimer);

            hoverTimer = setTimeout(() => {
                card.classList.remove('hover-active');
                if (bsPhoneModal) openPhoneModalFromCard(card, bsPhoneModal);
            }, HOVER_TRIGGER_DELAY);
        });

        card.addEventListener('mouseleave', function () {
            card.classList.remove('hover-active');
            clearTimeout(hoverTimer);
        });

        card.addEventListener('click', function (e) {
            card.classList.remove('hover-active');
            clearTimeout(hoverTimer);
            if (bsPhoneModal) openPhoneModalFromCard(card, bsPhoneModal);
        });
    });

    // Reset hover states on modal hide
    [previewModalEl, phoneModalEl].forEach(m => {
        if (!m) return;
        m.addEventListener('hidden.bs.modal', function () {
            document.querySelectorAll('.accessory-card, .phone-card').forEach(c => c.classList.remove('hover-active'));
            clearTimeout(hoverTimer);
        });
    });

    // Accessory modal add to cart
    const addCartBtn = document.getElementById('modalAddCartBtn');
    if (addCartBtn) {
        addCartBtn.addEventListener('click', function () {
            if (!currentPreviewProduct) return;
            const qty = parseInt(document.getElementById('modalPreviewQty').value) || 1;
            
            let cart = JSON.parse(localStorage.getItem('8j_cart') || '[]');
            let existing = cart.find(item => item.id === currentPreviewProduct.id && item.type === 'product');
            if (existing) {
                existing.quantity += qty;
            } else {
                cart.push({
                    id: currentPreviewProduct.id,
                    name: currentPreviewProduct.name,
                    price: currentPreviewProduct.price,
                    type: 'product',
                    quantity: qty
                });
            }
            localStorage.setItem('8j_cart', JSON.stringify(cart));
            refreshCartBadge(true);
            showToast('Added ' + qty + 'x ' + currentPreviewProduct.name + ' to Pick-Up Cart!');
            
            if (bsModal) bsModal.hide();
        });
    }

    // Phone modal reserve for pick-up
    const phoneReserveBtn = document.getElementById('modalPhoneReserveBtn');
    if (phoneReserveBtn) {
        phoneReserveBtn.addEventListener('click', function () {
            if (!currentPreviewPhone) return;
            addToCart(currentPreviewPhone.id, currentPreviewPhone.name, currentPreviewPhone.price, 'phone_unit');
            if (bsPhoneModal) bsPhoneModal.hide();
        });
    }
});

function openModalFromCard(card, bsModal) {
    const id = parseInt(card.dataset.id);
    const name = card.dataset.name;
    const brand = card.dataset.brand;
    const price = parseFloat(card.dataset.price);
    const formattedPrice = card.dataset.formattedPrice;
    const stock = parseInt(card.dataset.stock);
    const sku = card.dataset.sku;
    const description = card.dataset.description;
    const image = card.dataset.image;

    currentPreviewProduct = { id, name, brand, price, formattedPrice, stock, sku, description, image };

    document.getElementById('modalBrandBadge').innerText = brand;
    document.getElementById('modalStockBadge').innerText = stock > 0 ? (stock + ' Units In Stock') : 'Out of Stock';
    document.getElementById('modalProductSku').innerText = 'SKU: ' + sku;
    document.getElementById('modalProductName').innerText = name;
    document.getElementById('modalProductPrice').innerText = formattedPrice;
    document.getElementById('modalProductDescription').innerText = description;
    document.getElementById('modalPreviewQty').value = 1;
    const viewSpecsBtn = document.getElementById('modalViewFullSpecsBtn');
    if (viewSpecsBtn) {
        viewSpecsBtn.href = '<?= BASE_URL ?>product_detail.php?id=' + id;
    }

    const imgEl = document.getElementById('modalProductImage');
    const iconEl = document.getElementById('modalProductFallbackIcon');

    if (image && image.trim() !== '') {
        imgEl.src = image;
        imgEl.classList.remove('d-none');
        iconEl.classList.add('d-none');
    } else {
        imgEl.classList.add('d-none');
        iconEl.classList.remove('d-none');
    }

    bsModal.show();
}

function openPhoneModalFromCard(card, bsPhoneModal) {
    const id = parseInt(card.dataset.id);
    const name = card.dataset.name;
    const brand = card.dataset.brand;
    const model = card.dataset.model;
    const storage = card.dataset.storage;
    const color = card.dataset.color;
    const condition = card.dataset.condition;
    const battery = card.dataset.battery;
    const imei = card.dataset.imei;
    const price = parseFloat(card.dataset.price);
    const formattedPrice = card.dataset.formattedPrice;
    const notes = card.dataset.notes;
    const image = card.dataset.image;

    currentPreviewPhone = { id, name, brand, model, storage, color, condition, battery, imei, price, formattedPrice, notes, image };

    document.getElementById('modalPhoneBrand').innerText = brand;
    document.getElementById('modalPhoneCondition').innerText = condition;
    document.getElementById('modalPhoneName').innerText = brand + ' ' + model;
    document.getElementById('modalPhoneSpecs').innerText = storage + ' • ' + color + (battery ? (' • ' + battery + '% Battery Health') : '');
    document.getElementById('modalPhonePrice').innerText = formattedPrice;
    document.getElementById('modalSpecStorage').innerText = 'Storage: ' + storage;
    document.getElementById('modalSpecColor').innerText = 'Color: ' + color;
    document.getElementById('modalSpecBattery').innerText = 'Battery: ' + (battery ? (battery + '%') : 'Tested OK');
    document.getElementById('modalSpecImei').innerText = imei;
    document.getElementById('modalPhoneNotes').innerText = notes;
    const viewPhoneSpecsBtn = document.getElementById('modalPhoneViewFullSpecsBtn');
    if (viewPhoneSpecsBtn) {
        viewPhoneSpecsBtn.href = '<?= BASE_URL ?>product_detail.php?type=unit&id=' + id;
    }

    const imgEl = document.getElementById('modalPhoneImage');
    const iconEl = document.getElementById('modalPhoneFallbackIcon');

    if (image && image.trim() !== '') {
        imgEl.src = image;
        imgEl.classList.remove('d-none');
        iconEl.classList.add('d-none');
    } else {
        imgEl.classList.add('d-none');
        iconEl.classList.remove('d-none');
    }

    bsPhoneModal.show();
}

function adjustPreviewQty(delta) {
    const input = document.getElementById('modalPreviewQty');
    let val = parseInt(input.value) || 1;
    val = Math.max(1, Math.min(20, val + delta));
    input.value = val;
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
