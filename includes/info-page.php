<?php
if (!isset($activePage, $pageTitle, $pageIntro) || !function_exists('escapeHtml')) { http_response_code(404); exit; }
require __DIR__ . '/head.php';
require __DIR__ . '/header.php';
?>
<main id="main-content" class="info-page">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="index.php">Home</a><span aria-hidden="true">/</span><span aria-current="page"><?= escapeHtml($pageHeading) ?></span></nav>
        <header class="catalog-heading<?= $activePage === 'about' ? ' about-heading' : '' ?>">
            <?php if ($activePage === 'about' && isset($pageIntroParagraphs)): ?>
                <div class="about-overview">
                    <div class="about-overview-copy">
                        <p class="courses-eyebrow">Salaam Center</p>
                        <h1><?= escapeHtml($pageHeading) ?></h1>
                        <div class="about-introduction"><?php foreach ($pageIntroParagraphs as $paragraph): ?><p><?= escapeHtml($paragraph) ?></p><?php endforeach; ?></div>
                    </div>
                    <figure class="about-overview-image reveal">
                        <img src="assets/images/SCValues.webp" alt="A trainer leading a professional learning session with participants at Salaam Center" width="1300" height="800" decoding="async">
                    </figure>
                </div>
            <?php else: ?>
                <p class="courses-eyebrow">Salaam Center</p><h1><?= escapeHtml($pageHeading) ?></h1><p><?= escapeHtml($pageIntro) ?></p>
            <?php endif; ?>
        </header>
        <?php if ($activePage === 'events'): ?>
            <h2 class="info-subheading">Learning in Action.</h2>
            <div class="event-list"><?php foreach ($events as $event) require __DIR__ . '/event-card.php'; ?></div>
            <h2 class="info-subheading event-archive-heading">From the Archive</h2>
            <?php require __DIR__ . '/news-list.php'; ?>
        <?php elseif ($activePage !== 'contact'): ?>
            <div class="info-actions<?= $activePage === 'about' ? ' about-actions' : '' ?>"><a class="hero-button courses-catalog" href="<?= escapeHtml($pageActionUrl) ?>"><?= escapeHtml($pageActionLabel) ?></a><a class="text-link" href="contact.php">Contact Our Team →</a></div>
        <?php endif; ?>

    </div>
    <?php if ($activePage === 'about') require __DIR__ . '/venue-hire.php'; ?>
    <?php if ($activePage === 'contact') require __DIR__ . '/contact-section.php'; ?>
</main>
<?php require __DIR__ . '/footer.php'; ?>
</body>
</html>
