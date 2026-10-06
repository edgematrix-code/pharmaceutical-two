<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

/*
 * Dedicated crypto payment page (/pay/<order-number>/).
 *
 * The checkout hands off here as soon as an order is placed, so the wallet
 * address, amount and step-by-step instructions live on their own full-width
 * page instead of being buried inside the checkout form. The order number in
 * the path resolves the exact coin the customer chose.
 */

$number = trim((string)($_GET['number'] ?? $_GET['order'] ?? ''));
$order  = null;
$wallet = null;

if ($number !== '' && preg_match('/^[A-Z]{2,4}-[0-9]{8}-[A-Z0-9]{4,8}$/i', $number)) {
    $stmt = db()->prepare('SELECT * FROM orders WHERE order_number = ? LIMIT 1');
    $stmt->execute([strtoupper($number)]);
    $order = $stmt->fetch() ?: null;
    if ($order) {
        $wallet = crypto_payment_methods()[(string)$order['payment_method']] ?? null;
    }
}

seo_set([
    'title'       => seo_title('Complete your payment | ' . store_name()),
    'description' => 'Send your crypto payment to the wallet address shown for your order.',
    'canonical'   => 'pay/',
    'noindex'     => true,
    'robots'      => 'noindex,nofollow',
    'active_nav'  => '',
    'json_ld'     => [],
]);

require __DIR__ . '/includes/layout/head.php';
?>
<main class="min-w-0 max-w-full overflow-x-clip pb-16 pt-[7.75rem] sm:pt-[8.75rem]">
  <div class="crypto-page">
    <?php if ($order && $wallet): ?>
      <section class="crypto-page__card" aria-label="Crypto payment details">
        <header class="crypto-page__hero">
          <span class="crypto-page__badge" aria-hidden="true">
            <img src="<?= e(asset($wallet['mark'])) ?>" alt="" width="44" height="44">
          </span>
          <p class="crypto-page__eyebrow">Payment step</p>
          <h1 class="crypto-page__title">Complete your <?= e($wallet['label']) ?> payment</h1>
          <p class="crypto-page__sub">Order <strong><?= e((string)$order['order_number']) ?></strong> is reserved. Send the amount below and we&rsquo;ll confirm by email at <?= e((string)$order['customer_email']) ?>.</p>
        </header>

        <div class="crypto-page__amount">
          <span class="crypto-page__amount-label">Amount due</span>
          <span class="crypto-page__amount-value"><?= e(format_money((float)$order['total'])) ?></span>
          <span class="crypto-page__amount-note">Send the equivalent in <?= e($wallet['symbol']) ?></span>
        </div>

        <div class="crypto-page__field">
          <div class="crypto-page__field-head">
            <span class="crypto-page__field-label"><?= e($wallet['label']) ?> address</span>
            <span class="crypto-page__network"><?= e($wallet['network']) ?></span>
          </div>
          <div class="crypto-page__address-row" data-copy-scope>
            <code class="crypto-page__address" data-copy-value="<?= e($wallet['address']) ?>"><?= e($wallet['address']) ?></code>
            <button type="button" class="crypto-page__copy" data-copy-address>Copy address</button>
          </div>
        </div>

        <ol class="crypto-page__steps">
          <li><span class="crypto-page__num" aria-hidden="true">1</span><span>Copy the address above and paste it into your wallet.</span></li>
          <li><span class="crypto-page__num" aria-hidden="true">2</span><span>Send the equivalent of <strong><?= e(format_money((float)$order['total'])) ?></strong> in <?= e($wallet['symbol']) ?> on the <?= e($wallet['network']) ?>.</span></li>
          <li><span class="crypto-page__num" aria-hidden="true">3</span><span>Keep your transaction ID &mdash; we may ask for it to match your payment.</span></li>
          <li><span class="crypto-page__num" aria-hidden="true">4</span><span>Your order is marked paid once the transfer confirms on-chain.</span></li>
        </ol>

        <p class="crypto-page__warn">Send only <?= e($wallet['symbol']) ?> on the <?= e($wallet['network']) ?> to this address. Coins sent on another network cannot be recovered.</p>

        <div class="crypto-page__actions">
          <a class="btn-primary !min-h-12 !px-6 !text-sm crypto-page__cta" href="<?= e(url_path('order/' . $order['order_number'] . '/')) ?>">I have completed payment <span class="crypto-page__cta-sub">(view order details)</span></a>
          <a class="btn-secondary !min-h-12 !px-6 !text-sm" href="<?= e(url_path('contact/')) ?>">Need help?</a>
        </div>
      </section>
    <?php elseif ($order): ?>
      <section class="crypto-page__card crypto-page__card--narrow">
        <h1 class="crypto-page__title">No crypto payment for this order</h1>
        <p class="crypto-page__sub">Order <?= e((string)$order['order_number']) ?> does not use a Bitcoin or Ethereum wallet. Reply to your confirmation email if you need payment details.</p>
        <div class="crypto-page__actions">
          <a class="btn-primary !min-h-12 !px-6 !text-sm" href="<?= e(url_path('order/' . $order['order_number'] . '/')) ?>">View order details</a>
        </div>
      </section>
    <?php else: ?>
      <section class="crypto-page__card crypto-page__card--narrow">
        <h1 class="crypto-page__title">Payment link not found</h1>
        <p class="crypto-page__sub">We could not find that order. Use the link in your confirmation email, or contact us and we&rsquo;ll help you complete the payment.</p>
        <div class="crypto-page__actions">
          <a class="btn-primary !min-h-12 !px-6 !text-sm" href="<?= e(url_path('shop/')) ?>">Back to the shop</a>
          <a class="btn-secondary !min-h-12 !px-6 !text-sm" href="<?= e(url_path('contact/')) ?>">Contact us</a>
        </div>
      </section>
    <?php endif; ?>
  </div>
</main>
<?php require __DIR__ . '/includes/layout/tail.php'; ?>
