<?php
require_once __DIR__ . '/includes/helpers.php';
$site = require __DIR__ . '/data/site.php';
$homeContent = require __DIR__ . '/data/home.php';
$activePage = 'sc-business';
$pageHeading = 'SC Business';
$pageTitle = $pageHeading . ' — Salaam Center';
$pageIntro = 'Salaam Center provides tailored training and consultancy for organizations, with a focus on performance, change management and business processes.';
$pageDescription = $pageIntro;
$pageActionUrl = 'https://salaamcenter.net/sc-business/';
$pageActionLabel = 'Explore SC Business Services';
require __DIR__ . '/includes/info-page.php';
