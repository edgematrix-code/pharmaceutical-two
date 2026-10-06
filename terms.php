<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$page_h1    = 'Terms & Conditions';
$page_intro = 'The terms that apply when you buy from or use this website.';
$crumbs     = [['name' => 'Home', 'url' => ''], ['name' => 'Terms', 'url' => 'terms/']];

$sections = [
    [
        'h' => 'Eligibility',
        'p' => [
            'You must be at least 18 years old to order from this website. By placing an order you confirm that you meet this requirement and that you are purchasing for lawful laboratory research purposes.',
        ],
    ],
    [
        'h' => 'Orders and pricing',
        'p' => [
            'All prices are shown in ' . currency_code() . '. The final total, including any shipping, is confirmed on our server when you place your order, and stock is reserved once payment is received.',
            'We may cancel and refund an order if a pricing error or a stock error occurs, or if an order cannot lawfully be shipped to your address.',
        ],
    ],
    [
        'h' => 'Intellectual property',
        'p' => [
            'All content on this website, including text, images and product photography, is owned by ' . store_name() . ' or its licensors and may not be reproduced without permission.',
        ],
    ],
    [
        'h' => 'Liability',
        'p' => [
            'Products are supplied strictly for laboratory research use. We are not liable for any use outside that purpose. Nothing on this site constitutes medical advice.',
            'TODO (store owner): insert your governing law, registered entity name and company contact address here.',
        ],
    ],
];

seo_set([
    'title'       => seo_title('Terms & Conditions | ' . store_name()),
    'description' => seo_text('The terms and conditions that apply to orders placed with ' . store_name() . '.'),
    'canonical'   => 'terms/',
    'active_nav'  => '',
    'json_ld'     => [ld_breadcrumbs($crumbs), ld_organization()],
]);

require __DIR__ . '/includes/layout/head.php';
require __DIR__ . '/includes/partials/content-page.php';
require __DIR__ . '/includes/layout/tail.php';
