(function () {
  'use strict';
  var data = window.wrData || {};
  var strings = data.strings || {};

  /* Mobile menu */
  var toggle = document.querySelector('.wr-menu-toggle');
  if (toggle) {
    toggle.addEventListener('click', function () {
      var open = document.body.classList.toggle('wr-nav-open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }

  /* Sticky header shadow */
  var header = document.querySelector('.wr-header');
  if (header) {
    var onScroll = function () { header.classList.toggle('is-scrolled', window.scrollY > 8); };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* Search panel */
  var searchToggle = document.querySelector('.wr-search-toggle');
  var searchPanel = document.querySelector('.wr-search');
  if (searchToggle && searchPanel) {
    searchToggle.addEventListener('click', function () {
      var open = searchPanel.hasAttribute('hidden');
      if (open) searchPanel.removeAttribute('hidden');
      else searchPanel.setAttribute('hidden', '');
      searchToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (open) {
        var field = searchPanel.querySelector('input[type=search]');
        if (field) field.focus();
      }
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !searchPanel.hasAttribute('hidden')) {
        searchPanel.setAttribute('hidden', '');
        searchToggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  /* Variation swatches: buttons in place of the size and colour dropdowns. */
  var COLOURS = {
    black: '#141414', white: '#fbfbfa', grey: '#8a8a88', gray: '#8a8a88',
    charcoal: '#3a3a38', stone: '#cfcbc4', sand: '#ded6c8', navy: '#1e2432',
    ecru: '#e8e2d6', cream: '#f1ebdf', olive: '#4a4a3c', brown: '#5a4636'
  };

  function buildSwatches(form) {
    [].forEach.call(form.querySelectorAll('.variations select'), function (select) {
      if (select.dataset.wrSwatch) return;
      select.dataset.wrSwatch = '1';

      var row = select.closest('tr') || select.parentNode;
      var label = row.querySelector('label');
      var text = label ? label.textContent : '';
      var isColour = /colou?r/i.test(text);

      var wrap = document.createElement('div');
      wrap.className = 'wr-swatches' + (isColour ? ' wr-swatches--colour' : '');
      wrap.setAttribute('role', 'group');
      if (text) wrap.setAttribute('aria-label', text.trim());

      [].forEach.call(select.options, function (option) {
        if (!option.value) return;
        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'wr-swatch-btn' + (isColour ? ' wr-swatch-btn--colour' : '');
        button.dataset.value = option.value;
        var name = option.textContent.trim();
        if (isColour) {
          button.style.backgroundColor = COLOURS[name.toLowerCase()] || '#c9c7c2';
          button.title = name;
          button.setAttribute('aria-label', name);
        } else {
          button.textContent = name;
        }
        button.addEventListener('click', function () {
          select.value = option.value;
          if (window.jQuery) window.jQuery(select).trigger('change');
          select.dispatchEvent(new Event('change', { bubbles: true }));
          sync();
        });
        wrap.appendChild(button);
      });

      function sync() {
        [].forEach.call(wrap.querySelectorAll('button'), function (button) {
          button.classList.toggle('is-active', button.dataset.value === select.value);
          button.setAttribute('aria-pressed', button.dataset.value === select.value ? 'true' : 'false');
        });
      }

      select.parentNode.insertBefore(wrap, select);
      select.classList.add('wr-select-hidden');
      select.addEventListener('change', sync);
      sync();
    });

    /* A size chosen on a shop card is carried over in the URL. */
    var wanted = new URLSearchParams(window.location.search).get('wr_size');
    if (wanted) {
      [].forEach.call(form.querySelectorAll('.variations select'), function (select) {
        [].forEach.call(select.options, function (option) {
          if (option.value.toLowerCase() !== wanted.toLowerCase()) return;
          select.value = option.value;
          if (window.jQuery) window.jQuery(select).trigger('change');
          select.dispatchEvent(new Event('change', { bubbles: true }));
        });
      });
    }
  }

  var variationForm = document.querySelector('form.variations_form');
  if (variationForm) buildSwatches(variationForm);

  /* Shop-card quick add */
  var toast = null;
  function showToast(html, isError) {
    if (!toast) {
      toast = document.createElement('div');
      toast.className = 'wr-toast';
      toast.setAttribute('role', 'status');
      document.body.appendChild(toast);
    }
    toast.innerHTML = html;
    toast.classList.toggle('wr-toast--error', !!isError);
    toast.classList.add('is-visible');
    window.clearTimeout(showToast.timer);
    showToast.timer = window.setTimeout(function () { toast.classList.remove('is-visible'); }, 4500);
  }

  function updateFragments(fragments) {
    if (!fragments) return;
    Object.keys(fragments).forEach(function (selector) {
      var holder = document.createElement('div');
      holder.innerHTML = fragments[selector];
      var fresh = holder.firstElementChild;
      if (!fresh) return;
      [].forEach.call(document.querySelectorAll(selector), function (existing) {
        existing.replaceWith(fresh.cloneNode(true));
      });
    });
  }

  document.addEventListener('click', function (event) {
    var button = event.target.closest ? event.target.closest('.wr-quick__btn') : null;
    if (!button) return;
    event.preventDefault();

    var panel = button.closest('.wr-quick');
    if (!panel || !button.dataset.addId) return;
    var body = new URLSearchParams();
    // WooCommerce resolves the parent product and its attributes from a variation id.
    body.set('product_id', button.dataset.addId);
    body.set('quantity', '1');

    if (button.dataset.lastLabel === undefined) button.dataset.lastLabel = button.textContent;
    button.disabled = true;
    button.classList.add('is-busy');
    if (!button.classList.contains('wr-swatch-btn')) button.textContent = strings.adding || 'Adding…';

    fetch(data.addToCart || '/?wc-ajax=add_to_cart', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body.toString()
    })
      .then(function (r) { return r.json(); })
      .then(function (result) {
        if (!result || result.error) {
          showToast(result && result.product_url ? strings.failed : (strings.failed || 'Failed'), true);
          return;
        }
        updateFragments(result.fragments);
        var detail = button.dataset.label ? ', ' + button.dataset.label : '';
        showToast(
          '<strong>' + (strings.added || 'Added to cart') + '</strong><span>' + (panel.dataset.name || '') + detail + '</span>' +
          '<a href="' + (data.cartUrl || '/cart/') + '">' + (strings.viewCart || 'View cart') + '</a>'
        );
        if (window.jQuery) window.jQuery(document.body).trigger('wc_fragment_refresh');
      })
      .catch(function () { showToast(strings.failed || 'Failed', true); })
      .then(function () {
        button.disabled = false;
        button.classList.remove('is-busy');
        if (button.dataset.lastLabel !== undefined) button.textContent = button.dataset.lastLabel;
      });
  });
})();
