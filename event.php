<?php
require_once __DIR__ . '/includes/helpers.php';
$site = require __DIR__ . '/data/site.php';
$events = require __DIR__ . '/data/events.php';
$slug = $_GET['slug'] ?? '';
$event = is_string($slug) ? ($events[$slug] ?? null) : null;
if (!$event) http_response_code(404);
$activePage = 'events';
$pageTitle = ($event ? $event['title'] : 'Event not found') . ' — Salaam Center';
$pageDescription = $event ? $event['introduction'] : 'Browse training activities at Salaam Center.';
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/header.php';
?>
<main id="main-content" class="info-page event-page">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="index.php">Home</a><span aria-hidden="true">/</span><a href="events.php">Events</a><span aria-hidden="true">/</span><span aria-current="page"><?= escapeHtml($event ? $event['title'] : 'Event not found') ?></span></nav>
        <?php if (!$event): ?>
            <header class="catalog-heading"><h1>Event not found</h1><p>This activity is not available.</p></header>
        <?php else: ?>
            <header class="catalog-heading"><p class="courses-eyebrow">Training Highlight</p><h1><?= escapeHtml($event['title']) ?></h1><p><?= escapeHtml($event['introduction']) ?></p></header>
            <?php if (!empty($event['video'])): ?>
            <div class="event-video reveal">
                <video id="training-video" data-event-video autoplay muted loop playsinline preload="metadata"<?php if (!empty($event['video_poster'])): ?> poster="<?= escapeHtml($event['video_poster']) ?>"<?php endif; ?> width="<?= (int) $event['video_width'] ?>" height="<?= (int) $event['video_height'] ?>" aria-label="<?= escapeHtml($event['title'] . ' footage') ?>">
                    <source src="<?= escapeHtml($event['video']) ?>?v=<?= filemtime(__DIR__ . '/' . $event['video']) ?>" type="video/mp4">
                </video>
            </div>
            <?php elseif (!empty($event['image'])): ?>
            <figure class="event-photo"><img src="<?= escapeHtml($event['image']) ?>" alt="<?= escapeHtml($event['image_alt'] ?? $event['title']) ?>"<?php if (isset($event['image_width'], $event['image_height'])): ?> width="<?= (int) $event['image_width'] ?>" height="<?= (int) $event['image_height'] ?>"<?php endif; ?>></figure>
            <?php endif; ?>
            <p class="event-context"><?= escapeHtml($event['description'] ?? $event['excerpt']) ?></p>
            <section class="event-section" aria-labelledby="gallery-title">
                <header class="section-heading">
                    <?php if (!empty($event['gallery_eyebrow'])): ?><p class="courses-eyebrow"><?= escapeHtml($event['gallery_eyebrow']) ?></p><?php endif; ?>
                    <h2 id="gallery-title"><?= escapeHtml($event['gallery_title'] ?? 'Learning Together') ?></h2>
                    <?php if (!empty($event['gallery_intro'])): ?><p><?= escapeHtml($event['gallery_intro']) ?></p><?php endif; ?>
                </header>
                <div class="event-gallery<?= ($event['gallery_layout'] ?? '') === 'grid' ? ' event-gallery--grid' : '' ?>"><?php foreach ($event['gallery'] as $photo): ?><figure class="event-photo reveal"><img src="<?= escapeHtml($photo['image']) ?>" alt="<?= escapeHtml($photo['alt']) ?>" width="<?= (int) ($photo['width'] ?? 1600) ?>" height="<?= (int) $photo['height'] ?>" loading="lazy" decoding="async"></figure><?php endforeach; ?></div>
            </section>
            <?php if (!empty($event['completion'])): ?>
            <section class="event-section" aria-labelledby="completion-title">
                <header class="section-heading"><h2 id="completion-title">Celebrating Completion</h2><p><?= escapeHtml($event['completion']) ?></p></header>
                <figure class="event-photo event-certificate reveal"><img src="<?= escapeHtml($event['certificate_image']) ?>" alt="A participant receiving a certificate at the end of the training" width="1600" height="1124" loading="lazy" decoding="async"></figure>
                <figure class="event-photo event-group reveal"><img src="<?= escapeHtml($event['group_image']) ?>" alt="Final group photograph of the Administrative Management training participants" width="1600" height="1124" loading="lazy" decoding="async"></figure>
            </section>
            <?php endif; ?>
        <?php endif; ?>
        <a class="text-link" href="events.php">Back to Events <span aria-hidden="true">→</span></a>
    </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
