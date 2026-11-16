(function () {
  var toggle = document.querySelector('.wr-menu-toggle');
  if (toggle) {
    toggle.addEventListener('click', function () {
      var open = document.body.classList.toggle('wr-nav-open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }
  var header = document.querySelector('.wr-header');
  if (header) {
    var onScroll = function () { header.classList.toggle('is-scrolled', window.scrollY > 8); };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }
})();
