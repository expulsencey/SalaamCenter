<?php
// Shared metadata, canonical routes and discovery documents. No private configuration.
function seoConfig(): array
{
    static $config;
    if ($config === null) $config = require __DIR__ . '/../data/seo.php';
    return $config;
}

function seoBaseUrl(): string
{
    $url = rtrim(seoConfig()['base_url'], '/');
    $parts = parse_url($url);
    $host = strtolower($parts['host'] ?? '');
    if (($parts['scheme'] ?? '') !== 'https' || !str_contains($host, '.')
        || filter_var($host, FILTER_VALIDATE_IP) || !filter_var($url, FILTER_VALIDATE_URL)
        || preg_match('/(?:localhost|trycloudflare|workers\.dev|pages\.dev|\.local$|\.test$|\.invalid$)/i', $host)
        || isset($parts['user']) || isset($parts['pass'])
        || isset($parts['port']) || isset($parts['query']) || isset($parts['fragment'])
        || !in_array(seoConfig()['route_style'], ['php', 'clean'], true)) return '';
    return $url;
}

function seoPath(string $page, string $slug = ''): string
{
    if ($page === 'index') return '/';
    if (seoConfig()['route_style'] === 'clean') {
        $section = ['course' => 'courses', 'category' => 'categories', 'event' => 'events', 'article' => 'blog'][$page] ?? $page;
        return '/' . $section . '/' . ($slug !== '' ? rawurlencode($slug) . '/' : '');
    }
    return '/' . $page . '.php' . ($slug !== '' ? '?slug=' . rawurlencode($slug) : '');
}

function seoUrl(string $page, string $slug = ''): string
{
    return seoBaseUrl() !== '' ? seoBaseUrl() . seoPath($page, $slug) : '';
}

function seoRoutes(): array
{
    $routes = [];
    foreach (['index', 'about', 'courses', 'categories', 'events', 'partners', 'contact', 'sc-business'] as $page) {
        $routes[] = ['page' => $page, 'slug' => ''];
    }
    require_once __DIR__ . '/course-store.php';
    foreach (publishedCourses() as $record) $routes[] = ['page'=>'course','slug'=>$record['slug']];
    foreach (['category' => 'categories', 'event' => 'events'] as $page => $dataset) {
        foreach (require __DIR__ . '/../data/' . $dataset . '.php' as $key => $record) {
            $routes[] = ['page' => $page, 'slug' => $record['slug'] ?? $key];
        }
    }
    require_once __DIR__ . '/cms.php';
    try {
        $articles = cmsPublished();
        $routes[] = ['page' => 'blog', 'slug' => ''];
        foreach ($articles as $article) $routes[] = ['page' => 'article', 'slug' => $article['slug']];
    } catch (Throwable $e) { /* Existing PHP content remains discoverable when CMS setup is unavailable. */ }
    return $routes;
}

function seoImage(string $path, string $alt): array
{
    if (preg_match('/^media\.php\?file=([a-f0-9]{48}\.webp)$/D', $path, $match)) {
        require_once __DIR__ . '/cms.php';
        $file = cmsMediaPath($match[1]); $size = is_file($file) ? getimagesize($file) : false;
        return $size && seoBaseUrl() !== '' ? ['url'=>seoBaseUrl() . '/' . $path,'alt'=>$alt,'width'=>$size[0],'height'=>$size[1]] : [];
    }
    $root = realpath(__DIR__ . '/../assets/images');
    $file = realpath(__DIR__ . '/../' . rawurldecode($path));
    if (!$root || !$file || !str_starts_with($file, $root . DIRECTORY_SEPARATOR) || !is_file($file)) return [];
    $size = getimagesize($file);
    if (!$size || seoBaseUrl() === '') return [];
    return ['url' => seoBaseUrl() . '/' . implode('/', array_map('rawurlencode', explode('/', rawurldecode($path)))),
        'alt' => $alt, 'width' => $size[0], 'height' => $size[1]];
}

function seoMetadata(string $page, string $title, string $description, array $site, ?array $record = null): array
{
    $config = seoConfig();
    $valid = http_response_code() < 400 && seoBaseUrl() !== '';
    $slug = $record['slug'] ?? '';
    if (in_array($page, ['course', 'category', 'event', 'article'], true) && !$record) $valid = false;
    $title = str_replace(' — Salaam Center', ' | Salaam Center', $title);
    if ($page === 'index') $title = 'Professional Training, Research & Consultancy | Salaam Center';
    if ($description === '') {
        $description = match ($page) {
            'courses' => 'Explore the Salaam Center course catalogue, with professional training in finance, management, languages, information technology and safety.',
            'categories' => 'Explore Salaam Center course categories and find professional training by subject, from banking and finance to technology and languages.',
            'course' => 'Explore ' . ($record['name'] ?? 'courses') . ' at Salaam Center. ' . (!empty($record['description']) ? $record['description'] : 'View available course information and contact the team about this training.'),
            'category' => 'Browse ' . ($record['label'] ?? 'training') . ' courses at Salaam Center and open each course for available training details.',
            default => '',
        };
    }
    $description = trim(preg_replace('/\s+/u', ' ', strip_tags($description)));
    $url = $valid ? seoUrl($page, $slug) : '';
    $image = $valid ? seoImage($record['image'] ?? $config['social_image'], $record['image_alt'] ?? $config['social_image_alt']) : [];
    if ($valid && !$image) $image = seoImage($config['social_image'], $config['social_image_alt']);
    if ($valid && $page === 'article' && !empty($record['featured_image'])) {
        require_once __DIR__ . '/cms.php';
        $path = cmsMediaPath($record['featured_image']);
        $size = $path && is_file($path) ? getimagesize($path) : false;
        if ($size) $image = ['url' => seoBaseUrl() . '/media.php?file=' . rawurlencode($record['featured_image']), 'alt' => $record['image_alt'], 'width' => $size[0], 'height' => $size[1]];
    }
    $graph = [];
    if ($valid) {
        $orgId = seoUrl('index') . '#organization';
        $webId = seoUrl('index') . '#website';
        $graph[] = ['@type' => 'Organization', '@id' => $orgId, 'name' => 'Salaam Center', 'url' => seoUrl('index'),
            'email' => $site['email'], 'telephone' => substr($site['phone_uri'], 4),
            'sameAs' => [$site['facebook_url'], $site['linkedin_url']]];
        $graph[] = ['@type' => 'WebSite', '@id' => $webId, 'name' => 'Salaam Center', 'url' => seoUrl('index'), 'publisher' => ['@id' => $orgId], 'inLanguage' => 'en'];
        $webPage = ['@type' => match ($page) { 'about' => 'AboutPage', 'contact' => 'ContactPage', default => 'WebPage' },
            '@id' => $url . '#webpage', 'url' => $url, 'name' => $title, 'description' => $description, 'isPartOf' => ['@id' => $webId], 'inLanguage' => 'en'];
        if ($page !== 'index') {
            $crumbs = [['name' => 'Home', 'item' => seoUrl('index')]];
            $parents = ['course' => ['Courses', 'courses'], 'category' => ['Categories', 'categories'], 'event' => ['Events', 'events'], 'article' => ['News','blog']];
            if (isset($parents[$page])) $crumbs[] = ['name' => $parents[$page][0], 'item' => seoUrl($parents[$page][1])];
            if ($page === 'course') $crumbs[] = ['name' => $record['category'], 'item' => seoUrl('category', $record['category_slug'])];
            if ($page === 'categories') $crumbs[] = ['name' => 'All Courses', 'item' => seoUrl('courses')];
            $crumbs[] = ['name' => $record['name'] ?? $record['label'] ?? $record['title'] ?? str_replace(' | Salaam Center', '', $title), 'item' => $url];
            foreach ($crumbs as $i => &$crumb) $crumb = ['@type' => 'ListItem', 'position' => $i + 1] + $crumb;
            unset($crumb);
            $graph[] = ['@type' => 'BreadcrumbList', '@id' => $url . '#breadcrumb', 'itemListElement' => $crumbs];
            $webPage['breadcrumb'] = ['@id' => $url . '#breadcrumb'];
        }
        if ($page === 'course') {
            $course = ['@type' => 'Course', '@id' => $url . '#course', 'url' => $url, 'name' => $record['name'],
                'description' => $description, 'provider' => ['@id' => $orgId]];
            if ($image) $course['image'] = $image['url'];
            $graph[] = $course;
            $webPage['mainEntity'] = ['@id' => $url . '#course'];
        }
        if ($page === 'article') {
            $posting = ['@type'=>'BlogPosting', '@id'=>$url . '#article', 'headline'=>$record['title'], 'description'=>$record['excerpt'],
                'datePublished'=>str_replace(' ', 'T', $record['published_at']) . 'Z', 'dateModified'=>str_replace(' ', 'T', $record['updated_at']) . 'Z',
                'publisher'=>['@id'=>$orgId], 'mainEntityOfPage'=>['@id'=>$url . '#webpage']];
            if ($image) $posting['image'] = $image['url'];
            $graph[] = $posting; $webPage['mainEntity'] = ['@id'=>$url . '#article'];
        }
        // Event dates are not supplied; WebPage describes the factual training recap instead.
        $graph[] = $webPage;
    }
    return ['title' => $title, 'description' => $description, 'canonical' => $url,
        'robots' => $valid && $config['indexable'] ? 'index, follow' : 'noindex, follow',
        'image' => $image, 'graph' => $graph, 'og_type' => $page === 'article' ? 'article' : 'website'];
}
