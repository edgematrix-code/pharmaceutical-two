<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$page_h1    = 'Shipping';
$page_intro = 'How your order is packed, dispatched and tracked.';
$crumbs     = [['name' => 'Home', 'url' => ''], ['name' => 'Shipping', 'url' => 'shipping/']];

$flat = (float)arail_config()['app']['shipping_flat'];

$sections = [
    [
        'h' => 'Dispatch',
        'p' => [
            'Orders are processed in the order they are received and dispatched once payment has been confirmed. You will receive tracking details by email when your parcel leaves our facility.',
            'TODO (store owner): state your realistic dispatch window (for example "within 1-2 business days") here.',
        ],
    ],
    [
        'h' => 'Costs',
        'p' => [
            $flat > 0
                ? 'A flat shipping charge of ' . format_money($flat) . ' applies to every order.'
                : 'Shipping is included in the listed price — there is no separate shipping charge at checkout.',
        ],
    ],
    [
        'h' => 'Packaging',
        'p' => [
            'All parcels are packed discreetly in plain outer packaging with no product names, logos or descriptions on the outside.',
        ],
    ],
    [
        'h' => 'Tracking',
        'p' => [
            'Tracking is provided for every tracked service. If your tracking has not updated within a reasonable window, contact us with your order number and we will investigate with the carrier.',
        ],
    ],
];

seo_set([
    'title'       => seo_title('Shipping & Delivery Information | ' . store_name()),
    'description' => seo_text('Discreet packaging, dispatch times and tracking information for every ' . store_name() . ' order.'),
    'canonical'   => 'shipping/',
    'active_nav'  => '',
    'json_ld'     => [ld_breadcrumbs($crumbs), ld_organization()],
]);

require __DIR__ . '/includes/layout/head.php';
require __DIR__ . '/includes/partials/content-page.php';
require __DIR__ . '/includes/layout/tail.php';
