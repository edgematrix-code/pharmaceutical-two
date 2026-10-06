<?php
/**
 * Arail Pharmaceuticals - backend configuration.
 *
 * EVERY value below comes from the environment, so this file normally never
 * needs editing. Copy ".env.example" to ".env" and set the values there:
 *
 *   DB_HOST  DB_PORT  DB_USERNAME  DB_PASSWORD  DB_NAME   database connection
 *   APP_*                                                 storefront settings
 *   ADMIN_*                                               installer defaults
 *
 * includes/env.php parses that file and publishes each entry to getenv(); it is
 * started below (and from includes/bootstrap.php) before any value is read.
 * A real environment variable of the same name takes precedence over the file.
 *
 * IMPORTANT - domain setup
 * ------------------------
 * The live domain is arailpharma.si, so APP_BASE_URL is set to
 * https://arailpharma.si in ".env" (and documented in .env.example). Nothing
 * in the codebase hard-codes a host name: every absolute URL (canonical tags,
 * Open Graph, sitemaps, the Merchant Center feed) is generated from this value.
 *
 *   - Leave it empty  -> URLs are built from the current request host (local
 *                        development only, strictly validated).
 *   - Set it, e.g. in .env:
 *       APP_BASE_URL=https://arailpharma.si
 *
 * Never put an interim or third-party domain here.
 */
require_once __DIR__ . '/env.php';
arail_load_env();

return [
    'db' => [
        'host'     => env('DB_HOST') ?: '127.0.0.1',
        'port'     => env('DB_PORT') ?: '3306',
        'name'     => env('DB_NAME') ?: 'arail',
        'user'     => env('DB_USERNAME') ?: 'root',
        // An explicitly empty DB_PASSWORD is respected; the fallback only
        // applies when the variable is absent altogether.
        'password' => env('DB_PASSWORD', 'wiztech'),
        'charset'  => env('DB_CHARSET') ?: 'utf8mb4',
    ],
    'app' => [
        'name'          => env('APP_NAME') ?: 'Arail Pharmaceuticals',
        'tagline'       => env('APP_TAGLINE') ?: 'Pure Anabolics. Zero compromises.',
        'email'         => env('APP_EMAIL') ?: 'lemon@arailpharma.si',
        'phone'         => env('APP_PHONE') ?? '',
        'address'       => env('APP_ADDRESS') ?? '',
        'currency'      => env('APP_CURRENCY') ?: '$',
        'currency_code' => env('APP_CURRENCY_CODE') ?: 'USD',
        'country_code'  => env('APP_COUNTRY_CODE') ?: 'US',
        // Crypto wallet addresses shown as the Bitcoin / Ethereum payment
        // options at checkout and on the order confirmation. These are public
        // deposit addresses, not secrets, but they stay configurable so a new
        // wallet can be adopted without touching any template.
        'btc_address'   => env('APP_BTC_ADDRESS') ?: 'bc1q9take6d97hd9wthp7g2g3kttrly0k4sx84w2c8',
        'eth_address'   => env('APP_ETH_ADDRESS') ?: '0x40839B08ac24B45F91f15E7B681467e35EAF156A',
        'shipping_flat' => (float)(env('APP_SHIPPING_FLAT', '0.00')),
        // Minimum order value. Orders below this cannot be placed (see the
        // "Is there a minimum order?" FAQ on contact.php). The cart shows how
        // much is still missing and the checkout submit stays disabled until
        // the merchandise subtotal reaches it. Enforced on the client and,
        // authoritatively, in api/order.php.
        'min_order'     => (float)(env('APP_MIN_ORDER', '300.00')),
        // Absolute path to the uploads directory; defaults to ./uploads next to
        // the project root when APP_UPLOADS_DIR is empty.
        'uploads_dir'   => env('APP_UPLOADS_DIR') ?: (__DIR__ . '/../uploads'),
        'uploads_url'   => env('APP_UPLOADS_URL') ?: 'uploads',
        'max_upload_mb' => (int)(env('APP_MAX_UPLOAD_MB', '3')),
        // Empty until the real domain is registered. See the note above.
        'base_url'      => env('APP_BASE_URL') ?? '',
        // Analytics / verification placeholders (fill in when you have them).
        'ga4_id'        => env('APP_GA4_ID') ?? '',
        'gsc_token'     => env('APP_GSC_TOKEN') ?? '',
    ],
    'admin' => [
        // Used by install.php only (the real account lives in the database).
        'default_user'     => env('ADMIN_DEFAULT_USER') ?: 'admin',
        'default_password' => env('ADMIN_DEFAULT_PASSWORD') ?: 'Arail@2026',
    ],
];
