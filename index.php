<?php
$page_title = "Home - 8J Cellphone Accessories";
require_once __DIR__ . '/includes/header.php';

// Fetch featured products and phone units
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

<style>
.hero-mesh-pink {
    background-color: #fff5f8 !important;
    background-image: 
        radial-gradient(circle at 15% 25%, rgba(244, 114, 182, 0.28) 0%, transparent 50%),
        radial-gradient(circle at 85% 75%, rgba(251, 207, 232, 0.4) 0%, transparent 50%),
        linear-gradient(135deg, #fff5f8 0%, #fce7f3 50%, #fbcfe8 100%) !important;
    position: relative;
    overflow: hidden;
}
.hero-mesh-pink::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background-image: radial-gradient(rgba(219, 39, 119, 0.12) 1px, transparent 1px);
    background-size: 24px 24px;
    opacity: 0.55;
    pointer-events: none;
}
.bento-card {
    background: #ffffff;
    border: 1px solid #fce7f3;
    border-radius: 20px;
    padding: 2rem;
    position: relative;
    overflow: hidden;
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.35s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.3s ease;
}
.bento-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 36px -12px rgba(244, 114, 182, 0.25);
    border-color: #f472b6;
}
.bento-card.dark {
    background: linear-gradient(135deg, #2d1122 0%, #4a1534 100%) !important;
    border-color: #831843 !important;
    color: #ffffff;
}
.bento-card.dark:hover {
    border-color: #f472b6 !important;
    box-shadow: 0 20px 40px -15px rgba(74, 21, 52, 0.5) !important;
}
</style>

<div class="container-fluid px-0">
    <!-- Award-Winning Centered Hero Section (Light Pink Aesthetic) -->
    <div class="hero-mesh-pink py-5 position-relative border-bottom" 
         style="background: #fff5f8 linear-gradient(135deg, #fff5f8 0%, #fce7f3 50%, #fbcfe8 100%) !important; color: #2b1120 !important; border-color: #fbcfe8 !important;">
        <div class="container py-4 py-lg-5 position-relative" style="z-index: 2;">
            <div class="row justify-content-center">
                
                <div class="col-lg-10 col-xl-9 text-center">
                    <!-- Store Pill -->
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill mb-4 border" 
                         style="background: rgba(255, 255, 255, 0.95); border-color: #fbcfe8 !important; backdrop-filter: blur(12px); box-shadow: 0 2px 10px rgba(244, 114, 182, 0.15);">
                        <i class="fa-solid fa-store" style="font-size: 0.8rem; color: #db2777;"></i>
                        <span class="small fw-bold" style="font-size: 0.78rem; letter-spacing: 0.5px; color: #831843;">
                            Tanauan City Store Hub • In-Store Pick-Up & Certified Repairs
                        </span>
                    </div>

                    <!-- Main Display Headline -->
                    <h1 class="display-3 fw-extrabold mb-3" style="letter-spacing: -1.5px; line-height: 1.1; color: #2b1120;">
                        Engineered for Devices.<br>
                        <span style="background: linear-gradient(135deg, #db2777 0%, #e11d48 50%, #f472b6 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                            Built for Everyday Speed.
                        </span>
                    </h1>

                    <p class="lead mb-4 mx-auto" style="max-width: 660px; font-size: 1.1rem; line-height: 1.65; color: #6b2d4f;">
                        Your integrated hub for genuine cellphone accessories, transparent staff-graded phone trade-ins, and precision technician repairs with guaranteed same-day counter pick-up.
                    </p>

                    <!-- Interactive Action Buttons -->
                    <div class="d-flex flex-wrap justify-content-center gap-3 mb-5">
                        <a href="<?= BASE_URL ?>catalog.php" class="btn btn-primary btn-lg rounded-pill px-4 fw-bold shadow-lg d-inline-flex align-items-center gap-2 btn-tactile">
                            <i class="fa-solid fa-bag-shopping"></i> Browse Store Catalog
                        </a>
                        <a href="<?= BASE_URL ?>book_repair.php" class="btn btn-outline-dark btn-lg rounded-pill px-4 fw-semibold d-inline-flex align-items-center gap-2" style="background: #ffffff !important; border-color: #f472b6 !important; color: #db2777 !important;">
                            <i class="fa-solid fa-screwdriver-wrench"></i> Book Repair Service
                        </a>
                        <a href="<?= BASE_URL ?>track_repair.php" class="btn btn-outline-secondary btn-lg rounded-pill px-4 d-inline-flex align-items-center gap-2" style="background: rgba(255, 255, 255, 0.85) !important; border-color: #fbcfe8 !important; color: #831843 !important;">
                            <i class="fa-solid fa-barcode"></i> Track Order
                        </a>
                    </div>

                    <!-- Live Metrics Strip -->
                    <div class="row g-4 justify-content-center pt-4 border-top mx-auto" style="max-width: 760px; border-color: #fbcfe8 !important;">
                        <div class="col-6 col-md-3 text-center">
                            <div class="fw-extrabold fs-3" style="color: #9d174d;">1-Hour</div>
                            <small class="fw-semibold" style="font-size: 0.78rem; color: #831843;">Store Pick-Up</small>
                        </div>
                        <div class="col-6 col-md-3 text-center">
                            <div class="fw-extrabold fs-3" style="color: #9d174d;">100%</div>
                            <small class="fw-semibold" style="font-size: 0.78rem; color: #831843;">Tested Genuine</small>
                        </div>
                        <div class="col-6 col-md-3 text-center">
                            <div class="fw-extrabold fs-3" style="color: #9d174d;">₱0</div>
                            <small class="fw-semibold" style="font-size: 0.78rem; color: #831843;">Reservation Fee</small>
                        </div>
                        <div class="col-6 col-md-3 text-center">
                            <div class="fw-extrabold fs-3" style="color: #9d174d;">4.9 ★</div>
                            <small class="fw-semibold" style="font-size: 0.78rem; color: #831843;">Tanauan Store Rating</small>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- The 8J Advantage - Bento Grid Architecture -->
    <div class="container my-5 py-3">
        <div class="text-center max-w-xl mx-auto mb-5">
            <span class="badge bg-light text-dark border px-3 py-1 rounded-pill fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                The 8J Standard
            </span>
            <h2 class="display-5 fw-extrabold text-dark mt-2 mb-2" style="letter-spacing: -1px;">
                Why Customers in Tanauan Choose 8J
            </h2>
            <p class="text-muted">A modern retail & diagnostic workflow built on transparency, certified parts, and counter speed.</p>
        </div>

        <div class="row g-4">
            <!-- Bento Item 1: Counter Pick-Up Showcase (Span 8) -->
            <div class="col-lg-8">
                <div class="bento-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-dark text-white rounded-pill px-3 py-1.5">Same-Day In-Store Pick-Up</span>
                            <span class="text-muted small"><i class="fa-solid fa-bolt text-warning me-1"></i> Ready in 60 Mins</span>
                        </div>
                        <h3 class="fw-extrabold text-dark mb-2">Order Online, Inspect at Counter, Walk Out Ready</h3>
                        <p class="text-secondary mb-4" style="max-width: 520px; line-height: 1.6;">
                            Never wait for shipping delays. Reserve your accessories or certified smartphones through our online catalog with zero reservation fees. Walk into our Tanauan City plaza counter, test your item, and pay via Cash or GCash.
                        </p>
                    </div>

                    <!-- Simulated Live Digital Claim Ticket -->
                    <div class="p-3 bg-light rounded-4 border d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-dark text-white rounded-circle p-2.5 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fa-solid fa-receipt fs-5"></i>
                            </div>
                            <div>
                                <strong class="d-block text-dark">Digital Pickup Verification Ticket</strong>
                                <span class="text-muted small">Generated instantly upon reserving • Claim Code: <code class="text-dark fw-bold">8J-5921</code></span>
                            </div>
                        </div>
                        <a href="<?= BASE_URL ?>catalog.php" class="btn btn-outline-dark rounded-pill px-3 py-1.5 btn-sm fw-semibold">
                            Try It Now <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bento Item 2: 25-Point Diagnostic (Span 4) -->
            <div class="col-lg-4">
                <div class="bento-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="badge bg-light text-dark border rounded-pill px-3 py-1.5 mb-3">Hardware Standard</div>
                        <h4 class="fw-extrabold text-dark mb-2">25-Point Physical Diagnostic</h4>
                        <p class="text-muted small mb-3">Every pre-owned phone and repair unit undergoes full testing by in-house technicians.</p>
                        
                        <div class="d-flex flex-column gap-2 small">
                            <div class="d-flex align-items-center gap-2 text-secondary">
                                <i class="fa-solid fa-circle-check text-dark"></i> Original OLED & Digitizer Touch
                            </div>
                            <div class="d-flex align-items-center gap-2 text-secondary">
                                <i class="fa-solid fa-circle-check text-dark"></i> Face ID / Touch ID Verified
                            </div>
                            <div class="d-flex align-items-center gap-2 text-secondary">
                                <i class="fa-solid fa-circle-check text-dark"></i> Clean iCloud & Google Accounts
                            </div>
                            <div class="d-flex align-items-center gap-2 text-secondary">
                                <i class="fa-solid fa-circle-check text-dark"></i> Openline to All Philippine SIMs
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top text-muted small">
                        <i class="fa-solid fa-award text-dark me-1"></i> Technician Certified Quality Seal
                    </div>
                </div>
            </div>

            <!-- Bento Item 3: Anti-Fencing Compliance (Span 4) -->
            <div class="col-lg-4">
                <div class="bento-card h-100">
                    <div class="badge bg-light text-dark border rounded-pill px-3 py-1.5 mb-3">100% Legal & Safe</div>
                    <h4 class="fw-extrabold text-dark mb-2">Anti-Fencing Law Compliance</h4>
                    <p class="text-muted small mb-0" style="line-height: 1.6;">
                        All trade-in and buyout devices are verified through government-issued valid IDs, affidavit declarations, and NTC IMEI records, ensuring absolute peace of mind for every buyer.
                    </p>
                </div>
            </div>

            <!-- Bento Item 4: 7-Day Replacement Warranty (Span 4) -->
            <div class="col-lg-4">
                <div class="bento-card h-100">
                    <div class="badge bg-light text-dark border rounded-pill px-3 py-1.5 mb-3">Zero-Risk Guarantee</div>
                    <h4 class="fw-extrabold text-dark mb-2">7-Day Direct Swap Warranty</h4>
                    <p class="text-muted small mb-0" style="line-height: 1.6;">
                        Any accessory with a manufacturing defect within 7 days is swapped 1-to-1 over the counter at our Tanauan store immediately—no service center delays.
                    </p>
                </div>
            </div>

            <!-- Bento Item 5: Professional Repairs (Span 4) -->
            <div class="col-lg-4">
                <div class="bento-card h-100">
                    <div class="badge bg-light text-dark border rounded-pill px-3 py-1.5 mb-3">Micro-Soldering & LCD</div>
                    <h4 class="fw-extrabold text-dark mb-2">Expert Technician Workbench</h4>
                    <p class="text-muted small mb-0" style="line-height: 1.6;">
                        From cracked screen replacements to battery degradation and charging port failures, book your repair online and monitor the job status in real time.
                    </p>
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

    <!-- 4-Step Customer Experience Timeline -->
    <div class="py-5 bg-white border-top border-bottom border-light-subtle my-5">
        <div class="container py-3">
            <div class="text-center max-w-xl mx-auto mb-5">
                <span class="badge bg-light text-dark border px-3 py-1 rounded-pill fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                    Seamless In-Store Experience
                </span>
                <h2 class="display-6 fw-extrabold text-dark mt-2 mb-2" style="letter-spacing: -0.5px;">
                    How Store Pickup & Trade-In Works
                </h2>
                <p class="text-muted">Zero online payment risk. Reserve from your phone, inspect in person, take home today.</p>
            </div>

            <div class="row g-4">
                <!-- Step 1 -->
                <div class="col-md-6 col-lg-3">
                    <div class="bento-card h-100 d-flex flex-column justify-content-between p-4">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="badge bg-dark text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-weight: 800; font-size: 0.9rem;">
                                    01
                                </span>
                                <span class="badge bg-light text-dark border small"><i class="fa-regular fa-clock me-1"></i> &lt; 2 Mins</span>
                            </div>
                            <h5 class="fw-bold text-dark mb-2">Browse Live Stock</h5>
                            <p class="text-secondary small mb-0" style="line-height: 1.6;">
                                Explore real-time Tanauan inventory of genuine accessories and technician-certified smartphones with transparent pricing.
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-top text-muted small">
                            <i class="fa-solid fa-check text-dark me-1"></i> Live Stock Sync
                        </div>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="col-md-6 col-lg-3">
                    <div class="bento-card h-100 d-flex flex-column justify-content-between p-4">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="badge bg-dark text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-weight: 800; font-size: 0.9rem;">
                                    02
                                </span>
                                <span class="badge bg-light text-dark border small"><i class="fa-solid fa-bolt text-warning me-1"></i> Instant</span>
                            </div>
                            <h5 class="fw-bold text-dark mb-2">Generate Claim Slip</h5>
                            <p class="text-secondary small mb-0" style="line-height: 1.6;">
                                Confirm your reservation with 0 reservation fee. A digital claim ticket is created instantly with a verified reference code.
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-top text-muted small">
                            <i class="fa-solid fa-check text-dark me-1"></i> Zero Upfront Deposit
                        </div>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="col-md-6 col-lg-3">
                    <div class="bento-card h-100 d-flex flex-column justify-content-between p-4">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="badge bg-dark text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-weight: 800; font-size: 0.9rem;">
                                    03
                                </span>
                                <span class="badge bg-light text-dark border small"><i class="fa-solid fa-screwdriver-wrench text-dark me-1"></i> 15 Mins</span>
                            </div>
                            <h5 class="fw-bold text-dark mb-2">Counter Test & Check</h5>
                            <p class="text-secondary small mb-0" style="line-height: 1.6;">
                                Visit our Tanauan store. Our technician unboxes the unit and runs full audio, touch, and battery tests before you hand over any payment.
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-top text-muted small">
                            <i class="fa-solid fa-check text-dark me-1"></i> Hands-On Verification
                        </div>
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="col-md-6 col-lg-3">
                    <div class="bento-card h-100 d-flex flex-column justify-content-between p-4">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="badge bg-dark text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-weight: 800; font-size: 0.9rem;">
                                    04
                                </span>
                                <span class="badge bg-light text-dark border small"><i class="fa-solid fa-shield-halved text-success me-1"></i> 7-Day Warranty</span>
                            </div>
                            <h5 class="fw-bold text-dark mb-2">Pay & Walk Out</h5>
                            <p class="text-secondary small mb-0" style="line-height: 1.6;">
                                Pay easily via Cash or GCash. You receive an official warranty receipt covering 7-day direct replacement on all accessories.
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-top text-muted small">
                            <i class="fa-solid fa-check text-dark me-1"></i> Official Warranty Receipt
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tanauan Physical Store & Frequently Asked Questions -->
    <div class="container my-5 py-3">
        <div class="row g-4 align-items-start">
            <!-- Left: Tanauan Store Hub Card -->
            <div class="col-lg-5">
                <div class="bento-card dark h-100 p-4 p-xl-5">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-light text-dark rounded-pill px-3 py-1 font-monospace" style="font-size: 0.72rem;">
                            <i class="fa-solid fa-shop me-1 text-secondary"></i> STORE HUB
                        </span>
                        <span class="text-white-50 small">Tanauan City, Batangas</span>
                    </div>

                    <h3 class="fw-extrabold text-white mb-3" style="letter-spacing: -0.5px;">Visit Our Physical Service Desk</h3>
                    <p class="text-white-50 small mb-4" style="line-height: 1.65;">
                        Located right in the heart of Tanauan City. Drop by for express accessory pickup, walk-in device diagnostic appraisals, or precision glass screen installation.
                    </p>

                    <div class="d-flex flex-column gap-3 mb-4">
                        <div class="d-flex align-items-start gap-3">
                            <div class="bg-secondary bg-opacity-25 rounded-circle p-2.5 text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div>
                                <strong class="text-white d-block small">Store Address</strong>
                                <span class="text-white-50" style="font-size: 0.82rem;">Plaza Rizal Commercial Arcade, Poblacion 1, Tanauan City, Batangas (Beside Town Plaza)</span>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-3">
                            <div class="bg-secondary bg-opacity-25 rounded-circle p-2.5 text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <div>
                                <strong class="text-white d-block small">Operating Hours</strong>
                                <span class="text-white-50" style="font-size: 0.82rem;">Monday – Sunday: 8:00 AM – 7:00 PM (Including Holidays)</span>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-3">
                            <div class="bg-secondary bg-opacity-25 rounded-circle p-2.5 text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                            <div>
                                <strong class="text-white d-block small">Direct Helpline</strong>
                                <span class="text-white-50" style="font-size: 0.82rem;">(043) 778-8JIMS • 0917-888-8JIMS</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="<?= BASE_URL ?>book_repair.php" class="btn btn-light rounded-pill py-2.5 fw-bold d-flex align-items-center justify-content-center gap-2">
                            <i class="fa-solid fa-calendar-check"></i> Book Technician Appointment
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right: Interactive FAQ Accordion -->
            <div class="col-lg-7">
                <div class="bento-card h-100 p-4 p-xl-5">
                    <div class="mb-4">
                        <span class="badge bg-light text-dark border px-3 py-1 rounded-pill fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                            Answers & Policies
                        </span>
                        <h3 class="fw-extrabold text-dark mt-2 mb-1" style="letter-spacing: -0.5px;">Frequently Asked Questions</h3>
                        <p class="text-muted small">Everything you need to know about purchasing, trade-ins, and warranty terms.</p>
                    </div>

                    <div class="accordion accordion-flush" id="faqAccordion">
                        <!-- FAQ 1 -->
                        <div class="accordion-item border-bottom">
                            <h2 class="accordion-header" id="faqHead1">
                                <button class="accordion-button fw-bold text-dark collapsed px-0 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#faqCol1" aria-expanded="false" aria-controls="faqCol1">
                                    How does the in-store trade-in valuation work?
                                </button>
                            </h2>
                            <div id="faqCol1" class="accordion-collapse collapse" aria-labelledby="faqHead1" data-bs-parent="#faqAccordion">
                                <div class="accordion-body px-0 text-secondary small" style="line-height: 1.65;">
                                    Our system uses an advisory market benchmark matrix based on your phone model and cosmetic condition (Grade A, B, or C). When you visit our Tanauan counter, our certified technician physically inspects the device for touch responsiveness, camera autofocus, battery health, and body scratches, determining your transparent final valuation. You can apply this value toward an upgrade or receive immediate cash.
                                </div>
                            </div>
                        </div>

                        <!-- FAQ 2 -->
                        <div class="accordion-item border-bottom">
                            <h2 class="accordion-header" id="faqHead2">
                                <button class="accordion-button fw-bold text-dark collapsed px-0 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#faqCol2" aria-expanded="false" aria-controls="faqCol2">
                                    Do I need to pay upfront when reserving online?
                                </button>
                            </h2>
                            <div id="faqCol2" class="accordion-collapse collapse" aria-labelledby="faqHead2" data-bs-parent="#faqAccordion">
                                <div class="accordion-body px-0 text-secondary small" style="line-height: 1.65;">
                                    No upfront deposit is required. When you submit an order through our online catalog, our system holds the item under your unique Claim Verification Code. You have 24 hours to visit our Tanauan City store, inspect the item in person, and settle payment via Cash or GCash.
                                </div>
                            </div>
                        </div>

                        <!-- FAQ 3 -->
                        <div class="accordion-item border-bottom">
                            <h2 class="accordion-header" id="faqHead3">
                                <button class="accordion-button fw-bold text-dark collapsed px-0 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#faqCol3" aria-expanded="false" aria-controls="faqCol3">
                                    What does the 25-Point Physical Diagnostic cover?
                                </button>
                            </h2>
                            <div id="faqCol3" class="accordion-collapse collapse" aria-labelledby="faqHead3" data-bs-parent="#faqAccordion">
                                <div class="accordion-body px-0 text-secondary small" style="line-height: 1.65;">
                                    Every pre-owned smartphone undergoes an exhaustive hardware test including display touch digitizer, Face ID / Touch ID sensors, microphone and stereo speakers, charging port amperage, Wi-Fi / Bluetooth antennas, cellular SIM detection, True Tone, and battery charge cycles.
                                </div>
                            </div>
                        </div>

                        <!-- FAQ 4 -->
                        <div class="accordion-item border-bottom">
                            <h2 class="accordion-header" id="faqHead4">
                                <button class="accordion-button fw-bold text-dark collapsed px-0 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#faqCol4" aria-expanded="false" aria-controls="faqCol4">
                                    What are the legal compliance requirements for phone buyouts?
                                </button>
                            </h2>
                            <div id="faqCol4" class="accordion-collapse collapse" aria-labelledby="faqHead4" data-bs-parent="#faqAccordion">
                                <div class="accordion-body px-0 text-secondary small" style="line-height: 1.65;">
                                    In strict accordance with Presidential Decree No. 1612 (Anti-Fencing Law), customers selling or trading handsets must present one government-issued valid ID, sign an affidavit of legal ownership, and verify clean iCloud / Google account sign-out with unblacklisted NTC IMEI records.
                                </div>
                            </div>
                        </div>

                        <!-- FAQ 5 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faqHead5">
                                <button class="accordion-button fw-bold text-dark collapsed px-0 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#faqCol5" aria-expanded="false" aria-controls="faqCol5">
                                    How does the 7-day direct swap warranty work?
                                </button>
                            </h2>
                            <div id="faqCol5" class="accordion-collapse collapse" aria-labelledby="faqHead5" data-bs-parent="#faqAccordion">
                                <div class="accordion-body px-0 text-secondary small" style="line-height: 1.65;">
                                    If any accessory or certified unit exhibits a factory defect or technical failure within 7 calendar days of purchase, simply bring your official receipt to our Tanauan counter. We replace the unit immediately with an identical one with no waiting periods or service center delays.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
