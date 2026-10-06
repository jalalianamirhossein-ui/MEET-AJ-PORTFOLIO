/**
 * ===============================================
 * MEET AJ PORTFOLIO - SERVICE WORKER
 * ===============================================
 *
 * Progressive Web App (PWA) service worker
 * Handles offline functionality and caching strategies
 *
 * Features:
 * - Offline-first caching for static assets
 * - Automatic cache updates
 * - Performance optimization
 * - Dynamic form/API endpoints are NEVER cached
 *
 * Version: 1.0.7
 * Author: AmirHossein Jalalian
 * ===============================================
 */

// Cache configuration
const ASSET_VERSION = "1001";
const CACHE_NAME = `meet-aj-v1.0.7-${ASSET_VERSION}`;
const OFFLINE_FALLBACK_CACHE = `meet-aj-offline-${ASSET_VERSION}`;

const versionedAssets = [
  "./assets/css/main.css",
  "./assets/css/rtl.css",
  "./assets/css/lang-toggle.css",
  "./assets/js/main.js",
  "./assets/js/i18n.js",
];

// Core assets that must be cached for offline functionality
const coreAssets = [
  "./",
  "./index.html",
  ...versionedAssets,
  ...versionedAssets.map((url) => `${url}?v=${ASSET_VERSION}`),
  "./assets/img/icons/favicon.png",
  "./assets/img/icons/apple-touch-icon.png",
  "./assets/img/brand/logo.png",
  "./assets/img/banners/site/hero-bg.jpg",
  "./assets/img/avatars/profile/my-profile-img.jpg",
  "./assets/img/avatars/profile/my-profile-img-2.jpg",
  "./assets/vendor/bootstrap/css/bootstrap.min.css",
  "./assets/vendor/bootstrap-icons/bootstrap-icons.css",
  "./assets/vendor/bootstrap/js/bootstrap.bundle.min.js",
  "./assets/vendor/aos/aos.css",
  "./assets/vendor/aos/aos.js",
  "./assets/vendor/glightbox/css/glightbox.min.css",
  "./assets/vendor/glightbox/js/glightbox.min.js",
  "./assets/vendor/swiper/swiper-bundle.min.css",
  "./assets/vendor/swiper/swiper-bundle.min.js",
  "./assets/vendor/typed.js/typed.umd.js",
  "./assets/vendor/purecounter/purecounter_vanilla.js",
  "./assets/vendor/waypoints/noframework.waypoints.js",
  "./assets/vendor/imagesloaded/imagesloaded.pkgd.min.js",
  "./assets/vendor/isotope-layout/isotope.pkgd.min.js",
  "./manifest.json",
];

// Portfolio images - cached on demand with stale-while-revalidate
const portfolioImages = [
  "./assets/img/articles/banners/nginx-installation-configuration-ubuntu.png",
  "./assets/img/articles/banners/enable-ssh-linux-complete-guide.png",
  "./assets/img/articles/banners/linux-cli-common-commands.png",
  "./assets/img/articles/banners/ubuntu-date-time-settings.png",
  "./assets/img/articles/banners/set-static-ip-ubuntu-server-netplan.png",
  "./assets/img/articles/banners/linux-security-account-access-management.png",
  "./assets/img/articles/banners/mikrotik-block-website.png",
  "./assets/img/articles/banners/mikrotik-unequal-dual-wan-load-balancing-ecmp.png",
  "./assets/img/articles/banners/mikrotik-block-port-scanners.png",
  "./assets/img/articles/banners/mikrotik-openvpn-setup-v7.png",
  "./assets/img/articles/banners/downgrade-mikrotik-routeros-firmware-safely.png",
  "./assets/img/articles/banners/install-mikrotik-chr-vmware-workstation.png",
  "./assets/img/articles/banners/creating-a-bootable-usb.png",
  "./assets/img/articles/banners/http-vs-https-ssl-certificate-impact.png",
  "./assets/img/articles/banners/imap-vs-pop3-email-protocol-comparison.png",
  "./assets/img/articles/banners/vmware-esxi-8-installation-basic-configuration.png",
  "./assets/img/articles/banners/install-vmware-esxi-vmware-workstation-vmcisr.png",
  "./assets/img/articles/banners/vsphere-standard-switch-vs-distributed-switch.png",
  "./assets/img/articles/banners/windows-cmd-common-network-commands.png",
  "./assets/img/articles/banners/windows-hardware-info-cmd-vs-dxdiag.png",
  "./assets/img/articles/banners/windows-password-reset-secure-access-recovery.png",
  "./assets/img/articles/banners/sql-server-automatic-backup-job.png",
  "./assets/img/articles/banners/install-dfs-server-windows-server.png",
];

// Install Event - Cache Core Resources First
self.addEventListener("install", function (event) {
  event.waitUntil(
    caches
      .open(CACHE_NAME)
      .then(function (cache) {
        // Cache core assets first - critical for offline
        return cache.addAll(coreAssets);
      })
      .then(function () {
        // Portfolio images are cached on demand by the static-asset strategy.
        // Avoid fetching ~30 MB during installation on a visitor's first load.
        console.log("Service Worker installed successfully");
        return self.skipWaiting();
      })
      .catch(function (error) {
        // Core cache installation is mandatory - propagate failure
        console.error("Core cache installation failed:", error);
        throw error;
      }),
  );
});

// Fetch Event - Stale-While-Revalidate for Static Assets, Network-First for Documents
self.addEventListener("fetch", function (event) {
  // Skip non-GET requests (POST contact form submissions must reach the server)
  if (event.request.method !== "GET") {
    return;
  }

  // Skip cross-origin requests
  if (!event.request.url.startsWith(self.location.origin)) {
    return;
  }

  const url = new URL(event.request.url);

  // NEVER cache dynamic PHP endpoints (CSRF tokens, contact form, etc.)
  if (url.pathname.startsWith("/forms/") || url.pathname.endsWith(".php")) {
    return;
  }

  const isPortfolioImage = portfolioImages.some((img) =>
    url.pathname.endsWith(img.replace("./", "")),
  );
  const isStaticAsset =
    /\.(css|js|png|jpg|jpeg|gif|webp|svg|woff|woff2|ico)$/i.test(url.pathname);
  const isHtmlDocument =
    event.request.destination === "document" || url.pathname.endsWith(".html");

  // Strategy 1: Network-First for HTML documents (with offline fallback)
  if (isHtmlDocument) {
    event.respondWith(
      (async function () {
        // Use navigation preload if available for faster response
        if (self.registration.navigationPreload) {
          const preloadResponse = await event.preloadResponse;
          if (preloadResponse) {
            return preloadResponse;
          }
        }

        // Network-first with cache fallback
        try {
          const networkResponse = await fetch(event.request);
          if (networkResponse.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(event.request, networkResponse.clone());
          }
          return networkResponse;
        } catch (error) {
          // Network failed - return cached if available
          const cachedResponse = await caches.match(event.request);
          if (cachedResponse) {
            return cachedResponse;
          }
          // Return offline fallback
          return caches.match("./index.html");
        }
      })(),
    );
    return;
  }

  // Strategy 2: Stale-While-Revalidate for static assets and portfolio images
  if (isStaticAsset || isPortfolioImage) {
    event.respondWith(
      (async function () {
        const cache = await caches.open(CACHE_NAME);
        const cachedResponse = await cache.match(event.request);

        // Fetch from network in background
        const networkFetch = fetch(event.request)
          .then(function (networkResponse) {
            if (networkResponse.ok) {
              cache.put(event.request, networkResponse.clone());
            }
            return networkResponse;
          })
          .catch(function () {
            // Network failed - return cached if available
            return cachedResponse;
          });

        // Return cached immediately if available, otherwise wait for network
        return cachedResponse || networkFetch;
      })(),
    );
    return;
  }

  // Strategy 3: Cache-First for everything else
  event.respondWith(
    caches
      .match(event.request)
      .then(function (response) {
        if (response) {
          return response;
        }

        return fetch(event.request).then(function (response) {
          if (
            !response ||
            response.status !== 200 ||
            response.type !== "basic"
          ) {
            return response;
          }

          var responseToCache = response.clone();
          caches.open(CACHE_NAME).then(function (cache) {
            cache.put(event.request, responseToCache);
          });

          return response;
        });
      })
      .catch(function () {
        if (event.request.destination === "document") {
          return caches.match("./index.html");
        }
      }),
  );
});

// Activate Event - Clean Up Old Caches and Enable Navigation Preload
self.addEventListener("activate", function (event) {
  event.waitUntil(
    (async function () {
      // Clean up old caches
      const cacheNames = await caches.keys();
      await Promise.all(
        cacheNames.map(function (cacheName) {
          if (
            cacheName !== CACHE_NAME &&
            cacheName !== OFFLINE_FALLBACK_CACHE
          ) {
            console.log("Deleting old cache:", cacheName);
            return caches.delete(cacheName);
          }
        }),
      );

      // Enable Navigation Preload (if supported)
      if ("navigationPreload" in self.registration) {
        try {
          await self.registration.navigationPreload.enable();
        } catch (error) {
          console.warn("Navigation preload not supported:", error);
        }
      }

      // Take control of all clients immediately
      await self.clients.claim();
    })(),
  );
});

// Message Event - Handle Messages from Main Thread
self.addEventListener("message", function (event) {
  if (event.data && event.data.type === "SKIP_WAITING") {
    self.skipWaiting();
  }

  // Allow clients to request cache cleanup
  if (event.data && event.data.type === "CLEAR_CACHE") {
    caches.delete(CACHE_NAME).then(function () {
      event.ports[0].postMessage({ success: true });
    });
  }
});

// Periodic cache cleanup (optional - runs when SW is active)
self.addEventListener("periodicsync", function (event) {
  if (event.tag === "cache-cleanup") {
    event.waitUntil(cleanupOldCacheEntries());
  }
});

async function cleanupOldCacheEntries() {
  const cache = await caches.open(CACHE_NAME);
  const keys = await cache.keys();
  const now = Date.now();
  const MAX_AGE = 30 * 24 * 60 * 60 * 1000; // 30 days

  for (const request of keys) {
    const response = await cache.match(request);
    const dateHeader = response.headers.get("date");
    if (dateHeader) {
      const cacheDate = new Date(dateHeader).getTime();
      if (now - cacheDate > MAX_AGE) {
        await cache.delete(request);
      }
    }
  }
}
