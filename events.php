<?php
require __DIR__ . '/includes/catalog.php';
$homeContent = require __DIR__ . '/data/home.php';
$events = require __DIR__ . '/data/events.php';
$activePage = 'events';
$pageHeading = 'News & Events';
$pageTitle = $pageHeading . ' — Salaam Center';
$pageIntro = 'Discover training activities, events and professional learning experiences at Salaam Center.';
$pageDescription = $pageIntro;
require __DIR__ . '/includes/info-page.php';
