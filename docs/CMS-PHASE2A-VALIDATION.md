# CMS Phase 2A — Dashboard and navigation validation

Completed in Codex Cloud on 8 October 2026, on the provided `work` task branch.
Scope: shared administration layout, navigation, dashboard, branding and responsive
usability. Changes are ready for review and local WAMP validation. Phase 2B was not
started. This report covers this run; earlier Phase 1/2 reports describe their own
local validation, not access to the owner's database from Cloud.

## 1. Existing components reused

Reused `admin/_layout.php`, `admin/_fields.php`, `cmsRequireUser()`, `cmsToken()`,
`cmsCheckCsrf()`, `cmsDatabase()`, `courseStoreReady()`, `courseToday()`, badges,
UTC/date formatting and the existing native Menu disclosure. Kept every admin
route and its back-to-list/preview links. Course/session/article editors, media
management, public templates, SEO services and database structure are unchanged.
No framework or application dependency was added.

## 2. Dashboard improvements

The dashboard queries existing MySQL tables for total/published/draft courses,
total training sessions and published/draft articles. Related metrics link to
existing list filters. Tests started with zero records, then used 27 synthetic
courses (25 published, two drafts), eight sessions and three articles; adding one
more published course immediately changed the total to 28 and published count to
26. None of these are hardcoded application counts or owner business records.

Quick actions remain Add course, Add training session and New article. Recently
updated courses and recent articles now have separate lists, each limited to four
records with deterministic timestamp/ID ordering. The next three eligible training
sessions reuse the existing published-course, upcoming-status and date eligibility
rules, including today's date in Africa/Djibouti. Dates/statuses remain readable.

Empty lists have guidance and creation links. Content loading failure returns 503
and a generic alert, with no overview counters, recent records or schedule shown.
It does not suggest rerunning migrations or misrepresent an error as zero data.
Quick-action links and the navigation remain available. No prices or analytics
were introduced. Events & Partners retain their existing read-only navigation route.

## 3. Navigation improvements

One reusable shell provides a desktop sidebar and a tablet/mobile Menu. Active
sections are indicated with `aria-current="page"`, including course/article/session
editors and previews. The closed mobile Menu names the current section. Desktop
starts above 1024 px; narrower views use the disclosure in normal document flow,
so it does not overlay forms or need a modal focus trap.

View website and POST/CSRF-protected Log out stay easy to find before account
information. The authenticated display name/email are escaped and long text wraps.
The shared shell obtains account information through the existing authentication
service; no account editor, password change or new endpoint was added.

Keyboard Enter/Tab/Escape, focus outlines, resize focus restoration, comfortable
44+ px navigation/dashboard action targets and native operation without JavaScript
were verified. The skip link targets the focusable main landmark. Existing editor
JavaScript is unchanged apart from the independent navigation initialization.

## 4. Branding improvements

Preserved the exact `assets/images/logo/salaam-center-logo.webp` asset and natural
aspect ratio. The logo sits on white without recoloring, cropping, filters or
shadows. Administration uses the existing blue/cyan/navy, white and neutral CSS
variables and local Poppins fonts. Three grouped overview surfaces replace six
separate metric cards; activity lists use separators and whitespace. No gradients,
new public styles, asset changes or decorative dashboard graphics were added.

## 5. Responsive results

Final isolated suite: **539 assertions passed**, **78 admin route/viewport
combinations**, **78 ordinary public page/viewport combinations**, **zero JavaScript
exceptions**. These totals exclude the two failing public stress cases below.

| Width | Admin combinations | Ordinary public combinations | Result |
| --- | ---: | ---: | --- |
| 1440 px | 13 | 13 | Passed |
| 1366 px | 13 | 13 | Passed |
| 1024 px | 13 | 13 | Passed |
| 768 px | 13 | 13 | Passed |
| 390 px | 13 | 13 | Passed |
| 320 px | 13 | 13 | Passed |

Admin coverage: Dashboard, Courses, new/existing course editor, Training Sessions,
new/existing session editor, Articles, new/existing article editor, Media,
Events & Partners and Login. HTTP checks additionally covered course/article
previews, filtered destinations and Logout. Browser checks verified page overflow,
one H1, visible images, fonts, logo proportions and runtime exceptions. Dashboard
screenshots were captured at all six widths; desktop/mobile images were inspected
for readable spacing, wrapping and navigation. Synthetic long account/course text
was escaped and the admin pages stayed within the viewport.

Tested using headless Chromium in Linux. Firefox, Safari, physical devices and
production HTTPS behavior were not run. This is functional accessibility coverage,
not a formal WCAG certification.

## 6. Security regression

Verified anonymous redirects for protected pages, login CSRF rejection, successful
login/session rotation, generic safe setup failure, escaped account text, private
headers (`noindex`, `no-store`, frame denial and self-only CSP), draft course/article
404s and sitemap exclusion. Logout rejects GET/missing CSRF; valid POST ends the
session and replay cannot access the dashboard. Private configuration, schema,
setup scripts and storage paths were denied by the isolated QA router.

Core authentication, session, password, upload, database and content service files
are unchanged. No real administrator was used, recreated or reset. A randomly
credentialed synthetic QA account existed only in the disposable database and was
removed. This phase did not rerun the older QA scripts that perform migrations or
historical course imports, nor the Phase 2 suite requiring a private owner snapshot.

## 7. Public website regression and limits

Verified Home, About, Courses/detail, Categories/listing, Events/detail, Partners,
SC Business, Contact, Blog and Article against the isolated synthetic data. Ordinary
routes returned 200 with existing canonicals/JSON-LD; draft detail URLs returned
404 and were excluded from the sitemap. Course/session pricing controls and pricing
structured data remain absent. Venue rates are unchanged. No contact submission was
made. Public PHP/templates, CSS/JS, images, SEO, data and Cloudflare files compare
unchanged against the starting checkout.

**Separate failing stress checks:** a synthetic course name containing a single
180-character unbroken word overflows the existing public course detail page at
1440 px (document width 4365 px) and 320 px (document width 2872 px). The admin pages
handle that fixture. The public detail template/stylesheet are unchanged, so this is
an existing public wrapping limitation, not a Phase 2A regression. It is reported
separately rather than counted as a passing responsive check. No public redesign or
out-of-scope fix was made. The 78 ordinary public combinations use another synthetic
course with a normal spaced title; listings/Home also include the stress record.

The owner's WAMP database and its 25 published courses/eight sessions/account were
not accessed. Actual owner data and PHP 8.3/WAMP acceptance remain local validation
steps. Cloud testing used PHP 8.4.26, MariaDB 11.8.6 and Chromium; application queries
retain the existing MySQL JSON/query patterns. No compatibility change was required.

## 8. Reproducible QA and preservation

Added `scripts/check-cms-dashboard.py`. It requires an explicitly configured empty
`cms_phase2a_<12 hex characters>` database with the existing table definitions.
It refuses other database identities or populated tables before inserting fixtures.
It does not provision/change schema, call setup migrations/imports, reset accounts
or connect to the owner's database. Its finally block removes its synthetic rows and
stops only its own PHP server. Read-only UI/HTTP/browser checks were compared against
all six fixture tables; rows were unchanged. Table definitions were compared excluding
only insert-generated AUTO_INCREMENT counters and remained identical.

For this Cloud run, the disposable empty table definitions were copied from the
already-provisioned isolated onboarding QA database, without application migrations,
imports or schema alterations. The temporary database/configuration were removed
after validation. Future runs must pre-provision their own empty disposable instance;
never point this command at the owner's local configuration.

```sh
# Only after an empty disposable QA database is independently prepared.
SALAAM_CMS_CONFIG=/path/to/private/disposable-config.php \
  python scripts/check-cms-dashboard.py --php /path/to/php --browser \
  --chromium /path/to/chromium
```

Without `--browser`, only HTTP/database assertions run; browser checks are unrun,
not implicitly passed. QA needs requests/beautifulsoup4; browser mode also needs
Playwright and Chromium. These are test tools, not new site dependencies. Private
results/logs/screenshots remain ignored under `storage/cms-qa/phase2a/`; no credentials
are committed. Final syntax checks: **17 admin PHP files passed**, JavaScript syntax
passed, Python QA compilation passed. Changed/new files decode strictly as UTF-8
without BOM/replacement characters; `git diff --check` passed.

## 9. Files modified

- `admin/_layout.php` — shared branding/navigation/account shell.
- `admin/index.php` — dynamic dashboard, separate activity lists and safe empty/error states.
- `assets/css/admin.css` — admin sidebar, responsive navigation and grouped dashboard layout.
- `assets/js/admin.js` — navigation breakpoint and focus handling only.
- `docs/CMS.md` — current staff navigation/dashboard guide.

## 10. Files created

- `scripts/check-cms-dashboard.py` — isolated synthetic functional/browser QA.
- `docs/CMS-PHASE2A-VALIDATION.md` — this reviewable report.

Ignored temporary QA configuration, router, session/log files and screenshots were
created for testing. They are not application code or published content. No logo,
public template, editor template, media feature, schema, lockfile or secret was added
or changed. No commit, push, merge, export or deployment was performed in Phase 2A.

## 11. Remaining work for Phase 2B

Phase 2B was not started. Any future course/article editor redesign or media workflow
change requires its own scoped task. Local acceptance should verify the existing
owner's course totals, navigation and dashboard on PHP 8.3/WAMP without migrations,
imports or account resets. Cross-browser/device acceptance remains separate. The
public unbroken-title wrapping issue requires a separate public-site fix if desired.

## Explicit TRUE / FALSE confirmations

| Requirement | Result |
| --- | --- |
| Official logo preserved | TRUE |
| Existing CMS architecture preserved | TRUE |
| No database schema changes | TRUE; only existing definitions in disposable QA |
| No real business data modified | TRUE; no owner database access |
| No prices introduced | TRUE |
| Authentication preserved | TRUE |
| Dashboard uses dynamic data | TRUE |
| Admin navigation responsive | TRUE at all six requested widths |
| Public website preserved | TRUE; code unchanged, ordinary regression checks passed; stress limit above |
| No Cloudflare deployment | TRUE |
| No direct push to main | TRUE; no push of any kind |

Ready for local validation: **TRUE**. Validated against the owner's WAMP data: **FALSE**.
Phase 2A ends here.
