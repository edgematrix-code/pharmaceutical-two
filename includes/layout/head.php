<?php
/**
 * Shared <head> + opening markup. Every page sets its SEO state with seo_set()
 * before including this file.
 */
$app        = arail_config()['app'];
$page_title = (string)seo('title', store_name());
$page_desc  = (string)seo('description', (string)$app['tagline']);
$og_image   = (string)seo('og_image', 'assets/img/arail-logo-exact-og-v9.png');
$og_image_abs = preg_match('~^https?://~i', $og_image) ? $og_image : url_abs($og_image);
$robots     = seo('noindex') ? 'noindex,follow' : (string)seo('robots', 'index,follow');
?><!DOCTYPE html>
<html lang="en" data-age-verified="1">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($page_title) ?></title>
  <meta name="description" content="<?= e($page_desc) ?>">
  <meta name="robots" content="<?= e($robots) ?>">
  <link rel="canonical" href="<?= e(canonical_url()) ?>">

  <!-- Open Graph -->
  <meta property="og:site_name" content="<?= e(store_name()) ?>">
  <meta property="og:type" content="<?= e((string)seo('og_type', 'website')) ?>">
  <meta property="og:title" content="<?= e((string)seo('og_title', $page_title)) ?>">
  <meta property="og:description" content="<?= e((string)seo('og_description', $page_desc)) ?>">
  <meta property="og:url" content="<?= e(canonical_url()) ?>">
  <meta property="og:image" content="<?= e($og_image_abs) ?>">
  <meta property="og:image:alt" content="<?= e((string)seo('og_image_alt', store_name())) ?>">
  <meta property="og:locale" content="en_US">
  <?php foreach ((array)seo('og', []) as $prop => $value): ?>
  <meta property="<?= e((string)$prop) ?>" content="<?= e((string)$value) ?>">
  <?php endforeach; ?>

  <!-- Twitter -->
  <meta name="twitter:card" content="<?= e((string)seo('twitter_card', 'summary_large_image')) ?>">
  <meta name="twitter:title" content="<?= e($page_title) ?>">
  <meta name="twitter:description" content="<?= e($page_desc) ?>">
  <meta name="twitter:image" content="<?= e($og_image_abs) ?>">

  <!-- Icons -->
  <link rel="icon" href="<?= e(asset('img/favicon-32.png')) ?>" sizes="32x32" type="image/png">
  <link rel="apple-touch-icon" href="<?= e(asset('img/apple-touch-icon.png')) ?>" sizes="180x180">

  <!-- Fonts + styles -->
  <link rel="preload" href="<?= e(asset('fonts/4c9affa5bc8f420e-s.p.woff2')) ?>" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="<?= e(asset('css/theme.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>">
  <?php if (seo('preload_image')): ?>
  <link rel="preload" as="image" href="<?= e(asset((string)seo('preload_image'))) ?>" fetchpriority="high">
  <?php endif; ?>
  <style>
    /* Keep animated content visible now that hydration scripts are gone */
    .animate-soft-in,
    .animate-fade-up,
    .home-hero-title-line {
      animation: none !important;
      opacity: 1 !important;
      transform: none !important;
    }
    html:not([data-age-verified="1"]) #arail-storefront {
      visibility: visible !important;
      opacity: 1 !important;
      filter: none !important;
    }
    .cart-count-badge {
      position: absolute; top: -2px; right: -4px;
      min-width: 17px; height: 17px; padding: 0 4px;
      border-radius: 9px; background: var(--accent); color: #fff;
      font-size: 10px; font-weight: 700; line-height: 17px; text-align: center;
    }
  </style>

  <?php foreach ((array)seo('json_ld', []) as $block): ?>
  <?= json_ld_markup($block) ?>

  <?php endforeach; ?>

  <?php if (!empty($app['gsc_token'])): ?>
  <meta name="google-site-verification" content="<?= e((string)$app['gsc_token']) ?>">
  <?php endif; ?>
</head>
<body class="__variable_538739 antialiased">
  <div id="arail-storefront">
<?php require __DIR__ . '/header.php'; ?>
