/* Arail static copy - navbar behaviour.
   All dropdowns start closed; they open only when their own trigger is clicked.
   - Help button  -> toggles the Help dropdown
   - menu button  -> toggles the mobile menu
   - mobile Help accordion -> toggles its submenu
   Clicking outside or pressing Escape closes everything. */
(function () {
  'use strict';

  var PANEL_OPEN_CLASSES = ['visible', 'translate-y-0', 'opacity-100'];
  var PANEL_CLOSED_CLASSES = ['invisible', 'pointer-events-none', '-translate-y-1.5', 'opacity-0'];

  function panelFor(trigger) {
    var id = trigger.getAttribute('aria-controls');
    return id ? document.getElementById(id) : null;
  }

  function setDropdown(trigger, open) {
    trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
    var panel = panelFor(trigger);
    if (!panel) return;
    panel.setAttribute('aria-hidden', open ? 'false' : 'true');
    PANEL_OPEN_CLASSES.forEach(function (name) { panel.classList.toggle(name, open); });
    PANEL_CLOSED_CLASSES.forEach(function (name) { panel.classList.toggle(name, !open); });
  }

  function setAccordion(trigger, open) {
    trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
    var panel = panelFor(trigger);
    if (panel) {
      panel.classList.toggle('hidden', !open);
      if (open) {
        panel.removeAttribute('hidden');
      } else {
        panel.setAttribute('hidden', '');
      }
    }
    var chevron = trigger.querySelector('svg');
    if (chevron) chevron.classList.toggle('rotate-180', open);
  }

  var helpTriggers = [].slice.call(document.querySelectorAll('[data-help-nav-trigger]'));
  var accordionTriggers = [].slice.call(document.querySelectorAll('button[aria-controls="site-header-mobile-help"]'));
  var menuTriggers = [].slice.call(document.querySelectorAll('button[aria-controls="site-header-mobile-menu"]'));

  function isOpen(trigger) {
    return trigger.getAttribute('aria-expanded') === 'true';
  }

  function allTriggers() {
    return helpTriggers.concat(accordionTriggers, menuTriggers);
  }

  function closeAll(except) {
    helpTriggers.concat(menuTriggers).forEach(function (trigger) {
      if (trigger !== except) setDropdown(trigger, false);
    });
    accordionTriggers.forEach(function (trigger) {
      if (trigger !== except) setAccordion(trigger, false);
    });
  }

  helpTriggers.concat(menuTriggers).forEach(function (trigger) {
    trigger.addEventListener('click', function (event) {
      event.preventDefault();
      var open = !isOpen(trigger);
      closeAll(trigger);
      setDropdown(trigger, open);
      if (menuTriggers.indexOf(trigger) !== -1) {
        trigger.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
      }
    });
  });

  accordionTriggers.forEach(function (trigger) {
    trigger.addEventListener('click', function (event) {
      event.preventDefault();
      setAccordion(trigger, !isOpen(trigger));
    });
  });

  document.addEventListener('click', function (event) {
    var onTrigger = allTriggers().some(function (trigger) {
      return trigger.contains(event.target);
    });
    if (onTrigger) return;
    var insidePanel = allTriggers().some(function (trigger) {
      var panel = panelFor(trigger);
      return panel ? panel.contains(event.target) : false;
    });
    if (!insidePanel) closeAll(null);
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' || event.key === 'Esc') closeAll(null);
  });
})();
