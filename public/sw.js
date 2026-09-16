// Retire the legacy document caches before the CMS takes control.
// Network-only keeps authenticated pages, tokens and publication changes fresh.
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', event => {
  event.waitUntil((async () => {
    const keys = await caches.keys();
    await Promise.all(keys.filter(key => key.startsWith('meet-aj-')).map(key => caches.delete(key)));
    await self.clients.claim();
  })());
});
