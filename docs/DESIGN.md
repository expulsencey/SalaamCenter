> Current policy ? 7 October 2026: the user explicitly retired all active Course
> and Training pricing. Earlier requirements below for USD/DJF, price badges,
> currency controls and preserving conversion are superseded. Preserve dormant
> historical amounts and the immutable seed; do not display or edit them. Venue
> rental rates remain unchanged. See [CMS workflows](CMS.md) for details.

# Salaam Center Digital Interface Design

## Purpose and Authority

The direction is **Modern Professional Education Platform**: modern, professional,
credible, educational, international, premium but restrained, clean, spacious,
human and trustworthy. Learn from contemporary professional-learning interfaces
without copying another company's identity or layout.

This document defines digital interface direction. It is not an official corporate
brand manual and does not authorize a redesign or refactor by itself.

- [BRAND_GUIDELINES.md](BRAND_GUIDELINES.md) records brand/asset constraints and
  distinguishes current website values from verified official facts.
- [ARCHITECTURE.md](ARCHITECTURE.md) describes technical organization.
- [AGENTS.md](../AGENTS.md) contains permanent project rules.

If these documents conflict, report the conflict before implementing; do not
silently choose one. Preserve approved content, English interface text, official
French course names and UTF-8. Never invent claims, statistics or business facts.

## Core Design Principles

1. **Content first.** Help visitors understand content and find their next step.
   Add visual treatment only when it improves comprehension or navigation.
2. **Strong hierarchy.** Give each section one primary message. Use size, position,
   contrast and spacing to distinguish primary information from supporting detail.
3. **Generous negative space.** Separate concepts through whitespace. A border,
   box or card should have a functional reason.
4. **Authentic imagery.** Prefer suitable real Salaam Center photography. A strong
   photograph can anchor a large composition instead of occupying a small card.
5. **Restrained effects.** Gradients, shadows, blur and animation should support
   hierarchy and atmosphere. Use them selectively.
6. **Consistency.** Reuse good containers, type hierarchy, buttons, spacing,
   image treatments, card language, radii and transitions across pages.
7. **Responsive by design.** Plan mobile hierarchy, spacing and interaction from
   the start. Restructure desktop compositions where necessary.

## What to Avoid

- Excessive boxed layouts, cards inside cards and borders around every element.
- Excessive shadows, rounded containers and oversized empty cards.
- Repetitive three-column layouts in every section.
- Dense information blocks and long walls of text without hierarchy.
- Tiny typography, excessive centered text and uppercase paragraphs.
- Unnecessary icons and decorative gradients without a purpose.
- Excessive glassmorphism, random colors and inconsistent spacing.
- Cramped mobile layouts.
- Generic stock photography when suitable authentic Salaam Center media exists.
- Putting every piece of metadata directly on a card.
- Visually competing CTAs and excessive animation.
- Compositions that reproduce traditional corporate templates from the 2010s.

Evaluate these issues through readability and usefulness, not novelty alone.

## Layout System

**Content container:** important text and controls normally sit inside a readable,
centered container. Reuse the current container documented in BRAND_GUIDELINES.md;
long prose can use a narrower reading measure within it.

**Visual canvas:** large photography, backgrounds, sliders and selected visual
sections may reach the viewport edges. Keep essential text within the content
container even when the background extends beyond it.

Choose width and composition for the content. Do not force every section into
the same width or card structure. Full-width media must remain responsive without
horizontal overflow; do not hide a sizing defect by clipping the entire page.

## Typography

Use the actual current stack: `"Poppins", system-ui, sans-serif`. The project loads
weights 400, 500, 600 and 700. This is the current website font, not a declaration
of an official corporate font. Exact existing sizes are in BRAND_GUIDELINES.md.

| Role | Direction |
|---|---|
| Display / Hero headings | A concise primary message with fluid sizing and deliberate line breaks; leave room for the photograph and action. |
| Page titles | One clear H1 identifying the page; preserve a logical semantic hierarchy. |
| Section headings | Clearly below the page title in hierarchy; introduce the section's main idea. |
| Body text | Preserve the readable 1rem baseline and generous paragraph leading; use short paragraphs and controlled line lengths. |
| Labels / eyebrows | Brief supporting context; current uppercase styling is appropriate for short labels only. |
| Metadata | Visually secondary but readable, with sufficient contrast; never shrink essential information to fit more fields. |
| Buttons | Reuse existing weight, size and spacing; use concise action labels that remain legible on mobile. |

Current body/paragraph line heights are 1.6/1.7; headings generally use 600 weight
and 1.2 line height. Existing prose measures of roughly 65-78ch are useful starting
points. Adjust wrapping for the available width, without enlarging text merely to
fill space. Avoid tiny text and unnecessary all-uppercase passages.

## Color

Use the current website palette and CSS variables documented in
[BRAND_GUIDELINES.md](BRAND_GUIDELINES.md#color-system). Do not redefine official
colors here or invent corporate specifications.

No agent may introduce a new major color merely to make a section look different.
A proposed new visual color needs a justified design reason, compatibility with
the established palette and authorization within the task scope. Keep the green
Contact treatment scoped to its documented role. Check contrast in context.

## Gradients and Effects

Use documented gradients primarily for subtle page atmosphere, Hero/background
depth and transitions between major visual areas. Keep them atmospheric and
subordinate to content.

Avoid gradients on every card or button, competing gradients on one screen and
random colors outside the palette. Reuse existing shadow and radius roles; do not
add blur, shadows or glass effects to every surface.

## Photography

Follow BRAND_GUIDELINES.md and use authentic Salaam Center imagery first.

- Preserve people, original scenes, colors and source media. Never stretch photos.
- Use `object-fit: cover` for photographic frames when appropriate, with intentional
  `object-position`; responsive crops may differ by viewport.
- Use natural proportions or `contain` when the entire composition or embedded
  wording must remain visible. Logos must remain whole and follow brand rules.
- Let major photographs occupy large or full-width compositions when useful.
- Avoid unnecessary overlays. Where text overlaps a photo, ensure readable contrast
  on every image without making the scene unnecessarily dark.
- Select imagery for its meaning; do not insert an image just to fill empty space.

## Cards

Use cards for genuinely repeatable or independently actionable items, such as
courses, events and selected resources. Keep a clear title/action hierarchy and
limited metadata.

About copy, ordinary paragraphs, statistics, services and CTAs do not each need
a separate card. Prefer open layouts or lists when they communicate relationships
more clearly. Consistency does not require identical framing for every content type.

## Buttons and CTAs

Each section normally has one primary action and optionally one secondary action.
Make their relative importance clear; avoid several equally prominent buttons.
Reuse existing sizing, typography, spacing, hover, focus and disabled states.
Do not create a new button style for each page. Use links for navigation and
buttons for actions, with adequate touch targets and visible keyboard focus.

## Course Experience: Frozen Pending Business Clarification

The current catalogue/pricing presentation is frozen. This document authorizes no
course-card redesign, functionality removal, new pricing logic or new currencies.
Preserve USD and DJF, with USD as the first-visit default and no EUR.

For a later authorized redesign, course discovery should feel like a modern
professional learning platform, with clear hierarchy and information that helps
visitors decide. Keep detailed metadata on appropriate detail views rather than
overloading cards. Coursera and other platforms may later serve as UX references;
do not copy their identity or layouts.

Confirm public pricing, registration, quotation, upcoming sessions, online learning
and B2B versus individual audiences before redesigning catalogue UX. Existing
course hierarchy and price placement rules remain in force until explicitly changed.

## Section Composition

Vary composition intentionally within one design system. Suitable options include
text with large photography, editorial split layouts, full-width media, restrained
grids, logo strips, feature lists, editorial content and strong CTA sections.
Use statistics only when meaningful and verified. Avoid repeating one composition
throughout the page; let content and visitor needs determine the arrangement.

## Motion

Use subtle motion for transitions, sliders, gentle reveals and interaction feedback.
Do not animate everything on scroll or add movement purely to appear impressive.
Respect `prefers-reduced-motion`, retain readable content and preserve existing
feature-specific behavior unless a change is requested. This guidance does not
authorize changing slider controls or timings. Report accessibility conflicts
before implementing a different treatment.

## Mobile

Check desktop, laptop, tablet and mobile during future visual work. Mobile layouts
must have intentional reading order, spacing and interaction; restructure large
compositions rather than simply shrinking them.

Review navigation, hierarchy, typography, CTA priority, image cropping, spacing,
card density, touch targets and horizontal overflow. Let controls wrap or stack
naturally, keep essential actions reachable and preserve meaningful photo subjects.

## Accessibility

Preserve readable contrast, visible focus, semantic heading hierarchy, meaningful
alt text, adequate touch targets, keyboard usability and reduced-motion preferences.
Decorative images may use empty alt text when appropriate. Core content must remain
available when JavaScript fails. Visual minimalism must never remove accessibility.

## Design Decision Rule

Before creating a new visual pattern, determine whether an existing Salaam Center
pattern solves the same problem. Reuse before inventing.

Do not preserve a poor pattern merely for consistency. If an existing pattern
conflicts with this direction, propose its gradual replacement instead of spreading
it elsewhere. Describe the conflict and obtain the necessary task scope before
changing established behavior. Avoid unrelated redesigns.

## Implemented Editorial Patterns (5 October 2026)

- Open event and partner layouts use spacing or a single separator instead of enclosing
  every item in a rounded box. Non-interactive partner items have no lift-on-hover effect.
- Homepage benefits place photography above readable text; at tablet widths they use
  image/text rows. Decorative icons and dark text overlays are unnecessary here.
- Testimonials use an open quotation area with a restrained side rule. Preserve controls.
- Use the shared section-space and editorial-radius tokens for these compositions.
  Existing course cards remain outside this redesign.
- Closing CTAs use a left-aligned message and clear primary/secondary actions on an
  established dark background. Contact keeps its scoped green palette with a quiet pale
  surface, without layered gradients or a floating shadow.
- Small-screen footer links form two columns, with contact information spanning both.

These are interface decisions, not additional official brand specifications. The brand
reference records the earlier implementation values; this section records the authorized
presentation changes without changing logo, color or asset facts.

## Design Review Checklist

Before completing future visual work, verify:

- [ ] Clear primary message and visual hierarchy.
- [ ] Modern, restrained presentation without dated template repetition.
- [ ] Appropriate whitespace and readable line lengths.
- [ ] No unnecessary containers, nested cards or empty framing.
- [ ] Authentic, relevant imagery and intentional crops.
- [ ] Documented palette and correct logo treatment.
- [ ] Controlled gradients, shadows and blur.
- [ ] Consistent, readable typography.
- [ ] Clear primary and secondary CTA hierarchy.
- [ ] Intentional desktop, laptop, tablet and mobile layouts.
- [ ] Contrast, focus, semantics, alt text, touch targets and keyboard access preserved.
- [ ] No horizontal overflow.
- [ ] No unnecessary animation; reduced motion respected.
- [ ] Existing good patterns reused and document conflicts reported.
