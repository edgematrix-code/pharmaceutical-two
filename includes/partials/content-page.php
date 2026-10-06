<?php
/**
 * Simple content page renderer.
 * Expects: $page_h1, $page_intro, $sections = [ ['h' => '...', 'p' => ['...','...'] ], ... ]
 */
$sections = $sections ?? [];
$page_h1  = $page_h1 ?? '';
$page_intro = $page_intro ?? '';
?>
<main class="min-w-0 max-w-full overflow-x-clip pb-16 pt-[7.75rem] sm:pt-[8.75rem]">
  <div class="section-shell space-y-8">
    <?php require __DIR__ . '/breadcrumbs.php'; ?>

    <header class="space-y-3">
      <h1 class="text-[clamp(2rem,4.5vw,3rem)] font-bold tracking-[-0.01em] text-[var(--ink)]"><?= e($page_h1) ?></h1>
      <?php if ($page_intro !== ''): ?>
        <p class="max-w-2xl text-base font-light text-[var(--muted-2)]"><?= e($page_intro) ?></p>
      <?php endif; ?>
    </header>

    <div class="surface-card max-w-3xl space-y-6 px-6 py-8 sm:px-10">
      <?php foreach ($sections as $section): ?>
        <section class="space-y-2">
          <h2 class="text-xl font-bold text-[var(--accent)]"><?= e($section['h']) ?></h2>
          <?php foreach (($section['p'] ?? []) as $para): ?>
            <p class="text-base leading-relaxed text-[var(--muted-2)]"><?= e($para) ?></p>
          <?php endforeach; ?>
        </section>
      <?php endforeach; ?>

      <p class="rounded-[12px] border border-dashed border-[var(--line)] px-4 py-3 text-sm text-[var(--muted)]">
        TODO (store owner): review and expand this page with your own wording, and confirm the operational details
        (dispatch times, jurisdictions served, retention periods) before publishing. Nothing here is invented on your behalf.
      </p>
    </div>
  </div>
</main>
