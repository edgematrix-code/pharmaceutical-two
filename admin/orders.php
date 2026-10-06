<?php
declare(strict_types=1);

require_once __DIR__ . '/_common.php';
require_admin();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_verify();
    $id     = (int)($_POST['id'] ?? 0);
    $action = (string)($_POST['action'] ?? '');

    if ($id > 0 && $action === 'update') {
        $newStatus = (string)($_POST['status'] ?? '');
        $payment   = (string)($_POST['payment_status'] ?? '');
        if (validate_order_status($newStatus) && in_array($payment, payment_statuses(), true)) {
            db()->prepare('UPDATE orders SET status = ?, payment_status = ? WHERE id = ?')
                ->execute([$newStatus, $payment, $id]);
            flash('success', 'Order updated.');
        } else {
            flash('error', 'Invalid status value.');
        }
    } elseif ($id > 0 && $action === 'delete') {
        db()->prepare('DELETE FROM orders WHERE id = ?')->execute([$id]);
        flash('success', 'Order deleted.');
    }
    redirect('orders.php' . (($_SERVER['QUERY_STRING'] ?? '') !== '' ? '?' . $_SERVER['QUERY_STRING'] : ''));
}

$status = (string)($_GET['status'] ?? 'all');
$q      = trim((string)($_GET['q'] ?? ''));
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$where  = [];
$params = [];
if (in_array($status, ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'], true)) {
    $where[]  = 'o.status = ?';
    $params[] = $status;
}
if ($q !== '') {
    $where[]  = '(o.order_number LIKE ? OR o.customer_name LIKE ? OR o.customer_email LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = db()->prepare("SELECT COUNT(*) FROM orders o $whereSql");
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));
$page  = min($page, $pages);

$stmt = db()->prepare(
    "SELECT o.*, (SELECT COUNT(*) FROM order_items i WHERE i.order_id = o.id) AS items
     FROM orders o $whereSql ORDER BY o.id DESC LIMIT $perPage OFFSET " . (($page - 1) * $perPage)
);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$pageTitle = 'Orders';
$active    = 'orders';
require __DIR__ . '/partials/header.php';
$keep = array_filter(['status' => $status !== 'all' ? $status : null, 'q' => $q]);
$keepQ = $keep ? '?' . http_build_query($keep) : '';
?>
<div class="toolbar">
  <form method="get" action="orders.php">
    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search #, name or email…" style="width:240px">
    <select name="status" style="width:160px">
      <option value="all">All statuses</option>
      <?php foreach (order_statuses() as $s): ?>
        <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn" type="submit">Filter</button>
  </form>
</div>

<?php if (!$orders): ?>
  <div class="card"><p class="empty">No orders match. Orders posted to <code>api/order.php</code> land here.</p></div>
<?php else: ?>
  <div class="table-scroll">
  <table class="tbl">
    <tr><th>Order</th><th>Customer</th><th>Items</th><th>Total</th><th>Payment</th><th>Status</th><th></th></tr>
    <?php foreach ($orders as $o): ?>
      <tr>
        <td>
          <a href="order-view.php?id=<?= (int)$o['id'] ?>"><strong><?= e($o['order_number']) ?></strong></a>
          <div class="small muted"><?= e($o['created_at']) ?></div>
        </td>
        <td><?= e($o['customer_name']) ?><div class="small muted"><?= e($o['customer_email']) ?></div></td>
        <td><?= (int)$o['items'] ?></td>
        <td><?= e(money((float)$o['total'])) ?></td>
        <td><span class="badge <?= e($o['payment_status']) ?>"><?= e($o['payment_status']) ?></span></td>
        <td>
          <form class="inline" method="post" action="orders.php<?= e($keepQ) ?>">
            <?php csrf_field(); ?>
            <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
            <input type="hidden" name="action" value="update">
            <select name="status" style="width:130px;padding:.3rem">
              <?php foreach (order_statuses() as $s): ?>
                <option value="<?= e($s) ?>" <?= $o['status'] === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
              <?php endforeach; ?>
            </select>
            <select name="payment_status" style="width:110px;padding:.3rem">
              <?php foreach (payment_statuses() as $ps): ?>
                <option value="<?= e($ps) ?>" <?= $o['payment_status'] === $ps ? 'selected' : '' ?>><?= e($ps) ?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn btn-sm" type="submit">Save</button>
          </form>
        </td>
        <td><a class="btn btn-sm" href="order-view.php?id=<?= (int)$o['id'] ?>">View</a></td>
      </tr>
    <?php endforeach; ?>
  </table>
  </div>

  <?php if ($pages > 1): ?>
    <div class="pager">
      <?php for ($i = 1; $i <= $pages; $i++):
          $qs = http_build_query(array_filter(array_merge($keep, ['page' => $i > 1 ? $i : null]))); ?>
        <?php if ($i === $page): ?><span class="current"><?= $i ?></span>
        <?php else: ?><a href="orders.php<?= $qs !== '' ? '?' . e($qs) : '' ?>"><?= $i ?></a><?php endif; ?>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
<?php endif; ?>

<p class="small muted mt1"><?= (int)$total ?> order(s).</p>

<?php require __DIR__ . '/partials/footer.php'; ?>
