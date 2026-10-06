<?php
/**
 * Local Valet / Herd site driver.
 *
 * Herd (like Laravel Valet) serves a request by running resources/valet/
 * server.php, and that script hands any file that exists on disk straight back
 * to nginx as a static file - including dotfiles. That would make ".env" and
 * ".git/" publicly downloadable, because nginx only denies ".ht*".
 *
 * Valet lets a site override that behaviour with a "LocalValetDriver.php" in the
 * project root: it is loaded first, before the built-in drivers. This one
 * refuses to treat dotfiles as static files and instead sends them to the app's
 * own front controller (index.php), which answers 403 - exactly what happens on
 * a properly configured production server.
 *
 * This file does nothing on Apache or on a normal nginx/PHP-FPM host; there the
 * equivalent protection lives in .htaccess and the server block.
 */
class LocalValetDriver extends \Valet\Drivers\BasicValetDriver
{
    /**
     * Determine if the incoming request is for a static file.
     *
     * @return string|false
     */
    public function isStaticFile(string $sitePath, string $siteName, string $uri)
    {
        if ($this->isProtectedRequest($uri)) {
            return false;
        }

        return parent::isStaticFile($sitePath, $siteName, $uri);
    }

    /**
     * Get the fully resolved path to the application's front controller.
     */
    public function frontControllerPath(string $sitePath, string $siteName, string $uri): ?string
    {
        if ($this->isProtectedRequest($uri)) {
            // Let the app decide (index.php answers 403 for these paths).
            $_SERVER['SCRIPT_FILENAME'] = $sitePath . '/index.php';
            $_SERVER['SCRIPT_NAME']     = '/index.php';
            $_SERVER['DOCUMENT_ROOT']   = $sitePath;

            return $sitePath . '/index.php';
        }

        return parent::frontControllerPath($sitePath, $siteName, $uri);
    }

    /**
     * Paths that must never be executed or downloaded.
     *
     *   - any dot-prefixed segment (".env", ".git/config", "assets/.cache" ...);
     *     ".well-known" stays public for TLS/ACME challenges.
     *   - this driver file itself: server.php has already required it, so
     *     letting it also run as a page would be a fatal class redeclaration.
     */
    private function isProtectedRequest(string $uri): bool
    {
        $path = (string)(parse_url($uri, PHP_URL_PATH) ?? $uri);
        if ($path === '') {
            return false;
        }

        if (preg_match('~/LocalValetDriver\.php$~i', $path)) {
            return true;
        }

        if (!preg_match('~(^|/)\.~', $path)) {
            return false;
        }

        return !preg_match('~^\.well-known(/|$)~i', ltrim($path, '/'));
    }
}
