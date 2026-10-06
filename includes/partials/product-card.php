<?php
/**
 * Product card. Expects $card (a product row) to be set before inclusion.
 * Optional: $card_heading ('h2'|'h3', default 'h2').
 */
$card_heading = $card_heading ?? 'h2';
$in_stock = product_in_stock($card);
$price    = product_price($card);
$has_sale = $card['sale_price'] !== null && (float)$card['sale_price'] > 0 && (float)$card['sale_price'] < (float)$card['price'];
$href     = url_path('product/' . $card['slug'] . '/');
$img      = image_url($card['image'] ?? '');
$alt      = (string)$card['name'];
?>
<article class="product-card group">
  <div class="product-card-media-wrap">
    <a class="product-card-media<?= $in_stock ? '' : ' is-out-of-stock' ?>" href="<?= e($href) ?>">
      <?= picture($card['image'] ?? '', $alt, ['loading' => 'lazy', 'decoding' => 'async', 'width' => 600, 'height' => 600]) ?>
    </a>
    <div class="product-card-hover-action"><div class="contents">
      <button type="button"
              class="product-card-add-btn"
              data-add-to-cart
              data-slug="<?= e($card['slug']) ?>"
              data-name="<?= e($card['name']) ?>"
              data-price="<?= e(number_format($price, 2, '.', '')) ?>"
              data-image="<?= e($img) ?>"
              aria-label="Add <?= e($card['name']) ?> to cart"
              <?= $in_stock ? '' : 'disabled' ?>>
        <span class="product-card-add-label"><?= $in_stock ? 'Add to cart' : 'Out of stock' ?></span>
      </button>
    </div></div>
  </div>
  <div class="product-card-body">
    <a href="<?= e($href) ?>">
      <<?= $card_heading ?> class="product-card-title"><?= e($card['name']) ?></<?= $card_heading ?>>
    </a>
    <p class="product-card-price">
      <?php if ($has_sale): ?>
        <span class="product-card-price-sale"><?= e(format_money($price)) ?></span>
        <s><?= e(format_money((float)$card['price'])) ?></s>
      <?php else: ?>
        <?= e(format_money((float)$card['price'])) ?>
      <?php endif; ?>
    </p>
    <?php if (!$in_stock): ?>
      <p class="product-card-pack-hint">Out of stock</p>
    <?php endif; ?>
  </div>
</article>
