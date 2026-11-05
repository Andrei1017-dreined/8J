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
$has_3d = has_online_3d_model($item_name, $item['brand'] ?? '');
$bento_specs = get_quick_bento_specs($item, $specs);

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

// Image resolver & Outsourced Commercial Video
$main_image = BASE_URL . 'assets/img/icons/shop.png';
if (!empty($specs['gallery'][0]) && file_exists(__DIR__ . '/assets/img/products/' . $specs['gallery'][0])) {
    $main_image = BASE_URL . 'assets/img/products/' . $specs['gallery'][0];
} elseif (!empty($item['image']) && file_exists(__DIR__ . '/assets/img/products/' . $item['image'])) {
    $main_image = BASE_URL . 'assets/img/products/' . $item['image'];
}
$detail_ad_vid = get_product_ad_video($item_name, $item['brand'] ?? '');
?>

<?php
/* ---- Premium product hero (generated block) ---- */
$gallery_imgs = [];
foreach (($specs['gallery'] ?? []) as $gImg) {
    if (file_exists(__DIR__ . '/assets/img/products/' . $gImg)) {
        $gallery_imgs[] = BASE_URL . 'assets/img/products/' . $gImg;
    }
}
if (empty($gallery_imgs)) { $gallery_imgs[] = $main_image; }
$is_handset = ($type === 'unit') || stripos($sku, 'PHN-') === 0;
$brand_label = $item['brand'] ?? '8J';
$condition_label = $type === 'unit' ? ($item['unit_condition'] ?? 'Certified') : ($is_handset ? 'Brand New' : 'Genuine');
// 3D is only offered when it is a real model: a verified Sketchfab CAD stream (has_online_3d_model)
// or a scanned/CAD file in assets/models/. Android phones are not given a generated stand-in.
$model_file_url = local_3d_model_url($item['brand'] ?? '', $type === 'unit' ? ($item['model_name'] ?? '') : $item_name);
if ($model_file_url) {
    $has_3d = true;
}
?>
<style>
.pd-wrap{max-width:1240px}
.pd-shell{background:#fff;border-radius:32px;padding:clamp(18px,3vw,44px);box-shadow:0 30px 80px -40px rgba(15,23,42,.25)}
.pd-stage{position:relative;aspect-ratio:1/1;max-height:600px;width:100%;border-radius:28px;background:radial-gradient(120% 90% at 50% 30%,#ffffff 0%,#f5f5f7 70%,#ececf0 100%);overflow:hidden;display:flex;align-items:center;justify-content:center}
.pd-stage img.pd-main{max-width:82%;max-height:82%;object-fit:contain;transition:opacity .35s ease,transform .6s cubic-bezier(.16,1,.3,1);filter:drop-shadow(0 30px 40px rgba(15,23,42,.18));mix-blend-mode:multiply}
.pd-stage:hover img.pd-main{transform:scale(1.04)}
.pd-nav{position:absolute;top:50%;transform:translateY(-50%);width:44px;height:44px;border-radius:50%;border:0;background:rgba(255,255,255,.85);backdrop-filter:blur(8px);box-shadow:0 6px 20px rgba(15,23,42,.12);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .25s;z-index:6;color:#111}
.pd-stage:hover .pd-nav{opacity:1}
.pd-nav.prev{left:16px}.pd-nav.next{right:16px}
.pd-count{position:absolute;bottom:16px;right:18px;font-size:.72rem;font-weight:600;color:#6b7280;background:rgba(255,255,255,.8);padding:4px 10px;border-radius:999px;z-index:6}
.pd-layer{position:absolute;inset:0;z-index:5;display:none;background:#f5f5f7}
.pd-layer.active{display:block}
.pd-layer video{width:100%;height:100%;object-fit:cover;background:#000}
.pd-close-layer{position:absolute;top:14px;right:14px;z-index:8;border:0;border-radius:999px;background:rgba(17,17,17,.75);color:#fff;font-size:.75rem;font-weight:600;padding:6px 14px}
.pd-rail{display:flex;gap:10px;overflow-x:auto;padding:14px 2px 4px;scrollbar-width:none}
.pd-rail::-webkit-scrollbar{display:none}
.pd-thumb{flex:0 0 74px;height:74px;border-radius:16px;border:2px solid transparent;background:#f5f5f7;padding:6px;cursor:pointer;transition:border-color .2s,transform .2s;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:2px;font-size:.66rem;font-weight:700;color:#374151}
.pd-thumb img{width:100%;height:100%;object-fit:contain;mix-blend-mode:multiply}
.pd-thumb:hover{transform:translateY(-2px)}
.pd-thumb.active{border-color:#111}
.pd-thumb.special{background:#111;color:#fff}
.pd-thumb.special i{font-size:1.1rem}
.pd-eyebrow{font-size:.78rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#be185d}
.pd-title{font-size:clamp(1.8rem,3.2vw,2.7rem);font-weight:800;letter-spacing:-.03em;line-height:1.08;color:#0b0b0f}
.pd-tagline{color:#6b7280;font-size:1.02rem;line-height:1.55}
.pd-price{font-size:clamp(2rem,3.4vw,2.6rem);font-weight:800;letter-spacing:-.03em;color:#0b0b0f}
.pd-chip{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:999px;background:#f5f5f7;font-size:.8rem;font-weight:600;color:#1f2937}
.pd-chip.ok{background:#ecfdf5;color:#047857}
.pd-btn-primary{background:#0b0b0f;color:#fff;border:0;border-radius:999px;padding:16px 26px;font-weight:700;width:100%;transition:transform .2s,background .2s}
.pd-btn-primary:hover{background:#be185d;color:#fff;transform:translateY(-1px)}
.pd-btn-ghost{background:#fff;color:#0b0b0f;border:1.5px solid #e5e7eb;border-radius:999px;padding:15px 26px;font-weight:700;width:100%;transition:border-color .2s}
.pd-btn-ghost:hover{border-color:#0b0b0f}
.pd-qty{display:inline-flex;align-items:center;border:1.5px solid #e5e7eb;border-radius:999px;overflow:hidden}
.pd-qty button{border:0;background:transparent;width:42px;height:46px;font-size:1rem}
.pd-qty input{width:40px;border:0;text-align:center;font-weight:700;background:transparent}
.pd-spec-tile{background:#f5f5f7;border-radius:20px;padding:18px;height:100%}
.pd-spec-tile i{font-size:1.15rem;color:#be185d}
.pd-spec-tile .lbl{font-size:.68rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#9ca3af;margin-top:10px}
.pd-spec-tile .val{font-size:.9rem;font-weight:700;color:#111;line-height:1.35}
.pd-trust{display:flex;gap:22px;flex-wrap:wrap;font-size:.8rem;color:#4b5563}
.pd-trust i{color:#111;margin-right:6px}
.pd-hl li{padding:9px 0;border-bottom:1px solid #f1f1f4;font-size:.9rem;color:#374151;display:flex;gap:10px}
.pd-hl li:last-child{border-bottom:0}
.pd-hl li i{color:#be185d;margin-top:4px;font-size:.7rem}
@media (max-width:991px){.pd-stage{aspect-ratio:4/3.4}}
</style>

<div class="container pd-wrap py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0 bg-transparent p-0 small">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>" class="text-decoration-none text-muted">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>catalog.php<?= $is_handset ? '?type=units' : '' ?>" class="text-decoration-none text-muted"><?= $is_handset ? 'Phones & Tablets' : 'Accessories' ?></a></li>
            <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page"><?= htmlspecialchars($item_name) ?></li>
        </ol>
    </nav>

    <div class="pd-shell mb-5">
        <div class="row g-4 g-lg-5 align-items-start">
            <!-- Gallery -->
            <div class="col-lg-7">
                <div class="position-sticky" style="top:90px">
                    <div class="pd-stage" id="pdStage">
                        <img id="mainProductView" class="pd-main" src="<?= htmlspecialchars($gallery_imgs[0]) ?>" alt="<?= htmlspecialchars($item_name) ?>">
                        <?php if (count($gallery_imgs) > 1): ?>
                            <button type="button" class="pd-nav prev" onclick="pdStep(-1)" aria-label="Previous"><i class="fa-solid fa-chevron-left"></i></button>
                            <button type="button" class="pd-nav next" onclick="pdStep(1)" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>
                            <span class="pd-count" id="pdCount">1 / <?= count($gallery_imgs) ?></span>
                        <?php endif; ?>

                        <?php if ($has_3d): ?>
                            <div class="pd-layer" id="mainProduct3DWrapper">
                                <div id="mainProduct3DCanvas" style="position:absolute;inset:0;overflow:hidden"></div>
                                <button type="button" class="pd-close-layer" onclick="pdShowPhoto(pdIndex)"><i class="fa-solid fa-xmark me-1"></i>Close 3D</button>
                            </div>
                        <?php else: ?>
                            <div class="pd-layer" id="mainProductNo3DWrapper">
                                <div class="d-flex flex-column align-items-center justify-content-center text-center p-4 w-100 h-100" style="background: radial-gradient(circle at 50% 35%, #ffffff 0%, #f8fafc 65%, #f1f5f9 100%);">
                                    <div class="position-relative mb-3 d-flex align-items-center justify-content-center" style="width: 88px; height: 88px; border-radius: 24px; background: #ffffff; box-shadow: 0 12px 30px rgba(15,23,42,0.08); border: 1px solid #e2e8f0;">
                                        <i class="fa-solid fa-cube text-secondary opacity-40 fs-1"></i>
                                        <span class="badge bg-danger text-white rounded-pill position-absolute bottom-0 translate-middle-y px-2 py-0.5" style="font-size: 0.62rem; font-weight: 800;">
                                            <i class="fa-solid fa-ban me-1"></i>N/A
                                        </span>
                                    </div>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 mb-2 fw-extrabold" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                                        3D MODEL UNAVAILABLE
                                    </span>
                                    <h5 class="fw-extrabold text-dark mb-1" style="max-width: 380px;">No 3D CAD Model Available</h5>
                                    <p class="text-muted small mb-3" style="max-width: 380px; font-size: 0.82rem; line-height: 1.5;">
                                        An authentic 3D CAD mesh is not available online for <strong><?= htmlspecialchars($item_name) ?></strong>. Please inspect our high-resolution multi-angle studio gallery below.
                                    </p>
                                    <button type="button" onclick="pdShowPhoto(0)" class="btn btn-dark btn-sm rounded-pill px-4 py-2 fw-bold shadow-sm">
                                        <i class="fa-solid fa-images text-info me-1.5"></i> View Studio Photos
                                    </button>
                                </div>
                                <button type="button" class="pd-close-layer" onclick="pdShowPhoto(pdIndex)"><i class="fa-solid fa-xmark me-1"></i>Close</button>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($detail_ad_vid)): ?>
                            <div class="pd-layer" id="pdVideoLayer">
                                <?php if (($detail_ad_vid['type'] ?? '') === 'youtube'): ?>
                                    <iframe id="mainProductAdIframe"
                                            data-src="<?= htmlspecialchars($detail_ad_vid['hd']) ?>"
                                            src=""
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                            allowfullscreen
                                            style="position:absolute;inset:0;width:100%;height:100%;border:0;"></iframe>
                                <?php else: ?>
                                    <video id="mainProductAdVideo" muted loop playsinline preload="none" data-ad-src="<?= htmlspecialchars($detail_ad_vid['hd']) ?>"></video>
                                <?php endif; ?>
                                <button type="button" class="pd-close-layer" onclick="pdShowPhoto(pdIndex)"><i class="fa-solid fa-xmark me-1"></i>Close</button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="pd-rail" id="pdRail">
                        <?php foreach ($gallery_imgs as $i => $g): ?>
                            <button type="button" class="pd-thumb <?= $i === 0 ? 'active' : '' ?>" data-idx="<?= $i ?>" onclick="pdShowPhoto(<?= $i ?>)">
                                <img src="<?= htmlspecialchars($g) ?>" alt="" loading="lazy">
                            </button>
                        <?php endforeach; ?>
                        <?php if ($has_3d): ?>
                            <button type="button" class="pd-thumb special" id="pdThumb3D" onclick="pdShow3D()"><i class="fa-solid fa-cube"></i>3D</button>
                        <?php else: ?>
                            <button type="button" class="pd-thumb special na" id="pdThumb3D" onclick="pdShowNo3D()" title="3D Model Unavailable"><i class="fa-solid fa-cube opacity-40"></i><span style="font-size:0.60rem;color:#ef4444;font-weight:800;letter-spacing:0.04em;">3D N/A</span></button>
                        <?php endif; ?>
                        <?php if (!empty($detail_ad_vid)): ?>
                            <button type="button" class="pd-thumb special" id="pdThumbVid" onclick="pdShowVideo()"><i class="fa-solid fa-play"></i>Film</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Buy box -->
            <div class="col-lg-5">
                <div class="pd-eyebrow mb-2"><?= htmlspecialchars($brand_label) ?> · <?= htmlspecialchars($condition_label) ?></div>
                <h1 class="pd-title mb-3"><?= htmlspecialchars($item_name) ?></h1>
                <p class="pd-tagline mb-3"><?= htmlspecialchars($specs['tagline']) ?></p>

                <div class="d-flex align-items-center gap-2 mb-4 small">
                    <span class="text-warning"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></span>
                    <a href="#reviewsSection" class="text-decoration-none text-muted">4.9 · 14 reviews</a>
                </div>

                <div class="pd-price mb-1"><?= format_currency($price) ?></div>
                <div class="text-muted small mb-4">VAT inclusive · Pay at the counter · No reservation fee</div>

                <div class="d-flex flex-wrap gap-2 mb-4">
                    <?php if ($type === 'unit' && !empty($item['storage_capacity'])): ?>
                        <span class="pd-chip"><i class="fa-solid fa-hard-drive"></i><?= htmlspecialchars($item['storage_capacity']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($item['color'])): ?>
                        <span class="pd-chip"><i class="fa-solid fa-palette"></i><?= htmlspecialchars($item['color']) ?></span>
                    <?php endif; ?>
                    <?php if ($type === 'unit' && !empty($item['battery_health'])): ?>
                        <span class="pd-chip"><i class="fa-solid fa-battery-three-quarters"></i><?= (int)$item['battery_health'] ?>% battery</span>
                    <?php endif; ?>
                    <span class="pd-chip <?= $stock > 0 ? 'ok' : '' ?>"><i class="fa-solid fa-circle" style="font-size:.5rem"></i><?= $stock > 0 ? 'In stock · Ready today' : 'Out of stock' ?></span>
                    <?php if ($has_3d): ?>
                        <span class="pd-chip" style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;"><i class="fa-solid fa-cube text-success"></i>3D Model Available</span>
                    <?php else: ?>
                        <span class="pd-chip" style="background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0;"><i class="fa-solid fa-cube opacity-40"></i>3D Unavailable</span>
                    <?php endif; ?>
                </div>

                <div class="d-flex gap-2 mb-2 align-items-stretch">
                    <?php if ($type !== 'unit'): ?>
                        <div class="pd-qty">
                            <button type="button" onclick="adjustProductQty(-1)">−</button>
                            <input type="text" id="productQtyInput" value="1" readonly>
                            <button type="button" onclick="adjustProductQty(1)">+</button>
                        </div>
                    <?php endif; ?>
                    <button type="button" class="pd-btn-primary" onclick="reserveDirectlyNow()" <?= $stock > 0 ? '' : 'disabled' ?>>Reserve for pick-up</button>
                </div>
                <button type="button" class="pd-btn-ghost mb-4" onclick="addProductToPickupCart()" <?= $stock > 0 ? '' : 'disabled' ?>>
                    <i class="fa-solid fa-bag-shopping me-2"></i>Add to cart
                </button>

                <div class="pd-trust mb-4 pb-4 border-bottom">
                    <span><i class="fa-solid fa-store"></i>Victory Mall Tanauan</span>
                    <span><i class="fa-solid fa-rotate-left"></i>7-day replacement</span>
                    <span><i class="fa-solid fa-shield-halved"></i>Official warranty</span>
                </div>

                <h6 class="fw-bold mb-2" style="font-size:.95rem">Highlights</h6>
                <ul class="list-unstyled pd-hl mb-0">
                    <?php foreach (array_slice($specs['highlights'], 0, 5) as $hl): ?>
                        <li><i class="fa-solid fa-circle"></i><span><?= htmlspecialchars($hl) ?></span></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <?php if (!empty($bento_specs)): ?>
            <div class="row g-3 mt-4 pt-2">
                <?php foreach ($bento_specs as $bs): ?>
                    <div class="col-6 col-lg-3">
                        <div class="pd-spec-tile">
                            <i class="fa-solid <?= htmlspecialchars($bs['icon']) ?>"></i>
                            <div class="lbl"><?= htmlspecialchars(ucwords(strtolower($bs['label']))) ?></div>
                            <div class="val"><?= htmlspecialchars($bs['value']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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
const pdImages = <?= json_encode(array_values($gallery_imgs)) ?>;
let pdIndex = 0;
let detail3DStudio = null;
const detailProductConfig = {
    name: <?= json_encode($item_name) ?>,
    brand: <?= json_encode($item['brand'] ?? '') ?>,
    category: <?= json_encode($category_name) ?>,
    color: <?= json_encode($item['color'] ?? '') ?>,
    model: <?= json_encode($type === 'unit' ? ($item['model_name'] ?? '') : preg_replace('/^' . preg_quote($item['brand'] ?? '', '/') . '\s*/i', '', $item_name)) ?>,
    type: <?= json_encode($type) ?>,
    handset: <?= $is_handset ? 'true' : 'false' ?>,
    modelUrl: <?= json_encode($model_file_url) ?>,
    image: pdImages[0]
};

function pdHideLayers() {
    document.querySelectorAll('#pdStage .pd-layer').forEach(l => l.classList.remove('active'));
    const v = document.getElementById('mainProductAdVideo');
    if (v) { try { v.pause(); } catch (e) {} }
    const iframe = document.getElementById('mainProductAdIframe');
    if (iframe) { iframe.src = ''; }
    document.querySelectorAll('#pdRail .pd-thumb').forEach(t => t.classList.remove('active'));
}

function pdShowPhoto(i) {
    if (!pdImages.length) return;
    pdIndex = (i + pdImages.length) % pdImages.length;
    pdHideLayers();
    const img = document.getElementById('mainProductView');
    img.style.opacity = '0';
    setTimeout(() => { img.src = pdImages[pdIndex]; img.style.opacity = '1'; }, 160);
    const t = document.querySelector('#pdRail .pd-thumb[data-idx="' + pdIndex + '"]');
    if (t) { t.classList.add('active'); t.scrollIntoView({ behavior: 'smooth', inline: 'nearest', block: 'nearest' }); }
    const c = document.getElementById('pdCount');
    if (c) c.textContent = (pdIndex + 1) + ' / ' + pdImages.length;
}
function pdStep(d) { pdShowPhoto(pdIndex + d); }

function pdShow3D() {
    const layer = document.getElementById('mainProduct3DWrapper');
    if (!layer || typeof window.Product3DStudio !== 'function') return;
    pdHideLayers();
    layer.classList.add('active');
    document.getElementById('pdThumb3D')?.classList.add('active');
    if (!detail3DStudio) detail3DStudio = new window.Product3DStudio(document.getElementById('mainProduct3DCanvas'), detailProductConfig);
}

function pdShowNo3D() {
    const layer = document.getElementById('mainProductNo3DWrapper');
    if (!layer) return;
    pdHideLayers();
    layer.classList.add('active');
    document.getElementById('pdThumb3D')?.classList.add('active');
}

function pdShowVideo() {
    const layer = document.getElementById('pdVideoLayer');
    if (!layer) return;
    pdHideLayers();
    layer.classList.add('active');
    document.getElementById('pdThumbVid')?.classList.add('active');

    const v = document.getElementById('mainProductAdVideo');
    if (v) {
        if (!v.getAttribute('src') && v.dataset.adSrc) { v.src = v.dataset.adSrc; v.load(); }
        v.play().catch(() => {});
    }

    const iframe = document.getElementById('mainProductAdIframe');
    if (iframe) {
        if (!iframe.getAttribute('src') && iframe.dataset.src) {
            iframe.src = iframe.dataset.src;
        }
    }
}

// Backwards-compatible aliases
function toggleDetail3DView(open) { open === false ? pdShowPhoto(pdIndex) : pdShow3D(); }
function toggleDetailCommercialAd() { pdShowVideo(); }
function switchGalleryImage(src) { const i = pdImages.indexOf(src); pdShowPhoto(i >= 0 ? i : 0); }

// Keyboard + swipe navigation
document.addEventListener('keydown', e => {
    if (['INPUT', 'TEXTAREA'].includes(document.activeElement?.tagName)) return;
    if (e.key === 'ArrowRight') pdStep(1);
    if (e.key === 'ArrowLeft') pdStep(-1);
});
(function () {
    const stage = document.getElementById('pdStage');
    if (!stage) return;
    let x0 = null;
    stage.addEventListener('touchstart', e => { x0 = e.touches[0].clientX; }, { passive: true });
    stage.addEventListener('touchend', e => {
        if (x0 === null || document.querySelector('#pdStage .pd-layer.active')) return;
        const dx = e.changedTouches[0].clientX - x0;
        if (Math.abs(dx) > 40) pdStep(dx < 0 ? 1 : -1);
        x0 = null;
    });
})();
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
