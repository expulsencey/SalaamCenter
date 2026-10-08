# Cloudflare deployment

## Source and generated copy

The PHP/WAMP website remains authoritative at `http://localhost/SalaamCenter/`.
`cloudflare-dist/` is disposable generated output. Never edit it manually.
The exporter requests rendered pages from WAMP; it does not convert or replace
the PHP source. Cloudflare serves the resulting frontend using Workers Static
Assets, with no Worker backend, database or account credentials in this project.

## First-time setup

Use Node.js 22.14 or newer and the existing WAMP PHP installation with DOM enabled.
From a terminal in `C:\wamp64\www\SalaamCenter`, run `npm ci` to install the pinned
Wrangler dependency from package-lock.json. No global Wrangler is required.
If PowerShell blocks npm.ps1, use `npm.cmd` in place of `npm` below.

The launcher finds a working PHP executable under WAMP's bin/php directory, then
falls back to PHP on PATH. To choose one explicitly in PowerShell:

```powershell
$env:PHP_BINARY = 'C:\wamp64\bin\php\php8.3.14\php.exe'
```

Use your installed PHP path. This variable is not a credential.

## Normal workflow

1. Start WAMP and verify the PHP site in your browser.
2. Make changes in PHP/data/assets, never in cloudflare-dist.
3. To export and validate without publishing: `npm run export`.
4. To inspect the static copy: `npm run preview` (normally http://localhost:8787).
   Stop the preview with Ctrl+C. This is separate from the WAMP site.
5. When ready to publish manually: `npm run deploy`.

Deploy always exports again and invokes Wrangler only if export validation succeeds.
Cloudflare account authentication is a separate manual prerequisite; no login or
deployment was performed during setup. Never place credentials in these files.

## Route mapping

| PHP source | Generated URL |
|---|---|
| index.php | / |
| about.php | /about/ |
| courses.php | /courses/ |
| course.php?slug=... | /courses/.../ |
| categories.php | /categories/ |
| category.php?slug=... | /categories/.../ |
| events.php | /events/ |
| event.php?slug=... | /events/.../ |
| partners.php | /partners/ |
| contact.php | /contact/ |
| sc-business.php | /sc-business/ |

Dynamic slugs come from the current authoritative PHP datasets. Initial output:
40 pages, including 24 courses, six categories and two event detail pages.
Invalid slugs are not exported. Unknown paths receive Cloudflare's 404 response;
this is not a single-page application fallback.
Fragments are retained, as are external links, email and telephone URLs.
Venue contact links retain their space query; the static Contact fallback is generic
and does not render server-selected venue labels. Include the desired venue in your email.

## Contact limitation

Only in the generated copy, the PHP form and session CSRF token are removed.
A panel in the same presentation area explains that online submission is unavailable
and offers the verified email and telephone methods from data/site.php.
No fake submission or success message is provided. PHP forms continue unchanged on
WAMP. Any future server-side features require a separate architecture decision.

## Assets and validation

Only referenced local frontend assets are copied, preserving their asset paths.
URLs become /assets/...; CSS url() dependencies are followed. Existing external
Google Fonts links remain external. JS, currency display, sliders and video behavior
are retained. Images and videos are not re-encoded. The initial export has 74 assets.
Assets over 25 MiB cause export failure instead of a later upload surprise.

The exporter checks HTTP success, UTF-8, rendered PHP error markers, known routes,
local files, allowed asset extensions and generated link targets. It rejects residual
PHP links, localhost/Windows paths and session tokens. Unknown forms, inline styles
or srcset need an explicit exporter adaptation; they are not silently ignored.

No PHP, includes, data files, documentation, source configuration, secrets, logs,
screenshots, browser profiles, node_modules or Git metadata are published.
`.generated` is a local ownership marker excluded from upload by `.assetsignore`.

Only the fixed cloudflare-dist path can be rebuilt. Existing nonempty output needs
the exact ownership marker; symlinks/redirected paths abort cleanup. The complete
tree is checked before removal. WAMP pages are fetched before cleanup, so unavailable
WAMP leaves the old output intact. Other failures may leave partial output; never
deploy that folder directly. Fix the error and run `npm run deploy`, which rebuilds.

## Local setup verification (6 October 2026)

The export and subsequent rebuild completed successfully: 40 pages, 74 public assets.
Wrangler local preview returned 200 for all 40 routes and all 74 assets; a missing
route, PHP source paths, private configuration and the ownership files returned 404.
The exported main.js is byte-identical to the PHP site's script. Independent checks
found no form/session token, forbidden output type or localhost/filesystem reference
in generated HTML. A deliberately invalid ownership marker blocked cleanup and
preserved existing output; the valid marker was restored afterward.
No Cloudflare authentication or actual deployment was performed. Publishing to a
particular account/domain still needs the user's manual configuration and review.

## Configuration references

The minimal wrangler.jsonc uses assets.directory, auto-trailing-slash HTML routing
and 404-page handling, without a Worker script. Compatibility date: 2026-10-06.
Sources: [Cloudflare static asset configuration](https://developers.cloudflare.com/workers/static-assets/binding/)
and [HTML routing](https://developers.cloudflare.com/workers/static-assets/routing/advanced/html-handling/).
