<?php
declare(strict_types=1);

/**
 * Single source of truth for URL routing.
 *
 * The site is a front-controller application: every request that is not a real
 * file is handled by index.php, which consults this map. That means routing
 * behaves identically on nginx (Herd), Apache and `php -S` - no server-specific
 * rewrite rules are required for the site to work.
 *
 *   static  : exact path (no slashes) -> handler file
 *   dynamic : first path segment      -> [handler file, query-string key]
 *   legacy  : old mirror URL          -> new path (301, no chains). Use the
 *             canonical trailing-slash form, or the redirect will loop.
 */
return [
    'static' => [
        ''            => 'home.php',
        'shop'        => 'shop.php',
        'search'      => 'search.php',
        'cart'        => 'cart.php',
        'checkout'    => 'checkout.php',
        'wishlist'    => 'wishlist.php',
        'account'     => 'account.php',
        'dosing'      => 'dosing.php',
        'stacks'      => 'stacks.php',
        'testing'     => 'testing.php',
        'bitcoin'     => 'bitcoin.php',
        'contact'     => 'contact.php',
        'reviews'     => 'reviews.php',
        'about'       => 'about.php',
        'shipping'    => 'shipping.php',
        'returns'     => 'returns.php',
        'privacy'     => 'privacy.php',
        'terms'       => 'terms.php',
        'sitemap.xml' => 'sitemap.php',
        'robots.txt'  => 'robots.php',
    ],

    'dynamic' => [
        'product'  => ['product.php', 'slug'],
        'category' => ['category.php', 'slug'],
        'order'    => ['order-confirmation.php', 'number'],
    ],

    'legacy' => [
        'index.php'    => '',
        'index.html'   => '',
        'shop.html'    => 'shop/',
        'stacks.html'  => 'stacks/',
        'dosing.html'  => 'dosing/',
        'review.html'  => 'reviews/',
        'bitcoin.html' => 'bitcoin/',
        'contact.html' => 'contact/',
        'testing.html' => 'testing/',
    ],
];
