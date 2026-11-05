/**
 * 8J Cellphone & Accessories - Real-time 3D phone studio (Three.js r128)
 *
 * Used on the product page for every phone without a verified Sketchfab CAD model.
 * The handset is built in the browser from the item's own data:
 *   - body colour from the unit record, or finishes sampled from the product photos
 *   - a brand-appropriate camera module, buttons, ports and antenna lines
 *   - a live lock screen (real clock) with a wallpaper tinted to the finish
 * Studio lighting, real shadows, colour switching and an exploded "inside" view.
 */
(function () {
    'use strict';

    // ================================================================ colour helpers
    const NAMED = [
        ['Natural Titanium', /natural titanium|desert titanium|titanium natural/, '#a8a296'],
        ['Black Titanium', /black titanium/, '#2c2d30'],
        ['White Titanium', /white titanium/, '#e4e2dd'],
        ['Blue Titanium', /blue titanium/, '#3d4858'],
        ['Midnight', /midnight|black|graphite|onyx|obsidian|phantom|space|carbon|jet|dark/, '#1d1e22'],
        ['Starlight', /starlight|cream|beige|ivory|sand/, '#ece2d0'],
        ['Silver', /white|silver|snow|pearl|frost|glacier|moonlight/, '#e3e4e8'],
        ['Pink', /pink|rose|blush|sakura|coral/, '#efbfcf'],
        ['Purple', /purple|violet|lavender|lilac/, '#b5a2d6'],
        ['Deep Blue', /navy|deep blue|ocean|sierra|pacific/, '#2e4b70'],
        ['Blue', /blue|aqua|teal|cyan|sky|ice/, '#8cb1d8'],
        ['Green', /green|mint|emerald|forest|sage|jade|olive/, '#7c9c86'],
        ['Gold', /gold|champagne|sunrise/, '#e1cb9b'],
        ['Red', /red|crimson|ruby/, '#a3232c'],
        ['Yellow', /yellow|lemon/, '#efd65e'],
        ['Orange', /orange|tangerine/, '#e8834a'],
        ['Gray', /titanium|gray|grey|slate|ash/, '#7a7d84'],
    ];

    function colorFromName(name) {
        const c = String(name || '').toLowerCase();
        for (const [, re, hex] of NAMED) if (re.test(c)) return hex;
        return null;
    }

    function hexToRgb(hex) {
        const n = parseInt(hex.slice(1), 16);
        return [n >> 16, (n >> 8) & 255, n & 255];
    }
    function rgbToHex(r, g, b) {
        return '#' + ((1 << 24) + (Math.round(r) << 16) + (Math.round(g) << 8) + Math.round(b)).toString(16).slice(1);
    }
    function shade(hex, amt) {
        const [r, g, b] = hexToRgb(hex);
        const f = v => Math.max(0, Math.min(255, v + amt));
        return rgbToHex(f(r), f(g), f(b));
    }
    function luma(hex) {
        const [r, g, b] = hexToRgb(hex);
        return r * 0.299 + g * 0.587 + b * 0.114;
    }
    function hsl(r, g, b) {
        r /= 255; g /= 255; b /= 255;
        const max = Math.max(r, g, b), min = Math.min(r, g, b), l = (max + min) / 2;
        const s = max === min ? 0 : (l > 0.5 ? (max - min) / (2 - max - min) : (max - min) / (max + min));
        return { s, l };
    }
    // Plain colour words for finishes sampled from photos (no brand-specific names like "Titanium")
    function nearestName(hex) {
        const [r, g, b] = hexToRgb(hex);
        const max = Math.max(r, g, b) / 255, min = Math.min(r, g, b) / 255;
        const { s, l } = hsl(r, g, b);
        if (s < 0.09 || max - min < 0.04) {
            return l < 0.22 ? 'Black' : l < 0.5 ? 'Gray' : l < 0.82 ? 'Silver' : 'White';
        }
        let h;
        const R = r / 255, G = g / 255, B = b / 255, d = max - min;
        if (max === R) h = ((G - B) / d) % 6; else if (max === G) h = (B - R) / d + 2; else h = (R - G) / d + 4;
        h = (h * 60 + 360) % 360;
        const hue = h < 15 ? 'Red' : h < 40 ? 'Orange' : h < 65 ? 'Gold' : h < 165 ? 'Green' : h < 195 ? 'Teal'
            : h < 255 ? 'Blue' : h < 290 ? 'Purple' : h < 340 ? 'Pink' : 'Red';
        const tone = l > 0.72 ? 'Light ' : l < 0.3 ? 'Deep ' : '';
        return (s < 0.3 && hue === 'Green' ? 'Sage ' : tone) + hue;
    }

    /**
     * Sample the main body finishes shown in a product photo.
     * Ignores the white studio background and vivid screen wallpapers; returns up to 4 colours.
     */
    function finishesFromImage(src) {
        return new Promise(resolve => {
            if (!src) return resolve([]);
            const img = new Image();
            img.onload = () => {
                try {
                    const S = 140;
                    const c = document.createElement('canvas');
                    const k = S / Math.max(img.width, img.height);
                    c.width = Math.max(1, Math.round(img.width * k));
                    c.height = Math.max(1, Math.round(img.height * k));
                    const g = c.getContext('2d', { willReadFrequently: true });
                    g.drawImage(img, 0, 0, c.width, c.height);
                    const data = g.getImageData(0, 0, c.width, c.height).data;
                    const px = [];
                    for (let i = 0; i < data.length; i += 4) {
                        const r = data[i], gg = data[i + 1], b = data[i + 2], a = data[i + 3];
                        if (a < 200) continue;
                        if (r > 232 && gg > 232 && b > 232) continue;        // studio background
                        const { s, l } = hsl(r, gg, b);
                        if (s > 0.62 && l > 0.25 && l < 0.85) continue;     // vivid wallpaper pixels
                        px.push([r, gg, b]);
                    }
                    if (px.length < 200) return resolve([]);
                    // k-means, k = 6
                    let cent = [];
                    for (let i = 0; i < 6; i++) cent.push(px[Math.floor((i + 0.5) * px.length / 6)].slice());
                    let assign = new Array(px.length).fill(0);
                    for (let it = 0; it < 8; it++) {
                        const sum = cent.map(() => [0, 0, 0, 0]);
                        px.forEach((p, i) => {
                            let bi = 0, bd = Infinity;
                            cent.forEach((q, j) => {
                                const d = (p[0] - q[0]) ** 2 + (p[1] - q[1]) ** 2 + (p[2] - q[2]) ** 2;
                                if (d < bd) { bd = d; bi = j; }
                            });
                            assign[i] = bi;
                            const s = sum[bi]; s[0] += p[0]; s[1] += p[1]; s[2] += p[2]; s[3]++;
                        });
                        cent = sum.map((s, j) => s[3] ? [s[0] / s[3], s[1] / s[3], s[2] / s[3], s[3]] : cent[j]);
                    }
                    const total = px.length;
                    let out = cent
                        .filter(c => (c[3] || 0) / total > 0.07)
                        .filter(c => !(luma(rgbToHex(c[0], c[1], c[2])) < 40 && (c[3] / total) < 0.18)) // bezels / shadows
                        .sort((a, b) => b[3] - a[3])
                        .map(c => rgbToHex(c[0], c[1], c[2]));
                    // merge near-duplicates
                    const merged = [];
                    out.forEach(h => {
                        const [r, g2, b] = hexToRgb(h);
                        if (!merged.some(m => { const [R, G, B] = hexToRgb(m); return Math.hypot(r - R, g2 - G, b - B) < 38; })) merged.push(h);
                    });
                    resolve(merged.slice(0, 4));
                } catch (e) { resolve([]); }
            };
            img.onerror = () => resolve([]);
            img.src = src;
        });
    }

    // ================================================================ geometry helpers
    function roundedRectShape(w, h, r) {
        const s = new THREE.Shape();
        const x = -w / 2, y = -h / 2;
        r = Math.min(r, w / 2, h / 2);
        s.moveTo(x + r, y);
        s.lineTo(x + w - r, y);
        s.absarc(x + w - r, y + r, r, -Math.PI / 2, 0, false);
        s.lineTo(x + w, y + h - r);
        s.absarc(x + w - r, y + h - r, r, 0, Math.PI / 2, false);
        s.lineTo(x + r, y + h);
        s.absarc(x + r, y + h - r, r, Math.PI / 2, Math.PI, false);
        s.lineTo(x, y + r);
        s.absarc(x + r, y + r, r, Math.PI, Math.PI * 1.5, false);
        return s;
    }
    function fitUVs(geo) {
        geo.computeBoundingBox();
        const bb = geo.boundingBox, pos = geo.attributes.position, uv = geo.attributes.uv;
        const w = bb.max.x - bb.min.x, h = bb.max.y - bb.min.y;
        for (let i = 0; i < pos.count; i++) uv.setXY(i, (pos.getX(i) - bb.min.x) / w, (pos.getY(i) - bb.min.y) / h);
        uv.needsUpdate = true;
        return geo;
    }
    function flatPanel(w, h, r) {
        return fitUVs(new THREE.ShapeGeometry(roundedRectShape(w, h, r), 48));
    }
    function slab(w, h, r, depth, bevel) {
        const g = new THREE.ExtrudeGeometry(roundedRectShape(w - bevel * 2, h - bevel * 2, Math.max(0.01, r - bevel)), {
            depth: Math.max(0.001, depth - bevel * 2), bevelEnabled: true, bevelThickness: bevel, bevelSize: bevel,
            bevelSegments: 10, curveSegments: 48
        });
        g.center();
        return g;
    }
    function tex(canvas, srgb = true) {
        const t = new THREE.CanvasTexture(canvas);
        if (srgb) t.encoding = THREE.sRGBEncoding;
        t.anisotropy = 8;
        return t;
    }
    function rr(g, x, y, w, h, r) {
        g.beginPath();
        g.moveTo(x + r, y);
        g.arcTo(x + w, y, x + w, y + h, r);
        g.arcTo(x + w, y + h, x, y + h, r);
        g.arcTo(x, y + h, x, y, r);
        g.arcTo(x, y, x + w, y, r);
        g.closePath();
    }

    // ================================================================ textures
    const FONT = '-apple-system, "SF Pro Display", "Segoe UI", Roboto, "Helvetica Neue", sans-serif';

    function drawWallpaper(g, W, H, body) {
        const dark = luma(body) > 150 ? shade(body, -150) : shade(body, -25);
        const bg = g.createLinearGradient(0, 0, W * 0.4, H);
        bg.addColorStop(0, shade(dark, 30));
        bg.addColorStop(1, shade(dark, -30));
        g.fillStyle = bg;
        g.fillRect(0, 0, W, H);
        // Flowing silk bands, like stock wallpapers
        const tones = [shade(body, 40), body, shade(body, -40), '#f9a8d4', shade(body, 70)];
        for (let i = 0; i < 5; i++) {
            const y0 = H * (0.38 + i * 0.11);
            const grad = g.createLinearGradient(0, y0 - H * 0.2, W, y0 + H * 0.25);
            grad.addColorStop(0, hexA(tones[i], 0));
            grad.addColorStop(0.5, hexA(tones[i], i === 3 ? 0.22 : 0.55));
            grad.addColorStop(1, hexA(tones[i], 0.05));
            g.fillStyle = grad;
            g.beginPath();
            g.moveTo(0, y0);
            g.bezierCurveTo(W * 0.3, y0 - H * (0.16 - i * 0.02), W * 0.65, y0 + H * 0.14, W, y0 - H * 0.06);
            g.lineTo(W, H);
            g.lineTo(0, H);
            g.closePath();
            g.fill();
        }
        // Fine grain keeps the gradients from banding
        const n = g.getImageData(0, 0, W, H);
        for (let i = 0; i < n.data.length; i += 4) {
            const v = (Math.random() - 0.5) * 10;
            n.data[i] += v; n.data[i + 1] += v; n.data[i + 2] += v;
        }
        g.putImageData(n, 0, 0);
    }

    function hexA(hex, a) {
        const [r, g, b] = hexToRgb(hex);
        return `rgba(${r},${g},${b},${a})`;
    }

    function lockScreen(canvas, cfg, body) {
        const g = canvas.getContext('2d');
        const W = canvas.width, H = canvas.height;
        drawWallpaper(g, W, H, body);

        const now = new Date();
        const hh = now.getHours() % 12 || 12;
        const mm = String(now.getMinutes()).padStart(2, '0');
        const date = now.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
        g.fillStyle = 'rgba(255,255,255,0.96)';
        g.textAlign = 'center';
        g.textBaseline = 'alphabetic';

        // Status bar
        g.font = `600 30px ${FONT}`;
        g.textAlign = 'left';
        g.fillText(`${hh}:${mm}`, 70, 78);
        const sx = W - 70;
        for (let i = 0; i < 4; i++) { rr(g, sx - 128 + i * 12, 70 - 8 - i * 5, 8, 10 + i * 5, 2); g.fill(); }  // signal
        g.lineWidth = 3; g.strokeStyle = 'rgba(255,255,255,0.96)';
        rr(g, sx - 62, 52, 52, 26, 7); g.stroke();                                                          // battery
        rr(g, sx - 57, 57, 36, 16, 3); g.fill();
        rr(g, sx - 8, 60, 4, 10, 2); g.fill();

        // Clock
        g.textAlign = 'center';
        g.font = `500 36px ${FONT}`;
        g.fillText(date, W / 2, H * 0.135);
        g.font = `600 230px ${FONT}`;
        g.fillText(`${hh}:${mm}`, W / 2, H * 0.135 + 225);

        // Notification-style card
        g.fillStyle = 'rgba(255,255,255,0.18)';
        rr(g, 60, H * 0.62, W - 120, 150, 40); g.fill();
        g.fillStyle = 'rgba(255,255,255,0.95)';
        rr(g, 96, H * 0.62 + 38, 74, 74, 18); g.fill();
        g.fillStyle = '#db2777';
        g.font = `800 34px ${FONT}`;
        g.fillText('8J', 133, H * 0.62 + 88);
        g.textAlign = 'left';
        g.fillStyle = 'rgba(255,255,255,0.96)';
        g.font = `700 30px ${FONT}`;
        g.fillText('8J Cellphone & Accessories', 196, H * 0.62 + 66);
        g.font = `500 28px ${FONT}`;
        g.fillStyle = 'rgba(255,255,255,0.8)';
        g.fillText(('Ready for pick-up · ' + (cfg.modelLabel || '')).slice(0, 38), 196, H * 0.62 + 108);

        // Bottom controls
        g.textAlign = 'center';
        if (cfg.isApple) {
            [[140, H - 175], [W - 140, H - 175]].forEach(([x, y]) => {
                g.fillStyle = 'rgba(0,0,0,0.28)';
                g.beginPath(); g.arc(x, y, 58, 0, Math.PI * 2); g.fill();
            });
            g.fillStyle = 'rgba(255,255,255,0.95)';
            rr(g, 128, H - 205, 24, 50, 8); g.fill();                    // torch
            rr(g, W - 168, H - 196, 56, 40, 10); g.fill();               // camera
            g.fillStyle = 'rgba(0,0,0,0.5)';
            g.beginPath(); g.arc(W - 140, H - 176, 11, 0, Math.PI * 2); g.fill();
        } else {
            // In-display fingerprint glyph
            g.strokeStyle = 'rgba(255,255,255,0.75)';
            g.lineWidth = 4;
            for (let i = 1; i <= 5; i++) {
                g.beginPath();
                g.arc(W / 2, H - 230, i * 11, Math.PI * (1.05 + i * 0.01), Math.PI * (1.95 - i * 0.01));
                g.stroke();
            }
        }
        g.fillStyle = 'rgba(255,255,255,0.9)';
        rr(g, W / 2 - 130, H - 44, 260, 11, 6); g.fill();

        // Front camera, drawn the way this model actually has it
        g.fillStyle = '#000';
        const front = cfg.front || (cfg.isApple ? 'island' : 'punch');
        if (front === 'island') {                                    // Dynamic Island
            rr(g, W / 2 - 118, 30, 236, 70, 35); g.fill();
        } else if (front === 'notch') {                              // iPhone 11-14 notch
            const nw = W * 0.36, nh = 92;
            g.beginPath();
            g.moveTo(W / 2 - nw / 2 - 26, 0);
            g.quadraticCurveTo(W / 2 - nw / 2, 0, W / 2 - nw / 2, 26);
            g.lineTo(W / 2 - nw / 2, nh - 34);
            g.quadraticCurveTo(W / 2 - nw / 2, nh, W / 2 - nw / 2 + 34, nh);
            g.lineTo(W / 2 + nw / 2 - 34, nh);
            g.quadraticCurveTo(W / 2 + nw / 2, nh, W / 2 + nw / 2, nh - 34);
            g.lineTo(W / 2 + nw / 2, 26);
            g.quadraticCurveTo(W / 2 + nw / 2, 0, W / 2 + nw / 2 + 26, 0);
            g.closePath(); g.fill();
        } else if (front === 'waterdrop') {                          // U-shaped teardrop notch
            g.beginPath();
            g.moveTo(W / 2 - 70, 0);
            g.bezierCurveTo(W / 2 - 40, 0, W / 2 - 40, 58, W / 2, 58);
            g.bezierCurveTo(W / 2 + 40, 58, W / 2 + 40, 0, W / 2 + 70, 0);
            g.closePath(); g.fill();
        } else if (front === 'pill') {                               // Honor-style pill cut-out
            rr(g, W / 2 - 62, 34, 124, 46, 23); g.fill();
        } else if (front === 'punch') {
            g.beginPath(); g.arc(W / 2, 64, 21, 0, Math.PI * 2); g.fill();
        }
        // 'bezel' (tablets): the camera sits in the bezel, not on the screen
    }

    function lensTexture() {
        const c = document.createElement('canvas');
        c.width = c.height = 256;
        const g = c.getContext('2d');
        const base = g.createRadialGradient(128, 128, 4, 128, 128, 128);
        base.addColorStop(0, '#05060a');
        base.addColorStop(0.45, '#0d1326');
        base.addColorStop(0.7, '#1b1530');
        base.addColorStop(1, '#030305');
        g.fillStyle = base;
        g.fillRect(0, 0, 256, 256);
        // Coating rings
        [[96, 'rgba(120,90,255,0.35)'], [70, 'rgba(60,200,180,0.25)'], [44, 'rgba(255,120,200,0.22)']].forEach(([r, col]) => {
            g.strokeStyle = col; g.lineWidth = 3;
            g.beginPath(); g.arc(128, 128, r, 0, Math.PI * 2); g.stroke();
        });
        g.fillStyle = 'rgba(255,255,255,0.55)';
        g.beginPath(); g.ellipse(92, 88, 18, 9, -0.6, 0, Math.PI * 2); g.fill();
        g.fillStyle = 'rgba(255,255,255,0.2)';
        g.beginPath(); g.arc(165, 170, 7, 0, Math.PI * 2); g.fill();
        return tex(c);
    }

    function noiseTexture() {
        const c = document.createElement('canvas');
        c.width = c.height = 256;
        const g = c.getContext('2d');
        const d = g.createImageData(256, 256);
        for (let i = 0; i < d.data.length; i += 4) {
            const v = 120 + Math.random() * 70;
            d.data[i] = d.data[i + 1] = d.data[i + 2] = v;
            d.data[i + 3] = 255;
        }
        g.putImageData(d, 0, 0);
        const t = tex(c, false);
        t.wrapS = t.wrapT = THREE.RepeatWrapping;
        t.repeat.set(6, 12);
        return t;
    }

    function textTexture(text, color, size = 120, w = 1024, h = 256, weight = 600, spacing = 14) {
        const c = document.createElement('canvas');
        c.width = w; c.height = h;
        const g = c.getContext('2d');
        g.fillStyle = color;
        g.textAlign = 'center';
        g.textBaseline = 'middle';
        g.font = `${weight} ${size}px ${FONT}`;
        if ('letterSpacing' in g) g.letterSpacing = spacing + 'px';
        g.fillText(text, w / 2, h / 2);
        return tex(c);
    }

    function contactShadow() {
        const c = document.createElement('canvas');
        c.width = c.height = 256;
        const g = c.getContext('2d');
        const rg = g.createRadialGradient(128, 128, 0, 128, 128, 128);
        rg.addColorStop(0, 'rgba(60,20,40,0.5)');
        rg.addColorStop(0.5, 'rgba(60,20,40,0.16)');
        rg.addColorStop(1, 'rgba(60,20,40,0)');
        g.fillStyle = rg;
        g.fillRect(0, 0, 256, 256);
        return new THREE.CanvasTexture(c);
    }

    function fallbackEnvironment(renderer) {
        const c = document.createElement('canvas');
        c.width = 1024; c.height = 512;
        const g = c.getContext('2d');
        const sky = g.createLinearGradient(0, 0, 0, 512);
        sky.addColorStop(0, '#5a5560'); sky.addColorStop(0.5, '#b8b2b8'); sky.addColorStop(1, '#3a3438');
        g.fillStyle = sky; g.fillRect(0, 0, 1024, 512);
        [[150, 140, 240, 170], [600, 100, 300, 130], [850, 280, 170, 200]].forEach(([x, y, w, h]) => {
            const rg = g.createRadialGradient(x, y, 0, x, y, Math.max(w, h));
            rg.addColorStop(0, 'rgba(255,255,255,1)'); rg.addColorStop(1, 'rgba(255,255,255,0)');
            g.fillStyle = rg; g.fillRect(0, 0, 1024, 512);
        });
        const t = new THREE.CanvasTexture(c);
        t.mapping = THREE.EquirectangularReflectionMapping;
        t.encoding = THREE.sRGBEncoding;
        const pm = new THREE.PMREMGenerator(renderer);
        const env = pm.fromEquirectangular(t).texture;
        t.dispose(); pm.dispose();
        return env;
    }

    // ================================================================ per-model data
    /*
     * One entry per handset model sold at 8J. Keys match the PHP slug in config/db.php
     * (local_3d_model_slug): brand + model, lowercase, words joined by "-".
     *
     *   dims   [height, width, thickness] in mm - official figures (GSMArena spec sheets)
     *   edge   'flat' | 'round' | 'curved' (curved = curved-edge display)
     *   front  'punch' | 'pill' | 'waterdrop' | 'notch' | 'island' | 'bezel' (tablets)
     *   r      corner radius in mm (optional)
     *   cam    rear camera, measured from the store's product photos, in mm from the
     *          BACK's top-left corner (as seen when looking at the back):
     *            m     module: ['rect', x, y, w, h, radius] | ['pill', x, y, w, h] |
     *                  ['circle', cx, cy, diameter] | ['contour', x, y, w, h, radius] | null
     *            mc    module colour override (optional)
     *            lens  [[cx, cy, diameter], ...]   flash [[cx, cy, d]]   dark [[cx, cy, d]] (LiDAR / sensors)
     *   colors official colour names [[name, hex], ...]
     *   verified false when the maker publishes no matching model; `note` says what the shape is based on
     */
    const A = 4; // Apple camera modules sit 4 mm in from the back's corner
    const appleDiag = m => ({ m: ['rect', A, A, m, m, m * 0.25], lens: [[A + m * .27, A + m * .27, m * .39], [A + m * .73, A + m * .73, m * .39]], flash: [[A + m * .76, A + m * .24, 4]], dark: [[A + m * .24, A + m * .76, 1.4]] });
    const appleVert = m => ({ m: ['rect', A, A, m, m, m * 0.25], lens: [[A + m * .27, A + m * .27, m * .37], [A + m * .27, A + m * .73, m * .37]], flash: [[A + m * .74, A + m * .27, 4]], dark: [[A + m * .74, A + m * .73, 1.4]] });
    const applePro = (m, d) => ({ m: ['rect', A, A, m, m, m * 0.24], lens: [[A + m * .27, A + m * .26, d], [A + m * .27, A + m * .74, d], [A + m * .74, A + m * .5, d]], flash: [[A + m * .76, A + m * .2, 4.5]], dark: [[A + m * .76, A + m * .8, 4.5]] });
    const BLK = '#1e1f22';
    const square3 = (s, d) => ({ m: ['rect', 5, 5, s, s, 7], lens: [[5 + s * .27, 5 + s * .27, d], [5 + s * .73, 5 + s * .27, d], [5 + s * .27, 5 + s * .73, d]], flash: [[5 + s * .73, 5 + s * .73, 4.5]] });
    const tabPill2 = { m: ['pill', 7, 7, 10, 22, 5], lens: [[12, 12.5, 7], [12, 23.5, 6]] };

    const MODEL_SPECS = {
        // ---------------- Apple
        'apple-iphone-11':        { dims: [150.9, 75.7, 8.3], edge: 'round', front: 'notch', r: 12, cam: appleVert(33), colors: [['Black', '#1f2020'], ['White', '#f5f4f0'], ['Green', '#aee1cd'], ['Yellow', '#ffe680'], ['Purple', '#d1cde3'], ['(PRODUCT)RED', '#ba0c2e']] },
        'apple-iphone-12':        { dims: [146.7, 71.5, 7.4], edge: 'flat', front: 'notch', r: 10.5, cam: appleVert(29), colors: [['Black', '#252525'], ['White', '#f6f5f1'], ['(PRODUCT)RED', '#ba0c2e'], ['Green', '#d8efd5'], ['Blue', '#023b63'], ['Purple', '#b7afe6']] },
        'apple-iphone-12-pro':    { dims: [146.7, 71.5, 7.4], edge: 'flat', front: 'notch', r: 10.5, metal: 'steel', cam: applePro(32, 12), colors: [['Graphite', '#52514f'], ['Silver', '#e2e4e1'], ['Gold', '#f3e3cc'], ['Pacific Blue', '#2e4b5f']] },
        'apple-iphone-13':        { dims: [146.7, 71.5, 7.65], edge: 'flat', front: 'notch', r: 10.5, cam: appleDiag(30), colors: [['Midnight', '#232a31'], ['Starlight', '#faf6f2'], ['Blue', '#276787'], ['Pink', '#faddd7'], ['(PRODUCT)RED', '#bf0013'], ['Green', '#394c38']] },
        'apple-iphone-13-pro':    { dims: [146.7, 71.5, 7.65], edge: 'flat', front: 'notch', r: 10.5, metal: 'steel', cam: applePro(37, 15), colors: [['Graphite', '#54524f'], ['Gold', '#fae7cf'], ['Silver', '#f1f2ed'], ['Sierra Blue', '#a7c1d9'], ['Alpine Green', '#576856']] },
        'apple-iphone-14':        { dims: [146.7, 71.5, 7.8], edge: 'flat', front: 'notch', r: 10.5, cam: appleDiag(31), colors: [['Midnight', '#222930'], ['Starlight', '#faf6f2'], ['Blue', '#a0b4c7'], ['Purple', '#e5ddea'], ['(PRODUCT)RED', '#a50011'], ['Yellow', '#f9e479']] },
        'apple-iphone-14-pro':    { dims: [147.5, 71.5, 7.85], edge: 'flat', front: 'island', r: 10.5, metal: 'steel', cam: applePro(38, 15.5), colors: [['Space Black', '#403e3d'], ['Silver', '#f0f2f2'], ['Gold', '#f4e8ce'], ['Deep Purple', '#594f63']] },
        'apple-iphone-15':        { dims: [147.6, 71.6, 7.8], edge: 'flat', front: 'island', r: 11, cam: appleDiag(31), colors: [['Black', '#35393b'], ['Blue', '#d4e4ed'], ['Green', '#e3e8d9'], ['Yellow', '#f9f1c7'], ['Pink', '#f9e0e5']] },
        'apple-iphone-15-pro':    { dims: [146.6, 70.6, 8.25], edge: 'flat', front: 'island', r: 11, metal: 'titanium', cam: applePro(37, 15), colors: [['Natural Titanium', '#bab4a9'], ['Blue Titanium', '#2f4452'], ['White Titanium', '#f2f1ed'], ['Black Titanium', '#3b3b3b']] },
        'apple-iphone-15-pro-max':{ dims: [159.9, 76.7, 8.25], edge: 'flat', front: 'island', r: 11.5, metal: 'titanium', cam: applePro(40, 16), colors: [['Natural Titanium', '#bab4a9'], ['Blue Titanium', '#2f4452'], ['White Titanium', '#f2f1ed'], ['Black Titanium', '#3b3b3b']] },

        // ---------------- Honor
        'honor-200-5g':     { dims: [161.5, 74.6, 7.7], edge: 'round', front: 'punch', cam: { m: ['pill', 6, 6, 26, 44], mc: '#141416', lens: [[19, 17, 15], [19, 33, 15]], flash: [[19, 45.5, 3.5]] }, colors: [['Black', BLK], ['Moonlight White', '#ecebe6'], ['Emerald Green', '#2f6b5c'], ['Coral Pink', '#f0c6cf']] },
        'honor-pad-x8b':    { dims: [256.9, 168.5, 7.3], edge: 'flat', front: 'bezel', cam: { m: ['pill', 7, 7, 9, 18], lens: [[11.5, 12, 6.5]], flash: [[11.5, 20.5, 3]] }, colors: [['Space Gray', '#6d6f73']] },
        'honor-pad-x9b-max':{ dims: [267.3, 167.4, 6.9], edge: 'flat', front: 'bezel', cam: tabPill2, colors: [['Space Gray', '#5f6166']], verified: false, note: 'Honor lists no "Pad X9b Max"; sized like the Honor Pad X9' },
        'honor-x5b-plus':   { dims: [163.9, 75.8, 8.7], edge: 'round', front: 'waterdrop', cam: { m: ['rect', 5, 5, 25, 25, 7], lens: [[12, 12, 9], [12, 23, 7]], flash: [[23, 12, 4]], dark: [[23, 23, 5]] }, colors: [['Ocean Blue', '#8fc1d8'], ['Starry Purple', '#b9a9d3'], ['Midnight Black', BLK]] },
        'honor-x6b':        { dims: [163.6, 75.3, 8.4], edge: 'round', front: 'waterdrop', cam: square3(27, 9), colors: [['Forest Green', '#6f907e'], ['Starry Purple', '#b9a9d3'], ['Ocean Cyan', '#8ec7c6'], ['Midnight Black', BLK]] },
        'honor-x7b':        { dims: [166.7, 76.5, 8.2], edge: 'round', front: 'punch', cam: { m: ['pill', 6, 6, 19, 36], lens: [[15.5, 15.5, 13], [15.5, 32, 13]], flash: [[29, 10, 4]] }, colors: [['Flowing Silver', '#d6d8dc'], ['Emerald Green', '#3f6e65'], ['Midnight Black', BLK]] },
        'honor-x8b':        { dims: [161.1, 74.6, 6.8], edge: 'flat', front: 'pill', cam: square3(27, 9), colors: [['Glamorous Green', '#a9d6c2'], ['Titanium Silver', '#d8dadd'], ['Midnight Black', BLK], ['Blue', '#7fa9d1']] },
        'honor-x9b-5g':     { dims: [163.6, 75.5, 8.0], edge: 'curved', front: 'punch', cam: { m: ['circle', 22, 22, 34], lens: [[16, 22, 12], [28, 15, 7], [28, 29, 7]], flash: [[22, 33, 3.5]] }, colors: [['Sunrise Orange', '#e2793d'], ['Midnight Black', BLK], ['Emerald Green', '#3c6c60']] },
        'honor-x9c-5g':     { dims: [162.8, 75.5, 8.0], edge: 'curved', front: 'pill', cam: { m: ['circle', 22, 22, 34], lens: [[22, 15, 13], [15, 28, 7], [29, 28, 7]], flash: [[22, 31.5, 3]] }, colors: [['Titanium Black', '#26272b'], ['Jade Cyan', '#8cc4b9'], ['Titanium Purple', '#b6a5c9']] },

        // ---------------- Infinix
        'infinix-gt-10-pro':      { dims: [162.7, 75.9, 8.1], edge: 'flat', front: 'punch', cam: Object.assign(square3(28, 10), { mc: '#1d1e22' }), colors: [['Cyber Black', '#232427'], ['Mirage Silver', '#d9dadc']] },
        'infinix-gt-20-pro':      { dims: [164.3, 75.4, 8.2], edge: 'flat', front: 'punch', cam: square3(29, 10.5), colors: [['Mecha Blue', '#2f4a7a'], ['Mecha Orange', '#e2733a'], ['Mecha Silver', '#c8cbd0']] },
        'infinix-gt-20':          { dims: [164.3, 75.4, 8.2], edge: 'flat', front: 'punch', cam: square3(29, 10.5), colors: [['Mecha Blue', '#2f4a7a'], ['Mecha Orange', '#e2733a'], ['Mecha Silver', '#c8cbd0']], verified: false, note: 'Infinix lists no non-Pro GT 20; shape follows the GT 20 Pro' },
        'infinix-hot-40-pro':     { dims: [168.6, 76.6, 8.3], edge: 'flat', front: 'punch', cam: square3(30, 12), colors: [['Palm Blue', '#4a7fa8'], ['Horizon Gold', '#e5d09b'], ['Starlit Black', '#1f2023'], ['Starfall Green', '#7fae9a']] },
        'infinix-hot-40-pro-plus':{ dims: [168.6, 76.6, 8.3], edge: 'flat', front: 'punch', cam: square3(30, 12), colors: [['Palm Blue', '#4a7fa8'], ['Horizon Gold', '#e5d09b'], ['Starlit Black', '#1f2023'], ['Starfall Green', '#7fae9a']], verified: false, note: 'Infinix lists no Hot 40 Pro+; shape follows the Hot 40 Pro' },
        'infinix-hot-50':         { dims: [165.7, 77.1, 7.8], edge: 'flat', front: 'punch', cam: { m: ['pill', 6, 6, 11, 37], lens: [[11.5, 12, 8], [11.5, 23.5, 8], [11.5, 35, 8]], flash: [[20, 11, 3.5]] }, colors: [['Sleek Black', '#1d1e21'], ['Sage Green', '#a9bcae'], ['Titanium Grey', '#b9bbbe'], ['Dreamy Purple', '#b9a7d3'], ['Vibrant Blue', '#4f7fc0']] },
        'infinix-note-40-pro':    { dims: [164.4, 74.6, 7.8], edge: 'curved', front: 'punch', cam: { m: ['rect', 5, 5, 32, 30, 6], lens: [[13, 13, 12], [26, 13, 12], [13, 26, 12]], flash: [[26, 26, 8]] }, colors: [['Vintage Green', '#3d5a48'], ['Titan Gold', '#d9c08a'], ['Racing Edition', '#e9e9eb']] },
        'infinix-note-edge-5g':   { dims: [163.1, 77.4, 7.2], edge: 'curved', front: 'punch', cam: { m: ['rect', 5, 5, 36, 30, 9], lens: [[13, 13, 11], [13, 25.5, 11]], flash: [[24, 12, 3.5]] }, colors: [['Lunar Titanium', '#b6b4af'], ['Silk Green', '#3f6050'], ['Stellar Blue', '#3b5a8a'], ['Shadow Black', '#1d1e21']] },
        'infinix-smart-20':       { dims: [167.7, 78.8, 7.7], edge: 'flat', front: 'punch', cam: { m: ['rect', 5, 5, 21, 29, 7], lens: [[15.5, 12.5, 10], [15.5, 25, 10]] }, colors: [['Shadow Black', '#1d1e21'], ['Cloudline Blue', '#a3bad2'], ['Polaris Titanium', '#c8c6c2'], ['Sunlike Orange', '#e98a4b']] },
        'infinix-xpad-30-pro':    { dims: [254, 165.6, 6.6], edge: 'flat', front: 'bezel', cam: tabPill2, colors: [['Anthracite Grey', '#4a4c50'], ['Titanium Silver', '#d5d7da'], ['Twilight Purple', '#a79cc5']] },
        'infinix-xpad-30e':       { dims: [257, 168.6, 7.6], edge: 'flat', front: 'bezel', cam: tabPill2, colors: [['Dreamy Purple', '#b9a7d6'], ['Forest Green', '#6f8f7d'], ['Deep Blue', '#2f4b74']], verified: false, note: 'Infinix publishes no Xpad 30E dimensions; sized like the original Xpad' },

        // ---------------- itel
        'itel-a58e':          { dims: [163.9, 75.7, 8.7], edge: 'round', front: 'waterdrop', cam: { m: ['rect', 5, 5, 24, 24, 7], lens: [[12, 12, 9], [12, 22.5, 9]], flash: [[22, 12, 6]] }, colors: [['Mint Green', '#bfe5d5']], verified: false, note: 'itel lists no A58e; sized like the itel A50' },
        'itel-a70':           { dims: [164, 75.5, 8.6], edge: 'round', front: 'waterdrop', cam: { m: ['rect', 5, 5, 22, 29, 6], lens: [[11, 11.5, 9], [11, 23, 9], [20.5, 17, 6]], flash: [[20.5, 9, 3]] }, colors: [['Brilliant Gold', '#e6cf8e'], ['Stylish Black', BLK], ['Field Green', '#8db59a'], ['Azure Blue', '#8fb6d8']] },
        'itel-a70-plus':      { dims: [164, 75.5, 8.6], edge: 'round', front: 'waterdrop', cam: { m: ['rect', 5, 5, 22, 29, 6], lens: [[11, 11.5, 9], [11, 23, 9], [20.5, 17, 6]], flash: [[20.5, 9, 3]] }, colors: [['Brilliant Gold', '#e6cf8e'], ['Stylish Black', BLK], ['Field Green', '#8db59a'], ['Azure Blue', '#8fb6d8']], verified: false, note: 'itel lists no A70+; shape follows the A70' },
        'itel-vista-tab-11':  { dims: [257, 168, 8.0], edge: 'flat', front: 'bezel', cam: tabPill2, colors: [['Space Gray', '#5d6066']], verified: false, note: 'itel lists no "Vista Tab 11"; sized like the VistaTab 30' },
        'itel-vista-tab-12':  { dims: [257, 168, 8.0], edge: 'flat', front: 'bezel', cam: tabPill2, colors: [['Space Gray', '#5d6066']], verified: false, note: 'itel lists no "Vista Tab 12"; sized like the VistaTab 30' },
        'itel-vista-tab-30s': { dims: [257, 168, 7.0], edge: 'flat', front: 'bezel', cam: tabPill2, colors: [['Space Gray', '#5d6066']], verified: false, note: 'itel publishes only the thickness of the VistaTab 30S (7 mm); other sizes estimated' },

        // ---------------- OPPO
        'oppo-a06-5g': { dims: [165.7, 76.2, 8.0], edge: 'round', front: 'punch', cam: { m: ['rect', 5, 5, 24, 26, 7], lens: [[12.5, 12.5, 9], [12.5, 23.5, 9]], flash: [[22, 12.5, 4]] }, colors: [['Lavender Purple', '#c3b4e2']], verified: false, note: 'OPPO lists no A06 5G; sized like the OPPO A5x' },
        'oppo-a07':    { dims: [165.7, 76.2, 8.0], edge: 'round', front: 'punch', cam: { m: ['pill', 7, 7, 14, 30], lens: [[14, 14, 10], [14, 28, 10]] }, colors: [['Light Blue', '#bcd7ea']], verified: false, note: 'OPPO lists no A07; sized like the OPPO A5x' },
        'oppo-a17':    { dims: [164.2, 75.6, 8.3], edge: 'round', front: 'waterdrop', cam: { m: ['pill', 6, 6, 10, 38], mc: 'same', lens: [[11, 11, 8], [11, 21, 8]], flash: [[11, 30, 3]] }, colors: [['Midnight Black', BLK], ['Lake Blue', '#8fb3d4'], ['Sunlight Orange', '#e9a66a']] },
        'oppo-a17-5g': { dims: [164.2, 75.6, 8.3], edge: 'round', front: 'waterdrop', cam: { m: ['pill', 6, 6, 10, 38], mc: 'same', lens: [[11, 11, 8], [11, 21, 8]], flash: [[11, 30, 3]] }, colors: [['Midnight Black', BLK], ['Lake Blue', '#8fb3d4'], ['Sunlight Orange', '#e9a66a']], verified: false, note: 'OPPO sells the A17 as 4G only; shape follows the A17' },
        'oppo-a18-5g': { dims: [163.7, 75, 8.2], edge: 'round', front: 'waterdrop', cam: { m: ['pill', 6, 6, 14, 52], lens: [[13, 13, 10], [13, 26, 10]], flash: [[13, 36, 3]] }, colors: [['Glowing Black', BLK], ['Glowing Blue', '#a9cbe4']], verified: false, note: 'OPPO sells the A18 as 4G only; shape follows the A18' },
        'oppo-a38-5g': { dims: [163.7, 75, 8.2], edge: 'round', front: 'waterdrop', cam: { m: ['pill', 6, 6, 14, 52], lens: [[13, 13, 10], [13, 26, 10]], flash: [[13, 36, 3]] }, colors: [['Glowing Black', BLK], ['Glowing Gold', '#e3cf9e']], verified: false, note: 'OPPO sells the A38 as 4G only; shape follows the A38' },
        'oppo-a57-5g': { dims: [163.8, 75.1, 8.0], edge: 'round', front: 'waterdrop', cam: { m: ['rect', 5, 5, 17, 26, 5], lens: [[13.5, 12, 8], [13.5, 22, 8]], flash: [[21, 11, 3]] }, colors: [['Black', BLK], ['Blue', '#8fb0d4'], ['Lilac', '#c9bde0']] },

        // ---------------- realme
        'realme-13-5g':   { dims: [165.6, 76.1, 7.8], edge: 'flat', front: 'punch', cam: { m: ['circle', 21, 21, 32], lens: [[15, 21, 11], [26.5, 14.5, 6]], flash: [[26.5, 27.5, 5]] }, colors: [['Speed Green', '#86c2a0'], ['Dark Purple', '#3e2c52']] },
        'realme-c65':     { dims: [164.6, 76.1, 7.6], edge: 'flat', front: 'punch', cam: { m: ['pill', 7, 7, 11, 35], lens: [[12.5, 12.5, 8.5], [12.5, 24, 8.5]], flash: [[12.5, 35, 6]] }, colors: [['Purple Nebula', '#6b5b8e'], ['Black Milky Way', '#1f2328']] },
        'realme-note-50': { dims: [167.2, 76.7, 8.0], edge: 'flat', front: 'waterdrop', cam: { m: ['rect', 5, 5, 25, 25, 7], lens: [[12.5, 12.5, 10], [12.5, 23, 10]], flash: [[23, 16, 5]] }, colors: [['Sky Blue', '#a7cde8'], ['Midnight Black', BLK]] },
        'realme-note-60': { dims: [167.3, 76.7, 7.8], edge: 'flat', front: 'waterdrop', cam: { m: ['rect', 5, 5, 21, 31, 7], lens: [[12.5, 13, 10], [12.5, 25.5, 10]], flash: [[21, 9, 3]] }, colors: [['Voyage Blue', '#8fb6db'], ['Marble Black', '#222326']] },

        // ---------------- Samsung / Xiaomi / others
        'samsung-galaxy-s22-5g': { dims: [146.0, 70.6, 7.6], edge: 'flat', front: 'punch', r: 9, cam: { m: ['contour', 3, 3, 15, 41, 7.5], lens: [[10.5, 11, 9], [10.5, 22.5, 9], [10.5, 34, 9]], flash: [[20, 10, 3]] }, colors: [['Phantom Black', '#1f2022'], ['Phantom White', '#ecebe8'], ['Green', '#586f63'], ['Pink Gold', '#ecd2c4']] },
        'xiaomi-redmi-note-12':  { dims: [165.7, 76.0, 7.9], edge: 'flat', front: 'punch', cam: { m: ['rect', 5, 5, 24, 29, 6], lens: [[11.5, 12.5, 10], [11.5, 25, 10], [21.5, 12, 5]], flash: [[21.5, 21, 4]] }, colors: [['Onyx Gray', '#3a3c40'], ['Mint Green', '#b9dccb'], ['Ice Blue', '#8cb1d8']] },
        'techlife-pad':          { dims: [241, 159, 8.5], edge: 'flat', front: 'bezel', cam: { m: null, lens: [[10, 10, 6]] }, colors: [['Gold', '#d9c3a0']], verified: false, note: 'TechLife publishes no specifications; generic 10-inch tablet size' },
    };

    /**
     * Colour choices for the viewer:
     *  - explicit cfg.finishes (homepage showcase)
     *  - a physical unit: only its own recorded colour, named the way the maker names it
     *  - a catalogue product: the model's official colours
     *  - otherwise the colour named on the record (photo sampling happens later if there is none)
     */
    function finishesFor(cfg, spec) {
        if (Array.isArray(cfg.finishes) && cfg.finishes.length) return cfg.finishes.slice();
        const official = spec ? spec.colors.map(([name, hex]) => ({ name, hex })) : [];
        const recorded = String(cfg.color || '').trim();
        if (cfg.type === 'unit' || (recorded && official.length === 0)) {
            if (!recorded) return official.slice(0, 1);
            const words = recorded.toLowerCase().split(/\s+/);
            const match = official.find(o => o.name.toLowerCase() === recorded.toLowerCase())
                || official.find(o => words.some(w => w.length > 3 && o.name.toLowerCase().includes(w)));
            if (match) return [{ name: recorded, hex: match.hex }];
            const hex = colorFromName(recorded);
            return hex ? [{ name: recorded, hex }] : official.slice(0, 1);
        }
        return official;
    }

    // Same rule as local_3d_model_slug() in config/db.php
    function modelSlug(brand, name) {
        name = String(name || '').replace(/\s*\([^)]*\)/g, '');
        brand = String(brand || '').trim();
        if (brand && name.toLowerCase().indexOf(brand.toLowerCase()) !== 0) name = brand + ' ' + name;
        return name.trim().toLowerCase().replace(/\+/g, ' plus').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
    }
    function specFor(cfg) {
        return MODEL_SPECS[modelSlug(cfg.brand, cfg.name)] || MODEL_SPECS[modelSlug(cfg.brand, cfg.model)] || null;
    }

    // ================================================================ internals (exploded view)
    /*
     * What's inside each iPhone: chip, battery capacity (from Apple's regulatory filings /
     * teardowns - Apple does not print mAh in its own specs) and whether it has MagSafe.
     */
    const APPLE_INSIDE = {
        'apple-iphone-11':         { chip: 'A13 Bionic', mah: 3110, magsafe: false },
        'apple-iphone-12':         { chip: 'A14 Bionic', mah: 2815, magsafe: true },
        'apple-iphone-12-pro':     { chip: 'A14 Bionic', mah: 2815, magsafe: true },
        'apple-iphone-13':         { chip: 'A15 Bionic', mah: 3240, magsafe: true },
        'apple-iphone-13-pro':     { chip: 'A15 Bionic', mah: 3095, magsafe: true },
        'apple-iphone-14':         { chip: 'A15 Bionic', mah: 3279, magsafe: true },
        'apple-iphone-14-pro':     { chip: 'A16 Bionic', mah: 3200, magsafe: true },
        'apple-iphone-15':         { chip: 'A16 Bionic', mah: 3349, magsafe: true },
        'apple-iphone-15-pro':     { chip: 'A17 Pro',    mah: 3274, magsafe: true },
        'apple-iphone-15-pro-max': { chip: 'A17 Pro',    mah: 4441, magsafe: true },
    };

    function labelTexture(lines, w = 1024, h = 512, bg = null) {
        const c = document.createElement('canvas');
        c.width = w; c.height = h;
        const g = c.getContext('2d');
        if (bg) { g.fillStyle = bg; g.fillRect(0, 0, w, h); }
        let y = 0;
        lines.forEach(([text, size, color, weight]) => {
            y += size * 1.35;
            g.fillStyle = color;
            g.font = `${weight || 600} ${size}px ${FONT}`;
            g.fillText(text, 40, y);
        });
        return tex(c);
    }

    function grilleTexture() {
        const c = document.createElement('canvas');
        c.width = 512; c.height = 128;
        const g = c.getContext('2d');
        g.fillStyle = '#16161a'; g.fillRect(0, 0, 512, 128);
        g.fillStyle = '#050506';
        for (let x = 12; x < 512; x += 16) for (let y = 14; y < 128; y += 16) { g.beginPath(); g.arc(x, y, 4, 0, Math.PI * 2); g.fill(); }
        return tex(c);
    }

    function buildInternals({ parts, M, W, H, D, MM, spec, cfg, isApple, isTablet, metal }) {
        const inner = parts.inner;
        const info = APPLE_INSIDE[modelSlug(cfg.brand, cfg.name)] || APPLE_INSIDE[modelSlug(cfg.brand, cfg.model)] || null;
        const box = (w, h, d, mat, x, y, z) => {
            const m = new THREE.Mesh(new THREE.BoxGeometry(w, h, d), mat);
            m.position.set(x, y, z);
            inner.add(m);
            return m;
        };
        const plate = (w, h, map, x, y, z, group = inner) => {
            const m = new THREE.Mesh(new THREE.PlaneGeometry(w, h), new THREE.MeshBasicMaterial({ map, transparent: true }));
            m.position.set(x, y, z);
            group.add(m);
            return m;
        };
        const shield = new THREE.MeshStandardMaterial({ color: '#b9bcc2', metalness: 1, roughness: 0.32 });
        const copper = new THREE.MeshStandardMaterial({ color: '#c46a2c', metalness: 0.85, roughness: 0.35 });
        const flexMat = new THREE.MeshStandardMaterial({ color: '#b5651d', metalness: 0.3, roughness: 0.5 });
        const pcb = new THREE.MeshStandardMaterial({ color: '#0f1a24', metalness: 0.3, roughness: 0.5 });
        const steel = new THREE.MeshStandardMaterial({ color: '#8d9096', metalness: 1, roughness: 0.4 });
        const labels = [];

        // Battery: big cell filling the middle, labelled like the real one
        const bw = W * 0.8, bh = H * 0.5, bx = -W * 0.03, by = -H * 0.115;
        const battery = new THREE.Mesh(slab(bw, bh, 0.25, 0.4, 0.05), new THREE.MeshStandardMaterial({ color: '#202024', metalness: 0.35, roughness: 0.55 }));
        battery.position.set(bx, by, 0);
        inner.add(battery);
        const mah = info ? info.mah : (isTablet ? 7000 : 5000);
        plate(bw * 0.86, bw * 0.43, labelTexture([
            [isApple ? 'Apple' : 'Li-ion Polymer', 70, '#e4e4e7', 700],
            [mah.toLocaleString('en-US') + ' mAh', 92, '#ffffff', 800],
            ['Lithium-ion polymer battery', 38, '#a1a1aa', 500],
        ]), bx, by + bh * 0.12, 0.205);
        // pull tabs at the bottom edge
        [-0.28, 0.28].forEach(f => box(bw * 0.12, 0.35, 0.02, new THREE.MeshStandardMaterial({ color: '#111', roughness: 0.8 }), bx + bw * f, by - bh / 2 - 0.12, 0.2));

        // Logic board (stacked "sandwich" in the top corner beside the cameras)
        const lw = W * 0.5, lh = H * 0.27, lx = -W * 0.2, ly = H * 0.31;
        const board = new THREE.Mesh(slab(lw, lh, 0.28, 0.14, 0.03), pcb);
        board.position.set(lx, ly, 0.02);
        inner.add(board);
        const upper = new THREE.Mesh(slab(lw * 0.92, lh * 0.88, 0.25, 0.1, 0.02), pcb);
        upper.position.set(lx, ly, 0.16);
        inner.add(upper);
        // shield cans
        box(lw * 0.42, lh * 0.38, 0.08, shield, lx + lw * 0.22, ly + lh * 0.22, 0.25);
        box(lw * 0.38, lh * 0.3, 0.08, shield, lx - lw * 0.22, ly - lh * 0.24, 0.25);
        box(lw * 0.3, lh * 0.22, 0.06, shield, lx + lw * 0.26, ly - lh * 0.28, 0.24);
        // the SoC, with its name
        const chipW = lw * 0.36;
        box(chipW, chipW, 0.07, new THREE.MeshStandardMaterial({ color: '#0b0b0d', metalness: 0.5, roughness: 0.25 }), lx - lw * 0.2, ly + lh * 0.16, 0.25);
        plate(chipW * 0.92, chipW * 0.92, labelTexture([
            ['', 150, '#fff'],
            [info ? info.chip : 'SoC', 120, '#e4e4e7', 700],
        ], 1024, 1024), lx - lw * 0.2, ly + lh * 0.16, 0.29);
        // board-to-board connectors + tiny passives
        [[0.42, -0.05], [-0.42, 0.05], [0.05, -0.46]].forEach(([fx, fy]) => box(lw * 0.14, lh * 0.08, 0.05, M.gold, lx + lw * fx, ly + lh * fy, 0.23));
        for (let i = 0; i < 14; i++) {
            box(0.07, 0.04, 0.04, i % 3 ? M.chip : M.gold, lx - lw * 0.45 + (i % 7) * lw * 0.13, ly + lh * (0.42 - Math.floor(i / 7) * 0.84), 0.22);
        }

        // Rear cameras behind the board, lined up with the lenses on the back
        const camSpec = spec && spec.cam ? spec.cam.lens : [];
        const camGroup = new THREE.Group();
        inner.add(camGroup);
        camSpec.forEach(([cx, cy, d]) => {
            const x = W / 2 - cx * MM, y = H / 2 - cy * MM, r = d / 2 * MM;
            const housing = new THREE.Mesh(new THREE.BoxGeometry(r * 2.3, r * 2.3, 0.5), steel);
            housing.position.set(x, y, -0.05);
            const barrel = new THREE.Mesh(new THREE.CylinderGeometry(r * 0.85, r * 0.85, 0.45, 48), new THREE.MeshStandardMaterial({ color: '#1b1b1f', metalness: 0.7, roughness: 0.3 }));
            barrel.rotation.x = Math.PI / 2; barrel.position.set(x, y, -0.45);
            const glass = new THREE.Mesh(new THREE.CircleGeometry(r * 0.6, 48), M.lens);
            glass.rotation.y = Math.PI; glass.position.set(x, y, -0.68);
            camGroup.add(housing, barrel, glass);
        });

        // TrueDepth / front camera module at the top centre
        box(W * 0.3, 0.34, 0.28, new THREE.MeshStandardMaterial({ color: '#141417', metalness: 0.6, roughness: 0.35 }), W * 0.05, H * 0.465, 0.05);
        [-0.08, 0.02, 0.12].forEach(f => {
            const dot = new THREE.Mesh(new THREE.CircleGeometry(0.07, 24), M.lens);
            dot.position.set(W * (0.05 + f), H * 0.465, 0.195);
            inner.add(dot);
        });

        // Taptic Engine (bottom left as seen from the front) and loudspeaker (bottom right)
        const ty = -H * 0.43;
        const taptic = box(W * 0.3, H * 0.055, 0.32, steel, -W * 0.25, ty, 0.02);
        box(W * 0.26, H * 0.012, 0.02, new THREE.MeshStandardMaterial({ color: '#5b5e63', metalness: 1, roughness: 0.5 }), -W * 0.25, ty, 0.19);
        const speaker = box(W * 0.3, H * 0.055, 0.32, new THREE.MeshStandardMaterial({ color: '#16161a', roughness: 0.6 }), W * 0.25, ty, 0.02);
        plate(W * 0.28, H * 0.045, grilleTexture(), W * 0.25, ty, 0.185);
        // USB-C port assembly between them
        box(W * 0.16, H * 0.03, 0.22, steel, 0, -H * 0.462, 0.02);

        // Flex cables linking board, battery and the bottom assembly
        box(W * 0.07, H * 0.06, 0.015, flexMat, -W * 0.02, H * 0.155, 0.22);
        box(W * 0.06, H * 0.36, 0.015, flexMat, W * 0.4, -H * 0.11, 0.06);
        box(W * 0.08, H * 0.05, 0.015, flexMat, -W * 0.05, -H * 0.385, 0.2);

        // Inside face of the back glass: wireless charging coil (+ MagSafe magnet ring)
        const back = parts.back;
        const coilZ = -D / 2 + 0.02;
        const coil = new THREE.Mesh(new THREE.RingGeometry(9 * MM, 19 * MM, 64), copper);
        coil.position.set(0, 0, coilZ);
        back.add(coil);
        for (let i = 0; i < 9; i++) {           // coil windings
            const t = new THREE.Mesh(new THREE.TorusGeometry((10 + i * 1.1) * MM, 0.008, 6, 64), new THREE.MeshStandardMaterial({ color: '#8a4515', metalness: 0.8, roughness: 0.4 }));
            t.position.set(0, 0, coilZ + 0.002);
            back.add(t);
        }
        if (!info || info.magsafe) {
            const magMat = new THREE.MeshStandardMaterial({ color: '#2a2b2f', metalness: 0.7, roughness: 0.35 });
            for (let i = 0; i < 18; i++) {         // segmented magnet ring
                const a0 = (i / 18) * Math.PI * 2 + 0.03;
                const seg = new THREE.Mesh(new THREE.RingGeometry(21.5 * MM, 25.5 * MM, 8, 1, a0, Math.PI * 2 / 18 - 0.06), magMat);
                seg.position.set(0, 0, coilZ + 0.003);
                back.add(seg);
            }
            const align = new THREE.Mesh(new THREE.PlaneGeometry(4 * MM, 12 * MM), magMat); // orientation magnet
            align.position.set(0, -33 * MM, coilZ + 0.003);
            back.add(align);
        }
        // camera cut-out seen from inside
        if (spec && spec.cam && spec.cam.m && spec.cam.m[0] === 'rect') {
            const [, mx, my, mw, mh, mr] = spec.cam.m;
            const cut = new THREE.Mesh(flatPanel(mw * MM, mh * MM, (mr || 6) * MM), M.black);
            cut.position.set(W / 2 - (mx + mw / 2) * MM, H / 2 - (my + mh / 2) * MM, coilZ);
            back.add(cut);
        }

        // Inside face of the display: black panel back with its flex tail
        const panelBack = new THREE.Mesh(flatPanel(W * 0.92, H * 0.95, 0.6), new THREE.MeshStandardMaterial({ color: '#2b2c30', metalness: 0.6, roughness: 0.45, side: THREE.DoubleSide }));
        panelBack.position.set(0, 0, D / 2 - 0.02);
        parts.front.add(panelBack);
        const tail = new THREE.Mesh(new THREE.BoxGeometry(W * 0.12, H * 0.08, 0.01), flexMat);
        tail.position.set(W * 0.3, H * 0.25, D / 2 - 0.04);
        parts.front.add(tail);

        // Labels: [group, local position, text]
        const camCount = camSpec.length;
        const frameName = metal === 'titanium' ? 'Titanium frame' : metal === 'steel' ? 'Stainless steel frame' : metal === 'aluminium' ? 'Aluminium frame' : 'Frame';
        labels.push([parts.front, new THREE.Vector3(-W * 0.12, H * 0.05, 0.05), isApple ? 'Super Retina XDR display' : 'Display']);
        labels.push([inner, new THREE.Vector3(lx - lw * 0.2, ly + lh * 0.16, 0.3), (info ? info.chip : 'Main') + ' logic board']);
        if (camCount) labels.push([inner, new THREE.Vector3(W / 2 - camSpec[0][0] * MM, H / 2 - camSpec[0][1] * MM, -0.6), (camCount === 3 ? 'Triple' : camCount === 2 ? 'Dual' : '') + ' camera system']);
        labels.push([inner, new THREE.Vector3(bx, by - bh * 0.1, 0.25), 'Battery · ' + mah.toLocaleString('en-US') + ' mAh']);
        labels.push([inner, new THREE.Vector3(-W * 0.25, ty, 0.2), 'Taptic Engine']);
        labels.push([inner, new THREE.Vector3(W * 0.25, ty, 0.2), 'Loudspeaker']);
        labels.push([parts.back, new THREE.Vector3(0, 0, coilZ), (!info || info.magsafe) ? 'MagSafe coil & magnets' : 'Wireless charging coil']);
        labels.push([parts.frame, new THREE.Vector3(W / 2, -H * 0.15, 0), frameName]);
        labels.push([parts.back, new THREE.Vector3(-W * 0.25, -H * 0.32, -D / 2), 'Back glass']);
        return { labels, taptic, speaker };
    }

    // ================================================================ phone builder
    function buildPhone(cfg) {
        const brand = String(cfg.brand || '').toLowerCase();
        const name = String(cfg.name || '').toLowerCase();
        const spec = specFor(cfg);
        const isApple = brand === 'apple' || /iphone|ipad/.test(name);
        const isPro = /\bpro\b|ultra|max|plus|\+/.test(name);
        const isTablet = spec ? spec.front === 'bezel' : /\bpad\b|xpad|\btab\b|tablet|ipad/.test(name);
        const metal = spec ? (spec.metal || (isApple ? 'aluminium' : 'painted')) : (isApple && isPro ? 'titanium' : 'aluminium');

        // Real proportions: 10.32 mm per scene unit keeps a 160 mm phone at the same on-screen size as before
        const MM = 1 / 10.32;
        let W, H, D, R;
        if (spec) {
            [H, W, D] = spec.dims.map(v => v * MM);
            R = (spec.r || Math.min(spec.dims[1] * 0.13, 12)) * MM;
        } else {
            W = isTablet ? 10.8 : 7.45; H = isTablet ? 15.8 : 15.5; D = isTablet ? 0.66 : 0.86;
            R = isTablet ? 0.95 : (isApple ? 1.32 : 1.1);
        }
        // Edge profile: flat sides keep a small chamfer, rounded / curved-display phones get a deep roll-off
        const edge = spec ? spec.edge : (isApple ? 'flat' : 'round');
        const bevel = edge === 'flat' ? Math.min(0.08, D * 0.12) : D * (edge === 'curved' ? 0.44 : 0.38);

        const frameLook = {
            titanium:  { metalness: 1, roughness: 0.36, clearcoat: 0 },
            steel:     { metalness: 1, roughness: 0.1, clearcoat: 1 },
            aluminium: { metalness: 1, roughness: 0.26, clearcoat: 0.5 },
            painted:   { metalness: 0.55, roughness: 0.3, clearcoat: 0.8 },
        }[metal];

        const M = {
            frame: new THREE.MeshPhysicalMaterial(Object.assign({ clearcoatRoughness: 0.2 }, frameLook)),
            back: new THREE.MeshPhysicalMaterial({ metalness: 0, roughness: 0.42, clearcoat: 1, clearcoatRoughness: 0.28, roughnessMap: noiseTexture(), side: THREE.DoubleSide }),
            island: new THREE.MeshPhysicalMaterial({ metalness: 0.0, roughness: 0.18, clearcoat: 1, clearcoatRoughness: 0.08 }),
            black: new THREE.MeshPhysicalMaterial({ color: '#040405', metalness: 0.1, roughness: 0.12, clearcoat: 1, clearcoatRoughness: 0.05 }),
            darkMetal: new THREE.MeshStandardMaterial({ color: '#1a1a1d', metalness: 0.9, roughness: 0.35 }),
            antenna: new THREE.MeshStandardMaterial({ color: '#2a2a2e', metalness: 0.2, roughness: 0.6 }),
            lensRing: new THREE.MeshPhysicalMaterial({ metalness: 1, roughness: 0.18, clearcoat: 1 }),
            lens: new THREE.MeshPhysicalMaterial({ map: lensTexture(), metalness: 0.4, roughness: 0.02, clearcoat: 1, clearcoatRoughness: 0 }),
            flash: new THREE.MeshStandardMaterial({ color: '#f6eccf', emissive: '#2b2416', roughness: 0.35 }),
            glass: new THREE.MeshPhysicalMaterial({ color: '#ffffff', metalness: 0, roughness: 0.04, transparent: true, opacity: 0.08, clearcoat: 1, clearcoatRoughness: 0, depthWrite: false }),
            pcb: new THREE.MeshStandardMaterial({ color: '#16331f', metalness: 0.2, roughness: 0.55 }),
            chip: new THREE.MeshStandardMaterial({ color: '#111113', metalness: 0.6, roughness: 0.3 }),
            gold: new THREE.MeshStandardMaterial({ color: '#c9a24a', metalness: 1, roughness: 0.3 }),
            battery: new THREE.MeshStandardMaterial({ color: '#2b2b30', metalness: 0.5, roughness: 0.45 }),
        };

        const root = new THREE.Group();
        const parts = { front: new THREE.Group(), back: new THREE.Group(), inner: new THREE.Group(), frame: new THREE.Group() };
        Object.values(parts).forEach(p => root.add(p));

        // --- Frame (chassis ring) with antenna bands
        const frame = new THREE.Mesh(slab(W, H, R, D, bevel), M.frame);
        parts.frame.add(frame);
        // Hollow chassis ring: swapped in for the solid body in the exploded view
        const ringShape = roundedRectShape(W - 0.24, H - 0.24, R - 0.12);
        ringShape.holes.push(roundedRectShape(W - 0.7, H - 0.7, R - 0.35));
        const ringGeo = new THREE.ExtrudeGeometry(ringShape, { depth: D - 0.24, bevelEnabled: true, bevelThickness: 0.12, bevelSize: 0.12, bevelSegments: 8, curveSegments: 48 });
        ringGeo.center();
        const frameRing = new THREE.Mesh(ringGeo, M.frame);
        frameRing.visible = false;
        frameRing.castShadow = true;
        parts.frame.add(frameRing);
        const bandH = 0.07;
        [[W / 2, H * 0.4], [W / 2, -H * 0.4], [-W / 2, H * 0.4], [-W / 2, -H * 0.4]].forEach(([x, y]) => {
            const b = new THREE.Mesh(new THREE.BoxGeometry(0.06, bandH, D * 0.72), M.antenna);
            b.position.set(x + Math.sign(x) * 0.01, y, 0);
            parts.frame.add(b);
        });
        [[-W * 0.3, -H / 2], [W * 0.3, -H / 2]].forEach(([x, y]) => {
            const b = new THREE.Mesh(new THREE.BoxGeometry(bandH, 0.06, D * 0.72), M.antenna);
            b.position.set(x, y - 0.01, 0);
            parts.frame.add(b);
        });

        // Buttons (rounded)
        const button = (x, y, len) => {
            const g = new THREE.ExtrudeGeometry(roundedRectShape(len, D * 0.38, D * 0.18), { depth: 0.12, bevelEnabled: true, bevelThickness: 0.03, bevelSize: 0.03, bevelSegments: 4, curveSegments: 16 });
            g.center();
            const m = new THREE.Mesh(g, M.frame);
            m.rotation.set(0, Math.PI / 2, Math.PI / 2);
            m.position.set(x, y, 0);
            parts.frame.add(m);
        };
        button(W / 2 + 0.06, H * 0.17, 1.55);
        button(W / 2 + 0.06, H * 0.3, isApple ? 2.2 : 0.95);
        if (isApple) { button(-W / 2 - 0.06, H * 0.3, 0.95); button(-W / 2 - 0.06, H * 0.2, 0.95); button(-W / 2 - 0.06, H * 0.39, 0.5); }
        else { // SIM tray outline
            const tray = new THREE.Mesh(new THREE.BoxGeometry(0.03, 1.2, D * 0.3), M.antenna);
            tray.position.set(-W / 2 - 0.005, H * 0.22, 0);
            parts.frame.add(tray);
        }

        // Bottom edge: USB-C, speaker grille, mic
        const port = new THREE.Mesh(new THREE.ExtrudeGeometry(roundedRectShape(0.9, 0.28, 0.14), { depth: 0.1, bevelEnabled: false, curveSegments: 16 }), M.black);
        port.geometry.center();
        port.rotation.x = Math.PI / 2;
        port.position.set(0, -H / 2 - 0.01, 0);
        parts.frame.add(port);
        const holeGeo = new THREE.CylinderGeometry(0.055, 0.055, 0.06, 12);
        for (let i = 0; i < (isApple ? 7 : 6); i++) {
            const hole = new THREE.Mesh(holeGeo, M.black);
            hole.position.set(1.0 + i * 0.22, -H / 2 - 0.005, 0);
            parts.frame.add(hole);
            if (isApple) {
                const h2 = hole.clone();
                h2.position.x = -1.0 - i * 0.22;
                parts.frame.add(h2);
            }
        }

        // --- Front: black glass, live screen, glass sheen
        const bezel = new THREE.Mesh(flatPanel(W - 0.1, H - 0.1, R - 0.05), M.black);
        bezel.position.z = D / 2 + 0.003;
        const border = isTablet ? 0.85 : (isApple ? 0.25 : 0.2);
        const sw = W - border * 2 - 0.1, sh = H - border * 2 - 0.1;
        const screenCanvas = document.createElement('canvas');
        screenCanvas.width = 1000;
        screenCanvas.height = Math.round(1000 * sh / sw);
        const screenTex = tex(screenCanvas);
        const screenMat = new THREE.MeshStandardMaterial({ map: screenTex, emissive: 0xffffff, emissiveMap: screenTex, emissiveIntensity: 0, roughness: 1, metalness: 0 });
        const screen = new THREE.Mesh(flatPanel(sw, sh, R - border - 0.02), screenMat);
        screen.position.z = D / 2 + 0.005;
        const sheen = new THREE.Mesh(flatPanel(W - 0.1, H - 0.1, R - 0.05), M.glass);
        sheen.position.z = D / 2 + 0.008;
        parts.front.add(bezel, screen, sheen);

        // --- Back: glass panel, camera, wordmark
        const backPanel = new THREE.Mesh(flatPanel(W - 0.1, H - 0.1, R - 0.05), M.back);
        backPanel.rotation.y = Math.PI;
        backPanel.position.z = -D / 2 - 0.003;
        parts.back.add(backPanel);

        const cam = new THREE.Group();
        cam.position.z = -D / 2 - 0.004;
        parts.back.add(cam);

        const lens = (x, y, r) => {
            const ring = new THREE.Mesh(new THREE.CylinderGeometry(r, r * 1.04, 0.26, 64), M.lensRing);
            ring.rotation.x = Math.PI / 2; ring.position.set(x, y, -0.13);
            const inner = new THREE.Mesh(new THREE.CylinderGeometry(r * 0.86, r * 0.86, 0.28, 64), M.black);
            inner.rotation.x = Math.PI / 2; inner.position.set(x, y, -0.14);
            const face = new THREE.Mesh(new THREE.CircleGeometry(r * 0.74, 64), M.lens);
            face.rotation.y = Math.PI; face.position.set(x, y, -0.285);
            cam.add(ring, inner, face);
        };
        const flash = (x, y, r) => {
            const f = new THREE.Mesh(new THREE.CylinderGeometry(r, r, 0.08, 32), M.flash);
            f.rotation.x = Math.PI / 2; f.position.set(x, y, -0.04);
            cam.add(f);
        };
        const mic = (x, y) => {
            const m = new THREE.Mesh(new THREE.CircleGeometry(0.06, 16), M.black);
            m.rotation.y = Math.PI; m.position.set(x, y, -0.005);
            cam.add(m);
        };
        const island = (w, h, r, x, y) => {
            const g = new THREE.ExtrudeGeometry(roundedRectShape(w, h, r), { depth: 0.06, bevelEnabled: true, bevelThickness: 0.06, bevelSize: 0.06, bevelSegments: 8, curveSegments: 48 });
            const m = new THREE.Mesh(g, M.island);
            m.rotation.y = Math.PI; m.position.set(x, y, 0);
            cam.add(m);
            return m;
        };
        const L = W / 2 - 0.5, T = H / 2 - 0.5; // back's top-left corner is world +x

        if (spec && spec.cam) {
            // Measured layout: mm from the back's top-left corner -> world (that corner is +x, +y)
            const X = mm => W / 2 - mm * MM, Y = mm => H / 2 - mm * MM;
            const c = spec.cam;
            let modMat = M.island;
            if (c.mc && c.mc !== 'same') {
                modMat = new THREE.MeshPhysicalMaterial({ color: c.mc, metalness: 0.1, roughness: 0.15, clearcoat: 1, clearcoatRoughness: 0.05 });
            }
            const swapMat = mesh => { if (mesh && modMat !== M.island) mesh.material = modMat; };
            if (c.m) {
                const [kind, a, b, w, h, r] = c.m;
                if (kind === 'circle') {
                    const rad = w / 2 * MM;
                    const disc = new THREE.Mesh(new THREE.CylinderGeometry(rad, rad * 1.03, 0.14, 96), modMat);
                    disc.rotation.x = Math.PI / 2; disc.position.set(X(a), Y(b), -0.07);
                    const lip = new THREE.Mesh(new THREE.TorusGeometry(rad, 0.045, 12, 96), M.lensRing);
                    lip.position.set(X(a), Y(b), -0.14);
                    cam.add(disc, lip);
                } else {
                    const rr2 = kind === 'pill' ? Math.min(w, h) / 2 : (r || 6);
                    swapMat(island(w * MM, h * MM, rr2 * MM, X(a + w / 2), Y(b + h / 2)));
                }
            }
            (c.lens || []).forEach(([x, y, d]) => lens(X(x), Y(y), d / 2 * MM));
            (c.flash || []).forEach(([x, y, d]) => flash(X(x), Y(y), d / 2 * MM));
            (c.dark || []).forEach(([x, y, d]) => {
                const s = new THREE.Mesh(new THREE.CylinderGeometry(d / 2 * MM, d / 2 * MM, d > 3 ? 0.16 : 0.02, 32), M.black);
                s.rotation.x = Math.PI / 2; s.position.set(X(x), Y(y), d > 3 ? -0.08 : -0.005);
                cam.add(s);
            });
        } else if (isTablet) {
            island(1.6, 1.6, 0.8, L - 0.8, T - 0.8);
            lens(L - 0.8, T - 0.8, 0.45);
            flash(L - 1.9, T - 0.8, 0.14);
        } else if (isApple) {
            const s = isPro ? 3.45 : 3.0, cx = L - s / 2, cy = T - s / 2, o = s * 0.245, r = isPro ? 0.66 : 0.6;
            island(s, s, 0.85, cx, cy);
            if (isPro) { lens(cx + o, cy + o, r); lens(cx + o, cy - o, r); lens(cx - o, cy, r); flash(cx - o, cy + o, 0.2); mic(cx - o, cy - o); }
            else { lens(cx + o, cy + o, r); lens(cx - o, cy - o, r); flash(cx - o, cy + o, 0.18); mic(cx + o, cy - o); }
        } else if (brand === 'samsung') {
            const x = L - 0.8;
            for (let i = 0; i < 3; i++) lens(x, T - 0.85 - i * 1.4, 0.56);
            flash(x - 1.05, T - 0.85, 0.16);
        } else if (/honor|realme|oppo|huawei|vivo/.test(brand)) {
            const cx = L - 1.8, cy = T - 1.8;
            const disc = new THREE.Mesh(new THREE.CylinderGeometry(1.62, 1.68, 0.14, 96), M.island);
            disc.rotation.x = Math.PI / 2; disc.position.set(cx, cy, -0.07);
            const lip = new THREE.Mesh(new THREE.TorusGeometry(1.64, 0.05, 12, 96), M.lensRing);
            lip.position.set(cx, cy, -0.14);
            cam.add(disc, lip);
            lens(cx + 0.62, cy + 0.58, 0.58); lens(cx - 0.62, cy + 0.58, 0.44); lens(cx + 0.62, cy - 0.66, 0.44);
            flash(cx - 0.62, cy - 0.66, 0.18);
        } else {
            const w = 2.4, h = isPro ? 4.3 : 3.6, cx = L - w / 2, cy = T - h / 2;
            island(w, h, 0.8, cx, cy);
            lens(cx, cy + h / 2 - 1.05, 0.66);
            lens(cx, cy + h / 2 - 2.45, 0.52);
            if (isPro) lens(cx, cy - h / 2 + 0.72, 0.38); else flash(cx, cy - h / 2 + 0.58, 0.17);
        }

        let wordmark = null;
        if (!isApple && cfg.brand) {
            wordmark = new THREE.Mesh(new THREE.PlaneGeometry(3.2, 0.8), new THREE.MeshBasicMaterial({ transparent: true, depthWrite: false }));
            wordmark.rotation.y = Math.PI;
            wordmark.position.set(0, -H / 2 + 1.7, -D / 2 - 0.008);
            parts.back.add(wordmark);
        }

        // --- Inside (only seen in the exploded view)
        const inside = buildInternals({ parts, M, W, H, D, MM, spec, cfg, isApple, isTablet, metal });
        parts.inner.visible = false;

        root.traverse(o => { if (o.isMesh) { o.castShadow = true; } });

        return {
            root, parts, M, screenCanvas, screenTex, screenMat, wordmark, frameSolid: frame, frameRing,
            size: { W, H, D }, isTablet, isApple, spec, labelAnchors: inside.labels,
            info: {
                isApple,
                front: spec ? spec.front : (isApple ? 'island' : (isTablet ? 'bezel' : 'punch')),
                modelLabel: [cfg.brand, cfg.model].filter(Boolean).join(' ') || cfg.name
            }
        };
    }

    // ================================================================ real model files (.glb / .gltf)
    const GLTF_URL = 'https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js';
    const DRACO_URL = 'https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/DRACOLoader.js';
    const DRACO_DECODERS = 'https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/libs/draco/gltf/';
    const loaded = {};
    function script(src) {
        if (!loaded[src]) {
            loaded[src] = new Promise((res, rej) => {
                const s = document.createElement('script');
                s.src = src; s.async = true; s.onload = res; s.onerror = rej;
                document.head.appendChild(s);
            });
        }
        return loaded[src];
    }

    /** Load a real model and normalise it: centred, upright, scaled to the phone's height. */
    async function loadModelFile(url, targetHeight) {
        if (!THREE.GLTFLoader) await script(GLTF_URL);
        if (!THREE.DRACOLoader) await script(DRACO_URL).catch(() => {});
        const loader = new THREE.GLTFLoader();
        if (THREE.DRACOLoader) {
            const draco = new THREE.DRACOLoader();
            draco.setDecoderPath(DRACO_DECODERS);
            loader.setDRACOLoader(draco); // many exported phone models are Draco-compressed
        }
        const gltf = await new Promise((res, rej) => loader.load(url, res, undefined, rej));
        const model = gltf.scene;

        let box = new THREE.Box3().setFromObject(model);
        let size = box.getSize(new THREE.Vector3());
        // A phone stands tallest on Y; models exported lying flat (or on their side) get stood up
        if (size.z > size.y && size.z >= size.x) model.rotation.x = -Math.PI / 2;
        else if (size.x > size.y && size.x > size.z) model.rotation.z = Math.PI / 2;
        model.updateMatrixWorld(true);
        box = new THREE.Box3().setFromObject(model);
        size = box.getSize(new THREE.Vector3());
        const center = box.getCenter(new THREE.Vector3());

        const wrap = new THREE.Group();
        const inner = new THREE.Group();
        inner.add(model);
        inner.position.copy(center).multiplyScalar(-1);
        wrap.add(inner);
        wrap.scale.setScalar(targetHeight / Math.max(size.y, 0.0001));
        wrap.traverse(o => {
            if (o.isMesh) {
                o.castShadow = true;
                if (o.material) {
                    [].concat(o.material).forEach(m => { if ('envMapIntensity' in m) m.envMapIntensity = 1.1; });
                }
            }
        });
        return wrap;
    }

    // ================================================================ viewer
    class Procedural3DPhone {
        constructor(container, cfg) {
            this.container = container;
            this.cfg = cfg || {};
            this.disposed = false;
            // Embedded mode (homepage showcase): the page drives the pose, no zoom/drag so the page still scrolls
            this.embedded = !!this.cfg.embedded;
            this.autoSpin = !this.embedded;

            container.innerHTML = '';
            if (getComputedStyle(container).position === 'static') container.style.position = 'relative';
            container.style.touchAction = this.embedded ? 'pan-y' : 'none';
            container.classList.add(this.embedded ? 'p3d-embedded' : 'p3d-host');
            injectStyles();

            const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true, powerPreference: 'high-performance' });
            renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
            renderer.outputEncoding = THREE.sRGBEncoding;
            renderer.toneMapping = THREE.ACESFilmicToneMapping;
            renderer.toneMappingExposure = 0.95;
            renderer.shadowMap.enabled = true;
            renderer.shadowMap.type = THREE.PCFSoftShadowMap;
            renderer.domElement.className = 'p3d-canvas';
            renderer.domElement.setAttribute('role', 'img');
            renderer.domElement.setAttribute('aria-label', '3D model of ' + (this.cfg.name || 'phone') + '. Drag to rotate.');
            container.appendChild(renderer.domElement);
            this.renderer = renderer;

            const scene = new THREE.Scene();
            this.scene = scene;
            try {
                if (THREE.RoomEnvironment) {
                    const pm = new THREE.PMREMGenerator(renderer);
                    scene.environment = pm.fromScene(new THREE.RoomEnvironment(), 0.04).texture;
                    pm.dispose();
                } else {
                    scene.environment = fallbackEnvironment(renderer);
                }
            } catch (e) {
                scene.environment = fallbackEnvironment(renderer);
            }

            // Studio lights: soft key with real shadow, cool fill, warm pink rim
            scene.add(new THREE.HemisphereLight(0xffffff, 0x5a4450, 0.25));
            const key = new THREE.DirectionalLight(0xffffff, 1.1);
            key.position.set(7, 14, 12);
            key.castShadow = true;
            key.shadow.mapSize.set(1024, 1024);
            key.shadow.camera.left = -14; key.shadow.camera.right = 14;
            key.shadow.camera.top = 14; key.shadow.camera.bottom = -14;
            key.shadow.camera.near = 1; key.shadow.camera.far = 60;
            key.shadow.bias = -0.0006;
            const fill = new THREE.DirectionalLight(0xdfe8ff, 0.35);
            fill.position.set(-12, 2, 8);
            const rim = new THREE.DirectionalLight(0xffc4dd, 0.9);
            rim.position.set(-6, 8, -14);
            scene.add(key, fill, rim);

            const built = buildPhone(this.cfg);
            this.built = built;
            this.size = built.size;
            this.pivot = new THREE.Group();
            this.pivot.add(built.root);
            scene.add(this.pivot);

            // Floor: soft real shadow + contact shadow
            const floorY = -built.size.H / 2 - 1.3;
            const floor = new THREE.Mesh(new THREE.PlaneGeometry(80, 80), new THREE.ShadowMaterial({ opacity: this.embedded ? 0.35 : 0.14 }));
            floor.rotation.x = -Math.PI / 2;
            floor.position.y = floorY;
            floor.receiveShadow = true;
            const blob = new THREE.Mesh(new THREE.PlaneGeometry(built.size.W * 2.1, built.size.W * 1.0),
                new THREE.MeshBasicMaterial({ map: contactShadow(), transparent: true, depthWrite: false }));
            blob.rotation.x = -Math.PI / 2;
            blob.position.y = floorY + 0.01;
            scene.add(floor, blob);
            this.blob = blob;
            this.floor = floor;

            this.camera = new THREE.PerspectiveCamera(28, 1, 0.1, 300);
            // Camera distance follows the device's real height, so tablets and Pro Max models frame correctly
            this.baseDist = built.size.H * (this.embedded ? 2.52 : 2.97);
            this.dist = this.baseDist * 1.25;
            this.targetDist = this.baseDist;

            // Intro: phone rises and turns into a three-quarter view, then the screen wakes
            this.rotY = -Math.PI * 1.1; this.rotX = 0.25;
            this.targetRotY = 0.5; this.targetRotX = 0.06;
            this.tiltX = 0; this.tiltY = 0;
            this.velY = 0;
            this.explode = 0; this.targetExplode = 0;
            this.screenOn = 0;
            this.lastInteract = performance.now() + 1500;
            this.started = performance.now();

            // Finishes: given explicitly, else the unit's own colour, else colours sampled from the product photos
            this.finishes = finishesFor(this.cfg, built.spec);
            const named = this.finishes.length ? this.finishes[0].hex : null;
            this.setFinish(named || (built.isApple ? '#1d1e22' : '#2a2b30'), true);

            this.buildUI();
            this.bindInput();
            this.resize();
            this.ro = new ResizeObserver(() => this.resize());
            this.ro.observe(container);

            if (this.embedded) {
                // nothing to sample: the page supplies finishes
            } else if (!named && this.cfg.type !== 'unit') {
                finishesFromImage(this.cfg.image).then(list => {
                    if (this.disposed || !list.length) return;
                    const seen = {};
                    this.finishes = list.map(h => {
                        let n = nearestName(h);
                        seen[n] = (seen[n] || 0) + 1;
                        if (seen[n] > 1) n = (luma(h) > 170 ? 'Light ' : 'Deep ') + n;
                        return { hex: h, name: n };
                    });
                    this.setFinish(this.finishes[0].hex);
                    this.renderSwatches();
                });
            } else {
                this.renderSwatches();
            }

            this.clock = new THREE.Clock();
            this.loop = this.loop.bind(this);
            requestAnimationFrame(this.loop);
            this.clockTimer = setInterval(() => this.paintScreen(), 30000);

            if (this.cfg.modelUrl) this.useRealModel(this.cfg.modelUrl);
        }

        /**
         * Swap the generated handset for a real scanned/CAD model file.
         * The generated phone stays on screen until the file has loaded, and remains if loading fails.
         */
        async useRealModel(url) {
            const token = (this.modelToken = (this.modelToken || 0) + 1);
            try {
                const real = await loadModelFile(url, this.size.H);
                if (this.disposed || token !== this.modelToken) return;
                const old = this.built.root;
                this.pivot.remove(old);
                old.traverse(o => {
                    if (o.geometry) o.geometry.dispose();
                    if (o.material) o.material.dispose();
                });
                this.pivot.add(real);
                this.realModel = real;
                this.isReal = true;
                this.targetExplode = 0;
                this.explode = 0;
                if (this.ui) {
                    const sw = this.ui.querySelector('.p3d-swatches');
                    if (sw) sw.hidden = true;                 // a real model keeps its own true colours
                    const inside = this.ui.querySelector('[data-v=inside]');
                    if (inside) inside.hidden = true;
                    this.labels && this.labels.forEach(l => l.classList.remove('on'));
                }
            } catch (e) {
                console.warn('3D model file could not be loaded, showing the generated model instead:', url, e);
            }
        }

        // ---------------------------------------------------------- finishes
        setFinish(hex, instant) {
            if (this.isReal) return; // real models keep their scanned colours
            this.finish = hex;
            const M = this.built.M;
            const light = luma(hex) > 150;
            // Titanium frames are the finish colour itself; other frames sit a shade off the back glass
            const metalTint = (this.built.spec && this.built.spec.metal === 'titanium') ? hex : (light ? shade(hex, -18) : shade(hex, 28));
            const targets = {
                back: new THREE.Color(hex).convertSRGBToLinear(),
                frame: new THREE.Color(metalTint).convertSRGBToLinear(),
                island: new THREE.Color(light ? shade(hex, -14) : shade(hex, 14)).convertSRGBToLinear(),
                ring: new THREE.Color(light ? shade(hex, -40) : shade(hex, 40)).convertSRGBToLinear(),
            };
            if (instant) {
                M.back.color.copy(targets.back); M.frame.color.copy(targets.frame);
                M.island.color.copy(targets.island); M.lensRing.color.copy(targets.ring);
            }
            this.colorTargets = targets;
            if (this.built.wordmark) {
                const old = this.built.wordmark.material.map;
                this.built.wordmark.material.map = textTexture(String(this.cfg.brand).toUpperCase(), light ? 'rgba(30,30,34,0.55)' : 'rgba(255,255,255,0.6)', 110);
                this.built.wordmark.material.needsUpdate = true;
                if (old) old.dispose();
            }
            this.paintScreen();
            if (this.swatchWrap) {
                this.swatchWrap.querySelectorAll('button').forEach(b => b.classList.toggle('on', b.dataset.hex === hex));
                const f = this.finishes.find(f => f.hex === hex);
                if (this.swatchName) this.swatchName.textContent = f ? f.name : '';
            }
        }

        paintScreen() {
            lockScreen(this.built.screenCanvas, this.built.info, this.finish || '#2a2b30');
            this.built.screenTex.needsUpdate = true;
        }

        // ---------------------------------------------------------- UI
        buildUI() {
            const ui = document.createElement('div');
            ui.className = 'p3d-ui';
            if (this.embedded) {
                ui.innerHTML = '<div class="p3d-labels"></div>';
                this.container.appendChild(ui);
                this.ui = ui;
                this.makeLabels();
                return;
            }
            ui.innerHTML = `
                <div class="p3d-hint"><i class="fa-solid fa-hand-pointer"></i> Drag to rotate · Scroll to zoom</div>
                <div class="p3d-swatches" hidden><span class="p3d-swatch-name"></span><div class="p3d-swatch-row"></div></div>
                <div class="p3d-labels"></div>
                <div class="p3d-bar" role="toolbar" aria-label="3D view controls">
                    <button type="button" data-v="front">Front</button>
                    <button type="button" data-v="side">Side</button>
                    <button type="button" data-v="back">Back</button>
                    <span class="sep"></span>
                    <button type="button" data-v="inside" title="See inside"><i class="fa-solid fa-layer-group"></i><span> Inside</span></button>
                    <button type="button" data-v="reset" aria-label="Reset view" title="Reset view"><i class="fa-solid fa-rotate-left"></i></button>
                    <button type="button" data-v="full" aria-label="Full screen" title="Full screen"><i class="fa-solid fa-expand"></i></button>
                </div>`;
            this.container.appendChild(ui);
            this.ui = ui;
            this.hint = ui.querySelector('.p3d-hint');
            // Be upfront when the maker publishes no specs for this exact model
            const spec = this.built.spec;
            if (spec && spec.verified === false) {
                const n = document.createElement('div');
                n.className = 'p3d-note';
                n.innerHTML = '<i class="fa-solid fa-circle-info"></i> Approximate shape';
                n.title = spec.note || 'No official specifications published for this model';
                ui.appendChild(n);
            }
            this.swatchWrap = ui.querySelector('.p3d-swatches');
            this.swatchName = ui.querySelector('.p3d-swatch-name');
            ui.querySelector('.p3d-bar').addEventListener('click', e => {
                const b = e.target.closest('button');
                if (b) this.setView(b.dataset.v);
            });

            this.makeLabels();
        }

        // Floating part labels for the exploded view
        makeLabels() {
            const wrap = this.ui.querySelector('.p3d-labels');
            wrap.innerHTML = '<svg class="p3d-leaders" aria-hidden="true"></svg>';
            this.leaderSvg = wrap.querySelector('svg');
            const labels = (this.built.labelAnchors || []).map(a => a[2]);
            this.labels = labels.map(t => {
                const d = document.createElement('div');
                d.className = 'p3d-label';
                d.textContent = t;
                wrap.appendChild(d);
                return d;
            });
        }

        /** Page-driven pose (embedded mode): rotation in radians, explode 0..1 */
        setPose(rotY, rotX, explode) {
            this.targetRotY = rotY;
            this.targetRotX = rotX;
            this.targetExplode = explode;
            this.targetDist = this.baseDist * (1 + explode * 0.12); // pull back a little to fit the opened-up layers
            this.lastInteract = performance.now();
        }

        /** Swap to a different handset without recreating the renderer */
        setModel(cfg) {
            this.cfg = Object.assign({ modelUrl: null }, this.cfg, cfg);
            this.modelToken = (this.modelToken || 0) + 1;   // cancel any model file still loading
            if (this.isReal) {
                this.pivot.remove(this.realModel);
                this.realModel = null;
                this.isReal = false;
                this.pivot.add(this.built.root);            // so the disposal below has the right root
            }
            const old = this.built.root;
            this.pivot.remove(old);
            old.traverse(o => {
                if (o.geometry) o.geometry.dispose();
                if (o.material) {
                    ['map', 'emissiveMap', 'roughnessMap'].forEach(k => o.material[k] && o.material[k].dispose());
                    o.material.dispose();
                }
            });
            this.built = buildPhone(this.cfg);
            this.size = this.built.size;
            this.pivot.add(this.built.root);
            const floorY = -this.size.H / 2 - 1.3;
            this.floor.position.y = floorY;
            this.blob.position.y = floorY + 0.01;
            this.baseDist = this.size.H * (this.embedded ? 2.52 : 2.97);
            this.targetDist = this.baseDist;
            this.finishes = finishesFor(this.cfg, this.built.spec);
            this.colorTargets = null;
            this.setFinish(this.finishes.length ? this.finishes[0].hex : '#2a2b30', true);
            if (!this.embedded && this.swatchWrap) this.renderSwatches(); // new model, new colour list
            if (this.ui) {
                const old = this.ui.querySelector('.p3d-note');
                if (old) old.remove();
                const spec = this.built.spec;
                if (!this.embedded && spec && spec.verified === false) {
                    const n = document.createElement('div');
                    n.className = 'p3d-note';
                    n.innerHTML = '<i class="fa-solid fa-circle-info"></i> Approximate shape';
                    n.title = spec.note || 'No official specifications published for this model';
                    this.ui.appendChild(n);
                }
            }
            this.started = performance.now() - 900; // quick screen re-wake
            this.makeLabels();
            if (this.cfg.modelUrl) this.useRealModel(this.cfg.modelUrl);
        }

        renderSwatches() {
            if (!this.finishes.length) return;
            const row = this.swatchWrap.querySelector('.p3d-swatch-row');
            row.innerHTML = '';
            this.finishes.forEach(f => {
                const b = document.createElement('button');
                b.type = 'button';
                b.dataset.hex = f.hex;
                b.style.background = f.hex;
                b.title = f.name;
                b.setAttribute('aria-label', 'Show ' + f.name + ' finish');
                b.addEventListener('click', () => { this.setFinish(f.hex); this.lastInteract = performance.now(); });
                row.appendChild(b);
            });
            this.swatchWrap.hidden = false;
            this.setFinish(this.finish);
        }

        setView(v) {
            const bar = this.ui.querySelector('.p3d-bar');
            if (v === 'full') {
                const host = this.container.closest('.pd-layer') || this.container;
                if (document.fullscreenElement) document.exitFullscreen();
                else if (host.requestFullscreen) host.requestFullscreen().catch(() => {});
                return;
            }
            if (v === 'inside') {
                this.targetExplode = this.targetExplode > 0.5 ? 0 : 1;
                bar.querySelector('[data-v=inside]').classList.toggle('on', this.targetExplode > 0.5);
                if (this.targetExplode > 0.5) { v = 'three'; }
                else { this.targetDist = this.baseDist; return; }
            }
            this.lastInteract = performance.now() + 5000;
            this.velY = 0;
            const turns = Math.round(this.targetRotY / (Math.PI * 2)) * Math.PI * 2;
            const views = { front: [0, 0], back: [Math.PI, 0], side: [Math.PI / 2, 0], reset: [0.5, 0.06], three: [-1.12, 0.14] };
            const [ry, rx] = views[v] || views.reset;
            this.targetRotY = turns + ry;
            this.targetRotX = rx;
            this.targetDist = v === 'three' ? this.baseDist * 1.22 : (v === 'reset' ? this.baseDist : this.targetDist);
            if (v === 'reset') { this.targetDist = this.baseDist; this.targetExplode = 0; bar.querySelector('[data-v=inside]').classList.remove('on'); }
            bar.querySelectorAll('[data-v=front],[data-v=side],[data-v=back]').forEach(b => b.classList.toggle('on', b.dataset.v === v));
        }

        bindInput() {
            const el = this.renderer.domElement;
            if (this.embedded) {
                // Only a gentle tilt toward the cursor; scrolling and touch stay with the page
                el.style.cursor = 'default';
                el.addEventListener('pointermove', e => {
                    if (e.pointerType !== 'mouse') return;
                    const r = el.getBoundingClientRect();
                    this.tiltY = ((e.clientX - r.left) / r.width - 0.5) * 0.16;
                    this.tiltX = ((e.clientY - r.top) / r.height - 0.5) * 0.1;
                });
                el.addEventListener('pointerleave', () => { this.tiltX = 0; this.tiltY = 0; });
                return;
            }
            const pointers = new Map();
            let lastX = 0, lastY = 0, pinch0 = 0, dist0 = 0, dragging = false;

            el.addEventListener('pointerdown', e => {
                el.setPointerCapture(e.pointerId);
                pointers.set(e.pointerId, { x: e.clientX, y: e.clientY });
                lastX = e.clientX; lastY = e.clientY;
                dragging = true;
                el.classList.add('grabbing');
                this.velY = 0;
                this.lastInteract = performance.now();
                this.hint.classList.add('gone');
                if (pointers.size === 2) {
                    const [a, b] = [...pointers.values()];
                    pinch0 = Math.hypot(a.x - b.x, a.y - b.y);
                    dist0 = this.targetDist;
                }
            });
            el.addEventListener('pointermove', e => {
                const r = el.getBoundingClientRect();
                // gentle look-at-cursor tilt while not dragging
                this.tiltY = ((e.clientX - r.left) / r.width - 0.5) * 0.12;
                this.tiltX = ((e.clientY - r.top) / r.height - 0.5) * 0.08;
                if (!pointers.has(e.pointerId)) return;
                pointers.set(e.pointerId, { x: e.clientX, y: e.clientY });
                if (pointers.size === 2) {
                    const [a, b] = [...pointers.values()];
                    const d = Math.hypot(a.x - b.x, a.y - b.y);
                    if (pinch0) this.targetDist = THREE.MathUtils.clamp(dist0 * pinch0 / d, this.baseDist * 0.5, this.baseDist * 1.5);
                    return;
                }
                const dx = e.clientX - lastX, dy = e.clientY - lastY;
                lastX = e.clientX; lastY = e.clientY;
                this.targetRotY += dx * 0.009;
                this.targetRotX = THREE.MathUtils.clamp(this.targetRotX + dy * 0.006, -0.9, 0.9);
                this.velY = dx * 0.009;
                this.lastInteract = performance.now();
                this.ui.querySelectorAll('[data-v=front],[data-v=side],[data-v=back]').forEach(b => b.classList.remove('on'));
            });
            const up = e => {
                pointers.delete(e.pointerId);
                if (pointers.size < 2) pinch0 = 0;
                if (!pointers.size) { dragging = false; el.classList.remove('grabbing'); }
            };
            el.addEventListener('pointerup', up);
            el.addEventListener('pointercancel', up);
            el.addEventListener('pointerleave', () => { if (!dragging) { this.tiltX = 0; this.tiltY = 0; } });
            el.addEventListener('wheel', e => {
                e.preventDefault();
                this.targetDist = THREE.MathUtils.clamp(this.targetDist * (1 + e.deltaY * 0.0012), this.baseDist * 0.5, this.baseDist * 1.5);
                this.lastInteract = performance.now();
            }, { passive: false });
            el.addEventListener('dblclick', () => this.setView('reset'));
            el.tabIndex = 0;
            el.addEventListener('keydown', e => {
                if (e.key === 'ArrowLeft') { this.targetRotY -= 0.3; this.lastInteract = performance.now(); e.preventDefault(); }
                if (e.key === 'ArrowRight') { this.targetRotY += 0.3; this.lastInteract = performance.now(); e.preventDefault(); }
            });
        }

        resize() {
            const w = this.container.clientWidth, h = this.container.clientHeight;
            if (!w || !h) return;
            this.renderer.setSize(w, h, false);
            this.camera.aspect = w / h;
            this.fit = w / h < (this.embedded ? 1 : 0.8) ? 1.35 : 1;
            this.camera.updateProjectionMatrix();
        }

        // ---------------------------------------------------------- frame loop
        loop() {
            if (this.disposed) return;
            requestAnimationFrame(this.loop);
            if (!this.container.offsetParent && !document.fullscreenElement) return;
            if (document.hidden || this.paused) return;

            const now = performance.now();
            const t = this.clock.getElapsedTime();
            const idle = now - this.lastInteract > 2800;

            if (idle && this.autoSpin && this.targetExplode < 0.5) {
                this.targetRotY += 0.004;
                this.targetRotX += (0.06 - this.targetRotX) * 0.02;
            } else if (Math.abs(this.velY) > 0.0001) {
                this.targetRotY += this.velY;
                this.velY *= 0.92;
            }

            const k = 0.08;
            this.rotY += (this.targetRotY - this.rotY) * k;
            this.rotX += (this.targetRotX - this.rotX) * k;
            this.dist += (this.targetDist - this.dist) * 0.07;
            this.explode += (this.targetExplode - this.explode) * 0.08;

            this.pivot.rotation.set(this.rotX + this.tiltX, this.rotY + this.tiltY, 0);
            const bob = Math.sin(t * 1.1) * 0.16;
            this.pivot.position.y = (this.embedded ? 1.1 : 0.15) + bob;
            this.blob.material.opacity = 0.9 - bob * 0.8;

            if (this.isReal) {
                // Real model file: no generated parts to explode or recolour
                this.camera.position.set(0, 0.8, this.dist * (this.fit || 1));
                this.camera.lookAt(0, 0.3, 0);
                this.renderer.render(this.scene, this.camera);
                return;
            }

            // Exploded view
            const P = this.built.parts, e = this.explode, D = this.size.D;
            // back glass | frame | battery + board | display - fanned out along the depth axis
            P.front.position.set(-e * 1.4, e * 0.5, e * 5.2);
            P.back.position.set(e * 2.8, -e * 0.6, -e * 4.2);
            P.inner.visible = e > 0.02;
            this.built.frameSolid.visible = e < 0.03;
            this.built.frameRing.visible = e >= 0.03;
            P.inner.position.set(-e * 0.6, e * 0.2, e * 2.6);

            // Screen wakes after the intro
            const wake = THREE.MathUtils.clamp((now - this.started - 1100) / 700, 0, 1);
            this.built.screenMat.emissiveIntensity = 0.92 * wake;
            this.built.screenMat.color.setScalar(0.08 + 0.92 * wake);

            // Ease body colours toward the chosen finish
            if (this.colorTargets) {
                const M = this.built.M, ct = this.colorTargets;
                M.back.color.lerp(ct.back, 0.12);
                M.frame.color.lerp(ct.frame, 0.12);
                M.island.color.lerp(ct.island, 0.12);
                M.lensRing.color.lerp(ct.ring, 0.12);
            }

            this.camera.position.set(0, 0.8, this.dist * (this.fit || 1));
            this.camera.lookAt(0, 0.3, 0);
            this.renderer.render(this.scene, this.camera);
            this.placeLabels(e);
        }

        placeLabels(e) {
            if (!this.labels) return;
            const show = e > 0.6;
            const anchors = this.built.labelAnchors || [];
            const svg = this.leaderSvg;
            if (svg) svg.classList.toggle('on', show);
            this.labels.forEach(el => el.classList.toggle('on', show));
            if (!show) return;

            // Callout layout: each tag sits in a column beside the phone, joined to its part by a leader line
            const w = this.container.clientWidth, h = this.container.clientHeight;
            const pts = anchors.map(([obj, v]) => {
                const p = obj.localToWorld(v.clone()).project(this.camera);
                return { x: (p.x * 0.5 + 0.5) * w, y: (-p.y * 0.5 + 0.5) * h };
            });
            if (!pts.length) return;
            const midX = pts.reduce((s, p) => s + p.x, 0) / pts.length;
            const minX = Math.min(...pts.map(p => p.x)), maxX = Math.max(...pts.map(p => p.x));
            const gap = 34, pad = 10;
            const columns = { '-1': [], '1': [] };
            pts.forEach((p, i) => {
                const el = this.labels[i];
                if (!el) return;
                columns[p.x < midX ? '-1' : '1'].push({ i, el, ax: p.x, ay: p.y, lw: el.offsetWidth || 120, lh: (el.offsetHeight || 24) + 6 });
            });
            let lines = '';
            Object.entries(columns).forEach(([side, list]) => {
                const s = Number(side);
                list.sort((a, b) => a.ay - b.ay);
                // spread vertically so no two tags overlap, keeping each close to its part
                let lastY = -Infinity;
                list.forEach(it => { it.y = Math.max(it.ay, lastY + it.lh); lastY = it.y; });
                const overflow = lastY - (h - pad - 12);
                if (overflow > 0) list.forEach(it => { it.y -= overflow; });
                list.forEach(it => {
                    const edgeX = s < 0 ? Math.max(pad + it.lw, minX - gap) : Math.min(w - pad - it.lw, maxX + gap);
                    const left = s < 0 ? edgeX - it.lw : edgeX;
                    it.el.style.transform = `translate(${left}px, ${it.y - it.lh / 2 + 3}px)`;
                    lines += `<line x1="${it.ax.toFixed(1)}" y1="${it.ay.toFixed(1)}" x2="${edgeX.toFixed(1)}" y2="${it.y.toFixed(1)}"/>` +
                             `<circle cx="${it.ax.toFixed(1)}" cy="${it.ay.toFixed(1)}" r="3.5"/>`;
                });
            });
            if (svg) {
                svg.setAttribute('viewBox', `0 0 ${w} ${h}`);
                svg.innerHTML = lines;
            }
        }

        dispose() {
            this.disposed = true;
            clearInterval(this.clockTimer);
            if (this.ro) this.ro.disconnect();
            this.scene.traverse(o => {
                if (o.geometry) o.geometry.dispose();
                if (o.material) {
                    ['map', 'emissiveMap', 'roughnessMap'].forEach(k => o.material[k] && o.material[k].dispose());
                    o.material.dispose();
                }
            });
            if (this.scene.environment) this.scene.environment.dispose();
            this.renderer.dispose();
            this.container.innerHTML = '';
        }
    }

    // ================================================================ styles
    function injectStyles() {
        if (document.getElementById('p3d-style')) return;
        const st = document.createElement('style');
        st.id = 'p3d-style';
        st.textContent = `
            .p3d-host{background:radial-gradient(110% 80% at 50% 30%,#ffffff 0%,#fdf4f8 55%,#f9e3ee 100%)}
            .p3d-embedded{background:transparent}
            .p3d-embedded .p3d-canvas{cursor:default}
            .p3d-canvas{position:absolute;inset:0;width:100%;height:100%;display:block;cursor:grab;outline:none}
            .p3d-canvas.grabbing{cursor:grabbing}
            .p3d-canvas:focus-visible{box-shadow:inset 0 0 0 3px #f472b6}
            .p3d-ui{position:absolute;inset:0;pointer-events:none;z-index:4;font-family:inherit}
            .p3d-ui button{pointer-events:auto}
            .p3d-hint{position:absolute;left:50%;top:16px;transform:translateX(-50%);font-size:.72rem;font-weight:600;color:#6b5563;background:rgba(255,255,255,.85);padding:6px 12px;border-radius:999px;white-space:nowrap;transition:opacity .6s;box-shadow:0 2px 10px rgba(219,39,119,.08)}
            .p3d-hint i{margin-right:4px;color:#db2777}
            .p3d-hint.gone{opacity:0}
            .p3d-note{position:absolute;right:16px;top:58px;font-size:.68rem;font-weight:700;color:#9d174d;background:rgba(255,255,255,.9);padding:4px 10px;border-radius:999px;pointer-events:auto;cursor:help;box-shadow:0 2px 8px rgba(131,24,67,.12)}
            .p3d-note i{margin-right:4px}
            .p3d-bar{position:absolute;left:50%;bottom:16px;transform:translateX(-50%);display:flex;align-items:center;gap:2px;padding:5px;border-radius:999px;background:rgba(255,255,255,.92);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);box-shadow:0 10px 30px -8px rgba(131,24,67,.25),0 0 0 1px rgba(251,207,232,.7)}
            .p3d-bar button{border:0;background:transparent;border-radius:999px;padding:8px 13px;font-size:.78rem;font-weight:700;color:#4a3542;transition:background .2s,color .2s;white-space:nowrap}
            .p3d-bar button:hover{background:#fdf2f8;color:#be185d}
            .p3d-bar button.on{background:#db2777;color:#fff}
            .p3d-bar .sep{width:1px;height:18px;background:#fbcfe8;margin:0 3px}
            .p3d-swatches{position:absolute;left:16px;bottom:74px;display:flex;flex-direction:column;gap:6px;pointer-events:auto}
            .p3d-swatch-name{font-size:.7rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#831843}
            .p3d-swatch-row{display:flex;gap:8px}
            .p3d-swatch-row button{width:26px;height:26px;border-radius:50%;border:2px solid #fff;box-shadow:0 0 0 1px rgba(0,0,0,.12),0 3px 8px rgba(0,0,0,.12);transition:transform .2s,box-shadow .2s;padding:0}
            .p3d-swatch-row button:hover{transform:scale(1.12)}
            .p3d-swatch-row button.on{box-shadow:0 0 0 2px #db2777,0 3px 10px rgba(219,39,119,.35)}
            .p3d-label{position:absolute;left:0;top:0;font-size:.72rem;font-weight:700;color:#831843;background:#fff;padding:5px 10px;border-radius:999px;box-shadow:0 6px 16px -6px rgba(131,24,67,.35);opacity:0;transition:opacity .35s;white-space:nowrap}
            .p3d-label.on{opacity:1}
            .p3d-label::before{display:none}
            .p3d-leaders{position:absolute;inset:0;width:100%;height:100%;opacity:0;transition:opacity .35s;overflow:visible}
            .p3d-leaders.on{opacity:1}
            .p3d-leaders line{stroke:#f472b6;stroke-width:1.2;stroke-dasharray:3 3}
            .p3d-leaders circle{fill:#fff;stroke:#db2777;stroke-width:2}
            :fullscreen .p3d-host,.pd-layer:fullscreen{background:radial-gradient(110% 80% at 50% 30%,#ffffff 0%,#fdf4f8 55%,#f9e3ee 100%)}
            @media (max-width:575px){.p3d-bar button span{display:none}.p3d-bar button{padding:8px 10px}.p3d-swatches{bottom:70px;left:12px}}
            @media (prefers-reduced-motion:reduce){.p3d-hint,.p3d-label{transition:none}}
        `;
        document.head.appendChild(st);
    }

    window.Procedural3DPhone = Procedural3DPhone;
})();
