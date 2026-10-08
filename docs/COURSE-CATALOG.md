# Current Courses architecture

Reference inspected: [official homepage](https://salaamcenter.net/), [All Courses](https://salaamcenter.net/courses/), [Categories](https://salaamcenter.net/collections/).

Courses opens All Courses and Categories. courses.php reads the current published MySQL catalogue (24 original courses, with no operational count limit); categories.php shows six category image/name cards; category.php validates a category slug and renders its courses server-side. All destinations work without JavaScript. Homepage retains six featured courses.

One shared card: image and linked title below. All richer metadata remains on course.php. Course/session pricing and currency conversion are retired from public/admin interfaces. Historical source amounts remain inactive reference data. See CMS.md for the current staff workflow.

## Category assignments

Existing recorded official categories are retained where available. The remaining courses are mapped by subject into the existing six families, as requested; these are editorial mappings, not a claim that the legacy site explicitly classified every new course. PMP, administration and leadership use Capacity Development & Languages; HR uses Human Resource & Business Development; ACAMS uses Banking & Finance. No new official category was invented.

| Course | Category | Mapping basis |
| --- | --- | --- |
| Pratique de l’Analyse Financière | Banking & Finance | Closest subject match to existing system |
| Microsoft POWER BI | Information Technology | Closest subject match to existing system |
| Comptabilité Pratique | Banking & Finance | Closest subject match to existing system |
| IOSH Managing Safely | Safety & Security | Closest subject match to existing system |
| Pratique de l’Audit Interne | Banking & Finance | Closest subject match to existing system |
| PMP - Project Management Professionals | Capacity Development & Languages | Closest subject match to existing system |
| Droit de travail et gestion Ressources humaines | Human Resource & Business Development | Closest subject match to existing system |
| CISCO Certified Network Associate | Information Technology | Existing official-page category |
| AI Security Essentials | Information Technology | Closest subject match to existing system |
| Certification ACAMS | Banking & Finance | Closest subject match to existing system |
| Certification ICA — Compliance | Banking & Finance | Existing official-page category |
| Banquier Islamique Certifié | Islamic Banking & Finance | Existing official-page category |
| SAGE 100 Comptabilité | Banking & Finance | Existing official-page category |
| SAGE 100 RH & Paie | Human Resource & Business Development | Existing official-page category |
| Gestion Administrative | Capacity Development & Languages | Closest subject match to existing system |
| Technique de rédaction administrative | Capacity Development & Languages | Closest subject match to existing system |
| Maîtrise bureautique | Information Technology | Closest subject match to existing system |
| Service clientèle | Capacity Development & Languages | Existing official-page category |
| Anglais | Capacity Development & Languages | Existing official-page category |
| Français | Capacity Development & Languages | Existing official-page category |
| Anglais pratique pour les Professionnels | Capacity Development & Languages | Closest subject match to existing system |
| Sécurité des Réseaux avec Fortigate | Information Technology | Existing official-page category |
| Maîtrise Word et Excel | Information Technology | Existing official-page category |
| Leadership et Management | Capacity Development & Languages | Closest subject match to existing system |

## Research limitations

Official pages and indexed official-site content were reviewed during this task. Direct HTTP access returned 403/503 on several pages. Existing documented matches were retained, but unavailable pages were not claimed as freshly verified. Searches did not establish additional reliable matches for the current titles. Administrative writing is present in the official listing, but a usable matching detail page was not verified.

Price references: [Sage Accounting](https://salaamcenter.net/courses/sage-100-accounting/), [English](https://salaamcenter.net/courses/english-language-course/), [French](https://salaamcenter.net/courses/french-language-course/); the official [site listing](https://salaamcenter.net/events/) supplies the retained Payroll, Customer Service and Word/Excel USD amounts. These historical/reference amounts have no newer supplied price overriding them.

## Images

No new images were downloaded or generated during this restructuring. All 24 courses reuse local assets. User CCNA imagery and AI/Fortinet logos are preserved. Official images, original asset URLs, course assignments and the external Scott Graham document photograph (Unsplash License) are listed in [image provenance](../assets/images/courses/SOURCES.md). Some related-course images are illustrations, not proof of course equivalence.

## Intentionally omitted information

Unknown price: Gestion Administrative, Technique de rédaction administrative, Maîtrise bureautique, Anglais pratique pour les Professionnels, Sécurité des Réseaux avec Fortigate, Leadership et Management.

No confirmed description: Pratique de l’Analyse Financière, Microsoft POWER BI, Comptabilité Pratique, IOSH Managing Safely, Pratique de l’Audit Interne, PMP - Project Management Professionals, Droit de travail et gestion Ressources humaines, AI Security Essentials, Certification ACAMS, Gestion Administrative, Technique de rédaction administrative, Maîtrise bureautique, Anglais pratique pour les Professionnels, Leadership et Management.

Missing training languages, durations, session dates, levels, requirements and exam information remain absent field by field. Current user values override historical durations and formats. ACAMS qualification and Practical/Simplified Accounting equivalence remain unconfirmed. No old yearless session dates or certification claims were inferred.


## Files changed in architecture correction

- Created: categories.php, category.php, data/categories.php.
- Modified: index.php, courses.php, course.php, includes/header.php, includes/course-card.php, includes/catalog.php, data/courses.php, assets/css/style.css, assets/js/main.js and this report.
- Existing images reused for category illustrations; no new image license claims.
- AGENTS.md already contains the permanent architecture rule in section 44; left unchanged.
- No Git operations or unrelated homepage sections changed.

## Architecture QA

- Eleven PHP files pass syntax checks. All 33 public page variants load, including six categories and 24 details; invalid category/course queries return 404.
- Exactly 24 courses in supplied order, six featured courses, six categories. All local images and internal links checked.
- Cards contain image, existing USD badge and linked title only; badge geometry checked at the bottom-left of the image.
- Edge tested at 1440, 768, 390 and 320 px: no horizontal overflow; category pages, detail pages, homepage and catalogue render correctly.
- Courses disclosure, Escape handling, mobile menu and Hero autoplay pass. Categories and navigation work without JavaScript; no JavaScript exceptions observed.
- Desktop catalogue and mobile category screenshots inspected. All source prices and conversion code preserved.
