<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/api-boot.php';

/* GET /api/product.php?slug=...  (or ?id=...)
   Returns the product, 4 related products and approved reviews. */
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    json_out(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$slug = req_str($_GET, 'slug');
$id   = req_int($_GET, 'id');
if ($slug === '' && $id === 0) {
    json_out(['ok' => false, 'error' => 'Provide ?slug= or ?id= parameter.'], 400);
}

try {
    $sql = 'SELECT p.id, p.slug, p.name, p.description, p.price, p.sale_price,
                   p.stock, p.image, p.featured, p.category_id, p.created_at,
                   c.slug AS category_slug, c.name AS category_name
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE ';
    $sql .= $slug !== '' ? 'p.slug = ?' : 'p.id = ?';

    $stmt = db()->prepare($sql . ' LIMIT 1');
    $stmt->execute([$slug !== '' ? $slug : $id]);
    $product = $stmt->fetch();

    if (!$product) {
        json_out(['ok' => false, 'error' => 'Product not found'], 404);
    }

    $product['id']         = (int)$product['id'];
    $product['price']      = (float)$product['price'];
    $product['sale_price'] = $product['sale_price'] !== null ? (float)$product['sale_price'] : null;
    $product['stock']      = (int)$product['stock'];
    $product['featured']   = (bool)$product['featured'];
    $product['in_stock']   = $product['stock'] > 0;

    $related = [];
    if ($product['category_id'] !== null) {
        $stmt = db()->prepare(
            'SELECT id, slug, name, price, sale_price, image
             FROM products
             WHERE category_id = ? AND id <> ? AND is_active = 1
             ORDER BY RAND() LIMIT 4'
        );
        $stmt->execute([(int)$product['category_id'], $product['id']]);
        $related = $stmt->fetchAll();
    }

    $stmt = db()->prepare(
        "SELECT id, author_name, rating, title, body, created_at
         FROM reviews
         WHERE product_id = ? AND status = 'approved'
         ORDER BY created_at DESC LIMIT 20"
    );
    $stmt->execute([$product['id']]);
    $reviews = $stmt->fetchAll();

    json_out(['ok' => true, 'product' => $product, 'related' => $related, 'reviews' => $reviews]);
} catch (Throwable $e) {
    json_out(['ok' => false, 'error' => 'Server error'], 500);
}
