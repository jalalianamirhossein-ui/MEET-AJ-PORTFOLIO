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
  const MENU_LABELS = {
    en: { open: "Open menu", close: "Close menu" },
    fa: { open: "باز کردن منو", close: "بستن منو" },
  };

  // Simple debounce helper to prevent ReferenceError and calm resize spam
  function debounce(fn, delay = 200) {
    let timeout;
    return function (...args) {
      clearTimeout(timeout);
      timeout = setTimeout(() => fn.apply(this, args), delay);
    };
  }

  function enhanceMenuToggle() {
    if (!headerToggleBtn) return;
    headerToggleBtn.setAttribute("aria-controls", "header");
    headerToggleBtn.type = "button";
    if (!headerToggleBtn.querySelector(".menu-toggle-bars")) {
      const bars = document.createElement("span");
      bars.className = "menu-toggle-bars";
      bars.setAttribute("aria-hidden", "true");
      bars.innerHTML = "<span></span><span></span><span></span>";
      const icon = headerToggleBtn.querySelector("i");
      if (icon) icon.replaceWith(bars);
      else headerToggleBtn.prepend(bars);
    }
  }

  function menuLabel(open) {
    const lang = document.documentElement.lang === "fa" ? "fa" : "en";
    return open ? MENU_LABELS[lang].close : MENU_LABELS[lang].open;
  }

  function setToggleState(open) {
    if (!headerToggleBtn) return;
    headerToggleBtn.classList.toggle("is-open", open);
    headerToggleBtn.setAttribute("aria-expanded", String(open));
    headerToggleBtn.setAttribute("aria-label", menuLabel(open));
    const sr = headerToggleBtn.querySelector(".sr-only, .visually-hidden");
    if (sr) sr.textContent = menuLabel(open);
  }

  // Create overlay for mobile (kept for older CSS; fullscreen menu covers it)
  let overlay = document.getElementById("menu-overlay");
  if (!overlay) {
    overlay = document.createElement("div");
    overlay.id = "menu-overlay";
    document.body.appendChild(overlay);
  }

  function isMobileMenu() {
    return window.innerWidth < 1200;
  }

  function syncMenuAccessibility() {
    if (!header) return;

    const mobile = isMobileMenu();
    const isOpen = header.classList.contains("header-show");
    header.inert = mobile && !isOpen;
    header.setAttribute("aria-hidden", String(mobile && !isOpen));
    if (mobile && !isOpen) {
      header.setAttribute("hidden", "");
    } else {
      header.removeAttribute("hidden");
    }
  }

  function menuFocusables() {
    const nodes = [];
    if (headerToggleBtn) nodes.push(headerToggleBtn);
    const langToggle = document.getElementById("lang-toggle");
    if (langToggle) nodes.push(langToggle);
    if (header) {
      header
        .querySelectorAll('a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])')
        .forEach((el) => nodes.push(el));
    }
    return nodes.filter((el) => {
      if (!el || el.disabled || el.hidden) return false;
      const style = window.getComputedStyle(el);
      return style.display !== "none" && style.visibility !== "hidden";
    });
  }

  function trapMenuFocus(event) {
    if (!header || !header.classList.contains("header-show") || !isMobileMenu()) {
      return;
    }
    if (event.key !== "Tab") return;
    const items = menuFocusables();
    if (!items.length) return;
    const first = items[0];
    const last = items[items.length - 1];
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  }

  function setBackgroundInert(on) {
    const lang = document.getElementById("lang-switcher");
    [...document.body.children].forEach((el) => {
      if (
        el === header ||
        el === overlay ||
        el === headerToggleBtn ||
        el === lang
      ) {
        return;
      }
      el.inert = on;
    });
  }

  function openMenu() {
    if (!header || !isMobileMenu()) return;
    menuTrigger = document.activeElement;
    header.removeAttribute("hidden");
    header.inert = false;
    header.setAttribute("aria-hidden", "false");
    header.classList.add("header-show");
    overlay.classList.add("active");
    setToggleState(true);
    document.body.classList.add("menu-open");
    document.documentElement.classList.add("menu-open");
    setBackgroundInert(true);
    syncMenuAccessibility();

    const firstMenuLink = header.querySelector(".navmenu a");
    firstMenuLink?.focus({ preventScroll: true });
  }

  function closeMenu({ restoreFocus = false } = {}) {
    if (!header) return;
    header.classList.remove("header-show");
    overlay.classList.remove("active");
    setToggleState(false);
    document.body.classList.remove("menu-open");
    document.documentElement.classList.remove("menu-open");
    setBackgroundInert(false);
    syncMenuAccessibility();
    if (restoreFocus && menuTrigger instanceof HTMLElement) {
      menuTrigger.focus({ preventScroll: true });
    } else if (restoreFocus) {
      headerToggleBtn?.focus({ preventScroll: true });
    }
  }

  function headerToggle() {
    if (!isMobileMenu()) return;
    if (header && header.classList.contains("header-show")) {
      closeMenu({ restoreFocus: true });
    } else {
      openMenu();
    }
  }

  enhanceMenuToggle();
  setToggleState(false);

  if (headerToggleBtn) {
    headerToggleBtn.addEventListener("click", headerToggle);
  }

  if (overlay) {
    overlay.addEventListener("click", () => closeMenu({ restoreFocus: true }));
  }

  document.querySelectorAll("#navmenu a").forEach((navmenu) => {
    navmenu.addEventListener("click", () => {
      if (isMobileMenu() && header && header.classList.contains("header-show")) {
        closeMenu();
      }
    });
  });

  syncMenuAccessibility();

  document.addEventListener("keydown", function (e) {
    trapMenuFocus(e);
    if (
      e.key === "Escape" &&
      header &&
      header.classList.contains("header-show")
    ) {
      closeMenu({ restoreFocus: true });
    }
  });

  window.addEventListener(
    "resize",
    debounce(() => {
      if (!isMobileMenu() && header?.classList.contains("header-show")) {
        closeMenu();
      }
      syncMenuAccessibility();
    }, 150),
  );

  window.addEventListener("meetaj:languagechange", () => {
    setToggleState(header?.classList.contains("header-show"));
  });

  // scroll top is managed at the end of the file

  function aosInit() {
    if (window.AOS) {
      // Improve mobile settings for AOS
      let config = {
        duration: 560,
        easing: "ease-out-cubic",
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

  // Skill meters: announce the real value, then grow the bar to it once the
  // group scrolls into view. Values come from the markup; nothing is invented.
  function initSkillMeters() {
    const groups = document.querySelectorAll(".skills-animation");
    if (!groups.length) return;

    const bars = [];
    groups.forEach((group) => {
      group.querySelectorAll(".progress").forEach((row) => {
        const bar = row.querySelector(".progress-bar");
        if (!bar) return;
        const value = Number.parseInt(bar.getAttribute("aria-valuenow") || "", 10);
        if (!Number.isFinite(value)) return;

        bar.setAttribute("aria-valuemin", "0");
        bar.setAttribute("aria-valuemax", "100");
        bar.style.width = "0%";
        bars.push({ bar, row, value, done: false });
      });
    });

    if (!bars.length) return;

    // The visible skill name is the bar's label, and it is translated at
    // runtime, so re-read it whenever the language changes.
    const labelBars = () => {
      bars.forEach(({ bar, row, value }) => {
        const name = row.querySelector(".skill span")?.textContent?.trim();
        if (name) bar.setAttribute("aria-label", `${name}: ${value}%`);
      });
    };
    labelBars();
    window.addEventListener("meetaj:languagechange", labelBars);

    const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

    const fill = (entry) => {
      if (entry.done) return;
      entry.done = true;
      const target = `${entry.value}%`;
      if (reduceMotion.matches || typeof entry.bar.animate !== "function") {
        entry.bar.style.width = target;
        return;
      }
      entry.bar.style.width = target;
      entry.bar.animate(
        { width: ["0%", target] },
        { duration: 900, easing: "cubic-bezier(0.22, 1, 0.36, 1)", fill: "none" },
      );
    };

    if (!("IntersectionObserver" in window)) {
      bars.forEach(fill);
      return;
    }

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((observed) => {
          if (!observed.isIntersecting) return;
          const group = observed.target;
          bars
            .filter((entry) => group.contains(entry.bar))
            .forEach((entry, index) => {
              window.setTimeout(() => fill(entry), reduceMotion.matches ? 0 : index * 45);
            });
          observer.unobserve(group);
        });
      },
      { threshold: 0.2, rootMargin: "0px 0px -10% 0px" },
    );

    groups.forEach((group) => observer.observe(group));
  }

  initSkillMeters();

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

  function prefersReducedMotion() {
    return window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  }

  function initPortfolio() {
    document.querySelectorAll(".isotope-layout").forEach((layout) => {
      const container = layout.querySelector(".isotope-container");
      if (!container) return;

      container.classList.add("isotope-ready");
      const inPortfolio = Boolean(layout.closest("#portfolio"));
      const state = {
        filter: layout.getAttribute("data-default-filter") || "*",
        batchSize: inPortfolio ? 6 : Number.POSITIVE_INFINITY,
        visibleCount: inPortfolio ? 6 : Number.POSITIVE_INFINITY,
        booted: false,
      };
      container._flipState = state;

      const items = () =>
        Array.from(container.querySelectorAll(".isotope-item"));
      const matchesFilter = (el) =>
        state.filter === "*" || el.matches(state.filter);
      const nextVisible = () => {
        const matched = items().filter(matchesFilter);
        return new Set(matched.slice(0, state.visibleCount));
      };

      const capture = () => {
        const map = new Map();
        items().forEach((el) => {
          if (
            el.classList.contains("is-filtered-out") &&
            !el.classList.contains("is-flip-leave")
          ) {
            return;
          }
          const box = el.getBoundingClientRect();
          if (box.width === 0 && box.height === 0) return;
          map.set(el, {
            left: box.left,
            top: box.top,
            width: box.width,
            height: box.height,
          });
        });
        return map;
      };

      const clearInlineMotion = (el) => {
        el.style.transform = "";
        el.style.opacity = "";
        el.style.position = "";
        el.style.left = "";
        el.style.top = "";
        el.style.width = "";
        el.style.zIndex = "";
        el.classList.remove("is-flip-leave", "is-flip-enter", "is-flip-move");
      };

      const updateLoadMore = () => {
        const button = document.getElementById("articles-load-more");
        if (!button || !inPortfolio) return;
        const matched = items().filter(matchesFilter).length;
        button.style.display =
          state.visibleCount >= matched ? "none" : "inline-flex";
      };

      const applyVisibility = (visible) => {
        items().forEach((el) => {
          const show = visible.has(el);
          el.classList.toggle("is-filtered-out", !show);
          el.classList.toggle("is-hidden", !show);
        });
      };

      const animateFilter = () => {
        const reduce = prefersReducedMotion() || !state.booted;
        const duration = 420;
        const first = reduce ? new Map() : capture();
        const visible = nextVisible();

        items().forEach((el) => {
          el.getAnimations?.().forEach((anim) => anim.cancel());
          clearInlineMotion(el);
        });
        applyVisibility(visible);
        updateLoadMore();
        state.booted = true;
        if (reduce || typeof container.animate !== "function") return;

        const run = () => {
          const parent = container.getBoundingClientRect();
          const last = capture();

          first.forEach((box, el) => {
            if (last.has(el)) return;
            el.classList.remove("is-filtered-out", "is-hidden");
            el.classList.add("is-flip-leave");
            el.style.position = "absolute";
            el.style.left = `${box.left - parent.left}px`;
            el.style.top = `${box.top - parent.top}px`;
            el.style.width = `${box.width}px`;
            el.style.zIndex = "0";
            const leave = el.animate(
              [
                { transform: "translate3d(0,0,0)", opacity: 1 },
                { transform: "translate3d(0,12px,0)", opacity: 0 },
              ],
              {
                duration: Math.round(duration * 0.85),
                easing: "cubic-bezier(0.22, 1, 0.36, 1)",
                fill: "forwards",
              },
            );
            leave.onfinish = () => {
              el.classList.add("is-filtered-out", "is-hidden");
              clearInlineMotion(el);
            };
          });

          last.forEach((box, el) => {
            const prev = first.get(el);
            if (!prev) {
              el.classList.add("is-flip-enter");
              el.animate(
                [
                  { transform: "translate3d(0,14px,0)", opacity: 0 },
                  { transform: "translate3d(0,0,0)", opacity: 1 },
                ],
                { duration, easing: "cubic-bezier(0.22, 1, 0.36, 1)" },
              ).onfinish = () => el.classList.remove("is-flip-enter");
              return;
            }
            const dx = prev.left - box.left;
            const dy = prev.top - box.top;
            if (Math.abs(dx) < 1 && Math.abs(dy) < 1) return;
            el.classList.add("is-flip-move");
            el.animate(
              [
                { transform: `translate3d(${dx}px, ${dy}px, 0)` },
                { transform: "translate3d(0,0,0)" },
              ],
              { duration, easing: "cubic-bezier(0.22, 1, 0.36, 1)" },
            ).onfinish = () => el.classList.remove("is-flip-move");
          });
        };

        requestAnimationFrame(run);
      };

      layout.querySelectorAll(".isotope-filters [data-filter]").forEach((btn) => {
        if (btn.tagName !== "BUTTON") {
          btn.setAttribute("role", "button");
          btn.tabIndex = 0;
        }
        btn.setAttribute(
          "aria-pressed",
          String(btn.classList.contains("filter-active")),
        );

        const activate = () => {
          layout
            .querySelectorAll(".isotope-filters [data-filter]")
            .forEach((other) => {
              const on = other === btn;
              other.classList.toggle("filter-active", on);
              other.setAttribute("aria-pressed", String(on));
            });
          state.filter = btn.getAttribute("data-filter") || "*";
          state.visibleCount = Number.isFinite(state.batchSize)
            ? state.batchSize
            : Number.POSITIVE_INFINITY;
          animateFilter();
        };

        btn.addEventListener("click", activate);
        btn.addEventListener("keydown", (event) => {
          if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            activate();
          }
        });
      });

      const loadMore = document.getElementById("articles-load-more");
      if (loadMore && inPortfolio) {
        loadMore.addEventListener("click", () => {
          state.visibleCount += state.batchSize;
          animateFilter();
        });
      }

      animateFilter();
    });
  }

  window.addEventListener("load", initPortfolio);

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

              if (!hasBreakpoints && !isTestimonialsSlider) {
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
              if (isTestimonialsSlider) {
                config.slidesPerView = 1;
                if (reducedMotion) {
                  config.autoplay = false;
                } else if (config.autoplay && typeof config.autoplay === "object") {
                  config.autoplay.pauseOnMouseEnter = true;
                  config.autoplay.disableOnInteraction = false;
                }
              } else if (reducedMotion) {
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
                  if (swiper.autoplay) {
                    swiperElement.addEventListener("mouseenter", () => {
                      swiper.autoplay.stop();
                    });
                    swiperElement.addEventListener("mouseleave", () => {
                      swiper.autoplay.start();
                    });
                  }
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
    // Load-more and filtering are handled by initPortfolio (FLIP layout).
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

    window.addEventListener("meetaj:languagechange", () => {
      document.querySelectorAll(selector).forEach((button) => {
        if (button.classList.contains("is-copied") || button.classList.contains("is-copy-error")) {
          return;
        }
        updateButton(button);
      });
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

  function initArticleShare() {
    document.querySelectorAll("[data-copy-link]").forEach((button) => {
      button.addEventListener("click", async () => {
        const url = button.getAttribute("data-copy-link") || "";
        const status = button.closest(".article-share")?.querySelector(".article-share-status");
        const isFa = document.documentElement.lang === "fa";
        const copied = isFa ? "پیوند کپی شد" : "Link copied";
        const promptLabel = isFa ? "کپی لینک" : "Copy link";
        const done = () => {
          if (!status) return;
          status.hidden = false;
          status.textContent = copied;
          status.setAttribute("data-en", "Link copied");
          status.setAttribute("data-fa", "پیوند کپی شد");
        };
        try {
          if (navigator.clipboard?.writeText) {
            await navigator.clipboard.writeText(url);
            done();
          } else {
            window.prompt(promptLabel, url);
          }
        } catch (error) {
          window.prompt(promptLabel, url);
        }
      });
    });
  }

  window.addEventListener("load", initArticleShare);

  const aboutCore = document.querySelector("[data-about-core]");
  if (aboutCore) {
    const nodes = aboutCore.querySelectorAll(".about-node");
    const panels = aboutCore.querySelectorAll(".about-core-detail [data-panel]");
    const activate = (panel) => {
      nodes.forEach((node) => {
        const on = node.getAttribute("data-panel") === panel;
        node.classList.toggle("is-active", on);
        node.setAttribute("aria-pressed", on ? "true" : "false");
      });
      panels.forEach((item) => {
        const on = item.getAttribute("data-panel") === panel;
        item.classList.toggle("is-active", on);
        item.hidden = !on;
      });
      aboutCore.className = aboutCore.className
        .split(" ")
        .filter((cls) => cls && !cls.startsWith("is-"))
        .concat("is-" + panel)
        .join(" ");
    };
    aboutCore.classList.add("is-core");
    nodes.forEach((node) => {
      const panel = node.getAttribute("data-panel");
      node.addEventListener("click", () => activate(panel));
      node.addEventListener("focus", () => activate(panel));
      node.addEventListener("mouseenter", () => {
        if (window.matchMedia("(hover: hover)").matches) {
          activate(panel);
        }
      });
    });
  }
})();
