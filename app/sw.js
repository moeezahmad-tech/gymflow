/**
 * GymFlow Mobile App - Dedicated PWA Service Worker
 * Scoped specifically to /app/ directory for standalone mobile experience
 */

const APP_VERSION = '1.1.0';
const CACHE_NAME = `gymflow-mobile-v${APP_VERSION}`;

// Pre-cached assets for native app shell
const STATIC_ASSETS = [
    './index.php',
    './manifest.json',
    './images/app_logo.png',
    './images/logo.png',
    './images/favicon.png',
    './icons/icon-192x192.png',
    './icons/icon-512x512.png',
    './icons/maskable-icon-512x512.png',
    '../assets/css/style.css',
    '../assets/js/main.js'
];

// Install & Cache Shell
self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS).catch((err) => {
                console.warn('[GymFlow App SW] Pre-cache non-fatal error:', err);
            });
        })
    );
});

// Activate & Clean Old Caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((k) => {
                    if (k !== CACHE_NAME) {
                        return caches.delete(k);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch - Network first for PHP/dynamic, Cache first for assets
self.addEventListener('fetch', (event) => {
    const req = event.request;
    const url = new URL(req.url);

    if (req.method !== 'GET' || !url.protocol.startsWith('http')) return;

    // Navigation requests (HTML / PHP) -> Network first with cache fallback
    if (req.mode === 'navigate' || req.headers.get('accept')?.includes('text/html')) {
        event.respondWith(
            fetch(req)
                .then((res) => {
                    if (res && res.status === 200) {
                        const clone = res.clone();
                        caches.open(CACHE_NAME).then((c) => c.put(req, clone));
                    }
                    return res;
                })
                .catch(async () => {
                    const match = await caches.match(req);
                    if (match) return match;
                    const indexMatch = await caches.match('./index.php');
                    return indexMatch || new Response(
                        '<div style="background:#000;color:#fff;font-family:sans-serif;text-align:center;padding:60px 20px;min-height:100vh;"><h2>Offline</h2><p>Please connect to the internet to use the GymFlow App.</p><button onclick="window.location.reload()" style="background:#ff2a2a;color:#fff;border:none;padding:12px 24px;border-radius:12px;font-weight:bold;margin-top:20px;cursor:pointer;">Retry</button></div>',
                        { headers: { 'Content-Type': 'text/html' } }
                    );
                })
        );
        return;
    }

    // Static assets -> Cache first, background refresh
    event.respondWith(
        caches.match(req).then((cached) => {
            const fetchPromise = fetch(req).then((netRes) => {
                if (netRes && netRes.status === 200) {
                    caches.open(CACHE_NAME).then((c) => c.put(req, netRes.clone()));
                }
                return netRes;
            }).catch(() => {});
            return cached || fetchPromise;
        })
    );
});
