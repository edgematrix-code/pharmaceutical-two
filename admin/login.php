<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

admin_session_start();
if (current_admin() !== null) {
    redirect('index.php');
}

$brandName = (string)(arail_config()['app']['name'] ?? 'Arail Pharmaceuticals');
$error = null;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_verify();
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($username !== '' && $password !== '' && attempt_admin_login($username, $password)) {
        redirect('index.php');
    }
    usleep(400000); // small delay against brute force
    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign in · <?= e($brandName) ?> Admin</title>
<link rel="icon" href="../assets/img/favicon-32.png" sizes="32x32" type="image/png">
<link rel="apple-touch-icon" href="../assets/img/apple-touch-icon.png" sizes="180x180">
<link rel="stylesheet" href="assets/admin.css">
</head>
<body class="admin">
<div class="login-wrap">
  <div class="login-card">
    <?php // Same logo file the storefront header uses (assets/img/arail-logo-exact-v9.png). ?>
    <span class="brand brand-logo">
      <img class="brand-logo__img" alt="<?= e($brandName) ?>" width="482" height="239" decoding="async" src="../assets/img/arail-logo-exact-v9.png">
    </span>
    <h1>Sign in to the dashboard</h1>
    <?php if ($error !== null): ?>
      <div class="flash error"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="post" action="login.php" autocomplete="off">
      <?php csrf_field(); ?>
      <label class="fld"><span>Username</span>
        <input type="text" name="username" required autofocus>
      </label>
      <label class="fld"><span>Password</span>
        <input type="password" name="password" required>
      </label>
      <button class="btn btn-primary" type="submit">Sign in</button>
    </form>
    <p class="login-note"><?= e($brandName) ?> staff area</p>
  </div>
</div>
</body>
</html>
