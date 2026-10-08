<?php
ini_set('display_errors', '0');
require __DIR__ . '/includes/cms.php';
$site = require __DIR__ . '/data/site.php';
$activePage = 'blog'; $pageTitle = 'News | Salaam Center';
$pageDescription = 'Read news and updates published by the Salaam Center team.';
$unavailable = false;
try { $articles = cmsPublished(); }
catch (Throwable $e) { $articles = []; $unavailable = true; http_response_code(503); header('Retry-After: 300'); }
header('Cache-Control: no-store');
require __DIR__ . '/includes/head.php'; require __DIR__ . '/includes/header.php';
?>
<main id="main-content" class="info-page"><div class="container">
<nav class="breadcrumbs" aria-label="Breadcrumb"><a href="index.php">Home</a><span aria-hidden="true">/</span><span aria-current="page">News</span></nav>
<header class="catalog-heading"><p class="courses-eyebrow">From Salaam Center</p><h1>News</h1><p><?= escapeHtml($pageDescription) ?></p></header>
<?php if ($unavailable): ?><p>News is temporarily unavailable. Please try again later.</p><?php elseif (!$articles): ?><p>No articles have been published yet.</p><?php else: ?>
<div class="blog-list"><?php foreach ($articles as $article): ?><article class="blog-summary">
<?php if ($article['featured_image']): ?><a href="article.php?slug=<?= escapeHtml($article['slug']) ?>" tabindex="-1" aria-hidden="true"><img src="media.php?file=<?= escapeHtml($article['featured_image']) ?>" alt="" loading="lazy" width="1200" height="800"></a><?php endif; ?>
<div><p><time datetime="<?= escapeHtml(str_replace(' ', 'T', $article['published_at']) . 'Z') ?>"><?= escapeHtml(gmdate('j F Y', strtotime($article['published_at'] . ' UTC'))) ?></time></p><h2><a href="article.php?slug=<?= escapeHtml($article['slug']) ?>"><?= escapeHtml($article['title']) ?></a></h2><p><?= escapeHtml($article['excerpt']) ?></p><a class="text-link" href="article.php?slug=<?= escapeHtml($article['slug']) ?>">Read article<span class="visually-hidden">: <?= escapeHtml($article['title']) ?></span> →</a></div>
</article><?php endforeach; ?></div><?php endif; ?>
</div></main>
<?php require __DIR__ . '/includes/footer.php'; ?></body></html>
