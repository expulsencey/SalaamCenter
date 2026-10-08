<?php
require_once __DIR__ . '/includes/helpers.php';
$site = require __DIR__ . '/data/site.php';
$partners = require __DIR__ . '/data/partners.php';
$activePage = 'partners';
$pageTitle = 'Our Educational Partners — Salaam Center';
$pageDescription = 'Collaborating with organizations across professional learning, certification and skills development.';
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/header.php';
?>
<main id="main-content" class="info-page partners-page">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="index.php">Home</a><span aria-hidden="true">/</span><span aria-current="page">Partners</span></nav>
        <header class="catalog-heading"><p class="courses-eyebrow">Our Network</p><h1>Our Educational Partners</h1><p><?= escapeHtml($pageDescription) ?></p></header>
        <?php require __DIR__ . '/includes/partner-strip.php'; ?>
        <ul class="partners-grid" aria-label="All educational partners">
            <?php foreach ($partners as $partner): ?>
                <li class="partner-card"><img class="partner-logo" src="<?= escapeHtml($partner['logo']) ?>" alt="<?= escapeHtml($partner['alt']) ?>" width="240" height="100" loading="lazy" decoding="async"><h2><?= escapeHtml($partner['name']) ?></h2></li>
            <?php endforeach; ?>
        </ul>
    </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
