const ASSET_VERSION = "cms-3";
const CACHE_NAME = `meet-aj-v2.0.0-${ASSET_VERSION}`;
const PRIVATE_PREFIXES = ["/admin", "/livewire", "/forms", "/storage/livewire-tmp"];

self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(["/", "/manifest.json", "/offline.html"])).then(() => self.skipWaiting())
  );
});

self.addEventListener("activate", (event) => {
  event.waitUntil((async () => {
    const keys = await caches.keys();
    await Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)));
    if (self.registration.navigationPreload) {
      await self.registration.navigationPreload.enable();
    }
    await self.clients.claim();
  })());
});

function isPrivate(url) {
  const parsed = new URL(url);
  const path = parsed.pathname;
  return PRIVATE_PREFIXES.some((prefix) => path === prefix || path.startsWith(prefix + "/") || path.startsWith(prefix + "-"))
    || path.endsWith(".php")
    || parsed.searchParams.has("signature");
}

self.addEventListener("fetch", (event) => {
  const request = event.request;
  if (request.method !== "GET" || !request.url.startsWith(self.location.origin) || isPrivate(request.url) || request.headers.get("authorization")) {
    return;
  }
  const destination = request.destination;
  const isDocument = destination === "document";
  const isAsset = /\.(css|js|png|jpg|jpeg|gif|webp|svg|woff|woff2|ico)$/i.test(new URL(request.url).pathname);

  if (isDocument) {
    event.respondWith((async () => {
      try {
        const response = await fetch(request);
        const cacheControl = response.headers.get("cache-control") || "";
        if (response.ok && !cacheControl.includes("no-store")) {
          const cache = await caches.open(CACHE_NAME);
          cache.put(request, response.clone());
        }
        if (response.status === 404 || response.status === 410) {
          const cache = await caches.open(CACHE_NAME);
          await cache.delete(request);
        }
        return response;
      } catch (error) {
        return (await caches.match(request)) || (await caches.match("/")) || (await caches.match("/offline.html"));
      }
    })());
    return;
  }

  if (isAsset) {
    event.respondWith((async () => {
      const cache = await caches.open(CACHE_NAME);
      const cached = await cache.match(request);
      const network = fetch(request).then((response) => {
        if (response.ok) cache.put(request, response.clone());
        return response;
      }).catch(() => cached);
      return cached || network;
    })());
  }
});