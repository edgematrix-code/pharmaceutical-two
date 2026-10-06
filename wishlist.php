<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

seo_set([
    'title'       => seo_title('Your Wishlist | ' . store_name()),
    'description' => 'Products you have saved for later.',
    'canonical'   => 'wishlist/',
    'noindex'     => true,
    'robots'      => 'noindex,follow',
    'active_nav'  => '',
    'breadcrumbs' => [['name' => 'Home', 'url' => ''], ['name' => 'Wishlist', 'url' => 'wishlist/']],
    'json_ld'     => [],
]);

require __DIR__ . '/includes/layout/head.php';
?>
<main class="min-w-0 max-w-full overflow-x-clip pb-16 pt-[7.75rem] sm:pt-[8.75rem]">
  <div class="section-shell space-y-8">
    <?php require __DIR__ . '/includes/partials/breadcrumbs.php'; ?>
    <header class="space-y-3">
      <h1 class="text-[clamp(2rem,4.5vw,3rem)] font-bold tracking-[-0.01em] text-[var(--ink)]">Your wishlist</h1>
      <p class="text-base text-[var(--muted-2)]">Saved products stay in your browser until you clear them.</p>
    </header>

    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
         data-wishlist-root
         data-shop-url="<?= e(url_path('shop/')) ?>"
         data-product-url="<?= e(url_path('product/')) ?>"
         data-image-base="<?= e(asset('img/')) ?>">
      <p class="text-base text-[var(--muted-2)]">Loading your wishlist…</p>
    </div>
  </div>
</main>
<?php require __DIR__ . '/includes/layout/tail.php'; ?>
