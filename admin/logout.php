<?php
require __DIR__ . '/../includes/cms-auth.php';
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');
cmsRequireUser();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); header('Allow: POST'); exit; }
cmsCheckCsrf();
$_SESSION = [];
$params = session_get_cookie_params();
setcookie(session_name(), '', ['expires' => time()-3600, 'path' => $params['path'], 'secure' => $params['secure'], 'httponly' => true, 'samesite' => 'Strict']);
session_destroy();
header('Location: login.php', true, 303);
