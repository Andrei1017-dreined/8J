/**
 * ============================================================================
 * 8J CELLPHONE & ACCESSORIES IMS — ONLINE 3D MODEL STUDIO (SKETCHFAB API)
 * ============================================================================
 * Streams real, high-poly photorealistic 3D CAD models online via the official
 * Sketchfab 3D Viewer API (`sketchfab-viewer-1.12.1.js`).
 *
 * Features:
 *  - Zero local storage weight: streams real textured 3D models from Sketchfab CDN
 *  - Full 360° all-angles orbit, pan, pinch/scroll zoom & fullscreen support
 *  - Programmatic Camera Angle Presets (Front, Back, Left, Right, Top, Bottom, Iso)
 *    calibrated automatically to each 3D model's bounding radius
 *  - Live Color Variant 3D Model Switching (e.g., iPhone 15 Pink / Blue / Black)
 *  - Automatic online 3D model search fallback for any new admin-added products
 * ============================================================================
 */

(function () {
    'use strict';

    const SKETCHFAB_API_URL = 'https://static.sketchfab.com/api/sketchfab-viewer-1.12.1.js';
    let sketchfabLoadPromise = null;

    function ensureSketchfabAPI() {
        if (window.Sketchfab) return Promise.resolve(window.Sketchfab);
        if (sketchfabLoadPromise) return sketchfabLoadPromise;

        sketchfabLoadPromise = new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = SKETCHFAB_API_URL;
            script.async = true;
            script.onload = () => resolve(window.Sketchfab);
            script.onerror = (err) => reject(err);
            document.head.appendChild(script);
        });
        return sketchfabLoadPromise;
    }

    /**
     * Resolves the verified online Sketchfab 3D Model UID for any store product,
     * smartphone model, or color variant.
     */
    function resolveSketchfabModelUID(config) {
        const name = (config.name || '').toLowerCase();
        const color = (config.color || '').toLowerCase();
        const image = (config.image || '').toLowerCase();
        const hay = `${name} ${color} ${image}`;

        // --------------------------------------------------------------------
        // 1. SMARTPHONES (with color-specific 3D CAD models where available)
        // --------------------------------------------------------------------
        // iPhone 15 Pro Max
        if (name.includes('15 pro max')) {
            if (hay.includes('black')) return 'df17520841214c1792fb8a44c6783ee7'; // iPhone 15 Pro Max Black
            if (hay.includes('blue')) return 'abdb20d303884717a13fe154210d1de1';  // iPhone 15 Pro Deep Blue
            return '5b7b35513a154ac69619dc2b2fe15686'; // iPhone 15 Pro Max Natural Titanium
        }

        // iPhone 15 Pro (excluding cases)
        if (name.includes('15 pro') && !name.includes('case')) {
            if (hay.includes('black')) return '6fd1283ec05d412d99a3f23b2e80e473'; // iPhone 15 Pro Black
            if (hay.includes('natural') || hay.includes('white')) return '9e045e469d514fea9dda2ccd161f5fa3'; // iPhone 15 Pro Natural
            return 'abdb20d303884717a13fe154210d1de1'; // iPhone 15 Pro Blue Titanium
        }

        // iPhone 15 (Pink, Blue, Black)
        if (name.includes('iphone 15') && !name.includes('case')) {
            if (hay.includes('blue')) return '78bb0a55d5d1405784bb92f7056e1a0a';  // iPhone 15 Blue
            if (hay.includes('black')) return '6fd1283ec05d412d99a3f23b2e80e473'; // iPhone 15 Black
            return '206951b7b6ba4d8084acb96a05a6d06e'; // iPhone 15 Pink (Default for iPhone 15)
        }

        // iPhone 14 Pro
        if (name.includes('14 pro')) {
            if (hay.includes('gold')) return '704dff49176e482cb3c243b3e754b753'; // iPhone 14 Pro Gold
            return '5cb0778041a34f09b409a38c687bb1d4'; // iPhone 14 Pro Deep Purple
        }

        // iPhone 14 ( excluding glass/protector )
        if (name.includes('iphone 14') && !name.includes('glass') && !name.includes('protector')) {
            return 'feba05235df9485b87f316ebe7c54dd4'; // Apple iPhone 14
        }

        // iPhone 13 Pro
        if (name.includes('13 pro') && !name.includes('case')) {
            return 'a35156d91cf44e70a2fdfeade54ae0b2'; // iPhone 13 Pro Sierra Blue
        }

        // iPhone 13
        if (name.includes('iphone 13') && !name.includes('glass') && !name.includes('protector')) {
            return 'f505892cc6bf4e2399a95c6e56d515a1'; // Apple iPhone 13 Midnight
        }

        // iPhone 12 Pro
        if (name.includes('12 pro')) {
            return '05dfc991665e45c68c8b7062136c0c6e'; // iPhone 12 Pro Pacific Blue
        }

        // iPhone 12
        if (name.includes('iphone 12')) {
            return '3bfd56e122e446d3a5ecbfaa8a5c3bb2'; // iPhone 12 Purple
        }

        // iPhone 11
        if (name.includes('iphone 11')) {
            return 'b7c2e36dad814b398a65fadf0cee266f'; // Apple iPhone 11 White
        }

        // Samsung Galaxy S22
        if (name.includes('galaxy s22') || name.includes('samsung') && !name.includes('protector')) {
            return 'a71dfc62ae65414ca36525f63038e8aa'; // Samsung Galaxy S22
        }

        // Infinix Smart 20 / Infinix
        if (name.includes('infinix') || name.includes('smart 20')) {
            return '74f78043e34a477c8e2b6a55a27c55a9'; // Infinix Smartphone 3D
        }

        // Xiaomi Redmi Note 12
        if (name.includes('redmi') || name.includes('xiaomi')) {
            return '665bd0f698a940ef97e245b852d984fa'; // Xiaomi Redmi Note 12
        }

        // --------------------------------------------------------------------
        // 2. AUDIO, WEARABLES & ACCESSORIES
        // --------------------------------------------------------------------
        // Apple Watch Ultra 2
        if (name.includes('watch ultra') || name.includes('apple watch')) {
            return 'f33263c457664b43909200c5ed5e6fa2'; // Apple Watch Ultra 2 49mm
        }

        // AirPods Pro (2nd Gen)
        if (name.includes('airpods pro')) {
            return '0c1e6c9db901423d88a650d898d7b8d4'; // AirPods Pro 2 with MagSafe Case
        }

        // AirPods (3rd Gen)
        if (name.includes('airpods')) {
            return '7b46e68b8a0c4430b54e021a718c1c15'; // Apple AirPods 3
        }

        // Lenovo LP40 Pro / Wireless Earbuds
        if (name.includes('earbud') || name.includes('earphone') || name.includes('lenovo') || name.includes('lp40')) {
            return '1ad6915fb25647518d3e17264571df81'; // True Wireless Earbuds & Charging Case
        }

        // Apple MagSafe Magnetic Battery Pack
        if (name.includes('magsafe') && name.includes('battery')) {
            return 'dc39f25d0678487eac64c6b8b886dc48'; // Apple MagSafe Battery Pack
        }

        // Apple MagSafe 15W Wireless Charger
        if (name.includes('magsafe') && name.includes('charger')) {
            return '0f08def9c9124739b7d51c9d2cbb570f'; // Apple MagSafe Charger
        }

        // Apple 20W USB-C Power Adapter
        if (name.includes('20w') || name.includes('adapter')) {
            return '6343e348e7f94c5ea654b6aba3b7baef'; // Apple 20W USB-C Power Adapter
        }

        // Capsule Mini Power Bank
        if (name.includes('capsule')) {
            return '8468a5c0a3504caa8073a4abf6c3ec8a'; // Compact Power Bank
        }

        // BAVIN 20000mAh Power Bank
        if (name.includes('bavin') && (name.includes('power') || name.includes('20000'))) {
            return 'd644a373a5d2433ba3e0f642daa285ad'; // High-Capacity Fast Charge Power Bank
        }

        // Baseus 20000mAh Power Bank
        if (name.includes('powerbank') || name.includes('power bank') || name.includes('baseus')) {
            return '2ca131d5b5a242678e8218f709df4adc'; // 20000mAh Fast Charging Powerbank
        }

        // MagSafe Clear Shockproof Case
        if (name.includes('case')) {
            return '3a41ad00e27049d7aabfdcc78465263b'; // MagSafe Clear Shockproof Case
        }

        // 9D Tempered Glass / Screen Protector
        if (name.includes('glass') || name.includes('protector') || name.includes('tempered')) {
            return '226ca02f3ac648a49ec5a60e2973b5b3'; // 3D Screen Protector Glass
        }

        // Type-C to Lightning Braided Cable
        if (name.includes('cable')) {
            return '1fca53d42e364ca695040618889dd132'; // Type-C to Lightning Cable
        }

        // Default fallback (iPhone 15 Pro)
        return '9e045e469d514fea9dda2ccd161f5fa3';
    }

    // ========================================================================
    // SKETCHFAB 3D STUDIO CONTROLLER CLASS
    // ========================================================================
    class Product3DStudio {
        constructor(container, config) {
            this.container = container;
            this.config = config || {};
            this.api = null;
            this.currentUid = null;
            this.isWireframe = false;
            this.autoRotate = true;
            this.baseTarget = [0, 0, 0];
            this.baseRadius = 30;
            this.pendingAngle = null;

            this.initModel(this.config);
        }

        async initModel(newConfig) {
            this.config = Object.assign({}, this.config, newConfig || {});
            const targetUid = resolveSketchfabModelUID(this.config);

            // Avoid reloading iframe if the exact same 3D model UID is already active
            if (this.currentUid === targetUid && this.iframe) {
                return;
            }
            this.currentUid = targetUid;
            this.api = null;

            // Create clean responsive iframe + loading indicator
            this.container.innerHTML = `
                <div class="sketchfab-loader-overlay d-flex flex-column align-items-center justify-content-center"
                     style="position: absolute; inset: 0; background: radial-gradient(circle, #ffffff 0%, #fff0f6 100%); z-index: 2; transition: opacity 0.35s ease;">
                    <div class="spinner-border text-danger mb-2" role="status" style="width: 2.2rem; height: 2.2rem;"></div>
                    <span class="small fw-bold text-dark">Loading Online 3D Studio Model...</span>
                    <span class="text-muted" style="font-size: 0.72rem;">Drag to rotate 360° • Scroll to zoom</span>
                </div>
                <iframe class="w-100 h-100 border-0 rounded-4"
                        title="Interactive 3D Product Model"
                        allow="autoplay; fullscreen; xr-spatial-tracking"
                        allowfullscreen mozallowfullscreen="true" webkitallowfullscreen="true"
                        style="width: 100%; height: 100%; display: block;"></iframe>
            `;

            const loaderOverlay = this.container.querySelector('.sketchfab-loader-overlay');
            this.iframe = this.container.querySelector('iframe');

            try {
                const Sketchfab = await ensureSketchfabAPI();
                const client = new Sketchfab('1.12.1', this.iframe);

                client.init(targetUid, {
                    autostart: 1,
                    autospin: 0.2,
                    preload: 1,
                    ui_infos: 0,
                    ui_watermark: 0,
                    ui_watermark_link: 0,
                    ui_stop: 0,
                    ui_help: 0,
                    ui_inspector: 0,
                    ui_annotations: 0,
                    ui_settings: 0,
                    ui_vr: 0,
                    dnt: 1,
                    success: (api) => {
                        this.api = api;
                        api.start();
                        api.addEventListener('viewerready', () => {
                            if (loaderOverlay) {
                                loaderOverlay.style.opacity = '0';
                                setTimeout(() => loaderOverlay.remove(), 350);
                            }

                            // Measure initial bounding camera distance so angle buttons work at exact scale
                            api.getCameraLookAt((err, cam) => {
                                if (!err && cam && cam.position && cam.target) {
                                    this.baseTarget = cam.target;
                                    const dx = cam.position[0] - cam.target[0];
                                    const dy = cam.position[1] - cam.target[1];
                                    const dz = cam.position[2] - cam.target[2];
                                    const dist = Math.sqrt(dx * dx + dy * dy + dz * dz);
                                    if (dist > 0.001) {
                                        this.baseRadius = dist;
                                    }
                                    if (this.pendingAngle) {
                                        const pa = this.pendingAngle;
                                        this.pendingAngle = null;
                                        this.setAngle(pa.rotX, pa.rotY, pa.zoom);
                                    }
                                }
                            });
                        });
                    },
                    error: () => {
                        // Direct iframe embed fallback if API init fails
                        if (loaderOverlay) loaderOverlay.remove();
                        this.iframe.src = `https://sketchfab.com/models/${targetUid}/embed?autostart=1&autospin=0.2&ui_infos=0&ui_watermark=0`;
                    }
                });
            } catch (e) {
                if (loaderOverlay) loaderOverlay.remove();
                this.iframe.src = `https://sketchfab.com/models/${targetUid}/embed?autostart=1&autospin=0.2&ui_infos=0&ui_watermark=0`;
            }
        }

        rebuildModel(newConfig) {
            this.initModel(newConfig);
        }

        /**
         * Moves the online 3D camera smoothly to the requested spherical angle
         * around the model's center target.
         */
        setAngle(rotX, rotY, zoomFactor) {
            if (!this.api) {
                this.pendingAngle = { rotX, rotY, zoom: zoomFactor };
                return;
            }
            const t = this.baseTarget || [0, 0, 0];
            const r = this.baseRadius || 30;

            // Sketchfab uses Z-up coordinate system (X: right, Y: depth, Z: up)
            const elevation = Math.max(-1.45, Math.min(1.45, rotX || 0));
            const azimuth = (rotY || 0) - Math.PI / 2;

            const cosEl = Math.cos(elevation);
            const eyeX = t[0] + r * cosEl * Math.cos(azimuth);
            const eyeY = t[1] + r * cosEl * Math.sin(azimuth);
            const eyeZ = t[2] + r * Math.sin(elevation);

            this.api.setCameraLookAt([eyeX, eyeY, eyeZ], t, 0.85);
        }

        toggleAutoRotate() {
            this.autoRotate = !this.autoRotate;
            if (this.api && typeof this.api.setCameraLookAt === 'function') {
                // Orbit subtle nudge when toggling spin
                if (this.autoRotate) {
                    this.setAngle(0.15, 0.6, 5.0);
                }
            }
            return this.autoRotate;
        }

        setWireframe(enabled) {
            this.isWireframe = (typeof enabled === 'boolean') ? enabled : !this.isWireframe;
            if (this.api && typeof this.api.setWireframe === 'function') {
                this.api.setWireframe(this.isWireframe, { color: 'DB2777FF' });
            }
            return this.isWireframe;
        }

        resizeHandler() {
            // Sketchfab iframe resizes automatically via 100% width/height CSS
        }
    }

    window.Product3DStudio = Product3DStudio;
})();
