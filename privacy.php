<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$page_h1    = 'Privacy Policy';
$page_intro = 'What personal data we collect, why we collect it, and how long we keep it.';
$crumbs     = [['name' => 'Home', 'url' => ''], ['name' => 'Privacy', 'url' => 'privacy/']];

$sections = [
    [
        'h' => 'Data we collect',
        'p' => [
            'We collect the details you provide when you place an order, create an account, subscribe to our newsletter or contact us: your name, email address, phone number, shipping address and the contents of your messages.',
            'We do not store card numbers. Cryptocurrency payments are verified on-chain and we never request your private keys or seed phrase.',
        ],
    ],
    [
        'h' => 'Why we use it',
        'p' => [
            'Your data is used to process and deliver your order, respond to enquiries, meet our record-keeping obligations and — where you have opted in — send occasional product updates.',
        ],
    ],
    [
        'h' => 'Cookies',
        'p' => [
            'We use a small number of functional cookies and browser storage to keep your cart and session working. Analytics, if enabled, is configured to load only after the page has finished loading.',
        ],
    ],
    [
        'h' => 'Your rights',
        'p' => [
            'You can ask us to access, correct or delete the personal data we hold about you, and you can unsubscribe from marketing at any time using the link in any email or by contacting us.',
            'TODO (store owner): name your legal entity, contact address and data retention period, and confirm the jurisdictions you serve.',
        ],
    ],
];

seo_set([
    'title'       => seo_title('Privacy Policy | ' . store_name()),
    'description' => seo_text('How ' . store_name() . ' collects, uses and protects your personal data.'),
    'canonical'   => 'privacy/',
    'active_nav'  => '',
    'json_ld'     => [ld_breadcrumbs($crumbs), ld_organization()],
]);

require __DIR__ . '/includes/layout/head.php';
require __DIR__ . '/includes/partials/content-page.php';
require __DIR__ . '/includes/layout/tail.php';
