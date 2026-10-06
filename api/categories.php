<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/api-boot.php';

/* GET /api/categories.php - all categories with active product counts. */
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    json_out(['ok' => false, 'error' => 'Method not allowed'], 405);
}

try {
    $rows = db()->query(
        'SELECT c.id, c.slug, c.name, c.sort_order,
                (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.is_active = 1) AS product_count
         FROM categories c
         ORDER BY c.sort_order ASC, c.name ASC'
    )->fetchAll();

    foreach ($rows as &$row) {
        $row['id']            = (int)$row['id'];
        $row['sort_order']    = (int)$row['sort_order'];
        $row['product_count'] = (int)$row['product_count'];
    }
    unset($row);

    json_out(['ok' => true, 'categories' => $rows]);
} catch (Throwable $e) {
    json_out(['ok' => false, 'error' => 'Server error'], 500);
}
