<?php
function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function courseUrl(array $course): string
{
    return 'course.php?slug=' . rawurlencode($course['slug']);
}

function courseDate(string $date): string
{
    return (new DateTimeImmutable($date))->format('j F Y');
}

require_once __DIR__ . '/currency.php';

function courseFacts(array $course): array
{
    return array_filter([
        'Duration' => $course['duration'],
        'Language' => $course['language'],
        'Training Format' => $course['format'],
        'Level' => $course['level'],
        'Next Session' => $course['next_session'] ? courseDate($course['next_session']) : null,
        'Price' => $course['price_source'] !== null ? coursePrice($course) : null,
    ], static fn($value) => $value !== null && $value !== '');
}

$courses = require __DIR__ . '/../data/courses.php';

usort($courses, static fn($a, $b) => $a['order'] <=> $b['order']);
$courses = array_column($courses, null, 'slug');
$courseCategories = require __DIR__ . '/../data/categories.php';
foreach ($courses as &$courseEntry) {
    $courseEntry['category'] = $courseCategories[$courseEntry['category_slug']]['label'];
}
unset($courseEntry);
$featuredCourses = array_filter($courses, static fn($course) => $course['featured']);
