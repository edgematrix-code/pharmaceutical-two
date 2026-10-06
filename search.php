<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$q        = trim((string)($_GET['q'] ?? ''));
$per_page = 24;
$page     = max(1, (int)($_GET['page'] ?? 1));
$offset   = ($page - 1) * $per_page;

$opts  = ['q' => $q, 'limit' => $per_page, 'offset' => $offset];
$total = $q !== '' ? catalog_count($opts) : 0;
$pages = max(1, (int)ceil($total / $per_page));
$items = $q !== '' ? catalog_products($opts) : [];
$cats  = catalog_categories();

seo_set([
    'title'       => seo_title(($q !== '' ? 'Search: ' . $q : 'Search') . ' | ' . store_name()),
    'description' => seo_text('Search the ' . store_name() . ' catalogue.'),
    'canonical'   => 'search/',
    'noindex'     => true,   // internal search results are never indexed
    'robots'      => 'noindex,follow',
    'active_nav'  => 'shop',
    'breadcrumbs' => [['name' => 'Home', 'url' => ''], ['name' => 'Search', 'url' => 'search/']],
    'json_ld'     => [],
]);

$page_url = static function (int $p) use ($q): string {
    $params = array_filter(['q' => $q, 'page' => $p > 1 ? $p : null]);
    return url_path('search/') . ($params ? '?' . http_build_query($params) : '');
};

require __DIR__ . '/includes/layout/head.php';
?>
<main class="min-w-0 max-w-full overflow-x-clip pb-16 pt-[7.75rem] sm:pt-[8.75rem]">
  <div class="section-shell space-y-8">
    <?php $crumbs = [['name' => 'Home', 'url' => ''], ['name' => 'Search', 'url' => 'search/']]; require __DIR__ . '/includes/partials/breadcrumbs.php'; ?>

    <header class="space-y-3">
      <h1 class="text-[clamp(2rem,4.5vw,3rem)] font-bold tracking-[-0.01em] text-[var(--ink)]">
        <?= $q !== '' ? 'Search results' : 'Search the shop' ?>
      </h1>
      <?php if ($q !== ''): ?>
        <p class="text-base text-[var(--muted-2)]"><?= (int)$total ?> result<?= $total === 1 ? '' : 's' ?> for “<?= e($q) ?>”.</p>
      <?php endif; ?>
    </header>

    <form class="flex max-w-md gap-2" role="search" action="<?= e(url_path('search/')) ?>" method="get">
      <input placeholder="Search products" name="q" value="<?= e($q) ?>" class="h-12 flex-1 rounded-[12px] border border-[var(--line)] bg-white px-4 text-sm outline-none focus:border-[var(--accent)]">
      <button type="submit" class="btn-primary !min-h-12 !px-5 !text-sm">Search</button>
    </form>

    <?php if ($items): ?>
      <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <?php foreach ($items as $card): ?>
          <?php $card_heading = 'h3'; require __DIR__ . '/includes/partials/product-card.php'; ?>
        <?php endforeach; ?>
      </div>
      <?php require __DIR__ . '/includes/partials/pagination.php'; ?>
    <?php elseif ($q !== ''): ?>
      <p class="text-base text-[var(--muted-2)]">Nothing matched “<?= e($q) ?>”. Try a shorter product name such as “test”, “deca” or “hgh”.</p>
    <?php endif; ?>

    <section class="surface-card space-y-3 px-6 py-8">
      <h2 class="text-xl font-bold text-[var(--accent)]">Popular categories</h2>
      <div class="flex flex-wrap gap-2">
        <?php foreach ($cats as $cat): ?>
          <a class="underline text-[var(--accent)]" href="<?= e(url_path('category/' . $cat['slug'] . '/')) ?>"><?= e($cat['name']) ?> (<?= (int)$cat['product_count'] ?>)</a>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
</main>
<?php require __DIR__ . '/includes/layout/tail.php'; ?>
