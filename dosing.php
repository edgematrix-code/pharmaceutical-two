<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

seo_set([
    'title'       => seo_title('Dosing Guide - Anabolics & Peptides | Arail Pharmaceuticals'),
    'description' => seo_text('Reference dosing information for the anabolics and peptides we supply, with practical guidance on cycle length, frequency and storage.'),
    'canonical'   => 'dosing/',
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
    'name' => 'Dosing',
    'url' => 'dosing/',
  ),
)),
        ld_organization(),
    ],
]);
$crumbs = array (
  0 => 
  array (
    'name' => 'Home',
    'url' => '',
  ),
  1 => 
  array (
    'name' => 'Dosing',
    'url' => 'dosing/',
  ),
);

require __DIR__ . '/includes/layout/head.php';
?>
<main class="min-w-0 max-w-full overflow-x-clip pb-16 pt-[7.75rem] sm:pt-[8.75rem]">
      <div class="dosing-page">
        <section class="dosing-page__hero">
          <div class="section-shell dosing-page__hero-inner animate-fade-up"><span
              class="brand-logo brand-logo--page brand-logo--light dosing-page__brand-logo"
              aria-label="Arail Pharmaceuticals"><picture><source type="image/webp" srcset="/assets/img/arail-logo-exact-v9.webp"><img loading="lazy" decoding="async" alt="Arail Pharmaceuticals" width="482" height="239"
                decoding="async" class="brand-logo__img"
                src="/assets/img/arail-logo-exact-v9.png" style="color: transparent;"></picture></span>
            <p class="dosing-page__eyebrow">Research tools</p>
            <h1 class="dosing-page__title">Dosing calculator</h1>
            <p class="dosing-page__subtitle">Reconstitution math for research peptides, a quick HGH dilution reference,
              and practical storage guidelines — all in one place.</p>
            <nav class="dosing-page__toc" aria-label="Page sections"><a
                href="dosing.html#peptide">Peptide calculator</a><a
                href="dosing.html#hgh">HGH dilution</a><a
                href="dosing.html#storage">Storage</a></nav>
          </div>
        </section>
        <section id="peptide" class="section-shell dosing-page__section animate-soft-in" style="animation-delay: 60ms;">
          <header class="dosing-page__section-header"><span class="dosing-page__section-label">Section 1</span>
            <h2 class="dosing-page__section-title">Peptide calculator</h2>
            <p class="dosing-page__section-desc">Enter vial size, BAC water volume, and your target dose. Results update
              live for a U-100 insulin syringe (100 units = 1 mL).</p>
          </header>
          <div class="dosing-page__panel">
            <div class="dosing-calc">
              <div class="dosing-calc__grid">
                <div class="dosing-calc__inputs"><label class="dosing-field"><span class="dosing-field__label">Vial
                      amount</span>
                    <div class="dosing-field__row"><input inputmode="decimal" min="0" step="any"
                        class="dosing-field__input" aria-describedby="vial-unit" type="number" value="5"><span
                        id="vial-unit" class="dosing-field__suffix">mg</span></div>
                  </label><label class="dosing-field"><span class="dosing-field__label">Bacteriostatic water</span>
                    <div class="dosing-field__row"><input inputmode="decimal" min="0" step="any"
                        class="dosing-field__input" aria-describedby="water-unit" type="number" value="2"><span
                        id="water-unit" class="dosing-field__suffix">mL</span></div>
                  </label>
                  <div class="dosing-field"><span class="dosing-field__label" id="dose-label">Desired dose</span>
                    <div class="dosing-field__row dosing-field__row--dose"><input inputmode="decimal" min="0" step="any"
                        class="dosing-field__input" aria-labelledby="dose-label" type="number" value="250">
                      <div class="dosing-unit-toggle" role="group" aria-label="Dose unit"><button type="button"
                          class="dosing-unit-toggle__btn dosing-unit-toggle__btn--active"
                          aria-pressed="true">mcg</button><button type="button" class="dosing-unit-toggle__btn"
                          aria-pressed="false">mg</button></div>
                    </div>
                  </div>
                </div>
                <div class="dosing-calc__results" aria-live="polite">
                  <div class="dosing-result dosing-result--hero"><span class="dosing-result__label">Draw on U-100
                      syringe</span><strong class="dosing-result__value">10 <span
                        class="dosing-result__unit">units</span></strong><span class="dosing-result__meta">≈ 0.1
                      mL</span></div>
                  <dl class="dosing-result-grid">
                    <div>
                      <dt>Concentration</dt>
                      <dd>2.5 mg/mL<span class="dosing-result-grid__sub">(2500 mcg/mL)</span></dd>
                    </div>
                    <div>
                      <dt>Per insulin unit</dt>
                      <dd>25 mcg</dd>
                    </div>
                  </dl>
                </div>
              </div>
              <div class="dosing-calc__formula">
                <h3 class="dosing-calc__formula-title">How it's calculated</h3>
                <ol class="dosing-calc__formula-list">
                  <li>Concentration = vial (mg) ÷ BAC water (mL)</li>
                  <li>Volume (mL) = dose (mg) ÷ concentration — mcg ÷ 1000 = mg</li>
                  <li>Units to draw = volume (mL) × 100 (U-100: 100 units = 1 mL)</li>
                </ol>
                <p class="dosing-calc__disclaimer">For research and educational use only. Not medical advice. Always
                  verify measurements against your vial label and syringe markings.</p>
              </div>
            </div>
          </div>
        </section>
        <section id="hgh" class="section-shell dosing-page__section animate-soft-in" style="animation-delay: 100ms;">
          <header class="dosing-page__section-header"><span class="dosing-page__section-label">Section 2</span>
            <h2 class="dosing-page__section-title">HGH dilution chart</h2>
            <p class="dosing-page__section-desc">Common IU-based reconstitutions. Assumes a U-100 insulin syringe. IU
              per unit = vial IU ÷ (water mL × 100).</p>
          </header>
          <div class="dosing-page__panel dosing-page__panel--flush">
            <div class="dosing-table-wrap" role="region" aria-label="HGH dilution chart" tabindex="0">
              <table class="dosing-table">
                <thead>
                  <tr>
                    <th scope="col">Vial (IU)</th>
                    <th scope="col">BAC water</th>
                    <th scope="col">IU per unit</th>
                    <th scope="col">Units for 1 IU</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>10 IU</td>
                    <td>1 mL</td>
                    <td>0.1 IU</td>
                    <td>10</td>
                  </tr>
                  <tr>
                    <td>10 IU</td>
                    <td>2 mL</td>
                    <td>0.05 IU</td>
                    <td>20</td>
                  </tr>
                  <tr>
                    <td>12 IU</td>
                    <td>1 mL</td>
                    <td>0.12 IU</td>
                    <td>8.33</td>
                  </tr>
                  <tr>
                    <td>12 IU</td>
                    <td>2 mL</td>
                    <td>0.06 IU</td>
                    <td>16.67</td>
                  </tr>
                  <tr>
                    <td>36 IU</td>
                    <td>1 mL</td>
                    <td>0.36 IU</td>
                    <td>2.78</td>
                  </tr>
                  <tr>
                    <td>36 IU</td>
                    <td>2 mL</td>
                    <td>0.18 IU</td>
                    <td>5.56</td>
                  </tr>
                  <tr>
                    <td>100 IU</td>
                    <td>1 mL</td>
                    <td>1 IU</td>
                    <td>1</td>
                  </tr>
                  <tr>
                    <td>100 IU</td>
                    <td>2 mL</td>
                    <td>0.5 IU</td>
                    <td>2</td>
                  </tr>
                  <tr>
                    <td>100 IU</td>
                    <td>5 mL</td>
                    <td>0.2 IU</td>
                    <td>5</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <p class="dosing-page__table-note">Example: a 100 IU vial + 2 mL BAC water → 0.5 IU per unit. For 2 IU, draw
              4 units on a U-100 syringe.</p>
          </div>
        </section>
        <section id="storage" class="section-shell dosing-page__section animate-soft-in"
          style="animation-delay: 140ms;">
          <header class="dosing-page__section-header"><span class="dosing-page__section-label">Section 3</span>
            <h2 class="dosing-page__section-title">Storage guidelines</h2>
            <p class="dosing-page__section-desc">General best practices for research peptides. Always follow your
              product insert when available.</p>
          </header>
          <ul class="dosing-storage-list">
            <li class="surface-card dosing-storage-card">
              <h3 class="dosing-storage-card__title">Unreconstituted (lyophilized)</h3>
              <p class="dosing-storage-card__body">Keep sealed vials cool, dry, and away from direct light.
                Refrigeration (2–8 °C) is preferred for longer storage; short room-temperature periods are usually fine
                if heat and humidity are controlled.</p>
            </li>
            <li class="surface-card dosing-storage-card">
              <h3 class="dosing-storage-card__title">After reconstitution</h3>
              <p class="dosing-storage-card__body">Store reconstituted peptides refrigerated. Avoid repeated freeze–thaw
                cycles. Use bacteriostatic water as directed by your research protocol, and note the date of
                reconstitution on the vial.</p>
            </li>
            <li class="surface-card dosing-storage-card">
              <h3 class="dosing-storage-card__title">Light, heat &amp; freezing</h3>
              <p class="dosing-storage-card__body">Protect from UV and strong light. Do not leave vials in a hot car or
                near heat sources. Freezing reconstituted solutions is generally not recommended unless your specific
                material guidance says otherwise.</p>
            </li>
            <li class="surface-card dosing-storage-card">
              <h3 class="dosing-storage-card__title">Travel tips</h3>
              <p class="dosing-storage-card__body">Use an insulated pack with cold packs for reconstituted vials. Keep
                lyophilized product in original packaging. Avoid checked luggage temperature swings when possible.</p>
            </li>
          </ul>
        </section>
        <section class="section-shell dosing-page__cta">
          <div class="dosing-page__cta-card">
            <h2 class="dosing-page__cta-title">Need verified peptides?</h2>
            <p class="dosing-page__cta-desc">Browse the catalog — independent Janoshik lab reports on listed batches.
            </p><a class="btn-primary !min-h-12" href="/shop/">Shop all</a>
          </div>
        </section>
      </div>
    </main>

<?php require __DIR__ . '/includes/layout/tail.php'; ?>