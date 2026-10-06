/**
 * site-api.js - wires the storefront forms to the PHP API.
 * Forms opt in with data-api="subscribe | contact | review".
 * Search is handled server-side by /search/, so no JS is needed for it.
 */
(function () {
  'use strict';

  var BASE = window.ARAIL_BASE || '/';

  function statusEl(form, ok, text) {
    var el = form.querySelector('[data-api-status]');
    if (!el) {
      el = document.createElement('p');
      el.setAttribute('data-api-status', '');
      el.setAttribute('role', 'status');
      el.style.cssText = 'margin:.6rem 0 0;font-size:.9rem;line-height:1.4;';
      form.insertAdjacentElement('afterend', el);
    }
    el.textContent = text;
    el.style.color = ok ? '#0a7d38' : '#b3261e';
    return el;
  }

  function showMessage(form, data) {
    var text = data.message
      || (data.errors && data.errors.length ? data.errors.join(' ') : 'Unexpected server response.');
    statusEl(form, !!data.ok, text);
    return !!data.ok;
  }

  function resetErrors(form) {
    form.querySelectorAll('[aria-invalid]').forEach(function (el) { el.removeAttribute('aria-invalid'); });
  }

  function validate(form) {
    var invalid = false;
    form.querySelectorAll('input[required], textarea[required], select[required]').forEach(function (el) {
      if (!el.checkValidity()) {
        el.setAttribute('aria-invalid', 'true');
        invalid = true;
      }
    });
    return !invalid;
  }

  function handleSubscribe(form) {
    form.addEventListener('submit', function (ev) {
      ev.preventDefault();
      resetErrors(form);
      if (!validate(form)) return;
      var input = form.querySelector('input[type="email"], input[name="email"]');
      var btn = form.querySelector('[type="submit"]');
      if (btn) btn.disabled = true;
      var body = new URLSearchParams();
      body.set('email', input ? input.value : '');
      fetch(BASE + 'api/subscribe.php', { method: 'POST', body: body })
        .then(function (r) { return r.json(); })
        .then(function (data) { if (showMessage(form, data) && input) input.value = ''; })
        .catch(function () { statusEl(form, false, 'Network error — please try again.'); })
        .finally(function () { if (btn) btn.disabled = false; });
    });
  }

  function handleContact(form) {
    form.addEventListener('submit', function (ev) {
      ev.preventDefault();
      resetErrors(form);
      if (!validate(form)) return;
      var btn = form.querySelector('[type="submit"]');
      var original = btn ? btn.textContent : '';
      if (btn) { btn.disabled = true; btn.textContent = 'Sending…'; }
      fetch(BASE + 'api/contact.php', { method: 'POST', body: new FormData(form) })
        .then(function (r) { return r.json(); })
        .then(function (data) { if (showMessage(form, data)) form.reset(); })
        .catch(function () { statusEl(form, false, 'Network error — please try again.'); })
        .finally(function () { if (btn) { btn.disabled = false; btn.textContent = original; } });
    });
  }

  function handleReview(form) {
    form.addEventListener('submit', function (ev) {
      ev.preventDefault();
      resetErrors(form);
      if (!validate(form)) return;
      var btn = form.querySelector('[type="submit"]');
      var originalLabel = btn ? btn.textContent : '';
      if (btn) { btn.disabled = true; btn.textContent = 'Sending…'; }
      fetch(BASE + 'api/reviews.php', { method: 'POST', body: new FormData(form) })
        .then(function (r) { return r.json(); })
        .then(function (data) { if (showMessage(form, data)) form.reset(); })
        .catch(function () { statusEl(form, false, 'Network error — please try again.'); })
        .finally(function () { if (btn) { btn.disabled = false; btn.textContent = originalLabel; } });
    });
  }

  function boot() {
    document.querySelectorAll('form[data-api="subscribe"]').forEach(handleSubscribe);
    document.querySelectorAll('form[data-api="contact"]').forEach(handleContact);
    document.querySelectorAll('form[data-api="review"]').forEach(handleReview);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
