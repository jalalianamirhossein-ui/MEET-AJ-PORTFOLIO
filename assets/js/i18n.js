/**
 * Language switch — one clickable EN/FA control. No dropdown.
 * DE is included only when the page actually has data-de copy.
 * Switching language rewrites on-page strings and does not navigate away.
 */

(() => {
  "use strict";

  const KEY = "lang";
  const LABELS = {
    en: { code: "EN", name: "English" },
    fa: { code: "FA", name: "\u0641\u0627\u0631\u0633\u06CC" },
    de: { code: "DE", name: "Deutsch" },
  };

  const availableLanguages = () => {
    const langs = ["en"];
    if (document.querySelector("[data-fa]")) langs.push("fa");
    if (document.querySelector("[data-de]")) langs.push("de");
    return langs;
  };

  const init = (langs) => {
    const stored = localStorage.getItem(KEY);
    if (langs.includes(stored)) return stored;

    const cookieMatch = document.cookie.match(/(?:^|;\s*)lang=(en|fa|de)\b/);
    if (cookieMatch && langs.includes(cookieMatch[1])) return cookieMatch[1];

    const browserLang = navigator.language || navigator.userLanguage || "";
    if (browserLang.startsWith("fa") && langs.includes("fa")) return "fa";
    if (browserLang.startsWith("de") && langs.includes("de")) return "de";

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

  const translationFor = (el, lang) => {
    if (lang === "fa") {
      let fa = el.getAttribute("data-fa");
      if (isCorrupted(fa)) {
        fa = el.getAttribute("data-en") || fa || "";
        el.setAttribute("data-fa", fa);
      }
      return fa;
    }
    if (lang === "de") {
      const de = el.getAttribute("data-de");
      return de && !isCorrupted(de) ? de : el.getAttribute("data-en");
    }
    return el.getAttribute("data-en");
  };

  const nextLanguage = (langs, current) => {
    const index = Math.max(0, langs.indexOf(current));
    return langs[(index + 1) % langs.length];
  };

  const apply = (lang) => {
    const langs = availableLanguages();
    const next = langs.includes(lang) ? lang : "en";
    const isPersian = next === "fa";
    const html = document.documentElement;

    html.lang = next;
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

    document.querySelectorAll("[data-en]").forEach((el) => {
      const translation = translationFor(el, next);
      if (translation != null) {
        setElementText(el, translation);
      }
    });

    document.querySelectorAll("[data-en-placeholder]").forEach((el) => {
      const attr =
        next === "fa"
          ? "data-fa-placeholder"
          : next === "de"
            ? "data-de-placeholder"
            : "data-en-placeholder";
      const value = el.getAttribute(attr) || el.getAttribute("data-en-placeholder");
      if (value) {
        el.setAttribute("placeholder", value);
      }
    });

    const btn = document.getElementById("lang-toggle");
    const current = btn?.querySelector(".lang-switcher-current");
    const upcoming = nextLanguage(langs, next);
    if (btn && current) {
      current.textContent = LABELS[next].code;
      const targetName = LABELS[upcoming]?.name || upcoming.toUpperCase();
      btn.setAttribute(
        "aria-label",
        isPersian
          ? `\u062a\u063a\u06cc\u06cc\u0631 \u0632\u0628\u0627\u0646 \u0628\u0647 ${targetName}`
          : `Switch language to ${targetName}`,
      );
      btn.title = btn.getAttribute("aria-label");
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

    localStorage.setItem(KEY, next);
    setCookie(next);
    window.dispatchEvent(
      new CustomEvent("meetaj:languagechange", { detail: { lang: next } }),
    );
  };

  const mountSwitcher = (root) => {
    const header = document.querySelector("#header");
    const mount =
      document.getElementById("lang-mount") ||
      header?.querySelector(".brand-lang");
    const desktop = window.matchMedia("(min-width: 1200px)").matches;

    if (desktop && mount) {
      mount.appendChild(root);
      root.classList.remove("is-floating");
      return;
    }

    if (desktop && header) {
      const logo = header.querySelector(".logo-section");
      (logo?.parentNode || header).insertBefore(
        root,
        logo ? logo.nextSibling : header.firstChild,
      );
      root.classList.remove("is-floating");
      return;
    }

    document.body.appendChild(root);
    root.classList.add("is-floating");
  };

  const injectLanguageToggle = (langs) => {
    let root = document.getElementById("lang-switcher");
    const legacy = document.getElementById("lang-toggle");

    if (!root) {
      root = document.createElement("div");
      root.id = "lang-switcher";
      if (legacy && legacy.parentNode) {
        legacy.replaceWith(root);
      }
    } else {
      root.replaceChildren();
    }

    root.className = "lang-switcher";

    const btn = document.createElement("button");
    btn.id = "lang-toggle";
    btn.className = "lang-switcher-toggle";
    btn.type = "button";
    btn.setAttribute("aria-label", "Switch language");

    const current = document.createElement("span");
    current.className = "lang-switcher-current";
    current.textContent = "EN";

    const swap = document.createElement("span");
    swap.className = "lang-switcher-swap";
    swap.setAttribute("aria-hidden", "true");
    swap.textContent = "\u21c4";

    btn.appendChild(current);
    btn.appendChild(swap);
    root.appendChild(btn);

    if (langs.length < 2) {
      root.hidden = true;
    }

    mountSwitcher(root);

    btn.addEventListener("click", (event) => {
      event.preventDefault();
      event.stopPropagation();
      const available = availableLanguages();
      apply(nextLanguage(available, document.documentElement.lang));
    });

    window.addEventListener("resize", () => {
      mountSwitcher(root);
    });
  };

  const initSystem = () => {
    const langs = availableLanguages();
    injectLanguageToggle(langs);
    apply(init(langs));
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initSystem);
  } else {
    initSystem();
  }

  window.i18n = {
    getCurrentLanguage: () => document.documentElement.lang,
    setLanguage: (lang) => apply(lang),
    toggleLanguage: () => {
      const langs = availableLanguages();
      apply(nextLanguage(langs, document.documentElement.lang));
    },
    getAvailableLanguages: availableLanguages,
    isRtlCssEnabled: () => {
      const rtlStyle = document.getElementById("rtl-style");
      return rtlStyle ? !rtlStyle.disabled : false;
    },
  };
})();
