/** Entry motion is progressive enhancement: content is visible by default. */
(() => {
  'use strict';
  let cleanup = () => {};

  function init() {
    cleanup();
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    if (reducedMotion.matches || !('IntersectionObserver' in window)) return;

    const selector = '.main [data-aos], #resume .resume-item, .about-domain, .about-secondary, .skill-group, .service-catalog-card, .fi-main .fi-wi-widget, .fi-main > .fi-page .fi-section';
    const seen = new WeakSet();
    const observer = new IntersectionObserver((entries) => {
      for (const entry of entries) {
        if (!entry.isIntersecting) continue;
        const element = entry.target;
        // Do not interrupt a focused form/control with entry motion.
        if (!element.contains(document.activeElement)) {
          element.classList.add('meetaj-scroll-reveal');
          element.addEventListener('animationend', () => {
            element.classList.remove('meetaj-scroll-reveal');
          }, { once: true });
        }
        observer.unobserve(element);
      }
    }, { threshold: 0, rootMargin: '0px 0px -24px 0px' });

    const observeBlocks = () => {
      for (const element of document.querySelectorAll(selector)) {
        if (seen.has(element)) continue;
        seen.add(element);
        if (element.closest('#hero')) continue;
        // Animate individual resume branches, avoiding motion on their parent columns.
        if (element.matches('#resume .resume-tree > [data-aos]')) continue;
        const resumeBranch = element.matches('#resume .resume-item');
        if (!resumeBranch && element.parentElement.closest('[data-aos], .fi-wi-widget, .fi-section')) continue;
        // Avoid flashing content already visible on load or at a deep link.
        if (element.getBoundingClientRect().top < window.innerHeight) continue;
        observer.observe(element);
      }
    };
    observeBlocks();
    // Filament loads dashboard widgets lazily; observe newly rendered blocks.
    const mutations = new MutationObserver(observeBlocks);
    const main = document.querySelector('.fi-main, .main');
    if (main) mutations.observe(main, { childList: true, subtree: true });

    const stop = () => {
      observer.disconnect();
      mutations.disconnect();
      document.querySelectorAll('.meetaj-scroll-reveal').forEach(element => {
        element.classList.remove('meetaj-scroll-reveal');
      });
    };
    const motionChange = (event) => {
      if (event.matches) stop();
    };
    reducedMotion.addEventListener('change', motionChange);
    // Browsing-history restoration should show the restored position immediately.
    const restored = event => { if (event.persisted) stop(); };
    window.addEventListener('pageshow', restored);
    cleanup = () => {
      stop();
      reducedMotion.removeEventListener('change', motionChange);
      window.removeEventListener('pageshow', restored);
    };
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
  document.addEventListener('livewire:navigated', init);
})();
