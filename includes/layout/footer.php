<?php
/**
 * Shared site footer. Single source of truth for every public page.
 */
$footer_featured = catalog_products(['featured' => true, 'limit' => 5]);
$footer_link = 'inline-flex min-h-10 items-center text-base font-medium uppercase tracking-[0.04em] text-[var(--ink)] transition-colors hover:text-[var(--accent)]';
$footer_head = 'text-base font-bold text-[var(--accent)]';
?>
<footer class="pb-10 pt-6">
  <div class="section-shell">
    <div class="surface-card px-6 py-10 sm:px-12 sm:py-12">
      <div class="footer-layout">
        <div class="footer-main">
          <a class="brand-logo brand-logo--footer brand-logo--light shrink-0" aria-label="<?= e(store_name()) ?>" href="<?= e(url_path('')) ?>">
            <img alt="<?= e(store_name()) ?>" loading="lazy" width="482" height="239" decoding="async" class="brand-logo__img" style="color:transparent" src="<?= e(asset('img/arail-logo-exact-v9.png')) ?>">
          </a>
          <div class="footer-link-groups">
            <div class="footer-link-group space-y-4">
              <h3 class="<?= e($footer_head) ?>">Shop</h3>
              <ul class="space-y-1">
                <li><a class="<?= e($footer_link) ?>" href="<?= e(url_path('shop/')) ?>">All Products</a></li>
                <?php foreach (catalog_categories() as $cat): ?>
                  <li><a class="<?= e($footer_link) ?>" href="<?= e(url_path('category/' . $cat['slug'] . '/')) ?>"><?= e($cat['name']) ?></a></li>
                <?php endforeach; ?>
                <li><a class="<?= e($footer_link) ?>" href="<?= e(url_path('stacks/')) ?>">Stacks</a></li>
              </ul>
            </div>
            <div class="footer-link-group space-y-4">
              <h3 class="<?= e($footer_head) ?>">Top Products</h3>
              <ul class="space-y-1">
                <?php foreach ($footer_featured as $fp): ?>
                  <li><a class="<?= e($footer_link) ?>" href="<?= e(url_path('product/' . $fp['slug'] . '/')) ?>"><?= e($fp['name']) ?></a></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <div class="footer-link-group space-y-4">
              <h3 class="<?= e($footer_head) ?>">Help</h3>
              <ul class="space-y-1">
                <li><a class="<?= e($footer_link) ?>" href="<?= e(url_path('dosing/')) ?>">Dosing</a></li>
                <li><a class="<?= e($footer_link) ?>" href="<?= e(url_path('testing/')) ?>">Lab Testing</a></li>
                <li><a class="<?= e($footer_link) ?>" href="<?= e(url_path('reviews/')) ?>">Reviews</a></li>
                <li><a class="<?= e($footer_link) ?>" href="<?= e(url_path('bitcoin/')) ?>">Buy Bitcoin</a></li>
                <li><a class="<?= e($footer_link) ?>" href="<?= e(url_path('contact/')) ?>">Contact</a></li>
              </ul>
            </div>
            <div class="footer-link-group space-y-4">
              <h3 class="<?= e($footer_head) ?>">Company</h3>
              <ul class="space-y-1">
                <li><a class="<?= e($footer_link) ?>" href="<?= e(url_path('about/')) ?>">About Us</a></li>
                <li><a class="<?= e($footer_link) ?>" href="<?= e(url_path('shipping/')) ?>">Shipping</a></li>
                <li><a class="<?= e($footer_link) ?>" href="<?= e(url_path('returns/')) ?>">Returns</a></li>
                <li><a class="<?= e($footer_link) ?>" href="<?= e(url_path('privacy/')) ?>">Privacy</a></li>
                <li><a class="<?= e($footer_link) ?>" href="<?= e(url_path('terms/')) ?>">Terms</a></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="footer-newsletter space-y-4">
          <h2 class="text-2xl font-bold capitalize text-[var(--accent)]">Get 5% off your first purchase</h2>
          <p class="text-lg leading-[1.5] text-[var(--ink)]">Subscribe to get updates on new peptides, research and sales.</p>
          <form class="flex w-full min-w-0 flex-col gap-3 sm:flex-row" action="<?= e(url_path('api/subscribe.php')) ?>" method="post" data-api="subscribe">
            <input type="email" required placeholder="Email" class="h-11 w-full min-w-0 flex-1 rounded-[12px] border border-[var(--line)] px-4 text-sm outline-none focus:border-[var(--accent)]" name="email">
            <button type="submit" class="btn-primary shrink-0 !min-h-11 !px-5 !text-sm">Subscribe</button>
          </form>
          <p class="text-sm text-[var(--muted)]">By subscribing you agree to our <a href="<?= e(url_path('privacy/')) ?>" class="underline-offset-2 hover:text-[var(--accent)] hover:underline">Privacy Policy</a>.</p>
        </div>
      </div>
      <div class="mt-10 h-px w-full bg-[var(--line)]"></div>
      <div class="mt-6 flex flex-col gap-2 text-sm text-[var(--ink)]/50 sm:flex-row sm:items-center sm:justify-between">
        <p>&copy; <?= date('Y') ?> <?= e(store_name()) ?>. All rights reserved.</p>
        <p>For laboratory research use only. Not for human consumption.</p>
      </div>
    </div>
  </div>
</footer>
