<?php
declare(strict_types=1);

/**
 * Front controller.
 *
 * Every request that is not an existing file or directory is handled here, so
 * the clean URLs work on nginx (which ignores .htaccess), on Apache and under
 * `php -S` with router.php. The actual URL map lives in includes/routes.php.
 *
 *   /                      -> home.php
 *   /shop/                 -> shop.php
 *   /category/<slug>/      -> category.php
 *   /product/<slug>/       -> product.php
 *   /order/<number>/       -> order-confirmation.php
 *   /sitemap.xml           -> sitemap.php
 *   /robots.txt            -> robots.php
 */

require_once __DIR__ . '/includes/bootstrap.php';

$routes = require __DIR__ . '/includes/routes.php';

/* ------------------------------------------------------------------ *
 * Normalise the requested path.
 * ------------------------------------------------------------------ */
$uri  = (string)($_SERVER['REQUEST_URI'] ?? '/');
$path = (string)(parse_url($uri, PHP_URL_PATH) ?: '/');
$path = '/' . ltrim(rawurldecode($path), '/');

// Strip a leading base path when the app is installed in a sub-directory.
$base = base_path();
if ($base !== '' && str_starts_with($path, $base . '/')) {
    $path = substr($path, strlen($base));
}

// Collapse duplicate slashes; keep a single trailing slash off for matching.
$path = preg_replace('~/{2,}~', '/', $path) ?? '/';
$hadTrailingSlash = $path !== '/' && str_ends_with($path, '/');
$key = trim($path, '/');

/* ------------------------------------------------------------------ *
 * 1. Legacy mirror URLs -> permanent redirect (never a chain).
 * ------------------------------------------------------------------ */
$legacyKey = strtolower($key);
if (isset($routes['legacy'][$legacyKey])) {
    header('Location: ' . url_path($routes['legacy'][$legacyKey]), true, 301);
    exit;
}

/* ------------------------------------------------------------------ *
 * 2. Block non-public paths. A web server that does not read .htaccess
 *    would otherwise serve these directly; the CLI-only scripts guard
 *    themselves too (see db/ and tools/).
 * ------------------------------------------------------------------ */
if (preg_match('~^\.?(backup-html|work)(/|$)~i', $key)
    || preg_match('~^(db|tools)(/|$)~i', $key)
    || in_array(strtolower($key), ['router.php', 'seo-check.php'], true)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Forbidden');
}

$segments  = $key === '' ? [] : explode('/', $key);
$isFileLike = str_contains($key, '.');

/* ------------------------------------------------------------------ *
 * 3. Exact routes.
 *
 * "/" is served as-is. A single-segment route is served only with its
 * canonical trailing slash (or when the key is file-like, e.g.
 * "sitemap.xml"); the un-slashed form falls through to step 5 so it
 * gets a 301 instead of quietly serving a duplicate URL.
 * ------------------------------------------------------------------ */
if (isset($routes['static'][$key])
    && ($key === '' || $hadTrailingSlash || $isFileLike)) {
    require __DIR__ . '/' . $routes['static'][$key];
    exit;
}

/* ------------------------------------------------------------------ *
 * 4. Dynamic routes: /<segment>/<value>/
 * ------------------------------------------------------------------ */
if ($hadTrailingSlash && count($segments) === 2 && isset($routes['dynamic'][$segments[0]])) {
    [$handler, $param] = $routes['dynamic'][$segments[0]];
    $value = $segments[1];

    // Reject anything that could not be a valid slug / order number.
    if ($param === 'number') {
        if (!preg_match('~^[A-Za-z]{2,4}-[0-9]{8}-[A-Za-z0-9]{4,8}$~', $value)) {
            http_response_code(404);
            require __DIR__ . '/404.php';
            exit;
        }
    } elseif (!preg_match('~^[a-z0-9][a-z0-9\-]{0,119}$~i', $value)) {
        http_response_code(404);
        require __DIR__ . '/404.php';
        exit;
    }

    $_GET[$param] = $value;
    require __DIR__ . '/' . $handler;
    exit;
}

/* ------------------------------------------------------------------ *
 * 5. Canonical trailing slash: /shop -> 301 -> /shop/
 * ------------------------------------------------------------------ */
if (!$hadTrailingSlash && $key !== '' && !$isFileLike) {
    // Only redirect when the path actually resolves to something we serve.
    $resolves = (count($segments) === 1 && isset($routes['static'][$key]))
        || (count($segments) === 2 && isset($routes['dynamic'][$segments[0]]));
    if ($resolves) {
        $query = (string)(parse_url($uri, PHP_URL_QUERY) ?? '');
        header('Location: ' . url_path($key . '/') . ($query !== '' ? '?' . $query : ''), true, 301);
        exit;
    }
}

/* ------------------------------------------------------------------ *
 * 6. Nothing matched.
 * ------------------------------------------------------------------ */
http_response_code(404);
require __DIR__ . '/404.php';
