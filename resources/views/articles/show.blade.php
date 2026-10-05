<!doctype html>
<html lang="{{ data_get($article->presentation, 'content_language', 'en') }}" dir="{{ data_get($article->presentation, 'content_language') === 'fa' ? 'rtl' : 'ltr' }}" @if(data_get($article->presentation, 'localizations')) data-article-language="{{ data_get($article->presentation, 'content_language') }}" @endif>
  <head>
    <title>{{ $seo['title'] }}</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="{{ $seo['description'] }}" />
    <meta name="author" content="AmirHossein Jalalian" />
    <meta name="robots" content="{{ $seo['robots'] }}" />
    <link rel="canonical" href="{{ $seo['canonical'] }}" />
    @if (!empty($seo['keywords']))
      <meta name="keywords" content="{{ $seo['keywords'] }}" />
    @endif
    @foreach ($seo['alternates'] ?? [] as $language => $target)
      <link rel="alternate" hreflang="{{ $language }}" href="{{ $target }}" />
    @endforeach
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
    <link href="/assets/css/main.css?v=1002" rel="stylesheet" />
    <link href="/assets/css/articles.css?v=1102" rel="stylesheet" />
    <link href="/assets/css/lang-toggle.css?v=1403" rel="stylesheet" />
    <link id="rtl-style" href="/assets/css/rtl.css?v=1405" rel="stylesheet" disabled />
    <link href="/assets/css/visual-upgrade.css?v=1713" rel="stylesheet" />
    <link href="/assets/css/site-modules.css?v=1853" rel="stylesheet" />
    <link href="/assets/css/glass-system.css?v=18" rel="stylesheet" />
    <link href="/assets/css/article-reading.css?v=1" rel="stylesheet" />
    @if (!empty($languageSeo))
      <script id="article-language-seo" type="application/json">{!! json_encode($languageSeo, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    @endif
    <script id="article-schema" type="application/ld+json">{!! json_encode($seo['schema'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    @if (!empty($seo['breadcrumb']))
      <script id="article-breadcrumb-schema" type="application/ld+json">{!! json_encode($seo['breadcrumb'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    @endif
    @if (!empty($seo['faq_schema']))
      <script id="article-faq-schema" type="application/ld+json">{!! json_encode($seo['faq_schema'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    @endif
  </head>
  <body class="{{ data_get($article->presentation, 'body_class', 'article-page theme-other') }}" style="{{ $article->accentCustomProperties() }}">
    <button id="menu-toggle" class="menu-toggle d-xl-none" aria-label="Open menu" data-en-aria-label="Open menu" data-fa-aria-label="باز کردن منو" aria-expanded="false" aria-controls="header" type="button">
      <span class="menu-toggle-bars" aria-hidden="true"><span></span><span></span><span></span></span>
      <span class="sr-only" data-en="Open menu" data-fa="باز کردن منو">Open menu</span>
    </button>
    <x-site-sidebar logo-href="/#hero">
      <nav id="navmenu" class="navmenu" aria-label="Site navigation" data-en-aria-label="Site navigation" data-fa-aria-label="ناوبری سایت">
        <ul>
          <li>
            <a href="/#hero"><i class="bi bi-house navicon" aria-hidden="true"></i><span data-en="Home" data-fa="صفحه اصلی">Home</span></a>
          </li>
          <li>
            <a href="/#about"><i class="bi bi-person navicon" aria-hidden="true"></i><span data-en="About" data-fa="درباره من">About</span></a>
          </li>
          <li>
            <a href="/#resume"><i class="bi bi-file-earmark-text navicon" aria-hidden="true"></i><span data-en="Resume" data-fa="رزومه">Resume</span></a>
          </li>
          <li>
            <a href="/#services"><i class="bi bi-hdd-stack navicon" aria-hidden="true"></i><span data-en="Services" data-fa="خدمات">Services</span></a>
          </li>
          <li>
            <a href="/articles"><i class="bi bi-journal-text navicon" aria-hidden="true"></i><span data-en="Articles" data-fa="مقالات">Articles</span></a>
          </li>
          <li>
            <a href="/#testimonials"><i class="bi bi-chat-quote navicon" aria-hidden="true"></i><span data-en="Testimonials" data-fa="نظرات">Testimonials</span></a>
          </li>
          <li>
            <a href="/#contact"><i class="bi bi-envelope navicon" aria-hidden="true"></i><span data-en="Contact" data-fa="تماس با من">Contact</span></a>
          </li>
        </ul>
      </nav>
    </x-site-sidebar>
    <main id="main-content" class="main" role="main">
      <section class="article-hero article-header hero" role="banner">
        <div class="container">
          <div class="article-hero-layout">
            <div class="article-hero-copy">
              @include('articles.partials.breadcrumbs')
              <div class="article-meta">
                @if ($article->published_at)
                  <x-localized-date class="meta-date article-date" :date="$article->published_at" :locale="data_get($article->presentation, 'content_language', 'en')" />
                @endif
                <span class="article-readtime">{{ $article->readingMinutes() }} <span data-en="min read" data-fa="دقیقه مطالعه">min read</span></span>
              </div>
              <div class="article-kicker">
                <span class="article-category" style="--topic: {{ $article->accentColor() }}; --article-primary: {{ $article->accentColor() }};" data-en="{{ $article->categoryLabelEn() }}" data-fa="{{ $article->categoryLabelFa() }}">{{ $article->categoryLabelEn() }}</span>
                @if ($article->tags->isNotEmpty())
                  <ul class="article-tags">
                    @foreach ($article->tags as $tag)
                      <li><a href="{{ $tag->path() }}">{{ $tag->name }}</a></li>
                    @endforeach
                  </ul>
                @endif
              </div>
              @if (data_get($article->presentation, 'localizations'))
                @php $locale = data_get($article->presentation, 'content_language', 'en'); @endphp
                <h1 class="article-title hero-title" data-fa="{{ data_get($article->presentation, 'localizations.fa.title') }}" data-en="{{ data_get($article->presentation, 'localizations.en.title') }}">{{ data_get($article->presentation, 'localizations.'.$locale.'.title') }}</h1>
                <p class="article-excerpt hero-subtitle" data-fa="{{ data_get($article->presentation, 'localizations.fa.description') }}" data-en="{{ data_get($article->presentation, 'localizations.en.description') }}">{{ data_get($article->presentation, 'localizations.'.$locale.'.description') }}</p>
              @elseif (data_get($article->presentation, 'content_language') === 'fa')
                <h1 class="article-title hero-title" lang="fa" dir="rtl">{{ data_get($article->presentation, 'hero_title_fa', $article->title) }}</h1>
                <p class="article-excerpt hero-subtitle" lang="fa" dir="rtl">{{ data_get($article->presentation, 'excerpt_translations.fa', $article->excerpt) }}</p>
              @else
                <h1 class="article-title hero-title" data-i18n-lock data-en="{{ $article->englishTitle() }}">{{ $article->englishTitle() }}</h1>
                <p class="article-excerpt hero-subtitle" data-en="{{ $article->englishExcerpt() }}" data-fa="{{ data_get($article->presentation, 'excerpt_translations.fa', $article->englishExcerpt()) }}">{{ $article->englishExcerpt() }}</p>
              @endif
            </div>
            <figure class="article-hero-media">
              <img class="article-hero-thumbnail" src="{{ $article->thumbnailUrl() }}" decoding="async" fetchpriority="high" alt="{{ data_get($article->presentation, 'image_alt') ?: $article->title }}" />
            </figure>
          </div>
        </div>
      </section>
      <section id="article-content" class="article-content article-container" aria-label="Article content" data-en-aria-label="Article content" data-fa-aria-label="متن مقاله">
        <div class="container">
          @php $tocHtml = data_get($article->presentation, 'toc_html'); @endphp
          @php
            $articleContent = str_replace('my-profile-img.jpg', 'my-profile-img-2.jpg', $article->displayContent());
            $articleContent = str_replace(
                ['../index.html#portfolio', 'https://meetaj.ir/#portfolio', 'Back to portfolio', 'بازگشت به نمونه‌کارها'],
                [route('articles.index', [], false), route('articles.index', [], false), 'Back to articles', 'بازگشت به فهرست مقاله‌ها'],
                $articleContent
            );
            $standardToc = [
                ['introduction', 'Introduction', 'مقدمه'],
                ['architecture', 'Architecture and Core Concepts', 'معماری و مفاهیم اصلی'],
                ['prerequisites', 'Prerequisites', 'پیش‌نیازها'],
                ['configuration', 'Configuration and Validation', 'Configuration و اعتبارسنجی'],
                ['best-practices', 'Best Practices', 'Best Practiceها'],
                ['security', 'Security Considerations', 'ملاحظات امنیتی'],
                ['troubleshooting', 'Troubleshooting', 'عیب‌یابی'],
                ['conclusion', 'Conclusion', 'جمع‌بندی'],
                ['faq', 'Frequently Asked Questions', 'پرسش‌های متداول'],
                ['official-references', 'Official References', 'منابع رسمی و مرجع'],
            ];
            foreach ($standardToc as [$id, $en, $fa]) {
                if (str_contains($articleContent, 'id="'.$id.'"') && ! str_contains((string) $tocHtml, 'href="#'.$id.'"')) {
                    $tocHtml .= '<li class="article-nav-item"><a href="#'.$id.'"><span data-en="'.$en.'" data-fa="'.$fa.'">'.$en.'</span></a></li>';
                }
            }
                $articleContent = preg_replace('~<footer\b[^>]*class=["\'][^"\']*article-footer[^"\']*["\'][^>]*>.*?</footer>~is', '', $articleContent) ?? $articleContent;
              @endphp
          <div class="article-shell{{ $tocHtml ? ' article-shell--with-toc' : '' }}">
            @if ($tocHtml)
              <aside class="article-toc" aria-label="Table of contents" data-en-aria-label="Table of contents" data-fa-aria-label="فهرست مطالب">
                <p id="article-toc-heading" class="article-toc-title" data-en="On this page" data-fa="در این مقاله">On this page</p>
                <nav class="article-toc-nav" tabindex="0" aria-labelledby="article-toc-heading" @if(data_get($article->presentation, 'content_language') === 'fa') lang="fa" dir="rtl" @endif>
                  <ul class="article-toc-list">
                    {!! $tocHtml !!}
                  </ul>
                </nav>
              </aside>
            @endif
            <div class="article-reading">
              <article class="article-body" lang="{{ data_get($article->presentation, 'content_language', 'en') }}" dir="{{ data_get($article->presentation, 'content_language') === 'fa' ? 'rtl' : 'ltr' }}">
                {!! $articleContent !!}
              </article>
              @include('articles.partials.related')
              @include('articles.partials.author')
              <nav class="article-nav article-footer-nav" aria-label="Article footer navigation">
                <a class="article-back" href="{{ route('articles.index', [], false) }}">
                  <i class="bi bi-arrow-left" aria-hidden="true"></i>
                  <span data-en="Back to articles" data-fa="بازگشت به فهرست مقاله‌ها">{{ data_get($article->presentation, 'content_language') === 'fa' ? 'بازگشت به فهرست مقاله‌ها' : 'Back to articles' }}</span>
                </a>
              </nav>
              @include('articles.partials.share')
            </div>
          </div>
        </div>
      </section>
    </main>
    <footer id="footer" class="footer position-relative">
      <div class="footer-background">
        <div class="footer-pattern"></div>
      </div>
      <div class="container">
        <div class="footer-content">
          <!-- Footer Main Content -->
          <div class="footer-main">
            <!-- Brand Section -->
            <div class="footer-brand">
              <h3 class="brand-name">
                <span data-en="AmirHossein Jalalian" data-fa="امیرحسین جلالیان"
                  >AmirHossein Jalalian</span
                >
              </h3>
              <p
                class="brand-title"
                data-en="Infrastructure &amp; DevOps Engineer"
                data-fa="مهندس زیرساخت و DevOps"
              >
                Infrastructure &amp; DevOps Engineer
              </p>
              <p
                class="brand-description"
                data-en="Delivering reliable, secure, and scalable IT solutions for your business success."
                data-fa="ارائه راهکارهای IT پایدار، امن و مقیاس‌پذیر برای موفقیت کسب‌وکار شما."
              >
                Delivering reliable, secure, and scalable IT solutions for your
                business success.
              </p>
            </div>

            <!-- Contact Info -->
            <div class="footer-contact">
              <h4
                class="contact-title"
                data-en="Get In Touch"
                data-fa="تماس با من"
              >
                Get In Touch
              </h4>
              <div class="contact-info">
                <div class="contact-item">
                  <i class="bi bi-geo-alt"></i>
                  <span data-en="Tehran, Iran" data-fa="تهران، ایران"
                    >Tehran, Iran</span
                  >
                </div>
                <div class="contact-item">
                  <i class="bi bi-telephone"></i>
                  <a
                    href="tel:+989197276219"
                    data-en="+98 9197276219"
                    data-fa="09197276219"
                    >+98 9197276219</a
                  >
                </div>
                <div class="contact-item">
                  <i class="bi bi-envelope"></i>
                  <a href="mailto:jalalian.amirhossein@gmail.com"
                    >jalalian.amirhossein@gmail.com</a
                  >
                </div>
              </div>

              <!-- Social Links -->
              <div class="social-links">
                <h5
                  class="social-title"
                  data-en="Follow Me"
                  data-fa="دنبال کنید"
                >
                  Follow Me
                </h5>
                <div class="social-icons">
                  <a
                    href="https://wa.me/989197276219"
                    target="_blank"
                    rel="noopener"
                    class="social-link whatsapp"
                    aria-label="WhatsApp"
                  >
                    <i class="bi bi-whatsapp"></i>
                  </a>
                  <a
                    href="https://t.me/Aj_mercury"
                    target="_blank"
                    rel="noopener"
                    class="social-link telegram"
                    aria-label="Telegram"
                  >
                    <i class="bi bi-telegram"></i>
                  </a>
                  <a
                    href="mailto:jalalian.amirhossein@gmail.com"
                    class="social-link email"
                    aria-label="Email"
                  >
                    <i class="bi bi-envelope"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer Bottom -->
          <div class="footer-bottom">
            <div class="footer-bottom-content">
              <div class="copyright">
                <p>
                  © <x-localized-year />
                  <strong
                    data-en="AmirHossein Jalalian"
                    data-fa="امیرحسین جلالیان"
                    >AmirHossein Jalalian</strong
                  >
                  <span
                    data-en="All Rights Reserved"
                    data-fa="تمام حقوق محفوظ است"
                    >All Rights Reserved</span
                  >
                </p>
              </div>
              <div class="footer-heart">
                <span data-en="Made with" data-fa="ساخته شده با"
                  >Made with</span
                >
                <span class="footer-heart-symbol" aria-hidden="true">♥</span>
                <span data-en="in Iran" data-fa="در ایران">in Iran</span>
              </div>
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
    <script src="/assets/js/main.js?v=1418" defer></script>
    <script src="/assets/js/scroll-reveal.js?v=4" defer></script>
    <script src="/assets/js/i18n.js?v=1407" defer></script>
    <script>
      if ("serviceWorker" in navigator) {
        window.addEventListener("load", function () {
          navigator.serviceWorker.register("/sw.js").catch(function () {});
        });
      }
    </script>
  </body>
</html>
