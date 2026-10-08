<?php
ini_set('display_errors', '0');
require __DIR__ . '/includes/cms.php';
$site = require __DIR__ . '/data/site.php'; $activePage = 'blog';
$slug = $_GET['slug'] ?? ''; $article = null; $unavailable = false;
try {
    $article = is_string($slug) ? cmsArticle($slug) : null;
    if (!$article) http_response_code(404);
    $body = $article ? cmsContent($article['content']) : '';
} catch (Throwable $e) { $article = null; $unavailable = true; http_response_code(503); header('Retry-After: 300'); }
$pageTitle = ($article ? $article['title'] : ($unavailable ? 'News unavailable' : 'Article not found')) . ' | Salaam Center';
$pageDescription = $article ? $article['excerpt'] : 'Read news and updates from Salaam Center.';
header('Cache-Control: no-store');
require __DIR__ . '/includes/head.php'; require __DIR__ . '/includes/header.php';
?>
<main id="main-content" class="info-page"><div class="container">
<nav class="breadcrumbs" aria-label="Breadcrumb"><a href="index.php">Home</a><span aria-hidden="true">/</span><a href="blog.php">News</a><span aria-hidden="true">/</span><span aria-current="page"><?= escapeHtml($article['title'] ?? 'Article') ?></span></nav>
<?php if (!$article): ?><header class="catalog-heading"><h1><?= $unavailable ? 'News temporarily unavailable' : 'Article not found' ?></h1><p><?= $unavailable ? 'Please try again later.' : 'This article is not available.' ?></p></header><?php else: ?>
<article class="blog-article"><header class="catalog-heading"><p class="courses-eyebrow">News</p><h1><?= escapeHtml($article['title']) ?></h1><p><time datetime="<?= escapeHtml(str_replace(' ', 'T', $article['published_at']) . 'Z') ?>"><?= escapeHtml(gmdate('j F Y', strtotime($article['published_at'] . ' UTC'))) ?></time></p><p><?= escapeHtml($article['excerpt']) ?></p></header>
<?php if ($article['featured_image'] && is_file(cmsMediaPath($article['featured_image']))): $size = getimagesize(cmsMediaPath($article['featured_image'])); ?><img class="blog-featured" src="media.php?file=<?= escapeHtml($article['featured_image']) ?>" alt="<?= escapeHtml($article['image_alt']) ?>" width="<?= (int) ($size[0] ?? 1200) ?>" height="<?= (int) ($size[1] ?? 800) ?>" fetchpriority="high"><?php endif; ?>
<div class="blog-body"><?= $body ?></div></article><?php endif; ?>
<p class="catalog-back"><a href="blog.php">← Back to News</a></p>
</div></main><?php require __DIR__ . '/includes/footer.php'; ?></body></html>
