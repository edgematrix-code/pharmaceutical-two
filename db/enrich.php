<?php
declare(strict_types=1);

/*
 * CLI-only. nginx does not read .htaccess, so these utilities must refuse to
 * run over HTTP themselves rather than relying on server configuration.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    exit('This script can only be run from the command line.');
}

/**
 * One-off catalogue enrichment.
 *
 * CLI: php db/enrich.php
 *
 * Only data the store can legitimately claim is written here:
 *   - brand: the store's own brand
 *   - sku / mpn: internal identifiers generated from the product id + slug
 *   - category intro copy + meta description
 *
 * No GTIN is invented: real barcodes must be supplied by the supplier.
 */
require_once __DIR__ . '/../includes/db.php';

$pdo = db();
$app = arail_config()['app'];
$brand = 'Arail Pharmaceuticals';

/* Brand + internal identifiers for every catalogue product. */
$rows = $pdo->query('SELECT id, slug, brand, sku, mpn FROM products')->fetchAll();
$upd  = $pdo->prepare('UPDATE products SET brand = ?, sku = ?, mpn = ? WHERE id = ?');
$touched = 0;
foreach ($rows as $r) {
    $sku = $r['sku'] ?: sprintf('ARL-%04d', (int)$r['id']);
    $mpn = $r['mpn'] ?: strtoupper($r['slug']);
    $upd->execute([$brand, $sku, $mpn, (int)$r['id']]);
    $touched++;
}

/* Category SEO copy (short, factual). */
$copy = [
    'injectable-anabolics' => [
        'desc' => 'Injectable anabolic products from Arail Pharmaceuticals, supplied in sealed vials. Each batch is produced to a documented specification and independently lab tested.',
        'image' => 'img/category-injectable-anabolics.png',
    ],
    'oral-anabolics' => [
        'desc' => 'Oral anabolic products from Arail Pharmaceuticals, supplied in sealed bottles and dropper formats. Each batch is independently lab tested before release.',
        'image' => 'img/category-oral-anabolics.png',
    ],
    'peptides-hgh' => [
        'desc' => 'Research peptides, growth hormone and metabolic peptides from Arail Pharmaceuticals. Supplied lyophilised or ready to use, with independent lab testing on every batch.',
        'image' => 'img/category-peptides-hgh.png',
    ],
];

$updCat = $pdo->prepare(
    'UPDATE categories SET description = COALESCE(NULLIF(description, ""), ?), image = COALESCE(NULLIF(image, ""), ?) WHERE slug = ?'
);
foreach ($copy as $slug => $data) {
    $updCat->execute([$data['desc'], $data['image'], $slug]);
}

echo "products enriched: $touched\n";
echo "categories updated: " . count($copy) . "\n";
