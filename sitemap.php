<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

/**
 * XML sitemaps.
 *
 *   /sitemap.xml                       -> sitemap index
 *   /sitemap.xml?type=products&page=1  -> products (with image extension)
 *   /sitemap.xml?type=categories       -> categories
 *   /sitemap.xml?type=pages            -> static pages
 *
 * Only 200-status, indexable, canonical URLs are listed.
 */
header('Content-Type: application/xml; charset=utf-8');

$type = preg_replace('/[^a-z]/', '', strtolower((string)($_GET['type'] ?? 'index')));
$page = max(1, (int)($_GET['page'] ?? 1));
$per  = 50000;

/** Escape a value for XML output. */
$x = static fn(string $v): string => htmlspecialchars($v, ENT_XML1 | ENT_QUOTES, 'UTF-8');

$url = static function (string $loc, ?string $lastmod = null, array $images = []): string {
    $x = static fn(string $v): string => htmlspecialchars($v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    $out = "  <url>\n    <loc>" . $x($loc) . "</loc>\n";
    if ($lastmod) {
        $out .= "    <lastmod>" . $x($lastmod) . "</lastmod>\n";
    }
    foreach ($images as $img) {
        $out .= "    <image:image>\n      <image:loc>" . $x($img['loc']) . "</image:loc>\n";
        if (!empty($img['title'])) {
            $out .= "      <image:title>" . $x($img['title']) . "</image:title>\n";
        }
        $out .= "    </image:image>\n";
    }
    return $out . "  </url>\n";
};

if ($type === 'products') {
    $offset  = ($page - 1) * $per;
    $rows    = catalog_all_for_feed($per, $offset);
    $total   = catalog_count();
    $lastmod = null;
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
    foreach ($rows as $p) {
        $lm = !empty($p['updated_at']) ? date('Y-m-d', strtotime((string)$p['updated_at'])) : null;
        $images = [[
            'loc'   => url_abs(preg_replace('~^/~', '', image_url($p['image'] ?? ''))),
            'title' => (string)$p['name'],
        ]];
        echo $url(url_abs('product/' . $p['slug'] . '/'), $lm, $images);
    }
    echo '</urlset>' . "\n";
    exit;
}

if ($type === 'categories') {
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
    foreach (catalog_categories() as $cat) {
        if ((int)$cat['product_count'] === 0) {
            continue; // never list empty, thin category pages
        }
        $images = [];
        if (!empty($cat['image'])) {
            $images[] = ['loc' => url_abs(preg_replace('~^/~', '', image_url($cat['image']))), 'title' => (string)$cat['name']];
        }
        echo $url(url_abs('category/' . $cat['slug'] . '/'), null, $images);
    }
    echo '</urlset>' . "\n";
    exit;
}

if ($type === 'pages') {
    $pages = [
        ['', null],
        ['shop/', null],
        ['stacks/', null],
        ['dosing/', null],
        ['testing/', null],
        ['reviews/', null],
        ['bitcoin/', null],
        ['contact/', null],
        ['about/', null],
        ['shipping/', null],
        ['returns/', null],
        ['privacy/', null],
        ['terms/', null],
    ];
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($pages as [$path, $lm]) {
        echo $url(url_abs($path), $lm);
    }
    echo '</urlset>' . "\n";
    exit;
}

/* Sitemap index */
$productPages = max(1, (int)ceil(catalog_count() / $per));
$entries = [];
for ($i = 1; $i <= $productPages; $i++) {
    $entries[] = url_abs('sitemap.xml') . '?type=products&page=' . $i;
}
$entries[] = url_abs('sitemap.xml') . '?type=categories';
$entries[] = url_abs('sitemap.xml') . '?type=pages';

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($entries as $entry) {
    echo "  <sitemap>\n    <loc>" . $x($entry) . "</loc>\n    <lastmod>" . date('Y-m-d') . "</lastmod>\n  </sitemap>\n";
}
echo '</sitemapindex>' . "\n";
