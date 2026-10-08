# SEO architecture and audit

## Course CMS synchronization (7 October 2026)

Course routes and metadata now read published database courses through
includes/course-store.php, also used by every public course template. The original
data/courses.php is migration reference only. Title/description/category/image edits
flow through existing metadata and Course JSON-LD helpers; stable published slugs
preserve canonical URLs. Uploaded course images use the existing guarded media.php
endpoint for Open Graph. Drafts return 404/noindex and never enter the sitemap.
Session values are projected centrally from the next upcoming verified session;
course/session prices, currency conversion and Offer/price structured data are retired.
Historical amounts are inactive references only. Database failure yields 503 instead of falling
back to historical course data. The current sitemap is 41 routes plus additional
published courses/articles (24 original courses included in the 41 baseline).

Audit and implementation: 6 October 2026. PHP/WAMP remains authoritative.
This work changes metadata and discovery endpoints, not business datasets or routing.

## Initial audit

All 40 public variants returned HTTP 200: eight main pages, 24 course details,
six category listings and two training recaps. All already had unique titles,
one H1, English document language, UTF-8, viewport metadata and crawlable links.
Existing slug validation correctly returned 404. Preserve those strengths.

Confirmed gaps: 32 catalogue/category/course pages lacked descriptions; all 40
lacked canonical, social metadata and JSON-LD. Both robots.txt and sitemap.xml
returned 404. Home's title only identified the organization. Additional query
parameters had no canonical consolidation. Error pages lacked explicit noindex.
All 69 distinct rendered images had alt attributes. Three decorative benefit
photos already reserve space through CSS aspect-ratio; two course brand logos
lacked intrinsic dimensions. Partner-strip images were all eager-loaded.
English and French language courses share a source description; using it alone
would create duplicate descriptions. SEO now includes the official course name.

## Files and ownership

- `data/seo.php`: public production URL, route style, indexability and fallback image.
- `includes/seo.php`: URL validation, route registry, metadata, images and JSON-LD.
- `includes/seo-head.php`: escaped metadata rendering; JSON uses safe HEX encoding.
- `includes/head.php`: calls the shared SEO layer before rendering the common head.
- `sitemap.php`: XML built from the actual course/category/event datasets.
- `robots.php`: plain-text robots policy from the same configuration.
- `.htaccess`: two project-local rewrites for sitemap.xml and robots.txt only.
- `scripts/check-seo.py`: repeatable read-only HTTP/content checks.
- `scripts/check-seo-browser.py`: optional Chromium CDP interaction/layout checks.

Course data, currency calculations, contact handling, CSS, JavaScript, Wrangler and
all media files remain unchanged. `course.php` now supplies real width/height for
its brand images; `includes/partner-strip.php` lazily loads logos. AGENTS.md and
ARCHITECTURE.md link this document.

## Public URL and canonical strategy

Set `base_url` in data/seo.php to the final HTTPS production base. The initial
value is the project's established official reference: https://www.salaamcenter.net.
Confirm www/non-www and any deployment subdirectory before launch. This value
does not assert that the new PHP routes already exist on the current public site.
Never derive it from Host, REQUEST_URI, a tunnel or a filesystem path.
Invalid/local/IP/temporary Cloudflare bases suppress canonical/schema output and
mark pages noindex; the sitemap fails with 503 rather than publish invalid URLs.

Current `route_style` is `php`: Home uses `/`; detail pages use their validated
slug query, such as `/course.php?slug=financial-analysis`. Other pages use their
existing PHP filenames. Tracking parameters and Contact venue selection are not
canonical variants. Unknown, missing and array slugs retain HTTP 404, noindex,
and no canonical or JSON-LD. No redirect to Home is introduced.

The `indexable` flag defaults to true for intended public content. For an exposed
staging deployment, set it false (noindex + disallow; sitemap 503) and preferably
restrict access. Local WAMP remains a development site; do not expose it publicly.
robots.txt is crawl guidance, never protection for credentials or private files.

## Titles and descriptions

Pages keep their existing `$pageTitle` and `$pageDescription` inputs; head rendering
normalizes the brand suffix to ` | Salaam Center`. Home has a factual descriptive
title. Missing catalogue metadata is generated centrally from the page topic and
authoritative record. Official course names, accents and punctuation are preserved.
No dates, prices, achievements or learning outcomes are invented.

Course descriptions include the official name plus the existing description, or
a conservative invitation to view available details. There is no arbitrary truncation
that cuts sentences or titles. Some supplied descriptions are longer than a search
snippet; Google may truncate or choose different visible text. Review concise editorial
summaries with the owner if needed, rather than invent course content.

## Social metadata

Open Graph title, description, URL, website type and site name share the SEO values.
Twitter/X cards use the same values with no invented account handle. Courses,
categories and events use their existing image and alt text; other pages use the
verified reception photo. The helper validates a real local image under assets/images,
reads dimensions and produces an absolute public URL. It omits missing/unusable images.
Source photos are neither recompressed nor retouched. Live previews need public URLs.

## Structured data

- Organization and WebSite use stable IDs and existing public contact/social data.
- WebPage, AboutPage or ContactPage describes the current page.
- BreadcrumbList mirrors existing navigation on 39 non-home pages.
- Course is included on all 24 detail pages with official name, description, image
  and provider. No offers, invented ratings, reviews or course instances are added.
- Event schema is deliberately omitted: both current records are training recaps
  without confirmed dates. WebPage and breadcrumbs still describe them.
- Published CMS articles emit BlogPosting from real stored values; drafts never do.

Valid JSON-LD does not guarantee a Google rich result. Check current eligibility
and the live Rich Results Test separately at launch.

## Sitemap and robots

GET sitemap.xml dynamically returns 40 unique canonical URLs from shared route
helpers and authoritative datasets. No fake lastmod, priority or changefreq values.
robots.txt allows public crawling and references the configured production sitemap.
Apache serves both through the project's .htaccess; no WAMP/server configuration
is changed. Direct sitemap.php and robots.php endpoints are also available.
On a production origin, robots.txt must be served at the origin root, even when
the application itself occupies a subdirectory; arrange that during hosting setup.

## Internal links, headings, images and performance

Existing Home/Courses/Categories/detail/Events and institutional links were already
useful and crawlable. No artificial SEO links or visible heading rewrites were added.
One H1 is preserved everywhere; detail content uses H2, and cards under sections
use H3. Alt text is factual; keep empty alt for decorative/redundant images and never
invent names for people in photographs.

Brand-image intrinsic dimensions reduce unreserved space. Existing image frames,
lazy loading, deferred JavaScript, first-Hero priority and font display=swap remain.
Partner-strip lazy loading reduces competing offscreen requests without changing
its CSS motion. Large ports-event photos and video remain a performance follow-up:
responsive derivatives require a dedicated media task; originals are preserved.
No Core Web Vitals score or public performance improvement is claimed without measurement.

## CMS integration and clean routes

The article-only CMS now provides title, stable slug, summary, structured body,
featured image/alt and actual stored UTC timestamps. The shared metadata helper
accepts article context and returns BlogPosting, breadcrumbs, Open Graph article
type and a canonical URL. No author is invented. Published URLs cannot be changed
in V1. Authenticated previews use noindex,nofollow; public draft URLs return 404.
The sitemap adds News and published articles (41 + published article count), with
the original 40 routes retained if CMS storage is unavailable. CMS errors return
503/noindex without canonical/schema. Private image access is enforced by media.php;
public social image URLs use that endpoint. See [CMS.md](CMS.md) for setup and security.

For clean production routes, change route_style only once the matching routes work.
Update navigation/exported links, redirects, canonical, og:url, schema IDs, sitemap
and production asset URLs together; test that every advertised URL returns 200.

**Existing Cloudflare export needs a later integration step before deployment.**
This SEO task does not modify the exporter or Cloudflare configuration. Its old
cloudflare-dist output is stale. A rebuild in PHP mode would retain PHP canonicals
while generated navigation uses clean paths. Before publishing, use the clean
SEO route mode, ensure SEO images are copied, and include generated sitemap.xml
and robots.txt (PHP endpoints and .htaccess do not run on static hosting).
Revalidate the complete export, canonical URLs and schema references. Do not deploy
the earlier build or assume the old export workflow already handles these additions.

## Validation and limits

Run `python scripts/check-seo.py` while WAMP is running. Optional QA dependencies
are requests and beautifulsoup4; they are testing tools, not website dependencies.
The browser checker additionally uses websocket-client and a locally started
Chromium CDP endpoint on port 9223; use an isolated temporary profile.

HTTP checks passed for all 40 pages: 40 unique titles/descriptions/canonicals,
one H1, HTML language/UTF-8, JSON parsing, 69 image paths, local links/assets,
parameter normalization, nine invalid-route cases and the 40-URL XML sitemap.
No PHP warning markers, mojibake, development URL or filesystem path in SEO output.
Browser QA passed 44 page/viewport combinations (11 page types at 1440, 1366,
768 and 390 pixels wide), with no horizontal overflow, loaded-image failures or
JavaScript exceptions. Mobile menu, Courses dropdown, currency selector, homepage
autoplay, About autoplay and About reduced-motion stop passed. The mobile homepage
screenshot was inspected. PHP source lint and JavaScript syntax checks passed.
No contact form submission, database change, analytics, Search Console token or Git
operation. Production DNS, redirects, crawl access, live Google indexing, public
social previews, field Core Web Vitals and rich-result eligibility remain unverified.

## Recovery verification (6 October 2026)

The interrupted implementation was recovered without replacing its SEO architecture.
Metadata, endpoints, image attributes, lazy loading and documentation were already
implemented. No missing public-page SEO feature was found in the recovery review;
the outstanding work was a fresh full validation and final report.

The HTTP checker now independently compares sitemap detail routes with links in
the actual Courses, Categories and Events listings; it also checks main-content
heading progression, image response types, full-page mojibake and placeholder SEO
text. Revalidation passed: 40 public variants, 40 distinct titles/descriptions,
24 courses, six categories, two events, 40 sitemap URLs, 69 image paths and 54
local link/asset targets. Nine invalid-slug cases and three parameter-normalization
checks passed. All 40 non-private PHP source files passed lint; main.js passed its
syntax check. The 44 responsive/browser checks and interaction checks above were
rerun successfully. Hash comparison confirmed the course/category/event datasets,
currency helper, contact handler, CSS, JS and Wrangler configuration were unchanged
from the preceding SEO session. This recovery changed only the HTTP QA script and
this document. No production requests, deployment or form submission were made.

## References

- [Google canonical guidance](https://developers.google.com/search/docs/crawling-indexing/consolidate-duplicate-urls)
- [Google sitemap guidance](https://developers.google.com/search/docs/crawling-indexing/sitemaps/build-sitemap)
- [Google robots guidance](https://developers.google.com/search/docs/crawling-indexing/robots/intro)
- [Google event structured data](https://developers.google.com/search/docs/appearance/structured-data/event)
