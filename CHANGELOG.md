# Arail Pharmaceuticals — SEO & storefront build changelog

This document records everything that was audited, changed and verified, plus the
work that is deliberately left for the store owner.

**Important:** the production domain has **not** been purchased. No domain name is
hard-coded anywhere in the codebase any more — every absolute URL (canonical,
Open Graph, sitemap, Merchant Center feed) is generated from `APP_BASE_URL` in
`.env`, which is intentionally empty until the real domain exists.

---

## Phase 1 — Audit (what was found)

| Area | State before |
|---|---|
| Front end | Static HTML mirror: `index.html`, `Shop.html`, `review.html`, `bitcoin.html`, `Contact.html`, `dosing.html`, `stacks.html`, `Testing.html` + 8 `*_files/` asset directories |
| Back end | PHP 8.5 + MySQL 8.0 (`arail` DB, 3 categories, 50 products), working REST-style API (`api/*.php`) and admin panel (`admin/*.php`) |
| Product pages | **Did not exist locally.** Every product link pointed at `https://www.arailpharma.is/product/<slug>` — an unpurchased domain |
| Cart / checkout | Not implemented. 100 "Add to cart" buttons did nothing. Cart/Account links pointed off-domain |
| Account / login | No customer accounts existed at all (only an admin user) |
| Category filters | All three chips linked to the same `Shop.html`; no filtering |
| Search | Client-side JS filter over a hard-coded grid |
| Metadata | Per-page hard-coded title/description; no canonical, no Open Graph product tags, no JSON-LD, no sitemap, no `robots.txt`, no `.htaccess` |
| Structured data | None |
| Performance | Two full CSS copies per page, 8 duplicated font files, 399 duplicated assets, no WebP, no caching/compression headers |

Server detected: **Herd (nginx + Laravel Valet's `server.php`)** on
`arail-pharamceuticals.test`, i.e. **not** Apache — nginx ignores `.htaccess`.
An Apache `.htaccess` was written anyway for whatever host you deploy to, and the
routing was mirrored in `router.php` for `php -S`. After the phase work this was
replaced by a single front controller (`index.php` + `includes/routes.php`) so the
clean URLs work identically on every server; see *Post-audit fixes* for the bug
that this caused and fixed.

---

## Phase 2 — Shared templates and metadata

**Created**

| File | Purpose |
|---|---|
| `includes/seo.php` | Base-URL resolution, canonical/OG/Twitter builders, JSON-LD helpers, title/description clamping |
| `includes/bootstrap.php` | Loads DB + helpers + SEO + catalogue; `asset()`, `nav_classes()`, `picture()`, `image_webp()` |
| `includes/catalog.php` | Prepared-statement queries for products, categories, reviews, ratings, feed |
| `includes/layout/head.php` | Single `<head>` for every page |
| `includes/layout/header.php` | Single header (nav, category dropdown, search, cart badge, account) |
| `includes/layout/footer.php` | Single footer (shop/category/top-product/help/company links, newsletter) |
| `includes/layout/tail.php` | Closing markup, script tags, optional GA4 |
| `includes/partials/product-card.php` | One product card used by every listing |
| `includes/partials/breadcrumbs.php` | Visible breadcrumbs mirroring the JSON-LD |
| `includes/partials/pagination.php` | Crawlable `rel=prev`/`rel=next` pagination |
| `includes/partials/content-page.php` | Renderer for simple content pages |

**Metadata templates now generated from the database**

- Product title: `{Product Name} - {Category} | Arail Pharmaceuticals` (clamped to 60 chars)
- Product description: summary + price + benefit + CTA (clamped to 158 chars)
- Category title: `{Category} - Buy Online | Arail Pharmaceuticals` (+ ` - Page N`)
- Manual overrides honoured via `products.meta_title`, `products.meta_description`, `categories.meta_title`, `categories.meta_description`

**Modified:** `includes/config.php` — added `base_url` (empty), currency/currency_code,
country, GA4 and Search Console placeholders, tagline. All output is escaped with
`e()` / `htmlspecialchars()`; URLs come from config, not raw `$_SERVER` (the
request host is used only as a strictly validated local-development fallback).

---

## Phase 3 — URL structure and indexation control

**Clean URLs** (implemented once in `includes/routes.php`, dispatched by the
`index.php` front controller, so `.htaccess` and `router.php` only have to hand
unknown paths to `index.php`):

```
/                                 home
/shop/                            all products
/category/<slug>/                 category
/product/<slug>/                  product
/search/                          internal search
/cart/  /checkout/  /wishlist/  /account/  /order/<number>/
/dosing/ /stacks/ /testing/ /reviews/ /bitcoin/ /contact/
/about/ /shipping/ /returns/ /privacy/ /terms/
/sitemap.xml  /robots.txt  /feed.php
```

- Legacy mirror URLs get **301s, no chains**: `/index.html` → `/`, `/Shop.html` → `/shop/`,
  `/review.html` → `/reviews/`, `/bitcoin.html` → `/bitcoin/`, `/Contact.html` → `/contact/`,
  `/dosing.html` → `/dosing/`, `/stacks.html` → `/stacks/`, `/Testing.html` → `/testing/`,
  and `/index.php` → `/`. The old files were moved to `.backup-html/legacy/` and
  have since been moved again, out of the served directory (see *Post-audit fixes*).
- HTTPS forcing, canonical-host block and `.php`/`index.php` de-duplication live in
  `.htaccess` (host rule commented out until the domain exists).
- **Trailing-slash normalisation** is done by the front controller, not the web
  server: `/shop` → 301 → `/shop/`, `/product/<slug>` → 301 → `/product/<slug>/`,
  each as a single hop with the query string preserved. File-like routes
  (`/sitemap.xml`, `/robots.txt`, `/feed.php`) are served without a trailing slash.
- **Faceted navigation:** `?sort=` / `?q=` pages emit `noindex,follow` and a
  canonical pointing at the clean category/shop URL; `robots.txt` disallows the
  parameter patterns to kill crawl traps.
- **Pagination:** each page keeps a **self-referencing canonical**, has unique
  `"… - Page N"` titles and crawlable `<a rel=prev|next>` links.
- **noindex** (and excluded from the sitemap): cart, checkout, account, wishlist,
  search, order confirmation. All are also `Disallow`ed in `robots.txt`.
- **Out of stock:** page stays live (HTTP 200) with `schema.org/OutOfStock`, a
  back-in-stock notify form and related products. No fake scarcity anywhere.
- `404.php` returns a real **404** status with search, popular categories and
  bestsellers.
- `robots.txt` (generated by `robots.php`) allows CSS/JS/images explicitly,
  disallows the transactional paths **and** `/.backup-html/`, `/db/`, `/tools/`,
  and references the sitemap.
- Non-public paths return **403**: `db/`, `tools/`, `router.php`, `seo-check.php`
  and anything under `.backup-html/` / `.work/`. This is enforced in three places
  because the cheap one alone is not enough: the front controller (works on any
  server), the `.htaccess` `[F]` rules (Apache), and a CLI-only guard inside each
  of `db/*.php`, `tools/*.php` and `seo-check.php` (so that a server which hands
  `.php` straight to PHP-FPM can never execute them over HTTP).
- The legacy HTML mirror and the scraped asset trees are additionally **outside
  the web root** — see *Post-audit fixes*, which is the reason for that move.

**Created:** `robots.php` (routed at `/robots.txt`), `sitemap.php`, `.htaccess`, `router.php`, `404.php`, `includes/routes.php`.

**Sitemaps** (`sitemap.php`, served at `/sitemap.xml`): a sitemap **index** with
separate product (image extension included), category and pages sitemaps, 50 000
URLs per file maximum, `lastmod` from database timestamps, and only 200-status
indexable URLs. Verified: 66 URLs, all 200.

---

## Phase 4 — Structured data (JSON-LD)

Generated with `json_encode(..., JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)`:

- **Product** — name, image(s), description, sku, mpn, brand, offers (price,
  priceCurrency from config, availability mapped from real stock, url,
  priceValidUntil, itemCondition, seller).
- **AggregateRating + Review** — emitted **only** when approved reviews exist for
  that product. No rating is ever fabricated.
- **CollectionPage + ItemList** on shop and category pages.
- **BreadcrumbList** on every page (with matching visible breadcrumbs).
- **Organization** + **WebSite** (with `SearchAction`) site-wide, on the home page.

Markup matches the visible page exactly — price and stock come from the same query.

---

## Phase 5 — Google Merchant Center feed

**Created:** `feed.php`

- `/feed.php` → RSS 2.0 XML with the `g:` namespace
- `/feed.php?format=tsv` → tab-separated values
- Fields: id, title, description, link, image_link, availability, price, brand,
  condition, product_type, mpn, gtin (when present), shipping
- Prices and availability are read from the same source as the product pages and
  the JSON-LD, so the feed can never disagree with the site.
- Verified: 50 items, well-formed XML; TSV has 50 rows + header.

**How to submit it in Merchant Center**
1. Merchant Center → *Products* → *Feeds* → **Add feed**.
2. Country/language = your target market; feed name e.g. "Arail main feed".
3. Input method = **Scheduled fetch**; URL =
   `https://your-real-domain.com/feed.php` (set `app.base_url` first).
4. Fetch frequency = daily; time zone = yours.
5. Save, then use the *Diagnostics* tab to fix any item disapprovals.
6. GTINs are currently empty for every product (see Phase 5 note below) — either
   supply real barcodes or keep `identifier_exists` handling in mind.

---

## Phase 6 — On-page and content

- Product page: one `<h1>`, price, stock badge, add-to-cart with quantity,
  wishlist, description, specifications table, shipping/returns/testing links,
  related products, reviews list + moderated review form.
- Category page: one `<h1>`, intro above the grid, subcategory/cross links and a
  longer SEO block below (marked `TODO` where the owner must write copy).
- Shop page: one `<h1>`, filters, sort, grid, pagination and a shop introduction
  with an explicit `TODO` copy placeholder.
- Internal linking: header categories, footer (shop, categories, top products,
  help, company), related products, cross-links on categories, bestsellers on the
  404 page. Nothing is an orphan; product pages are ≤3 clicks from home.
- Trust pages created and linked in the footer: **About, Shipping, Returns,
  Privacy, Terms** (each contains a `TODO` block for the owner's real policy
  wording rather than invented claims).
- A blog/guides structure was **not** built — see *Not done* below.

**Pages created (dynamic or converted):** `index.php`, `shop.php`, `category.php`,
`product.php`, `search.php`, `cart.php`, `checkout.php`, `wishlist.php`,
`account.php`, `order-confirmation.php`, `reviews.php`, `dosing.php`, `stacks.php`,
`testing.php`, `bitcoin.php`, `contact.php`, `about.php`, `shipping.php`,
`returns.php`, `privacy.php`, `terms.php`, `404.php`.

The eight original content pages were converted mechanically from the mirror
markup so the design is preserved exactly, but now use the shared includes, clean
internal URLs, database-driven product cards and WebP `<picture>` images.

---

## Phase 7 — Performance and Core Web Vitals

- **Assets consolidated:** 8 duplicated `*_files/` trees folded into one
  `/assets` tree (135 files copied, 93 duplicates removed), 2 shared stylesheets
  merged into `assets/css/theme.css` + `assets/css/site.css`, 6 fonts into
  `assets/fonts/`, images into `assets/img/`.
- **WebP:** `tools/optimize-images.php` generated **129 WebP files — 68.6 % smaller**
  (21.4 MB → 6.8 MB). The oversized hero photo went from **648 KB → 34 KB** (95 %).
- **`<picture>` + fallback:** 97 content images wrapped in `<picture>` with a WebP
  source and the original PNG/JPEG as fallback; product cards and product pages
  use the same mechanism.
- **Lazy loading:** 121 `loading="lazy"` added to below-the-fold images; the home
  hero and main product image use `fetchpriority="high"` and are never lazy.
- **Explicit `width`/`height`** on product and hero images, so no layout shift.
- **Fonts:** self-hosted, `font-display: swap`, main font preloaded.
- **JS:** all deferred; unused Next.js `.js.download` chunks not shipped.
- **Server (`.htaccess`):** Brotli + gzip, 1-year `immutable` caching for static
  assets, `no-cache, must-revalidate` for HTML, correct WOFF2 MIME type.
- **Database:** indexes added on `products.is_active`, `products.featured`,
  `products.price` and `categories.sort_order` (slug and category_id already
  indexed).

### Lighthouse results (measured, not estimated)

Run with Lighthouse 12 against `php -S` (a **single-threaded dev server**, so
absolute numbers are indicative and there is run-to-run noise; the first desktop
home run was depressed by server contention and re-measured).

**Desktop**

| Page | Performance | Accessibility | Best practices | SEO | LCP | CLS | TBT |
|---|---|---|---|---|---|---|---|
| Home — before | 90 | 93 | 96 | 100 | 1.7 s | 0 | 10 ms |
| Shop — before | 88 | 96 | 96 | 100 | 2.3 s | 0 | 0 ms |
| Home — after | 93–99 | 93 | **100** | **100** | 0.7–1.8 s | 0 | 0–70 ms |
| Shop — after | **99** | 96 | **100** | **100** | 1.0 s | 0 | 0 ms |
| Category — after | 97 | 96 | **100** | **100** | 1.2 s | 0 | 0 ms |
| Product — after | **99–100** | 96 | **100** | **100** | 0.6 s | 0 | 0 ms |

**Mobile** (Lighthouse default mobile preset)

| Page | Performance | LCP | CLS |
|---|---|---|---|
| Home — before | 74 | 7.9 s | 0 |
| Home — after | **90** | **3.3 s** | 0 |
| Shop — after | 96 | 2.5 s | 0 |
| Product — after | 95 | 2.6 s | 0 |
| Testing — after | 84 | 4.1 s | 0 |

Targets: LCP < 2.5 s ✅ (desktop and product/shop mobile), INP proxy (TBT) ✅ 0 ms,
CLS < 0.1 ✅ 0. Mobile home and the image-heavy testing page are still above the
2.5 s LCP target — that needs a production web server plus the content work below.

> Note: Lighthouse's *SEO* category only checks basics (title, meta description,
> viewport, crawlable links) and scored 100 on the old mirror too. The real gains
> here — canonical tags, product/category pages, JSON-LD, sitemaps, feed, internal
> linking — are **not** reflected in that score.

> Note on "Best practices 100": measured on `http://127.0.0.1` that is a secure
> context for Chrome, but on `http://arail-pharamceuticals.test` it dropped to
> **79**, with the only failures being `is-on-https` and `redirects-http`. Once
> the local site began serving HTTPS (see *Post-audit fixes* #8) it returned to
> **100**, confirming nothing was ever wrong except the missing TLS. **SEO is 100
> on every host tested.**

> Note: the numbers above were measured against `php -S`. Re-measured on the real
> Herd/nginx server after the front-controller change: **desktop product 98-99,
> LCP ~0.8 s, CLS 0, TBT ~70 ms; mobile home 68-83, LCP 2.8-3.0 s**. Two
> consecutive mobile runs gave TBT 421 ms and 1181 ms, so the mobile score on this
> machine is dominated by run-to-run noise and should not be read as a trend. The
> desktop figures are stable and confirmed the refactor did not regress rendering.

> Note: Lighthouse was run against `php -S`, not Apache/Nginx. Apache is not
> installed on this machine, so `.htaccess` could not be executed during
> verification. Because of that, the site was later moved to a **front controller**
> (`index.php` + `includes/routes.php`) so that routing does not depend on
> `.htaccess` at all, and every clean URL, 301 and 404 was re-verified through the
> real nginx server that this site runs on. See *Post-audit fixes*. The `.htaccess`
> rules (HTTPS, canonical host, headers, compression, caching) remain
> **untested in practice** and should be smoke-tested on the target host.

---

## Phase 8 — Security and trust

- `.htaccess`: `X-Content-Type-Options`, `Referrer-Policy`, `X-Frame-Options`,
  `Permissions-Policy`, and a tested `Content-Security-Policy`; HSTS prepared but
  commented until HTTPS is verified end-to-end.
- All database access uses **prepared statements (PDO)**; all output is escaped.
- CSRF tokens on every account form (`csrf_field()` / `csrf_verify()`).
- Honeypot field on the contact form and the review form.
- `display_errors` off in production (`includes/bootstrap.php`).
- `install.php` is now **disabled for anonymous HTTP** (returns 403) and requires
  CLI or `APP_ALLOW_INSTALL=1`; `config.php`, `seed.php`, `migrate.php` and
  `seo-check.php` are denied by `.htaccess` (and now every dotfile too, `.env`
  included — see *Configuration moved into an environment file*).

---

## Phase 9 — Tracking and verification

- Google Search Console verification meta tag placeholder (`app.gsc_token`).
- GA4 loaded asynchronously and configured from `app.ga4_id`, with e-commerce
  events `view_item` (via product data), `add_to_cart`, `begin_checkout` and
  `purchase` implemented in `assets/js/cart.js`.
- **`seo-check.php`** — run before every deploy:
  `php seo-check.php http://arail-pharamceuticals.test` (or the production URL;
  with `php -S`: `php seo-check.php http://127.0.0.1:8099`). CLI-only.
  It reports missing/duplicate titles and descriptions, products with images,
  descriptions and GTINs missing, stale domain references, missing required
  files, non-200 URLs in the sitemap, canonical mismatches, **broken internal
  links** and soft 404s. Exits non-zero on errors.
  Latest run (against the live Herd/nginx server): **0 errors, 1 warning** (the
  warning is the intentionally unset `base_url`); 199 internal links checked, 0
  broken; 66 sitemap URLs, all 200; exit code 0.

---

## Phase 0 — Features made to work (the "every feature works" part)

| Feature | Before | After |
|---|---|---|
| Add to cart | Buttons did nothing | `assets/js/cart.js` adds to a persisted cart; badges update site-wide |
| Cart page | Didn't exist (off-domain link) | `/cart/` with quantity controls, remove, subtotal |
| Checkout | Didn't exist | `/checkout/` posts the cart to the existing `api/order.php`, which **recalculates every price server-side**; server-side validation verified |
| Order confirmation | Didn't exist | `/order/<number>/` shows the real order (`noindex`) |
| Customer accounts | Didn't exist | `customers` table + `/account/` register, login, profile, order history, logout (CSRF-protected); verified end-to-end |
| Wishlist | Didn't exist | "Save for later" + `/wishlist/` (browser-local) |
| Category filtering | All chips → `Shop.html` | Real `/category/<slug>/` pages from the database + sort + pagination |
| Search | Client-side JS over a hard-coded grid | Server-rendered `/search/` over the database (`noindex`) |
| Product pages | External dead links | 50 server-rendered `/product/<slug>/` pages |
| Contact form | Not present on the page | Working form wired to `api/contact.php` (honeypot + validation) |
| Reviews | Static screenshots only | Real moderated review form + list; ratings only from approved reviews |
| Newsletter | Worked | Still works (form now posts to the clean API path) |

**Database changes**
- `db/migrate.php` (new, idempotent) added: `products.meta_title`,
  `meta_description`, `short_description`, `sku`, `brand`, `gtin`, `mpn`, `specs`;
  `categories.meta_title`, `meta_description`, `description`, `image`;
  `orders.customer_id`; the `customers` table; and the four new indexes.
- `db/enrich.php` (new) set brand + internal SKU/MPN for all 50 products and short
  factual category copy. **No GTIN was invented.**
- `db/schema.php` updated so a fresh install gets all of the above.
- `db/seed.php` image paths updated to the consolidated `assets/img/` location.

---

## Post-audit fixes (routing, FAQs, footer, file exposure)

Work done after the phase-by-phase build, in response to three reported problems
and one that surfaced while fixing them.

### 1. The navbar changed the URL but the page never changed

**Symptom:** clicking any navbar link updated the address bar, but the page kept
showing the homepage. `/shop/`, `/product/...` and every other URL returned
**HTTP 200 with the homepage HTML**. Search engines would have seen a site where
every URL was a duplicate of the home page.

**Cause:** the site is served by **Herd (nginx)**, not Apache. Nginx does not read
`.htaccess`, so the clean-URL rules in it never ran. Herd routes unknown paths
through Laravel Valet's `server.php`, which was falling back to `/index.php` — and
`index.php` *was* the homepage. So every unknown path rendered the homepage.

**Fix:** `index.php` is now a front controller that consults a single URL map in
`includes/routes.php`; it dispatches exact routes, `/<segment>/<value>/` dynamic
routes, the legacy 301s, the 403 blocklist and the trailing-slash redirects. This
is server-agnostic: nginx, Apache and `php -S` all resolve URLs through the same
code, and the web server only has to forward unknown paths to `index.php`.
`.htaccess` was reduced to `RewriteRule ^ index.php [L,QSA]` plus its security and
caching rules; `router.php` now serves real files and otherwise requires
`index.php`.

**Two bugs found while verifying the fix:**

- `/` returned 404 — the exact-route check required exactly one path segment, so
the root never matched.
- After fixing the root, `/shop` (no trailing slash) was served directly by the
exact-route check, which made the trailing-slash redirect dead code; and once it
ran, it redirected to `/shop` — **an infinite 301 loop** (50 hops, confirmed).
  The redirect now targets the canonical `/shop/`, and the legacy `.html` map uses
trailing-slash targets too.

**Verified in a real browser** (headless Chrome, clicking the actual navbar
links): Home, Shop, Stacks, all five Help-dropdown links, all three Shop-dropdown
categories, Cart and Account — each one loads the right URL, a different `<h1>`
and a different `<title>` from the homepage, the active-nav highlight follows the
page, and browser Back re-renders correctly. Verified by HTTP too: 24 clean URLs
return 200 with distinct titles, `/nope/` returns 404, and all 14 legacy
`/…html` + `/index.php` URLs 301 in a **single hop**.

### 2. The contact-page FAQs did not show their answers

All eight questions and their original answers were recovered from the archived
Next.js chunk (`Contact_files/page-*.js.download`), so nothing was invented.

- `ld_faq()` added to `includes/seo.php`; `contact.php` now emits a **FAQPage**
  JSON-LD block (verified: 8 questions, parseable).
- The answers are **server-rendered** into the HTML with `data-faq-*` hooks,
  `aria-expanded` / `aria-controls` on each trigger and the first item open — so
  they are visible with JavaScript disabled and crawlable either way.
- `assets/js/faq.js` (new, loaded deferred from `layout/tail.php`) toggles the
  panels and keeps `aria-expanded` in sync.
- **Verified in a real browser:** all 8 panels open with their answer text
  (103–249 characters each), clicking an open one closes it, clicking again
  re-opens it, and the FAQPage JSON-LD is present with 8 questions.

### 3. The footer subscribe button overflowed on desktop

Root cause: `assets/css/theme.css` gives `.btn-primary`
`min-height: 56px; padding: 8px 20px; font-size: 16px; font-weight: 800`, so at
the `sm` breakpoint the email field and the button could not fit on one row.
`includes/layout/footer.php` was changed to a `min-w-0` flex column that becomes a
row at `sm`, with a `max-w-[400px]` newsletter column, a `flex-1 min-w-0` email
input and a `shrink-0` button whose metrics are overridden (`!min-h-11 !px-5
!text-sm`) so it stops fighting for space.

**Verified in a real browser at 1440 / 1280 / 1024 / 768 / 390 px:** no horizontal
overflow at any width (`scrollWidth <= clientWidth`), the button stays inside the
form and inside the viewport, and the email input stays usable.

### 4. The legacy mirror and scraped assets were publicly downloadable

Because nginx does not read `.htaccess`, every real file under the site directory
was served — including `/.backup-html/legacy/index.html`, a complete HTML mirror
of the old site **that still links to the unpurchased domain**, and the eight
scraped `*_files/` asset trees whose JS chunks contain the same domain string.
For an SEO job whose whole premise is "that domain does not exist yet", serving a
crawlable duplicate of the site pointing at it is the worst possible leak.

These have been **moved out of the served directory** to
`C:\Users\HP\arail-legacy-archive\` (40 MB; nothing deleted, and a `README.txt`
there explains what it is and why). They are no longer referenced by anything: all
images, fonts and scripts live under `/assets`, and `seo-check.php` still reports
0 broken internal links without them. Verified: `/…/index.html` and the `*_files`
paths now return 403/404 while every public URL still returns 200.

The front controller and `.htaccess` still block `.backup-html`, `.work`, `db/`,
`tools/`, `router.php` and `seo-check.php`, so the same leak cannot reappear if the
folders are ever moved back — but the robust answer for a live server is to keep
them outside the web root or block them in the server config.

### 5. The newsletter block overlapped the footer link columns

**Symptom:** "the Subscribe block is overlapping the footer content".

**Why the previous fix missed it:** the check I ran only looked at page-level
horizontal scrolling, and nothing ever scrolled the page. The newsletter column
had `min-w-0`, so it *shrank* and slid leftwards over the link columns; the link
columns could not shrink (`min-width:auto`), so they spilled out of their own
container. Measured at 1440px: the links sat in a 407px box but `Help` ended at
x=777 and `Company` ran 841→917, straight through the newsletter at 848→1248.
It overlapped at **every width from 1024px to 1920px**.

**Fix:** the footer row is now a CSS grid with a reserved newsletter track
(`minmax(0,1fr) 320px`, `360px` from 1280px) and a wrapping link area, so the two
columns cannot reach each other. Markup uses `.footer-layout` /`.footer-main` /
`.footer-link-groups` /`.footer-newsletter`, defined in `site.css`.

**Verified by rectangle-intersection at 18 widths from 360px to 1920px:** zero
overlaps with any link column, the logo or the bottom bar; no content clipped
(`scrollWidth == clientWidth`); no page-level horizontal scroll; the email input
stays 186–266px wide and the link columns 82–395px. A separate sweep of **100
page/width combinations** found no real horizontal overflow anywhere (elements
inside intentional `overflow-x:auto` carousels/tables excluded).

### 6. Cards and containers had no vertical padding

**Symptom:** "most of the containers and div in the website seem not to have padding".

**Cause (not a design mistake in the markup):** `theme.css` is a **pre-built**
Tailwind artefact and there is **no build step in this repo** (no `package.json`,
no Tailwind config). Any utility class that was not present when that file was
generated silently does nothing. `py-8` — used by **9 templates** — was simply not
in the file, so those cards had `px-6` horizontal padding but **zero vertical
padding**; `.underline` was missing too, so "underlined" links were not underlined.

A scan of every `class="…"` in the templates against the compiled CSS found 28
utilities that resolve to nothing, including `py-8`, `py-5`, `px-10`, `sm:px-10`,
`pt-12`, `pl-5`, `mt-3`, `!px-4/6/7`, `text-xl`, `text-3xl`, `leading-tight`,
`h-fit`, `w-24`, `min-h-11`, `min-w-11`, `list-disc`, `underline`,
`md:grid-cols-2`, `lg:col-span-2`, `border-dashed`, `opacity-60`.

**Fix:** those utilities are now defined in `site.css` (which loads after
`theme.css`), written as attribute selectors — `[class~="py-8"]{…}` — so no
selector escaping is involved and specificity matches a normal utility class.

**Verified via computed styles on 20 real pages:** every one of the restored
utilities computes to its intended value (e.g. `py-8` → 32px, `py-5` → 20px,
`text-xl` → 20px), and **all 53 `.surface-card` elements now have padding on all
four sides**.

> This is a workaround for a missing build step, not the ideal fix. If you ever
> reintroduce a Tailwind build, regenerate `theme.css` from the templates and the
> block in `site.css` can be deleted.

### 7. Checkout rejected orders over an email typo

**Symptom:** "any shipping address it says enter a valid shipping address: please
provide a valid email address".

**Cause:** the shipping address was never the problem — an empty address is
accepted. `api/order.php` validated the email with `FILTER_VALIDATE_EMAIL`, which
requires a dot in the domain, so a customer typing `name@gmail` (or
`name@localhost`) could not place an order at all, and the error text mentioned
"email" while they were looking at the address field. Reproduced in a browser:
`john@example` and `john@localhost` were rejected with exactly that message.

**Fix:**
- `plausible_email()` added to `includes/helpers.php` — requires one `@`, a
  non-empty local part and domain, and no whitespace. Used by the checkout only;
  **account login (`api/auth.php`) keeps the strict filter**, because there the
  email is a credential.
- `cart.js` now checks name and email before sending and names the missing field
  ("Please enter your email address."), and trims every value.
- The required fields are marked `*` and `aria-required="true"`.

**Verified in a browser:** `john@example`, `john@localhost`, `JOHN@EXAMPLE.COM`,
`john+orders@example.co.uk` and a blank shipping address all now **create an
order**; only a blank email is refused, and it is refused in the browser with a
message naming the field. All test orders created during the check were deleted
`orders` and `order_items` are back to their previous counts.

### 8. The local site now runs over HTTPS (Herd), and the audit tools follow it

While verifying the above, Herd generated `Arail-Pharamceuticals.test.conf` and
the site started **301-redirecting HTTP to HTTPS**. That is a server-config change,
not an application change, and it is welcome: the `is-on-https` and
`redirects-http` Best-Practices audits that previously failed now pass.

- `seo-check.php` could not crawl it: PHP's HTTPS wrapper does not trust Herd's
  private CA, so every URL came back as status `0`. It now relaxes certificate
  verification **only for local development hosts** (`.test`, `.local`,
  `localhost`, `127.0.0.1`, `::1`) and prints a notice when it does; a real domain
  is always verified strictly.
- Re-measured Lighthouse over HTTPS (mobile home):
  **performance 90, accessibility 93, best practices 100, SEO 100**, LCP 2.09s,
  CLS 0, TBT 339 ms.

> Note: `.htaccess` still contains its own HTTP→HTTPS rule for Apache. On this
> machine the redirect is currently performed by nginx/Herd instead, so the
> `.htaccess` rule is still unverified in practice — but when it *is* used, make
> sure it does not clash with the server-level redirect.

### 9. The cart and checkout pages now match the supplied design exactly

The reference captures you supplied (`checkout-and-cart/Cart page.html` and
`Checkout page.html` — snapshots of the previous site) are now the source of
truth for both pages. Both were rebuilt structurally, not just re-coloured.

**The styles were already there.** The capture's stylesheet
(`Cart page_files/86156bd811798fd7.css`) is byte-identical to
`assets/css/theme.css` (same MD5), so every class the reference uses —
`checkout-page__*`, `checkout-form__*`, `checkout-summary__*`,
`checkout-payment__*`, `side-cart-*`, `coupon-form*`, `reviews-strip*`,
`review-card--strip` — was already in the project. No CSS was added. This was a
markup problem, not a styling problem.

What changed:

- **`cart.php` + `renderCartPage()`** — `section-shell > mx-auto max-w-3xl`
  heading + `surface-card p-5 sm:p-8`, a bordered `divide-y / border-y` item
  list (`h-24 w-20` image panel, price per line, `Qty` number input, `Remove`),
  then the coupon box, `Total` and the Checkout button bottom-aligned.
- **`checkout.php` + `renderCheckout()`** — `checkout-page__inner` with the page
  header, a **reviews strip**, `Contact` + `Shipping` sections, the billing
  toggle, and a sticky sidebar holding the order summary (items with quantity
  badges, `Subtotal / Shipping / Total`, coupon box), the upsells, the payment
  cards and the trust list.
- Quantity changes no longer redraw the whole cart: the row's line total and the
  grand total update in place, so the number input keeps focus while typing
  (previously every keystroke rebuilt the list and dropped the caret).
- Upsells on the checkout page are real: “Add BAC water” and “Add all” add the
  actual catalogue products (`bacteriostatic-water-10ml`, `test-cyp-200`,
  `test-enanthate-300`, `arimidex-1`) and the summary redraws with them.
- Selecting a payment card moves the `is-selected` state, including the
  indicator dot.
- **New `assets/js/reviews-carousel.js`**: the arrows and dots in the review
  strips were inert markup (on the home page too). The controller now syncs the
  scroll-snapped track with both, and maps the end of the track to the last dot
  so no dot is unreachable.

Checkout data model (this is the part that is not cosmetic):

- The form now collects **first/last name** and a **split address**
  (address, city, state, postal code, country). `api/order.php` composes the two
  names into `customer_name` and the address parts into a multi-line
  `shipping_address`; a legacy `name` field is still accepted.
- **Payment methods are now the two providers the storefront actually uses**:
  `btcpaygf_default` (Bitcoin / BTCPay) and `cryptapi` (other cryptocurrency).
  `orders.payment_method` was an `ENUM('bitcoin','bank','cash','other')`, so
  `db/schema.php` and `db/migrate.php` extend it — the old values are kept in the
  enum so existing orders still resolve. `payment_method_label()` renders the
  provider name on the confirmation page (it previously printed
  `ucfirst('btcpaygf_default')`).
- **The coupon box works as far as the data allows**: it enables `Apply` when text
  is entered, remembers the code in `localStorage` (so it survives cart →
  checkout), and sends it with the order, where it is stored in
  `customer_notes` as `Coupon requested: <code>`. There is no discount engine and
  none was invented; the note under the box says the code is applied when the
  order is confirmed.

Two deliberate differences from the capture, both your calls:

- **Shipping shows `$0.00`, not `$15.00`.** You asked to keep free shipping, and
  `app.shipping_flat` is `0.00` (which is what `/shipping/` advertises). The
  summary reads Subtotal / Shipping / Total from that one config value, so
  setting `shipping_flat` to `15.00` makes the page match the capture exactly.
- **No “order notes” textarea and no asterisks on required fields** — the
  reference has neither. Required fields keep `required` +
  `aria-required="true"`, and the client still names the missing field
  (“Please enter your first and last name.” / “Please enter your email
  address.”). Say the word if you want the notes field back; it is one textarea.

Breadcrumbs were removed from both pages (the reference has none, and no
JSON-LD either). Both stay `noindex,nofollow`.

Verification (headless Chrome, 1440px unless stated):

- The **class structure is identical** to the capture on both pages: diffing the
  class histogram of every element under `<main>` reports **0 classes present in
  the reference but missing from the live page**.
- **Geometry matches on every measured element** — e.g. checkout grid
  `700px 380px` both sides, sidebar `x:900 w:380`, `.checkout-summary`
  `h:377`, `.checkout-page__inner h:1966`, cart card `x:336 w:768 h:371`,
  `main` padding `140px 0px 64px`. The only deltas are the coupon input/button
  (`282→280` px) because the capture cannot load its webfont from `file://`.
- **51 functional assertions pass**: summary contents, quantity badge,
  `$585.00 → $593.00 → $703.00` totals as items are added, card selection,
  coupon enable/apply/persist, the missing-field message, a real order reaching
  `/order/…` with the right label and address, the cart emptying afterwards,
  in-place quantity updates, and the carousel arrows/dots at 1440px and 390px.
- **20 cart/checkout page-width combinations with a 3-item cart** (including an
  awkward long product name) at 360–1920px: no horizontal overflow.
- The email matrix was re-run against the new fields: `john@example`,
  `john@localhost`, `JOHN@EXAMPLE.COM`, `john+orders@example.co.uk` and a blank
  address are all accepted; a space in the address is rejected with a message
  about the email; a blank email/name is refused client-side before any request.
- All orders created by these checks were deleted and the database was restored
  to its previous counts (3 orders, 3 order items, 1 message, 1 subscriber,
  0 customers, 1 review).

`checkout-and-cart/` was then **moved out of the site directory** to
`C:\Users\HP\arail-legacy-archive\checkout-and-cart\`: it was publicly
downloadable (`/checkout-and-cart/Cart%20page.html` returned 200) and contains
141 references to the unpurchased domain. Those URLs now return **404**.

### 10. A $300 minimum order is enforced before checkout

The contact page already answered “Is there a minimum order?” with “Orders
require a minimum of $300 before they can be placed. Your cart will show how
much you still need to add.” That promise is now true — previously the copy
described a rule the code did not implement.

One config value drives everything:

- **`includes/config.php`** — `app.min_order = 300.00`. Set it to `0` to remove
  the minimum entirely; nothing else needs to change.
- **`includes/helpers.php`** — two helpers share the wording so the server and
  the browser can never disagree:
  `min_order_value(): float` and
  `min_order_notice(float $subtotal): string`, the latter returning `''` once the
  subtotal qualifies and otherwise
  `Add $X more to reach the $Y minimum order.`

How it reaches the page:

- **`cart.php`** and **`checkout.php`** expose the value as
  `data-min-order` on `[data-cart-root]` / `[data-checkout-summary]`, so the JS
  reads the configured amount rather than hard-coding 300.
- **Cart page (`cart.js`)** — below the minimum the Checkout control is a
  **disabled `<button>`** (not a link), and a `[data-cart-min]` note says how
  much is still needed. Once the subtotal reaches the minimum the note is removed
  and the button becomes a real link to `/checkout/`. It is re-evaluated on every
  quantity change and item removal, including the in-place quantity edit that
  keeps the input focused.
- **Checkout page** — a `[data-checkout-min]` notice appears and
  `.checkout-submit` is disabled while below the minimum; `applyMin()` runs again
  after a failed or rejected submit, so a cart that changes between pages cannot
  slip through.

Server-side enforcement (the part that matters):

- **`api/order.php`** calls `min_order_notice($subtotal)` **before creating the
  order** and returns **HTTP 422** with that exact message when the subtotal is
  short. Disabling a button in the browser is a courtesy; this is the actual
  rule, and it cannot be bypassed with a hand-crafted request.
- The boundary is **inclusive**: a subtotal of exactly `$300.00` is accepted.
  (Because `app.shipping_flat` is `0.00`, the subtotal equals the total today, so
  the rule behaves the same whether you think of it as merchandise or order
  total. If shipping is ever set above zero, the rule is measured on the
  merchandise subtotal, not the shipping-inclusive total.)

The existing check that the announcement matched the FAQ was kept: the notice
copy in `cart.js`, the notice copy in `min_order_notice()`, and the FAQ text on
`contact.php` all describe the same $300 rule.

Verification (headless Chrome, real nginx server):

- A new suite, **`check-min-order.mjs`, passes 30/30 assertions**: the exact
  notice text at a $225 cart, a disabled button and **no** `/checkout/` link
  there; editing the quantity in place to reach $315 keeps focus, removes the
  note and turns the control into a real link; going back to $225 restores both;
  a $585 cart shows no note; **exactly $300 is allowed**; at $225 the checkout
  submit is disabled and clicking it neither navigates nor creates an order;
  after adding all upsells ($343) the notice is hidden and submit is enabled; and
  a real order then reaches `/order/ARL-…/` with the cart emptied, **no uncaught
  JS errors and no 404 responses**.
- The pre-existing **`check-cart-checkout.mjs` still passes 51/51**, the 20
  cart/checkout overflow combinations are still clean, and the 53
  `.surface-card` padding checks still pass — the gate did not regress the pages
  it was added to.
- `seo-check.php` against `https://arail-pharamceuticals.test`: **0 errors,
  1 warning** (`app.base_url`, unchanged and intentional), exit 0.
- Every order created while testing was deleted and the database was restored to
  its previous counts (**3 orders, 3 order items, 1 message, 1 subscriber,
  0 customers, 1 review**). The three pre-existing orders (ids 1, 15, 16) were
  never touched.

### 11. The admin panel now looks like the storefront (same font, same logo)

The admin was a separate look-alike: `system-ui`/Segoe UI, a teal that was close
to but not the site accent, and a text wordmark (`Arail Admin`) instead of the
logo. It now shares the storefront's design language.

- **Same font.** `admin/assets/admin.css` now declares the **Manrope** webfont
  from the same `assets/fonts/*.woff2` files `assets/css/site.css` loads, with
the same `"Manrope Fallback"` metrics-override face, and `body.admin` uses the
same computed stack as the storefront (`Manrope, "Manrope Fallback", system-ui,
sans-serif`). A browser check measures a fixed string in both places and gets an
identical width, so it is the same typeface rather than a lookalike.
- **Same logo.** The sidebar and the login card use
  `assets/img/arail-logo-exact-v9.png` — the exact file the storefront header
  uses — so both show the same artwork. The admin also declares the storefront's
  favicon/apple-touch-icon links, which removes a stray `/favicon.ico` 404.
- **Same palette.** The `:root` token block in `admin/assets/admin.css` is a copy
  of the storefront's (`--accent:#47a9d4`, `--accent-soft:#eaf6fb`,
  `--accent-dark:#2789b4`, `--ink:#2d2d2d`, `--muted:#758087`, `--line:#dedede`,
  `--background:#f5f8fa`, `--radius:20px`, `--radius-sm:12px`, `--shadow-soft`),
so buttons, inputs, cards, badges, pager and table rows all pick up the site's
colours, 12 px/20 px radii and soft shadows. Nav items use the storefront's
exact active/hover treatment (`bg-[accent-soft] text-[accent-dark]`).
- `admin/assets/admin.css` is deliberately **standalone** — it still does not load
  `theme.css`/`site.css`, so nothing here can leak into the storefront or reset
  the admin markup. The font faces and tokens are the only duplication, and they
  are byte-copied from the storefront's own files.

Two real bugs surfaced while restyling and were fixed:

- **Every product thumbnail on `/admin/products.php` was broken.** The column
  stores legacy mirror paths (`Shop_files/diwone-…-600x600.png`, all 50 rows),
  and the admin printed the raw value (`src="../Shop_files/…"`) instead of
  running it through the storefront's `image_url()` mapping. All 50 thumbnails
  returned 404. The admin now uses the same mapper.
  A second wrinkle: `image_url()` anchors its result to `base_path()`, which is
  derived from `dirname(SCRIPT_NAME)` — inside `/admin/` that is `/admin`, so the
  URL came back as `/admin/assets/…` and *still* 404'd. `admin/_common.php` adds
  `admin_image_url()`, which re-anchors the mapped path for the admin directory
  while keeping `image_url()` as the single source of the path mapping.
- **Buttons rendered in Arial**, not Manrope: native `<button>` elements do not
  inherit the page font. Fixed with `button,input,select,textarea{font:inherit}`.

Also fixed while in there: **the admin scrolled sideways on narrow screens**
(760 px of overflow at a 390 px viewport). Tables were allowed to dictate the
page width because a table's min-content width is the sum of its unbreakable
cells, and the dashboard's cards are grid items whose default `min-width:auto`
let a wide table widen its own track. Tables are now wrapped in a
`.table-scroll` container that scrolls internally, the row grids' items can
shrink (`min-width:0`), and the mobile layout stretches instead of
shrink-to-fitting `.content`.

Verification (headless Chrome through the real nginx server, logged in as
`admin`):

- New suite **`check-admin-theme.mjs` — 73 assertions, 0 failures**: the font,
the sample-text width match against the storefront, the logo being the same
file *and* loading, and every colour/radius token measured with
`getComputedStyle` on the live pages (accent `rgb(71,169,212)`, active nav
`rgb(234,246,251)` / `rgb(39,137,180)`, cards `20px`, page bg `rgb(245,248,250)`).
- `/admin/products.php` paginated across all three pages: **50 thumbnails, all
  loading (`naturalWidth > 0`), all resolving under `/assets/img/`, 0 remaining
  `Shop_files/` references**, no 404s. Spot-checked a sample of those URLs with
  `curl`: **200**.
- **All 10 admin pages return 200 with no PHP error text** (`index`, `products`,
  `product-edit`, `categories`, `orders`, `order-view`, `reviews`, `messages`,
  `subscribers`, `logout`).
- **No horizontal overflow** at 1440/1100/1024/900/768/390 px on the dashboard,
  orders, products and order-view pages.
- No uncaught JS errors and no 4xx/5xx responses anywhere in the run.
- `seo-check.php`: **0 errors, 1 warning** (`app.base_url`, unchanged), exit 0 —
  the storefront is untouched by this work.

---

## Configuration moved into an environment file

Every setting now comes from a single `.env` file in the project root and is read
with `getenv()` — nothing else has to be edited to configure the site.

- **New loader `includes/env.php`** (dependency-free: no Composer, no framework).
  It parses `.env` once per request and publishes each entry to `getenv()`,
  `$_ENV` and `$_SERVER`. It handles comments, blank lines, an `export` prefix,
  and single/double-quoted values (with `\n \r \t \" \\` unescaped); nothing is
expanded, so a literal `$` stays a `$`. A **real environment variable of the
  same name always wins over the file**, so one-off overrides keep working:
  `APP_DEBUG=1 php install.php`.
- **`includes/config.php` reads everything with `getenv()`.** The database
  connection uses exactly the requested names:

  ```
  DB_HOST   DB_PORT   DB_USERNAME   DB_PASSWORD   DB_NAME
  ```

  (replacing the old `ARAIL_DB_HOST` / `ARAIL_DB_PORT` / `ARAIL_DB_NAME` /
  `ARAIL_DB_USER` / `ARAIL_DB_PASS`). The rest of the configuration is exposed
  too, under `APP_*` / `ADMIN_*`: name, tagline, support email, phone, address,
  currency + code, country, flat shipping, **minimum order (300)**, uploads URL
  and directory, max upload size, base URL, GA4/GSC placeholders, and the
  installer defaults. `ARAIL_DEBUG` / `ARAIL_ALLOW_INSTALL` became `APP_DEBUG` /
  `APP_ALLOW_INSTALL`. The **keys inside config.php are unchanged**, so no call
  site and no template had to change.
- **`.env`** holds the live values and **`.env.example`** is the annotated,
  secret-free template to copy onto a new machine. A new **`.gitignore`** keeps
  `.env` out of version control.
- The loader is started **before anything reads a value**: from `includes/config.php`
  itself (which every consumer goes through) and from `includes/bootstrap.php` /
  `install.php`, which read flags directly.
- Because PHP-FPM reuses worker processes — and Herd serves every site from the
  same pool on `127.0.0.1:9085` — the published variables are **removed again at
  the end of the request**, so one site's `.env` cannot leak into another.
- `install.php`'s failure hint and the config header comment now point at `.env`.
- **Deployment note:** on Apache the repo's `.htaccess` protects `.env`. A real
  nginx host ignores `.htaccess`, so add `location ~ /\.(?!well-known) { deny all; }`
  to the server block. Herd's own config is machine-generated, which is why the
  local fix lives in `LocalValetDriver.php` instead.

### A stray `.env` would have been publicly downloadable

Herd/nginx (`resources/valet/server.php`) returns **any file that exists on
disk**, including dotfiles, and its only deny rule covers `.ht*` — so a plain
`.env` in the web root was readable over HTTP (proved with a temporary
`.dotfile-probe` file that came back `200` with its contents). It is now blocked
at three layers:

- **`LocalValetDriver.php`** (new): a Valet/Herd site driver that refuses to treat
  dotfiles as static files and routes them to `index.php`, which answers `403`.
  This is the layer that matters on this machine.
- **`index.php`** now refuses every dot-prefixed path (`.well-known` excepted) and
  `LocalValetDriver.php` itself — the driver is already `require_once`d by
  `server.php`, so letting it also run as a page was a fatal "cannot redeclare
  class". **`router.php`** (`php -S`) applies the same rule, so the built-in
  server behaves like the live one.
- **`.htaccess`** (for Apache) now denies every dotfile plus the server-side
  scripts, replacing the shorter `FilesMatch` list.
- **`seo-check.php`** gained a live probe: `/.env`, `/.env.example` and
  `/.git/config` are fetched and any `200` is reported as an error.

Verified:

- `php -l` clean on every changed/new PHP file.
- CLI: `getenv()` returns `DB_HOST` / `DB_PORT` / `DB_USERNAME` /
  `DB_PASSWORD` / `DB_NAME`, and the config resolves them to `127.0.0.1` /
  `3306` / `root` / `arail`. `db()` connects — **50 products, 3 categories,
  4 orders, 1 admin user**, matching the baseline — and `app.min_order` comes
  back as a float `300` while `max_upload_mb` is an int `3`.
- `.env` parsing checked for double quotes, a single-quoted `#`, trailing
  comments, an empty value, `export`, and a literal `$`.
- A real environment variable overrides the file (`DB_HOST=from-shell` wins).
- A missing `.env` falls back to the defaults without an error and still
  connects.
- HTTP on `https://arail-pharamceuticals.test`: `/.env`, `/.env.example`,
  `/.gitignore`, `/.git/config`, `/.htaccess` and `/LocalValetDriver.php` all
  return **403** with no secret in the body, while `/`, `/shop/`, `/cart/`,
  `/checkout/`, `/admin/login.php`, `/robots.txt`, `/sitemap.xml` and
  `/feed.php` all return **200**.
- `php -S 127.0.0.1:8123 router.php`: `/.env` → 403, pages → 200.
- Request-scoped cleanup confirmed: the values are visible during the request
  and `getenv('DB_HOST')` is `false` again once it ends.
- `check-min-order.mjs` **30/30**, `check-admin-theme.mjs` **85/85**, and
  `seo-check.php` **0 errors, 1 warning** (the intentional empty `APP_BASE_URL`),
  exit 0. The single order the min-order suite creates was deleted, the database
  is back to its baseline counts, and orders 1, 15, 16 and 35 are untouched.

---

---

## Wasmer Edge: "500 Htaccess evaluation failed"

The deployment at `https://arailpharmaceutical.wasmer.app/` answered **every**
request — `/`, `/shop/`, `/assets/css/site.css`, even 404s — with

```
HTTP/1.1 500 Internal Server Error
x-phpix-version: 0.3.0-rc.5

Htaccess evaluation failed
```

### Cause

Wasmer Edge runs this app on **phpix** (`phpix: true`, PHP 8.3, anybuild provider
`php`), a PHP runtime that **parses `.htaccess` itself** and implements only a
small subset of it. The `.htaccess` was written for Apache: `Options`,
`DirectoryIndex`, `ErrorDocument`, `<IfModule>` blocks, compression, caching and
— the fatal one — security headers written as `Header always set …`. phpix stops
at the first directive it does not implement and fails the whole request. The
runtime's own log named the exact position:

```
ERROR phpix::server::htaccess: htaccess evaluation failed request_path=/
  error=Parse(ParseError { message: "Unsupported header action",
    span: Span { start: Position { offset: 2651, line: 61, column: 12 },
                 end:   Position { offset: 2657, line: 61, column: 18 } } })
```

Line 61, column 12–18 of the deployed file is `always` in
`    Header always set X-Content-Type-Options "nosniff"`: phpix takes the word
after `Header` as the action and only knows its own set.

### What changed

- **`.htaccess` is back, in the minimal rewrite-only form phpix provably
  accepts** — the same four lines the working sibling app
  `nexuspharmacy.wasmer.app` runs:

  ```
  RewriteEngine On
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule ^ index.php [QSA,L]
  ```

  Everything else it used to do was either already handled by the front
  controller (dotfiles / `db/` / `tools/` → 403, the legacy `.html` 301s, the
  `.php` and `index.php` stripping, the 404 page) or is the hosting platform's
  job (HTTPS: Herd's 301 and Wasmer's `force_https`; gzip/brotli and caching).
- **The security headers moved into PHP, where they actually run on every host:**
  `arail_send_security_headers()` in `includes/helpers.php`, called from
  `includes/bootstrap.php`. The five headers are byte-for-byte the ones from the
  old `.htaccess` (`X-Content-Type-Options`, `Referrer-Policy`, `X-Frame-Options`,
  `Permissions-Policy`, CSP). This exposed a second, silent problem: Herd/nginx
  never read `.htaccess`, so **none of those headers were being sent** — `curl -I`
  showed none before the change and all five after.
- The `.htaccess` the user deleted from the project root (it was still in the
  Recycle Bin) is restored in the new minimal form, so the repository is complete
  again.

Verified:

- `php -l` clean on `includes/helpers.php` and `includes/bootstrap.php`.
- Local nginx: all 24 public URLs return **200**; `/.env`, `/.env.example`,
  `/.gitignore`, `/.htaccess`, `/LocalValetDriver.php`, `/db/schema.php` and
  `/tools/optimize-images.php` return **403**.
- **`check-csp.mjs`** (new harness): 16 pages, **0 CSP violations**, all five
  headers present on every page.
- `check-min-order.mjs` **30/30**, `check-cart-checkout.mjs` **51/51**,
  `check-admin-theme.mjs` **85/85** — the CSP does not break a single inline
  script or the admin panel.
- `seo-check.php`: **0 errors, 1 warning** (the usual empty `APP_BASE_URL`), and
  its secret probes report `403 /.env blocked`.
- The transient orders the browser suites create were deleted again; orders 1,
  15, 16 and 35 are untouched and the counts match the baseline.

### On a real Apache host

`.htaccess` no longer denies dotfiles or the server-side scripts, because phpix
rejects that directive. On Apache only, add back:

```
<FilesMatch "(^\.|^(config|seed|migrate|enrich|schema|seo-check)\.php$)">
    Require all denied
</FilesMatch>
```

The hosts actually in use are covered without it: Herd/nginx goes through
`LocalValetDriver.php` + `index.php` (section 2), and phpix answers
`Direct access forbidden` for any path starting with a dot — the same answer the
sibling app returns for its own `.htaccess`.

---

## Verification performed

- `php -l` on **all** PHP files — clean.
- All 24 public URLs return **200**; `/this-page-should-not-exist/` returns **404**.
- **Cart/checkout diffed against the supplied captures**: 0 classes present in
  the reference but missing from the live page (both pages), and geometry
  identical on every measured element (see *Post-audit fixes #9*).
- Regression suites re-run after that rebuild: 100 page/width overflow
  combinations, 20 more with a 3-item cart, 53 `.surface-card` padding checks,
  the navbar suite and the FAQ/footer suite — all pass. `seo-check.php`:
  **0 errors, 1 warning**, exit 0.
- **$300 minimum order** re-checked end to end (see *Post-audit fixes #10*): a
  dedicated 30-assertion browser suite passes, the cart/checkout regression
  suites still pass unchanged, and the server rejects a short order with 422 even
  when the disabled button is bypassed.
- **Admin panel** re-checked after the restyle (see *Post-audit fixes #11*): a
  73-assertion browser suite passes, every admin page returns 200 without PHP
  errors, all 50 product thumbnails load, and there is no horizontal overflow
  from 390 px up to 1440 px.
- Checkout order created and shown on the confirmation page (prices verified:
  2 × $40 + $30 = $110), then the test order and all other test rows were deleted.
- Account register → dashboard → logout verified.
- Contact, subscribe and review endpoints verified.
- `feed.php` and all three sitemaps confirmed **well-formed XML** (DOMDocument).
- All internal links and assets verified; **0 broken links**, **0 missing assets**.
- **0 references** to the unpurchased domain in any served page or PHP file, and
  the archived mirror that did contain them is no longer reachable over HTTP.
- Re-verified end to end through the real nginx server (not just `php -S`), plus a
  headless-Chrome pass over the navbar, the contact-page FAQs and the footer at
  five viewport widths (see *Post-audit fixes*).

---

## Not done / left for you

1. **`APP_BASE_URL` is unset** because the domain isn't purchased. Set it in
   `.env` before launch — until then canonicals/sitemaps/feed follow the request
   host, which is fine locally but wrong in production.
2. **Product descriptions are thin**: 48 of 50 products have a one-line
   description. `docs/content-todo.txt` lists every product that needs 150–300
   words of original copy. Nothing was invented to pad them.
3. **GTINs are missing for all 50 products** — real barcodes must come from the
   supplier. Merchant Center may warn about this.
4. **Category/trust-page copy**: category SEO blocks, the shop introduction, and
   the About/Shipping/Returns/Privacy/Terms pages contain explicit `TODO` markers
   where your own wording is required.
5. **Blog / buying guides** structure was not built (Phase 6). It's a content
   project of its own.
6. **AVIF** was not generated (WebP only) — AVIF would save a further ~20 % but
   needs a newer encoder than GD provides here.
7. **Faceted "indexable" combinations** (e.g. "black leather sofas" equivalents)
   were not researched or whitelisted; all filters are currently `noindex`.
8. The legacy HTML mirror, the 8 `*_files/` directories and the
   `checkout-and-cart/` captures (~44 MB of now-duplicated assets) were **moved
   out of the site directory** to `C:\Users\HP\arail-legacy-archive\` rather
   than deleted, because leaving them in the web root served a crawlable
   duplicate of the site that linked to the unpurchased domain. Nothing was
   destroyed — move them back, or delete the folder, whenever you're happy.
9. **Coupon codes have no pricing engine.** The box matches the design, the code
   is remembered and stored on the order, but nothing is discounted. Real coupon
   support needs a `coupons` table, validation and discount maths — tell me the
   rules and I'll build it.
10. The **billing-address checkbox** (“Billing address same as shipping”) is
   present and checked because the reference has it, but there is no billing
   address form behind it in the capture either.
11. The **$300 minimum order** is enforced on the merchandise subtotal, and the
   threshold lives in one place (`APP_MIN_ORDER` in `.env`). If
   you sell a cheap accessory you want exempt, that product-level exception does
   not exist yet — tell me the rule and I'll add it.
12. **The admin's colour tokens are a copy, not a shared file.**
   `admin/assets/admin.css` duplicates the storefront's `:root` block and the
   Manrope `@font-face` rules on purpose (so the admin stays standalone), which
   means a future palette or font change has to be made in **both**
   `assets/css/theme.css`/`site.css` and `admin/assets/admin.css`. If you would
   rather they could never drift, extract the tokens and faces into a shared
   `assets/css/tokens.css` and link it from both — say the word.
13. **`/favicon.ico` is still a 404 site-wide.** The storefront declares
   `favicon-32.png` (and now the admin does too, so no admin page probes
   `/favicon.ico`) but a request for the bare `/favicon.ico` path returns 404.
   Harmless, but add an `.ico` at the web root if you want to silence it.

---

## Manual checklist for you

- [ ] Buy the domain, then set `APP_BASE_URL` in `.env` and re-run
      `php seo-check.php`.
- [ ] Uncomment the canonical-host rule in `.htaccess` and set your real domain.
- [ ] Enable HSTS in `.htaccess` once HTTPS is verified.
- [ ] Delete `install.php` (or keep it CLI-only) — it is already HTTP-disabled.
- [ ] In Google Search Console: add the property, paste the token into
      `app.gsc_token`, then submit `https://your-domain/sitemap.xml`.
- [ ] In Merchant Center: submit `https://your-domain/feed.php` (steps in Phase 5).
- [ ] Set up a Google Business Profile if you have a physical address.
- [ ] Do keyword research, then write the category, shop and buying-guide copy.
- [ ] Collect real customer reviews (they are the only source of ratings).
- [ ] Build backlinks (supplier directories, lab reports, forum threads).
- [ ] Provide real GTINs, or accept the Merchant Center warnings.
- [ ] Add your real support email/phone/address to `.env`.
