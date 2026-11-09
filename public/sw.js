/**
 * GymFlow - Progressive Web App Service Worker (v2.0 Next.js)
 * Standalone PWA Engine, Offline Fallback & Turnstile Gate Cache
 */

const APP_VERSION = '2.0.0';
const CACHE_NAME = `gymflow-pwa-v${APP_VERSION}`;

// Pre-cached critical PWA assets
const STATIC_ASSETS = [
  '/',
  '/app',
  '/manifest.json',
  '/favicon.ico',
  '/images/logo.png',
  '/images/app_logo.png',
  '/assets/images/icons/icon-192x192.png',
  '/assets/images/icons/icon-512x512.png',
  '/assets/images/icons/maskable-icon-512x512.png',
];

// 1. INSTALL EVENT - Pre-cache core assets
self.addEventListener('install', (event) => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(STATIC_ASSETS).catch((err) => {
        console.warn('[GymFlow PWA] Pre-cache non-fatal error:', err);
      });
    })
  );
});

// 2. ACTIVATE EVENT - Clean up old cache versions
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
    }).then(() => self.clients.claim())
  );
});

// 3. FETCH EVENT - Network-First for Navigation with Offline Fallback
self.addEventListener('fetch', (event) => {
  const request = event.request;
  const url = new URL(request.url);

  // Skip non-GET and chrome-extension/external schemas
  if (request.method !== 'GET' || !url.protocol.startsWith('http')) {
    return;
  }

  // A. Navigation requests: Network-First, fallback to cached /app or offline slip
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
          if (cachedResponse) return cachedResponse;

          const appFallback = await caches.match('/app');
          if (appFallback) return appFallback;

          return new Response(
            '<div style="background:#000;color:#fff;font-family:sans-serif;text-align:center;padding:60px 20px;min-height:100vh;">' +
            '<h1 style="color:#ff2a2a;font-size:32px;margin-bottom:10px;">GYMFLOW OFFLINE PASS</h1>' +
            '<p style="color:#a1a1aa;font-size:14px;max-width:320px;margin:0 auto 24px;">Your device is currently offline. Your last cached turnstile QR credential remains valid for gate entry.</p>' +
            '<a href="/app" style="display:inline-block;background:#ff2a2a;color:#fff;text-decoration:none;font-weight:bold;padding:12px 24px;border-radius:12px;font-size:13px;">Open Cached Pass</a>' +
            '</div>',
            { headers: { 'Content-Type': 'text/html' } }
          );
        })
    );
    return;
  }

  // B. Static Assets (CSS, Scripts, Images, Fonts)
  if (
    request.destination === 'style' ||
    request.destination === 'script' ||
    request.destination === 'image' ||
    request.destination === 'font'
  ) {
    event.respondWith(
      caches.match(request).then((cachedResponse) => {
        if (cachedResponse) {
          fetch(request)
            .then((networkResponse) => {
              if (networkResponse && networkResponse.status === 200) {
                caches.open(CACHE_NAME).then((cache) => cache.put(request, networkResponse));
              }
            })
            .catch(() => {});
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
  event.respondWith(fetch(request).catch(() => caches.match(request)));
});
