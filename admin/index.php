<?php
require __DIR__ . '/../includes/cms-auth.php';
require __DIR__ . '/_layout.php'; require __DIR__ . '/_fields.php';
require __DIR__ . '/../includes/course-store.php';
$user = cmsRequireUser();
$events=require __DIR__.'/../data/events.php';$partners=require __DIR__.'/../data/partners.php';$recent=[];$schedule=[];
try {
    courseStoreReady();$pdo=cmsDatabase();
    $counts = $pdo->query('SELECT status,COUNT(*) AS total FROM cms_articles GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);
    $courseCount=(int)$pdo->query('SELECT COUNT(*) FROM cms_courses')->fetchColumn();
    $q=$pdo->prepare("SELECT COUNT(*) FROM cms_course_sessions s JOIN cms_courses c ON c.id=s.course_id WHERE s.status='upcoming' AND c.status='published' AND s.start_date>=?");$q->execute([courseToday()]);$upcoming=(int)$q->fetchColumn();
    $q=$pdo->prepare("SELECT s.id,s.start_date,JSON_UNQUOTE(JSON_EXTRACT(c.data,'$.name')) AS title FROM cms_course_sessions s JOIN cms_courses c ON c.id=s.course_id WHERE s.status='upcoming' AND c.status='published' AND s.start_date>=? ORDER BY s.start_date,s.id LIMIT 3");$q->execute([courseToday()]);$schedule=$q->fetchAll();
    $recent=$pdo->query("SELECT id,JSON_UNQUOTE(JSON_EXTRACT(data,'$.name')) AS title,status,updated_at,'course-edit.php' AS editor,'Course' AS kind FROM cms_courses UNION ALL SELECT id,title,status,updated_at,'article-edit.php','Article' FROM cms_articles ORDER BY updated_at DESC LIMIT 8")->fetchAll();
}catch (Throwable $e) { $counts = []; http_response_code(503); }
adminHead('Dashboard');
?>
<p>Welcome, <?= escapeHtml($user['display_name']) ?>.</p>
<?php if (http_response_code() === 503): ?><p class="notice error" role="alert">Content is temporarily unavailable. Please ask the site owner to check the migration.</p><?php else: ?>
<div class="actions dashboard-actions"><a class="button primary" href="course-edit.php">Add course</a><a class="button" href="session-edit.php">Add training session</a><a class="button" href="article-edit.php">New article</a></div>
<section class="dashboard-section"><h2>Content overview</h2><div class="overview-grid"><?php foreach([['Courses',$courseCount,'courses.php'],['Upcoming training',$upcoming,'sessions.php?upcoming=1'],['Published articles',$counts['published']??0,'articles.php?status=published'],['Draft articles',$counts['draft']??0,'articles.php?status=draft'],['Events',count($events),'references.php'],['Partners',count($partners),'references.php']] as [$label,$count,$href]): ?><a class="overview-item" href="<?= $href ?>"><span><?= $label ?></span><strong><?= (int)$count ?></strong><span class="overview-link">View <span aria-hidden="true">→</span></span></a><?php endforeach; ?></div></section>
<div class="dashboard-columns"><section class="dashboard-section"><div class="page-actions"><h2>Recently updated</h2><p>Latest course and article changes · UTC</p></div><ul class="recent-list"><?php foreach($recent as $item): ?><li><a href="<?= $item['editor'] ?>?id=<?= (int)$item['id'] ?>"><?= escapeHtml($item['title']) ?></a><span><?= $item['kind'] ?></span><?php adminBadge($item['status']); ?><time datetime="<?= escapeHtml(str_replace(' ', 'T', $item['updated_at'])) ?>Z"><?= escapeHtml(adminDate($item['updated_at'])) ?></time></li><?php endforeach; ?></ul></section>
<section class="dashboard-section"><div class="page-actions"><h2>Next training sessions</h2><a href="sessions.php?upcoming=1">View upcoming</a></div>
<?php if(!$schedule): ?><p class="empty-state">No upcoming sessions are currently scheduled for published courses.</p><?php else: ?><ul class="schedule-list"><?php foreach($schedule as $session): ?><li><time datetime="<?= escapeHtml($session['start_date']) ?>"><?= escapeHtml(adminDate($session['start_date'],false)) ?></time><a href="session-edit.php?id=<?= (int)$session['id'] ?>"><?= escapeHtml($session['title']) ?></a></li><?php endforeach; ?></ul><?php endif; ?></section></div>
<?php endif; adminFoot(); ?>
