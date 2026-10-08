<?php
require_once __DIR__ . '/includes/helpers.php';
$site = require __DIR__ . '/data/site.php';
$homeContent = require __DIR__ . '/data/home.php';
$events = require __DIR__ . '/data/events.php';
$activePage = 'events';
$pageHeading = 'News & Events';
$pageTitle = $pageHeading . ' — Salaam Center';
$pageIntro = 'Discover training activities, events and professional learning experiences at Salaam Center.';
$pageDescription = $pageIntro;
require __DIR__ . '/includes/info-page.php';
