<?php
declare(strict_types=1);

require_once __DIR__ . '/_common.php';
require_admin();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_verify();
    $id     = (int)($_POST['id'] ?? 0);
    $action = (string)($_POST['action'] ?? '');
    $return = (string)($_POST['return'] ?? '');

    if ($id > 0 && in_array($action, ['approve', 'reject', 'delete'], true)) {
        if ($action === 'delete') {
            $stmt = db()->prepare('SELECT photo FROM reviews WHERE id = ?');
            $stmt->execute([$id]);
            $photo = $stmt->fetchColumn();
            db()->prepare('DELETE FROM reviews WHERE id = ?')->execute([$id]);
            if (is_string($photo) && str_starts_with($photo, 'uploads/')) {
                @unlink(__DIR__ . '/../' . $photo);
            }
            flash('success', 'Review deleted.');
        } else {
            db()->prepare('UPDATE reviews SET status = ? WHERE id = ?')
                ->execute([$action === 'approve' ? 'approved' : 'rejected', $id]);
            flash('success', 'Review ' . $action . 'd.');
        }
    }
    redirect('reviews.php' . ($return !== '' ? '?' . $return : ''));
}

$status = (string)($_GET['status'] ?? 'all');
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;

$where = '';
if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
    $where = 'WHERE r.status = ' . "'" . $status . "'";
}

$stmt = db()->prepare("SELECT COUNT(*) FROM reviews r $where");
$stmt->execute();
$total = (int)$stmt->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));
$page  = min($page, $pages);

$reviews = db()->query(
    "SELECT r.*, p.name AS product_name, p.slug AS product_slug
     FROM reviews r LEFT JOIN products p ON p.id = r.product_id
     $where ORDER BY r.id DESC LIMIT $perPage OFFSET " . (($page - 1) * $perPage)
)->fetchAll();

$keep   = array_filter(['status' => $status !== 'all' ? $status : null]);
$keepQ  = $keep ? '?' . http_build_query($keep) : '';
$counts = [
    'all'      => (int)db()->query('SELECT COUNT(*) FROM reviews')->fetchColumn(),
    'pending'  => (int)db()->query("SELECT COUNT(*) FROM reviews WHERE status = 'pending'")->fetchColumn(),
    'approved' => (int)db()->query("SELECT COUNT(*) FROM reviews WHERE status = 'approved'")->fetchColumn(),
    'rejected' => (int)db()->query("SELECT COUNT(*) FROM reviews WHERE status = 'rejected'")->fetchColumn(),
];

$pageTitle = 'Reviews';
$active    = 'reviews';
require __DIR__ . '/partials/header.php';
?>
<div class="toolbar">
  <?php foreach (['all', 'pending', 'approved', 'rejected'] as $st): ?>
    <a class="btn <?= ($status === $st || ($st === 'all' && $status === 'all')) ? 'btn-primary' : '' ?>"
       href="reviews.php<?= $st !== 'all' ? '?status=' . $st : '' ?>">
      <?= ucfirst($st) ?> (<?= $counts[$st] ?>)
    </a>
  <?php endforeach; ?>
</div>

<?php if (!$reviews): ?>
  <div class="card"><p class="empty">No reviews here. Reviews posted to <code>api/reviews.php</code> arrive as <em>pending</em>.</p></div>
<?php else: ?>
  <?php foreach ($reviews as $r): ?>
    <div class="review-item">
      <div style="display:flex;justify-content:space-between;gap:1rem;flex-wrap:wrap;align-items:flex-start">
        <div>
          <span class="stars"><?= str_repeat('★', (int)$r['rating']) . str_repeat('☆', 5 - (int)$r['rating']) ?></span>
          <span class="badge <?= e($r['status']) ?>"><?= e($r['status']) ?></span>
          <?php if ($r['product_name']): ?><span class="small muted"> on <?= e($r['product_name']) ?></span><?php endif; ?>
          <div style="margin:.3rem 0"><strong><?= e($r['author_name']) ?></strong><?= $r['title'] ? ' — ' . e($r['title']) : '' ?></div>
          <p style="margin:.3rem 0"><?= nl2br(e($r['body'])) ?></p>
          <div class="small muted">
            <?= e($r['created_at']) ?>
            <?php if ($r['author_email']): ?> · <?= e($r['author_email']) ?><?php endif; ?>
            <?php if ($r['forum_url']): ?> · <a href="<?= e($r['forum_url']) ?>" target="_blank" rel="noopener">forum link</a><?php endif; ?>
          </div>
          <?php if ($r['photo']): ?>
            <img src="<?= e(admin_image_url((string)$r['photo'])) ?>" alt="" style="max-width:140px;max-height:140px;border-radius:12px;border:1px solid var(--line);margin-top:.5rem;display:block">
          <?php endif; ?>
        </div>
        <div style="display:flex;gap:.4rem;flex-wrap:wrap">
          <form class="inline" method="post" action="reviews.php<?= e($keepQ) ?>">
            <?php csrf_field(); ?>
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <input type="hidden" name="return" value="<?= e(ltrim($keepQ, '?')) ?>">
            <?php if ($r['status'] !== 'approved'): ?>
              <button class="btn btn-sm btn-primary" name="action" value="approve" type="submit">Approve</button>
            <?php endif; ?>
            <?php if ($r['status'] !== 'rejected'): ?>
              <button class="btn btn-sm" name="action" value="reject" type="submit">Reject</button>
            <?php endif; ?>
            <button class="btn btn-sm btn-danger" name="action" value="delete" type="submit" onclick="return confirm('Delete this review?')">Delete</button>
          </form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <?php if ($pages > 1): ?>
    <div class="pager">
      <?php for ($i = 1; $i <= $pages; $i++):
          $qs = http_build_query(array_filter(array_merge($keep, ['page' => $i > 1 ? $i : null]))); ?>
        <?php if ($i === $page): ?><span class="current"><?= $i ?></span>
        <?php else: ?><a href="reviews.php<?= $qs !== '' ? '?' . e($qs) : '' ?>"><?= $i ?></a><?php endif; ?>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
<?php endif; ?>

<p class="small muted mt1"><?= (int)$total ?> review(s).</p>

<?php require __DIR__ . '/partials/footer.php'; ?>
