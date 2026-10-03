<?php
require __DIR__ . '/includes/catalog.php';
$homeContent = require __DIR__ . '/data/home.php';
$venues = require __DIR__ . '/data/venues.php';
$space = $_GET['space'] ?? '';
$selectedVenue = is_string($space) ? ($venues[$space] ?? null) : null;
$venueTopic = $selectedVenue ? 'Venue Hire — ' . $selectedVenue['name'] : '';
// Submission remains unavailable until validated PHP mail processing is implemented.
if ($_SERVER['REQUEST_METHOD'] === 'POST') { http_response_code(405); header('Allow: GET, HEAD'); }
$activePage = 'contact';
$pageHeading = 'Contact Salaam Center';
$pageTitle = $pageHeading . ' — Salaam Center';
$pageIntro = 'Speak with our team about your training goals, business needs or partnership enquiries.';
$pageDescription = $pageIntro;
require __DIR__ . '/includes/info-page.php';
