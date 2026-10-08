<?php
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) { http_response_code(404); exit; }
function adminHead(string $title, bool $loggedIn = true): void
{
    header('Content-Type: text/html; charset=UTF-8');
    header('X-Robots-Tag: noindex, nofollow');
    header('Cache-Control: no-store, private');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");
    $assetVersion = max(filemtime(__DIR__ . '/../assets/css/admin.css'), filemtime(__DIR__ . '/../assets/js/admin.js'));
    ?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex, nofollow"><title><?= escapeHtml($title) ?> | Salaam Center Administration</title><link rel="stylesheet" href="../assets/fonts/poppins.css"><link rel="stylesheet" href="../assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>"><link rel="stylesheet" href="../assets/css/admin.css?v=<?= $assetVersion ?>"><script src="../assets/js/admin.js?v=<?= $assetVersion ?>" defer></script></head><body class="<?= $loggedIn?'admin-shell':'admin-login' ?>"><a class="admin-skip" href="#admin-content">Skip to content</a><header class="site-header admin-header"><div class="container header-content"><a class="site-logo admin-brand" href="index.php" aria-label="Salaam Center administration"><img src="../assets/images/logo/salaam-center-logo.webp" width="215" height="72" alt="Salaam Center"></a>
    <?php if($loggedIn): ?><details class="admin-navigation" open><summary aria-label="Administration menu">Menu</summary><nav aria-label="Administration"><ul class="navigation-list"><?php
    $current=basename($_SERVER['SCRIPT_NAME']);
    foreach(['index.php'=>'Dashboard','courses.php'=>'Courses','sessions.php'=>'Training Sessions','articles.php'=>'Articles','media.php'=>'Media','references.php'=>'Events & Partners'] as $href=>$label){
        $active=$current===$href || ($href==='courses.php'&&str_starts_with($current,'course-')) || ($href==='sessions.php'&&$current==='session-edit.php') || ($href==='articles.php'&&in_array($current,['article-edit.php','article-delete.php','preview.php'],true));
        echo '<li><a class="navigation-link" href="'.$href.'"'.($active?' aria-current="page"':'').'>'.$label.'</a></li>';
    }
    ?></ul><div class="admin-secondary"><a class="view-site" href="../index.php">View website <span aria-hidden="true">↗</span></a><form action="logout.php" method="post"><input type="hidden" name="csrf_token" value="<?= escapeHtml(cmsToken()) ?>"><button>Log out</button></form></div></nav></details><?php else: ?><a class="login-back" href="../index.php">View website</a><?php endif; ?></div></header><main id="admin-content" class="container admin-main"><p class="admin-eyebrow">Salaam Center / Administration</p><h1><?= escapeHtml($title) ?></h1><?php
    $section = str_starts_with(basename($_SERVER['SCRIPT_NAME']),'course-')?'courses.php':(in_array(basename($_SERVER['SCRIPT_NAME']),['article-edit.php','article-delete.php','preview.php'],true)?'articles.php':(basename($_SERVER['SCRIPT_NAME'])==='session-edit.php'?'sessions.php':null));
    if($section) echo '<p class="back-link"><a href="'.$section.'">Back to '.($section==='courses.php'?'Courses':($section==='articles.php'?'Articles':'Training Sessions')).'</a></p>'; 
}
function adminFoot(): void { echo '</main></body></html>'; }
