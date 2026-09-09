/**
 * Language Switch System - Resilient Version
 * Cleans corrupted data-fa attributes and falls back gracefully.
 */

(() => {
  "use strict";

  const KEY = "lang";

  const init = () => {
    const stored = localStorage.getItem(KEY);
    if (stored === "fa" || stored === "en") return stored;

    const cookieMatch = document.cookie.match(/(?:^|;\s*)lang=(fa|en)\b/);
    if (cookieMatch) return cookieMatch[1];

    const browserLang = navigator.language || navigator.userLanguage;
    if (browserLang && browserLang.startsWith("fa")) return "fa";

    return "en";
  };

  const setCookie = (lang) => {
    document.cookie = `lang=${lang}; Path=/; Max-Age=31536000; SameSite=Lax`;
  };

  const isCorrupted = (val) =>
    val == null || val.indexOf("\uFFFD") !== -1 || /[\u0000-\u001f]/.test(val);

  const getRtlStyle = () => {
    const existing = document.getElementById("rtl-style");
    if (existing) return existing;

    const script = Array.from(document.scripts).find((item) =>
      /assets\/js\/i18n\.js(?:\?|$)/.test(item.src),
    );
    if (!script?.src) return null;

    const stylesheet = document.createElement("link");
    stylesheet.id = "rtl-style";
    stylesheet.rel = "stylesheet";
    stylesheet.href = new URL("../css/rtl.css", script.src).href;
    stylesheet.disabled = true;
    document.head.appendChild(stylesheet);
    return stylesheet;
  };

  /**
   * Translate only the text that belongs to an element. Replacing `textContent`
   * on every translated element looks convenient, but it also removes nested
   * icons, links, and presentational spans (for example the two-line hero
   * heading). Keeping child elements intact makes language changes safe and
   * avoids a full-page reload.
   */
  const setElementText = (element, value) => {
    const textNodes = Array.from(element.childNodes).filter(
      (node) =>
        node.nodeType === Node.TEXT_NODE && node.nodeValue.trim().length > 0,
    );

    if (element.children.length === 0) {
      element.textContent = value;
      return;
    }

    if (textNodes.length === 1) {
      const textNode = textNodes[0];
      const trailingSpace = /\s$/.test(textNode.nodeValue) ? " " : "";
      textNode.nodeValue = value + trailingSpace;
    }
  };

  const apply = (lang) => {
    const isPersian = lang === "fa";
    const html = document.documentElement;

    html.lang = lang;
    html.dir = isPersian ? "rtl" : "ltr";

    const rtlStyle = getRtlStyle();
    if (rtlStyle) {
      if (isPersian) {
        rtlStyle.disabled = false;
        rtlStyle.setAttribute("rel", "stylesheet");
      } else {
        rtlStyle.disabled = true;
        rtlStyle.removeAttribute("rel");
      }
    }

    document.querySelectorAll("[data-en][data-fa]").forEach((el) => {
      const en = el.getAttribute("data-en");
      let fa = el.getAttribute("data-fa");

      if (isCorrupted(fa)) {
        fa = en || fa || "";
        el.setAttribute("data-fa", fa);
      }

      const translation = isPersian ? fa : en;
      if (translation != null) {
        setElementText(el, translation);
      }
    });

    const btn = document.getElementById("lang-toggle");
    if (btn) {
      btn.setAttribute("aria-pressed", String(isPersian));
      btn.setAttribute(
        "aria-label",
        isPersian
          ? "\u062A\u063A\u06CC\u06CC\u0631 \u0632\u0628\u0627\u0646 \u0628\u0647 \u0627\u0646\u06AF\u0644\u06CC\u0633\u06CC"
          : "Switch language to \u0641\u0627\u0631\u0633\u06CC",
      );

      const cur = btn.querySelector(".i18n-cur");
      const alt = btn.querySelector(".i18n-alt");
      if (cur && alt) {
        cur.textContent = isPersian ? "FA" : "EN";
        alt.textContent = isPersian ? "EN" : "FA";
        cur.classList.add("is-active");
        alt.classList.remove("is-active");
      }
    }

    const preloaderContainer = document.getElementById("preloader-container");
    const loadingText = document.getElementById("loading-text");
    if (preloaderContainer && loadingText) {
      preloaderContainer.className = `preloader-container ${
        isPersian ? "rtl" : "ltr"
      }`;
      loadingText.textContent = isPersian
        ? "\u062F\u0631 \u062D\u0627\u0644 \u0628\u0627\u0631\u06AF\u0630\u0627\u0631\u06CC..."
        : "Loading...";
    }

    localStorage.setItem(KEY, lang);
    setCookie(lang);
    window.dispatchEvent(
      new CustomEvent("meetaj:languagechange", { detail: { lang } }),
    );
  };

  const toggle = () => {
    const currentLang = document.documentElement.lang;
    const nextLang = currentLang === "fa" ? "en" : "fa";
    apply(nextLang);
  };

  const injectLanguageToggle = () => {
    if (document.querySelector("#lang-toggle")) return;

    const headerSelectors = [
      "nav",
      ".navbar",
      ".navmenu",
      "header",
      ".header",
      ".topbar",
      "#header",
      "#navmenu",
    ];

    let headerContainer = null;
    for (const selector of headerSelectors) {
      headerContainer = document.querySelector(selector);
      if (headerContainer) break;
    }

    const toggleButton = document.createElement("button");
    toggleButton.id = "lang-toggle";
    toggleButton.className = "i18n-link";
    toggleButton.type = "button";
    toggleButton.setAttribute("aria-pressed", "false");
    toggleButton.setAttribute("aria-label", "Switch language");

    const curSpan = document.createElement("span");
    curSpan.className = "i18n-cur";
    curSpan.textContent = "EN";

    const sepSpan = document.createElement("span");
    sepSpan.className = "i18n-sep";
    sepSpan.textContent = " | ";

    const altSpan = document.createElement("span");
    altSpan.className = "i18n-alt";
    altSpan.textContent = "FA";

    toggleButton.appendChild(curSpan);
    toggleButton.appendChild(sepSpan);
    toggleButton.appendChild(altSpan);

    if (headerContainer) {
      headerContainer.appendChild(toggleButton);
    } else {
      document.body.appendChild(toggleButton);
    }
  };

  const initSystem = () => {
    apply(init());
    injectLanguageToggle();
    apply(document.documentElement.lang);

    const btn = document.getElementById("lang-toggle");
    if (!btn) return;

    btn.addEventListener("click", (e) => {
      e.preventDefault();
      btn.style.transform = "scale(0.95)";
      btn.style.opacity = "0.7";
      setTimeout(() => {
        btn.style.transform = "";
        btn.style.opacity = "";
      }, 100);
      toggle();
    });

    btn.addEventListener("keydown", (e) => {
      if (e.key === " " || e.key === "Enter") {
        e.preventDefault();
        btn.style.transform = "scale(0.95)";
        btn.style.opacity = "0.7";
        setTimeout(() => {
          btn.style.transform = "";
          btn.style.opacity = "";
        }, 100);
        toggle();
      }
    });

  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initSystem);
  } else {
    initSystem();
  }

  window.i18n = {
    getCurrentLanguage: () => document.documentElement.lang,
    setLanguage: (lang) => {
      if (lang === "fa" || lang === "en") apply(lang);
    },
    toggleLanguage: toggle,
    getAvailableLanguages: () => ["fa", "en"],
    isRtlCssEnabled: () => {
      const rtlStyle = document.getElementById("rtl-style");
      return rtlStyle ? !rtlStyle.disabled : false;
    },
  };
})();
