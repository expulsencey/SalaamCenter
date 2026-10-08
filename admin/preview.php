<?php
require __DIR__ . '/../includes/cms-auth.php';
require __DIR__ . '/_layout.php';
cmsRequireUser();
$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
try {
    $q = cmsDatabase()->prepare('SELECT * FROM cms_articles WHERE id=?'); $q->execute([$id ?: 0]); $article = $q->fetch();
    if (!$article) { http_response_code(404); exit('Article not found.'); }
    $body = cmsContent($article['content']);
} catch (Throwable $e) { http_response_code(503); exit('Preview is temporarily unavailable.'); }
adminHead('Article preview');
?>
<p class="notice preview-notice">Saved version preview · <?= escapeHtml(ucfirst($article['status'])) ?></p><p><a href="article-edit.php?id=<?= (int) $article['id'] ?>">Back to editor</a></p>
<article class="preview"><h2><?= escapeHtml($article['title']) ?></h2><p><?= escapeHtml($article['excerpt']) ?></p><?php if ($article['featured_image']): ?><img class="editor-image" src="../media.php?file=<?= escapeHtml($article['featured_image']) ?>" alt="<?= escapeHtml($article['image_alt']) ?>"><?php endif; ?><div class="article-body"><?= $body ?></div></article>
<?php adminFoot(); ?>
