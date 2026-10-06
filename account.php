<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$notice = '';
$errors = [];

/** Load the signed-in customer, if any. */
function account_customer(): ?array
{
    if (empty($_SESSION['customer_id'])) {
        return null;
    }
    $stmt = db()->prepare('SELECT id, email, first_name, last_name, phone, address, created_at FROM customers WHERE id = ? LIMIT 1');
    $stmt->execute([(int)$_SESSION['customer_id']]);
    return $stmt->fetch() ?: null;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    csrf_verify();

    if ($action === 'logout') {
        unset($_SESSION['customer_id']);
        redirect(url_path('account/'));
    }

    if ($action === 'login') {
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $pass  = (string)($_POST['password'] ?? '');
        $stmt  = db()->prepare('SELECT * FROM customers WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        if ($row && password_verify($pass, (string)$row['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['customer_id'] = (int)$row['id'];
            db()->prepare('UPDATE customers SET last_login_at = NOW() WHERE id = ?')->execute([(int)$row['id']]);
            redirect(url_path('account/'));
        }
        $errors[] = 'Email or password is incorrect.';
    }

    if ($action === 'register') {
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $pass  = (string)($_POST['password'] ?? '');
        $first = trim((string)($_POST['first_name'] ?? ''));
        $last  = trim((string)($_POST['last_name'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (strlen($pass) < 8) {
            $errors[] = 'Your password must be at least 8 characters.';
        }
        if (!$errors) {
            $stmt = db()->prepare('SELECT id FROM customers WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'An account with that email already exists — please sign in.';
            } else {
                $stmt = db()->prepare('INSERT INTO customers (email, password_hash, first_name, last_name, phone) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$email, password_hash($pass, PASSWORD_DEFAULT), $first, $last, $phone !== '' ? $phone : null]);
                session_regenerate_id(true);
                $_SESSION['customer_id'] = (int)db()->lastInsertId();
                redirect(url_path('account/'));
            }
        }
    }

    if ($action === 'update') {
        $me = account_customer();
        if ($me) {
            $stmt = db()->prepare('UPDATE customers SET first_name = ?, last_name = ?, phone = ?, address = ? WHERE id = ?');
            $stmt->execute([
                trim((string)($_POST['first_name'] ?? '')),
                trim((string)($_POST['last_name'] ?? '')),
                trim((string)($_POST['phone'] ?? '')) ?: null,
                trim((string)($_POST['address'] ?? '')) ?: null,
                (int)$me['id'],
            ]);
            $notice = 'Profile updated.';
        }
    }
}

$customer = account_customer();
$orders   = [];
if ($customer) {
    $stmt = db()->prepare('SELECT order_number, total, status, payment_status, created_at FROM orders WHERE customer_email = ? ORDER BY created_at DESC LIMIT 50');
    $stmt->execute([$customer['email']]);
    $orders = $stmt->fetchAll();
}

seo_set([
    'title'       => seo_title(($customer ? 'Your account' : 'Sign in or create an account') . ' | ' . store_name()),
    'description' => 'Sign in to your account to track orders and manage your details.',
    'canonical'   => 'account/',
    'noindex'     => true,
    'robots'      => 'noindex,nofollow',
    'active_nav'  => '',
    'breadcrumbs' => [['name' => 'Home', 'url' => ''], ['name' => 'Account', 'url' => 'account/']],
    'json_ld'     => [],
]);

$field = 'mt-1 h-12 w-full rounded-[12px] border border-[var(--line)] px-4 text-sm outline-none focus:border-[var(--accent)]';

require __DIR__ . '/includes/layout/head.php';
?>
<main class="min-w-0 max-w-full overflow-x-clip pb-16 pt-[7.75rem] sm:pt-[8.75rem]">
  <div class="section-shell space-y-8">
    <?php require __DIR__ . '/includes/partials/breadcrumbs.php'; ?>

    <?php if ($errors): ?>
      <div class="surface-card border-l-4 border-[#b3261e] px-5 py-4">
        <ul class="list-disc pl-5 text-sm text-[#8a2b2b]">
          <?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
        </ul>
      </div>
    <?php elseif ($notice): ?>
      <div class="surface-card border-l-4 border-[var(--accent)] px-5 py-4 text-sm text-[var(--accent-dark)]"><?= e($notice) ?></div>
    <?php endif; ?>

    <?php if ($customer): ?>
      <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
          <h1 class="text-[clamp(2rem,4.5vw,3rem)] font-bold tracking-[-0.01em] text-[var(--ink)]">Your account</h1>
          <p class="text-base text-[var(--muted-2)]">Signed in as <?= e((string)$customer['email']) ?></p>
        </div>
        <form method="post" action="<?= e(url_path('account/')) ?>">
          <?php csrf_field(); ?>
          <input type="hidden" name="action" value="logout">
          <button type="submit" class="btn-secondary !min-h-12 !px-6 !text-sm">Sign out</button>
        </form>
      </header>

      <div class="grid gap-8 lg:grid-cols-2">
        <section class="surface-card space-y-4 px-6 py-8">
          <h2 class="text-xl font-bold text-[var(--ink)]">Your details</h2>
          <form method="post" action="<?= e(url_path('account/')) ?>" class="space-y-4">
            <?php csrf_field(); ?>
            <input type="hidden" name="action" value="update">
            <div class="grid gap-4 sm:grid-cols-2">
              <label class="text-sm">First name<input name="first_name" value="<?= e((string)$customer['first_name']) ?>" class="<?= e($field) ?>"></label>
              <label class="text-sm">Last name<input name="last_name" value="<?= e((string)$customer['last_name']) ?>" class="<?= e($field) ?>"></label>
              <label class="text-sm">Phone<input name="phone" value="<?= e((string)$customer['phone']) ?>" class="<?= e($field) ?>"></label>
              <label class="text-sm">Email<input value="<?= e((string)$customer['email']) ?>" disabled class="<?= e($field) ?> opacity-60"></label>
            </div>
            <label class="block text-sm">Shipping address<textarea name="address" rows="3" class="mt-1 w-full rounded-[12px] border border-[var(--line)] px-4 py-3 text-sm outline-none focus:border-[var(--accent)]"><?= e((string)$customer['address']) ?></textarea></label>
            <button type="submit" class="btn-primary !min-h-11 !px-6 !text-sm">Save changes</button>
          </form>
        </section>

        <section class="surface-card space-y-4 px-6 py-8">
          <h2 class="text-xl font-bold text-[var(--ink)]">Order history</h2>
          <?php if ($orders): ?>
            <ul class="divide-y divide-[var(--line)]">
              <?php foreach ($orders as $order): ?>
                <li class="flex flex-wrap items-center justify-between gap-2 py-3 text-sm">
                  <span class="font-semibold text-[var(--ink)]"><?= e((string)$order['order_number']) ?></span>
                  <span class="text-[var(--muted)]"><?= e(date('j M Y', strtotime((string)$order['created_at']))) ?></span>
                  <span class="text-[var(--ink)]"><?= e(format_money((float)$order['total'])) ?></span>
                  <span class="rounded-full bg-[var(--surface-soft)] px-3 py-1 text-xs uppercase tracking-[0.05em]"><?= e((string)$order['status']) ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="text-sm text-[var(--muted-2)]">You have no orders yet. <a class="underline text-[var(--accent)]" href="<?= e(url_path('shop/')) ?>">Start shopping</a>.</p>
          <?php endif; ?>
        </section>
      </div>
    <?php else: ?>
      <header class="space-y-3">
        <h1 class="text-[clamp(2rem,4.5vw,3rem)] font-bold tracking-[-0.01em] text-[var(--ink)]">Sign in or create an account</h1>
        <p class="max-w-xl text-base text-[var(--muted-2)]">An account lets you track orders and keep your delivery details handy. You can also check out as a guest.</p>
      </header>

      <div class="grid gap-8 lg:grid-cols-2">
        <section class="surface-card space-y-4 px-6 py-8">
          <h2 class="text-xl font-bold text-[var(--ink)]">Sign in</h2>
          <form method="post" action="<?= e(url_path('account/')) ?>" class="space-y-4">
            <?php csrf_field(); ?>
            <input type="hidden" name="action" value="login">
            <label class="block text-sm">Email<input type="email" name="email" required autocomplete="email" class="<?= e($field) ?>"></label>
            <label class="block text-sm">Password<input type="password" name="password" required autocomplete="current-password" class="<?= e($field) ?>"></label>
            <button type="submit" class="btn-primary !min-h-11 !px-6 !text-sm">Sign in</button>
          </form>
        </section>

        <section class="surface-card space-y-4 px-6 py-8">
          <h2 class="text-xl font-bold text-[var(--ink)]">Create an account</h2>
          <form method="post" action="<?= e(url_path('account/')) ?>" class="space-y-4">
            <?php csrf_field(); ?>
            <input type="hidden" name="action" value="register">
            <div class="grid gap-4 sm:grid-cols-2">
              <label class="text-sm">First name<input name="first_name" class="<?= e($field) ?>"></label>
              <label class="text-sm">Last name<input name="last_name" class="<?= e($field) ?>"></label>
            </div>
            <label class="block text-sm">Email<input type="email" name="email" required autocomplete="email" class="<?= e($field) ?>"></label>
            <label class="block text-sm">Phone (optional)<input name="phone" autocomplete="tel" class="<?= e($field) ?>"></label>
            <label class="block text-sm">Password (min 8 characters)<input type="password" name="password" required minlength="8" autocomplete="new-password" class="<?= e($field) ?>"></label>
            <button type="submit" class="btn-primary !min-h-11 !px-6 !text-sm">Create account</button>
          </form>
        </section>
      </div>
    <?php endif; ?>
  </div>
</main>
<?php require __DIR__ . '/includes/layout/tail.php'; ?>
