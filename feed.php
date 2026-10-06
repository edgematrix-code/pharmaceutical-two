<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

/**
 * Google Merchant Center product feed.
 *
 *   /feed.php            -> XML (RSS 2.0 with the g: namespace)
 *   /feed.php?format=tsv -> tab-separated values
 *
 * Prices and availability are read from the same source as the product pages
 * and structured data, so the feed can never disagree with the site.
 */
$format = strtolower((string)($_GET['format'] ?? 'xml'));
$rows   = catalog_all_for_feed(1000);

$app    = arail_config()['app'];
$flat   = (float)$app['shipping_flat'];
$cc     = currency_code();

$clean = static function (?string $html, int $max): string {
    $text = html_entity_decode((string)$html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = trim(preg_replace('/\s+/u', ' ', strip_tags($text)) ?? '');
    if (mb_strlen($text) > $max) {
        $text = rtrim(mb_substr($text, 0, $max - 1), " ,.;:-") . '…';
    }
    return $text;
};

$records = [];
foreach ($rows as $p) {
    $price    = product_price($p);
    $in_stock = product_in_stock($p);
    $desc     = $clean($p['description'] ?? '', 5000);
    $title    = $clean($p['name'], 150);
    if ($desc === '') {
        $desc = $title . ' supplied by ' . store_name() . ' for laboratory research use.';
    }
    $records[] = [
        'id'                => (string)($p['sku'] ?: $p['slug']),
        'title'             => $title,
        'description'       => $desc,
        'link'              => url_abs('product/' . $p['slug'] . '/'),
        'image_link'        => url_abs(preg_replace('~^/~', '', image_url($p['image'] ?? ''))),
        'availability'      => $in_stock ? 'in_stock' : 'out_of_stock',
        'price'             => number_format($price, 2, '.', '') . ' ' . $cc,
        'brand'             => (string)($p['brand'] ?: store_name()),
        'condition'         => 'new',
        'product_type'      => (string)($p['category_name'] ?? ''),
        'mpn'               => (string)($p['mpn'] ?? ''),
        'gtin'              => (string)($p['gtin'] ?? ''),
        'shipping'          => number_format($flat, 2, '.', '') . ' ' . $cc,
    ];
}

if ($format === 'tsv') {
    header('Content-Type: text/tab-separated-values; charset=utf-8');
    header('Content-Disposition: attachment; filename="google-merchant-feed.tsv"');
    $columns = [
        'id', 'title', 'description', 'link', 'image_link', 'availability', 'price',
        'brand', 'condition', 'product_type', 'mpn', 'gtin', 'shipping',
    ];
    echo implode("\t", $columns) . "\n";
    foreach ($records as $r) {
        echo implode("\t", array_map(static fn($v) => str_replace(["\t", "\n"], ' ', (string)$v), $r)) . "\n";
    }
    exit;
}

header('Content-Type: application/xml; charset=utf-8');
$x = static fn(string $v): string => htmlspecialchars($v, ENT_XML1 | ENT_QUOTES, 'UTF-8');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">' . "\n";
echo "  <channel>\n";
echo '    <title>' . $x(store_name() . ' product feed') . "</title>\n";
echo '    <link>' . $x(url_abs('')) . "</link>\n";
echo '    <description>' . $x('Product feed for Google Merchant Center') . "</description>\n";
foreach ($records as $r) {
    echo "    <item>\n";
    foreach ($r as $key => $value) {
        if ($value === '') {
            continue;
        }
        echo '      <g:' . $key . '>' . $x((string)$value) . "</g:$key>\n";
    }
    echo "    </item>\n";
}
echo "  </channel>\n</rss>\n";
