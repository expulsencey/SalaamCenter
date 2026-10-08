<?php
require_once __DIR__ . '/includes/seo.php';
header('Content-Type: text/plain; charset=UTF-8');
echo "User-agent: *\n";
if (seoBaseUrl() === '' || !seoConfig()['indexable']) {
    echo "Disallow: /\n";
} else {
    echo "Disallow:\nSitemap: " . seoBaseUrl() . "/sitemap.xml\n";
}
