<?php
// Build tool only: never served as a public application endpoint.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
set_error_handler(static function ($level, $message) { throw new RuntimeException($message); });

const SOURCE_URL = 'http://localhost/SalaamCenter/';
const BUILD_MARKER = 'Salaam Center generated static export';
$root = realpath(__DIR__ . '/..');
$output = $root . DIRECTORY_SEPARATOR . 'cloudflare-dist';
$assets = [];
$routes = [];

function fail(string $message): never { throw new RuntimeException($message); }
function put(string $path, string $content): void {
    if (!is_dir(dirname($path))) mkdir(dirname($path), 0777, true);
    file_put_contents($path, $content);
}
function fetchPage(string $route): string {
    $context = stream_context_create(['http' => ['timeout' => 20, 'follow_location' => 0,
        'ignore_errors' => true, 'header' => "Connection: close\r\n"]]);
    $html = file_get_contents(SOURCE_URL . $route, false, $context);
    if (!preg_match('~^HTTP/\S+ 200\b~', $http_response_header[0] ?? '')) fail("WAMP did not return 200 for $route. Start WAMP and verify localhost.");
    if (!str_contains($html, '<html') || !preg_match('//u', $html) || preg_match('~(?:Warning|Notice|Fatal error|Parse error):~', $html)) fail("Invalid rendered HTML: $route");
    return $html;
}
function document(string $html): DOMDocument {
    $doc = new DOMDocument('1.0', 'UTF-8');
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NONET);
    libxml_clear_errors();
    foreach (iterator_to_array($doc->childNodes) as $node) if ($node->nodeType === XML_PI_NODE) $doc->removeChild($node);
    $doc->encoding = 'UTF-8';
    return $doc;
}
function localPath(string $relative): string {
    global $root;
    if (str_contains($relative, "\0") || str_contains($relative, '\\') || preg_match('~(^|/)\.\.?(/|$)~', $relative)) fail('Unsafe path: ' . $relative);
    $path = realpath($root . '/' . $relative);
    $prefix = $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR;
    if (!$path || !str_starts_with($path, $prefix) || !is_file($path)) fail('Missing or unsafe asset: ' . $relative);
    if (!preg_match('~\.(css|js|png|jpe?g|webp|gif|svg|ico|mp4|webm|woff2?|ttf|otf)$~i', $path)) fail('Non-public asset type: ' . $relative);
    if (filesize($path) > 25 * 1024 * 1024) fail('Asset exceeds 25 MiB: ' . $relative);
    return $path;
}
function rewriteUrl(string $url): string {
    global $routes, $assets;
    if ($url === '' || $url[0] === '#' || preg_match('~^(mailto:|tel:|data:)~i', $url)) return $url;
    $parts = parse_url($url);
    if ($parts === false) fail('Invalid URL');
    if (isset($parts['host'])) {
        if (!in_array(strtolower($parts['host']), ['localhost', '127.0.0.1'], true)) return $url;
        $url = ($parts['path'] ?? '/') . (isset($parts['query']) ? '?' . $parts['query'] : '') . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
    } elseif (isset($parts['scheme'])) fail('Unsupported URL scheme: ' . $parts['scheme']);
    $parts = parse_url($url);
    $path = rawurldecode($parts['path'] ?? '');
    $path = preg_replace('~^/SalaamCenter/~', '', $path);
    $path = preg_replace('~^\./~', '', $path);
    $path = ltrim($path, '/');
    $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';
    if (str_starts_with($path, 'assets/')) {
        localPath($path); $assets[$path] = true;
        return '/' . implode('/', array_map('rawurlencode', explode('/', $path))) . (isset($parts['query']) ? '?' . $parts['query'] : '') . $fragment;
    }
    if ($path === '') $path = 'index.php';
    parse_str($parts['query'] ?? '', $query);
    $key = $path;
    if (in_array($path, ['course.php', 'category.php', 'event.php'], true)) {
        if (!is_string($query['slug'] ?? null)) fail('Missing route slug');
        $key .= '?slug=' . rawurlencode($query['slug']);
        unset($query['slug']);
    }
    if (!isset($routes[$key])) fail('Unmapped internal URL: ' . $url);
    if ($query && ($path !== 'contact.php' || array_keys($query) !== ['space'] || !is_string($query['space']))) fail('Unsupported internal query: ' . $url);
    // Static Contact uses the same verified contact fallback for every venue.
    return $routes[$key] . ($query ? '?' . http_build_query($query) : '') . $fragment;
}
function safeReset(): void {
    global $root, $output;
    if ($output !== $root . DIRECTORY_SEPARATOR . 'cloudflare-dist' || is_link($output)) fail('Unsafe output directory');
    if (file_exists($output)) {
        if (realpath($output) !== $output || !is_dir($output)) fail('Output is redirected or not a directory');
        $entries = array_diff(scandir($output), ['.', '..']);
        if ($entries && (!is_file($output . '/.generated') || file_get_contents($output . '/.generated') !== BUILD_MARKER)) fail('Refusing to delete output without exporter ownership marker');
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($output, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        $paths = [];
        foreach ($iterator as $entry) {
            $path = $entry->getPathname();
            if ($entry->isLink() || !str_starts_with(realpath($path) ?: '', $output . DIRECTORY_SEPARATOR)) fail('Output contains a redirected path');
            $paths[] = [$path, $entry->isDir()];
        }
        // Validate the whole tree before removing any generated file.
        foreach ($paths as [$path, $directory]) $directory ? rmdir($path) : unlink($path);
    } else mkdir($output);
    put($output . '/.generated', BUILD_MARKER);
}

try {
    if (!class_exists('DOMDocument')) fail('PHP DOM extension is required.');
    foreach (['index' => '/', 'about' => '/about/', 'courses' => '/courses/', 'categories' => '/categories/', 'events' => '/events/', 'partners' => '/partners/', 'contact' => '/contact/', 'sc-business' => '/sc-business/'] as $page => $destination) $routes[$page . '.php'] = $destination;
    $counts = [];
    foreach (['courses' => 'course', 'categories' => 'category', 'events' => 'event'] as $dataset => $page) {
        $records = require $root . '/data/' . $dataset . '.php';
        $counts[$dataset] = count($records);
        foreach ($records as $key => $record) {
            $slug = $record['slug'] ?? $key;
            if (!is_string($slug) || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug)) fail('Unsafe dataset slug');
            $source = $page . '.php?slug=' . rawurlencode($slug);
            if (isset($routes[$source])) fail('Duplicate slug');
            $routes[$source] = '/' . $dataset . '/' . $slug . '/';
        }
    }
    $site = require $root . '/data/site.php';
    // Fetch first: an unavailable WAMP server cannot erase the previous export.
    $pages = [];
    foreach ($routes as $source => $destination) $pages[$destination] = fetchPage($source);
    safeReset();
    foreach ($pages as $destination => $html) {
        $doc = document($html); $xpath = new DOMXPath($doc);
        foreach (iterator_to_array($doc->getElementsByTagName('form')) as $form) {
            if (!str_contains($form->getAttribute('class'), 'contact-form')) fail('Unknown form requires a static export policy');
            $fallback = $doc->createElement('div'); $fallback->setAttribute('class', 'contact-form');
            $fallback->appendChild($doc->createElement('p', 'Online form submission is unavailable on this website. Please contact our team by email or telephone.'));
            foreach (['mailto:' . $site['email'] => 'Email Our Team', $site['phone_uri'] => 'Call ' . $site['phone']] as $href => $label) {
                $p = $doc->createElement('p'); $a = $doc->createElement('a', $label); $a->setAttribute('href', $href); $a->setAttribute('class', 'text-link'); $p->appendChild($a); $fallback->appendChild($p);
            }
            $form->parentNode->replaceChild($fallback, $form);
        }
        foreach ($xpath->query('//*[@href or @src or @poster]') as $node) foreach (['href','src','poster'] as $attribute) if ($node->hasAttribute($attribute)) $node->setAttribute($attribute, rewriteUrl($node->getAttribute($attribute)));
        if ($xpath->query('//*[@srcset or @style]')->length) fail('Review srcset/inline style URLs before exporting this markup');
        $rendered = $doc->saveHTML();
        if (preg_match('~localhost|127\.0\.0\.1|\b[A-Z]:[\\\\/]|<\?php|csrf_token|(?:href|src|action)="[^" ]*\.php~i', $rendered)) fail('Private/source reference in generated HTML');
        put($output . $destination . 'index.html', $rendered);
    }
    // Copy referenced assets only; follow local CSS dependencies when present.
    $copied = [];
    while ($pending = array_diff_key($assets, $copied)) foreach ($pending as $asset => $_) {
        $source = localPath($asset); $target = $output . '/' . $asset;
        if (!is_dir(dirname($target))) mkdir(dirname($target), 0777, true);
        if (pathinfo($source, PATHINFO_EXTENSION) === 'css') {
            $css = file_get_contents($source);
            $css = preg_replace_callback('~url\(\s*[\'\"]?([^\'\")]+)[\'\"]?\s*\)~i', function ($match) use ($asset) {
                $url = trim($match[1]);
                if (preg_match('~^(https?:|//|data:|#)~', $url)) return $match[0];
                $resolved = realpath(dirname(localPath($asset)) . '/' . rawurldecode($url));
                global $root;
                if (!$resolved) fail('Missing CSS dependency');
                return 'url("' . rewriteUrl(str_replace(DIRECTORY_SEPARATOR, '/', substr($resolved, strlen($root) + 1))) . '")';
            }, $css);
            put($target, $css);
        } else copy($source, $target);
        $copied[$asset] = true;
    }
    foreach ($routes as $destination) {
        $doc = document(file_get_contents($output . $destination . 'index.html'));
        foreach ((new DOMXPath($doc))->query('//*[@href or @src or @poster]') as $node) foreach (['href','src','poster'] as $attribute) {
            $url = $node->getAttribute($attribute);
            if (!str_starts_with($url, '/')) continue;
            $path = rawurldecode(parse_url($url, PHP_URL_PATH));
            if (!is_file($output . $path . (str_ends_with($path, '/') ? 'index.html' : ''))) fail('Broken generated reference: ' . $url);
        }
    }
    // Ownership marker stays local, never uploaded by Wrangler.
    put($output . '/.assetsignore', ".generated\n.assetsignore\n");
    echo 'Export validated: ' . count($routes) . ' pages; ' . json_encode($counts) . '; ' . count($copied) . " assets.\n";
} catch (Throwable $error) {
    fwrite(STDERR, 'Export failed: ' . $error->getMessage() . "\nNo deployment was started.\n");
    exit(1);
}
