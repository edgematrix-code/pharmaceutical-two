<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_admin();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_verify();
    $action = (string)($_POST['action'] ?? '');
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'add') {
        $name = trim((string)($_POST['name'] ?? ''));
        $sort = (int)($_POST['sort_order'] ?? 0);
        if ($name !== '') {
            $slug = slugify($name);
            $stmt = db()->prepare('SELECT COUNT(*) FROM categories WHERE slug = ?');
            $stmt->execute([$slug]);
            if ((int)$stmt->fetchColumn() > 0) {
                $slug .= '-' . time();
            }
            db()->prepare('INSERT INTO categories (name, slug, sort_order) VALUES (?, ?, ?)')
                ->execute([$name, $slug, $sort]);
            flash('success', 'Category created.');
        } else {
            flash('error', 'Category name is required.');
        }
    } elseif ($action === 'update' && $id > 0) {
        $name = trim((string)($_POST['name'] ?? ''));
        $sort = (int)($_POST['sort_order'] ?? 0);
        if ($name !== '') {
            db()->prepare('UPDATE categories SET name = ?, sort_order = ? WHERE id = ?')
                ->execute([$name, $sort, $id]);
            flash('success', 'Category updated.');
        } else {
            flash('error', 'Category name is required.');
        }
    } elseif ($action === 'delete' && $id > 0) {
        db()->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]); // products keep with NULL category
        flash('success', 'Category deleted. Its products were kept but are now uncategorised.');
    }
    redirect('categories.php');
}

$categories = db()->query(
    'SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count
     FROM categories c ORDER BY c.sort_order, c.name'
)->fetchAll();

$pageTitle = 'Categories';
$active    = 'categories';
require __DIR__ . '/partials/header.php';
?>
<div class="card" style="margin-bottom:1.1rem">
  <h2 style="margin:0 0 .8rem;font-size:1.05rem">Add a category</h2>
  <form method="post" action="categories.php" class="toolbar" style="margin-bottom:0">
    <?php csrf_field(); ?>
    <input type="hidden" name="action" value="add">
    <input type="text" name="name" placeholder="Category name *" required style="width:260px">
    <input type="number" name="sort_order" value="0" title="Sort order" style="width:90px">
    <button class="btn btn-primary" type="submit">Add</button>
  </form>
</div>

<div class="table-scroll">
<table class="tbl">
  <tr><th>Name</th><th>Slug</th><th>Sort</th><th>Products</th><th></th></tr>
  <?php foreach ($categories as $c): ?>
    <tr>
      <td colspan="5" style="padding:.5rem .7rem">
        <form method="post" action="categories.php" class="toolbar" style="margin-bottom:0">
          <?php csrf_field(); ?>
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
          <input type="text" name="name" value="<?= e($c['name']) ?>" required style="width:240px">
          <input type="number" name="sort_order" value="<?= (int)$c['sort_order'] ?>" style="width:90px">
          <button class="btn btn-sm" type="submit">Save</button>
          <span class="small muted">slug: <?= e($c['slug']) ?> · <?= (int)$c['product_count'] ?> product(s)</span>
        </form>
      </td>
    </tr>
    <tr>
      <td colspan="4" class="small muted">View on site:
        <a href="../Shop.html">Shop.html</a> — categories are browsed via the navbar dropdown / shop page chips.
      </td>
      <td>
        <form class="inline" method="post" action="categories.php">
          <?php csrf_field(); ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
          <button class="btn btn-sm btn-danger" type="submit" onclick="return confirm('Delete this category? Products will remain but become uncategorised.')">Delete</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$categories): ?>
    <tr><td colspan="5" class="empty">No categories yet — add one above.</td></tr>
  <?php endif; ?>
</table>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
