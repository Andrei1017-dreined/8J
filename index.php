<?php
$page_title = "Home - 8J Cellphone Accessories";
require_once __DIR__ . '/includes/header.php';

// Fetch featured products and phone units
$featured_products = [];
$featured_units = [];
try {
    $stmt = $pdo->query("SELECT * FROM products WHERE status = 'available' AND (sku LIKE 'ACC-%' OR category_id != 6) ORDER BY (brand = 'Apple') DESC, id DESC LIMIT 8");
    $featured_products = $stmt->fetchAll();

    $stmt2 = $pdo->query("SELECT MIN(id) as id, brand, model_name, storage_capacity, color, unit_condition, battery_health, selling_price, status, image, imei_1, COUNT(*) as unit_count
                          FROM phone_units
                          WHERE status = 'in_stock'
                          GROUP BY brand, model_name, storage_capacity
                          ORDER BY FIELD(brand, 'Apple', 'Honor', 'Infinix', 'Realme', 'Oppo', 'Samsung', 'Itel', 'TechLife') ASC, selling_price DESC
                          LIMIT 8");
    $featured_units = $stmt2->fetchAll();
} catch (Exception $e) {
    // Handled gracefully
}
?>

<style>
.hero-mesh-pink {
    background: transparent !important;
    position: relative;
    overflow: hidden;
}
.hero-mesh-pink::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(255, 255, 255, 0.25);
    pointer-events: none;
    z-index: 1;
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
/* ---- Front page vibrancy upgrades ---- */
.hero-blob {
    position: absolute; border-radius: 50%; filter: blur(60px); pointer-events: none; z-index: 1;
    animation: blobDrift 14s ease-in-out infinite alternate;
}
.hero-blob.b1 { width: 340px; height: 340px; background: rgba(244,114,182,.45); top: -90px; left: -80px; }
.hero-blob.b2 { width: 380px; height: 380px; background: rgba(251,146,190,.40); bottom: -120px; right: -90px; animation-delay: -6s; }
.hero-blob.b3 { width: 220px; height: 220px; background: rgba(255,255,255,.7); top: 40%; left: 55%; animation-delay: -3s; }
@keyframes blobDrift { from { transform: translate(0,0) scale(1); } to { transform: translate(40px,30px) scale(1.15); } }

/* Enlarged Automatic Kiosk Slideshow */
.kiosk-showcase-card {
    background: #ffffff;
    border: 1px solid #fce7f3;
    border-radius: 26px;
    padding: 1rem;
    box-shadow: 0 24px 50px -15px rgba(244, 114, 182, 0.35);
    transition: transform 0.35s ease, box-shadow 0.35s ease;
    overflow: hidden;
}
.kiosk-showcase-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 30px 60px -15px rgba(244, 114, 182, 0.45);
}
.kiosk-pill-tabs {
    display: flex;
    gap: 6px;
    background: #fff5f8;
    padding: 6px;
    border-radius: 50px;
    border: 1px solid #fce7f3;
    margin-bottom: 0.85rem;
}
.kiosk-pill-tab {
    flex: 1;
    border: none;
    background: transparent;
    color: #831843;
    font-size: 0.8rem;
    font-weight: 700;
    padding: 8px 12px;
    border-radius: 50px;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    white-space: nowrap;
    text-align: center;
}
.kiosk-pill-tab:hover {
    color: #db2777;
    background: rgba(255, 255, 255, 0.7);
}
.kiosk-pill-tab.active {
    background: #db2777;
    color: #ffffff;
    box-shadow: 0 3px 12px rgba(219, 39, 119, 0.35);
}
.kiosk-carousel-viewport {
    position: relative;
    height: 420px;
    border-radius: 20px;
    overflow: hidden;
    background: #2b1120;
    box-shadow: 0 8px 24px -8px rgba(0, 0, 0, 0.2);
}
@media (max-width: 768px) {
    .kiosk-carousel-viewport {
        height: 300px;
    }
}
.kiosk-slide-item {
    height: 420px;
    position: relative;
    cursor: pointer;
}
@media (max-width: 768px) {
    .kiosk-slide-item {
        height: 300px;
    }
}
.kiosk-slide-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.6s ease;
}
.kiosk-slide-item:hover .kiosk-slide-img {
    transform: scale(1.03);
}
.kiosk-caption-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(43, 17, 32, 0.92) 0%, rgba(43, 17, 32, 0.5) 75%, transparent 100%);
    padding: 24px 20px 14px;
    color: #ffffff;
    pointer-events: none;
}
.kiosk-caption-title {
    font-weight: 800;
    font-size: 1.05rem;
    margin-bottom: 3px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.5);
}
.kiosk-caption-sub {
    font-size: 0.82rem;
    color: rgba(255, 255, 255, 0.85);
    margin: 0;
}
.kiosk-nav-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(8px);
    border: 1px solid #fbcfe8;
    color: #db2777;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 5;
    opacity: 0;
    transition: all 0.25s ease;
}
.kiosk-showcase-card:hover .kiosk-nav-btn {
    opacity: 1;
}
.kiosk-nav-btn:hover {
    background: #db2777;
    color: #ffffff;
    transform: translateY(-50%) scale(1.08);
}
.kiosk-nav-prev { left: 14px; }
.kiosk-nav-next { right: 14px; }

/* Brand Signboard Strip */
.brand-pill-sign {
    background: #ffffff;
    border: 1px solid #fce7f3;
    height: 38px;
    padding: 0 16px;
    border-radius: 50px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(244, 114, 182, 0.08);
    transition: all 0.25s ease;
    white-space: nowrap;
}
.brand-pill-sign:hover {
    border-color: #db2777;
    background: #fff0f5;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(219, 39, 119, 0.18);
}
.brand-logo-img {
    height: 18px;
    width: auto;
    max-width: 68px;
    object-fit: contain;
    display: inline-block;
    vertical-align: middle;
    transition: transform 0.2s ease;
}
.brand-logo-img.sq {
    height: 18px;
    width: 18px;
}
.brand-pill-sign:hover .brand-logo-img {
    transform: scale(1.12);
}

/* Real Store Gallery Bento Cards */
.gallery-bento-card {
    background: #ffffff;
    border: 1px solid #fce7f3;
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    height: 100%;
    cursor: pointer;
}
.gallery-bento-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 40px -12px rgba(244, 114, 182, 0.35);
    border-color: #f472b6;
}
.gallery-bento-img {
    width: 100%;
    height: 220px;
    object-fit: cover;
    transition: transform 0.5s ease;
}
.gallery-bento-card:hover .gallery-bento-img {
    transform: scale(1.06);
}

/* Free Check-Up Banner */
.free-checkup-card {
    background: linear-gradient(135deg, #2d1122 0%, #4a1534 60%, #db2777 100%);
    border-radius: 24px;
    color: #ffffff;
    position: relative;
    overflow: hidden;
    box-shadow: 0 20px 40px -10px rgba(74, 21, 52, 0.45);
}
.free-checkup-card::before {
    content: '';
    position: absolute;
    top: -50px; right: -50px;
    width: 250px; height: 250px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(244, 114, 182, 0.35) 0%, transparent 70%);
}

.hero-glass-metrics {
    background: rgba(255, 255, 255, 0.96);
    border: 1px solid #fbcfe8 !important; border-radius: 20px; padding: 1rem .5rem;
    box-shadow: 0 14px 30px -15px rgba(219,39,119,.22);
}
.marquee-strip {
    background: linear-gradient(90deg, #200b18 0%, #4a1534 25%, #831843 50%, #4a1534 75%, #200b18 100%);
    color: #ffffff;
    overflow: hidden;
    white-space: nowrap;
    padding: 0.95rem 0;
    border-top: 1px solid rgba(244, 114, 182, 0.35);
    border-bottom: 1px solid rgba(244, 114, 182, 0.35);
    box-shadow: 0 8px 24px -6px rgba(45, 17, 34, 0.4);
    position: relative;
    user-select: none;
    mask-image: linear-gradient(to right, transparent, black 3%, black 97%, transparent);
    -webkit-mask-image: linear-gradient(to right, transparent, black 3%, black 97%, transparent);
}
.marquee-track {
    display: flex;
    width: max-content;
    overflow: hidden;
}
.marquee-group {
    display: flex;
    align-items: center;
    justify-content: space-around;
    flex-shrink: 0;
    gap: 2.5rem;
    padding-right: 2.5rem;
    min-width: 100%;
    will-change: transform;
    animation: marquee-smooth 38s linear infinite;
    transform: translate3d(0, 0, 0);
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
    perspective: 1000px;
    -webkit-perspective: 1000px;
}
@keyframes marquee-smooth {
    0% {
        transform: translate3d(0, 0, 0);
    }
    100% {
        transform: translate3d(-100%, 0, 0);
    }
}
.marquee-item {
    display: inline-flex;
    align-items: center;
    gap: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.6px;
    font-size: 0.88rem;
    color: #ffffff;
    text-transform: uppercase;
    flex-shrink: 0;
}
.marquee-badge {
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
    color: #1f1203;
    font-size: 0.68rem;
    font-weight: 800;
    padding: 3px 10px;
    border-radius: 50px;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.4);
}
.marquee-sep {
    color: #f472b6;
    font-size: 0.9rem;
    opacity: 0.8;
    margin: 0 0.25rem;
    flex-shrink: 0;
}
.cat-tile {
    display: block; text-decoration: none; text-align: center; padding: 2rem 1.25rem 1.5rem;
    border-radius: 24px; background: #ffffff; border: 1px solid #fce7f3; color: #2b1120;
    box-shadow: 0 4px 20px rgba(244, 114, 182, 0.08);
    transition: transform .35s cubic-bezier(.16,1,.3,1), box-shadow .35s ease, border-color .3s ease;
}
.cat-tile:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px -12px rgba(244, 114, 182, 0.45);
    border-color: #f472b6;
    color: #2b1120;
}
.cat-3d-icon {
    width: 105px;
    height: 105px;
    object-fit: contain;
    border-radius: 24px;
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), filter 0.35s ease;
    filter: drop-shadow(0 12px 20px rgba(219, 39, 119, 0.28));
}
.cat-tile:hover .cat-3d-icon {
    transform: translateY(-5px) scale(1.08);
    filter: drop-shadow(0 16px 28px rgba(219, 39, 119, 0.45));
}
.cta-band {
    background: linear-gradient(135deg, #db2777 0%, #f472b6 55%, #fbcfe8 100%);
    border-radius: 28px; color: #fff; position: relative; overflow: hidden;
}
.cta-band::after {
    content: ''; position: absolute; right: -60px; top: -60px; width: 260px; height: 260px;
    border-radius: 50%; background: rgba(255,255,255,.18);
}
.reveal-up { animation: revealUp .8s cubic-bezier(.16,1,.3,1) both; }
@keyframes revealUp { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: none; } }

/* 4-Step Process Timeline Cards */
.step-flow-card {
    background: #ffffff;
    border: 1px solid rgba(244, 114, 182, 0.28);
    border-radius: 18px;
    box-shadow: 0 10px 30px rgba(244, 114, 182, 0.08), 0 2px 6px rgba(0, 0, 0, 0.02);
    transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.28s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.25s ease;
    position: relative;
}
.step-flow-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 18px 40px rgba(244, 114, 182, 0.2), 0 4px 12px rgba(0, 0, 0, 0.04);
    border-color: #f43f5e;
}
.step-circle-badge {
    width: 32px;
    height: 32px;
    background: #e11d48;
    color: #ffffff;
    font-weight: 800;
    font-size: 0.85rem;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 10px rgba(225, 29, 72, 0.25);
    flex-shrink: 0;
}
.step-pill-tag {
    background: #ffffff;
    border: 1px solid #fecdd3;
    color: #334155;
    font-size: 0.72rem;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    white-space: nowrap;
}
.step-headline {
    font-size: 1.15rem;
    font-weight: 800;
    line-height: 1.25;
    letter-spacing: -0.3px;
    color: #0f172a;
}
.step-ill-box {
    width: 76px;
    height: 68px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.step-ill-box img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.step-flow-card:hover .step-ill-box img {
    transform: scale(1.1);
}
.step-body-text {
    font-size: 0.82rem;
    line-height: 1.6;
    color: #64748b !important;
    min-height: 60px;
}
.step-footer-check {
    border-top: 1px solid #f1f5f9;
    padding-top: 0.85rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: #1e293b;
    display: flex;
    align-items: center;
}
.step-footer-check i {
    color: #0f172a;
    margin-right: 0.45rem;
}
.step-connector-arrow {
    position: absolute;
    top: 50%;
    right: -13px;
    transform: translateY(-50%);
    z-index: 3;
    pointer-events: none;
    display: flex;
    align-items: center;
    justify-content: center;
}
@media (max-width: 991.98px) {
    .step-body-text {
        min-height: auto;
    }
}

/* ============================================================================
   APPLE-STYLE SCROLL-DRIVEN FLAGSHIP ENTRANCE SHOWCASE
   ============================================================================ */
.scrolly-track {
    position: relative;
    height: 235vh;
    background-color: #040407 !important;
    background: radial-gradient(130% 90% at 50% 28%, #1f170e 0%, #0d0a07 50%, #040407 100%) !important;
    color: #ffffff;
    overflow: clip;
    z-index: 20;
    transition: background 0.7s cubic-bezier(0.16, 1, 0.3, 1);
}
.scrolly-sticky {
    position: sticky;
    top: 64px;
    height: calc(100vh - 64px);
    min-height: 600px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: clamp(14px, 2vh, 26px) 0;
}
.scrolly-aura {
    position: absolute;
    width: clamp(380px, 46vw, 680px);
    height: clamp(380px, 46vw, 680px);
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    border-radius: 50%;
    background: radial-gradient(circle at 50% 50%, rgba(212, 175, 55, 0.42) 0%, rgba(245, 158, 11, 0.20) 40%, rgba(212, 175, 55, 0.05) 60%, transparent 75%);
    filter: blur(55px);
    pointer-events: none;
    z-index: 1;
    transition: all 0.7s cubic-bezier(0.16, 1, 0.3, 1);
}
.scrolly-bg-word {
    position: absolute;
    left: 50%;
    top: 48%;
    transform: translate(-50%, -50%);
    font-size: clamp(4.5rem, 13vw, 12rem);
    font-weight: 900;
    letter-spacing: -0.04em;
    line-height: 0.85;
    white-space: nowrap;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.18) 0%, rgba(255, 255, 255, 0.02) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    pointer-events: none;
    user-select: none;
    z-index: 2;
    will-change: transform, opacity;
    transition: opacity 0.4s ease;
}
.scrolly-stage-center {
    position: relative;
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    perspective: 1200px;
    z-index: 4;
}
.scrolly-phone-rig {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    transform-style: preserve-3d;
    will-change: transform;
    transition: transform 0.08s linear;
}
.scrolly-hero-cutout {
    height: clamp(320px, 46vh, 480px);
    width: auto;
    max-width: 85vw;
    object-fit: contain;
    filter: drop-shadow(0 32px 55px rgba(0, 0, 0, 0.88)) drop-shadow(0 0 40px rgba(212, 175, 55, 0.35));
    transition: filter 0.6s ease, transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease;
    user-select: none;
    -webkit-user-drag: none;
    display: block;
}
.scrolly-floor-shadow {
    width: clamp(190px, 24vw, 310px);
    height: 26px;
    margin-top: -12px;
    border-radius: 50%;
    background: radial-gradient(ellipse at center, rgba(0, 0, 0, 0.94) 0%, rgba(0, 0, 0, 0.5) 45%, transparent 75%);
    filter: blur(9px);
    transform: scaleY(0.45);
    pointer-events: none;
    transition: transform 0.4s ease, opacity 0.4s ease;
}
.scrolly-angle-tabs {
    position: absolute;
    bottom: -18px;
    left: 50%;
    transform: translateX(-50%);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(15, 23, 42, 0.85);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border: 1px solid rgba(255, 255, 255, 0.18);
    border-radius: 999px;
    padding: 5px 12px;
    z-index: 6;
}
.scrolly-angle-tab-btn {
    border: 0;
    background: transparent;
    color: rgba(255, 255, 255, 0.65);
    font-size: 0.72rem;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 999px;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
}
.scrolly-angle-tab-btn:hover {
    color: #ffffff;
    background: rgba(255, 255, 255, 0.12);
}
.scrolly-angle-tab-btn.active {
    background: #ffffff;
    color: #0b0b14;
    font-weight: 800;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.35);
}
.scrolly-Spec-pill {
    position: absolute;
    background: rgba(10, 15, 29, 0.90);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.20);
    border-radius: 18px;
    padding: 13px 18px;
    width: 250px;
    color: #ffffff;
    box-shadow: 0 24px 50px rgba(0, 0, 0, 0.65), 0 0 0 1px rgba(255, 255, 255, 0.05);
    z-index: 6;
    will-change: transform, opacity;
    pointer-events: none;
    transition: border-color 0.4s ease;
}
.scrolly-Spec-pill .lbl {
    font-size: 0.70rem;
    font-weight: 800;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #fbcfe8;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.scrolly-Spec-pill .val {
    font-size: 0.94rem;
    font-weight: 800;
    color: #ffffff;
    line-height: 1.28;
}
.scrolly-Spec-pill.p1 { left: calc(50% - 440px); top: 18%; }
.scrolly-Spec-pill.p2 { right: calc(50% - 440px); top: 20%; }
.scrolly-Spec-pill.p3 { left: calc(50% - 440px); bottom: 18%; }
.scrolly-Spec-pill.p4 { right: calc(50% - 440px); bottom: 16%; }
@media (max-width: 1199px) {
    .scrolly-Spec-pill.p1 { left: 16px; top: 14%; width: 220px; }
    .scrolly-Spec-pill.p2 { right: 16px; top: 16%; width: 220px; }
    .scrolly-Spec-pill.p3 { left: 16px; bottom: 16%; width: 220px; }
    .scrolly-Spec-pill.p4 { right: 16px; bottom: 14%; width: 220px; }
}
@media (max-width: 991px) {
    .scrolly-track { height: auto; min-height: 680px; }
    .scrolly-sticky { position: relative; top: 0; height: auto; min-height: 640px; padding: 32px 0; }
    .scrolly-Spec-pill { display: none; }
}
.scrolly-switcher {
    display: inline-flex;
    background: rgba(15, 23, 42, 0.85);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.18);
    border-radius: 999px;
    padding: 5px;
    gap: 5px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.45);
}
.scrolly-switcher button {
    border: 0;
    background: transparent;
    color: #cbd5e1;
    font-size: 0.82rem;
    font-weight: 700;
    padding: 8px 18px;
    border-radius: 999px;
    transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    cursor: pointer;
}
.scrolly-switcher button:hover {
    color: #ffffff;
    background: rgba(255, 255, 255, 0.10);
}
.scrolly-switcher button.active {
    background: #ffffff;
    color: #0b0b14;
    font-weight: 800;
    box-shadow: 0 4px 16px rgba(255, 255, 255, 0.25);
}
.scrolly-progress-bar {
    width: 160px;
    height: 4px;
    background: rgba(255, 255, 255, 0.18);
    border-radius: 999px;
    overflow: hidden;
}
.scrolly-progress-fill {
    height: 100%;
    width: 25%;
    background: linear-gradient(90deg, #f472b6, #38bdf8);
    border-radius: 999px;
    transition: width 0.08s linear;
}
</style>

<div class="container-fluid px-0">
    <!-- Apple.com-Style Scroll-Driven Flagship Entrance Showcase -->
    <section class="scrolly-track" id="scrollyEntrance">
        <div class="scrolly-sticky">
            <div class="scrolly-aura" id="scrollyAura"></div>
            <div class="scrolly-bg-word" id="scrollyBgWord">TITANIUM</div>

            <!-- Top Header & Interactive Flagship Switcher -->
            <div class="container text-center position-relative" style="z-index: 5;">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill mb-2" style="background: rgba(255,255,255,0.10); border: 1px solid rgba(255,255,255,0.22); font-size: 0.74rem; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; color: #ffffff;">
                    <span class="pulse-indicator green"></span> 8J Flagship Studio • Scroll to Rotate &amp; Inspect
                </div>
                <h2 class="fw-extrabold mb-1" id="scrollyTitle" style="font-size: clamp(2rem, 4vw, 3.4rem); letter-spacing: -0.03em; color: #ffffff; text-shadow: 0 4px 24px rgba(0,0,0,0.8);">iPhone 15 Pro Max</h2>
                <p class="small mb-3" id="scrollySub" style="color: #cbd5e1; font-size: clamp(0.92rem, 1.1vw, 1.05rem); font-weight: 500; text-shadow: 0 2px 10px rgba(0,0,0,0.8); max-width: 680px; margin-left: auto; margin-right: auto;">Forged in aerospace titanium. A17 Pro 3nm silicon. In stock at Victory Mall Tanauan.</p>

                <div class="scrolly-switcher">
                    <button type="button" class="active" onclick="selectScrollyDevice(0, this)">iPhone 15 Pro Max</button>
                    <button type="button" onclick="selectScrollyDevice(1, this)">Honor X9c 5G</button>
                    <button type="button" onclick="selectScrollyDevice(2, this)">Infinix GT 20 Pro</button>
                    <button type="button" onclick="selectScrollyDevice(3, this)">Realme 13 5G</button>
                </div>
            </div>

            <!-- Center 3D Scroll-Animated Hardware Stage -->
            <div class="scrolly-stage-center" id="scrollyStageCenter">
                <div class="scrolly-Spec-pill p1" id="scrollyPill1">
                    <div class="lbl" id="sp1Lbl"><i class="fa-solid fa-microchip me-1"></i> CHIPSET ARCHITECTURE</div>
                    <div class="val" id="sp1Val">Apple A17 Pro 3nm • 6-Core GPU</div>
                </div>
                <div class="scrolly-Spec-pill p2" id="scrollyPill2">
                    <div class="lbl" id="sp2Lbl"><i class="fa-solid fa-camera-retro me-1"></i> PRO CAMERA SYSTEM</div>
                    <div class="val" id="sp2Val">48MP Main • 5x Optical Telephoto</div>
                </div>
                <div class="scrolly-Spec-pill p3" id="scrollyPill3">
                    <div class="lbl" id="sp3Lbl"><i class="fa-solid fa-mobile-screen me-1"></i> DISPLAY PANEL</div>
                    <div class="val" id="sp3Val">6.7" Super Retina XDR 120Hz</div>
                </div>
                <div class="scrolly-Spec-pill p4" id="scrollyPill4">
                    <div class="lbl" id="sp4Lbl"><i class="fa-solid fa-store me-1"></i> 8J TANAUAN COUNTER</div>
                    <div class="val" id="sp4Val">Same-Day Pick-Up • 0% Deposit</div>
                </div>

                <!-- Pure Cutout Phone Rig: No cardboard white box! -->
                <div class="scrolly-phone-rig" id="scrollyPhoneRig">
                    <img id="scrollyPhoneImg" src="<?= BASE_URL ?>assets/img/products/showcase_iphone_15_pro_1.png" alt="Flagship Showcase" class="scrolly-hero-cutout">
                    <div class="scrolly-floor-shadow" id="scrollyFloorShadow"></div>

                    <!-- Interactive Angle / Colorway Dots -->
                    <div class="scrolly-angle-tabs" id="scrollyAngleTabs">
                        <button type="button" class="scrolly-angle-tab-btn active" onclick="setScrollyFrame(0, this)">Natural Titanium</button>
                        <button type="button" class="scrolly-angle-tab-btn" onclick="setScrollyFrame(1, this)">Blue Titanium</button>
                        <button type="button" class="scrolly-angle-tab-btn" onclick="setScrollyFrame(2, this)">Black Titanium</button>
                        <button type="button" class="scrolly-angle-tab-btn" onclick="setScrollyFrame(3, this)">White Titanium</button>
                    </div>
                </div>
            </div>

            <!-- Bottom Price Lockup, Scroll Progress & CTA -->
            <div class="container position-relative" style="z-index: 5;">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 px-3 py-2.5 rounded-pill mx-auto" style="max-width: 820px; background: rgba(15, 23, 42, 0.92); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.20); box-shadow: 0 25px 60px rgba(0, 0, 0, 0.7);">
                    <div class="d-flex align-items-center gap-3 ps-2">
                        <div>
                            <div style="font-size: 0.68rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8;">Counter Pick-Up Price</div>
                            <div class="fw-extrabold" id="scrollyPrice" style="color: #4ade80; font-size: 1.55rem; line-height: 1.1; letter-spacing: -0.02em;">₱68,990.00</div>
                        </div>
                        <div class="d-none d-sm-flex align-items-center gap-2 ps-3 border-start border-light border-opacity-20">
                            <div class="scrolly-progress-bar"><div class="scrolly-progress-fill" id="scrollyProgFill"></div></div>
                            <span style="font-size: 0.72rem; color: #cbd5e1; font-weight: 600;" id="scrollyAngleLabel">Angle 1 • Front View</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="<?= BASE_URL ?>catalog.php?type=units" id="scrollyCtaLink" class="btn btn-primary btn-sm rounded-pill px-4 py-2 fw-extrabold text-white">
                            Inspect Specs &amp; Reserve <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

    <script>
    (function () {
        const base = <?= json_encode(BASE_URL) ?>;
        const scrollyDevices = [
            {
                title: 'iPhone 15 Pro Max',
                sub: 'Forged in aerospace titanium. A17 Pro 3nm silicon. In stock at Victory Mall Tanauan.',
                word: 'TITANIUM',
                price: '₱68,990.00',
                link: base + 'catalog.php?q=iPhone+15+Pro+Max',
                bgGradient: 'radial-gradient(130% 90% at 50% 28%, #1f170e 0%, #0d0a07 50%, #040407 100%)',
                auraColor: 'radial-gradient(circle at 50% 50%, rgba(212, 175, 55, 0.42) 0%, rgba(245, 158, 11, 0.20) 40%, rgba(212, 175, 55, 0.05) 60%, transparent 75%)',
                dropGlow: 'drop-shadow(0 32px 55px rgba(0, 0, 0, 0.88)) drop-shadow(0 0 40px rgba(212, 175, 55, 0.35))',
                accentColor: '#fbbf24',
                frames: [
                    base + 'assets/img/products/showcase_iphone_15_pro_1.png',
                    base + 'assets/img/products/showcase_iphone_15_pro_2.png',
                    base + 'assets/img/products/showcase_iphone_15_pro_3.png',
                    base + 'assets/img/products/showcase_iphone_15_pro_4.png'
                ],
                frameLabels: ['Natural Titanium', 'Blue Titanium', 'Black Titanium', 'White Titanium'],
                specs: [
                    ['CHIPSET ARCHITECTURE', 'Apple A17 Pro 3nm • 6-Core GPU', 'fa-microchip'],
                    ['PRO CAMERA SYSTEM', '48MP Main • 5x Optical Telephoto', 'fa-camera-retro'],
                    ['DISPLAY PANEL', '6.7" Super Retina XDR 120Hz', 'fa-mobile-screen'],
                    ['8J TANAUAN COUNTER', '3D CAD View • Same-Day Pick-Up', 'fa-store']
                ]
            },
            {
                title: 'HONOR X9c 5G',
                sub: 'Ultra-Bounce Anti-Drop Curved AMOLED. 108MP OIS Camera & 6600mAh Silicon-Carbon Battery.',
                word: 'UNBREAKABLE',
                price: '₱16,999.00',
                link: base + 'catalog.php?q=Honor+X9c',
                bgGradient: 'radial-gradient(130% 90% at 50% 28%, #08241b 0%, #04140f 50%, #030807 100%)',
                auraColor: 'radial-gradient(circle at 50% 50%, rgba(16, 185, 129, 0.45) 0%, rgba(5, 150, 105, 0.22) 40%, rgba(16, 185, 129, 0.05) 60%, transparent 75%)',
                dropGlow: 'drop-shadow(0 32px 55px rgba(0, 0, 0, 0.88)) drop-shadow(0 0 40px rgba(16, 185, 129, 0.35))',
                accentColor: '#34d399',
                frames: [
                    base + 'assets/img/products/showcase_honor_x9c_1.png',
                    base + 'assets/img/products/showcase_honor_x9c_2.png',
                    base + 'assets/img/products/showcase_honor_x9c_3.png'
                ],
                frameLabels: ['Front & Back View', 'Curved Display', 'Ultra-Bounce Close-up'],
                specs: [
                    ['ANTI-DROP ARMOR', '360° Ultra-Bounce Curved Shield', 'fa-shield-halved'],
                    ['OPTICAL CAMERA', '108MP OIS Ultra-Clear Main Lens', 'fa-camera-retro'],
                    ['CURVED DISPLAY', '6.78" 1.5K 120Hz Eye-Care AMOLED', 'fa-mobile-screen'],
                    ['ENDURANCE BATTERY', '6600mAh Silicon-Carbon Fast Charge', 'fa-battery-full']
                ]
            },
            {
                title: 'Infinix GT 20 Pro',
                sub: 'Cyber Mecha Gaming Design with Mecha Loop LED & 144Hz Esports AMOLED.',
                word: 'CYBER MECHA',
                price: '₱15,999.00',
                link: base + 'catalog.php?q=GT+20+Pro',
                bgGradient: 'radial-gradient(130% 90% at 50% 28%, #261107 0%, #120904 50%, #050508 100%)',
                auraColor: 'radial-gradient(circle at 50% 50%, rgba(249, 115, 22, 0.48) 0%, rgba(6, 182, 212, 0.22) 40%, rgba(249, 115, 22, 0.05) 60%, transparent 75%)',
                dropGlow: 'drop-shadow(0 32px 55px rgba(0, 0, 0, 0.88)) drop-shadow(0 0 40px rgba(249, 115, 22, 0.35))',
                accentColor: '#fb923c',
                frames: [
                    base + 'assets/img/products/showcase_infinix_gt20_1.png',
                    base + 'assets/img/products/showcase_infinix_gt20_2.png',
                    base + 'assets/img/products/showcase_infinix_gt20_3.png'
                ],
                frameLabels: ['Mecha Silver Chassis', 'RGB Loop Optics', 'Esports Gaming Profile'],
                specs: [
                    ['DUAL GAMING CHIP', 'Dimensity 8200 Ultimate + Pixelworks', 'fa-microchip'],
                    ['ESPORTS DISPLAY', '6.78" 144Hz Bezel-Less AMOLED', 'fa-mobile-screen'],
                    ['OPTICAL SYSTEM', '108MP OIS Triple Camera + RGB Loop', 'fa-camera-retro'],
                    ['THERMAL ARCHITECTURE', 'Bypass Charging + VC Liquid Cooling', 'fa-snowflake']
                ]
            },
            {
                title: 'realme 13 5G',
                sub: 'Dimensity 6300 5G Speed Chipset, 120Hz Eye Comfort Display & 50MP OIS Camera.',
                word: 'SPEED WAVE',
                price: '₱13,999.00',
                link: base + 'catalog.php?q=Realme+13+5G',
                bgGradient: 'radial-gradient(130% 90% at 50% 28%, #081d33 0%, #040f1a 50%, #030810 100%)',
                auraColor: 'radial-gradient(circle at 50% 50%, rgba(56, 189, 248, 0.45) 0%, rgba(59, 130, 246, 0.20) 40%, rgba(56, 189, 248, 0.05) 60%, transparent 75%)',
                dropGlow: 'drop-shadow(0 32px 55px rgba(0, 0, 0, 0.88)) drop-shadow(0 0 40px rgba(56, 189, 248, 0.35))',
                accentColor: '#38bdf8',
                frames: [
                    base + 'assets/img/products/showcase_realme_13_1.png',
                    base + 'assets/img/products/showcase_realme_13_2.png',
                    base + 'assets/img/products/showcase_realme_13_3.png'
                ],
                frameLabels: ['Speed Green Full View', 'Speed Wave Island', '120Hz Bezel Profile'],
                specs: [
                    ['5G PROCESSOR', 'MediaTek Dimensity 6300 5G Octa-Core', 'fa-microchip'],
                    ['STABILIZED CAMERA', '50MP OIS Speed-Wave Camera Island', 'fa-camera-retro'],
                    ['SMOOTH DISPLAY', '120Hz Eye-Comfort FHD+ Display', 'fa-mobile-screen'],
                    ['FAST CHARGE', '45W SUPERVOOC + 5000mAh Battery', 'fa-bolt']
                ]
            }
        ];

        let activeDev = 0;
        let lastFrameIdx = 0;

        window.selectScrollyDevice = function (idx, btn) {
            activeDev = idx;
            const d = scrollyDevices[idx];
            const track = document.getElementById('scrollyEntrance');
            const aura = document.getElementById('scrollyAura');
            const img = document.getElementById('scrollyPhoneImg');

            if (track) track.style.background = d.bgGradient;
            if (aura) aura.style.background = d.auraColor;
            if (img) img.style.filter = d.dropGlow;

            document.getElementById('scrollyTitle').textContent = d.title;
            document.getElementById('scrollySub').textContent = d.sub;
            document.getElementById('scrollyBgWord').textContent = d.word;
            document.getElementById('scrollyPrice').textContent = d.price;
            document.getElementById('scrollyCtaLink').href = d.link;
            img.src = d.frames[0];
            lastFrameIdx = 0;

            for (let i = 0; i < 4; i++) {
                const pill = document.getElementById('scrollyPill' + (i + 1));
                const lbl = document.getElementById('sp' + (i + 1) + 'Lbl');
                const val = document.getElementById('sp' + (i + 1) + 'Val');
                if (lbl && d.specs[i]) {
                    lbl.innerHTML = '<i class="fa-solid ' + (d.specs[i][2] || 'fa-check') + ' me-1"></i> ' + d.specs[i][0];
                    lbl.style.color = d.accentColor;
                }
                if (val && d.specs[i]) val.textContent = d.specs[i][1];
                if (pill) pill.style.borderColor = 'rgba(255, 255, 255, 0.20)';
            }

            // Rebuild Angle / Colorway Tabs for active phone
            const tabsContainer = document.getElementById('scrollyAngleTabs');
            if (tabsContainer) {
                tabsContainer.innerHTML = '';
                d.frameLabels.forEach((label, fIdx) => {
                    const tBtn = document.createElement('button');
                    tBtn.type = 'button';
                    tBtn.className = 'scrolly-angle-tab-btn' + (fIdx === 0 ? ' active' : '');
                    tBtn.textContent = label;
                    tBtn.onclick = function () { setScrollyFrame(fIdx, this); };
                    tabsContainer.appendChild(tBtn);
                });
            }

            if (btn) {
                btn.parentElement.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            }
            updateScrollyAnimation();
        };

        window.setScrollyFrame = function (fIdx, btn) {
            const d = scrollyDevices[activeDev];
            if (!d.frames[fIdx]) return;
            lastFrameIdx = fIdx;
            const img = document.getElementById('scrollyPhoneImg');
            if (img) img.src = d.frames[fIdx];

            const tabs = document.getElementById('scrollyAngleTabs');
            if (tabs) {
                tabs.querySelectorAll('.scrolly-angle-tab-btn').forEach(b => b.classList.remove('active'));
                if (btn) btn.classList.add('active');
            }

            const lbl = document.getElementById('scrollyAngleLabel');
            if (lbl && d.frameLabels[fIdx]) {
                lbl.textContent = d.frameLabels[fIdx];
            }
        };

        function updateScrollyAnimation() {
            const track = document.getElementById('scrollyEntrance');
            const rig = document.getElementById('scrollyPhoneRig');
            const img = document.getElementById('scrollyPhoneImg');
            const bgWord = document.getElementById('scrollyBgWord');
            const prog = document.getElementById('scrollyProgFill');
            const lbl = document.getElementById('scrollyAngleLabel');
            if (!track || !rig || !img) return;

            const rect = track.getBoundingClientRect();
            const scrollable = Math.max(1, track.offsetHeight - window.innerHeight);
            const p = Math.max(0, Math.min(1, -rect.top / scrollable));

            // Smooth 3D perspective rotation & responsive scale
            const rotY = (p - 0.5) * 28; // -14deg to +14deg
            const rotX = Math.sin(p * Math.PI) * 7;
            const scale = 0.94 + Math.sin(p * Math.PI) * 0.12;
            rig.style.transform = `rotateY(${rotY.toFixed(2)}deg) rotateX(${rotX.toFixed(2)}deg) scale(${scale.toFixed(3)})`;

            if (bgWord) {
                const wordY = (0.5 - p) * 90;
                bgWord.style.transform = `translate(-50%, calc(-50% + ${wordY.toFixed(1)}px)) scale(${(0.95 + p * 0.12).toFixed(3)})`;
            }

            // Switch frame as user scrolls through fractions
            const d = scrollyDevices[activeDev];
            const frameIdx = Math.min(d.frames.length - 1, Math.floor(p * d.frames.length));
            if (frameIdx !== lastFrameIdx && d.frames[frameIdx]) {
                lastFrameIdx = frameIdx;
                img.src = d.frames[frameIdx];

                const tabs = document.getElementById('scrollyAngleTabs');
                if (tabs) {
                    const allBtns = tabs.querySelectorAll('.scrolly-angle-tab-btn');
                    allBtns.forEach((b, i) => b.classList.toggle('active', i === frameIdx));
                }
            }

            // Animate floating spec cards
            [1, 2, 3, 4].forEach((n, i) => {
                const pill = document.getElementById('scrollyPill' + n);
                if (!pill) return;
                const threshold = i * 0.12;
                const localP = Math.max(0, Math.min(1, (p - threshold) / 0.35));
                const dir = (n % 2 === 1) ? -1 : 1;
                const tx = (1 - localP) * 44 * dir;
                pill.style.opacity = (0.35 + localP * 0.65).toFixed(2);
                pill.style.transform = `translateX(${tx.toFixed(1)}px) translateY(${((0.5 - p) * 22).toFixed(1)}px)`;
            });

            if (prog) prog.style.width = Math.round(15 + p * 85) + '%';
            if (lbl && d.frameLabels[frameIdx]) {
                lbl.textContent = d.frameLabels[frameIdx];
            }
        }

        window.addEventListener('scroll', () => requestAnimationFrame(updateScrollyAnimation), { passive: true });
        document.addEventListener('DOMContentLoaded', updateScrollyAnimation);
    })();
    </script>

    <!-- Hero Section with Light Pink Tech Circuit Background -->
    <div class="hero-mesh-pink py-5 position-relative border-bottom" style="color: #2b1120 !important; border-color: #fbcfe8 !important;">
        <div class="container py-4 py-lg-5 position-relative reveal-up" style="z-index: 2;">
            <div class="row align-items-center">
                
                <!-- Left: Headline & Action Column -->
                <div class="col-lg-6 text-center text-lg-start pe-lg-4">
                    <!-- Store Pill -->
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill mb-3 border" 
                         style="background: #ffffff; border-color: #fbcfe8 !important; box-shadow: 0 4px 14px rgba(244, 114, 182, 0.15);">
                        <span class="pulse-indicator green"></span>
                        <i class="fa-solid fa-location-dot" style="font-size: 0.85rem; color: #db2777;"></i>
                        <span class="small fw-bold" style="font-size: 0.8rem; letter-spacing: 0.3px; color: #831843;">
                            Victory Mall Tanauan 2nd Floor • CA Exhibit Stall #S1
                        </span>
                    </div>

                    <!-- Main Display Headline -->
                    <h1 class="display-4 fw-extrabold mb-3" style="letter-spacing: -1.5px; line-height: 1.15; color: #2b1120;">
                        Genuine Gadgets.<br>
                        <span style="background: linear-gradient(135deg, #db2777 0%, #e11d48 50%, #f472b6 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                            Master Repairs with Free Check-Up.
                        </span>
                    </h1>

                    <p class="lead mb-4" style="max-width: 580px; font-size: 1.05rem; line-height: 1.65; color: #6b2d4f;">
                        Your official Tanauan City hub for in-stock <strong>Infinix</strong>, <strong>Tecno</strong> &amp; <strong>Apple</strong> handsets, certified fast-charging accessories, and expert technician diagnostics with <strong>FREE CHECK-UP</strong> on all devices.
                    </p>

                    <!-- Interactive Action Buttons -->
                    <div class="d-flex flex-wrap justify-content-center justify-content-lg-start gap-2 mb-4">
                        <a href="<?= BASE_URL ?>catalog.php" class="btn btn-primary btn-lg rounded-pill px-4 fw-bold shadow-lg d-inline-flex align-items-center gap-2 btn-tactile">
                            <i class="fa-solid fa-bag-shopping"></i> Browse Catalog
                        </a>
                        <a href="<?= BASE_URL ?>book_repair.php" class="btn btn-outline-dark btn-lg rounded-pill px-4 fw-semibold d-inline-flex align-items-center gap-2" style="background: #ffffff !important; border-color: #f472b6 !important; color: #db2777 !important;">
                            <i class="fa-solid fa-screwdriver-wrench"></i> Book Free Check-Up
                        </a>
                        <a href="<?= BASE_URL ?>track_repair.php" class="btn btn-outline-secondary btn-lg rounded-pill px-3 d-inline-flex align-items-center gap-2" style="background: rgba(255, 255, 255, 0.85) !important; border-color: #fbcfe8 !important; color: #831843 !important;">
                            <i class="fa-solid fa-barcode"></i> Track Order
                        </a>
                    </div>

                    <!-- Direct Hotline & Social Pill -->
                    <div class="d-flex flex-wrap justify-content-center justify-content-lg-start align-items-center gap-2 mb-4">
                        <a href="tel:09099833981" class="text-decoration-none d-flex align-items-center gap-2 small fw-bold text-dark bg-white px-3 py-1.5 rounded-pill border shadow-sm" style="border-color: #fbcfe8; font-size: 0.82rem;">
                            <i class="fa-solid fa-phone" style="color: #db2777;"></i> 0909-9833-981
                        </a>
                        <a href="https://www.facebook.com/profile.php?id=100063760157450" target="_blank" rel="noopener noreferrer" class="text-decoration-none d-flex align-items-center gap-2 small fw-bold text-dark bg-white px-3 py-1.5 rounded-pill border shadow-sm" style="border-color: #fbcfe8; font-size: 0.82rem;">
                            <i class="fa-brands fa-facebook" style="color: #1877f2;"></i> @Gab_8J Facebook
                        </a>
                        <span class="badge bg-danger rounded-pill px-3 py-1.5 fw-bold" style="font-size: 0.78rem;">
                            <i class="fa-solid fa-bolt me-1"></i> FREE CHECK-UP
                        </span>
                    </div>

                    <!-- Live Metrics Strip -->
                    <div class="row g-3 justify-content-center justify-content-lg-start hero-glass-metrics" style="max-width: 600px;">
                        <div class="col-3 text-center">
                            <div class="fw-extrabold fs-4" style="color: #9d174d;">FREE</div>
                            <small class="fw-semibold text-truncate d-block" style="font-size: 0.72rem; color: #831843;">Diagnostic Check</small>
                        </div>
                        <div class="col-3 text-center">
                            <div class="fw-extrabold fs-4" style="color: #9d174d;">1-Hour</div>
                            <small class="fw-semibold text-truncate d-block" style="font-size: 0.72rem; color: #831843;">Counter Pick-Up</small>
                        </div>
                        <div class="col-3 text-center">
                            <div class="fw-extrabold fs-4" style="color: #9d174d;">100%</div>
                            <small class="fw-semibold text-truncate d-block" style="font-size: 0.72rem; color: #831843;">Tested Genuine</small>
                        </div>
                        <div class="col-3 text-center">
                            <div class="fw-extrabold fs-4" style="color: #9d174d;">7-Day</div>
                            <small class="fw-semibold text-truncate d-block" style="font-size: 0.72rem; color: #831843;">Direct Swap</small>
                        </div>
                    </div>
                </div>

                <!-- Right: Enlarged Automatic Slideshow -->
                <div class="col-lg-6 mt-4 mt-lg-0">
                    <div class="kiosk-showcase-card">
                        <!-- Navigation Tabs that Sync with Slides -->
                        <div class="kiosk-pill-tabs">
                            <button type="button" class="kiosk-pill-tab active" data-slide-index="0" onclick="goToKioskSlide(0)">
                                Kiosk
                            </button>
                            <button type="button" class="kiosk-pill-tab" data-slide-index="1" onclick="goToKioskSlide(1)">
                                Infinix
                            </button>
                            <button type="button" class="kiosk-pill-tab" data-slide-index="2" onclick="goToKioskSlide(2)">
                                Apple / Audio
                            </button>
                            <button type="button" class="kiosk-pill-tab" data-slide-index="3" onclick="goToKioskSlide(3)">
                                Tecno
                            </button>
                        </div>

                        <!-- Bootstrap 5 Automatic Carousel Viewport -->
                        <div id="kioskHeroCarousel" class="carousel slide carousel-fade kiosk-carousel-viewport" data-bs-ride="carousel" data-bs-interval="3500" data-bs-pause="hover">
                            <div class="carousel-inner h-100">
                                <!-- Slide 1: Kiosk -->
                                <div class="carousel-item active h-100">
                                    <div class="kiosk-slide-item h-100" role="button" onclick="openStorePhotoModal('<?= BASE_URL ?>assets/img/store/store_kiosk.jpg', 'Victory Mall Tanauan Kiosk • CA Exhibit Stall #S1')">
                                        <img src="<?= BASE_URL ?>assets/img/store/store_kiosk.jpg" alt="Victory Mall Kiosk" class="kiosk-slide-img">
                                        <div class="kiosk-caption-overlay">
                                            <div class="kiosk-caption-title">Victory Mall Tanauan Kiosk</div>
                                            <p class="kiosk-caption-sub">CA Exhibit Stall #S1, 2nd Floor (Beside Town Center)</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Slide 2: Infinix -->
                                <div class="carousel-item h-100">
                                    <div class="kiosk-slide-item h-100" role="button" onclick="openStorePhotoModal('<?= BASE_URL ?>assets/img/store/store_infinix_display.jpg', 'Infinix Counter &amp; Mascot Lightbox')">
                                        <img src="<?= BASE_URL ?>assets/img/store/store_infinix_display.jpg" alt="Infinix Display" class="kiosk-slide-img">
                                        <div class="kiosk-caption-overlay">
                                            <div class="kiosk-caption-title">Infinix Counter &amp; 8J Mascot</div>
                                            <p class="kiosk-caption-sub">Official Infinix partner showcase with Smart 20 and Hot series</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Slide 3: Apple & Audio -->
                                <div class="carousel-item h-100">
                                    <div class="kiosk-slide-item h-100" role="button" onclick="openStorePhotoModal('<?= BASE_URL ?>assets/img/store/store_apple_audio.jpg', 'Apple Handsets, iPads &amp; Premium Audio')">
                                        <img src="<?= BASE_URL ?>assets/img/store/store_apple_audio.jpg" alt="Apple &amp; Audio Desk" class="kiosk-slide-img">
                                        <div class="kiosk-caption-overlay">
                                            <div class="kiosk-caption-title">Apple Devices &amp; Premium Audio</div>
                                            <p class="kiosk-caption-sub">Certified pre-owned iPhones, iPads, and wireless headphones</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Slide 4: Tecno -->
                                <div class="carousel-item h-100">
                                    <div class="kiosk-slide-item h-100" role="button" onclick="openStorePhotoModal('<?= BASE_URL ?>assets/img/store/store_tecno_display.jpg', 'TECNO Smartphone Showcase')">
                                        <img src="<?= BASE_URL ?>assets/img/store/store_tecno_display.jpg" alt="Tecno Display" class="kiosk-slide-img">
                                        <div class="kiosk-caption-overlay">
                                            <div class="kiosk-caption-title">TECNO Smartphone Showcase</div>
                                            <p class="kiosk-caption-sub">Spark Go, Spark 50, and Camon 50 with local official warranty</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Carousel Nav Arrows -->
                            <button class="kiosk-nav-btn kiosk-nav-prev" type="button" data-bs-target="#kioskHeroCarousel" data-bs-slide="prev" aria-label="Previous">
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>
                            <button class="kiosk-nav-btn kiosk-nav-next" type="button" data-bs-target="#kioskHeroCarousel" data-bs-slide="next" aria-label="Next">
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Continuous Scrolling Highlights Ribbon -->
    <div class="marquee-strip">
        <div class="marquee-track">
            <?php for ($m = 0; $m < 2; $m++): ?>
                <div class="marquee-group" <?= $m > 0 ? 'aria-hidden="true"' : '' ?>>
                    <div class="marquee-item">
                        <i class="fa-solid fa-store" style="color: #f472b6;"></i>
                        <span>8J CELLPHONE &amp; LAPTOP REPAIR • INTEGRATED STORE HUB</span>
                    </div>
                    <span class="marquee-sep">✦</span>

                    <div class="marquee-item">
                        <i class="fa-solid fa-star text-warning"></i>
                        <span>TANAUAN'S PREMIER CELLPHONE &amp; LAPTOP REPAIR HUB</span>
                    </div>
                    <span class="marquee-sep">✦</span>

                    <div class="marquee-item">
                        <span class="badge bg-danger rounded-pill px-2.5 py-1 text-white fw-bold" style="font-size:0.7rem;"><i class="fa-solid fa-bolt me-1"></i>FREE CHECK-UP</span>
                        <span>100% COMPLIMENTARY HARDWARE DIAGNOSTICS</span>
                    </div>
                    <span class="marquee-sep">✦</span>

                    <div class="marquee-item">
                        <i class="fa-solid fa-microchip" style="color: #f472b6;"></i>
                        <span>25-POINT CERTIFIED TECHNICIAN QUALITY VERIFICATION</span>
                    </div>
                    <span class="marquee-sep">✦</span>

                    <div class="marquee-item">
                        <span class="badge bg-white text-dark rounded-pill px-2.5 py-1 fw-bold" style="font-size:0.7rem;"><i class="fa-solid fa-shield-halved text-success me-1"></i>LEGAL COMPLIANCE</span>
                        <span>P.D. 1612 ANTI-FENCING VERIFIED APPARATUS</span>
                    </div>
                    <span class="marquee-sep">✦</span>

                    <div class="marquee-item">
                        <i class="fa-solid fa-arrows-rotate text-warning"></i>
                        <span>7-DAY DIRECT REPLACEMENT 1-TO-1 SWAP WARRANTY</span>
                    </div>
                    <span class="marquee-sep">✦</span>

                    <div class="marquee-item">
                        <i class="fa-solid fa-location-dot text-danger"></i>
                        <span>VICTORY MALL TANAUAN • 2ND FLOOR EXHIBIT STALL #S1</span>
                    </div>
                    <span class="marquee-sep">✦</span>

                    <div class="marquee-item">
                        <span class="marquee-badge"><i class="fa-solid fa-crown me-1"></i>TOP-RATED</span>
                        <span>4.9★ RATED STOREFRONT BY 2,500+ RESIDENTS</span>
                    </div>
                    <span class="marquee-sep">✦</span>

                    <div class="marquee-item">
                        <i class="fa-solid fa-clock text-info"></i>
                        <span>60-MINUTE IN-STORE PICKUP • ZERO RESERVATION DEPOSIT</span>
                    </div>
                    <span class="marquee-sep">✦</span>
                </div>
            <?php endfor; ?>
        </div>
    </div>

    <!-- Authorized Brand Coverage Strip (From Official Signboard) -->
    <div class="py-3 border-bottom" style="background: #ffffff;">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <span class="small fw-bold text-muted text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">
                    <i class="fa-solid fa-screwdriver-wrench text-primary me-1"></i> BRANDS WE SERVICE &amp; SUPPORT:
                </span>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <a href="<?= BASE_URL ?>catalog.php?q=Apple" class="brand-pill-sign text-decoration-none" title="Apple &amp; iPhone"><img src="assets/img/brands/apple.svg" class="brand-logo-img sq" alt="Apple"></a>
                    <a href="<?= BASE_URL ?>catalog.php?q=Infinix" class="brand-pill-sign text-decoration-none" title="Infinix"><img src="assets/img/brands/infinix.svg" class="brand-logo-img" alt="Infinix"></a>
                    <a href="<?= BASE_URL ?>catalog.php?q=Tecno" class="brand-pill-sign text-decoration-none" title="Tecno"><img src="assets/img/brands/tecno.svg" class="brand-logo-img" alt="Tecno"></a>
                    <a href="<?= BASE_URL ?>catalog.php?q=Samsung" class="brand-pill-sign text-decoration-none" title="Samsung"><img src="assets/img/brands/samsung.svg" class="brand-logo-img" alt="Samsung"></a>
                    <a href="<?= BASE_URL ?>catalog.php?q=realme" class="brand-pill-sign text-decoration-none" title="realme"><img src="assets/img/brands/realme.svg" class="brand-logo-img" alt="realme"></a>
                    <a href="<?= BASE_URL ?>catalog.php?q=OPPO" class="brand-pill-sign text-decoration-none" title="OPPO"><img src="assets/img/brands/oppo.svg" class="brand-logo-img" alt="OPPO"></a>
                    <a href="<?= BASE_URL ?>catalog.php?q=Xiaomi" class="brand-pill-sign text-decoration-none" title="Xiaomi"><img src="assets/img/brands/xiaomi.svg" class="brand-logo-img sq" alt="Xiaomi"></a>
                    <a href="<?= BASE_URL ?>catalog.php?q=vivo" class="brand-pill-sign text-decoration-none" title="vivo"><img src="assets/img/brands/vivo.svg" class="brand-logo-img" alt="vivo"></a>
                    <a href="<?= BASE_URL ?>catalog.php?q=Huawei" class="brand-pill-sign text-decoration-none" title="Huawei"><img src="assets/img/brands/huawei.svg" class="brand-logo-img sq" alt="Huawei"></a>
                    <a href="<?= BASE_URL ?>catalog.php?q=Lenovo" class="brand-pill-sign text-decoration-none" title="Lenovo"><img src="assets/img/brands/lenovo.svg" class="brand-logo-img" alt="Lenovo"></a>
                    <a href="<?= BASE_URL ?>catalog.php?q=ASUS" class="brand-pill-sign text-decoration-none" title="ASUS"><img src="assets/img/brands/asus.svg" class="brand-logo-img" alt="ASUS"></a>
                    <a href="<?= BASE_URL ?>catalog.php?q=Acer" class="brand-pill-sign text-decoration-none" title="Acer"><img src="assets/img/brands/acer.svg" class="brand-logo-img" alt="Acer"></a>
                    <a href="<?= BASE_URL ?>catalog.php?q=POCO" class="brand-pill-sign text-decoration-none" title="POCO"><img src="assets/img/brands/poco.svg" class="brand-logo-img" alt="POCO"></a>
                    <a href="<?= BASE_URL ?>catalog.php?q=Nokia" class="brand-pill-sign text-decoration-none" title="Nokia"><img src="assets/img/brands/nokia.svg" class="brand-logo-img" alt="Nokia"></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Specialized Shop Services Banner (From 8J Signboard) -->
    <div class="container mt-5">
        <div class="free-checkup-card p-4 p-md-5">
            <div class="row align-items-center g-4 position-relative" style="z-index: 2;">
                <div class="col-lg-7">
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3);">
                        <i class="fa-solid fa-certificate text-warning"></i>
                        <span class="small fw-bold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.75rem;">Official Stall Signboard Services</span>
                    </div>
                    <h2 class="display-6 fw-extrabold text-white mb-2" style="letter-spacing: -0.5px;">
                        FREE DIAGNOSTIC CHECK-UP
                    </h2>
                    <p class="text-white-50 mb-4" style="max-width: 540px; font-size: 0.95rem; line-height: 1.6;">
                        Drop by our Victory Mall Tanauan 2nd floor kiosk for a 100% free physical inspection of your smartphone or laptop before deciding on any repair service.
                    </p>
                    <div class="row g-2 mb-4">
                        <div class="col-6 col-sm-4">
                            <div class="p-2 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-10 small fw-semibold text-white">
                                <i class="fa-solid fa-unlock-keyhole text-warning me-1"></i> Openline &amp; Bypass
                            </div>
                        </div>
                        <div class="col-6 col-sm-4">
                            <div class="p-2 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-10 small fw-semibold text-white">
                                <i class="fa-solid fa-mobile text-info me-1"></i> LCD Replacement
                            </div>
                        </div>
                        <div class="col-6 col-sm-4">
                            <div class="p-2 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-10 small fw-semibold text-white">
                                <i class="fa-solid fa-battery-three-quarters text-success me-1"></i> Battery Replacement
                            </div>
                        </div>
                        <div class="col-6 col-sm-4">
                            <div class="p-2 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-10 small fw-semibold text-white">
                                <i class="fa-solid fa-code text-primary me-1"></i> Program &amp; Flashing
                            </div>
                        </div>
                        <div class="col-6 col-sm-4">
                            <div class="p-2 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-10 small fw-semibold text-white">
                                <i class="fa-solid fa-laptop text-light me-1"></i> Laptop Hardware
                            </div>
                        </div>
                        <div class="col-6 col-sm-4">
                            <div class="p-2 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-10 small fw-semibold text-white">
                                <i class="fa-solid fa-screwdriver-wrench text-warning me-1"></i> Micro-Soldering
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?= BASE_URL ?>book_repair.php" class="btn btn-light rounded-pill px-4 fw-bold">
                            <i class="fa-solid fa-calendar-check text-danger me-1"></i> Book Your Free Check-Up
                        </a>
                        <a href="tel:09099833981" class="btn btn-outline-light rounded-pill px-4 fw-semibold">
                            <i class="fa-solid fa-phone me-1"></i> Call 0909-9833-981
                        </a>
                    </div>
                </div>
                <div class="col-lg-5 text-center">
                    <img src="<?= BASE_URL ?>assets/img/store/store_banner.png" alt="8J Signboard" loading="lazy" decoding="async" class="img-fluid rounded-4 shadow-lg border border-white border-opacity-25" style="max-height: 240px; object-fit: contain; cursor: pointer;" onclick="openStorePhotoModal('<?= BASE_URL ?>assets/img/store/store_banner.png', '8J Cellphone &amp; Laptop Repair Official Signboard')">
                    <small class="d-block text-white-50 mt-2" style="font-size: 0.72rem;">Click to view full official signboard</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Real Physical Store Photo Showcase -->
    <div class="container my-5 py-2">
        <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
            <div>
                <span class="badge bg-light text-dark border px-3 py-1 rounded-pill fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                    <i class="fa-solid fa-camera text-primary me-1"></i> Real Store Walkthrough
                </span>
                <h2 class="display-6 fw-extrabold text-dark mt-2 mb-1" style="letter-spacing: -0.5px;">
                    Inside Our Victory Mall Tanauan Branch
                </h2>
                <p class="text-muted small mb-0">Browse our real-world booth at the 2nd Floor CA Exhibit Stall #S1 (Beside Town Center)</p>
            </div>
            <a href="https://www.facebook.com/profile.php?id=100063760157450" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                <i class="fa-brands fa-facebook me-1"></i> Follow @Gab_8J on Facebook
            </a>
        </div>

        <div class="row g-4">
            <!-- Photo 1: Flagship Kiosk -->
            <div class="col-md-6 col-lg-3">
                <div class="gallery-bento-card" onclick="openStorePhotoModal('<?= BASE_URL ?>assets/img/store/store_kiosk.jpg', '8J Victory Mall Kiosk - 2nd Floor Exhibit Stall #S1')">
                    <div class="position-relative overflow-hidden">
                        <img src="<?= BASE_URL ?>assets/img/store/store_kiosk.jpg" alt="8J Mall Kiosk" loading="lazy" decoding="async" class="gallery-bento-img">
                        <span class="badge bg-dark bg-opacity-75 text-white position-absolute top-0 start-0 m-3 px-2.5 py-1 rounded-pill small">
                            <i class="fa-solid fa-store me-1 text-warning"></i> Flagship Booth
                        </span>
                    </div>
                    <div class="p-3">
                        <h6 class="fw-bold text-dark mb-1">Victory Mall Exhibit Kiosk</h6>
                        <p class="text-muted small mb-0" style="font-size: 0.8rem; line-height: 1.5;">
                            2nd Floor CA Exhibit Stall #S1. Walk in for same-day counter pickups and hardware testing.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Photo 2: Infinix Showcase & Mascot -->
            <div class="col-md-6 col-lg-3">
                <div class="gallery-bento-card" onclick="openStorePhotoModal('<?= BASE_URL ?>assets/img/store/store_infinix_display.jpg', 'Infinix Showcase &amp; 8J Phone Mascot Lightbox')">
                    <div class="position-relative overflow-hidden">
                        <img src="<?= BASE_URL ?>assets/img/store/store_infinix_display.jpg" alt="Infinix Display" loading="lazy" decoding="async" class="gallery-bento-img">
                        <span class="badge bg-dark bg-opacity-75 text-white position-absolute top-0 start-0 m-3 px-2.5 py-1 rounded-pill small">
                            <i class="fa-solid fa-mobile-screen me-1 text-info"></i> Infinix Authorized
                        </span>
                    </div>
                    <div class="p-3">
                        <h6 class="fw-bold text-dark mb-1">Infinix Counter &amp; 8J Mascot</h6>
                        <p class="text-muted small mb-0" style="font-size: 0.8rem; line-height: 1.5;">
                            Official Infinix showcase featuring Smart 20, Hot 50 Pro, and the signature 8J mascot lightbox.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Photo 3: Apple Devices & Audio -->
            <div class="col-md-6 col-lg-3">
                <div class="gallery-bento-card" onclick="openStorePhotoModal('<?= BASE_URL ?>assets/img/store/store_apple_audio.jpg', 'Apple Devices, iPads &amp; Premium Headphones')">
                    <div class="position-relative overflow-hidden">
                        <img src="<?= BASE_URL ?>assets/img/store/store_apple_audio.jpg" alt="Apple & Audio" loading="lazy" decoding="async" class="gallery-bento-img">
                        <span class="badge bg-dark bg-opacity-75 text-white position-absolute top-0 start-0 m-3 px-2.5 py-1 rounded-pill small">
                            <i class="fa-brands fa-apple me-1 text-light"></i> Apple &amp; Audio
                        </span>
                    </div>
                    <div class="p-3">
                        <h6 class="fw-bold text-dark mb-1">iPhones, iPads &amp; Headphones</h6>
                        <p class="text-muted small mb-0" style="font-size: 0.8rem; line-height: 1.5;">
                            Pre-owned certified Apple units, SonicAir earbuds, and P9 Max wireless over-ear audio gear.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Photo 4: TECNO Handset Display -->
            <div class="col-md-6 col-lg-3">
                <div class="gallery-bento-card" onclick="openStorePhotoModal('<?= BASE_URL ?>assets/img/store/store_tecno_display.jpg', 'TECNO Smartphone Showcase - Spark &amp; Camon Series')">
                    <div class="position-relative overflow-hidden">
                        <img src="<?= BASE_URL ?>assets/img/store/store_tecno_display.jpg" alt="TECNO Display" loading="lazy" decoding="async" class="gallery-bento-img">
                        <span class="badge bg-dark bg-opacity-75 text-white position-absolute top-0 start-0 m-3 px-2.5 py-1 rounded-pill small">
                            <i class="fa-solid fa-bolt me-1 text-primary"></i> TECNO Official
                        </span>
                    </div>
                    <div class="p-3">
                        <h6 class="fw-bold text-dark mb-1">TECNO Spark &amp; Camon Display</h6>
                        <p class="text-muted small mb-0" style="font-size: 0.8rem; line-height: 1.5;">
                            High-endurance battery phones including SPARK Go, SPARK 50, and CAMON series boxed with warranty.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Shop by Category with 3D Glossy Icons -->
    <div class="container mt-5 pt-3">
        <div class="text-center mb-4">
            <span class="badge bg-light text-dark border px-3 py-1 rounded-pill fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                Instant Access
            </span>
            <h2 class="display-6 fw-extrabold mb-1 mt-2" style="letter-spacing:-1px; color:#2b1120;">Shop by Category</h2>
            <p class="text-muted mb-0">Jump straight to what you need</p>
        </div>
        <div class="row g-3 text-center">
            <div class="col-6 col-md-3">
                <a class="cat-tile h-100 d-flex flex-column align-items-center justify-content-between" href="<?= BASE_URL ?>catalog.php?type=units">
                    <div class="cat-img-wrapper mb-3">
                        <img src="<?= BASE_URL ?>assets/img/icons/icon_smartphones.png" alt="Smartphones" loading="lazy" decoding="async" class="cat-3d-icon">
                    </div>
                    <div>
                        <strong class="d-block fs-6 fw-extrabold text-dark mb-1">Smartphones</strong>
                        <span class="small text-muted">New &amp; pre-owned</span>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a class="cat-tile h-100 d-flex flex-column align-items-center justify-content-between" href="<?= BASE_URL ?>catalog.php">
                    <div class="cat-img-wrapper mb-3">
                        <img src="<?= BASE_URL ?>assets/img/icons/icon_powerbanks.png" alt="Power Banks" loading="lazy" decoding="async" class="cat-3d-icon">
                    </div>
                    <div>
                        <strong class="d-block fs-6 fw-extrabold text-dark mb-1">Power Banks</strong>
                        <span class="small text-muted">Up to 20000mAh</span>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a class="cat-tile h-100 d-flex flex-column align-items-center justify-content-between" href="<?= BASE_URL ?>catalog.php">
                    <div class="cat-img-wrapper mb-3">
                        <img src="<?= BASE_URL ?>assets/img/icons/icon_chargers.png" alt="Chargers & Cables" loading="lazy" decoding="async" class="cat-3d-icon">
                    </div>
                    <div>
                        <strong class="d-block fs-6 fw-extrabold text-dark mb-1">Chargers &amp; Cables</strong>
                        <span class="small text-muted">Fast &amp; genuine</span>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a class="cat-tile h-100 d-flex flex-column align-items-center justify-content-between" href="<?= BASE_URL ?>book_repair.php">
                    <div class="cat-img-wrapper mb-3">
                        <img src="<?= BASE_URL ?>assets/img/icons/icon_repairs.png" alt="Repairs" loading="lazy" decoding="async" class="cat-3d-icon">
                    </div>
                    <div>
                        <strong class="d-block fs-6 fw-extrabold text-dark mb-1">Repairs</strong>
                        <span class="small text-muted">Book a technician</span>
                    </div>
                </a>
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

    <!-- Official Apple Ecosystem Spotlight & Interactive Filter Bar -->
    <div class="container mb-4">
        <div class="p-4 rounded-4 border d-flex flex-wrap align-items-center justify-content-between gap-3" style="background: linear-gradient(135deg, #18181b 0%, #2d1122 65%, #4a1534 100%); border-color: #831843 !important; color: #ffffff; box-shadow: 0 18px 40px -15px rgba(45, 17, 34, 0.4);">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 56px; height: 56px; background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2);">
                    <i class="fa-brands fa-apple fs-2 text-white"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-danger rounded-pill px-2.5 py-0.5" style="font-size: 0.68rem;">NEW ARRIVALS</span>
                        <span class="text-white-50 small fw-semibold" style="font-size: 0.75rem;">iPhone 11 – 15 Pro Max • AirPods Pro 2 • Watch Ultra 2 • MagSafe</span>
                    </div>
                    <h4 class="fw-extrabold mb-0 text-white" style="letter-spacing: -0.4px;">Apple Certified Devices &amp; Original MagSafe Ecosystem</h4>
                </div>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-light rounded-pill px-3 fw-bold home-brand-filter active" data-filter="all" onclick="filterHomeShowcase('all', this)">
                    All Featured
                </button>
                <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3 fw-semibold home-brand-filter" data-filter="Apple" onclick="filterHomeShowcase('Apple', this)">
                    <i class="fa-brands fa-apple me-1"></i> Apple Only
                </button>
                <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3 fw-semibold home-brand-filter" data-filter="other" onclick="filterHomeShowcase('other', this)">
                    Android &amp; Universal
                </button>
                <a href="<?= BASE_URL ?>catalog.php?q=Apple" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">
                    Full Apple Catalog <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Featured Accessories -->
    <div class="container mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0">Featured Accessories</h3>
                <p class="text-muted small mb-0">MagSafe gear, AirPods, fast chargers, power banks, and cases</p>
            </div>
            <a href="<?= BASE_URL ?>catalog.php?type=accessories" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                View All <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4" id="homeAccessoriesGrid">
            <?php if (!empty($featured_products)): ?>
                <?php foreach ($featured_products as $prod): 
                    $prod_img = '';
                    if (!empty($prod['image']) && file_exists(__DIR__ . '/assets/img/products/' . $prod['image'])) {
                        $prod_img = BASE_URL . 'assets/img/products/' . $prod['image'];
                    }
                    $prod_brand = $prod['brand'] ?? 'Accessory';
                    $ad_vid = get_product_ad_video($prod['name'], $prod_brand);
                ?>
                    <div class="col-6 col-md-3 home-showcase-item" data-brand="<?= htmlspecialchars($prod_brand) ?>">
                        <div class="card h-100 card-custom stat-card overflow-hidden accessory-card position-relative"
                             role="button"
                             tabindex="0"
                             data-id="<?= $prod['id'] ?>"
                             data-name="<?= htmlspecialchars($prod['name']) ?>"
                             data-brand="<?= htmlspecialchars($prod_brand) ?>"
                             data-price="<?= $prod['selling_price'] ?>"
                             data-formatted-price="<?= format_currency($prod['selling_price']) ?>"
                             data-stock="<?= $prod['stock_quantity'] ?>"
                             data-sku="<?= htmlspecialchars($prod['sku']) ?>"
                             data-description="<?= htmlspecialchars($prod['description'] ?? 'Quality cellphone accessory from 8J.') ?>"
                             data-image="<?= htmlspecialchars($prod_img) ?>"
                             data-ad-hd="<?= htmlspecialchars($ad_vid['hd'] ?? '') ?>"
                             data-ad-label="<?= htmlspecialchars($ad_vid['label'] ?? '') ?>"
                             style="cursor: pointer;">
                            
                            <!-- Hover Progress Countdown Indicator -->
                            <div class="hover-progress-bar"></div>

                            <div class="bg-light p-3 text-center position-relative d-flex align-items-center justify-content-center" style="height: 175px; overflow: hidden; background: radial-gradient(circle, #ffffff 55%, #fdf2f8 100%);">
                                <?php if (!empty($ad_vid['hover'])): ?>
                                    <!-- Outsourced Commercial Video Ad (Streams on Hover, Zero Initial Load) -->
                                    <video class="product-hover-video" muted playsinline preload="none" data-ad-src="<?= htmlspecialchars($ad_vid['hover']) ?>" data-ad-next="<?= htmlspecialchars($ad_vid['hover_next'] ?? '') ?>"></video>
                                <?php endif; ?>

                                <?php if (strcasecmp($prod_brand, 'Apple') === 0): ?>
                                    <span class="badge bg-dark text-white position-absolute top-0 start-0 m-2 px-2 py-1 rounded-pill" style="font-size: 0.65rem; z-index: 4;">
                                        <i class="fa-brands fa-apple me-1"></i>Apple
                                    </span>
                                <?php endif; ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle position-absolute top-0 end-0 m-2 px-2 py-0.5 rounded-pill" style="font-size: 0.65rem; z-index: 4;">
                                    <?= (int)$prod['stock_quantity'] ?> in stock
                                </span>
                                <?php if (!empty($prod_img)): ?>
                                    <img src="<?= $prod_img ?>" alt="<?= htmlspecialchars($prod['name']) ?>" loading="lazy" decoding="async" class="product-thumb" style="max-height: 140px; max-width: 100%; object-fit: contain; transition: transform 0.3s ease, opacity 0.3s ease; filter: drop-shadow(0 8px 16px rgba(0,0,0,0.08));">
                                <?php else: ?>
                                    <i class="fa-solid fa-plug-circle-bolt fs-1 text-primary opacity-50"></i>
                                <?php endif; ?>
                            </div>

                            <div class="card-body d-flex flex-column">
                                <div class="badge bg-light text-secondary border align-self-start mb-2" style="font-size: 0.68rem;">
                                    <?= htmlspecialchars($prod_brand) ?>
                                </div>
                                <h6 class="fw-bold mb-1 text-dark text-truncate" title="<?= htmlspecialchars($prod['name']) ?>">
                                    <?= htmlspecialchars($prod['name']) ?>
                                </h6>
                                <p class="text-muted small mb-2 text-truncate" style="font-size: 0.76rem;">
                                    <?= htmlspecialchars($prod['description'] ?? 'Official 8J certified accessory') ?>
                                </p>
                                <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center">
                                    <span class="fw-extrabold text-primary fs-5"><?= format_currency($prod['selling_price']) ?></span>
                                    <div class="d-flex gap-1">
                                        <a href="<?= BASE_URL ?>product_detail.php?id=<?= $prod['id'] ?>"
                                           class="btn btn-sm btn-outline-dark rounded-circle add-cart-btn"
                                           title="Full Specs"
                                           onclick="event.stopPropagation();"
                                           style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                                            <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 0.72rem;"></i>
                                        </a>
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
                <p class="text-muted small mb-0">25-point diagnostic certified iPhones &amp; Android units ready for pickup or swap</p>
            </div>
            <a href="<?= BASE_URL ?>catalog.php?type=units" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                View All Phones <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4" id="homePhonesGrid">
            <?php if (!empty($featured_units)): ?>
                <?php foreach ($featured_units as $unit): 
                    $unit_img = '';
                    if (!empty($unit['image']) && file_exists(__DIR__ . '/assets/img/products/' . $unit['image'])) {
                        $unit_img = BASE_URL . 'assets/img/products/' . $unit['image'];
                    }
                    $u_ad_vid = get_product_ad_video($unit['model_name'], $unit['brand']);
                ?>
                    <div class="col-md-6 col-lg-3 home-showcase-item" data-brand="<?= htmlspecialchars($unit['brand']) ?>">
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
                             data-ad-hd="<?= htmlspecialchars($u_ad_vid['hd'] ?? '') ?>"
                             data-ad-label="<?= htmlspecialchars($u_ad_vid['label'] ?? '') ?>"
                             style="cursor: pointer;">
                            
                            <!-- Hover Progress Countdown Indicator -->
                            <div class="hover-progress-bar"></div>

                            <div class="bg-light p-3 text-center position-relative d-flex align-items-center justify-content-center" style="height: 180px; overflow: hidden; background: radial-gradient(circle, #ffffff 55%, #fdf2f8 100%);">
                                <?php if (!empty($u_ad_vid['hover'])): ?>
                                    <!-- Outsourced Commercial Video Ad (Streams on Hover, Zero Initial Load) -->
                                    <video class="product-hover-video" muted playsinline preload="none" data-ad-src="<?= htmlspecialchars($u_ad_vid['hover']) ?>" data-ad-next="<?= htmlspecialchars($u_ad_vid['hover_next'] ?? '') ?>"></video>
                                <?php endif; ?>

                                <?php if (strcasecmp($unit['brand'], 'Apple') === 0): ?>
                                    <span class="badge bg-dark text-white position-absolute top-0 start-0 m-2 px-2.5 py-1 rounded-pill" style="font-size: 0.65rem; z-index: 4;">
                                        <i class="fa-brands fa-apple me-1"></i>iPhone
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($unit_img)): ?>
                                    <img src="<?= $unit_img ?>" alt="<?= htmlspecialchars($unit['model_name']) ?>" loading="lazy" decoding="async" class="product-thumb" style="max-height: 148px; max-width: 100%; object-fit: contain; transition: transform 0.3s ease, opacity 0.3s ease; filter: drop-shadow(0 10px 18px rgba(0,0,0,0.1));">
                                <?php else: ?>
                                    <i class="fa-solid fa-mobile-screen fs-1 text-primary opacity-50"></i>
                                <?php endif; ?>
                            </div>

                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-info-subtle text-info-emphasis border"><?= htmlspecialchars($unit['brand']) ?></span>
                                    <span class="badge bg-success-subtle text-success-emphasis border"><?= htmlspecialchars($unit['unit_condition']) ?></span>
                                </div>
                                <h5 class="fw-bold text-dark mb-1 text-truncate" title="<?= htmlspecialchars($unit['model_name']) ?>"><?= htmlspecialchars($unit['model_name']) ?></h5>
                                <div class="text-muted small mb-2">
                                    <span class="fw-semibold text-dark"><?= htmlspecialchars($unit['storage_capacity']) ?></span> • 
                                    <span><?= htmlspecialchars($unit['color']) ?></span>
                                    <?php if ($unit['battery_health']): ?>
                                        • <span class="text-success fw-semibold"><i class="fa-solid fa-battery-three-quarters me-1"></i><?= $unit['battery_health'] ?>%</span>
                                    <?php endif; ?>
                                </div>
                                <div class="p-2 bg-light rounded text-muted small mb-3 d-flex justify-content-between align-items-center">
                                    <span><i class="fa-solid fa-barcode me-1"></i> IMEI: <code><?= substr($unit['imei_1'], 0, 8) ?>******</code></span>
                                    <span class="badge bg-white text-success border" style="font-size: 0.62rem;"><i class="fa-solid fa-check me-1"></i>NTC Clean</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                                    <div>
                                        <div class="text-muted" style="font-size: 0.7rem;">Store Pick-Up Price</div>
                                        <div class="fw-bolder text-primary fs-5"><?= format_currency($unit['selling_price']) ?></div>
                                    </div>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-primary rounded-pill px-2.5" 
                                                onclick="event.stopPropagation(); openPhoneModalFromCard(this.closest('.phone-card'), bootstrap.Modal.getInstance(document.getElementById('phonePreviewModal')) || new bootstrap.Modal(document.getElementById('phonePreviewModal')));"
                                                style="white-space: nowrap; font-size: 0.76rem;">
                                            <i class="fa-regular fa-eye me-1"></i> Quick View
                                        </button>
                                        <button class="btn btn-sm btn-primary rounded-circle"
                                                title="Reserve Unit for Counter Pick-Up"
                                                onclick="event.stopPropagation(); addToCart(<?= $unit['id'] ?>, '<?= addslashes(htmlspecialchars($unit['brand'] . ' ' . $unit['model_name'] . ' ' . $unit['storage_capacity'])) ?>', <?= $unit['selling_price'] ?>, 'phone_unit');"
                                                style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                                            <i class="fa-solid fa-cart-plus" style="font-size: 0.76rem;"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- 4-Step Customer Experience Timeline (Enhanced Store Pickup & Trade-In Design) -->
    <div class="py-5 my-5 position-relative" style="background: linear-gradient(180deg, #fff5f8 0%, #ffffff 100%); border-top: 1px solid #fce7f3; border-bottom: 1px solid #fce7f3;">
        <div class="container py-3">
            <div class="text-center max-w-xl mx-auto mb-5">
                <span class="badge px-3 py-1.5 rounded-pill fw-bold text-uppercase" 
                      style="background: #ffe4e6; color: #e11d48; font-size: 0.75rem; letter-spacing: 0.8px; border: 1px solid #fecdd3;">
                    SEAMLESS IN-STORE EXPERIENCE
                </span>
                <h2 class="display-6 fw-extrabold text-dark mt-2.5 mb-2" style="font-weight: 800; letter-spacing: -0.5px;">
                    How Store Pickup &amp; Trade-In Works
                </h2>
                <p class="text-muted" style="font-size: 0.95rem;">
                    Zero online payment risk. Reserve from your phone, inspect in person, take home today.
                </p>
            </div>

            <div class="row g-4 position-relative align-items-stretch">
                <!-- Step 1 -->
                <div class="col-12 col-md-6 col-lg-3 position-relative">
                    <div class="step-flow-card h-100 p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="step-circle-badge">01</span>
                                <span class="step-pill-tag"><i class="fa-regular fa-clock me-1 text-danger"></i> &lt; 2 Mins</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <h5 class="step-headline mb-0">Browse<br>Live Stock</h5>
                                <div class="step-ill-box flex-shrink-0">
                                    <img src="assets/img/steps/step1_browse.png" alt="Browse Live Stock" loading="lazy" decoding="async">
                                </div>
                            </div>
                            <p class="step-body-text mb-0">
                                Explore real-time Tanauan inventory of genuine accessories and technician-certified smartphones with transparent pricing.
                            </p>
                        </div>
                        <div class="step-footer-check mt-3">
                            <i class="fa-solid fa-check"></i> Live Stock Sync
                        </div>
                    </div>
                    <!-- Connector arrow (visible on lg screens) -->
                    <div class="step-connector-arrow d-none d-lg-flex" aria-hidden="true">
                        <svg width="24" height="12" viewBox="0 0 24 12" fill="none">
                            <path d="M1 6H20M20 6L15 2M20 6L15 10" stroke="#cbd5e1" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="2.5 2.5"/>
                        </svg>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="col-12 col-md-6 col-lg-3 position-relative">
                    <div class="step-flow-card h-100 p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="step-circle-badge">02</span>
                                <span class="step-pill-tag"><i class="fa-solid fa-bolt text-warning me-1"></i> Instant</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <h5 class="step-headline mb-0">Generate<br>Claim Slip</h5>
                                <div class="step-ill-box flex-shrink-0">
                                    <img src="assets/img/steps/step2_claim.png" alt="Generate Claim Slip" loading="lazy" decoding="async">
                                </div>
                            </div>
                            <p class="step-body-text mb-0">
                                Confirm your reservation with 0 reservation fee. A digital claim ticket is created instantly with a verified reference code.
                            </p>
                        </div>
                        <div class="step-footer-check mt-3">
                            <i class="fa-solid fa-check"></i> Zero Upfront Deposit
                        </div>
                    </div>
                    <!-- Connector arrow -->
                    <div class="step-connector-arrow d-none d-lg-flex" aria-hidden="true">
                        <svg width="24" height="12" viewBox="0 0 24 12" fill="none">
                            <path d="M1 6H20M20 6L15 2M20 6L15 10" stroke="#cbd5e1" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="2.5 2.5"/>
                        </svg>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="col-12 col-md-6 col-lg-3 position-relative">
                    <div class="step-flow-card h-100 p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="step-circle-badge">03</span>
                                <span class="step-pill-tag"><i class="fa-solid fa-wrench text-secondary me-1"></i> 15 Mins</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <h5 class="step-headline mb-0">Counter Test<br>&amp; Check</h5>
                                <div class="step-ill-box flex-shrink-0">
                                    <img src="assets/img/steps/step3_test.png" alt="Counter Test & Check" loading="lazy" decoding="async">
                                </div>
                            </div>
                            <p class="step-body-text mb-0">
                                Visit our Tanauan store. Our technician unboxes the unit and runs full audio, touch, and battery tests before you hand over any payment.
                            </p>
                        </div>
                        <div class="step-footer-check mt-3">
                            <i class="fa-solid fa-check"></i> Hands-On Verification
                        </div>
                    </div>
                    <!-- Connector arrow -->
                    <div class="step-connector-arrow d-none d-lg-flex" aria-hidden="true">
                        <svg width="24" height="12" viewBox="0 0 24 12" fill="none">
                            <path d="M1 6H20M20 6L15 2M20 6L15 10" stroke="#cbd5e1" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="2.5 2.5"/>
                        </svg>
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="col-12 col-md-6 col-lg-3 position-relative">
                    <div class="step-flow-card h-100 p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="step-circle-badge">04</span>
                                <span class="step-pill-tag"><i class="fa-solid fa-shield-halved text-danger me-1"></i> 7-Day Warranty</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <h5 class="step-headline mb-0">Pay &amp;<br>Walk Out</h5>
                                <div class="step-ill-box flex-shrink-0">
                                    <img src="assets/img/steps/step4_warranty.png" alt="Pay & Walk Out" loading="lazy" decoding="async">
                                </div>
                            </div>
                            <p class="step-body-text mb-0">
                                Pay easily via Cash or GCash. You receive an official warranty receipt covering 7-day direct replacement on all accessories.
                            </p>
                        </div>
                        <div class="step-footer-check mt-3">
                            <i class="fa-solid fa-check"></i> Official Warranty Receipt
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
                            <i class="fa-solid fa-shop me-1 text-secondary"></i> PHYSICAL KIOSK
                        </span>
                        <span class="badge bg-danger rounded-pill px-2.5 py-1 text-white small fw-bold">FREE CHECK-UP</span>
                    </div>

                    <h3 class="fw-extrabold text-white mb-2" style="letter-spacing: -0.5px;">Victory Mall Tanauan Booth</h3>
                    <p class="text-white-50 small mb-4" style="line-height: 1.65;">
                        Visit our official kiosk for counter pickups, phone trade-in inspections, and full diagnostic repairs with zero consultation fees.
                    </p>

                    <div class="d-flex flex-column gap-3 mb-4">
                        <div class="d-flex align-items-start gap-3">
                            <div class="bg-secondary bg-opacity-25 rounded-circle p-2.5 text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div>
                                <strong class="text-white d-block small">Store Address</strong>
                                <span class="text-white-50" style="font-size: 0.82rem;">Victory Mall Tanauan, 2nd Floor CA Exhibit Stall #S1, Tanauan City, Batangas</span>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-3">
                            <div class="bg-secondary bg-opacity-25 rounded-circle p-2.5 text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <div>
                                <strong class="text-white d-block small">Operating Hours</strong>
                                <span class="text-white-50" style="font-size: 0.82rem;">Monday – Sunday: 9:00 AM – 7:00 PM (Mall Hours)</span>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-3">
                            <div class="bg-secondary bg-opacity-25 rounded-circle p-2.5 text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                            <div>
                                <strong class="text-white d-block small">Direct Helpline &amp; Viber</strong>
                                <span class="text-white-50" style="font-size: 0.82rem;"><strong>0909-9833-981</strong> (Call &amp; SMS)</span>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-3">
                            <div class="bg-secondary bg-opacity-25 rounded-circle p-2.5 text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-brands fa-facebook-f"></i>
                            </div>
                            <div>
                                <strong class="text-white d-block small">Official Facebook</strong>
                                <a href="https://www.facebook.com/profile.php?id=100063760157450" target="_blank" rel="noopener noreferrer" class="text-white-50 text-decoration-underline" style="font-size: 0.82rem;">facebook.com/Gab_8J (@Gab_8J)</a>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="<?= BASE_URL ?>book_repair.php" class="btn btn-light rounded-pill py-2.5 fw-bold d-flex align-items-center justify-content-center gap-2">
                            <i class="fa-solid fa-calendar-check text-danger"></i> Book Free Diagnostic Appointment
                        </a>
                        <a href="tel:09099833981" class="btn btn-outline-light rounded-pill py-2 small fw-semibold d-flex align-items-center justify-content-center gap-2">
                            <i class="fa-solid fa-phone"></i> Call Store: 0909-9833-981
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right: Interactive FAQ Accordion -->
            <div class="col-lg-7" id="faqSection">
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
                    <!-- Item Picture & Outsourced Commercial Video Centered in Column -->
                    <div class="col-md-5 text-center">
                        <div class="p-3 bg-light rounded-4 d-flex align-items-center justify-content-center shadow-inner position-relative overflow-hidden" style="min-height: 270px; max-height: 310px; background: radial-gradient(circle, #ffffff 60%, #e9ecef 100%);">
                            <img id="modalProductImage" src="" alt="Product Picture" class="img-fluid rounded-3" style="max-height: 250px; object-fit: contain; filter: drop-shadow(0 12px 20px rgba(0,0,0,0.12)); transition: transform 0.35s ease;">
                            <div id="modalProduct3DContainer" class="d-none studio-3d-viewport rounded-3" style="position: absolute; inset: 0; width: 100%; height: 100%; background: radial-gradient(circle, #ffffff 0%, #fff0f6 70%, #fce7f3 100%); z-index: 4;"></div>
                            <video id="modalProductVideo" muted loop playsinline class="d-none rounded-3" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; background: #09090b; z-index: 3;"></video>
                            <div id="modalProductFallbackIcon" class="d-none">
                                <i class="fa-solid fa-plug-circle-bolt display-1 text-primary opacity-50"></i>
                            </div>
                            <button type="button" id="toggleModalProduct3DBtn" class="btn btn-sm btn-dark rounded-pill position-absolute bottom-0 start-0 m-2 px-3 py-1 fw-bold shadow-sm" style="font-size: 0.72rem; z-index: 5;" onclick="toggleModal3DView('product')">
                                <i class="fa-solid fa-cube text-info me-1"></i> 360° 3D
                            </button>
                            <button type="button" id="toggleModalProductAdBtn" class="btn btn-sm btn-dark rounded-pill position-absolute bottom-0 end-0 m-2 px-3 py-1 fw-bold shadow-sm" style="font-size: 0.72rem; z-index: 5;" onclick="toggleModalAdVideo('product')">
                                <i class="fa-solid fa-play text-warning me-1"></i> Watch Ad
                            </button>
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
                    <!-- Phone Picture & Outsourced Commercial Video Centered in Column -->
                    <div class="col-md-5 text-center">
                        <div class="p-3 bg-light rounded-4 d-flex align-items-center justify-content-center shadow-inner position-relative overflow-hidden" style="min-height: 290px; max-height: 330px; background: radial-gradient(circle, #ffffff 60%, #e9ecef 100%);">
                            <img id="modalPhoneImage" src="" alt="Phone Picture" class="img-fluid rounded-3" style="max-height: 270px; object-fit: contain; filter: drop-shadow(0 15px 25px rgba(0,0,0,0.15)); transition: transform 0.35s ease;">
                            <div id="modalPhone3DContainer" class="d-none studio-3d-viewport rounded-3" style="position: absolute; inset: 0; width: 100%; height: 100%; background: radial-gradient(circle, #ffffff 0%, #fff0f6 70%, #fce7f3 100%); z-index: 4;"></div>
                            <video id="modalPhoneVideo" muted loop playsinline class="d-none rounded-3" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; background: #09090b; z-index: 3;"></video>
                            <div id="modalPhoneFallbackIcon" class="d-none">
                                <i class="fa-solid fa-mobile-screen display-1 text-primary opacity-50"></i>
                            </div>
                            <button type="button" id="toggleModalPhone3DBtn" class="btn btn-sm btn-dark rounded-pill position-absolute bottom-0 start-0 m-2 px-3 py-1 fw-bold shadow-sm" style="font-size: 0.72rem; z-index: 5;" onclick="toggleModal3DView('phone')">
                                <i class="fa-solid fa-cube text-info me-1"></i> 360° 3D
                            </button>
                            <button type="button" id="toggleModalPhoneAdBtn" class="btn btn-sm btn-dark rounded-pill position-absolute bottom-0 end-0 m-2 px-3 py-1 fw-bold shadow-sm" style="font-size: 0.72rem; z-index: 5;" onclick="toggleModalAdVideo('phone')">
                                <i class="fa-solid fa-play text-warning me-1"></i> Watch Ad
                            </button>
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
                            <a href="#faqSection" class="btn btn-outline-dark rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.85rem;" data-bs-dismiss="modal" onclick="setTimeout(function(){ var f = document.getElementById('faqCol1'); if (f && window.bootstrap) { new bootstrap.Collapse(f, {show:true}); } }, 350);">
                                <i class="fa-solid fa-arrows-rotate text-primary"></i> Trade-In Swap Info
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
    background: linear-gradient(90deg, #db2777, #fbbf24);
    transition: width 45s linear;
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

function filterHomeShowcase(brandFilter, btnEl) {
    document.querySelectorAll('.home-brand-filter').forEach(btn => {
        btn.classList.remove('btn-light', 'fw-bold', 'active');
        btn.classList.add('btn-outline-light', 'fw-semibold');
    });
    if (btnEl) {
        btnEl.classList.remove('btn-outline-light', 'fw-semibold');
        btnEl.classList.add('btn-light', 'fw-bold', 'active');
    }

    document.querySelectorAll('.home-showcase-item').forEach(col => {
        const itemBrand = (col.dataset.brand || '').toLowerCase();
        let show = true;
        if (brandFilter === 'Apple') {
            show = (itemBrand === 'apple');
        } else if (brandFilter === 'other') {
            show = (itemBrand !== 'apple');
        }
        col.style.display = show ? '' : 'none';
    });
}

let currentPreviewProduct = null;
let currentPreviewPhone = null;
let hoverTimer = null;
const HOVER_TRIGGER_DELAY = 45000; // 45 seconds after hovering before modal preview auto-opens

document.addEventListener('DOMContentLoaded', function () {
    const previewModalEl = document.getElementById('itemPreviewModal');
    const phoneModalEl = document.getElementById('phonePreviewModal');
    const bsModal = previewModalEl ? new bootstrap.Modal(previewModalEl) : null;
    const bsPhoneModal = phoneModalEl ? new bootstrap.Modal(phoneModalEl) : null;

    // Cancel any hover countdown immediately when user scrolls with wheel
    window.addEventListener('scroll', function () {
        if (hoverTimer) {
            clearTimeout(hoverTimer);
            hoverTimer = null;
            document.querySelectorAll('.accessory-card.hover-active, .phone-card.hover-active').forEach(c => c.classList.remove('hover-active'));
        }
    }, { passive: true });

    // 1. Wire up accessory cards
    document.querySelectorAll('.accessory-card').forEach(card => {
        card.addEventListener('mouseenter', function () {
            if (document.body.classList.contains('is-scrolling')) return;
            if ((previewModalEl && previewModalEl.classList.contains('show')) || 
                (phoneModalEl && phoneModalEl.classList.contains('show'))) return;

            card.classList.add('hover-active');
            clearTimeout(hoverTimer);

            hoverTimer = setTimeout(() => {
                card.classList.remove('hover-active');
                if (!document.body.classList.contains('is-scrolling') && bsModal) {
                    openModalFromCard(card, bsModal);
                }
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
            if (document.body.classList.contains('is-scrolling')) return;
            if ((previewModalEl && previewModalEl.classList.contains('show')) || 
                (phoneModalEl && phoneModalEl.classList.contains('show'))) return;

            card.classList.add('hover-active');
            clearTimeout(hoverTimer);

            hoverTimer = setTimeout(() => {
                card.classList.remove('hover-active');
                if (!document.body.classList.contains('is-scrolling') && bsPhoneModal) {
                    openPhoneModalFromCard(card, bsPhoneModal);
                }
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

    // Reset hover states and pause modal videos on modal hide
    [previewModalEl, phoneModalEl].forEach(m => {
        if (!m) return;
        m.addEventListener('hidden.bs.modal', function () {
            document.querySelectorAll('.accessory-card, .phone-card').forEach(c => c.classList.remove('hover-active'));
            clearTimeout(hoverTimer);
            ['modalProductVideo', 'modalPhoneVideo'].forEach(vidId => {
                const v = document.getElementById(vidId);
                if (v) {
                    try { v.pause(); } catch (e) {}
                    v.classList.add('d-none');
                }
            });
            ['modalProduct3DContainer', 'modalPhone3DContainer'].forEach(cid => {
                const c = document.getElementById(cid);
                if (c) c.classList.add('d-none');
            });
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
    const vidEl = document.getElementById('modalProductVideo');
    const c3dEl = document.getElementById('modalProduct3DContainer');
    const adBtn = document.getElementById('toggleModalProductAdBtn');
    const btn3d = document.getElementById('toggleModalProduct3DBtn');
    const iconEl = document.getElementById('modalProductFallbackIcon');

    if (vidEl) {
        try { vidEl.pause(); } catch (e) {}
        vidEl.classList.add('d-none');
        vidEl.dataset.adHd = card.dataset.adHd || '';
    }
    if (c3dEl) c3dEl.classList.add('d-none');
    if (adBtn) {
        adBtn.innerHTML = '<i class="fa-solid fa-play text-warning me-1"></i> Watch Ad';
    }
    if (btn3d) {
        btn3d.innerHTML = '<i class="fa-solid fa-cube text-info me-1"></i> 360° 3D';
    }

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
    const vidEl = document.getElementById('modalPhoneVideo');
    const c3dEl = document.getElementById('modalPhone3DContainer');
    const adBtn = document.getElementById('toggleModalPhoneAdBtn');
    const btn3d = document.getElementById('toggleModalPhone3DBtn');
    const iconEl = document.getElementById('modalPhoneFallbackIcon');

    if (vidEl) {
        try { vidEl.pause(); } catch (e) {}
        vidEl.classList.add('d-none');
        vidEl.dataset.adHd = card.dataset.adHd || '';
    }
    if (c3dEl) c3dEl.classList.add('d-none');
    if (adBtn) {
        adBtn.innerHTML = '<i class="fa-solid fa-play text-warning me-1"></i> Watch Ad';
    }
    if (btn3d) {
        btn3d.innerHTML = '<i class="fa-solid fa-cube text-info me-1"></i> 360° 3D';
    }

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

let modalProduct3DInstance = null;
let modalPhone3DInstance = null;

function toggleModal3DView(mode) {
    const isPhone = (mode === 'phone');
    const c3dEl = document.getElementById(isPhone ? 'modalPhone3DContainer' : 'modalProduct3DContainer');
    const vidEl = document.getElementById(isPhone ? 'modalPhoneVideo' : 'modalProductVideo');
    const btn3d = document.getElementById(isPhone ? 'toggleModalPhone3DBtn' : 'toggleModalProduct3DBtn');
    const btnAd = document.getElementById(isPhone ? 'toggleModalPhoneAdBtn' : 'toggleModalProductAdBtn');
    if (!c3dEl || !btn3d || typeof window.Product3DStudio !== 'function') return;

    if (c3dEl.classList.contains('d-none')) {
        if (vidEl && !vidEl.classList.contains('d-none')) {
            try { vidEl.pause(); } catch (e) {}
            vidEl.classList.add('d-none');
            if (btnAd) btnAd.innerHTML = '<i class="fa-solid fa-play text-warning me-1"></i> Watch Ad';
        }
        c3dEl.classList.remove('d-none');
        btn3d.innerHTML = '<i class="fa-solid fa-image text-warning me-1"></i> Photo';

        const cfg = isPhone && currentPreviewPhone ? {
            name: (currentPreviewPhone.brand || '') + ' ' + (currentPreviewPhone.model || ''),
            brand: currentPreviewPhone.brand,
            color: currentPreviewPhone.color,
            type: 'unit',
            image: currentPreviewPhone.image
        } : (currentPreviewProduct ? {
            name: currentPreviewProduct.name,
            brand: currentPreviewProduct.brand,
            type: 'product',
            image: currentPreviewProduct.image
        } : { name: '8J Device' });

        if (isPhone) {
            if (!modalPhone3DInstance) {
                modalPhone3DInstance = new window.Product3DStudio(c3dEl, cfg);
            } else {
                modalPhone3DInstance.rebuildModel(cfg);
                if (modalPhone3DInstance.resizeHandler) modalPhone3DInstance.resizeHandler();
            }
        } else {
            if (!modalProduct3DInstance) {
                modalProduct3DInstance = new window.Product3DStudio(c3dEl, cfg);
            } else {
                modalProduct3DInstance.rebuildModel(cfg);
                if (modalProduct3DInstance.resizeHandler) modalProduct3DInstance.resizeHandler();
            }
        }
    } else {
        c3dEl.classList.add('d-none');
        btn3d.innerHTML = '<i class="fa-solid fa-cube text-info me-1"></i> 360° 3D';
    }
}

function toggleModalAdVideo(mode) {
    const isPhone = (mode === 'phone');
    const vidEl = document.getElementById(isPhone ? 'modalPhoneVideo' : 'modalProductVideo');
    const c3dEl = document.getElementById(isPhone ? 'modalPhone3DContainer' : 'modalProduct3DContainer');
    const btnEl = document.getElementById(isPhone ? 'toggleModalPhoneAdBtn' : 'toggleModalProductAdBtn');
    const btn3d = document.getElementById(isPhone ? 'toggleModalPhone3DBtn' : 'toggleModalProduct3DBtn');
    if (!vidEl || !btnEl) return;

    if (vidEl.classList.contains('d-none')) {
        if (c3dEl && !c3dEl.classList.contains('d-none')) {
            c3dEl.classList.add('d-none');
            if (btn3d) btn3d.innerHTML = '<i class="fa-solid fa-cube text-info me-1"></i> 360° 3D';
        }
        const src = vidEl.dataset.adHd;
        if (!src) return;
        if (vidEl.getAttribute('src') !== src) {
            vidEl.src = src;
            vidEl.load();
        }
        vidEl.classList.remove('d-none');
        vidEl.play().catch(() => {});
        btnEl.innerHTML = '<i class="fa-solid fa-image text-info me-1"></i> Studio Photo';
    } else {
        try { vidEl.pause(); } catch (e) {}
        vidEl.classList.add('d-none');
        btnEl.innerHTML = '<i class="fa-solid fa-play text-warning me-1"></i> Watch Ad';
    }
}

function adjustPreviewQty(delta) {
    const input = document.getElementById('modalPreviewQty');
    let val = parseInt(input.value) || 1;
    val = Math.max(1, Math.min(20, val + delta));
    input.value = val;
}

document.addEventListener('DOMContentLoaded', function() {
    const carouselEl = document.getElementById('kioskHeroCarousel');
    if (carouselEl && window.bootstrap) {
        const carousel = new bootstrap.Carousel(carouselEl, {
            interval: 3500,
            ride: 'carousel',
            pause: 'hover'
        });

        carouselEl.addEventListener('slide.bs.carousel', function (e) {
            const tabs = document.querySelectorAll('.kiosk-pill-tab');
            tabs.forEach(t => t.classList.remove('active'));
            const activeTab = document.querySelector(`.kiosk-pill-tab[data-slide-index="${e.to}"]`);
            if (activeTab) activeTab.classList.add('active');
        });
    }
});

function goToKioskSlide(index) {
    const carouselEl = document.getElementById('kioskHeroCarousel');
    if (carouselEl && window.bootstrap) {
        const carousel = bootstrap.Carousel.getInstance(carouselEl) || new bootstrap.Carousel(carouselEl);
        carousel.to(index);
    }
}

function openStorePhotoModal(imgSrc, caption) {
    const modalEl = document.getElementById('storePhotoModal');
    if (!modalEl) return;
    document.getElementById('storeModalImg').src = imgSrc;
    document.getElementById('storeModalTitle').innerText = caption || 'Victory Mall Tanauan Stall #S1';
    document.getElementById('storeModalCaption').innerText = caption || '8J Cellphone & Laptop Repair • Victory Mall Tanauan 2nd Floor';
    const bsModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    bsModal.show();
}
</script>

<!-- Store Real Photo Lightbox Modal -->
<div class="modal fade" id="storePhotoModal" tabindex="-1" aria-labelledby="storePhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden" style="background: #2b1120; color: #ffffff;">
            <div class="modal-header border-0 pb-0 pt-3 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-danger rounded-pill px-3 py-1 small fw-bold">
                        <i class="fa-solid fa-location-dot me-1"></i> Victory Mall Tanauan
                    </span>
                    <span id="storeModalTitle" class="fw-bold text-white small"></span>
                </div>
                <button type="button" class="btn-close btn-close-white rounded-circle p-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 text-center">
                <img id="storeModalImg" src="" alt="Store Photo" class="img-fluid rounded-3 shadow" style="max-height: 75vh; width: 100%; object-fit: contain;">
                <p id="storeModalCaption" class="text-white-50 small mt-3 mb-1"></p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
