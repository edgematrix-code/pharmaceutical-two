<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

seo_set([
    'title'       => seo_title('Recommended Stacks - Build a Cycle | Arail Pharmaceuticals'),
    'description' => seo_text('Curated product stacks for common goals, with the individual products, what is included and links straight to each item in the shop.'),
    'canonical'   => 'stacks/',
    'active_nav'  => 'stacks',
    'json_ld'     => [
        ld_breadcrumbs([
            ['name' => 'Home',   'url' => ''],
            ['name' => 'Stacks', 'url' => 'stacks/'],
        ]),
        ld_organization(),
    ],
]);
$crumbs = [
    ['name' => 'Home',   'url' => ''],
    ['name' => 'Stacks', 'url' => 'stacks/'],
];

/**
 * Curated stacks.
 *
 * Each entry names the catalogue slugs and the quantity that makes up the kit.
 * The product names, prices and images are read from the live catalogue at
 * render time (never hand-copied), and the same list is handed to the cart
 * through the button's data-stack-items payload.
 */
$stack_bundles = [
    [
        'name'     => 'GLP-1 Metabolic Stack',
        'blurb'    => 'Tirzepatide 10MG + L-Carnitine — metabolic research pairing.',
        'discount' => 12,
        'coupon'   => 'stack12',
        'items'    => [
            ['slug' => 'tirzepatide',           'qty' => 1],
            ['slug' => 'l-carnitine-500mg-ml',  'qty' => 1],
        ],
    ],
    [
        'name'     => 'Healing Stack',
        'blurb'    => 'BPC-157 5MG + TB-500 5MG — classic recovery combo.',
        'discount' => 12,
        'coupon'   => 'stack12',
        'items'    => [
            ['slug' => 'bpc-157-5mg', 'qty' => 1],
            ['slug' => 'tb-500-5mg',  'qty' => 1],
        ],
    ],
    [
        'name'     => 'Recovery Stack',
        'blurb'    => 'Ipamorelin 10MG + GHK-CU 50MG — recovery-focused pairing.',
        'discount' => 12,
        'coupon'   => 'stack12',
        'items'    => [
            ['slug' => 'ipamorelin-10mg', 'qty' => 1],
            ['slug' => 'ghk-cu-50mg',     'qty' => 1],
        ],
    ],
    [
        'name'     => 'First Cycle Stack',
        'blurb'    => '~12 weeks at ~400 mg/wk Test Cyp + Arimidex on-cycle AI (0.5 mg EOD).',
        'discount' => 15,
        'coupon'   => 'stack15',
        'items'    => [
            ['slug' => 'test-cyp-200', 'qty' => 3],
            ['slug' => 'arimidex-1',   'qty' => 1],
        ],
    ],
    [
        'name'     => 'Kickstart Bulk Stack',
        'blurb'    => '~12 weeks at ~400 mg/wk Test Cyp + Dianabol oral kickstart (~4–6 weeks).',
        'discount' => 15,
        'coupon'   => 'stack15',
        'items'    => [
            ['slug' => 'test-cyp-200', 'qty' => 3],
            ['slug' => 'dianabol-10',  'qty' => 1],
        ],
    ],
    [
        'name'     => 'Classic Bulk Stack',
        'blurb'    => '~12 weeks at ~500 mg/wk Test E + ~300 mg/wk Deca — classic mass stack.',
        'discount' => 15,
        'coupon'   => 'stack15',
        'items'    => [
            ['slug' => 'test-enanthate-300',    'qty' => 2],
            ['slug' => 'nandrolone-deca-300',   'qty' => 2],
        ],
    ],
    [
        'name'     => 'Lean Bulk EQ Stack',
        'blurb'    => '~12 weeks at ~400 mg/wk Test E + ~500 mg/wk Equipoise — lean bulk.',
        'discount' => 15,
        'coupon'   => 'stack15',
        'items'    => [
            ['slug' => 'test-enanthate-300', 'qty' => 2],
            ['slug' => 'equipose-300',       'qty' => 2],
        ],
    ],
    [
        'name'     => 'Prop / NPP Stack',
        'blurb'    => '~12 weeks at ~350 mg/wk Test Prop + ~300 mg/wk NPP — short-ester recomp.',
        'discount' => 15,
        'coupon'   => 'stack15',
        'items'    => [
            ['slug' => 'test-prop-100',       'qty' => 5],
            ['slug' => 'nandrolone-prop-100', 'qty' => 4],
        ],
    ],
    [
        'name'     => 'Cutting Stack',
        'blurb'    => 'Test Prop ~10 weeks @ ~350 mg/wk + Tren Acetate ~8 weeks @ ~350 mg/wk (acetate runs are typically shorter).',
        'discount' => 15,
        'coupon'   => 'stack15',
        'items'    => [
            ['slug' => 'test-prop-100',    'qty' => 4],
            ['slug' => 'tren-acetate-100', 'qty' => 3],
        ],
    ],
    [
        'name'     => 'Tren E Cut Stack',
        'blurb'    => '~12 weeks at ~400 mg/wk Test Cyp + Tren E ~10 weeks @ ~300 mg/wk.',
        'discount' => 15,
        'coupon'   => 'stack15',
        'items'    => [
            ['slug' => 'test-cyp-200',      'qty' => 3],
            ['slug' => 'tren-enanthate-200', 'qty' => 2],
        ],
    ],
    [
        'name'     => 'Dry Finish Stack',
        'blurb'    => '~12 weeks at ~400 mg/wk Test Cyp + Winstrol oral finish (~4–6 weeks).',
        'discount' => 15,
        'coupon'   => 'stack15',
        'items'    => [
            ['slug' => 'test-cyp-200', 'qty' => 3],
            ['slug' => 'winstrol-25',  'qty' => 1],
        ],
    ],
    [
        'name'     => 'PCT Kit',
        'blurb'    => 'Clomid + Nolvadex sized for a standard ~4-week PCT (not a full cycle).',
        'discount' => 12,
        'coupon'   => 'stack12',
        'items'    => [
            ['slug' => 'clomid-50',   'qty' => 1],
            ['slug' => 'nolvadex-20', 'qty' => 1],
        ],
    ],
];

require __DIR__ . '/includes/layout/head.php';
?>
<main class="min-w-0 max-w-full overflow-x-clip pb-16 pt-[7.75rem] sm:pt-[8.75rem]"><div class="section-shell space-y-8 pb-10 pt-4 sm:pt-6"><header class="max-w-2xl space-y-3"><p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--accent)]">Bundles</p><h1 class="text-[clamp(1.85rem,4vw,2.75rem)] font-bold tracking-[-0.015em] text-[var(--ink)]">Protocol stacks</h1><p class="text-base font-light leading-relaxed text-[var(--muted-2)]">Built from live catalog SKUs. Peptide kits apply <code class="rounded bg-[var(--surface-soft)] px-1.5 py-0.5 text-sm">stack12</code> (12% off). 12-week cycle stacks apply <code class="rounded bg-[var(--surface-soft)] px-1.5 py-0.5 text-sm">stack15</code> (15% off) with multi-vial quantities sized for common research doses.</p></header><section class="stack-bundles stack-bundles--page" aria-label="Protocol stacks" data-stack-root data-image-base="<?= e(asset('img/')) ?>"><header class="stack-bundles__header"><div><p class="stack-bundles__eyebrow">Protocol stacks</p><h2 class="stack-bundles__title">Save 12–15% on curated kits</h2><p class="stack-bundles__desc">Peptide kits at 12% · 12-week cycle stacks at 15% — coupon applied automatically at cart.</p></div></header><div class="stack-bundles__grid"><?php
    foreach ($stack_bundles as $bundle):
        $lines    = [];
        $subtotal = 0.0;
        foreach ($bundle['items'] as $item) {
            $product = catalog_product((string)$item['slug']);
            if (!$product || (int)($product['is_active'] ?? 0) !== 1) {
                continue;
            }
            $qty      = max(1, (int)$item['qty']);
            $price    = product_price($product);
            $subtotal += $price * $qty;
            $lines[]  = ['product' => $product, 'qty' => $qty, 'price' => $price];
        }
        // A bundle whose SKUs all disappeared is skipped rather than shown empty.
        if (!$lines) {
            continue;
        }
        $total       = round($subtotal * (1 - ((int)$bundle['discount'] / 100)), 2);
        $cart_items  = json_encode(array_map(static function (array $line): array {
            return ['slug' => (string)$line['product']['slug'], 'qty' => $line['qty']];
        }, $lines), JSON_UNESCAPED_SLASHES);
?><article class="stack-bundle"><div class="stack-bundle__top"><h3 class="stack-bundle__name"><?= e($bundle['name']) ?></h3><span class="stack-bundle__badge"><?= (int)$bundle['discount'] ?>% off</span></div><p class="stack-bundle__blurb"><?= e($bundle['blurb']) ?></p><ul class="stack-bundle__products"><?php foreach ($lines as $line): ?><li><a class="stack-bundle__product" href="<?= e(url_path('product/' . $line['product']['slug'] . '/')) ?>"><span class="stack-bundle__thumb"><?= picture($line['product']['image'] ?? '', '') ?></span><span><?= e($line['product']['name']) ?><?= $line['qty'] > 1 ? ' ×' . (int)$line['qty'] : '' ?></span></a></li><?php endforeach; ?></ul><div class="stack-bundle__price"><strong><?= e(format_money($total)) ?></strong><span class="stack-bundle__was"><?= e(format_money($subtotal)) ?></span></div><button type="button" class="stack-bundle__cta" data-stack-add data-stack-items="<?= e($cart_items) ?>" data-stack-coupon="<?= e($bundle['coupon']) ?>">Add stack to cart</button></article><?php endforeach; ?></div></section></div></main>

<?php require __DIR__ . '/includes/layout/tail.php'; ?>
