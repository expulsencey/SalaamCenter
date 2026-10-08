> Current policy ? 7 October 2026: the user explicitly retired all active Course
> and Training pricing. Earlier requirements below for USD/DJF, price badges,
> currency controls and preserving conversion are superseded. Preserve dormant
> historical amounts and the immutable seed; do not display or edit them. Venue
> rental rates remain unchanged. See [CMS workflows](docs/CMS.md) for details.

# Website language policy

- English is the default language for all current and future website pages and sections. Use `<html lang="en">`.
- Write all public interface text in English, including navigation, headings, descriptions, buttons, placeholders, metadata, messages, image descriptions, accessibility labels and JavaScript-generated text. Future administrative interfaces must also use English.
- Preserve official course names exactly as supplied by the user, including French names. Mark French titles with `lang="fr"` where appropriate.
- Display course durations in English by translating units only. Preserve every numerical value and the original meaning.
- Do not invent facts or change the visual design to accommodate this language policy.
- Apply this policy until the user explicitly requests a multilingual version.

# AGENTS.md — Salaam Center Website

## 1. Purpose

This file defines the permanent development rules for the Salaam Center
website.

Any coding agent working on this repository MUST read this file before
creating, editing, deleting, renaming, or moving files.

These instructions apply to the entire project unless a more specific
AGENTS.md exists in a subdirectory.

The goal is to build a clean, professional, maintainable and responsive
website for Salaam Center without introducing unnecessary complexity,
fabricated information, duplicated code, or regressions.

---

# 2. Project Location

The project root is:

C:\wamp64\www\SalaamCenter

Local development URL:

http://localhost/SalaamCenter/

The project runs locally with:

- Windows
- WAMP64
- Apache
- PHP

Never modify files outside the SalaamCenter project unless explicitly
requested by the user.

In particular, DO NOT modify:

C:\wamp64\

or other projects inside:

C:\wamp64\www\

Do not modify:

- Apache configuration
- PHP configuration
- WAMP configuration
- php.ini
- virtual hosts
- other WAMP projects

unless explicitly requested.

---

# 3. Technology Stack

The project intentionally uses a simple traditional web stack.

Allowed:

- HTML5
- CSS3
- Vanilla JavaScript
- PHP
- MySQL for authorized contact storage and CMS articles, courses and training sessions; additional modules require an explicit request

Do NOT introduce without explicit approval:

- React
- Next.js
- Vue
- Angular
- Svelte
- jQuery
- Bootstrap
- Tailwind CSS
- Laravel
- Symfony
- WordPress
- Node.js
- npm
- frontend build systems
- unnecessary third-party frameworks

Do not introduce a dependency simply because it makes a small feature
easier.

Prefer native browser features and simple PHP.

---

# 4. Main Project Architecture

Current/future architecture should remain understandable.

Preferred structure:

SalaamCenter/
│
├── AGENTS.md
├── index.php
├── course.php
│
├── data/
│   └── courses.php
│
└── assets/
    ├── css/
    │   └── style.css
    │
    ├── js/
    │   └── main.js
    │
    └── images/
        ├── logo/
        ├── hero/
        ├── courses/
        ├── about/
        ├── testimonials/
        └── partners/

Do not create folders or files without a clear purpose.

Do not create:

index.html

The homepage is:

index.php

---

# 5. Language Policy — Critical

The public website is ENGLISH-FIRST.

The entire user interface must be in English.

This includes:

- navigation
- headings
- buttons
- labels
- forms
- placeholders
- validation messages
- course metadata
- calls to action
- slider controls
- accessibility labels
- error messages
- success messages
- footer
- contact section
- mobile menu
- empty states

The root document must use:

<html lang="en">

Do not introduce French interface text.

## Exception: official names

Official course names supplied by Salaam Center must NOT be automatically
translated.

Examples:

Pratique de l’Analyse Financière

Gestion Administrative

Maîtrise Word et Excel

Banquier Islamique Certifié

These names may remain in French.

When appropriate, French titles may use:

lang="fr"

Example:

<h3 lang="fr">Pratique de l’Analyse Financière</h3>

However, surrounding metadata remains English:

Duration
Language
Price
Next Session
View Course
Course Description

---

# 6. Content Accuracy — Critical

NEVER invent business information.

Do not fabricate:

- course prices
- course durations
- session dates
- languages
- training formats
- levels
- instructors
- addresses
- phone numbers
- email addresses
- statistics
- partners
- accreditations
- certifications
- testimonials
- learning outcomes
- prerequisites
- examination requirements
- course contents
- company claims

If information is unknown, omit it.

Do NOT display fake values such as:

TBD
Unknown
0 DJF
Free
Coming Soon

unless the user explicitly requests them.

It is better to hide a field than to invent a value.

---

# 7. Source Priority

When course/business information conflicts, use the following priority.

## Priority 1 — User-provided current information

The most recent information directly supplied by the user is authoritative
for the project.

This includes current Salaam Center documents, posters, schedules and
explicit corrections.

## Priority 2 — Current official Salaam Center website

Official reference:

https://www.salaamcenter.net/

Use the official website for information that has not been superseded by
newer user-provided data.

It can be used for:

- course descriptions
- course categories
- learning outcomes
- prerequisites
- course contents
- certificates
- training information
- institutional information
- existing images

## Priority 3 — External sources

External sources should only be used when necessary.

Never allow external information to override confirmed Salaam Center
information.

---

# 8. Current Data Overrides Historical Website Data

The existing Salaam Center website may contain historical information.

Do NOT assume every value on the existing website is current.

If a recent user-provided document says:

Price: 38,000 DJF

while the existing website contains an older price:

the recent user-provided price wins.

The same rule applies to:

- price
- duration
- session date
- language
- training format
- course name when explicitly corrected

Old descriptions may still be reused when they clearly describe the same
course and have not been contradicted.

---

# 9. Course Data Architecture

Course information must have ONE source of truth.

Do NOT manually duplicate the same course information in:

index.php
course.php
and other pages.

After the authorized CMS migration, courses and training sessions use MySQL through
includes/course-store.php. The following file is retained only as an immutable
migration/reference source, not a second live catalogue:

data/courses.php

Homepage cards, course/category pages, details, SEO and sitemap read the same
database-backed course service. Never restore a silent file fallback after import.

Conceptually:

data/courses.php
        ↓
 ┌──────┴──────┐
 ↓             ↓
index.php    course.php
cards        details

This reduces inconsistencies.

---

# 10. Course Data Model

When applicable, a course can contain fields such as:

- slug
- title
- language
- category
- duration
- training_format
- level
- next_session
- price
- currency
- image
- description
- learning_outcomes
- course_includes
- certificate
- requirements
- exam_evaluation

Only populate fields supported by reliable information.

Missing optional fields should remain absent/null rather than fabricated.

---

# 11. Course URLs

Use stable, readable slugs.

Example:

course.php?slug=sage-100-accounting

course.php?slug=ccna

course.php?slug=ai-security-essentials

Never use the visible course title directly as an unsafe query parameter.

Validate the requested slug against the known course dataset.

If a course does not exist, handle it gracefully.

Do not expose PHP warnings or stack traces to visitors.

---

# 12. Course Cards

Homepage/course catalogue cards should remain concise.

A card may show:

- image
- category
- official course title
- duration
- next session when confirmed
- price when confirmed
- View Course link

Do NOT place the entire course description on homepage cards.

Detailed information belongs on the course detail page.

Keep card heights visually consistent.

---

# 13. Course Detail Pages

Detailed course pages should be able to show, when available:

- course image
- category
- official title
- duration
- language
- training format
- level
- next session
- price
- course description
- learning outcomes
- course includes
- certificate information
- requirements
- exam/evaluation
- contact/enquiry CTA

If a field is unavailable, hide the section.

Do not display an empty heading.

---

# 14. Images

Every course should ideally have a clean visual.

Image priority:

1. image supplied by the user
2. appropriate official Salaam Center image
3. appropriately licensed professional external image
4. clean local placeholder when no suitable image is available

Never use:

- watermarked images
- obviously copyrighted images copied without permission
- blurry thumbnails
- stretched images
- unrelated images
- random logos as photographic backgrounds
- broken image URLs

Do not treat Google Images itself as an image license/source.

External images must come from an identifiable source with appropriate
usage rights.

Prefer downloading approved project images locally rather than
hotlinking remote images.

Store course images in:

assets/images/courses/

Use meaningful filenames.

Good:

sage-100-accounting.jpg
customer-service.jpg
ccna.jpg

Bad:

IMG123.jpg
image1.jpg
photo-final-final2.jpg

Use appropriate alt text.

---

# 15. Image Rendering

Images must never be distorted.

Prefer:

object-fit: cover;

where appropriate.

Maintain consistent card image ratios.

Avoid unnecessary huge image files.

Do not upscale tiny images aggressively.

Use modern formats when appropriate and supported.

---

# 16. Visual Identity

The new website should preserve Salaam Center's recognizable identity
while modernizing the experience.

Desired character:

- professional
- institutional
- modern
- elegant
- premium but restrained
- spacious
- readable
- trustworthy

Avoid:

- excessive gradients
- huge shadows
- gaming-style effects
- excessive animation
- random colors
- excessive border radius
- generic startup appearance
- inconsistent components

---

# 17. Design System

Reuse existing CSS variables.

Do not introduce slightly different versions of the same colors
throughout the stylesheet.

Prefer a centralized system such as:

:root {
    --color-primary: ...;
    --color-secondary: ...;
    --color-accent: ...;
    --color-background: ...;
    --color-surface: ...;
    --color-text: ...;
    --color-text-light: ...;
    --color-border: ...;
}

Only label colors as official if they were actually verified.

Reuse consistent:

- colors
- typography
- spacing
- buttons
- borders
- shadows
- containers
- breakpoints
- card styles

---

# 18. CSS Rules

Primary stylesheet:

assets/css/style.css

Do not add inline CSS unless technically unavoidable.

Do not duplicate existing selectors unnecessarily.

Before adding a new CSS rule:

1. inspect existing styles;
2. determine whether an existing component can be reused;
3. avoid specificity wars;
4. avoid !important unless genuinely necessary.

Organize CSS into logical sections.

Example:

/* Variables */
/* Reset */
/* Base */
/* Layout */
/* Top Bar */
/* Header */
/* Navigation */
/* Hero */
/* Courses */
/* About */
/* Statistics */
/* Services */
/* Testimonials */
/* News */
/* Partners */
/* Contact */
/* Footer */
/* Utilities */
/* Responsive */

Do not rewrite the entire stylesheet to add one component.

---

# 19. JavaScript Rules

Primary JavaScript:

assets/js/main.js

Use Vanilla JavaScript.

Keep features separated logically.

Preferred organization:

initMobileNavigation()
initHeroSlider()
initCoursesSlider()
initScrollReveal()

Do not duplicate event listeners unnecessarily.

Do not overwrite existing working features when adding a new feature.

Check that DOM elements exist before attaching behavior.

A JavaScript error in one optional component must not break the entire
homepage.

Avoid unnecessary global variables.

---

# 20. Animation Rules

Animations must support the design, not dominate it.

Allowed examples:

- subtle fade
- small translateY reveal
- subtle image scale on hover
- smooth carousel movement
- restrained button transitions

Avoid:

- aggressive zoom
- bouncing content
- constant movement
- unnecessary parallax
- spinning elements
- excessive 3D effects

Respect:

prefers-reduced-motion: reduce

Autoplay components must remain readable and usable.

---

# 21. Hero Slider

Do not break an existing working Hero Slider.

Hero requirements:

- responsive
- readable overlay
- appropriate contrast
- controls only where the current feature specification calls for them
- indicators when used
- current user-approved exception: homepage and About photo Heroes have no visible controls; do not restore them without a new request
- keyboard/accessibility considerations
- no image distortion
- reduced-motion support

The Hero Slider and Courses Slider must have independent logic.

---

# 22. Responsive Design

Every new component must be responsive from the beginning.

At minimum consider:

Desktop
Laptop
Tablet
Mobile

Do not build desktop-only components and postpone mobile indefinitely.

Check for:

- horizontal overflow
- clipped text
- broken grids
- oversized headings
- unusable buttons
- distorted images
- inaccessible navigation

The page must not create an unintended horizontal scrollbar.

---

# 23. Accessibility

Use semantic HTML5.

Prefer:

<header>
<nav>
<main>
<section>
<article>
<footer>

Use heading levels logically.

Do not skip heading hierarchy purely for visual styling.

Interactive elements must be keyboard accessible.

Use real:

<button>

for actions.

Use:

<a>

for navigation.

Provide:

- useful alt text
- visible focus states
- aria-label where needed
- aria-expanded for expandable navigation
- sufficient contrast

Do not remove focus outlines without a visible replacement.

---

# 24. Forms

When forms are introduced:

- use proper labels
- validate server-side
- validate client-side only as enhancement
- sanitize/validate input
- never trust browser input
- provide clear English feedback

Do not implement insecure mail/database handling.

---

# 25. PHP Rules

Write simple, readable PHP.

Avoid unnecessary abstraction.

Escape user-facing dynamic output appropriately.

Use:

htmlspecialchars()

where appropriate for untrusted/dynamic text.

Validate query parameters.

Do not expose filesystem paths, warnings or stack traces publicly.

Avoid copy/pasting the same template logic across many files.

---

# 26. Security

Never commit or hard-code:

- passwords
- API keys
- database passwords
- private tokens
- SMTP credentials
- secret keys

When secrets become necessary, keep them outside tracked public source
code.

Do not weaken PHP/server security for convenience.

Do not implement authentication casually.

Article, course and training-session administration is authorized; follow docs/CMS.md.

---

# 27. Database

The contact enquiry backend has already been explicitly authorized and implemented.
The article/course/session CMS is explicitly authorized; schema and setup are documented in docs/CMS.md.
Its local configuration and database readiness must be verified before claiming successful storage.
Course and session content is authoritative in MySQL after the recorded import. Other
business datasets remain file-backed. Do not introduce further modules without a request.

Current course data may remain in structured PHP data.

For existing and future authorized MySQL work:

- use prepared statements;
- never concatenate untrusted input into SQL;
- use proper schema design;
- preserve the existing UI data model where practical.

Do not start phpMyAdmin/database work during unrelated frontend tasks.

---

# 28. Homepage Development Order

The homepage is developed progressively.

Historical development sequence (not the current implementation inventory):

1. Project structure
2. Top Bar + Header + Navigation
3. Hero Slider
4. Featured Courses
5. About Salaam Center
6. Key Figures / Statistics
7. Programs & Services
8. Talent Management Program (superseded by the learning-spaces presentation at the user's request; do not restore it from this historical list)
9. Testimonials
10. News & Events
11. Partners
12. Call to Action
13. Contact
14. Footer
15. Global polish and QA

Do not jump ahead unless explicitly instructed.

When asked to implement one step, stop after that step.

---

# 29. Preserve Existing Work

Before modifying a file:

1. inspect the existing implementation;
2. understand what already works;
3. identify dependencies;
4. make the smallest coherent change;
5. preserve unrelated working behavior.

Do NOT rewrite an entire file simply because it is easier.

Do NOT delete working code without a clear reason.

Do NOT silently replace the user's existing design.

---

# 30. No Unrequested Destructive Operations

Never perform destructive operations without explicit approval.

Do not:

- delete large groups of files
- overwrite assets unnecessarily
- reset Git history
- clean directories aggressively
- rename many files without checking references
- remove existing functionality because it seems unused

When uncertain, preserve the existing resource.

---

# 31. Git Rules

Do not automatically execute:

git init
git add
git commit
git push
git pull
git reset
git rebase
git clean
git checkout -- .
git restore .

unless explicitly requested.

Never rewrite Git history automatically.

Do not delete:

.git/

The user controls Git operations.

---

# 32. Comments

Comments should explain WHY when the reason is not obvious.

Avoid excessive comments describing obvious HTML/CSS.

Good:

/* Keep cards equal height despite different title lengths */

Bad:

/* Set display to flex */
display: flex;

Keep code readable enough that excessive comments are unnecessary.

---

# 33. Naming

Use clear, consistent English names in code.

Good:

.course-card
.course-card__image
.course-duration
.hero-slider
.mobile-nav-toggle

Avoid:

.box1
.test2
.truc
.div-course-new
.final-card-2

Use consistent naming throughout the project.

---

# 34. No Fake Content

Do not use lorem ipsum in production-facing sections unless explicitly
requested as temporary placeholder content.

Do not fabricate realistic-looking business information to make a design
appear complete.

A visually incomplete but truthful component is preferable to polished
false information.

---

# 35. External Research

When research is required:

1. prefer the official Salaam Center website;
2. prefer official certification/vendor websites when relevant;
3. distinguish confirmed information from inference;
4. do not silently merge conflicting sources;
5. prioritize newer confirmed Salaam Center data.

Do not assume a logo displayed on an old webpage proves a current active
partnership.

---

# 36. Performance

Keep the website lightweight.

Avoid unnecessary libraries.

Optimize images.

Avoid loading assets that are not used.

Avoid excessive JavaScript.

Avoid layout shifts caused by images when practical.

Do not sacrifice maintainability for microscopic optimizations.

---

# 37. SEO Foundations

For SEO-related work, read [docs/SEO.md](docs/SEO.md). Use the shared SEO helpers
and public configuration; preserve canonical/sitemap consistency and factual data.

When building pages, maintain basic SEO quality:

- meaningful <title>
- useful meta description when appropriate
- one clear primary H1 per page
- logical heading hierarchy
- descriptive image alt text
- meaningful link text
- clean course slugs
- semantic HTML

Do not keyword-stuff.

---

# 38. Browser Quality

The site should work in current mainstream browsers.

Do not depend on experimental features without a reasonable fallback.

Use progressive enhancement where practical.

Core content must remain accessible even if JavaScript fails.

---

# 39. Before Every Task

Before coding, the agent MUST:

1. read this AGENTS.md;
2. inspect relevant existing files;
3. understand the requested scope;
4. identify existing reusable components;
5. identify data sources;
6. preserve working functionality;
7. avoid unrelated changes.

Do not begin by blindly generating replacement files.

---

# 40. After Every Task

After implementation, verify as applicable:

- page loads
- PHP syntax is valid
- no obvious JavaScript errors
- CSS paths work
- JS paths work
- images load
- navigation still works
- Hero still works
- mobile menu still works
- responsive layout works
- no accidental horizontal overflow
- no broken links introduced
- no fabricated data introduced
- English interface rule respected
- French official course names preserved when applicable
- accessibility basics preserved

Report:

1. files created;
2. files modified;
3. main changes;
4. data sources used;
5. remaining missing information;
6. anything that could not be verified.

---

# 41. Current Project Principle

The project should evolve like this:

Reliable data
      ↓
Clean PHP structure
      ↓
Semantic HTML
      ↓
Consistent CSS
      ↓
Minimal JavaScript
      ↓
Responsive design
      ↓
Accessibility
      ↓
Testing
      ↓
Polished result

Do not reverse this process by prioritizing flashy visual effects over
data quality and maintainability.

---

# 42. Definition of Done

A feature is NOT complete merely because it appears on screen.

It is complete when:

- it matches the requested scope;
- its content is accurate;
- it integrates with the existing design;
- it works on desktop and mobile;
- it does not break previous features;
- it is accessible at a reasonable baseline;
- it does not duplicate unnecessary code;
- it does not introduce fake data;
- it follows this AGENTS.md;
- it is understandable enough to maintain later.

If any of these conditions cannot be satisfied, clearly report the
limitation instead of hiding it.
# 43. Currency Policy — Critical

- Public course prices support exactly USD and DJF.
- USD is the default display currency, including when JavaScript is unavailable.
- One global selector offers USD ($) and DJF (Fdj).
- The selected currency applies consistently to all course prices and persists across navigation and refresh via localStorage key `salaam_currency`.
- Invalid or unavailable storage falls back safely to USD.
- Keep one authoritative source amount and currency per course. Prefer verified original DJF amounts when these are the current source.
- Never maintain independent USD and DJF prices for one course.
- Centralize conversion using the established project parity in includes/currency.php only. Do not duplicate the rate in templates or JavaScript.
- Format USD with two decimals and DJF with no decimals (for example $213.82 and 38,000 Fdj).
- Never invent missing prices; omit them.
- Current user-supplied prices take priority over historical prices.
- Do not add EUR, other currencies or geolocation unless explicitly requested later.

# 44. Existing Salaam Center Information Architecture — Critical

The existing official Salaam Center website is the primary reference for
the INFORMATION ARCHITECTURE and interface hierarchy of features that
already exist.

Official reference:

https://www.salaamcenter.net/

Before redesigning an existing section, page, menu or content type, first
inspect how salaamcenter.net currently organizes it.

Preserve the recognizable functional structure whenever it remains useful.

The goal is:

OLD SALAAM CENTER STRUCTURE
        +
MODERN DESIGN
        +
CURRENT DATA
        +
BETTER UX

NOT:

completely different information architecture.

Do not invent a new navigation structure simply because it appears more
modern.

Modernize the presentation without unnecessarily changing how users find
existing Salaam Center information.

------------------------------------------------------------
COURSES ARCHITECTURE
------------------------------------------------------------

The Courses area must follow the existing Salaam Center hierarchy.

Main navigation:

COURSES
    ├── All Courses
    └── Categories

"All Courses" and "Categories" are NOT merely JavaScript filters on the
same page.

They represent distinct navigation concepts as on the reference website.

All Courses:
shows the current course catalogue.

Categories:
shows the available course categories.

A visitor can then enter a category and see the courses belonging to that
category.

------------------------------------------------------------
COURSE CATEGORIES
------------------------------------------------------------

When rebuilding categories, first use the category system already present
on the official Salaam Center website.

Known reference categories include:

- Islamic Banking & Finance
- Banking & Finance
- Capacity Development & Languages
- Human Resource & Business Development
- Information Technology
- Safety & Security

Do not replace these with newly invented category names unless there is
a genuine current business requirement.

Map the current 24-course catalogue into the closest appropriate existing
Salaam Center categories.

If a course does not clearly fit an existing category, report the
ambiguity instead of silently inventing an official category.

------------------------------------------------------------
COURSE CARD REFERENCE
------------------------------------------------------------

Course cards should preserve the recognizable hierarchy used by the
existing Salaam Center website:

COURSE IMAGE

PRICE BADGE
positioned at the BOTTOM-LEFT of the course image

COURSE TITLE
below the image

The visual design may be modernized, but this hierarchy must remain
recognizable.

Do not overload course cards with information.

Do NOT place all of these directly on every catalogue card:

- duration
- language
- training format
- level
- learning outcomes
- requirements
- full description

Those belong primarily on the detailed course page.

------------------------------------------------------------
PRICE POSITION — MANDATORY
------------------------------------------------------------

The course price must visually appear at the bottom-left edge of the
course image, following the existing Salaam Center course-card pattern.

Example:

┌──────────────────────────────┐
│                              │
│                              │
│         COURSE IMAGE         │
│                              │
│                              │
│ $213.82                      │
└──────────────────────────────┘

Pratique de l’Analyse Financière

The price should appear as a clean badge/label integrated with the lower
left edge of the image.

Do not put the primary price:

- at the top-right;
- centered over the image;
- underneath multiple metadata rows;
- in a random card location.

All public course prices follow the selected USD or DJF currency.

------------------------------------------------------------
HOMEPAGE COURSES
------------------------------------------------------------

The homepage should follow the course-presentation concept of the
reference Salaam Center homepage/course showcase.

Show a limited selection of current courses.

Each featured course uses:

IMAGE
+
SELECTED CURRENCY PRICE AT BOTTOM-LEFT OF IMAGE
+
COURSE TITLE

Do not show all 24 courses on the homepage.

The homepage is a showcase, not the full catalogue.

Provide a clear path to:

All Courses

------------------------------------------------------------
ALL COURSES PAGE
------------------------------------------------------------

The All Courses page contains the current 24-course catalogue.

Preserve the official order supplied by the user unless a specific
sorting/filtering action changes the view.

Use a clean responsive grid inspired by the reference website.

Course card:

IMAGE
SELECTED CURRENCY PRICE — BOTTOM LEFT OF IMAGE
COURSE TITLE

Clicking the image/title/card leads to the detailed course page.

Do not reproduce obsolete template metadata such as:

0 vote
0 Students
Teacher s

------------------------------------------------------------
CATEGORIES PAGE
------------------------------------------------------------

Create/preserve a dedicated Categories page inspired by the existing
Salaam Center Categories interface.

Each category should have:

CATEGORY IMAGE
CATEGORY NAME

The design may be cleaner and more modern than the legacy website.

Do not reproduce obsolete metadata such as:

admin
No Comment
fake dates

unless those elements have an actual current purpose.

Clicking a category opens that category's course listing.

------------------------------------------------------------
CATEGORY COURSE PAGE
------------------------------------------------------------

A category page should show only courses belonging to that category.

Use the same reusable course-card component:

IMAGE
SELECTED CURRENCY PRICE AT BOTTOM-LEFT
COURSE TITLE

Do not create a visually unrelated card design for category pages.

The same course should look consistent across:

Homepage
All Courses
Category listings.

------------------------------------------------------------
DETAILED COURSE PAGE
------------------------------------------------------------

Only after the visitor opens a course should the richer information
appear:

- title
- image
- price in the selected currency
- description
- duration
- language
- format
- level
- next session
- learning outcomes
- certificate
- requirements
- exam/evaluation

Only display information that is actually known.

------------------------------------------------------------
GENERAL REFERENCE RULE
------------------------------------------------------------

This principle applies beyond Courses.

Before rebuilding an existing Salaam Center feature:

1. inspect its implementation on salaamcenter.net;
2. understand its navigation hierarchy;
3. understand its content hierarchy;
4. identify recognizable visual conventions;
5. preserve useful structural conventions;
6. modernize visual quality and responsiveness;
7. replace obsolete information with current verified information;
8. remove legacy template clutter;
9. do not unnecessarily redesign the site's information architecture.

The user should not have to repeat this instruction for every future
section.

# UTF-8 ENCODING — CRITICAL PERMANENT RULE

The entire Salaam Center project MUST use UTF-8 consistently.

This rule applies to:

- PHP
- HTML
- CSS
- JavaScript
- JSON/data files
- course data
- text content
- forms
- future database connections
- future MySQL tables
- imported content

All project source files must be saved as:

UTF-8

Prefer UTF-8 without BOM for PHP source files.

============================================================
HTML / PHP
============================================================

Every HTML document must contain:

<meta charset="UTF-8">

It must appear near the beginning of <head>.

When appropriate, PHP responses must explicitly use UTF-8:

header('Content-Type: text/html; charset=UTF-8');

Do not introduce a BOM or whitespace before PHP headers.

============================================================
TEXT INTEGRITY
============================================================

All human-readable text must display correctly.

Examples that MUST remain correct:

Français
Maîtrise
Sécurité
Réseaux
Comptabilité
Durée
Évaluation
Développement
Présentation
Compétences
À propos

Never accept corrupted text such as:

FranÃ§ais
MaÃ®trise
SÃ©curitÃ©
RÃ©seaux
ComptabilitÃ©

or:

�
?

when these characters are encoding errors.

============================================================
DO NOT REMOVE ACCENTS TO "FIX" ENCODING
============================================================

Never solve an encoding problem by replacing correct accented characters
with unaccented characters.

Wrong approach:

Francais
Maitrise
Securite
Reseaux

Correct approach:

Français
Maîtrise
Sécurité
Réseaux

Preserve the correct spelling and fix the encoding itself.

============================================================
SOURCE CONTENT
============================================================

When importing or copying Salaam Center content, preserve:

- accents
- apostrophes
- punctuation
- capitalization
- special characters
- official course names

Do not silently corrupt or normalize official titles incorrectly.

============================================================
PHP OUTPUT
============================================================

Dynamic PHP content must preserve UTF-8.

When escaping HTML, use UTF-8 explicitly when appropriate:

htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

Do not perform unnecessary encode/decode operations that can cause
double-encoding.

Avoid incorrect transformations involving:

utf8_encode()
utf8_decode()

unless there is a specifically diagnosed legacy encoding problem.

============================================================
FUTURE MYSQL RULE
============================================================

When MySQL is introduced later, use:

utf8mb4

not legacy utf8 when utf8mb4 is available.

Database:
utf8mb4

Tables:
utf8mb4

Columns:
utf8mb4

Connection:
utf8mb4

Use an appropriate utf8mb4 collation.

For PDO, configure the connection charset appropriately.

Do not implement MySQL now unless explicitly requested.

============================================================
QUALITY CONTROL
============================================================

After every content-related modification, inspect the affected pages for
encoding corruption.

Search the project for common mojibake indicators such as:

Ã
Â
â€
â€™
â€œ
â€
�

If found, determine the actual intended text and correct the encoding.

Do not blindly replace characters without understanding the intended
content.

Before declaring a task complete, verify that:

[ ] source files are UTF-8
[ ] HTML pages declare UTF-8
[ ] PHP output preserves UTF-8
[ ] French course titles display correctly
[ ] apostrophes display correctly
[ ] accented characters display correctly
[ ] special punctuation displays correctly
[ ] no visible mojibake remains
[ ] no replacement character � remains
[ ] text has not been stripped of accents merely to avoid encoding issues

UTF-8 integrity is part of the project's Definition of Done.

# 45. Architecture, Brand and Maintainability

For any visual/UI work, read [docs/DESIGN.md](docs/DESIGN.md) and
[docs/BRAND_GUIDELINES.md](docs/BRAND_GUIDELINES.md) before implementation.
If the work also changes architecture, read [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md).
DESIGN.md defines interface direction; BRAND_GUIDELINES.md defines brand/asset
constraints; ARCHITECTURE.md defines technical organization. If these documents
conflict, report the conflict before implementing; do not silently choose one.

Before substantial architectural or visual changes, read:

- [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)
- [docs/BRAND_GUIDELINES.md](docs/BRAND_GUIDELINES.md)

These documents distinguish current implementation from recommended future work.
They do not authorize a refactor or override the latest explicit user instructions.

- Inspect before implementing; reuse before creating.
- Keep one authoritative source for each business dataset.
- Avoid unrelated responsibilities in one file, but never split files solely by line count.
- Avoid duplicated PHP, CSS, JavaScript and business data.
- Document meaningful architecture changes and mark obsolete task reports as historical.
- Follow the brand guidelines before visual changes; do not invent colors, gradients or logo treatments.
- Do not perform unrelated large refactors. Preserve behavior and validate affected pages during refactoring.
- Current About photo slideshow: 4-second rotation, 1.2-second dissolve, no visible controls or hover pause.
  Homepage Hero remains independent at 5.5 seconds. Both respect reduced motion and hidden tabs.
- Course/session CMS editing is authorized. Preserve the public catalogue design, currency
  conversion and verified source amounts; do not invent prices or dates.
  Current currencies remain USD ($) and DJF (Fdj), with USD as the first-visit default; no EUR.
- Structured SEO and the article/course/session CMS are implemented; read docs/SEO.md and docs/CMS.md.
  Do not add other CMS modules, WordPress or a headless service without authorization.

Policy reconciliation on 5 October 2026: the stack/database language now acknowledges the
already-authorized contact backend; course storage remains in PHP. The old compulsory Hero
controls and historical Talent step are explicitly qualified by later user decisions.
The section 43 heading's corrupted separator was corrected; the USD/DJF policy was preserved.
Existing valid language, provenance, security, UTF-8, scope and Git rules remain in force.

# 46. Content CMS

- Read [docs/CMS.md](docs/CMS.md) before CMS work.
- Never bypass administrator authentication, CSRF or media access checks.
- Never expose credentials in source, responses, logs or documentation.
- Never expose drafts or their images publicly, including through previews or sitemap.
- Never invent article or business content; remove temporary test articles after validation.
- Preserve shared SEO integration and stable published URLs.
- CMS manages articles, courses and training sessions. Events/Partners remain read-only datasets.
- Preserve the existing admin account and course-import ledger. Re-running import must never overwrite CMS edits.
- Preserve historical USD source amounts until explicitly replaced with verified DJF amounts; new session prices use DJF.
- Never expose course/session drafts in the public catalogue, media endpoint, SEO or sitemap.
