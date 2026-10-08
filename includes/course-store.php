<?php
require_once __DIR__ . '/cms.php';

function courseToday(): string { return (new DateTimeImmutable('now', new DateTimeZone('Africa/Djibouti')))->format('Y-m-d'); }

function courseStoreReady(): void
{
    if (!cmsDatabase()->query("SELECT name FROM cms_migrations WHERE name='courses-v1'")->fetchColumn()) throw new RuntimeException('Course migration is required.');
}

function courseSessions(int $id, bool $public = true): array
{
    $sql = 'SELECT id,course_id,start_date,duration,language,status,version FROM cms_course_sessions WHERE course_id=?'; $values = [$id];
    if ($public) { $sql .= " AND status='upcoming' AND start_date>=?"; $values[] = courseToday(); }
    $q = cmsDatabase()->prepare($sql . ' ORDER BY start_date,id'); $q->execute($values);
    return $q->fetchAll();
}

function courseRecord(array $row, bool $withSession = true): array
{
    $data = json_decode($row['data'], true, 32, JSON_THROW_ON_ERROR);
    // Legacy amounts stay in storage; public and preview projections never expose them.
    unset($data['price_source'], $data['price_source_currency'], $data['price_source_url']);
    $data['slug'] = $row['slug']; $data['order'] = (int) $row['sort_order']; $data['featured'] = (bool) $row['featured'];
    $data['next_session'] = null;
    if ($row['featured_image']) $data['image'] = 'media.php?file=' . $row['featured_image'];
    if ($withSession) {
        $sessions = courseSessions((int) $row['id']);
        $data['sessions'] = $sessions;
        if ($sessions) {
            $next = $sessions[0]; $data['next_session'] = $next['start_date'];
            foreach (['duration','language'] as $key) if ($next[$key] !== null && $next[$key] !== '') $data[$key] = $next[$key];
        }
    }
    $categories = require __DIR__ . '/../data/categories.php';
    $data['category'] = $categories[$data['category_slug']]['label'];
    return $data;
}

function publishedCourses(): array
{
    courseStoreReady(); $courses = [];
    foreach (cmsDatabase()->query("SELECT * FROM cms_courses WHERE status='published' ORDER BY sort_order,id") as $row) $courses[$row['slug']] = courseRecord($row);
    return $courses;
}

function courseRow(int $id): ?array
{
    $q = cmsDatabase()->prepare('SELECT * FROM cms_courses WHERE id=?'); $q->execute([$id]); return $q->fetch() ?: null;
}

function courseDefaults(): array
{
    return ['name'=>'','title_language'=>'en','category_slug'=>'','duration'=>null,'language'=>null,'format'=>null,'level'=>null,
        'next_session'=>null,'image'=>'','image_alt'=>'',
        'description'=>null,'subtitle'=>null,'outcomes'=>[],'includes'=>[],'certificate'=>[],'requirements'=>[],'exam'=>[],
        'source_urls'=>[],'aliases'=>[],'data_source'=>'Administrator supplied'];
}

function courseListInput(mixed $input): array
{
    if (!is_array($input) || count($input) > 80) throw new InvalidArgumentException('Use up to 80 items per list.');
    $items = []; $length = 0;
    foreach ($input as $value) {
        $text = cmsText($value, 10000); $length += mb_strlen($text);
        if ($length > 10000) throw new InvalidArgumentException('Use up to 10000 characters per list.');
        if ($text !== '') $items[] = $text;
    }
    return $items;
}

// Historical one-time seed import. Retained amounts are inactive compatibility data.
function courseImport(): array
{
    $pdo = cmsDatabase();
    if (!$pdo->query("SELECT GET_LOCK('salaam-courses-import',10)")->fetchColumn()) throw new RuntimeException('Course import is already running.');
    try {
        $pdo->beginTransaction();
        if ($pdo->query("SELECT name FROM cms_migrations WHERE name='courses-v1'")->fetchColumn()) { $pdo->commit(); return ['already_imported'=>true]; }
        if ((int) $pdo->query('SELECT COUNT(*) FROM cms_courses')->fetchColumn() !== 0) throw new RuntimeException('Existing courses require manual migration review.');
        $source = require __DIR__ . '/../data/courses.php';
        if (count($source) !== 24 || count(array_unique(array_column($source, 'slug'))) !== 24) throw new RuntimeException('Expected 24 unique original courses.');
        $insert = $pdo->prepare("INSERT INTO cms_courses(slug,status,sort_order,featured,data,published_at) VALUES (?,'published',?,?,?,UTC_TIMESTAMP())");
        $session = $pdo->prepare('INSERT INTO cms_course_sessions(course_id,start_date,duration,language,price_djf,status) VALUES (?,?,?,?,?,?)');
        $count = 0;
        foreach ($source as $original) {
            $data = $original; unset($data['slug'],$data['order'],$data['featured'],$data['next_session']);
            if ($original['next_session']) {
                if ($original['price_source'] !== null && $original['price_source_currency'] !== 'DJF') throw new RuntimeException('Dated non-DJF price needs review.');
                $data['price_source'] = null; $data['price_source_currency'] = null;
            }
            $insert->execute([$original['slug'],$original['order'],(int)$original['featured'],json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)]);
            if ($original['next_session']) {
                $session->execute([$pdo->lastInsertId(),$original['next_session'],$original['duration'],$original['language'],$original['price_source'],$original['next_session'] >= courseToday() ? 'upcoming' : 'completed']);
                $count++;
            }
        }
        $q = $pdo->prepare("INSERT INTO cms_migrations(name,source_hash) VALUES ('courses-v1',?)"); $q->execute([hash_file('sha256',__DIR__ . '/../data/courses.php')]);
        $pdo->commit(); return ['courses'=>24,'sessions'=>$count];
    } catch (Throwable $e) { if ($pdo->inTransaction()) $pdo->rollBack(); throw $e; }
    finally { $pdo->query("SELECT RELEASE_LOCK('salaam-courses-import')"); }
}
