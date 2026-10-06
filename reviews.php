<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

seo_set([
    'title'       => seo_title('Customer Reviews & Feedback | Arail Pharmaceuticals'),
    'description' => seo_text('Verified customer reviews and community feedback for Arail Pharmaceuticals, including forum threads and independent third-party reports.'),
    'canonical'   => 'reviews/',
    'active_nav'  => 'help',
    'json_ld'     => [
        ld_breadcrumbs(array (
  0 => 
  array (
    'name' => 'Home',
    'url' => '',
  ),
  1 => 
  array (
    'name' => 'Reviews',
    'url' => 'reviews/',
  ),
)),
        ld_organization(),
    ],
]);
$crumbs = array (
  0 => 
  array (
    'name' => 'Home',
    'url' => '',
  ),
  1 => 
  array (
    'name' => 'Reviews',
    'url' => 'reviews/',
  ),
);

require __DIR__ . '/includes/layout/head.php';
?>
<main class="min-w-0 max-w-full overflow-x-clip pb-16 pt-[7.75rem] sm:pt-[8.75rem]"><div class="reviews-page"><section class="section-shell reviews-page__shell animate-fade-up"><header class="reviews-page__header"><p class="reviews-page__eyebrow">Rewards</p><h1 class="reviews-page__title">Leave a review</h1><p class="reviews-page__lede">Photo + short write-up. After approval we email <strong>20% off</strong>. Each SST or MESO source / product thread review adds <strong>+5%</strong>.</p><ul class="reviews-page__rewards" aria-label="Reward rates"><li class="reviews-page__reward"><span class="reviews-page__reward-amt">20%</span><span class="reviews-page__reward-label">Site photo review</span></li><li class="reviews-page__reward"><span class="reviews-page__reward-amt">+5%</span><span class="reviews-page__reward-label">Per SST / MESO thread</span></li></ul></header><div class="surface-card reviews-page__panel"><form class="reviews-form" novalidate="" data-api="review"><h2 class="reviews-page__form-title">Submit for approval</h2><div class="reviews-form__row"><label class="reviews-form__field"><span>Name</span><input required="" maxlength="80" autocomplete="nickname" placeholder="Display name" type="text" value="" name="name"></label><label class="reviews-form__field"><span>Email</span><input required="" autocomplete="email" placeholder="For your coupon" type="email" value="" name="email"></label></div><label class="reviews-form__field"><span>Review</span><textarea name="quote" required="" minlength="20" maxlength="2000" rows="4" placeholder="Shipping, packaging, quality…"></textarea></label><div class="reviews-form__row reviews-form__row--photo"><label class="reviews-form__field reviews-form__field--grow"><span>Photo <em>required</em></span><input accept="image/jpeg,image/png,image/webp" required="" type="file" name="photo"></label></div><label class="reviews-form__field"><span>Forum post URL <em>optional · +5%</em></span><input placeholder="https://…" type="url" value="" name="forumUrl"></label><div class="reviews-form__actions"><button type="submit" class="btn-primary reviews-form__submit">Submit for approval</button><p class="reviews-form__note">Coupon emailed after approval · photo required · one site reward per submission</p></div></form><aside id="forums" class="reviews-page__forums" aria-label="Forum threads"><p class="reviews-page__forums-label">Also post here for +5%</p><div class="reviews-page__forum-chips"><a href="https://www.steroidsourcetalk.cc/index.php?threads/source-arail-pharmaceuticals-https-arailpharma-is-quality-and-customer-service-personified-serving-the-community-to-our-utmost-ability.18505/" target="_blank" rel="noopener noreferrer" class="reviews-page__forum-chip"><picture><source type="image/webp" srcset="/assets/img/sst-logo.webp"><img loading="lazy" decoding="async" alt="" width="96" height="34" src="/assets/img/sst-logo.png"></picture><span>SST thread</span></a><a href="https://thinksteroids.com/community/threads/arail-pharmaceuticals-us-domestic.134426367/" target="_blank" rel="noopener noreferrer" class="reviews-page__forum-chip"><picture><source type="image/webp" srcset="/assets/img/meso-rx-logo.webp"><img loading="lazy" decoding="async" alt="" width="110" height="34" src="/assets/img/meso-rx-logo.png"></picture><span>MESO-Rx thread</span></a></div></aside></div></section></div></main>

    <?php $latest = catalog_latest_reviews(12); ?>
    <?php if ($latest): ?>
      <div class="section-shell space-y-5 pt-12">
        <h2 class="text-2xl font-bold text-[var(--ink)]">Latest customer reviews</h2>
        <ul class="grid gap-4 md:grid-cols-2">
          <?php foreach ($latest as $review): ?>
            <li class="surface-card space-y-1 px-5 py-4">
              <p class="text-sm text-[var(--accent)]" aria-label="<?= (int)$review['rating'] ?> out of 5 stars"><?= str_repeat('★', (int)$review['rating']) ?><?= str_repeat('☆', 5 - (int)$review['rating']) ?></p>
              <p class="text-sm leading-relaxed text-[var(--muted-2)]"><?= e((string)$review['body']) ?></p>
              <p class="text-xs text-[var(--muted)]">
                <?= e((string)$review['author_name']) ?><?php if (!empty($review['product_slug'])): ?> ·
                  <a class="underline" href="<?= e(url_path('product/' . $review['product_slug'] . '/')) ?>"><?= e((string)$review['product_name']) ?></a><?php endif; ?>
              </p>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
<?php require __DIR__ . '/includes/layout/tail.php'; ?>