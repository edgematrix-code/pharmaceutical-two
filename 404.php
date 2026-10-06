<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if (!headers_sent()) {
    http_response_code(404);
}

$cats    = catalog_categories();
$popular = catalog_products(['featured' => true, 'limit' => 8]);

seo_set([
    'title'       => seo_title('Page not found (404) | ' . store_name()),
    'description' => 'The page you were looking for could not be found. Browse our categories or search the shop instead.',
    'canonical'   => '404/',
    'noindex'     => true,
    'robots'      => 'noindex,follow',
    'active_nav'  => '',
    'json_ld'     => [],
]);

require __DIR__ . '/includes/layout/head.php';
?>
<main class="min-w-0 max-w-full overflow-x-clip pb-16 pt-[7.75rem] sm:pt-[8.75rem]">
  <div class="section-shell space-y-8">
    <header class="space-y-3">
      <p class="text-sm font-semibold uppercase tracking-[0.08em] text-[var(--accent)]">Error 404</p>
      <h1 class="text-[clamp(2rem,4.5vw,3rem)] font-bold tracking-[-0.01em] text-[var(--ink)]">We couldn’t find that page</h1>
      <p class="max-w-xl text-base text-[var(--muted-2)]">The link may be broken or the product may have been renamed. Try a search or start from one of the sections below.</p>
    </header>

    <form class="flex max-w-md gap-2" role="search" action="<?= e(url_path('search/')) ?>" method="get">
      <input placeholder="Search products" name="q" class="h-12 flex-1 rounded-[12px] border border-[var(--line)] bg-white px-4 text-sm outline-none focus:border-[var(--accent)]">
      <button type="submit" class="btn-primary !min-h-12 !px-5 !text-sm">Search</button>
    </form>

    <section class="space-y-3">
      <h2 class="text-xl font-bold text-[var(--accent)]">Popular categories</h2>
      <div class="flex flex-wrap gap-3">
        <?php foreach ($cats as $cat): ?>
          <a class="underline text-[var(--accent)]" href="<?= e(url_path('category/' . $cat['slug'] . '/')) ?>"><?= e($cat['name']) ?> (<?= (int)$cat['product_count'] ?>)</a>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="space-y-4">
      <h2 class="text-xl font-bold text-[var(--accent)]">Bestsellers</h2>
      <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <?php foreach ($popular as $card): ?>
          <?php $card_heading = 'h3'; require __DIR__ . '/includes/partials/product-card.php'; ?>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
</main>
<?php require __DIR__ . '/includes/layout/tail.php'; ?>
