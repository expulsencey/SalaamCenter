# Homepage continuation and navigation

## Scope

Completed homepage sections after Featured Courses: About, training benefits, testimonials, Latest News & Events, Talent Management Program, final CTA, contact UI and footer. Five destination pages contain only concise verified introductions and useful links. No full redesign of those secondary pages.

The existing 24-course dataset, category data, card component, helpers, current USD prices and conversion rule are unchanged. Existing catalogue templates only include the new shared footer. Hero behavior and the existing reveal observer are preserved; no second IntersectionObserver was added.

## Navigation

| Label | Destination |
| --- | --- |
| Home | index.php |
| Salaam Center | about.php |
| Courses → All Courses | courses.php |
| Courses → Categories | categories.php |
| Partners | partners.php |
| Events | events.php |
| SC Business | sc-business.php |
| Contact | contact.php |

Active page styling applies across the shared header. Courses keeps a native details/summary disclosure with aria-controls, a synchronized aria-expanded attribute under JavaScript, keyboard and touch support, outside-click dismissal, focus handling and Escape. Native disclosure remains usable without JavaScript.

## Content and reference pages

- [Official homepage](https://www.salaamcenter.net/): section order, training approach, two short testimonial excerpts (Ayane and Aden), names, linked Ayane portrait, news titles and brief article summaries, contact information and footer concepts.
- [About](https://salaamcenter.net/about-us/): Djibouti/East Africa introduction, training/research/consultancy, tailored approach, experienced trainers and clear learning objectives. Text is concise paraphrase.
- [Talent Management Program](https://salaamcenter.net/tmp/): development of key skills and preparation for responsibilities and leadership; no duration, admission rules or promises imported.
- [SC Business](https://salaamcenter.net/sc-business/): short introduction to organizational training and consultancy, performance and change management.
- [Partnerships](https://salaamcenter.net/partnerships/): destination reference only; no list of current partner claims or logo wall copied.

The two news titles currently featured by the official homepage are “Salaam Center: Versatile Spaces for Events and Training” and “Artificial Intelligence: A Driver of Innovation and Development”. The Read More links preserve their exact official destinations. Direct article fetches returned 403 in the research environment; only the official homepage excerpts were used. Article detail accessibility could not be verified from this environment. No full articles were reproduced.

Images and download sources: [homepage image provenance](../assets/images/home/SOURCES.md). About and Talent visuals are official assets. The laptop image accompanying the AI article is illustrative.

## Intentionally omitted

- Historical student, partner and training statistics.
- Publication dates: the official homepage shows day/month without a reliable year for the selected items; other indexed listings have inconsistent date representations.
- Upcoming-event schedules, fake ratings, job titles and companies for testimonials.
- Portrait for Aden: source unavailable during download.
- Current partner roster, legal pages, fake footer links and stale template metadata.

Testimonials use manual previous/next controls with a count. Without JavaScript both excerpts remain visible. No autoplay or pause control is necessary because this component never moves automatically.

## Contact form status

The Name, Email, Phone, Topic and Message fields are present with labels, appropriate input types and autocomplete. Phone is optional. Send Message is visibly disabled and a nearby notice directs users to the working mailto/telephone links. JavaScript cancels submission; contact.php rejects POST with HTTP 405 and Allow: GET, HEAD. No mail is sent, no data is saved, no success message is shown.

Backend work still required: server-side validation, CSRF/abuse controls, a configured mail transport, safe delivery/error handling and real status feedback. Do not enable submission until these are implemented and tested. This task did not configure SMTP, a database or server settings.

## Files

Created:
- about.php, partners.php, events.php, sc-business.php, contact.php
- data/home.php
- includes/home-sections.php, includes/contact-section.php, includes/footer.php, includes/news-list.php, includes/info-page.php
- assets/images/home/about-center.png, talent-management.webp, ayane.png, SOURCES.md
- docs/HOMEPAGE.md and QA screenshots

Modified:
- index.php
- includes/header.php, includes/head.php
- assets/css/style.css, assets/js/main.js
- courses.php, categories.php, category.php, course.php (shared footer include only)

No Git operations, package installations, WAMP configuration changes or edits outside the project.

## Validation

Server checks: all 22 PHP files pass syntax checks; 38 public page variants return 200; all local images, styles/scripts and internal destinations load. Active navigation labels verified. Invalid slugs and direct template requests return clean 404s; contact POST returns 405. Protected catalogue data/helpers/card hashes match their pre-task values.

Browser checks: Edge at 1440, 1024, 768, 390 and 320 px. Homepage and all navigation destinations, catalogue, categories and an ICA detail page have no horizontal overflow. Form controls remain within their columns. Native Enter/Escape handling, nested Courses disclosure and mobile-menu closing pass. Testimonial next/previous and keyboard activation pass. Hero autoplay advances; reduced-motion stops it. Price badges remain at the bottom-left of images. Without JavaScript, navigation and both testimonials are visible and content is not hidden by reveal classes. No JavaScript exceptions observed.

Screenshots inspected: home-about-desktop.png, home-news-desktop.png, home-contact-desktop.png, home-contact-mobile.png. News article external pages could not be validated beyond their official homepage links (HTTP 403 during research). Backend message delivery remains intentionally unavailable.
