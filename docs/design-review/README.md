# Visual review - 5 October 2026

## Scope and decisions

Reviewed Home, About, Courses, Course detail, Categories, Events, Event detail,
Partners, Contact and SC Business in the rendered localhost site before editing.

- KEEP: authentic media, About editorial sections and slideshow, GIFA content,
  documentary event galleries, routes, course cards/prices, navigation behavior.
- IMPROVE: shared heading rhythm, button spacing, header visual weight, mobile
  footer organization, image corner consistency and breadcrumb touch targets.
- REDESIGN: boxed testimonials, dark benefit overlays with decorative icons,
  enclosing event/partner panels, competing space CTAs and layered Contact effects.

No public copy, contact fields, course data, pricing logic, logos or media files
changed. No JavaScript changes. Implementation files: assets/css/style.css and
includes/home-sections.php. Reusable presentation patterns recorded in DESIGN.md.

## Evidence

Before/after overview images show all ten reviewed page types at 1440, 768 and
390 pixels. Full homepage desktop captures and mobile/tablet opening crops are
also retained. Screenshots force lazy images to load and reveal pending sections
for full-page capture; normal runtime behavior was tested separately.

validation.json records 88 browser layout checks: eleven routes at 1920x1080,
1440x900, 1366x768, 1024x768, 768x1024, 430x932, 390x844 and 375x667.
Checks cover overflow, heading count, image alt presence and loaded image failures.
Interaction checks cover mobile menu/Escape, Courses dropdown, both currencies,
persistence, both photo slideshows, testimonial controls, event playback, partner
motion, reduced motion, form label/target sizing and focus visibility.

Separate HTTP checks covered 51 route variants including all course/category/event
records and invalid/missing/array slugs. No missing local link or asset and no
rendered PHP error marker. Modified PHP passed syntax checking; UTF-8 checked.

## Limits and pending work

Browser checks use Edge; this is not a full WCAG certification or cross-browser
certification. External historical destinations were not revalidated. Contact
submission and private configuration were not changed or tested. Existing autoplay
control decisions remain unchanged. Course discovery, pricing presentation,
registration/quotation and audience decisions await business clarification.
SC Business retains its concise approved content. No SEO/CMS work was started.
