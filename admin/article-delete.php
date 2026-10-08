<?php
require __DIR__.'/../includes/cms-auth.php';
require __DIR__.'/_layout.php';
cmsRequireUser();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST'); http_response_code(405); exit('Use the article editor to request deletion.');
}
cmsCheckCsrf();
$id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$version=filter_var($_POST['version']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$pdo=null;
try {
    if (!$id || !$version) throw new InvalidArgumentException('Choose a saved article and reload its editor.');
    $pdo=cmsDatabase(); $pdo->beginTransaction();
    $q=$pdo->prepare('SELECT id,slug,status,version FROM cms_articles WHERE id=? FOR UPDATE');
    $q->execute([$id]); $row=$q->fetch();
    if (!$row || (int)$row['version']!==$version) throw new InvalidArgumentException('This article changed. Reload its editor before deleting.');
    if (cmsText($_POST['confirm_slug']??'',190)!==$row['slug']) throw new InvalidArgumentException('Type the exact URL name to confirm permanent deletion.');
    if ($row['status']!=='draft') throw new InvalidArgumentException('Unpublish the article before requesting permanent deletion.');
    $q=$pdo->prepare('DELETE FROM cms_articles WHERE id=? AND version=?'); $q->execute([$id,$version]);
    $pdo->commit(); $_SESSION['cms_notice']='Article permanently deleted. Its images have been retained.'; header('Location: articles.php',true,303); exit;
} catch (Throwable $e) {
    if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
    $expected=$e instanceof InvalidArgumentException;
    http_response_code($expected?422:503);
    adminHead('Article not deleted');
    echo '<p class="notice error" role="alert">'.escapeHtml($expected?$e->getMessage():'The article could not be deleted. Please reload and try again.').'</p>';
    echo '<p><a href="articles.php">Return to articles</a></p>'; adminFoot();
}
