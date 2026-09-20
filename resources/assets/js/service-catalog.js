(() => {
  "use strict";

  const drawer = document.getElementById("service-drawer");
  const catalog = document.getElementById("service-catalog");
  if (!drawer || !catalog || typeof drawer.showModal !== "function") {
    return;
  }

  const titleEl = drawer.querySelector("#service-drawer-title");
  const leadEl = drawer.querySelector(".service-drawer-lead");
  const featuresEl = drawer.querySelector(".service-drawer-features");
  const markEl = drawer.querySelector(".service-drawer-mark");
  const contactEl = document.getElementById("service-drawer-contact");
  const subjectField = document.getElementById("subject-field");
  const serviceField = document.getElementById("contact-service-slug");
  let activeCard = null;

  const currentLang = () =>
    document.documentElement.lang === "fa" ? "fa" : "en";

  const textFor = (node, lang) => {
    if (!node) return "";
    const attr = lang === "fa" ? "data-fa" : "data-en";
    return node.getAttribute(attr) || node.textContent.trim();
  };

  const fillDrawer = (card) => {
    activeCard = card;
    const lang = currentLang();
    const source = card.querySelector(".service-details-source");
    const mark = card.querySelector(".service-card-mark");
    titleEl.textContent = textFor(card.querySelector(".title"), lang);
    leadEl.textContent = textFor(source?.querySelector(".service-details-lead"), lang);
    markEl.innerHTML = mark ? mark.innerHTML : "";
    featuresEl.replaceChildren();
    source?.querySelectorAll(".service-details-features li").forEach((item) => {
      const li = document.createElement("li");
      li.textContent = textFor(item, lang);
      featuresEl.appendChild(li);
    });
    const subject =
      lang === "fa"
        ? card.getAttribute("data-service-subject-fa")
        : card.getAttribute("data-service-subject-en");
    if (contactEl) {
      contactEl.dataset.serviceSlug = card.getAttribute("data-service-slug") || "";
      contactEl.dataset.serviceSubject = subject || "";
    }
  };

  const applyContactContext = (card) => {
    if (!card) return;
    const lang = currentLang();
    const slug = card.getAttribute("data-service-slug") || "";
    const subject =
      lang === "fa"
        ? card.getAttribute("data-service-subject-fa")
        : card.getAttribute("data-service-subject-en");
    if (serviceField) {
      serviceField.value = slug;
    }
    if (subjectField && subject) {
      subjectField.value = subject;
    }
  };

  const openDrawer = (card) => {
    fillDrawer(card);
    if (!drawer.open) {
      drawer.showModal();
    }
    const closeBtn = drawer.querySelector("[data-service-drawer-close]");
    closeBtn?.focus();
  };

  const closeDrawer = () => {
    if (drawer.open) {
      drawer.close();
    }
  };

  catalog.addEventListener("click", (event) => {
    const detailsBtn = event.target.closest(".service-details-open");
    if (detailsBtn) {
      event.preventDefault();
      const card = detailsBtn.closest(".service-catalog-card");
      if (card) {
        openDrawer(card);
      }
      return;
    }

    const contactLink = event.target.closest(".service-contact-link");
    if (contactLink) {
      applyContactContext(contactLink.closest(".service-catalog-card"));
    }
  });

  drawer.addEventListener("click", (event) => {
    if (event.target === drawer || event.target.closest("[data-service-drawer-close]")) {
      closeDrawer();
    }
  });

  contactEl?.addEventListener("click", () => {
    applyContactContext(activeCard);
    closeDrawer();
  });

  window.addEventListener("meetaj:languagechange", () => {
    if (drawer.open && activeCard) {
      fillDrawer(activeCard);
    }
  });
})();
