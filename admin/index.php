<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_admin();

$pageTitle = 'Dashboard';
$active    = 'index';
require __DIR__ . '/partials/header.php';

$stat = static fn(string $sql): int => (int)db()->query($sql)->fetchColumn();

$stats = [
    ['Active products',  $stat('SELECT COUNT(*) FROM products WHERE is_active = 1'), 'products.php'],
    ['Pending orders',   $stat("SELECT COUNT(*) FROM orders WHERE status = 'pending'"), 'orders.php?status=pending'],
    ['Unpaid orders',    $stat("SELECT COUNT(*) FROM orders WHERE payment_status = 'unpaid'"), 'orders.php'],
    ['Pending reviews',  $stat("SELECT COUNT(*) FROM reviews WHERE status = 'pending'"), 'reviews.php?status=pending'],
    ['Unread messages',  $stat('SELECT COUNT(*) FROM messages WHERE is_read = 0'), 'messages.php'],
    ['Subscribers',      $stat("SELECT COUNT(*) FROM subscribers WHERE status = 'subscribed'"), 'subscribers.php'],
];

$recentOrders = db()->query(
    'SELECT o.id, o.order_number, o.customer_name, o.total, o.status, o.payment_status, o.created_at,
            (SELECT COUNT(*) FROM order_items i WHERE i.order_id = o.id) AS items
     FROM orders o ORDER BY o.id DESC LIMIT 6'
)->fetchAll();

$recentMessages = db()->query(
    'SELECT id, name, email, subject, is_read, created_at FROM messages ORDER BY id DESC LIMIT 5'
)->fetchAll();

$pendingReviews = db()->query(
    "SELECT r.id, r.author_name, r.rating, r.created_at, p.name AS product_name
     FROM reviews r LEFT JOIN products p ON p.id = r.product_id
     WHERE r.status = 'pending' ORDER BY r.id DESC LIMIT 5"
)->fetchAll();
?>
<div class="grid stats">
  <?php foreach ($stats as [$label, $value, $href]): ?>
    <div class="stat">
      <div class="n"><?= (int)$value ?></div>
      <div class="l"><?= e($label) ?></div>
      <a href="<?= e($href) ?>">View →</a>
    </div>
  <?php endforeach; ?>
</div>

<div class="row2">
  <div class="card">
    <h2 style="margin:0 0 .8rem;font-size:1.05rem">Latest orders</h2>
    <?php if (!$recentOrders): ?>
      <p class="empty">No orders yet. Orders placed through <code>api/order.php</code> show up here.</p>
    <?php else: ?>
      <div class="table-scroll">
      <table class="tbl">
        <tr><th>#</th><th>Customer</th><th>Items</th><th>Total</th><th>Status</th></tr>
        <?php foreach ($recentOrders as $o): ?>
          <tr>
            <td><a href="order-view.php?id=<?= (int)$o['id'] ?>"><?= e($o['order_number']) ?></a><div class="small muted"><?= e($o['created_at']) ?></div></td>
            <td><?= e($o['customer_name']) ?></td>
            <td><?= (int)$o['items'] ?></td>
            <td><?= e(money((float)$o['total'])) ?></td>
            <td><span class="badge <?= e($o['status']) ?>"><?= e($o['status']) ?></span>
                <span class="badge <?= e($o['payment_status']) ?>"><?= e($o['payment_status']) ?></span></td>
          </tr>
        <?php endforeach; ?>
      </table>
      </div>
    <?php endif; ?>
  </div>

  <div>
    <div class="card" style="margin-bottom:1rem">
      <h2 style="margin:0 0 .8rem;font-size:1.05rem">New messages</h2>
      <?php if (!$recentMessages): ?>
        <p class="empty">Inbox is empty.</p>
      <?php else: ?>
        <?php foreach ($recentMessages as $m): ?>
          <p style="margin:.45rem 0">
            <?php if (!$m['is_read']): ?><span class="badge pending">new</span><?php endif; ?>
            <a href="messages.php?id=<?= (int)$m['id'] ?>"><?= e($m['subject'] ?: '(no subject)') ?></a>
            <span class="small muted">— <?= e($m['name']) ?>, <?= e($m['created_at']) ?></span>
          </p>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="card">
      <h2 style="margin:0 0 .8rem;font-size:1.05rem">Reviews awaiting approval</h2>
      <?php if (!$pendingReviews): ?>
        <p class="empty">Nothing pending.</p>
      <?php else: ?>
        <?php foreach ($pendingReviews as $r): ?>
          <p style="margin:.45rem 0">
            <span class="stars"><?= str_repeat('★', (int)$r['rating']) ?></span>
            <a href="reviews.php?status=pending"><?= e($r['author_name']) ?></a>
            <span class="small muted"><?= e($r['product_name'] ?? 'general') ?> · <?= e($r['created_at']) ?></span>
          </p>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
