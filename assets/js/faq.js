/**
 * faq.js - accessible FAQ accordion.
 *
 * Each item is marked up as:
 *   <div data-faq>
 *     <button data-faq-trigger aria-expanded="…" aria-controls="faq-1">…<span data-faq-icon>+</span></button>
 *     <div id="faq-1" data-faq-panel>answer</div>
 *   </div>
 *
 * Without JavaScript every panel is still in the HTML, so the answers remain
 * available to crawlers and to users with JS disabled (the first is expanded,
 * the rest are toggled by this script).
 */
(function () {
  'use strict';

  function setOpen(item, trigger, panel, icon, open) {
    trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (open) {
      panel.removeAttribute('hidden');
    } else {
      panel.setAttribute('hidden', '');
    }
    if (icon) {
      icon.classList.toggle('rotate-45', open);
    }
    item.setAttribute('data-faq-open', open ? 'true' : 'false');
  }

  function init() {
    var items = document.querySelectorAll('[data-faq]');
    if (!items.length) {
      return;
    }

    Array.prototype.forEach.call(items, function (item) {
      var trigger = item.querySelector('[data-faq-trigger]');
      var panel   = item.querySelector('[data-faq-panel]');
      var icon    = item.querySelector('[data-faq-icon]');
      if (!trigger || !panel) {
        return;
      }

      // Sync the DOM with the server-rendered state.
      var open = trigger.getAttribute('aria-expanded') === 'true';
      setOpen(item, trigger, panel, icon, open);

      trigger.addEventListener('click', function () {
        setOpen(item, trigger, panel, icon, trigger.getAttribute('aria-expanded') !== 'true');
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
