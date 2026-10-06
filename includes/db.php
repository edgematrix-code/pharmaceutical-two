<?php
declare(strict_types=1);

/**
 * Shared PDO connection helpers.
 */

function arail_config(): array
{
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/config.php';
    }
    return $config;
}

/**
 * PDO connection to the "arail" database.
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $cfg = arail_config()['db'];
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $cfg['host'],
            $cfg['port'],
            $cfg['name'],
            $cfg['charset']
        );
        $pdo = new PDO($dsn, $cfg['user'], $cfg['password'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

/**
 * PDO connection to the MySQL server without selecting a database
 * (used by install.php to create the "arail" database if needed).
 */
function db_server(): PDO
{
    $cfg = arail_config()['db'];
    $dsn = sprintf('mysql:host=%s;port=%s;charset=%s', $cfg['host'], $cfg['port'], $cfg['charset']);
    return new PDO($dsn, $cfg['user'], $cfg['password'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

/**
 * Human readable database error text.
 */
function db_error_message(Throwable $e): string
{
    return $e->getMessage();
}
