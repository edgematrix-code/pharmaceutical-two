<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/api-boot.php';

/* GET /api/products.php
   Query: category (slug or id), q, featured=1, limit, offset */
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    json_out(['ok' => false, 'error' => 'Method not allowed'], 405);
}

try {
    $where  = ['p.is_active = 1'];
    $params = [];

    $category = req_str($_GET, 'category');
    if ($category !== '') {
        if (ctype_digit($category)) {
            $where[]  = 'p.category_id = ?';
            $params[] = (int)$category;
        } else {
            $where[]  = 'c.slug = ?';
            $params[] = $category;
        }
    }

    $q = req_str($_GET, 'q');
    if ($q !== '') {
        $where[]  = '(p.name LIKE ? OR p.description LIKE ?)';
        $params[] = '%' . $q . '%';
        $params[] = '%' . $q . '%';
    }

    if (req_str($_GET, 'featured') === '1') {
        $where[] = 'p.featured = 1';
    }

    $limit  = min(max(req_int($_GET, 'limit', 50), 1), 200);
    $offset = max(req_int($_GET, 'offset', 0), 0);

    $sql = 'SELECT p.id, p.slug, p.name, p.description, p.price, p.sale_price,
                   p.stock, p.image, p.featured,
                   c.slug AS category_slug, c.name AS category_name
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY p.featured DESC, p.name ASC
            LIMIT ' . $limit . ' OFFSET ' . $offset;

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    foreach ($products as &$p) {
        $p['id']         = (int)$p['id'];
        $p['price']      = (float)$p['price'];
        $p['sale_price'] = $p['sale_price'] !== null ? (float)$p['sale_price'] : null;
        $p['stock']      = (int)$p['stock'];
        $p['featured']   = (bool)$p['featured'];
        $p['in_stock']   = $p['stock'] > 0;
    }
    unset($p);

    json_out(['ok' => true, 'count' => count($products), 'products' => $products]);
} catch (Throwable $e) {
    json_out(['ok' => false, 'error' => 'Server error'], 500);
}
