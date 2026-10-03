<?php if (!isset($homeContent) || !function_exists('escapeHtml')) { http_response_code(404); exit; } ?>
<div class="news-grid">
    <?php foreach ($homeContent['news'] as $item): ?>
        <article class="news-card" id="news-<?= escapeHtml($item['id']) ?>">
            <img src="<?= escapeHtml($item['image']) ?>" alt="<?= escapeHtml($item['alt']) ?>" width="1000" height="600" loading="lazy">
            <div class="news-content"><p class="courses-eyebrow">From Salaam Center</p><h3><?= escapeHtml($item['title']) ?></h3><p><?= escapeHtml($item['excerpt']) ?></p><a class="text-link" href="<?= escapeHtml($item['url']) ?>">Read More<span class="visually-hidden">: <?= escapeHtml($item['title']) ?></span> <span aria-hidden="true">↗</span></a></div>
        </article>
    <?php endforeach; ?>
</div>
