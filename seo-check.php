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
 * SEO audit. Run before every deploy:
 *
 *   php seo-check.php                  # database + static checks
 *   php seo-check.php http://localhost:8080   # also crawls the running site
 *
 * Exit code is non-zero when errors are found (warnings do not fail the run).
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/seo.php';
require_once __DIR__ . '/includes/catalog.php';

$base = rtrim((string)($argv[1] ?? ''), '/');
$errors = [];
$warnings = [];
$notes = [];

echo "Arail SEO check\n";
echo str_repeat('=', 60) . "\n\n";

/* ---------------------------------------------------------------- *
 * 1. Titles / descriptions / H1 uniqueness for products & categories
 * ---------------------------------------------------------------- */
echo "Database metadata\n";

$products = db()->query('SELECT id, slug, name, meta_title, meta_description, short_description, description, image, gtin, mpn, is_active FROM products ORDER BY id')->fetchAll();
$titles = [];
$descs  = [];
$thin   = [];
$noimg  = [];
$nogtin = [];

foreach ($products as $p) {
    $title = (string)($p['meta_title'] ?? '') !== '' ? (string)$p['meta_title'] : $p['name'] . ' - Buy Online | ' . store_name();
    $desc  = trim((string)($p['meta_description'] ?? '') . (string)($p['short_description'] ?? '') . (string)($p['description'] ?? ''));

    $titles[strtolower($title)][] = $p['slug'];
    $descs[strtolower($desc)][]   = $p['slug'];

    if (mb_strlen(strip_tags($desc)) < 120) {
        $thin[] = $p['slug'];
    }
    if (trim((string)$p['image']) === '') {
        $noimg[] = $p['slug'];
    }
    if (trim((string)$p['gtin']) === '') {
        $nogtin[] = $p['slug'];
    }
}

foreach ($titles as $title => $slugs) {
    if (count($slugs) > 1) {
        $errors[] = 'Duplicate product title used by: ' . implode(', ', $slugs);
    }
}
foreach ($descs as $desc => $slugs) {
    if (count($slugs) > 1 && $desc !== '') {
        $errors[] = 'Duplicate product description used by: ' . implode(', ', $slugs);
    }
}

$cats = db()->query('SELECT slug, name, description, meta_description FROM categories')->fetchAll();
foreach ($cats as $c) {
    if (trim((string)$c['description']) === '' && trim((string)$c['meta_description']) === '') {
        $warnings[] = 'Category "' . $c['slug'] . '" has no description copy.';
    }
}

printf("  products checked ......... %d\n", count($products));
printf("  thin descriptions ....... %d\n", count($thin));
printf("  missing images .......... %d\n", count($noimg));
printf("  missing GTIN ............ %d\n", count($nogtin));
echo "\n";

/* ---------------------------------------------------------------- *
 * 2. Canonical base URL configured?
 * ---------------------------------------------------------------- */
echo "Configuration\n";
if (!has_configured_domain()) {
    $warnings[] = 'app.base_url is empty - set it to your real domain before going live (canonical/sitemap/feed URLs currently follow the request host).';
    echo "  base_url ................ NOT SET (local fallback in use)\n";
} else {
    echo "  base_url ................ " . base_url() . "\n";
}
echo "\n";

/* ---------------------------------------------------------------- *
 * 3. Static checks over the codebase
 * ---------------------------------------------------------------- */
echo "Static checks\n";
$root = __DIR__;
$phpFiles = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach ($it as $file) {
    $path = str_replace('\\', '/', $file->getPathname());
    if (str_contains($path, '/.work/') || str_contains($path, '/.backup-html/')) {
        continue;
    }
    if ($file->getExtension() === 'php') {
        $phpFiles[] = $path;
    }
}

$oldDomain = 0;
foreach ($phpFiles as $file) {
    $contents = file_get_contents($file);
    if (preg_match('~arailpharma\.(is|ts|com)~i', $contents)) {
        $oldDomain++;
        $errors[] = 'Old/unpurchased domain referenced in ' . str_replace($root . '/', '', $file);
    }
}
printf("  PHP files scanned ........ %d\n", count($phpFiles));
printf("  stale domain references .. %d\n", $oldDomain);

$requiredFiles = ['robots.php', 'sitemap.php', 'feed.php', '404.php', '.htaccess', 'index.php', 'shop.php', 'product.php', 'cart.php', 'checkout.php', 'account.php', 'about.php', 'shipping.php', 'returns.php', 'privacy.php', 'terms.php'];
$missing = [];
foreach ($requiredFiles as $req) {
    if (!is_file($root . '/' . $req)) {
        $missing[] = $req;
    }
}
if ($missing) {
    $errors[] = 'Missing required files: ' . implode(', ', $missing);
}
printf("  required files present ... %d/%d\n", count($requiredFiles) - count($missing), count($requiredFiles));
echo "\n";

/* ---------------------------------------------------------------- *
 * 4. Optional live crawl of the running site
 * ---------------------------------------------------------------- */
if ($base !== '') {
    echo "Live crawl of $base\n";

    // Herd/Valet serve local sites over HTTPS with a private CA that PHP's
    // HTTPS wrapper does not trust, so a local crawl would come back as status 0.
    // Verification is relaxed ONLY for local development hosts; a real domain is
    // always checked with full certificate verification.
    $isLocalHost = static function (string $url): bool {
        $host = strtolower((string)parse_url($url, PHP_URL_HOST));

        return $host === 'localhost' || $host === '127.0.0.1' || $host === '::1'
            || str_ends_with($host, '.test') || str_ends_with($host, '.local')
            || str_ends_with($host, '.localhost');
    };
    if (str_starts_with($base, 'https://') && $isLocalHost($base)) {
        echo "  (local host: TLS verification relaxed - PHP does not trust Herd's CA)\n";
    }

    $fetch = static function (string $url) use ($isLocalHost): array {
        $http = ['method' => 'GET', 'timeout' => 10, 'ignore_errors' => true, 'header' => "User-Agent: seo-check\r\n"];
        $ctx  = str_starts_with($url, 'https://') && $isLocalHost($url)
            ? stream_context_create(['http' => $http, 'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]])
            : stream_context_create(['http' => $http]);
        $body = @file_get_contents($url, false, $ctx);
        $headers = function_exists('http_get_last_response_headers')
            ? (http_get_last_response_headers() ?: [])
            : (array)($GLOBALS['http_response_header'] ?? []);
        $status = 0;
        if (isset($headers[0]) && preg_match('~\s(\d{3})\s~', (string)$headers[0], $m)) {
            $status = (int)$m[1];
        }
        return ['status' => $status, 'body' => (string)$body, 'headers' => $headers];
    };

    /** Only HTML documents are checked for titles, H1s and canonicals. */
    $isHtmlUrl = static function (string $url): bool {
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        return !preg_match('~(/sitemap|/robots|/feed)\.(xml|txt|tsv)$~i', $path);
    };

    $urls  = [$base . '/', $base . '/shop/'];
    foreach (array_slice($products, 0, 3) as $p) {
        $urls[] = $base . '/product/' . $p['slug'] . '/';
    }
    foreach ($cats as $c) {
        $urls[] = $base . '/category/' . $c['slug'] . '/';
    }
    $urls[] = $base . '/cart/';
    $urls[] = $base . '/checkout/';
    $urls[] = $base . '/account/';
    $urls[] = $base . '/sitemap.xml';
    $urls[] = $base . '/robots.txt';
    $urls[] = $base . '/this-page-should-not-exist-9999/';

    foreach ($urls as $url) {
        $res  = $fetch($url);
        $html = $isHtmlUrl($url);
        $h1  = $html && preg_match('~<h1[^>]*>(.*?)</h1>~si', $res['body'], $m) ? trim(strip_tags($m[1])) : '';
        $title = $html && preg_match('~<title>(.*?)</title>~si', $res['body'], $m2) ? trim($m2[1]) : '';
        $canon = $html && preg_match('~<link rel="canonical" href="([^"]+)"~i', $res['body'], $m3) ? $m3[1] : '';
        $robots = $html && preg_match('~<meta name="robots" content="([^"]+)"~i', $res['body'], $m4) ? $m4[1] : '';
        $jsonld = $html ? substr_count($res['body'], 'application/ld+json') : 0;

        $isNoindex = str_contains(strtolower($robots), 'noindex');
        printf("  %-3d %-58s %s\n", $res['status'], str_replace($base, '', $url) ?: '/', $html ? sprintf('h1:%d canon:%s jsonld:%d%s', $h1 ? 1 : 0, $canon ? 'y' : 'n', $jsonld, $isNoindex ? ' noindex' : '') : '(non-HTML)');

        if (str_contains($url, 'should-not-exist')) {
            if ($res['status'] !== 404) {
                $errors[] = 'Nonexistent URL returned HTTP ' . $res['status'] . ' (expected 404 - soft 404 risk): ' . $url;
            }
        } elseif ($res['status'] !== 200) {
            $errors[] = 'Expected 200 but got ' . $res['status'] . ' for ' . $url;
        }

        if (!$html) {
            continue;
        }

        // Canonical must be self-referencing (except deliberately noindex pages).
        if ($res['status'] === 200 && !$isNoindex && $canon !== '') {
            $expected = preg_replace('~^https?://~', '', $url);
            $got      = preg_replace('~^https?://~', '', $canon);
            if (rtrim($expected, '/') !== rtrim($got, '/')) {
                $warnings[] = 'Canonical mismatch on ' . str_replace($base, '', $url) . ': ' . $canon;
            }
        }

        if ($res['status'] === 200 && !$isNoindex && $title === '') {
            $errors[] = 'Missing <title> on ' . $url;
        }
        if ($res['status'] === 200 && !$isNoindex && $h1 === '') {
            $warnings[] = 'No <h1> on ' . $url;
        }
    }

    // Every URL listed in the sitemap (index + children) must return 200.
    $sitemap = $fetch($base . '/sitemap.xml');
    if (preg_match_all('~<loc>(.*?)</loc>~i', $sitemap['body'], $m)) {
        $childSitemaps = $m[1];
        $checked = 0;

        // Index entries (child sitemaps) themselves.
        foreach ($childSitemaps as $loc) {
            $res = $fetch($loc);
            if ($res['status'] !== 200) {
                $errors[] = 'Sitemap entry returned ' . $res['status'] . ': ' . $loc;
            }
        }

        // And every page URL inside each child sitemap.
        foreach ($childSitemaps as $loc) {
            $child = $fetch($loc);
            if (preg_match_all('~<loc>(.*?)</loc>~i', $child['body'], $cm)) {
                foreach ($cm[1] as $page) {
                    $checked++;
                    $res = $fetch($page);
                    if ($res['status'] !== 200) {
                        $errors[] = 'Sitemap page returned ' . $res['status'] . ': ' . $page;
                    }
                }
            }
        }
        echo "  sitemap index entries .... " . count($childSitemaps) . "\n";
        echo "  sitemap page urls checked  $checked\n";
    } else {
        $errors[] = 'Could not read /sitemap.xml as a sitemap index.';
    }

    // Internal links and assets referenced from the key templates must resolve.
    $crawlPages = ['/', '/shop/', '/reviews/', '/dosing/', '/stacks/', '/testing/', '/bitcoin/', '/contact/', '/about/', '/shipping/', '/returns/', '/privacy/', '/terms/'];
    if ($products) {
        $crawlPages[] = '/product/' . $products[0]['slug'] . '/';
    }
    if ($cats) {
        $crawlPages[] = '/category/' . $cats[0]['slug'] . '/';
    }

    $seenLinks = [];
    $brokenLinks = [];
    foreach ($crawlPages as $page) {
        $body = $fetch($base . $page)['body'];
        if (preg_match_all('~(?:href|src)="([^"]+)"~i', $body, $lm)) {
            foreach ($lm[1] as $href) {
                $href = html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if ($href === '' || str_starts_with($href, '#') || preg_match('~^(mailto|tel|data|javascript):~i', $href) || preg_match('~^https?://~i', $href)) {
                    continue;
                }
                $url = $base . '/' . ltrim($href, '/');
                if (isset($seenLinks[$url])) {
                    continue;
                }
                $seenLinks[$url] = true;
                $status = $fetch($url)['status'];
                if (!in_array($status, [200, 301, 302, 304], true)) {
                    $brokenLinks[] = $status . ' ' . $href . '  (linked from ' . $page . ')';
                }
            }
        }
    }
    foreach ($brokenLinks as $broken) {
        $errors[] = 'Broken internal link: ' . $broken;
    }
    echo "  internal links checked ... " . count($seenLinks) . "\n";
    echo "  broken internal links .... " . count($brokenLinks) . "\n";
    echo "\n";
} else {
    $notes[] = 'Pass a base URL (e.g. php seo-check.php http://127.0.0.1:8080) to also crawl the running site.';
}

/* ---------------------------------------------------------------- *
 * 5. Products needing attention (hand-off list)
 * ---------------------------------------------------------------- */
echo "Products needing attention\n";
echo "  thin descriptions: " . ($thin ? implode(', ', array_slice($thin, 0, 20)) . (count($thin) > 20 ? ' …' : '') : 'none') . "\n";
echo "  missing GTIN:      " . ($nogtin ? implode(', ', array_slice($nogtin, 0, 20)) . (count($nogtin) > 20 ? ' …' : '') : 'none') . "\n";
echo "  missing images:    " . ($noimg ? implode(', ', $noimg) : 'none') . "\n\n";

/* ---------------------------------------------------------------- *
 * Report
 * ---------------------------------------------------------------- */
foreach ($notes as $note) {
    echo "NOTE: $note\n";
}
foreach ($warnings as $warning) {
    echo "WARN: $warning\n";
}
foreach ($errors as $error) {
    echo "ERROR: $error\n";
}

echo "\n";
echo sprintf("Summary: %d error(s), %d warning(s)\n", count($errors), count($warnings));
exit($errors ? 1 : 0);
