<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$page_h1    = 'Returns & Refunds';
$page_intro = 'What to do if an order arrives damaged, incorrect or incomplete.';
$crumbs     = [['name' => 'Home', 'url' => ''], ['name' => 'Returns', 'url' => 'returns/']];

$sections = [
    [
        'h' => 'Damaged or incorrect items',
        'p' => [
            'If your order arrives damaged, or you received the wrong item, contact us within 7 days of delivery with your order number and photographs of the parcel and its contents. We will arrange a replacement or refund once we have reviewed the evidence.',
        ],
    ],
    [
        'h' => 'Change of mind',
        'p' => [
            'Because of the nature of the products we supply, we cannot accept returns of opened, used or refrigerated items. Unopened goods in their original, undamaged packaging may be returned by prior arrangement.',
            'TODO (store owner): confirm your exact change-of-mind window and any restocking terms.',
        ],
    ],
    [
        'h' => 'Refunds',
        'p' => [
            'Approved refunds are issued to the original payment method. Cryptocurrency refunds are returned to the sending wallet address; network fees are not refundable.',
        ],
    ],
    [
        'h' => 'How to start a return',
        'p' => [
            'Contact us with your order number and a description of the issue. Do not send anything back before we have confirmed the return with you.',
        ],
    ],
];

seo_set([
    'title'       => seo_title('Returns & Refund Policy | ' . store_name()),
    'description' => seo_text('Our returns and refund policy, including damaged or incorrect orders and how to start a return.'),
    'canonical'   => 'returns/',
    'active_nav'  => '',
    'json_ld'     => [ld_breadcrumbs($crumbs), ld_organization()],
]);

require __DIR__ . '/includes/layout/head.php';
require __DIR__ . '/includes/partials/content-page.php';
require __DIR__ . '/includes/layout/tail.php';
