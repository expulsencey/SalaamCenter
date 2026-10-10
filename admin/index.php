<?php
require __DIR__ . '/../includes/cms-auth.php';
require __DIR__ . '/_layout.php';
require __DIR__ . '/_fields.php';
require __DIR__ . '/../includes/course-store.php';
$user = cmsRequireUser();
$error = '';
try {
    courseStoreReady();
    $pdo = cmsDatabase();
    $courses = $pdo->query('SELECT status,COUNT(*) AS total FROM cms_courses GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);
    $articles = $pdo->query('SELECT status,COUNT(*) AS total FROM cms_articles GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);
    $sessionCount = (int) $pdo->query('SELECT COUNT(*) FROM cms_course_sessions')->fetchColumn();
    $q = $pdo->prepare("SELECT COUNT(*) FROM cms_course_sessions s JOIN cms_courses c ON c.id=s.course_id WHERE s.status='upcoming' AND c.status='published' AND s.start_date>=?");
    $q->execute([courseToday()]);
    $upcoming = (int) $q->fetchColumn();
    $q = $pdo->prepare("SELECT s.id,s.start_date,JSON_UNQUOTE(JSON_EXTRACT(c.data,'$.name')) AS title,JSON_UNQUOTE(JSON_EXTRACT(c.data,'$.title_language')) AS title_language FROM cms_course_sessions s JOIN cms_courses c ON c.id=s.course_id WHERE s.status='upcoming' AND c.status='published' AND s.start_date>=? ORDER BY s.start_date,s.id LIMIT 3");
    $q->execute([courseToday()]);
    $schedule = $q->fetchAll();
    $recentCourses = $pdo->query("SELECT id,JSON_UNQUOTE(JSON_EXTRACT(data,'$.name')) AS title,JSON_UNQUOTE(JSON_EXTRACT(data,'$.title_language')) AS title_language,status,updated_at FROM cms_courses ORDER BY updated_at DESC,id DESC LIMIT 4")->fetchAll();
    $recentArticles = $pdo->query('SELECT id,title,status,updated_at FROM cms_articles ORDER BY updated_at DESC,id DESC LIMIT 4')->fetchAll();
} catch (Throwable $e) {
    http_response_code(503);
    $error = 'Content is temporarily unavailable. Please try again later or ask the site owner to check setup.';
}
adminHead('Dashboard');
?>
<p class="dashboard-intro">Welcome, <?= escapeHtml($user['display_name']) ?>. Manage your catalogue, training schedule and articles.</p>
<div class="actions dashboard-actions" aria-label="Quick actions">
    <a class="button primary" href="course-edit.php">Add course</a>
    <a class="button" href="session-edit.php">Add training session</a>
    <a class="button" href="article-edit.php">New article</a>
</div>
<?php if ($error): adminNotice($error); else: ?>
<section class="dashboard-section" aria-labelledby="overview-heading">
    <h2 id="overview-heading">Content overview</h2>
    <div class="overview-grid">
    <?php foreach ([
        ['Courses', [
            ['Total courses', array_sum($courses), 'courses.php'],
            ['Published courses', $courses['published'] ?? 0, 'courses.php?status=published'],
            ['Draft courses', $courses['draft'] ?? 0, 'courses.php?status=draft'],
        ]],
        ['Training Sessions', [
            ['Total training sessions', $sessionCount, 'sessions.php'],
            ['Upcoming for published courses', $upcoming, 'sessions.php?upcoming=1'],
        ]],
        ['Articles', [
            ['Published articles', $articles['published'] ?? 0, 'articles.php?status=published'],
            ['Draft articles', $articles['draft'] ?? 0, 'articles.php?status=draft'],
        ]],
    ] as [$heading, $metrics]): ?>
        <section class="overview-group">
            <h3><?= $heading ?></h3>
            <ul>
            <?php foreach ($metrics as [$label, $count, $href]): ?>
                <li><a class="overview-item" href="<?= $href ?>"><span><?= $label ?></span><strong><?= (int) $count ?></strong></a></li>
            <?php endforeach; ?>
            </ul>
        </section>
    <?php endforeach; ?>
    </div>
    <p class="help">Upcoming sessions include today and future dates for published courses.</p>
</section>
<div class="dashboard-columns">
<?php foreach ([
    ['Recently updated courses', $recentCourses, 'courses.php', 'course-edit.php', 'No courses yet.', 'Add your first course', 'A new course starts as a private draft.'],
    ['Recent articles', $recentArticles, 'articles.php', 'article-edit.php', 'No articles yet.', 'Create your first article', 'Articles stay private until you publish them.'],
] as [$heading, $items, $listing, $editor, $empty, $action, $help]): ?>
    <section class="dashboard-section dashboard-activity">
        <div class="page-actions"><h2><?= $heading ?></h2><a href="<?= $listing ?>">View all</a></div>
        <?php if (!$items): ?>
        <div class="empty-state"><p><?= $empty ?></p><p><?= $help ?></p><a class="button" href="<?= $editor ?>"><?= $action ?></a></div>
        <?php else: ?>
        <p class="help">Latest updates · UTC</p>
        <ul class="recent-list">
        <?php foreach ($items as $item): ?>
            <li>
                <a href="<?= $editor ?>?id=<?= (int) $item['id'] ?>"<?= ($item['title_language'] ?? '') === 'fr' ? ' lang="fr"' : '' ?>><?= escapeHtml($item['title']) ?></a>
                <?php adminBadge($item['status']); ?>
                <time datetime="<?= escapeHtml(str_replace(' ', 'T', $item['updated_at'])) ?>Z"><?= escapeHtml(adminDate($item['updated_at'])) ?></time>
            </li>
        <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </section>
<?php endforeach; ?>
</div>
<section class="dashboard-section dashboard-schedule">
    <div class="page-actions"><h2>Next training sessions</h2><a href="sessions.php?upcoming=1">View upcoming</a></div>
    <?php if (!$schedule): ?>
    <div class="empty-state">
        <p><?= $sessionCount === 0 ? 'No training sessions yet.' : 'No upcoming sessions are currently scheduled for published courses.' ?></p>
        <p>Choose an existing course to schedule its next training session.</p>
        <a class="button" href="session-edit.php">Add training session</a>
    </div>
    <?php else: ?>
    <ul class="schedule-list">
    <?php foreach ($schedule as $session): ?>
        <li><time datetime="<?= escapeHtml($session['start_date']) ?>"><?= escapeHtml(adminDate($session['start_date'], false)) ?></time><a href="session-edit.php?id=<?= (int) $session['id'] ?>"<?= $session['title_language'] === 'fr' ? ' lang="fr"' : '' ?>><?= escapeHtml($session['title']) ?></a></li>
    <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</section>
<?php endif; adminFoot(); ?>
