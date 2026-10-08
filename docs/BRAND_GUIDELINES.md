> Current policy ? 7 October 2026: the user explicitly retired all active Course
> and Training pricing. Earlier requirements below for USD/DJF, price badges,
> currency controls and preserving conversion are superseded. Preserve dormant
> historical amounts and the immutable seed; do not display or edit them. Venue
> rental rates remain unchanged. See [CMS workflows](CMS.md) for details.

# Salaam Center Digital Brand Guidelines

Current website reference, audited 5 October 2026. This is an implementation guide, not
an official corporate identity manual. It documents the assets and decisions already
present locally. Read AGENTS.md and ARCHITECTURE.md before substantial visual changes.
No asset, color, photograph or visible page was changed to produce this document.

## Purpose

Keep the institutional site professional, readable and consistent while preserving
approved content and authentic media. Reuse current variables/components instead of
inventing colors, gradients or logo treatments. Latest explicit user instructions take
priority over historical task reports. A new visual treatment needs a clear task scope.

## Logo Assets

### Full Logo / Wordmark

| Local asset | Technical properties | Current role / evidence |
|---|---|---|
| `assets/images/logo/salaam-center-logo.webp` | 2271x761, alpha transparency, horizontal blue/cyan/gold symbol and wordmark | Header. User previously requested this logo replacement. Current local asset is the evidence; no official usage manual accompanies it. |
| `assets/images/Logo_SalaamCenter.png` | 360x72, alpha transparency, horizontal symbol/wordmark with internal transparent padding | Footer. Different composition and resolution from header; not byte-identical. |
| `assets/images/salaam_center_for_training_research_and_consultancies_logo.jpg` | 200x200, fully opaque JPEG, white background, stacked symbol and multilingual wordmark | Retained local variant; no current rendered reference found. |

The current header file has partially transparent edge pixels; an alpha channel does
not imply identical quality on every background. Inspect the actual asset at display
size. Do not regenerate, erase edges or recolor it without explicit approval.

### Logo Mark

`assets/images/WhatsApp Image 2026-09-30 at 15.54.14.jpeg`: 447x447 opaque square,
white/cyan/gold symbol on a baked blue background, no wordmark. Used by `head.php` as
the JPEG favicon. It is not a transparent symbol-only master. No standalone vector
Salaam Center master or approved monochrome/inverse variant was found locally.

### Available Variants

The files above are technically available, not all certified current brand variants.
The similarly named `WhatsApp Image ... (1).jpeg` is a 588x360 photograph used on a
benefit panel, not an interchangeable logo. Embedded logos on GIFA posters/photographs
remain part of the original media; do not extract or alter them as identity replacements.
`SALAAM_CENTER_LOGO-03.webp` from historical requests is not a separate current file
in the inspected tree; the current header uses the descriptive copied filename above.

### Clear Space

No official numerical exclusion zone was supplied. Preserve each asset's existing
padding and provide uncluttered surrounding layout space. Do not claim a measured
brand clear-space formula. Header currently provides 1.25rem block padding and a
1rem layout gap; these are website layout values, not official logo rules.

### Sizing

Header: flexible slot up to 15rem; image width/height auto, max-width 100%, max-height
4.5rem, reduced to 3.5rem on small screens. At the mobile navigation breakpoint its
slot is constrained to leave room for currency/menu controls. Footer image is
`min(15rem, 100%)`, height auto. Preserve natural proportions and never stretch both
width and height independently. The small footer source is not suitable for a large Hero.

### Background Usage

Current header is navy translucent glass; footer is #202121. Current full-logo files
are transparent and retain original colors. The square favicon and small stacked JPG
have baked backgrounds, so they are not drop-in replacements on dark surfaces. Check
contrast/legibility on the intended background; do not apply CSS invert/filter to invent
an inverse variant. Header/footer visual equivalence needs business confirmation.

### Prohibited Logo Treatments

Do not crop, stretch, recolor, add glow/shadow, redraw the symbol, remove text, change
wordmark spacing, or substitute a random stored variant. Do not use partner logos as
Salaam Center identity. Use `object-fit: contain` for constrained logo stages; the
current header/footer use natural width/height, which also preserves the full image.

## Color System

### Current website design palette

All values below describe actual CSS. The CSS header records that primary blues/white
and Poppins were observed on the official site's stylesheet on 30 September 2026;
its footer comment records #202121 on 3 October. That is project-recorded implementation
provenance, not a supplied official brand specification or fresh external verification.
Do not call supporting neutrals, greens, gradients or this whole palette official colors.

| Role / CSS token | Current value |
|---|---|
| --color-primary | #02549e |
| --color-secondary | #30aee4 |
| --color-accent | #68c0e6 |
| --color-background | #fff |
| --color-surface | #f5f7f9 |
| --color-text | #243241 |
| --color-text-light | #596775 |
| --color-border | #dce3e9 |
| --ambient-navy | #062843 |
| --ambient-soft | #eaf6fc |
| --ambient-glass | rgba(6, 40, 67, 0.88) |
| --ambient-glass-blurred | rgba(6, 40, 67, 0.72) |
| --ambient-border | rgba(104, 192, 230, 0.24) |
| --footer-background | #202121 |
| --footer-text | #dce3e9 |
| --footer-muted | #b6c0ca |
| --footer-border | rgba(255, 255, 255, 0.08) |

Ambient blue/glow/highlight alias primary/secondary/accent. Contact scopes its own
palette to `.contact-section`; do not spread the green theme to other sections:

| Contact token | Current value |
|---|---|
| --contact-deep | #1F5A4A |
| --contact-hover | #174638 |
| --contact-medium | #3F7A68 |
| --contact-soft | #EAF4EF |
| --contact-pale | #F4F9F6 |
| --contact-input-border | #7C998D |

Contact additionally uses white at .94 opacity, medium green at .16/.25, and deep green
at .06/.08 for ambient wash, borders, shadow/focus. Hero overlays use RGB(5,25,44), a
supporting dark tone distinct from ambient navy. Card shadows use RGB(36,50,65).
The gold/brown embedded in logos is not a declared CSS brand token: do not sample it
and invent an official gold specification.

### Literal repetition and potential future tokens

Exact textual CSS counts at audit time (including token definitions): #fff 10,
#dce3e9 2, rgba(36,50,65,.08) 3 using the spaced source spelling.
Most other color literals occur once. The two #dce3e9 tokens represent different roles;
that is not automatically a defect. White literals may eventually use the existing
background token where semantically appropriate.

`border-radius: 0.25rem` occurs 16 times, `20px` 7 times; .5rem once, 50% once,
and zero/full corner-specific rules elsewhere. Repeated padding/gaps center on 1rem,
1.25rem, 1.5rem, 2rem and 3rem, with the shared 80rem container. Future tokens could
name compact/editorial radii, card shadows, section spacing and container width.
Do not blindly replace every numeric value: some are focal crops or local geometry.

## Gradients

Current gradients are purposeful: ambient blue opening, photograph contrast overlay,
light About heading and scoped green Contact. Reuse only in these established roles
unless the user requests a new treatment. Recognition uses solid navy; About slideshow
has no dark overlay. Full-width photographs should not receive arbitrary color grading.

Exact current recipes follow. Their occurrences are unique declarations; homepage and
mobile variants are deliberate differences, not redundant copies.

```css
background: linear-gradient(180deg, var(--ambient-navy), var(--ambient-blue) 27%, var(--ambient-soft) 64%, var(--color-background));
```

```css
background: radial-gradient(ellipse at 72% 32%, var(--ambient-glow), transparent 48%), radial-gradient(ellipse at 20% 48%, var(--ambient-highlight), transparent 50%);
```

```css
background: linear-gradient(180deg, transparent 24%, var(--ambient-soft) 68%, var(--color-background) 98%);
```

```css
background: linear-gradient(180deg, transparent 45%, var(--ambient-soft) 82%, var(--color-background) 100%);
```

```css
background: linear-gradient(90deg, rgba(5, 25, 44, 0.84), rgba(5, 25, 44, 0.52) 55%, rgba(5, 25, 44, 0.12));
```

```css
.slide::before { background: linear-gradient(0deg, rgba(5, 25, 44, 0.8), rgba(5, 25, 44, 0.62) 65%, rgba(5, 25, 44, 0.25)); }
```

```css
.benefit-card::before { content: ""; position: absolute; inset: 0; z-index: -1; background: linear-gradient(180deg, rgba(5, 25, 44, 0.55), rgba(5, 25, 44, 0.88)); }
```

```css
background: linear-gradient(to bottom, var(--color-background), transparent 18%, transparent 82%, var(--color-background)), radial-gradient(ellipse at 78% 40%, rgba(63, 122, 104, 0.16), transparent 60%), linear-gradient(125deg, var(--contact-pale), var(--contact-soft) 58%, var(--color-background));
```

```css
.about-hero { padding-block: 2rem 0; background: linear-gradient(180deg, var(--ambient-soft), var(--color-background)); }
```


The ambient layer animates gently over 18s, with 0.45 opacity normally and 0.72 on
home. Reduced motion stops it. No extra gradient engine/library exists.

## Typography

Actual font stack: `"Poppins", system-ui, sans-serif`. Google Fonts weights 400, 500,
600 and 700 are requested with `display=swap`. Poppins is the current website choice,
not a claim about an official corporate font.

- Body: 1rem; body line height 1.6; paragraphs 1.7.
- Headings: weight 600, line height 1.2.
- Base H1: clamp(2rem, 4vw, 3.5rem); H2: clamp(1.75rem, 3vw, 2.5rem);
  H3: clamp(1.25rem, 2vw, 1.5rem).
- Homepage Hero: clamp(2rem, 3.6vw, 3.75rem), 1.15 line height.
- About heading: clamp(2.25rem, 3.5vw, 3.75rem), letter spacing -0.035em.
- Navigation: .875rem; small/eyebrow: .8125rem. Eyebrow is uppercase with .12em spacing.
- Course card titles: 1.125rem, line height 1.5; detail sections use local overrides.

Keep readable line lengths: 80rem overall container; many headings 42rem, detail/body
copy 65-78ch as appropriate. Do not enlarge body text merely to fill space. English UI,
French official names preserved with language attributes where appropriate, UTF-8.

## Photography

Prioritize authentic Salaam Center media supplied locally. Preserve people, scene,
original colors and source files. No destructive retouching, artificial additions or
stretching. Responsive cropping is allowed with intentional `object-position`.
Use cover for photographic frames; use natural dimensions/contain when embedded text
or the entire composition must remain visible. Preserve originals and provenance.

### Actual image treatments

| Context | Current rendering |
|---|---|
| Homepage Hero | Full-width cover, responsive frame and individual mobile focal points, dark readable overlay |
| About Hero | Full-width cover; five local photos, 4s rotation, 1.2s dissolve; no controls or hover pause by latest request |
| Homepage reception | Contain inside its established frame, preserving complete source composition |
| About Who We Are | SCValues.webp, 13:8 frame, cover, 60% center, 20px corners |
| GIFA recognition | All three images at original 1280:900 ratio with contain, preserving embedded labels and people |
| Learning spaces / venue panels | Cover, controlled aspect ratios and some individual focal positions |
| Course/category cards | 16:9 cover, price at bottom-left for courses; content is frozen pending business clarification |
| Course detail brand logos | Contain, maximum 15rem width / 5rem height |
| Event galleries | Width 100%, height auto; preserve documentary compositions |
| Homepage event cards | Contain in 1600:1124 frame; preserve printed branding |
| Partner logos | Contain, entire logo visible |

About Hero focal points, desktop -> mobile:
reception 60% 45% -> 73% center; laptop learner 45% 0% -> 35% center;
classroom 50% 45% -> 50% center; group workshop 55% 45% -> 65% center;
training presentation 35% 20% -> 20% center. Frame height uses clamp and breakpoint
rules, approximately 430-720px in the previously tested viewport range.

Not every different treatment is an inconsistency: contain is intentional for reception,
GIFA and documentary media. However, course illustrations may contain embedded titles
while generic card cover crops to 16:9; review affected crops before future catalogue
changes. The unused `.course-logo` rule is not applied to current course cards.
Some course imagery is documented illustrative reuse across related topics, not proof
of course equivalence. One benefit photo has an external-looking filename and user
provision but no explicit licensing record in SOURCES.md; confirm provenance before a
public deployment without replacing the user-selected media during this audit.

No stock imagery should replace suitable authentic photos. Image rights/provenance
are recorded in existing SOURCES.md files; those documents do not make images public domain.

## Partner Logos

Use `data/partners.php`, 20 local records, and the existing strip/grid. Preserve every
logo, original color and aspect ratio with contain. No newly inferred accreditations,
relationships or external partner links. The strip renders the dataset twice solely for
its continuous CSS motion; the second set is hidden from assistive technology and is
not a second business dataset. Browser caching can reuse identical URLs; repeated DOM
does not necessarily mean repeated network transfers. Reduced motion makes it a static grid.

Partner files currently live at the images root. Do not move them to a preferred folder
without checking every reference and keeping provenance. Do not use the partial ICA download.

## Image Corners and Shapes

Current language has compact .25rem corners on course/venue panels, 20px on editorial
About/event/partner/contact elements, .5rem on GIFA images and circular testimonial
portraits. Both full-bleed Heroes have no outer radius. These are website decisions,
not official brand geometry. Unify future usage by role rather than making every image
an identical rounded card. Never add gutters simply to preserve corners on full-width media.

## Shadows

Current recipes:

- Course base: `0 3px 10px rgba(36, 50, 65, 0.04)`.
- Course hover and dropdown: `0 6px 14px rgba(36, 50, 65, 0.08)` (two declarations).
- Homepage event hover: `0 8px 20px rgba(36, 50, 65, 0.08)`.
- Contact: `0 12px 36px rgba(31, 90, 74, 0.06)`.
- Contact focus ring: `0 0 0 4px rgba(31, 90, 74, 0.08)` plus visible outline.

No logo shadows. Keep effects restrained; reuse a role rather than invent near-duplicate
shadow variants. Partner cards currently use border/translation, not a large shadow.

## Buttons

Base `.hero-button`: inline-flex, centered, min-height 3rem, .75rem 1.125rem padding,
.25rem corners, .875rem/600 text. Variants: blue `.courses-catalog` / `.spaces-primary`,
outline `.button-outline`, dark Hero secondary, scoped green contact submit and white/
transparent About closing buttons. Links navigate; real buttons act. Preserve focus
states and readable text; `.hero-button` is currently shared despite its historical name.
Future renaming should be mechanical and regression-tested, not a visual redesign.

## Cards

One reusable course card: image + price bottom-left + linked official title. No metadata
expansion while catalogue decisions are frozen. Cards use a border and consistent height.
Event card content is shared, with two layout modes. Partner cards display full logos
and names. About What We Do uses editorial columns, not floating cards. Do not convert
all compositions to one generic card system merely for uniformity.

## Full-Width Visuals

Media/background may meet viewport edges while text stays inside a readable centered
container. `.container` is `min(100% - 3rem, 80rem)`; below 36rem it uses 2rem total gutters.
Both Heroes are outside or structured independently of the constrained text area.
Use width 100% on a correctly placed block, not 100vw plus negative margins. Never hide
a sizing bug with body overflow-x hidden. Keep image frame dimensions stable before load.

## Responsive Behavior

Current common breakpoints: 68rem navigation, 64rem grids/venues, 48rem splits/details,
36rem mobile; homepage events use 47rem and partner grid also 24rem. These differences
are not necessarily bugs but should be explained before adding more breakpoints.
Use minmax(0,1fr), wrapping controls, clamped headings/spacing, cover/contain appropriately.
Preserve the agreed desktop/tablet/mobile compositions. Existing test targets:
1920x1080, 1440x900, 1366x768, 1024x768, 768x1024, 430x932, 390x844, 375x667.
These are recommended regression targets; this documentation audit did not rerun browser QA.

## Accessibility

Semantic structure, logical headings, useful identity-neutral alt text and visible focus.
Empty alt may be correct for decorative media with equivalent nearby information. English
labels; preserve French title language. Do not hide core content when JS fails. Respect
reduced motion for both Heroes, reveal, ambient animation, video and partner strip.
Keep keyboard-accessible controls where present and preserve menu Escape/focus behavior.

Contrast must be checked in context before any palette changes; a token alone is not a
contrast guarantee. The current control-free autoplay treatment was explicitly requested;
it is not a declaration of WCAG compliance. A later accessibility review should address
pause mechanisms and video alternatives with the user rather than silently restore controls.

## Do / Don't Examples

- Do use the current header wordmark; do not replace it with the small white-background JPG.
- Do contain a partner logo; do not crop it into a photographic frame.
- Do crop wide photographs responsively; do not compress them horizontally.
- Do preserve GIFA embedded wording; do not invent award recipients or judging criteria.
- Do reuse --color-primary; do not introduce a slightly different blue for each button.
- Do keep Contact greens scoped; do not spread an unrelated new gradient across the site.
- Do keep public titles and text approved; do not translate official French course names.
- Do record asset source and usage; do not delete originals because a copy is currently rendered.

## Items Requiring Business Confirmation

- Official color specifications and any gold/monochrome/inverse palette.
- Definitive logo masters, permitted variants, minimum sizes and clear-space rules.
- Whether header/footer wordmarks should eventually use the same composition.
- Official typography, if a corporate font manual exists.
- Rights/provenance for user-provided external-looking illustrations and updated media masters.
- Catalogue/pricing UX, currently frozen; USD and DJF only, no EUR.
- Accessibility choices for autoplay and video controls.
- Future news/blog editorial roles, SEO ownership and CMS selection.

No missing brand guidance was filled by inventing official rules in this task.
