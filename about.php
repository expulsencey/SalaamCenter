<?php
require __DIR__ . '/includes/catalog.php';
$homeContent = require __DIR__ . '/data/home.php';
$venues = require __DIR__ . '/data/venues.php';
$activePage = 'about';
$pageHeading = 'About Salaam Center';
$pageTitle = $pageHeading . ' — Salaam Center';
$pageIntroParagraphs = [
    'Salaam Center is a training, research and consultancy center based in Djibouti, dedicated to developing human capital through professional learning and capacity development.',
    'The Center provides professional training programs and practical workshops designed to strengthen knowledge, skills and capabilities. Its programs support individuals, businesses and organizations across a range of fields, including finance, compliance, management and digital skills.',
    'Through practical and theoretical learning, professional certifications and international examination opportunities, Salaam Center aims to equip learners with relevant skills while helping organizations strengthen their capabilities and performance.',
    'With a focus on quality, innovation and results-oriented learning, Salaam Center contributes to the development of skilled professionals in Djibouti and the wider region.',
];
$pageIntro = $pageIntroParagraphs[0];
$pageDescription = $pageIntro;
$pageActionUrl = 'courses.php';
$pageActionLabel = 'Explore Courses';
require __DIR__ . '/includes/info-page.php';
