<?php
if (!isset($pageTitle, $activePage) || !function_exists('escapeHtml')) { http_response_code(404); exit; }
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (!empty($pageDescription)): ?><meta name="description" content="<?= escapeHtml($pageDescription) ?>"><?php endif; ?>
    <title><?= escapeHtml($pageTitle) ?></title>
    <link rel="icon" type="image/jpeg" href="assets/images/WhatsApp%20Image%202026-09-30%20at%2015.54.14.jpeg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
    <script src="assets/js/main.js?v=<?= filemtime(__DIR__ . '/../assets/js/main.js') ?>" defer></script>
</head>
<body class="ambient-page<?= $activePage === 'home' ? ' ambient-page--home' : '' ?>">
    <div class="page-ambient-hero" aria-hidden="true"></div>
