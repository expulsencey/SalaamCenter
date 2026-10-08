<?php
require_once __DIR__ . '/cms.php';
ini_set('display_errors', '0');
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store, private');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('SALAAMADMIN');
    if (!session_start(['use_strict_mode' => true, 'use_only_cookies' => true, 'cookie_httponly' => true,
        'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'cookie_samesite' => 'Strict'])) {
        http_response_code(503); exit('Administration is temporarily unavailable. Please try again later.');
    }
}

function cmsToken(): string
{
    return $_SESSION['cms_csrf'] ??= bin2hex(random_bytes(32));
}

function cmsCheckCsrf(): void
{
    $token = $_POST['csrf_token'] ?? null;
    if (!is_string($token) || !hash_equals(cmsToken(), $token)) {
        http_response_code(403);
        exit('Your form has expired. Reload the page and try again.');
    }
}

function cmsUser(): ?array
{
    if (empty($_SESSION['cms_user']) || time() - ($_SESSION['cms_seen'] ?? 0) > 1800 || time() - ($_SESSION['cms_started'] ?? 0) > 28800) {
        unset($_SESSION['cms_user']); return null;
    }
    $query = cmsDatabase()->prepare('SELECT id,email,display_name,password_hash FROM cms_users WHERE id=?');
    $query->execute([$_SESSION['cms_user']]);
    $user = $query->fetch();
    if (!$user || !hash_equals($_SESSION['cms_auth'] ?? '', hash('sha256', $user['password_hash']))) { unset($_SESSION['cms_user']); return null; }
    $_SESSION['cms_seen'] = time();
    unset($user['password_hash']);
    return $user;
}

function cmsRequireUser(): array
{
    try { $user = cmsUser(); }
    catch (Throwable $e) { http_response_code(503); exit('Administration is temporarily unavailable. Please try again later.'); }
    if (!$user) { header('Location: login.php', true, 303); exit; }
    return $user;
}

function cmsLogin(string $email, string $password): bool
{
    $pdo = cmsDatabase();
    // Atomic counters shared across sessions. Both account and IP buckets must permit an attempt.
    $keys = [hash('sha256', 'email:' . strtolower($email)), hash('sha256', 'ip:' . ($_SERVER['REMOTE_ADDR'] ?? 'cli'))];
    $limited = false;
    foreach ($keys as $key) {
        $q = $pdo->prepare("INSERT INTO cms_login_limits (bucket,attempts,window_start) VALUES (?,1,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE attempts=IF(window_start < UTC_TIMESTAMP()-INTERVAL 15 MINUTE,1,attempts+1), window_start=IF(window_start < UTC_TIMESTAMP()-INTERVAL 15 MINUTE,UTC_TIMESTAMP(),window_start)");
        $q->execute([$key]);
        $q = $pdo->prepare('SELECT attempts FROM cms_login_limits WHERE bucket=?'); $q->execute([$key]);
        if ((int) $q->fetchColumn() > 8) $limited = true;
    }
    $pdo->exec('DELETE FROM cms_login_limits WHERE window_start < UTC_TIMESTAMP()-INTERVAL 1 DAY');
    if ($limited) return false;
    $q = $pdo->prepare('SELECT * FROM cms_users WHERE email=?'); $q->execute([$email]); $user = $q->fetch();
    // A fixed non-account hash keeps password verification work for unknown emails too.
    $dummy = '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
    $verified = password_verify($password, $user['password_hash'] ?? $dummy);
    if (!$user || !$verified) return false;
    session_regenerate_id(true);
    $_SESSION = ['cms_user' => $user['id'], 'cms_auth' => hash('sha256', $user['password_hash']),
        'cms_started' => time(), 'cms_seen' => time(), 'cms_csrf' => bin2hex(random_bytes(32))];
    $q = $pdo->prepare('DELETE FROM cms_login_limits WHERE bucket=?'); $q->execute([$keys[0]]);
    return true;
}
