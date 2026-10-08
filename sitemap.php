<?php
require_once __DIR__ . '/includes/seo.php';
header('Content-Type: application/xml; charset=UTF-8');
if (seoBaseUrl() === '' || !seoConfig()['indexable']) {
    http_response_code(503);
    exit;
}
try { $routes = seoRoutes(); }
catch (Throwable $e) { http_response_code(503); header('Retry-After: 300'); exit; }
header('Cache-Control: no-store');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
foreach ($routes as $route) {
    echo '<url><loc>' . htmlspecialchars(seoUrl($route['page'], $route['slug']), ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc></url>';
}
echo '</urlset>';
