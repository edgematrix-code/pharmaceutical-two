/**
 * cart.js - cart, wishlist and checkout for the storefront.
 *
 * The cart lives in localStorage and is sent to api/order.php at checkout,
 * which recalculates every price from the database server-side.
 */
(function () {
  'use strict';

  var CART_KEY = 'arail_cart';
  var WISH_KEY = 'arail_wishlist';
  var COUPON_KEY = 'arail_coupon';
  var API = (window.ARAIL_BASE || '/') + 'api/order.php';
  var COUPON_NOTE = 'We\u2019ll apply this code when we confirm your order.';

  /* ---------- storage ---------- */
  function read(key) {
    try {
      var raw = localStorage.getItem(key);
      var data = raw ? JSON.parse(raw) : [];
      return Array.isArray(data) ? data : [];
    } catch (e) {
      return [];
    }
  }

  function write(key, value) {
    try {
      localStorage.setItem(key, JSON.stringify(value));
    } catch (e) { /* quota / private mode */ }
  }

  function money(n) {
    return '$' + (Math.round(n * 100) / 100).toFixed(2);
  }

  /** Escapes a value before it is placed in markup (names come from the DOM). */
  function esc(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function num(value) {
    var n = parseFloat(value);
    return isFinite(n) ? n : 0;
  }

  function qtyOf(item) {
    return Math.max(0, parseInt(item && item.qty, 10) || 0);
  }

  /** Sets the text of the first match inside a scope, when it exists. */
  function setText(scope, selector, value) {
    var el = scope.querySelector(selector);
    if (el) { el.textContent = value; }
  }

  /* ---------- coupon ---------- */
  /* There is no pricing engine for coupons yet, so the code the customer
     types is simply remembered and passed on with the order. */
  function getCoupon() {
    try {
      return (localStorage.getItem(COUPON_KEY) || '').trim();
    } catch (e) {
      return '';
    }
  }

  function setCoupon(code) {
    try {
      if (code) { localStorage.setItem(COUPON_KEY, code); }
      else { localStorage.removeItem(COUPON_KEY); }
    } catch (e) { /* quota / private mode */ }
  }

  function couponFormHtml(id, variant, code) {
    var filled = code !== '';
    return '<div class="coupon-form coupon-form--' + variant + '">'
      + '<div class="coupon-form__row">'
      + '<label class="coupon-form__label sr-only" for="' + id + '">Coupon code</label>'
      + '<input id="' + id + '" autocomplete="off" placeholder="Coupon code" class="coupon-form__input" type="text" name="coupon" value="' + esc(code) + '">'
      + '<button type="button" class="coupon-form__apply" data-coupon-apply' + (filled ? '' : ' disabled') + '>Apply</button>'
      + '</div>'
      + '<p class="text-xs text-[var(--muted)]" data-coupon-note' + (filled ? '' : ' hidden') + '>'
      + (filled ? COUPON_NOTE : '') + '</p>'
      + '</div>';
  }

  /** Mirrors the stored coupon into every coupon box on the page. */
  function syncCouponFields() {
    var code = getCoupon();
    document.querySelectorAll('.coupon-form__input[name="coupon"]').forEach(function (input) {
      if (input.value.trim() !== code) { input.value = code; }
      var row = input.parentNode;
      var apply = row ? row.querySelector('[data-coupon-apply]') : null;
      if (apply) { apply.disabled = code === ''; }
      var form = input.closest ? input.closest('.coupon-form') : null;
      var note = form ? form.querySelector('[data-coupon-note]') : null;
      if (note) {
        note.textContent = code === '' ? '' : COUPON_NOTE;
        note.hidden = code === '';
      }
    });
  }

  function bindCouponFields() {
    document.addEventListener('input', function (ev) {
      var input = ev.target.closest ? ev.target.closest('.coupon-form__input') : null;
      if (!input) return;
      var row = input.parentNode;
      var apply = row ? row.querySelector('[data-coupon-apply]') : null;
      if (apply) { apply.disabled = input.value.trim() === ''; }
    });

    document.addEventListener('click', function (ev) {
      var btn = ev.target.closest ? ev.target.closest('[data-coupon-apply]') : null;
      if (!btn) return;
      var row = btn.parentNode;
      var input = row ? row.querySelector('.coupon-form__input') : null;
      var code = input ? input.value.trim() : '';
      if (!code) return;
      setCoupon(code);
      syncCouponFields();
    });
  }

  function track(event, payload) {
    if (typeof window.gtag === 'function') {
      window.gtag('event', event, payload || {});
    }
  }

  /* ---------- cart operations ---------- */
  function getCart() { return read(CART_KEY); }

  function saveCart(items, silent) {
    write(CART_KEY, items);
    refreshBadges();
    // A silent save skips the redraw so a quantity input keeps focus while the
    // customer types; the caller updates the affected totals itself.
    if (!silent) {
      document.dispatchEvent(new CustomEvent('cart:updated'));
    }
  }

  function addItem(item, qty) {
    var items = getCart();
    qty = Math.max(1, parseInt(qty, 10) || 1);
    var slug = String(item.slug || '');
    if (!slug) return;
    var existing = null;
    for (var i = 0; i < items.length; i++) {
      if (items[i].slug === slug) { existing = items[i]; break; }
    }
    if (existing) {
      existing.qty += qty;
    } else {
      items.push({
        slug: slug,
        name: String(item.name || slug),
        price: parseFloat(item.price) || 0,
        image: String(item.image || ''),
        qty: qty
      });
    }
    saveCart(items);
    track('add_to_cart', {
      currency: 'USD',
      value: (parseFloat(item.price) || 0) * qty,
      items: [{ item_id: slug, item_name: String(item.name || slug), quantity: qty }]
    });
  }

  function removeItem(slug) {
    saveCart(getCart().filter(function (i) { return i.slug !== slug; }));
  }

  function cartTotal(items) {
    return items.reduce(function (sum, i) { return sum + (parseFloat(i.price) || 0) * (parseInt(i.qty, 10) || 0); }, 0);
  }

  function cartCount(items) {
    return items.reduce(function (sum, i) { return sum + (parseInt(i.qty, 10) || 0); }, 0);
  }

  /* ---------- badges ---------- */
  function refreshBadges() {
    var count = cartCount(getCart());
    document.querySelectorAll('[data-cart-count]').forEach(function (el) {
      el.textContent = String(count);
      el.hidden = count === 0;
    });
    document.querySelectorAll('[data-cart-count-inline]').forEach(function (el) {
      el.textContent = count > 0 ? '(' + count + ')' : '';
    });
  }

  /* ---------- wishlist ---------- */
  function getWish() { return read(WISH_KEY); }

  function toggleWish(slug) {
    var list = getWish();
    var idx = list.indexOf(slug);
    if (idx === -1) { list.push(slug); } else { list.splice(idx, 1); }
    write(WISH_KEY, list);
    refreshWishButtons();
    return idx === -1;
  }

  function refreshWishButtons() {
    var list = getWish();
    document.querySelectorAll('[data-wishlist-toggle]').forEach(function (btn) {
      var on = list.indexOf(btn.getAttribute('data-slug')) !== -1;
      btn.textContent = on ? 'Saved ✓' : 'Save for later';
      btn.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
  }

  /* ---------- add-to-cart binding ---------- */
  function bindAddButtons() {
    document.addEventListener('click', function (ev) {
      var btn = ev.target.closest ? ev.target.closest('[data-add-to-cart]') : null;
      if (!btn || btn.disabled) return;
      ev.preventDefault();
      addItem({
        slug: btn.getAttribute('data-slug'),
        name: btn.getAttribute('data-name'),
        price: btn.getAttribute('data-price'),
        image: btn.getAttribute('data-image')
      }, 1);
      var label = btn.querySelector('.product-card-add-label');
      if (label) {
        var original = 'Add to cart';
        label.textContent = 'Added ✓';
        setTimeout(function () { label.textContent = original; }, 1200);
      }
    });

    document.querySelectorAll('[data-cart-form]').forEach(function (form) {
      form.addEventListener('submit', function (ev) {
        ev.preventDefault();
        var data = new FormData(form);
        addItem({
          slug: data.get('slug'),
          name: data.get('name'),
          price: data.get('price'),
          image: data.get('image')
        }, data.get('qty'));
        flash(form, 'Added to cart.');
      });
    });
  }

  function flash(form, text) {
    var msg = form.querySelector('[data-cart-message]');
    if (!msg) {
      msg = document.createElement('p');
      msg.setAttribute('data-cart-message', '');
      msg.setAttribute('role', 'status');
      msg.className = 'text-sm text-[var(--accent)]';
      form.appendChild(msg);
    }
    msg.textContent = text;
  }

  /* ---------- wishlist button binding ---------- */
  function bindWishButtons() {
    document.addEventListener('click', function (ev) {
      var btn = ev.target.closest ? ev.target.closest('[data-wishlist-toggle]') : null;
      if (!btn) return;
      ev.preventDefault();
      var added = toggleWish(btn.getAttribute('data-slug'));
      btn.textContent = added ? 'Saved ✓' : 'Save for later';
      btn.setAttribute('aria-pressed', added ? 'true' : 'false');
    });
  }

  /* ---------- copy to clipboard ---------- */
  function copyToClipboard(value) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      return navigator.clipboard.writeText(value);
    }
    return new Promise(function (resolve, reject) {
      try {
        var area = document.createElement('textarea');
        area.value = value;
        area.setAttribute('readonly', '');
        area.style.position = 'fixed';
        area.style.top = '-1000px';
        document.body.appendChild(area);
        area.select();
        var ok = document.execCommand('copy');
        document.body.removeChild(area);
        ok ? resolve() : reject(new Error('copy failed'));
      } catch (e) {
        reject(e);
      }
    });
  }

  /* Any [data-copy-address] button copies the nearest [data-copy-value] text
     inside its [data-copy-scope]. Used by the checkout wallet panel and the
     order confirmation. */
  function bindCopyButtons() {
    document.addEventListener('click', function (ev) {
      var btn = ev.target.closest ? ev.target.closest('[data-copy-address]') : null;
      if (!btn) return;
      ev.preventDefault();
      var scope = btn.closest('[data-copy-scope]') || btn.closest('section') || document;
      var node = scope.querySelector('[data-copy-value]');
      var value = node ? String(node.getAttribute('data-copy-value') || node.textContent || '').trim() : '';
      if (value === '') return;
      var original = btn.getAttribute('data-copy-label') || btn.textContent;
      btn.setAttribute('data-copy-label', original);
      copyToClipboard(value).then(function () {
        btn.textContent = 'Copied \u2713';
        btn.classList.add('is-copied');
        setTimeout(function () { btn.textContent = original; btn.classList.remove('is-copied'); }, 1600);
      }).catch(function () {
        btn.textContent = 'Copy failed';
        setTimeout(function () { btn.textContent = original; }, 1600);
      });
    });
  }

  /* ---------- stack bundles ---------- */
  /* "Add stack to cart" buttons carry their bundle as JSON (slug + qty). The
     product details are resolved from the live catalogue so the cart always
     shows current names, prices and images, then every line is added with the
     quantity the kit calls for. */
  function bindStackButtons() {
    var root = document.querySelector('[data-stack-root]');
    if (!root) return;

    var imageBase = root.getAttribute('data-image-base') || (window.ARAIL_BASE || '/') + 'assets/img/';
    var productsUrl = (window.ARAIL_BASE || '/') + 'api/products.php?limit=200';
    var catalog = null;
    var pending = null;

    function loadCatalog() {
      if (catalog) return Promise.resolve(catalog);
      if (pending) return pending;
      pending = fetch(productsUrl)
        .then(function (r) { return r.json(); })
        .then(function (data) {
          catalog = (data && data.products) || [];
          return catalog;
        })
        .catch(function () {
          pending = null;
          return null;
        });
      return pending;
    }

    document.addEventListener('click', function (ev) {
      var btn = ev.target.closest ? ev.target.closest('[data-stack-add]') : null;
      if (!btn || btn.disabled) return;
      ev.preventDefault();

      var items = [];
      try { items = JSON.parse(btn.getAttribute('data-stack-items') || '[]') || []; } catch (e) { items = []; }
      if (!items.length) return;

      var original = btn.textContent;
      btn.disabled = true;

      loadCatalog().then(function (list) {
        var added = 0;
        if (list) {
          var bySlug = {};
          list.forEach(function (p) { bySlug[p.slug] = p; });
          items.forEach(function (row) {
            var p = bySlug[row.slug];
            if (!p) return;
            var price = p.sale_price != null && p.sale_price > 0 ? p.sale_price : p.price;
            addItem({
              slug: p.slug,
              name: p.name,
              price: price,
              image: imageBase + (p.image ? String(p.image).split('/').pop() : 'arail-logo-exact-v9.png')
            }, row.qty || 1);
            added += 1;
          });
        }

        if (added) {
          var code = btn.getAttribute('data-stack-coupon') || '';
          if (code) { setCoupon(code); syncCouponFields(); }
          btn.textContent = 'Added to cart ✓';
        } else {
          btn.textContent = 'Could not add — try again';
        }
        setTimeout(function () {
          btn.disabled = false;
          btn.textContent = original;
        }, 1800);
      });
    });
  }

  /* ---------- cart page ---------- */
  /* Markup mirrors the reference cart design: a bordered item list, then the
     coupon box, the total and the checkout button, all bottom-aligned. */
  function renderCartPage() {
    var root = document.querySelector('[data-cart-root]');
    if (!root) return;

    var shopUrl = root.getAttribute('data-shop-url') || '';
    var productUrl = root.getAttribute('data-product-url') || '';
    var checkoutUrl = root.getAttribute('data-checkout-url') || '';
    var shipping = num(root.getAttribute('data-shipping'));
    var min = num(root.getAttribute('data-min-order'));

    function lineTotal(item) {
      return num(item.price) * qtyOf(item);
    }

    /** "Add $X more to reach the $Y minimum order." - mirrors
        min_order_notice() in includes/helpers.php. */
    function shortfallNote(subtotal) {
      return 'Add ' + money(Math.round((min - subtotal) * 100) / 100)
        + ' more to reach the ' + money(min) + ' minimum order.';
    }

    /** Renders the checkout gate: a link once the minimum is met, otherwise the
        shortfall note plus a disabled button. Rebuilt on its own so editing a
        quantity never steals focus from the input being typed in. */
    function drawGate(items) {
      var gate = root.querySelector('[data-cart-gate]');
      if (!gate) return;
      var subtotal = cartTotal(items);
      if (min > 0 && Math.round(subtotal * 100) / 100 < min) {
        gate.innerHTML = '<p class="cart-min-note" data-cart-min>' + esc(shortfallNote(subtotal)) + '</p>'
          + '<button type="button" class="cart-gate__button bg-[var(--accent)] px-6 py-3 text-sm font-semibold text-white" disabled aria-disabled="true">Checkout</button>';
      } else {
        gate.innerHTML = '<a class="bg-[var(--accent)] px-6 py-3 text-sm font-semibold text-white hover:bg-[var(--accent-hover)]" href="' + esc(checkoutUrl) + '">Checkout</a>';
      }
    }

    function draw() {
      var items = getCart();
      if (!items.length) {
        root.innerHTML = '<p class="text-base text-[var(--muted-2)]">Your cart is empty. '
          + '<a class="underline text-[var(--accent)]" href="' + esc(shopUrl) + '">Browse the shop</a>.</p>';
        return;
      }
      var rows = items.map(function (i) {
        return '<li class="flex gap-4 py-4" data-row="' + esc(i.slug) + '">'
          + '<div class="h-24 w-20 shrink-0 overflow-hidden bg-[var(--surface-2)]">'
          + '<img alt="' + esc(i.name) + '" class="h-full w-full object-cover" loading="lazy" decoding="async" src="' + esc(i.image) + '">'
          + '</div>'
          + '<div class="flex flex-1 flex-col gap-2">'
          + '<div class="flex justify-between gap-4">'
          + '<a class="font-[family-name:var(--font-display)] text-lg" href="' + esc(productUrl + i.slug) + '/">' + esc(i.name) + '</a>'
          + '<span class="text-sm" data-line-total>' + money(lineTotal(i)) + '</span>'
          + '</div>'
          + '<div class="flex items-center gap-3 text-sm">'
          + '<label class="text-[var(--muted)]">Qty '
          + '<input min="0" max="99" class="ml-1 w-16 border border-[var(--line)] bg-white px-2 py-1" type="number" value="' + qtyOf(i) + '" data-qty="' + esc(i.slug) + '">'
          + '</label>'
          + '<button type="button" class="text-[var(--muted)] hover:text-red-700" data-remove="' + esc(i.slug) + '">Remove</button>'
          + '</div></div></li>';
      }).join('');

      root.innerHTML = '<ul class="divide-y divide-[var(--line)] border-y border-[var(--line)]">' + rows + '</ul>'
        + '<div class="flex flex-col items-end gap-4">'
        + couponFormHtml('coupon-cart', 'cart', getCoupon())
        + '<p class="text-lg">Total <strong data-cart-total>' + money(cartTotal(items) + shipping) + '</strong></p>'
        + '<div class="flex flex-col items-end gap-2" data-cart-gate></div>'
        + '</div>';
      syncCouponFields();
      drawGate(items);
    }

    /* Updates only what a quantity change affects, keeping the input focused. */
    function updateTotals(items) {
      var totalEl = root.querySelector('[data-cart-total]');
      if (totalEl) { totalEl.textContent = money(cartTotal(items) + shipping); }
      root.querySelectorAll('[data-row]').forEach(function (row) {
        var slug = row.getAttribute('data-row');
        var item = null;
        items.forEach(function (i) { if (i.slug === slug) { item = i; } });
        var lineEl = row.querySelector('[data-line-total]');
        if (item && lineEl) { lineEl.textContent = money(lineTotal(item)); }
      });
      drawGate(items);
    }

    root.addEventListener('input', function (ev) {
      var input = ev.target.closest ? ev.target.closest('[data-qty]') : null;
      if (!input || input.value.trim() === '') return;
      var slug = input.getAttribute('data-qty');
      var qty = parseInt(input.value, 10);
      if (!isFinite(qty)) return;
      if (qty <= 0) { removeItem(slug); draw(); return; }
      qty = Math.min(99, qty);
      if (String(qty) !== input.value.trim()) { input.value = qty; }
      var items = getCart();
      items.forEach(function (i) { if (i.slug === slug) { i.qty = qty; } });
      saveCart(items, true);
      updateTotals(items);
    });
    root.addEventListener('click', function (ev) {
      var btn = ev.target.closest ? ev.target.closest('[data-remove]') : null;
      if (!btn) return;
      removeItem(btn.getAttribute('data-remove'));
      draw();
    });
    document.addEventListener('cart:updated', draw);
    draw();
  }

  /* ---------- checkout page ---------- */
  function renderCheckout() {
    var form = document.querySelector('[data-checkout-form]');
    var summary = document.querySelector('[data-checkout-summary]');
    if (!form || !summary) return;

    var cartUrl = summary.getAttribute('data-cart-url') || '';
    var confirmUrl = summary.getAttribute('data-confirm-url') || '';
    var shipping = num(summary.getAttribute('data-shipping'));
    var min = num(summary.getAttribute('data-min-order'));
    var minNote = form.querySelector('[data-checkout-min]');

    /** Keeps the minimum-order notice and the submit button in step with the
        cart. Returns true while the order is too small to be placed. */
    function applyMin(subtotal) {
      var short = min > 0 && Math.round(subtotal * 100) / 100 < min;
      if (minNote) {
        minNote.textContent = short
          ? 'Add ' + money(Math.round((min - subtotal) * 100) / 100) + ' more to reach the ' + money(min) + ' minimum order.'
          : '';
        minNote.hidden = !short;
      }
      var submit = form.querySelector('.checkout-submit');
      if (submit) { submit.disabled = short; }
      return short;
    }

    function summaryItemHtml(item) {
      return '<li class="checkout-summary__item">'
        + '<div class="checkout-summary__item-image">'
        + '<img alt="' + esc(item.name) + '" loading="lazy" decoding="async" src="' + esc(item.image) + '">'
        + '<span class="checkout-summary__item-qty">' + qtyOf(item) + '</span>'
        + '</div>'
        + '<div class="checkout-summary__item-details">'
        + '<span class="checkout-summary__item-name">' + esc(item.name) + '</span>'
        + '<span class="checkout-summary__item-price">' + money(num(item.price) * qtyOf(item)) + '</span>'
        + '</div></li>';
    }

    function draw() {
      var items = getCart();
      if (!items.length) {
        window.location.href = cartUrl;
        return;
      }
      var subtotal = cartTotal(items);
      summary.innerHTML = '<h2 class="checkout-summary__title">Order summary</h2>'
        + '<ul class="checkout-summary__items">' + items.map(summaryItemHtml).join('') + '</ul>'
        + '<dl class="checkout-summary__totals">'
        + '<dt>Subtotal</dt><dd>' + money(subtotal) + '</dd>'
        + '<dt>Shipping</dt><dd>' + money(shipping) + '</dd>'
        + '<dt class="checkout-summary__total-label">Total</dt>'
        + '<dd class="checkout-summary__total-value">' + money(subtotal + shipping) + '</dd>'
        + '</dl>'
        + couponFormHtml('coupon-checkout', 'checkout', getCoupon());
      syncCouponFields();
      applyMin(subtotal);
      syncCoinPanel();
    }

    /* Add-to-cart for the upsell strip. addItem() fires cart:updated, which
       redraws the summary, so only the button itself is updated here. */
    function addUpsell(btn) {
      if (!btn || btn.disabled) return;
      addItem({
        slug: btn.getAttribute('data-slug'),
        name: btn.getAttribute('data-name'),
        price: btn.getAttribute('data-price'),
        image: btn.getAttribute('data-image')
      }, 1);
      btn.textContent = 'Added ✓';
      btn.disabled = true;
    }

    /** Reveals the wallet address for the selected payment option. */
    function syncCoinPanel() {
      var panel = form.querySelector('[data-payment-coin]');
      if (!panel) return;
      var radio = form.querySelector('.checkout-payment__radio:checked');
      var address = radio ? String(radio.getAttribute('data-coin-address') || '') : '';
      if (!radio || address === '') { panel.hidden = true; return; }
      panel.hidden = false;
      var image = panel.querySelector('[data-coin-image]');
      if (image) { image.src = radio.getAttribute('data-coin-mark') || ''; }
      setText(panel, '[data-coin-symbol-label]', radio.getAttribute('data-coin-symbol') || '');
      setText(panel, '[data-coin-network-label]', radio.getAttribute('data-coin-network') || '');
      setText(panel, '[data-coin-symbol-note]', radio.getAttribute('data-coin-symbol') || '');
      var code = panel.querySelector('[data-coin-address-label]');
      if (code) {
        code.textContent = address;
        code.setAttribute('data-copy-value', address);
      }
    }

    form.addEventListener('change', function (ev) {
      var radio = ev.target.closest ? ev.target.closest('.checkout-payment__radio') : null;
      if (!radio) return;
      form.querySelectorAll('.checkout-payment__card').forEach(function (card) {
        var input = card.querySelector('.checkout-payment__radio');
        card.classList.toggle('is-selected', !!input && input.checked);
      });
      syncCoinPanel();
    });

    form.addEventListener('click', function (ev) {
      var all = ev.target.closest ? ev.target.closest('[data-upsell-add-all]') : null;
      if (all) {
        form.querySelectorAll('[data-upsell-add]').forEach(addUpsell);
        return;
      }
      var single = ev.target.closest ? ev.target.closest('[data-upsell-add]') : null;
      if (single) { addUpsell(single); }
    });

    document.addEventListener('cart:updated', draw);
    draw();

    form.addEventListener('submit', function (ev) {
      ev.preventDefault();
      var btn = form.querySelector('.checkout-submit');
      var status = form.querySelector('[data-checkout-status]');
      // Below the store minimum the submit is disabled; re-check here as well
      // so a programmatic submit cannot slip a smaller order through.
      if (applyMin(cartTotal(getCart()))) { return; }
      var data = new FormData(form);
      var first = String(data.get('first_name') || '').trim();
      var last = String(data.get('last_name') || '').trim();
      var email = String(data.get('email') || '').trim();

      // Name the field that is missing instead of showing one vague message.
      var missing = [];
      if (first === '' || last === '') { missing.push('your first and last name'); }
      if (email === '') { missing.push('your email address'); }
      if (missing.length) {
        if (status) { status.textContent = 'Please enter ' + missing.join(' and ') + '.'; }
        return;
      }

      var payload = {
        customer: {
          first_name: first,
          last_name: last,
          email: email,
          phone: String(data.get('phone') || '').trim(),
          address: String(data.get('address') || '').trim(),
          city: String(data.get('city') || '').trim(),
          state: String(data.get('state') || '').trim(),
          postal_code: String(data.get('postal_code') || '').trim(),
          country: String(data.get('country') || '').trim()
        },
        payment_method: String(data.get('payment') || 'bitcoin'),
        coupon: getCoupon(),
        items: getCart().map(function (i) { return { slug: i.slug, qty: i.qty }; })
      };
      if (btn) { btn.disabled = true; }
      if (status) { status.textContent = 'Placing your order…'; }

      track('begin_checkout', { currency: 'USD', value: cartTotal(getCart()) });

      fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res && res.ok) {
            track('purchase', {
              transaction_id: res.order_number,
              currency: 'USD',
              value: res.total,
              items: getCart().map(function (i) { return { item_id: i.slug, item_name: i.name, quantity: i.qty }; })
            });
            localStorage.removeItem(CART_KEY);
            refreshBadges();
            window.location.href = confirmUrl + encodeURIComponent(res.order_number) + '/';
            return;
          }
          if (status) {
            status.textContent = (res && res.errors ? res.errors.join(' ') : (res && res.error) || 'Could not place the order. Please try again.');
          }
          applyMin(cartTotal(getCart()));
        })
        .catch(function () {
          if (status) { status.textContent = 'Network error — please try again.'; }
          applyMin(cartTotal(getCart()));
        });
    });
  }

  /* ---------- wishlist page ---------- */
  function renderWishlistPage() {
    var root = document.querySelector('[data-wishlist-root]');
    if (!root) return;
    var slugs = getWish();
    if (!slugs.length) {
      root.innerHTML = '<p class="text-base text-[var(--muted-2)]">Your wishlist is empty. '
        + '<a class="underline text-[var(--accent)]" href="' + root.getAttribute('data-shop-url') + '">Browse the shop</a>.</p>';
      return;
    }
    fetch((window.ARAIL_BASE || '/') + 'api/products.php?limit=200')
      .then(function (r) { return r.json(); })
      .then(function (data) {
        var products = (data && data.products) || [];
        var picked = products.filter(function (p) { return slugs.indexOf(p.slug) !== -1; });
        if (!picked.length) {
          root.innerHTML = '<p class="text-base text-[var(--muted-2)]">No saved products are available right now.</p>';
          return;
        }
        root.innerHTML = picked.map(function (p) {
          var price = p.sale_price != null && p.sale_price > 0 ? p.sale_price : p.price;
          return '<article class="product-card group"><div class="product-card-media-wrap">'
            + '<a class="product-card-media" href="' + root.getAttribute('data-product-url') + p.slug + '/">'
            + '<img alt="' + p.name + '" loading="lazy" width="600" height="600" src="' + root.getAttribute('data-image-base') + (p.image ? p.image.split('/').pop() : 'arail-logo-exact-v9.png') + '"></a>'
            + '<div class="product-card-hover-action"><div class="contents"><button type="button" class="product-card-add-btn" data-add-to-cart data-slug="' + p.slug + '" data-name="' + p.name + '" data-price="' + price.toFixed(2) + '" data-image="' + root.getAttribute('data-image-base') + (p.image ? p.image.split('/').pop() : 'arail-logo-exact-v9.png') + '"><span class="product-card-add-label">Add to cart</span></button></div></div></div>'
            + '<div class="product-card-body"><a href="' + root.getAttribute('data-product-url') + p.slug + '/"><h3 class="product-card-title">' + p.name + '</h3></a>'
            + '<p class="product-card-price">' + money(price) + '</p></div></article>';
        }).join('');
      })
      .catch(function () {
        root.innerHTML = '<p class="text-base text-[var(--muted-2)]">Could not load your wishlist right now.</p>';
      });
  }

  /* ---------- boot ---------- */
  function boot() {
    refreshBadges();
    refreshWishButtons();
    bindAddButtons();
    bindWishButtons();
    bindStackButtons();
    bindCopyButtons();
    bindCouponFields();
    renderCartPage();
    renderCheckout();
    renderWishlistPage();
    syncCouponFields();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
