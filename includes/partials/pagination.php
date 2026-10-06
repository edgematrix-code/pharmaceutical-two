<?php
/**
 * Crawlable pagination.
 * Expects: $page (current), $pages (total), $page_url (callable(int):string).
 */
$page  = (int)($page ?? 1);
$pages = (int)($pages ?? 1);
$page_url = $page_url ?? static fn(int $p): string => '?page=' . $p;
if ($pages < 2) {
    return;
}
$window = range(max(1, $page - 2), min($pages, $page + 2));
$link = 'inline-flex min-h-11 min-w-11 items-center justify-center rounded-[12px] border border-[var(--line)] px-3 text-sm font-semibold text-[var(--ink)] transition-colors hover:border-[var(--accent)] hover:text-[var(--accent)]';
?>
<nav class="mt-10 flex flex-wrap items-center justify-center gap-2" aria-label="Pagination">
  <?php if ($page > 1): ?>
    <a class="<?= e($link) ?>" href="<?= e($page_url($page - 1)) ?>" rel="prev">Previous</a>
  <?php endif; ?>
  <?php foreach ($window as $p): ?>
    <?php if ($p === $page): ?>
      <span class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-[12px] bg-[var(--accent)] px-3 text-sm font-semibold text-white" aria-current="page"><?= $p ?></span>
    <?php else: ?>
      <a class="<?= e($link) ?>" href="<?= e($page_url($p)) ?>"><?= $p ?></a>
    <?php endif; ?>
  <?php endforeach; ?>
  <?php if ($page < $pages): ?>
    <a class="<?= e($link) ?>" href="<?= e($page_url($page + 1)) ?>" rel="next">Next</a>
  <?php endif; ?>
</nav>
