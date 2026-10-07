(() => {
  const contents = document.querySelector('.article-toc-disclosure');
  if (!contents) return;

  const desktop = window.matchMedia('(min-width: 1024px)');
  const updateLayout = () => { contents.open = desktop.matches; };
  updateLayout();
  desktop.addEventListener('change', updateLayout);
})();
