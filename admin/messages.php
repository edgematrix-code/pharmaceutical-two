<?php
declare(strict_types=1);

require_once __DIR__ . '/_common.php';
require_admin();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_verify();
    $id     = (int)($_POST['id'] ?? 0);
    $action = (string)($_POST['action'] ?? '');

    if ($id > 0 && in_array($action, ['read', 'unread', 'delete'], true)) {
        if ($action === 'delete') {
            db()->prepare('DELETE FROM messages WHERE id = ?')->execute([$id]);
            flash('success', 'Message deleted.');
        } else {
            db()->prepare('UPDATE messages SET is_read = ? WHERE id = ?')
                ->execute([$action === 'read' ? 1 : 0, $id]);
            flash('success', 'Message marked as ' . $action . '.');
        }
    }
    redirect('messages.php');
}

$viewId = (int)($_GET['id'] ?? 0);
$message = null;
if ($viewId > 0) {
    $stmt = db()->prepare('SELECT * FROM messages WHERE id = ?');
    $stmt->execute([$viewId]);
    $message = $stmt->fetch();
    if ($message && (int)$message['is_read'] === 0) {
        db()->prepare('UPDATE messages SET is_read = 1 WHERE id = ?')->execute([$viewId]);
    }
}

$messages = db()->query('SELECT * FROM messages ORDER BY id DESC LIMIT 100')->fetchAll();

$pageTitle = $message ? 'Message from ' . $message['name'] : 'Messages';
$active    = 'messages';
require __DIR__ . '/partials/header.php';
?>
<?php if ($message): ?>
  <p><a href="messages.php">← Back to inbox</a></p>
  <div class="card">
    <h2 style="margin:0 0 .5rem;font-size:1.1rem"><?= e($message['subject'] ?: '(no subject)') ?></h2>
    <p class="small muted" style="margin:.2rem 0">
      From <strong><?= e($message['name']) ?></strong> &lt;<?= e($message['email']) ?>&gt; · <?= e($message['created_at']) ?>
    </p>
    <hr style="border:none;border-top:1px solid var(--line);margin:.8rem 0">
    <p class="body" style="margin:0"><?= nl2br(e($message['body'])) ?></p>
    <div class="mt2" style="display:flex;gap:.5rem;flex-wrap:wrap">
      <a class="btn" href="mailto:<?= e($message['email']) ?>?subject=Re: <?= e($message['subject'] ?: 'your message') ?>">Reply by email</a>
      <form class="inline" method="post" action="messages.php">
        <?php csrf_field(); ?>
        <input type="hidden" name="id" value="<?= (int)$message['id'] ?>">
        <button class="btn" name="action" value="unread" type="submit">Mark unread</button>
      </form>
      <form class="inline" method="post" action="messages.php">
        <?php csrf_field(); ?>
        <input type="hidden" name="id" value="<?= (int)$message['id'] ?>">
        <button class="btn btn-danger" name="action" value="delete" type="submit" onclick="return confirm('Delete this message?')">Delete</button>
      </form>
    </div>
  </div>
<?php else: ?>
  <?php if (!$messages): ?>
    <div class="card"><p class="empty">Inbox is empty. Messages sent via <code>api/contact.php</code> land here.</p></div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="tbl">
      <tr><th>From</th><th>Subject</th><th>Received</th><th></th></tr>
      <?php foreach ($messages as $m): ?>
        <tr class="msg-row <?= (int)$m['is_read'] === 0 ? 'unread' : '' ?>">
          <td><?= e($m['name']) ?><div class="small muted"><?= e($m['email']) ?></div></td>
          <td><a href="messages.php?id=<?= (int)$m['id'] ?>"><?= e($m['subject'] ?: '(no subject)') ?></a>
              <?php if ((int)$m['is_read'] === 0): ?><span class="badge pending">new</span><?php endif; ?></td>
          <td class="small"><?= e($m['created_at']) ?></td>
          <td style="white-space:nowrap">
            <form class="inline" method="post" action="messages.php">
              <?php csrf_field(); ?>
              <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
              <button class="btn btn-sm" name="action" value="<?= (int)$m['is_read'] === 0 ? 'read' : 'unread' ?>" type="submit">
                Mark <?= (int)$m['is_read'] === 0 ? 'read' : 'unread' ?>
              </button>
              <button class="btn btn-sm btn-danger" name="action" value="delete" type="submit" onclick="return confirm('Delete this message?')">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
    </div>
    <p class="small muted mt1">Showing the latest <?= count($messages) ?> message(s).</p>
  <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/partials/footer.php'; ?>
