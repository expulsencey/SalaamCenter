# CMS Phase 1 implementation and validation

Completed locally on 7 October 2026. Scope: existing Courses, Training Sessions,
Articles and admin shell; active Course/Training pricing retired. No Phase 2 module,
Git command, deployment, real database migration or original import was performed.

## 1. Files modified

- `AGENTS.md`
- `admin/_fields.php`
- `admin/article-edit.php`
- `admin/course-edit.php`
- `admin/course-preview.php`
- `admin/courses.php`
- `admin/preview.php`
- `admin/session-edit.php`
- `admin/sessions.php`
- `assets/css/admin.css`
- `assets/css/style.css`
- `assets/js/admin.js`
- `assets/js/main.js`
- `course.php`
- `courses.php`
- `docs/ARCHITECTURE.md`
- `docs/BRAND_GUIDELINES.md`
- `docs/CMS.md`
- `docs/DESIGN.md`
- `includes/catalog.php`
- `includes/course-card.php`
- `includes/course-store.php`
- `includes/header.php`
- `scripts/check-cms-courses.py`
- `scripts/check-seo-browser.py`
- `scripts/check-seo.py`

Created: `admin/course-delete.php`, this report and
`docs/CMS-PHASE1-PRICING-INVENTORY.md`.
Removed: `includes/currency.php`, after checking its only consumers were course pricing.
Private recovery/QA artifacts under `storage/cms-qa/` include the database/media
snapshot, source baseline, verification scripts, result JSON and screenshots.
They are not public application components; direct HTTP access was verified as 403.
No private configuration, image/video asset or original reference dataset changed.

## 2. Schema changes

None. All seven real tables have exactly the original schema and rows, verified
against the pre-change snapshot. Counts: users 1, courses 24, sessions 8, articles 0,
login limits 1, migration ledger 1, contact messages 0. CMS media are unchanged.
The snapshot was restored and compared exactly in a unique isolated test database.
The owner should retain a secure off-server copy through their backup procedure.

## 3. Course CRUD

Search, category/status filtering, real totals and SQL pagination at 25 records.
Add/edit/draft/publish/unpublish, feature selection and display order work through
Admin. A test catalogue reached 26 records and produced 25/1 paginated results.
No runtime catalogue limit of 24 exists; that number only describes original seed data.
New published records automatically join existing public listings, details and SEO.

## 4. Course editor

Retains title/title language, slug, category, subtitle, description, duration,
teaching language, format, level, image/alt, order and homepage selection. Rich
lists have explicit editors. Pricing inputs/help/validation have been removed.
Blank slugs generate unique safe URLs; published URLs remain fixed after unpublishing.

## 5. Data-loss protections

Only validated allowlisted fields are written. Absent scalar fields, lists and
checkbox state are preserved; presence markers distinguish intentionally cleared
lists/checkboxes. Blank optional text is represented as null; clearing a known
value is deliberate. Hidden provenance, aliases, source URLs, brand metadata and
legacy prices are preserved. Attempts to mass-assign hidden fields had no effect.
Version checks reject stale saves. Existing course values were edited and restored.

## 6. Repeatable lists

Outcomes, includes, certification, requirements and exam/evaluation each provide
ordered text rows, Remove and Add item. Server validation rejects non-array input,
more than 80 items and more than 10000 characters per list. Text is escaped.
Keyboard focus moves sensibly after removal; changes have status announcements.
Without JavaScript, blank rows can be filled and existing rows cleared before saving.
Add/remove and mobile wrapping were tested at all six widths.

## 7. Course lifecycle and deletion

Unpublish/save as draft is normal retirement. Permanent deletion requires a saved
draft, typed exact URL name, authenticated POST, CSRF and current version. A transaction
locks the course while checking dependencies. Sessions block deletion; FK RESTRICT
remains unchanged. GET, missing CSRF, wrong confirmation, stale version, published
state and dependent sessions were rejected. A confirmed dependency-free draft was
deleted. Uploaded files are not automatically deleted by this action.

## 8. Training Sessions

Prepared searchable/filterable lists, course filter and 25-result pagination.
Create/edit course, date, duration, language and draft/upcoming/completed state,
plus a link to the associated course. There is no price input or price write.
Same course/date warns with an explicit override checkbox. A legitimate override
was tested. No uniqueness constraint was added. Invalid dates/unknown courses/stale
writes are rejected. Past sessions stay stored with their chosen status but are
excluded from upcoming public projection. Courses remain after sessions complete.
There is no separate public Upcoming Training page in the current architecture;
upcoming dates are displayed within course details. That structure was preserved.

## 9. Articles

The existing module was preserved. Only save/preview labels and action handling
changed. Draft, publish, Blog synchronization, BlogPosting/sitemap, unpublish and
cleanup were tested in the isolated database. There is no new article schema/editor.

## 10. Preview semantics

Course and Article **Preview saved version** links are authenticated GET requests.
They do not save forms, stage changes or expose drafts publicly. The old preview
POST action is rejected. Published updates explicitly write to the live website.
To edit privately, unpublish first. No oversized revision system was introduced.

## 11. Admin header/design

Previously corrected public header classes, logo proportions, Poppins, colors and
responsive navigation were retained. No header rebuild. Browser checks compared
public/admin logo and header proportions at all six dimensions. Additions are scoped
to repeatable lists, pagination and the retirement disclosure; no page overflow mask,
zoom, transform scaling or tiny typography was introduced.

## 12. Dashboard

Existing real metrics/quick actions/recent list were retained. Counts reflect the
database and reference datasets. At 1366?768, the metrics and start of recent content
are visible without scrolling to reach basic operational information. No financial
or fabricated analytics were added.

## 13. Responsive results

| Viewport | Admin and public result |
|---|---|
| 1920?1080 | Passed |
| 1440?900 | Passed |
| 1366?768 | Passed; dashboard fold checked |
| 1024?768 | Passed |
| 768?1024 | Passed |
| 390?844 | Passed; mobile menu and repeatable controls checked |

The browser suite checked 11 admin routes at each size, CSS loading, one H1, input
labels, image loading and document scroll width. Screenshots inspected included
desktop dashboard/editor and mobile repeatable fields/public header. No page-level
horizontal overflow or JavaScript exceptions in the final successful browser run.

## 14. Public regression

72 responsive page checks across six widths covered Home, About, Courses, Categories,
course/category details, Events/event detail, Partners, Contact, SC Business and Blog.
Mobile navigation, Courses dropdown, Home autoplay, About autoplay/reduced motion
and both event video metadata loads passed. No contact form was submitted.
SEO HTTP suite: 41 pages, 24 course details, six category listings, two event details,
69 image paths, 56 local link/asset targets, nine invalid routes and three canonical
parameter checks. All existing course names/metadata remain sourced from MySQL.
Only pricing controls/labels/badges and the obsolete catalogue mention of fees changed
publicly. Public asset paths, typography, section structure and other copy are retained.

## 15. Course round-trip test

On the isolated restored database: original Power BI title/description edited through
CMS, public title/catalogue/category/SEO checked, exact original business fields
restored. Partial payload preserved hidden fields and ignored legacy-price injection.
Temporary course created as draft without price, previewed, published, featured,
unpublished and deleted. Unique/stable slug, image privacy and stale saves checked.
Two extra temporary drafts verified pagination beyond 24, then were deleted.
The real 24 original records were never edited and match the snapshot byte-for-byte
as fetched by PDO, including JSON, versions and timestamps.

## 16. Session round-trip test

On the isolated copy: an existing duration changed and was restored, while an injected
retired amount had no effect on historical storage. A temporary draft was created
without a price, made upcoming, edited/completed, tested for duplicate warning and
past-date filtering. Its dependency blocked course deletion. Test-only session rows
were removed from the disposable database before deleting the test course.
The real eight session records, including dormant amounts, were unchanged.

## 17. Article test

Temporary draft saved, authenticated saved preview opened, draft absent from Blog,
published article synchronized with public detail/BlogPosting/sitemap, old preview
POST rejected without changing live title, unpublished and verified public 404.
The disposable database was dropped after tests; no article remains in the real site.

## 18. Security and verification

Existing authentication/password hashing/session/throttling code is byte-identical.
Auth guards, CSRF, SQL-like input, escaped XSS, stale course/session writes, safe upload,
rejected executable upload, draft media isolation, unique slugs and deletion safeguards
were exercised. SQL mutations are prepared and errors remain generic publicly.
This is scoped functional security verification, not a penetration-test certification.

- Browser-inclusive CMS run: **276 assertions passed** (before the final extra
  pagination/deletion/past-session assertions were added).
- Final expanded functional CMS run: **124 assertions passed**; no runtime code
  changed between those checks except the saved-preview explanatory label.
- Public browser run: **72 responsive page checks passed**, no JS exceptions.
- PHP syntax: **61 PHP files passed**.
- UTF-8: **48 files in active admin/includes/data/CSS/JS** decoded strictly without
  BOM, replacement characters or mojibake markers; affected root PHP files also
  render UTF-8 successfully in the HTTP/SEO checks. French official titles preserved.
- Separate live pricing checks: **33 public pages passed** (Home, Courses, Categories,
  all 24 course details and six category listings), including JSON-LD inspection.
- Real seven-table schema/data and media comparison: **unchanged**.

Private result artifacts: `phase1-results.json`, `phase1-public-pricing.json`,
`phase1-database-verification.json`, `phase1-file-checks.json`, browser screenshots.
Test credentials were generated for the disposable database and never printed.

## 19. Documentation

CMS.md explains current workflows, JSON preservation, lifecycle, saved previews,
seed-versus-catalogue, sessions and dormant pricing. ARCHITECTURE.md records the
current service boundaries. AGENTS.md, DESIGN.md and BRAND_GUIDELINES.md explicitly
mark old currency/price rules as superseded by the latest management requirement.
Historical audit narratives remain labelled historical. The inventory lists every
remaining matching application/reference/documentation file and its classification.

## 20. Phase 2 deferred

Events migration, Partners migration, Media Library database, Site Settings and
Contact Inbox were not started. No framework or runtime dependency was added.
No owner action is required to activate these PHP changes on localhost. Future
static publishing requires a fresh export and separate deployment validation.

## PRICING REMOVAL REPORT

1. **Components found:** `includes/currency.php`, `course-store.php`, `catalog.php`,
   `course-card.php`, `header.php`, `course.php`, `courses.php` (fees wording),
   `admin/course-edit.php`, `course-preview.php`, `session-edit.php`, `sessions.php`,
   `assets/js/main.js`, `assets/css/style.css`; QA scripts, immutable seed,
   `database/cms.sql`, policy/history docs and old generated Cloudflare files.
   Unrelated `data/venues.php` / `includes/venue-hire.php` were inspected and retained.
2. **Public:** badges, fact/Price branch, multi-session amounts and header selector
   removed at rendering level. No replacement sales/quotation wording was added.
3. **Course Admin:** general price, historical source amount/replacement/removal
   controls and pricing help removed. Preview no longer renders amounts.
4. **Session Admin:** amount field, list amount and conversion explanation removed.
5. **Session handling:** INSERT/UPDATE omit the old column; active reads project
   only supported columns. No amount parsing, validation or calculation remains.
6. **Course JSON:** legacy keys excluded by courseRecord(), retained in raw storage
   and preserved by allowlisted updates. New course defaults contain no amount keys.
7. **Currency:** helper deleted, requires/calls, JS selector/localStorage listeners
   and exclusive CSS removed. No unrelated helper consumer existed. Venue rates
   already use independent rendering and remain unchanged. An old localStorage
   value may persist harmlessly but is no longer read or written by the application.
8. **SEO:** no price/Offer schema was present to remove. Existing Course/Organization/
   WebPage/Breadcrumb metadata retained; output tested for forbidden price keys.
9. **Database:** price_djf and three historical JSON keys retained inactive. Physical
   deletion was unnecessary and would destroy historical information. No migration.
10. **References:** immutable seed, historical importer/schema, documentation, guarded
    preservation tests, private backups and old static export intentionally retained.
    [Complete occurrence classification](CMS-PHASE1-PRICING-INVENTORY.md):
    A 3 files, B 44, C 4, D 12, E 0 (mixed B/C counted twice). Generated export
    accounts for 42 historical files. **The old static export still contains prices;
    it was not regenerated or deployed. E=0 refers to the active PHP/MySQL site.**
11. **Tests:** public source/DOM/schema absence, Admin input/preview absence, creation
    and publication without prices, ignored price payloads, legacy values preserved,
    no active conversion/selector, all course/category pages and mobile checks.

## Explicit TRUE / FALSE confirmations

Results refer to the current local PHP/MySQL application and tested scope, not an
older static deployment. Preservation is established by exact snapshot comparison.

### General

| Requirement | Result |
|---|---|
| Existing administrator account preserved | TRUE |
| Original 24 Courses preserved | TRUE |
| Original 8 Sessions preserved | TRUE |
| Catalogue not limited to 24 | TRUE |
| New Courses created entirely through Admin | TRUE |
| Published Courses automatically feed public architecture | TRUE |
| Courses can be unpublished | TRUE |
| Safe Course deletion exists | TRUE |
| Dependent sessions prevent accidental Course deletion | TRUE |
| Course edits preserve hidden data | TRUE |
| Training Sessions remain separate from Courses | TRUE |
| Articles remain synchronized | TRUE |
| Public design unchanged except approved pricing removal | TRUE |
| Admin header follows public visual identity | TRUE |
| No page-level overflow at tested widths | TRUE |
| No test/fake content remains in application databases | TRUE |
| No business information invented | TRUE |
| No Git commands executed | TRUE |

### Pricing

| Requirement | Result |
|---|---|
| No Course price displayed publicly | TRUE |
| No Training Session price displayed publicly | TRUE |
| Admin cannot enter/edit Course prices | TRUE |
| Admin cannot enter/edit Session prices | TRUE |
| Course creation requires no price | TRUE |
| Session creation requires no price | TRUE |
| No Course/Training currency selector remains visible | TRUE |
| No Course/Training conversion remains active | TRUE |
| No Course/Training price in structured SEO data | TRUE |
| Historical compatibility pricing has no active effect | TRUE |
| Existing Courses intact after retirement | TRUE |
| Existing Sessions intact after retirement | TRUE |
| No unrelated functionality broken in tested scope | TRUE |
| No fabricated replacement pricing/contact text | TRUE |

Phase 1 ends here.
