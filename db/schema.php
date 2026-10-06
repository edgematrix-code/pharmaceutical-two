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
 * Schema for the "arail" database.
 * Keyed by table name; install.php executes each statement and
 * db/arail.sql is generated from the same definitions.
 */
return [
    'admin_users' => "CREATE TABLE IF NOT EXISTS admin_users (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        username VARCHAR(60) NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        display_name VARCHAR(120) NOT NULL DEFAULT '',
        role ENUM('admin','editor') NOT NULL DEFAULT 'admin',
        last_login_at DATETIME NULL DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_admin_users_username (username)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'categories' => "CREATE TABLE IF NOT EXISTS categories (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        slug VARCHAR(80) NOT NULL,
        name VARCHAR(120) NOT NULL,
        description TEXT NULL DEFAULT NULL,
        meta_title VARCHAR(160) NULL DEFAULT NULL,
        meta_description VARCHAR(320) NULL DEFAULT NULL,
        image VARCHAR(255) NULL DEFAULT NULL,
        sort_order INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_categories_slug (slug),
        KEY idx_categories_sort (sort_order)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'products' => "CREATE TABLE IF NOT EXISTS products (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        category_id INT UNSIGNED NULL DEFAULT NULL,
        slug VARCHAR(120) NOT NULL,
        name VARCHAR(200) NOT NULL,
        description TEXT NULL,
        short_description VARCHAR(500) NULL DEFAULT NULL,
        specs TEXT NULL DEFAULT NULL,
        meta_title VARCHAR(160) NULL DEFAULT NULL,
        meta_description VARCHAR(320) NULL DEFAULT NULL,
        sku VARCHAR(64) NULL DEFAULT NULL,
        brand VARCHAR(120) NULL DEFAULT NULL,
        gtin VARCHAR(32) NULL DEFAULT NULL,
        mpn VARCHAR(64) NULL DEFAULT NULL,
        price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        sale_price DECIMAL(10,2) NULL DEFAULT NULL,
        stock INT NOT NULL DEFAULT 0,
        image VARCHAR(255) NULL DEFAULT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        featured TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_products_slug (slug),
        KEY idx_products_category (category_id),
        KEY idx_products_active (is_active),
        KEY idx_products_featured (featured),
        KEY idx_products_price (price),
        CONSTRAINT fk_products_category FOREIGN KEY (category_id)
            REFERENCES categories (id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'orders' => "CREATE TABLE IF NOT EXISTS orders (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        order_number VARCHAR(24) NOT NULL,
        customer_id INT UNSIGNED NULL DEFAULT NULL,
        customer_name VARCHAR(150) NOT NULL,
        customer_email VARCHAR(190) NOT NULL,
        customer_phone VARCHAR(40) NULL DEFAULT NULL,
        shipping_address TEXT NULL,
        customer_notes TEXT NULL,
        admin_notes TEXT NULL,
        payment_method ENUM('bitcoin','ethereum','btcpaygf_default','cryptapi','bank','cash','other') NOT NULL DEFAULT 'bitcoin',
        payment_status ENUM('unpaid','pending','paid','refunded') NOT NULL DEFAULT 'unpaid',
        status ENUM('pending','confirmed','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
        subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        shipping DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_orders_number (order_number),
        KEY idx_orders_status (status),
        KEY idx_orders_payment_status (payment_status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'order_items' => "CREATE TABLE IF NOT EXISTS order_items (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        order_id INT UNSIGNED NOT NULL,
        product_id INT UNSIGNED NULL DEFAULT NULL,
        product_name VARCHAR(200) NOT NULL,
        unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        quantity INT NOT NULL DEFAULT 1,
        line_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        PRIMARY KEY (id),
        KEY idx_order_items_order (order_id),
        CONSTRAINT fk_order_items_order FOREIGN KEY (order_id)
            REFERENCES orders (id) ON DELETE CASCADE,
        CONSTRAINT fk_order_items_product FOREIGN KEY (product_id)
            REFERENCES products (id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'reviews' => "CREATE TABLE IF NOT EXISTS reviews (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        product_id INT UNSIGNED NULL DEFAULT NULL,
        author_name VARCHAR(120) NOT NULL,
        author_email VARCHAR(190) NULL DEFAULT NULL,
        rating TINYINT UNSIGNED NOT NULL DEFAULT 5,
        title VARCHAR(160) NULL DEFAULT NULL,
        body TEXT NOT NULL,
        forum_url VARCHAR(255) NULL DEFAULT NULL,
        photo VARCHAR(255) NULL DEFAULT NULL,
        status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_reviews_status (status),
        KEY idx_reviews_product (product_id),
        CONSTRAINT fk_reviews_product FOREIGN KEY (product_id)
            REFERENCES products (id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'customers' => "CREATE TABLE IF NOT EXISTS customers (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'messages' => "CREATE TABLE IF NOT EXISTS messages (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(120) NOT NULL,
        email VARCHAR(190) NOT NULL,
        subject VARCHAR(200) NULL DEFAULT NULL,
        body TEXT NOT NULL,
        is_read TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_messages_read (is_read)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'subscribers' => "CREATE TABLE IF NOT EXISTS subscribers (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        email VARCHAR(190) NOT NULL,
        status ENUM('subscribed','unsubscribed') NOT NULL DEFAULT 'subscribed',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_subscribers_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];
