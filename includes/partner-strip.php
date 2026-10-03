<?php if (!isset($partners) || !function_exists('escapeHtml')) { http_response_code(404); exit; } ?>
<div class="partner-strip" tabindex="0" role="region" aria-label="Educational partner logos; focus to pause movement">
    <div class="partner-track">
        <?php for ($copy = 0; $copy < 2; $copy++): ?>
            <ul class="partner-strip-group"<?= $copy ? ' aria-hidden="true"' : '' ?>>
                <?php foreach ($partners as $partner): ?>
                    <li><img class="partner-logo" src="<?= escapeHtml($partner['logo']) ?>" alt="<?= $copy ? '' : escapeHtml($partner['alt']) ?>" width="200" height="90" decoding="async"></li>
                <?php endforeach; ?>
            </ul>
        <?php endfor; ?>
    </div>
</div>
