<?php
require_once __DIR__ . '/helpers.php';

function cmsDatabase(): PDO
{
    static $pdo;
    if ($pdo) return $pdo;
    // Same private configuration shape as Contact; do not load its form/session handler.
    $configFile = __DIR__ . '/../config.local.php';
    // An explicit server environment override supports isolated QA/deployments, never request input.
    if (getenv('SALAAM_CMS_CONFIG')) $configFile = getenv('SALAAM_CMS_CONFIG');
    $config = is_file($configFile) ? require $configFile : null;
    $db = is_array($config) ? ($config['db'] ?? null) : null;
    if (!is_array($db) || empty($db['host']) || empty($db['database']) || empty($db['username']) || !isset($db['password'])) throw new RuntimeException('CMS unavailable.');
    if (preg_match('/[;\x00-\x1f]/', $db['host'] . $db['database'])) throw new RuntimeException('CMS unavailable.');
    $port = filter_var($db['port'] ?? 3306, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]);
    if (!$port) throw new RuntimeException('CMS unavailable.');
    $pdo = new PDO('mysql:host=' . $db['host'] . ';port=' . $port . ';dbname=' . $db['database'] . ';charset=utf8mb4', $db['username'], $db['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_TIMEOUT => 3]);
    $pdo->exec("SET time_zone = '+00:00'");
    return $pdo;
}

function cmsPublished(): array
{
    return cmsDatabase()->query("SELECT id,title,slug,excerpt,featured_image,image_alt,published_at,updated_at FROM cms_articles WHERE status='published' AND published_at <= UTC_TIMESTAMP() ORDER BY published_at DESC,id DESC")->fetchAll();
}

function cmsArticle(string $slug): ?array
{
    if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug) || strlen($slug) > 190) return null;
    $query = cmsDatabase()->prepare("SELECT * FROM cms_articles WHERE slug=? AND status='published' AND published_at <= UTC_TIMESTAMP()");
    $query->execute([$slug]);
    return $query->fetch() ?: null;
}

function cmsSlug(string $title): string
{
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', str_replace(['’', "'"], '-', $title));
    return trim(substr(strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $ascii ?: '')), 0, 180), '-');
}

function cmsText(mixed $value, int $max): string
{
    if (!is_string($value) || !mb_check_encoding($value, 'UTF-8') || str_contains($value, "\0") || mb_strlen($value) > $max) throw new InvalidArgumentException('Please check the length and characters in your text.');
    return trim($value);
}

function cmsBlocks(mixed $input): array
{
    if (!is_array($input) || count($input) > 80) throw new InvalidArgumentException('Use up to 80 content blocks.');
    $blocks = []; $length = 0;
    foreach ($input as $block) {
        if (!is_array($block) || !in_array($block['type'] ?? '', ['p','h2','h3','ul','ol'], true)) throw new InvalidArgumentException('Choose a valid content block type.');
        $text = cmsText($block['text'] ?? '', 10000);
        $length += strlen($text);
        if ($length > 100000) throw new InvalidArgumentException('The article is too long.');
        if ($text !== '') $blocks[] = ['type' => $block['type'], 'text' => $text];
    }
    return $blocks;
}

function cmsInline(string $text): string
{
    // Tokens never become arbitrary HTML. Every text/attribute is escaped independently.
    $pattern = '/(\*\*[^*\n]+\*\*|\*[^*\n]+\*|\[[^\]\n]+\]\((?:https?:\/\/|mailto:)[^\s)]+\))/u';
    $parts = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    $html = '';
    foreach ($parts as $part) {
        if (preg_match('/^\[([^\]\n]+)\]\(([^\s)]+)\)$/uD', $part, $m)) {
            $link = $m[2];
            $valid = preg_match('/^https?:\/\//i', $link) ? filter_var($link, FILTER_VALIDATE_URL) : (str_starts_with($link, 'mailto:') && filter_var(substr($link, 7), FILTER_VALIDATE_EMAIL));
            $html .= $valid ? '<a href="' . escapeHtml($link) . '" rel="nofollow">' . escapeHtml($m[1]) . '</a>' : escapeHtml($part);
        } elseif (str_starts_with($part, '**') && str_ends_with($part, '**')) $html .= '<strong>' . escapeHtml(substr($part, 2, -2)) . '</strong>';
        elseif (str_starts_with($part, '*') && str_ends_with($part, '*')) $html .= '<em>' . escapeHtml(substr($part, 1, -1)) . '</em>';
        else $html .= nl2br(escapeHtml($part));
    }
    return $html;
}

function cmsContent(string $json): string
{
    $blocks = cmsBlocks(json_decode($json, true, 16, JSON_THROW_ON_ERROR));
    $html = '';
    foreach ($blocks as $block) {
        $tag = $block['type'];
        $text = $block['text'];
        if (in_array($tag, ['ul','ol'], true)) {
            $html .= '<' . $tag . '>';
            foreach (preg_split('/\R/u', $text) as $line) if (trim($line) !== '') $html .= '<li>' . cmsInline($line) . '</li>';
            $html .= '</' . $tag . '>';
        } else $html .= '<' . $tag . '>' . cmsInline($text) . '</' . $tag . '>';
    }
    return $html;
}

function cmsMediaPath(string $name): string
{
    if (!preg_match('/^[a-f0-9]{48}\.webp$/D', $name)) return '';
    return __DIR__ . '/../storage/cms-media/' . $name;
}

function cmsUpload(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if (($file['error'] ?? -1) !== UPLOAD_ERR_OK || !is_string($file['tmp_name'] ?? null) || !is_uploaded_file($file['tmp_name']) || ($file['size'] ?? 0) > 5 * 1024 * 1024) throw new InvalidArgumentException('Choose a JPEG, PNG or WebP image up to 5 MB.');
    $type = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $size = @getimagesize($file['tmp_name']);
    if (!in_array($type, ['image/jpeg','image/png','image/webp'], true) || !$size || $size[0] * $size[1] > 12000000 || max($size[0], $size[1]) > 6000) throw new InvalidArgumentException('Choose a valid image up to 12 megapixels and 6000 pixels per side.');
    $image = @imagecreatefromstring(file_get_contents($file['tmp_name']));
    if (!$image) throw new InvalidArgumentException('This image could not be read.');
    $directory = __DIR__ . '/../storage/cms-media';
    if (!is_dir($directory) && !mkdir($directory, 0700, true)) throw new RuntimeException('Upload unavailable.');
    $name = bin2hex(random_bytes(24)) . '.webp';
    $stream = fopen($directory . '/' . $name, 'xb');
    if (!$stream) throw new RuntimeException('Upload unavailable.');
    try { if (!imagewebp($image, $stream, 90)) throw new RuntimeException('Upload unavailable.'); }
    finally { fclose($stream); imagedestroy($image); }
    return $name;
}
