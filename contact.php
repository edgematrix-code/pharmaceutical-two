<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

seo_set([
    'title'       => seo_title('Contact Arail Pharmaceuticals - Support & Enquiries'),
    'description' => seo_text('Get in touch with the Arail Pharmaceuticals team about orders, shipping, lab testing or product questions. We reply to every enquiry.'),
    'canonical'   => 'contact/',
    'active_nav'  => 'help',
    'json_ld'     => [
        ld_breadcrumbs(array (
  0 => 
  array (
    'name' => 'Home',
    'url' => '',
  ),
  1 => 
  array (
    'name' => 'Contact',
    'url' => 'contact/',
  ),
)),
        ld_organization(),
    ],
]);
$contact_email = (string)arail_config()['app']['email'];

/* FAQ content - shown in the accordion below AND used for FAQPage structured data. */
$faqs = [
    ['How long does shipping take?', 'Most orders ship within 1–5 business days. Domestic delivery is typically 2–5 business days after that.'],
    ['Do you provide tracking numbers?', 'Not by default. Tracking is available only by request, and only 10 days after your order has been marked completed and shipped.'],
    ['Is shipping discreet?', 'Yes. Packages ship in plain, unmarked packaging with no product names or branding on the outside label.'],
    ['Is there a minimum order?', 'Yes. Orders require a minimum of $300 before they can be placed. Your cart will show how much you still need to add.'],
    ['Do you accept Bitcoin?', 'Yes. Bitcoin and Lightning are available through BTCPay. Other coins (USDT, ETH, SOL, LTC, and more) are available through CryptAPI at checkout — pick the coin, then send the exact amount shown on the payment page.'],
    ['Are products lab tested?', 'Yes. Listed batches are independently analyzed by Janoshik. Lab reports are available on product pages and in our testing database.'],
    ['What if a product is out of stock?', 'Availability is shown on each product page. Out-of-stock items cannot be added to cart — please check back on the site later. We do not provide product restock dates or ETAs by email, so please do not message support asking when an item will return.'],
    ['What carrier oils do you use?', 'Injectable products use pharmaceutical-grade carrier oils selected for stability and injection comfort. See each product page for specifics.'],
];

/* FAQPage structured data, built from the same visible content. */
seo_set(['json_ld' => array_merge((array)seo('json_ld', []), [ld_faq($faqs)])]);
$crumbs = array (
  0 => 
  array (
    'name' => 'Home',
    'url' => '',
  ),
  1 => 
  array (
    'name' => 'Contact',
    'url' => 'contact/',
  ),
);

require __DIR__ . '/includes/layout/head.php';
?>
<main class="min-w-0 max-w-full overflow-x-clip pb-16 pt-[7.75rem] sm:pt-[8.75rem]"><div class="contact-page"><section class="contact-page__hero"><div class="section-shell contact-page__hero-inner animate-fade-up"><p class="contact-page__eyebrow">Support</p><h1 class="contact-page__title">Contact us</h1><p class="contact-page__subtitle">Email us for order, shipping, or payment issues. Please do not ask for product restock dates or ETAs — availability is only shown on product pages.</p></div></section><section class="section-shell contact-page__cards animate-soft-in" style="animation-delay: 60ms;"><div class="contact-page__card-grid"><div class="surface-card contact-page__card"><div class="contact-page__card-icon"><svg width="22" height="22" viewBox="0 0 22 22" fill="none" aria-hidden="true"><rect x="2" y="4.5" width="18" height="13" rx="2" stroke="currentColor" stroke-width="1.5"></rect><path d="M2 7.5 11 13l9-5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg></div><h2 class="contact-page__card-title">Email support</h2><a href="mailto:<?= e($contact_email) ?>" class="contact-page__card-value"><?= e($contact_email) ?></a><p class="contact-page__card-desc">For order issues, shipping problems, payments, and lab reports — not restock dates or product ETAs.</p></div><div class="surface-card contact-page__card"><div class="contact-page__card-icon"><svg width="22" height="22" viewBox="0 0 22 22" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="1.5"></circle><path d="M11 7v4.5l3 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg></div><h2 class="contact-page__card-title">Response time</h2><p class="contact-page__card-value">Within 24 hours</p><p class="contact-page__card-desc">We reply on business days. Urgent shipping issues get priority.</p></div><div class="surface-card contact-page__card"><div class="contact-page__card-icon"><svg width="22" height="22" viewBox="0 0 22 22" fill="none" aria-hidden="true"><path d="M4 8.5V18a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V8.5" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"></path><path d="M2.5 8.5h17L18.5 4H3.5L2.5 8.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"></path><path d="M8.5 11.5v5M13.5 11.5v5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path></svg></div><h2 class="contact-page__card-title">Browse catalog</h2><a href="/shop/" class="contact-page__card-value">Shop all products</a><p class="contact-page__card-desc">Peptides with independent Janoshik lab verification.</p></div></div></section><section class="section-shell animate-soft-in" style="animation-delay: 90ms;">
  <div class="surface-card px-6 py-8 sm:px-10">
    <h2 class="text-2xl font-bold text-[var(--accent)]">Send us a message</h2>
    <p class="mt-2 max-w-2xl text-base text-[var(--muted-2)]">Use the form below and we will reply to your email. Do not include card or bank details in your message.</p>
    <form class="mt-6 grid max-w-2xl gap-4" role="form" aria-label="Contact form" action="<?= e(url_path('api/contact.php')) ?>" method="post" data-api="contact" novalidate>
      <div class="grid gap-4 sm:grid-cols-2">
        <label class="text-sm">Your name
          <input name="name" required minlength="2" maxlength="120" autocomplete="name" class="mt-1 h-12 w-full rounded-[12px] border border-[var(--line)] px-4 text-sm outline-none focus:border-[var(--accent)]">
        </label>
        <label class="text-sm">Email
          <input type="email" name="email" required autocomplete="email" class="mt-1 h-12 w-full rounded-[12px] border border-[var(--line)] px-4 text-sm outline-none focus:border-[var(--accent)]">
        </label>
      </div>
      <label class="text-sm">Subject (optional)
        <input name="subject" maxlength="200" class="mt-1 h-12 w-full rounded-[12px] border border-[var(--line)] px-4 text-sm outline-none focus:border-[var(--accent)]">
      </label>
      <label class="text-sm">Message
        <textarea name="message" required minlength="5" maxlength="5000" rows="5" class="mt-1 w-full rounded-[12px] border border-[var(--line)] px-4 py-3 text-sm outline-none focus:border-[var(--accent)]"></textarea>
      </label>
      <input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
      <button type="submit" class="btn-primary !min-h-12 !px-7 !text-sm">Send message</button>
    </form>
  </div>
</section>

<section class="section-shell contact-page__faq animate-soft-in" style="animation-delay: 120ms;"><div class="surface-card contact-page__faq-card"><div class="contact-page__faq-layout"><div class="contact-page__faq-intro"><div class="contact-page__faq-copy"><h2 class="contact-page__faq-title">FAQs</h2><p class="contact-page__faq-desc">Common questions about shipping, ordering, lab reports, and payments.</p></div><div class="contact-page__faq-help"><div class="contact-page__faq-help-title">Still need help?</div><p class="contact-page__faq-help-desc">For real order issues, email us and we'll reply within 24 hours. We cannot answer restock or product ETA requests.</p><div class="contact-page__faq-help-actions"><a href="mailto:<?= e($contact_email) ?>" class="btn-primary !min-h-12">Email support</a><a class="contact-page__faq-shop-link" href="/shop/">Or browse the shop →</a></div></div></div><div class="space-y-3">
          <?php foreach ($faqs as $faqIndex => $faq): $faqId = 'faq-' . ($faqIndex + 1); $faqOpen = $faqIndex === 0; ?>
            <div class="overflow-hidden rounded-[16px] border border-[var(--line)] bg-white" data-faq>
              <button type="button" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left" data-faq-trigger aria-expanded="<?= $faqOpen ? 'true' : 'false' ?>" aria-controls="<?= e($faqId) ?>">
                <span class="text-base font-semibold text-[var(--ink)] sm:text-lg"><?= e($faq[0]) ?></span>
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-[var(--line)] text-[var(--accent)] transition-transform <?= $faqOpen ? 'rotate-45' : '' ?>" data-faq-icon aria-hidden="true">+</span>
              </button>
              <div id="<?= e($faqId) ?>" class="border-t border-[var(--line)] px-5 py-4 text-sm leading-relaxed text-[var(--muted-2)] sm:text-base" data-faq-panel<?= $faqOpen ? '' : ' hidden' ?>><?= e($faq[1]) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div></div></div></section></div></main>

<?php require __DIR__ . '/includes/layout/tail.php'; ?>