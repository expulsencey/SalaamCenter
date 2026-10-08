<?php
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) { http_response_code(404); exit; }
function adminInput(string $name, string $label, mixed $value, string $type = 'text', int $max = 250): void
{
    echo '<label>' . escapeHtml($label) . '<input name="' . escapeHtml($name) . '" type="' . escapeHtml($type) . '" maxlength="' . $max . '" value="' . escapeHtml((string)($value ?? '')) . '"></label>';
}
function adminArea(string $name, string $label, mixed $value, int $max = 10000): void
{
    echo '<label>' . escapeHtml($label) . '<textarea name="' . escapeHtml($name) . '" rows="4" maxlength="' . $max . '">' . escapeHtml((string)($value ?? '')) . '</textarea></label>';
}
function adminSelect(string $name, string $label, array $options, mixed $value): void
{
    echo '<label>' . escapeHtml($label) . '<select name="' . escapeHtml($name) . '">';
    foreach ($options as $key=>$text) echo '<option value="' . escapeHtml((string)$key) . '"' . ((string)$key === (string)$value ? ' selected' : '') . '>' . escapeHtml($text) . '</option>';
    echo '</select></label>';
}
function adminNotice(string $error): void
{
    if ($error) echo '<p class="notice error" role="alert">' . escapeHtml($error) . '</p>';
    elseif (isset($_SESSION['cms_notice'])) {
        echo '<p class="notice" role="status">'.escapeHtml($_SESSION['cms_notice']).'</p>';
        unset($_SESSION['cms_notice']);
    }
    elseif (isset($_GET['saved'])) echo '<p class="notice" role="status">Changes saved.</p>';
}

function adminSavedNotice(string $kind, string $status, string $previous, bool $uploaded = false): void
{
    $message = $status === 'published' ? $kind.' published. The website now shows this saved version.'
        : ($status === 'draft' ? ($previous === 'published' ? $kind.' unpublished and saved as a private draft.' : $kind.' saved as a private draft.') : $kind.' saved as '.$status.'.');
    $_SESSION['cms_notice'] = $message.($uploaded ? ' Your image was uploaded successfully.' : '');
}

function adminBadge(string $status): void
{
    $known = in_array($status, ['draft','published','upcoming','completed'], true);
    echo '<span class="badge'.($known?' badge--'.$status:'').'">'.escapeHtml(ucfirst($status)).'</span>';
}

function adminDate(?string $value, bool $time = true): string
{
    if (!$value) return '';
    return (new DateTimeImmutable($value, new DateTimeZone('UTC')))->format($time?'j M Y, H:i':'j M Y').($time?' UTC':'');
}

function adminEditorNav(array $sections): void
{
    echo '<nav class="editor-sections" aria-label="Editor sections"><span>On this page</span>';
    foreach ($sections as $id=>$label) echo '<a href="#'.escapeHtml($id).'">'.escapeHtml($label).'</a>';
    echo '</nav>';
}

function adminImageChoices(bool $courseAssets = false): array
{
    $choices = [''=>'No image'];
    if ($courseAssets) foreach (glob(__DIR__.'/../assets/images/courses/*') as $file) {
        if (is_file($file) && in_array(strtolower(pathinfo($file,PATHINFO_EXTENSION)),['jpg','jpeg','png','webp'],true))
            $choices['assets/images/courses/'.basename($file)] = ucwords(str_replace(['-','_'],' ',pathinfo($file,PATHINFO_FILENAME)));
    }
    foreach (cmsDatabase()->query("SELECT featured_image,title AS label FROM cms_articles WHERE featured_image IS NOT NULL UNION SELECT featured_image,JSON_UNQUOTE(JSON_EXTRACT(data,'$.name')) FROM cms_courses WHERE featured_image IS NOT NULL") as $media) {
        $choices['media.php?file='.$media['featured_image']] = 'Uploaded: '.$media['label'];
    }
    return $choices;
}

function adminImagePicker(string $name, array $choices, string $selected): void
{
    echo '<div data-image-picker>';
    adminSelect($name,'Choose an existing image',$choices,$selected);
    echo '<img class="editor-image image-selection" data-image-preview alt="Selected featured image"'.($selected!==''?' src="../'.escapeHtml($selected).'"':' hidden').'>';
    echo '</div>';
}

function adminRepeatable(string $name, string $label, array $items): void
{
    echo '<fieldset class="repeatable" data-list-name="'.escapeHtml($name).'"><legend>'.escapeHtml($label).'</legend>';
    echo '<input type="hidden" name="lists_present['.escapeHtml($name).']" value="1"><div class="repeatable-items">';
    // A blank row also allows an additional item when JavaScript is unavailable.
    foreach (count($items)<80?[...$items, '']:$items as $i=>$text) {
        echo '<div class="repeatable-row"><label>Item '.($i+1).'<textarea name="'.escapeHtml($name).'['.$i.']" rows="2" maxlength="10000">'.escapeHtml($text).'</textarea></label><button type="button" data-remove-item hidden>Remove</button></div>';
    }
    echo '</div><button type="button" data-add-item hidden>Add item</button><p class="help">Clear an item to remove it when saving.</p><span class="visually-hidden" data-list-status role="status"></span></fieldset>';
}

function adminPagination(string $path, int $page, int $pages, array $filters): void
{
    if ($pages < 2) return;
    echo '<nav class="pagination" aria-label="Results pages">';
    if ($page>1) echo '<a href="'.escapeHtml($path.'?'.http_build_query($filters+['page'=>$page-1])).'">Previous page</a>';
    echo '<span>Page '.$page.' of '.$pages.'</span>';
    if ($page<$pages) echo '<a href="'.escapeHtml($path.'?'.http_build_query($filters+['page'=>$page+1])).'">Next page</a>';
    echo '</nav>';
}
