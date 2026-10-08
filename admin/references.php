<?php
require __DIR__.'/../includes/cms-auth.php';require __DIR__.'/_layout.php';cmsRequireUser();
$events=require __DIR__.'/../data/events.php';$partners=require __DIR__.'/../data/partners.php';adminHead('Events & Partners'); ?>
<p>These records come directly from the existing website datasets. Editing is reserved for a future CMS phase.</p>
<section><h2><?= count($events) ?> events</h2><ul class="article-list"><?php foreach($events as $event): ?><li><a href="../event.php?slug=<?= escapeHtml($event['slug']) ?>"><?= escapeHtml($event['title']) ?></a></li><?php endforeach; ?></ul></section>
<section><h2><?= count($partners) ?> partners</h2><p><a href="../partners.php">View public partners page</a></p><ul><?php foreach($partners as $partner): ?><li><?= escapeHtml($partner['name']) ?></li><?php endforeach; ?></ul></section><?php adminFoot(); ?>
