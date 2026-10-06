<?php
declare(strict_types=1);

/**
 * Catalogue data-access helpers (products, categories, reviews).
 *
 * All queries use prepared statements. Product image paths that were captured
 * from the old static mirror ("Shop_files/foo.png") are remapped onto the
 * consolidated /assets/img directory; admin uploads ("uploads/foo.png") are
 * served from /uploads.
 */

/** Map a stored image value to a root-relative, web-safe URL. */
function image_url(?string $stored, string $fallback = 'assets/img/arail-logo-exact-v9.png'): string
{
    $stored = trim((string)$stored);
    if ($stored === '') {
        return url_path($fallback);
    }
    // Already web-absolute.
    if (preg_match('~^https?://~i', $stored)) {
        return $stored;
    }
    if (str_starts_with($stored, 'uploads/')) {
        return url_path($stored);
    }
    // Mirror capture such as "Shop_files/diwone-x-600x600.png".
    return url_path('assets/img/' . rawurlencode(basename($stored)));
}

/** Effective selling price for a product row. */
function product_price(array $p): float
{
    return (float)($p['sale_price'] !== null && $p['sale_price'] !== '' ? $p['sale_price'] : $p['price']);
}

function product_in_stock(array $p): bool
{
    return (int)($p['stock'] ?? 0) > 0;
}

/** All active categories with product counts. */
function catalog_categories(): array
{
    return db()->query(
        "SELECT c.id, c.slug, c.name, c.meta_title, c.meta_description, c.sort_order,
                (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.is_active = 1) AS product_count
         FROM categories c
         ORDER BY c.sort_order ASC, c.name ASC"
    )->fetchAll();
}

function catalog_category(string $slug): ?array
{
    $stmt = db()->prepare('SELECT * FROM categories WHERE slug = ? LIMIT 1');
    $stmt->execute([$slug]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/**
 * Product listing.
 *
 * @param array{category?:string,q?:string,featured?:bool,limit?:int,offset?:int,sort?:string} $opts
 * @return array<int,array<string,mixed>>
 */
function catalog_products(array $opts = []): array
{
    [$where, $params] = catalog_where($opts);
    $limit  = max(1, min((int)($opts['limit'] ?? 24), 200));
    $offset = max(0, (int)($opts['offset'] ?? 0));

    $order = match ($opts['sort'] ?? '') {
        'price-asc'  => 'p.price ASC',
        'price-desc' => 'p.price DESC',
        'name'       => 'p.name ASC',
        'newest'     => 'p.created_at DESC',
        default      => 'p.featured DESC, p.name ASC',
    };

    $sql = "SELECT p.*, c.slug AS category_slug, c.name AS category_name
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY $order
            LIMIT $limit OFFSET $offset";
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function catalog_count(array $opts = []): int
{
    [$where, $params] = catalog_where($opts);
    $sql = "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON c.id = p.category_id
            WHERE " . implode(' AND ', $where);
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}

/** @return array{0:array<int,string>,1:array<int,mixed>} */
function catalog_where(array $opts): array
{
    $where  = ['p.is_active = 1'];
    $params = [];

    $category = trim((string)($opts['category'] ?? ''));
    if ($category !== '') {
        if (ctype_digit($category)) {
            $where[]  = 'p.category_id = ?';
            $params[] = (int)$category;
        } else {
            $where[]  = 'c.slug = ?';
            $params[] = $category;
        }
    }

    $q = trim((string)($opts['q'] ?? ''));
    if ($q !== '') {
        $where[]  = '(p.name LIKE ? OR p.description LIKE ?)';
        $params[] = '%' . $q . '%';
        $params[] = '%' . $q . '%';
    }

    if (!empty($opts['featured'])) {
        $where[] = 'p.featured = 1';
    }

    return [$where, $params];
}

function catalog_product(string $slug): ?array
{
    $stmt = db()->prepare(
        'SELECT p.*, c.slug AS category_slug, c.name AS category_name
         FROM products p LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.slug = ? LIMIT 1'
    );
    $stmt->execute([$slug]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/** Products in the same category, excluding the current one. */
function catalog_related(array $product, int $limit = 4): array
{
    if (empty($product['category_id'])) {
        return [];
    }
    $stmt = db()->prepare(
        'SELECT p.*, c.slug AS category_slug, c.name AS category_name
         FROM products p LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.category_id = ? AND p.id <> ? AND p.is_active = 1
         ORDER BY p.featured DESC, RAND() LIMIT ' . max(1, $limit)
    );
    $stmt->execute([(int)$product['category_id'], (int)$product['id']]);
    return $stmt->fetchAll();
}

/** Approved reviews for a product (newest first). */
function catalog_reviews(int $productId, int $limit = 20): array
{
    $stmt = db()->prepare(
        "SELECT id, author_name, rating, title, body, created_at
         FROM reviews
         WHERE product_id = ? AND status = 'approved'
         ORDER BY created_at DESC LIMIT " . max(1, min($limit, 100))
    );
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}

/** Aggregate rating built from real reviews only; empty when there are none. */
function catalog_rating(int $productId): array
{
    $stmt = db()->prepare(
        "SELECT COUNT(*) AS count, COALESCE(AVG(rating), 0) AS average
         FROM reviews WHERE product_id = ? AND status = 'approved'"
    );
    $stmt->execute([$productId]);
    $row = $stmt->fetch() ?: ['count' => 0, 'average' => 0];
    return ['count' => (int)$row['count'], 'average' => (float)$row['average']];
}

/** Latest approved reviews across the catalogue (used by /reviews/). */
function catalog_latest_reviews(int $limit = 12): array
{
    $stmt = db()->prepare(
        "SELECT r.*, p.slug AS product_slug, p.name AS product_name
         FROM reviews r LEFT JOIN products p ON p.id = r.product_id
         WHERE r.status = 'approved'
         ORDER BY r.created_at DESC LIMIT " . max(1, min($limit, 50))
    );
    $stmt->execute();
    return $stmt->fetchAll();
}

/** Products for the Merchant Center feed / sitemap (all indexable ones). */
function catalog_all_for_feed(int $limit = 1000, int $offset = 0): array
{
    $stmt = db()->prepare(
        'SELECT p.*, c.slug AS category_slug, c.name AS category_name
         FROM products p LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.is_active = 1
         ORDER BY p.id ASC LIMIT ' . max(1, $limit) . ' OFFSET ' . max(0, $offset)
    );
    $stmt->execute();
    return $stmt->fetchAll();
}
