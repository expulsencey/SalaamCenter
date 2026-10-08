<?php
require_once __DIR__ . '/helpers.php';

function courseUrl(array $course): string
{
    return 'course.php?slug=' . rawurlencode($course['slug']);
}

function courseDate(string $date): string
{
    return (new DateTimeImmutable($date))->format('j F Y');
}


function courseFacts(array $course): array
{
    return array_filter([
        'Duration' => $course['duration'],
        'Language' => $course['language'],
        'Training Format' => $course['format'],
        'Level' => $course['level'],
        'Next Session' => $course['next_session'] ? courseDate($course['next_session']) : null,
    ], static fn($value) => $value !== null && $value !== '');
}

require_once __DIR__ . '/course-store.php';
try { $courses = publishedCourses(); }
catch (Throwable $e) {
    http_response_code(503); header('Content-Type: text/html; charset=UTF-8');
    header('X-Robots-Tag: noindex'); header('Retry-After: 300');
    exit('<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Courses unavailable | Salaam Center</title></head><body><main><h1>Courses temporarily unavailable</h1><p>Please try again later.</p></main></body></html>');
}
header('Cache-Control: no-store');

usort($courses, static fn($a, $b) => $a['order'] <=> $b['order']);
$courses = array_column($courses, null, 'slug');
$courseCategories = require __DIR__ . '/../data/categories.php';
foreach ($courses as &$courseEntry) {
    $courseEntry['category'] = $courseCategories[$courseEntry['category_slug']]['label'];
}
unset($courseEntry);
$featuredCourses = array_filter($courses, static fn($course) => $course['featured']);
