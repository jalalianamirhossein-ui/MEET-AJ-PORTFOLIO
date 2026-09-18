<?php

namespace App\Services;

class LegacySitePublisher
{
    public function publishAssets(): array
    {
        $copied = [];
        $this->copyDirectory(base_path('assets'), public_path('assets'), $copied);
        foreach (['manifest.json' => 'manifest.json', 'preloader.html' => 'preloader.html', 'preloader.css' => 'preloader.css'] as $from => $to) {
            $this->copyFile(base_path($from), public_path($to), $copied);
        }
        $this->copyFile(base_path('partials/lang-toggle.html'), public_path('partials/lang-toggle.html'), $copied);
        $this->copyFile(base_path('docs/netbox_installation_guide_v2.pdf'), public_path('docs/netbox_installation_guide_v2.pdf'), $copied);
        $this->writeServiceWorker();
        $copied[] = 'public/sw.js';

        $forbidden = [
            public_path('articles'),
            public_path('services'),
            public_path('sitemap.xml'),
            public_path('robots.txt'),
            public_path('forms'),
            public_path('index.html'),
        ];
        foreach ($forbidden as $path) {
            if (is_file($path) || is_dir($path)) {
                throw new \RuntimeException('Forbidden public path present: '.$path);
            }
        }

        return $copied;
    }

    public function buildViews(): array
    {
        $written = [];
        $written[] = $this->writeHome();
        $written[] = $this->writeArticleIndex();

        return $written;
    }

    private function writeHome(): string
    {
        $html = file_get_contents(base_path('index.html'));
        if ($html === false) {
            throw new \RuntimeException('Unable to read index.html');
        }
        $html = $this->toBlade($html);
        $html = $this->injectCsrfTokens($html);
        $html = $this->replacePortfolioGrid($html);
        $html = $this->replaceServicesGrid($html);
        $html = $this->replaceTestimonials($html);
        $html = $this->injectArticleLibrary($html, false);
        $target = resource_path('views/home.blade.php');
        file_put_contents($target, $html);

        return $target;
    }

    private function writeArticleIndex(): string
    {
        $html = file_get_contents(base_path('index.html'));
        if ($html === false) {
            throw new \RuntimeException('Unable to read index.html');
        }
        $start = strpos($html, '<section id="portfolio"');
        $end = strpos($html, '<!-- End Articles Section -->');
        if ($start === false || $end === false) {
            throw new \RuntimeException('Unable to extract portfolio section');
        }
        $section = substr($html, $start, $end - $start);
        $chrome = $this->articleListingChrome($html);
        $footer = $this->sliceInclusive($html, '<footer id="footer"', '</footer>');
        $assembled = $chrome."\n    <main id=\"main-content\" class=\"main\" role=\"main\">\n".$section."\n    </main>\n".$footer;
        $assembled = $this->toBlade($assembled);
        $assembled = $this->replacePortfolioGrid($assembled);
        $assembled = $this->injectArticleLibrary($assembled, true);
        $page = <<<'BLADE'
<!doctype html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Articles | Meet AJ</title>
    <meta name="description" content="Technical articles by AmirHossein Jalalian covering Linux, Microsoft, MikroTik, VMware and infrastructure." />
    <link rel="canonical" href="{{ rtrim(config('app.url'), '/') }}/articles" />
    <meta property="og:title" content="Articles | Meet AJ" />
    <meta property="og:url" content="{{ rtrim(config('app.url'), '/') }}/articles" />
    <link href="/assets/img/favicon.png" rel="icon" />
    <link rel="manifest" href="/manifest.json" />
    <link href="/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet" />
    <link href="/assets/vendor/aos/aos.css" rel="stylesheet" />
    <link href="/assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet" />
    <link href="/assets/css/main.css?v=1000" rel="stylesheet" />
    <link href="/assets/css/lang-toggle.css?v=1400" rel="stylesheet" />
    <link id="rtl-style" href="/assets/css/rtl.css?v=1000" rel="stylesheet" disabled />
    <link href="/assets/css/visual-upgrade.css?v=1705" rel="stylesheet" />
    <link href="/assets/css/site-modules.css?v=1819" rel="stylesheet" />
  </head>
  <body class="index-page articles-index-page">
BLADE;
        $page .= $assembled;
        $page .= <<<'BLADE'
    <script src="/assets/vendor/bootstrap/js/bootstrap.bundle.min.js" defer></script>
    <script src="/assets/vendor/aos/aos.js" defer></script>
    <script src="/assets/vendor/glightbox/js/glightbox.min.js" defer></script>
    <script src="/assets/vendor/imagesloaded/imagesloaded.pkgd.min.js" defer></script>
    <script src="/assets/vendor/isotope-layout/isotope.pkgd.min.js" defer></script>
    <script src="/assets/js/contact-form.js?v=1403" defer></script>
    <script src="/assets/js/main.js?v=1404" defer></script>
    <script src="/assets/js/service-catalog.js?v=1813" defer></script>
    <script src="/assets/js/i18n.js?v=1400" defer></script>
    <script>
      if ("serviceWorker" in navigator) {
        window.addEventListener("load", function () {
          navigator.serviceWorker.register("/sw.js").catch(function () {});
        });
      }
    </script>
  </body>
</html>
BLADE;
        $target = resource_path('views/articles/index.blade.php');
        if (! is_dir(dirname($target))) {
            mkdir(dirname($target), 0755, true);
        }
        file_put_contents($target, $page);

        return $target;
    }

    private function articleListingChrome(string $html): string
    {
        $start = strpos($html, '<a class="skip-link"');
        $toggle = strpos($html, 'id="menu-toggle"');
        $endBtn = $toggle === false ? false : strpos($html, '</button>', $toggle);
        if ($start === false || $endBtn === false) {
            throw new \RuntimeException('Unable to extract listing chrome from index.html');
        }
        $chrome = substr($html, $start, $endBtn + strlen('</button>') - $start);
        $chrome = str_replace('href="#hero"', 'href="/#hero"', $chrome);
        $chrome = str_replace('href="#about"', 'href="/#about"', $chrome);
        $chrome = str_replace('href="#resume"', 'href="/#resume"', $chrome);
        $chrome = str_replace('href="#services"', 'href="/#services"', $chrome);
        $chrome = str_replace('href="#testimonials"', 'href="/#testimonials"', $chrome);
        $chrome = str_replace('href="#contact"', 'href="/#contact"', $chrome);
        $chrome = str_replace(' class="active" aria-current="page"', '', $chrome);
        $chrome = str_replace(
            'href="#portfolio"',
            'href="/articles" class="active" aria-current="page"',
            $chrome
        );
        $chrome = str_replace('href="#main-content"', 'href="#portfolio"', $chrome);

        return $chrome;
    }

    private function sliceInclusive(string $html, string $startNeedle, string $endNeedle): string
    {
        $from = strpos($html, $startNeedle);
        $to = $from === false ? false : strpos($html, $endNeedle, $from);
        if ($from === false || $to === false) {
            throw new \RuntimeException('Unable to slice '.$startNeedle);
        }

        return substr($html, $from, $to + strlen($endNeedle) - $from);
    }

    private function replaceServicesGrid(string $html): string
    {
        if (str_contains($html, '@forelse ($services as $service)')) {
            return $html;
        }

        $end = strpos($html, '<!-- End Service Catalog -->');
        $marker = strpos($html, 'id="service-catalog"');
        if ($end === false || $marker === false) {
            throw new \RuntimeException('Unable to locate homepage service catalog');
        }
        $open = strrpos(substr($html, 0, $marker), '<div');
        if ($open === false) {
            throw new \RuntimeException('Unable to locate service catalog opening tag');
        }
        $loop = <<<'BLADE'
@endverbatim
          <div class="row gy-4" id="service-catalog">
            @forelse ($services as $service)
              @include('components.service-card', ['service' => $service])
            @empty
            @endforelse
          </div>
          @include('partials.service-drawer')
          <!-- End Service Catalog -->
@verbatim
BLADE;

        return substr($html, 0, $open).$loop.substr($html, $end + strlen('<!-- End Service Catalog -->'));
    }

    private function replaceTestimonials(string $html): string
    {
        $include = <<<'BLADE'
@endverbatim
      @include('partials.testimonials')
@verbatim
      <!-- /Testimonials Section -->
BLADE;

        if (str_contains($html, "@include('partials.testimonials')")) {
            if (! str_contains($html, "@endverbatim\n      @include('partials.testimonials')")
                && ! str_contains($html, "@endverbatim\r\n      @include('partials.testimonials')")) {
                return str_replace("@include('partials.testimonials')", $include, $html);
            }

            return $html;
        }

        $start = strpos($html, '<section id="testimonials"');
        $end = strpos($html, '<!-- /Testimonials Section -->');
        if ($start === false || $end === false) {
            throw new \RuntimeException('Unable to locate testimonials section');
        }

        return substr($html, 0, $start).$include.substr($html, $end + strlen('<!-- /Testimonials Section -->'));
    }

    private function replacePortfolioGrid(string $html): string
    {
        if (str_contains($html, '@foreach ($articles as $article)')) {
            return $html;
        }

        $startNeedle = 'class="row gy-4 isotope-container"';
        $start = strpos($html, $startNeedle);
        $end = strpos($html, '<!-- End Articles Grid -->');
        if ($start === false || $end === false) {
            throw new \RuntimeException('Unable to locate homepage article grid');
        }
        $open = strrpos(substr($html, 0, $start), '<div');
        $loop = <<<'BLADE'
@endverbatim
            <div
              class="row gy-4 isotope-container"
              data-aos="fade-up"
              data-aos-delay="200"
            >
              @foreach ($articles as $article)
                @include('components.article-card', ['article' => $article])
              @endforeach
            </div>
            <!-- End Articles Grid -->
@verbatim
BLADE;

        return substr($html, 0, $open).$loop.substr($html, $end + strlen('<!-- End Articles Grid -->'));
    }

    private function replaceCategoryFilters(string $html): string
    {
        if (str_contains($html, "@include('articles.partials.library-toolbar'")) {
            return $html;
        }

        $startNeedle = '==================== FILTER BUTTONS =================';
        $endNeedle = '<!-- End Filter Buttons -->';
        $start = strpos($html, $startNeedle);
        $end = strpos($html, $endNeedle);
        if ($start === false || $end === false) {
            throw new \RuntimeException('Unable to locate article category filters');
        }

        $commentOpen = strrpos(substr($html, 0, $start), '<!--');
        if ($commentOpen === false) {
            throw new \RuntimeException('Unable to locate article category filter comment');
        }

        $include = <<<'BLADE'
@endverbatim
            @include('articles.partials.library-toolbar', ['showCategoryFilters' => true])
@verbatim
            <!-- End Filter Buttons -->
BLADE;

        return substr($html, 0, $commentOpen).$include.substr($html, $end + strlen($endNeedle));
    }

    private function injectArticleLibrary(string $html, bool $allowSearchResults): string
    {
        if ($allowSearchResults && ! str_contains($html, '@if ($searching)')) {
            $toolbar = <<<'BLADE'
        <!-- End Section Title -->
@endverbatim
        @if ($searching)
          @include('articles.partials.library-toolbar')
          @include('articles.partials.search-results')
        @else
@verbatim

        <!-- ===============================================
        ==================== ARTICLES CONTAINER ==============
BLADE;
            $html = preg_replace(
                '/        <!-- End Section Title -->\r?\n\r?\n        <!-- ===============================================\r?\n        ==================== ARTICLES CONTAINER ==============/',
                $toolbar,
                $html,
                1,
                $count
            );
            if (! is_string($html) || $count !== 1) {
                throw new \RuntimeException('Unable to inject article library toolbar');
            }
        }

        $html = $this->replaceCategoryFilters($html);

        if (! $allowSearchResults) {
            return $html;
        }

        if (preg_match('/@endif\s*@verbatim\s*<!-- End Main Container -->/', preg_replace('/\s+/', ' ', $html) ?? $html)) {
            return $html;
        }

        $close = <<<'BLADE'
          <!-- End Articles Container -->
        </div>
@endverbatim
        @endif
@verbatim
        <!-- End Main Container -->
BLADE;
        $html = preg_replace(
            '/          <!-- End Articles Container -->\r?\n        <\/div>\r?\n        <!-- End Main Container -->/',
            $close,
            $html,
            1,
            $closed
        );
        if (! is_string($html) || $closed !== 1) {
            throw new \RuntimeException('Unable to close article search results branch');
        }

        return $html;
    }

    public function toBlade(string $html): string
    {
        $html = preg_replace('/\xEF\xBB\xBF/', '', $html) ?? $html;
        $html = preg_replace('/^\s*@verbatim\s*/', '', $html) ?? $html;
        $html = preg_replace('/\s*@endverbatim\s*$/', '', $html) ?? $html;
        $html = preg_replace('/^\s*\?\s*(?=<!doctype)/i', '', $html) ?? $html;
        $html = str_replace('../assets/', '/assets/', $html);
        $html = preg_replace('#(?<![\w./])assets/#', '/assets/', $html) ?? $html;
        $html = str_replace('href="../index.html', 'href="/', $html);
        $html = str_replace('href="index.html', 'href="/', $html);
        $html = str_replace('action="forms/contact.php"', 'action="/forms/contact.php"', $html);
        $html = str_replace('href="manifest.json"', 'href="/manifest.json"', $html);
        $html = str_replace('register("sw.js")', 'register("/sw.js")', $html);
        $html = preg_replace('#href="(?:\.\./)?articles/([a-z0-9-]+)\.html#', 'href="/articles/$1', $html) ?? $html;
        $html = preg_replace('#href="(?:\.\./)?services/([a-z0-9-]+)\.html#', 'href="/services/$1', $html) ?? $html;

        // Keep original @ and {{ characters. Escaping every @ as @@ left
        // mailto: addresses as @@gmail in the compiled HTML because Blade
        // treats @gmail as a directive. @verbatim preserves JSON-LD @type too.
        return "@verbatim\n".$html."\n@endverbatim";
    }

    private function injectCsrfTokens(string $html): string
    {
        if (! str_contains($html, 'content="{{ csrf_token() }}"')) {
            $meta = <<<'BLADE'
@endverbatim
    <meta name="csrf-token" content="{{ csrf_token() }}">
@verbatim
BLADE;
            $html = preg_replace('/<meta charset="utf-8"\s*\/?>/i', '$0'.$meta, $html, 1, $metaCount);
            if (! is_string($html) || $metaCount !== 1) {
                throw new \RuntimeException('Unable to inject CSRF meta tag');
            }
        }

        $input = <<<'BLADE'
@endverbatim
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="csrf_token" id="csrf_token" value="{{ csrf_token() }}">
@verbatim
BLADE;

        if (str_contains($html, 'name="_token"') && str_contains($html, 'value="{{ csrf_token() }}"')) {
            return $html;
        }

        $html = preg_replace(
            '/<input\s+type="hidden"\s+name="csrf_token"[^>]*>/s',
            $input,
            $html,
            1,
            $replaced
        );
        if (! is_string($html) || $replaced !== 1) {
            throw new \RuntimeException('Unable to inject CSRF form fields');
        }

        return $html;
    }

    private function writeServiceWorker(): void
    {
        $js = <<<'JS'
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
JS;
        file_put_contents(public_path('sw.js'), $js);
        $offline = <<<'HTML'
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><title>Offline | Meet AJ</title>
<meta name="robots" content="noindex"></head>
<body><h1>You are offline</h1><p>Reconnect to load the latest Meet AJ pages.</p></body></html>
HTML;
        file_put_contents(public_path('offline.html'), $offline);
    }

    private function copyDirectory(string $from, string $to, array &$copied): void
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($from, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            /** @var \SplFileInfo $file */
            $target = $to.substr($file->getPathname(), strlen($from));
            if (! is_dir(dirname($target))) {
                mkdir(dirname($target), 0755, true);
            }
            copy($file->getPathname(), $target);
            $copied[] = $target;
        }
    }

    private function copyFile(string $from, string $to, array &$copied): void
    {
        if (! is_file($from)) {
            return;
        }
        if (! is_dir(dirname($to))) {
            mkdir(dirname($to), 0755, true);
        }
        copy($from, $to);
        $copied[] = $to;
    }
}
