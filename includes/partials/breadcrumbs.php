<?php
/**
 * Visible breadcrumbs. Expects $crumbs = [['name' => 'Shop', 'url' => 'shop/'], ...].
 * The last item is rendered as the current page (not a link).
 */
$crumbs = $crumbs ?? [];
if (!$crumbs) {
    return;
}
$last = count($crumbs) - 1;
?>
<nav aria-label="Breadcrumb" class="text-sm">
  <ol class="flex flex-wrap items-center gap-1.5 text-[var(--muted)]">
    <?php foreach ($crumbs as $i => $crumb): ?>
      <li class="flex items-center gap-1.5">
        <?php if ($i < $last): ?>
          <a class="underline-offset-2 hover:text-[var(--accent)] hover:underline" href="<?= e(url_path($crumb['url'])) ?>"><?= e($crumb['name']) ?></a>
          <span aria-hidden="true">/</span>
        <?php else: ?>
          <span aria-current="page" class="text-[var(--ink)]"><?= e($crumb['name']) ?></span>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ol>
</nav>
