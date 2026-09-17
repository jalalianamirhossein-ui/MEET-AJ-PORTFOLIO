@verbatim
<!doctype html>
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
    <meta charset="utf-8" />@endverbatim
    <meta name="csrf-token" content="{{ csrf_token() }}">
@verbatim
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
      content="Professional portfolio of AmirHossein Jalalian - Network Expert, DevOps Engineer, and IT Infrastructure Specialist."
    />
    <meta
      name="twitter:image"
      content="https://meetaj.irassets/img/hero-bg.jpg"
    />
    <script type="application/ld+json">
      {
        "@context": "https://schema.org",
        "@type": "Person",
        "name": "AmirHossein Jalalian",
        "url": "https://meetaj.ir/",
        "jobTitle": "Network Expert & DevOps Engineer",
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
    <link href="/assets/css/lang-toggle.css?v=1301" rel="stylesheet" />

    <!-- RTL Support Stylesheet -->
    <link
      id="rtl-style"
      href="/assets/css/rtl.css?v=1000"
      rel="stylesheet"
      disabled
    />
    <link href="/assets/css/visual-upgrade.css?v=1703" rel="stylesheet" />
    <link href="/assets/css/site-modules.css?v=1811" rel="stylesheet" />

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
    <a class="skip-link" href="#main-content" data-en="Skip to main content" data-fa="رفتن به محتوای اصلی">Skip to main content</a>
    <!-- ===============================================
    ==================== HEADER SECTION ================
    =============================================== -->
    <header id="header" class="header dark-background d-flex flex-column">
      <div class="brand-lang" id="lang-mount"></div>
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
        class="logo-section d-flex align-items-center justify-content-center"
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

        <!-- ???? ???: ???? -->
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
      <nav id="navmenu" class="navmenu" role="navigation" aria-label="Primary" data-en-aria-label="Primary" data-fa-aria-label="???? ????">
        <ul>
          <!-- Home Section -->
          <li>
            <a href="#hero" class="active" aria-current="page"
              ><i class="bi bi-house navicon"></i
              ><span data-en="Home" data-fa="???? ????">Home</span></a
            >
          </li>

          <!-- About Section -->
          <li>
            <a href="#about"
              ><i class="bi bi-person navicon"></i
              ><span data-en="About" data-fa="?????? ??">About</span></a
            >
          </li>

          <!-- Resume Section -->
          <li>
            <a href="#resume"
              ><i class="bi bi-file-earmark-text navicon"></i
              ><span data-en="Resume" data-fa="????? ????">Resume</span></a
            >
          </li>

          <!-- Services Section -->
          <li>
            <a href="#services"
              ><i class="bi bi-hdd-stack navicon"></i
              ><span data-en="Services" data-fa="?????">Services</span></a
            >
          </li>

          <!-- Articles Section -->
          <li>
            <a href="#portfolio"
              ><i class="bi bi-images navicon"></i
              ><span data-en="Articles" data-fa="??????">Articles</span></a
            >
          </li>

          <!-- Testimonials Section -->
          <li>
            <a href="#testimonials"
              ><i class="bi bi-menu-button navicon"></i
              ><span data-en="Testimonials" data-fa="?????"
                >Testimonials</span
              ></a
            >
          </li>

          <!-- Contact Section -->
          <li>
            <a href="#contact"
              ><i class="bi bi-envelope navicon"></i
              ><span data-en="Contact" data-fa="???? ?? ??">Contact</span></a
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
      data-fa-aria-label="??? ???? ???"
      aria-expanded="false"
      aria-controls="header"
    >
      <span class="menu-toggle-bars" aria-hidden="true"><span></span><span></span><span></span></span>
      <span class="sr-only" data-en="Open menu" data-fa="??? ???? ???">Open menu</span>
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
                    data-fa="????? ???? ??????"
                    >Available for Work</span
                  >
                  <div class="badge-dot"></div>
                </div>

                <!-- Main Title with Modern Typography -->
                <h1
                  class="hero-title"
                  data-en="AmirHossein Jalalian"
                  data-fa="???????? ???????"
                  data-aos="fade-up"
                  data-aos-delay="400"
                >
                  <span
                    class="title-line-1"
                    data-en="AmirHossein"
                    data-fa="????????"
                    >AmirHossein</span
                  >
                  <span
                    class="title-line-2"
                    data-en="Jalalian"
                    data-fa="???????"
                    >Jalalian</span
                  >
                </h1>
                <p
                  class="hero-role"
                  data-en="Network and IT Infrastructure Specialist"
                  data-fa="????? ???? ? ??????? ?????? ???????"
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
                    <span class="subtitle-prefix" data-en="I'm a" data-fa="??"
                      >I'm a</span
                    >
                    <span
                      class="typed"
                      data-typed-items="Network Specialist, DevOps Engineer, IT Consultant, Systems Expert, VMware Administrator"
                      data-typed-items-fa="????? ???? ????, ????? DevOps ????, ????? ?????? ??????? ????, ??????? ???????? ????, ???? ?????????? VMware ????"
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
                  data-aos-delay="80"
                >
                  <a
                    href="#contact"
                    class="btn btn-primary btn-modern"
                    data-en="Get In Touch"
                    data-fa="???? ?? ??"
                  >
                    <span
                      class="btn-text"
                      data-en="Get In Touch"
                      data-fa="???? ?? ??"
                      >Get In Touch</span
                    >
                    <span class="btn-icon" aria-hidden="true"><i class="bi bi-envelope"></i></span>
                  </a>
                  <a
                    href="#portfolio"
                    class="btn btn-outline-light btn-modern"
                    data-en="Articles"
                    data-fa="??????"
                  >
                    <span class="btn-text" data-en="Articles" data-fa="??????"
                      >Articles</span
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
            <p class="about-kicker" data-en="IT Infrastructure Specialist" data-fa="????? ??????? ?????? ???????">IT Infrastructure Specialist</p>
            <h2 data-en="Get to Know Me" data-fa="???? ??? ?????">Get to Know Me</h2>
            <p class="about-headline" data-en="Designing, managing, and optimizing enterprise systems." data-fa="?????? ?????? ? ?????????? ????????? ???????.">Designing, managing, and optimizing enterprise systems.</p>
            <div class="about-profile">
              <div class="about-photo">
                <img src="/assets/img/my-profile-img-2.jpg" alt="Amirhossein Jalalian Profile" width="200" height="200" loading="lazy" sizes="120px" />
              </div>
              <div class="about-profile-meta">
                <p class="about-name" data-en="Amirhossein Jalalian" data-fa="???????? ???????">Amirhossein Jalalian</p>
                <p class="about-role" data-en="Network and IT Infrastructure Specialist" data-fa="????? ???? ? ??????? ?????? ???????">Network and IT Infrastructure Specialist</p>
                <p class="about-place"><i class="bi bi-geo-alt" aria-hidden="true"></i> <span data-en="Tehran, Iran" data-fa="?????? ?????">Tehran, Iran</span></p>
              </div>
            </div>
            <p class="about-lead" data-en="I am Amirhossein Jalalian, a Network and IT Infrastructure Specialist with extensive experience in designing, managing, and optimizing enterprise systems. My goal is to provide reliable, secure, and scalable solutions that help organizations operate more efficiently while reducing risks." data-fa="?? ?? ????? ???? ? ??????? ?????? ??????? ???? ?? ?? ?????????? ?? ??? ???? ?????? ??????. ?? ??????? ??????? ????? ????????? ?? ?????? ?????????? ? ?????? ????????? ??????? ?? ??? ???????? ? ???? ?????? ????? ?????????? ????? ??? ?? ??????? ??? ? ?????????? ?????. ?? ???? ?? Windows Server? ??????? Cisco ? MikroTik? ?????????? ? ????????? ?? ?????????? ????????? ??????? ????????? ??????? ?? ????? ??? ? ???????? ??????? ?? ???? ???. ?? ???? ???????????? ??? ??? ? ????????????? ???? ?? ??? ??? ? ???? ???? ?????? ?? ????? ???? ?????? ? ??????? ???? ???? ???????.">
              I am Amirhossein Jalalian, a Network and IT Infrastructure Specialist with extensive experience in designing, managing, and optimizing enterprise systems. My goal is to provide reliable, secure, and scalable solutions that help organizations operate more efficiently while reducing risks.
            </p>
            <p class="about-lead" data-en="I have extensive experience in Windows Server, Cisco and MikroTik technologies, virtualization, and other IT infrastructure solutions. My approach is always results-driven, carrying out each project with accountability, precision, and strong teamwork." data-fa="?? ???????? ?????? ?? Windows Server? ??????? ????? ? ????????? ?????????? ? ???? ?????????? ???????? ????. ?????? ?? ?? ???? ?????? ?????????? ??? ? ?? ????? ?? ?? ????????????? ????? ??? ??? ? ????? ??? ???? ?? ??????? ????????.">
              I have extensive experience in Windows Server, Cisco and MikroTik technologies, virtualization, and other IT infrastructure solutions. My approach is always results-driven, carrying out each project with accountability, precision, and strong teamwork.
            </p>
            <dl class="about-facts">
              <div>
                <dt data-en="Birthday" data-fa="????? ????">Birthday</dt>
                <dd data-en="19 July 1999" data-fa="?? ??? ????">19 July 1999</dd>
              </div>
              <div>
                <dt data-en="Degree" data-fa="???? ??????">Degree</dt>
                <dd data-en="Bachelor of IT Engineering" data-fa="???????? ?????? ?????? ???????">Bachelor of IT Engineering</dd>
              </div>
              <div>
                <dt data-en="Experience" data-fa="?????">Experience</dt>
                <dd data-en="5+ Years" data-fa="??? ?? ? ???">5+ Years</dd>
              </div>
            </dl>
            <div class="about-actions">
              <a class="btn btn-primary" href="#contact" data-en="Get In Touch" data-fa="???? ?? ??">Get In Touch</a>
              <a class="btn btn-outline-primary" href="#services" data-en="Services" data-fa="?????">Services</a>
            </div>
          </div>

          <div class="about-visual" data-aos="fade-up" data-aos-delay="120">
            <div class="about-core is-core" data-about-core>
              <p class="about-core-label" data-en="Infrastructure Core" data-fa="???? ???????">Infrastructure Core</p>
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
                  <span data-en="Infrastructure" data-fa="???????">Infrastructure</span>
                </button>
                <button type="button" class="about-node about-node--devops" data-panel="devops" aria-pressed="false" aria-controls="about-core-detail">
                  <span class="about-node-status" aria-hidden="true"></span>
                  <span data-en="DevOps" data-fa="?????">DevOps</span>
                </button>
                <button type="button" class="about-node about-node--net" data-panel="net" aria-pressed="false" aria-controls="about-core-detail">
                  <span class="about-node-status" aria-hidden="true"></span>
                  <span data-en="Networking" data-fa="????">Networking</span>
                </button>
                <button type="button" class="about-node about-node--virt" data-panel="virt" aria-pressed="false" aria-controls="about-core-detail">
                  <span class="about-node-status" aria-hidden="true"></span>
                  <span data-en="Virtualization" data-fa="??????????">Virtualization</span>
                </button>
                <button type="button" class="about-node about-node--mon" data-panel="mon" aria-pressed="false" aria-controls="about-core-detail">
                  <span class="about-node-status" aria-hidden="true"></span>
                  <span data-en="Monitoring" data-fa="??????????">Monitoring</span>
                </button>
                <button type="button" class="about-node about-node--auto" data-panel="auto" aria-pressed="false" aria-controls="about-core-detail">
                  <span class="about-node-status" aria-hidden="true"></span>
                  <span data-en="Automation" data-fa="?????????">Automation</span>
                </button>
              </div>
              <div class="about-core-detail" id="about-core-detail" aria-live="polite">
                <p data-panel="core" class="is-active" data-en="Reliable, secure, and scalable solutions for enterprise systems ? Windows Server, Cisco, MikroTik, and virtualization." data-fa="????????? ??????? ??? ? ?????????? ???? ????????? ??????? ? Windows Server? ?????? ???????? ? ??????????.">Reliable, secure, and scalable solutions for enterprise systems ? Windows Server, Cisco, MikroTik, and virtualization.</p>
                <p data-panel="infra" hidden data-en="Linux administration (Ubuntu, CentOS) and Microsoft services (Active Directory, DNS, DFS, WDS, WSUS, NTP)." data-fa="?????? ?????? (???????? ????????) ? ????? ?????????? (Active Directory? DNS? DFS? WDS? WSUS? NTP).">Linux administration (Ubuntu, CentOS) and Microsoft services (Active Directory, DNS, DFS, WDS, WSUS, NTP).</p>
                <p data-panel="devops" hidden data-en="Docker containerization, CI/CD pipelines, and GitLab / Jenkins workflows." data-fa="?????????????? Docker? ?? ???? CI/CD ? ???????? GitLab / Jenkins.">Docker containerization, CI/CD pipelines, and GitLab / Jenkins workflows.</p>
                <p data-panel="net" hidden data-en="Network design with Cisco and MikroTik, firewall and security rules, VPN, and VoIP infrastructure." data-fa="????? ???? ?? ????? ? ????????? ??????? ? ?????? ??????? VPN ? ??????? VoIP.">Network design with Cisco and MikroTik, firewall and security rules, VPN, and VoIP infrastructure.</p>
                <p data-panel="virt" hidden data-en="VMware vSphere and KVM virtualization, plus cloud platforms (AWS, Azure)." data-fa="?????????? VMware vSphere ? KVM? ? ?????????? ???? (AWS? Azure).">VMware vSphere and KVM virtualization, plus cloud platforms (AWS, Azure).</p>
                <p data-panel="mon" hidden data-en="Zabbix, Grafana, Cacti, and Redgate monitoring ? dashboards and alerting." data-fa="?????????? Zabbix? Grafana? Cacti ? Redgate ? ??????? ? ?????.">Zabbix, Grafana, Cacti, and Redgate monitoring ? dashboards and alerting.</p>
                <p data-panel="auto" hidden data-en="CI/CD pipeline setup and infrastructure automation, including Ansible and Terraform where the project requires it." data-fa="?????????? CI/CD ? ?????????? ???????? ???? Ansible ? Terraform ?? ???? ???? ?????.">CI/CD pipeline setup and infrastructure automation, including Ansible and Terraform where the project requires it.</p>
              </div>
            </div>
          </div>
        </div>

        <div class="container about-domains" data-aos="fade-up">
          <h3 class="about-domains-title" data-en="Expertise" data-fa="???????">Expertise</h3>
          <div class="about-domain-grid">
            <section class="about-domain">
              <h4 data-en="Infrastructure" data-fa="???????">Infrastructure</h4>
              <ul>
                <li>Linux</li>
                <li>Windows Server</li>
                <li>VMware</li>
                <li>KVM</li>
              </ul>
            </section>
            <section class="about-domain">
              <h4 data-en="Networking" data-fa="????">Networking</h4>
              <ul>
                <li>Cisco</li>
                <li>MikroTik</li>
                <li>VPN</li>
                <li>VoIP</li>
              </ul>
            </section>
            <section class="about-domain">
              <h4 data-en="DevOps" data-fa="?????">DevOps</h4>
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
              <h4 data-en="Monitoring" data-fa="??????????">Monitoring</h4>
              <ul>
                <li>Zabbix</li>
                <li>Grafana</li>
                <li>Cacti</li>
                <li>Redgate</li>
              </ul>
            </section>
            <section class="about-domain">
              <h4 data-en="Security" data-fa="?????">Security</h4>
              <ul>
                <li data-en="Firewall &amp; security rules" data-fa="??????? ? ?????? ??????">Firewall &amp; security rules</li>
                <li>Active Directory</li>
                <li>VPN</li>
                <li data-en="Backup (Veeam)" data-fa="پشتیبان‌گیری (Veeam)">Backup (Veeam)</li>
              </ul>
            </section>
          </div>
        </div>

        <div class="container about-secondary">
          <div class="about-values">
            <h3 data-en="Core Values" data-fa="???????? ????">Core Values</h3>
            <ul class="about-value-list">
              <li>
                <h4 data-en="Integrity" data-fa="?????">Integrity</h4>
                <p data-en="Honest and transparent in all professional interactions" data-fa="???? ? ???? ?? ???? ??????? ???????">Honest and transparent in all professional interactions</p>
              </li>
              <li>
                <h4 data-en="Excellence" data-fa="?????">Excellence</h4>
                <p data-en="Committed to delivering the highest quality solutions" data-fa="????? ?? ????? ????????? ?? ???????? ?????">Committed to delivering the highest quality solutions</p>
              </li>
              <li>
                <h4 data-en="Collaboration" data-fa="??????">Collaboration</h4>
                <p data-en="Strong believer in teamwork and collective success" data-fa="???? ??? ?? ??? ???? ? ?????? ????">Strong believer in teamwork and collective success</p>
              </li>
              <li>
                <h4 data-en="Innovation" data-fa="??????">Innovation</h4>
                <p data-en="Continuously learning and adapting to new technologies" data-fa="??????? ????? ? ??????? ?? ?????????? ????">Continuously learning and adapting to new technologies</p>
              </li>
            </ul>
          </div>
          <blockquote class="about-quote">
            <p data-en="Success is born of sustained effort, continuous learning, and faith in the journey." data-fa="?????? ???? ???? ?????? ??????? ????? ? ????? ?? ???? ???.">Success is born of sustained effort, continuous learning, and faith in the journey.</p>
            <footer data-en="? My Personal Motto" data-fa="? ???? ???? ??">? My Personal Motto</footer>
          </blockquote>
          <div class="about-philosophy">
            <h3 data-en="My Philosophy" data-fa="????? ??">My Philosophy</h3>
            <p data-en="I value honesty, patience, and accountability as core principles. I believe that respect for the profession and adherence to fundamental values form the foundation for achieving long-term and sustainable success in the field of Information Technology." data-fa="???? ??? ??????  ??? ? ????????????? ???????? ????? ?????. ???? ???? ?? ?????? ?? ???? ? ??????? ?? ???? ???????? ??????? ??????? ?? ?????? ?????? ? ??????? ?? ???? ?????? ??????? ???.">
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
                <i class="bi bi-people" aria-hidden="true"></i>
                <span
                  data-purecounter-start="0"
                  data-purecounter-end="49"
                  data-purecounter-duration="1"
                  class="purecounter"
                ></span>
                <p>
                  <span data-en="Satisfied customers" data-fa="??????? ????"
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
                  <span data-en="Successful projects" data-fa="????????? ????"
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
                    data-fa="???? ???????? ????? ???"
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
                  <span data-en="Expert team" data-fa="??? ?????"
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
            data-fa="????????? ???? ??? ? ??????? ?? ?? ???????? ????????? ? ??????? ?????? ???????."
          >
            My core technical and professional skills in computer networks and
            IT infrastructure.
          </p>
        </div>
        <!-- End Section Title -->

                                <div class="container" data-aos="fade-up" data-aos-delay="100">
          <div class="skills-board skills-content skills-animation">
            <article class="skill-group">
              <h3 data-en="Infrastructure" data-fa="زیرساخت">Infrastructure</h3>
              <div class="progress">
                <span class="skill"><span>Linux</span> <i class="val">100%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span>Windows Server</span> <i class="val">92%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="92" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span>Active Directory</span> <i class="val">90%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span>DNS / DFS / WSUS</span> <i class="val">88%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="88" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span data-en="Backup (Veeam)" data-fa="پشتیبان‌گیری (Veeam)">Backup (Veeam)</span> <i class="val">77%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="77" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
            </article>
            <article class="skill-group">
              <h3 data-en="Networking" data-fa="شبکه">Networking</h3>
              <div class="progress">
                <span class="skill"><span>Cisco</span> <i class="val">92%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="92" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span>MikroTik</span> <i class="val">90%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span data-en="Firewall" data-fa="فایروال">Firewall</span> <i class="val">88%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="88" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span>VPN</span> <i class="val">85%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span>VoIP</span> <i class="val">80%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
            </article>
            <article class="skill-group">
              <h3 data-en="Virtualization & Cloud" data-fa="مجازی‌سازی و ابر">Virtualization & Cloud</h3>
              <div class="progress">
                <span class="skill"><span>VMware vSphere</span> <i class="val">90%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span>KVM</span> <i class="val">82%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="82" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span>AWS</span> <i class="val">78%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="78" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span>Azure</span> <i class="val">76%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="76" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span>SQL Server HA</span> <i class="val">80%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
            </article>
            <article class="skill-group">
              <h3 data-en="DevOps & Operations" data-fa="DevOps و عملیات">DevOps & Operations</h3>
              <div class="progress">
                <span class="skill"><span>Docker</span> <i class="val">88%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="88" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span>CI/CD</span> <i class="val">85%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span>GitLab / Jenkins</span> <i class="val">82%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="82" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span>Ansible</span> <i class="val">80%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span>Terraform</span> <i class="val">75%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span>Zabbix / Grafana</span> <i class="val">86%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="86" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
            </article>
            <article class="skill-group">
              <h3 data-en="Professional" data-fa="مهارت‌های حرفه‌ای">Professional</h3>
              <div class="progress">
                <span class="skill"><span data-en="Documentation" data-fa="مستندسازی">Documentation</span> <i class="val">90%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span data-en="Troubleshooting" data-fa="عیب‌یابی">Troubleshooting</span> <i class="val">95%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span data-en="Planning" data-fa="برنامه‌ریزی">Planning</span> <i class="val">85%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span data-en="Communication" data-fa="ارتباط با مشتری">Communication</span> <i class="val">88%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="88" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
            </article>
          </div>
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
          <h2 data-en="Resume" data-fa="رزومه کاری">Resume</h2>
          <p
            data-en="My academic background and professional work experience in computer networks and IT infrastructure."
            data-fa="?????? ?????? ? ????? ???? ??????? ?? ?? ???????? ????????? ? ??????? IT."
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
              <h3 class="resume-title" data-en="Education" data-fa="???????">
                Education
              </h3>
              <div class="resume-item">
                <h4
                  data-en="DevOps Engineering Program"
                  data-fa="?????? ?????? DevOps"
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
                      data-fa="???? ????? ??????"
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
                      data-fa="????? ????? ???? ????? (????? ?????? ??????????? ??????????? ????????????? ??)"
                      >????? ????? ???? ????? (????? ?????? ???????????
                      ??????????? ????????????? ??)</a
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
                      data-fa="????? ????? ???? ????? (????? ?????? ??????????? ??????????? ????????????? ??)"
                      >????? ????? ???? ????? (????? ?????? ???????????
                      ??????????? ????????????? ??)</a
                    ></em
                  >
                </p>
              </div>

              <div class="resume-item">
                <h4
                  data-en="Comprehensive VMware vSphere: Install, Configure, Manage"
                  data-fa="VMware vSphere ????: ???? ????????? ??????"
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
                      data-fa="????? ????? ???? ????? (????? ?????? ??????????? ??????????? ????????????? ??)"
                      >????? ????? ???? ?????</a
                    ></em
                  >
                </p>
              </div>

              <div class="resume-item">
                <h4
                  data-en="Bachelor's in Computer Networks & Internet"
                  data-fa="???????? ???????? ????????? ? ???????"
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
                      data-fa="??????? ???? ? ??????? (?????????? ?????)"
                      >University of Applied Science and Technology (Iran
                      Informatics Branch)</a
                    ></em
                  >
                </p>
              </div>

              <div class="resume-item">
                <h4 data-en="MCSA & Network Plus" data-fa="MCSA ? Network Plus">
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
                      data-fa="????? ????? ???? ????? (????? ?????? ??????????? ??????????? ????????????? ??)"
                      >????? ????? ???? ?????</a
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
                      data-fa="????? ????? ???? ????? (????? ?????? ??????????? ??????????? ????????????? ??)"
                      >????? ????? ???? ?????</a
                    ></em
                  >
                </p>
              </div>

              <div class="resume-item">
                <h4
                  data-en="Associate Degree in Software Engineering"
                  data-fa="??????? ?????? ?????????"
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
                      data-fa="??????? ??? ? ??????? ???? ???????"
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
                      data-fa="????? ????? ???? ????? (????? ?????? ??????????? ??????????? ????????????? ??)"
                      >????? ????? ???? ?????</a
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
                data-fa="????? ????"
              >
                Professional Experience
              </h3>

              <div class="resume-item">
                <h4
                  data-en="DevOps & Network Infrastructure Specialist"
                  data-fa="????? DevOps ? ??????? ????"
                >
                  DevOps & Network Infrastructure Specialist
                </h4>
                <h5 data-en="Sep 2018 ? Present" data-fa="?????? ???? ? ??????">
                  Sep 2018 ? Present
                </h5>
                <p>
                  <em
                    ><a
                      href="https://newshadrinks.com"
                      target="_blank"
                      rel="noopener"
                      data-en="Newsha Drinks Co."
                      data-fa="???? ??????? ????"
                      >Newsha Drinks Co.</a
                    ></em
                  >
                </p>
                <ul>
                  <li
                    data-en="Designed and implemented virtualization infrastructure for all enterprise servers"
                    data-fa="????? ? ?????????? ??????? ?????????? ???? ???? ??????? ???????"
                  >
                    Designed and implemented virtualization infrastructure for
                    all enterprise servers
                  </li>
                  <li
                    data-en="Deployed SQL Server cluster with High Availability and Redgate monitoring"
                    data-fa="??????? ???? SQL Server ?? ?????? ?????? ???? ? ????? Redgate"
                  >
                    Deployed SQL Server cluster with High Availability and
                    Redgate monitoring
                  </li>
                  <li
                    data-en="Implemented core Windows infrastructure services: Active Directory, DNS, NTP, DFS, WDS, WSUS"
                    data-fa="?????????? ????????? ???? ??????? Windows: Active Directory? DNS? NTP? DFS? WDS? WSUS"
                  >
                    Implemented core Windows infrastructure services: Active
                    Directory, DNS, NTP, DFS, WDS, WSUS
                  </li>
                  <li
                    data-en="Deployed Nginx Proxy Manager for advanced HTTP/HTTPS traffic management and load balancing"
                    data-fa="??????? Nginx Proxy Manager ???? ?????? ??????? ?????? HTTP/HTTPS ? ????? ???"
                  >
                    Deployed Nginx Proxy Manager for advanced HTTP/HTTPS traffic
                    management and load balancing
                  </li>
                  <li
                    data-en="Redesigned LAN structure with segmented VLANs and removed direct static IPs"
                    data-fa="????? ???? ?????? LAN ?? VLAN ??? ???? ? ??? IP ??? ??????? ??????"
                  >
                    Redesigned LAN structure with segmented VLANs and removed
                    direct static IPs
                  </li>
                  <li
                    data-en="Configured advanced Cisco switching (STP, VTP, VLAN, Port-Channel, Port Security)"
                    data-fa="???????? ????????? ??????? ????? (STP? VTP? VLAN? Port-Channel? Port Security)"
                  >
                    Configured advanced Cisco switching (STP, VTP, VLAN,
                    Port-Channel, Port Security)
                  </li>
                  <li
                    data-en="Implemented secure Site-to-Site VPN tunnels between Tehran offices"
                    data-fa="?????????? ???????? VPN ??? Site-to-Site ??? ????? ?????"
                  >
                    Implemented secure Site-to-Site VPN tunnels between Tehran
                    offices
                  </li>
                  <li
                    data-en="Optimized Internet performance with MikroTik firewall and security rules"
                    data-fa="?????????? ?????? ??????? ?? ??????? MikroTik ? ?????? ??????"
                  >
                    Optimized Internet performance with MikroTik firewall and
                    security rules
                  </li>
                  <li
                    data-en="Installed Kaspersky Endpoint Security with centralized management console"
                    data-fa="??? Kaspersky Endpoint Security ?? ????? ?????? ??????"
                  >
                    Installed Kaspersky Endpoint Security with centralized
                    management console
                  </li>
                  <li
                    data-en="Upgraded Jira from version 6 to 9 with full documentation"
                    data-fa="?????? Jira ?? ???? 6 ?? 9 ?? ??????? ????"
                  >
                    Upgraded Jira from version 6 to 8 with full documentation
                  </li>
                  <li
                    data-en="Upgraded HQ infrastructure with fiber backbone and improved datacenter"
                    data-fa="?????? ??????? ???? ???? ?? ???? ????? ???????? ? ????? ???? ????"
                  >
                    Upgraded HQ infrastructure with fiber backbone and improved
                    datacenter
                  </li>
                  <li
                    data-en="Integrated CCTV surveillance system with stable infrastructure"
                    data-fa="???????????? ????? ????? CCTV ?? ??????? ??????"
                  >
                    Integrated CCTV surveillance system with stable
                    infrastructure
                  </li>
                  <li
                    data-en="Enhanced VoIP infrastructure for reliable internal telephony"
                    data-fa="????? ??????? VoIP ???? ???? ????? ???? ??????"
                  >
                    Enhanced VoIP infrastructure for reliable internal telephony
                  </li>
                  <li
                    data-en="Implemented Zabbix with Telegram API alerts"
                    data-fa="?????????? Zabbix ?? ???????? Telegram API"
                  >
                    Implemented Zabbix with Telegram API alerts
                  </li>
                  <li
                    data-en="Integrated Zabbix with Grafana for real-time dashboards"
                    data-fa="???????????? Zabbix ?? Grafana ???? ?????????? ???? ?????"
                  >
                    Integrated Zabbix with Grafana for real-time dashboards
                  </li>
                  <li
                    data-en="Deployed Cacti for graphical network traffic monitoring"
                    data-fa="??????? Cacti ???? ????? ??????? ?????? ????"
                  >
                    Deployed Cacti for graphical network traffic monitoring
                  </li>
                  <li
                    data-en="Implemented NAS and automated backups with Veeam Backup"
                    data-fa="?????????? NAS ? ???????????? ?????? ?? Veeam Backup"
                  >
                    Implemented NAS and automated backups with Veeam Backup
                  </li>
                  <li
                    data-en="Created technical diagrams, documentation, and asset inventory"
                    data-fa="????? ????????? ???? ??????? ? ????? ?????????"
                  >
                    Created technical diagrams, documentation, and asset
                    inventory
                  </li>
                  <li
                    data-en="Configured CDN for improved website performance and security"
                    data-fa="???????? CDN ???? ????? ?????? ? ????? ???????"
                  >
                    Configured CDN for improved website performance and security
                  </li>
                  <li
                    data-en="Configured ArvanCloud with CDN, caching, and access security policies"
                    data-fa="???????? ArvanCloud ?? CDN? ?? ? ????????? ????? ??????"
                  >
                    Configured ArvanCloud with CDN, caching, and access security
                    policies
                  </li>
                  <li
                    data-en="Deployed MongoDB for internal NoSQL services"
                    data-fa="??????? MongoDB ???? ????????? ????? NoSQL"
                  >
                    Deployed MongoDB for internal NoSQL services
                  </li>
                  <li
                    data-en="Implemented ABS NG for user behavior analytics and reporting"
                    data-fa="?????????? ABS NG ???? ????? ????? ????? ? ??????????"
                  >
                    Implemented ABS NG for user behavior analytics and reporting
                  </li>
                  <li
                    data-en="Configured Xray for traffic control, tunneling, and bypassing restrictions"
                    data-fa="???????? Xray ???? ????? ??????? ???????? ? ??? ??? ??????????"
                  >
                    Configured Xray for traffic control, tunneling, and
                    bypassing restrictions
                  </li>
                  <li
                    data-en="Deployed Docker for isolated environments and faster service delivery"
                    data-fa="??????? Docker ???? ???????? ???? ? ????? ??????? ?????"
                  >
                    Deployed Docker for isolated environments and faster service
                    delivery
                  </li>
                  <li
                    data-en="Configured Apache Maven for Java project build automation"
                    data-fa="???????? Apache Maven ???? ????????? ???? ????????? Java"
                  >
                    Configured Apache Maven for Java project build automation
                  </li>
                  <li
                    data-en="Installed n8n for workflow automation"
                    data-fa="??? n8n ???? ????????? ???? ???"
                  >
                    Installed n8n for workflow automation
                  </li>
                  <li
                    data-en="Developed Bash scripts for automation and server management"
                    data-fa="????? ??????????? Bash ???? ????????? ? ?????? ????"
                  >
                    Developed Bash scripts for automation and server management
                  </li>
                  <li
                    data-en="Migrated Windows web servers to Linux for performance and cost optimization"
                    data-fa="?????? ?????????? Windows ?? Linux ???? ?????????? ?????? ? ?????"
                  >
                    Migrated Windows web servers to Linux for performance and
                    cost optimization
                  </li>
                  <li
                    data-en="Configured load balancing across 5 Internet connections for stability"
                    data-fa="???????? ????? ??? ??? 5 ????? ??????? ???? ???????"
                  >
                    Configured load balancing across 5 Internet connections for
                    stability
                  </li>
                </ul>
              </div>

              <div class="resume-item">
                <h4 data-en="Network Administrator" data-fa="????? ????">
                  Network Administrator
                </h4>
                <h5
                  data-en="Jul 2017 ? Aug 2018"
                  data-fa="??? ???? ? ????? ????"
                >
                  Jul 2017 ? Aug 2018
                </h5>
                <p>
                  <em
                    ><a
                      href="https://uast48ac.ir/fa/"
                      target="_blank"
                      rel="noopener"
                      data-en="University of Applied Science & Technology ? Culture & Arts Unit 48"
                      data-fa="??????? ???? ? ??????? ? ???? ????? ? ??? ??"
                      >University of Applied Science & Technology ? Culture &
                      Arts Unit 48</a
                    ></em
                  >
                </p>
                <ul>
                  <li
                    data-en="Installed, configured, and maintained network equipment (switches, routers, servers)"
                    data-fa="???? ???????? ? ??????? ??????? ???? (?????? ????? ????)"
                  >
                    Installed, configured, and maintained network equipment
                    (switches, routers, servers)
                  </li>
                  <li
                    data-en="Performed passive networking tasks including cabling, termination, and rack installation"
                    data-fa="????? ????? ????????? ??????? ???? ????????? ???????? ? ??? ??"
                  >
                    Performed passive networking tasks including cabling,
                    termination, and rack installation
                  </li>
                  <li
                    data-en="Troubleshot LAN/WAN/Internet connectivity issues"
                    data-fa="???????? ?????? ????? LAN/WAN/???????"
                  >
                    Troubleshot LAN/WAN/Internet connectivity issues
                  </li>
                  <li
                    data-en="Installed software and hardware for end-users"
                    data-fa="??? ????????? ? ????????? ???? ??????? ?????"
                  >
                    Installed software and hardware for end-users
                  </li>
                  <li
                    data-en="Implemented CCTV surveillance systems"
                    data-fa="?????????? ????????? ????? CCTV"
                  >
                    Implemented CCTV surveillance systems
                  </li>
                  <li
                    data-en="Applied security policies and kept systems updated"
                    data-fa="????? ????????? ?????? ? ??????????? ????????"
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
          <h2 data-en="Services" data-fa="?????">Services</h2>
          <p
            data-en="Professional IT services including network design, system administration, infrastructure management, and technical consulting."
            data-fa="????? ??????? IT ???? ????? ????? ?????? ?????? ?????? ??????? ? ?????? ???."
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
          <h2 data-en="Articles" data-fa="??????">Articles</h2>
          <p
            data-en="A collection of my technical articles and insights on network infrastructure, system administration, and DevOps solutions."
            data-fa="????????? ?? ?????? ??? ? ???????? ?? ?? ????? ??????? ????? ?????? ????? ? ????????? DevOps."
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
        <!-- End Main Container -->
      </section>
      <!-- End Articles Section -->

      @endverbatim
      @include('partials.testimonials')
@verbatim
      <!-- /Testimonials Section -->
      <!-- /Testimonials Section -->

      <!-- ===============================================
      ==================== CONTACT SECTION ================
      =============================================== -->
      <section id="contact" class="contact section">
        <!-- Section Title -->
        <div class="container section-title" data-aos="fade-up">
          <h2 data-en="Contact" data-fa="تماس با من">Contact</h2>
          <p
            data-en="Get in touch for professional IT services, network solutions, or technical consulting."
            data-fa="???? ????? ??????? IT? ????????? ???? ?? ?????? ??? ?? ?? ???? ??????."
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
                    data-fa="?????? ?? ?? ??? ????"
                    >Let's Work Together</span
                  >
                      <i class="bi bi-people-fill title-accent" aria-hidden="true"></i>
                </h3>
                <p
                  class="contact-intro-text"
                  data-en="Ready to discuss your IT infrastructure needs? I'm here to help you achieve your goals with professional solutions."
                  data-fa="????? ??? ?? ???? ??????? ??????? IT ??? ????? ?? ????? ???? ?? ?? ????????? ??????? ?? ??? ??? ???."
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
                      <h4 data-en="Call Me" data-fa="???? ?????">Call Me</h4>
                      <p
                        data-en="Direct phone consultation"
                        data-fa="?????? ????? ??????"
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
                      <h4 data-en="WhatsApp" data-fa="??????">WhatsApp</h4>
                      <p
                        data-en="Quick messaging & support"
                        data-fa="?????????? ???? ? ????????"
                      >
                        Quick messaging & support
                      </p>
                      <a
                        href="https://wa.me/989197276219"
                        target="_blank"
                        rel="noopener"
                        class="contact-link"
                      >
                        <span data-en="Start Chat" data-fa="???? ?????"
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
                      <h4 data-en="Email Me" data-fa="?????">Email Me</h4>
                      <p
                        data-en="Detailed project discussions"
                        data-fa="??????? ?????? ?????"
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
                      <h4 data-en="Telegram" data-fa="??????">Telegram</h4>
                      <p data-en="Instant communication" data-fa="?????? ????">
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
                      <h4 data-en="My Location" data-fa="?????? ??">
                        My Location
                      </h4>
                    </div>
                    <div class="location-content">
                      <p data-en="Tehran, Iran" data-fa="?????? ?????">
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
                          title="?????? ????? - ?????? ?????"
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
                      <span data-en="Send a Message" data-fa="????? ????"
                        >Send a Message</span
                      >
                      <i class="bi bi-chat-dots-fill title-accent" aria-hidden="true"></i>
                    </h3>
                    <p
                      class="form-subtitle"
                      data-en="Fill out the form below and I'll get back to you within 24 hours"
                      data-fa="??? ??? ?? ?? ???? ? ?? ??? ?? ???? ?? ??? ???? ????? ???"
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
                    @endverbatim
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="csrf_token" id="csrf_token" value="{{ csrf_token() }}">
@verbatim
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
                          <span data-en="Your Name" data-fa="??? ???"
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
                          <span data-en="Your Email" data-fa="????? ???"
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
                        <span data-en="Subject" data-fa="?????">Subject</span>
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
                        <span data-en="Message" data-fa="????">Message</span>
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
                          data-fa="?? ??? ?????..."
                          role="status"
                          aria-live="polite"
                          aria-busy="true"
                        >
                          <i
                            class="bi bi-hourglass-split"
                            aria-hidden="true"
                          ></i>
                          <span data-en="Sending..." data-fa="?? ??? ?????..."
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
                          data-fa="???? ?? ?????? ????? ??! ?? ???? ?? ??? ???? ????? ???."
                          role="status"
                          aria-live="polite"
                        >
                          <i class="bi bi-check-circle" aria-hidden="true"></i>
                          <span
                            data-en="Message sent successfully! I'll get back to you soon."
                            data-fa="???? ?? ?????? ????? ??! ?? ???? ?? ??? ???? ????? ???."
                            >Message sent successfully! I'll get back to you
                            soon.</span
                          >
                        </div>
                      </div>

                      <button type="submit" class="submit-btn">
                        <span data-en="Send Message" data-fa="????? ????"
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
    <script src="/assets/js/contact-form.js?v=1403" defer></script>
    <script src="/assets/js/main.js?v=1403" defer></script>

    <!-- Internationalization (i18n) Support -->
    <!-- Language Toggle JavaScript -->
    <script src="/assets/js/i18n.js?v=1301" defer></script>

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