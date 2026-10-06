<?php
/**
 * Shared site header. Included by every public page through layout/head.php.
 * Single source of truth - do not copy this markup into individual pages.
 */
$active_nav  = (string)seo('active_nav', '');
$nav_cats    = catalog_categories();
$category_imgs = [
    'injectable-anabolics' => 'img/category-injectable-anabolics.png',
    'oral-anabolics'       => 'img/category-oral-anabolics.png',
    'peptides-hgh'         => 'img/category-peptides-hgh.png',
];
?>
<header class="pointer-events-none fixed inset-x-0 top-0 z-50">
  <div class="section-shell pointer-events-auto relative pt-3 sm:pt-4">
    <div class="site-header-bar flex min-w-0 items-center gap-2 rounded-full border bg-white/94 px-3 py-2.5 backdrop-blur-md sm:gap-4 sm:px-5 sm:py-3.5">
      <a class="brand-logo brand-logo--header brand-logo--light site-header-logo min-w-0 shrink-0 outline-none transition-opacity hover:opacity-85 focus-visible:ring-2 focus-visible:ring-[var(--accent)] focus-visible:ring-offset-2" aria-label="<?= e(store_name()) ?>" href="<?= e(url_path('')) ?>">
        <img alt="<?= e(store_name()) ?>" width="482" height="239" decoding="async" class="brand-logo__img" style="color:transparent" src="<?= e(asset('img/arail-logo-exact-v9.png')) ?>">
      </a>
      <nav class="hidden min-w-0 flex-1 items-center justify-center gap-0.5 lg:flex" aria-label="Primary">
        <a class="<?= e(nav_classes('home', $active_nav)) ?>" href="<?= e(url_path('')) ?>">Home</a>
        <div class="shop-nav-dropdown relative">
          <a data-shop-nav-trigger="true" aria-expanded="false" aria-haspopup="true" aria-controls="_R_4kldb_" class="<?= e(nav_classes('shop', $active_nav)) ?>" href="<?= e(url_path('shop/')) ?>">Shop</a>
          <div id="_R_4kldb_" role="menu" aria-label="Shop categories" aria-hidden="true" class="shop-nav-dropdown-panel absolute left-1/2 top-[calc(100%+14px)] z-50 w-[min(28rem,calc(100vw-2rem))] -translate-x-1/2 origin-top rounded-[18px] border bg-white p-3 shadow-[0_18px_50px_rgba(25,48,58,0.12)] transition-[opacity,transform,visibility] duration-180 ease-[cubic-bezier(0.22,1,0.36,1)] invisible pointer-events-none -translate-y-1.5 opacity-0">
            <div class="shop-nav-dropdown-grid">
              <?php foreach ($nav_cats as $cat): ?>
                <?php $img = $category_imgs[$cat['slug']] ?? 'img/category-injectable-anabolics.png'; ?>
                <a role="menuitem" class="shop-nav-category" href="<?= e(url_path('category/' . $cat['slug'] . '/')) ?>">
                  <span class="shop-nav-category-image">
                    <img alt="<?= e($cat['name']) ?>" loading="lazy" decoding="async" src="<?= e(asset($img)) ?>">
                  </span>
                  <span class="shop-nav-category-label"><?= e($cat['name']) ?></span>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <a class="<?= e(nav_classes('stacks', $active_nav)) ?>" href="<?= e(url_path('stacks/')) ?>">Stacks</a>
        <div class="help-nav-dropdown relative">
          <button type="button" data-help-nav-trigger="true" aria-expanded="false" aria-haspopup="true" aria-controls="_R_14ldb_" class="<?= e(nav_classes('help', $active_nav)) ?>">Help</button>
          <div id="_R_14ldb_" role="menu" aria-label="Help" aria-hidden="true" class="help-nav-dropdown-panel absolute left-1/2 top-[calc(100%+14px)] z-50 w-[min(13.5rem,calc(100vw-2rem))] -translate-x-1/2 origin-top rounded-[18px] border bg-white p-2 shadow-[0_18px_50px_rgba(25,48,58,0.12)] transition-[opacity,transform,visibility] duration-180 ease-[cubic-bezier(0.22,1,0.36,1)] invisible pointer-events-none -translate-y-1.5 opacity-0">
            <a role="menuitem" class="help-nav-dropdown-link flex items-center gap-2.5 rounded-[12px] px-3.5 py-2.5 text-[13px] font-medium tracking-[-0.01em] outline-none transition-colors sm:text-[14px] text-[var(--ink)] hover:bg-[var(--surface-soft)] hover:text-[var(--accent-dark)]" href="<?= e(url_path('dosing/')) ?>">
              <svg width="18" height="18" viewBox="0 0 20 20" fill="none" aria-hidden="true" class="help-nav-icon shrink-0 text-[var(--accent)]"><path d="M12.8 3.2 16.8 7.2M14.2 4.6l1.6 1.6" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path><path d="M13.4 5.4 5.6 13.2a2.2 2.2 0 0 0-.5.9l-.7 2.5 2.5-.7c.33-.1.63-.27.9-.5l7.8-7.8" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path><path d="m8.2 10.8 1.2 1.2M6.6 12.4l1.1 1.1" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"></path><path d="M4.1 15.9 3.2 16.8" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"></path></svg>
              <span>Dosing</span>
            </a>
            <a role="menuitem" class="help-nav-dropdown-link flex items-center gap-2.5 rounded-[12px] px-3.5 py-2.5 text-[13px] font-medium tracking-[-0.01em] outline-none transition-colors sm:text-[14px] text-[var(--ink)] hover:bg-[var(--surface-soft)] hover:text-[var(--accent-dark)]" href="<?= e(url_path('bitcoin/')) ?>">
              <img src="<?= e(asset('img/bitcoin-logo.svg')) ?>" alt="" width="18" height="18" aria-hidden="true" class="help-nav-icon shrink-0" style="width:18px;height:18px;display:block">
              <span>Buy Bitcoin</span>
            </a>
            <a role="menuitem" class="help-nav-dropdown-link flex items-center gap-2.5 rounded-[12px] px-3.5 py-2.5 text-[13px] font-medium tracking-[-0.01em] outline-none transition-colors sm:text-[14px] text-[var(--ink)] hover:bg-[var(--surface-soft)] hover:text-[var(--accent-dark)]" href="<?= e(url_path('reviews/')) ?>">
              <svg width="18" height="18" viewBox="0 0 20 20" fill="none" aria-hidden="true" class="help-nav-icon shrink-0 text-[var(--accent)]"><path d="M4 4.8h12a1.6 1.6 0 0 1 1.6 1.6v6.2a1.6 1.6 0 0 1-1.6 1.6H9.2L5.6 17.2v-3H4A1.6 1.6 0 0 1 2.4 12.6V6.4A1.6 1.6 0 0 1 4 4.8Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"></path><path d="M6.4 8.6h7.2M6.4 11.2h4.8" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"></path></svg>
              <span>Reviews</span>
            </a>
            <a role="menuitem" class="help-nav-dropdown-link flex items-center gap-2.5 rounded-[12px] px-3.5 py-2.5 text-[13px] font-medium tracking-[-0.01em] outline-none transition-colors sm:text-[14px] text-[var(--ink)] hover:bg-[var(--surface-soft)] hover:text-[var(--accent-dark)]" href="<?= e(url_path('contact/')) ?>">
              <svg width="18" height="18" viewBox="0 0 20 20" fill="none" aria-hidden="true" class="help-nav-icon shrink-0 text-[var(--accent)]"><rect x="2.8" y="4.5" width="14.4" height="11" rx="1.6" stroke="currentColor" stroke-width="1.4"></rect><path d="m3.6 6.2 5.6 4.2c.48.36 1.12.36 1.6 0l5.6-4.2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path></svg>
              <span>Contact</span>
            </a>
            <a role="menuitem" class="help-nav-dropdown-link flex items-center gap-2.5 rounded-[12px] px-3.5 py-2.5 text-[13px] font-medium tracking-[-0.01em] outline-none transition-colors sm:text-[14px] text-[var(--ink)] hover:bg-[var(--surface-soft)] hover:text-[var(--accent-dark)]" href="<?= e(url_path('testing/')) ?>">
              <svg width="18" height="18" viewBox="0 0 20 20" fill="none" aria-hidden="true" class="help-nav-icon shrink-0 text-[var(--accent)]"><path d="M8.2 2.5h3.6M9 2.5v4.1L5.4 13.2a3.1 3.1 0 0 0 2.65 4.6h4.9a3.1 3.1 0 0 0 2.65-4.6L11 6.6V2.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path><path d="M7.1 11.6h5.8" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"></path><circle cx="12.6" cy="14.2" r="0.9" fill="currentColor"></circle></svg>
              <span>Testing</span>
            </a>
          </div>
        </div>
      </nav>
      <form class="hidden min-w-0 flex-1 items-center md:flex lg:max-w-[200px] lg:flex-none xl:max-w-[220px]" role="search" aria-label="Search shop" action="<?= e(url_path('search/')) ?>" method="get">
        <div class="site-header-search flex w-full items-center gap-2 rounded-full bg-[var(--surface-soft)] px-3 py-1.5">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true" class="shrink-0 text-[var(--muted)]"><path d="M10.5 18C14.6421 18 18 14.6421 18 10.5C18 6.35786 14.6421 3 10.5 3C6.35786 3 3 6.35786 3 10.5C3 14.6421 6.35786 18 10.5 18Z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"></path><path d="M15.8 15.8L21 21" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"></path></svg>
          <input name="q" type="search" placeholder="Search" aria-label="Search products" class="min-w-0 flex-1 bg-transparent text-[13px] text-[var(--ink)] outline-none placeholder:text-[var(--muted)]" value="<?= e((string)($_GET['q'] ?? '')) ?>">
        </div>
      </form>
      <div class="ml-auto flex shrink-0 items-center gap-0.5 sm:gap-1.5">
        <a aria-label="Cart" class="site-header-icon-btn site-header-cart-btn relative" href="<?= e(url_path('cart/')) ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 6h15l-1.5 9h-12L6 6Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"></path><path d="M6 6L5 3H2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"></path><circle cx="9.5" cy="20" r="1.25" fill="currentColor"></circle><circle cx="17.5" cy="20" r="1.25" fill="currentColor"></circle></svg>
          <span data-cart-count class="cart-count-badge" hidden>0</span>
        </a>
        <a aria-label="Account" class="site-header-icon-btn site-header-icon-btn--desktop-only" href="<?= e(url_path('account/')) ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="8" r="3.5" stroke="currentColor" stroke-width="1.6"></circle><path d="M5 20c0-3.5 3.1-6 7-6s7 2.5 7 6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"></path></svg>
        </a>
        <button type="button" aria-label="Open menu" aria-expanded="false" aria-controls="site-header-mobile-menu" class="site-header-menu-btn flex h-10 w-10 items-center justify-center rounded-full outline-none transition-transform sm:h-10 sm:w-10 lg:hidden">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"></path></svg>
        </button>
      </div>
    </div>
    <div id="site-header-mobile-menu" role="dialog" aria-label="Site menu" aria-hidden="true" class="site-header-panel absolute inset-x-[var(--page-pad)] top-[calc(100%+8px)] origin-top overflow-hidden rounded-[20px] border bg-white shadow-[0_18px_50px_rgba(25,48,58,0.12)] transition-[opacity,transform,visibility] duration-200 ease-[cubic-bezier(0.22,1,0.36,1)] lg:hidden invisible -translate-y-1.5 pointer-events-none opacity-0">
      <nav class="flex flex-col px-2 py-2" aria-label="Mobile">
        <a class="<?= e(nav_mobile_classes('home', $active_nav)) ?>" href="<?= e(url_path('')) ?>">Home</a>
        <a class="<?= e(nav_mobile_classes('shop', $active_nav)) ?>" href="<?= e(url_path('shop/')) ?>">Shop</a>
        <div class="mobile-menu-categories" aria-label="Shop categories">
          <div class="mobile-menu-categories-scroll">
            <?php foreach ($nav_cats as $cat): ?>
              <?php $img = $category_imgs[$cat['slug']] ?? 'img/category-injectable-anabolics.png'; ?>
              <a class="mobile-menu-category" href="<?= e(url_path('category/' . $cat['slug'] . '/')) ?>">
                <span class="mobile-menu-category-image"><img alt="" loading="lazy" decoding="async" src="<?= e(asset($img)) ?>"></span>
                <span class="mobile-menu-category-label"><?= e($cat['name']) ?></span>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
        <a class="<?= e(nav_mobile_classes('stacks', $active_nav)) ?>" href="<?= e(url_path('stacks/')) ?>">Stacks</a>
        <div class="flex flex-col">
          <button type="button" aria-expanded="false" aria-controls="site-header-mobile-help" class="flex items-center justify-between rounded-[14px] px-4 py-3 text-left text-[15px] font-medium tracking-[-0.01em] outline-none transition-colors focus-visible:ring-2 focus-visible:ring-[var(--accent)] text-[var(--ink)] hover:bg-[var(--surface-soft)] hover:text-[var(--accent-dark)]">
            <span>Help</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true" class="shrink-0 transition-transform duration-200"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path></svg>
          </button>
          <div id="site-header-mobile-help" role="region" aria-label="Help links" class="flex flex-col pb-1 pl-2 hidden" hidden>
            <a class="help-nav-mobile-link flex items-center gap-2.5 rounded-[14px] px-4 py-2.5 text-[14px] font-medium tracking-[-0.01em] outline-none transition-colors focus-visible:ring-2 focus-visible:ring-[var(--accent)] text-[var(--muted)] hover:bg-[var(--surface-soft)] hover:text-[var(--accent-dark)]" href="<?= e(url_path('dosing/')) ?>"><span>Dosing</span></a>
            <a class="help-nav-mobile-link flex items-center gap-2.5 rounded-[14px] px-4 py-2.5 text-[14px] font-medium tracking-[-0.01em] outline-none transition-colors focus-visible:ring-2 focus-visible:ring-[var(--accent)] text-[var(--muted)] hover:bg-[var(--surface-soft)] hover:text-[var(--accent-dark)]" href="<?= e(url_path('bitcoin/')) ?>"><span>Buy Bitcoin</span></a>
            <a class="help-nav-mobile-link flex items-center gap-2.5 rounded-[14px] px-4 py-2.5 text-[14px] font-medium tracking-[-0.01em] outline-none transition-colors focus-visible:ring-2 focus-visible:ring-[var(--accent)] text-[var(--muted)] hover:bg-[var(--surface-soft)] hover:text-[var(--accent-dark)]" href="<?= e(url_path('reviews/')) ?>"><span>Reviews</span></a>
            <a class="help-nav-mobile-link flex items-center gap-2.5 rounded-[14px] px-4 py-2.5 text-[14px] font-medium tracking-[-0.01em] outline-none transition-colors focus-visible:ring-2 focus-visible:ring-[var(--accent)] text-[var(--muted)] hover:bg-[var(--surface-soft)] hover:text-[var(--accent-dark)]" href="<?= e(url_path('contact/')) ?>"><span>Contact</span></a>
            <a class="help-nav-mobile-link flex items-center gap-2.5 rounded-[14px] px-4 py-2.5 text-[14px] font-medium tracking-[-0.01em] outline-none transition-colors focus-visible:ring-2 focus-visible:ring-[var(--accent)] text-[var(--muted)] hover:bg-[var(--surface-soft)] hover:text-[var(--accent-dark)]" href="<?= e(url_path('testing/')) ?>"><span>Testing</span></a>
          </div>
        </div>
        <a class="rounded-[14px] px-4 py-3 text-[15px] font-medium tracking-[-0.01em] outline-none transition-colors focus-visible:ring-2 focus-visible:ring-[var(--accent)] text-[var(--ink)] hover:bg-[var(--surface-soft)] hover:text-[var(--accent-dark)]" href="<?= e(url_path('cart/')) ?>">Cart <span data-cart-count-inline></span></a>
        <a class="rounded-[14px] px-4 py-3 text-[15px] font-medium tracking-[-0.01em] outline-none transition-colors focus-visible:ring-2 focus-visible:ring-[var(--accent)] text-[var(--ink)] hover:bg-[var(--surface-soft)] hover:text-[var(--accent-dark)]" href="<?= e(url_path('account/')) ?>">Account</a>
      </nav>
      <form class="border-t border-[var(--line)] px-3 py-3 md:hidden" role="search" aria-label="Search shop" action="<?= e(url_path('search/')) ?>" method="get">
        <div class="flex items-center gap-2 rounded-[14px] bg-[var(--surface-soft)] px-3 py-2.5">
          <input name="q" type="search" placeholder="Search shop" aria-label="Search products" class="min-w-0 flex-1 bg-transparent text-[14px] text-[var(--ink)] outline-none placeholder:text-[var(--muted)]" value="<?= e((string)($_GET['q'] ?? '')) ?>">
          <button type="submit" aria-label="Search" class="flex h-8 w-8 items-center justify-center rounded-[10px] bg-[var(--accent)] text-white transition-colors hover:bg-[var(--accent-hover)] focus-visible:ring-2 focus-visible:ring-[var(--accent)] focus-visible:ring-offset-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M10.5 18C14.6421 18 18 14.6421 18 10.5C18 6.35786 14.6421 3 10.5 3C6.35786 3 3 6.35786 3 10.5C3 14.6421 6.35786 18 10.5 18Z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"></path><path d="M15.8 15.8L21 21" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"></path></svg>
          </button>
        </div>
      </form>
    </div>
  </div>
</header>
