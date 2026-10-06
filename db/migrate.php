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
 * Idempotent migration for existing installations.
 *
 * CLI:  php db/migrate.php
 *
 * Safe to re-run: every statement is guarded by an existence check.
 */
require_once __DIR__ . '/../includes/db.php';

$pdo = db();
$log = [];

function column_exists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.columns
         WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?'
    );
    $stmt->execute([$table, $column]);
    return (int)$stmt->fetchColumn() > 0;
}

function index_exists(PDO $pdo, string $table, string $index): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.statistics
         WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?'
    );
    $stmt->execute([$table, $index]);
    return (int)$stmt->fetchColumn() > 0;
}

function add_column(PDO $pdo, string $table, string $column, string $definition, array &$log): void
{
    if (column_exists($pdo, $table, $column)) {
        $log[] = "skip  $table.$column (exists)";
        return;
    }
    $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
    $log[] = "added $table.$column";
}

function add_index(PDO $pdo, string $table, string $index, string $columns, array &$log): void
{
    if (index_exists($pdo, $table, $index)) {
        $log[] = "skip  $table.$index (exists)";
        return;
    }
    $pdo->exec("ALTER TABLE `$table` ADD INDEX `$index` ($columns)");
    $log[] = "added index $table.$index";
}

/* ---- products: SEO + feed fields ---- */
add_column($pdo, 'products', 'meta_title', 'VARCHAR(160) NULL DEFAULT NULL', $log);
add_column($pdo, 'products', 'meta_description', 'VARCHAR(320) NULL DEFAULT NULL', $log);
add_column($pdo, 'products', 'short_description', 'VARCHAR(500) NULL DEFAULT NULL', $log);
add_column($pdo, 'products', 'sku', 'VARCHAR(64) NULL DEFAULT NULL', $log);
add_column($pdo, 'products', 'brand', 'VARCHAR(120) NULL DEFAULT NULL', $log);
add_column($pdo, 'products', 'gtin', 'VARCHAR(32) NULL DEFAULT NULL', $log);
add_column($pdo, 'products', 'mpn', 'VARCHAR(64) NULL DEFAULT NULL', $log);
add_column($pdo, 'products', 'specs', 'TEXT NULL DEFAULT NULL', $log);

/* ---- categories: SEO fields ---- */
add_column($pdo, 'categories', 'meta_title', 'VARCHAR(160) NULL DEFAULT NULL', $log);
add_column($pdo, 'categories', 'meta_description', 'VARCHAR(320) NULL DEFAULT NULL', $log);
add_column($pdo, 'categories', 'description', 'TEXT NULL DEFAULT NULL', $log);
add_column($pdo, 'categories', 'image', 'VARCHAR(255) NULL DEFAULT NULL', $log);

/* ---- indexes that keep category/product queries fast ---- */
add_index($pdo, 'products', 'idx_products_active', 'is_active', $log);
add_index($pdo, 'products', 'idx_products_featured', 'featured', $log);
add_index($pdo, 'products', 'idx_products_price', 'price', $log);
add_index($pdo, 'categories', 'idx_categories_sort', 'sort_order', $log);

/* ---- customer accounts (storefront login) ---- */
$pdo->exec(
    "CREATE TABLE IF NOT EXISTS customers (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        email VARCHAR(190) NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        first_name VARCHAR(80) NOT NULL DEFAULT '',
        last_name VARCHAR(80) NOT NULL DEFAULT '',
        phone VARCHAR(40) NULL DEFAULT NULL,
        address TEXT NULL DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        last_login_at DATETIME NULL DEFAULT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY uq_customers_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);
$log[] = 'ensured table customers';

/* Orders gain an optional customer link. */
add_column($pdo, 'orders', 'customer_id', 'INT UNSIGNED NULL DEFAULT NULL', $log);

/*
 * Checkout offers the crypto wallets the storefront actually uses (Bitcoin
 * and Ethereum) alongside the legacy provider values, so the payment_method
 * enum has to accept them. The old values stay in the enum: orders placed
 * before the wallet options keep their original method and a readable label.
 */
$paymentEnum = "enum('bitcoin','ethereum','btcpaygf_default','cryptapi','bank','cash','other')";
$stmt = $pdo->prepare(
    'SELECT COLUMN_TYPE FROM information_schema.columns
     WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?'
);
$stmt->execute(['orders', 'payment_method']);
$current = (string)$stmt->fetchColumn();
if ($current === '') {
    $log[] = 'skip  orders.payment_method (no orders table)';
} elseif (strcasecmp($current, $paymentEnum) === 0) {
    $log[] = 'skip  orders.payment_method (already current)';
} else {
    $pdo->exec("ALTER TABLE `orders` MODIFY COLUMN `payment_method` $paymentEnum NOT NULL DEFAULT 'bitcoin'");
    $log[] = 'extended orders.payment_method enum';
}

$log[] = 'migration complete';
echo implode(PHP_EOL, $log) . PHP_EOL;
