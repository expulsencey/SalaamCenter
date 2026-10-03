<?php
if (!isset($course) || !function_exists('escapeHtml')) { http_response_code(404); exit; }
$headingTag = ($cardHeading ?? 'h3') === 'h2' ? 'h2' : 'h3';
?>
<article class="course-card" aria-labelledby="course-title-<?= escapeHtml($course['slug']) ?>">
    <div class="course-media">
        <a class="course-image" href="<?= escapeHtml(courseUrl($course)) ?>" tabindex="-1" aria-hidden="true">
            <img src="<?= escapeHtml($course['image']) ?>" alt="<?= escapeHtml($course['image_alt']) ?>" loading="lazy" width="1000" height="560">
        </a>
        <?php if ($course['price_usd'] !== null): ?><p class="course-price"><span class="visually-hidden">Price: </span><?= escapeHtml(coursePrice($course['price_usd'])) ?></p><?php endif; ?>
    </div>
    <div class="course-content">
        <<?= $headingTag ?> class="course-card-title" id="course-title-<?= escapeHtml($course['slug']) ?>" lang="<?= escapeHtml($course['title_language']) ?>"><a href="<?= escapeHtml(courseUrl($course)) ?>"><?= escapeHtml($course['name']) ?></a></<?= $headingTag ?>>
    </div>
</article>
