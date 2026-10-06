<?php
declare(strict_types=1);

require_once __DIR__ . '/_common.php';
require_admin();

/* Actions */
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_verify();
    $id     = (int)($_POST['id'] ?? 0);
    $action = (string)($_POST['action'] ?? '');
    $return = (string)($_POST['return'] ?? '');

    if ($id > 0 && in_array($action, ['toggle_active', 'toggle_featured', 'delete'], true)) {
        if ($action === 'delete') {
            $stmt = db()->prepare('SELECT image FROM products WHERE id = ?');
            $stmt->execute([$id]);
            $image = $stmt->fetchColumn();
            db()->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
            if (is_string($image) && str_starts_with($image, 'uploads/')) {
                @unlink(__DIR__ . '/../' . $image);
            }
            flash('success', 'Product deleted.');
        } else {
            $column = $action === 'toggle_active' ? 'is_active' : 'featured';
            db()->prepare("UPDATE products SET $column = 1 - $column WHERE id = ?")->execute([$id]);
            flash('success', 'Product updated.');
        }
    }
    redirect('products.php' . ($return !== '' ? '?' . $return : ''));
}

$q       = trim((string)($_GET['q'] ?? ''));
$catId   = (int)($_GET['category'] ?? 0);
$status  = (string)($_GET['status'] ?? 'all');
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$where  = [];
$params = [];
if ($q !== '') {
    $where[]  = '(p.name LIKE ? OR p.slug LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
if ($catId > 0) {
    $where[]  = 'p.category_id = ?';
    $params[] = $catId;
}
if ($status === 'active') {
    $where[] = 'p.is_active = 1';
} elseif ($status === 'hidden') {
    $where[] = 'p.is_active = 0';
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = db()->prepare("SELECT COUNT(*) FROM products p $whereSql");
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));
$page  = min($page, $pages);

$stmt = db()->prepare(
    "SELECT p.*, c.name AS category_name
     FROM products p LEFT JOIN categories c ON c.id = p.category_id
     $whereSql ORDER BY p.id DESC LIMIT $perPage OFFSET " . (($page - 1) * $perPage)
);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = db()->query('SELECT id, name FROM categories ORDER BY sort_order, name')->fetchAll();
$returnQ    = http_build_query(array_filter([
    'q'        => $q,
    'category' => $catId ?: null,
    'status'   => $status !== 'all' ? $status : null,
    'page'     => $page > 1 ? $page : null,
]));

$pageTitle = 'Products';
$active    = 'products';
require __DIR__ . '/partials/header.php';
?>
<div class="toolbar">
  <form method="get" action="products.php">
    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search name or slug…" style="width:220px">
    <select name="category" style="width:180px">
      <option value="0">All categories</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?= (int)$c['id'] ?>" <?= $catId === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="status" style="width:140px">
      <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All statuses</option>
      <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
      <option value="hidden" <?= $status === 'hidden' ? 'selected' : '' ?>>Hidden</option>
    </select>
    <button class="btn" type="submit">Filter</button>
  </form>
  <a class="btn btn-primary" href="product-edit.php" style="margin-left:auto">+ Add product</a>
</div>

<?php if (!$products): ?>
  <div class="card"><p class="empty">No products match this filter.</p></div>
<?php else: ?>
  <div class="table-scroll">
  <table class="tbl">
    <tr><th></th><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th></th></tr>
    <?php foreach ($products as $p): ?>
      <tr>
        <td><?php if ($p['image']): ?><img class="thumb" src="<?= e(admin_image_url((string)$p['image'])) ?>" alt="" width="46" height="46"><?php else: ?><span class="thumb thumb-empty">n/a</span><?php endif; ?></td>
        <td>
          <a href="product-edit.php?id=<?= (int)$p['id'] ?>"><strong><?= e($p['name']) ?></strong></a>
          <div class="small muted"><?= e($p['slug']) ?><?= (int)$p['featured'] === 1 ? ' · ★ featured' : '' ?></div>
        </td>
        <td><?= e($p['category_name'] ?? '—') ?></td>
        <td>
          <?= e(money((float)$p['price'])) ?>
          <?php if ($p['sale_price'] !== null): ?><div class="small muted">sale <?= e(money((float)$p['sale_price'])) ?></div><?php endif; ?>
        </td>
        <td><?= (int)$p['stock'] ?></td>
        <td><span class="badge <?= (int)$p['is_active'] === 1 ? 'active' : 'inactive' ?>"><?= (int)$p['is_active'] === 1 ? 'active' : 'hidden' ?></span></td>
        <td style="white-space:nowrap">
          <form class="inline" method="post" action="products.php">
            <?php csrf_field(); ?>
            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
            <input type="hidden" name="return" value="<?= e($returnQ) ?>">
            <button class="btn btn-sm" name="action" value="toggle_active" type="submit"><?= (int)$p['is_active'] === 1 ? 'Hide' : 'Show' ?></button>
            <button class="btn btn-sm" name="action" value="toggle_featured" type="submit"><?= (int)$p['featured'] === 1 ? 'Unstar' : 'Star' ?></button>
            <button class="btn btn-sm btn-danger" name="action" value="delete" type="submit" onclick="return confirm('Delete this product?')">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
  </div>

  <?php if ($pages > 1): ?>
    <div class="pager">
      <?php for ($i = 1; $i <= $pages; $i++):
          $qs = http_build_query(array_filter([
              'q'        => $q,
              'category' => $catId ?: null,
              'status'   => $status !== 'all' ? $status : null,
              'page'     => $i > 1 ? $i : null,
          ])); ?>
        <?php if ($i === $page): ?><span class="current"><?= $i ?></span>
        <?php else: ?><a href="products.php<?= $qs !== '' ? '?' . e($qs) : '' ?>"><?= $i ?></a><?php endif; ?>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
<?php endif; ?>

<p class="small muted mt1"><?= (int)$total ?> product(s) found.</p>

<?php require __DIR__ . '/partials/footer.php'; ?>
