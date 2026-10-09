/**
 * GymFlow - Progressive Web App Service Worker
 * Version Management, Offline Caching & Seamless Auto-Update Engine
 */

const APP_VERSION = '1.1.0';
const CACHE_NAME = `gymflow-v${APP_VERSION}`;

// Pre-cached Critical Assets
const STATIC_ASSETS = [
    './assets/css/style.css',
    './assets/js/main.js',
    './assets/js/pwa.js',
    './assets/images/logo.png',
    './assets/images/app_logo.png',
    './assets/images/favicon.png',
    './assets/images/icons/apple-touch-icon.png',
    './assets/images/icons/icon-192x192.png',
    './assets/images/icons/icon-512x512.png',
    './assets/images/icons/maskable-icon-512x512.png',
    './manifest.json'
];

// 1. INSTALL EVENT - Pre-cache core assets
self.addEventListener('install', (event) => {
    // Force new service worker to become active immediately upon install
    self.skipWaiting();

    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS).catch((err) => {
                console.warn('[GymFlow SW] Pre-cache non-fatal error:', err);
            });
        })
    );
});

// 2. ACTIVATE EVENT - Clean up outdated caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cache) => {
                    if (cache !== CACHE_NAME) {
                        return caches.delete(cache);
                    }
                })
            );
        }).then(() => {
            // Claim clients immediately so the new version takes control without reload delays
            return self.clients.claim();
        })
    );
});

// 3. FETCH EVENT - Network-First for Navigation / PHP Pages, Cache-First for Static Assets
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Skip non-GET requests and non-http(s) schemas
    if (request.method !== 'GET' || !url.protocol.startsWith('http')) {
        return;
    }

    // A. For HTML / PHP Navigation Pages: Network First, fallback to cache
    if (request.mode === 'navigate' || request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    if (response && response.status === 200) {
                        const copy = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                    }
                    return response;
                })
                .catch(async () => {
                    const cachedResponse = await caches.match(request);
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    // Fallback to home/index if cached
                    const indexFallback = await caches.match('./index.php');
                    return indexFallback || new Response(
                        '<div style="background:#050507;color:#fff;font-family:sans-serif;text-align:center;padding:50px;min-height:100vh;"><h2>Offline Mode</h2><p>You are currently offline. Connect to the internet to access live GymFlow metrics.</p><button onclick="window.location.reload()" style="background:#dc2626;color:#fff;border:none;padding:10px 20px;border-radius:10px;cursor:pointer;">Retry</button></div>',
                        { headers: { 'Content-Type': 'text/html' } }
                    );
                })
        );
        return;
    }

    // B. For Static Assets (CSS, JS, Images, Fonts): Cache First, background refresh
    if (
        request.destination === 'style' ||
        request.destination === 'script' ||
        request.destination === 'image' ||
        request.destination === 'font'
    ) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                if (cachedResponse) {
                    // Update cache in background
                    fetch(request).then((networkResponse) => {
                        if (networkResponse && networkResponse.status === 200) {
                            caches.open(CACHE_NAME).then((cache) => cache.put(request, networkResponse));
                        }
                    }).catch(() => {/* Offline, ignore */});
                    return cachedResponse;
                }

                return fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const copy = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                    }
                    return networkResponse;
                });
            })
        );
        return;
    }

    // Default fetch
    event.respondWith(
        fetch(request).catch(() => caches.match(request))
    );
});

// 4. MESSAGE EVENT - Handle explicit skipWaiting & version queries
self.addEventListener('message', (event) => {
    if (event.data) {
        if (event.data.action === 'skipWaiting') {
            self.skipWaiting();
        } else if (event.data.action === 'getVersion') {
            event.ports[0]?.postMessage({ version: APP_VERSION });
        }
    }
});
