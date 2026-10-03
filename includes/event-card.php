<?php
if (!isset($event) || !function_exists('escapeHtml')) { http_response_code(404); exit; }
$clickableCard = !empty($eventCardClickable);
$cardTag = $clickableCard ? 'a' : 'article';
$mediaTag = $clickableCard ? 'span' : 'a';
$ctaTag = $clickableCard ? 'span' : 'a';
$eventUrl = 'event.php?slug=' . $event['slug'];
?>
<<?= $cardTag ?> class="event-feature"<?= $clickableCard ? ' href="' . escapeHtml($eventUrl) . '" aria-labelledby="event-title-' . escapeHtml($event['slug']) . '"' : '' ?>>
    <<?= $mediaTag ?> class="event-feature-image"<?= !$clickableCard ? ' href="' . escapeHtml($eventUrl) . '" tabindex="-1" aria-hidden="true"' : '' ?>><img src="<?= escapeHtml($event['image']) ?>" alt="" width="<?= (int) ($event['image_width'] ?? 1600) ?>" height="<?= (int) ($event['image_height'] ?? 1124) ?>" loading="lazy" decoding="async"></<?= $mediaTag ?>>
    <div class="event-feature-copy">
        <p class="courses-eyebrow">Training Highlight</p>
        <h3 id="event-title-<?= escapeHtml($event['slug']) ?>"><?= escapeHtml($event['title']) ?></h3>
        <p><?= escapeHtml($event['excerpt']) ?></p>
        <<?= $ctaTag ?> class="text-link"<?= !$clickableCard ? ' href="' . escapeHtml($eventUrl) . '"' : '' ?>>View Event <span aria-hidden="true">→</span><?php if (!$clickableCard): ?><span class="visually-hidden">: <?= escapeHtml($event['title']) ?></span><?php endif; ?></<?= $ctaTag ?>>
    </div>
</<?= $cardTag ?>>
