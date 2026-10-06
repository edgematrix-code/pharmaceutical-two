<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

/*
 * Checkout layout mirrors the reference capture (checkout-and-cart/Checkout page.html).
 * The order summary, upsells state and payment selection are wired up in
 * assets/js/cart.js, which reads the [data-*] hooks below.
 */

/* Payment options: the crypto wallets the store accepts. Addresses come from
   crypto_payment_methods() (see includes/helpers.php + config). */
$providers = crypto_payment_methods();

/* Upsell strip: the pairings the reference shows, resolved against the live
   catalogue so prices and images are always real. */
$upsell_slugs = ['test-cyp-200', 'test-enanthate-300', 'arimidex-1'];
$upsells      = [];
foreach ($upsell_slugs as $upsell_slug) {
    $row = catalog_product($upsell_slug);
    if ($row && product_in_stock($row)) {
        $upsells[] = $row;
    }
}

$bac = catalog_product('bacteriostatic-water-10ml');

/* Reviews strip: the four reviews shown at the top of the reference page. */
$strip_reviews = [
    [
        'name' => 'Thomas Carolan', 'initials' => 'TC', 'colour' => 'rgb(72, 168, 121)',
        'photo' => 'review-3-1024x768.jpg', 'date' => 'Oct 2026',
        'quote' => 'Top notch as always. #1 customer service, quick arrival, great packaging. I have been ordering from Arail for a while and never fails. I had a couple of errors on my end and they had the issue resolved same day.',
    ],
    [
        'name' => 'Dennis', 'initials' => 'DE', 'colour' => 'rgb(61, 155, 181)',
        'photo' => 'review-1-576x1024.jpg', 'date' => 'Oct 2026',
        'quote' => 'Arail comes through every single time. Consistent, reliable, and fast. My go to place!',
    ],
    [
        'name' => 'PumpChaser', 'initials' => 'PU', 'colour' => 'rgb(71, 169, 212)',
        'photo' => 'review-2-1024x768.jpg', 'date' => 'Oct 2026',
        'quote' => 'Shipping took 5 days from purchase to touchdown. Packaged extremely well and professionally. Everything looks great and came as expected. Glad I decided to atart using Arail!',
    ],
    [
        'name' => 'Bradley', 'initials' => 'BR', 'colour' => 'rgb(91, 184, 217)',
        'photo' => 'review-1024x768.jpg', 'date' => 'Oct 2026',
        'quote' => 'Always in good condition and the product is always great',
    ],
];

seo_set([
    'title'       => seo_title('Checkout | ' . store_name()),
    'description' => 'Complete your order. Prices are recalculated securely on our server.',
    'canonical'   => 'checkout/',
    'noindex'     => true,
    'robots'      => 'noindex,nofollow',
    'active_nav'  => '',
    'json_ld'     => [],
]);

require __DIR__ . '/includes/layout/head.php';
?>
<main class="min-w-0 max-w-full overflow-x-clip pb-16 pt-[7.75rem] sm:pt-[8.75rem]">
  <div class="section-shell checkout-page">
    <div class="checkout-page__inner">
      <header class="checkout-page__header">
        <h1 class="checkout-page__title">Checkout</h1>
        <p class="checkout-page__subtitle">Complete your order securely. Your details are encrypted and never shared.</p>
      </header>

      <div class="checkout-page__reviews">
        <aside class="reviews-strip" aria-label="Customer reviews">
          <p class="reviews-strip__eyebrow">Trusted by the community</p>
          <div class="reviews-carousel">
            <ul class="reviews-carousel__track reviews-strip__list" tabindex="0" aria-label="Community trust reviews" aria-roledescription="carousel">
              <?php foreach ($strip_reviews as $i => $review): ?>
                <li class="reviews-carousel__item" aria-label="Review <?= $i + 1 ?> of <?= count($strip_reviews) ?>"<?= $i === 0 ? ' aria-current="true"' : '' ?>>
                  <article class="review-card review-card--strip">
                    <div class="review-card__photo">
                      <?= picture('assets/img/' . $review['photo'], $review['name'] . "'s product photo", ['loading' => 'lazy']) ?>
                    </div>
                    <header class="review-card__header">
                      <span class="review-card__avatar" aria-hidden="true" style="background-color: <?= e($review['colour']) ?>;"><?= e($review['initials']) ?></span>
                      <div class="review-card__meta">
                        <div class="review-card__name-row">
                          <span class="review-card__name"><?= e($review['name']) ?></span>
                          <span class="review-card__source">site</span>
                        </div>
                        <time class="review-card__date"><?= e($review['date']) ?></time>
                      </div>
                    </header>
                    <blockquote class="review-card__quote"><p>“<?= e($review['quote']) ?>”</p></blockquote>
                  </article>
                </li>
              <?php endforeach; ?>
            </ul>
            <div class="reviews-carousel__controls" role="group" aria-label="Review slides">
              <button type="button" class="reviews-carousel__arrow" aria-label="Previous review" disabled><span aria-hidden="true">‹</span></button>
              <div class="reviews-carousel__dots">
                <?php foreach ($strip_reviews as $i => $review): ?>
                  <button type="button" class="reviews-carousel__dot<?= $i === 0 ? ' is-active' : '' ?>" aria-label="Go to review <?= $i + 1 ?>"<?= $i === 0 ? ' aria-current="true"' : '' ?>></button>
                <?php endforeach; ?>
              </div>
              <button type="button" class="reviews-carousel__arrow" aria-label="Next review"><span aria-hidden="true">›</span></button>
            </div>
          </div>
        </aside>
      </div>

      <form class="checkout-form" data-checkout-form novalidate>
        <div class="checkout-form__main">
          <section class="checkout-section surface-card">
            <h2 class="checkout-section__title">Contact</h2>
            <div class="checkout-fields">
              <label class="checkout-field">
                <span class="checkout-field__label">Email</span>
                <input required aria-required="true" class="checkout-field__input" type="email" name="email" autocomplete="email">
              </label>
              <label class="checkout-field">
                <span class="checkout-field__label">Phone (optional)</span>
                <input class="checkout-field__input" type="tel" name="phone" autocomplete="tel">
              </label>
            </div>
          </section>

          <section class="checkout-section surface-card">
            <h2 class="checkout-section__title">Shipping</h2>
            <div class="checkout-fields">
              <label class="checkout-field">
                <span class="checkout-field__label">First name</span>
                <input required aria-required="true" class="checkout-field__input" type="text" name="first_name" autocomplete="given-name">
              </label>
              <label class="checkout-field">
                <span class="checkout-field__label">Last name</span>
                <input required aria-required="true" class="checkout-field__input" type="text" name="last_name" autocomplete="family-name">
              </label>
              <label class="checkout-field">
                <span class="checkout-field__label">Address</span>
                <input required aria-required="true" class="checkout-field__input" type="text" name="address" autocomplete="street-address">
              </label>
              <label class="checkout-field">
                <span class="checkout-field__label">City</span>
                <input required aria-required="true" class="checkout-field__input" type="text" name="city" autocomplete="address-level2">
              </label>
              <label class="checkout-field">
                <span class="checkout-field__label">State / Province</span>
                <input required aria-required="true" class="checkout-field__input" type="text" name="state" autocomplete="address-level1">
              </label>
              <label class="checkout-field">
                <span class="checkout-field__label">Postal code</span>
                <input required aria-required="true" class="checkout-field__input" type="text" name="postal_code" autocomplete="postal-code">
              </label>
              <label class="checkout-field">
                <span class="checkout-field__label">Country</span>
                <input class="checkout-field__input" type="text" name="country" value="United States" readonly aria-readonly="true" autocomplete="country-name">
              </label>
            </div>
          </section>

          <div class="checkout-ship-toggle">
            <label class="checkout-checkbox">
              <input type="checkbox" checked>
              <span>Billing address same as shipping</span>
            </label>
          </div>
        </div>

        <aside class="checkout-form__sidebar">
          <div class="checkout-summary surface-card"
               data-checkout-summary
               data-cart-url="<?= e(url_path('cart/')) ?>"
               data-confirm-url="<?= e(url_path('pay/')) ?>"
               data-shipping="<?= e(number_format((float)arail_config()['app']['shipping_flat'], 2, '.', '')) ?>"
               data-min-order="<?= e(number_format(min_order_value(), 2, '.', '')) ?>">
            <h2 class="checkout-summary__title">Order summary</h2>
            <p class="text-sm text-[var(--muted)]">Loading…</p>
          </div>

          <div class="checkout-upsells surface-card">
            <p class="checkout-upsells__eyebrow">Boost your order</p>

            <?php if ($bac): ?>
              <section class="side-cart-promo side-cart-promo--kit" aria-label="Complete your kit">
                <div class="side-cart-promo__body">
                  <p class="side-cart-promo__eyebrow">Complete your kit</p>
                  <p class="side-cart-promo__title">Add bacteriostatic water for reconstitution</p>
                  <p class="side-cart-promo__meta"><?= e($bac['name']) ?></p>
                </div>
                <button type="button" class="side-cart-promo__cta"
                        data-upsell-add
                        data-slug="<?= e($bac['slug']) ?>"
                        data-name="<?= e($bac['name']) ?>"
                        data-price="<?= e(number_format(product_price($bac), 2, '.', '')) ?>"
                        data-image="<?= e(image_url($bac['image'] ?? '')) ?>">Add BAC water</button>
              </section>
            <?php endif; ?>

            <?php if ($upsells): ?>
              <section class="side-cart-upsells" aria-label="Frequently bought together">
                <div class="side-cart-upsells__head">
                  <h3 class="side-cart-upsells__title">Frequently bought together</h3>
                  <button type="button" class="side-cart-upsells__add-all" data-upsell-add-all>Add all</button>
                </div>
                <ul class="side-cart-upsells__list">
                  <?php foreach ($upsells as $upsell): ?>
                    <li class="side-cart-upsell">
                      <a class="side-cart-upsell__link" href="<?= e(url_path('product/' . $upsell['slug'] . '/')) ?>">
                        <div class="side-cart-upsell__image">
                          <?= picture($upsell['image'] ?? '', $upsell['name'], ['loading' => 'lazy', 'decoding' => 'async']) ?>
                        </div>
                        <div class="side-cart-upsell__info">
                          <span class="side-cart-upsell__name"><?= e($upsell['name']) ?></span>
                          <span class="side-cart-upsell__price"><?= e(format_money(product_price($upsell))) ?></span>
                        </div>
                      </a>
                      <button type="button" class="side-cart-upsell__add"
                              aria-label="Add <?= e($upsell['name']) ?> to cart"
                              data-upsell-add
                              data-slug="<?= e($upsell['slug']) ?>"
                              data-name="<?= e($upsell['name']) ?>"
                              data-price="<?= e(number_format(product_price($upsell), 2, '.', '')) ?>"
                              data-image="<?= e(image_url($upsell['image'] ?? '')) ?>">Add</button>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </section>
            <?php endif; ?>

            <a class="checkout-stacks-link" href="<?= e(url_path('stacks/')) ?>">Save more with protocol stacks →</a>
          </div>

          <div class="checkout-payment surface-card">
            <h2 class="checkout-section__title">Payment</h2>
            <p class="checkout-payment__intro">Choose how you want to pay. After you place the order we’ll show the wallet address, the amount and the payment steps on a dedicated page.</p>
            <ul class="checkout-payment__methods" role="radiogroup" aria-label="Payment method">
              <?php $first = true; ?>
              <?php foreach ($providers as $value => $provider): ?>
                <li>
                  <label class="checkout-payment__card<?= $first ? ' is-selected' : '' ?>">
                    <input class="checkout-payment__radio" type="radio" value="<?= e($value) ?>" name="payment"<?= $first ? ' checked' : '' ?>>
                    <span class="checkout-payment__brand" aria-hidden="true">
                      <img alt="" class="checkout-payment__mark" src="<?= e(asset($provider['mark'])) ?>">
                    </span>
                    <span class="checkout-payment__card-body">
                      <span class="checkout-payment__card-title"><?= e($provider['label']) ?><span class="checkout-payment__card-symbol"><?= e($provider['symbol']) ?></span></span>
                      <span class="checkout-payment__card-desc"><?= e($provider['desc']) ?></span>
                    </span>
                    <span class="checkout-payment__indicator" aria-hidden="true"></span>
                  </label>
                </li>
                <?php $first = false; ?>
              <?php endforeach; ?>
            </ul>
            <p class="checkout-min-note" data-checkout-min role="status" hidden></p>
            <p class="checkout-error" data-checkout-status role="status"></p>
            <button type="submit" class="checkout-submit btn-primary">Place order</button>
          </div>

          <div class="side-cart-trust checkout-trust" role="group" aria-label="Why shop with Arail">
            <p class="side-cart-trust-eyebrow">Shop with confidence</p>
            <ul class="side-cart-trust-list">
              <li class="side-cart-trust-item">
                <span class="side-cart-trust-icon" aria-hidden="true">
                  <svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true" class="shrink-0 text-[var(--accent)]"><rect x="4.5" y="8.5" width="11" height="8" rx="1.5" stroke="currentColor" stroke-width="1.4"></rect><path d="M7 8.5V6.8a3 3 0 0 1 6 0V8.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"></path><circle cx="10" cy="12.5" r="1" fill="currentColor"></circle></svg>
                </span>
                <span class="side-cart-trust-label">Secure checkout</span>
              </li>
              <li class="side-cart-trust-item">
                <span class="side-cart-trust-icon" aria-hidden="true">
                  <svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true" class="shrink-0 text-[var(--accent)]"><path d="M8.2 2.5h3.6M9 2.5v4.1L5.4 13.2a3.1 3.1 0 0 0 2.65 4.6h4.9a3.1 3.1 0 0 0 2.65-4.6L11 6.6V2.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path><path d="M7.1 11.6h5.8" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"></path><circle cx="12.6" cy="14.2" r="0.9" fill="currentColor"></circle></svg>
                </span>
                <span class="side-cart-trust-label">Lab tested</span>
              </li>
              <li class="side-cart-trust-item">
                <span class="side-cart-trust-icon" aria-hidden="true">
                  <svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true" class="shrink-0 text-[var(--accent)]"><path d="M3.4 6.8 10 3.4l6.6 3.4v6.8L10 16.6 3.4 13.6V6.8Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"></path><path d="M3.4 6.8 10 10.2l6.6-3.4M10 10.2v6.4" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"></path><path d="m6.2 5.4 7.6 3.9" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" opacity="0.85"></path></svg>
                </span>
                <span class="side-cart-trust-label">Discreet shipping</span>
              </li>
              <li class="side-cart-trust-item">
                <span class="side-cart-trust-icon" aria-hidden="true">
                  <img src="<?= e(asset('img/bitcoin-logo.svg')) ?>" alt="" width="20" height="20" aria-hidden="true" class="shrink-0" style="width:20px;height:20px;display:block">
                </span>
                <span class="side-cart-trust-label">BTC &amp; ETH accepted</span>
              </li>
            </ul>
          </div>
        </aside>
      </form>
    </div>
  </div>
</main>
<?php require __DIR__ . '/includes/layout/tail.php'; ?>
