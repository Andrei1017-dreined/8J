/**
 * 8J Cellphone & Accessories IMS
 * Interactive Animation Engine
 * Handles scroll-reveal triggers, tactile button ripples, and animated cart feedback
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Navbar Dynamic Blur Elevation on Scroll
    const navbar = document.querySelector('.navbar-custom');
    if (navbar) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 15) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        }, { passive: true });
    }

    // 2. IntersectionObserver for Smooth Scroll-Reveal
    const revealElements = document.querySelectorAll('.stat-card, .card-custom, .pos-item, .table-responsive, .brand-pill');
    if ('IntersectionObserver' in window) {
        const observerOptions = {
            root: null,
            rootMargin: '0px 0px -40px 0px',
            threshold: 0.1
        };

        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry, idx) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-reveal');
                    // Add staggered timing for cards in the same container
                    const delay = (idx % 6) * 0.06;
                    entry.target.style.animationDelay = delay + 's';
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        revealElements.forEach(el => revealObserver.observe(el));
    } else {
        revealElements.forEach(el => el.classList.add('animate-reveal'));
    }

    // 3. Tactile Button Click Ripple Physics (Buttons Only)
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            const ripple = document.createElement('span');
            ripple.classList.add('btn-ripple-wave');
            
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;

            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';

            this.appendChild(ripple);

            setTimeout(() => {
                ripple.remove();
            }, 500);
        });
    });
});

// 4. Elastic Cart Bounce Animation Trigger
function triggerCartAnimation() {
    const badge = document.getElementById('cart-counter');
    if (badge) {
        badge.classList.remove('badge-bounce');
        void badge.offsetWidth; // Force DOM reflow
        badge.classList.add('badge-bounce');
    }
}

// 5. Sleek Floating Toast Notification
function showToast(title, message = '', type = 'dark') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:99999;display:flex;flex-direction:column;gap:10px;pointer-events:none;';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = 'animate-reveal shadow-lg';
    toast.style.cssText = `
        background: #09090b;
        color: #ffffff;
        border: 1px solid #27272a;
        padding: 12px 20px;
        border-radius: 9999px;
        font-size: 0.85rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        pointer-events: auto;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
        transform: translateY(10px);
        opacity: 0;
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    `;

    toast.innerHTML = `
        <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#10b981;"></span>
        <span>${title}</span>
    `;

    container.appendChild(toast);

    requestAnimationFrame(() => {
        toast.style.transform = 'translateY(0)';
        toast.style.opacity = '1';
    });

    setTimeout(() => {
        toast.style.transform = 'translateY(10px) scale(0.95)';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 400);
    }, 2500);
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
