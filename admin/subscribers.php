<?php
declare(strict_types=1);

require_once __DIR__ . '/_common.php';
require_admin();

/* CSV export */
if (($_GET['export'] ?? '') === 'csv') {
    $rows = db()->query('SELECT email, status, created_at FROM subscribers ORDER BY id DESC')->fetchAll();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="subscribers-' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['email', 'status', 'subscribed_at']);
    foreach ($rows as $r) {
        fputcsv($out, [$r['email'], $r['status'], $r['created_at']]);
    }
    fclose($out);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_verify();
    $id     = (int)($_POST['id'] ?? 0);
    $action = (string)($_POST['action'] ?? '');

    if ($id > 0 && in_array($action, ['toggle', 'delete'], true)) {
        if ($action === 'delete') {
            db()->prepare('DELETE FROM subscribers WHERE id = ?')->execute([$id]);
            flash('success', 'Subscriber removed.');
        } else {
            db()->prepare("UPDATE subscribers SET status = IF(status = 'subscribed', 'unsubscribed', 'subscribed') WHERE id = ?")
                ->execute([$id]);
            flash('success', 'Subscriber status changed.');
        }
    }
    redirect('subscribers.php');
}

$subscribers = db()->query('SELECT * FROM subscribers ORDER BY id DESC LIMIT 300')->fetchAll();
$counts = [
    'subscribed'   => (int)db()->query("SELECT COUNT(*) FROM subscribers WHERE status = 'subscribed'")->fetchColumn(),
    'unsubscribed' => (int)db()->query("SELECT COUNT(*) FROM subscribers WHERE status = 'unsubscribed'")->fetchColumn(),
];

$pageTitle = 'Subscribers';
$active    = 'subscribers';
require __DIR__ . '/partials/header.php';
?>
<div class="toolbar">
  <span class="badge subscribed"><?= $counts['subscribed'] ?> subscribed</span>
  <span class="badge unsubscribed"><?= $counts['unsubscribed'] ?> unsubscribed</span>
  <a class="btn" href="subscribers.php?export=csv" style="margin-left:auto">Export CSV</a>
</div>

<?php if (!$subscribers): ?>
  <div class="card"><p class="empty">No subscribers yet. Sign-ups via <code>api/subscribe.php</code> land here.</p></div>
<?php else: ?>
  <div class="table-scroll">
  <table class="tbl">
    <tr><th>Email</th><th>Status</th><th>Since</th><th></th></tr>
    <?php foreach ($subscribers as $s): ?>
      <tr>
        <td><?= e($s['email']) ?></td>
        <td><span class="badge <?= e($s['status']) ?>"><?= e($s['status']) ?></span></td>
        <td class="small"><?= e($s['created_at']) ?></td>
        <td style="white-space:nowrap">
          <form class="inline" method="post" action="subscribers.php">
            <?php csrf_field(); ?>
            <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
            <button class="btn btn-sm" name="action" value="toggle" type="submit">Toggle</button>
            <button class="btn btn-sm btn-danger" name="action" value="delete" type="submit" onclick="return confirm('Remove this subscriber?')">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
  </div>
  <p class="small muted mt1">Showing latest <?= count($subscribers) ?> of <?= $counts['subscribed'] + $counts['unsubscribed'] ?>.</p>
<?php endif; ?>

<?php require __DIR__ . '/partials/footer.php'; ?>
