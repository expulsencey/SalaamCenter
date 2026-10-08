# Administration UI correction — 7 October 2026

## Findings and correction

1. **Oversized logo:** the old HTML declared the original 2271 × 761 dimensions.
   Its display size depended entirely on the current admin stylesheet. Missing or
   stale styling therefore allowed a viewport-wide image. The inspected local CSS
   returned HTTP 200 and contained constraints, so the owner's exact oversized state
   was not reproduced; a stale browser stylesheet is a hypothesis, not a proven cause.
   HTML now declares 215 × 72, and the shared public logo constraints preserve its ratio.
2. **Overflow:** the intrinsic logo size was an overflow risk when CSS was absent.
   No page overflow was reproduced with the fresh original CSS. The replacement uses
   the public bounded container, flexible children with zero minimum width and a
   mobile menu at the public breakpoint. No body overflow-hiding workaround was used.
3. **Public references:** inspected `includes/header.php`, `includes/head.php`, and
   the actual header/navigation/reset/responsive rules in `assets/css/style.css`.
4. **Reused styles:** admin loads the unchanged public stylesheet and directly uses
   `site-header`, `header-content`, `container`, `site-logo`, `navigation-list` and
   `navigation-link`, together with the existing palette and font variables.
   Poppins 400/500/600/700 Latin fonts are locally hosted with their SIL license;
   the admin Content Security Policy remains unchanged and self-only.
5. **Header:** replaced the unrelated sidebar with the public horizontal header
   proportions, CMS-specific links, active states, View website and Log out.
   Every existing admin screen continues using the same `_layout.php`.
6. **Dashboard:** six compact metrics in three columns, reduced section spacing,
   immediate quick actions and compact recent-content rows. At 1366 × 768 the header,
   title/welcome, actions, all six metrics and several recent rows are visible.
7. **Responsive:** native collapsible Menu at the public 68rem breakpoint, Escape
   support, usable touch targets, two-column mobile metrics and stacked recent rows.
   Existing course/session/article lists wrap; forms retain responsive columns.
8. **Assets:** CSS, JS and logo paths returned HTTP 200 before correction; no incorrect
   relative path was found. Admin CSS/JS previously lacked cache versioning. Added
   file-based versions as on the public site, plus a versioned shared stylesheet.
   Browser tests verified actual loaded CSS rules, not just link elements. WAMP's
   `/SalaamCenter/admin/login.php` returns the new versioned references and markup.
9. **Files:** modified `admin/_layout.php`, `admin/index.php`, `assets/css/admin.css`,
   `assets/js/admin.js`, `scripts/check-cms-courses.py`, `docs/CMS.md` and
   `docs/ARCHITECTURE.md`. Added this report and `assets/fonts/poppins.css`, four
   `poppins-latin-{400,500,600,700}.woff2` files and `Poppins-OFL.txt`.
10. **Validation:** 215 integration assertions passed, including 11 admin routes at
    six viewports: 1920 × 1080, 1440 × 900, 1366 × 768, 1024 × 768, 768 × 1024,
    390 × 844. Dashboard screenshots at all six widths were visually inspected,
    together with desktop/mobile course lists, editor and mobile menu. Public/admin
    header measurements were compared at every width. Desktop logo is about 215 × 72
    and header 102px; mobile logo about 167 × 56 and header 98px, matching the public
    header. Centered alignment differs only when one page has a vertical scrollbar.

Functional tests ran through authenticated forms against a disposable catalogue copy;
the screenshots therefore contain clearly marked QA records. That database and its
uploads were removed. The real catalogue remains at 24 courses with no source-field
differences. Private fingerprints confirm the administrator, articles and contact
records are unchanged. PHP lint and UTF-8 checks passed. No backend/schema/repository,
public stylesheet, public navigation or public JavaScript was modified.

Screenshots and comparison measurements are under `storage/cms-qa/`, including
`dashboard-1366.png`, `dashboard-390.png`, `admin-menu-390.png` and
`header-comparison.json`. They are private QA artifacts, not public website content.

## Required confirmations

| Statement | Result |
|---|---|
| Admin header follows the public Salaam Center header design | TRUE |
| Official logo asset preserved | TRUE |
| Logo dimensions constrained correctly | TRUE |
| No page-level horizontal scrolling at tested widths | TRUE |
| Dashboard appropriately compact at 1366 × 768 | TRUE |
| Existing CMS functionality intact | TRUE |
| Existing administrator account intact | TRUE |
| Public website not redesigned | TRUE |
| No Git commands executed | TRUE |
