/**
 * GymFlow - Main Client-Side JavaScript & Custom Toast Engine
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Mobile Menu Toggle
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileMenuClose = document.getElementById('mobileMenuClose');

    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
            document.body.classList.toggle('overflow-hidden');
        });

        if (mobileMenuClose) {
            mobileMenuClose.addEventListener('click', () => {
                mobileMenu.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            });
        }
    }

    // 2. Navbar Shrink on Scroll
    const mainHeader = document.getElementById('mainHeader');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            mainHeader?.classList.add('shadow-2xl', 'border-zinc-800/90', 'py-3');
            mainHeader?.classList.remove('py-4');
        } else {
            mainHeader?.classList.remove('shadow-2xl', 'border-zinc-800/90', 'py-3');
            mainHeader?.classList.add('py-4');
        }
    });

    // 3. Smooth Anchor Scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId && targetId !== '#') {
                const targetEl = document.querySelector(targetId);
                if (targetEl) {
                    e.preventDefault();
                    targetEl.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                        mobileMenu.classList.add('hidden');
                        document.body.classList.remove('overflow-hidden');
                    }
                }
            }
        });
    });
});

/**
 * Custom Toast Notification Engine
 * Replaces ugly browser alerts with dark-mode glassmorphic cards
 */
window.showToast = function(message, type = 'success', title = '') {
    let container = document.getElementById('gymflow-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'gymflow-toast-container';
        container.className = 'fixed bottom-6 right-6 z-[9999] flex flex-col gap-3 max-w-sm w-full px-4 pointer-events-none';
        document.body.appendChild(container);
    }

    const toastId = 'toast-' + Date.now();
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = 'pointer-events-auto flex items-start gap-3 p-4 rounded-2xl bg-[#0c0c12]/95 backdrop-blur-md border shadow-2xl transition-all duration-300 transform translate-y-4 opacity-0';

    let iconHtml = '<i class="fa-solid fa-circle-check text-emerald-400 text-lg"></i>';
    let borderColor = 'border-emerald-500/40 shadow-emerald-500/10';
    let defaultTitle = 'Success';

    if (type === 'fire') {
        iconHtml = '<i class="fa-solid fa-fire text-red-500 text-xl animate-bounce"></i>';
        borderColor = 'border-red-500/50 shadow-red-500/20';
        defaultTitle = 'Streak Updated';
    } else if (type === 'info') {
        iconHtml = '<i class="fa-solid fa-circle-info text-blue-400 text-lg"></i>';
        borderColor = 'border-blue-500/40 shadow-blue-500/10';
        defaultTitle = 'Notice';
    } else if (type === 'warning') {
        iconHtml = '<i class="fa-solid fa-triangle-exclamation text-amber-400 text-lg"></i>';
        borderColor = 'border-amber-500/40 shadow-amber-500/10';
        defaultTitle = 'Attention';
    } else if (type === 'message') {
        iconHtml = '<i class="fa-regular fa-paper-plane text-red-400 text-lg"></i>';
        borderColor = 'border-red-500/40 shadow-red-500/10';
        defaultTitle = 'Message Sent';
    }

    toast.className += ' ' + borderColor;

    toast.innerHTML = `
        <div class="shrink-0 pt-0.5">${iconHtml}</div>
        <div class="flex-1 min-w-0">
            <h5 class="text-xs font-bold uppercase tracking-wider text-white">${title || defaultTitle}</h5>
            <p class="text-xs text-zinc-300 mt-0.5 leading-relaxed">${message}</p>
        </div>
        <button onclick="this.closest('#${toastId}').remove()" class="text-zinc-500 hover:text-white text-xs px-1">
            <i class="fa-solid fa-xmark"></i>
        </button>
    `;

    container.appendChild(toast);

    // Animate In
    setTimeout(() => {
        toast.classList.remove('translate-y-4', 'opacity-0');
        toast.classList.add('translate-y-0', 'opacity-100');
    }, 20);

    // Auto Dismiss after 4 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.classList.remove('translate-y-0', 'opacity-100');
            toast.classList.add('translate-y-4', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }
    }, 4200);
};
