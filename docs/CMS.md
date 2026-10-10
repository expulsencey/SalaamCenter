# Salaam Center staff guide

Current workflow: Phase 2A, 8 October 2026. This guide replaces the older installation
and expansion instructions. The existing installation is ready; do not repeat its
migration/import or recreate the administrator. Technical operations are documented
in [ARCHITECTURE.md](ARCHITECTURE.md#cms-operations-and-security).

## Sign in and find your work

Open `http://localhost/SalaamCenter/admin/login.php` and use your staff account.
If access fails, contact the site owner. Do not share passwords.

On desktop, a shared sidebar appears throughout administration: **Dashboard**, **Courses**,
**Training Sessions**, **Articles**, **Media**, and **Events & Partners**. The current
section is highlighted, including inside its editors and previews. On a tablet or
small screen, open **Menu**; its closed label also identifies the current section.
Escape closes it and returns keyboard focus to Menu. Navigation also works without
JavaScript. **View website** opens the public site. **Log out** ends your session.
The navigation shows your signed-in name and email; long account details wrap.

## Dashboard

The dashboard shows total, published and draft courses, total training sessions,
and published/draft articles. Counts link to the corresponding lists and filters
and adapt to the database contents. Recently updated courses and recent articles
have separate lists; the next three eligible training sessions show their dates.
Choose **Add course**, **Add training session** or
**New article** to start. Select a count to open its list. Upcoming training counts
only upcoming sessions dated today or later whose courses are published.
When a list is empty, the dashboard explains how to start. If content cannot be
loaded, it displays an unavailable message instead of misleading zero counts.
Events and Partners remain view-only references in navigation. There are no visitor
or sales analytics.

## Find and edit a course

1. Open **Courses**. Search by title or URL name, filter by category/status and use
   the page links when needed. **Clear** resets filters.
2. Select **Edit**, or **Add course** for a genuinely new catalogue entry.
3. Use **On this page** to move between Basics, Image, Information, Content and
   Presentation. Preserve official course names, including French accents. Write
   interface descriptions and image descriptions in English.
4. Enter only confirmed information. Leave unknown optional fields empty. A course
   is a permanent catalogue entry; use a training session for a new delivery date.
5. Choose an existing image or upload one and describe what it shows. An uploaded
   file takes priority over the selected existing image when you save.
6. Use **Add item** and **Remove** for outcomes, included content, certification,
   requirements and evaluation. Each row is one item. No HTML or JSON is needed.
7. Set the display order and homepage selection if appropriate. The homepage is a
   selected showcase; it does not automatically display every course.
8. **Save draft**, then **Preview saved version**. Publish only after checking it.

The URL name is generated when left blank. After first publication it stays fixed,
even if you change the title or temporarily unpublish the course. Published edits
appear automatically in the catalogue, category pages, details and selected homepage
cards. No manual copying or database administration is required.

## Training sessions

Open **Training Sessions**, then select **Add training session** or edit an existing
entry. Choose its course, verified date, optional duration/language and status:

- **Draft**: private preparation; a date can remain blank.
- **Upcoming**: a date is required. It appears publicly only from today onward and
  only while the course is published.
- **Completed**: retained as a record, excluded from upcoming information.

Blank duration/language uses the course information. Completing a session does not
remove the course. Past dates stay stored without automatic status changes. A second
session for the same course/date produces a warning; confirm only if intentional.
Use search, course/status filters and pagination to find a session. The dashboard's
upcoming link opens a filtered list; **Clear** returns to all sessions.

## Write an article

1. Open **Articles**, then **New article**. Search and status filters help find existing
   articles. The list shows update and first publication information when available.
2. Enter an approved title and short summary. Leave URL name blank to generate it.
3. Optionally select an attached upload or upload a featured image. Add an English
   image description whenever an image is used.
4. Add content blocks: paragraphs, section headings, subsection headings or lists.
   A subsection should follow a section heading. For lists, enter one item per line.
5. Select text to use **Bold**, **Italic** or **Link**. The editor displays simple
   formatting markers; Preview shows the formatted result. Pasted HTML is plain text.
6. Use **Add content block**, **Move up**, **Move down** or **Remove block** to organize
   the article. Removing a populated block asks for confirmation.
7. **Save draft**, open **Preview saved version**, then **Publish** when approved.
   Publication requires a title, summary and body. Published titles/summaries must
   be distinct. First publication time and the URL remain stable on later updates.

## Draft, published, preview and feedback

**Save draft** keeps content private. **Publish** makes it public. **Update published
course/article** immediately replaces the public version. To prepare a revision
privately, first choose **Unpublish and save as draft**. There is no staged revision
or autosave system.

**Preview saved version** opens an authenticated preview in a separate tab. It never
saves and does not include unsaved changes. Save first to preview your latest edit.
Never share an administrator preview URL as a public article link.

A success message confirms the operation. Errors above the form explain corrections
needed. If another window saved first, reload the record and reconcile your changes
before saving again. Unsaved edits are marked, and supported browsers warn when
leaving an edited form. If validation rejects an upload, select the file again.
Timestamps shown with **UTC** use that timezone; session dates are calendar dates.

## Media

**Media** shows uploaded images attached to courses/articles, with their current uses.
Search by the attached content's title. **Use for new course/article** starts an editor
with that image selected; it does not create or publish content by itself.

To upload, open the course/article editor and select **Upload image**, then save.
Allowed files: JPEG, PNG or WebP, up to 5 MB, 12 megapixels and 6000 pixels per side.
Use approved images only. The system validates and converts uploads to WebP.
Existing course photographs are also selectable in the course editor.

An upload is public while any published course/article uses it. Otherwise only
signed-in staff can access it. Removing one reference does not remove other uses.
Replaced or detached uploads are retained privately for recovery; they are not
listed as attached media. There is no permanent image deletion tool.

## Remove content safely

Unpublishing is the usual way to retire a course or article without losing it.
For permanent deletion, open **Retire or delete this course/article** in its editor.
Check the displayed title, type its exact URL name and choose permanent deletion.

Only a saved draft can be deleted. A course with linked sessions cannot be deleted;
unpublish it instead. Another editor's changes invalidate an old deletion request.
Deletion cannot be undone through the interface. Images are retained. There is no
training-session deletion tool.

## Current limits and content rules

Course and training prices are not displayed or managed. Historical amounts remain
inactive for reference only. Venue rental rates are a separate, unchanged feature.
Do not invent dates, learning outcomes, statistics or other business information.
Events/Partners are read-only; Home/About/contact/brand settings are not CMS editors.

Finish by selecting **Log out**, especially on a shared computer.
See [Phase 2A validation](CMS-PHASE2A-VALIDATION.md) for the current dashboard and
navigation checks and limits. [Phase 2 validation](CMS-PHASE2-VALIDATION.md) records
the earlier editor/media implementation; those workflows were preserved.
