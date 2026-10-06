<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$number = trim((string)($_GET['number'] ?? $_GET['order'] ?? ''));
$order  = null;
$items  = [];

if ($number !== '' && preg_match('/^[A-Z]{2,4}-[0-9]{8}-[A-Z0-9]{4,8}$/i', $number)) {
    $stmt = db()->prepare('SELECT * FROM orders WHERE order_number = ? LIMIT 1');
    $stmt->execute([strtoupper($number)]);
    $order = $stmt->fetch() ?: null;
    if ($order) {
        $stmt = db()->prepare('SELECT product_name, unit_price, quantity, line_total FROM order_items WHERE order_id = ? ORDER BY id');
        $stmt->execute([(int)$order['id']]);
        $items = $stmt->fetchAll();
    }
}

seo_set([
    'title'       => seo_title('Order confirmation | ' . store_name()),
    'description' => 'Your order has been received.',
    'canonical'   => 'order/',
    'noindex'     => true,
    'robots'      => 'noindex,nofollow',
    'active_nav'  => '',
    'json_ld'     => [],
]);

require __DIR__ . '/includes/layout/head.php';
?>
<main class="min-w-0 max-w-full overflow-x-clip pb-16 pt-[7.75rem] sm:pt-[8.75rem]">
  <div class="section-shell space-y-8">
    <?php if ($order): ?>
      <header class="space-y-3">
        <p class="text-sm font-semibold uppercase tracking-[0.08em] text-[var(--accent)]">Thank you</p>
        <h1 class="text-[clamp(2rem,4.5vw,3rem)] font-bold tracking-[-0.01em] text-[var(--ink)]">Order <?= e((string)$order['order_number']) ?></h1>
        <p class="text-base text-[var(--muted-2)]">We have received your order and will email payment instructions to <?= e((string)$order['customer_email']) ?> shortly.</p>
      </header>

      <section class="surface-card space-y-3 px-6 py-8">
        <h2 class="text-xl font-bold text-[var(--ink)]">Order summary</h2>
        <ul class="divide-y divide-[var(--line)]">
          <?php foreach ($items as $item): ?>
            <li class="flex justify-between gap-3 py-2 text-sm">
              <span class="text-[var(--muted-2)]"><?= e((string)$item['product_name']) ?> × <?= (int)$item['quantity'] ?></span>
              <span class="font-semibold text-[var(--ink)]"><?= e(format_money((float)$item['line_total'])) ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
        <div class="space-y-1 border-t border-[var(--line)] pt-3 text-sm">
          <p class="flex justify-between"><span class="text-[var(--muted)]">Subtotal</span><span><?= e(format_money((float)$order['subtotal'])) ?></span></p>
          <p class="flex justify-between"><span class="text-[var(--muted)]">Shipping</span><span><?= e(format_money((float)$order['shipping'])) ?></span></p>
          <p class="flex justify-between text-lg font-bold text-[var(--ink)]"><span>Total</span><span><?= e(format_money((float)$order['total'])) ?></span></p>
        </div>
        <p class="text-sm text-[var(--muted)]">Payment method: <?= e(payment_method_label((string)$order['payment_method'])) ?> · Status: <?= e((string)$order['status']) ?></p>
        <?php if (!empty($order['shipping_address'])): ?>
          <p class="text-sm text-[var(--muted)]">Shipping to: <?= nl2br(e((string)$order['shipping_address'])) ?></p>
        <?php endif; ?>
      </section>

      <div class="flex flex-wrap gap-3">
        <a class="btn-primary !min-h-12 !px-6 !text-sm" href="<?= e(url_path('shop/')) ?>">Continue shopping</a>
        <a class="btn-secondary !min-h-12 !px-6 !text-sm" href="<?= e(url_path('bitcoin/')) ?>">How to pay with Bitcoin</a>
      </div>
    <?php else: ?>
      <header class="space-y-3">
        <h1 class="text-[clamp(2rem,4.5vw,3rem)] font-bold tracking-[-0.01em] text-[var(--ink)]">Order not found</h1>
        <p class="text-base text-[var(--muted-2)]">We could not find that order reference. Check your confirmation email or contact us.</p>
      </header>
      <div class="flex flex-wrap gap-3">
        <a class="btn-primary !min-h-12 !px-6 !text-sm" href="<?= e(url_path('shop/')) ?>">Back to the shop</a>
        <a class="btn-secondary !min-h-12 !px-6 !text-sm" href="<?= e(url_path('contact/')) ?>">Contact us</a>
      </div>
    <?php endif; ?>
  </div>
</main>
<?php require __DIR__ . '/includes/layout/tail.php'; ?>
