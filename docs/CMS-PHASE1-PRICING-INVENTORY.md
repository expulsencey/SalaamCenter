# Phase 1 pricing occurrence inventory

Second search, 7 October 2026. Application source, data, SQL, scripts, documentation
and the old generated export were inspected. Line numbers refer to this final scan.
Private configuration is excluded from output; private backups remain historical
snapshots. Vendor packages, caches and browser profiles are development artifacts,
not Course/Training application code. No third-party package was modified.

A = required unrelated functionality; B = historical migration/reference/export;
C = dormant compatibility and its protective validation; D = documentation;
E = missed active Course/Training functionality.

The pre-change components and their removal are listed in CMS-PHASE1-VALIDATION.md.
The old cloudflare-dist snapshot still contains historical prices. It is not used
by the active WAMP PHP site. No claim is made that a previously deployed static
site changed. Re-export from current source before any future deployment.

| File | Class | Matching lines | Explanation |
|---|---|---|---|
| `AGENTS.md` | D | 2, 3, 216, 229, 256, 316, 318, 320, 324, 380, 381, 429, 452, 1182, 1184, 1185, 1186, 1187, 1188, 1189, 1190, 1191, 1192, 1193, 1194, 1292, 1316, 1319, 1335, 1338, 1345, 1360, 1386, 1430, 1450, 1709, 1710, 1711, 1718, 1731 | Documentation / historical validation, superseded by current policy. |
| `cloudflare-dist/index.html` | B | 56, 103, 114, 125, 136, 147, 158 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `data/courses.php` | B | 4, 20, 21, 22, 51, 52, 53, 81, 82, 83, 113, 114, 115, 142, 143, 144, 171, 172, 173, 200, 201, 202, 229, 230, 231, 270, 271, 272, 301, 302, 303, 333, 334, 335, 377, 378, 379, 425, 426, 427, 470, 471, 472, 519, 520, 521, 548, 549, 550, 577, 578, 579, 606, 607, 608, 650, 651, 652, 694, 695, 696, 742, 743, 744, 771, 772, 773, 809, 810, 811, 855, 856, 857 | Immutable historical seed/reference. |
| `data/home.php` | A | 15 | Ordinary service wording or unrelated venue rental rates. |
| `data/venues.php` | A | 2 | Ordinary service wording or unrelated venue rental rates. |
| `database/cms.sql` | C | 53 | Dormant compatibility column retained. |
| `docs/ARCHITECTURE.md` | D | 10, 11, 24, 25, 27, 28, 48, 55, 63, 66, 67, 160, 204, 210, 219, 220, 221, 222, 243, 272, 287, 322, 324, 397, 398, 463, 495, 540, 556 | Documentation / historical validation, superseded by current policy. |
| `docs/BRAND_GUIDELINES.md` | D | 2, 3, 62, 230, 298, 353 | Documentation / historical validation, superseded by current policy. |
| `docs/CLOUDFLARE_DEPLOYMENT.md` | D | 68, 76 | Documentation / historical validation, superseded by current policy. |
| `docs/CMS-EXPANSION-VALIDATION.md` | D | 26, 33, 34, 46, 51, 60, 62, 149 | Documentation / historical validation, superseded by current policy. |
| `docs/CMS.md` | D | 66, 67, 68, 70, 71, 76, 121, 135, 138, 145, 146, 150, 151, 152, 153, 154, 155, 156, 193, 214, 283 | Documentation / historical validation, superseded by current policy. |
| `docs/COURSE-CATALOG.md` | D | 7, 44, 52, 71, 74 | Documentation / historical validation, superseded by current policy. |
| `docs/DESIGN.md` | D | 2, 3, 154, 164 | Documentation / historical validation, superseded by current policy. |
| `docs/HOMEPAGE.md` | D | 7, 73 | Documentation / historical validation, superseded by current policy. |
| `docs/SEO.md` | D | 12, 48, 80, 103, 174, 197 | Documentation / historical validation, superseded by current policy. |
| `includes/course-store.php` | B/C | 23, 84, 89, 90, 94 | Legacy import only; explicit exclusion from active projection. |
| `includes/venue-hire.php` | A | 24 | Ordinary service wording or unrelated venue rental rates. |
| `scripts/check-cms-courses.py` | C | 77, 87, 89, 90, 92, 93, 94, 95, 96, 98, 215, 217 | Tests verify retired fields cannot affect active application. |
| `scripts/check-seo-browser.py` | C | 68 | Tests verify retired fields cannot affect active application. |
| `docs/design-review/README.md` | D | 9 | Documentation / historical validation, superseded by current policy. |
| `docs/design-review/validation.json` | D | 889, 890, 915, 921 | Documentation / historical validation, superseded by current policy. |
| `cloudflare-dist/about/index.html` | B | 56 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/categories/index.html` | B | 56 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/contact/index.html` | B | 56 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/courses/index.html` | B | 56, 66, 75, 86, 97, 108, 119, 130, 141, 152, 163, 174, 185, 196, 207, 218, 262, 273, 284, 317 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/events/index.html` | B | 56, 90 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/partners/index.html` | B | 56 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/sc-business/index.html` | B | 56 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/events/administrative-management-training/index.html` | B | 56 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/events/strategic-commercial-development-ports/index.html` | B | 56 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/courses/acams/index.html` | B | 56, 74 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/courses/administrative-management/index.html` | B | 56 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/courses/administrative-writing/index.html` | B | 56 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/courses/ai-security-essentials/index.html` | B | 56, 74 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/courses/ccna/index.html` | B | 56, 87 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/courses/customer-service/index.html` | B | 56, 87 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/courses/english-course/index.html` | B | 56, 91 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/courses/financial-analysis/index.html` | B | 56, 74 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/courses/fortigate/index.html` | B | 56 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/courses/french-course/index.html` | B | 56, 91 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/courses/human-resources/index.html` | B | 56, 74 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/courses/ica/index.html` | B | 56, 91 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/courses/internal-audit/index.html` | B | 56, 74 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/courses/iosh-managing-safely/index.html` | B | 56, 74 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/courses/islamic-banker/index.html` | B | 56, 91 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/courses/leadership-management/index.html` | B | 56 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/courses/office-skills/index.html` | B | 56 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/courses/pmp/index.html` | B | 56, 74 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/courses/power-bi/index.html` | B | 56, 74 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/courses/practical-accounting/index.html` | B | 56, 74 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/courses/professional-english/index.html` | B | 56 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/courses/sage-100-accounting/index.html` | B | 56, 91 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/courses/sage-100-payroll/index.html` | B | 56, 91 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/courses/word-excel/index.html` | B | 56, 87 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/categories/banking-finance/index.html` | B | 56, 71, 82, 93, 104, 115, 126 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/categories/capacity-development-languages/index.html` | B | 56, 71, 104, 115, 126 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/categories/human-resource-business-development/index.html` | B | 56, 71, 82 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/categories/information-technology/index.html` | B | 56, 71, 82, 93, 126 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/categories/islamic-banking-finance/index.html` | B | 56, 71 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/categories/safety-security/index.html` | B | 56, 71 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/assets/css/style.css` | B | 526, 904, 905, 906, 908 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |
| `cloudflare-dist/assets/js/main.js` | B | 214, 215, 217, 218, 219, 220, 221, 222, 223, 224, 226, 230, 231, 232, 236, 239, 241, 243 | Old generated static export, not rebuilt or deployed; not used by PHP/MySQL routes. |

File classifications (mixed B/C counted in both): {"A": 3, "B": 44, "C": 4, "D": 12, "E": 0}.

**E = 0 in the active PHP/MySQL application.** The retained generated export is explicitly historical.

This inventory and CMS-PHASE1-VALIDATION.md themselves belong to D; they are not recursively counted in the scan totals.
