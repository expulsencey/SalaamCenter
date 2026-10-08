<?php
if (!isset($pageTitle, $activePage) || !function_exists('escapeHtml')) { http_response_code(404); exit; }
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/seo.php';
$seoPage = pathinfo($_SERVER['SCRIPT_FILENAME'], PATHINFO_FILENAME);
$seoRecord = match ($seoPage) {
    'course' => $course ?? null,
    'category' => isset($category) ? $category + ['slug' => $slug] : null,
    'event' => $event ?? null,
    'article' => $article ?? null,
    default => null,
};
$seo = seoMetadata($seoPage, $pageTitle, $pageDescription ?? '', $site, $seoRecord);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if ($seo['description'] !== ''): ?><meta name="description" content="<?= escapeHtml($seo['description']) ?>"><?php endif; ?>
    <title><?= escapeHtml($seo['title']) ?></title>
    <?php require __DIR__ . '/seo-head.php'; ?>
    <link rel="icon" type="image/jpeg" href="assets/images/WhatsApp%20Image%202026-09-30%20at%2015.54.14.jpeg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
    <?php if (in_array($seoPage, ['blog','article'], true)): ?><link rel="stylesheet" href="assets/css/blog.css?v=<?= filemtime(__DIR__ . '/../assets/css/blog.css') ?>"><?php endif; ?>
    <script src="assets/js/main.js?v=<?= filemtime(__DIR__ . '/../assets/js/main.js') ?>" defer></script>
</head>
<body class="ambient-page<?= $activePage === 'home' ? ' ambient-page--home' : '' ?>">
    <div class="page-ambient-hero" aria-hidden="true"></div>
