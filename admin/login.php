<?php
require __DIR__ . '/../includes/cms-auth.php';
require __DIR__ . '/_layout.php';
$error = ''; $email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cmsCheckCsrf();
    try {
        $email = cmsText($_POST['email'] ?? '', 254);
        $password = $_POST['password'] ?? '';
        if (!is_string($password) || strlen($password) > 72 || str_contains($password, "\0")) $password = '';
        if (cmsLogin(strtolower($email), $password)) { header('Location: index.php', true, 303); exit; }
        $error = 'Unable to sign in. Check your details or wait 15 minutes before trying again.';
    } catch (Throwable $e) { $error = 'Unable to sign in. Please try again later or ask the site owner to check setup.'; http_response_code(503); }
}
adminHead('Sign in', false);
?>
<p>Manage courses, training sessions and articles. Sign in with your staff account.</p>
<?php if ($error): ?><p class="notice error" role="alert"><?= escapeHtml($error) ?></p><?php endif; ?>
<form method="post" class="login-form"><input type="hidden" name="csrf_token" value="<?= escapeHtml(cmsToken()) ?>">
<label>Email<input name="email" type="email" autocomplete="username" required maxlength="254" value="<?= escapeHtml($email) ?>"></label>
<label>Password<input name="password" type="password" autocomplete="current-password" required maxlength="72"></label>
<button class="primary">Sign in</button></form>
<?php adminFoot(); ?>
