<?php
require __DIR__.'/../includes/cms-auth.php';
require __DIR__.'/../includes/course-store.php';
require __DIR__.'/_layout.php';
cmsRequireUser();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST'); http_response_code(405); exit('Use the course editor to request deletion.');
}
cmsCheckCsrf();
$id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$version=filter_var($_POST['version']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$pdo=null;
try {
    if (!$id || !$version) throw new InvalidArgumentException('Choose a saved course and reload its editor.');
    $pdo=cmsDatabase(); $pdo->beginTransaction();
    $q=$pdo->prepare('SELECT id,slug,status,version FROM cms_courses WHERE id=? FOR UPDATE');
    $q->execute([$id]); $row=$q->fetch();
    if (!$row || (int)$row['version']!==$version) throw new InvalidArgumentException('This course changed. Reload its editor before deleting.');
    if (cmsText($_POST['confirm_slug']??'',190)!==$row['slug']) throw new InvalidArgumentException('Type the exact URL name to confirm permanent deletion.');
    if ($row['status']!=='draft') throw new InvalidArgumentException('Unpublish the course before requesting permanent deletion.');
    $q=$pdo->prepare('SELECT COUNT(*) FROM cms_course_sessions WHERE course_id=?'); $q->execute([$id]);
    if ($q->fetchColumn()) throw new InvalidArgumentException('This course cannot be deleted because training sessions exist. Keep it as a draft to retire it.');
    $q=$pdo->prepare('DELETE FROM cms_courses WHERE id=? AND version=?'); $q->execute([$id,$version]);
    $pdo->commit(); $_SESSION['cms_notice']='Course permanently deleted. Its images have been retained.'; header('Location: courses.php',true,303); exit;
} catch (Throwable $e) {
    if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
    $expected=$e instanceof InvalidArgumentException;
    http_response_code($expected?422:503);
    adminHead('Course not deleted');
    echo '<p class="notice error" role="alert">'.escapeHtml($expected?$e->getMessage():'The course could not be deleted. Please reload and try again.').'</p>';
    echo '<p><a href="courses.php">Return to courses</a></p>'; adminFoot();
}
