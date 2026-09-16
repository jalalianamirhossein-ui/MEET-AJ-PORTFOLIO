@verbatim
﻿<!doctype html>
<html lang="en" dir="ltr">
  <head>
    <!-- ===============================================
    MEET AJ PORTFOLIO - MAIN HTML DOCUMENT
    ===============================================
    
    Professional portfolio website for AmirHossein Jalalian
    Network Expert, DevOps Engineer & IT Infrastructure Specialist
    
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
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />

    <!-- Page Title & SEO Meta Tags -->
    <title>Meet Aj - AmirHossein Jalalian Portfolio</title>
    <meta
      content="Professional portfolio of AmirHossein Jalalian - Network Expert, DevOps Engineer, and IT Infrastructure Specialist. Experienced in Cisco, MikroTik, VMware, Linux administration, and system optimization."
      name="description"
    />
    <meta
      content="Network Expert, DevOps Engineer, IT Infrastructure, Cisco, MikroTik, VMware, Linux, System Administration, Tehran Iran"
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
      content="Professional portfolio of AmirHossein Jalalian - Network Expert, DevOps Engineer, and IT Infrastructure Specialist. Experienced in Cisco, MikroTik, VMware, Linux administration, and system optimization."
    />
    <meta property="og:url" content="https://meetaj.ir/" />
    <meta
      property="og:image"
      content="https://meetaj.ir/assets/img/hero-bg.jpg"
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
      content="Professional portfolio of AmirHossein Jalalian - Network Expert, DevOps Engineer, and IT Infrastructure Specialist."
    />
    <meta
      name="twitter:image"
      content="https://meetaj.ir/assets/img/hero-bg.jpg"
    />
    <script type="application/ld+json">
      {
        "@context": "https://schema.org",
        "@type": "Person",
        "name": "AmirHossein Jalalian",
        "url": "https://meetaj.ir/",
        "jobTitle": "Network Expert & DevOps Engineer",
        "image": "https://meetaj.ir/assets/img/my-profile-img-2.jpg",
        "sameAs": [
          "https://www.linkedin.com/in/amirhosseinjalalian",
          "https://github.com/amirhosseinjalalian",
          "https://instagram.com/aj.mercury"
        ]
      }
    </script>
    <script type="application/ld+json">
      {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "url": "https://meetaj.ir/",
        "name": "Meet AJ - AmirHossein Jalalian Portfolio",
        "inLanguage": "en",
        "potentialAction": {
          "@type": "SearchAction",
          "target": "https://www.google.com/search?q=site:meetaj.ir+{search_term_string}",
          "query-input": "required name=search_term_string"
        }
      }
    </script>
    <script type="application/ld+json">
      {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
          {
            "@type": "ListItem",
            "position": 1,
            "name": "Home",
            "item": "https://meetaj.ir/#hero"
          },
          {
            "@type": "ListItem",
            "position": 2,
            "name": "About",
            "item": "https://meetaj.ir/#about"
          },
          {
            "@type": "ListItem",
            "position": 3,
            "name": "Resume",
            "item": "https://meetaj.ir/#resume"
          },
          {
            "@type": "ListItem",
            "position": 4,
            "name": "Services",
            "item": "https://meetaj.ir/#services"
          },
          {
            "@type": "ListItem",
            "position": 5,
            "name": "Articles",
            "item": "https://meetaj.ir/#portfolio"
          },
          {
            "@type": "ListItem",
            "position": 6,
            "name": "Testimonials",
            "item": "https://meetaj.ir/#testimonials"
          },
          {
            "@type": "ListItem",
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
    <link href="/assets/css/main.css?v=1000" rel="preload" as="style" />

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
    <link href="/assets/css/main.css?v=1000" rel="stylesheet" />

    <!-- Language Toggle Stylesheet -->
    <link href="/assets/css/lang-toggle.css?v=1115" rel="stylesheet" />

    <!-- RTL Support Stylesheet -->
    <link
      id="rtl-style"
      href="/assets/css/rtl.css?v=1000"
      rel="stylesheet"
      disabled
    />
    <link href="/assets/css/visual-upgrade.css?v=1119" rel="stylesheet" />

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
          align-items: center;
          justify-content: center;
          text-align: center;
          padding: 3rem 1.5rem;
          gap: 1.25rem;
        }

        .hero-title {
          font-size: 2.2rem;
          color: #1e40af;
        }

        .hero-subtitle {
          font-size: 1rem;
          color: #374151;
        }

        .hero .hero-actions {
          display: flex;
          flex-direction: column;
          align-items: center;
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
          padding: 2.5rem 1rem;
        }

        .hero-title {
          font-size: 1.8rem;
        }

        .hero-subtitle {
          font-size: 0.95rem;
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
    <a class="skip-link" href="#main-content">Skip to main content</a>
    <!-- ===============================================
    ==================== HEADER SECTION ================
    =============================================== -->
    <header id="header" class="header dark-background d-flex flex-column">
      <!-- ===============================================
      ==================== PROFILE IMAGE =================
      =============================================== -->
      <div class="profile-img">
        <img
          src="/assets/img/my-profile-img.jpg"
          alt="AmirHossein Jalalian Profile Picture"
          class="img-fluid rounded-circle"
          width="200"
          height="200"
          decoding="async"
          fetchpriority="high"
          sizes="120px"
        />
      </div>

      <!-- ===============================================
      ==================== LOGO SECTION ==================
      =============================================== -->
      <div
        class="logo-section d-flex align-items-center justify-content-between"
      >
        <!-- Site Logo & Name -->
        <a href="#hero" class="logo d-flex align-items-center">
          <img
            src="/assets/img/logo.png"
            alt="Aj-Network"
            width="40"
            height="40"
            decoding="async"
            fetchpriority="high"
            sizes="40px"
          />
          <div class="sitename">Meet AJ</div>
        </a>
      </div>

      <!-- ===============================================
      ==================== SOCIAL LINKS ===================
      =============================================== -->
      <div class="social-links text-center">
        <!-- Primary Social Links Row -->
        <div class="social-row social-row-main">
          <a
            href="https://www.linkedin.com/in/amirhussein-jalalian-050702188/"
            class="linkedin"
            target="_blank"
            rel="noopener"
            aria-label="LinkedIn"
            ><i class="bi bi-linkedin"></i
          ></a>
          <a
            href="https://github.com/jalalianamirhossein-ui"
            class="github"
            target="_blank"
            rel="noopener"
            aria-label="GitHub"
            ><i class="bi bi-github"></i
          ></a>
          <a
            href="https://wa.me/989197276219"
            class="whatsapp"
            target="_blank"
            rel="noopener"
            aria-label="WhatsApp"
            ><i class="bi bi-whatsapp"></i
          ></a>
          <a
            href="https://t.me/Aj_mercury"
            class="telegram"
            target="_blank"
            rel="noopener"
            aria-label="Telegram"
            ><i class="bi bi-telegram"></i
          ></a>
        </div>

        <!-- ردیف دوم: بقیه -->
        <div class="social-row social-row-secondary">
          <a
            href="https://twitter.com/RealAjMercury"
            class="twitter"
            target="_blank"
            rel="noopener"
            aria-label="X (Twitter)"
            ><svg
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="currentColor"
            >
              <path
                d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"
              />
            </svg>
          </a>
          <a
            href="https://stackoverflow.com/users/24522280/amir-jalalian"
            class="stackoverflow"
            target="_blank"
            rel="noopener"
            aria-label="Stack Overflow"
            ><i class="bi bi-stack-overflow"></i
          ></a>
          <a
            href="https://www.facebook.com/amir.jalalian.37"
            class="facebook"
            target="_blank"
            rel="noopener"
            aria-label="Facebook"
            ><i class="bi bi-facebook"></i
          ></a>
          <a
            href="https://instagram.com/aj.mercury"
            class="instagram"
            target="_blank"
            rel="noopener"
            aria-label="Instagram"
            ><i class="bi bi-instagram"></i
          ></a>
          <a href="tel:+989197276219" class="google-plus" aria-label="Phone"
            ><i class="bi bi-telephone"></i
          ></a>
        </div>
      </div>

      <!-- ===============================================
      ==================== NAVIGATION MENU ================
      =============================================== -->
      <nav id="navmenu" class="navmenu" role="navigation" aria-label="Primary">
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
              ><span data-en="Resume" data-fa="رزومه کاری">Resume</span></a
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
          src="/assets/img/hero-bg.jpg"
          alt="Network infrastructure inspired hero background"
          data-aos="fade-in"
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
                    data-en="Available for Work"
                    data-fa="آماده برای همکاری"
                    >Available for Work</span
                  >
                  <div class="badge-dot"></div>
                </div>

                <!-- Main Title with Modern Typography -->
                <h1
                  class="hero-title"
                  data-en="AmirHossein Jalalian"
                  data-fa="امیرحسین جلالیان"
                  data-aos="fade-up"
                  data-aos-delay="400"
                >
                  <span
                    class="title-line-1"
                    data-en="AmirHossein"
                    data-fa="امیرحسین"
                    >AmirHossein</span
                  >
                  <span
                    class="title-line-2"
                    data-en="Jalalian"
                    data-fa="جلالیان"
                    >Jalalian</span
                  >
                </h1>
                <p
                  class="hero-role"
                  data-en="Network and IT Infrastructure Specialist"
                  data-fa="متخصص شبکه و زیرساخت فناوری اطلاعات"
                >
                  Network and IT Infrastructure Specialist
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
                      data-typed-items="Network Specialist, DevOps Engineer, IT Consultant, Systems Expert, VMware Administrator"
                      data-typed-items-fa="متخصص شبکه هستم, مهندس DevOps هستم, مشاور فناوری اطلاعات هستم, کارشناس سیستم‌ها هستم, مدیر مجازی‌سازی VMware هستم"
                      aria-live="polite"
                      aria-atomic="true"
                      >Systems Administrator</span
                    >
                  </p>
                </div>

                <!-- Modern Action Buttons -->
                <div
                  class="hero-actions"
                  data-aos="fade-up"
                  data-aos-delay="1000"
                >
                  <a
                    href="#contact"
                    class="btn btn-primary btn-modern"
                    data-en="Get In Touch"
                    data-fa="ارتباط با من"
                  >
                    <span
                      class="btn-text"
                      data-en="Get In Touch"
                      data-fa="ارتباط با من"
                      >Get In Touch</span
                    >
                    <span class="btn-icon">→</span>
                  </a>
                  <a
                    href="#portfolio"
                    class="btn btn-outline-light btn-modern"
                    data-en="Articles"
                    data-fa="مقالات"
                  >
                    <span class="btn-text" data-en="Articles" data-fa="مقالات"
                      >Articles</span
                    >
                    <span class="btn-icon">→</span>
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
            <p class="about-kicker" data-en="IT Infrastructure Specialist" data-fa="متخصص زیرساخت فناوری اطلاعات">IT Infrastructure Specialist</p>
            <h2 data-en="Get to Know Me" data-fa="بهتر منو بشناس">Get to Know Me</h2>
            <p class="about-headline" data-en="Designing, managing, and optimizing enterprise systems." data-fa="طراحی، مدیریت و بهینه‌سازی سیستم‌های سازمانی.">Designing, managing, and optimizing enterprise systems.</p>
            <div class="about-profile">
              <div class="about-photo">
                <img src="/assets/img/my-profile-img-2.jpg" alt="Amirhossein Jalalian Profile" width="200" height="200" loading="lazy" sizes="120px" />
              </div>
              <div class="about-profile-meta">
                <p class="about-name" data-en="Amirhossein Jalalian" data-fa="امیرحسین جلالیان">Amirhossein Jalalian</p>
                <p class="about-role" data-en="Network and IT Infrastructure Specialist" data-fa="متخصص شبکه و زیرساخت فناوری اطلاعات">Network and IT Infrastructure Specialist</p>
                <p class="about-place"><i class="bi bi-geo-alt" aria-hidden="true"></i> <span data-en="Tehran, Iran" data-fa="تهران، ایران">Tehran, Iran</span></p>
              </div>
            </div>
            <p class="about-lead" data-en="I am Amirhossein Jalalian, a Network and IT Infrastructure Specialist with extensive experience in designing, managing, and optimizing enterprise systems. My goal is to provide reliable, secure, and scalable solutions that help organizations operate more efficiently while reducing risks." data-fa="من امیرحسین جلالیان هستم، متخصص شبکه و زیرساخت فناوری اطلاعات با تجربه گسترده در طراحی، مدیریت و بهینه‌سازی سیستم‌های سازمانی. هدف من ارائه راهکارهای پایدار، امن و مقیاس‌پذیر است که به سازمان‌ها کمک می‌کند کارآیی بیشتری داشته باشند و در عین حال ریسک‌ها را کاهش دهند.">
              I am Amirhossein Jalalian, a Network and IT Infrastructure Specialist with extensive experience in designing, managing, and optimizing enterprise systems. My goal is to provide reliable, secure, and scalable solutions that help organizations operate more efficiently while reducing risks.
            </p>
            <p class="about-lead" data-en="I have extensive experience in Windows Server, Cisco and MikroTik technologies, virtualization, and other IT infrastructure solutions. My approach is always results-driven, carrying out each project with accountability, precision, and strong teamwork." data-fa="من تجربه‌ای گسترده در Windows Server، تجهیزات سیسکو و میکروتیک، مجازی‌سازی و سایر فناوری‌های زیرساختی دارم. رویکرد من در کار، همواره نتیجه‌محور است و هر پروژه را با مسئولیت‌پذیری بالا، دقت فنی و روحیه کار تیمی به سرانجام می‌رسانم.">
              I have extensive experience in Windows Server, Cisco and MikroTik technologies, virtualization, and other IT infrastructure solutions. My approach is always results-driven, carrying out each project with accountability, precision, and strong teamwork.
            </p>
            <dl class="about-facts">
              <div>
                <dt data-en="Birthday" data-fa="تاریخ تولد">Birthday</dt>
                <dd data-en="19 July 1999" data-fa="۲۸ تیر ۱۳۷۸">19 July 1999</dd>
              </div>
              <div>
                <dt data-en="Degree" data-fa="مدرک تحصیلی">Degree</dt>
                <dd data-en="Bachelor of IT Engineering" data-fa="کارشناسی مهندسی فناوری اطلاعات">Bachelor of IT Engineering</dd>
              </div>
              <div>
                <dt data-en="Experience" data-fa="تجربه">Experience</dt>
                <dd data-en="5+ Years" data-fa="بیش از ۵ سال">5+ Years</dd>
              </div>
            </dl>
            <div class="about-actions">
              <a class="btn btn-primary" href="#contact" data-en="Get In Touch" data-fa="ارتباط با من">Get In Touch</a>
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
                <p data-panel="core" class="is-active" data-en="Reliable, secure, and scalable solutions for enterprise systems — Windows Server, Cisco, MikroTik, and virtualization." data-fa="راهکارهای پایدار، امن و مقیاس‌پذیر برای سیستم‌های سازمانی — Windows Server، سیسکو، میکروتیک و مجازی‌سازی.">Reliable, secure, and scalable solutions for enterprise systems — Windows Server, Cisco, MikroTik, and virtualization.</p>
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

        <div class="container about-domains" data-aos="fade-up">
          <h3 class="about-domains-title" data-en="Expertise" data-fa="تخصص‌ها">Expertise</h3>
          <div class="about-domain-grid">
            <section class="about-domain">
              <h4 data-en="Infrastructure" data-fa="زیرساخت">Infrastructure</h4>
              <ul>
                <li>Linux</li>
                <li>Windows Server</li>
                <li>VMware</li>
                <li>KVM</li>
              </ul>
            </section>
            <section class="about-domain">
              <h4 data-en="Networking" data-fa="شبکه">Networking</h4>
              <ul>
                <li>Cisco</li>
                <li>MikroTik</li>
                <li>VPN</li>
                <li>VoIP</li>
              </ul>
            </section>
            <section class="about-domain">
              <h4 data-en="DevOps" data-fa="دواپس">DevOps</h4>
              <ul>
                <li>Docker</li>
                <li>CI/CD</li>
                <li>Jenkins</li>
                <li>GitLab</li>
                <li>Ansible</li>
                <li>Terraform</li>
              </ul>
            </section>
            <section class="about-domain">
              <h4 data-en="Monitoring" data-fa="مانیتورینگ">Monitoring</h4>
              <ul>
                <li>Zabbix</li>
                <li>Grafana</li>
                <li>Cacti</li>
                <li>Redgate</li>
              </ul>
            </section>
            <section class="about-domain">
              <h4 data-en="Security" data-fa="امنیت">Security</h4>
              <ul>
                <li data-en="Firewall &amp; security rules" data-fa="فایروال و قوانین امنیتی">Firewall &amp; security rules</li>
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
              <li>
                <h4 data-en="Integrity" data-fa="صداقت">Integrity</h4>
                <p data-en="Honest and transparent in all professional interactions" data-fa="صادق و شفاف در تمام تعاملات حرفه‌ای">Honest and transparent in all professional interactions</p>
              </li>
              <li>
                <h4 data-en="Excellence" data-fa="تعالی">Excellence</h4>
                <p data-en="Committed to delivering the highest quality solutions" data-fa="متعهد به ارائه راهکارهای با بالاترین کیفیت">Committed to delivering the highest quality solutions</p>
              </li>
              <li>
                <h4 data-en="Collaboration" data-fa="همکاری">Collaboration</h4>
                <p data-en="Strong believer in teamwork and collective success" data-fa="باور قوی به کار تیمی و موفقیت جمعی">Strong believer in teamwork and collective success</p>
              </li>
              <li>
                <h4 data-en="Innovation" data-fa="نوآوری">Innovation</h4>
                <p data-en="Continuously learning and adapting to new technologies" data-fa="یادگیری مداوم و سازگاری با فناوری‌های جدید">Continuously learning and adapting to new technologies</p>
              </li>
            </ul>
          </div>
          <blockquote class="about-quote">
            <p data-en="Success is born of sustained effort, continuous learning, and faith in the journey." data-fa="موفقیت زاده تلاش مستمر، یادگیری مداوم و ایمان به مسیر است.">Success is born of sustained effort, continuous learning, and faith in the journey.</p>
            <footer data-en="— My Personal Motto" data-fa="— شعار شخصی من">— My Personal Motto</footer>
          </blockquote>
          <div class="about-philosophy">
            <h3 data-en="My Philosophy" data-fa="فلسفه من">My Philosophy</h3>
            <p data-en="I value honesty, patience, and accountability as core principles. I believe that respect for the profession and adherence to fundamental values form the foundation for achieving long-term and sustainable success in the field of Information Technology." data-fa="برای من، صداقت،  صبر و مسئولیت‌پذیری ارزش‌های اساسی هستند. باور دارم که احترام به حرفه و پایبندی به اصول بنیادین، زیربنای دستیابی به موفقیت پایدار و بلندمدت در حوزه فناوری اطلاعات است.">
              I value honesty, patience, and accountability as core principles. I believe that respect for the profession and adherence to fundamental values form the foundation for achieving long-term and sustainable success in the field of Information Technology.
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
                <i class="bi bi-emoji-smile"></i>
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
        <!-- ===============================================
        ==================== SECTION TITLE ==================
        =============================================== -->
        <div class="container section-title" data-aos="fade-up">
          <h2 data-en="Skills" data-fa="مهارت‌ها">Skills</h2>
          <p
            data-en="My core technical and professional skills in computer networks and IT infrastructure."
            data-fa="مهارت‌های اصلی فنی و حرفه‌ای من در شبکه‌های کامپیوتری و زیرساخت فناوری اطلاعات."
          >
            My core technical and professional skills in computer networks and
            IT infrastructure.
          </p>
        </div>
        <!-- End Section Title -->

        <!-- ===============================================
        ==================== SKILLS CONTENT ==================
        =============================================== -->
        <div class="container" data-aos="fade-up" data-aos-delay="100">
          <div class="row skills-content skills-animation">
            <!-- ===============================================
            ==================== LEFT COLUMN ====================
            =============================================== -->
            <div class="col-lg-6">
              <div class="progress">
                <span class="skill">
                  <span
                    data-en="Network Design (Cisco, MikroTik)"
                    data-fa="طراحی شبکه (سیسکو، میکروتیک)"
                    >Network Design (Cisco, MikroTik)</span
                  >
                  <i class="val">100%</i>
                </span>
                <div class="progress-bar-wrap">
                  <div
                    class="progress-bar"
                    role="progressbar"
                    aria-valuenow="100"
                  ></div>
                </div>
              </div>
              <div class="progress">
                <span class="skill">
                  <span
                    data-en="Firewall & Security Rules (MikroTik, Cisco)"
                    data-fa="فایروال و قوانین امنیتی (میکروتیک، سیسکو)"
                    >Firewall & Security Rules (MikroTik, Cisco)</span
                  >
                  <i class="val">92%</i>
                </span>
                <div class="progress-bar-wrap">
                  <div
                    class="progress-bar"
                    role="progressbar"
                    aria-valuenow="92"
                  ></div>
                </div>
              </div>
              <div class="progress">
                <span class="skill">
                  <span
                    data-en="Linux Administration (Ubuntu, CentOS)"
                    data-fa="مدیریت لینوکس (اوبونتو، سنت‌اواس)"
                    >Linux Administration (Ubuntu, CentOS)</span
                  >
                  <i class="val">90%</i>
                </span>
                <div class="progress-bar-wrap">
                  <div
                    class="progress-bar"
                    role="progressbar"
                    aria-valuenow="90"
                  ></div>
                </div>
              </div>
              <div class="progress">
                <span class="skill">
                  <span
                    data-en="Microsoft Services (AD, DNS, DFS, WDS, WSUS, NTP)"
                    data-fa="خدمات مایکروسافت (AD، DNS، DFS، WDS، WSUS، NTP)"
                    >Microsoft Services (AD, DNS, DFS, WDS, WSUS, NTP)</span
                  >
                  <i class="val">88%</i>
                </span>
                <div class="progress-bar-wrap">
                  <div
                    class="progress-bar"
                    role="progressbar"
                    aria-valuenow="88"
                  ></div>
                </div>
              </div>
              <div class="progress">
                <span class="skill">
                  <span
                    data-en="Virtualization (VMware vSphere, KVM)"
                    data-fa="مجازی‌سازی (VMware vSphere، KVM)"
                    >Virtualization (VMware vSphere, KVM)</span
                  >
                  <i class="val">77%</i>
                </span>
                <div class="progress-bar-wrap">
                  <div
                    class="progress-bar"
                    role="progressbar"
                    aria-valuenow="77"
                  ></div>
                </div>
              </div>
              <div class="progress">
                <span class="skill"
                  ><span
                    data-en="SQL Server (Clustering, HA, Monitoring)"
                    data-fa="SQL Server (خوشه‌بندی، HA، نظارت)"
                    >SQL Server (Clustering, HA, Monitoring)</span
                  >
                  <i class="val">66%</i></span
                >
                <div class="progress-bar-wrap">
                  <div
                    class="progress-bar"
                    role="progressbar"
                    aria-valuenow="66"
                  ></div>
                </div>
              </div>
              <div class="progress">
                <span class="skill"
                  ><span
                    data-en="Backup Systems (Veeam Backup & NAS)"
                    data-fa="سیستم‌های پشتیبان‌گیری (Veeam Backup & NAS)"
                    >Backup Systems (Veeam Backup & NAS)</span
                  >
                  <i class="val">98%</i></span
                >
                <div class="progress-bar-wrap">
                  <div
                    class="progress-bar"
                    role="progressbar"
                    aria-valuenow="98"
                  ></div>
                </div>
              </div>
              <div class="progress">
                <span class="skill"
                  ><span
                    data-en="Monitoring Tools (Zabbix, Cacti, Redgate)"
                    data-fa="ابزارهای نظارت (Zabbix، Cacti، Redgate)"
                    >Monitoring Tools (Zabbix, Cacti, Redgate)</span
                  >
                  <i class="val">94%</i></span
                >
                <div class="progress-bar-wrap">
                  <div
                    class="progress-bar"
                    role="progressbar"
                    aria-valuenow="94"
                  ></div>
                </div>
              </div>
              <div class="progress">
                <span class="skill"
                  ><span
                    data-en="VPN & Tunnel Configuration"
                    data-fa="پیکربندی VPN و تانل"
                    >VPN & Tunnel Configuration</span
                  >
                  <i class="val">96%</i></span
                >
                <div class="progress-bar-wrap">
                  <div
                    class="progress-bar"
                    role="progressbar"
                    aria-valuenow="96"
                  ></div>
                </div>
              </div>
              <div class="progress">
                <span class="skill"
                  ><span
                    data-en="Cloud Platforms (AWS, Azure)"
                    data-fa="پلتفرم‌های ابری (AWS، Azure)"
                    >Cloud Platforms (AWS, Azure)</span
                  >
                  <i class="val">66%</i></span
                >
                <div class="progress-bar-wrap">
                  <div
                    class="progress-bar"
                    role="progressbar"
                    aria-valuenow="66"
                  ></div>
                </div>
              </div>
            </div>
            <!-- End Left Column -->

            <!-- ===============================================
            ==================== RIGHT COLUMN ===================
            =============================================== -->
            <div class="col-lg-6">
              <div class="progress">
                <span class="skill"
                  ><span data-en="Technical Documentation" data-fa="مستندات فنی"
                    >Technical Documentation</span
                  >
                  <i class="val">88%</i></span
                >
                <div class="progress-bar-wrap">
                  <div
                    class="progress-bar"
                    role="progressbar"
                    aria-valuenow="88"
                  ></div>
                </div>
              </div>
              <div class="progress">
                <span class="skill"
                  ><span
                    data-en="Diagram & Network Mapping"
                    data-fa="نمودار و نقشه‌برداری شبکه"
                    >Diagram & Network Mapping</span
                  >
                  <i class="val">80%</i></span
                >
                <div class="progress-bar-wrap">
                  <div
                    class="progress-bar"
                    role="progressbar"
                    aria-valuenow="80"
                  ></div>
                </div>
              </div>
              <div class="progress">
                <span class="skill"
                  ><span
                    data-en="System Analysis & Troubleshooting"
                    data-fa="تحلیل سیستم و عیب‌یابی"
                    >System Analysis & Troubleshooting</span
                  >
                  <i class="val">95%</i></span
                >
                <div class="progress-bar-wrap">
                  <div
                    class="progress-bar"
                    role="progressbar"
                    aria-valuenow="95"
                  ></div>
                </div>
              </div>
              <div class="progress">
                <span class="skill"
                  ><span
                    data-en="Project Planning & IT Management"
                    data-fa="برنامه‌ریزی پروژه و مدیریت IT"
                    >Project Planning & IT Management</span
                  >
                  <i class="val">88%</i></span
                >
                <div class="progress-bar-wrap">
                  <div
                    class="progress-bar"
                    role="progressbar"
                    aria-valuenow="88"
                  ></div>
                </div>
              </div>
              <div class="progress">
                <span class="skill"
                  ><span
                    data-en="Team Collaboration & Communication"
                    data-fa="همکاری تیمی و ارتباطات"
                    >Team Collaboration & Communication</span
                  >
                  <i class="val">85%</i></span
                >
                <div class="progress-bar-wrap">
                  <div
                    class="progress-bar"
                    role="progressbar"
                    aria-valuenow="85"
                  ></div>
                </div>
              </div>
              <div class="progress">
                <span class="skill"
                  ><span
                    data-en="Problem Solving Under Pressure"
                    data-fa="حل مسئله تحت فشار"
                    >Problem Solving Under Pressure</span
                  >
                  <i class="val">92%</i></span
                >
                <div class="progress-bar-wrap">
                  <div
                    class="progress-bar"
                    role="progressbar"
                    aria-valuenow="92"
                  ></div>
                </div>
              </div>
              <div class="progress">
                <span class="skill"
                  ><span data-en="Security Awareness" data-fa="آگاهی امنیتی"
                    >Security Awareness</span
                  >
                  <i class="val">90%</i></span
                >
                <div class="progress-bar-wrap">
                  <div
                    class="progress-bar"
                    role="progressbar"
                    aria-valuenow="90"
                  ></div>
                </div>
              </div>
              <div class="progress">
                <span class="skill"
                  ><span
                    data-en="Adaptability & Continuous Learning"
                    data-fa="انطباق‌پذیری و یادگیری مداوم"
                    >Adaptability & Continuous Learning</span
                  >
                  <i class="val">100%</i></span
                >
                <div class="progress-bar-wrap">
                  <div
                    class="progress-bar"
                    role="progressbar"
                    aria-valuenow="100"
                  ></div>
                </div>
              </div>
              <div class="progress">
                <span class="skill"
                  ><span
                    data-en="Leadership & Mentoring"
                    data-fa="رهبری و مربیگری"
                    >Leadership & Mentoring</span
                  >
                  <i class="val">75%</i></span
                >
                <div class="progress-bar-wrap">
                  <div
                    class="progress-bar"
                    role="progressbar"
                    aria-valuenow="75"
                  ></div>
                </div>
              </div>
              <div class="progress">
                <span class="skill"
                  ><span
                    data-en="Client Communication & Reporting"
                    data-fa="ارتباط با مشتری و گزارش‌دهی"
                    >Client Communication & Reporting</span
                  >
                  <i class="val">80%</i></span
                >
                <div class="progress-bar-wrap">
                  <div
                    class="progress-bar"
                    role="progressbar"
                    aria-valuenow="80"
                  ></div>
                </div>
              </div>
            </div>
            <!-- End Right Column -->
          </div>
          <!-- End Skills Row -->
        </div>
        <!-- End Skills Container -->
      </section>
      <!-- /Skills Section -->

      <!-- ===============================================
      ==================== RESUME SECTION =================
      =============================================== -->
      <section id="resume" class="resume section">
        <!-- Section Title -->
        <div class="container section-title" data-aos="fade-up">
          <h2 data-en="Resume" data-fa="رزومه">Resume</h2>
          <p
            data-en="My academic background and professional work experience in computer networks and IT infrastructure."
            data-fa="پیشینه تحصیلی و تجربه کاری حرفه‌ای من در شبکه‌های کامپیوتری و زیرساخت IT."
          >
            My academic background and professional work experience in computer
            networks and IT infrastructure.
          </p>
        </div>
        <!-- End Section Title -->

        <!-- ===============================================
        ==================== RESUME CONTENT ==================
        =============================================== -->
        <div class="container">
          <div class="row">
            <!-- ===============================================
            ==================== EDUCATION COLUMN ================
            =============================================== -->
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
              <h3 class="resume-title" data-en="Education" data-fa="تحصیلات">
                Education
              </h3>
              <div class="resume-item">
                <h4
                  data-en="DevOps Engineering Program"
                  data-fa="برنامه مهندسی DevOps"
                >
                  DevOps Engineering Program
                </h4>
                <h5>2024</h5>
                <p>
                  <em
                    ><a
                      href="https://sananetco.com/en/home/"
                      target="_blank"
                      rel="noopener"
                      data-en="Sanat Training Center"
                      data-fa="مرکز آموزش سانانت"
                      >Sanat Training Center</a
                    ></em
                  >
                </p>
              </div>

              <div class="resume-item">
                <h4 data-en="LPIC-2" data-fa="LPIC-2">LPIC-2</h4>
                <h5>2023</h5>
                <p>
                  <em
                    ><a
                      href="https://arjang.ac.ir/"
                      target="_blank"
                      rel="noopener"
                      data-en="Arzhang Higher Education Institute (Networking, Security, Virtualization, Storage, Programming, Web)"
                      data-fa="مؤسسه آموزش عالی ارژنگ (شبکه، امنیت، مجازی‌سازی، ذخیره‌سازی، برنامه‌نویسی، وب)"
                      >مؤسسه آموزش عالی ارژنگ (شبکه، امنیت، مجازی‌سازی،
                      ذخیره‌سازی، برنامه‌نویسی، وب)</a
                    ></em
                  >
                </p>
              </div>

              <div class="resume-item">
                <h4 data-en="LPIC-1" data-fa="LPIC-1">LPIC-1</h4>
                <h5>2023</h5>
                <p>
                  <em
                    ><a
                      href="https://arjang.ac.ir/"
                      target="_blank"
                      rel="noopener"
                      data-en="Arzhang Higher Education Institute (Networking, Security, Virtualization, Storage, Programming, Web)"
                      data-fa="مؤسسه آموزش عالی ارژنگ (شبکه، امنیت، مجازی‌سازی، ذخیره‌سازی، برنامه‌نویسی، وب)"
                      >مؤسسه آموزش عالی ارژنگ (شبکه، امنیت، مجازی‌سازی،
                      ذخیره‌سازی، برنامه‌نویسی، وب)</a
                    ></em
                  >
                </p>
              </div>

              <div class="resume-item">
                <h4
                  data-en="Comprehensive VMware vSphere: Install, Configure, Manage"
                  data-fa="VMware vSphere جامع: نصب، پیکربندی، مدیریت"
                >
                  Comprehensive VMware vSphere: Install, Configure, Manage
                </h4>
                <h5>2021</h5>
                <p>
                  <em
                    ><a
                      href="https://arjang.ac.ir/"
                      target="_blank"
                      rel="noopener"
                      data-en="Arzhang Higher Education Institute (Networking, Security, Virtualization, Storage, Programming, Web)"
                      data-fa="مؤسسه آموزش عالی ارژنگ (شبکه، امنیت، مجازی‌سازی، ذخیره‌سازی، برنامه‌نویسی، وب)"
                      >مؤسسه آموزش عالی ارژنگ</a
                    ></em
                  >
                </p>
              </div>

              <div class="resume-item">
                <h4
                  data-en="Bachelor's in Computer Networks & Internet"
                  data-fa="کارشناسی شبکه‌های کامپیوتری و اینترنت"
                >
                  Bachelor's in Computer Networks & Internet
                </h4>
                <h5>2020 - 2022</h5>
                <p>
                  <em
                    ><a
                      href="https://iii.ac.ir/"
                      target="_blank"
                      rel="noopener"
                      data-en="University of Applied Science and Technology (Iran Informatics Branch)"
                      data-fa="دانشگاه علمی و کاربردی (انفورماتیک ایران)"
                      >University of Applied Science and Technology (Iran
                      Informatics Branch)</a
                    ></em
                  >
                </p>
              </div>

              <div class="resume-item">
                <h4 data-en="MCSA & Network Plus" data-fa="MCSA و Network Plus">
                  MCSA & Network Plus
                </h4>
                <h5>2018</h5>
                <p>
                  <em
                    ><a
                      href="https://arjang.ac.ir/"
                      target="_blank"
                      rel="noopener"
                      data-en="Arzhang Higher Education Institute (Networking, Security, Virtualization, Storage, Programming, Web)"
                      data-fa="مؤسسه آموزش عالی ارژنگ (شبکه، امنیت، مجازی‌سازی، ذخیره‌سازی، برنامه‌نویسی، وب)"
                      >مؤسسه آموزش عالی ارژنگ</a
                    ></em
                  >
                </p>
              </div>

              <div class="resume-item">
                <h4 data-en="CCNA 200-125" data-fa="CCNA 200-125">
                  CCNA 200-125
                </h4>
                <h5>2018</h5>
                <p>
                  <em
                    ><a
                      href="https://arjang.ac.ir/"
                      target="_blank"
                      rel="noopener"
                      data-en="Arzhang Higher Education Institute (Networking, Security, Virtualization, Storage, Programming, Web)"
                      data-fa="مؤسسه آموزش عالی ارژنگ (شبکه، امنیت، مجازی‌سازی، ذخیره‌سازی، برنامه‌نویسی، وب)"
                      >مؤسسه آموزش عالی ارژنگ</a
                    ></em
                  >
                </p>
              </div>

              <div class="resume-item">
                <h4
                  data-en="Associate Degree in Software Engineering"
                  data-fa="کاردانی مهندسی نرم‌افزار"
                >
                  Associate Degree in Software Engineering
                </h4>
                <h5>2016 - 2018</h5>
                <p>
                  <em
                    ><a
                      href="https://shamsipour.nus.ac.ir/"
                      target="_blank"
                      rel="noopener"
                      data-en="Shahid Shamsipour Technical and Vocational University"
                      data-fa="دانشگاه فنی و حرفه‌ای شهید شمس‌پور"
                      >Shahid Shamsipour Technical and Vocational University</a
                    ></em
                  >
                </p>
              </div>

              <div class="resume-item">
                <h4 data-en="Network Plus" data-fa="Network Plus">
                  Network Plus
                </h4>
                <h5>2013</h5>
                <p>
                  <em
                    ><a
                      href="https://arjang.ac.ir/"
                      target="_blank"
                      rel="noopener"
                      data-en="Arzhang Higher Education Institute (Networking, Security, Virtualization, Storage, Programming, Web)"
                      data-fa="مؤسسه آموزش عالی ارژنگ (شبکه، امنیت، مجازی‌سازی، ذخیره‌سازی، برنامه‌نویسی، وب)"
                      >مؤسسه آموزش عالی ارژنگ</a
                    ></em
                  >
                </p>
              </div>
            </div>
            <!-- End Education Column -->

            <!-- ===============================================
            ==================== EXPERIENCE COLUMN ==============
            =============================================== -->
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
              <h3
                class="resume-title"
                data-en="Professional Experience"
                data-fa="تجربه کاری"
              >
                Professional Experience
              </h3>

              <div class="resume-item">
                <h4
                  data-en="DevOps & Network Infrastructure Specialist"
                  data-fa="متخصص DevOps و زیرساخت شبکه"
                >
                  DevOps & Network Infrastructure Specialist
                </h4>
                <h5 data-en="Sep 2018 – Present" data-fa="شهریور ۱۳۹۷ – تاکنون">
                  Sep 2018 – Present
                </h5>
                <p>
                  <em
                    ><a
                      href="https://newshadrinks.com"
                      target="_blank"
                      rel="noopener"
                      data-en="Newsha Drinks Co."
                      data-fa=" شرکت کشت و صنعت نیوشاداریان "
                      >Newsha Drinks Co.</a
                    ></em
                  >
                </p>
                <ul>
                  <li
                    data-en="Designed and implemented virtualization infrastructure for all enterprise servers"
                    data-fa="طراحی و پیاده‌سازی زیرساخت مجازی‌سازی برای تمام سرورهای سازمانی"
                  >
                    Designed and implemented virtualization infrastructure for
                    all enterprise servers
                  </li>
                  <li
                    data-en="Deployed SQL Server cluster with High Availability and Redgate monitoring"
                    data-fa="استقرار خوشه SQL Server با قابلیت دسترسی بالا و نظارت Redgate"
                  >
                    Deployed SQL Server cluster with High Availability and
                    Redgate monitoring
                  </li>
                  <li
                    data-en="Implemented core Windows infrastructure services: Active Directory, DNS, NTP, DFS, WDS, WSUS"
                    data-fa="پیاده‌سازی سرویس‌های اصلی زیرساخت Windows: Active Directory، DNS، NTP، DFS، WDS، WSUS"
                  >
                    Implemented core Windows infrastructure services: Active
                    Directory, DNS, NTP, DFS, WDS, WSUS
                  </li>
                  <li
                    data-en="Deployed Nginx Proxy Manager for advanced HTTP/HTTPS traffic management and load balancing"
                    data-fa="استقرار Nginx Proxy Manager برای مدیریت پیشرفته ترافیک HTTP/HTTPS و تعادل بار"
                  >
                    Deployed Nginx Proxy Manager for advanced HTTP/HTTPS traffic
                    management and load balancing
                  </li>
                  <li
                    data-en="Redesigned LAN structure with segmented VLANs and removed direct static IPs"
                    data-fa="طراحی مجدد ساختار LAN با VLAN های مجزا و حذف IP های استاتیک مستقیم"
                  >
                    Redesigned LAN structure with segmented VLANs and removed
                    direct static IPs
                  </li>
                  <li
                    data-en="Configured advanced Cisco switching (STP, VTP, VLAN, Port-Channel, Port Security)"
                    data-fa="پیکربندی سوئیچ‌ینگ پیشرفته سیسکو (STP، VTP، VLAN، Port-Channel، Port Security)"
                  >
                    Configured advanced Cisco switching (STP, VTP, VLAN,
                    Port-Channel, Port Security)
                  </li>
                  <li
                    data-en="Implemented secure Site-to-Site VPN tunnels between Tehran offices"
                    data-fa="پیاده‌سازی تونل‌های VPN امن Site-to-Site بین دفاتر تهران"
                  >
                    Implemented secure Site-to-Site VPN tunnels between Tehran
                    offices
                  </li>
                  <li
                    data-en="Optimized Internet performance with MikroTik firewall and security rules"
                    data-fa="بهینه‌سازی عملکرد اینترنت با فایروال MikroTik و قوانین امنیتی"
                  >
                    Optimized Internet performance with MikroTik firewall and
                    security rules
                  </li>
                  <li
                    data-en="Installed Kaspersky Endpoint Security with centralized management console"
                    data-fa="نصب Kaspersky Endpoint Security با کنسول مدیریت متمرکز"
                  >
                    Installed Kaspersky Endpoint Security with centralized
                    management console
                  </li>
                  <li
                    data-en="Upgraded Jira from version 6 to 9 with full documentation"
                    data-fa="ارتقاء Jira از نسخه 6 به 9 با مستندات کامل"
                  >
                    Upgraded Jira from version 6 to 8 with full documentation
                  </li>
                  <li
                    data-en="Upgraded HQ infrastructure with fiber backbone and improved datacenter"
                    data-fa="ارتقاء زیرساخت مرکز اصلی با ستون فقرات فیبرنوری و بهبود مرکز داده"
                  >
                    Upgraded HQ infrastructure with fiber backbone and improved
                    datacenter
                  </li>
                  <li
                    data-en="Integrated CCTV surveillance system with stable infrastructure"
                    data-fa="یکپارچه‌سازی سیستم نظارت CCTV با زیرساخت پایدار"
                  >
                    Integrated CCTV surveillance system with stable
                    infrastructure
                  </li>
                  <li
                    data-en="Enhanced VoIP infrastructure for reliable internal telephony"
                    data-fa="بهبود زیرساخت VoIP برای تلفن داخلی قابل اعتماد"
                  >
                    Enhanced VoIP infrastructure for reliable internal telephony
                  </li>
                  <li
                    data-en="Implemented Zabbix with Telegram API alerts"
                    data-fa="پیاده‌سازی Zabbix با هشدارهای Telegram API"
                  >
                    Implemented Zabbix with Telegram API alerts
                  </li>
                  <li
                    data-en="Integrated Zabbix with Grafana for real-time dashboards"
                    data-fa="یکپارچه‌سازی Zabbix با Grafana برای داشبوردهای زمان واقعی"
                  >
                    Integrated Zabbix with Grafana for real-time dashboards
                  </li>
                  <li
                    data-en="Deployed Cacti for graphical network traffic monitoring"
                    data-fa="استقرار Cacti برای نظارت گرافیکی ترافیک شبکه"
                  >
                    Deployed Cacti for graphical network traffic monitoring
                  </li>
                  <li
                    data-en="Implemented NAS and automated backups with Veeam Backup"
                    data-fa="پیاده‌سازی NAS و پشتیبان‌گیری خودکار با Veeam Backup"
                  >
                    Implemented NAS and automated backups with Veeam Backup
                  </li>
                  <li
                    data-en="Created technical diagrams, documentation, and asset inventory"
                    data-fa="ایجاد نمودارهای فنی، مستندات و فهرست دارایی‌ها"
                  >
                    Created technical diagrams, documentation, and asset
                    inventory
                  </li>
                  <li
                    data-en="Configured CDN for improved website performance and security"
                    data-fa="پیکربندی CDN برای بهبود عملکرد و امنیت وب‌سایت"
                  >
                    Configured CDN for improved website performance and security
                  </li>
                  <li
                    data-en="Configured ArvanCloud with CDN, caching, and access security policies"
                    data-fa="پیکربندی ArvanCloud با CDN، کش و سیاست‌های امنیت دسترسی"
                  >
                    Configured ArvanCloud with CDN, caching, and access security
                    policies
                  </li>
                  <li
                    data-en="Deployed MongoDB for internal NoSQL services"
                    data-fa="استقرار MongoDB برای سرویس‌های داخلی NoSQL"
                  >
                    Deployed MongoDB for internal NoSQL services
                  </li>
                  <li
                    data-en="Implemented ABS NG for user behavior analytics and reporting"
                    data-fa="پیاده‌سازی ABS NG برای تحلیل رفتار کاربر و گزارش‌گیری"
                  >
                    Implemented ABS NG for user behavior analytics and reporting
                  </li>
                  <li
                    data-en="Configured Xray for traffic control, tunneling, and bypassing restrictions"
                    data-fa="پیکربندی Xray برای کنترل ترافیک، تونل‌زنی و دور زدن محدودیت‌ها"
                  >
                    Configured Xray for traffic control, tunneling, and
                    bypassing restrictions
                  </li>
                  <li
                    data-en="Deployed Docker for isolated environments and faster service delivery"
                    data-fa="استقرار Docker برای محیط‌های مجزا و تحویل سریع‌تر سرویس"
                  >
                    Deployed Docker for isolated environments and faster service
                    delivery
                  </li>
                  <li
                    data-en="Configured Apache Maven for Java project build automation"
                    data-fa="پیکربندی Apache Maven برای اتوماسیون بیلد پروژه‌های Java"
                  >
                    Configured Apache Maven for Java project build automation
                  </li>
                  <li
                    data-en="Installed n8n for workflow automation"
                    data-fa="نصب n8n برای اتوماسیون گردش کار"
                  >
                    Installed n8n for workflow automation
                  </li>
                  <li
                    data-en="Developed Bash scripts for automation and server management"
                    data-fa="توسعه اسکریپت‌های Bash برای اتوماسیون و مدیریت سرور"
                  >
                    Developed Bash scripts for automation and server management
                  </li>
                  <li
                    data-en="Migrated Windows web servers to Linux for performance and cost optimization"
                    data-fa="مهاجرت وب‌سرورهای Windows به Linux برای بهینه‌سازی عملکرد و هزینه"
                  >
                    Migrated Windows web servers to Linux for performance and
                    cost optimization
                  </li>
                  <li
                    data-en="Configured load balancing across 5 Internet connections for stability"
                    data-fa="پیکربندی تعادل بار بین 5 اتصال اینترنت برای پایداری"
                  >
                    Configured load balancing across 5 Internet connections for
                    stability
                  </li>
                </ul>
              </div>

              <div class="resume-item">
                <h4 data-en="Network Administrator" data-fa="ادمین شبکه">
                  Network Administrator
                </h4>
                <h5
                  data-en="Jul 2017 – Aug 2018"
                  data-fa="تیر ۱۳۹۶ – مرداد ۱۳۹۷"
                >
                  Jul 2017 – Aug 2018
                </h5>
                <p>
                  <em
                    ><a
                      href="https://uast48ac.ir/fa/"
                      target="_blank"
                      rel="noopener"
                      data-en="University of Applied Science & Technology – Culture & Arts Unit 48"
                      data-fa="دانشگاه علمی و کاربردی – واحد فرهنگ و هنر ۴۸"
                      >University of Applied Science & Technology – Culture &
                      Arts Unit 48</a
                    ></em
                  >
                </p>
                <ul>
                  <li
                    data-en="Installed, configured, and maintained network equipment (switches, routers, servers)"
                    data-fa="نصب، پیکربندی و نگهداری تجهیزات شبکه (سوئیچ، روتر، سرور)"
                  >
                    Installed, configured, and maintained network equipment
                    (switches, routers, servers)
                  </li>
                  <li
                    data-en="Performed passive networking tasks including cabling, termination, and rack installation"
                    data-fa="انجام وظایف شبکه‌سازی غیرفعال شامل کابل‌کشی، ترمینیشن و نصب رک"
                  >
                    Performed passive networking tasks including cabling,
                    termination, and rack installation
                  </li>
                  <li
                    data-en="Troubleshot LAN/WAN/Internet connectivity issues"
                    data-fa="عیب‌یابی مشکلات اتصال LAN/WAN/اینترنت"
                  >
                    Troubleshot LAN/WAN/Internet connectivity issues
                  </li>
                  <li
                    data-en="Installed software and hardware for end-users"
                    data-fa="نصب نرم‌افزار و سخت‌افزار برای کاربران نهایی"
                  >
                    Installed software and hardware for end-users
                  </li>
                  <li
                    data-en="Implemented CCTV surveillance systems"
                    data-fa="پیاده‌سازی سیستم‌های نظارت CCTV"
                  >
                    Implemented CCTV surveillance systems
                  </li>
                  <li
                    data-en="Applied security policies and kept systems updated"
                    data-fa="اعمال سیاست‌های امنیتی و به‌روزرسانی سیستم‌ها"
                  >
                    Applied security policies and kept systems updated
                  </li>
                </ul>
              </div>
            </div>
            <!-- End Experience Column -->
          </div>
          <!-- End Resume Row -->
        </div>
        <!-- End Resume Container -->
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
            data-en="Professional IT services including network design, system administration, infrastructure management, and technical consulting."
            data-fa="خدمات حرفه‌ای IT شامل طراحی شبکه، مدیریت سیستم، مدیریت زیرساخت و مشاوره فنی."
          >
            Professional IT services including network design, system
            administration, infrastructure management, and technical consulting.
          </p>
        </div>
        <!-- End Section Title -->

        <!-- ===============================================
        ==================== SERVICES CONTENT ================
        =============================================== -->
        <div class="container">
          @endverbatim
          <div class="row gy-4" id="service-catalog">
            @forelse ($services as $service)
              @include('components.service-card', ['service' => $service])
            @empty
            @endforelse
          </div>
          <!-- End Service Catalog -->
@verbatim
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
            <!-- ===============================================
            ==================== FILTER BUTTONS =================
            =============================================== -->
            <ul
              class="portfolio-filters isotope-filters"
              role="group"
              aria-label="Filter articles"
              data-aos="fade-up"
              data-aos-delay="100"
            >
              <li>
                <button
                  type="button"
                  data-filter="*"
                  class="filter-active"
                  aria-pressed="true"
                  data-en="All Articles"
                  data-fa="همه مقالات"
                >
                  All Articles
                </button>
              </li>
              <li>
                <button
                  type="button"
                  data-filter=".filter-microsoft"
                  aria-pressed="false"
                  data-en="Microsoft"
                  data-fa="مایکروسافت"
                >
                  Microsoft
                </button>
              </li>
              <li>
                <button
                  type="button"
                  data-filter=".filter-linux"
                  aria-pressed="false"
                  data-en="Linux Articles"
                  data-fa="لینوکس"
                >
                  Linux Articles
                </button>
              </li>
              <li>
                <button
                  type="button"
                  data-filter=".filter-mikrotik"
                  aria-pressed="false"
                  data-en="MikroTik"
                  data-fa="میکروتیک"
                >
                  MikroTik
                </button>
              </li>
              <li>
                <button
                  type="button"
                  data-filter=".filter-vmware"
                  aria-pressed="false"
                  data-en="Vmware"
                  data-fa="مجازی‌سازی"
                >
                  Vmware
                </button>
              </li>
              <li>
                <button
                  type="button"
                  data-filter=".filter-others"
                  aria-pressed="false"
                  data-en="Other Articles"
                  data-fa="سایر مقالات"
                >
                  Other Articles
                </button>
              </li>
            </ul>
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

      <!-- ===============================================
      ==================== TESTIMONIALS SECTION ============
      =============================================== -->
      <section id="testimonials" class="testimonials section light-background">
        <!-- Section Title -->
        <div class="container section-title" data-aos="fade-up">
          <h2 data-en="Testimonials" data-fa="نظرات">Testimonials</h2>
          <p
            data-en="What clients and colleagues say about my work and professional approach."
            data-fa="نظرات مشتریان و همکاران درباره کار و رویکرد حرفه‌ای من."
          >
            What clients and colleagues say about my work and professional
            approach.
          </p>
        </div>
        <!-- End Section Title -->

        <!-- ===============================================
        ==================== TESTIMONIALS CONTENT =============
        =============================================== -->
        <div class="container" data-aos="fade-up" data-aos-delay="100">
          <div class="swiper init-swiper">
            <script type="application/json" class="swiper-config">
              {
                "loop": true,
                "speed": 600,
                "autoplay": {
                  "delay": 4000,
                  "pauseOnMouseEnter": true,
                  "disableOnInteraction": false
                },
                "slidesPerView": 1,
                "spaceBetween": 20,
                "centeredSlides": true,
                "pagination": {
                  "el": ".swiper-pagination",
                  "type": "bullets",
                  "clickable": true,
                  "dynamicBullets": true
                },
                "navigation": {
                  "nextEl": ".swiper-button-next",
                  "prevEl": ".swiper-button-prev"
                },
                "breakpoints": {
                  "320": {
                    "slidesPerView": 1,
                    "spaceBetween": 15,
                    "centeredSlides": true
                  },
                  "480": {
                    "slidesPerView": 1,
                    "spaceBetween": 20,
                    "centeredSlides": true
                  },
                  "768": {
                    "slidesPerView": 2,
                    "spaceBetween": 25,
                    "centeredSlides": false
                  },
                  "992": {
                    "slidesPerView": 3,
                    "spaceBetween": 30,
                    "centeredSlides": false
                  }
                }
              }
            </script>

            <div class="swiper-wrapper">
              <!-- Testimonial 1 -->
              <div class="swiper-slide">
                <div class="testimonial-item">
                  <p>
                    <i class="bi bi-quote quote-icon-left"></i>
                    <span
                      data-en="Amir's expertise in network infrastructure and system administration has been invaluable to our organization. His attention to detail and problem-solving skills are exceptional."
                      data-fa="تخصص امیر در زیرساخت شبکه و مدیریت سیستم برای سازمان ما بسیار ارزشمند بوده است. توجه دقیق او به جزئیات و مهارت‌های حل مسئله فوق‌العاده است."
                      >Amir's expertise in network infrastructure and system
                      administration has been invaluable to our organization.
                      His attention to detail and problem-solving skills are
                      exceptional.</span
                    >
                    <i class="bi bi-quote quote-icon-right"></i>
                  </p>
                  <img
                    src="/assets/img/testimonials/testimonials-1.jpg"
                    loading="lazy"
                    class="testimonial-img"
                    alt="IT Director"
                    width="80"
                    height="80"
                  />
                  <h3 data-en="IT Director" data-fa="مدیر فناوری اطلاعات">
                    IT Director
                  </h3>
                  <h4 data-en="Newsha Drinks Co." data-fa="شرکت نوشیدنی نوشا">
                    Newsha Drinks Co.
                  </h4>
                </div>
              </div>
              <!-- End Testimonial 1 -->

              <!-- Testimonial 2 -->
              <div class="swiper-slide">
                <div class="testimonial-item">
                  <p>
                    <i class="bi bi-quote quote-icon-left"></i>
                    <span
                      data-en="Working with Amir on our virtualization project was a great experience. His technical knowledge and ability to explain complex concepts clearly made the implementation smooth and successful."
                      data-fa="همکاری با امیر در پروژه مجازی‌سازی تجربه فوق‌العاده‌ای بود. دانش فنی عمیق او و توانایی در توضیح مفاهیم پیچیده، پیاده‌سازی را روان و موفق کرد."
                      >Working with Amir on our virtualization project was a
                      great experience. His technical knowledge and ability to
                      explain complex concepts clearly made the implementation
                      smooth and successful.</span
                    >
                    <i class="bi bi-quote quote-icon-right"></i>
                  </p>
                  <img
                    src="/assets/img/testimonials/testimonials-2.jpg"
                    loading="lazy"
                    class="testimonial-img"
                    alt="Project Manager"
                    width="80"
                    height="80"
                  />
                  <h3 data-en="Project Manager" data-fa="مدیر پروژه">
                    Project Manager
                  </h3>
                  <h4 data-en="Technology Solutions" data-fa="راهکارهای فناوری">
                    Technology Solutions
                  </h4>
                </div>
              </div>
              <!-- End Testimonial 2 -->

              <!-- Testimonial 3 -->
              <div class="swiper-slide">
                <div class="testimonial-item">
                  <p>
                    <i class="bi bi-quote quote-icon-left"></i>
                    <span
                      data-en="Amir's monitoring and security implementations have significantly improved our system reliability. His proactive approach to IT infrastructure management is commendable."
                      data-fa="پیاده‌سازی سیستم‌های نظارت و امنیت امیر، قابلیت اطمینان سیستم ما را به طور چشمگیری بهبود داده است. رویکرد پیشگیرانه او در مدیریت زیرساخت فناوری اطلاعات بسیار قابل تحسین است."
                      >Amir's monitoring and security implementations have
                      significantly improved our system reliability. His
                      proactive approach to IT infrastructure management is
                      commendable.</span
                    >
                    <i class="bi bi-quote quote-icon-right"></i>
                  </p>
                  <img
                    src="/assets/img/testimonials/testimonials-3.jpg"
                    loading="lazy"
                    class="testimonial-img"
                    alt="System Administrator"
                    width="80"
                    height="80"
                  />
                  <h3 data-en="System Administrator" data-fa="مدیر سیستم">
                    System Administrator
                  </h3>
                  <h4 data-en="Enterprise Client" data-fa="مشتری سازمانی">
                    Enterprise Client
                  </h4>
                </div>
              </div>
              <!-- End Testimonial 3 -->

              <!-- Testimonial 4 -->
              <div class="swiper-slide">
                <div class="testimonial-item">
                  <p>
                    <i class="bi bi-quote quote-icon-left"></i>
                    <span
                      data-en="The network optimization and load balancing solutions implemented by Amir have greatly enhanced our internet connectivity and overall system performance."
                      data-fa="راهکارهای بهینه‌سازی شبکه و تعادل بار پیاده‌سازی شده توسط امیر، اتصال اینترنت و عملکرد کلی سیستم ما را به طور چشمگیری بهبود داده است."
                      >The network optimization and load balancing solutions
                      implemented by Amir have greatly enhanced our internet
                      connectivity and overall system performance.</span
                    >
                    <i class="bi bi-quote quote-icon-right"></i>
                  </p>
                  <img
                    src="/assets/img/testimonials/testimonials-4.jpg"
                    loading="lazy"
                    class="testimonial-img"
                    alt="Network Engineer"
                    width="80"
                    height="80"
                  />
                  <h3 data-en="Network Engineer" data-fa="مهندس شبکه">
                    Network Engineer
                  </h3>
                  <h4 data-en="Infrastructure Team" data-fa="تیم زیرساخت">
                    Infrastructure Team
                  </h4>
                </div>
              </div>
              <!-- End Testimonial 4 -->

              <!-- Testimonial 5 -->
              <div class="swiper-slide">
                <div class="testimonial-item">
                  <p>
                    <i class="bi bi-quote quote-icon-left"></i>
                    <span
                      data-en="Amir's expertise in DevOps and automation has streamlined our deployment processes. His technical documentation and knowledge transfer are excellent."
                      data-fa="تخصص امیر در DevOps و اتوماسیون، فرآیندهای استقرار ما را به طور قابل توجهی روان کرده است. مستندات فنی و انتقال دانش او بسیار عالی است."
                      >Amir's expertise in DevOps and automation has streamlined
                      our deployment processes. His technical documentation and
                      knowledge transfer are excellent.</span
                    >
                    <i class="bi bi-quote quote-icon-right"></i>
                  </p>
                  <img
                    src="/assets/img/testimonials/testimonials-5.jpg"
                    loading="lazy"
                    class="testimonial-img"
                    alt="DevOps Lead"
                    width="80"
                    height="80"
                  />
                  <h3 data-en="DevOps Lead" data-fa="سرپرست DevOps">
                    DevOps Lead
                  </h3>
                  <h4 data-en="Development Team" data-fa="تیم توسعه">
                    Development Team
                  </h4>
                </div>
              </div>
              <!-- End Testimonial 5 -->
            </div>

            <button
              type="button"
              class="swiper-button-next"
              aria-label="Next testimonial"
            ></button>
            <button
              type="button"
              class="swiper-button-prev"
              aria-label="Previous testimonial"
            ></button>
            <div class="swiper-pagination"></div>
            <button
              type="button"
              class="swiper-autoplay-toggle"
              data-swiper-autoplay-toggle
              aria-pressed="false"
              aria-label="Pause testimonial motion"
            >
              <i class="bi bi-pause-fill" aria-hidden="true"></i>
              <span data-en="Pause motion" data-fa="توقف حرکت">Pause motion</span>
            </button>
          </div>
        </div>

        <!-- End Testimonials Container -->
      </section>
      <!-- /Testimonials Section -->

      <!-- ===============================================
      ==================== CONTACT SECTION ================
      =============================================== -->
      <section id="contact" class="contact section">
        <!-- Section Title -->
        <div class="container section-title" data-aos="fade-up">
          <h2 data-en="Contact" data-fa="تماس">Contact</h2>
          <p
            data-en="Get in touch for professional IT services, network solutions, or technical consulting."
            data-fa="برای خدمات حرفه‌ای IT، راهکارهای شبکه یا مشاوره فنی با من تماس بگیرید."
          >
            Get in touch for professional IT services, network solutions, or
            technical consulting.
          </p>
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
                    data-en="Let's Work Together"
                    data-fa="بیایید با هم کار کنیم"
                    >Let's Work Together</span
                  >
                      <i class="bi bi-people-fill title-accent" aria-hidden="true"></i>
                </h3>
                <p
                  class="contact-intro-text"
                  data-en="Ready to discuss your IT infrastructure needs? I'm here to help you achieve your goals with professional solutions."
                  data-fa="آماده بحث در مورد نیازهای زیرساخت IT شما هستم؟ من اینجا هستم تا با راهکارهای حرفه‌ای به شما کمک کنم."
                >
                  Ready to discuss your IT infrastructure needs? I'm here to
                  help you achieve your goals with professional solutions.
                </p>
              </div>
            </div>

            <!-- Contact Main Grid -->
            <div class="contact-main-grid">
              <!-- Left Column - Contact Methods -->
              <div class="contact-methods">
                <!-- Quick Contact Cards -->
                <div class="contact-cards-grid">
                  <!-- Phone Card -->
                  <div
                    class="contact-card phone-card"
                    data-aos="fade-right"
                    data-aos-delay="200"
                  >
                    <div class="card-icon">
                      <i class="bi bi-telephone"></i>
                    </div>
                    <div class="card-content">
                      <h4 data-en="Call Me" data-fa="تماس تلفنی">Call Me</h4>
                      <p
                        data-en="Direct phone consultation"
                        data-fa="مشاوره تلفنی مستقیم"
                      >
                        Direct phone consultation
                      </p>
                      <a href="tel:+989197276219" class="contact-link">
                        <span data-en="+98 9197276219" data-fa="09197276219"
                          >+98 9197276219</span
                        >
                        <i class="bi bi-arrow-right"></i>
                      </a>
                    </div>
                  </div>

                  <!-- WhatsApp Card -->
                  <div
                    class="contact-card whatsapp-card"
                    data-aos="fade-right"
                    data-aos-delay="250"
                  >
                    <div class="card-icon">
                      <i class="bi bi-whatsapp"></i>
                    </div>
                    <div class="card-content">
                      <h4 data-en="WhatsApp" data-fa="واتساپ">WhatsApp</h4>
                      <p
                        data-en="Quick messaging & support"
                        data-fa="پیام‌رسانی سریع و پشتیبانی"
                      >
                        Quick messaging & support
                      </p>
                      <a
                        href="https://wa.me/989197276219"
                        target="_blank"
                        rel="noopener"
                        class="contact-link"
                      >
                        <span data-en="Start Chat" data-fa="شروع گفتگو"
                          >Start Chat</span
                        >
                        <i class="bi bi-arrow-right"></i>
                      </a>
                    </div>
                  </div>

                  <!-- Email Card -->
                  <div
                    class="contact-card email-card"
                    data-aos="fade-right"
                    data-aos-delay="300"
                  >
                    <div class="card-icon">
                      <i class="bi bi-envelope"></i>
                    </div>
                    <div class="card-content">
                      <h4 data-en="Email Me" data-fa="ایمیل">Email Me</h4>
                      <p
                        data-en="Detailed project discussions"
                        data-fa="بحث‌های تفصیلی پروژه"
                      >
                        Detailed project discussions
                      </p>
                      <a
                        href="mailto:jalalian.amirhossein@gmail.com"
                        class="contact-link"
                      >
                        <span>jalalian.amirhossein@gmail.com</span>
                        <i class="bi bi-arrow-right"></i>
                      </a>
                    </div>
                  </div>

                  <!-- Telegram Card -->
                  <div
                    class="contact-card telegram-card"
                    data-aos="fade-right"
                    data-aos-delay="350"
                  >
                    <div class="card-icon">
                      <i class="bi bi-telegram"></i>
                    </div>
                    <div class="card-content">
                      <h4 data-en="Telegram" data-fa="تلگرام">Telegram</h4>
                      <p data-en="Instant communication" data-fa="ارتباط فوری">
                        Instant communication
                      </p>
                      <a
                        href="https://t.me/Aj_mercury"
                        target="_blank"
                        rel="noopener"
                        class="contact-link"
                      >
                        <span>@Aj_mercury</span>
                        <i class="bi bi-arrow-right"></i>
                      </a>
                    </div>
                  </div>
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
                      <p data-en="Tehran, Iran" data-fa="تهران، ایران">
                        Tehran, Iran
                      </p>
                      <div class="map-container">
                        <iframe
                          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1145.5099796149461!2d51.40510627035861!3d35.701826831163324!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3f8e010c2bbc0853%3A0xd7ec3a4d591a3242!2sReza%20Computer%20Center!5e0!3m2!1sen!2sde!4v1756209223815!5m2!1sen!2sde"
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
                          title="موقعیت مکانی - تهران، ایران"
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
                    <input
                      type="hidden"
                      name="csrf_token"
                      id="csrf_token"
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
                data-en="IT Infrastructure Specialist"
                data-fa="متخصص زیرساخت فناوری اطلاعات"
              >
                IT Infrastructure Specialist
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
                  © <span>2025</span>
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
                <i class="bi bi-heart-fill"></i>
                <span data-en="in Iran" data-fa="در ایران">in Iran</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </footer>
    <!-- End Footer Section -->

    <!-- ===============================================
    PAGE PRELOADER - Modern Bilingual Design
    =============================================== -->
    <div id="preloader" class="preloader-overlay visible">
      <div id="preloader-container" class="preloader-container ltr">
        <!-- Spinning Circle Animation -->
        <div class="preloader-spinner">
          <div class="spinner-circle">
            <div class="spinner-inner"></div>
          </div>
        </div>

        <!-- Loading Text -->
        <div class="preloader-text">
          <span id="loading-text" class="loading-text">Loading...</span>
        </div>

        <!-- Progress Line Animation -->
        <div class="preloader-progress">
          <div class="progress-line"></div>
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
    <script src="/assets/js/main.js?v=1117" defer></script>

    <!-- Internationalization (i18n) Support -->
    <!-- Language Toggle JavaScript -->
    <script src="/assets/js/i18n.js?v=1115" defer></script>

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

@endverbatim