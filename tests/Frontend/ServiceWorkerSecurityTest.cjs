const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

async function run() {
  const handlers = {};
  const cached = [];
  let precache;
  let response;
  const cache = {
    addAll: async (urls) => { precache = urls; },
    put: async (request) => { cached.push(request.url); },
    match: async () => undefined,
    delete: async () => true,
  };
  const context = {
    URL, Response,
    self: {
      location: { origin: 'https://meetaj.ir' },
      addEventListener: (event, handler) => { handlers[event] = handler; },
      skipWaiting: async () => {},
    },
    caches: { open: async () => cache, match: async () => undefined },
    fetch: async () => response,
  };
  vm.runInNewContext(fs.readFileSync('public/sw.js', 'utf8'), context);
  let installation;
  handlers.install({ waitUntil: (promise) => { installation = promise; } });
  await installation;
  assert(!precache.includes('/'), 'Session-specific homepage must not be precached.');

  async function request(url, cacheControl = '', responseUrl = url, destination = 'script') {
    let intercepted;
    response = {
      ok: true, status: 200, url: responseUrl,
      headers: { get: (key) => key === 'cache-control' ? cacheControl : null },
      clone() { return this; },
    };
    handlers.fetch({
      request: { url, method: 'GET', destination, headers: { get: () => null } },
      respondWith: (promise) => { intercepted = promise; },
    });
    if (intercepted) await intercepted;
    return Boolean(intercepted);
  }

  assert.equal(await request('https://meetaj.ir.attacker.example/asset.js'), false);
  assert.equal(await request('https://meetaj.ir/admin/login', '', undefined, 'document'), false);
  assert.equal(await request('https://meetaj.ir/livewire/update'), false);
  assert.equal(await request('https://meetaj.ir/assets/no-cache.js', 'no-store'), true);
  assert.equal(cached.length, 0);
  await request('https://meetaj.ir/', 'no-store', undefined, 'document');
  assert.equal(cached.length, 0);
  await request('https://meetaj.ir/assets/redirect.js', '', 'https://meetaj.ir/admin/login');
  assert.equal(cached.length, 0);
  await request('https://meetaj.ir/assets/public.js', 'public, max-age=3600');
  assert.deepEqual(cached, ['https://meetaj.ir/assets/public.js']);
  console.log('Service worker security checks passed.');
}

run().catch((error) => { console.error(error); process.exitCode = 1; });
