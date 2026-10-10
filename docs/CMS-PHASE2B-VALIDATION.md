# CMS Phase 2B validation

Completed locally on 10 October 2026. Scope: reliable course saving, five-step course editor and verified session usability defects.

## Problems identified

- Sequential validation could stop before later submitted fields/lists were collected. The error page then showed old/default values, losing the administrator's pending input.
- An existing uploaded image took precedence in the redisplayed picker after a failed edit, hiding the administrator's new selection.
- Stale update errors rendered the latest database version into the retry form, allowing a subsequent retry to overwrite intervening edits.
- Replaying a new-course POST could generate another unique slug and create a duplicate. Undated draft sessions had a similar repeated-submission risk.
- Existing validation text did not identify which scalar field or list exceeded its limits. A failed uploaded-file submission did not explain that the file needed reselection.

No missing SQL mapping was found: all exposed scalar fields, five repeatable lists, image references and presentation flags persist in the existing structure. Hide/republish, badges, filters and session statuses already worked and were preserved.

## Corrections

- Restore submitted text, lists, selected image, checkbox and order after validation errors, with output escaping. Preserve original submitted versions after stale-edit rejection.
- Identify the offending field/list in length validation. Explain file reselection after unsuccessful uploads. Keep saved images intact.
- Session-bound creation keys prevent replayed inserts; a browser submission guard avoids repeated sends without disabling the selected publish/draft action's form value.
- Group the same course form into General information, Organization, Learning content, Images and presentation, Review and publication. Add progress, Previous/Next, step navigation and an escaped text review. Save actions work throughout; no-JavaScript rendering keeps all sections visible.
- Reveal and focus the relevant step when browser validation fails. Preserve unsaved-change detection, deletion dialog and public synchronization.
- Apply the same entered-text/stale-version protection and creation replay protection to sessions. No new session fields or statuses.

## Files

Modified: `admin/course-edit.php`, `admin/session-edit.php`, `admin/_fields.php`, `assets/js/admin.js`, `assets/css/admin.css`, `scripts/check-course-deletion.py`, `docs/CMS.md`, `docs/ARCHITECTURE.md`.

Created: `scripts/check-cms-phase2b.py`, this report. Private backup and browser artifacts are under protected `storage/cms-qa/`.

## Validation results

`python scripts/check-course-deletion.py --phase2b --browser`: **131 checks passed** in a temporary isolated database with synthetic accounts/content. This reuses the deletion fixture rather than importing real records or duplicating test setup.

Coverage: create/edit, all scalar fields and five lists, presentation fields, failed-form retention, duplicate creation replay, uploaded image handling, stale updates, draft privacy, publish/hide/republish, existing status filters, session creation/edits/statuses, public catalogue/sitemap/detail synchronization, no public prices, and the unchanged permanent deletion workflow/security tests.

Browser checks: five steps at 1440, 1366, 768 and 390 pixels; one visible step, no horizontal overflow, Previous/Next, review values, browser-added list persistence, publication action preservation and reveal/focus of an invalid hidden field. Existing deletion dialog also passed its six viewport checks. No JavaScript exceptions or PHP warnings observed. Modified PHP files passed PHP 8.3.14 syntax checks. Modified sources remain UTF-8 without BOM.

An initial browser launch could not open the debug port inside the restricted environment. Launching the isolated headless browser with approved permissions resolved this; the full final run passed. No failing functional tests remain in that run.

## Data protection

The real database contained **24 courses, 8 sessions and 1 administrator at this task's baseline**, rather than the historical 25-course count. No reason for the prior count change is inferred. All seven real tables' rows and schema definitions, and all CMS media bytes, matched the private baseline after testing. No real record was deleted, reset, reimported or updated; no migration was needed.

## Limits

- Browsers cannot refill file inputs after a server-rendered validation error. Text and existing-image selection remain; an explicit message requests file reselection.
- Expired authentication/invalid CSRF remain protected by the existing access checks. Creation replay protection is scoped to the signed-in session and its most recent 100 creation forms per type.
- On an edit conflict, copy pending changes before reloading the latest version; automatic merging was not introduced.
- Browser checks used Edge; other browsers were not directly exercised. There is no new frontend dependency.

No public redesign, price feature, deployment, Cloudflare change, Git command or permanent deletion reimplementation.
