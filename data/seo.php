<?php
// Public deployment settings only; never derive these from the request Host header.
return [
    'base_url' => 'https://www.salaamcenter.net',
    'route_style' => 'php', // Switch to clean only when those production routes exist.
    'indexable' => true,
    'social_image' => 'assets/images/about/salaam-center-reception.webp',
    'social_image_alt' => 'Salaam Center reception area',
];
