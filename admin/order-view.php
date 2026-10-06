<?php
declare(strict_types=1);

require_once __DIR__ . '/_common.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_verify();
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'update') {
        $status  = (string)($_POST['status'] ?? '');
        $payment = (string)($_POST['payment_status'] ?? '');
        $notes   = trim((string)($_POST['admin_notes'] ?? ''));
        if (validate_order_status($status) && in_array($payment, payment_statuses(), true)) {
            db()->prepare('UPDATE orders SET status = ?, payment_status = ?, admin_notes = ? WHERE id = ?')
                ->execute([$status, $payment, $notes !== '' ? $notes : null, $id]);
            flash('success', 'Order saved.');
        } else {
            flash('error', 'Invalid status value.');
        }
    } elseif ($action === 'delete') {
        db()->prepare('DELETE FROM orders WHERE id = ?')->execute([$id]);
        flash('success', 'Order deleted.');
        redirect('orders.php');
    }
    redirect('order-view.php?id=' . $id);
}

$stmt = db()->prepare('SELECT * FROM orders WHERE id = ?');
$stmt->execute([$id]);
$order = $stmt->fetch();
if (!$order) {
    flash('error', 'Order not found.');
    redirect('orders.php');
}

$stmt = db()->prepare(
    'SELECT i.*, p.slug FROM order_items i LEFT JOIN products p ON p.id = i.product_id
     WHERE i.order_id = ? ORDER BY i.id'
);
$stmt->execute([$id]);
$items = $stmt->fetchAll();

$pageTitle = 'Order ' . $order['order_number'];
$active    = 'orders';
require __DIR__ . '/partials/header.php';
?>
<p><a href="orders.php">← Back to orders</a></p>

<div class="row2">
  <div class="card">
    <h2 style="margin:0 0 .7rem;font-size:1.05rem">Items</h2>
    <div class="table-scroll">
    <table class="tbl">
      <tr><th>Product</th><th>Unit</th><th>Qty</th><th>Total</th></tr>
      <?php foreach ($items as $it): ?>
        <tr>
          <td><?= e($it['product_name']) ?><?php if ($it['slug']): ?><div class="small muted"><?= e($it['slug']) ?></div><?php endif; ?></td>
          <td><?= e(money((float)$it['unit_price'])) ?></td>
          <td><?= (int)$it['quantity'] ?></td>
          <td><?= e(money((float)$it['line_total'])) ?></td>
        </tr>
      <?php endforeach; ?>
      <tr><td colspan="3" style="text-align:right">Subtotal</td><td><?= e(money((float)$order['subtotal'])) ?></td></tr>
      <tr><td colspan="3" style="text-align:right">Shipping</td><td><?= e(money((float)$order['shipping'])) ?></td></tr>
      <tr><td colspan="3" style="text-align:right"><strong>Total</strong></td><td><strong><?= e(money((float)$order['total'])) ?></strong></td></tr>
    </table>
    </div>
  </div>

  <div>
    <div class="card" style="margin-bottom:1rem">
      <h2 style="margin:0 0 .7rem;font-size:1.05rem">Customer</h2>
      <p style="margin:.3rem 0"><strong><?= e($order['customer_name']) ?></strong></p>
      <p style="margin:.3rem 0" class="small"><?= e($order['customer_email']) ?><?= $order['customer_phone'] ? ' · ' . e($order['customer_phone']) : '' ?></p>
      <?php if ($order['shipping_address']): ?>
        <p style="margin:.5rem 0" class="small"><span class="muted">Address:</span><br><?= nl2br(e($order['shipping_address'])) ?></p>
      <?php endif; ?>
      <?php if ($order['customer_notes']): ?>
        <p style="margin:.5rem 0" class="small"><span class="muted">Customer note:</span><br><?= nl2br(e($order['customer_notes'])) ?></p>
      <?php endif; ?>
      <p class="small muted" style="margin:.5rem 0">
        Placed <?= e($order['created_at']) ?> · Payment: <?= e(payment_method_label((string)$order['payment_method'])) ?> ·
        <span class="badge <?= e($order['payment_status']) ?>"><?= e($order['payment_status']) ?></span>
      </p>
      <?php $crypto_wallet = crypto_payment_methods()[(string)$order['payment_method']] ?? null; ?>
      <?php if ($crypto_wallet): ?>
        <p class="small" style="margin:.25rem 0"><span class="muted">Wallet:</span> <code><?= e($crypto_wallet['address']) ?></code> (<?= e($crypto_wallet['network']) ?>)</p>
      <?php endif; ?>
    </div>

    <div class="card">
      <h2 style="margin:0 0 .7rem;font-size:1.05rem">Update order</h2>
      <form method="post" action="order-view.php?id=<?= (int)$order['id'] ?>">
        <?php csrf_field(); ?>
        <input type="hidden" name="action" value="update">
        <div class="row2">
          <label class="fld"><span>Status</span>
            <select name="status">
              <?php foreach (order_statuses() as $s): ?>
                <option value="<?= e($s) ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="fld"><span>Payment</span>
            <select name="payment_status">
              <?php foreach (payment_statuses() as $ps): ?>
                <option value="<?= e($ps) ?>" <?= $order['payment_status'] === $ps ? 'selected' : '' ?>><?= e(ucfirst($ps)) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
        </div>
        <label class="fld"><span>Admin notes (internal)</span>
          <textarea name="admin_notes" rows="3" style="min-height:80px"><?= e((string)$order['admin_notes']) ?></textarea>
        </label>
        <button class="btn btn-primary" type="submit">Save</button>
        <button class="btn btn-danger" name="action" value="delete" type="submit" onclick="return confirm('Delete this order permanently?')">Delete order</button>
      </form>
    </div>
  </div>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
