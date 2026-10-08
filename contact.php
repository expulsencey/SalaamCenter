<?php
require_once __DIR__ . '/includes/helpers.php';
$site = require __DIR__ . '/data/site.php';
require __DIR__ . '/includes/contact-handler.php';
$homeContent = require __DIR__ . '/data/home.php';
$venues = require __DIR__ . '/data/venues.php';
$space = $_GET['space'] ?? '';
$selectedVenue = is_string($space) ? ($venues[$space] ?? null) : null;
$venueTopic = $selectedVenue ? 'Venue Hire — ' . $selectedVenue['name'] : '';
$activePage = 'contact';
$pageHeading = 'Contact Salaam Center';
$pageTitle = $pageHeading . ' — Salaam Center';
$pageIntro = 'Speak with our team about your training goals, business needs or partnership enquiries.';
$pageDescription = $pageIntro;
require __DIR__ . '/includes/info-page.php';
