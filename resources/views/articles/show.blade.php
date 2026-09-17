<!doctype html>
<html lang="en" dir="ltr">
  <head>
    <title>{{ $seo['title'] }}</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="{{ $seo['description'] }}" />
    <meta name="author" content="AmirHossein Jalalian" />
    <meta name="robots" content="{{ $seo['robots'] }}" />
    <link rel="canonical" href="{{ $seo['canonical'] }}" />
    <meta property="og:type" content="{{ $seo['og_type'] }}" />
    <meta property="og:title" content="{{ $seo['og_title'] }}" />
    <meta property="og:description" content="{{ $seo['og_description'] }}" />
    <meta property="og:url" content="{{ $seo['og_url'] }}" />
    <meta property="og:site_name" content="Meet AJ" />
    <meta property="og:image" content="{{ $seo['og_image'] }}" />
    <meta name="twitter:card" content="{{ $seo['twitter_card'] }}" />
    @if (!empty($seo['twitter_title']))
      <meta name="twitter:title" content="{{ $seo['twitter_title'] }}" />
    @endif
    @if (!empty($seo['twitter_description']))
      <meta name="twitter:description" content="{{ $seo['twitter_description'] }}" />
    @endif
    @if (!empty($seo['twitter_image']))
      <meta name="twitter:image" content="{{ $seo['twitter_image'] }}" />
    @endif
    <link href="/assets/img/favicon.png" rel="icon" />
    <link rel="manifest" href="/manifest.json" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Vazirmatn:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet" />
    <link href="/assets/vendor/aos/aos.css" rel="stylesheet" />
    <link href="/assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet" />
    <link href="/assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet" />
    <link href="/assets/css/main.css?v=1000" rel="stylesheet" />
    <link href="/assets/css/articles.css?v=1013" rel="stylesheet" />
    <link href="/assets/css/lang-toggle.css?v=1116" rel="stylesheet" />
    <link id="rtl-style" href="/assets/css/rtl.css?v=1000" rel="stylesheet" disabled />
    <link href="/assets/css/visual-upgrade.css?v=1314" rel="stylesheet" />
    <script type="application/ld+json">{!! json_encode($seo['schema'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    @if (!empty($seo['breadcrumb']))
      <script type="application/ld+json">{!! json_encode($seo['breadcrumb'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    @endif
  </head>
  <body class="{{ data_get($article->presentation, 'body_class', 'article-page theme-other') }}">
    <a href="#main-content" class="skip-link sr-only sr-only-focusable"
      ><span data-en="Skip to main content" data-fa="رفتن به محتوای اصلی">Skip to main content</span></a
    >
    <button id="menu-toggle" class="menu-toggle d-xl-none" aria-label="Open menu" aria-expanded="false" aria-controls="header" type="button">
      <span class="menu-toggle-bars" aria-hidden="true"><span></span><span></span><span></span></span>
      <span class="sr-only" data-en="Open menu" data-fa="باز کردن منو">Open menu</span>
    </button>
    <header id="header" class="header dark-background d-flex flex-column">
      <div class="profile-img">
        <img src="/assets/img/my-profile-img.jpg" loading="lazy" alt="AmirHossein Jalalian Profile Picture" class="img-fluid rounded-circle" />
      </div>
      <div class="logo-section d-flex align-items-center justify-content-between">
        <a href="/#hero" class="logo d-flex align-items-center">
          <img src="/assets/img/logo.png" alt="Aj-Network" loading="lazy" />
          <p class="sitename">Meet AJ</p>
        </a>
      </div>
      <div class="social-links text-center">
        <div class="social-row social-row-main">
          <a href="https://www.linkedin.com/in/amirhussein-jalalian-050702188/" class="linkedin" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
          <a href="https://github.com/jalalianamirhossein-ui" class="github" target="_blank" rel="noopener" aria-label="GitHub"><i class="bi bi-github"></i></a>
          <a href="https://wa.me/989197276219" class="whatsapp" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
          <a href="https://t.me/Aj_mercury" class="telegram" target="_blank" rel="noopener" aria-label="Telegram"><i class="bi bi-telegram"></i></a>
        </div>
        <div class="social-row social-row-secondary">
          <a href="https://twitter.com/RealAjMercury" class="twitter" target="_blank" rel="noopener" aria-label="X (Twitter)"
            ><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a>
          <a href="https://stackoverflow.com/users/24522280/amir-jalalian" class="stackoverflow" target="_blank" rel="noopener" aria-label="Stack Overflow"><i class="bi bi-stack-overflow"></i></a>
          <a href="https://www.facebook.com/amir.jalalian.37" class="facebook" target="_blank" rel="noopener" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
          <a href="https://instagram.com/aj.mercury" class="instagram" target="_blank" rel="noopener" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
          <a href="tel:+989197276219" class="google-plus" aria-label="Phone"><i class="bi bi-telephone"></i></a>
        </div>
      </div>
      <nav id="navmenu" class="navmenu navmenu--article-toc" aria-label="Article navigation">
        <ul>
          <li>
            <a href="/#hero"><i class="bi bi-house navicon" aria-hidden="true"></i><span data-en="Home" data-fa="خانه">Home</span></a>
          </li>
          <li>
            <a href="/#portfolio"><i class="bi bi-journal-text navicon" aria-hidden="true"></i><span data-en="Articles" data-fa="مقالات">Articles</span></a>
          </li>
          {!! data_get($article->presentation, 'toc_html') !!}
        </ul>
      </nav>
    </header>
    <main id="main-content" class="main" role="main">
      <section class="article-hero article-header hero" role="banner">
        <div class="container">
          <div class="row">
            <div class="col-lg-10 mx-auto">
              @include('articles.partials.breadcrumbs')
              <div class="article-meta">
                <span class="article-category" data-en="{{ data_get($article->presentation, 'category_label_en', $article->category->name ?? 'Article') }}" data-fa="{{ data_get($article->presentation, 'category_label_fa', $article->category->name ?? 'مقاله') }}">{{ data_get($article->presentation, 'category_label_en', $article->category->name ?? 'Article') }}</span>
                @if ($article->published_at)
                  <time class="meta-date article-date" datetime="{{ $article->published_at->toAtomString() }}">{{ $article->published_at->timezone(config('cms.display_timezone', config('app.timezone')))->format('M j, Y') }}</time>
                @endif
                <span class="article-readtime">{{ $article->readingMinutes() }} <span data-en="min read" data-fa="دقیقه مطالعه">min read</span></span>
              </div>
              @if ($article->tags->isNotEmpty())
                <ul class="article-tags">
                  @foreach ($article->tags as $tag)
                    <li><a href="{{ $tag->path() }}">{{ $tag->name }}</a></li>
                  @endforeach
                </ul>
              @endif
              <h1 class="article-title hero-title" data-en="{{ data_get($article->presentation, 'hero_title_en', $article->title) }}" data-fa="{{ data_get($article->presentation, 'hero_title_fa', $article->title) }}">{{ $article->title }}</h1>
              <p class="article-excerpt hero-subtitle" data-en="{{ $article->excerpt }}" data-fa="{{ data_get($article->presentation, 'excerpt_translations.fa', $article->excerpt) }}">{{ $article->excerpt }}</p>
              <div class="article-banner">
                <div class="banner-card">
                  <img class="article-hero-thumbnail" src="{{ $article->galleryUrl() }}" alt="{{ data_get($article->presentation, 'image_alt') ?: $article->title }}" />
                  <div class="banner-content">
                    <h2 class="banner-title" data-en="{{ data_get($article->presentation, 'category_label_en') }}" data-fa="{{ data_get($article->presentation, 'category_label_fa') }}">{{ data_get($article->presentation, 'category_label_en') }}</h2>
                    <p class="banner-subtitle" data-en="Meet AJ technical article" data-fa="مقاله فنی Meet AJ">Meet AJ technical article</p>
                  </div>
                </div>
              </div>
              <div class="article-actions">
                <a href="#article-content" class="btn btn-primary btn-lg action-btn"><span class="btn-content"><i class="bi bi-play-circle" aria-hidden="true"></i><span data-en="Start reading" data-fa="شروع مطالعه">Start reading</span></span></a>
              </div>
            </div>
          </div>
        </div>
        <div class="hero-background" aria-hidden="true">
          <div class="bg-shape bg-shape-1"></div>
          <div class="bg-shape bg-shape-2"></div>
          <div class="bg-shape bg-shape-3"></div>
        </div>
      </section>
      <section id="article-content" class="article-content article-container" aria-label="Article content">
        <div class="container">
          <div class="row">
            <div class="col-lg-8 mx-auto">
              <article class="article-body">
                {!! $article->content !!}
              </article>
              @include('articles.partials.share')
              @include('articles.partials.related')
            </div>
          </div>
        </div>
      </section>
    </main>
    <footer id="footer" class="footer position-relative">
      <div class="footer-background"><div class="footer-pattern"></div></div>
      <div class="container">
        <div class="footer-content">
          <div class="footer-main">
            <div class="footer-brand">
              <h3 class="brand-name"><span data-en="AmirHossein Jalalian" data-fa="امیرحسین جلالیان">AmirHossein Jalalian</span></h3>
              <p class="brand-title" data-en="IT Infrastructure Specialist" data-fa="متخصص زیرساخت فناوری اطلاعات">IT Infrastructure Specialist</p>
              <p class="brand-description" data-en="Delivering reliable, secure, and scalable IT solutions for your business success." data-fa="ارائه راهکارهای IT پایدار، امن و مقیاس‌پذیر برای موفقیت کسب‌وکار شما.">Delivering reliable, secure, and scalable IT solutions for your business success.</p>
            </div>
            <div class="footer-contact">
              <h4 class="contact-title" data-en="Get In Touch" data-fa="تماس با من">Get In Touch</h4>
              <div class="contact-info">
                <div class="contact-item"><i class="bi bi-geo-alt"></i><span data-en="Tehran, Iran" data-fa="تهران، ایران">Tehran, Iran</span></div>
                <div class="contact-item"><i class="bi bi-telephone"></i><a href="tel:+989197276219" data-en="+98 9197276219" data-fa="09197276219">+98 9197276219</a></div>
                <div class="contact-item"><i class="bi bi-envelope"></i><a href="mailto:jalalian.amirhossein@gmail.com">jalalian.amirhossein@gmail.com</a></div>
              </div>
            </div>
          </div>
          <div class="footer-bottom">
            <div class="footer-bottom-content">
              <div class="copyright"><p>© <span>2025</span> <strong data-en="AmirHossein Jalalian" data-fa="امیرحسین جلالیان">AmirHossein Jalalian</strong> <span data-en="All Rights Reserved" data-fa="تمام حقوق محفوظ است">All Rights Reserved</span></p></div>
              <div class="footer-heart"><span data-en="Made with" data-fa="ساخته شده با">Made with</span> <i class="bi bi-heart-fill"></i> <span data-en="in Iran" data-fa="در ایران">in Iran</span></div>
            </div>
          </div>
        </div>
      </div>
    </footer>
    <div id="preloader"></div>
    <script src="/assets/vendor/bootstrap/js/bootstrap.bundle.min.js" defer></script>
    <script src="/assets/vendor/aos/aos.js" defer></script>
    <script src="/assets/vendor/typed.js/typed.umd.js" defer></script>
    <script src="/assets/vendor/purecounter/purecounter_vanilla.js" defer></script>
    <script src="/assets/vendor/waypoints/noframework.waypoints.js" defer></script>
    <script src="/assets/vendor/glightbox/js/glightbox.min.js" defer></script>
    <script src="/assets/vendor/imagesloaded/imagesloaded.pkgd.min.js" defer></script>
    <script src="/assets/vendor/isotope-layout/isotope.pkgd.min.js" defer></script>
    <script src="/assets/vendor/swiper/swiper-bundle.min.js" defer></script>
    <script src="/assets/js/main.js?v=1119" defer></script>
    <script src="/assets/js/i18n.js?v=1116" defer></script>
    <script>
      document.querySelectorAll("[data-copy-link]").forEach(function (button) {
        button.addEventListener("click", function () {
          var url = button.getAttribute("data-copy-link") || "";
          var status = button.closest(".article-share") && button.closest(".article-share").querySelector(".article-share-status");
          var done = function () {
            if (!status) return;
            status.hidden = false;
            status.textContent = "Link copied";
            status.setAttribute("data-en", "Link copied");
            status.setAttribute("data-fa", "پیوند کپی شد");
          };
          if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(done).catch(function () {
              window.prompt("Copy link", url);
            });
          } else {
            window.prompt("Copy link", url);
          }
        });
      });
      if ("serviceWorker" in navigator) {
        window.addEventListener("load", function () {
          navigator.serviceWorker.register("/sw.js").catch(function () {});
        });
      }
    </script>
  </body>
</html>
