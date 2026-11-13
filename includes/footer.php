    </div><!-- /.site-wrapper -->
    <footer class="bg-white border-top py-4 mt-auto" style="border-color: #fce7f3 !important;">
        <div class="container-fluid px-4 d-flex flex-wrap justify-content-between align-items-center gap-3 text-muted small">
            <div class="d-flex align-items-center gap-2">
                <img src="<?= BASE_URL ?>assets/img/logo.png" style="width: 30px; height: 30px; object-fit: contain;" alt="8J Logo" loading="lazy" decoding="async">
                <div>
                    © <?= date('Y') ?> <strong>8J Cellphone &amp; Laptop Repair / Accessories</strong>. All rights reserved.
                    <span class="text-secondary ms-1">• Victory Mall Tanauan 2nd Floor (Stall #S1) • Hotline: <a href="tel:09099833981" class="text-decoration-none fw-semibold text-dark">0909-9833-981</a></span>
                </div>
            </div>

            <!-- Quick Footer Navigation & Official Facebook Page Link -->
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="<?= BASE_URL ?>catalog.php?q=Apple" class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="font-size: 0.76rem;">
                    <i class="fa-brands fa-apple me-1"></i> Apple Hub
                </a>
                <a href="<?= BASE_URL ?>book_repair.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="font-size: 0.76rem;">
                    <i class="fa-solid fa-screwdriver-wrench me-1"></i> Free Check-Up
                </a>
                <a href="https://www.facebook.com/profile.php?id=100063760157450" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-dark rounded-pill px-3 d-inline-flex align-items-center gap-2" style="font-size: 0.78rem; font-weight: 600;">
                    <i class="fa-brands fa-facebook-f text-primary" style="color: #1877f2 !important;"></i>
                    <span>@Gab_8J Official Page</span>
                    <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 0.68rem;"></i>
                </a>
            </div>

            <div>
                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1">
                    <i class="fa-solid fa-shield-halved text-success me-1"></i> P.D. 1612 Compliant
                </span>
                <span class="ms-2">BS Computer Science Thesis • <em>Tanauan Institute, Inc.</em></span>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- 120fps Inertial Wheel Scroller & UI Animations Engine -->
    <script src="<?= BASE_URL ?>assets/js/animations.js?v=<?= filemtime(__DIR__ . '/../assets/js/animations.js') ?>"></script>
    <!-- Interactive 360° 3D All-Angles WebGL Product Studio -->
    <script src="<?= BASE_URL ?>assets/js/product-3d-viewer.js?v=<?= filemtime(__DIR__ . '/../assets/js/product-3d-viewer.js') ?>"></script>
    <script>
        // Update cart badge and floating cart dock dynamically from localStorage
        function refreshCartBadge(animate = false) {
            const cart = JSON.parse(localStorage.getItem('8j_cart') || '[]');
            const badge = document.getElementById('cart-counter');
            if (badge) {
                const totalCount = cart.reduce((sum, item) => sum + (parseInt(item.quantity) || 1), 0);
                if (totalCount > 0) {
                    badge.innerText = totalCount;
                    badge.style.display = 'inline-block';
                    if (animate && typeof triggerCartAnimation === 'function') {
                        triggerCartAnimation();
                    }
                } else {
                    badge.style.display = 'none';
                }
            }
            if (typeof window.syncFloatingCartDock === 'function') {
                window.syncFloatingCartDock();
            }
        }
        // While a form is being sent, lock its submit button so it can't be clicked twice
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (e.defaultPrevented || !(form instanceof HTMLFormElement) || (form.method || '').toLowerCase() !== 'post') return;
            const btn = e.submitter || form.querySelector('[type="submit"]');
            if (!btn || btn.dataset.busy) return;
            btn.dataset.busy = '1';
            // Keep the clicked button's name/value in the submission before disabling it
            if (btn.name) {
                const keep = document.createElement('input');
                keep.type = 'hidden'; keep.name = btn.name; keep.value = btn.value;
                form.appendChild(keep);
            }
            setTimeout(() => {
                btn.disabled = true;
                btn.dataset.label = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span> Saving…';
            }, 0);
        });
        // Coming back with the browser's Back button: re-enable buttons
        window.addEventListener('pageshow', (ev) => {
            if (!ev.persisted) return;
            document.querySelectorAll('[data-busy]').forEach(b => { b.disabled = false; delete b.dataset.busy; if (b.dataset.label) b.innerHTML = b.dataset.label; });
        });

        document.addEventListener('DOMContentLoaded', () => {
            // Shortcut links like admin/repairs.php?open=newJobModal open that form straight away
            const openId = new URLSearchParams(location.search).get('open');
            const openEl = openId && /^[A-Za-z][\w-]*$/.test(openId) ? document.getElementById(openId) : null;
            if (openEl && openEl.classList.contains('modal') && window.bootstrap) {
                new bootstrap.Modal(openEl).show();
            }

            refreshCartBadge(false);
            // Keyboard shortcut '/' to focus search input
            document.addEventListener('keydown', function (e) {
                if (e.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName)) {
                    const searchInput = document.getElementById('liveCatalogSearch') || document.getElementById('globalNavSearch');
                    if (searchInput) {
                        e.preventDefault();
                        searchInput.focus();
                        searchInput.select();
                    }
                }
            });
        });
    </script>
</body>
</html>
