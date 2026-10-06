<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$page_h1    = 'About ' . store_name();
$page_intro = 'Who we are, how we produce, and why independent laboratory testing is at the centre of everything we sell.';
$crumbs     = [['name' => 'Home', 'url' => ''], ['name' => 'About', 'url' => 'about/']];

$sections = [
    [
        'h' => 'What we do',
        'p' => [
            store_name() . ' supplies anabolics, peptides and growth hormone for laboratory research. We control our own production and publish the results of independent third-party testing for each batch we release.',
        ],
    ],
    [
        'h' => 'Independent testing',
        'p' => [
            'Every batch is sent to an independent laboratory for purity and potency analysis before it is offered for sale. The certificates of analysis are the source of truth for what is in each vial, not marketing claims.',
            'You can review the published results on our testing page.',
        ],
    ],
    [
        'h' => 'How we ship',
        'p' => [
            'Orders are packed discreetly and dispatched with tracking. Packaging is plain and gives no indication of the contents.',
            'See the shipping page for the current process, and the returns page for what to do if something is not right with your order.',
        ],
    ],
    [
        'h' => 'Research use statement',
        'p' => [
            'All products listed on this website are supplied strictly for laboratory research use by qualified professionals. They are not medicines, foods, or consumer products, and nothing on this site is medical advice.',
        ],
    ],
];

seo_set([
    'title'       => seo_title('About Us - Lab-Tested Supplier | ' . store_name()),
    'description' => seo_text('Learn about ' . store_name() . ': in-house production, independent batch testing and discreet worldwide shipping.'),
    'canonical'   => 'about/',
    'active_nav'  => '',
    'json_ld'     => [ld_breadcrumbs($crumbs), ld_organization()],
]);

require __DIR__ . '/includes/layout/head.php';
require __DIR__ . '/includes/partials/content-page.php';
require __DIR__ . '/includes/layout/tail.php';
