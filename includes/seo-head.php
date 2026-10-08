<?php if (!isset($seo) || !function_exists('escapeHtml')) { http_response_code(404); exit; } ?>
    <meta name="robots" content="<?= escapeHtml($seo['robots']) ?>">
    <?php if ($seo['canonical']): ?>
    <link rel="canonical" href="<?= escapeHtml($seo['canonical']) ?>">
    <meta property="og:type" content="<?= escapeHtml($seo['og_type']) ?>">
    <meta property="og:site_name" content="Salaam Center">
    <meta property="og:title" content="<?= escapeHtml($seo['title']) ?>">
    <meta property="og:description" content="<?= escapeHtml($seo['description']) ?>">
    <meta property="og:url" content="<?= escapeHtml($seo['canonical']) ?>">
    <meta name="twitter:card" content="<?= $seo['image'] ? 'summary_large_image' : 'summary' ?>">
    <meta name="twitter:title" content="<?= escapeHtml($seo['title']) ?>">
    <meta name="twitter:description" content="<?= escapeHtml($seo['description']) ?>">
    <?php if ($seo['image']): ?>
    <meta property="og:image" content="<?= escapeHtml($seo['image']['url']) ?>">
    <meta property="og:image:alt" content="<?= escapeHtml($seo['image']['alt']) ?>">
    <meta property="og:image:width" content="<?= $seo['image']['width'] ?>">
    <meta property="og:image:height" content="<?= $seo['image']['height'] ?>">
    <meta name="twitter:image" content="<?= escapeHtml($seo['image']['url']) ?>">
    <meta name="twitter:image:alt" content="<?= escapeHtml($seo['image']['alt']) ?>">
    <?php endif; ?>
    <script type="application/ld+json"><?= json_encode(['@context' => 'https://schema.org', '@graph' => $seo['graph']], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) ?></script>
    <?php endif; ?>
