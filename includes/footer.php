    </div><!-- /.site-wrapper -->
    <footer class="bg-white border-top py-4 mt-auto">
        <div class="container-fluid px-4 d-flex flex-wrap justify-content-between align-items-center gap-3 text-muted small">
            <div class="d-flex align-items-center gap-2">
                <img src="<?= BASE_URL ?>assets/img/logo.png" style="width: 28px; height: 28px; object-fit: contain;" alt="8J Logo">
                <div>
                    © <?= date('Y') ?> <strong>8J Cellphone & Accessories</strong>. All rights reserved.
                    <span class="text-secondary ms-1">• Tanauan City, Batangas</span>
                </div>
            </div>

            <!-- Official Facebook Page Link -->
            <div>
                <a href="https://www.facebook.com/profile.php?id=100063760157450" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-dark rounded-pill px-3 d-inline-flex align-items-center gap-2" style="font-size: 0.8rem; font-weight: 600;">
                    <i class="fa-brands fa-facebook-f text-primary" style="color: #1877f2 !important;"></i>
                    <span>Official Facebook Page</span>
                    <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 0.7rem;"></i>
                </a>
            </div>

            <div>
                BS Computer Science Thesis • <em>Tanauan Institute, Inc.</em>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Award-Winning UI Animations Engine -->
    <script src="<?= BASE_URL ?>assets/js/animations.js"></script>
    <script>
        // Update cart badge dynamically from localStorage with bounce animation
        function refreshCartBadge(animate = false) {
            const cart = JSON.parse(localStorage.getItem('8j_cart') || '[]');
            const badge = document.getElementById('cart-counter');
            if (badge) {
                const totalCount = cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
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
        }
        document.addEventListener('DOMContentLoaded', () => refreshCartBadge(false));
    </script>
</body>
</html>
