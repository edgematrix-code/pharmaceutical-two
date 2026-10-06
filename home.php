<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

seo_set([
    'title'       => seo_title('Arail Pharmaceuticals - Lab-Tested Anabolics, Peptides & HGH'),
    'description' => seo_text('Shop independently lab-tested anabolics, peptides and HGH from Arail Pharmaceuticals. Discreet worldwide shipping, live stock and batch testing on every product.'),
    'canonical'   => '',
    'active_nav'  => 'home',
    'json_ld'     => [
        ld_organization(),
        ld_website(),
    ],
]);


require __DIR__ . '/includes/layout/head.php';
?>
<main class="min-w-0 max-w-full overflow-x-clip pb-16 pt-[7.75rem] sm:pt-[8.75rem]">
      <div class="space-y-5 sm:space-y-6">
        <section class="section-shell home-hero-section animate-soft-in" aria-label="Arail introduction">
          <div class="home-hero-card" style="--hero-bg-url: url(&quot;/assets/img/hero-bg.webp&quot;);">
            <div class="home-hero-content">
              <div class="home-hero-main">
                <div class="home-hero-copy">
                  <h1 class="home-hero-title"><span class="home-hero-title-line animate-fade-up" style="animation-delay: 0ms;">Tested.</span><span class="home-hero-title-line animate-fade-up" style="animation-delay: 80ms;">Verified.</span><span class="home-hero-title-line animate-fade-up" style="animation-delay: 160ms;">Trusted.</span></h1>
                  <p class="home-hero-desc animate-fade-up" style="animation-delay: 240ms;">We source the finest peptides and back everything with independent lab testing. Pure Anabolics. Zero compromises.</p>
                </div>
                <div class="home-hero-actions animate-fade-up" style="animation-delay: 320ms;"><a class="home-hero-btn" href="/shop/">Shop All</a></div>
              </div>
              <ul class="home-hero-badges animate-fade-up" style="animation-delay: 400ms;">
                <li class="home-hero-badge"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true" class="home-hero-badge-icon"><path d="M17.6453 8.03281C17.3508 7.725 17.0461 7.40781 16.9312 7.12891C16.825 6.87344 16.8187 6.45 16.8125 6.03984C16.8008 5.27734 16.7883 4.41328 16.1875 3.8125C15.5867 3.21172 14.7227 3.19922 13.9602 3.1875C13.55 3.18125 13.1266 3.175 12.8711 3.06875C12.593 2.95391 12.275 2.64922 11.9672 2.35469C11.4281 1.83672 10.8156 1.25 10 1.25C9.18437 1.25 8.57266 1.83672 8.03281 2.35469C7.725 2.64922 7.40781 2.95391 7.12891 3.06875C6.875 3.175 6.45 3.18125 6.03984 3.1875C5.27734 3.19922 4.41328 3.21172 3.8125 3.8125C3.21172 4.41328 3.20312 5.27734 3.1875 6.03984C3.18125 6.45 3.175 6.87344 3.06875 7.12891C2.95391 7.40703 2.64922 7.725 2.35469 8.03281C1.83672 8.57188 1.25 9.18437 1.25 10C1.25 10.8156 1.83672 11.4273 2.35469 11.9672C2.64922 12.275 2.95391 12.5922 3.06875 12.8711C3.175 13.1266 3.18125 13.55 3.1875 13.9602C3.19922 14.7227 3.21172 15.5867 3.8125 16.1875C4.41328 16.7883 5.27734 16.8008 6.03984 16.8125C6.45 16.8187 6.87344 16.825 7.12891 16.9312C7.40703 17.0461 7.725 17.3508 8.03281 17.6453C8.57188 18.1633 9.18437 18.75 10 18.75C10.8156 18.75 11.4273 18.1633 11.9672 17.6453C12.275 17.3508 12.5922 17.0461 12.8711 16.9312C13.1266 16.825 13.55 16.8187 13.9602 16.8125C14.7227 16.8008 15.5867 16.7883 16.1875 16.1875C16.7883 15.5867 16.8008 14.7227 16.8125 13.9602C16.8187 13.55 16.825 13.1266 16.9312 12.8711C17.0461 12.593 17.3508 12.275 17.6453 11.9672C18.1633 11.4281 18.75 10.8156 18.75 10C18.75 9.18437 18.1633 8.57266 17.6453 8.03281ZM13.5672 8.56719L9.19219 12.9422C9.13414 13.0003 9.06521 13.0464 8.98934 13.0779C8.91346 13.1093 8.83213 13.1255 8.75 13.1255C8.66787 13.1255 8.58654 13.1093 8.51066 13.0779C8.43479 13.0464 8.36586 13.0003 8.30781 12.9422L6.43281 11.0672C6.31554 10.9499 6.24965 10.7909 6.24965 10.625C6.24965 10.4591 6.31554 10.3001 6.43281 10.1828C6.55009 10.0655 6.70915 9.99965 6.875 9.99965C7.04085 9.99965 7.19991 10.0655 7.31719 10.1828L8.75 11.6164L12.6828 7.68281C12.7409 7.62474 12.8098 7.57868 12.8857 7.54725C12.9616 7.51583 13.0429 7.49965 13.125 7.49965C13.2071 7.49965 13.2884 7.51583 13.3643 7.54725C13.4402 7.57868 13.5091 7.62474 13.5672 7.68281C13.6253 7.74088 13.6713 7.80982 13.7027 7.88569C13.7342 7.96156 13.7503 8.04288 13.7503 8.125C13.7503 8.20712 13.7342 8.28844 13.7027 8.36431C13.6713 8.44018 13.6253 8.50912 13.5672 8.56719Z" fill="currentColor"></path></svg><span class="home-hero-badge-text">ISO 9001:2015</span></li>
                <li class="home-hero-badge"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true" class="home-hero-badge-icon"><path d="M17.6453 8.03281C17.3508 7.725 17.0461 7.40781 16.9312 7.12891C16.825 6.87344 16.8187 6.45 16.8125 6.03984C16.8008 5.27734 16.7883 4.41328 16.1875 3.8125C15.5867 3.21172 14.7227 3.19922 13.9602 3.1875C13.55 3.18125 13.1266 3.175 12.8711 3.06875C12.593 2.95391 12.275 2.64922 11.9672 2.35469C11.4281 1.83672 10.8156 1.25 10 1.25C9.18437 1.25 8.57266 1.83672 8.03281 2.35469C7.725 2.64922 7.40781 2.95391 7.12891 3.06875C6.875 3.175 6.45 3.18125 6.03984 3.1875C5.27734 3.19922 4.41328 3.21172 3.8125 3.8125C3.21172 4.41328 3.20312 5.27734 3.1875 6.03984C3.18125 6.45 3.175 6.87344 3.06875 7.12891C2.95391 7.40703 2.64922 7.725 2.35469 8.03281C1.83672 8.57188 1.25 9.18437 1.25 10C1.25 10.8156 1.83672 11.4273 2.35469 11.9672C2.64922 12.275 2.95391 12.5922 3.06875 12.8711C3.175 13.1266 3.18125 13.55 3.1875 13.9602C3.19922 14.7227 3.21172 15.5867 3.8125 16.1875C4.41328 16.7883 5.27734 16.8008 6.03984 16.8125C6.45 16.8187 6.87344 16.825 7.12891 16.9312C7.40703 17.0461 7.725 17.3508 8.03281 17.6453C8.57188 18.1633 9.18437 18.75 10 18.75C10.8156 18.75 11.4273 18.1633 11.9672 17.6453C12.275 17.3508 12.5922 17.0461 12.8711 16.9312C13.1266 16.825 13.55 16.8187 13.9602 16.8125C14.7227 16.8008 15.5867 16.7883 16.1875 16.1875C16.7883 15.5867 16.8008 14.7227 16.8125 13.9602C16.8187 13.55 16.825 13.1266 16.9312 12.8711C17.0461 12.593 17.3508 12.275 17.6453 11.9672C18.1633 11.4281 18.75 10.8156 18.75 10C18.75 9.18437 18.1633 8.57266 17.6453 8.03281ZM13.5672 8.56719L9.19219 12.9422C9.13414 13.0003 9.06521 13.0464 8.98934 13.0779C8.91346 13.1093 8.83213 13.1255 8.75 13.1255C8.66787 13.1255 8.58654 13.1093 8.51066 13.0779C8.43479 13.0464 8.36586 13.0003 8.30781 12.9422L6.43281 11.0672C6.31554 10.9499 6.24965 10.7909 6.24965 10.625C6.24965 10.4591 6.31554 10.3001 6.43281 10.1828C6.55009 10.0655 6.70915 9.99965 6.875 9.99965C7.04085 9.99965 7.19991 10.0655 7.31719 10.1828L8.75 11.6164L12.6828 7.68281C12.7409 7.62474 12.8098 7.57868 12.8857 7.54725C12.9616 7.51583 13.0429 7.49965 13.125 7.49965C13.2071 7.49965 13.2884 7.51583 13.3643 7.54725C13.4402 7.57868 13.5091 7.62474 13.5672 7.68281C13.6253 7.74088 13.6713 7.80982 13.7027 7.88569C13.7342 7.96156 13.7503 8.04288 13.7503 8.125C13.7503 8.20712 13.7342 8.28844 13.7027 8.36431C13.6713 8.44018 13.6253 8.50912 13.5672 8.56719Z" fill="currentColor"></path></svg><span class="home-hero-badge-text">Janoshik Approved</span></li>
                <li class="home-hero-badge"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true" class="home-hero-badge-icon"><path d="M17.6453 8.03281C17.3508 7.725 17.0461 7.40781 16.9312 7.12891C16.825 6.87344 16.8187 6.45 16.8125 6.03984C16.8008 5.27734 16.7883 4.41328 16.1875 3.8125C15.5867 3.21172 14.7227 3.19922 13.9602 3.1875C13.55 3.18125 13.1266 3.175 12.8711 3.06875C12.593 2.95391 12.275 2.64922 11.9672 2.35469C11.4281 1.83672 10.8156 1.25 10 1.25C9.18437 1.25 8.57266 1.83672 8.03281 2.35469C7.725 2.64922 7.40781 2.95391 7.12891 3.06875C6.875 3.175 6.45 3.18125 6.03984 3.1875C5.27734 3.19922 4.41328 3.21172 3.8125 3.8125C3.21172 4.41328 3.20312 5.27734 3.1875 6.03984C3.18125 6.45 3.175 6.87344 3.06875 7.12891C2.95391 7.40703 2.64922 7.725 2.35469 8.03281C1.83672 8.57188 1.25 9.18437 1.25 10C1.25 10.8156 1.83672 11.4273 2.35469 11.9672C2.64922 12.275 2.95391 12.5922 3.06875 12.8711C3.175 13.1266 3.18125 13.55 3.1875 13.9602C3.19922 14.7227 3.21172 15.5867 3.8125 16.1875C4.41328 16.7883 5.27734 16.8008 6.03984 16.8125C6.45 16.8187 6.87344 16.825 7.12891 16.9312C7.40703 17.0461 7.725 17.3508 8.03281 17.6453C8.57188 18.1633 9.18437 18.75 10 18.75C10.8156 18.75 11.4273 18.1633 11.9672 17.6453C12.275 17.3508 12.5922 17.0461 12.8711 16.9312C13.1266 16.825 13.55 16.8187 13.9602 16.8125C14.7227 16.8008 15.5867 16.7883 16.1875 16.1875C16.7883 15.5867 16.8008 14.7227 16.8125 13.9602C16.8187 13.55 16.825 13.1266 16.9312 12.8711C17.0461 12.593 17.3508 12.275 17.6453 11.9672C18.1633 11.4281 18.75 10.8156 18.75 10C18.75 9.18437 18.1633 8.57266 17.6453 8.03281ZM13.5672 8.56719L9.19219 12.9422C9.13414 13.0003 9.06521 13.0464 8.98934 13.0779C8.91346 13.1093 8.83213 13.1255 8.75 13.1255C8.66787 13.1255 8.58654 13.1093 8.51066 13.0779C8.43479 13.0464 8.36586 13.0003 8.30781 12.9422L6.43281 11.0672C6.31554 10.9499 6.24965 10.7909 6.24965 10.625C6.24965 10.4591 6.31554 10.3001 6.43281 10.1828C6.55009 10.0655 6.70915 9.99965 6.875 9.99965C7.04085 9.99965 7.19991 10.0655 7.31719 10.1828L8.75 11.6164L12.6828 7.68281C12.7409 7.62474 12.8098 7.57868 12.8857 7.54725C12.9616 7.51583 13.0429 7.49965 13.125 7.49965C13.2071 7.49965 13.2884 7.51583 13.3643 7.54725C13.4402 7.57868 13.5091 7.62474 13.5672 7.68281C13.6253 7.74088 13.6713 7.80982 13.7027 7.88569C13.7342 7.96156 13.7503 8.04288 13.7503 8.125C13.7503 8.20712 13.7342 8.28844 13.7027 8.36431C13.6713 8.44018 13.6253 8.50912 13.5672 8.56719Z" fill="currentColor"></path></svg><span class="home-hero-badge-text">Priority Shipping</span></li>
              </ul>
            </div>
            <div class="home-hero-products" aria-hidden="true">
              <div class="home-hero-product"><picture><source type="image/webp" srcset="/assets/img/magnific_y6SO3nBPW9-1.webp"><img fetchpriority="high" decoding="async" alt="Testosterone Cypionate injectable vial" loading="eager" src="/assets/img/magnific_y6SO3nBPW9-1.png"></picture></div>
            </div>
          </div>
        </section>

        <section class="trust-signals-bar relative left-1/2 w-screen max-w-[100vw] -translate-x-1/2 overflow-hidden border-y border-[var(--line)] bg-[rgba(255,255,255,0.78)] py-3 backdrop-blur-sm sm:py-3.5" aria-label="Trust signals">
          <div class="trust-signals-scroll">
            <ul class="trust-signals-track" aria-hidden="false">
              <li class="trust-signals-item flex shrink-0 items-center gap-2.5 px-5 sm:px-7"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true" class="shrink-0 text-[var(--accent)]"><path d="M7.2 16.8h8.2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"></path><path d="M8.6 16.8V9.4a2.4 2.4 0 0 1 2.4-2.4h.2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path><path d="M11.2 7V3.8M10.1 3.8h2.2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"></path><circle cx="13.6" cy="11.2" r="2.6" stroke="currentColor" stroke-width="1.4"></circle><path d="M13.6 13.8v3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"></path></svg><span class="whitespace-nowrap text-[13px] font-semibold tracking-[-0.01em] text-[var(--ink)] sm:text-[14px]">Independent testing</span></li>
              <li class="trust-signals-item flex shrink-0 items-center gap-2.5 px-5 sm:px-7"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true" class="shrink-0 text-[var(--accent)]"><path d="M8.2 2.5h3.6M9 2.5v4.1L5.4 13.2a3.1 3.1 0 0 0 2.65 4.6h4.9a3.1 3.1 0 0 0 2.65-4.6L11 6.6V2.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path><path d="M7.1 11.6h5.8" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"></path><circle cx="12.6" cy="14.2" r="0.9" fill="currentColor"></circle></svg><span class="whitespace-nowrap text-[13px] font-semibold tracking-[-0.01em] text-[var(--ink)] sm:text-[14px]">Third-party lab tested</span></li>
              <li class="trust-signals-item flex shrink-0 items-center gap-2.5 px-5 sm:px-7"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true" class="shrink-0 text-[var(--accent)]"><path d="M10 2.2 3.5 5v4.8c0 3.6 2.8 6.4 6.5 7.2 3.7-.8 6.5-3.6 6.5-7.2V5L10 2.2Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"></path><path d="m7.4 10.2 1.8 1.8 3.8-3.8" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path></svg><span class="whitespace-nowrap text-[13px] font-semibold tracking-[-0.01em] text-[var(--ink)] sm:text-[14px]">Verified purity</span></li>
              <li class="trust-signals-item flex shrink-0 items-center gap-2.5 px-5 sm:px-7"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true" class="shrink-0 text-[var(--accent)]"><path d="M5.5 2.8h7.2L15.2 5.3v11.4a1.2 1.2 0 0 1-1.2 1.2H5.5a1.2 1.2 0 0 1-1.2-1.2V4a1.2 1.2 0 0 1 1.2-1.2Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"></path><path d="M12.5 2.9v2.6h2.5" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"></path><path d="M7 8.2h6M7 11h4.2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"></path><circle cx="12.4" cy="14.2" r="2" stroke="currentColor" stroke-width="1.3"></circle><path d="M12.4 16.2v1.6l.9-.5.9.5V16.2" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"></path></svg><span class="whitespace-nowrap text-[13px] font-semibold tracking-[-0.01em] text-[var(--ink)] sm:text-[14px]">COA available</span></li>
              <li class="trust-signals-item flex shrink-0 items-center gap-2.5 px-5 sm:px-7"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true" class="shrink-0 text-[var(--accent)]"><rect x="4.5" y="8.5" width="11" height="8" rx="1.5" stroke="currentColor" stroke-width="1.4"></rect><path d="M7 8.5V6.8a3 3 0 0 1 6 0V8.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"></path><circle cx="10" cy="12.5" r="1" fill="currentColor"></circle></svg><span class="whitespace-nowrap text-[13px] font-semibold tracking-[-0.01em] text-[var(--ink)] sm:text-[14px]">Secure checkout</span></li>
              <li class="trust-signals-item flex shrink-0 items-center gap-2.5 px-5 sm:px-7"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true" class="shrink-0 text-[var(--accent)]"><path d="M3.4 6.8 10 3.4l6.6 3.4v6.8L10 16.6 3.4 13.6V6.8Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"></path><path d="M3.4 6.8 10 10.2l6.6-3.4M10 10.2v6.4" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"></path><path d="m6.2 5.4 7.6 3.9" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" opacity="0.85"></path></svg><span class="whitespace-nowrap text-[13px] font-semibold tracking-[-0.01em] text-[var(--ink)] sm:text-[14px]">Discreet shipping</span></li>
              <li class="trust-signals-item flex shrink-0 items-center gap-2.5 px-5 sm:px-7"><img loading="lazy" decoding="async" alt="" width="20" height="20" aria-hidden="true" class="shrink-0" src="/assets/img/bitcoin-logo.svg" style="width: 20px; height: 20px; display: block;"><span class="whitespace-nowrap text-[13px] font-semibold tracking-[-0.01em] text-[var(--ink)] sm:text-[14px]">Bitcoin accepted</span></li>
              <li class="trust-signals-item flex shrink-0 items-center gap-2.5 px-5 sm:px-7" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true" class="shrink-0 text-[var(--accent)]"><path d="M7.2 16.8h8.2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"></path><path d="M8.6 16.8V9.4a2.4 2.4 0 0 1 2.4-2.4h.2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path><path d="M11.2 7V3.8M10.1 3.8h2.2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"></path><circle cx="13.6" cy="11.2" r="2.6" stroke="currentColor" stroke-width="1.4"></circle><path d="M13.6 13.8v3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"></path></svg><span class="whitespace-nowrap text-[13px] font-semibold tracking-[-0.01em] text-[var(--ink)] sm:text-[14px]">Independent testing</span></li>
              <li class="trust-signals-item flex shrink-0 items-center gap-2.5 px-5 sm:px-7" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true" class="shrink-0 text-[var(--accent)]"><path d="M8.2 2.5h3.6M9 2.5v4.1L5.4 13.2a3.1 3.1 0 0 0 2.65 4.6h4.9a3.1 3.1 0 0 0 2.65-4.6L11 6.6V2.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path><path d="M7.1 11.6h5.8" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"></path><circle cx="12.6" cy="14.2" r="0.9" fill="currentColor"></circle></svg><span class="whitespace-nowrap text-[13px] font-semibold tracking-[-0.01em] text-[var(--ink)] sm:text-[14px]">Third-party lab tested</span></li>
              <li class="trust-signals-item flex shrink-0 items-center gap-2.5 px-5 sm:px-7" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true" class="shrink-0 text-[var(--accent)]"><path d="M10 2.2 3.5 5v4.8c0 3.6 2.8 6.4 6.5 7.2 3.7-.8 6.5-3.6 6.5-7.2V5L10 2.2Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"></path><path d="m7.4 10.2 1.8 1.8 3.8-3.8" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path></svg><span class="whitespace-nowrap text-[13px] font-semibold tracking-[-0.01em] text-[var(--ink)] sm:text-[14px]">Verified purity</span></li>
              <li class="trust-signals-item flex shrink-0 items-center gap-2.5 px-5 sm:px-7" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true" class="shrink-0 text-[var(--accent)]"><path d="M5.5 2.8h7.2L15.2 5.3v11.4a1.2 1.2 0 0 1-1.2 1.2H5.5a1.2 1.2 0 0 1-1.2-1.2V4a1.2 1.2 0 0 1 1.2-1.2Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"></path><path d="M12.5 2.9v2.6h2.5" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"></path><path d="M7 8.2h6M7 11h4.2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"></path><circle cx="12.4" cy="14.2" r="2" stroke="currentColor" stroke-width="1.3"></circle><path d="M12.4 16.2v1.6l.9-.5.9.5V16.2" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"></path></svg><span class="whitespace-nowrap text-[13px] font-semibold tracking-[-0.01em] text-[var(--ink)] sm:text-[14px]">COA available</span></li>
              <li class="trust-signals-item flex shrink-0 items-center gap-2.5 px-5 sm:px-7" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true" class="shrink-0 text-[var(--accent)]"><rect x="4.5" y="8.5" width="11" height="8" rx="1.5" stroke="currentColor" stroke-width="1.4"></rect><path d="M7 8.5V6.8a3 3 0 0 1 6 0V8.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"></path><circle cx="10" cy="12.5" r="1" fill="currentColor"></circle></svg><span class="whitespace-nowrap text-[13px] font-semibold tracking-[-0.01em] text-[var(--ink)] sm:text-[14px]">Secure checkout</span></li>
              <li class="trust-signals-item flex shrink-0 items-center gap-2.5 px-5 sm:px-7" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true" class="shrink-0 text-[var(--accent)]"><path d="M3.4 6.8 10 3.4l6.6 3.4v6.8L10 16.6 3.4 13.6V6.8Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"></path><path d="M3.4 6.8 10 10.2l6.6-3.4M10 10.2v6.4" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"></path><path d="m6.2 5.4 7.6 3.9" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" opacity="0.85"></path></svg><span class="whitespace-nowrap text-[13px] font-semibold tracking-[-0.01em] text-[var(--ink)] sm:text-[14px]">Discreet shipping</span></li>
              <li class="trust-signals-item flex shrink-0 items-center gap-2.5 px-5 sm:px-7" aria-hidden="true"><img loading="lazy" decoding="async" alt="" width="20" height="20" aria-hidden="true" class="shrink-0" src="/assets/img/bitcoin-logo.svg" style="width: 20px; height: 20px; display: block;"><span class="whitespace-nowrap text-[13px] font-semibold tracking-[-0.01em] text-[var(--ink)] sm:text-[14px]">Bitcoin accepted</span></li>
            </ul>
          </div>
          <span class="sr-only">Independent testing. Third-party lab tested. Verified purity. COA available. Secure checkout. Discreet shipping. Bitcoin accepted</span>
        </section>

        <section class="section-shell animate-soft-in" style="animation-delay: 100ms;">
          <div class="surface-card products-section p-5 sm:p-8 lg:p-10">
            <header class="products-section-header">
              <div class="products-section-intro">
                <h2 class="products-section-title">Products</h2>
                <p class="products-section-desc">Each peptide is tested for purity and potency before shipping</p>
              </div>
              <a class="products-section-view-all" href="/shop/">View all<span aria-hidden="true">→</span></a>
            </header>
            <div class="products-section-filters" role="tablist" aria-label="Product categories">
              <a role="tab" aria-selected="true" class="products-filter-chip is-active" href="#products"><span class="products-filter-label">Injectable Anabolics</span><span class="products-filter-count">21</span></a>
              <a role="tab" aria-selected="false" class="products-filter-chip" href="#products"><span class="products-filter-label">Oral Anabolics</span><span class="products-filter-count">10</span></a>
              <a role="tab" aria-selected="false" class="products-filter-chip" href="#products"><span class="products-filter-label">Peptides &amp; HGH</span><span class="products-filter-count">21</span></a>
            </div>
            <div id="products">
              <div class="products-grid">
                <?php if (($card = catalog_product('test-cyp-200')) && (int)$card['is_active'] === 1): $card_heading = 'h3'; require __DIR__ . '/includes/partials/product-card.php'; endif; ?>
                <?php if (($card = catalog_product('test-enanthate-300')) && (int)$card['is_active'] === 1): $card_heading = 'h3'; require __DIR__ . '/includes/partials/product-card.php'; endif; ?>
                <?php if (($card = catalog_product('equipose-300')) && (int)$card['is_active'] === 1): $card_heading = 'h3'; require __DIR__ . '/includes/partials/product-card.php'; endif; ?>
                <?php if (($card = catalog_product('tren-acetate-100')) && (int)$card['is_active'] === 1): $card_heading = 'h3'; require __DIR__ . '/includes/partials/product-card.php'; endif; ?>
                <?php if (($card = catalog_product('sustanon-250')) && (int)$card['is_active'] === 1): $card_heading = 'h3'; require __DIR__ . '/includes/partials/product-card.php'; endif; ?>
                <?php if (($card = catalog_product('test-prop-100')) && (int)$card['is_active'] === 1): $card_heading = 'h3'; require __DIR__ . '/includes/partials/product-card.php'; endif; ?>
                <?php if (($card = catalog_product('mast-prop-100')) && (int)$card['is_active'] === 1): $card_heading = 'h3'; require __DIR__ . '/includes/partials/product-card.php'; endif; ?>
                <?php if (($card = catalog_product('nandrolone-prop-100')) && (int)$card['is_active'] === 1): $card_heading = 'h3'; require __DIR__ . '/includes/partials/product-card.php'; endif; ?>
                <?php if (($card = catalog_product('superdrol-40')) && (int)$card['is_active'] === 1): $card_heading = 'h3'; require __DIR__ . '/includes/partials/product-card.php'; endif; ?>
                <?php if (($card = catalog_product('tren-enanthate-200')) && (int)$card['is_active'] === 1): $card_heading = 'h3'; require __DIR__ . '/includes/partials/product-card.php'; endif; ?>
                <?php if (($card = catalog_product('nandrolone-deca-300')) && (int)$card['is_active'] === 1): $card_heading = 'h3'; require __DIR__ . '/includes/partials/product-card.php'; endif; ?>
                <?php if (($card = catalog_product('test-cyp-50mg-ml')) && (int)$card['is_active'] === 1): $card_heading = 'h3'; require __DIR__ . '/includes/partials/product-card.php'; endif; ?>
              </div>
            </div>
          </div>
        </section>

        <section class="section-shell animate-soft-in" style="animation-delay: 140ms;">
          <div class="surface-card p-5 sm:p-8 lg:p-10">
            <section class="stack-bundles-cta" aria-label="Protocol stacks">
              <div class="stack-bundles-cta__copy">
                <p class="stack-bundles-cta__eyebrow">Protocol stacks</p>
                <h2 class="stack-bundles-cta__title">Save 12–15% on curated kits</h2>
                <p class="stack-bundles-cta__desc">Peptide kits at 12% off · 12-week cycle stacks at 15% off — discount applied at cart.</p>
              </div>
              <a class="btn-primary stack-bundles-cta__btn" href="/stacks/">View stacks</a>
            </section>
          </div>
        </section>

        <section class="section-shell animate-soft-in" style="animation-delay: 180ms;">
          <div class="surface-card relative overflow-hidden p-6 sm:p-10 lg:p-12">
            <img alt="" class="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-35" src="/assets/img/hero-bg.webp" loading="lazy" decoding="async">
            <div class="relative z-[1] grid items-center gap-10 lg:grid-cols-2">
              <div class="space-y-6">
                <h2 class="text-[clamp(2.25rem,5vw,4rem)] font-bold leading-[1.1] text-[var(--ink)]"><span class="block">Third-party</span><span class="block">lab report</span></h2>
                <p class="max-w-md text-base font-light leading-6 text-[var(--ink)]">We believe in transparency. Every peptide we sell comes with independent lab analysis confirming purity, potency, and composition. You get the facts.</p>
                <a href="/assets/img/jano-dbol-inj-batch-2-1.png" class="btn-primary">Open report</a>
              </div>
              <div class="overflow-hidden rounded-[20px] border border-[var(--line)] bg-white shadow-[var(--shadow)]">
                <picture><source type="image/webp" srcset="/assets/img/jano-dbol-inj-batch-2-1.webp"><img loading="lazy" decoding="async" alt="Lab test report" class="aspect-[4/3] w-full object-cover object-top" src="/assets/img/jano-dbol-inj-batch-2-1.png"></picture>
                <div class="space-y-4 p-5 sm:p-6">
                  <img loading="lazy" decoding="async" alt="Janoshik" class="h-8 w-auto object-contain" src="/assets/img/logo-1.svg">
                  <p class="text-sm leading-relaxed text-[var(--muted-2)]">Janoshik is an independent testing laboratory offering reliable and verifiable analytical services.</p>
                  <ul class="space-y-2 text-sm font-medium text-[var(--ink)]">
                    <li class="flex items-center gap-2"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true" class="shrink-0 text-[var(--badge)]"><path d="M10 1.25c.82 0 1.43.59 1.97 1.1.31.3.63.6.9.72.26.11.68.11 1.09.12.76.01 1.63.02 2.23.63.6.6.62 1.46.63 2.23.01.41.01.83.12 1.09.12.28.42.59.72.9.52.54 1.1 1.15 1.1 1.97s-.59 1.43-1.1 1.97c-.3.31-.6.63-.72.9-.11.26-.11.68-.12 1.09-.01.76-.02 1.63-.63 2.23-.6.6-1.46.62-2.23.63-.41.01-.83.01-1.09.12-.28.12-.59.42-.9.72-.54.52-1.15 1.1-1.97 1.1s-1.43-.59-1.97-1.1c-.31-.3-.63-.6-.9-.72-.26-.11-.68-.11-1.09-.12-.76-.01-1.63-.02-2.23-.63-.6-.6-.62-1.46-.63-2.23-.01-.41-.01-.83-.12-1.09-.12-.28-.42-.59-.72-.9C1.84 11.43 1.25 10.82 1.25 10s.59-1.43 1.1-1.97c.3-.31.6-.63.72-.9.11-.26.11-.68.12-1.09.01-.76.02-1.63.63-2.23.6-.6 1.46-.62 2.23-.63.41-.01.83-.01 1.09-.12.28-.12.59-.42.9-.72.54-.52 1.15-1.1 1.97-1.1Zm3.57 7.32L9.19 12.94a.94.94 0 0 1-1.32 0L6.43 11.07a.94.94 0 1 1 1.32-1.32L8.75 11.62l3.93-3.93a.94.94 0 0 1 1.33 1.33Z" fill="currentColor"></path></svg> Over 10 years of experience</li>
                    <li class="flex items-center gap-2"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true" class="shrink-0 text-[var(--badge)]"><path d="M10 1.25c.82 0 1.43.59 1.97 1.1.31.3.63.6.9.72.26.11.68.11 1.09.12.76.01 1.63.02 2.23.63.6.6.62 1.46.63 2.23.01.41.01.83.12 1.09.12.28.42.59.72.9.52.54 1.1 1.15 1.1 1.97s-.59 1.43-1.1 1.97c-.3.31-.6.63-.72.9-.11.26-.11.68-.12 1.09-.01.76-.02 1.63-.63 2.23-.6.6-1.46.62-2.23.63-.41.01-.83.01-1.09.12-.28.12-.59.42-.9.72-.54.52-1.15 1.1-1.97 1.1s-1.43-.59-1.97-1.1c-.31-.3-.63-.6-.9-.72-.26-.11-.68-.11-1.09-.12-.76-.01-1.63-.02-2.23-.63-.6-.6-.62-1.46-.63-2.23-.01-.41-.01-.83-.12-1.09-.12-.28-.42-.59-.72-.9C1.84 11.43 1.25 10.82 1.25 10s.59-1.43 1.1-1.97c.3-.31.6-.63.72-.9.11-.26.11-.68.12-1.09.01-.76.02-1.63.63-2.23.6-.6 1.46-.62 2.23-.63.41-.01.83-.01 1.09-.12.28-.12.59-.42.9-.72.54-.52 1.15-1.1 1.97-1.1Zm3.57 7.32L9.19 12.94a.94.94 0 0 1-1.32 0L6.43 11.07a.94.94 0 1 1 1.32-1.32L8.75 11.62l3.93-3.93a.94.94 0 0 1 1.33 1.33Z" fill="currentColor"></path></svg> State-of-the-art laboratory equipment</li>
                    <li class="flex items-center gap-2"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true" class="shrink-0 text-[var(--badge)]"><path d="M10 1.25c.82 0 1.43.59 1.97 1.1.31.3.63.6.9.72.26.11.68.11 1.09.12.76.01 1.63.02 2.23.63.6.6.62 1.46.63 2.23.01.41.01.83.12 1.09.12.28.42.59.72.9.52.54 1.1 1.15 1.1 1.97s-.59 1.43-1.1 1.97c-.3.31-.6.63-.72.9-.11.26-.11.68-.12 1.09-.01.76-.02 1.63-.63 2.23-.6.6-1.46.62-2.23.63-.41.01-.83.01-1.09.12-.28.12-.59.42-.9.72-.54.52-1.15 1.1-1.97 1.1s-1.43-.59-1.97-1.1c-.31-.3-.63-.6-.9-.72-.26-.11-.68-.11-1.09-.12-.76-.01-1.63-.02-2.23-.63-.6-.6-.62-1.46-.63-2.23-.01-.41-.01-.83-.12-1.09-.12-.28-.42-.59-.72-.9C1.84 11.43 1.25 10.82 1.25 10s.59-1.43 1.1-1.97c.3-.31.6-.63.72-.9.11-.26.11-.68.12-1.09.01-.76.02-1.63.63-2.23.6-.6 1.46-.62 2.23-.63.41-.01.83-.01 1.09-.12.28-.12.59-.42.9-.72.54-.52 1.15-1.1 1.97-1.1Zm3.57 7.32L9.19 12.94a.94.94 0 0 1-1.32 0L6.43 11.07a.94.94 0 1 1 1.32-1.32L8.75 11.62l3.93-3.93a.94.94 0 0 1 1.33 1.33Z" fill="currentColor"></path></svg> Globally recognized &amp; trusted</li>
                  </ul>
                  <a href="https://janoshik.com/" class="inline-flex text-sm font-bold uppercase tracking-[0.04em] text-[var(--accent)] hover:text-[var(--accent-hover)]">Visit Janoshik →</a>
                </div>
              </div>
            </div>
          </div>
        </section>

        <section class="section-shell animate-soft-in" aria-labelledby="community-trust-heading" style="animation-delay: 210ms;">
          <div class="surface-card community-trust">
            <div class="community-trust__inner">
              <h2 id="community-trust-heading" class="community-trust__title">Don't just take our word for it</h2>
              <p class="community-trust__subtitle">Independent community reviews across the forums and source-rating sites people actually use.</p>
              <ul class="community-trust__links">
                <li><a href="https://www.steroidsourcetalk.cc/index.php?threads/source-arail-pharmaceuticals-https-arailpharma-is-quality-and-customer-service-personified-serving-the-community-to-our-utmost-ability.18505/" class="community-trust__link community-trust__link--sst"><picture><source type="image/webp" srcset="/assets/img/sst-logo.webp"><img loading="lazy" decoding="async" alt="Steroid Source Talk" class="community-trust__logo community-trust__logo--sst" width="160" height="56" src="/assets/img/sst-logo.png"></picture></a></li>
                <li><a href="https://thinksteroids.com/community/threads/arail-pharmaceuticals-us-domestic.134426367/" class="community-trust__link community-trust__link--meso"><picture><source type="image/webp" srcset="/assets/img/meso-rx-logo.webp"><img loading="lazy" decoding="async" alt="MESO-Rx" class="community-trust__logo community-trust__logo--meso" width="200" height="56" src="/assets/img/meso-rx-logo.png"></picture></a></li>
              </ul>
            </div>
          </div>
        </section>

        <section class="section-shell animate-soft-in" aria-labelledby="customer-reviews-heading" style="animation-delay: 220ms;">
          <div class="surface-card reviews-section">
            <header class="reviews-section__header">
              <div class="reviews-section__intro">
                <h2 id="customer-reviews-heading" class="reviews-section__title">Trusted by the community</h2>
                <p class="reviews-section__subtitle">Real feedback from customers and the forums — shipping speed, packaging, and quality.</p>
              </div>
              <div class="reviews-section__links">
                <a href="https://www.steroidsourcetalk.cc/index.php?threads/source-arail-pharmaceuticals-https-arailpharma-is-quality-and-customer-service-personified-serving-the-community-to-our-utmost-ability.18505/" class="reviews-section__link">See more on SST<span aria-hidden="true">→</span></a>
                <a class="reviews-section__link" href="/reviews/">Leave a review<span aria-hidden="true">→</span></a>
              </div>
            </header>
            <div class="reviews-carousel">
              <div class="reviews-carousel__track reviews-section__grid" tabindex="0" role="list" aria-label="Customer review cards" aria-roledescription="carousel">
                <div class="reviews-carousel__item" role="listitem" aria-label="Review 1 of 12" aria-current="true">
                  <article class="review-card review-card--full">
                    <div class="review-card__photo"><picture><source type="image/webp" srcset="/assets/img/review-4-1024x768.webp"><img alt="Bryce's product photo" loading="lazy" src="/assets/img/review-4-1024x768.jpg"></picture></div>
                    <header class="review-card__header"><span class="review-card__avatar" aria-hidden="true" style="background-color: rgb(39, 137, 180);">BR</span><div class="review-card__meta"><div class="review-card__name-row"><span class="review-card__name">Bryce</span><span class="review-card__source">site</span></div><time class="review-card__date">Sep 2026</time></div></header>
                    <blockquote class="review-card__quote"><p>“Best product. Prefer arail over any other brand.”</p></blockquote>
                  </article>
                </div>
                <div class="reviews-carousel__item" role="listitem" aria-label="Review 2 of 12">
                  <article class="review-card review-card--full">
                    <div class="review-card__photo"><picture><source type="image/webp" srcset="/assets/img/review-3-1024x768.webp"><img alt="Cody conn's product photo" loading="lazy" src="/assets/img/review-3-1024x768.jpg"></picture></div>
                    <header class="review-card__header"><span class="review-card__avatar" aria-hidden="true" style="background-color: rgb(61, 155, 181);">CC</span><div class="review-card__meta"><div class="review-card__name-row"><span class="review-card__name">Cody conn</span><span class="review-card__source">site</span></div><time class="review-card__date">Sep 2026</time></div></header>
                    <blockquote class="review-card__quote"><p>“Great source always great packaging and always on time becoming probably one of if not the best on sst definitely very reliable”</p></blockquote>
                  </article>
                </div>
                <div class="reviews-carousel__item" role="listitem" aria-label="Review 3 of 12">
                  <article class="review-card review-card--full">
                    <div class="review-card__photo"><picture><source type="image/webp" srcset="/assets/img/review-2-1024x768.webp"><img alt="Rafael's product photo" loading="lazy" src="/assets/img/review-2-1024x768.jpg"></picture></div>
                    <header class="review-card__header"><span class="review-card__avatar" aria-hidden="true" style="background-color: rgb(74, 124, 140);">RA</span><div class="review-card__meta"><div class="review-card__name-row"><span class="review-card__name">Rafael</span><span class="review-card__source">site</span></div><time class="review-card__date">Sep 2026</time></div></header>
                    <blockquote class="review-card__quote"><p>“The whole process was smooth, arrived in 3 days and very professionally packed. Will purchase again.”</p></blockquote>
                  </article>
                </div>
                <div class="reviews-carousel__item" role="listitem" aria-label="Review 4 of 12">
                  <article class="review-card review-card--full">
                    <div class="review-card__photo"><picture><source type="image/webp" srcset="/assets/img/review-1-576x1024.webp"><img alt="Landon's product photo" loading="lazy" src="/assets/img/review-1-576x1024.jpg"></picture></div>
                    <header class="review-card__header"><span class="review-card__avatar" aria-hidden="true" style="background-color: rgb(72, 168, 121);">LA</span><div class="review-card__meta"><div class="review-card__name-row"><span class="review-card__name">Landon</span><span class="review-card__source">site</span></div><time class="review-card__date">Sep 2026</time></div></header>
                    <blockquote class="review-card__quote"><p>“Amazing fast and discrete”</p></blockquote>
                  </article>
                </div>
                <div class="reviews-carousel__item" role="listitem" aria-label="Review 5 of 12">
                  <article class="review-card review-card--full">
                    <div class="review-card__photo"><picture><source type="image/webp" srcset="/assets/img/review-1024x768.webp"><img alt="Dasgovtron's product photo" loading="lazy" src="/assets/img/review-1024x768.jpg"></picture></div>
                    <header class="review-card__header"><span class="review-card__avatar" aria-hidden="true" style="background-color: rgb(91, 184, 217);">DA</span><div class="review-card__meta"><div class="review-card__name-row"><span class="review-card__name">Dasgovtron</span><span class="review-card__source">site</span></div><time class="review-card__date">Sep 2026</time></div></header>
                    <blockquote class="review-card__quote"><p>“Always fast and product's on point, been my go to for awhile now.”</p></blockquote>
                  </article>
                </div>
                <div class="reviews-carousel__item" role="listitem" aria-label="Review 6 of 12">
                  <article class="review-card review-card--full">
                    <div class="review-card__photo"><picture><source type="image/webp" srcset="/assets/img/IMG_8756-1024x768.webp"><img alt="Houndzzv1's product photo" loading="lazy" src="/assets/img/IMG_8756-1024x768.jpg"></picture></div>
                    <header class="review-card__header"><span class="review-card__avatar" aria-hidden="true" style="background-color: rgb(91, 184, 217);">HO</span><div class="review-card__meta"><div class="review-card__name-row"><span class="review-card__name">Houndzzv1</span><span class="review-card__source">site</span></div><time class="review-card__date">Sep 2026</time></div></header>
                    <blockquote class="review-card__quote"><p>“Shipping and customer service was great!!!”</p></blockquote>
                  </article>
                </div>
                <div class="reviews-carousel__item" role="listitem" aria-label="Review 7 of 12">
                  <article class="review-card review-card--full">
                    <div class="review-card__photo"><picture><source type="image/webp" srcset="/assets/img/tempImageqV5KTE-1024x768.webp"><img alt="ogs's product photo" loading="lazy" src="/assets/img/tempImageqV5KTE-1024x768.jpg"></picture></div>
                    <header class="review-card__header"><span class="review-card__avatar" aria-hidden="true" style="background-color: rgb(61, 155, 181);">OG</span><div class="review-card__meta"><div class="review-card__name-row"><span class="review-card__name">ogs</span><span class="review-card__source">site</span></div><time class="review-card__date">Sep 2026</time></div></header>
                    <blockquote class="review-card__quote"><p>“amazing shipping and quality.”</p></blockquote>
                  </article>
                </div>
                <div class="reviews-carousel__item" role="listitem" aria-label="Review 8 of 12">
                  <article class="review-card review-card--full">
                    <div class="review-card__photo"><picture><source type="image/webp" srcset="/assets/img/image-4-1024x768.webp"><img alt="Sam Natters's product photo" loading="lazy" src="/assets/img/image-4-1024x768.jpg"></picture></div>
                    <header class="review-card__header"><span class="review-card__avatar" aria-hidden="true" style="background-color: rgb(72, 168, 121);">SN</span><div class="review-card__meta"><div class="review-card__name-row"><span class="review-card__name">Sam Natters</span><span class="review-card__source">site</span></div><time class="review-card__date">Sep 2026</time></div></header>
                    <blockquote class="review-card__quote"><p>“Amazing product!!!! I have been using the Test E for some time now and it is amazing.”</p></blockquote>
                  </article>
                </div>
                <div class="reviews-carousel__item" role="listitem" aria-label="Review 9 of 12">
                  <article class="review-card review-card--full">
                    <div class="review-card__photo"><picture><source type="image/webp" srcset="/assets/img/IMG_4451-576x1024.webp"><img alt="Brenda Nolen's product photo" loading="lazy" src="/assets/img/IMG_4451-576x1024.jpg"></picture></div>
                    <header class="review-card__header"><span class="review-card__avatar" aria-hidden="true" style="background-color: rgb(107, 143, 158);">BN</span><div class="review-card__meta"><div class="review-card__name-row"><span class="review-card__name">Brenda Nolen</span><span class="review-card__source">site</span></div><time class="review-card__date">Sep 2026</time></div></header>
                    <blockquote class="review-card__quote"><p>“Shipping was quick and everything arrived in perfect condition. I have been completely satisfied with every purchase.”</p></blockquote>
                  </article>
                </div>
                <div class="reviews-carousel__item" role="listitem" aria-label="Review 10 of 12">
                  <article class="review-card review-card--full">
                    <div class="review-card__photo"><picture><source type="image/webp" srcset="/assets/img/IMG_3863-1024x768.webp"><img alt="Rogerb813's product photo" loading="lazy" src="/assets/img/IMG_3863-1024x768.jpg"></picture></div>
                    <header class="review-card__header"><span class="review-card__avatar" aria-hidden="true" style="background-color: rgb(61, 155, 181);">RO</span><div class="review-card__meta"><div class="review-card__name-row"><span class="review-card__name">Rogerb813</span><span class="review-card__source">site</span></div><time class="review-card__date">Sep 2026</time></div></header>
                    <blockquote class="review-card__quote"><p>“Have been using Arail for a while. Always predictable service and delivery. If you have a question they are quick to answer. Now that they are carrying more I will be buying more. Get your juice straight from The Lemon!”</p></blockquote>
                  </article>
                </div>
                <div class="reviews-carousel__item" role="listitem" aria-label="Review 11 of 12">
                  <article class="review-card review-card--full">
                    <div class="review-card__photo"><picture><source type="image/webp" srcset="/assets/img/image-3-1024x768.webp"><img alt="Stumpgrinder's product photo" loading="lazy" src="/assets/img/image-3-1024x768.jpg"></picture></div>
                    <header class="review-card__header"><span class="review-card__avatar" aria-hidden="true" style="background-color: rgb(45, 138, 171);">ST</span><div class="review-card__meta"><div class="review-card__name-row"><span class="review-card__name">Stumpgrinder</span><span class="review-card__source">site</span></div><time class="review-card__date">Sep 2026</time></div></header>
                    <blockquote class="review-card__quote"><p>“Always a quick ship great communication even better product”</p></blockquote>
                  </article>
                </div>
                <div class="reviews-carousel__item" role="listitem" aria-label="Review 12 of 12">
                  <article class="review-card review-card--full">
                    <div class="review-card__photo"><picture><source type="image/webp" srcset="/assets/img/image-2-1024x768.webp"><img alt="Lsuper3's product photo" loading="lazy" src="/assets/img/image-2-1024x768.jpg"></picture></div>
                    <header class="review-card__header"><span class="review-card__avatar" aria-hidden="true" style="background-color: rgb(71, 169, 212);">LS</span><div class="review-card__meta"><div class="review-card__name-row"><span class="review-card__name">Lsuper3</span><span class="review-card__source">site</span></div><time class="review-card__date">Sep 2026</time></div></header>
                    <blockquote class="review-card__quote"><p>“Shipping usually comes in 5-7 business days. As you can see from the photo the product comes in a vacuum sealed bag and a pouch to protect the contents during shipping. I’ve been very happy with the quality and the performance it provides.”</p></blockquote>
                  </article>
                </div>
              </div>
              <div class="reviews-carousel__controls" role="group" aria-label="Review slides">
                <button type="button" class="reviews-carousel__arrow" aria-label="Previous review" disabled><span aria-hidden="true">‹</span></button>
                <div class="reviews-carousel__dots">
                  <button type="button" class="reviews-carousel__dot is-active" aria-label="Go to review 1" aria-current="true"></button>
                  <button type="button" class="reviews-carousel__dot" aria-label="Go to review 2"></button>
                  <button type="button" class="reviews-carousel__dot" aria-label="Go to review 3"></button>
                  <button type="button" class="reviews-carousel__dot" aria-label="Go to review 4"></button>
                  <button type="button" class="reviews-carousel__dot" aria-label="Go to review 5"></button>
                  <button type="button" class="reviews-carousel__dot" aria-label="Go to review 6"></button>
                  <button type="button" class="reviews-carousel__dot" aria-label="Go to review 7"></button>
                  <button type="button" class="reviews-carousel__dot" aria-label="Go to review 8"></button>
                  <button type="button" class="reviews-carousel__dot" aria-label="Go to review 9"></button>
                  <button type="button" class="reviews-carousel__dot" aria-label="Go to review 10"></button>
                  <button type="button" class="reviews-carousel__dot" aria-label="Go to review 11"></button>
                  <button type="button" class="reviews-carousel__dot" aria-label="Go to review 12"></button>
                </div>
                <button type="button" class="reviews-carousel__arrow" aria-label="Next review"><span aria-hidden="true">›</span></button>
              </div>
            </div>
          </div>
        </section>

        <section class="section-shell animate-soft-in" style="animation-delay: 240ms;">
          <div class="surface-card p-6 sm:p-10 lg:p-12">
            <div class="grid gap-10 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.2fr)] lg:gap-16">
              <div class="space-y-8">
                <div class="space-y-4">
                  <h2 class="text-[clamp(2.25rem,5vw,4.5rem)] font-bold text-[var(--ink)]">FAQs</h2>
                  <p class="max-w-sm text-base font-light leading-6 text-[var(--ink)]">Common questions about our peptides, ordering, and what to expect.</p>
                </div>
                <div class="rounded-[16px] border border-[var(--line)] bg-[var(--surface-soft)] p-5">
                  <div class="text-lg font-bold text-[var(--ink)]">Need more help?</div>
                  <p class="mt-1 text-sm text-[var(--muted-2)]">For order or shipping issues — not restock ETAs</p>
                  <a class="btn-primary mt-4 !min-h-12" href="/contact/">Contact Us</a>
                </div>
              </div>
              <div class="space-y-3">
                <div class="overflow-hidden rounded-[16px] border border-[var(--line)] bg-white"><button type="button" aria-expanded="true" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left"><span class="text-base font-semibold text-[var(--ink)] sm:text-lg">How long does shipping take?</span><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-[var(--line)] text-[var(--accent)] transition-transform rotate-45" aria-hidden="true">+</span></button><div class="border-t border-[var(--line)] px-5 py-4 text-sm leading-relaxed text-[var(--muted-2)] sm:text-base">Most orders ship within 1–5 business days. Domestic delivery is typically 2–5 business days after that.</div></div>
                <div class="overflow-hidden rounded-[16px] border border-[var(--line)] bg-white"><button type="button" aria-expanded="false" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left"><span class="text-base font-semibold text-[var(--ink)] sm:text-lg">Do you provide tracking numbers?</span><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-[var(--line)] text-[var(--accent)] transition-transform " aria-hidden="true">+</span></button></div>
                <div class="overflow-hidden rounded-[16px] border border-[var(--line)] bg-white"><button type="button" aria-expanded="false" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left"><span class="text-base font-semibold text-[var(--ink)] sm:text-lg">Is shipping discreet?</span><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-[var(--line)] text-[var(--accent)] transition-transform " aria-hidden="true">+</span></button></div>
                <div class="overflow-hidden rounded-[16px] border border-[var(--line)] bg-white"><button type="button" aria-expanded="false" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left"><span class="text-base font-semibold text-[var(--ink)] sm:text-lg">Is there a minimum order?</span><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-[var(--line)] text-[var(--accent)] transition-transform " aria-hidden="true">+</span></button></div>
                <div class="overflow-hidden rounded-[16px] border border-[var(--line)] bg-white"><button type="button" aria-expanded="false" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left"><span class="text-base font-semibold text-[var(--ink)] sm:text-lg">Do you accept Bitcoin?</span><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-[var(--line)] text-[var(--accent)] transition-transform " aria-hidden="true">+</span></button></div>
                <div class="overflow-hidden rounded-[16px] border border-[var(--line)] bg-white"><button type="button" aria-expanded="false" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left"><span class="text-base font-semibold text-[var(--ink)] sm:text-lg">Are products lab tested?</span><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-[var(--line)] text-[var(--accent)] transition-transform " aria-hidden="true">+</span></button></div>
                <div class="overflow-hidden rounded-[16px] border border-[var(--line)] bg-white"><button type="button" aria-expanded="false" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left"><span class="text-base font-semibold text-[var(--ink)] sm:text-lg">What if a product is out of stock?</span><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-[var(--line)] text-[var(--accent)] transition-transform " aria-hidden="true">+</span></button></div>
                <div class="overflow-hidden rounded-[16px] border border-[var(--line)] bg-white"><button type="button" aria-expanded="false" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left"><span class="text-base font-semibold text-[var(--ink)] sm:text-lg">What carrier oils do you use?</span><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-[var(--line)] text-[var(--accent)] transition-transform " aria-hidden="true">+</span></button></div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </main>

<?php require __DIR__ . '/includes/layout/tail.php'; ?>