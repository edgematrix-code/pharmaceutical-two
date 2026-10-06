<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';

$admin     = require_admin();
$pageTitle = $pageTitle ?? 'Dashboard';
$active    = $active ?? '';
$flashes   = take_flashes();

$brandName = (string)(arail_config()['app']['name'] ?? 'Arail Pharmaceuticals');

$navCounts = [
    'orders'   => (int)db()->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
    'reviews'  => (int)db()->query("SELECT COUNT(*) FROM reviews WHERE status = 'pending'")->fetchColumn(),
    'messages' => (int)db()->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn(),
];
$nav = [
    'index'       => ['index.php', 'Dashboard', 0],
    'products'    => ['products.php', 'Products', 0],
    'categories'  => ['categories.php', 'Categories', 0],
    'orders'      => ['orders.php', 'Orders', $navCounts['orders']],
    'reviews'     => ['reviews.php', 'Reviews', $navCounts['reviews']],
    'messages'    => ['messages.php', 'Messages', $navCounts['messages']],
    'subscribers' => ['subscribers.php', 'Subscribers', 0],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> · Arail Admin</title>
<link rel="icon" href="../assets/img/favicon-32.png" sizes="32x32" type="image/png">
<link rel="apple-touch-icon" href="../assets/img/apple-touch-icon.png" sizes="180x180">
<link rel="stylesheet" href="assets/admin.css">
</head>
<body class="admin">
<div class="layout">
  <aside class="sidebar">
    <?php // Same logo file the storefront header uses (assets/img/arail-logo-exact-v9.png). ?>
    <a class="brand brand-logo" href="index.php" aria-label="<?= e($brandName) ?> admin">
      <img class="brand-logo__img" alt="<?= e($brandName) ?>" width="482" height="239" decoding="async" src="../assets/img/arail-logo-exact-v9.png">
    </a>
    <span class="brand-tag">Admin</span>
    <?php foreach ($nav as $key => [$href, $label, $count]): ?>
      <a class="nav<?= $active === $key ? ' active' : '' ?>" href="<?= e($href) ?>">
        <span><?= e($label) ?></span>
        <?php if ($count > 0): ?><span class="badge pending"><?= (int)$count ?></span><?php endif; ?>
      </a>
    <?php endforeach; ?>
    <a class="logout" href="logout.php">Log out</a>
  </aside>
  <main class="content">
    <div class="topbar">
      <div>
        <p class="eyebrow"><?= e($brandName) ?> admin</p>
        <h1><?= e($pageTitle) ?></h1>
      </div>
      <div class="user-chip">Signed in as <strong><?= e($admin['name']) ?></strong></div>
    </div>
    <?php foreach ($flashes as $f): ?>
      <div class="flash <?= e($f['type']) ?>"><?= e($f['message']) ?></div>
    <?php endforeach; ?>
