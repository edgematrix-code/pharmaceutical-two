/**
 * reviews-carousel.js - wires up the review carousels.
 *
 * Every `.reviews-carousel` is a native horizontal scroller with scroll-snap
 * (see `.reviews-carousel__track` in theme.css). Without this controller the
 * arrows and dots render but do nothing, so the strip is only usable by
 * swiping. This keeps the three parts - track, arrows and dots - in sync in
 * both directions: clicking a control scrolls the track, and scrolling the
 * track updates the active dot and the disabled state of the arrows.
 *
 * The markup is produced by the templates, so nothing here adds or removes
 * elements; it only reflects state onto them.
 */
(function () {
  'use strict';

  function initCarousel(carousel) {
    var track = carousel.querySelector('.reviews-carousel__track');
    var dots = carousel.querySelectorAll('.reviews-carousel__dot');
    var arrows = carousel.querySelectorAll('.reviews-carousel__arrow');
    if (!track || !dots.length) return;

    var items = Array.prototype.slice.call(track.children);
    if (items.length < 2) return;

    var prev = arrows[0] || null;
    var next = arrows[1] || null;

    /* Scroll positions (relative to the first item) that each item snaps to. */
    function positions() {
      var base = items[0].offsetLeft;
      return items.map(function (item) { return Math.max(0, item.offsetLeft - base); });
    }

    function step() {
      var pos = positions();
      return pos.length > 1 && pos[1] > 0 ? pos[1] : track.clientWidth;
    }

    function maxScroll() {
      return Math.max(0, track.scrollWidth - track.clientWidth);
    }

    /**
     * Index of the item the track is showing. The last item usually cannot be
     * scrolled to the left edge (the track runs out of scrollable content
     * first), so reaching the end of the track selects the last dot.
     */
    function currentIndex() {
      var max = maxScroll();
      if (max <= 1) return 0;
      if (track.scrollLeft >= max - 2) return items.length - 1;
      var index = Math.round(track.scrollLeft / step());
      return Math.max(0, Math.min(items.length - 1, index));
    }

    function scrollToIndex(index) {
      if (index < 0 || index >= items.length) return;
      var target = Math.min(positions()[index], maxScroll());
      track.scrollTo({ left: target, behavior: 'smooth' });
    }

    function sync() {
      var index = currentIndex();
      for (var i = 0; i < dots.length; i++) {
        var active = i === index;
        dots[i].classList.toggle('is-active', active);
        if (active) { dots[i].setAttribute('aria-current', 'true'); }
        else { dots[i].removeAttribute('aria-current'); }
      }
      var max = maxScroll();
      if (prev) { prev.disabled = track.scrollLeft <= 1; }
      if (next) { next.disabled = max <= 1 || track.scrollLeft >= max - 1; }
    }

    for (var d = 0; d < dots.length; d++) {
      (function (index) {
        dots[index].addEventListener('click', function () { scrollToIndex(index); });
      })(d);
    }
    if (prev) { prev.addEventListener('click', function () { scrollToIndex(currentIndex() - 1); }); }
    if (next) { next.addEventListener('click', function () { scrollToIndex(currentIndex() + 1); }); }

    var ticking = false;
    track.addEventListener('scroll', function () {
      if (ticking) return;
      ticking = true;
      window.requestAnimationFrame(function () { ticking = false; sync(); });
    }, { passive: true });
    window.addEventListener('resize', sync);
    sync();
  }

  function boot() {
    document.querySelectorAll('.reviews-carousel').forEach(initCarousel);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
