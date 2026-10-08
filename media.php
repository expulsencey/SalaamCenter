<?php
ini_set('display_errors', '0');
require __DIR__ . '/includes/cms.php';
header('X-Content-Type-Options: nosniff'); header('Cache-Control: no-store');
$name = $_GET['file'] ?? '';
$path = is_string($name) ? cmsMediaPath($name) : '';
if (!$path || !is_file($path)) { http_response_code(404); exit; }
try {
    $q = cmsDatabase()->prepare("SELECT id FROM cms_articles WHERE featured_image=? AND status='published' AND published_at <= UTC_TIMESTAMP() LIMIT 1");
    $q->execute([$name]); $visible = (bool) $q->fetch();
    if (!$visible) {
        $q = cmsDatabase()->prepare("SELECT id FROM cms_courses WHERE featured_image=? AND status='published' LIMIT 1");
        $q->execute([$name]); $visible = (bool) $q->fetch();
    }
    if (!$visible && isset($_COOKIE['SALAAMADMIN'])) { require __DIR__ . '/includes/cms-auth.php'; $visible = (bool) cmsUser(); }
    if (!$visible) { http_response_code(404); exit; }
} catch (Throwable $e) { http_response_code(503); exit; }
header('Content-Type: image/webp'); header('Content-Length: ' . filesize($path));
readfile($path);
