<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

seo_set([
    'title'       => seo_title('Your Cart | ' . store_name()),
    'description' => 'Review the items in your cart and continue to checkout.',
    'canonical'   => 'cart/',
    'noindex'     => true,       // transactional pages are never indexed
    'robots'      => 'noindex,nofollow',
    'active_nav'  => '',
    'json_ld'     => [],
]);

require __DIR__ . '/includes/layout/head.php';
?>
<main class="min-w-0 max-w-full overflow-x-clip pb-16 pt-[7.75rem] sm:pt-[8.75rem]">
  <div class="section-shell">
    <div class="mx-auto max-w-3xl space-y-8">
      <h1 class="text-[clamp(2rem,4vw,3rem)] font-bold tracking-[-0.01em]">Your cart</h1>
      <div class="surface-card p-5 sm:p-8">
        <div class="space-y-8"
             data-cart-root
             data-shop-url="<?= e(url_path('shop/')) ?>"
             data-product-url="<?= e(url_path('product/')) ?>"
             data-checkout-url="<?= e(url_path('checkout/')) ?>"
             data-shipping="<?= e(number_format((float)arail_config()['app']['shipping_flat'], 2, '.', '')) ?>"
             data-min-order="<?= e(number_format(min_order_value(), 2, '.', '')) ?>">
          <p class="text-base text-[var(--muted-2)]">Loading your cart…</p>
        </div>
      </div>
    </div>
  </div>
</main>
<?php require __DIR__ . '/includes/layout/tail.php'; ?>
