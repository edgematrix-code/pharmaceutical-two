<?php
declare(strict_types=1);

/**
 * SEO primitives: base-URL resolution, canonical / Open Graph / Twitter tags
 * and JSON-LD structured-data builders.
 *
 * No host name is hard-coded anywhere. Every absolute URL is derived from the
 * configured "app.base_url" (see includes/config.php). When that is empty
 * (local development only) the current request host is used, but only after it
 * has been validated against a strict host-name pattern.
 */

/** Populated by each page before layout/head.php is included. */
$GLOBALS['SEO'] = [
    'title'        => '',
    'description'  => '',
    'canonical'    => '/',
    'robots'       => 'index,follow,max-image-preview:large',
    'og_type'      => 'website',
    'og_image'     => 'assets/img/arail-logo-exact-og-v9.png',
    'og_image_alt' => 'Arail Pharmaceuticals',
    'og'           => [],
    'twitter_card' => 'summary_large_image',
    'json_ld'      => [],
    'preload_image' => '',
    'active_nav'   => '',
    'noindex'      => false,
];

/**
 * Merge values into the global SEO state for the current page.
 */
function seo_set(array $values): void
{
    $GLOBALS['SEO'] = array_merge($GLOBALS['SEO'], $values);
}

function seo(string $key, $default = '')
{
    return $GLOBALS['SEO'][$key] ?? $default;
}

/**
 * Root-relative base path the app is served from ('' at document root,
 * '/sub-dir' when the project lives in a sub-folder).
 */
function base_path(): string
{
    static $base = null;
    if ($base === null) {
        $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
        // The app lives at the web root of its own document root.
        $base = ($script === '/' || $script === '.') ? '' : rtrim($script, '/');
    }
    return $base;
}

/**
 * Absolute site origin, e.g. https://example.com - never a hard-coded domain.
 */
function base_url(): string
{
    static $url = null;
    if ($url !== null) {
        return $url;
    }

    $configured = trim((string)(arail_config()['app']['base_url'] ?? ''));
    if ($configured !== '') {
        return $url = rtrim($configured, '/');
    }

    // Local development fallback: derive from the request, but validate strictly.
    $host = (string)($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost');
    if (!preg_match('/^[a-z0-9]([a-z0-9.-]*[a-z0-9])?(:\d{1,5})?$/i', $host)) {
        $host = 'localhost';
    }
    $https  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443');
    $scheme = $https ? 'https' : 'http';

    return $url = $scheme . '://' . $host;
}

/** Root-relative URL for an internal link, e.g. url_path('shop/'). */
function url_path(string $path = ''): string
{
    $path = ltrim($path, '/');
    return base_path() . '/' . $path;
}

/** Absolute URL, e.g. url_abs('product/test-cyp-200/'). */
function url_abs(string $path = ''): string
{
    return base_url() . url_path($path);
}

/** True when the site is running on a real configured domain. */
function has_configured_domain(): bool
{
    return trim((string)(arail_config()['app']['base_url'] ?? '')) !== '';
}

/** Path of the current request, normalised with a leading and trailing slash. */
function current_path(): string
{
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    if (base_path() !== '' && str_starts_with($uri, base_path())) {
        $uri = substr($uri, strlen(base_path()));
    }
    $uri = '/' . ltrim($uri, '/');
    if ($uri !== '/' && !str_ends_with($uri, '/') && !str_contains(basename($uri), '.')) {
        $uri .= '/';
    }
    return $uri;
}

/** Absolute canonical URL for the current page. */
function canonical_url(): string
{
    $path = (string)seo('canonical', current_path());
    // Allow a page to override the canonical with a full URL if it needs to.
    if (preg_match('~^https?://~i', $path)) {
        return $path;
    }
    return base_url() . url_path($path);
}

/**
 * Strip tags, collapse whitespace and clamp to a character budget (used for
 * meta descriptions).
 */
function seo_text(?string $html, int $max = 158): string
{
    $text = html_entity_decode((string)$html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/\s+/u', ' ', strip_tags($text)) ?? '';
    $text = trim($text);
    if (mb_strlen($text) <= $max) {
        return $text;
    }
    $cut = mb_substr($text, 0, $max - 1);
    $space = mb_strrpos($cut, ' ');
    if ($space !== false && $space > $max * 0.6) {
        $cut = mb_substr($cut, 0, $space);
    }
    return rtrim($cut, " ,.;:-") . '…';
}

/** Clamp a title to roughly 50-60 characters without chopping words. */
function seo_title(string $title, int $max = 60): string
{
    $title = preg_replace('/\s+/u', ' ', trim($title)) ?? '';
    if (mb_strlen($title) <= $max) {
        return $title;
    }
    $cut = mb_substr($title, 0, $max - 1);
    $space = mb_strrpos($cut, ' ');
    if ($space !== false) {
        $cut = mb_substr($cut, 0, $space);
    }
    return rtrim($cut, " ,.;:-|") . '…';
}

/** Store name used in generated titles. */
function store_name(): string
{
    return (string)(arail_config()['app']['name'] ?? 'Store');
}

/** Store currency symbol / ISO code. */
function currency_symbol(): string
{
    return (string)(arail_config()['app']['currency'] ?? '$');
}

function currency_code(): string
{
    return (string)(arail_config()['app']['currency_code'] ?? 'USD');
}

/** Emit a JSON-LD block. */
function json_ld_markup(array $data): string
{
    return '<script type="application/ld+json">'
        . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        . '</script>';
}

/* ------------------------------------------------------------------ *
 * Structured-data builders
 * ------------------------------------------------------------------ */

function ld_breadcrumbs(array $items): array
{
    $elements = [];
    foreach (array_values($items) as $i => $item) {
        $elements[] = [
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'name'     => $item['name'],
            'item'     => url_abs($item['url']),
        ];
    }
    return ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $elements];
}

function ld_organization(): array
{
    $app = arail_config()['app'];
    $data = [
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        'name'     => $app['name'],
        'url'      => url_abs('/'),
        'logo'     => url_abs('assets/img/arail-logo-exact-v9.png'),
    ];
    if (!empty($app['email'])) {
        $data['email'] = $app['email'];
    }
    if (!empty($app['phone'])) {
        $data['telephone'] = $app['phone'];
    }
    if (!empty($app['address'])) {
        $data['address'] = ['@type' => 'PostalAddress', 'streetAddress' => $app['address'], 'addressCountry' => $app['country_code']];
    }
    return $data;
}

function ld_website(): array
{
    return [
        '@context'        => 'https://schema.org',
        '@type'           => 'WebSite',
        'name'            => store_name(),
        'url'             => url_abs('/'),
        'potentialAction' => [
            '@type'       => 'SearchAction',
            'target'      => ['@type' => 'EntryPoint', 'urlTemplate' => url_abs('search/') . '?q={search_term_string}'],
            'query-input' => 'required name=search_term_string',
        ],
    ];
}

/** Product schema; rating/review blocks only added when real reviews exist. */
function ld_product(array $p, array $reviews = [], array $rating = []): array
{
    $images = array_values(array_filter(array_merge([$p['image'] ?? ''], $p['gallery'] ?? [])));
    $images = array_map(static fn($i) => url_abs($i), $images);
    if (!$images) {
        $images = [url_abs('assets/img/arail-logo-exact-v9.png')];
    }

    $price    = (float)($p['sale_price'] ?? $p['price'] ?? 0);
    $inStock  = (int)($p['stock'] ?? 0) > 0;

    $data = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Product',
        'name'        => $p['name'],
        'description' => seo_text($p['description'] ?? '', 500) ?: $p['name'],
        'image'       => $images,
        'sku'         => $p['sku'] ?? ('ARL-' . ($p['id'] ?? '')),
        'brand'       => ['@type' => 'Brand', 'name' => $p['brand'] ?? store_name()],
        'offers'      => [
            '@type'         => 'Offer',
            'url'           => url_abs('product/' . $p['slug'] . '/'),
            'priceCurrency' => currency_code(),
            'price'         => number_format($price, 2, '.', ''),
            'availability'  => $inStock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'itemCondition' => 'https://schema.org/NewCondition',
            'priceValidUntil' => date('Y-12-31'),
            'seller'        => ['@type' => 'Organization', 'name' => store_name()],
        ],
    ];

    if (!empty($p['mpn'])) {
        $data['mpn'] = $p['mpn'];
    }
    if (!empty($p['gtin'])) {
        $data['gtin'] = $p['gtin'];
    }

    // Only real, approved reviews are ever emitted.
    if ($reviews && !empty($rating['count'])) {
        $data['aggregateRating'] = [
            '@type'       => 'AggregateRating',
            'ratingValue' => number_format((float)$rating['average'], 1, '.', ''),
            'reviewCount' => (int)$rating['count'],
        ];
        $data['review'] = array_map(static function (array $r): array {
            return [
                '@type'         => 'Review',
                'author'        => ['@type' => 'Person', 'name' => $r['author_name']],
                'datePublished' => substr((string)$r['created_at'], 0, 10),
                'reviewBody'    => seo_text($r['body'], 900),
                'reviewRating'  => ['@type' => 'Rating', 'ratingValue' => (int)$r['rating'], 'bestRating' => 5],
            ];
        }, array_slice($reviews, 0, 10));
    }

    return $data;
}

/**
 * FAQPage schema. Pass the same [question, answer] pairs that are rendered
 * visibly on the page - Google requires the markup to match visible content.
 *
 * @param array<int,array{0:string,1:string}> $faqs
 */
function ld_faq(array $faqs): array
{
    $entities = [];
    foreach ($faqs as $faq) {
        $entities[] = [
            '@type'          => 'Question',
            'name'           => (string)$faq[0],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => (string)$faq[1]],
        ];
    }
    return [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => $entities,
    ];
}

/** CollectionPage + ItemList for category / shop listings. */
function ld_collection(string $name, string $description, string $canonical, array $products): array
{
    $items = [];
    foreach (array_values($products) as $i => $p) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'url'      => url_abs('product/' . $p['slug'] . '/'),
            'name'     => $p['name'],
        ];
    }
    return [
        '@context'    => 'https://schema.org',
        '@type'       => 'CollectionPage',
        'name'        => $name,
        'description' => seo_text($description, 300),
        'url'         => url_abs($canonical),
        'mainEntity'  => ['@type' => 'ItemList', 'itemListElement' => $items],
    ];
}
