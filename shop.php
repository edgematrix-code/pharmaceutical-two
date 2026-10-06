<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$q        = trim((string)($_GET['q'] ?? ''));
$sort     = trim((string)($_GET['sort'] ?? ''));
$allowed  = ['price-asc', 'price-desc', 'name', 'newest'];
$sort     = in_array($sort, $allowed, true) ? $sort : '';
$per_page = 24;
$page     = max(1, (int)($_GET['page'] ?? 1));
$offset   = ($page - 1) * $per_page;

$opts   = ['limit' => $per_page, 'offset' => $offset, 'q' => $q, 'sort' => $sort];
$total  = catalog_count($opts);
$pages  = max(1, (int)ceil($total / $per_page));
$items  = catalog_products($opts);
$cats   = catalog_categories();

$is_filtered = $q !== '' || $sort !== '';
$canonical   = 'shop/';

seo_set([
    'title'       => $page > 1 ? seo_title('Shop All Products - Page ' . $page . ' | ' . store_name()) : seo_title('Shop All Products - Buy Online | ' . store_name()),
    'description' => seo_text('Browse the full ' . store_name() . ' catalogue of independently lab-tested anabolics, peptides and HGH. Discreet worldwide shipping.'),
    'canonical'   => $canonical,
    'noindex'     => $is_filtered,
    'robots'      => $is_filtered ? 'noindex,follow' : 'index,follow',
    'active_nav'  => 'shop',
    'breadcrumbs' => [['name' => 'Home', 'url' => ''], ['name' => 'Shop', 'url' => 'shop/']],
    'json_ld'     => $is_filtered ? [] : [
        ld_collection('Shop All Products', 'Full catalogue of lab-tested products.', $canonical, $items),
        ld_breadcrumbs([['name' => 'Home', 'url' => ''], ['name' => 'Shop', 'url' => 'shop/']]),
        ld_organization(),
    ],
]);

$page_url = static function (int $p) use ($q, $sort): string {
    $params = array_filter(['q' => $q, 'sort' => $sort, 'page' => $p > 1 ? $p : null]);
    return url_path('shop/') . ($params ? '?' . http_build_query($params) : '');
};

require __DIR__ . '/includes/layout/head.php';
?>
<main class="min-w-0 max-w-full overflow-x-clip pb-16 pt-[7.75rem] sm:pt-[8.75rem]">
  <div class="section-shell space-y-8">
    <?php $crumbs = [['name' => 'Home', 'url' => ''], ['name' => 'Shop', 'url' => 'shop/']]; require __DIR__ . '/includes/partials/breadcrumbs.php'; ?>

    <header class="space-y-3">
      <h1 class="text-[clamp(2.25rem,5vw,3.75rem)] font-bold tracking-[-0.01em] text-[var(--ink)]">Shop</h1>
      <p class="max-w-xl text-base font-light text-[var(--muted-2)]">Browse the full <?= e(store_name()) ?> catalogue. All products are independently lab tested.</p>
    </header>

    <form class="flex max-w-md gap-2" role="search" action="<?= e(url_path('search/')) ?>" method="get">
      <input placeholder="Search products" name="q" value="<?= e($q) ?>" class="h-12 flex-1 rounded-[12px] border border-[var(--line)] bg-white px-4 text-sm outline-none focus:border-[var(--accent)]">
      <button type="submit" class="btn-primary !min-h-12 !px-5 !text-sm">Search</button>
    </form>

    <div class="shop-category-filters flex flex-wrap gap-2">
      <a class="shop-category-chip shrink-0 rounded-[12px] border px-4 py-2 text-sm font-semibold uppercase tracking-[0.04em] <?= $q === '' ? 'border-[#4a4b50] bg-[rgba(74,75,80,0.03)]' : 'border-[var(--line)]' ?>" href="<?= e(url_path('shop/')) ?>">All</a>
      <?php foreach ($cats as $cat): ?>
        <a class="shop-category-chip shrink-0 rounded-[12px] border border-[var(--line)] px-4 py-2 text-sm font-semibold capitalize" href="<?= e(url_path('category/' . $cat['slug'] . '/')) ?>"><?= e($cat['name']) ?> <span class="text-[var(--accent)]"><?= (int)$cat['product_count'] ?></span></a>
      <?php endforeach; ?>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3">
      <p class="text-sm text-[var(--muted)]"><?= (int)$total ?> product<?= $total === 1 ? '' : 's' ?><?= $q !== '' ? ' matching “' . e($q) . '”' : '' ?></p>
      <form method="get" action="" class="flex items-center gap-2 text-sm">
        <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?= e($q) ?>"><?php endif; ?>
        <label for="sort" class="text-[var(--muted)]">Sort</label>
        <select id="sort" name="sort" onchange="this.form.submit()" class="h-11 rounded-[12px] border border-[var(--line)] bg-white px-3 text-sm outline-none focus:border-[var(--accent)]">
          <option value="" <?= $sort === '' ? 'selected' : '' ?>>Featured</option>
          <option value="price-asc" <?= $sort === 'price-asc' ? 'selected' : '' ?>>Price: low to high</option>
          <option value="price-desc" <?= $sort === 'price-desc' ? 'selected' : '' ?>>Price: high to low</option>
          <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name</option>
          <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
        </select>
        <noscript><button class="btn-secondary !min-h-11 !px-4 !text-sm" type="submit">Apply</button></noscript>
      </form>
    </div>

    <?php if ($items): ?>
      <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <?php foreach ($items as $card): ?>
          <?php $card_heading = 'h2'; require __DIR__ . '/includes/partials/product-card.php'; ?>
        <?php endforeach; ?>
      </div>
      <?php
      $page_url_pag = $page_url;
      $page_url = $page_url_pag;
      require __DIR__ . '/includes/partials/pagination.php';
      ?>
    <?php else: ?>
      <p class="text-base text-[var(--muted-2)]">No products matched your search. <a class="underline" href="<?= e(url_path('shop/')) ?>">Clear filters</a>.</p>
    <?php endif; ?>

    <section class="surface-card mt-10 px-6 py-8">
      <h2 class="text-2xl font-bold text-[var(--accent)]">About the <?= e(store_name()) ?> shop</h2>
      <p class="mt-3 max-w-3xl text-base leading-relaxed text-[var(--muted-2)]">
        We supply anabolics, peptides and growth hormone from our own production line, with independent third-party
        laboratory testing on every batch. Use the category filters above to browse injectable anabolics, oral anabolics
        and peptides &amp; HGH, or search by product name. Prices are shown in <?= e(currency_code()) ?> and stock levels
        are updated from our live inventory.
      </p>
      <p class="mt-4 max-w-3xl rounded-[12px] border border-dashed border-[var(--line)] px-4 py-3 text-sm text-[var(--muted)]">
        TODO (store owner): expand this shop introduction with your own 150-300 word copy targeting the keywords you want
        to rank for. Placeholder kept intentionally - no keyword stuffing or invented claims.
      </p>
    </section>
  </div>
</main>
<?php require __DIR__ . '/includes/layout/tail.php'; ?>
