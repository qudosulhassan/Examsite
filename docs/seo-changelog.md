# AzureExamsHub SEO overhaul: change log (branch `seo-overhaul`)

Live at https://azureexamshub.com since 5 November 2026. 18 commits on top of `main`; the live site's server is checked out on this branch. `main` is not changed.

## Step 0: indexing
- The site-wide lock (`noindex, nofollow`) is off: `sites.robots_directive` = `index, follow` and the site settings `seo_robots_index/follow` = `index/follow` for site 3.
- One shared rule decides robots for every page (`TechnicalSeoService::robotsForRequest`, used by the layouts, and `SecurityHeaders` for the header): login, register, password, cart, checkout, dashboard, search and test-session pages are `noindex, follow`; admin/api/error responses stay `noindex, nofollow`; everything else follows the site setting.
- Retired or unlisted exams (PL-400, AZ-303, 62-193) and exams without free questions are `noindex` on the relevant pages and out of the sitemap.

## Content
- 54 exam guides are built from the official Microsoft Learn study guides (`resources/seo-content/learn/*.json`, rendered by `App\Support\ExamGuideContent`): at a glance, audience, domains with weightings and explanations, a study plan, six FAQs per exam, a "where it fits" path, and the date the guide was checked. Retiring/retired exams show a notice; MS-102 shows the retirement date.
- FAQs: per-exam questions and answers are built from each exam's own data and checked for uniqueness across all exams in a unit test. The site FAQ (14 questions) and the contact page FAQ come from one data source each (`SiteFaq`) for both the visible text and the FAQPage markup. Payment and refund questions are left out until the owner supplies the facts.
- The old shared FAQ page claimed "real exam questions" and is replaced on this site.
- Homepage: new H1 and a section on how the practice tests work, a curated popular-exams grid, a new six-question FAQ. Seven track intros (220-270 words each).
- Trust pages: About (placeholders for founder, experience, and how questions are written), Contact (form now logs messages on this site, response-time placeholder), Terms (draft, owner to review), Refund policy (holding page, `noindex`, waiting for the owner's text).
- Blog: five posts (1,200-1,440 words), all facts from Microsoft Learn pages and Microsoft's June 2026 credentials roundup, published with a team byline through `php artisan blog:publish-site-posts`. 20-topic plan in `docs/content-plan.md`.

## Titles, descriptions, markup
- `App\Support\SeoMeta` builds every title (50-60 characters, brand suffix) and description (at most 158) from data. `docs/seo-titles-meta.csv` lists all 145 URLs.
- Structured data: Organization (raster logo, support email) on every page, WebSite + SearchAction on the home page, BreadcrumbList matching visible breadcrumbs, Course on indexable guides, Product with Offers (USD, InStock) on plans pages only, FAQPage wherever FAQs show, CollectionPage on tracks and the vendor page, BlogPosting on posts (team byline as Organization). No ratings or reviews. Retired/noindex pages carry no Course or Product.

## Technical
- Redirects were already correct (http to https, www to non-www, trailing slash, uppercase codes, tracking parameters via canonical). Sitemap: 145 URLs with real `lastmod` on dated pages, `application/xml`, retired exams excluded. A hook that regenerated static sitemap files on non-core sites (hiding the per-host sitemap) now does nothing there.
- robots.txt disallows account, cart, checkout, login, register, search, test-session and similar paths and names the sitemap.
- Helpful 404 with search, all tracks and popular exams. `site.webmanifest`, `theme-color`, `lang="en"`, Open Graph `website`/`article`, favicon set, no meta keywords tag.

## Speed
- Measured on the live site (server time = first byte minus connection/TLS; this PC is about 0.65 s from the server): before any work about 2.8 s of server time per page; with the file cache and trimmed queries about 1.0-1.9 s; with the guest page cache about 0.25 s on a cache hit.
- Guest page cache (`CacheGuestPages`, `SITE_PAGE_CACHE=true`, 15 minutes, flush with `php artisan pagecache:flush`) swaps in each visitor's own CSRF token, and is skipped for signed-in users, carts, queries and flash messages. Redirect lookups cached for 10 minutes on this site. Database indexes on the lookup columns already exist (exam code, slug, vendor, question exam id); the cost was network round trips to the other server.
- Front end: one Google Fonts request (`display=swap`), Brotli on HTML and assets, one-year cache for hashed build files, module scripts load deferred, logos have width/height and the footer logo is lazy. The site has no content images, so no WebP conversion was needed.

## Accessibility
- Logo alt "AzureExamsHub logo"; labelled inputs and buttons on the blog, test engine and contact pages; the sign-in pages show this site's logo instead of ExamTopicsBase's; 48 px tap targets on the new form controls.

## Not done / needs the owner
- Refund policy text; payment methods (Stripe not connected); named founder/reviewer and how questions are written (placeholders on About); response time on Contact; terms review and governing law; a mailbox for `support@azureexamshub.com`.
- Lighthouse scores could not be produced: the local tool could not start the browser in this environment, and Google's PageSpeed API quota was exhausted. Run https://pagespeed.web.dev for the home page, `/tracks/azure-cloud` and `/exams/az-104` and compare with the server timings above.
- `/about` and `/contact` are still short pages by nature; the exam pages average about 1,770 words including header and sidebar.
