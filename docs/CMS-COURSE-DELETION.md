# Permanent course deletion validation

Validated locally on 10 October 2026. Scope: course deletion only; no Phase 2B work.

## 1. Previous behavior

The editor required a draft course and a typed slug. Courses with training sessions could not be deleted.

## 2. Cause

The draft and slug requirements were explicit PHP application checks, not database limitations. The session restriction remains necessary for the existing foreign key integrity.

## 3. Files

Modified:

- `admin/course-edit.php`: saved course title, warning and confirmation form.
- `admin/course-delete.php`: direct published deletion; counted session refusal and management link.
- `assets/js/admin.js`: accessible native dialog; focus restoration; avoid a redundant dirty-form prompt after explicit confirmation.
- `assets/css/admin.css`: scoped responsive dialog styling.
- `scripts/check-cms-courses.py`: align historical deletion assertions with the new policy; remove unnecessary foreign-key disabling in isolated fixture restoration.
- `docs/CMS.md` and `docs/ARCHITECTURE.md`: current deletion policy.

Created:

- `scripts/check-course-deletion.py`: isolated synthetic regression suite.
- `docs/CMS-COURSE-DELETION.md`: this report.

Private QA backup, results, logs and screenshot are stored under protected `storage/cms-qa/`. They are not public content.

## 4. New workflow

Open a saved course, select **Delete course**, then choose **Cancel** or **Delete permanently**. The dialog shows the saved title and explains permanent removal from the public website. No slug entry or prior unpublishing is required. Cancel receives initial focus; Escape closes the dialog and restores trigger focus. Without JavaScript, the server-rendered confirmation remains available inline.

## 5. Database behavior

Authenticated POST, CSRF, positive ID/version validation, existence and stale-version checks precede deletion. A transaction locks the course row, counts all associated sessions and deletes exactly one course using prepared SQL. Any associated session blocks deletion with its count and a management link. FK RESTRICT remains enabled; no cascading deletion, schema migration or reimport was performed. Image files are retained.

## 6. Public synchronization

The isolated published fixture was visible before deletion on the catalogue, category, homepage and sitemap. After deletion, it was absent from those outputs and public/admin search listings. The former detail URL returned 404. The existing MySQL course service and public templates were unchanged. Successful deletion redirected to the course list with the existing success notice and the updated count.

## 7. Security validation

Rejected unauthenticated requests, GET deletion, invalid CSRF, invalid IDs, nonexistent IDs and stale versions. Failed requests preserved all synthetic course rows and the associated session. The dependency refusal did not show a success notice. Escaping, authorization and session protection remain in place. The private database backup returned HTTP 403 through Apache.

## 8. Synthetic test results

`python scripts/check-course-deletion.py --browser`: **70 checks passed**. Tests used an isolated temporary database, synthetic administrator, four synthetic courses and one synthetic session. Draft and published courses without sessions were deleted successfully; a linked course was refused. Uploaded media and unrelated courses were preserved. The temporary database and test upload were removed afterward.

Native Edge dialog checks passed at widths 1440, 1366, 1024, 768, 390 and 375 pixels: viewport fit, no horizontal overflow, Cancel focus, keyboard access to confirmation, Escape focus restoration and cancellation without deletion. No JavaScript exceptions or PHP warnings were observed.

Both modified PHP files passed PHP 8.3.14 syntax checks. `python scripts/check-seo.py` passed for 42 pages, including all 25 course pages and 42 sitemap URLs. The historical broad CMS suite was updated but was not rerun; the dedicated deletion suite supplies this task's destructive coverage.

## 9. Real data preservation

A private backup was created before destructive tests. Comparison afterward confirmed identical schemas and rows for all seven original database tables, and identical original CMS media bytes. The real database retains **25 courses, 8 sessions and 1 administrator**, including the existing manually created test course. No real course, session or account was modified or deleted.

## 10. Limitations and follow-up

Uploaded files intentionally remain after course deletion. Any later orphan cleanup must verify references across all content before removing files. The native dialog was tested in Edge; unsupported browsers retain the inline confirmation. The private backup contains sensitive data and must remain protected. No deployment, Cloudflare change, Git operation, price restoration or unrelated redesign was performed.
