# SEO · GEO · LLM 2026 — Complete Audit

**Project:** CompressPix — free online image compressor (Laravel)
**Audit date:** 2026-09-16
**Audit type:** Read-only code audit plus rendered-HTML verification. No project files, database records or packages were changed.
**Method:**
1. Read every route, controller, model, config file, Blade layout, component, view, content file, seeder, migration, JS/CSS entry and feature test.
2. Rendered every public route in memory: production env, SQLite `:memory:`, seeded posts, `SITE_INDEXABLE=true`, `APP_URL=https://compresspix.example`. Captured status codes, `<head>` output, JSON-LD, sitemap/robots output and internal-link graphs.
3. Did a light SERP check for the two main query families (see §16).

### Evidence labels used throughout

| Label | Meaning |
|---|---|
| **VERIFIED** | Confirmed in the code and/or in the rendered HTML output. |
| **RECOMMENDATION** | A change I suggest, based on a verified finding. |
| **OPPORTUNITY** | A growth idea that isn't fixing a defect. |
| **EXTERNAL DATA** | Needs production server config, Search Console, analytics, CrUX or backlink data that the codebase doesn't contain. |

---

## Table of contents

1. Executive Summary
2. Project Architecture
3. Complete Page Inventory
4. Page-by-Page SEO Audit
5. Technical SEO Findings (Critical → Informational)
6. Metadata Audit
7. Structured Data Audit
8. Internal Linking Architecture
9. Image SEO Audit
10. GEO / AI Search Audit
11. LLM Understanding Audit
12. Content Gap Analysis
13. Keyword / Intent Map
14. Cannibalization Report
15. Orphan Page Report
16. Competitor Opportunity Analysis
17. Core Web Vitals / Performance
18. Mobile SEO
19. Crawlability & Indexation
20. Sitemap / Robots
21. E-E-A-T / Trust
22. 2026 SEO Recommendations
23. Prioritized Action Plan
24. Final SEO Scorecard
25. Top 25 Actions to Implement First

---

## 1. Executive Summary

### Current SEO architecture

CompressPix is a server-rendered Laravel 13 / Blade site. Its SEO architecture is centralized and generally clean:

- **One SEO value object:** `App\Support\Seo\Seo` holds title, description, canonical, robots, OG image, type, breadcrumbs, schema and article dates.
- **One structured-data factory:** `App\Support\Seo\StructuredData` builds WebSite, WebApplication, BreadcrumbList, FAQPage and BlogPosting.
- **One head component:** `resources/views/components/seo/meta.blade.php` prints title, description, robots, canonical, OG, Twitter, icons, manifest and JSON-LD.
- **Tools are config-driven:** `config/tools.php` → `resources/content/tools/*.php` → `App\Tools\ToolRegistry` → one controller (`ToolController@show`) and one template (`tools/show.blade.php`), with a unique content partial per tool.
- **Blog is DB-driven:** the `posts` table is seeded from Markdown (`database/seeders/content/posts/*.md`).
- **Sitemap and robots are dynamic:** `SeoController` serves `/sitemap.xml`, `/robots.txt` and `/site.webmanifest`.
- **One indexability switch:** `config('site.indexable')` (`SITE_INDEXABLE`) controls both the meta robots tag and robots.txt.

### Major strengths (VERIFIED)

- Every public page renders a unique `<title>`, meta description, self-referencing absolute canonical, exactly one H1, OG/Twitter tags and BreadcrumbList JSON-LD. This is enforced by `tests/Feature/PublicPagesTest.php`.
- All SEO content is in the initial HTML. JavaScript is only needed for the compressor widget itself.
- Tool pages carry real, specific, non-boilerplate content: about 500–900 unique words each, with how-to steps, format explanations and FAQs.
- The 8 blog articles are substantial (1,109–1,559 words each), accurate, well structured with H2/H3 and tables, and internally linked to tools.
- Blog search (`?q=`) is correctly `noindex, follow`. Unknown or draft posts return real 404s. Error pages are `noindex` with no canonical.
- Clear URL design: short, lowercase, hyphenated, flat, keyword-bearing slugs. Uppercase variants 404 rather than duplicating.
- Privacy-first processing that runs in the browser is clearly explained. That's a genuine differentiator for users and for AI answer engines.

### Major weaknesses

1. **E-E-A-T placeholders would ship as-is.** `[Company Name]`, `[Hosting Provider]`, `[Jurisdiction]`, `[Retention Period]`, `[Log Retention Period]` and `hello@example.com` appear on the legal pages, the Contact page and the privacy policy.
2. **Homepage and `/image-compressor` target the same intent:** same widget, near-identical title, same WebApplication entity, same FAQ themes. Sitewide navigation sends more body links to `/image-compressor` than the homepage gets for that intent.
3. **Almost no image SEO.** For a site in the image niche there are zero content images, no featured images on any post, no image sitemap, and one generic OG image for every URL.
4. **Weak entity layer.** No Organization entity (logo, contact point, sameAs), no `@id` graph linking WebSite → Organization → WebPage → WebApplication/BlogPosting, articles authored by "Organization", and an About page with no operator identity.
5. **Indexation controls are fragile by environment.** `.env.example` ships `SITE_INDEXABLE=true`, and `.env` has `APP_URL=http://127.0.0.1:8765`. If the production `.env` isn't set carefully, canonicals and the sitemap will point at the wrong host, or staging copies will be indexable.
6. **Crawl-waste and soft-404 edges.** `/blog?page=99` returns 200 with a self-canonical and `index`. `/up` (Laravel health check) returns 200 HTML with no robots control.
7. **Fabricated-looking publish dates.** The seeder back-dates `published_at` relative to *whenever the seed runs*, while `dateModified` is the seed time.

### Biggest opportunities

- Consolidate home and `/image-compressor` into one strong "image compressor" URL. Easiest done now, before launch.
- Build original, indexable visual content: before/after compression examples, artifact demonstrations, format comparison images. Useful for Google Images, Discover eligibility, AI answer citations and topical authority.
- Add supported-but-missing tool landing pages that the widget already handles: 50KB (the preset exists but has no page), 20KB/30KB, 2MB, and format conversions such as PNG→JPG, JPG→WebP, PNG→WebP, WebP→JPG.
- Add an entity graph plus a real About/operator page to become the "known source" that LLMs can describe accurately.
- Add original, citable measurements (a benchmark table of quality setting vs. file size on a published test set). This is the strongest GEO lever available.

### Biggest risks

- Launching with placeholder legal/company text → a trust/quality signal problem (Search Quality Rater guidelines emphasize who is responsible for a site).
- Launching with the wrong `APP_URL`/`SITE_INDEXABLE` → the whole site canonicalized to localhost, or blocked (EXTERNAL DATA: production `.env` not visible).
- Home vs `/image-compressor` cannibalization for the site's single most valuable head term.
- A crowded SERP: dozens of established free compressors (see §16). Undifferentiated tool pages will struggle without authority, unique content assets and links.

---

## 2. Project Architecture

### Stack (VERIFIED)

| Layer | Detail | Source |
|---|---|---|
| PHP | ^8.3 (local CLI 8.3.32) | `composer.json` |
| Framework | Laravel ^13.17 | `composer.json` |
| Frontend build | Vite ^8, laravel-vite-plugin ^3.1, Tailwind CSS ^4 (`@tailwindcss/vite`) | `package.json`, `vite.config.js` |
| Fonts | Inter 400/500/600/700, self-hosted via `laravel-vite-plugin/fonts` (bunny provider, bundled), `@fonts` directive | `vite.config.js`, layout |
| JS | `resources/js/app.js` (mobile menu, 632 B); `resources/js/compressor/*` (browser compression engine, 22.7 KB raw / 8.4 KB gzip) | `public/build/manifest.json` |
| Server image processing | GD fallback: `App\Services\ImageCompression\GdImageCompressor` via `POST /process/compress` | controller |
| DB | MySQL (local); `posts`, `contact_messages`, `users`, `cache`, `sessions` | migrations, `.env` |
| Session/cache | `SESSION_DRIVER=database`, `CACHE_STORE=database` | `.env` |
| Analytics | GA4, only when `ANALYTICS_GA4_ID` is set **and** `app()->isProduction()` | `components/seo/analytics.blade.php` |
| Security headers | `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy` (no HSTS/CSP) | `app/Http/Middleware/SecurityHeaders.php` |
| Admin / CMS | None. Posts only come from the seeder. | routes, tests (`/admin` → 404) |
| i18n | English only; `lang="en"`, `og:locale=en_US` | layout, meta |

### SEO-relevant component map

```
routes/web.php
 ├─ HomeController (invokable) ───────────► home.blade.php
 ├─ ToolController@show (x8, config/tools.php) ► tools/show.blade.php
 │     ├─ tools/widgets/compressor.blade.php → components/tools/compressor(.blade + /controls /upload-area /image-preview /result)
 │     └─ tools/content/{key}.blade.php (unique copy per tool)
 ├─ PageController@about|faq|sitemap|legal ► pages/*.blade.php, pages/legal/*.blade.php (prose layout)
 ├─ ContactController@show|store ────────► pages/contact.blade.php
 ├─ BlogController@index|show ───────────► blog/index.blade.php, blog/show.blade.php
 └─ SeoController@sitemap|robots|manifest ► seo/sitemap.blade.php, text, JSON

components/layouts/app.blade.php
 ├─ <x-seo.meta :seo>  ← App\Support\Seo\Seo  ← App\Support\Seo\StructuredData
 ├─ @fonts, @vite, <x-seo.analytics/>
 ├─ <x-site.header/>  (nav from config('tools.navigation') + Blog)
 └─ <x-site.footer/>  (config('tools.footer') + Company/Resources/Legal)
```

### Route table (VERIFIED from `php artisan route:list`)

27 routes: 22 public GET pages/endpoints, 2 POST, the framework `storage/{path}` GET/PUT (local disk `serve => true`), and `/up`.

---

## 3. Complete Page Inventory

Legend: Canonical ✓ = self-referencing absolute canonical verified in the rendered HTML. Schema abbreviations: BC = BreadcrumbList, WA = WebApplication, FAQ = FAQPage, WS = WebSite, BP = BlogPosting.

| URL | Route | Blade/View | Page Type | Indexable | Canonical | Sitemap | Schema | SEO Status | Priority |
|---|---|---|---|---|---|---|---|---|---|
| `/` | `home` | `home` | Home + tool hub | Yes | ✓ (no trailing slash) | Yes (1.0) | WS, WA (points to /image-compressor), FAQ | Good markup; **cannibalizes /image-compressor** | P0 |
| `/image-compressor` | `tools.image-compressor` | `tools/show` + `tools/content/image-compressor` | Tool (generic) | Yes | ✓ | Yes (0.9) | BC, WA, FAQ | Title 68 chars; cannibalization | P0 |
| `/compress-jpg` | `tools.compress-jpg` | `tools/show` + content | Tool (format) | Yes | ✓ | Yes | BC, WA, FAQ | Good; overlaps a blog post | P1 |
| `/compress-png` | `tools.compress-png` | `tools/show` + content | Tool (format) | Yes | ✓ | Yes | BC, WA, FAQ | Good; overlaps a blog post | P1 |
| `/compress-webp` | `tools.compress-webp` | `tools/show` + content | Tool (format/convert) | Yes | ✓ | Yes | BC, WA, FAQ | Good | P2 |
| `/compress-image-to-100kb` | `tools.compress-image-to-100kb` | `tools/show` + content | Tool (size target) | Yes | ✓ | Yes | BC, WA, FAQ | Good; overlaps a blog post | P1 |
| `/compress-image-to-200kb` | `tools.compress-image-to-200kb` | same | Tool (size target) | Yes | ✓ | Yes | BC, WA, FAQ | Under-linked (7 inbound) | P2 |
| `/compress-image-to-500kb` | `tools.compress-image-to-500kb` | same | Tool (size target) | Yes | ✓ | Yes | BC, WA, FAQ | Under-linked (6 inbound) | P2 |
| `/compress-image-to-1mb` | `tools.compress-image-to-1mb` | same | Tool (size target) | Yes | ✓ | Yes | BC, WA, FAQ | **Weakest linked (3 inbound)** | P1 |
| `/blog` | `blog.index` | `blog/index` | Blog listing | Yes | ✓ | Yes (0.7) | BC | Good | P2 |
| `/blog?page=N` | `blog.index` | `blog/index` | Pagination | Yes (self-canonical) | ✓ self | No | BC | **Out-of-range pages = soft 404** | P1 |
| `/blog?q=…` | `blog.index` | `blog/index` | Internal search | noindex, follow | → `/blog` | No | BC | noindex + cross-canonical (mixed signal) | P3 |
| `/blog/how-to-compress-image-without-losing-quality` | `blog.show` | `blog/show` | Article (featured) | Yes | ✓ | Yes (lastmod) | BC, BP | Strong; no images/author | P1 |
| `/blog/how-to-compress-jpg-images-online` | `blog.show` | `blog/show` | Article | Yes | ✓ | Yes | BC, BP | **Cannibalizes /compress-jpg** | P1 |
| `/blog/how-to-compress-png-images` | `blog.show` | `blog/show` | Article | Yes | ✓ | Yes | BC, BP | Overlaps /compress-png | P2 |
| `/blog/how-to-reduce-image-size-for-website` | `blog.show` | `blog/show` | Article | Yes | ✓ | Yes | BC, BP | Strong pillar candidate | P2 |
| `/blog/how-to-compress-image-to-100kb` | `blog.show` | `blog/show` | Article | Yes | ✓ | Yes | BC, BP | Overlaps 100KB tool | P1 |
| `/blog/jpg-vs-png-vs-webp` | `blog.show` | `blog/show` | Article (comparison) | Yes | ✓ | Yes | BC, BP | Strong GEO candidate | P2 |
| `/blog/how-image-compression-works` | `blog.show` | `blog/show` | Article (explainer) | Yes | ✓ | Yes | BC, BP | Strong GEO candidate | P2 |
| `/blog/best-image-formats-for-websites` | `blog.show` | `blog/show` | Article | Yes | ✓ | Yes | BC, BP | Partial overlap with jpg-vs-png-vs-webp | P2 |
| `/faq` | `pages.faq` | `pages/faq` | FAQ hub | Yes | ✓ | Yes | BC, FAQ | Generic H1 | P3 |
| `/about-us` | `pages.about` | `pages/about` | Trust | Yes | ✓ | Yes | BC | **No operator identity** | P0 |
| `/contact-us` | `pages.contact` (GET) | `pages/contact` | Trust | Yes | ✓ | Yes | BC | **Placeholder email** | P0 |
| `/privacy-policy` | `pages.privacy-policy` | `pages/legal/privacy-policy` | Legal | Yes | ✓ | Yes | BC | **Placeholders** | P0 |
| `/terms-and-conditions` | `pages.terms-and-conditions` | `pages/legal/terms-and-conditions` | Legal | Yes | ✓ | Yes | BC | **`[Jurisdiction]` placeholder** | P0 |
| `/cookie-policy` | `pages.cookie-policy` | `pages/legal/cookie-policy` | Legal | Yes | ✓ | Yes | BC | OK | P4 |
| `/disclaimer` | `pages.disclaimer` | `pages/legal/disclaimer` | Legal | Yes | ✓ | Yes | BC | OK | P4 |
| `/sitemap` | `pages.sitemap` | `pages/sitemap` | HTML sitemap | Yes | ✓ | Yes (0.3) | BC | Low search value | P4 |
| `/sitemap.xml` | `seo.sitemap` | `seo/sitemap` | XML | n/a | n/a | — | — | Missing lastmod on 18/26 URLs | P2 |
| `/robots.txt` | `seo.robots` | inline | Text | n/a | n/a | — | — | OK | P3 |
| `/site.webmanifest` | `seo.manifest` | JSON | Manifest | n/a | n/a | — | — | OK | P4 |
| `/up` | framework health | framework view | Utility | **Unprotected** (200 HTML, no robots) | none | No | — | Should be noindex/blocked | P3 |
| `POST /process/compress` | `process.compress` | JSON/binary | API | GET → 405 | — | No | — | Disallowed in robots ✓ | — |
| `POST /contact-us` | `pages.contact.store` | redirect | Form | — | — | No | — | OK | — |
| `storage/{path}` GET/PUT | `storage.local*` | framework | File serving | Not linked | — | No | — | Informational | P4 |
| 404/403/419/429/500/503 | exceptions | `errors/*` | Error | noindex, follow | none | No | none | Correct | — |

---

## 4. Page-by-Page SEO Audit

> Rendered figures come from the in-memory render. "Rendered words" = visible text inside the page (header/footer/nav removed), including widget UI labels. Titles include the `| CompressPix` suffix added by `Seo::fullTitle()`.

### 4.1 `/` — Homepage

| Aspect | Assessment |
|---|---|
| Search intent | Transactional/navigational: "image compressor", "compress image online", plus the brand query. |
| Primary topic | Online image compression (JPG, PNG, WebP). |
| Title (VERIFIED) | `Image Compressor: Compress Images Online Free \| CompressPix`: 59 chars, head term first, brand last. Good, but nearly identical to `/image-compressor`. |
| Meta description (VERIFIED) | 159 chars, benefit-led, near the truncation limit. Good. |
| H1/H2 (VERIFIED) | H1 "Compress Images Online". 12 H2s, including a visually small "Supported formats" H2, a CTA H2 "Ready to compress your images?", and 4 **footer H2s** ("Tools", "Company", "Resources", "Legal") that appear on every page. 25 H3s. |
| Content | About 1,900 rendered words: steps, reasons, features, 6 FAQs, latest guides. Good, but heavily overlaps `/image-compressor` (same widget, same "why compress" and "how it works" themes). |
| Internal linking | 39 internal links. Body links to 4 tools only (JPG, PNG, WebP, 100KB). **200KB/500KB/1MB aren't linked from the homepage.** |
| Image SEO | No content images; only the SVG logo (`alt=""` inside a link with `aria-label`, which is correct). |
| Schema (VERIFIED) | WebSite (no publisher, no `@id`); WebApplication whose `url` is `/image-compressor` (entity sits on home but points away); FAQPage (FAQ rich results no longer shown as of May 2026, see §7). |
| Canonical | `https://compresspix.example` (no trailing slash). Acceptable; Google normalizes the root. |
| Indexability | `index, follow`; no `max-image-preview:large`. |
| UX/performance | 79 KB HTML raw (≈12 KB gz). Compressor JS loads only where needed. No above-the-fold images, so LCP will be a text node. Good. |
| GEO/LLM | Lacks a one-sentence "what CompressPix is and who runs it" definition; no Organization entity. |
| **Recommended actions** | (1) Resolve the home vs `/image-compressor` conflict (§14, option A). (2) Add Organization + WebSite `@graph` with `@id`s. (3) Link all 7 tool pages from the home tool grid. (4) Change footer column headings from `<h2>` to non-heading elements (e.g. `<p class="…">`) or accept them as low-priority. (5) Add `max-image-preview:large` to robots. |
| Priority | **P0** |

**Recommended title (if home becomes the canonical "image compressor" URL):** `Image Compressor – Compress JPG, PNG & WebP Online Free | CompressPix`
**Recommended H1:** `Free Online Image Compressor` (keeps "image compressor" in the H1; "Compress Images Online" can become the sub-heading).

---

### 4.2 `/image-compressor`

| Aspect | Assessment |
|---|---|
| Intent / topic | Same as the homepage: "image compressor". |
| Title (VERIFIED) | `Image Compressor: Compress JPG, PNG & WebP Online Free \| CompressPix`: 68 chars, likely truncated on desktop. |
| Description | 148 chars. Good. |
| H1 | "Image Compressor". Short but exact-match. |
| H2s | "How to compress an image", "What this image compressor does", "Choosing the right format", "Why compress your images?", "Compress an image to a specific size", FAQ, "Guides and tips", "More image compression tools", plus footer. Logical. |
| Content | About 2,080 rendered words; unique partial `tools/content/image-compressor.blade.php`. Good hub of links to all format/size tools. |
| Internal linking | Most-linked tool: header CTA "Compress Image" (every page), nav "Image Compressor", breadcrumb parent of every other tool, 404 page CTA, blog "Try it yourself" sidebars, 21 body links. |
| Schema | BC (Home › Image Compressor), WA, FAQ. |
| Issue | **VERIFIED cannibalization with `/`** (§14 #1). |
| **Recommended actions** | See §14 #1. If kept separate instead of consolidated: shorten the title to `Online Image Compressor – JPG, PNG & WebP \| CompressPix` (≈55 chars) and differentiate the homepage as a brand/tool hub. |
| Priority | **P0** |

---

### 4.3 `/compress-jpg`

| Aspect | Assessment |
|---|---|
| Intent | Transactional: "compress jpg", "compress jpeg", "reduce jpg size", "jpg compressor". |
| Title | `Compress JPG Online: Free JPG Compressor \| CompressPix` (54). Good; covers both "compress jpg" and "jpg compressor". JPEG is only in the description. |
| Description | 148; includes JPEG. Good. |
| H1 | "Compress JPG Images Online". **Identical wording to the blog post title "How to Compress JPG Images Online".** |
| Content | Strong: JPEG pipeline explanation (YCbCr, 8×8 DCT, quantization, entropy coding), tips, benefits, 6 FAQs. |
| Linking | Nav, footer, image-compressor hub, home grid, several posts. |
| Image SEO | **Opportunity:** the "How JPG compression works" section has no visual. A quality-ladder figure (same photo at 90/75/50/25 with byte sizes) would be highly relevant to Google Images and genuinely helpful. |
| Schema | WA `name` = H1 ("Compress JPG Images Online"). An entity name should be a product name, e.g. "CompressPix JPG Compressor". |
| **Actions** | Add "JPEG" to the H1 (`Compress JPG / JPEG Images Online`). Rename the competing blog post (§14 #2). Add an original example figure. Fix the WA name. |
| Priority | P1 |

### 4.4 `/compress-png`

| Aspect | Assessment |
|---|---|
| Intent | "compress png", "reduce png file size", "png compressor", "compress png keep transparency". |
| Title | `Compress PNG Online: Reduce PNG File Size Free \| CompressPix` (60). Good. |
| Description | 145. Good; mentions transparency. |
| H1 | "Compress PNG Images Online". Fine. |
| Content | Excellent honesty-first explanation (lossless vs palette quantization, colour counts per quality, transparency, server fallback behaviour). Unique. |
| Overlap | `/blog/how-to-compress-png-images` (§14 #3). |
| Image SEO | Opportunity: a palette-reduction example (256 vs 64 vs 16 colours with sizes) and a transparency-preserved example. |
| **Actions** | Differentiate the blog post title. Add a figure. Fix the WA name. Consider "PNG compressor" in the H1 or an H2 for synonym coverage. |
| Priority | P2 |

### 4.5 `/compress-webp`

| Aspect | Assessment |
|---|---|
| Intent | Mixed: "compress webp" plus conversion intent "jpg to webp", "png to webp". |
| Title | `Compress WebP Online: Free WebP Compressor \| CompressPix` (56). |
| Description | 156; mentions conversion. Good. |
| H1 | "Compress WebP Images Online". |
| Content | Good; the "Using WebP on a website" section links to 2 posts. |
| Issue | Conversion intent ("convert jpg to webp", "png to webp") is a separate, large query class. One page serving both dilutes both. **OPPORTUNITY:** dedicated `jpg-to-webp` / `png-to-webp` pages (§12). |
| Priority | P2 |

### 4.6 `/compress-image-to-100kb`

| Aspect | Assessment |
|---|---|
| Intent | Strongly transactional, often from job/exam/visa form users on mobile. |
| Title | `Compress Image to 100KB Online Free \| CompressPix` (49). Room to add a "JPEG" synonym: competitors rank with "Compress JPEG to 100KB" (§16). |
| Description | 142. Good use case focus. |
| H1 | "Compress Image to 100KB Online". |
| Content | Good and specific (KB = 1,024 bytes nuance; 95 KB safety margin; dimensions guidance). |
| Preset | JPG output and 100 KB target pre-selected (VERIFIED by test `test_target_size_pages_preselect_their_target`). |
| Overlap | `/blog/how-to-compress-image-to-100kb` (§14 #4). |
| Linking | Footer (every page) and home grid. Well linked. |
| **Actions** | Title → `Compress Image to 100KB (JPG/JPEG) Online Free \| CompressPix` (≈60). Add a short "typical dimensions under 100KB" table on the tool page, since that's answer-extractable. Differentiate the blog post. |
| Priority | P1 |

### 4.7 `/compress-image-to-200kb`

| Aspect | Assessment |
|---|---|
| Title / description | `Compress Image to 200KB Online Free \| CompressPix` (49) / 148. Good. |
| H1 | "Compress Image to 200KB Online". |
| Content | Unique; good "200KB is a ceiling, not a goal" section. |
| Linking (VERIFIED) | **7 inbound internal links**, not in header or footer. |
| **Actions** | Add to the footer "Tools" column and the home tool grid; link from the 100KB and 500KB blog/tool content (partly done). |
| Priority | P2 |

### 4.8 `/compress-image-to-500kb`

| Aspect | Assessment |
|---|---|
| Title / description | 49 / 142. Good. |
| Content | Unique; explains max-quality behaviour with a target. |
| Linking (VERIFIED) | **6 inbound.** |
| Actions | Same as 200KB. |
| Priority | P2 |

### 4.9 `/compress-image-to-1mb`

| Aspect | Assessment |
|---|---|
| Title / description | `Compress Image to 1MB Online Free \| CompressPix` (47) / 154. |
| Content | Unique; the email base64 overhead point is a good, citable fact. |
| Linking (VERIFIED) | **Only 3 inbound links** (image-compressor hub, 500KB page, HTML sitemap). Weakest page in the site. |
| **Actions** | Footer + home grid + link from `how-to-compress-image-without-losing-quality` and `how-to-compress-image-to-100kb` ("If the limit is larger", which already lists it as a relative link, VERIFIED in markdown). Add "2MB" as a sibling page later. |
| Priority | **P1** |

### 4.10 `/blog`

| Aspect | Assessment |
|---|---|
| Intent | Navigational/informational hub. |
| Title | `Image Compression Blog: Guides, Formats and Tips \| CompressPix` (62). OK. |
| Description | 118. Could be richer. |
| H1 | "Image Compression Blog". |
| Content | Featured post card + grid of 7 + search form. No intro text about topics or authorship. |
| Pagination (VERIFIED) | Links via the default Laravel paginator (`?page=N`); canonical self-references each page (correct modern practice). **`/blog?page=99` → 200, "No articles found. Try a different search term.", `index, follow`, canonical `?page=99`: soft 404.** |
| Pagination bug (VERIFIED by code) | `BlogController@index` excludes the featured post only when `$page === 1`. From page 2 onward the query includes it again, so the offsets differ between page 1 and page 2. Once there are more than 10 posts, one post will be **duplicated** across pages 1 and 2 (or skipped, depending on the featured post's position). |
| Search | `?q=`: `noindex, follow` ✓; canonical → `/blog` (noindex + cross-canonical is a mixed signal; low impact). |
| Images | Post cards would render `featured_image` with `alt=""`, but no post has one. The featured card uses a decorative icon. |
| **Actions** | 404 for `page > lastPage`; exclude the featured post consistently on every page; drop or self-reference the canonical on noindexed search; add a 2–3 sentence intro and topic links (Guides / Formats / Web Performance). |
| Priority | P1 (soft 404 + pagination bug) |

### 4.11–4.18 Blog articles (`/blog/{slug}`)

Shared template findings (VERIFIED, `blog/show.blade.php`, `StructuredData::article`):

- H1 = `post.title`; excerpt shown as the deck; "By CompressPix · date · N min read".
- **No human author.** `author` is an Organization. No author page or bio.
- **`datePublished` is back-dated by the seeder** (`now()->subDays(published_days_ago)`); `dateModified` = the seed run time (e.g. published 2026-07-26, modified 2026-09-16 12:43). The visible date shows only published. On a fresh production seed, all dates shift again.
- **No `featured_image`** on any post → BlogPosting `image` falls back to the generic 1200×630 brand OG image for all 8 posts; `og:image` is identical sitewide.
- If a featured image is added: `<img … alt="">` (no alt), no `fetchpriority="high"`, and there's no alt column in the DB.
- No table of contents, although posts have 7–19 H2/H3s (`jpg-vs-png-vs-webp`: 14 H2, 19 H3).
- Markdown internal links are root-relative (`/compress-webp`). They work if the app is served at the domain root.
- Right sidebar "Try it yourself" links 2–3 related tools; "Keep reading" shows 3 related posts (same category first).
- Content is stripped of raw HTML on seed (`html_input => strip`), so it's safe.

| Post | Title (meta) / chars | Desc chars | Intent | Key assessment | Recommended change | Priority |
|---|---|---|---|---|---|---|
| how-to-compress-image-without-losing-quality (featured) | "How to Compress an Image Without Losing Quality" / 64 w/ brand | 159 | Informational, high volume | Strong workflow, table, checklist. Needs visual before/after examples to prove the claim. | Add 3–4 original comparison figures + captions; add a "short answer" summary box at top. | P1 |
| how-to-compress-jpg-images-online | "How to Compress JPG Images Online" / 50 | 155 | Informational, but the phrase is transactional | **Competes with `/compress-jpg`** (title ≈ tool H1). | Retitle: "JPG Compression Settings: Best Quality Levels and Mistakes to Avoid"; slug may stay; link prominently to `/compress-jpg`. | P1 |
| how-to-compress-png-images | "How to Compress PNG Images (Keep Transparency)" / 63 | 158 | Informational | Overlaps `/compress-png` content. | Retitle toward the explainer: "PNG Compression Explained: Palette vs Lossless, and When to Convert". | P2 |
| how-to-reduce-image-size-for-website | "How to Reduce Image Size for a Website" / 55 | 157 | Informational (developers/site owners) | Best pillar candidate (srcset, lazy load, width/height). Code examples present. | Make it the pillar of an "Image optimization for websites" cluster; add a diagram; link to future WordPress/Shopify guides. | P2 |
| how-to-compress-image-to-100kb | "How to Compress an Image to 100KB" / 50 | 156 | Mixed; SERP is tool-dominated | Overlaps the 100KB tool. Good unique content: signatures, scans, dimension table. | Retitle: "How to Get a Photo, Signature or Scan Under 100KB for Online Forms"; emphasise the form use case; tool page keeps the head term. | P1 |
| jpg-vs-png-vs-webp | "JPG vs PNG vs WebP: Which Format to Use?" / 54 | 157 | Comparison; high GEO value | Excellent comparison table and scenarios. | Add an AVIF column note or link; add a visual comparison; keep. | P2 |
| how-image-compression-works | "How Image Compression Works: JPEG, PNG, WebP" / 58 | 159 | Explainer; high GEO value | Deep technical content (DCT, DEFLATE, VP8/VP8L). | Add diagrams (8×8 block, chroma subsampling, PNG filter), which are ideal for Google Images; add a glossary anchor list. | P2 |
| best-image-formats-for-websites | "Best Image Formats for Websites" / 45 | 158 | Comparison | Partial overlap with jpg-vs-png-vs-webp. Differentiated by AVIF/SVG and use-case table. | Keep; make it explicitly "for websites (incl. AVIF & SVG)"; cross-link both ways in the intro. | P3 |

---

### 4.19 `/faq`

| Aspect | Assessment |
|---|---|
| Title | `Image Compression FAQ \| CompressPix` (35). Short, good. |
| H1 | "Frequently Asked Questions". **Generic; doesn't match the title.** Recommend "Image Compression FAQ". |
| Description | 127. |
| Content | 22 Q&As in 5 groups with anchor nav (`#basics`, `#formats`…). About 2,700 rendered words. Several answers duplicate tool-page FAQs almost verbatim (privacy/upload questions appear on 6+ pages). |
| Schema | FAQPage with all 22 questions. Valid; no rich result in 2026 (§7). |
| Actions | H1 fix; keep the canonical Q&A text here and vary wording on tool pages to page-specific context. |
| Priority | P3 |

### 4.20 `/about-us`

| Aspect | Assessment |
|---|---|
| Title / H1 | "About Us \| CompressPix" (22) / "About CompressPix". |
| Content | About 950 words about purpose, usability and privacy. **No mention of who operates the site, company, location, founding date, team, expertise, or how the compression engine was built or tested.** |
| Links (VERIFIED) | Only **1 body link** into About across the whole site (footer and sitemap otherwise). |
| Schema | BreadcrumbList only. Should be `AboutPage` + Organization. |
| Actions | See §21. Add operator identity, a methodology section (browser encoders, palette quantization, GD fallback, test procedure), a contact method, and Organization JSON-LD. Don't invent credentials. |
| Priority | **P0** (trust) |

### 4.21 `/contact-us`

| Aspect | Assessment |
|---|---|
| Title / description | "Contact Us \| CompressPix" (24) / **82 chars (short)**. |
| Content | Form with honeypot, aside with email **`hello@example.com` (placeholder, from `SITE_CONTACT_EMAIL`)**. |
| Schema | BC only. Recommend `ContactPage`, plus a `contactPoint` on the Organization. |
| Priority | **P0** (placeholder email) |

### 4.22–4.25 Legal pages

| URL | Title | Desc | Findings (VERIFIED) | Priority |
|---|---|---|---|---|
| `/privacy-policy` | Privacy Policy (28) | 140 | Placeholders **`[Company Name]`** (via `SITE_COMPANY_NAME`), **`[Hosting Provider]`**, **`[Retention Period]`**, **`[Log Retention Period]`**. TOC present. "Last updated 2026-09-16" hardcoded in view. | P0 |
| `/terms-and-conditions` | Terms & Conditions (32) | 86 | **`[Jurisdiction]`** ×2, `[Company Name]`. | P0 |
| `/cookie-policy` | Cookie Policy (27) | 98 | Accurate, conditional on GA4. OK. | P4 |
| `/disclaimer` | Disclaimer (24) | 113 | OK. | P4 |

Indexation: keep indexable (they're trust pages), low sitemap priority. No SEO copy changes needed beyond filling in placeholders.

### 4.26 `/sitemap` (HTML)

- Lists all tools, company, legal and posts (54 links). Useful as a crawl aid for a small site. Search value is negligible.
- RECOMMENDATION: keep it, but either `noindex, follow` it and remove it from the XML sitemap, or leave it as is (low impact). P4.

### 4.27 Utility and error routes

- **`/up`** (VERIFIED): 200, framework HTML page, loads `cdn.jsdelivr.net/npm/@tailwindcss/browser@4` and Bunny fonts, no robots meta, no `X-Robots-Tag`. Not linked, but discoverable via logs, uptime tools or referrers. → Add `Disallow: /up` and an `X-Robots-Tag: noindex` header, or move the health path behind a non-public path. P3.
- **Errors** (VERIFIED): `/nope`, `/blog/nope`, `/Compress-JPG`, `/index.php/compress-jpg` → 404 with `noindex, follow`, no canonical, helpful CTAs. Correct.
- **`/compress-jpg/`**: the Laravel router returns 200 (trailing slash ignored). On Apache, `public/.htaccess` 301s to the non-slash URL (VERIFIED rule). On Nginx/Caddy/Octane no redirect exists. The canonical protects against duplication either way. **EXTERNAL DATA:** confirm the production web server.

---

## 5. Technical SEO Findings

Format: **file → component → issue → impact → fix**.

### CRITICAL

**C1. Placeholder company/legal data will ship** — VERIFIED
- Where: `.env` / `.env.example` (`SITE_COMPANY_NAME="[Company Name]"`, `SITE_CONTACT_EMAIL="hello@example.com"`, `MAIL_FROM_ADDRESS="noreply@example.com"`); `resources/views/pages/legal/privacy-policy.blade.php` (`[Hosting Provider]`, `[Retention Period]`, `[Log Retention Period]`); `terms-and-conditions.blade.php` (`[Jurisdiction]`).
- Impact: Severe trust/E-E-A-T signal. Quality raters and users check who runs a site. LLMs may quote the placeholders.
- Fix: Fill in real values. Add a boot-time guard (e.g. in `AppServiceProvider::boot` when `app()->isProduction()`) that throws or logs if `site.company_name`/`site.contact_email` contain `[` or `example.com`. Add a feature test that asserts no `[` placeholder patterns are rendered on legal pages.

**C2. Production host and indexability depend on unverified env values** — VERIFIED risk / EXTERNAL DATA
- Where: `config/site.php` (`'indexable' => (bool) env('SITE_INDEXABLE', env('APP_ENV') === 'production')`); `.env.example` has **`SITE_INDEXABLE=true`**; local `.env` has `APP_URL=http://127.0.0.1:8765`; `AppServiceProvider::boot` forces https only when `APP_URL` starts with `https://`.
- Impact:
  - Wrong `APP_URL` in production → every canonical, `og:url`, breadcrumb `item`, JSON-LD URL and sitemap `<loc>` points at the wrong host. That's a sitewide canonicalization failure.
  - `.env.example` defaulting to `true` → a staging or preview environment built from the example is indexable.
- Fix:
  1. Set `.env.example` to `SITE_INDEXABLE=false`.
  2. In production, assert `APP_URL` is the final `https://` canonical host (with or without www; pick one).
  3. Add a deploy checklist item and a test that `route('home')` starts with `config('app.url')`.
  4. Protect staging with HTTP auth rather than robots rules.

**C3. Homepage vs `/image-compressor` cannibalization** — VERIFIED
- Where: `resources/content/home.php` title vs `resources/content/tools/image-compressor.php` title; `HomeController` also emits `StructuredData::webApplication($tools->get('image-compressor'))`; header CTA, nav, breadcrumbs and 404 CTA all point to `/image-compressor`.
- Impact: Two URLs split relevance, links and CTR for the head term "image compressor / compress image online". Google may alternate between them.
- Fix: see §14 #1.

### HIGH

**H1. Blog pagination soft 404** — VERIFIED
- `app/Http/Controllers/BlogController.php@index`: `/blog?page=99` → 200, indexable, self-canonical, empty state.
- Fix: after `paginate()`, `if ($page > 1 && $posts->isEmpty()) abort(404);` (or `$page > $posts->lastPage()`).

**H2. Blog pagination offset bug with featured post** — VERIFIED by code
- `BlogController@index`: `$featured` is only resolved when `$page === 1`, so `whereKeyNot($featured)` isn't applied on later pages.
- Impact: duplicate or missing posts across paginated pages once there are more than 10 posts → duplicate internal listings and a missed crawl path.
- Fix: resolve the featured post ID whenever `$search === ''`, exclude it on every page, and render the card only on page 1.

**H3. Back-dated/unstable article dates** — VERIFIED
- `database/seeders/PostSeeder.php`: `$post->published_at ??= now()->subDays($data['published_days_ago'])`. `dateModified` = `updated_at` = seed time.
- Impact: Google's byline-date guidance asks for accurate dates. Dates depend on deploy day, not the real publication date, and `dateModified` doesn't reflect real content edits.
- Fix: store explicit ISO dates in `database/seeders/data/posts.php` (`published_at`, `content_updated_at`). Add a `content_updated_at` column used for `dateModified`, sitemap `lastmod` and a visible "Updated" date.

**H4. No Organization entity / no `@id` graph** — VERIFIED
- `app/Support/Seo/StructuredData.php`: WebSite has no `publisher`; WebApplication `provider` and BlogPosting `author`/`publisher` are anonymous inline Organizations; no `logo` (except the 180px apple-touch icon in BlogPosting), no `sameAs`, no `contactPoint`.
- Fix: see §7.2 example.

**H5. Zero content images / generic OG image everywhere** — VERIFIED (§9)

**H6. Robots meta lacks `max-image-preview:large`** — VERIFIED
- `app/Support/Seo/Seo.php` default `robots = 'index, follow'`.
- Impact: Google Discover and some image previews are limited to small thumbnails without `max-image-preview:large`.
- Fix: default to `'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1'`; keep `noindex()` as is.

**H7. Weakly linked size pages** — VERIFIED (`/compress-image-to-1mb` 3 inbound, `-500kb` 6, `-200kb` 7) (§15)

### MEDIUM

**M1. `/up` health endpoint crawlable** (§4.27). Fix: `Disallow: /up` in `SeoController@robots` plus an `X-Robots-Tag` header, or change `health:` path.

**M2. Non-indexable mode blocks crawling *and* sets noindex** — VERIFIED (`SeoController@robots` → `Disallow: /`; `meta.blade.php` → `noindex, nofollow`)
- Impact: Google can't see the noindex on disallowed URLs. Linked staging URLs can still be indexed URL-only.
- Fix: for non-production, prefer HTTP auth. If you rely on noindex, send a global `X-Robots-Tag: noindex, nofollow` header from middleware and don't disallow in robots.txt.

**M3. Sitemap `lastmod` missing on 18 of 26 URLs; `priority`/`changefreq` ignored by Google** — VERIFIED (`SeoController@sitemap`)
- Fix: compute `lastmod` for tools from `filemtime()` of `resources/content/tools/{key}.php` and `resources/views/tools/content/{key}.blade.php` (max). Blog index = latest post date. Static pages = a hardcoded "last updated" constant shared with the view (legal pages already show `updated="2026-09-16"`). `priority`/`changefreq` can be removed.

**M4. WebApplication `name` uses the H1 instead of a product name** — VERIFIED (`StructuredData::webApplication` → `'name' => $tool->h1`)
- Fix: `'name' => config('site.brand').' '.$tool->name` (e.g. "CompressPix JPG Compressor"); add `@id` `{url}#app`, `featureList`, `publisher` → `#organization`.

**M5. Blog search: noindex + canonical to a different URL** — VERIFIED
- Fix: when `$search !== ''`, set `$seo->canonical = null` (or the current URL). noindex is enough.

**M6. Title length** — `/image-compressor` title is 68 chars (VERIFIED). Truncation risk. Fix per §4.2.

**M7. Every page opens a DB-backed session and sets cookies** — VERIFIED (`SESSION_DRIVER=database` in `.env`; `web` middleware group; `csrf-token` meta on all pages)
- Impact: Every crawler and visitor hit writes a session row (TTFB, DB load), and `Set-Cookie` on every HTML response prevents CDN/edge HTML caching.
- Fix: use `SESSION_DRIVER=cookie` or `redis`; consider a stateless middleware group for purely static pages (legal, blog, FAQ) and cache full HTML at the edge. **EXTERNAL DATA:** measure TTFB in production.

**M8. Footer column headings are `<h2>` on every page** — VERIFIED (`components/site/footer.blade.php`)
- Impact: Adds 4 non-topical H2s to every page outline (minor for ranking, noisy for accessibility tooling and LLM outline extraction).
- Fix: use `<p class="text-sm font-semibold text-ink">` or an `aria-labelledby` nav with non-heading labels.

**M9. FAQ H1 mismatch** (§4.19).

**M10. No `featured_image_alt`, author, OG image or robots columns on `posts`** — VERIFIED (`2026_09_16_114001_create_posts_table.php`) (§9, §28 notes in §7)

**M11. No redirect mechanism for changed slugs** — VERIFIED (no redirects table/middleware)
- Impact: Renaming posts (recommended in §14) or consolidating `/image-compressor` needs 301s.
- Fix: add explicit `Route::permanentRedirect()` entries, or a `redirects` table plus a fallback middleware.

### LOW

- **L1.** `og:image` has no `og:image:width/height/alt/type`; no `twitter:image:alt`; `twitter:site` empty (`SITE_TWITTER_HANDLE` unset). `meta.blade.php`.
- **L2.** `og:locale` hardcoded `en_US` while copy mixes British ("colour", "optimisation") and American ("optimization") spelling. Pick one locale and spelling standard for terminology consistency.
- **L3.** Five `<img>` tags without `src` in the widget markup (`image-preview`, `result` components; VERIFIED count = 5 on `/compress-jpg`). Invalid HTML; harmless for ranking. Fix: add `hidden` or create the elements in JS.
- **L4.** `site.webmanifest` `display: browser`, no 192/512 PNG icons (only 180px PNG + SVG). Irrelevant to ranking; a 512px PNG is also useful as the Organization logo.
- **L5.** Security headers lack HSTS and CSP. HSTS supports canonical https consolidation. **EXTERNAL DATA:** may be set at the server/CDN.
- **L6.** `public/.htaccess` has no caching/compression directives (may be handled by the server/CDN; EXTERNAL DATA).

### INFORMATIONAL

- **I1.** `Disallow: /admin` references a route that doesn't exist (test confirms 404). Harmless.
- **I2.** `storage/{path}` GET/PUT routes exist because `config/filesystems.php` local disk has `'serve' => true`. Not linked, not in sitemap. Laravel signs these URLs, so no SEO risk. Verify that's intended.
- **I3.** Uppercase URLs 404 instead of 301 to lowercase. Acceptable; could add a lowercase redirect middleware if external links use caps.
- **I4.** HTML weight: tool pages are ~66–74 KB raw / ~12.4 KB gzip. 41 inline SVGs (14 KB) and 23 KB of class attributes. Acceptable.
- **I5.** Legal "Last updated" date is hardcoded per view. Fine, but must be edited manually.

---

## 6. Metadata Audit

### 6.1 Titles (VERIFIED, rendered)

| URL | Current title | Len | Verdict | Recommended |
|---|---|---|---|---|
| `/` | Image Compressor: Compress Images Online Free \| CompressPix | 59 | Good, but duplicates intent | `Image Compressor – Compress JPG, PNG & WebP Online Free \| CompressPix` (if consolidated) |
| `/image-compressor` | Image Compressor: Compress JPG, PNG & WebP Online Free \| CompressPix | 68 | Too long; cannibalizes | 301 to `/` (preferred), or `Online Image Compressor – JPG, PNG & WebP \| CompressPix` |
| `/compress-jpg` | Compress JPG Online: Free JPG Compressor \| CompressPix | 54 | Good | `Compress JPG/JPEG Online – Free JPG Compressor \| CompressPix` (59) |
| `/compress-png` | Compress PNG Online: Reduce PNG File Size Free \| CompressPix | 60 | Good | keep |
| `/compress-webp` | Compress WebP Online: Free WebP Compressor \| CompressPix | 56 | Good | keep |
| `/compress-image-to-100kb` | Compress Image to 100KB Online Free \| CompressPix | 49 | Good | `Compress Image to 100KB (JPG/JPEG) Online Free \| CompressPix` |
| `/compress-image-to-200kb` | Compress Image to 200KB Online Free \| CompressPix | 49 | Good | keep |
| `/compress-image-to-500kb` | Compress Image to 500KB Online Free \| CompressPix | 49 | Good | keep |
| `/compress-image-to-1mb` | Compress Image to 1MB Online Free \| CompressPix | 47 | Good | keep |
| `/blog` | Image Compression Blog: Guides, Formats and Tips \| CompressPix | 62 | OK | `Image Compression & Optimization Guides \| CompressPix` |
| `/faq` | Image Compression FAQ \| CompressPix | 35 | Good | keep; align H1 |
| `/about-us` | About Us \| CompressPix | 22 | Weak | `About CompressPix – Who Builds This Free Image Compressor` (after adding identity) |
| `/contact-us` | Contact Us \| CompressPix | 24 | OK | `Contact CompressPix` |
| Legal (4) | {Page} \| CompressPix | 24–32 | Fine | keep |
| `/sitemap` | Sitemap \| CompressPix | 21 | Fine | keep (or noindex) |
| Blog posts | see §4.11 table | 45–64 | 2 cannibalize | see §14 |

No duplicate titles across indexable URLs (VERIFIED). Paginated `/blog?page=N` shares the `/blog` title. RECOMMENDATION: append ` – Page N` for N>1.

### 6.2 Meta descriptions

All pages have unique descriptions (VERIFIED). Short ones to improve:

| URL | Current (chars) | Recommended |
|---|---|---|
| `/contact-us` | 82 | "Contact the CompressPix team about the free online image compressor: report a bug, suggest a feature or ask about privacy. We reply by email." |
| `/sitemap` | 78 | "Browse every CompressPix page: image compression tools for JPG, PNG, WebP and target sizes, guides, FAQ and policies." |
| `/terms-and-conditions` | 86 | "The terms for using CompressPix's free online image compression tools, including acceptable use, your content and limitations of liability." |
| `/cookie-policy` | 98 | OK; optionally name the cookies. |
| `/blog` | 118 | "Practical, tested guides to compressing images: JPG, PNG and WebP settings, target file sizes like 100KB, and faster-loading website images." |

### 6.3 Canonicals — VERIFIED

- Set explicitly in every controller (`$seo->canonical = route(...)`). `Seo::make()` defaults to `url()->current()` (query string stripped).
- Tracking parameters (`/?ref=abc`, `/blog?utm_source=x`) → canonical to the clean URL ✓.
- Pagination → self-canonical ✓. Search → `/blog` (see M5). Errors → none ✓.
- Canonical host/scheme = `APP_URL` (see C2). No www/non-www or http→https enforcement in the app. **EXTERNAL DATA:** server-level redirects.

### 6.4 Robots — VERIFIED

| Context | Output |
|---|---|
| Indexable env, normal page | `index, follow` |
| Blog search | `noindex, follow` |
| Error pages | `noindex, follow` |
| `SITE_INDEXABLE=false` | `noindex, nofollow` on all pages + robots.txt `Disallow: /` |
| `/up`, `/sitemap.xml`, `/robots.txt`, manifest | no robots control |

### 6.5 Open Graph — VERIFIED

`og:site_name`, `og:type` (website/article), `og:title` (unbranded title), `og:description`, `og:url` (= canonical), `og:image` (always `images/brand/og-image.png`, 1200×630 PNG, 38.9 KB), `og:locale en_US`, and `article:published_time`/`modified_time` on posts.
Missing: `og:image:width`, `og:image:height`, `og:image:alt`, `og:image:type`; per-page images; `article:section`.

### 6.6 Twitter/X — VERIFIED

`summary_large_image`, title, description, image. `twitter:site` only if `SITE_TWITTER_HANDLE` is set (currently empty). Missing `twitter:image:alt`.

---

## 7. Structured Data Audit

### 7.1 Existing schema (VERIFIED from rendered JSON-LD)

| Page(s) | Types | Assessment |
|---|---|---|
| `/` | WebSite, WebApplication, FAQPage | WebSite lacks `publisher`/`@id`. WebApplication `url` = `/image-compressor` (entity mismatch with the host page). FAQPage valid, visible parity ✓. |
| 8 tool pages | BreadcrumbList, WebApplication, FAQPage | Valid. `name` = H1 (not a product name). `offers` price 0 USD ✓, `isAccessibleForFree` ✓. No aggregateRating (correct; none exists). |
| Blog posts | BreadcrumbList, BlogPosting | `author` = Organization; `image` = generic brand image; `publisher.logo` = 180×180 apple-touch icon; `dateModified` = seed time; `wordCount` ✓; `articleSection` ✓. |
| Blog index, About, Contact, FAQ, legal, HTML sitemap | BreadcrumbList (+ FAQPage on /faq) | Page-type schema (CollectionPage/AboutPage/ContactPage) missing. |
| Errors | none | Correct. |

Breadcrumb trails (VERIFIED): Home › Image Compressor › {Tool}; Home › Blog › {Post}; Home › {Page}. Visible breadcrumbs match JSON-LD ✓ (the homepage has none, which is correct).

### 7.2 Status of rich results in 2026 (important context)

- **FAQ rich results:** Google restricted them to authoritative government/health sites in Aug 2023 and removed them from Search on May 7, 2026. FAQPage remains valid Schema.org and is harmless. Its value now is semantic clarity only. RECOMMENDATION: keep the visible FAQs (they're useful to users and answer engines); keeping or removing the markup is low priority. Don't add more FAQ markup expecting SERP features.
- **HowTo rich results** were deprecated in 2023. Don't add HowTo for SERP features.
- **Sitelinks search box** was retired in Nov 2024. Don't add `SearchAction` for that purpose.
- **Software App rich results** need `aggregateRating` or `review`. CompressPix has no genuine reviews → **don't fabricate**; accept no star rich result.
- **Article** (no required properties; recommended: `headline`, `image` in multiple aspect ratios ≥ 50K pixels, `author` with name + url, `datePublished`, `dateModified`) and **Breadcrumb** remain supported.
- **Organization** markup (logo, url, sameAs, contactPoint) is supported and informs knowledge-panel/logo understanding.

### 7.3 Invalid / weak / conflicting schema

| # | Issue | File | Fix |
|---|---|---|---|
| S1 | No Organization node; anonymous inline orgs repeated | `StructuredData.php` | Single `Organization` with `@id` `{home}#organization` |
| S2 | No `@id`s → entities can't be joined | `StructuredData.php` | Emit one `@graph` per page |
| S3 | WebApplication on `/` describes `/image-compressor` | `HomeController` | After consolidation, WA `url` = home |
| S4 | WebApplication `name` = marketing H1 | `webApplication()` | `"{brand} {tool name}"` |
| S5 | BlogPosting `author` = Organization, no person | `article()` | Use a real Person (name, url to author page) when one exists; otherwise keep the Organization but link via `@id` |
| S6 | BlogPosting `image` = generic sitewide image | `article()` | Real per-post images (16:9, 4:3, 1:1; ≥1200px wide) |
| S7 | `dateModified` = seed timestamp | Post model/seeder | `content_updated_at` |
| S8 | Publisher logo = 180px icon | `article()` | Organization logo ≥112px square PNG (recommend 512×512) |
| S9 | No WebPage / CollectionPage / AboutPage / ContactPage | controllers | Add a `WebPage` node per page (`isPartOf` WebSite, `breadcrumb` @id, `primaryImageOfPage` where an image exists) |

### 7.4 Recommended JSON-LD architecture

Replace multiple `<script>` blocks with **one `@graph`** built by `Seo`/`StructuredData`. Example for a tool page (values from config; `sameAs` **only** if real profiles exist, since `config('site.social')` is currently empty):

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "Organization",
      "@id": "https://compresspix.example/#organization",
      "name": "CompressPix",
      "legalName": "<real company name — currently [Company Name]>",
      "url": "https://compresspix.example/",
      "logo": { "@type": "ImageObject", "url": "https://compresspix.example/images/brand/logo-512.png", "width": 512, "height": 512 },
      "email": "<real contact email>",
      "contactPoint": { "@type": "ContactPoint", "contactType": "customer support", "email": "<real contact email>", "url": "https://compresspix.example/contact-us" },
      "sameAs": []
    },
    {
      "@type": "WebSite",
      "@id": "https://compresspix.example/#website",
      "url": "https://compresspix.example/",
      "name": "CompressPix",
      "inLanguage": "en",
      "publisher": { "@id": "https://compresspix.example/#organization" }
    },
    {
      "@type": "WebPage",
      "@id": "https://compresspix.example/compress-jpg#webpage",
      "url": "https://compresspix.example/compress-jpg",
      "name": "Compress JPG Online: Free JPG Compressor",
      "description": "Compress JPG and JPEG images online for free…",
      "isPartOf": { "@id": "https://compresspix.example/#website" },
      "breadcrumb": { "@id": "https://compresspix.example/compress-jpg#breadcrumb" },
      "mainEntity": { "@id": "https://compresspix.example/compress-jpg#app" },
      "inLanguage": "en"
    },
    {
      "@type": "WebApplication",
      "@id": "https://compresspix.example/compress-jpg#app",
      "name": "CompressPix JPG Compressor",
      "url": "https://compresspix.example/compress-jpg",
      "applicationCategory": "MultimediaApplication",
      "operatingSystem": "Any",
      "browserRequirements": "Requires JavaScript and a modern web browser.",
      "isAccessibleForFree": true,
      "featureList": ["Adjustable quality 10–100%", "Target file size", "Before/after comparison", "In-browser JPEG encoding", "EXIF metadata removal"],
      "offers": { "@type": "Offer", "price": "0", "priceCurrency": "USD" },
      "publisher": { "@id": "https://compresspix.example/#organization" }
    },
    {
      "@type": "BreadcrumbList",
      "@id": "https://compresspix.example/compress-jpg#breadcrumb",
      "itemListElement": [
        { "@type": "ListItem", "position": 1, "name": "Home", "item": "https://compresspix.example/" },
        { "@type": "ListItem", "position": 2, "name": "JPG Compressor", "item": "https://compresspix.example/compress-jpg" }
      ]
    }
  ]
}
```

BlogPosting node (in the same graph pattern):

```json
{
  "@type": "BlogPosting",
  "@id": "https://compresspix.example/blog/jpg-vs-png-vs-webp#article",
  "isPartOf": { "@id": "https://compresspix.example/blog/jpg-vs-png-vs-webp#webpage" },
  "mainEntityOfPage": { "@id": "https://compresspix.example/blog/jpg-vs-png-vs-webp#webpage" },
  "headline": "JPG vs PNG vs WebP: Which Format Should You Use?",
  "description": "…",
  "image": [
    "https://compresspix.example/images/blog/jpg-vs-png-vs-webp-comparison-16x9.webp",
    "https://compresspix.example/images/blog/jpg-vs-png-vs-webp-comparison-4x3.webp",
    "https://compresspix.example/images/blog/jpg-vs-png-vs-webp-comparison-1x1.webp"
  ],
  "datePublished": "<real first-publication date>",
  "dateModified": "<real last substantive edit>",
  "author": { "@type": "Person", "name": "<real author>", "url": "https://compresspix.example/authors/<slug>" },
  "publisher": { "@id": "https://compresspix.example/#organization" },
  "articleSection": "Formats",
  "wordCount": 1109,
  "inLanguage": "en"
}
```

**Implementation note:** `Seo::withSchema()` currently appends separate top-level arrays, and `meta.blade.php` loops `@foreach ($seo->schema …)` to print one script per item. Change it to collect nodes without `@context` and print a single `{"@context":"https://schema.org","@graph":[…]}`. Keep `JSON_HEX_TAG` escaping (already present ✓).

---

## 8. Internal Linking Architecture

### 8.1 Current state (VERIFIED: internal inbound link counts from the rendered HTML of all 26 sitemap URLs; "body" = links inside `<main>`, absolute links only, so markdown root-relative links in posts are undercounted)

| Target | Total inbound pages | Inbound from `<main>` |
|---|---|---|
| `/` | 25 | 25 |
| `/image-compressor` | 25 | 21 |
| `/compress-jpg`, `/compress-png`, `/compress-webp` | 25 each | 13–14 |
| `/compress-image-to-100kb` | 25 (footer) | 6 |
| `/compress-image-to-200kb` | **7** | 7 |
| `/compress-image-to-500kb` | **6** | 6 |
| `/compress-image-to-1mb` | **3** | 3 |
| `/blog` | 25 | 19 |
| `/faq` | 25 | 12 |
| `/about-us` | 25 (footer) | **1** |
| `/blog/best-image-formats-for-websites` | 13 | 13 |
| `/blog/how-to-compress-image-without-losing-quality` | 12 | 12 |
| `/blog/how-to-compress-image-to-100kb` | 9 | 9 |
| `/blog/jpg-vs-png-vs-webp` | 8 | 8 |
| `/blog/how-to-reduce-image-size-for-website`, `…png-images`, `…jpg-images-online`, `how-image-compression-works` | 5–6 | 5–6 |

Mechanisms (VERIFIED): header nav (4 tools + Blog), header CTA → `/image-compressor`, footer (5 tools, company, resources, legal), breadcrumbs, tool `related_tools` (4 each), tool `related_posts` (≤3, `Post::relatedToTool`), blog sidebar related tools, "Keep reading" posts (same category first), contextual links inside tool content partials and Markdown.

### 8.2 Recommended architecture

```
                              ┌─────────────────────────────┐
                              │ /  (Image Compressor hub)   │
                              └──────────────┬──────────────┘
          ┌──────────────────────────────────┼───────────────────────────────────┐
   Format tools                        Size-target tools                     Content hubs
   /compress-jpg ◄──► /compress-png   /compress-image-to-50kb (new)          /blog (pillar list)
        ▲   ▲            ▲   ▲        /…-100kb ◄─► /…-200kb ◄─► /…-500kb     /faq
        │   └── /compress-webp ─┘      ◄─► /…-1mb ◄─► /…-2mb (new)             /about-us (entity)
        │                                  ▲
   Convert tools (new):                    │
   /png-to-jpg /jpg-to-webp /png-to-webp /webp-to-jpg
        ▲                                  ▲
        └─────────── Pillar guides ────────┘
   /blog/how-to-compress-image-without-losing-quality  → all format + size tools
   /blog/how-to-reduce-image-size-for-website          → /compress-webp, 200KB, best-formats
   /blog/jpg-vs-png-vs-webp  ⇄ /blog/best-image-formats-for-websites
   /blog/how-image-compression-works → /compress-jpg, /compress-png, /compress-webp
```

Rules:
1. **Every tool page gets ≥10 contextual inbound links.** Implement a "size ladder" component on every size page (50KB → 100KB → 200KB → 500KB → 1MB → 2MB) and a "format switcher" on every format page.
2. **Footer "Tools" column:** list all 8 tool pages (`config/tools.php` → `footer`). Currently 5.
3. **Home tool grid** (`HomeController` `relatedTools`): include all tools (currently 4).
4. **Blog → tool:** first mention of a tool-able task in each post links to the matching tool (mostly already done). Add a top-of-post "Tool for this guide" box linking the primary tool with exact-match anchor text.
5. **Tool → blog:** keep `related_posts`; add one contextual in-copy link per tool page to the most relevant guide (mostly done).
6. **About page:** link from the homepage intro ("Built by … — about CompressPix"), from blog author lines, and from the Contact page.
7. **Anchor text:** use descriptive anchors ("compress an image to 1MB") rather than "Open tool" / "Read article". Cards currently use generic visual labels, but the linked title text is descriptive ✓.

### 8.3 Specific links to add

| From | To | Anchor |
|---|---|---|
| `/` tool grid | `/compress-image-to-200kb`, `/…-500kb`, `/…-1mb` | tool names |
| `/compress-image-to-100kb` content ("Leave a small margin" section) | `/compress-image-to-500kb`, `/…-1mb` | "500KB", "1MB compressor" |
| `/compress-image-to-200kb` | `/compress-image-to-1mb` | "compress to 1MB" |
| `/blog/how-to-compress-image-without-losing-quality` | `/compress-image-to-500kb`, `/…-1mb` | "compress to 500KB while keeping detail" |
| `/blog/how-to-reduce-image-size-for-website` | `/compress-image-to-200kb`, `/compress-png` | "200KB target", "PNG compressor" |
| `/compress-jpg` | `/blog/how-to-compress-jpg-images-online` (after retitle) | "best JPG quality settings" |
| `/compress-png` | `/blog/how-image-compression-works#how-png-compression-works` | "how PNG filtering and DEFLATE work" (needs heading IDs in the Markdown render) |
| `/faq` each group | matching tool pages | already partially ✓ |
| `/blog/jpg-vs-png-vs-webp` intro | `/blog/best-image-formats-for-websites` | "including AVIF and SVG" |
| `/blog/best-image-formats-for-websites` intro | `/blog/jpg-vs-png-vs-webp` | "detailed JPG vs PNG vs WebP comparison" |

**Heading anchors:** the Markdown render (`Str::markdown`) doesn't add `id`s to H2/H3 → no deep links or TOC. RECOMMENDATION: enable CommonMark `HeadingPermalinkExtension` (or a slug-id pass) in `PostSeeder`, then render a TOC in `blog/show.blade.php`.

---

## 9. Image SEO Audit

### 9.1 Inventory (VERIFIED)

| Asset | Where | Format / size | alt | Notes |
|---|---|---|---|---|
| `images/brand/logo-mark.svg` | Header + footer logo | SVG 479 B, `width/height=32` | `alt=""` inside a link with `aria-label="CompressPix home"` | Correct accessibility |
| `images/brand/logo.svg` | Not rendered (config only) | SVG 659 B, contains `<text>` | — | Unused |
| `images/brand/og-image.png` | `og:image`, `twitter:image`, BlogPosting fallback | PNG 1200×630, 38.9 KB | none (no `og:image:alt`) | **Same on every URL** |
| `apple-touch-icon.png` | icon, manifest, BlogPosting publisher logo | PNG 180×180 | — | Too small for an ideal logo |
| `favicon.ico`, `favicon.svg` | icons | — | — | OK |
| Post `featured_image` | `blog/show`, `post-card`, blog featured card | **NULL for all 8 posts** | `alt=""` hardcoded | No alt field in DB |
| Compressor `<img>` × 5 | widget | no `src` in HTML (blob URLs at runtime) | "Preview of the selected image", "Original image", "Compressed image" | User content; correctly not indexable |
| Content images in tool pages / posts | — | **none** | — | **Largest gap** |

Other checks:
- `srcset`/`sizes`/`<picture>`: none anywhere (VERIFIED grep).
- `fetchpriority`/preload for images: none.
- `loading="lazy"` only on post cards ✓ (the featured blog card would be eager, which is correct above the fold).
- `width`/`height` present on all static `<img>` ✓. Post show image uses 1200×630 plus a CSS aspect ratio ✓.
- **No image sitemap.** No `ImageObject` beyond the publisher logo.

### 9.2 Assessment

The site is about images but gives Google Images, Discover and multimodal AI nothing to index. Competing compressor sites rarely publish good original examples, so this is a real, legitimate gap to exploit.

### 9.3 Recommendations

**RECOMMENDATION (P1): original instructional images**, built from images you own or have licensed:

| Page | Image idea | Filename example | Alt text example |
|---|---|---|---|
| `/compress-jpg` | Same photo at quality 90/75/50/25, cropped at 200% zoom, with byte sizes | `jpg-quality-comparison-90-75-50-25.webp` | "The same landscape photo saved as JPG at quality 90, 75, 50 and 25, showing file sizes and blocking artifacts at low quality" |
| `/compress-png` | Screenshot at 256/64/16 colours with sizes; banding example | `png-palette-reduction-256-64-16-colors.webp` | "PNG screenshot reduced to 256, 64 and 16 colours with resulting file sizes" |
| `/compress-webp` | JPG vs WebP at equal size, detail crop | `jpg-vs-webp-same-file-size-detail.webp` | "Detail crop comparing a JPG and a WebP of the same file size" |
| `/compress-image-to-100kb` | Portrait at 600×800 under 100KB; signature under 20KB | `passport-photo-under-100kb-example.webp` | "Passport-style portrait compressed to 96 KB at 600 × 800 pixels" |
| `/blog/how-image-compression-works` | Diagrams: chroma subsampling 4:2:0, 8×8 DCT blocks, PNG filter types | `jpeg-chroma-subsampling-420-diagram.svg` (+ PNG fallback for Images) | "Diagram of 4:2:0 chroma subsampling: full-resolution brightness, quarter-resolution colour" |
| `/blog/jpg-vs-png-vs-webp` | Side-by-side format grid for photo/screenshot/logo | `jpg-png-webp-comparison-photo-screenshot-logo.webp` | descriptive |
| Every post | Unique featured image (16:9 1200×675 min, plus 4:3 and 1:1 crops) | `{slug}.webp` | descriptive |

**Implementation (code-level):**
1. **DB:** migration adding `featured_image_alt` (string), `featured_image_width`/`height` (int), optionally `og_image` to `posts`.
2. **Markdown images:** allow Markdown `![alt](path)` in posts. `html_input => strip` keeps images since they aren't raw HTML ✓. Render them in `<figure>` with `<figcaption>` (CommonMark extension or post-processing).
3. **Responsive delivery:** generate AVIF + WebP + JPEG fallback at 640/960/1280/1920 widths at build time. Render `<picture>` with `srcset`/`sizes`, `width`/`height`, `loading="lazy"` below the fold, and `fetchpriority="high"` + no lazy on the post hero.
4. **Blade:** in `blog/show.blade.php` and `components/blog/post-card.blade.php`, use `alt="{{ $post->featured_image_alt }}"` for the article hero. Card thumbnails can stay `alt=""` when the title is adjacent (decorative duplicate).
5. **Per-page OG images:** `Seo::$image` exists but is only set for articles. Add `image` to the tool content files and pass it in `ToolController`. Emit `og:image:width/height/alt`.
6. **Image sitemap:** extend `seo/sitemap.blade.php` with `xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"` and `<image:image><image:loc>` per content image.
7. **Stable URLs:** serve content images from stable, descriptive paths (`/images/guides/…`). Don't use hashed Vite asset names for images meant to rank.
8. **Licensing:** state image ownership/licence on the About page or in figure credits. Optionally add IPTC `creator`/`copyrightNotice` and ImageObject `creditText`/`copyrightNotice`/`license` (Google supports licensable-image metadata).

---

## 10. GEO / AI Search Audit

> Caveat: Google AI Overviews/AI Mode, Gemini, ChatGPT search, Copilot and Perplexity don't publish ranking factors. The points below follow publicly documented guidance (Google: AI features use the same Search index and quality systems; no special markup is required; being indexable and snippet-eligible is the prerequisite) plus general information-retrieval principles. Nothing here is a guaranteed citation lever.

### 10.1 Prerequisites (VERIFIED)

| Requirement | Status |
|---|---|
| Content server-rendered in initial HTML | ✓ |
| Indexable, snippet-eligible (`max-snippet` not restricted) | ✓ (add explicit `max-snippet:-1`) |
| robots.txt allows all user agents (incl. Googlebot, Bingbot, OAI-SearchBot, PerplexityBot) | ✓ (`User-agent: *` / `Allow: /`) |
| Clear page purpose and headings | ✓ |
| Stable canonical URLs | ✓ (subject to C2) |
| Bing indexing (powers Copilot and contributes to other engines) | EXTERNAL DATA: Bing Webmaster Tools; IndexNow not implemented |

**Owner decision (not a defect):** the robots.txt doesn't distinguish AI crawlers. Allowing search-retrieval bots (e.g. `OAI-SearchBot`, `PerplexityBot`) supports citations. Training-only controls (e.g. `Google-Extended`, `GPTBot`) are a separate business choice and don't affect Google Search ranking.

### 10.2 Existing answer-candidate content (VERIFIED)

| Content | Page | Why it's extractable |
|---|---|---|
| "Is JPG the same as JPEG?" | `/compress-jpg` FAQ | Direct Q→A |
| 1 KB = 1,024 bytes; use a 95 KB margin | `/compress-image-to-100kb` FAQ | Specific, practical fact |
| Email attachments grow ~⅓ in transit | `/compress-image-to-1mb` | Specific fact |
| Palette colours by quality (256 at ≥90% → 16 at 10%) | `/compress-png`, FAQ | Product-specific fact |
| Format comparison table (compression type, transparency, animation, max dimensions) | `/blog/jpg-vs-png-vs-webp` | Table |
| Quality range table (90–100 / 75–85 / 55–70 / <50) | `/blog/how-to-compress-jpg-images-online` | Table |
| Dimensions that fit under 100KB | `/blog/how-to-compress-image-to-100kb` | Table |
| JPEG / PNG / WebP pipeline comparison | `/blog/how-image-compression-works` | Table |

### 10.3 Gaps and recommendations

1. **Answer-first summaries (RECOMMENDATION):** start every post and tool page with a 40–60 word direct answer before the detail. Example for `/compress-image-to-100kb`:
   > "To compress an image to 100KB, upload it, keep the 100 KB target and JPG output, and download the result. The tool lowers JPEG quality first and reduces pixel dimensions only if needed. Portraits around 600 × 800 px usually fit; use 95 KB if a form counts 1 KB as 1,000 bytes."
2. **Original, citable data (OPPORTUNITY, highest value):** publish a reproducible benchmark, e.g. "We compressed 50 public-domain photos and screenshots at quality 90/80/70/60 as JPG, WebP and palette PNG: median size reduction by format and quality." Include methodology, the dataset licence, a table, a downloadable CSV and a date. AI answer engines prefer citing sources with unique numbers.
3. **Entity clarity:** a 1–2 sentence definition of CompressPix on home and About ("CompressPix is a free, browser-based image compressor for JPG, PNG and WebP operated by <company>…"), consistent everywhere (§11).
4. **Terminology consistency:** standardize on "JPG (JPEG)", "KB" with a stated 1,024 definition, and one spelling locale ("colour" vs "color", "optimisation" vs "optimization" are currently mixed).
5. **Freshness signals:** visible "Updated {date}" on posts and tool pages, backed by real `content_updated_at` (H3).
6. **Comparison content:** the tool itself can't be compared with named competitors without real testing. Only publish "CompressPix vs X" pages if based on documented, fair tests; otherwise skip.
7. **Author/operator transparency:** see §21.
8. **Bing/Copilot:** submit the sitemap in Bing Webmaster Tools; optionally implement IndexNow pings on post publish/update (EXTERNAL DATA: account access).
9. **`llms.txt`:** optional, low cost, and **not confirmed** to be used by Google or major engines. Only add as a courtesy index of key pages; don't expect ranking impact.

---

## 11. LLM Understanding Audit

Could an LLM that crawled the site correctly answer these questions?

| Question | Answerable? | Evidence |
|---|---|---|
| What is this website? | **Yes** | Clear H1s, descriptions, About "What we do" |
| What does it offer? | **Yes** | 8 tool pages, feature grids, WebApplication schema |
| How does the tool work technically? | **Yes (strong)** | Privacy notes, PNG palette details, GD fallback in privacy policy |
| Who operates it? | **No** | `[Company Name]` placeholder; no people; no address; no Organization entity |
| How to contact them? | **Partially** | Contact form; email is `hello@example.com` placeholder |
| Is it trustworthy/authoritative? | **Weak** | No authorship, no founding info, no external profiles (`site.social` empty), no methodology/testing evidence |
| Relationships between pages? | **Mostly** | Breadcrumbs, related tools/posts. Entity graph missing (§7) |
| Which page is authoritative for "image compressor"? | **Ambiguous** | Home vs `/image-compressor` |
| Content hierarchy/categories? | **Partial** | Post categories exist as labels only (no category pages); tools have no "format vs size" grouping in schema |
| When was content published/updated? | **Unreliable** | Back-dated seeds; no visible updated date |

**Consistency of entity naming (VERIFIED):**
- Brand: "CompressPix" consistent in titles, `og:site_name`, logo text, footer ✓.
- Tool names vary: config `name` "JPG Compressor", nav "JPG Compressor", footer "Compress JPG", H1 "Compress JPG Images Online", WebApplication name "Compress JPG Images Online". RECOMMENDATION: one canonical entity name per tool (`name`) used in schema, cards and breadcrumbs; keyword variants in H1/title.

**RECOMMENDATIONS:**
1. Organization + WebSite + WebPage `@graph` (§7.4).
2. About page rewrite with operator identity, methodology and contact (§21).
3. A "How CompressPix works" technical page (or About section): client-side canvas encoding for JPG/WebP, custom indexed-PNG encoder, GD server fallback, limits (20 MB, 50 MP browser / 25 MP server), metadata removal. All of this is already true in the code and content.
4. Stable entity names per tool.
5. Resolve the home vs `/image-compressor` ambiguity.

---

## 12. Content Gap Analysis

Only gaps the **current product actually supports** are marked "Supported". Others depend on product work.

### 12.1 Tool / landing pages

| Opportunity | Supported by current tool? | Evidence | Intent | Priority |
|---|---|---|---|---|
| Compress image to **50KB** | **Yes** (preset `50 => '50 KB'` in `config/compressor.php`) | config | Transactional (signatures/forms) | P1 |
| Compress image to **20KB / 30KB** | **Yes** (custom target, min 5 KB) | `CompressImageRequest` `min:5` | Transactional (exam/job signature uploads) | P2 |
| Compress image to **2MB** | **Yes** (custom target, max 20,480 KB) | config | Transactional | P3 |
| **PNG to JPG** converter | **Yes** (output JPG) | `output_formats` | Transactional | P1 |
| **JPG to WebP** / **PNG to WebP** | **Yes** | output WebP | Transactional | P1 |
| **WebP to JPG** | **Yes** | output JPG | Transactional (compatibility) | P1 |
| Remove EXIF / GPS metadata from photos | **Yes** (re-encoding strips metadata, documented) | FAQ | Transactional/privacy | P2 |
| Resize image to exact pixel dimensions | **No** (only automatic downscale for targets) | engine | Transactional | Product-dependent |
| Batch/multiple compression | **No** (FAQ says one at a time) | faq.php | Transactional | Product-dependent |
| AVIF / HEIC / GIF input or output | **No** (`mimes:jpg,jpeg,png,webp`) | request rules | Transactional | Product-dependent |

**Doorway-page caution:** each new landing page must have unique, genuinely useful content (use-case specifics, format behaviour, presets, examples), like the existing size pages. Don't mass-generate "compress image to 37KB" variants.

### 12.2 Informational content (supporting the clusters)

| Topic | Cluster | Links to |
|---|---|---|
| How to compress a signature image to 20KB/50KB for online forms | Size targets | 50KB/20KB tools |
| KB vs KiB: why "100KB" means different byte counts on different sites | Size targets | all size tools |
| How to check an image's file size and dimensions (Windows, Mac, iPhone, Android) | Size targets | 100KB tool |
| Why is my photo "too large to upload"? Fixes for common form errors | Size targets | 100KB/200KB tools |
| What is EXIF data and how to remove location from photos before sharing | Privacy | EXIF page, compress-jpg |
| JPEG compression artifacts explained (blocking, ringing, banding) with examples | Formats | compress-jpg |
| What is AVIF? (and when WebP is enough) | Formats | best-formats post |
| Lossy vs lossless compression (standalone definition page) | Fundamentals | FAQ, how-compression-works |
| Image optimization for LCP / Core Web Vitals | Web performance | reduce-image-size pillar |
| How to optimize images for WordPress / Shopify (neutral, practical) | Web performance | webp tool |
| Compress photos for email attachments | Use cases | 1MB tool |

### 12.3 Hub/category pages

- Blog categories (`Guides`, `Formats`, `Web Performance`) exist as data only. With 8 posts, category archives would be thin. **Add them once each category has ≥5 posts,** with an intro paragraph and a curated order.
- **Tool hub:** after consolidation, the homepage is the tool hub. Otherwise add a `/tools` listing page.

---

## 13. Keyword / Intent Map

> Search volumes and difficulty: EXTERNAL DATA (not available in this audit). Mapping is based on topical relevance and observed SERP types.

| Page | Primary topic | Secondary / variants | Entities & concepts | Intent | Page type | Content angle | Cannibalization risk |
|---|---|---|---|---|---|---|---|
| `/` (post-consolidation) | image compressor | compress image online, reduce image size, free image compressor, photo compressor | JPG, PNG, WebP, quality, target size, EXIF, browser-based | Transactional | Tool hub | Private, in-browser, target size + comparison | High with `/image-compressor` (fix) |
| `/compress-jpg` | compress jpg | compress jpeg, jpg compressor, reduce jpg size | JPEG quality, DCT, chroma subsampling, EXIF | Transactional | Tool | Quality guidance + in-browser | With JPG blog post |
| `/compress-png` | compress png | png compressor, reduce png size, compress png keep transparency | palette, quantization, alpha, DEFLATE | Transactional | Tool | Honest palette explanation | With PNG blog post |
| `/compress-webp` | compress webp | webp compressor, reduce webp size | VP8, alpha, browser support | Transactional | Tool | Web-ready format | Convert intent (split out) |
| `/compress-image-to-100kb` | compress image to 100kb | compress jpeg to 100kb, reduce photo size to 100kb, photo under 100kb | forms, passport photo, KB=1024 | Transactional | Tool | Form-ready presets | With 100KB blog post |
| `/compress-image-to-200kb` | compress image to 200kb | reduce image to 200kb | listings, documents | Transactional | Tool | Middle-ground size | Low |
| `/compress-image-to-500kb` | compress image to 500kb | reduce photo to 500kb | portfolios, full-HD | Transactional | Tool | Quality-preserving | Low |
| `/compress-image-to-1mb` | compress image to 1mb | reduce image size to 1mb, photo under 1mb | email, attachments | Transactional | Tool | High-res under 1MB | Low |
| `/blog/how-to-compress-image-without-losing-quality` | compress image without losing quality | reduce image size without losing quality | lossless/lossy, dimensions, re-saving | Informational | Guide (pillar) | Workflow + checklist | Low |
| `/blog/how-to-compress-jpg-images-online` (retitle) | best jpg quality setting | jpeg quality 80 vs 90, jpg compression mistakes | quality ranges, re-saving | Informational | Guide | Settings deep dive | **High** until retitled |
| `/blog/how-to-compress-png-images` (retitle) | png compression explained | lossless vs lossy png, png too large | palette, DEFLATE, transparency | Informational | Explainer | When to convert | Medium |
| `/blog/how-to-reduce-image-size-for-website` | reduce image size for website | optimize images for web, srcset, lazy loading | LCP, CWV, `<picture>` | Informational | Pillar | Dev checklist | Low |
| `/blog/how-to-compress-image-to-100kb` (retitle) | photo/signature under 100kb for forms | signature size 100kb, scan under 100kb | portals, dimensions | Informational | Guide | Form troubleshooting | **High** until retitled |
| `/blog/jpg-vs-png-vs-webp` | jpg vs png vs webp | png vs jpg, webp vs jpg | comparison attributes | Commercial-investigation / informational | Comparison | Decision table | Medium with best-formats |
| `/blog/how-image-compression-works` | how image compression works | how jpeg compression works, dct | DCT, quantization, DEFLATE, VP8 | Informational | Explainer | Technical depth | Low |
| `/blog/best-image-formats-for-websites` | best image format for web | webp vs avif, svg for logos | AVIF, SVG, fallbacks | Informational | Guide | Use-case table | Medium with jpg-vs-png-vs-webp |
| `/faq` | image compression faq | — | all | Informational | FAQ | Consolidated answers | Low |
| `/about-us` | CompressPix (brand) | who makes CompressPix | Organization | Navigational | Entity | Identity + method | — |

---

## 14. Cannibalization Report

### #1 — `/` vs `/image-compressor` (CRITICAL)
- **Overlap:** "image compressor", "compress images online". Same widget and same WebApplication entity; near-identical titles ("Image Compressor: Compress Images Online Free" vs "Image Compressor: Compress JPG, PNG & WebP Online Free"); same benefit/how-to sections.
- **Likely problem:** Google picks one inconsistently. Link equity splits: the homepage gets external/brand links, while `/image-compressor` gets more internal CTA/nav links.
- **Option A (recommended, pre-launch):** make `/` the canonical image compressor.
  1. Merge the unique `tools/content/image-compressor` sections (format choice, size-target links) into `home.blade.php`.
  2. `Route::permanentRedirect('image-compressor', '/')`; remove the key from `config('tools.pages')` or map its `url()` to `route('home')`.
  3. Point header CTA, nav, 404 CTA, disclaimer/privacy links and `related_tools` to `/`.
  4. Tool breadcrumbs: `Home › JPG Compressor` (drop the middle level).
  5. WebApplication `url` = home.
- **Option B:** keep both but differentiate. Home = brand/tool hub (no widget, or a light one) targeting "free image compression tools"; `/image-compressor` = the tool targeting "image compressor". Move nav/CTA links consistently. Weaker, because the homepage usually wins head terms anyway.
- **Internal links:** all "Image Compressor" anchors → the single winner.

### #2 — `/compress-jpg` vs `/blog/how-to-compress-jpg-images-online` (HIGH)
- **Overlap:** "compress JPG (images) online". The blog title matches the tool H1 wording.
- **Fix:** retitle the post and its meta ("JPG Compression Settings: Best Quality Levels and Mistakes to Avoid"). Keep the slug or 301 to `/blog/jpg-compression-settings`. Its first paragraph links to `/compress-jpg` with the anchor "compress JPG online". The tool page links back with "best JPG quality settings".

### #3 — `/compress-png` vs `/blog/how-to-compress-png-images` (MEDIUM)
- **Fix:** position the post as the explainer ("PNG Compression Explained: Palette vs Lossless, and When to Convert"); trim step-by-step tool instructions in the post to a short pointer to the tool.

### #4 — `/compress-image-to-100kb` vs `/blog/how-to-compress-image-to-100kb` (HIGH)
- **Overlap:** the query "how to compress image to 100kb" returns a tool-dominated SERP (§16).
- **Fix:** tool keeps "compress image to 100KB". Post → "How to Get a Photo, Signature or Scan Under 100KB for Online Forms" (form troubleshooting angle). Consider 301 to a new slug `/blog/photo-signature-under-100kb-for-forms`.

### #5 — `/blog/jpg-vs-png-vs-webp` vs `/blog/best-image-formats-for-websites` (MEDIUM)
- **Overlap:** format choice.
- **Differentiation:** the first = general three-format comparison (any use, including forms/email/print); the second = websites only, including AVIF/SVG and `<picture>` fallbacks. Cross-link in both intros. Avoid repeating the same comparison table.

### #6 — `/blog/how-image-compression-works` vs `/compress-jpg` "How JPG compression works" section vs FAQ "basics" (LOW)
- **Fix:** keep the tool section short (already concise) and link to the post for depth ✓ (already linked).

### #7 — Repeated privacy/upload FAQ answers across home, 6 tool pages and `/faq` (LOW)
- Near-duplicate Q&A blocks. Not a penalty risk, but dilutes page uniqueness. Tailor each tool page's privacy answer to its format (already partly done for PNG/WebP), and keep the general one on `/faq`.

---

## 15. Orphan Page Report

No true orphans: every sitemap URL is linked at least from `/sitemap` (VERIFIED). Under-linked pages:

| Page | Inbound (VERIFIED) | Where to add links |
|---|---|---|
| `/compress-image-to-1mb` | 3 | Footer Tools column; home tool grid; size ladder component on all size pages; `/blog/how-to-compress-image-without-losing-quality`; `/compress-png` ("large screenshots… 1MB"); `/compress-webp` |
| `/compress-image-to-500kb` | 6 | Footer; home grid; size ladder; `/blog/how-to-reduce-image-size-for-website` (hero images); `/compress-jpg` |
| `/compress-image-to-200kb` | 7 | Footer; home grid; size ladder; `/compress-jpg`, `/compress-webp` |
| `/about-us` | 1 body link | Home intro/trust strip; blog byline ("By CompressPix" → About/author page); contact page aside; compressor privacy note |
| `/blog/how-to-reduce-image-size-for-website` | 5 | `/compress-webp` (✓), `/compress-image-to-200kb` (✓), `/compress-image-to-500kb`, home "Optimize images for websites" reason card, `/blog/how-to-compress-image-without-losing-quality` (✓) |
| `/blog/how-image-compression-works` | 6 | `/compress-png`, `/faq` basics group, `/blog/how-to-compress-png-images` (✓) |
| `/blog/how-to-compress-png-images` | 6 | `/compress-image-to-500kb` (PNG screenshot section), `/compress-image-to-1mb` (✓ related_posts) |
| `/blog?page=N` | 0 (only 1 page exists today) | n/a |

---

## 16. Competitor Opportunity Analysis

Light SERP observation (web search, US, September 2026). Backlink profiles, traffic and SERP features in detail: **EXTERNAL DATA**.

### Query: "compress image to 100kb online"
Observed ranking pages: Zamzar (`/tools/compress-jpg-100kb/`), 11zon (`compress-jpeg-to-100kb`), compressjpeg.dev, imagy.app (`resize-image-to-100kb`, "exact file size"), SmallSEOTools, Pi7 (`compress-image-to-100kb`), jpeg-optimizer.com, hicompress.com (two separate pages: JPEG-to-100KB and image-to-100KB).

Observations:
- The SERP is dominated by **dedicated size-target tool pages**; blog guides are rare → confirms the tool page should own this query (§14 #4).
- Many competitors use **"JPEG" in the title/URL** ("Compress JPEG to 100KB"). Adding a JPG/JPEG synonym to the title/H1 is legitimate synonym coverage.
- Some promise "exact" sizes. CompressPix honestly says "approximately" and explains KB = 1,024 bytes. **Differentiate with honesty plus usefulness:** show the byte count, the target-met indicator (already implemented: `X-Target-Met`, UI notices), and the 95 KB tip. Don't copy "exact" claims the tool can't guarantee.
- Several competitors run separate pages for adjacent sizes (20KB, 50KB, 200KB…) → supports §12 (50KB, 20KB).

### Query: "image compressor online free jpg png webp"
Observed: TinyPNG, imagecompressor.com, ShortPixel, Compressor.io, Creative Fabrica, MiniWebtool, Cloudinary (`/tools/compress-webp`), compressimage.io, imgconverters.com, onlineimagetool.com.

Observations:
- **"No upload / in-browser" privacy is common** (imagecompressor.com, MiniWebtool, compressimage.io). It's table stakes, not a unique selling point.
- **Batch processing** (TinyPNG: up to 20 images; MiniWebtool: ZIP download) and **more formats** (GIF, SVG, AVIF, HEIC) are common. CompressPix supports neither → product gap (EXTERNAL decision).
- Established brands (TinyPNG, ShortPixel, Cloudinary) have strong authority. Competing on the head term needs unique assets and links.

### Legitimate opportunities
1. **Evidence-rich pages:** real before/after examples and a published benchmark (§10.3). Most tool pages in these SERPs are thin.
2. **Use-case depth for forms:** signature/photo/scan guidance with dimension tables (already strong in content) plus 20KB/50KB pages.
3. **Transparent methodology:** explain exactly what happens in the browser vs server (already unusually detailed). Surface it on About/"How it works".
4. **Conversion pages** the tool already supports (PNG→JPG, JPG→WebP, WebP→JPG).
5. **Product roadmap (if the business chooses):** batch compression and AVIF output would close the most visible feature gaps.

---

## 17. Core Web Vitals / Performance

> Field data (CrUX/Search Console CWV) and lab runs: **EXTERNAL DATA**. Findings below are code-level.

### Verified measurements

| Resource | Raw | gzip | Loaded on |
|---|---|---|---|
| HTML (tool pages) | 66–74 KB | ~12.4 KB (`/compress-jpg`) | tools |
| HTML (home) | 79 KB | — | home |
| `app-*.css` | 68.0 KB | 11.5 KB | all (render-blocking, `<link rel=preload as=style>` ✓) |
| `app-*.js` | 632 B | — | all (module) |
| `index-*.js` (compressor) | 22.7 KB | 8.4 KB | tool pages + home only, end of body ✓ |
| Inter fonts | 4 weights × (woff2 ≈ 24 KB + woff ≈ 31 KB) | — | inline `@font-face` with `font-display: swap` |
| Third-party | GA4 (`gtag.js`, async) only if configured and production | — | all |

### Findings

| # | Metric | Finding | File | Fix | Priority |
|---|---|---|---|---|---|
| P1 | TTFB | DB session write on every page view (`SESSION_DRIVER=database`) + DB cache store | `.env`, web middleware | Redis/cookie session driver; skip sessions on static GET pages; edge cache HTML for anonymous users | P2 |
| P2 | TTFB | `Post::relatedToTool()` loads **all** published posts on every tool page view; `PageController@sitemap` and `SeoController@sitemap` load all posts uncached | `app/Models/Post.php`, controllers | Query with `whereJsonContains` + limit, or cache (`Cache::remember`) keyed by latest `updated_at`; cache sitemap XML | P3 (scales with post count) |
| P3 | TTFB | `ToolRegistry` `require`s 8 PHP content files per request (singleton, per request) | `app/Tools/ToolRegistry.php` | OPcache handles this; fine. Optionally cache the compiled array | P4 |
| P4 | LCP | No LCP images today; LCP = H1/intro text → good. When post hero images are added: set `fetchpriority="high"`, don't lazy-load, use responsive sources | `blog/show.blade.php` | see §9.3 | P2 (when images added) |
| P5 | CLS | `font-display: swap` without metric-matched fallback (`size-adjust`) can shift text slightly | fonts config | Use a fallback `@font-face` with `size-adjust`/`ascent-override` for Inter, or preload the 400/600 woff2 | P3 |
| P6 | Render | 4 font weights; the woff fallbacks are unnecessary in 2026 browsers (only fetched if woff2 is unsupported, so no real cost) | `vite.config.js` | Consider 3 weights (400/600/700) | P4 |
| P7 | CSS size | Tailwind `@source '../../storage/framework/views/*.php'` scans compiled views, which may retain stale class names | `resources/css/app.css` | Remove that `@source` (Blade sources are scanned by default in the Laravel plugin setup); verify build size | P4 |
| P8 | INP | Compression runs in-browser (canvas encode, palette quantization in `png.js`). Heavy PNG quantization on large images may block the main thread → poor INP during interaction | `resources/js/compressor/png.js`, `engine.js` | Move quantization/encoding into a Web Worker with `OffscreenCanvas` where supported; yield between binary-search iterations | P2 |
| P9 | Caching | No cache headers for `/build/assets/*` in `.htaccess` (hashed filenames → safe to cache 1 year) | `public/.htaccess` / server | `Cache-Control: public, max-age=31536000, immutable` for `/build/`; brotli/gzip (EXTERNAL: server/CDN) | P2 |
| P10 | HTML weight | 41 inline SVG icons (14 KB raw) repeated per page | `components/ui/icon.blade.php` | SVG `<symbol>` sprite + `<use>`; low benefit after gzip | P4 |
| P11 | Third party | GA4 on all pages (when enabled) | `components/seo/analytics.blade.php` | Acceptable; consider loading after interaction/idle | P4 |

---

## 18. Mobile SEO

VERIFIED:
- `<meta name="viewport" content="width=device-width, initial-scale=1">` ✓. No zoom restrictions ✓.
- Responsive Tailwind layout; container `px-4` on mobile ✓.
- **Content parity:** the mobile nav (`#mobile-menu`) uses the `hidden` attribute but the links are in the DOM ✓. Header nav links are identical to desktop ✓. No mobile-only or desktop-only content except "You can also paste an image from your clipboard" (`hidden sm:block`), which is appropriate.
- Form inputs use `text-base` on mobile (prevents iOS zoom) ✓.
- Touch targets: pill buttons `px-3 py-2 text-sm` ≈ 36px tall (above WCAG 2.2 AA's 24px minimum; below the 44–48px comfortable guideline). Footer links have `space-y-2.5` text-sm ≈ tight on mobile.
- The sticky header (h-16) uses `scroll-mt-16/24` on anchor targets ✓.
- Breadcrumb last item `truncate` ✓.
- Compressor: `accept=".jpg,.jpeg,.png,.webp,image/*…"` ✓; notes device memory limits in FAQs ✓.

RECOMMENDATIONS:
1. Increase pill/radio label height to ≥44px on touch (`py-2.5 sm:py-2`). P4.
2. Increase footer link line-height on mobile (`space-y-3`, `py-1`). P4.
3. Move PNG quantization to a Web Worker (§17 P8). Mobile devices are the most affected (INP). P2.
4. **EXTERNAL DATA:** check Search Console's mobile CWV report after launch.

---

## 19. Crawlability & Indexation

### 19.1 Status-code behaviour (VERIFIED, in-app)

| Request | Status | Notes |
|---|---|---|
| All 25 public pages | 200 | ✓ |
| `/blog?page=2`, `/blog?page=99` | 200 | **Soft 404** (H1) |
| `/blog?q=png` | 200 noindex | ✓ |
| `/blog/does-not-exist`, draft posts | 404 | ✓ (test verified) |
| `/Compress-JPG` | 404 | acceptable |
| `/compress-jpg/` | 200 in app; 301 on Apache via `.htaccess` | canonical protects |
| `/index.php/compress-jpg` | 404 | ✓ |
| `GET /process/compress` | 405 JSON | ✓, also disallowed |
| `/up` | 200 HTML | M1 |
| Redirect chains/loops | none found | no app-level redirects exist |
| Broken internal links | none found | all links built with `route()`; Markdown links resolve to existing routes (VERIFIED against the route list) |

### 19.2 Recommended indexation strategy

| URL group | Index? | Canonical | In sitemap | Action |
|---|---|---|---|---|
| Home, tool pages (7 after consolidation) | Index | self | Yes | — |
| `/image-compressor` | — | — | Remove | 301 → `/` (Option A) |
| Blog index | Index | self | Yes | — |
| Blog pages 2..N | Index | self | No | 404 beyond last page |
| Blog search | Noindex, follow | none/self | No | M5 |
| Blog posts | Index | self | Yes | real `lastmod` |
| Future category pages | Index when ≥5 posts | self | Yes | — |
| FAQ, About, Contact | Index | self | Yes | — |
| Legal ×4 | Index | self | Yes | low priority |
| HTML `/sitemap` | Optional noindex, follow | self | Optional remove | P4 |
| `/up`, `/storage/*`, `/process/*` | Not indexed | — | No | robots + header |
| Errors | Noindex | none | No | ✓ |
| Non-production environments | Not indexed | — | — | HTTP auth (M2) |

### 19.3 JavaScript / rendering (VERIFIED)

All titles, meta, canonical, JSON-LD, headings, body copy, FAQs and internal links are in the server HTML. JS only powers the widget and mobile menu toggle. No SEO dependence on client rendering. ✓

---

## 20. Sitemap / Robots

### 20.1 XML sitemap (VERIFIED output)

- Single `urlset`, 26 URLs: home, 8 tools, 9 static/legal/HTML-sitemap pages, 8 posts.
- `lastmod` only on posts (from `updated_at`, currently the seed time).
- `changefreq` and `priority` on every URL (ignored by Google; harmless).
- Only published posts (`published_at <= now`) ✓; drafts excluded (test) ✓.
- Canonical-consistent (`route()` URLs = canonicals) ✓. Home `<loc>` has no trailing slash (same as the canonical ✓).
- No sitemap index (fine under 50k URLs). No image extension. Not cached. `Content-Type: application/xml; charset=UTF-8` ✓.

**Should NOT be in the sitemap after the recommended changes:** `/image-compressor` (if redirected); optionally `/sitemap`.
**Should be added:** new tool pages; future category pages; image entries.

**Implementation sketch (`SeoController@sitemap`):**
```php
$toolLastmod = fn (ToolPage $t) => Carbon::createFromTimestamp(max(
    filemtime(resource_path("content/tools/{$t->key}.php")),
    filemtime(resource_path("views/tools/content/{$t->key}.blade.php")),
))->toAtomString();
// posts: $post->content_updated_at ?? $post->published_at
// cache: Cache::remember('sitemap.xml', now()->addHour(), fn () => view(...)->render())
```
Caveat: `filemtime` changes on every deploy if files are re-copied. Prefer an explicit `updated_at` key in each tool content file.

### 20.2 robots.txt (VERIFIED output, indexable mode)

```
User-agent: *
Allow: /
Disallow: /admin
Disallow: /process/

Sitemap: https://compresspix.example/sitemap.xml
```

- CSS/JS/images/fonts not blocked ✓. Search pages not blocked (correct, so noindex is seen) ✓.
- `Disallow: /admin`: unnecessary (I1).
- Missing: `Disallow: /up`; optionally `Disallow: /storage/`.
- Non-indexable mode: `Disallow: /` (see M2).

**Recommended:**
```
User-agent: *
Allow: /
Disallow: /process/
Disallow: /up
Disallow: /storage/

Sitemap: https://<production-host>/sitemap.xml
```

---

## 21. E-E-A-T / Trust

| Signal | Status (VERIFIED) | Recommendation |
|---|---|---|
| About page | Exists; describes purpose, not people/company | Add: operating entity (real legal name), country, when/why started, who builds and maintains the tool, and how compression quality was tested |
| Contact | Form ✓; email placeholder `hello@example.com` | Real monitored email; optional postal/business address if applicable |
| Company identity | `[Company Name]` in privacy/terms | Fill in; also in Organization JSON-LD `legalName` |
| Legal completeness | `[Hosting Provider]`, `[Retention Period]`, `[Log Retention Period]`, `[Jurisdiction]` | Fill in; lawyers/owner decision |
| Author attribution | "By CompressPix" (organization) | If real authors exist: author pages with name, role, relevant experience (**don't invent credentials**) and Person schema. Otherwise keep organization authorship, but add an "Editorial team / how we write and test" note |
| Editorial policy | None | Short page: how guides are researched, tested with the tool, and updated; corrections contact |
| Update dates | Legal pages ✓ visible; posts show published only (back-dated) | Real `datePublished`, visible "Updated" dates |
| Methodology / evidence | Strong technical explanations, but no demonstrations | Before/after images, benchmark data (§10.3) |
| Privacy transparency | Strong and specific ✓ | Keep; add a short "Where is my image processed?" diagram |
| Social / external profiles | `site.social` all empty | Only add `sameAs` for real, maintained profiles (e.g. a GitHub repo if the tool is open-sourced) |
| Security | HTTPS assumed (EXTERNAL); CSRF; rate limits; honeypot ✓ | Add HSTS |
| Copyright | "© {year} CompressPix. All rights reserved." ✓ | Use the legal entity name once it's real |
| Image licensing | N/A (no images) | State licences for the example images you add |

---

## 22. 2026 SEO Recommendations (current, defensible)

1. **Helpful, people-first content** with first-hand evidence (Google's helpful content guidance is now part of core ranking systems). Show real compression results rather than generic claims.
2. **Entity clarity:** Organization/WebSite/WebPage graph, a consistent brand and tool naming, and a real About page.
3. **Accurate dates** (`datePublished`/`dateModified` reflect reality).
4. **Image-first assets** for Google Images and multimodal search (Lens/AI Mode): original, descriptive, responsive, in image sitemaps.
5. **`max-image-preview:large`** for Discover and image-rich previews.
6. **Structured data only where supported and visible:** Breadcrumb, Article, Organization. FAQ/HowTo produce no rich results (FAQ retired May 2026; HowTo 2023). Don't fabricate ratings.
7. **Core Web Vitals, with INP a priority for an interactive tool:** move heavy work off the main thread.
8. **Consolidate duplicates** (home vs `/image-compressor`) and separate informational from transactional intents.
9. **Bing Webmaster Tools + sitemap submission** (Copilot visibility), optional IndexNow.
10. **Don't:** keyword-stuff, create doorway pages for arbitrary KB sizes, publish fake reviews/ratings, cloak AI-bot content, or buy links.

---

## 23. Prioritized Action Plan

| Priority | Issue | Page/File | SEO Impact | Effort | Recommended Action |
|---|---|---|---|---|---|
| P0 | Placeholder company/legal/contact data | `.env`, `pages/legal/privacy-policy`, `terms-and-conditions`, `pages/contact` | Trust/E-E-A-T, LLM misstatements | S | Fill in real values; production guard + test against `[`/`example.com` |
| P0 | `APP_URL`/`SITE_INDEXABLE` production correctness | `.env` (prod), `.env.example`, `config/site.php` | Sitewide canonical/sitemap host; staging indexing | S | Verify prod `.env`; set example to `false`; test canonical host |
| P0 | Home vs `/image-compressor` cannibalization | `HomeController`, `home.blade.php`, `config/tools.php`, `header.blade.php`, `ToolController` breadcrumbs | Head-term rankings | M | Option A: merge and 301 `/image-compressor` → `/` |
| P0 | About page lacks operator identity | `pages/about.blade.php` | E-E-A-T, entity understanding | S | Add identity, methodology, contact; AboutPage + Organization schema |
| P1 | No Organization/`@graph` entity layer | `StructuredData.php`, `Seo.php`, `meta.blade.php` | Entity/knowledge understanding, AI citations | M | Implement `@graph` per §7.4 |
| P1 | Blog pagination soft 404 | `BlogController@index` | Crawl waste, soft-404 reports | S | `abort(404)` past last page |
| P1 | Featured-post pagination offset bug | `BlogController@index` | Duplicate/missing listings | S | Exclude featured on all pages |
| P1 | Back-dated seed dates / `dateModified` = seed time | `PostSeeder`, `posts` migration, `data/posts.php` | Date accuracy, freshness trust | S | Explicit dates; `content_updated_at` column |
| P1 | Robots meta lacks `max-image-preview:large` | `Seo.php` | Discover/image previews | XS | Update default robots string |
| P1 | Blog JPG post cannibalizes `/compress-jpg` | `data/posts.php`, markdown | Tool ranking | S | Retitle/re-angle; 301 if slug changes |
| P1 | Blog 100KB post cannibalizes 100KB tool | `data/posts.php` | Tool ranking | S | Retitle to forms/signature angle |
| P1 | `/compress-image-to-1mb` only 3 inbound links | `config/tools.php` footer, `HomeController`, content partials | Rankability of size pages | S | Footer + home grid + size-ladder component |
| P1 | Zero original images | tools content, posts | Google Images, Discover, GEO, E-E-A-T | L | Create example figures (§9.3) |
| P1 | New supported landing pages: 50KB, PNG→JPG, JPG→WebP, WebP→JPG | `config/tools.php`, `resources/content/tools/*`, `tools/content/*` | New query classes | M each | Add with unique content |
| P2 | Per-post featured images + alt column | `posts` migration, `blog/show`, `post-card` | Article image quality, Images | M | Add columns and responsive rendering |
| P2 | Image sitemap | `seo/sitemap.blade.php` | Image discovery | S | `image:image` entries |
| P2 | Sitemap `lastmod` for non-post URLs | `SeoController@sitemap` | Recrawl efficiency | S | Explicit `updated_at` per tool/page |
| P2 | WebApplication `name` = H1 | `StructuredData::webApplication` | Entity naming | XS | `"{brand} {tool name}"` + `@id` + `featureList` |
| P2 | Session DB writes on every request | `.env` session driver, middleware | TTFB, cacheability | M | Redis/cookie driver; edge caching |
| P2 | PNG quantization on main thread | `resources/js/compressor/png.js`, `engine.js` | INP (mobile) | M | Web Worker + OffscreenCanvas |
| P2 | Static asset cache headers | `public/.htaccess` / server | Repeat-visit performance | S | Immutable caching for `/build/` (EXTERNAL) |
| P2 | Answer-first summaries | posts, tool partials | AI Overviews/answer engines | S | 40–60 word lead answers |
| P2 | Heading IDs + TOC in posts | `PostSeeder` (CommonMark ext), `blog/show` | Jump links, deep linking, GEO | S | Heading permalink extension |
| P2 | Per-page OG images + `og:image:*` tags | `meta.blade.php`, tool content | Social CTR | M | Add fields and tags |
| P2 | 200KB/500KB pages under-linked | footer/home/related | Rankability | S | Same as 1MB |
| P3 | `/up` crawlable | `bootstrap/app.php`, `SeoController@robots` | Crawl hygiene | XS | Disallow + X-Robots-Tag |
| P3 | Non-indexable mode Disallow + noindex conflict | `SeoController@robots`, middleware | Staging URL leaks | S | HTTP auth / `X-Robots-Tag` |
| P3 | Blog search cross-canonical + noindex | `BlogController@index` | Mixed signals | XS | Null canonical when searching |
| P3 | FAQ H1 generic | `pages/faq.blade.php` | On-page relevance | XS | "Image Compression FAQ" |
| P3 | `/image-compressor` title 68 chars (if kept) | `content/tools/image-compressor.php` | CTR | XS | Shorten |
| P3 | Short descriptions (contact, sitemap, terms, blog) | controllers | CTR | XS | Per §6.2 |
| P3 | Post-to-post differentiation (formats posts) | markdown | Cannibalization | S | Cross-link intros; distinct angles |
| P3 | Uncached all-post queries | `Post::relatedToTool`, sitemap controllers | TTFB at scale | S | Cache/limit |
| P3 | Font CLS fallback metrics | fonts config/CSS | CLS | S | `size-adjust` fallback |
| P3 | Editorial policy page | new page | Trust | S | Create |
| P3 | Redirect mechanism | routes / middleware | Safe migrations | S | `permanentRedirect` entries |
| P4 | Footer `<h2>` headings | `components/site/footer.blade.php` | Outline noise | XS | Non-heading labels |
| P4 | `og:locale` vs mixed spelling | `meta.blade.php`, content | Consistency | S | Pick en_US or en_GB |
| P4 | `<img>` without `src` in widget | `tools/compressor/*` | HTML validity | XS | `hidden` or JS-created |
| P4 | Remove `Disallow: /admin`, priority/changefreq | `SeoController` | Hygiene | XS | Remove |
| P4 | HTML `/sitemap` indexation | `PageController@sitemap` | Minor | XS | Optional noindex |
| P4 | 512px logo PNG + manifest icons | `public/images/brand`, `SeoController@manifest` | Organization logo | XS | Add |
| P4 | HSTS / CSP | `SecurityHeaders` | HTTPS consolidation | S | Add HSTS in production |
| P4 | Touch-target sizes | `controls.blade.php`, footer | Mobile UX | XS | Larger padding |

Effort: XS < 1h · S ≈ 1 day · M ≈ 2–4 days · L ≈ 1–2 weeks.

---

## 24. Final SEO Scorecard

Scores measure the **current implementation** (0–10). They aren't predictions of ranking or comparisons against competitors.

| Category | Score | Evidence |
|---|---|---|
| Technical SEO | **7.0** | Centralized SEO object, canonicals, correct 404s, tests enforce essentials. Minus: env-dependent host/indexability risk, soft-404 pagination, `/up`, pagination bug. |
| Crawlability | **8.5** | Server-rendered, all links via `route()`, no JS dependence, no broken links, robots doesn't block assets. Minus: `/up`, 405/soft-404 edges. |
| Indexation | **6.5** | noindex on search/errors ✓. Minus: indexable empty pagination, home/`/image-compressor` duplication, non-indexable mode conflict, `.env.example` defaults to indexable. |
| On-page SEO | **7.5** | Unique titles/descriptions/H1s, keyword-aligned slugs, strong body copy. Minus: cannibalizing post titles, generic FAQ H1, a few short descriptions, footer H2 noise. |
| Metadata | **7.5** | Complete title/description/canonical/OG/Twitter. Minus: single OG image, no `og:image` dimensions/alt, no `max-image-preview`, one over-long title. |
| Structured Data | **5.5** | Valid BreadcrumbList, BlogPosting, WebApplication, FAQPage. Minus: no Organization/`@id` graph, generic article images, org author, seed-time `dateModified`, H1-as-entity-name. |
| Internal Linking | **6.5** | Related tools/posts, contextual links in partials and Markdown, breadcrumbs. Minus: 1MB/500KB/200KB under-linked, About barely linked, no TOC/heading anchors. |
| Image SEO | **2.5** | Correct width/height/alt on the few images present. Minus: no content images, no featured images, no alt field, no srcset/modern delivery, no image sitemap, single OG image. |
| Performance | **7.0** (estimated) | Small JS, route-scoped compressor bundle, self-hosted swap fonts, text LCP. Minus: DB sessions per request, main-thread PNG quantization (INP risk), no asset caching rules in repo. Field data needed. |
| Mobile SEO | **8.0** | Proper viewport, responsive layouts, content parity, iOS-safe inputs. Minus: smallish touch targets, heavy client work on mobile. |
| Content Quality | **7.5** | Accurate, specific, honest tool and article content (1.1–1.6k words per post). Minus: no visuals or original data, some repeated FAQ blocks. |
| Topical Authority | **4.5** | Good core coverage (formats, size targets, how compression works). Minus: 8 posts, no clusters/categories, missing conversion/signature/EXIF/AVIF topics. |
| Entity SEO | **3.0** | Consistent brand name. Minus: no Organization entity, placeholder company, no sameAs, inconsistent tool entity names, ambiguous main tool URL. |
| GEO | **6.0** | Extractable tables, precise facts, clear Q&A, crawlable by all bots. Minus: no answer-first summaries, no original data, weak source identity. |
| LLM/AI Search Readiness | **5.5** | An LLM can explain what the site does and how it works. It can't identify the operator or reliably pick the authoritative URL/dates. |
| E-E-A-T / Trust | **2.5** | Detailed privacy explanations and legal pages exist. Minus: placeholders across legal/contact/company, no authors, no methodology evidence, back-dated dates. |

---

## 25. TOP 25 ACTIONS TO IMPLEMENT FIRST

1. **Replace every placeholder:** `[Company Name]`, `[Hosting Provider]`, `[Retention Period]`, `[Log Retention Period]`, `[Jurisdiction]`, `hello@example.com`, `noreply@example.com`. Add a production guard and a test.
2. **Verify production `.env`:** `APP_URL=https://<final-host>`, `SITE_INDEXABLE=true`, `APP_ENV=production`. Change `.env.example` to `SITE_INDEXABLE=false`.
3. **Consolidate `/image-compressor` into `/`** (merge content, 301, update nav/CTA/breadcrumbs/schema).
4. **Rewrite About** with the real operator identity, methodology and contact. Mark it as AboutPage.
5. **Implement a JSON-LD `@graph`** with Organization (logo 512px, contactPoint, real sameAs only), WebSite, WebPage, WebApplication, BlogPosting and BreadcrumbList, all linked by `@id`.
6. **Return 404 for empty blog pagination pages** in `BlogController@index`.
7. **Fix the featured-post pagination offset** (exclude the featured post on every page).
8. **Use real article dates:** explicit `published_at`, new `content_updated_at` for `dateModified`, sitemap `lastmod` and a visible "Updated" date.
9. **Default robots to** `index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1`.
10. **Retitle `how-to-compress-jpg-images-online`** to a quality-settings angle, and add a 301 if the slug changes.
11. **Retitle `how-to-compress-image-to-100kb`** to a forms/signature/scan angle, and add a 301 if the slug changes.
12. **Add all size pages to the footer and the home tool grid**, plus a size-ladder component on every size page.
13. **Create original before/after example images** for `/compress-jpg`, `/compress-png`, `/compress-webp` and `/compress-image-to-100kb`, with descriptive filenames, alt text and captions.
14. **Give every post a unique featured image:** add `featured_image_alt`, responsive `<picture>`, and `fetchpriority="high"` on the hero.
15. **Add image entries to `sitemap.xml`** (image sitemap extension).
16. **Launch `/compress-image-to-50kb`** (the preset already exists) with unique content about signatures and forms.
17. **Launch converter pages the tool already supports:** PNG→JPG, JPG→WebP, WebP→JPG, each with unique guidance.
18. **Add 40–60 word answer-first summaries** at the top of every tool page and post.
19. **Publish an original, reproducible compression benchmark** (quality vs size by format) with methodology and a downloadable data table.
20. **Add heading IDs and a table of contents** to blog posts (CommonMark heading permalink extension).
21. **Add `lastmod` to all sitemap URLs** and drop `priority`/`changefreq`.
22. **Rename WebApplication entities** to "CompressPix {Tool}" and add `featureList`.
23. **Move PNG palette quantization/encoding into a Web Worker** to protect INP on mobile.
24. **Switch sessions off the database** (Redis/cookie) and set immutable caching for `/build/` assets. Measure TTFB in production.
25. **Block `/up` and `/storage/`** in robots.txt with an `X-Robots-Tag: noindex` header. Protect non-production environments with HTTP auth. Submit the sitemap to Google Search Console and Bing Webmaster Tools.

---

### Sources consulted for external facts
- [Google Search Central — Changes to HowTo and FAQ rich results (2023)](https://developers.google.com/search/blog/2023/08/howto-faq-changes)
- [Search Engine Journal — Google Drops FAQ Rich Results From Search (2026)](https://www.searchenginejournal.com/google-drops-faq-rich-results-from-search/574429/)
- [Quattr — FAQ Schema in 2026](https://www.quattr.com/blog/faq-schema-in-2026)
- SERP observation, query "compress image to 100kb online": [Zamzar](https://www.zamzar.com/tools/compress-jpg-100kb/), [11zon](https://imagecompressor.11zon.com/en/compress-jpeg/compress-jpeg-to-100kb), [compressjpeg.dev](https://compressjpeg.dev/compress-jpeg-to-100kb), [imagy](https://imagy.app/resize-image-to-100kb/), [SmallSEOTools](https://smallseotools.com/compress-jpeg-to-100kb/), [Pi7](https://image.pi7.org/compress-image-to-100kb), [jpeg-optimizer](https://jpeg-optimizer.com/compress-jpeg-to-100kb/), [hicompress](https://hicompress.com/compress-image/compress-image-to-100kb)
- SERP observation, query "image compressor online free jpg png webp": [TinyPNG](https://tinypng.com/), [ImageCompressor.com](https://imagecompressor.com/), [ShortPixel](https://shortpixel.com/online-image-compression), [Compressor.io](https://compressor.io/), [MiniWebtool](https://miniwebtool.com/image-compressor/), [Cloudinary](https://cloudinary.com/tools/compress-webp), [compressimage.io](https://compressimage.io/)
