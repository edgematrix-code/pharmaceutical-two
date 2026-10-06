<?php
/**
 * Arail Pharmaceuticals - backend configuration.
 *
 * Edit the "db" credentials below (or set the ARAIL_DB_* environment
 * variables) so they match your MySQL server.
 *
 * IMPORTANT - domain setup
 * ------------------------
 * The production domain has NOT been purchased yet, so "base_url" is left
 * empty on purpose. Nothing in the codebase hard-codes a host name any more:
 * every absolute URL (canonical tags, Open Graph, sitemaps, the Merchant
 * Center feed) is generated from this value.
 *
 *   - Leave it empty  -> URLs are built from the current request host (local
 *                        development only, strictly validated).
 *   - Set it once the domain is live, e.g.:
 *       'base_url' => 'https://your-real-domain.com',
 *     or via the environment variable ARAIL_BASE_URL.
 *
 * Never put an interim or third-party domain here.
 */
return [
    'db' => [
        'host'     => getenv('ARAIL_DB_HOST') ?: '127.0.0.1',
        'port'     => getenv('ARAIL_DB_PORT') ?: '3306',
        'name'     => getenv('ARAIL_DB_NAME') ?: 'arail',
        'user'     => getenv('ARAIL_DB_USER') ?: 'root',
        'password' => getenv('ARAIL_DB_PASS') !== false ? getenv('ARAIL_DB_PASS') : 'wiztech',
        'charset'  => 'utf8mb4',
    ],
    'app' => [
        'name'          => 'Arail Pharmaceuticals',
        'tagline'       => 'Pure Anabolics. Zero compromises.',
        'email'         => 'support@example.com',
        'phone'         => '',
        'address'       => '',
        'currency'      => '$',
        'currency_code' => 'USD',
        'country_code'  => 'US',
        'shipping_flat' => 0.00,
        // Minimum order value. Orders below this cannot be placed (see the
        // "Is there a minimum order?" FAQ on contact.php). The cart shows how
        // much is still missing and the checkout submit stays disabled until
        // the merchandise subtotal reaches it. Enforced on the client and,
        // authoritatively, in api/order.php.
        'min_order'     => 300.00,
        'uploads_dir'   => __DIR__ . '/../uploads',
        'uploads_url'   => 'uploads',
        'max_upload_mb' => 3,
        // Empty until the real domain is registered. See the note above.
        'base_url'      => getenv('ARAIL_BASE_URL') ?: '',
        // Analytics / verification placeholders (fill in when you have them).
        'ga4_id'        => getenv('ARAIL_GA4_ID') ?: '',
        'gsc_token'     => getenv('ARAIL_GSC_TOKEN') ?: '',
    ],
    'admin' => [
        // Used by install.php only (the real account lives in the database).
        'default_user'     => 'admin',
        'default_password' => 'Arail@2026',
    ],
];
