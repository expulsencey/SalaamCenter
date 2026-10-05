<?php
// Project-approved fixed parity, not a live exchange rate.
const COURSE_DJF_PER_USD = 177.721;

function convertCoursePrice(int|float $amount, string $source, string $target): float
{
    if (!in_array($source, ['USD', 'DJF'], true) || !in_array($target, ['USD', 'DJF'], true)) {
        throw new InvalidArgumentException('Unsupported course currency.');
    }
    if ($source === $target) return (float) $amount;
    return $target === 'USD' ? $amount / COURSE_DJF_PER_USD : $amount * COURSE_DJF_PER_USD;
}

function coursePrice(array $course, string $currency = 'USD'): string
{
    $amount = convertCoursePrice($course['price_source'], $course['price_source_currency'], $currency);
    return $currency === 'USD' ? '$' . number_format($amount, 2, '.', ',') : number_format($amount, 0, '.', ',') . ' Fdj';
}

function coursePriceMarkup(array $course): string
{
    if ($course['price_source'] === null) return '';
    $usd = number_format(convertCoursePrice($course['price_source'], $course['price_source_currency'], 'USD'), 2, '.', '');
    $djf = number_format(convertCoursePrice($course['price_source'], $course['price_source_currency'], 'DJF'), 0, '.', '');
    return '<span data-course-price data-usd="' . $usd . '" data-djf="' . $djf . '">' . htmlspecialchars(coursePrice($course), ENT_QUOTES, 'UTF-8') . '</span>';
}
