<?php
declare(strict_types=1);

/**
 * Arail installer - creates the database, tables, seed catalogue and admin user.
 *
 * CLI:      php install.php [admin-password]
 * Browser:  http://localhost/<project>/install.php
 *
 * Safe to re-run: existing tables/data are kept.
 * Delete this file (or protect it) once the site is live.
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

$isCli = PHP_SAPI === 'cli';
$log   = [];

/*
 * Security guard: this installer creates a database, an admin user and seeds
 * the catalogue, so it must never be reachable anonymously on a live site.
 *   - CLI always works (php install.php).
 *   - Browser access requires ARAIL_ALLOW_INSTALL=1 in the environment.
 * Delete this file once the site is live.
 */
if (!$isCli && (getenv('ARAIL_ALLOW_INSTALL') ?: '') !== '1') {
    http_response_code(403);
    exit('Installer disabled. Run it from the command line, or set ARAIL_ALLOW_INSTALL=1 temporarily.');
}

function log_step(string $msg): void
{
    global $log;
    $log[] = $msg;
    if (PHP_SAPI === 'cli') {
        echo $msg . PHP_EOL;
    }
}

if (!$isCli) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title>Arail installer</title>'
       . '<style>body{font-family:system-ui,Segoe UI,sans-serif;background:#f4f7f9;color:#10222b;padding:2rem}'
       . 'main{max-width:680px;margin:auto;background:#fff;border:1px solid #dbe6ec;border-radius:14px;padding:2rem}'
       . 'li{margin:.35rem 0}.ok{color:#0a7d3c}.err{color:#b3261e}'
       . 'code{background:#eef4f7;padding:.15rem .4rem;border-radius:6px}</style></head><body><main>'
       . '<h1>Arail Pharmaceuticals installer</h1><ul>';
}

$adminPassword = ($isCli && isset($argv[1]) && $argv[1] !== '')
    ? (string)$argv[1]
    : arail_config()['admin']['default_password'];

try {
    $cfg  = arail_config();
    $name = $cfg['db']['name'];

    $server = db_server();
    $server->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    log_step('Database "' . $name . '" is ready.');

    $pdo    = db();
    $schema = require __DIR__ . '/db/schema.php';
    foreach ($schema as $table => $sql) {
        $pdo->exec($sql);
        log_step('Table "' . $table . '" OK.');
    }

    $seed = require __DIR__ . '/db/seed.php';

    /* Categories */
    $catMap = [];
    foreach ($pdo->query('SELECT id, slug FROM categories') as $row) {
        $catMap[(string)$row['slug']] = (int)$row['id'];
    }
    if (count($catMap) === 0) {
        $stmt = $pdo->prepare('INSERT INTO categories (slug, name, sort_order) VALUES (?, ?, ?)');
        foreach ($seed['categories'] as $c) {
            $stmt->execute([$c['slug'], $c['name'], (int)$c['sort_order']]);
            $catMap[$c['slug']] = (int)$pdo->lastInsertId();
        }
        log_step('Inserted ' . count($seed['categories']) . ' categories.');
    } else {
        log_step('Categories already present (' . count($catMap) . ').');
    }

    /* Products */
    $productCount = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
    if ($productCount === 0) {
        $stmt = $pdo->prepare(
            'INSERT INTO products (category_id, slug, name, description, price, stock, image, featured, is_active)
             VALUES (?, ?, ?, ?, ?, 100, ?, ?, 1)'
        );
        foreach ($seed['products'] as $p) {
            $stmt->execute([
                $catMap[$p['category']] ?? null,
                $p['slug'],
                $p['name'],
                $p['description'],
                $p['price'],
                $p['image'] !== '' ? $p['image'] : null,
                (int)$p['featured'],
            ]);
        }
        log_step('Inserted ' . count($seed['products']) . ' products.');
    } else {
        log_step('Products already present (' . $productCount . ').');
    }

    /* Admin account */
    $username = $cfg['admin']['default_user'];
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM admin_users WHERE username = ?');
    $stmt->execute([$username]);
    if ((int)$stmt->fetchColumn() === 0) {
        $stmt = $pdo->prepare(
            "INSERT INTO admin_users (username, password_hash, display_name, role) VALUES (?, ?, 'Administrator', 'admin')"
        );
        $stmt->execute([$username, password_hash($adminPassword, PASSWORD_DEFAULT)]);
        log_step('Admin user created: "' . $username . '" with password "' . $adminPassword . '".');
    } else {
        log_step('Admin user "' . $username . '" already exists (password unchanged).');
    }

    log_step('Installation complete. Delete install.php when you are done.');
    $ok = true;
} catch (Throwable $e) {
    log_step('ERROR: ' . $e->getMessage());
    log_step('Check the database credentials in includes/config.php (host/port/user/password/database name).');
    $ok = false;
}

if ($isCli) {
    exit($ok ? 0 : 1);
}

foreach ($log as $line) {
    $class = str_starts_with($line, 'ERROR') ? 'err' : 'ok';
    echo '<li class="' . $class . '">' . e($line) . '</li>';
}
echo '</ul><p>Next: <a href="admin/login.php">open the admin panel</a>.</p></main></body></html>';
