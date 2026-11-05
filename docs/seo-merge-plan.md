# Merging `seo-overhaul` into `main`: what it changes on ExamTopicsBase

`seo-overhaul` is 18 commits ahead of `main`. Merging changes the shared code that ExamTopicsBase (site 1) and ProExamTopics (site 2) also run, but only when their servers pull it. **Nothing changes on ExamTopicsBase until its server is updated.** Do not merge until the owner says "merge".

## New files (no effect on other sites)
`ExamGuideContent`, `SeoMeta`, `SiteFaq`, `FlushPageCache`, `PublishSitePosts`, `CacheGuestPages`, the `views-sites/stackcert` pages, `resources/seo-content/*`, `public/sites/azureexamshub/*`, `public/images/logo-primary.png`, docs and tests.

## Shared files that change behaviour, and what each does to ExamTopicsBase

| File | Change | Effect on site 1 |
|---|---|---|
| `config/site.php`, `bootstrap/app.php`, `CacheGuestPages` | page cache, redirect cache TTL, schema logo, editorial block | **None.** All off unless an env value is set (`SITE_PAGE_CACHE`, `SITE_REDIRECT_CACHE_TTL`, `SITE_SCHEMA_LOGO`) |
| `GenerateSitemap` | does nothing when `site.id` is not 1 | **None** (site 1 is id 1) |
| `DynamicSeoController` (sitemap) | adds blog posts, drops `/vendors` for one-vendor sites, omits fake `lastmod` on static pages | **None** while site 1 keeps serving its static sitemap files; applies to sites without them |
| `routes/web.php` | `/terms`, `/refund-policy` routes (404 unless the site has those views); contact form logs only when `site.id` is not 1 | **None** |
| `SecurityHeaders`, layouts, `guest`/`app` layouts, cart, checkout, success, cancel, demo-engine pages | private pages (login, register, cart, checkout, dashboard, search) become `noindex, follow` instead of `noindex, nofollow` | **Small:** those pages stay out of the index but crawlers may follow their links |
| `TechnicalSeoService` | `robotsForRequest`; BlogPosting uses the site's own image/logo when set and an Organization byline for "... Editorial Team"; extra robots.txt disallows for non-core sites | **Small:** site 1's blog markup and robots header follow the new rule above; robots.txt for site 1 still comes from its own file |
| `CheckRedirects` | schema checks cached for a day; rule lookup cached only if `SITE_REDIRECT_CACHE_TTL` > 0 | Negligible (schema facts only) |
| `blog/index`, `blog/show` | aria labels, `og:type` article on posts, `noindex` only when the blog is empty | Better accessibility; `og:type` of posts becomes `article` |
| `application-logo` component | uses the current site's logo when the site has one | None (site 1 has no `sites.logo`) |
| `public/.htaccess` | one-year `Cache-Control` for `build/assets` | Positive: hashed files; confirm a new Vite build is deployed with each release |
| `test-engine` view | aria label | None |

## Safest ways to merge (recommended order)
1. **Recommended: merge to `main` as is, deploy to the site 3 server only.** Because the risky behaviours are off by default or scoped to non-core sites, the merge is low risk. Update the ExamTopicsBase and ProExamTopics servers later, on their own schedule, and check `/login`, `/blog` and the home page afterwards. Keep a note of the commit before the merge so it can be reverted.
2. **If you want zero change to site 1 for now:** merge, but leave its server on the current commit (it only updates when someone pulls). The auto-deploy webhook on site 1 is unreliable, so confirm it before merging.
3. **Separate long-lived branch:** keeps main untouched but means the shared fixes (sitemap guard, robots rules, blog accessibility, sign-in logo) must be copied by hand. Not recommended.

Suggested checks before updating site 1: private pages return `noindex, follow`, the blog still renders, the static sitemap files are untouched, the home page and one exam page return 200.
