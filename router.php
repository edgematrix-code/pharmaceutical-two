<?php
/**
 * Dev router for the PHP built-in server:
 *
 *   php -S 127.0.0.1:8099 router.php
 *
 * Real files (assets, api endpoints, admin) are served untouched; everything
 * else is handed to the front controller, so `php -S`, nginx and Apache all
 * resolve clean URLs through exactly the same code (includes/routes.php).
 */
declare(strict_types=1);

$uri = (string)(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');

// Don't let the router itself be requested over HTTP.
if (preg_match('~^/router\.php$~i', $uri)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Forbidden');
}

// Dotfiles (".env", ".git", ...) must never be served as static files: hand
// them to the front controller, which answers 403 exactly like the live
// server. ".well-known" is left alone so TLS/ACME challenges keep working.
$isDotfile = str_starts_with($uri, '/.')
    && !str_starts_with($uri, '/.well-known/')
    && $uri !== '/.well-known';

// Serve real files/directories exactly as stored.
$candidate = __DIR__ . '/' . ltrim(rawurldecode($uri), '/');
if ($uri !== '/' && !$isDotfile && (is_file($candidate) || is_dir($candidate))) {
    return false;
}

require __DIR__ . '/index.php';
