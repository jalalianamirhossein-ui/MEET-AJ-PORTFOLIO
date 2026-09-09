/**
 * ===============================================
 * MEET AJ PORTFOLIO - MAIN JAVASCRIPT
 * ===============================================
 *
 * Main JavaScript functionality for the portfolio website
 * Handles navigation, mobile menu, and UI interactions
 *
 * Features:
 * - Mobile navigation toggle
 * - Smooth scrolling
 * - Menu overlay management
 * - Accessibility improvements
 *
 * ===============================================
 */
(function () {
  "use strict";

  const headerToggleBtn = document.querySelector("#menu-toggle");
  const header = document.querySelector("#header");
  let menuTrigger = null;

  // Simple debounce helper to prevent ReferenceError and calm resize spam
  function debounce(fn, delay = 200) {
    let timeout;
    return function (...args) {
      clearTimeout(timeout);
      timeout = setTimeout(() => fn.apply(this, args), delay);
    };
  }

  // Create overlay for mobile
  let overlay = document.getElementById("menu-overlay");
  if (!overlay) {
    overlay = document.createElement("div");
    overlay.id = "menu-overlay";
    document.body.appendChild(overlay);
  }

  function syncMenuAccessibility() {
    if (!header) return;

    const isMobileMenu = window.innerWidth < 1200;
    const isOpen = header.classList.contains("header-show");
    header.inert = isMobileMenu && !isOpen;
    header.setAttribute("aria-hidden", String(isMobileMenu && !isOpen));
  }

  function openMenu() {
    if (header) {
      menuTrigger = document.activeElement;
      header.classList.add("header-show");
      overlay.classList.add("active");
      if (headerToggleBtn) {
        headerToggleBtn.setAttribute("aria-expanded", "true");
        const icon = headerToggleBtn.querySelector("i");
        if (icon) {
          icon.classList.remove("bi-list");
          icon.classList.add("bi-x");
        }
      }
      document.body.classList.add("menu-open");
      syncMenuAccessibility();

      const firstMenuLink = header.querySelector(".navmenu a");
      firstMenuLink?.focus({ preventScroll: true });
    }
  }

  function closeMenu({ restoreFocus = false } = {}) {
    if (header) {
      header.classList.remove("header-show");
      overlay.classList.remove("active");
      if (headerToggleBtn) {
        headerToggleBtn.setAttribute("aria-expanded", "false");
        const icon = headerToggleBtn.querySelector("i");
        if (icon) {
          icon.classList.add("bi-list");
          icon.classList.remove("bi-x");
        }
      }
      document.body.classList.remove("menu-open");
      syncMenuAccessibility();
      if (restoreFocus && menuTrigger instanceof HTMLElement) {
        menuTrigger.focus({ preventScroll: true });
      }
    }
  }

  function headerToggle() {
    if (header && header.classList.contains("header-show")) {
      closeMenu();
    } else {
      openMenu();
    }
  }

  // Event listeners
  if (headerToggleBtn) {
    headerToggleBtn.addEventListener("click", headerToggle);
  }

  if (overlay) {
    overlay.addEventListener("click", () => closeMenu({ restoreFocus: true }));
  }

  // Close menu after clicking menu links on mobile
  document.querySelectorAll("#navmenu a").forEach((navmenu) => {
    navmenu.addEventListener("click", () => {
      if (
        window.innerWidth < 1200 &&
        header &&
        header.classList.contains("header-show")
      ) {
        closeMenu();
      }
    });
  });

  syncMenuAccessibility();

  // Improve keyboard navigation performance
  document.addEventListener("keydown", function (e) {
    if (
      e.key === "Escape" &&
      header &&
      header.classList.contains("header-show")
    ) {
      closeMenu({ restoreFocus: true });
    }
  });

  // scroll top is managed at the end of the file

  function aosInit() {
    if (window.AOS) {
      // Improve mobile settings for AOS
      let config = {
        duration: 600,
        easing: "ease-in-out",
        once: true,
        mirror: false,
      };

      if (window.innerWidth <= 768) {
        config.duration = 400; // Reduce duration for mobile
        config.offset = 50; // Reduce offset for mobile
      }

      if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
        config.disable = true;
      }

      AOS.init(config);
    }
  }
  window.addEventListener("load", aosInit);

  if (window.PureCounter) {
    // Improve mobile settings for PureCounter
    let config = {
      duration: 2000,
      delay: 10,
    };

    if (window.innerWidth <= 768) {
      config.duration = 1500; // Reduce duration for mobile
      config.delay = 5;
    }

    new PureCounter(config);
  }

  let skillsAnimation = document.querySelectorAll(".skills-animation");
  if (skillsAnimation.length > 0 && window.Waypoint) {
    skillsAnimation.forEach((item) => {
      // Improve mobile settings for Waypoint
      let offset = "80%";
      if (window.innerWidth <= 768) {
        offset = "60%"; // Reduce offset for mobile
      }

      new Waypoint({
        element: item,
        offset: offset,
        handler: function () {
          let progress = item.querySelectorAll(".progress .progress-bar");
          progress.forEach((el) => {
            const value = el.getAttribute("aria-valuenow");
            if (value) {
              // Improve animation for mobile
              let duration = "0.9s";
              if (window.innerWidth <= 768) {
                duration = "0.6s";
              }
              el.style.transition = `width ${duration} ease`;
              el.style.width = value + "%";
            }
          });
        },
      });
    });
  }

  if (window.GLightbox) {
    // Improve mobile settings for GLightbox
    let config = {
      selector: ".glightbox",
      touchNavigation: true,
      loop: true,
      autoplayVideos: false,
    };

    if (window.innerWidth <= 768) {
      config.touchNavigation = true;
      config.keyboardNavigation = false; // Disable keyboard navigation on mobile
    }

    const glightbox = GLightbox(config);
  }

  function initPortfolio() {
    document
      .querySelectorAll(".isotope-layout")
      .forEach(function (isotopeItem) {
        let layout = isotopeItem.getAttribute("data-layout") ?? "masonry";
        let filter = isotopeItem.getAttribute("data-default-filter") ?? "*";
        let sort = isotopeItem.getAttribute("data-sort") ?? "original-order";

        let initIsotope;
        const container = isotopeItem.querySelector(".isotope-container");

        if (container && window.imagesLoaded && window.Isotope) {
          // Initialize before below-the-fold images arrive. Waiting for every
          // lazy thumbnail delayed filtering and encouraged an unnecessary
          // 30 MB image fetch on first visit.
          let transitionDuration = "0.6s";
          if (window.innerWidth <= 768) {
            transitionDuration = "0.4s";
          }

          initIsotope = new Isotope(container, {
            itemSelector: ".isotope-item",
            layoutMode: layout,
            filter: filter,
            sortBy: sort,
            transitionDuration: transitionDuration,
            isOriginLeft: document.documentElement.dir !== "rtl",
          });
          container._isotopeInstance = initIsotope;
          container.classList.add("isotope-ready");

          // Relayout incrementally as images and fonts settle instead of
          // blocking initialization on all lazy media.
          imagesLoaded(container).on("progress", () => {
            if (container._isotopeInstance) initIsotope.arrange();
          });
          setTimeout(() => initIsotope.arrange(), 150);
          if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(() => {
              if (container._isotopeInstance) initIsotope.arrange();
            });
          }

          // After initializing Isotope, refresh AOS
          if (typeof aosInit === "function") {
            setTimeout(aosInit, 100);
          }

          // Event listeners for filters
          isotopeItem
            .querySelectorAll(".isotope-filters li")
            .forEach(function (filters) {
              filters.setAttribute("role", "button");
              filters.setAttribute("tabindex", "0");
              filters.setAttribute(
                "aria-pressed",
                String(filters.classList.contains("filter-active")),
              );

              const activateFilter = function () {
                const activeFilter = isotopeItem.querySelector(
                  ".isotope-filters .filter-active",
                );
                if (activeFilter) {
                  activeFilter.classList.remove("filter-active");
                  activeFilter.setAttribute("aria-pressed", "false");
                }
                this.classList.add("filter-active");
                this.setAttribute("aria-pressed", "true");
                if (initIsotope) {
                  initIsotope.arrange({
                    filter: this.getAttribute("data-filter"),
                  });
                  setTimeout(function () {
                    if (typeof aosInit === "function") aosInit();
                  }, 200);
                }
              };

              filters.addEventListener("click", activateFilter, false);
              filters.addEventListener("keydown", function (event) {
                if (event.key === "Enter" || event.key === " ") {
                  event.preventDefault();
                  this.click();
                }
              });
            });
        } else if (container) {
          // Keep the Bootstrap grid usable if an optional enhancement fails to
          // load. Retrying forever creates an unnecessary timer on every page.
          console.warn("Portfolio enhancements are unavailable; using the static grid.");
        }
      });
  }

  // Run Portfolio after complete DOM load
  window.addEventListener("load", initPortfolio);

  // Safeguard: relayout isotope on resize/orientation to keep articles grid aligned
  window.addEventListener(
    "resize",
    debounce(() => {
      document.querySelectorAll(".isotope-container").forEach((container) => {
        if (container._isotopeInstance) container._isotopeInstance.arrange();
      });
    }, 150),
  );

  function initTypedText() {
    const typedElement = document.querySelector(".typed");
    if (!typedElement || !window.Typed) return;

    const language = document.documentElement.lang === "fa" ? "fa" : "en";
    const source =
      language === "fa"
        ? typedElement.getAttribute("data-typed-items-fa")
        : typedElement.getAttribute("data-typed-items");
    const strings = (source || "")
      .split(",")
      .map((item) => item.trim())
      .filter(Boolean);

    if (!strings.length) return;

    typedElement._typedInstance?.destroy();
    typedElement.textContent = strings[0];
    typedElement.setAttribute("aria-label", strings[0]);
    typedElement.setAttribute("aria-live", "off");
    typedElement._typedInstance = new window.Typed(typedElement, {
      strings,
      typeSpeed: 70,
      backSpeed: 35,
      backDelay: 1800,
      loop: true,
    });
  }

  function labelIconOnlyLinks() {
    document.querySelectorAll(".preview-link, .details-link").forEach((link) => {
      if (link.hasAttribute("aria-label")) return;
      const title =
        link.closest(".portfolio-content")?.querySelector("h4")?.textContent?.trim() ||
        link.getAttribute("title") ||
        "article";
      const isPreview = link.classList.contains("preview-link");
      link.setAttribute(
        "aria-label",
        isPreview ? `Preview image: ${title}` : `Read article: ${title}`,
      );
    });
  }

  document.addEventListener("DOMContentLoaded", () => {
    initTypedText();
    labelIconOnlyLinks();
  });

  window.addEventListener("meetaj:languagechange", () => {
    initTypedText();
    labelIconOnlyLinks();
  });

  // Enable tap-to-reveal overlay for portfolio cards on touch devices
  function initPortfolioTouchToggle() {
    if (!window.matchMedia("(hover: none), (pointer: coarse)").matches) {
      return;
    }

    const cards = document.querySelectorAll(".portfolio .portfolio-content");
    if (!cards.length) return;

    const clearActive = (except) => {
      cards.forEach((card) => {
        if (card !== except) card.classList.remove("is-active");
      });
    };

    document.addEventListener("click", (event) => {
      if (!event.target.closest(".portfolio .portfolio-content")) {
        clearActive();
      }
    });

    cards.forEach((card) => {
      card.addEventListener("click", (event) => {
        const link = event.target.closest("a");
        const isActive = card.classList.contains("is-active");

        if (!isActive) {
          event.preventDefault();
          clearActive(card);
          card.classList.add("is-active");
          return;
        }

        if (!link) {
          card.classList.remove("is-active");
        }
      });
    });
  }

  window.addEventListener("load", initPortfolioTouchToggle);

  function bindSwiperAutoplayControls(swiperElement, swiper) {
    const testimonials = swiperElement.closest(".testimonials");
    const toggle = testimonials?.querySelector("[data-swiper-autoplay-toggle]");
    if (!testimonials || !toggle || !swiper.autoplay) {
      toggle?.setAttribute("hidden", "");
      return;
    }

    const reducedMotion = window.matchMedia?.(
      "(prefers-reduced-motion: reduce)",
    ).matches;
    if (reducedMotion) {
      swiper.autoplay.stop();
      toggle.setAttribute("hidden", "");
      return;
    }
    let userPaused = false;

    const labels = () =>
      document.documentElement.lang === "fa"
        ? {
            pause: "توقف حرکت نظرات",
            play: "پخش حرکت نظرات",
            pauseText: "توقف حرکت",
            playText: "پخش حرکت",
          }
        : {
            pause: "Pause testimonial motion",
            play: "Play testimonial motion",
            pauseText: "Pause motion",
            playText: "Play motion",
          };

    const updateToggle = () => {
      const isPaused = userPaused;
      const label = labels();
      const icon = toggle.querySelector("i");
      const text = toggle.querySelector("span");

      toggle.setAttribute("aria-pressed", String(isPaused));
      toggle.setAttribute("aria-label", isPaused ? label.play : label.pause);
      toggle.title = isPaused ? label.play : label.pause;
      icon?.classList.toggle("bi-pause-fill", !isPaused);
      icon?.classList.toggle("bi-play-fill", isPaused);
      if (text) text.textContent = isPaused ? label.playText : label.pauseText;
    };

    const pause = () => swiper.autoplay.stop();
    const resume = () => {
      if (!userPaused && !document.hidden) {
        swiper.autoplay.start();
      }
    };

    updateToggle();

    toggle.addEventListener("click", () => {
      userPaused = !userPaused;
      if (userPaused) pause();
      else resume();
      updateToggle();
    });

    testimonials.addEventListener("focusin", pause);
    testimonials.addEventListener("focusout", (event) => {
      if (!testimonials.contains(event.relatedTarget)) resume();
    });
    document.addEventListener("visibilitychange", () => {
      if (document.hidden) pause();
      else resume();
    });
    document.addEventListener("meetaj:languagechange", updateToggle);
  }

  function enhanceSwiperPagination(swiper) {
    const labelBullets = () => {
      swiper.pagination?.bullets?.forEach((bullet, index) => {
        bullet.setAttribute("role", "button");
        bullet.setAttribute("tabindex", "0");
        bullet.setAttribute("aria-label", `Show testimonial ${index + 1}`);

        if (bullet.dataset.keyboardReady) return;
        bullet.dataset.keyboardReady = "true";
        bullet.addEventListener("keydown", (event) => {
          if (event.key !== "Enter" && event.key !== " ") return;
          event.preventDefault();
          swiper.slideToLoop(index);
        });
      });
    };

    labelBullets();
    swiper.on("paginationUpdate", labelBullets);
  }

  function initSwiper() {
    if (window.Swiper) {
      document
        .querySelectorAll(".init-swiper")
        .forEach(function (swiperElement) {
          const configElement = swiperElement.querySelector(".swiper-config");
          if (configElement) {
            try {
              let config = JSON.parse(configElement.innerHTML.trim());

              const isRtl = document.documentElement.dir === "rtl";
              const isTestimonialsSlider =
                swiperElement.closest(".testimonials");

              // Keep Swiper aware of document direction for testimonials
              if (isTestimonialsSlider) {
                swiperElement.setAttribute("dir", isRtl ? "rtl" : "ltr");
                swiperElement.classList.toggle("swiper-rtl", isRtl);
              }

              const hasBreakpoints =
                config.breakpoints &&
                Object.keys(config.breakpoints).length > 0;

              if (!hasBreakpoints) {
                if (window.innerWidth <= 768) {
                  config.slidesPerView = 1;
                  config.spaceBetween = 20;
                } else if (window.innerWidth <= 991) {
                  config.slidesPerView = 2;
                  config.spaceBetween = 30;
                } else {
                  config.slidesPerView = 3;
                  config.spaceBetween = 30;
                }
              }

              if (
                isTestimonialsSlider &&
                isRtl &&
                config.navigation &&
                config.navigation.nextEl &&
                config.navigation.prevEl
              ) {
                const originalNext = config.navigation.nextEl;
                config.navigation.nextEl = config.navigation.prevEl;
                config.navigation.prevEl = originalNext;
              }

              const reducedMotion = window.matchMedia?.(
                "(prefers-reduced-motion: reduce)",
              ).matches;
              if (reducedMotion) {
                config.autoplay = false;
              } else if (window.innerWidth <= 768 && config.autoplay) {
                config.autoplay = Object.assign({}, config.autoplay, {
                  delay: config.autoplay.delay || 4000,
                  disableOnInteraction: false,
                });
              }

              if (swiperElement.classList.contains("swiper-tab")) {
                if (typeof initSwiperWithCustomPagination === "function") {
                  initSwiperWithCustomPagination(swiperElement, config);
                }
              } else {
                const swiper = new Swiper(swiperElement, config);
                if (isTestimonialsSlider) {
                  if (!swiperElement.id) swiperElement.id = "testimonials-carousel";
                  const toggle = isTestimonialsSlider.querySelector(
                    "[data-swiper-autoplay-toggle]",
                  );
                  toggle?.setAttribute("aria-controls", swiperElement.id);
                  enhanceSwiperPagination(swiper);
                  bindSwiperAutoplayControls(swiperElement, swiper);
                }
              }
            } catch (e) {
              console.warn("Invalid Swiper config:", e);
            }
          }
        });
    }
  }
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initSwiper, { once: true });
  } else {
    initSwiper();
  }

  window.addEventListener("load", function () {
    if (window.location.hash && document.querySelector(window.location.hash)) {
      setTimeout(() => {
        let section = document.querySelector(window.location.hash);
        let scrollMarginTop = getComputedStyle(section).scrollMarginTop;
        let offset = parseInt(scrollMarginTop) || 0;

        // Improve mobile performance for scroll
        if (window.innerWidth <= 768) {
          offset += 20; // Add more margin for mobile
        }

        window.scrollTo({
          top: section.offsetTop - offset,
          behavior: "smooth",
        });
      }, 100);
    }
  });

  let navmenulinks = document.querySelectorAll(".navmenu a");
  function navmenuScrollspy() {
    navmenulinks.forEach((navmenulink) => {
      if (!navmenulink.hash) return;
      let section = document.querySelector(navmenulink.hash);
      if (!section) return;

      // Improve mobile performance for scrollspy
      let offset = 200;
      if (window.innerWidth <= 768) {
        offset = 100; // Reduce offset for mobile
      }

      let position = window.scrollY + offset;
      if (
        position >= section.offsetTop &&
        position <= section.offsetTop + section.offsetHeight
      ) {
        document
          .querySelectorAll(".navmenu a.active")
          .forEach((link) => link.classList.remove("active"));
        navmenulink.classList.add("active");
      } else {
        navmenulink.classList.remove("active");
      }
    });
  }
  window.addEventListener("load", navmenuScrollspy);
  let scrollFrame = null;
  window.addEventListener(
    "scroll",
    () => {
      if (scrollFrame !== null) return;
      scrollFrame = window.requestAnimationFrame(() => {
        navmenuScrollspy();
        scrollFrame = null;
      });
    },
    { passive: true },
  );

  // Improve mobile performance for resize events
  let resizeTimeout;
  function throttledResize() {
    if (!resizeTimeout) {
      resizeTimeout = setTimeout(function () {
        // Swiper already owns its responsive configuration. Refresh existing
        // instances instead of creating duplicate sliders on every resize.
        document.querySelectorAll(".init-swiper").forEach((swiperElement) => {
          if (swiperElement.swiper && typeof swiperElement.swiper.update === "function") {
            swiperElement.swiper.update();
          }
        });
        resizeTimeout = null;
      }, 250);
    }
  }

  window.addEventListener("resize", throttledResize);

  // Add new resize event listener
  window.addEventListener("resize", function () {
    if (
      window.innerWidth >= 1200 &&
      header &&
      header.classList.contains("header-show")
    ) {
      closeMenu();
    }
    syncMenuAccessibility();
  });

  // Load-more for articles (portfolio) section
  function initArticlesLoadMore() {
    const container = document.querySelector("#portfolio .isotope-container");
    const loadMoreBtn = document.getElementById("articles-load-more");
    if (!container || !loadMoreBtn) return;

    const items = Array.from(container.querySelectorAll(".portfolio-item"));
    const batchSize = 6;
    let visibleCount = batchSize;
    let ready = false;
    let attempts = 0;

    const getFilteredItems = () => {
      const iso = container._isotopeInstance;
      if (iso && Array.isArray(iso.filteredItems)) {
        return iso.filteredItems.map((entry) => entry.element);
      }
      return items;
    };

    const updateVisibility = () => {
      const filtered = getFilteredItems();

      // Reset hidden state on all items first
      items.forEach((item) => item.classList.remove("is-hidden"));

      filtered.forEach((item, index) => {
        const show = index < visibleCount;
        item.classList.toggle("is-hidden", !show);
      });

      if (container._isotopeInstance) {
        container._isotopeInstance.arrange();
      }

      loadMoreBtn.style.display =
        visibleCount >= filtered.length ? "none" : "inline-flex";
    };

    const kickOff = () => {
      if (ready) return;
      if (container._isotopeInstance) {
        ready = true;
        updateVisibility();
      } else if (attempts++ < 20) {
        // Give the optional layout plugin one second to finish initialization.
        setTimeout(kickOff, 50);
      } else {
        // Graceful fallback for pages where the optional plugin is unavailable.
        ready = true;
        updateVisibility();
      }
    };

    kickOff();

    loadMoreBtn.addEventListener("click", () => {
      visibleCount += batchSize;
      updateVisibility();
    });

    // Reset and recalc when filters change
    document.querySelectorAll(".portfolio-filters li").forEach((filterBtn) => {
      filterBtn.addEventListener("click", () => {
        visibleCount = batchSize;
        // Give Isotope a moment to apply the filter
        setTimeout(updateVisibility, 50);
      });
    });

  }

  window.addEventListener("load", initArticlesLoadMore);

  // Modern Bilingual Preloader Animation
  const preloader = document.querySelector("#preloader");

  if (preloader) {
    let preloaderDismissed = false;
    const hidePreloader = () => {
      if (preloaderDismissed) return;
      preloaderDismissed = true;
      preloader.classList.remove("visible");
      preloader.classList.add("hidden");
      window.setTimeout(() => {
        preloader.style.display = "none";
      }, 200);
    };

    // Do not hide meaningful content behind a load-event gate. DOM-ready is
    // sufficient, while the timeout remains a safe fallback for slow scripts.
    document.addEventListener("DOMContentLoaded", hidePreloader, { once: true });
    window.setTimeout(hidePreloader, 900);
  }

  // ===============================================
  // CONTACT FORM HANDLING
  // ===============================================
  function initContactForm() {
    const form = document.querySelector(".php-email-form");
    if (!form) return;

    const submitBtn = form.querySelector('button[type="submit"]');
    const statusContainer = form.querySelector(".form-status");
    const loadingEl = statusContainer?.querySelector(".loading");
    const errorEl = statusContainer?.querySelector(".error-message");
    const sentEl = statusContainer?.querySelector(".sent-message");
    if (!submitBtn) return;
    const originalText = submitBtn.innerHTML;

    const setLoading = (isLoading) => {
      loadingEl?.classList.toggle("visible", isLoading);
      if (isLoading) loadingEl?.setAttribute("aria-busy", "true");
      else loadingEl?.removeAttribute("aria-busy");
    };

    const showError = (message) => {
      const errorSpan = errorEl?.querySelector("span");
      if (errorSpan) errorSpan.textContent = message;
      errorEl?.classList.add("visible");
      window.requestAnimationFrame(() => {
        errorEl?.focus({ preventScroll: false });
      });
    };

    form.addEventListener("submit", async function (e) {
      e.preventDefault();
      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }

      const formData = new FormData(form);

      // Show loading state
      submitBtn.disabled = true;
      submitBtn.innerHTML =
        '<i class="bi bi-hourglass-split"></i><span data-en="Sending..." data-fa="در حال ارسال...">Sending...</span>';
      submitBtn.setAttribute("aria-busy", "true");
      setLoading(true);
      errorEl?.classList.remove("visible");
      sentEl?.classList.remove("visible");

      // Get CSRF token - must succeed before submitting
      let csrfToken = null;
      try {
        const csrfResponse = await fetch("/forms/get-csrf-token.php", {
          cache: "no-store",
          credentials: "same-origin",
        });
        if (!csrfResponse.ok) {
          throw new Error("CSRF endpoint returned " + csrfResponse.status);
        }
        const csrfData = await csrfResponse.json();
        if (csrfData.token) {
          csrfToken = csrfData.token;
        } else {
          throw new Error("No CSRF token in response");
        }
      } catch (err) {
        console.error("Could not fetch CSRF token:", err);
        setLoading(false);
        showError("Security token error. Please refresh and try again.");
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        submitBtn.removeAttribute("aria-busy");
        return;
      }

      formData.set("csrf_token", csrfToken);

      try {
        const response = await fetch("/forms/contact.php", {
          method: "POST",
          body: formData,
          credentials: "same-origin",
        });

        // Read response body to check for "OK"
        const responseText = await response.text();

        if (response.ok && responseText.trim() === "OK") {
          // Show success
          setLoading(false);
          sentEl?.classList.add("visible");
          form.reset();

          // Hide success message after 5 seconds
          setTimeout(() => {
            sentEl?.classList.remove("visible");
          }, 5000);
        } else {
          setLoading(false);
          showError(responseText || "Error sending message. Please try again.");
        }
      } catch (error) {
        console.error("Form submission error:", error);
        setLoading(false);
        showError("An error occurred. Please try again later.");
      } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        submitBtn.removeAttribute("aria-busy");
      }
    });
  }

  window.addEventListener("load", initContactForm);

  // ===============================================
  // ARTICLE CODE COPY COMPONENT
  // ===============================================
  function initArticleCodeCopy() {
    const selector = ".article-page .article-copy-button";
    const defaultIcon = '<i class="bi bi-clipboard" aria-hidden="true"></i>';
    const successIcon = '<i class="bi bi-check2" aria-hidden="true"></i>';

    const labels = () => {
      const isRtl = document.documentElement.dir === "rtl";
      return isRtl
        ? { copy: "کپی کد", copied: "کپی شد", error: "کپی نشد" }
        : {
            copy: "Copy code",
            copied: "Code copied",
            error: "Could not copy code",
          };
    };

    const updateButton = (button, state = "default") => {
      const label = labels();
      button.classList.remove("is-copied", "is-copy-error");
      button.disabled = false;
      button.innerHTML = defaultIcon;
      button.setAttribute("aria-label", label.copy);
      button.title = label.copy;

      if (state === "copied") {
        button.classList.add("is-copied");
        button.innerHTML = successIcon;
        button.setAttribute("aria-label", label.copied);
        button.title = label.copied;
      }

      if (state === "error") {
        button.classList.add("is-copy-error");
        button.setAttribute("aria-label", label.error);
        button.title = label.error;
      }
    };

    const fallbackCopy = (value) => {
      const textArea = document.createElement("textarea");
      textArea.value = value;
      textArea.setAttribute("readonly", "");
      textArea.style.cssText = "position:fixed;left:-9999px;top:0;opacity:0;";
      document.body.appendChild(textArea);
      textArea.select();
      const copied = document.execCommand("copy");
      textArea.remove();
      return copied;
    };

    document.querySelectorAll(selector).forEach((button) => {
      button.type = "button";
      updateButton(button);
    });

    document.addEventListener("click", async (event) => {
      const button = event.target.closest(selector);
      if (!button || button.dataset.articleCopyBusy === "true") return;

      const code = button
        .closest(".article-code")
        ?.querySelector("code")?.textContent;
      if (!code) return;

      button.dataset.articleCopyBusy = "true";
      button.disabled = true;

      try {
        if (navigator.clipboard?.writeText && window.isSecureContext) {
          await navigator.clipboard.writeText(code);
        } else if (!fallbackCopy(code)) {
          throw new Error("Clipboard fallback failed");
        }

        updateButton(button, "copied");
      } catch (error) {
        console.error("Article code copy failed:", error);
        updateButton(button, "error");
      }

      window.setTimeout(() => {
        delete button.dataset.articleCopyBusy;
        updateButton(button);
      }, 1800);
    });
  }

  window.addEventListener("load", initArticleCodeCopy);

  // ===============================================
  // ARTICLE SCROLL PROGRESS
  // ===============================================
  function initScrollProgress() {
    // Only run on article pages
    if (!document.querySelector(".article-content")) return;

    const progressBar = document.createElement("div");
    progressBar.className = "scroll-progress-container";
    progressBar.innerHTML = '<div class="scroll-progress"></div>';
    document.body.appendChild(progressBar);

    const progress = progressBar.querySelector(".scroll-progress");

    window.addEventListener("scroll", () => {
      const winScroll =
        document.body.scrollTop || document.documentElement.scrollTop;
      const height =
        document.documentElement.scrollHeight -
        document.documentElement.clientHeight;
      const scrolled = (winScroll / height) * 100;
      progress.style.width = scrolled + "%";
    });
  }

  window.addEventListener("load", initScrollProgress);
})();
