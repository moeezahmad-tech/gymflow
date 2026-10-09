/**
 * GymFlow - Progressive Web App (PWA) Auto-Update & Client Engine
 * Handles Service Worker Registration, Auto-Updates, Version Checks, and A2HS Install Prompts
 */

(function () {
    'use strict';

    let deferredInstallPrompt = null;
    let isRefreshing = false;

    // 1. Service Worker Registration & Auto-Update Engine
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            const swUrl = './sw.js';

            navigator.serviceWorker.register(swUrl, { scope: './' })
                .then((registration) => {
                    // Check for updates periodically (every 15 minutes)
                    setInterval(() => {
                        registration.update().catch(() => {});
                    }, 15 * 60 * 1000);

                    // Check for updates on tab focus
                    document.addEventListener('visibilitychange', () => {
                        if (document.visibilityState === 'visible') {
                            registration.update().catch(() => {});
                        }
                    });

                    // Handle update found
                    registration.addEventListener('updatefound', () => {
                        const newWorker = registration.installing;
                        if (newWorker) {
                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    // New version detected -> Auto-update
                                    showUpdateToast(newWorker);
                                }
                            });
                        }
                    });

                    // If a worker is already waiting, trigger update
                    if (registration.waiting) {
                        showUpdateToast(registration.waiting);
                    }
                })
                .catch((err) => {
                    console.info('[GymFlow PWA] SW Registration:', err.message);
                });

            // Prevent reload loops on controllerchange
            navigator.serviceWorker.addEventListener('controllerchange', () => {
                if (!isRefreshing) {
                    isRefreshing = true;
                    window.location.reload();
                }
            });
        });
    }

    // 2. Auto-Update Notification Banner
    function showUpdateToast(worker) {
        // Automatically activate new service worker
        worker.postMessage({ action: 'skipWaiting' });

        // Show floating sleek update notification
        const existingToast = document.getElementById('pwa-update-toast');
        if (existingToast) return;

        const toast = document.createElement('div');
        toast.id = 'pwa-update-toast';
        toast.className = 'fixed bottom-5 right-5 z-50 bg-zinc-900/95 border border-red-500/40 text-white px-5 py-3.5 rounded-2xl shadow-2xl backdrop-blur-xl flex items-center gap-3.5 transition-all transform translate-y-0 text-xs animate-bounce';
        toast.innerHTML = `
            <div class="w-8 h-8 rounded-xl bg-red-600/20 text-red-400 border border-red-500/30 flex items-center justify-center text-sm flex-shrink-0">
                <i class="fa-solid fa-arrows-rotate fa-spin"></i>
            </div>
            <div>
                <strong class="block text-white font-heading text-sm uppercase tracking-wider">Update Detected</strong>
                <span class="text-zinc-400 text-[11px]">Updating GymFlow to the latest version...</span>
            </div>
        `;
        document.body.appendChild(toast);

        // Auto remove after 3 seconds
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    // 3. Android / Desktop Install Prompt
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredInstallPrompt = e;
        
        // Dispatch custom event for UI install buttons
        window.dispatchEvent(new CustomEvent('gymflow:caninstall'));
    });

    window.addEventListener('appinstalled', () => {
        deferredInstallPrompt = null;
        if (typeof showToast === 'function') {
            showToast('GymFlow was installed successfully!', 'check', 'App Installed');
        }
    });

    // Public helper to trigger install
    window.triggerPWAInstall = function () {
        if (deferredInstallPrompt) {
            deferredInstallPrompt.prompt();
            deferredInstallPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('[GymFlow PWA] User accepted installation');
                }
                deferredInstallPrompt = null;
            });
        } else if (isIOS() && !isInStandaloneMode()) {
            showIOSInstallInstructions();
        } else {
            if (typeof showToast === 'function') {
                showToast('GymFlow is already running as a native web app.', 'info', 'GymFlow App');
            } else {
                alert('GymFlow is already installed or accessible via your browser menu.');
            }
        }
    };

    // 4. iOS Detection & Standalone Helpers
    function isIOS() {
        return /iphone|ipad|ipod/.test(window.navigator.userAgent.toLowerCase());
    }

    function isInStandaloneMode() {
        return ('standalone' in window.navigator && window.navigator.standalone) || window.matchMedia('(display-mode: standalone)').matches;
    }

    function showIOSInstallInstructions() {
        const modal = document.createElement('div');
        modal.id = 'ios-install-modal';
        modal.className = 'fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-end sm:items-center justify-center p-4';
        modal.innerHTML = `
            <div class="bg-zinc-950 border border-zinc-800 w-full max-w-sm rounded-3xl p-6 shadow-2xl text-center space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-red-600 to-red-800 text-white flex items-center justify-center text-2xl mx-auto shadow-xl shadow-red-600/30">
                    <i class="fa-solid fa-mobile-screen-button"></i>
                </div>
                <div>
                    <h3 class="font-heading text-2xl font-bold text-white uppercase">Install on iPhone / iPad</h3>
                    <p class="text-xs text-zinc-400 mt-1">Add GymFlow to your Home Screen for full-screen 24/7 access:</p>
                </div>
                <div class="bg-zinc-900/80 p-4 rounded-2xl border border-zinc-800 text-left space-y-2.5 text-xs text-zinc-300">
                    <div class="flex items-center gap-2.5">
                        <span class="w-6 h-6 rounded-full bg-red-600/20 text-red-400 font-bold flex items-center justify-center text-[10px]">1</span>
                        <span>Tap the <i class="fa-solid fa-arrow-up-from-bracket text-red-400 mx-1"></i> <strong>Share</strong> icon in Safari.</span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="w-6 h-6 rounded-full bg-red-600/20 text-red-400 font-bold flex items-center justify-center text-[10px]">2</span>
                        <span>Scroll down and tap <i class="fa-regular fa-square-plus text-red-400 mx-1"></i> <strong>Add to Home Screen</strong>.</span>
                    </div>
                </div>
                <button onclick="document.getElementById('ios-install-modal').remove()" class="w-full btn-primary text-xs uppercase font-bold tracking-wider py-3 rounded-xl">
                    Got it
                </button>
            </div>
        `;
        document.body.appendChild(modal);
    }
})();
