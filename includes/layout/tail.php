<?php
/**
 * Shared closing markup: footer, scripts, analytics.
 */
$app = arail_config()['app'];
?>
<?php require __DIR__ . '/footer.php'; ?>
  </div><!-- /#arail-storefront -->
  <script>window.ARAIL_BASE = <?= json_encode(url_path(''), JSON_UNESCAPED_SLASHES) ?>;</script>
  <script src="<?= e(asset('js/site-nav.js')) ?>" defer></script>
  <script src="<?= e(asset('js/faq.js')) ?>" defer></script>
  <script src="<?= e(asset('js/cart.js')) ?>" defer></script>
  <script src="<?= e(asset('js/reviews-carousel.js')) ?>" defer></script>
  <script src="<?= e(asset('js/site-api.js')) ?>" defer></script>
<?php if (!empty($app['ga4_id'])): ?>
  <script>
    /* GA4 - loaded asynchronously; extend with consent gating if required. */
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    window.gtag = gtag;
    gtag('js', new Date());
    gtag('config', <?= json_encode((string)$app['ga4_id'], JSON_UNESCAPED_SLASHES) ?>, { send_page_view: true });
    window.addEventListener('load', function () {
      var s = document.createElement('script');
      s.async = true;
      s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(<?= json_encode((string)$app['ga4_id'], JSON_UNESCAPED_SLASHES) ?>);
      document.head.appendChild(s);
    });
  </script>
<?php endif; ?>
</body>
</html>
