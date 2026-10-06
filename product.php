<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$slug    = trim((string)($_GET['slug'] ?? $_GET['product'] ?? ''));
$product = $slug !== '' ? catalog_product($slug) : null;

if (!$product || (int)$product['is_active'] !== 1) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$reviews  = catalog_reviews((int)$product['id']);
$rating   = catalog_rating((int)$product['id']);
$related  = catalog_related($product, 4);
$in_stock = product_in_stock($product);
$price    = product_price($product);
$has_sale = $product['sale_price'] !== null && (float)$product['sale_price'] > 0
    && (float)$product['sale_price'] < (float)$product['price'];
$canonical = 'product/' . $product['slug'] . '/';

$summary = (string)($product['short_description'] ?? '') !== ''
    ? (string)$product['short_description']
    : (string)($product['description'] ?? '');

$title = (string)($product['meta_title'] ?? '');
if ($title === '') {
    $attr  = (string)($product['category_name'] ?: 'Buy Online');
    $title = $product['name'] . ' - ' . $attr . ' | ' . store_name();
}
$description = (string)($product['meta_description'] ?? '');
if ($description === '') {
    $description = seo_text($summary . ' Buy ' . $product['name'] . ' online at ' . format_money($price) . '. Independently lab tested, discreet worldwide shipping.', 158);
}

$crumbs = [
    ['name' => 'Home', 'url' => ''],
    ['name' => 'Shop', 'url' => 'shop/'],
];
if (!empty($product['category_slug'])) {
    $crumbs[] = ['name' => (string)$product['category_name'], 'url' => 'category/' . $product['category_slug'] . '/'];
}
$crumbs[] = ['name' => (string)$product['name'], 'url' => $canonical];

$og = [
    'product:price:amount'   => number_format($price, 2, '.', ''),
    'product:price:currency' => currency_code(),
    'product:availability'   => $in_stock ? 'in stock' : 'out of stock',
];

seo_set([
    'title'        => seo_title($title),
    'description'  => seo_text($description),
    'canonical'    => $canonical,
    'og_type'      => 'product',
    'og_title'     => $product['name'] . ' | ' . store_name(),
    'og_description' => seo_text($description),
    'og_image'     => image_url($product['image'] ?? ''),
    'og_image_alt' => (string)$product['name'],
    'og'           => $og,
    'preload_image' => '', // converted to an assets-relative path below
    'active_nav'   => 'shop',
    'breadcrumbs'  => $crumbs,
    'json_ld'      => array_filter([
        ld_product($product, $reviews, $rating),
        ld_breadcrumbs($crumbs),
        ld_organization(),
    ]),
]);

require __DIR__ . '/includes/layout/head.php';
?>
<main class="min-w-0 max-w-full overflow-x-clip pb-16 pt-[7.75rem] sm:pt-[8.75rem]">
  <div class="section-shell space-y-10">
    <?php require __DIR__ . '/includes/partials/breadcrumbs.php'; ?>

    <div class="grid gap-10 lg:grid-cols-2 lg:gap-14">
      <div class="space-y-4">
        <div class="surface-card overflow-hidden p-0">
          <?= picture($product['image'] ?? '', (string)$product['name'], [
                'width'         => 600,
                'height'        => 600,
                'fetchpriority' => 'high',
                'decoding'      => 'async',
                'class'         => 'h-auto w-full max-w-[600px] object-contain',
              ]) ?>
        </div>
      </div>

      <div class="space-y-5">
        <h1 class="text-[clamp(1.9rem,3.4vw,2.8rem)] font-bold leading-tight tracking-[-0.01em] text-[var(--ink)]"><?= e($product['name']) ?></h1>

        <div class="flex flex-wrap items-baseline gap-3">
          <p class="text-3xl font-bold text-[var(--accent)]"><?= e(format_money($price)) ?></p>
          <?php if ($has_sale): ?>
            <s class="text-lg text-[var(--muted)]"><?= e(format_money((float)$product['price'])) ?></s>
          <?php endif; ?>
          <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.06em] <?= $in_stock ? 'bg-[var(--accent-soft)] text-[var(--accent-dark)]' : 'bg-[#f3dddd] text-[#8a2b2b]' ?>">
            <?= $in_stock ? 'In stock' : 'Out of stock' ?>
          </span>
        </div>

        <?php if ($rating['count'] > 0): ?>
          <p class="text-sm text-[var(--muted)]" aria-label="Customer rating">
            <?= str_repeat('★', (int)round($rating['average'])) ?><?= str_repeat('☆', 5 - (int)round($rating['average'])) ?>
            <?= number_format($rating['average'], 1) ?>/5 from <?= (int)$rating['count'] ?> review<?= $rating['count'] === 1 ? '' : 's' ?>
          </p>
        <?php endif; ?>

        <?php if ($summary !== ''): ?>
          <p class="text-base leading-relaxed text-[var(--muted-2)]"><?= e(seo_text($summary, 320)) ?></p>
        <?php endif; ?>

        <form class="flex flex-wrap items-end gap-3" data-cart-form>
          <input type="hidden" name="slug" value="<?= e($product['slug']) ?>">
          <input type="hidden" name="name" value="<?= e($product['name']) ?>">
          <input type="hidden" name="price" value="<?= e(number_format($price, 2, '.', '')) ?>">
          <input type="hidden" name="image" value="<?= e(image_url($product['image'] ?? '')) ?>">
          <label class="text-sm font-medium text-[var(--ink)]">
            Quantity
            <input type="number" name="qty" value="1" min="1" max="99"
                   class="mt-1 block h-11 w-24 rounded-[12px] border border-[var(--line)] px-3 text-sm outline-none focus:border-[var(--accent)]">
          </label>
          <button type="submit" class="btn-primary !min-h-12 !px-7 !text-sm" <?= $in_stock ? '' : 'disabled' ?>>
            <?= $in_stock ? 'Add to cart' : 'Out of stock' ?>
          </button>
          <button type="button" class="btn-secondary !min-h-12 !px-5 !text-sm" data-wishlist-toggle data-slug="<?= e($product['slug']) ?>">Save for later</button>
        </form>

        <div class="surface-card space-y-2 px-5 py-4 text-sm text-[var(--muted-2)]">
          <p><strong class="text-[var(--ink)]">Shipping:</strong> discreet worldwide delivery. Tracking provided on dispatch.</p>
          <p><strong class="text-[var(--ink)]">Returns:</strong> see our <a class="underline" href="<?= e(url_path('returns/')) ?>">returns policy</a> for unopened goods.</p>
          <p><strong class="text-[var(--ink)]">Testing:</strong> every batch independently lab tested. <a class="underline" href="<?= e(url_path('testing/')) ?>">View testing</a>.</p>
        </div>

        <?php if (!$in_stock): ?>
          <div class="surface-card space-y-2 px-5 py-4">
            <p class="text-sm font-semibold text-[var(--ink)]">Currently out of stock</p>
            <p class="text-sm text-[var(--muted-2)]">Join the waitlist and we will email you the moment this product is back.</p>
            <form class="flex flex-col gap-2 sm:flex-row" action="<?= e(url_path('api/subscribe.php')) ?>" method="post" data-api="subscribe">
              <input type="email" name="email" required placeholder="you@example.com" class="h-11 flex-1 rounded-[12px] border border-[var(--line)] px-4 text-sm outline-none focus:border-[var(--accent)]">
              <button class="btn-primary !min-h-11 !px-5 !text-sm" type="submit">Notify me</button>
            </form>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="grid gap-10 lg:grid-cols-3">
      <section class="lg:col-span-2 space-y-4">
        <h2 class="text-2xl font-bold text-[var(--ink)]">Product description</h2>
        <?php if (trim((string)$product['description']) !== ''): ?>
          <div class="space-y-3 text-base leading-relaxed text-[var(--muted-2)]">
            <?php foreach (preg_split('/\n+/', trim((string)$product['description'])) as $para): ?>
              <p><?= e($para) ?></p>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="text-base leading-relaxed text-[var(--muted-2)]">
            Full product notes for <?= e($product['name']) ?> are being prepared. Contact us for the current batch
            certificate of analysis or specification sheet.
          </p>
        <?php endif; ?>
      </section>

      <section class="space-y-4">
        <h2 class="text-2xl font-bold text-[var(--ink)]">Specifications</h2>
        <table class="w-full text-sm">
          <tbody class="divide-y divide-[var(--line)]">
            <tr><th class="py-2 text-left font-medium text-[var(--muted)]">Product</th><td class="py-2 text-right text-[var(--ink)]"><?= e($product['name']) ?></td></tr>
            <tr><th class="py-2 text-left font-medium text-[var(--muted)]">Brand</th><td class="py-2 text-right text-[var(--ink)]"><?= e((string)($product['brand'] ?: store_name())) ?></td></tr>
            <tr><th class="py-2 text-left font-medium text-[var(--muted)]">SKU</th><td class="py-2 text-right text-[var(--ink)]"><?= e((string)$product['sku']) ?></td></tr>
            <?php if (!empty($product['category_name'])): ?>
              <tr><th class="py-2 text-left font-medium text-[var(--muted)]">Category</th><td class="py-2 text-right text-[var(--ink)]"><a class="underline" href="<?= e(url_path('category/' . $product['category_slug'] . '/')) ?>"><?= e((string)$product['category_name']) ?></a></td></tr>
            <?php endif; ?>
            <tr><th class="py-2 text-left font-medium text-[var(--muted)]">Availability</th><td class="py-2 text-right text-[var(--ink)]"><?= $in_stock ? 'In stock' : 'Out of stock' ?></td></tr>
          </tbody>
        </table>
      </section>
    </div>

    <?php if ($related): ?>
      <section class="space-y-5">
        <h2 class="text-2xl font-bold text-[var(--ink)]">Related products</h2>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
          <?php foreach ($related as $card): ?>
            <?php $card_heading = 'h3'; require __DIR__ . '/includes/partials/product-card.php'; ?>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <section class="space-y-6" id="reviews">
      <h2 class="text-2xl font-bold text-[var(--ink)]">Customer reviews</h2>
      <?php if ($reviews): ?>
        <ul class="space-y-4">
          <?php foreach ($reviews as $review): ?>
            <li class="surface-card space-y-1 px-5 py-4">
              <p class="text-sm text-[var(--accent)]" aria-label="<?= (int)$review['rating'] ?> out of 5 stars"><?= str_repeat('★', (int)$review['rating']) ?><?= str_repeat('☆', 5 - (int)$review['rating']) ?></p>
              <?php if (!empty($review['title'])): ?><p class="font-semibold text-[var(--ink)]"><?= e((string)$review['title']) ?></p><?php endif; ?>
              <p class="text-sm leading-relaxed text-[var(--muted-2)]"><?= e((string)$review['body']) ?></p>
              <p class="text-xs text-[var(--muted)]"><?= e((string)$review['author_name']) ?> · <?= e(date('j M Y', strtotime((string)$review['created_at']))) ?></p>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p class="text-base text-[var(--muted-2)]">No reviews yet for <?= e($product['name']) ?>. Be the first to share your experience.</p>
      <?php endif; ?>

      <form class="surface-card space-y-3 px-5 py-5" novalidate data-api="review">
        <h3 class="text-lg font-bold text-[var(--ink)]">Write a review</h3>
        <input type="hidden" name="product_slug" value="<?= e($product['slug']) ?>">
        <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
        <input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
        <div class="grid gap-3 sm:grid-cols-2">
          <label class="text-sm">Display name
            <input name="name" required maxlength="80" class="mt-1 h-11 w-full rounded-[12px] border border-[var(--line)] px-4 text-sm outline-none focus:border-[var(--accent)]">
          </label>
          <label class="text-sm">Email (not published)
            <input type="email" name="email" required class="mt-1 h-11 w-full rounded-[12px] border border-[var(--line)] px-4 text-sm outline-none focus:border-[var(--accent)]">
          </label>
        </div>
        <label class="block text-sm">Rating
          <select name="rating" class="mt-1 h-11 w-full rounded-[12px] border border-[var(--line)] px-4 text-sm outline-none focus:border-[var(--accent)]">
            <option value="5">5 - Excellent</option>
            <option value="4">4 - Good</option>
            <option value="3">3 - Average</option>
            <option value="2">2 - Poor</option>
            <option value="1">1 - Bad</option>
          </select>
        </label>
        <label class="block text-sm">Your review
          <textarea name="quote" required minlength="20" maxlength="2000" rows="4" class="mt-1 w-full rounded-[12px] border border-[var(--line)] px-4 py-3 text-sm outline-none focus:border-[var(--accent)]"></textarea>
        </label>
        <button type="submit" class="btn-primary !min-h-11 !px-6 !text-sm">Submit review</button>
        <p class="text-xs text-[var(--muted)]">Reviews are moderated before they appear. We never publish fabricated reviews.</p>
      </form>
    </section>
  </div>
</main>
<?php require __DIR__ . '/includes/layout/tail.php'; ?>
