<?php
// Requires $site, escapeHtml(), $activePage, $pageTitle, $pageHeading and $pageIntro.
// Events also requires $events and $homeContent; Contact requires contact-handler.php
// before output (see contact-section.php); SC Business requires $pageActionUrl/$pageActionLabel.

if (!isset($activePage, $pageTitle, $pageHeading, $pageIntro) || !function_exists('escapeHtml')) { http_response_code(404); exit; }
require __DIR__ . '/head.php';
require __DIR__ . '/header.php';
?>
<main id="main-content" class="info-page">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="index.php">Home</a><span aria-hidden="true">/</span><span aria-current="page"><?= escapeHtml($pageHeading) ?></span></nav>
        <header class="catalog-heading">
            <p class="courses-eyebrow">Salaam Center</p><h1><?= escapeHtml($pageHeading) ?></h1><p><?= escapeHtml($pageIntro) ?></p>
        </header>
        <?php if ($activePage === 'events'): ?>
            <p><a class="text-link" href="blog.php">Read news from Salaam Center →</a></p>
            <h2 class="info-subheading">Learning in Action.</h2>
            <div class="event-list"><?php foreach ($events as $event) require __DIR__ . '/event-card.php'; ?></div>
            <h2 class="info-subheading event-archive-heading">From the Archive</h2>
            <?php require __DIR__ . '/news-list.php'; ?>
        <?php elseif ($activePage !== 'contact'): ?>
            <div class="info-actions"><a class="hero-button courses-catalog" href="<?= escapeHtml($pageActionUrl) ?>"><?= escapeHtml($pageActionLabel) ?></a><a class="text-link" href="contact.php">Contact Our Team →</a></div>
        <?php endif; ?>

    </div>
    <?php if ($activePage === 'contact') require __DIR__ . '/contact-section.php'; ?>
</main>
<?php require __DIR__ . '/footer.php'; ?>
</body>
</html>
