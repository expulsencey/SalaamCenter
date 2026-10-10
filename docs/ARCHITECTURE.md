# Salaam Center Website Architecture

## Course editor Phase 2B - 10 October 2026

The existing `admin/course-edit.php` form now groups the same mapped fields into five
steps, enhanced by `assets/js/admin.js`; without JavaScript all sections remain visible.
Validated values alone reach SQL; after errors, submitted text is restored separately
for display. Failed version checks retain the submitted version to prevent stale retries
from overwriting newer edits. The session editor shares the error-retention behavior.
`admin/_fields.php` issues session-bound creation keys (up to 100 per record type).
Successful inserts record their ID against the key; replay returns that editor without
another insert. Existing authentication/CSRF and update version checks remain required.
No schema, course data mapping, public service or permanent deletion handler changed.
See [Phase 2B validation](CMS-PHASE2B-VALIDATION.md).

## Course deletion update - 10 October 2026

This replaces earlier draft-only/typed-slug requirements for course deletion.
`admin/course-delete.php` accepts authenticated, CSRF-protected POSTs with a valid
course ID and optimistic version. A transaction locks the course, checks existence,
version and the number of linked sessions, then deletes exactly one row with prepared
SQL. Any session blocks deletion; the error shows its count and management link.
Published courses are eligible. FK RESTRICT is retained; no constraints are disabled
and no session cascade or uploaded-file removal occurs. Article deletion is unchanged.

The course editor progressively enhances an inline confirmation into a native dialog:
labelled title/warning, safe initial Cancel focus, Escape, native focus containment and
focus return. Explicit deletion submission dismisses the unrelated unsaved-edit warning.
Without JavaScript the server confirmation form remains usable. The public catalogue,
category/Home lists and sitemap continue reading the same published MySQL records.
See [CMS-COURSE-DELETION.md](CMS-COURSE-DELETION.md) for validation and limitations.

## Current Phase 2 - administration workflow (7 October 2026)

Phase 1 storage, public projection, authentication, uploads and SEO are reused.
The public website and its assets are unchanged. All sections marked historical
below describe earlier states; their pricing, preview-save and installation guidance
is not current. Course/session pricing remains retired; venue rates are separate.

- `admin/_layout.php` is the only shell. `_fields.php` centralizes badges, UTC date
  formatting, section links, one-use session feedback and allowlisted image choices.
- Course/session editors retain their validated writes and optimistic versions.
  Forms gain section landmarks, saved-preview links and unsaved-change feedback.
- Articles use prepared title/status filtering and 25-result pagination. The existing
  safe JSON block renderer/editor is reused, with client-side ordering controls.
- `admin/article-delete.php` requires authentication, POST, CSRF, typed exact slug,
  draft status and current version under a transaction/row lock. It deletes one
  article, never files. Existing protected course deletion remains unchanged in scope.
- Media reads attached uploads from existing courses/articles, groups shared images,
  searches content titles and paginates at 18. There is no new media table/store.
  Editor image selections must belong to the database-backed allowlist. New uploads
  retain the existing content/dimension checks and private guarded endpoint.
- Preview is a read-only authenticated GET of saved content, never a form action.
  Published updates are live immediately; there is no revision staging or autosave.
- Vanilla admin JavaScript enhances native forms. Block reordering changes submitted
  DOM order; PHP normalizes validated blocks. Input/change signals mark unsaved work;
  submit clears the browser warning. Security never depends on JavaScript.

### CMS operations and security

The current local installation already has its owner, 24 original courses and eight
sessions. Do not migrate/import/reset them during routine editing. `data/courses.php`
is an immutable historical source, not a runtime fallback. The original import ledger
prevents re-import from overwriting CMS edits.

Runtime requirements: PHP 8.3, PDO MySQL, mbstring, fileinfo, GD/WebP, iconv and sessions;
MySQL utf8mb4; writable private media and configured session/upload temporary paths.
Private `config.local.php` follows `config.example.php`; never display its values.
`SALAAM_CMS_CONFIG` is a server-only override used by isolated QA, not a URL parameter.
Preserve existing Contact configuration and data. No WAMP configuration changes.

For an explicitly authorized fresh installation, `scripts/cms-setup.php migrate`
creates/validates CMS tables; `import-courses` is the guarded one-time source import.
Back up first. Do not use these commands to repair or reset the current catalogue.
Account setup uses `scripts/cms-admin.ps1` with a hidden password prompt passed through
stdin; `-Action reset-password` is reserved for an explicitly requested owner recovery.
No credentials in command arguments. No public registration or password reset email.

Passwords use bcrypt cost 12 with a 12-72 byte setup requirement. The existing account
and hash are preserved. Sessions regenerate on login, expire after 30 minutes idle or
8 hours total and invalidate on logout/password reset. Cookies are HttpOnly,
SameSite=Strict, and Secure on HTTPS. Login throttling tracks email/IP in MySQL
(eight attempts in 15 minutes). Prepared SQL, output escaping, CSRF and authorization
protect mutations; restrictive CSP, frame denial, no-store and noindex protect admin.
Public errors never expose SQL, paths or credentials.

Uploads are JPEG/PNG/WebP, at most 5 MB, 12 MP and 6000 pixels per side. The existing
validator decodes/re-encodes WebP to random exclusive filenames. `storage/cms-media/`
is private. `media.php` serves authenticated staff or uploads referenced by published
content, with no caching. Previously downloaded copies cannot be revoked. Removing
one shared reference cannot hide an image still used by published content.

Back up all CMS tables and media together; keep configuration separately protected.
Test restore into an isolated database. Apache must honor the repository restrictions
on storage/database/scripts/config; reproduce them on any future host. PHP's built-in
server requires an explicit denying router and is used only for isolated QA here.
The application requires persistent PHP/MySQL hosting. No export or deployment is
part of this phase.

### Phase 2 validation tooling

`python scripts/check-cms-courses.py --browser` restores the private
`storage/cms-qa/phase2-backup.json` into a uniquely named disposable database. It requires
CREATE DATABASE permission, Python QA packages and an isolated Chromium CDP endpoint
on 9225. A separate PHP server uses port 8095 and a protected private config. Only its
own database/uploads are removed afterward; the owner's account/content are never
used for authentication or editing. No migration/import is rerun. Source copies and
snapshot comparisons are private QA artifacts, not runtime dependencies. Results are
summarized in [CMS-PHASE2-VALIDATION.md](CMS-PHASE2-VALIDATION.md).

## Phase 1 foundation - 7 October 2026

This section supersedes earlier pricing and fixed-seed-count descriptions below.
MySQL owns Courses, Training Sessions and Articles; `data/courses.php` is immutable
migration/reference data. The catalogue has no 24-course operational limit.

- `includes/course-store.php` projects active course fields and upcoming sessions.
  It explicitly excludes historical price keys and selects session columns without
  amounts. No currency helper or conversion remains in the active application.
- `admin/course-edit.php` validates allowlisted fields, preserves absent and hidden
  values, handles ordered repeatable lists and saves draft/published state. URLs
  stay fixed after first publication. Images reuse the existing upload validator.
- `admin/course-delete.php` is authenticated POST/CSRF/version protected. It locks
  the course, requires typed confirmation and draft state, blocks dependencies,
  and preserves FK RESTRICT. Unpublishing remains the normal retirement path.
- `admin/courses.php` and `sessions.php` use prepared search/filter queries and
  25-result pagination. Lists/counts grow with the database.
- Sessions have active course/date/duration/language/status fields. Exact same
  course/date warns before an explicit override. No new uniqueness constraint.
- Course and Article previews are read-only authenticated GETs of saved content.
  Save/publish controls are separate; published updates are immediately live.
- Public templates/card/header no longer render course/training prices; the
  currency JS and price-only CSS are removed. Venue rental rates remain unrelated.

No schema or real business record was changed. Legacy `price_djf` and JSON amount/
currency/source fields remain dormant for compatibility, not editor inputs or
public output. The historical import and seed remain intact and were not rerun.
CMS media, authentication, prepared statements, optimistic version checks and
SEO publication filtering retain their existing responsibilities. The admin header
and dashboard retain the previously corrected public visual identity.

See [CMS.md](CMS.md) for daily workflows and [CMS-PHASE1-VALIDATION.md](CMS-PHASE1-VALIDATION.md)
for scope, validation, pricing inventory and remaining phases. Existing generated
Cloudflare files are a historical export, not the current dynamic deployment.

## Historical architecture and audit record


## Current CMS expansion — 7 October 2026

See [CMS-EXPANSION-VALIDATION.md](CMS-EXPANSION-VALIDATION.md) for the completed
recovery audit, real database counts, test results and file manifest.

This section supersedes the file-backed course and article-only CMS statements in
the historical audit below. The authorized CMS now manages courses and sessions
as well as articles. Public presentation and the shared currency helper are unchanged.

`admin/course-edit.php` and `session-edit.php` write prepared statements to
`cms_courses` and `cms_course_sessions`. `includes/course-store.php` is the single
read/projection service for `includes/catalog.php`, public course pages and the
shared SEO route registry. There is no second public catalogue or manual sync.
Published rows alone are projected into the established template data shape.
The next upcoming session overrides date, duration, language and price where known;
missing values fall back to general course values. Completing a session never
removes the permanent course. Dates use Africa/Djibouti for upcoming selection.

`cms_courses.data` is validated JSON holding the existing rich course metadata,
including all provenance fields, aliases and arrays. Stable slug, publication
status, order, homepage selection, uploaded media and optimistic version have
dedicated columns. Sessions have a course foreign key, verified date, duration,
language, a single DJF amount, status and version. `cms_migrations` records the
one-time transactional import and original source checksum. No account, article
or contact table is reset. The 24 original rows and eight dated sessions were
imported without retyping their information. Historical undated USD source amounts
remain USD until an explicit administrator replacement; conversion remains centralized.

`data/courses.php` is a retained reference for first import only. Live readers never
fall back to it. Failed/uninitialized database access returns a safe 503 instead of
silently resurrecting unpublished or stale data. The sitemap returns 503 if course
storage is unavailable. Categories, Events, Partners, Home, venues and public contact
data remain file-backed; Events/Partners have read-only administration summaries.

Admin layout/CSS/JS reuse the public header classes and stylesheet for a horizontal
desktop header and native collapsible mobile navigation. Local Poppins assets keep
the existing self-only admin CSP. See [ADMIN-UI-CORRECTION.md](ADMIN-UI-CORRECTION.md).
Article functionality shares that shell. Course images reuse the existing
validated upload pipeline and guarded media endpoint; public access requires a
published course or article referencing the upload. The Media screen lists existing
attached uploads, not a second file store. See [CMS.md](CMS.md) for staff workflows,
migration operations, validation and hosting constraints.

Audit date: 5 October 2026. Scope: current files in `C:/wamp64/www/SalaamCenter`.
This document records the implementation inspected, not an already completed refactor.
The roadmap is a recommendation and does not authorize implementation.
Phase 1 PHP foundations completed on 5 October 2026; current sections below reflect
that refactor. The inventory line counts and original audit findings remain historical.

## Technology Stack

Deployment addition (6 October 2026): PHP/WAMP remains the source; `npm run export`
launches `scripts/export.mjs` and the CLI-only `scripts/export-static.php` to generate
`cloudflare-dist/` from localhost. `npm run deploy` exports before invoking the local
Wrangler dependency for Workers Static Assets. Node/npm is authorized for deployment
tooling only, not an application framework. No PHP backend is deployed; generated
Contact uses verified email/telephone links. See [CLOUDFLARE_DEPLOYMENT.md](CLOUDFLARE_DEPLOYMENT.md).
The application-stack inventory below describes the PHP source; Wrangler and its
lockfile are additive development tooling.

Current: server-rendered PHP, HTML5, CSS, vanilla JavaScript, Windows/WAMP Apache.
No application framework, application bundler or routing framework is used. The
authorized article-only PHP/MySQL CMS was added on 6 October 2026; see [CMS.md](CMS.md).
PHP uses union types and string helpers requiring PHP 8; local CLI inspected is PHP 8.3.14.
The contact handler requires PDO MySQL and mbstring. It targets MySQL database
`salaam_center`, table `contact_messages`. Course data remains in PHP arrays.
Google Fonts supplies Poppins with a system-font fallback. All image/video URLs in
rendered pages are local. No database or external service was changed during this audit.

## Directory Structure

```text
/                       11 public PHP entry pages; config.example.php; ignored config.local.php
/includes/              15 shared templates, helpers and contact handling files
/data/                  7 PHP datasets, including public site contact information
/assets/css/style.css   single shared stylesheet
/assets/js/main.js      single deferred shared script
/assets/images/         93 image assets, 2 provenance documents, 1 partial download
/assets/videos/         3 MP4 files (2 live references, 1 retained source)
/docs/                  5 earlier Markdown reports, 56 images, 1 JSON baseline before this audit
/AGENTS.md              permanent project instructions
/.gitignore             local configuration, environment files, logs, backups and editor exclusions
```

Counts exclude Git internals and count the empty local configuration as a PHP file.
There were 33 PHP files, 1 CSS file and 1 JS file before this documentation update.
The `.gitignre` and `.gitignre.txt` names in historical IDE context are not present
on disk; the actual `.gitignore` is present and excludes `config.local.php`.

## Page Layer

- `index.php`: homepage composition; catalogue, home/event/partner data and contact setup.
- `about.php`: institutional sections, About slideshow and expandable existing venue component.
- `courses.php`: all 24 courses in authoritative order; `categories.php`: six categories.
- `category.php`: category slug validation and filtered listing; `course.php`: validated detail page.
- `events.php`: current events and archived external articles through `info-page.php`.
- `event.php`: validated event slug, video, gallery, optional completion material.
- `partners.php`: shared moving strip and full static partner grid.
- `contact.php`: prepares venue query context and shared form; receives form POSTs.
- `sc-business.php`: concise page with link to the existing official service destination.

`index.php`, `courses.php`, `category.php` and `course.php` load `includes/catalog.php`.
The other seven pages load `includes/helpers.php` without loading course data;
`categories.php` loads only `data/categories.php` for its listing. Every public page
explicitly prepares `$site` from `data/site.php` and sets page variables before
including `head.php` and `header.php`. Most render their
own main content. Events, Contact and SC Business use `info-page.php`. Every page uses
`footer.php`. There is no application router or common dependency container.

## Component Layer

SEO addition (6 October 2026): `head.php` uses `includes/seo.php` and `seo-head.php`.
Public settings live in `data/seo.php`; sitemap/robots PHP endpoints share its URL
strategy and are exposed by two project-local .htaccess rules. See [SEO.md](SEO.md)
for validation, CMS integration and the required later static-export adaptation.
The older missing-SEO findings below describe the pre-implementation audit.

- `head.php`: UTF-8 response/meta, language, title, optional description, shared assets and body opening.
- `header.php` / `footer.php`: shared navigation and institutional contact links.
- `course-card.php`: image, bottom-left price and linked official title. Used by all course listings.
- `event-card.php`: common event content; full-card link on home, separate links on Events.
- `partner-strip.php`: one logo source rendered twice for the CSS loop; second copy is aria-hidden.
- `home-sections.php`: homepage About, benefits, testimonials, events, spaces, partners, CTA and contact.
- `venue-hire.php`: spaces navigation and venue panels from `$venues`; reused inside About disclosure.
- `contact-section.php`: form and contact details; depends on session/handler variables already prepared.
- `news-list.php`: archived article cards from `$homeContent['news']`.
- `info-page.php`: shell for Events, Contact and SC Business. The unused About branch was removed in Phase 1.
- `helpers.php`: only the generic `escapeHtml()` function; no business-data loading.

Templates communicate through caller-scope variables such as `$course`, `$cardHeading`,
`$event`, `$eventCardClickable`, `$partners`, `$venues`, `$activePage` and form state.
Phase 1 added concise input contracts to header, footer, home-sections, contact-section
and info-page. Header/footer require `$site`; home-sections also requires home/event/
partner data and contact state. The contact handler must run before output to prepare
form values, errors, topics, success state and the session CSRF token. `$venueTopic` is
optional. The info-page contract specifies Events data, Contact setup and SC Business
action variables; its required-heading guard now includes `$pageHeading`. No fake defaults
or framework-style context objects were introduced.

## Data Layer

### Article CMS addition (6 October 2026)

Recovery on 7 October is scoped to foundation/authentication only. The components
below were already created by the interrupted task; phases 2–5 still need separate
acceptance. Preserve them without expanding their scope. `cms_users` is the existing
administrator table, not an additional table alongside `admin_users`. See CMS.md.

`includes/cms.php` reads the private database configuration independently of the
Contact form/session handler. MySQL owns only CMS users, article records and login
counters in addition to the pre-existing Contact feature. `database/cms.sql` and
CLI-only `scripts/cms-setup.php` provide repeatable non-destructive setup.
`admin/` owns guarded login/dashboard/list/editor/preview/logout; `cms-auth.php`
uses its own session name. Public `blog.php`/`article.php` reuse shared layout/SEO.
Featured uploads are decoded/re-encoded into private `storage/cms-media/`; `media.php`
enforces publication or administrator access. JSON content blocks render through
fixed allowed tags and escaped inline formatting. Admin CSS/JS are independent;
blog.css is loaded only for News pages. Footer/Events provide discovery. See
[CMS.md](CMS.md) for operations, security, backups and the static-hosting limitation.
Course, pricing, events and Contact behavior are unchanged.

### Existing structured datasets

- `data/courses.php`: 24 keyed records; 18 confirmed source prices, 6 absent prices, 6 featured entries.
  Uses `name`, `format`, `outcomes`, `includes`, `exam`, not every conceptual field name in AGENTS.
  Stores source provenance, aliases, category mapping notes, images and optional content.
- `data/categories.php`: six category labels and images. `catalog.php` derives course labels from this.
- `data/events.php`: two real training records; video and gallery metadata; optional completion fields.
- `data/partners.php`: 20 local logo records, supplied/confirmed by the user.
- `data/venues.php`: three venue records, capacities, features and USD rental rates.
- `data/home.php`: short institutional copy, benefits, testimonials and two archived external articles.
- `data/site.php`: authoritative public email, phone display/dial target, address lines and
  existing Facebook/LinkedIn URLs. Loaded explicitly by each public page as `$site`; never
  used for private configuration. Templates retain the existing wording and markup.
- `config.example.php`: expected DB configuration shape. `config.local.php`: ignored and currently empty.

`helpers.php` defines UTF-8 HTML escaping only. `catalog.php` requires it once and retains
course-specific `courseUrl()`, `courseDate()` and `courseFacts()`. It loads and sorts
courses, keys them by slug, joins category names and computes featured courses. `currency.php` alone owns
177.721 DJF per USD and server formatting. JavaScript selects precomputed `data-usd` or
`data-djf`; it does not repeat the exchange rate. `courseFacts()` currently computes the
Price string which `course.php` replaces with `coursePriceMarkup()` for the live selector.
This minor repeated computation is low impact and is not a reason to change frozen pricing.

Contact flow: session and CSRF setup -> POST validation -> prepared INSERT -> token
rotation and 303 redirect -> session success message. Invalid CSRF returns 403, invalid
fields 422, storage failure 503. No mail transport exists; a successful submission means
stored enquiry, not a dispatched email. Configuration is checked only when submitting.

## Assets

Photography, logos, illustrations and video originals coexist with presentation copies.
There is no asset manifest or automated derivative pipeline. Do not delete originals
based only on reference searches. Docs include QA screenshots, not runtime dependencies.
Deployment should eventually use an explicit file selection rather than publish every
source, partial download and QA artifact by default.

## CSS Architecture

Current `style.css` has 916 physical lines; many complete rules occupy one long line.
Sections: tokens/reset/type/layout -> header/navigation -> ambient layer -> homepage
Hero/reveal -> courses/catalogue -> home sections/contact -> About -> footer -> venues
-> events -> partners -> home event overrides -> currency header layout.
Responsive rules are partly local, partly grouped near unrelated later sections.
The cascade is understandable but increasingly sensitive to additions at the bottom.

Existing reuse: tokens, `.container`, `.home-section`, `.section-heading`, `.split-layout`,
`.hero-button`, shared cards, and reduced-motion rules. The name `.hero-button` now
covers many non-Hero buttons. `.courses-catalog` is also used for the contact submit
button and overridden by contact styles: naming and styling responsibilities overlap.

Candidate future organization (not created): base/tokens/layout; shared header/buttons/
cards/footer; feature styles for home, catalogue, About, events, partners and contact.
Keep responsive rules beside the component. Preserve stylesheet order and validate the
cascade before extraction. Plain link tags or native CSS organization suffice; no build
system is required. Repeated selector names under media queries are intentional, not
proof of redundant rules. Palette, gradient and literal-count evidence is in BRAND_GUIDELINES.

## JavaScript Architecture

Current `main.js`: 295 lines, loaded with `defer` everywhere.

| Feature | Current implementation |
|---|---|
| Mobile navigation | Top-level scoped block, breakpoint 68rem, Escape/focus handling |
| Homepage Hero | Top-level block, 5500ms rotation, shared reducedMotion query, pauses on focus/hidden tab |
| Courses dropdown | `initCoursesMenu()`, native details plus Escape/outside click/focus handling |
| Reveal | One one-shot IntersectionObserver; reveals remain available without JS |
| Testimonials | `initTestimonials()`, manual previous/next |
| Contact | Submit guard, disabled button while submitting, pageshow reset; server remains authoritative |
| Event video | `initEventVideo()`, separate ongoing observer at 20% visibility; pause for hidden/reduced-motion |
| Currency | `initCurrencySelector()`, USD/DJF only, safe localStorage fallback and pageshow/storage sync |
| About Hero | `initAboutSlideshow()`, 4000ms rotation, 1200ms dissolve, decode before switching |
| Partner strip | CSS animation, not a JavaScript slider |

No duplicated slider initialization or duplicated listeners for the same component were
found. The reveal and video observers have different lifecycles and should stay separate.
Homepage and About slideshows intentionally differ. About retains an opaque previous
frame during incoming fade, and has no controls or hover pause at the user's request.
Both respect reduced motion and hidden tabs; no-JS first photographs remain visible.

Weakness: independent initializers execute serially in one global script. A synchronous
exception early in it can prevent later initializers. DOM guards help, but aren't fault
isolation. Some state is top-level (`reducedMotion`, navigation, slideshow); initialization
styles are inconsistent. About's 1200ms JS cleanup duplicates its CSS transition duration.

Future: encapsulate navigation/reveal/currency as site behavior; load homepage, About,
event and form behavior only where needed if growth warrants it. Native deferred files
or browser modules are sufficient. Do not combine both sliders merely because both rotate
images; first document their different contracts and test the requested behavior.

## Media Organization

- `assets/images/` root: 41 files, predominantly legacy/user originals and partner logos;
  includes one incomplete `ICA_Logo.jpg.crdownload` which is not referenced.
- `courses/`: 17 images plus SOURCES.md. `home/`: 11 images plus SOURCES.md, including retained sources.
- `hero/`: five WebP facility photos. `about/`: reception copy; `about/hero/`: five photos;
  `about/recognition/`: three original GIFA JPEG copies.
- `events/`: six administrative training JPEGs; ports subdirectory: four JPEGs.
- `logo/`: one current header WebP. Partner logos remain at the images root, not in this folder.
- `assets/videos/`: administrative H.264 (6.25 MB); live ports H.264 (19.40 MB);
  retained ports HEVC source (11.27 MB). Decimal MB, rounded. The retained source isn't the live URL.

Nine exact-byte duplicate groups were found: reception (three copies), four other About
photos (two each), AI logo, CCNA, Fortinet and Sage Payroll (two each). This includes
intentional original/copy preservation and is not a deletion instruction. Originals and
optimized Hero derivatives are not necessarily exact duplicates.

The ports gallery JPEGs range from 1.86 to 2.30 MB each, also used for cards. A future
responsive derivative policy can save transfer without changing source media. The About
workshop is 768x513 and will look softer at large desktop widths; CSS cannot restore detail.

## Naming Conventions

Existing code uses English kebab-case classes/URLs, slug-keyed datasets and readable PHP
helpers. New assets use descriptive lowercase names; older assets still have mixed case,
spaces and WhatsApp timestamps. Preserve paths until every reference has been checked.
Keep official French course titles and `lang="fr"`; interface text stays English.

## Single Source of Truth Rules

Keep courses, source prices, categories, events, partners and venues in their current
central datasets. No course detail text should be copied into page templates. Keep the
conversion parity in `currency.php`. Do not turn missing information into invented values.
Public contact details and social URLs have one source in `data/site.php`. Header, footer,
contact section and course enquiry links escape those values when rendering. Keep
phone display text and its dialable URI together as two representations of one number. About institutional paragraphs currently
live in `about.php`; homepage summary is separate approved copy, not a conflicting course source.

## Component Reuse Rules

Inspect existing include contracts before adding components. Reuse course/event cards,
partner strip, head/header/footer, contact and venue components. Do not extract a one-off
paragraph merely to increase the number of files. A new shared include needs an explicit
input contract and clear ownership of IDs/headings; never rely on unexplained prior loop variables.

## Maintainability Rules

Inspect before implementing; reuse before creating. Keep unrelated behavior separated,
but only extract when it reduces real coupling or duplicate maintenance. Preserve behavior,
visible wording and source values during refactoring. Record meaningful changes here.
No framework, Node/npm, CMS or unrelated refactor is authorized by this document.

## When to Split a File

Split when independent features change separately, a shared dependency loads unrelated
data, or repeated markup has diverged. Example: catalogue bootstrap versus HTML escaping;
page-specific styles versus global tokens; DB access versus form validation when needed.
Make one reversible extraction and verify existing pages before moving the next concern.

## When NOT to Split a File

Do not use a line-count threshold as a quality rule. The 872-line course dataset has a
single coherent responsibility and useful provenance. Keep it intact while pricing is
frozen. A 15-line card is already a reasonable unit. More tiny includes can make scope
variables and navigation harder to follow. Do not add abstraction for hypothetical scale.

## Adding a New Page

Choose a stable root PHP destination. Prepare only required data before output, set
`$pageTitle`, `$activePage` and a verified `$pageDescription`, then reuse head/header/footer.
Validate query slugs against known data, return 404 for missing entries and escape dynamic
output. Use one H1, semantic main/sections, UTF-8, responsive media and useful link text.
Load `includes/helpers.php` for generic HTML escaping, or `includes/catalog.php` when
course data/functions are required. Prepare `$site = require __DIR__ . "/data/site.php";`
at the page entry point for shared contact markup; load only the feature data needed.

## Adding a New Component

Check existing components first; define inputs, optional fields, IDs and heading levels.
Prefer an include for repeated presentation, not another business-data source. Ensure
omitted optional data does not render empty headings. Keep fallbacks meaningful without JS.

## Adding New JavaScript Behavior

Use a feature initializer, guard required elements, avoid duplicate registration and
unnecessary globals. Reuse the existing reveal system. Account for reduced motion,
visibility, focus, loading failures and no-JS content. Test existing features after shared edits.

## Adding New CSS

Use current tokens and compositions from BRAND_GUIDELINES. Keep selectors scoped, inspect
the cascade before adding overrides and place responsive/reduced-motion rules with the
component. Do not add arbitrary colors, gradients or !important to mask structural mistakes.

## Adding New Business Data

Use current user-approved sources, preserve provenance and official names, and omit unknowns.
Update the authoritative dataset, not its consumers. Never publish secrets or undocumented
relationship/accreditation claims. Confirm new dataset fields and their consumers together.

## Technical Debt / Refactoring Roadmap

### Business constraints

Catalogue/pricing presentation awaits business clarification. Do not redesign it, remove
functionality, change pricing logic or add currencies. Current course selector: USD ($),
DJF (Fdj), first-visit USD, no EUR. Existing venue rates remain USD and are not wired into
the course selector; any change in that policy requires business confirmation.

The structured SEO layer and article-only CMS now support news publication without
editing PHP or using Git. Other CMS modules require separate authorization. The
historical audit recommendations below predate this implementation.

### Original audit findings and Phase 1 status

Severity assesses current impact; timing is a recommendation, not authorization to refactor.
No CRITICAL issue was established in this scoped audit. This is not a security certification.
The table records the original findings. Phase 1 resolves A3 (generic helper coupling)
and A4 (repeated public contact values), and removes the dormant PHP branch in A5.
Historical reports and unused CSS selectors remain for later work. A1/A2 and A6-A10
are outside this refactor; contact configuration and behavior remain unchanged.

| ID | Severity / timing | Evidence and problem | Impact and recommended action |
|---|---|---|---|
| A1 | HIGH / NOW | `config.local.php` is 0 bytes; `contactDatabase()` requires a returned DB array. No schema/migration file in the project. | Valid enquiries cannot be stored with this configuration. Owner must supply private configuration and confirm existing table; later perform an authorized end-to-end submission. Document schema/deployment prerequisites. No DB write attempted in this audit. |
| A2 | MEDIUM / SOON | `contact.php` derives venue only from GET; form posts to `contact.php`, sending generic `venue-hire` topic with no space slug. | Specific selected room is lost unless visitor mentions it. Later preserve an allowlisted venue identifier through validation and storage; do not trust the display label. |
| A3 | MEDIUM / SOON | Every public page requires `includes/catalog.php` to obtain general helpers, loading all course data. | Unrelated pages depend on catalogue validity. Extract general escaping/bootstrap helpers from catalogue loading; keep pricing/data untouched. |
| A4 | MEDIUM / SOON | Phone/email repeated in header, footer, contact section and course.php; address/social links repeated too. | Updates can drift. Introduce one approved site-contact configuration and reuse it without changing values. |
| A5 | MEDIUM / NOW documentation, SOON code | Historical HOMEPAGE/EVENTS/COURSE-CATALOG reports conflict with current implementation; dormant About branch remains in info-page.php. | A future agent could restore obsolete code. This document records current state; later label old reports historical and remove only proven dormant branches/selectors. |
| A6 | MEDIUM / SOON | style.css mixes independent features, global button names and scattered responsive overrides; repeated radius/shadow literals. | Changes require broad cascade knowledge. Extract feature styles gradually with unchanged order and visual comparisons; tokenize recurring roles, not every number. |
| A7 | MEDIUM / SOON | main.js mixes top-level state and initializers, executes everything serially; About timing is duplicated in CSS/JS. | Early exceptions may stop unrelated functionality; timings can diverge. Encapsulate features and define timing ownership before selective loading. No duplicate initializer found. |
| A8 | MEDIUM / SOON after brand confirmation | Header and footer use different full-logo assets; favicon uses a baked blue square image; no documented official clear-space or minimum size. | Future replacements may be inconsistent. Preserve existing assets now; confirm usage variants and document a canonical role for each. |
| A9 | LOW / SOON media inventory; LATER optimization | 9 duplicate groups, large ports photos, retained HEVC and partial download; partner strip eagerly renders two copies. | Avoidable transfer/storage and unclear source/copy roles. Record provenance and active paths, consider responsive derivatives and deferred logos, preserve originals until review. |
| A10 | MEDIUM / LATER, plan SOON | No structured SEO/CMS architecture. All 32 catalogue/category/detail variants inspected lack meta descriptions; no canonical/social/schema output in head.php, no sitemap/robots file. | Search presentation and nontechnical publishing need an explicit plan. Confirm content model/URLs first; evaluate CMS separately rather than install one now. |

### Additional evidence and non-issues

- Unreferenced CSS candidates: `.course-image-placeholder`, `.course-duration`, `.course-link`,
  `.course-card-facts`, `.course-category`, `.course-logo`. No PHP/JS consumer found. LOW / SOON
  after browser coverage. Do not remove shared `.about-overview` rules: current About uses them.
- Phase 1 removed the dormant About branch from `info-page.php` after checking all PHP
  callers, CSS selectors, JS references and the standalone `about.php`. Only Events, Contact
  and SC Business use the shared shell. `.about-heading`/`.about-actions` CSS remains untouched
  for later cleanup; current About still uses `.about-overview` and the venue component.
- About is 112 physical lines and home-sections 85, but densely packed markup contains many
  sections. MEDIUM / LATER: format and extract only independently maintained blocks.
- `event-card.php` deliberately switches tags to avoid nested links; this is controlled reuse,
  not duplicate event data. LOW / NO ACTION REQUIRED beyond documenting inputs.
- Empty alt on decorative benefit/event-card media is deliberate, with nearby visible text;
  do not replace all empty attributes blindly. LOW / NO ACTION REQUIRED.
- Source `official_category` and `category_source` fields are provenance, while category_slug
  owns current membership. Do not erase provenance as apparent duplicate data.
- Original Hero photos, removed Talent media, old video and partial ICA download are not live
  references. Unused does not mean broken, and no deletion is proposed without provenance review.
- Event media uses `filemtime()` without an existence guard. LOW / SOON: validate assets on
  deployment; a missing future file would otherwise produce warnings. No current missing file found.
- Autoplay photos/video have no visible controls by explicit user decision. Reduced motion is
  honored, but a broader accessibility review should revisit user control, video alternatives,
  and keyboard access before claiming WCAG conformance. MEDIUM / SOON review, no UI change now.
- Generic include variables and inline long markup matter more than raw file size. No repeated
  course pricing source, duplicate JS initialization or duplicate reveal observer was found.

### Prioritized phases (future)

1. Resolve contact configuration/schema readiness; confirm venue enquiry requirements and
   catalogue freeze. Update stale-document status, record deployment prerequisites and baseline checks.
2. Low-risk cleanup: document include contracts, general helper extraction, approved contact-data
   centralization; remove proven dormant About code only after checking every caller.
3. CSS organization: preserve order, group components and responsive rules, establish radius/shadow
   roles; compare all pages at existing breakpoints. No visual redesign.
4. JS separation: scope initializers, isolate page features, clarify animation timing ownership;
   verify both sliders, no-JS, reduced motion, navigation, currency, video and forms.
5. PHP/media cleanup: format dense sections, extract only useful components, introduce asset provenance
   and delivery rules; preserve current route/slug/query contracts and originals.
6. SEO architecture: verified metadata, canonical strategy, sitemap/indexing, social previews and
   truthful structured data. Do not invent course/event facts to fill schemas.
7. CMS decision, then separately authorized implementation: business editing workflow, role model,
   hosting/backups/media, security and migration. Preserve public URLs and the existing content model.

### Original documentation audit verification and limits

Read-only GET checks: 40 valid page variants returned 200 with no rendered PHP warning/notice
marker. Their rendered local media references and internal destination files exist. Invalid
array slugs for course/category/event returned 404. These are file/HTTP checks, not a fresh
full browser/interaction or accessibility certification. External archived URLs were not
revalidated. No contact POST, DB connection/schema query, credentials disclosure, or Git operation.
Existing screenshot artifacts are historical evidence, not tests rerun by this audit.

`config.local.php` was checked only for existence/size (empty); no secret values were read.
Only ARCHITECTURE.md, BRAND_GUIDELINES.md and AGENTS.md may change in this task. Final hashes
are compared against the starting inventory to verify runtime files and assets remain unchanged.

## Phase 1 Verification

- 51 HTTP route variants compared before/after: all public page types, all 24 course
  details, six category listings, both events, invalid/missing/array slugs and Contact
  query variants. Status, parsed element/attribute/text structure match after normalizing
  the session-specific CSRF token. No rendered PHP error marker or missing local link/asset.
- All 35 PHP files pass syntax checks. CLI include traces confirm the seven non-catalogue
  pages do not load catalog.php or data/courses.php.
- CLI execution encountered sandbox permission warnings for the WAMP Xdebug log and
  Contact session directory; localhost HTTP checks did not show these warnings. No server
  configuration was changed. No database submission or SMTP test was performed.
- No CSS, JavaScript, images, course data, currency logic, contact handler, AGENTS.md,
  DESIGN.md or BRAND_GUIDELINES.md changes. No intentional visual change or Git operation.
- Existing contact venue-context loss and private backend setup remain separate work.
  Phase 2 has not been started.

## Inventory and Large File Analysis

Physical line counts at audit time; a long single-line template is not necessarily simple.

| File | Lines | Responsibility / decision |
|---|---:|---|
| assets/css/style.css | 916 | Independent global and feature styles; gradual extraction justified by concerns, not size alone. |
| data/courses.php | 872 | Coherent 24-course dataset with provenance; keep intact under business freeze. |
| assets/js/main.js | 295 | Multiple independent features; encapsulate first, selectively split as needed. |
| about.php | 112 | Page composition and approved institutional content; optional later section includes, not urgent. |
| includes/contact-handler.php | 109 | Session, validation, DB access and submission orchestration; later separate pure validation/storage if testing or reuse warrants it. |
| includes/home-sections.php | 85 | Many homepage sections with shared scope; eventual section extraction, preserve composition order. |
| AGENTS.md | 1665 before update | Permanent policy, not executable code; large because it records requirements. Link supporting docs rather than repeat all details. |

### Complete PHP/CSS/JS inventory

| File | Lines |
|---|---:|
| `about.php` | 112 |
| `assets/css/style.css` | 916 |
| `assets/js/main.js` | 295 |
| `categories.php` | 24 |
| `category.php` | 29 |
| `config.example.php` | 11 |
| `config.local.php` | 0 |
| `contact.php` | 14 |
| `course.php` | 62 |
| `courses.php` | 26 |
| `data/categories.php` | 10 |
| `data/courses.php` | 872 |
| `data/events.php` | 47 |
| `data/home.php` | 18 |
| `data/partners.php` | 24 |
| `data/venues.php` | 41 |
| `event.php` | 51 |
| `events.php` | 10 |
| `includes/catalog.php` | 40 |
| `includes/contact-handler.php` | 109 |
| `includes/contact-section.php` | 36 |
| `includes/course-card.php` | 15 |
| `includes/currency.php` | 26 |
| `includes/event-card.php` | 17 |
| `includes/footer.php` | 14 |
| `includes/head.php` | 20 |
| `includes/header.php` | 45 |
| `includes/home-sections.php` | 85 |
| `includes/info-page.php` | 40 |
| `includes/news-list.php` | 9 |
| `includes/partner-strip.php` | 12 |
| `includes/venue-hire.php` | 32 |
| `index.php` | 65 |
| `partners.php` | 24 |
| `sc-business.php` | 11 |

### Existing documentation inventory

- COURSE-CATALOG.md: provenance/category mapping, but older USD-only wording; current policy is USD/DJF.
- HOMEPAGE.md: historical rollout; references removed Talent content and unavailable backend as an old state.
- HERO.md: current homepage image provenance and 5.5s/850ms behavior; not the About slider specification.
- EVENTS.md: useful original sources, but ports HEVC/poster/unfinished status is superseded by current H.264 URL.
- PARTNERS.md: 20 logo records and provenance; visible question-mark separators are documentation corruption.
- `assets/images/courses/SOURCES.md`, `assets/images/home/SOURCES.md`: provenance; historical use columns
  are not automatically a list of current consumers.
- `docs/hero-baseline.json`, 53 PNG screenshots, three JPG contact sheets: retained QA artifacts.

Current code and latest explicit user decisions take precedence over these older task reports.
No old report was silently rewritten or deleted in this documentation task.
