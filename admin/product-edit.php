<?php
declare(strict_types=1);

require_once __DIR__ . '/_common.php';
require_admin();

$id    = (int)($_GET['id'] ?? 0);
$isNew = $id === 0;

$form = [
    'id' => 0, 'name' => '', 'slug' => '', 'category_id' => '',
    'price' => '', 'sale_price' => '', 'stock' => '100',
    'description' => '', 'image' => null, 'is_active' => 1, 'featured' => 0,
];

if (!$isNew) {
    $stmt = db()->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        flash('error', 'Product not found.');
        redirect('products.php');
    }
    $form = $row;
}

$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_verify();

    $form['name']        = trim((string)($_POST['name'] ?? ''));
    $form['slug']        = trim((string)($_POST['slug'] ?? ''));
    $form['category_id'] = (int)($_POST['category_id'] ?? 0);
    $form['price']       = trim((string)($_POST['price'] ?? ''));
    $form['sale_price']  = trim((string)($_POST['sale_price'] ?? ''));
    $form['stock']       = trim((string)($_POST['stock'] ?? '0'));
    $form['description'] = trim((string)($_POST['description'] ?? ''));
    $form['is_active']   = isset($_POST['is_active']) ? 1 : 0;
    $form['featured']    = isset($_POST['featured']) ? 1 : 0;

    if ($form['name'] === '') {
        $errors[] = 'The product name is required.';
    }
    if (!is_numeric($form['price']) || (float)$form['price'] < 0) {
        $errors[] = 'Enter a valid price (0 or more).';
    }
    if ($form['sale_price'] !== '' && (!is_numeric($form['sale_price']) || (float)$form['sale_price'] < 0)) {
        $errors[] = 'The sale price must be empty or a positive number.';
    }

    $slug = slugify($form['slug'] !== '' ? $form['slug'] : $form['name']);
    if ($slug === '') {
        $slug = 'product-' . time();
    }
    $base = $slug;
    $n    = 2;
    while (true) {
        $stmt = db()->prepare('SELECT COUNT(*) FROM products WHERE slug = ? AND id <> ?');
        $stmt->execute([$slug, (int)$form['id']]);
        if ((int)$stmt->fetchColumn() === 0) {
            break;
        }
        $slug = $base . '-' . $n++;
    }
    $form['slug'] = $slug;

    [$uploaded, $uploadError] = handle_image_upload('image', 'product');
    if ($uploadError !== null) {
        $errors[] = $uploadError;
    }

    if (!$errors) {
        $oldImage = $isNew ? null : ($form['image'] ?? null);
        if ($uploaded !== null) {
            $form['image'] = $uploaded;
        } elseif (($_POST['remove_image'] ?? '') === '1') {
            $form['image'] = null;
        }

        $price = round((float)$form['price'], 2);
        $sale  = $form['sale_price'] === '' ? null : round((float)$form['sale_price'], 2);
        $stock = (int)$form['stock'];
        $catId = (int)$form['category_id'] > 0 ? (int)$form['category_id'] : null;
        $image = $form['image'] ?: null;

        if ($isNew) {
            $stmt = db()->prepare(
                'INSERT INTO products (category_id, slug, name, description, price, sale_price, stock, image, is_active, featured)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$catId, $slug, $form['name'], $form['description'] ?: null, $price, $sale, $stock, $image, $form['is_active'], $form['featured']]);
            $id = (int)db()->lastInsertId();
            flash('success', 'Product created.');
        } else {
            $stmt = db()->prepare(
                'UPDATE products SET category_id = ?, slug = ?, name = ?, description = ?, price = ?, sale_price = ?,
                 stock = ?, image = ?, is_active = ?, featured = ? WHERE id = ?'
            );
            $stmt->execute([$catId, $slug, $form['name'], $form['description'] ?: null, $price, $sale, $stock, $image, $form['is_active'], $form['featured'], $id]);
            flash('success', 'Product saved.');
        }

        if ($oldImage !== null && $oldImage !== $image && str_starts_with((string)$oldImage, 'uploads/')) {
            @unlink(__DIR__ . '/../' . $oldImage);
        }
        redirect('product-edit.php?id=' . $id);
    }
}

$categories = db()->query('SELECT id, name FROM categories ORDER BY sort_order, name')->fetchAll();

$pageTitle = $isNew ? 'Add product' : 'Edit product';
$active    = 'products';
require __DIR__ . '/partials/header.php';
?>
<p><a href="products.php">← Back to products</a></p>

<?php if ($errors): ?>
  <div class="flash error"><?= e(implode(' ', $errors)) ?></div>
<?php endif; ?>

<form method="post" action="product-edit.php<?= $id ? '?id=' . (int)$id : '' ?>" enctype="multipart/form-data">
  <?php csrf_field(); ?>
  <div class="row2-3">
    <div class="card">
      <label class="fld"><span>Name *</span>
        <input type="text" name="name" required value="<?= e((string)$form['name']) ?>">
      </label>
      <label class="fld"><span>Slug (optional — generated from the name)</span>
        <input type="text" name="slug" value="<?= e((string)$form['slug']) ?>">
      </label>
      <div class="row2">
        <label class="fld"><span>Category</span>
          <select name="category_id">
            <option value="0">— none —</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= (int)$c['id'] ?>" <?= (int)$form['category_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label class="fld"><span>Stock</span>
          <input type="number" name="stock" min="0" value="<?= e((string)$form['stock']) ?>">
        </label>
      </div>
      <div class="row3">
        <label class="fld"><span>Price *</span>
          <input type="text" name="price" required value="<?= e((string)$form['price']) ?>" placeholder="45.00">
        </label>
        <label class="fld"><span>Sale price (optional)</span>
          <input type="text" name="sale_price" value="<?= e((string)$form['sale_price']) ?>">
        </label>
        <div class="fld"><span>&nbsp;</span>
          <label class="chk"><input type="checkbox" name="is_active" value="1" <?= (int)$form['is_active'] === 1 ? 'checked' : '' ?>> Active</label>
          <label class="chk"><input type="checkbox" name="featured" value="1" <?= (int)$form['featured'] === 1 ? 'checked' : '' ?>> Featured</label>
        </div>
      </div>
      <label class="fld"><span>Description</span>
        <textarea name="description" rows="6"><?= e((string)$form['description']) ?></textarea>
      </label>
    </div>

    <div>
      <div class="card">
        <h2 style="margin:0 0 .7rem;font-size:1rem">Image</h2>
        <?php if (!empty($form['image'])): ?>
          <img src="<?= e(admin_image_url((string)$form['image'])) ?>" alt="" style="width:100%;max-width:220px;border-radius:12px;border:1px solid var(--line);display:block;margin-bottom:.6rem">
          <label class="chk"><input type="checkbox" name="remove_image" value="1"> Remove current image</label>
        <?php else: ?>
          <p class="muted small">No image yet.</p>
        <?php endif; ?>
        <label class="fld mt1"><span>Upload / replace (JPG, PNG, WEBP · max <?= (int)arail_config()['app']['max_upload_mb'] ?> MB)</span>
          <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
        </label>
      </div>
    </div>
  </div>

  <div class="mt1">
    <button class="btn btn-primary" type="submit"><?= $isNew ? 'Create product' : 'Save changes' ?></button>
    <a class="btn" href="products.php">Cancel</a>
  </div>
</form>

<?php require __DIR__ . '/partials/footer.php'; ?>
