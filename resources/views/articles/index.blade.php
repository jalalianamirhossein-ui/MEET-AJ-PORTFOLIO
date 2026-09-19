<!doctype html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Articles | Meet AJ</title>
    <meta name="description" content="Technical articles by AmirHossein Jalalian covering Linux, Microsoft, MikroTik, VMware, DevOps and infrastructure." />
    <link rel="canonical" href="{{ rtrim(config('app.url'), '/') }}/articles" />
    <meta property="og:title" content="Articles | Meet AJ" />
    <meta property="og:url" content="{{ rtrim(config('app.url'), '/') }}/articles" />
    <link href="/assets/img/favicon.png" rel="icon" />
    <link rel="manifest" href="/manifest.json" />
    <link href="/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet" />
    <link href="/assets/vendor/aos/aos.css" rel="stylesheet" />
    <link href="/assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet" />
    <link href="/assets/css/main.css?v=1002" rel="stylesheet" />
    <link href="/assets/css/lang-toggle.css?v=1403" rel="stylesheet" />
    <link id="rtl-style" href="/assets/css/rtl.css?v=1405" rel="stylesheet" disabled />
    <link href="/assets/css/visual-upgrade.css?v=1713" rel="stylesheet" />
    <link href="/assets/css/site-modules.css?v=1842" rel="stylesheet" />
  </head>
  <body class="index-page articles-index-page">@verbatim
<a class="skip-link" href="#portfolio" data-en="Skip to main content" data-fa="رفتن به محتوای اصلی">Skip to main content</a>
    <!-- ===============================================
    ==================== HEADER SECTION ================
    =============================================== -->
    <header id="header" class="header dark-background d-flex flex-column">
      @endverbatim
      @include('partials.site-sidebar-chrome', ['logoHref' => '/#hero'])
@verbatim

      <!-- ===============================================
      ==================== NAVIGATION MENU ================
      =============================================== -->
      <nav id="navmenu" class="navmenu" role="navigation" aria-label="Primary" data-en-aria-label="Primary" data-fa-aria-label="ناوبری اصلی">
        <ul>
          <!-- Home Section -->
          <li>
            <a href="/#hero"
              ><i class="bi bi-house navicon"></i
              ><span data-en="Home" data-fa="صفحه اصلی">Home</span></a
            >
          </li>

          <!-- About Section -->
          <li>
            <a href="/#about"
              ><i class="bi bi-person navicon"></i
              ><span data-en="About" data-fa="درباره من">About</span></a
            >
          </li>

          <!-- Resume Section -->
          <li>
            <a href="/#resume"
              ><i class="bi bi-file-earmark-text navicon"></i
              ><span data-en="Resume" data-fa="رزومه">Resume</span></a
            >
          </li>

          <!-- Services Section -->
          <li>
            <a href="/#services"
              ><i class="bi bi-hdd-stack navicon"></i
              ><span data-en="Services" data-fa="خدمات">Services</span></a
            >
          </li>

          <!-- Articles Section -->
          <li>
            <a href="/articles" class="active" aria-current="page"
              ><i class="bi bi-images navicon"></i
              ><span data-en="Articles" data-fa="مقالات">Articles</span></a
            >
          </li>

          <!-- Testimonials Section -->
          <li>
            <a href="/#testimonials"
              ><i class="bi bi-menu-button navicon"></i
              ><span data-en="Testimonials" data-fa="نظرات"
                >Testimonials</span
              ></a
            >
          </li>

          <!-- Contact Section -->
          <li>
            <a href="/#contact"
              ><i class="bi bi-envelope navicon"></i
              ><span data-en="Contact" data-fa="تماس با من">Contact</span></a
            >
          </li>
        </ul>
      </nav>
    </header>

    <!-- ===============================================
    ==================== MOBILE MENU TOGGLE ==============
    =============================================== -->
    <button
      id="menu-toggle"
      class="menu-toggle d-xl-none"
      type="button"
      aria-label="Open menu"
      data-en-aria-label="Open menu"
      data-fa-aria-label="باز کردن منو"
      aria-expanded="false"
      aria-controls="header"
    >
      <span class="menu-toggle-bars" aria-hidden="true"><span></span><span></span><span></span></span>
      <span class="sr-only" data-en="Open menu" data-fa="باز کردن منو">Open menu</span>
    </button>
    <main id="main-content" class="main" role="main">
<section id="portfolio" class="portfolio section light-background">
        <!-- ===============================================
        ==================== SECTION TITLE ==================
        =============================================== -->
        <div class="container section-title" data-aos="fade-up">
          <h2 data-en="Articles" data-fa="مقالات">Articles</h2>
          <p
            data-en="A collection of my technical articles and insights on network infrastructure, system administration, and DevOps solutions."
            data-fa="مجموعه‌ای از مقالات فنی و بینش‌های من در زمینه زیرساخت شبکه، مدیریت سیستم و راهکارهای DevOps."
          >
            A collection of my technical articles and insights on network
            infrastructure, system administration, and DevOps solutions.
          </p>
        </div>
        <!-- End Section Title -->
@endverbatim
        @if ($searching)
          @include('articles.partials.library-toolbar')
          @include('articles.partials.search-results')
        @else
@verbatim

        <!-- ===============================================
        ==================== ARTICLES CONTAINER ==============
        =============================================== -->
        <div class="container">
          <div
            class="isotope-layout"
            data-default-filter="*"
            data-layout="masonry"
            data-sort="original-order"
          >
            @endverbatim
            @include('articles.partials.library-toolbar', ['showCategoryFilters' => true])
@verbatim
            <!-- End Filter Buttons -->

            <!-- ===============================================
            ==================== ARTICLES GRID ==================
            =============================================== -->
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

            <!-- Load More Button -->
            <div
              class="articles-load-more text-center"
              data-aos="fade-up"
              data-aos-delay="300"
            >
              <button
                id="articles-load-more"
                class="btn btn-primary load-more-btn"
                type="button"
                data-en="Load more articles"
                data-fa="نمایش مقالات بیشتر"
                aria-label="Load more articles"
                data-en-aria-label="Load more articles"
                data-fa-aria-label="نمایش مقالات بیشتر"
              >
                Load more articles
              </button>
            </div>
          </div>
          <!-- End Articles Container -->
        </div>
@endverbatim
        @endif
@verbatim
        <!-- End Main Container -->
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
                  © <span data-current-year>2026</span>
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
@endverbatim    <script src="/assets/vendor/bootstrap/js/bootstrap.bundle.min.js" defer></script>
    <script src="/assets/vendor/aos/aos.js" defer></script>
    <script src="/assets/vendor/glightbox/js/glightbox.min.js" defer></script>
    <script src="/assets/vendor/imagesloaded/imagesloaded.pkgd.min.js" defer></script>
    <script src="/assets/vendor/isotope-layout/isotope.pkgd.min.js" defer></script>
    <script src="/assets/js/contact-form.js?v=1403" defer></script>
    <script src="/assets/js/main.js?v=1413" defer></script>
    <script src="/assets/js/service-catalog.js?v=1813" defer></script>
    <script src="/assets/js/i18n.js?v=1403" defer></script>
    <script>
      if ("serviceWorker" in navigator) {
        window.addEventListener("load", function () {
          navigator.serviceWorker.register("/sw.js").catch(function () {});
        });
      }
    </script>
  </body>
</html>