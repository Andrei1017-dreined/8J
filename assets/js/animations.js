/**
 * 8J Cellphone & Accessories IMS
 * 120fps Inertial Wheel Smooth Scroller & Interactive UI Engine
 */

(function () {
    'use strict';

    // =========================================================================
    // 1. FRAME-RATE INDEPENDENT INERTIAL MOUSE-WHEEL SMOOTH SCROLLER
    // =========================================================================
    const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const isTouchDevice = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0 && window.innerWidth < 992);

    let targetScrollY = window.scrollY || window.pageYOffset || 0;
    let currentScrollY = targetScrollY;
    let isAnimatingScroll = false;
    let lastFrameTime = 0;
    let scrollIdleTimer = null;

    function getMaxScroll() {
        return Math.max(0, document.documentElement.scrollHeight - window.innerHeight);
    }

    // Check if wheel event happened inside a scrollable child container (modal, table, sidebar, etc.)
    function hasScrollableParent(el, deltaY) {
        while (el && el !== document.body && el !== document.documentElement) {
            if (el.classList && (el.classList.contains('modal') || el.classList.contains('dropdown-menu'))) {
                return true;
            }
            const style = window.getComputedStyle(el);
            const overflowY = style.overflowY;
            if ((overflowY === 'auto' || overflowY === 'scroll') && el.scrollHeight > el.clientHeight + 2) {
                const canScrollUp = deltaY < 0 && el.scrollTop > 0;
                const canScrollDown = deltaY > 0 && (el.scrollTop + el.clientHeight < el.scrollHeight - 1);
                if (canScrollUp || canScrollDown) {
                    return true;
                }
            }
            el = el.parentElement;
        }
        return false;
    }

    function markScrollingActive() {
        document.body.classList.add('is-scrolling');
        clearTimeout(scrollIdleTimer);
        scrollIdleTimer = setTimeout(() => {
            document.body.classList.remove('is-scrolling');
        }, 140);
    }

    function updateScrollLoop(now) {
        if (!isAnimatingScroll) return;

        const dt = lastFrameTime ? Math.min((now - lastFrameTime) / 1000, 0.05) : (1 / 60);
        lastFrameTime = now;

        // Frame-rate independent exponential damping (feels equally silky on 60Hz, 120Hz, 144Hz, 240Hz)
        const lerpFactor = 1 - Math.exp(-14.5 * dt);
        const diff = targetScrollY - currentScrollY;

        if (Math.abs(diff) > 0.35) {
            currentScrollY += diff * lerpFactor;
            window.scrollTo(0, Math.round(currentScrollY * 10) / 10);
            requestAnimationFrame(updateScrollLoop);
        } else {
            currentScrollY = targetScrollY;
            window.scrollTo(0, Math.round(targetScrollY));
            isAnimatingScroll = false;
            lastFrameTime = 0;
        }
    }

    function startSmoothScroll() {
        if (!isAnimatingScroll) {
            isAnimatingScroll = true;
            lastFrameTime = performance.now();
            requestAnimationFrame(updateScrollLoop);
        }
    }

    // Global smooth scroll helper for Back-to-Top and anchor jumps
    window.smoothScrollTo = function (yPos) {
        if (prefersReducedMotion) {
            window.scrollTo(0, yPos);
            return;
        }
        currentScrollY = window.scrollY || window.pageYOffset || 0;
        targetScrollY = Math.max(0, Math.min(getMaxScroll(), yPos));
        markScrollingActive();
        startSmoothScroll();
    };

    if (!prefersReducedMotion && !isTouchDevice) {
        window.addEventListener('wheel', function (e) {
            // Do not hijack pinch-zoom or horizontal trackpad swipes
            if (e.ctrlKey || e.metaKey || e.shiftKey || Math.abs(e.deltaX) > Math.abs(e.deltaY)) {
                return;
            }
            // Do not hijack if any Bootstrap modal is open, inside 3D viewport, or inside scrollable child
            if (e.defaultPrevented || (e.target && e.target.closest && e.target.closest('.studio-3d-viewport')) || document.body.classList.contains('modal-open') || hasScrollableParent(e.target, e.deltaY)) {
                return;
            }

            e.preventDefault();

            // Sync currentScrollY if user dragged the native scrollbar when idle
            if (!isAnimatingScroll) {
                currentScrollY = window.scrollY || window.pageYOffset || 0;
                targetScrollY = currentScrollY;
            }

            // Normalize wheel delta across browsers & input modes (pixel vs line vs page)
            let delta = e.deltaY;
            if (e.deltaMode === 1) {
                delta *= 36; // Line mode (Firefox)
            } else if (e.deltaMode === 2) {
                delta *= window.innerHeight * 0.85; // Page mode
            }

            // Boost discrete mouse-wheel notches slightly for effortless gliding
            const wheelMultiplier = Math.abs(delta) >= 80 ? 1.08 : 1.0;
            targetScrollY = Math.max(0, Math.min(getMaxScroll(), targetScrollY + delta * wheelMultiplier));

            markScrollingActive();
            startSmoothScroll();
        }, { passive: false });

        // Sync state if user uses keyboard arrows, Spacebar, PageUp/PageDown, or scrollbar drag
        window.addEventListener('keydown', function (e) {
            const navKeys = ['ArrowUp', 'ArrowDown', 'PageUp', 'PageDown', 'Home', 'End', ' '];
            if (navKeys.includes(e.key)) {
                isAnimatingScroll = false;
            }
        }, { passive: true });

        window.addEventListener('mousedown', function (e) {
            // If clicking near scrollbar edge, cancel active interpolation
            if (e.clientX >= document.documentElement.clientWidth - 18) {
                isAnimatingScroll = false;
            }
        }, { passive: true });
    }

    // =========================================================================
    // 2. DOM READY: PROGRESS BAR, NAVBAR, REVEAL OBSERVER & FLOATING DOCK
    // =========================================================================
    document.addEventListener('DOMContentLoaded', function () {
        // Inject Top GPU Scroll Progress Bar
        let progressBar = document.getElementById('scroll-progress-bar');
        if (!progressBar) {
            progressBar = document.createElement('div');
            progressBar.id = 'scroll-progress-bar';
            document.body.prepend(progressBar);
        }

        // Inject Floating Quick-Action Dock (Back-to-Top + Live Cart Pill)
        let dock = document.querySelector('.floating-quick-dock');
        if (!dock && !window.location.pathname.includes('/admin/')) {
            dock = document.createElement('div');
            dock.className = 'floating-quick-dock';
            const cartHref = (typeof window.BASE_URL_JS !== 'undefined' ? window.BASE_URL_JS : '') + 'cart.php';
            dock.innerHTML = `
                <a href="${cartHref}" id="floatingCartPill" class="floating-dock-btn" title="View Pick-Up Cart">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span id="floatingCartText">0 Items</span>
                    <span class="badge bg-white text-dark rounded-pill px-2 py-0.5 fw-extrabold" id="floatingCartTotal" style="font-size: 0.75rem;">₱0</span>
                </a>
                <button type="button" id="backToTopBtn" class="floating-dock-btn" title="Smooth Scroll to Top" aria-label="Scroll to top">
                    <i class="fa-solid fa-arrow-up"></i>
                </button>
            `;
            document.body.appendChild(dock);

            const bttBtn = document.getElementById('backToTopBtn');
            if (bttBtn) {
                bttBtn.addEventListener('click', function () {
                    window.smoothScrollTo(0);
                });
            }
        }

        const navbar = document.querySelector('.navbar-custom');
        const bttBtn = document.getElementById('backToTopBtn');
        let scrollTicking = false;

        function onScrollUpdate() {
            const y = window.scrollY || window.pageYOffset || 0;
            const max = getMaxScroll();

            if (navbar) {
                if (y > 16) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            }

            if (progressBar) {
                const ratio = max > 0 ? Math.min(1, Math.max(0, y / max)) : 0;
                progressBar.style.transform = `scaleX(${ratio}) translateZ(0)`;
            }

            if (bttBtn) {
                if (y > 380) {
                    bttBtn.classList.add('visible');
                } else {
                    bttBtn.classList.remove('visible');
                }
            }

            scrollTicking = false;
        }

        window.addEventListener('scroll', function () {
            if (!scrollTicking) {
                scrollTicking = true;
                requestAnimationFrame(onScrollUpdate);
            }
        }, { passive: true });

        onScrollUpdate();
        window.syncFloatingCartDock();

        // Smooth Anchor Link Scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (!href || href === '#' || this.hasAttribute('data-bs-toggle')) return;
                const targetEl = document.querySelector(href);
                if (targetEl) {
                    e.preventDefault();
                    const rect = targetEl.getBoundingClientRect();
                    const offsetTop = rect.top + (window.scrollY || window.pageYOffset) - 80;
                    window.smoothScrollTo(offsetTop);
                }
            });
        });

        // Clean IntersectionObserver for Scroll-Reveal (cleans up animation classes after completion)
        const revealElements = document.querySelectorAll('.stat-card, .card-custom, .pos-item, .bento-card, .cat-tile, .gallery-bento-card, .step-flow-card');
        if ('IntersectionObserver' in window && !prefersReducedMotion) {
            const revealObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach((entry, idx) => {
                    if (entry.isIntersecting) {
                        const el = entry.target;
                        el.classList.add('animate-reveal');
                        const delay = Math.min((idx % 4) * 0.04, 0.16);
                        if (delay > 0) {
                            el.style.animationDelay = delay + 's';
                        }
                        el.addEventListener('animationend', function cleanup() {
                            el.classList.remove('animate-reveal');
                            el.style.animationDelay = '';
                            el.removeEventListener('animationend', cleanup);
                        });
                        observer.unobserve(el);
                    }
                });
            }, {
                root: null,
                rootMargin: '0px 0px -25px 0px',
                threshold: 0.08
            });

            revealElements.forEach(el => revealObserver.observe(el));
        }

        // Tactile Button Click Ripple Physics
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn');
            if (!btn) return;

            const ripple = document.createElement('span');
            ripple.classList.add('btn-ripple-wave');

            const rect = btn.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;

            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';

            btn.appendChild(ripple);
            setTimeout(() => ripple.remove(), 480);
        });

        // Outsourced Product Commercial Hover Video Player (Zero initial bandwidth, lazy-streamed on hover)
        const adVideos = document.querySelectorAll('video.product-hover-video[data-ad-src]');
        if (adVideos.length > 0 && !prefersReducedMotion) {
            let activeAdCard = null;
            let activeAdVideo = null;
            let adIntentTimer = null;

            function stopActiveAd() {
                clearTimeout(adIntentTimer);
                if (activeAdCard) {
                    activeAdCard.classList.remove('card-ad-playing');
                    activeAdCard = null;
                }
                if (activeAdVideo) {
                    try {
                        activeAdVideo.pause();
                        activeAdVideo.currentTime = 0;
                        if (activeAdVideo.dataset.adNext && activeAdVideo.dataset.adScene === '2') {
                            activeAdVideo.src = activeAdVideo.dataset.adSrc;
                            activeAdVideo.dataset.adScene = '1';
                        }
                    } catch (e) {}
                    activeAdVideo = null;
                }
            }

            // Pause any playing hover ad immediately when scrolling starts
            window.addEventListener('scroll', stopActiveAd, { passive: true });

            adVideos.forEach(video => {
                const card = video.closest('.card') || video.parentElement;
                if (!card) return;

                // Handle sequence & extended ~10-second commercial loop
                video.addEventListener('ended', function () {
                    if (activeAdVideo !== video) return;
                    if (video.dataset.adNext && video.dataset.adScene !== '2') {
                        // Play Scene 2 for another ~5-10s
                        video.dataset.adScene = '2';
                        video.src = video.dataset.adNext;
                        video.playbackRate = 0.65;
                        video.play().catch(() => {});
                    } else {
                        // Loop back to Scene 1
                        video.dataset.adScene = '1';
                        video.src = video.dataset.adSrc;
                        video.playbackRate = 0.65;
                        video.play().catch(() => {});
                    }
                });

                card.addEventListener('mouseenter', function () {
                    if (document.body.classList.contains('is-scrolling')) return;
                    stopActiveAd();

                    adIntentTimer = setTimeout(() => {
                        if (document.body.classList.contains('is-scrolling')) return;

                        if (!video.getAttribute('src')) {
                            video.src = video.dataset.adSrc;
                            video.load();
                        }

                        // Extended 10-second cinematic playback rate (slows 5s renders to ~10s)
                        video.playbackRate = video.dataset.adNext ? 0.75 : 0.55;

                        activeAdCard = card;
                        activeAdVideo = video;

                        const p = video.play();
                        if (p !== undefined) {
                            p.then(() => {
                                if (activeAdCard === card) {
                                    card.classList.add('card-ad-playing');
                                    // Ensure playbackRate persists after play starts
                                    video.playbackRate = video.dataset.adNext ? 0.75 : 0.55;
                                }
                            }).catch(() => {
                                // Ignore autoplay restrictions or network abort
                            });
                        } else {
                            card.classList.add('card-ad-playing');
                            video.playbackRate = video.dataset.adNext ? 0.75 : 0.55;
                        }
                    }, 3000); // 3-second continuous hover intent before streaming and playing ad
                });

                card.addEventListener('mouseleave', function () {
                    if (activeAdCard === card) {
                        stopActiveAd();
                    } else {
                        clearTimeout(adIntentTimer);
                    }
                });
            });
        }
    });

    // =========================================================================
    // 3. FLOATING CART DOCK & BADGE SYNC
    // =========================================================================
    window.syncFloatingCartDock = function () {
        const pill = document.getElementById('floatingCartPill');
        const textEl = document.getElementById('floatingCartText');
        const totalEl = document.getElementById('floatingCartTotal');
        if (!pill || !textEl || !totalEl) return;

        // Hide on cart.php itself
        if (window.location.pathname.endsWith('cart.php')) {
            pill.classList.remove('visible');
            return;
        }

        try {
            const cart = JSON.parse(localStorage.getItem('8j_cart') || '[]');
            const totalItems = cart.reduce((sum, item) => sum + (parseInt(item.quantity) || 1), 0);
            const totalAmount = cart.reduce((sum, item) => sum + ((parseFloat(item.price) || 0) * (parseInt(item.quantity) || 1)), 0);

            if (totalItems > 0) {
                textEl.textContent = totalItems + (totalItems === 1 ? ' Item in Cart' : ' Items in Cart');
                totalEl.textContent = '₱' + totalAmount.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                pill.classList.add('visible');
            } else {
                pill.classList.remove('visible');
            }
        } catch (err) {
            pill.classList.remove('visible');
        }
    };
})();

// 4. Elastic Cart Bounce Animation Trigger
function triggerCartAnimation() {
    const badge = document.getElementById('cart-counter');
    if (badge) {
        badge.classList.remove('badge-bounce');
        void badge.offsetWidth;
        badge.classList.add('badge-bounce');
    }
    if (typeof window.syncFloatingCartDock === 'function') {
        window.syncFloatingCartDock();
    }
}

// 5. Sleek Floating Toast Notification
function showToast(title, message = '', type = 'dark') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = 'position:fixed;bottom:84px;right:24px;z-index:99999;display:flex;flex-direction:column;gap:10px;pointer-events:none;';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.style.cssText = `
        background: linear-gradient(135deg, #2b1120 0%, #4a1534 100%);
        color: #ffffff;
        border: 1px solid rgba(244, 114, 182, 0.45);
        padding: 12px 20px;
        border-radius: 9999px;
        font-size: 0.85rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        pointer-events: auto;
        box-shadow: 0 14px 30px -6px rgba(43, 17, 32, 0.45);
        transform: translate3d(0, 12px, 0);
        opacity: 0;
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease;
    `;

    toast.innerHTML = `
        <span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;background:#10b981;color:#fff;font-size:0.7rem;">
            <i class="fa-solid fa-check"></i>
        </span>
        <span>${title}</span>
    `;

    container.appendChild(toast);

    requestAnimationFrame(() => {
        toast.style.transform = 'translate3d(0, 0, 0)';
        toast.style.opacity = '1';
    });

    setTimeout(() => {
        toast.style.transform = 'translate3d(0, 10px, 0) scale(0.95)';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 320);
    }, 2600);
}

// 6. Smooth Number Counter Easing Function
function animateNumber(element, start, end, duration = 400, prefix = '₱') {
    if (!element) return;
    const startTime = performance.now();
    function update(now) {
        const elapsed = now - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const ease = 1 - Math.pow(1 - progress, 3);
        const current = start + (end - start) * ease;
        element.innerText = (prefix ? prefix : '') + current.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (progress < 1) {
            requestAnimationFrame(update);
        }
    }
    requestAnimationFrame(update);
}
