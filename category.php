<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$slug     = trim((string)($_GET['slug'] ?? $_GET['category'] ?? ''));
$category = $slug !== '' ? catalog_category($slug) : null;

if (!$category) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$sort     = trim((string)($_GET['sort'] ?? ''));
$allowed  = ['price-asc', 'price-desc', 'name', 'newest'];
$sort     = in_array($sort, $allowed, true) ? $sort : '';
$per_page = 24;
$page     = max(1, (int)($_GET['page'] ?? 1));
$offset   = ($page - 1) * $per_page;

$opts  = ['category' => $slug, 'limit' => $per_page, 'offset' => $offset, 'sort' => $sort];
$total = catalog_count($opts);
$pages = max(1, (int)ceil($total / $per_page));
$items = catalog_products($opts);
$cats  = catalog_categories();

$cat_name  = (string)$category['name'];
$canonical = 'category/' . $slug . '/';
$is_filtered = $sort !== '';
$intro = trim((string)($category['description'] ?? ''));
if ($intro === '') {
    $intro = 'Shop ' . $cat_name . ' from ' . store_name() . '. Every batch is independently lab tested for purity and potency.';
}

$title = (string)($category['meta_title'] ?? '');
if ($title === '') {
    $title = $cat_name . ' - Buy Online | ' . store_name();
}
if ($page > 1) {
    $title .= ' - Page ' . $page;
}
$meta_desc = (string)($category['meta_description'] ?? '') ?: seo_text($intro . ' Discreet worldwide shipping from ' . store_name() . '.', 158);

$crumbs = [
    ['name' => 'Home', 'url' => ''],
    ['name' => 'Shop', 'url' => 'shop/'],
    ['name' => $cat_name, 'url' => $canonical],
];

seo_set([
    'title'       => seo_title($title),
    'description' => seo_text($meta_desc),
    'canonical'   => $canonical,
    'noindex'     => $is_filtered,
    'robots'      => $is_filtered ? 'noindex,follow' : 'index,follow',
    'active_nav'  => 'shop',
    'breadcrumbs' => $crumbs,
    'json_ld'     => $is_filtered ? [] : [
        ld_collection($cat_name, $intro, $canonical, $items),
        ld_breadcrumbs($crumbs),
        ld_organization(),
    ],
]);

$page_url = static function (int $p) use ($slug, $sort): string {
    $params = array_filter(['sort' => $sort, 'page' => $p > 1 ? $p : null]);
    return url_path('category/' . $slug . '/') . ($params ? '?' . http_build_query($params) : '');
};

require __DIR__ . '/includes/layout/head.php';
?>
<main class="min-w-0 max-w-full overflow-x-clip pb-16 pt-[7.75rem] sm:pt-[8.75rem]">
  <div class="section-shell space-y-8">
    <?php require __DIR__ . '/includes/partials/breadcrumbs.php'; ?>

    <header class="space-y-3">
      <h1 class="text-[clamp(2rem,4.5vw,3.25rem)] font-bold tracking-[-0.01em] text-[var(--ink)]"><?= e($cat_name) ?></h1>
      <p class="max-w-2xl text-base font-light text-[var(--muted-2)]"><?= e($intro) ?></p>
    </header>

    <div class="shop-category-filters flex flex-wrap gap-2">
      <a class="shop-category-chip shrink-0 rounded-[12px] border border-[var(--line)] px-4 py-2 text-sm font-semibold uppercase tracking-[0.04em]" href="<?= e(url_path('shop/')) ?>">All</a>
      <?php foreach ($cats as $cat): ?>
        <?php $is_current = $cat['slug'] === $slug; ?>
        <a class="shop-category-chip shrink-0 rounded-[12px] border px-4 py-2 text-sm font-semibold capitalize <?= $is_current ? 'border-[#4a4b50] bg-[rgba(74,75,80,0.03)]' : 'border-[var(--line)]' ?>" href="<?= e(url_path('category/' . $cat['slug'] . '/')) ?>"><?= e($cat['name']) ?> <span class="text-[var(--accent)]"><?= (int)$cat['product_count'] ?></span></a>
      <?php endforeach; ?>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3">
      <p class="text-sm text-[var(--muted)]"><?= (int)$total ?> product<?= $total === 1 ? '' : 's' ?> in <?= e($cat_name) ?></p>
      <form method="get" action="" class="flex items-center gap-2 text-sm">
        <label for="sort" class="text-[var(--muted)]">Sort</label>
        <select id="sort" name="sort" onchange="this.form.submit()" class="h-11 rounded-[12px] border border-[var(--line)] bg-white px-3 text-sm outline-none focus:border-[var(--accent)]">
          <option value="" <?= $sort === '' ? 'selected' : '' ?>>Featured</option>
          <option value="price-asc" <?= $sort === 'price-asc' ? 'selected' : '' ?>>Price: low to high</option>
          <option value="price-desc" <?= $sort === 'price-desc' ? 'selected' : '' ?>>Price: high to low</option>
          <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name</option>
          <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
        </select>
      </form>
    </div>

    <?php if ($items): ?>
      <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <?php foreach ($items as $card): ?>
          <?php $card_heading = 'h2'; require __DIR__ . '/includes/partials/product-card.php'; ?>
        <?php endforeach; ?>
      </div>
      <?php require __DIR__ . '/includes/partials/pagination.php'; ?>
    <?php else: ?>
      <p class="text-base text-[var(--muted-2)]">No products are currently listed in this category. <a class="underline" href="<?= e(url_path('shop/')) ?>">Browse the full shop</a>.</p>
    <?php endif; ?>

    <section class="surface-card space-y-4 px-6 py-8">
      <h2 class="text-2xl font-bold text-[var(--accent)]">Buying <?= e($cat_name) ?> online</h2>
      <p class="max-w-3xl text-base leading-relaxed text-[var(--muted-2)]">
        <?= e(store_name()) ?> ships <?= e(strtolower($cat_name)) ?> worldwide with discreet packaging and tracking on
        every order. Use the sort control to compare prices, and open any product for its specification, stock status,
        lab testing information and related products.
      </p>
      <div class="flex flex-wrap gap-2 pt-1 text-sm">
        <?php foreach ($cats as $cat): ?>
          <?php if ($cat['slug'] !== $slug): ?>
            <a class="underline text-[var(--accent)]" href="<?= e(url_path('category/' . $cat['slug'] . '/')) ?>"><?= e($cat['name']) ?></a>
          <?php endif; ?>
        <?php endforeach; ?>
        <a class="underline text-[var(--accent)]" href="<?= e(url_path('stacks/')) ?>">Recommended stacks</a>
        <a class="underline text-[var(--accent)]" href="<?= e(url_path('dosing/')) ?>">Dosing guide</a>
      </div>
      <p class="max-w-3xl rounded-[12px] border border-dashed border-[var(--line)] px-4 py-3 text-sm text-[var(--muted)]">
        TODO (store owner): replace this block with 150-300 words of original category copy (uses, comparisons, safety)
        to strengthen this landing page. Placeholder kept intentionally.
      </p>
    </section>
  </div>
</main>
<?php require __DIR__ . '/includes/layout/tail.php'; ?>
