<?php
declare(strict_types=1);

/**
 * Environment file loader.
 *
 * The whole application is configured through a single ".env" file in the
 * project root (see .env.example for the full list). Nothing else has to be
 * edited: every value in includes/config.php - database credentials, store
 * details, the minimum order, runtime flags - is read with getenv().
 *
 * PHP does not read ".env" files on its own, so this loader parses the file
 * once per request and publishes each entry to getenv(), $_ENV and $_SERVER,
 * which is what a framework's Dotenv package does. Variables that already
 * exist in the real process environment (set by the shell, Docker, Herd, ...)
 * are never overridden.
 *
 * Because PHP-FPM reuses its worker processes between requests - and Herd
 * serves every site from the same pool - the values published here are removed
 * again when the request ends, so one site's ".env" can never leak into
 * another. The next request simply loads the file again.
 *
 * Security: the file holds secrets, so it must not be downloadable. See the
 * "Protect sensitive files" section of .htaccess and LocalValetDriver.php.
 */

/**
 * Read an environment value, falling back when the variable is not set.
 *
 * An empty value ("KEY=") is returned as an empty string - only a genuinely
 * absent variable yields the default. Use this instead of `getenv($k) ?: $d`
 * when "" is a meaningful value (for example a blank database password).
 */
function env(string $key, ?string $default = null): ?string
{
    $value = getenv($key);

    return $value === false ? $default : $value;
}

/**
 * Absolute path of the ".env" file that will be used.
 *
 * Set ARAIL_ENV_FILE to point somewhere else (for example a file kept outside
 * the web root); otherwise the project root ".env" is used.
 */
function env_file_path(): string
{
    $explicit = getenv('ARAIL_ENV_FILE');
    if (is_string($explicit) && $explicit !== '') {
        return $explicit;
    }

    return dirname(__DIR__) . '/.env';
}

/**
 * Parse ".env" contents into key => value pairs.
 *
 * Supported syntax:
 *   KEY=value              plain value
 *   KEY="quoted value"     double quotes; \n \r \t \\ \" are unescaped
 *   KEY='quoted value'     single quotes; the value is taken literally
 *   export KEY=value       the leading "export " is ignored
 *   # comment              full-line comments and blank lines are skipped
 *   KEY=value # comment    trailing comments start at " #" on unquoted values
 *
 * Values are never expanded, so a literal "$" stays a "$".
 *
 * @return array<string, string>
 */
function env_parse(string $contents): array
{
    $vars  = [];
    $lines = preg_split('/\r\n|\r|\n/', $contents) ?: [];

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || $line[0] === ';') {
            continue;
        }
        if (str_starts_with($line, 'export ')) {
            $line = trim(substr($line, 7));
        }

        $pos = strpos($line, '=');
        if ($pos === false) {
            continue;
        }

        $key = trim(substr($line, 0, $pos));
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key)) {
            continue;   // not a usable variable name; ignore the line
        }

        $value  = trim(substr($line, $pos + 1));
        $length = strlen($value);

        if ($length >= 2 && $value[0] === '"' && $value[$length - 1] === '"') {
            $value = strtr(substr($value, 1, -1), [
                '\\n'  => "\n",
                '\\r'  => "\r",
                '\\t'  => "\t",
                '\\"'  => '"',
                '\\\\' => '\\',
            ]);
        } elseif ($length >= 2 && $value[0] === "'" && $value[$length - 1] === "'") {
            $value = substr($value, 1, -1);
        } else {
            // Unquoted: drop a trailing comment and any surrounding space.
            $value = trim(preg_replace('/\s+#.*$/', '', $value) ?? $value);
        }

        $vars[$key] = $value;
    }

    return $vars;
}

/**
 * Load the ".env" file once per request. Safe to call from anywhere; later
 * calls are no-ops. Missing file is not an error - the defaults in
 * includes/config.php then apply.
 */
function arail_load_env(): void
{
    static $loaded = false;
    if ($loaded) {
        return;
    }
    $loaded = true;

    $path = env_file_path();
    if (!is_file($path) || !is_readable($path)) {
        return;
    }

    $contents = file_get_contents($path);
    if ($contents === false) {
        return;
    }

    $vars = env_parse($contents);
    if ($vars === []) {
        return;
    }

    $applied = [];
    foreach ($vars as $key => $value) {
        // A real environment variable always wins over the file.
        if (getenv($key) !== false) {
            continue;
        }

        putenv($key . '=' . $value);
        $_ENV[$key]    = $value;
        $_SERVER[$key] = $value;
        $applied[]     = $key;
    }

    if ($applied === []) {
        return;
    }

    // Shared workers: publish nothing beyond the current request.
    register_shutdown_function(static function () use ($applied): void {
        foreach ($applied as $key) {
            putenv($key);
            unset($_ENV[$key], $_SERVER[$key]);
        }
    });
}
