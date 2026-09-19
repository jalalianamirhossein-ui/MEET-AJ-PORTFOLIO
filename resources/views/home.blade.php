@php
  $sectionContent = static function (string $key) use ($homepageContent): array {
      return $homepageContent[$key]?->content ?? [];
  };
  $heroContent = $sectionContent('hero');
  $aboutContent = $sectionContent('about');
  $statsContent = $sectionContent('stats');
  $skillsContent = $sectionContent('skills');
  $resumeContent = $sectionContent('resume');
  $contactContent = $sectionContent('contact');
@endphp
<!doctype html>
<html lang="en" dir="ltr">
  <head>
    <!-- ===============================================
    MEET AJ PORTFOLIO - MAIN HTML DOCUMENT
    ===============================================
    
    Professional portfolio website for AmirHossein Jalalian
    Infrastructure & DevOps Engineer
    
    Features:
    - Progressive Web App (PWA) ready
    - Multi-language support (English/Persian)
    - Responsive design
    - Modern Apple Design System
    - SEO optimized
    
    =============================================== -->

    <!-- ===============================================
    META TAGS & SEO CONFIGURATION
    =============================================== -->
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta content="width=device-width, initial-scale=1.0" name="viewport" />

    <!-- Page Title & SEO Meta Tags -->
    <title>Meet Aj - AmirHossein Jalalian Portfolio</title>
    <meta
      content="Professional portfolio of AmirHossein Jalalian - Infrastructure & DevOps Engineer. Experienced in enterprise networks, server infrastructure, VMware, Linux, automation, and system optimization."
      name="description"
    />
    <meta
      content="Infrastructure & DevOps Engineer, enterprise networks, server infrastructure, Cisco, MikroTik, VMware, Linux, automation, Tehran Iran"
      name="keywords"
    />
    <meta name="author" content="AmirHossein Jalalian" />
    <meta
      name="robots"
      content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1"
    />
    <link rel="canonical" href="https://meetaj.ir/" />
    <meta property="og:site_name" content="Meet AJ" />
    <meta property="og:type" content="website" />
    <meta
      property="og:title"
      content="Meet Aj - AmirHossein Jalalian Portfolio"
    />
    <meta
      property="og:description"
      content="Professional portfolio of AmirHossein Jalalian - Infrastructure & DevOps Engineer. Experienced in enterprise networks, server infrastructure, VMware, Linux, automation, and system optimization."
    />
    <meta property="og:url" content="https://meetaj.ir/" />
    <meta
      property="og:image"
      content="https://meetaj.irassets/img/hero-bg.jpg"
    />
    <meta
      property="og:image:alt"
      content="AmirHossein Jalalian network infrastructure portfolio hero"
    />
    <meta property="og:locale" content="en_US" />
    <meta property="og:locale:alternate" content="fa_IR" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta
      name="twitter:title"
      content="Meet Aj - AmirHossein Jalalian Portfolio"
    />
    <meta
      name="twitter:description"
      content="Professional portfolio of AmirHossein Jalalian - Infrastructure & DevOps Engineer."
    />
    <meta
      name="twitter:image"
      content="https://meetaj.irassets/img/hero-bg.jpg"
    />
    <script type="application/ld+json">
      {
        "@@context": "https://schema.org",
        "@@type": "Person",
        "name": "AmirHossein Jalalian",
        "url": "https://meetaj.ir/",
        "jobTitle": "Infrastructure & DevOps Engineer",
        "image": "https://meetaj.irassets/img/my-profile-img-2.jpg",
        "sameAs": [
          "https://www.linkedin.com/in/amirhosseinjalalian",
          "https://github.com/amirhosseinjalalian",
          "https://instagram.com/aj.mercury"
        ]
      }
    </script>
    <script type="application/ld+json">
      {
        "@@context": "https://schema.org",
        "@@type": "WebSite",
        "url": "https://meetaj.ir/",
        "name": "Meet AJ - AmirHossein Jalalian Portfolio",
        "inLanguage": "en",
        "potentialAction": {
          "@@type": "SearchAction",
          "target": "https://www.google.com/search?q=site:meetaj.ir+{search_term_string}",
          "query-input": "required name=search_term_string"
        }
      }
    </script>
    <script type="application/ld+json">
      {
        "@@context": "https://schema.org",
        "@@type": "BreadcrumbList",
        "itemListElement": [
          {
            "@@type": "ListItem",
            "position": 1,
            "name": "Home",
            "item": "https://meetaj.ir/#hero"
          },
          {
            "@@type": "ListItem",
            "position": 2,
            "name": "About",
            "item": "https://meetaj.ir/#about"
          },
          {
            "@@type": "ListItem",
            "position": 3,
            "name": "Resume",
            "item": "https://meetaj.ir/#resume"
          },
          {
            "@@type": "ListItem",
            "position": 4,
            "name": "Services",
            "item": "https://meetaj.ir/#services"
          },
          {
            "@@type": "ListItem",
            "position": 5,
            "name": "Articles",
            "item": "https://meetaj.ir/#portfolio"
          },
          {
            "@@type": "ListItem",
            "position": 6,
            "name": "Testimonials",
            "item": "https://meetaj.ir/#testimonials"
          },
          {
            "@@type": "ListItem",
            "position": 7,
            "name": "Contact",
            "item": "https://meetaj.ir/#contact"
          }
        ]
      }
    </script>

    <!-- ===============================================
    FAVICONS & APP ICONS
    =============================================== -->
    <link href="/assets/img/favicon.png" rel="icon" />
    <link href="/assets/img/apple-touch-icon.png" rel="apple-touch-icon" />

    <!-- ===============================================
    PROGRESSIVE WEB APP (PWA) CONFIGURATION
    =============================================== -->
    <link rel="manifest" href="/manifest.json" />
    <meta name="theme-color" content="#0050a0" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />
    <meta name="apple-mobile-web-app-title" content="Meet AJ" />
    <meta name="color-scheme" content="light dark" />
    <meta name="format-detection" content="telephone=no" />
    <meta name="msapplication-tap-highlight" content="no" />
    <meta
      http-equiv="Content-Security-Policy"
      content="default-src 'self'; img-src 'self' data: https://meetaj.ir; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; script-src 'self' 'unsafe-inline'; connect-src 'self'; frame-src 'self' https://www.google.com https://maps.gstatic.com;"
    />
    <meta name="referrer" content="strict-origin-when-cross-origin" />
    <link rel="preload" as="image" href="/assets/img/hero-bg.jpg" />

    <!-- ===============================================
    GOOGLE FONTS & TYPOGRAPHY
    =============================================== -->
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Vazirmatn:wght@400;500;600;700&family=Estedad:wght@400;500;600;700&display=swap"
      rel="stylesheet"
    />

    <!-- ===============================================
    STYLESHEETS & CSS FRAMEWORKS
    =============================================== -->

    <!-- Preload main stylesheet for faster first paint -->
    <link href="/assets/css/main.css?v=1002" rel="preload" as="style" />

    <!-- Bootstrap CSS Framework -->
    <link
      href="/assets/vendor/bootstrap/css/bootstrap.min.css"
      rel="stylesheet"
    />

    <!-- Bootstrap Icons -->
    <link
      href="/assets/vendor/bootstrap-icons/bootstrap-icons.css"
      rel="stylesheet"
    />

    <!-- AOS Animation Library -->
    <link href="/assets/vendor/aos/aos.css" rel="stylesheet" />

    <!-- GLightbox Gallery Library -->
    <link
      href="/assets/vendor/glightbox/css/glightbox.min.css"
      rel="stylesheet"
    />

    <!-- Swiper Slider Library -->
    <link href="/assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet" />

    <!-- ===============================================
    ==================== MAIN CSS ======================
    =============================================== -->
    <!-- Main Stylesheet -->
    <link href="/assets/css/main.css?v=1002" rel="stylesheet" />

    <!-- Language Toggle Stylesheet -->
    <link href="/assets/css/lang-toggle.css?v=1403" rel="stylesheet" />

    <!-- RTL Support Stylesheet -->
    <link
      id="rtl-style"
      href="/assets/css/rtl.css?v=1405"
      rel="stylesheet"
      disabled
    />
    <link href="/assets/css/visual-upgrade.css?v=1713" rel="stylesheet" />
    <link href="/assets/css/site-modules.css?v=1852" rel="stylesheet" />

    <!-- ===============================================
    ==================== CRITICAL CSS ==================
    =============================================== -->
    <style>
      /* Critical CSS for initial render */
      body {
        margin: 0;
        padding: 0;
        font-family: "Poppins", sans-serif;
      }

      .header {
        background: linear-gradient(135deg, #0ea5e9, #1e40af);
      }

      .hero {
        min-height: 100vh;
        display: flex;
        align-items: center;
      }

      .hero-title {
        font-size: 3rem;
        font-weight: 700;
        color: #1e40af;
      }

      .hero-subtitle {
        font-size: 1.2rem;
        color: #374151;
      }

      .btn {
        padding: 12px 24px;
        border-radius: 8px;
        text-decoration: none;
        display: inline-block;
      }

      .btn-primary {
        background: #0ea5e9;
        color: white;
      }

      .skip-link {
        position: absolute;
        top: -999px;
        left: -999px;
        padding: 10px 14px;
        background: #0ea5e9;
        color: #fff;
        border-radius: 8px;
        z-index: 10000;
        transition:
          top 0.2s ease,
          left 0.2s ease;
      }

      .skip-link:focus-visible {
        top: 16px;
        left: 16px;
      }

      .btn-outline-light {
        border: 2px solid white;
        color: white;
      }

      /* Responsive overrides to keep the hero section mobile friendly
         when these critical styles load before the main bundle */
      @media (max-width: 768px) {
        .hero {
          flex-direction: column;
          align-items: flex-end;
          justify-content: flex-end;
          text-align: start;
          padding: 0;
          gap: 0;
        }

        .hero-title {
          font-size: 2.2rem;
          color: #f8fafc;
        }

        .hero-subtitle {
          font-size: 1rem;
          color: #e2e8f0;
        }

        .hero .hero-actions {
          display: flex;
          flex-direction: column;
          align-items: stretch;
          width: 100%;
          gap: 0.75rem;
        }

        .hero .hero-actions .btn {
          width: 100%;
          max-width: 260px;
        }
      }

      @media (max-width: 480px) {
        .hero {
          padding: 0;
        }

        .hero-title {
          font-size: 1.8rem;
          color: #f8fafc;
        }

        .hero-subtitle {
          font-size: 0.95rem;
          color: #e2e8f0;
        }

        .btn {
          padding: 10px 18px;
        }
      }
    </style>
    <noscript>
      <style>
        #preloader {
          display: none !important;
        }
      </style>
    </noscript>
  </head>

  <!-- ===============================================
  ==================== BODY START ====================
  =============================================== -->

  <body class="index-page">
    <a class="skip-link" href="#main-content" data-en="Skip to main content" data-fa="رفتن به محتوای اصلی">Skip to main content</a>
    <!-- ===============================================
    ==================== HEADER SECTION ================
    =============================================== -->
    <header id="header" class="header dark-background d-flex flex-column">
      
      @include('partials.site-sidebar-chrome', ['logoHref' => '#hero'])


      <!-- ===============================================
      ==================== NAVIGATION MENU ================
      =============================================== -->
      <nav id="navmenu" class="navmenu" role="navigation" aria-label="Primary" data-en-aria-label="Primary" data-fa-aria-label="ناوبری اصلی">
        <ul>
          <!-- Home Section -->
          <li>
            <a href="#hero" class="active" aria-current="page"
              ><i class="bi bi-house navicon"></i
              ><span data-en="Home" data-fa="صفحه اصلی">Home</span></a
            >
          </li>

          <!-- About Section -->
          <li>
            <a href="#about"
              ><i class="bi bi-person navicon"></i
              ><span data-en="About" data-fa="درباره من">About</span></a
            >
          </li>

          <!-- Resume Section -->
          <li>
            <a href="#resume"
              ><i class="bi bi-file-earmark-text navicon"></i
              ><span data-en="Resume" data-fa="رزومه">Resume</span></a
            >
          </li>

          <!-- Services Section -->
          <li>
            <a href="#services"
              ><i class="bi bi-hdd-stack navicon"></i
              ><span data-en="Services" data-fa="خدمات">Services</span></a
            >
          </li>

          <!-- Articles Section -->
          <li>
            <a href="#portfolio"
              ><i class="bi bi-images navicon"></i
              ><span data-en="Articles" data-fa="مقالات">Articles</span></a
            >
          </li>

          <!-- Testimonials Section -->
          <li>
            <a href="#testimonials"
              ><i class="bi bi-menu-button navicon"></i
              ><span data-en="Testimonials" data-fa="نظرات"
                >Testimonials</span
              ></a
            >
          </li>

          <!-- Contact Section -->
          <li>
            <a href="#contact"
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

    <!-- ===============================================
    ==================== MAIN CONTENT ====================
    =============================================== -->
    <main id="main-content" class="main" role="main">
      <!-- ===============================================
      ==================== HERO SECTION ==================
      =============================================== -->
      <section id="hero" class="hero section dark-background">
        <!-- Hero Background Image -->
        <img
          src="{{ data_get($heroContent, 'image', '/assets/img/hero-bg.jpg') }}"
          alt="{{ data_get($heroContent, 'title_en', 'Meet AJ') }}"
          class="hero-bg"
          width="1920"
          height="1080"
          decoding="async"
          fetchpriority="high"
          sizes="100vw"
        />

        <div class="container" data-aos="fade-up" data-aos-delay="100">
          <div class="hero-wrapper">
            <!-- Hero Profile Image -->
            <div
              class="hero-profile-section"
              data-aos="fade-right"
              data-aos-delay="200"
            ></div>

            <!-- Content -->
            <div
              class="hero-content-section"
              data-aos="fade-left"
              data-aos-delay="300"
            >
              <div class="hero-content">
                <!-- Modern Badge -->
                <div class="hero-badge" data-aos="fade-up" data-aos-delay="200">
                  <i class="bi bi-briefcase-fill" aria-hidden="true"></i>
                  <span
                    class="badge-text"
                    data-en="{{ data_get($heroContent, 'badge_en', 'Available for Work') }}"
                    data-fa="{{ data_get($heroContent, 'badge_fa', 'آماده برای همکاری') }}"
                    >{{ data_get($heroContent, 'badge_en', 'Available for Work') }}</span
                  >
                  <div class="badge-dot"></div>
                </div>

                <!-- Main Title with Modern Typography -->
                <h1
                  class="hero-title"
                  data-en="{{ data_get($heroContent, 'title_en', 'AmirHossein Jalalian') }}"
                  data-fa="{{ data_get($heroContent, 'title_fa', 'امیرحسین جلالیان') }}"
                  data-aos="fade-up"
                  data-aos-delay="400"
                >
                  <span
                    class="title-line-1"
                    data-en="{{ data_get($heroContent, 'title_en', 'AmirHossein Jalalian') }}"
                    data-fa="{{ data_get($heroContent, 'title_fa', 'امیرحسین جلالیان') }}"
                    >{{ data_get($heroContent, 'title_en', 'AmirHossein Jalalian') }}</span
                  >
                  <span
                    class="title-line-2"
                    data-en=""
                    data-fa=""
                    ></span
                  >
                </h1>
                <p
                  class="hero-role"
                  data-en="{{ data_get($heroContent, 'role_en', 'Infrastructure & DevOps Engineer') }}"
                  data-fa="{{ data_get($heroContent, 'role_fa', 'مهندس زیرساخت و DevOps') }}"
                >
                  {{ data_get($heroContent, 'role_en', 'Infrastructure & DevOps Engineer') }}
                </p>

                <!-- Subtitle with Enhanced Typography -->
                <div
                  class="hero-subtitle-wrapper"
                  data-aos="fade-up"
                  data-aos-delay="600"
                >
                  <p class="hero-subtitle">
                    <span class="subtitle-prefix" data-en="I'm a" data-fa="من"
                      >I'm a</span
                    >
                    <span
                      class="typed"
                      data-typed-items="{{ data_get($heroContent, 'subtitle_en', data_get($heroContent, 'role_en', 'Infrastructure & DevOps Engineer')) }}"
                      data-typed-items-fa="{{ data_get($heroContent, 'subtitle_fa', data_get($heroContent, 'role_fa', 'مهندس زیرساخت و DevOps')) }}"
                      aria-live="polite"
                      aria-atomic="true"
                      >{{ data_get($heroContent, 'subtitle_en', data_get($heroContent, 'role_en', 'Infrastructure & DevOps Engineer')) }}</span
                    >
                  </p>
                </div>

                <!-- Modern Action Buttons -->
                <div
                  class="hero-actions"
                  data-aos="fade-up"
                  data-aos-delay="80"
                >
                  <a
                    href="{{ data_get($heroContent, 'primary_href', '#contact') }}"
                    class="btn btn-primary btn-modern"
                    data-en="{{ data_get($heroContent, 'primary_label_en', 'Get In Touch') }}"
                    data-fa="{{ data_get($heroContent, 'primary_label_fa', 'تماس با من') }}"
                  >
                    <span
                      class="btn-text"
                      data-en="{{ data_get($heroContent, 'primary_label_en', 'Get In Touch') }}"
                      data-fa="{{ data_get($heroContent, 'primary_label_fa', 'تماس با من') }}"
                      >{{ data_get($heroContent, 'primary_label_en', 'Get In Touch') }}</span
                    >
                    <span class="btn-icon" aria-hidden="true"><i class="bi bi-envelope"></i></span>
                  </a>
                  <a
                    href="{{ data_get($heroContent, 'secondary_href', '#portfolio') }}"
                    class="btn btn-outline-light btn-modern"
                    data-en="{{ data_get($heroContent, 'secondary_label_en', 'Articles') }}"
                    data-fa="{{ data_get($heroContent, 'secondary_label_fa', 'مقالات') }}"
                  >
                    <span class="btn-text" data-en="{{ data_get($heroContent, 'secondary_label_en', 'Articles') }}" data-fa="{{ data_get($heroContent, 'secondary_label_fa', 'مقالات') }}"
                      >{{ data_get($heroContent, 'secondary_label_en', 'Articles') }}</span
                    >
                    <span class="btn-icon" aria-hidden="true"><i class="bi bi-journal-text"></i></span>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <!-- /Hero Section -->

      <!-- ===============================================
      ==================== ABOUT SECTION ==================
      =============================================== -->
      <section id="about" class="about section about-premium">
        <div class="container about-stage">
          <div class="about-copy" data-aos="fade-up">
            <p class="about-kicker" data-en="{{ data_get($aboutContent, 'kicker_en', 'Infrastructure & DevOps Engineer') }}" data-fa="{{ data_get($aboutContent, 'kicker_fa', 'مهندس زیرساخت و DevOps') }}">{{ data_get($aboutContent, 'kicker_en', 'Infrastructure & DevOps Engineer') }}</p>
            <h2 data-en="{{ data_get($aboutContent, 'title_en', 'Get to Know Me') }}" data-fa="{{ data_get($aboutContent, 'title_fa', 'با من آشنا شوید') }}">{{ data_get($aboutContent, 'title_en', 'Get to Know Me') }}</h2>
            <p class="about-headline" data-en="{{ data_get($aboutContent, 'headline_en', 'Designing, managing, and optimizing enterprise systems.') }}" data-fa="{{ data_get($aboutContent, 'headline_fa', 'طراحی، مدیریت و بهینه‌سازی سیستم‌های سازمانی.') }}">{{ data_get($aboutContent, 'headline_en', 'Designing, managing, and optimizing enterprise systems.') }}</p>
            <div class="about-profile">
              <div class="about-photo">
                <img src="{{ data_get($aboutContent, 'image', '/assets/img/my-profile-img-2.jpg') }}" alt="{{ data_get($aboutContent, 'name_en', 'Amirhossein Jalalian') }}" width="200" height="200" loading="lazy" sizes="120px" />
              </div>
              <div class="about-profile-meta">
                <p class="about-name" data-en="{{ data_get($aboutContent, 'name_en', 'Amirhossein Jalalian') }}" data-fa="{{ data_get($aboutContent, 'name_fa', 'امیرحسین جلالیان') }}">{{ data_get($aboutContent, 'name_en', 'Amirhossein Jalalian') }}</p>
                <p class="about-role" data-en="{{ data_get($aboutContent, 'role_en', 'Infrastructure & DevOps Engineer') }}" data-fa="{{ data_get($aboutContent, 'role_fa', 'مهندس زیرساخت و DevOps') }}">{{ data_get($aboutContent, 'role_en', 'Infrastructure & DevOps Engineer') }}</p>
                <p class="about-place"><i class="bi bi-geo-alt" aria-hidden="true"></i> <span data-en="{{ data_get($aboutContent, 'location_en', 'Tehran, Iran') }}" data-fa="{{ data_get($aboutContent, 'location_fa', 'تهران، ایران') }}">{{ data_get($aboutContent, 'location_en', 'Tehran, Iran') }}</span></p>
              </div>
            </div>
            <p class="about-lead" data-en="{{ data_get($aboutContent, 'lead_1_en', '') }}" data-fa="{{ data_get($aboutContent, 'lead_1_fa', '') }}">
              {{ data_get($aboutContent, 'lead_1_en', '') }}
            </p>
            <p class="about-lead" data-en="{{ data_get($aboutContent, 'lead_2_en', '') }}" data-fa="{{ data_get($aboutContent, 'lead_2_fa', '') }}">
              {{ data_get($aboutContent, 'lead_2_en', '') }}
            </p>
            <dl class="about-facts">
              @foreach (data_get($aboutContent, 'facts', []) as $fact)
                <div>
                  <dt data-en="{{ $fact['label_en'] ?? '' }}" data-fa="{{ $fact['label_fa'] ?? ($fact['label_en'] ?? '') }}">{{ $fact['label_en'] ?? '' }}</dt>
                  <dd data-en="{{ $fact['value_en'] ?? '' }}" data-fa="{{ $fact['value_fa'] ?? ($fact['value_en'] ?? '') }}">{{ $fact['value_en'] ?? '' }}</dd>
                </div>
              @endforeach
            </dl>
            <div class="about-actions">
              <a class="btn btn-primary" href="#contact" data-en="Get In Touch" data-fa="تماس با من">Get In Touch</a>
              <a class="btn btn-outline-primary" href="#services" data-en="Services" data-fa="خدمات">Services</a>
            </div>
          </div>

          <div class="about-visual" data-aos="fade-up" data-aos-delay="120">
            <div class="about-core is-core" data-about-core>
              <p class="about-core-label" data-en="Infrastructure Core" data-fa="هسته زیرساخت">Infrastructure Core</p>
              <div class="about-core-canvas" dir="ltr">
                <div class="about-core-grid" aria-hidden="true"></div>
                <svg class="about-core-svg" viewBox="0 0 640 420" aria-hidden="true" focusable="false">
                  <line class="about-link" data-link="infra" x1="320" y1="210" x2="320" y2="64"></line>
                  <line class="about-link" data-link="devops" x1="320" y1="210" x2="118" y2="128"></line>
                  <line class="about-link" data-link="net" x1="320" y1="210" x2="522" y2="128"></line>
                  <line class="about-link" data-link="virt" x1="320" y1="210" x2="118" y2="308"></line>
                  <line class="about-link" data-link="mon" x1="320" y1="210" x2="522" y2="308"></line>
                  <line class="about-link" data-link="auto" x1="320" y1="210" x2="320" y2="368"></line>
                </svg>
                <button type="button" class="about-node about-node--core is-active" data-panel="core" aria-pressed="true" aria-controls="about-core-detail">
                  <span class="about-node-status" aria-hidden="true"></span>
                  <span data-en="AJ" data-fa="AJ" dir="ltr">AJ</span>
                </button>
                <button type="button" class="about-node about-node--infra" data-panel="infra" aria-pressed="false" aria-controls="about-core-detail">
                  <span class="about-node-status" aria-hidden="true"></span>
                  <span data-en="Infrastructure" data-fa="زیرساخت">Infrastructure</span>
                </button>
                <button type="button" class="about-node about-node--devops" data-panel="devops" aria-pressed="false" aria-controls="about-core-detail">
                  <span class="about-node-status" aria-hidden="true"></span>
                  <span data-en="DevOps" data-fa="دواپس">DevOps</span>
                </button>
                <button type="button" class="about-node about-node--net" data-panel="net" aria-pressed="false" aria-controls="about-core-detail">
                  <span class="about-node-status" aria-hidden="true"></span>
                  <span data-en="Networking" data-fa="شبکه">Networking</span>
                </button>
                <button type="button" class="about-node about-node--virt" data-panel="virt" aria-pressed="false" aria-controls="about-core-detail">
                  <span class="about-node-status" aria-hidden="true"></span>
                  <span data-en="Virtualization" data-fa="مجازی‌سازی">Virtualization</span>
                </button>
                <button type="button" class="about-node about-node--mon" data-panel="mon" aria-pressed="false" aria-controls="about-core-detail">
                  <span class="about-node-status" aria-hidden="true"></span>
                  <span data-en="Monitoring" data-fa="مانیتورینگ">Monitoring</span>
                </button>
                <button type="button" class="about-node about-node--auto" data-panel="auto" aria-pressed="false" aria-controls="about-core-detail">
                  <span class="about-node-status" aria-hidden="true"></span>
                  <span data-en="Automation" data-fa="خودکارسازی">Automation</span>
                </button>
              </div>
              <div class="about-core-detail" id="about-core-detail" aria-live="polite">
                <p data-panel="core" class="is-active" data-en="Reliable, secure, and scalable solutions for enterprise systems — Windows Server, Cisco, MikroTik, and virtualization." data-fa="راه‌حل‌های قابل اعتماد، امن و مقیاس‌پذیر برای سیستم‌های سازمانی — Windows Server، سیسکو، میکروتیک و مجازی‌سازی.">Reliable, secure, and scalable solutions for enterprise systems — Windows Server, Cisco, MikroTik, and virtualization.</p>
                <p data-panel="infra" hidden data-en="Linux administration (Ubuntu, CentOS) and Microsoft services (Active Directory, DNS, DFS, WDS, WSUS, NTP)." data-fa="مدیریت لینوکس (اوبونتو، سنت‌اواس) و خدمات مایکروسافت (Active Directory، DNS، DFS، WDS، WSUS، NTP).">Linux administration (Ubuntu, CentOS) and Microsoft services (Active Directory, DNS, DFS, WDS, WSUS, NTP).</p>
                <p data-panel="devops" hidden data-en="Docker containerization, CI/CD pipelines, and GitLab / Jenkins workflows." data-fa="کانتینریزاسیون Docker، خط لوله CI/CD و گردش‌کار GitLab / Jenkins.">Docker containerization, CI/CD pipelines, and GitLab / Jenkins workflows.</p>
                <p data-panel="net" hidden data-en="Network design with Cisco and MikroTik, firewall and security rules, VPN, and VoIP infrastructure." data-fa="طراحی شبکه با سیسکو و میکروتیک، فایروال و قوانین امنیتی، VPN و زیرساخت VoIP.">Network design with Cisco and MikroTik, firewall and security rules, VPN, and VoIP infrastructure.</p>
                <p data-panel="virt" hidden data-en="VMware vSphere and KVM virtualization, plus cloud platforms (AWS, Azure)." data-fa="مجازی‌سازی VMware vSphere و KVM، و پلتفرم‌های ابری (AWS، Azure).">VMware vSphere and KVM virtualization, plus cloud platforms (AWS, Azure).</p>
                <p data-panel="mon" hidden data-en="Zabbix, Grafana, Cacti, and Redgate monitoring — dashboards and alerting." data-fa="مانیتورینگ Zabbix، Grafana، Cacti و Redgate — داشبورد و هشدار.">Zabbix, Grafana, Cacti, and Redgate monitoring — dashboards and alerting.</p>
                <p data-panel="auto" hidden data-en="CI/CD pipeline setup and infrastructure automation, including Ansible and Terraform where the project requires it." data-fa="راه‌اندازی CI/CD و خودکارسازی زیرساخت، شامل Ansible و Terraform در صورت نیاز پروژه.">CI/CD pipeline setup and infrastructure automation, including Ansible and Terraform where the project requires it.</p>
              </div>
            </div>
          </div>
        </div>

        <div class="container about-domains">
          <h3 class="about-domains-title" data-en="Expertise" data-fa="تخصص‌ها">Expertise</h3>
          <div class="about-domain-grid">
            <section class="about-domain" data-expertise="infrastructure">
              <h4>
                <span class="about-domain-label">
                  <i class="bi bi-hdd-stack about-domain-icon" aria-hidden="true"></i>
                  <span data-en="Infrastructure" data-fa="زیرساخت">Infrastructure</span>
                </span>
              </h4>
              <ul>
                <li>Linux</li>
                <li>Windows Server</li>
                <li>VMware</li>
                <li>KVM</li>
              </ul>
            </section>
            <section class="about-domain" data-expertise="networking">
              <h4>
                <span class="about-domain-label">
                  <i class="bi bi-diagram-3 about-domain-icon" aria-hidden="true"></i>
                  <span data-en="Networking" data-fa="شبکه">Networking</span>
                </span>
              </h4>
              <ul>
                <li>Cisco</li>
                <li>MikroTik</li>
                <li>VPN</li>
                <li>VoIP</li>
              </ul>
            </section>
            <section class="about-domain" data-expertise="devops">
              <h4>
                <span class="about-domain-label">
                  <i class="bi bi-gear about-domain-icon" aria-hidden="true"></i>
                  <span data-en="DevOps" data-fa="دواپس">DevOps</span>
                </span>
              </h4>
              <ul>
                <li>Docker</li>
                <li>CI/CD</li>
                <li>Jenkins</li>
                <li>GitLab</li>
                <li>Ansible</li>
                <li>Terraform</li>
              </ul>
            </section>
            <section class="about-domain" data-expertise="monitoring">
              <h4>
                <span class="about-domain-label">
                  <i class="bi bi-graph-up about-domain-icon" aria-hidden="true"></i>
                  <span data-en="Monitoring" data-fa="مانیتورینگ">Monitoring</span>
                </span>
              </h4>
              <ul>
                <li>Zabbix</li>
                <li>Grafana</li>
                <li>Cacti</li>
                <li>Redgate</li>
              </ul>
            </section>
            <section class="about-domain" data-expertise="security">
              <h4>
                <span class="about-domain-label">
                  <i class="bi bi-shield-check about-domain-icon" aria-hidden="true"></i>
                  <span data-en="Security" data-fa="امنیت">Security</span>
                </span>
              </h4>
              <ul>
                <li data-en="Firewall &amp; security rules" data-fa="قوانین فایروال و امنیت">Firewall &amp; security rules</li>
                <li>Active Directory</li>
                <li>VPN</li>
                <li data-en="Backup (Veeam)" data-fa="پشتیبان‌گیری (Veeam)">Backup (Veeam)</li>
              </ul>
            </section>
          </div>
        </div>

        <div class="container about-secondary">
          <div class="about-values">
            <h3 data-en="Core Values" data-fa="ارزش‌های اصلی">Core Values</h3>
            <ul class="about-value-list">
              @foreach (data_get($aboutContent, 'values', []) as $value)
                <li>
                  <h4 data-en="{{ $value['title_en'] ?? '' }}" data-fa="{{ $value['title_fa'] ?? ($value['title_en'] ?? '') }}">{{ $value['title_en'] ?? '' }}</h4>
                  <p data-en="{{ $value['body_en'] ?? '' }}" data-fa="{{ $value['body_fa'] ?? ($value['body_en'] ?? '') }}">{{ $value['body_en'] ?? '' }}</p>
                </li>
              @endforeach
            </ul>
          </div>
          <blockquote class="about-quote">
            <p data-en="{{ data_get($aboutContent, 'motto_en', '') }}" data-fa="{{ data_get($aboutContent, 'motto_fa', '') }}">{{ data_get($aboutContent, 'motto_en', '') }}</p>
            <footer data-en="? My Personal Motto" data-fa="شعار شخصی من">? My Personal Motto</footer>
          </blockquote>
          <div class="about-philosophy">
            <h3 data-en="{{ data_get($aboutContent, 'philosophy_title_en', 'My Philosophy') }}" data-fa="{{ data_get($aboutContent, 'philosophy_title_fa', 'فلسفه من') }}">{{ data_get($aboutContent, 'philosophy_title_en', 'My Philosophy') }}</h3>
            <p data-en="{{ data_get($aboutContent, 'philosophy_en', '') }}" data-fa="{{ data_get($aboutContent, 'philosophy_fa', '') }}">
              {{ data_get($aboutContent, 'philosophy_en', '') }}
            </p>
          </div>
        </div>
      </section>
      <!-- /About Section -->

      <!-- ===============================================
      ==================== STATS SECTION ====================
      =============================================== -->
      <section id="stats" class="stats section">
        <!-- Stats Container -->
        <div class="container" data-aos="fade-up" data-aos-delay="100">
          <div class="row gy-4">
            <!-- Happy Clients Stat -->
            <div class="col-lg-3 col-md-6">
              <div class="stats-item">
                <i class="bi bi-people" aria-hidden="true"></i>
                <span
                  data-purecounter-start="0"
                  data-purecounter-end="49"
                  data-purecounter-duration="1"
                  class="purecounter"
                ></span>
                <p>
                  <span data-en="Satisfied customers" data-fa="مشتریان راضی"
                    >Satisfied customers</span
                  >
                </p>
              </div>
            </div>
            <!-- End Happy Clients Stat -->

            <!-- Projects Stat -->
            <div class="col-lg-3 col-md-6">
              <div class="stats-item">
                <i class="bi bi-journal-richtext"></i>
                <span
                  data-purecounter-start="0"
                  data-purecounter-end="31"
                  data-purecounter-duration="1"
                  class="purecounter"
                ></span>
                <p>
                  <span data-en="Successful projects" data-fa="پروژه‌های موفق"
                    >Successful projects</span
                  >
                </p>
              </div>
            </div>
            <!-- End Projects Stat -->

            <!-- Support Hours Stat -->
            <div class="col-lg-3 col-md-6">
              <div class="stats-item">
                <i class="bi bi-headset"></i>
                <span
                  data-purecounter-start="0"
                  data-purecounter-end="4160"
                  data-purecounter-duration="1"
                  class="purecounter"
                ></span>
                <p>
                  <span
                    data-en="Support hours provided"
                    data-fa="ساعت پشتیبانی ارائه شده"
                    >Support hours provided</span
                  >
                </p>
              </div>
            </div>
            <!-- End Support Hours Stat -->

            <!-- Team Members Stat -->
            <div class="col-lg-3 col-md-6">
              <div class="stats-item">
                <i class="bi bi-people"></i>
                <span
                  data-purecounter-start="0"
                  data-purecounter-end="12"
                  data-purecounter-duration="1"
                  class="purecounter"
                ></span>
                <p>
                  <span data-en="Expert team" data-fa="تیم متخصص"
                    >Expert team</span
                  >
                </p>
              </div>
            </div>
            <!-- End Team Members Stat -->
          </div>
        </div>
        <!-- End Stats Container -->
      </section>
      <!-- /Stats Section -->

      <!-- ===============================================
      ==================== SKILLS SECTION ====================
      =============================================== -->
      <section id="skills" class="skills section light-background">
        <div class="container section-title" data-aos="fade-up">
          <h2 data-en="{{ data_get($skillsContent, 'title_en', 'Skills') }}" data-fa="{{ data_get($skillsContent, 'title_fa', 'مهارت‌ها') }}">{{ data_get($skillsContent, 'title_en', 'Skills') }}</h2>
          <p data-en="{{ data_get($skillsContent, 'intro_en', '') }}" data-fa="{{ data_get($skillsContent, 'intro_fa', '') }}">{{ data_get($skillsContent, 'intro_en', '') }}</p>
        </div>
        <div class="container" data-aos="fade-up" data-aos-delay="100">
          <div class="skills-board skills-content skills-animation">
            @foreach (data_get($skillsContent, 'groups', []) as $group)
              <article class="skill-group" data-skill-domain="{{ $group['key'] ?? '' }}">
                <h3 data-en="{{ $group['title_en'] ?? '' }}" data-fa="{{ $group['title_fa'] ?? ($group['title_en'] ?? '') }}">{{ $group['title_en'] ?? '' }}</h3>
                @foreach ($group['items'] ?? [] as $skill)
                  @php($skillValue = max(0, min(100, (int) ($skill['value'] ?? 0))))
                  <div class="progress">
                    <span class="skill"><span data-en="{{ $skill['name_en'] ?? '' }}" data-fa="{{ $skill['name_fa'] ?? ($skill['name_en'] ?? '') }}">{{ $skill['name_en'] ?? '' }}</span> <i class="val">{{ $skillValue }}%</i></span>
                    <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="{{ $skillValue }}" aria-valuemin="0" aria-valuemax="100"></div></div>
                  </div>
                @endforeach
              </article>
            @endforeach
          </div>
        </div>
      </section>
      <!-- /Skills Section -->

      <!-- ===============================================
      ==================== RESUME SECTION =================
      =============================================== -->
      <section id="resume" class="resume section">
        <div class="container section-title" data-aos="fade-up">
          <h2 data-en="{{ data_get($resumeContent, 'title_en', 'Resume') }}" data-fa="{{ data_get($resumeContent, 'title_fa', 'رزومه') }}">{{ data_get($resumeContent, 'title_en', 'Resume') }}</h2>
          <p data-en="{{ data_get($resumeContent, 'intro_en', '') }}" data-fa="{{ data_get($resumeContent, 'intro_fa', '') }}">{{ data_get($resumeContent, 'intro_en', '') }}</p>
        </div>
        <div class="container">
          <div class="row">
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
              <h3 class="resume-title" data-en="Education" data-fa="تحصیلات">Education</h3>
              @foreach (data_get($resumeContent, 'education', []) as $item)
                <div class="resume-item" data-resume-kind="education">
                  <h4 data-en="{{ $item['title_en'] ?? '' }}" data-fa="{{ $item['title_fa'] ?? ($item['title_en'] ?? '') }}">{{ $item['title_en'] ?? '' }}</h4>
                  <h5>{{ $item['period'] ?? '' }}</h5>
                  <p><em><a href="{{ $item['url'] ?? '#' }}" target="_blank" rel="noopener" data-en="{{ $item['org_en'] ?? '' }}" data-fa="{{ $item['org_fa'] ?? ($item['org_en'] ?? '') }}">{{ $item['org_en'] ?? '' }}</a></em></p>
                </div>
              @endforeach
            </div>
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
              <h3 class="resume-title" data-en="Professional Experience" data-fa="تجربه کاری">Professional Experience</h3>
              @foreach (data_get($resumeContent, 'experience', []) as $item)
                <div class="resume-item" data-resume-kind="experience">
                  <h4 data-en="{{ $item['title_en'] ?? '' }}" data-fa="{{ $item['title_fa'] ?? ($item['title_en'] ?? '') }}">{{ $item['title_en'] ?? '' }}</h4>
                  <h5 data-en="{{ $item['period'] ?? '' }}" data-fa="{{ $item['period_fa'] ?? ($item['period'] ?? '') }}">{{ $item['period'] ?? '' }}</h5>
                  <p><em><a href="{{ $item['url'] ?? '#' }}" target="_blank" rel="noopener" data-en="{{ $item['org_en'] ?? '' }}" data-fa="{{ $item['org_fa'] ?? ($item['org_en'] ?? '') }}">{{ $item['org_en'] ?? '' }}</a></em></p>
                  @if (! empty($item['body_en']) || ! empty($item['body_fa']))
                    <p data-en="{{ $item['body_en'] ?? '' }}" data-fa="{{ $item['body_fa'] ?? ($item['body_en'] ?? '') }}">{{ $item['body_en'] ?? '' }}</p>
                  @endif
                  @if (! empty($item['highlights']))
                    <ul>
                      @foreach ($item['highlights'] as $highlight)
                        <li data-en="{{ $highlight['en'] ?? '' }}" data-fa="{{ $highlight['fa'] ?? ($highlight['en'] ?? '') }}">{{ $highlight['en'] ?? '' }}</li>
                      @endforeach
                    </ul>
                  @endif
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </section>
      <!-- /Resume Section -->

      <!-- ===============================================
      ==================== SERVICES SECTION ===============
      =============================================== -->
                  <section id="services" class="services section">
        <!-- Section Title -->
        <div class="container section-title" data-aos="fade-up">
          <h2 data-en="Services" data-fa="خدمات">Services</h2>
          <p
            data-en="What I actually implement and support: enterprise networks, servers, virtualization, SQL, Jira, monitoring, DevOps, VoIP, CCTV, and security."
            data-fa="آنچه واقعاً پیاده‌سازی و پشتیبانی می‌کنم: شبکه سازمانی، سرور، مجازی‌سازی، SQL، Jira، مانیتورینگ، DevOps، VoIP، دوربین مداربسته و امنیت."
          >
            What I actually implement and support: enterprise networks, servers,
            virtualization, SQL, Jira, monitoring, DevOps, VoIP, CCTV, and
            security.
          </p>
        </div>
        <!-- End Section Title -->

        <!-- ===============================================
        ==================== SERVICES CONTENT ================
        =============================================== -->
        <div class="container">
          
          <div class="row gy-4" id="service-catalog">
            @forelse ($services as $service)
              @include('components.service-card', ['service' => $service])
            @empty
            @endforelse
          </div>
                    @include('partials.service-drawer')
          <!-- End Service Catalog -->

          <!-- End Services Row -->
        </div>
        <!-- End Services Container -->
      </section>
      <!-- /Services Section -->

      <!-- ===============================================
      ==================== ARTICLES SECTION ===============
      =============================================== -->
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
            
            @include('articles.partials.library-toolbar', ['showCategoryFilters' => true])

            <!-- End Filter Buttons -->

            <!-- ===============================================
            ==================== ARTICLES GRID ==================
            =============================================== -->
            
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
        <!-- End Main Container -->
      </section>
      <!-- End Articles Section -->

      
      @include('partials.testimonials')

      <!-- /Testimonials Section -->
      <!-- /Testimonials Section -->

      <!-- ===============================================
      ==================== CONTACT SECTION ================
      =============================================== -->
      <section id="contact" class="contact section">
        <!-- Section Title -->
        <div class="container section-title" data-aos="fade-up">
          <h2 data-en="{{ data_get($contactContent, 'title_en', 'Contact') }}" data-fa="{{ data_get($contactContent, 'title_fa', 'تماس با من') }}">{{ data_get($contactContent, 'title_en', 'Contact') }}</h2>
          <p data-en="{{ data_get($contactContent, 'intro_en', '') }}" data-fa="{{ data_get($contactContent, 'intro_fa', '') }}">{{ data_get($contactContent, 'intro_en', '') }}</p>
        </div>
        <!-- End Section Title -->

        <!-- ===============================================
        ==================== CONTACT CONTENT ================
        =============================================== -->
        <div class="container" data-aos="fade-up" data-aos-delay="100">
          <div class="contact-wrapper">
            <!-- Contact Header -->
            <div class="contact-header" data-aos="fade-up" data-aos-delay="150">
              <div class="contact-intro">
                <h3 class="contact-intro-title">
                  <span
                    data-en="{{ data_get($contactContent, 'heading_en', "Let's Work Together") }}"
                    data-fa="{{ data_get($contactContent, 'heading_fa', 'بیایید با هم کار کنیم') }}"
                    >{{ data_get($contactContent, 'heading_en', "Let's Work Together") }}</span
                  >
                      <i class="bi bi-people-fill title-accent" aria-hidden="true"></i>
                </h3>
                <p
                  class="contact-intro-text"
                  data-en="{{ data_get($contactContent, 'body_en', '') }}"
                  data-fa="{{ data_get($contactContent, 'body_fa', '') }}"
                >
                  {{ data_get($contactContent, 'body_en', '') }}
                </p>
              </div>
            </div>

            <!-- Contact Main Grid -->
            <div class="contact-main-grid">
              <!-- Left Column - Contact Methods -->
              <div class="contact-methods">
                <!-- Quick Contact Cards -->
                <div class="contact-cards-grid">
                  @foreach (data_get($contactContent, 'items', []) as $card)
                    <div class="contact-card {{ ($card['key'] ?? 'contact') }}-card" data-aos="fade-right">
                      <div class="card-icon"><i class="{{ $card['icon'] ?? 'bi bi-chat' }}"></i></div>
                      <div class="card-content">
                        <h4 data-en="{{ $card['title_en'] ?? '' }}" data-fa="{{ $card['title_fa'] ?? ($card['title_en'] ?? '') }}">{{ $card['title_en'] ?? '' }}</h4>
                        <p data-en="{{ $card['body_en'] ?? '' }}" data-fa="{{ $card['body_fa'] ?? ($card['body_en'] ?? '') }}">{{ $card['body_en'] ?? '' }}</p>
                        <a href="{{ $card['href'] ?? ($card['value'] ?? '#') }}" class="contact-link" @if (str_starts_with((string) ($card['href'] ?? ''), 'http')) target="_blank" rel="noopener" @endif>
                          <span data-en="{{ $card['cta_en'] ?? ($card['value'] ?? '') }}" data-fa="{{ $card['cta_fa'] ?? ($card['cta_en'] ?? ($card['value'] ?? '')) }}">{{ $card['cta_en'] ?? ($card['value'] ?? '') }}</span>
                          <i class="bi bi-arrow-right"></i>
                        </a>
                      </div>
                    </div>
                  @endforeach
                </div>

                <!-- Location Info -->
                <div
                  class="location-info"
                  data-aos="fade-right"
                  data-aos-delay="400"
                >
                  <div class="location-card">
                    <div class="location-header">
                      <i class="bi bi-geo-alt"></i>
                      <h4 data-en="My Location" data-fa="موقعیت من">
                        My Location
                      </h4>
                    </div>
                    <div class="location-content">
                      <p data-en="{{ data_get($contactContent, 'location_en', 'Tehran, Iran') }}" data-fa="{{ data_get($contactContent, 'location_fa', 'تهران، ایران') }}">{{ data_get($contactContent, 'location_en', 'Tehran, Iran') }}</p>
                      <div class="map-container">
                        <iframe
                          src="{{ data_get($contactContent, 'map_url', '') }}"
                          frameborder="0"
                          style="
                            border: 0;
                            width: 100%;
                            height: 200px;
                            border-radius: 12px;
                          "
                          allowfullscreen=""
                          loading="lazy"
                          referrerpolicy="no-referrer-when-downgrade"
                          title="Google Maps - Meet AJ"
                        ></iframe>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Right Column - Contact Form -->
              <div
                class="contact-form-section"
                data-aos="fade-left"
                data-aos-delay="200"
              >
                <div class="form-container">
                  <div class="form-header">
                    <h3 class="form-title">
                      <span data-en="Send a Message" data-fa="ارسال پیام"
                        >Send a Message</span
                      >
                      <i class="bi bi-chat-dots-fill title-accent" aria-hidden="true"></i>
                    </h3>
                    <p
                      class="form-subtitle"
                      data-en="Fill out the form below and I'll get back to you within 24 hours"
                      data-fa="فرم زیر را پر کنید و من ظرف ۲۴ ساعت به شما پاسخ خواهم داد"
                    >
                      Fill out the form below and I'll get back to you within 24
                      hours
                    </p>
                  </div>

                  <form
                    action="/forms/contact.php"
                    method="post"
                    class="php-email-form"
                  >
                    
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="csrf_token" id="csrf_token" value="{{ csrf_token() }}">

                    <input
                      type="hidden"
                      name="service"
                      id="contact-service-slug"
                      value=""
                    />
                    <!-- Honeypot field for bot detection (hidden from humans via CSS) -->
                    <div
                      class="form-group honeypot"
                      aria-hidden="true"
                      style="display: none"
                    >
                      <label for="website-field">Website</label>
                      <input
                        type="text"
                        name="website"
                        id="website-field"
                        tabindex="-1"
                        autocomplete="off"
                      />
                    </div>
                    <div class="form-row">
                      <div class="form-group">
                        <label for="name-field" class="form-label">
                          <i class="bi bi-person"></i>
                          <span data-en="Your Name" data-fa="نام شما"
                            >Your Name</span
                          >
                        </label>
                        <input
                          type="text"
                          name="name"
                          id="name-field"
                          class="form-input"
                          placeholder="Enter your full name"
                          required
                          minlength="2"
                          maxlength="50"
                        />
                      </div>

                      <div class="form-group">
                        <label for="email-field" class="form-label">
                          <i class="bi bi-envelope"></i>
                          <span data-en="Your Email" data-fa="ایمیل شما"
                            >Your Email</span
                          >
                        </label>
                        <input
                          type="email"
                          class="form-input"
                          name="email"
                          id="email-field"
                          placeholder="Enter your email address"
                          required
                          maxlength="100"
                        />
                      </div>
                    </div>

                                          <div class="form-group">
                        <label for="phone-field" class="form-label">
                          <i class="bi bi-telephone"></i>
                          <span data-en="Your Phone" data-fa="شماره تلفن">Your Phone</span>
                        </label>
                        <input
                          type="tel"
                          class="form-input"
                          name="phone"
                          id="phone-field"
                          placeholder="Optional phone number"
                          data-en-placeholder="Optional phone number"
                          data-fa-placeholder="شماره تلفن (اختیاری)"
                          maxlength="40"
                          autocomplete="tel"
                        />
                      </div>
                    <div class="form-group">
                      <label for="subject-field" class="form-label">
                        <i class="bi bi-tag"></i>
                        <span data-en="Subject" data-fa="موضوع">Subject</span>
                      </label>
                      <input
                        type="text"
                        class="form-input"
                        name="subject"
                        id="subject-field"
                        placeholder="What's this about?"
                        required
                        minlength="5"
                        maxlength="100"
                      />
                    </div>

                    <div class="form-group">
                      <label for="message-field" class="form-label">
                        <i class="bi bi-chat-text"></i>
                        <span data-en="Message" data-fa="پیام">Message</span>
                      </label>
                      <textarea
                        class="form-textarea"
                        name="message"
                        rows="6"
                        id="message-field"
                        placeholder="Tell me about your project or requirements..."
                        required
                        minlength="10"
                        maxlength="1000"
                      ></textarea>
                    </div>

                    <div class="form-actions">
                      <div
                        class="form-status"
                        aria-live="polite"
                        aria-atomic="true"
                      >
                        <div
                          class="loading"
                          data-en="Sending..."
                          data-fa="در حال ارسال..."
                          role="status"
                          aria-live="polite"
                          aria-busy="true"
                        >
                          <i
                            class="bi bi-hourglass-split"
                            aria-hidden="true"
                          ></i>
                          <span data-en="Sending..." data-fa="در حال ارسال..."
                            >Sending...</span
                          >
                        </div>
                        <div
                          class="error-message"
                          role="alert"
                          aria-live="assertive"
                          tabindex="-1"
                        >
                          <i
                            class="bi bi-exclamation-triangle"
                            aria-hidden="true"
                          ></i>
                          <span>Error sending message. Please try again.</span>
                        </div>
                        <div
                          class="sent-message"
                          data-en="Message sent successfully! I'll get back to you soon."
                          data-fa="پیام با موفقیت ارسال شد! به زودی به شما پاسخ خواهم داد."
                          role="status"
                          aria-live="polite"
                        >
                          <i class="bi bi-check-circle" aria-hidden="true"></i>
                          <span
                            data-en="Message sent successfully! I'll get back to you soon."
                            data-fa="پیام با موفقیت ارسال شد! به زودی به شما پاسخ خواهم داد."
                            >Message sent successfully! I'll get back to you
                            soon.</span
                          >
                        </div>
                      </div>

                      <button type="submit" class="submit-btn">
                        <span data-en="Send Message" data-fa="ارسال پیام"
                          >Send Message</span
                        >
                        <i class="bi bi-send"></i>
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- End Contact Container -->
      </section>
      <!-- /Contact Section -->
    </main>

    <!-- ===============================================
    ==================== FOOTER SECTION ==================
    =============================================== -->
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
    <!-- End Footer Section -->

    <!-- ===============================================
    PAGE PRELOADER - Meet AJ Ambient Reveal
    =============================================== -->
    <div id="preloader" class="preloader-overlay visible" role="status" aria-live="polite" aria-busy="true">
      <div id="preloader-container" class="preloader-container ltr">
        <div class="preloader-ambient preloader-ambient-a" aria-hidden="true"></div>
        <div class="preloader-ambient preloader-ambient-b" aria-hidden="true"></div>

        <div class="preloader-brandmark" aria-hidden="true">
          <div class="preloader-brandmark-core"><span>AJ</span></div>
          <span class="preloader-orbit-dot preloader-orbit-dot-a"></span>
          <span class="preloader-orbit-dot preloader-orbit-dot-b"></span>
        </div>

        <div class="preloader-wordmark">
          <strong>Meet AJ</strong>
          <span data-en="Infrastructure &amp; DevOps" data-fa="Infrastructure و DevOps">Infrastructure &amp; DevOps</span>
        </div>

        <div class="preloader-status-line">
          <span id="loading-text" class="loading-text" data-en="Preparing your experience" data-fa="در حال آماده‌سازی تجربه شما">Preparing your experience</span>
          <span class="preloader-status-dot" aria-hidden="true"></span>
        </div>
        <div class="preloader-progress" role="progressbar" aria-label="Loading">
          <span class="progress-line"></span>
        </div>
      </div>
    </div>

    <!-- ===============================================
    JAVASCRIPT LIBRARIES & SCRIPTS
    =============================================== -->

    <!-- Bootstrap JavaScript Bundle -->
    <script
      src="/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"
      defer
    ></script>

    <!-- AOS Animation Library -->
    <script src="/assets/vendor/aos/aos.js" defer></script>

    <!-- Typed.js Text Animation -->
    <script src="/assets/vendor/typed.js/typed.umd.js" defer></script>

    <!-- PureCounter Counter Animation -->
    <script
      src="/assets/vendor/purecounter/purecounter_vanilla.js"
      defer
    ></script>

    <!-- Waypoints Scroll Triggers -->
    <script
      src="/assets/vendor/waypoints/noframework.waypoints.js"
      defer
    ></script>

    <!-- GLightbox Gallery -->
    <script src="/assets/vendor/glightbox/js/glightbox.min.js" defer></script>

    <!-- ImagesLoaded Plugin -->
    <script
      src="/assets/vendor/imagesloaded/imagesloaded.pkgd.min.js"
      defer
    ></script>

    <!-- Isotope Layout Plugin -->
    <script
      src="/assets/vendor/isotope-layout/isotope.pkgd.min.js"
      defer
    ></script>

    <!-- Swiper Slider -->
    <script src="/assets/vendor/swiper/swiper-bundle.min.js" defer></script>

    <!-- ===============================================
    CUSTOM JAVASCRIPT MODULES
    =============================================== -->

    <!-- Main Application JavaScript -->
    <script src="/assets/js/contact-form.js?v=1403" defer></script>
    <script src="/assets/js/main.js?v=1414" defer></script>
    <script src="/assets/js/service-catalog.js?v=1813" defer></script>

    <!-- Internationalization (i18n) Support -->
    <!-- Language Toggle JavaScript -->
    <script src="/assets/js/i18n.js?v=1403" defer></script>

    <!-- Progressive image loading for non-critical media -->
    <script>
      document.addEventListener("DOMContentLoaded", () => {
        document.querySelectorAll("img:not([loading])").forEach((img) => {
          const priority = (
            img.getAttribute("fetchpriority") || ""
          ).toLowerCase();
          if (priority === "high") return;
          img.setAttribute("loading", "lazy");
          if (!img.hasAttribute("decoding")) {
            img.setAttribute("decoding", "async");
          }
        });
      });
    </script>

    <!-- ===============================================
    SERVICE WORKER REGISTRATION
    =============================================== -->

    <script>
      // ===============================================
      // SERVICE WORKER REGISTRATION FOR PWA
      // ===============================================
      if ("serviceWorker" in navigator) {
        window.addEventListener("load", function () {
          navigator.serviceWorker
            .register("/sw.js")
            .then(function (registration) {
              // Service Worker registered successfully
              console.log("PWA Service Worker registered");
            })
            .catch(function (error) {
              // Service Worker registration failed silently
              console.warn("PWA Service Worker registration failed:", error);
            });
        });
      }
    </script>
  </body>
</html>

<!-- ===============================================
END OF MEET AJ PORTFOLIO HTML DOCUMENT
=============================================== -->
