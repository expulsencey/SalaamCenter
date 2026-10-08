<?php
require __DIR__ . '/../includes/cms-auth.php';
require __DIR__ . '/_layout.php';
require __DIR__ . '/_fields.php';
cmsRequireUser();
$id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
if ($id === false) { http_response_code(404); exit('Article not found.'); }
$article = ['id'=>0,'title'=>'','slug'=>'','excerpt'=>'','content'=>'[]','featured_image'=>null,'image_alt'=>'','status'=>'draft','published_at'=>null,'version'=>0];
$images=[''=>'No image']; $selectedImage='';
$error = ''; $blocks = [['type'=>'p','text'=>'']];
try {
    $pdo = cmsDatabase();
    if ($id) {
        $q = $pdo->prepare('SELECT * FROM cms_articles WHERE id=?'); $q->execute([$id]); $article = $q->fetch();
        if (!$article) { http_response_code(404); exit('Article not found.'); }
        $blocks = json_decode($article['content'], true, 16, JSON_THROW_ON_ERROR);
    }
    $images=adminImageChoices();
    $selectedImage=$article['featured_image']?'media.php?file='.$article['featured_image']:'';
    if(!$id && $_SERVER['REQUEST_METHOD']==='GET' && is_string($_GET['image']??null) && isset($images['media.php?file='.$_GET['image']])) $selectedImage='media.php?file='.$_GET['image'];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        cmsCheckCsrf(); $saved = $article;
        $article['title'] = cmsText($_POST['title'] ?? '', 200);
        $article['excerpt'] = cmsText($_POST['excerpt'] ?? '', 500);
        $article['image_alt'] = cmsText($_POST['image_alt'] ?? '', 250);
        $blocks = cmsBlocks($_POST['blocks'] ?? []);
        if ($article['title'] === '') throw new InvalidArgumentException('Enter an article title.');
        $slug = cmsText($_POST['slug'] ?? '', 190);
        if ($saved['published_at'] !== null && $slug !== $saved['slug']) throw new InvalidArgumentException('Published URLs are kept stable. The title can still be changed.');
        if ($slug === '') {
            $base = cmsSlug($article['title']);
            if ($base === '') throw new InvalidArgumentException('Enter a URL name using letters a-z and numbers.');
            $slug = $base; $suffix = 2;
            $q = $pdo->prepare('SELECT id FROM cms_articles WHERE slug=? AND id<>?');
            while (true) { $q->execute([$slug,$id]); if (!$q->fetch()) break; $slug = $base . '-' . $suffix++; }
        }
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug)) throw new InvalidArgumentException('Use lowercase letters a-z, numbers and single hyphens in the URL name.');
        $article['slug'] = $slug; $action = $_POST['action'] ?? '';
        if (!in_array($action, ['draft','publish'], true)) throw new InvalidArgumentException('Choose Save draft or Publish.');
        $status = $action === 'publish' ? 'published' : 'draft';
        if ($status === 'published') {
            if ($article['excerpt'] === '' || !$blocks) throw new InvalidArgumentException('Add a summary and article content before publishing.');
            $heading = 1;
            foreach ($blocks as $block) if (in_array($block['type'], ['h2','h3'], true)) { $level = (int) $block['type'][1]; if ($level > $heading + 1) throw new InvalidArgumentException('Add a section heading before a subsection heading.'); $heading = $level; }
            $q = $pdo->prepare("SELECT id FROM cms_articles WHERE status='published' AND id<>? AND (title=? OR excerpt=?)");
            $q->execute([$id,$article['title'],$article['excerpt']]);
            if ($q->fetch()) throw new InvalidArgumentException('Use a distinct title and summary for each published article.');
        }
        $selectedImage=cmsText($_POST['existing_image']??($saved['featured_image']?'media.php?file='.$saved['featured_image']:''),250);
        if(!array_key_exists($selectedImage,$images)) throw new InvalidArgumentException('Choose an available uploaded image or upload a new one.');
        $newImage = null;
        try {
            $newImage = cmsUpload($_FILES['image'] ?? []); $image = $newImage ?? ($selectedImage!==''?substr($selectedImage,15):null);
            if ($image && $article['image_alt'] === '') throw new InvalidArgumentException('Describe the featured image for readers who cannot see it.');
            $published = $status === 'published' ? ($saved['published_at'] ?? gmdate('Y-m-d H:i:s')) : $saved['published_at'];
            $values = [$article['title'],$slug,$article['excerpt'],json_encode($blocks, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),$image,$article['image_alt'],$status,$published];
            if ($id) {
                $version = filter_var($_POST['version'] ?? null, FILTER_VALIDATE_INT);
                $q = $pdo->prepare('UPDATE cms_articles SET title=?,slug=?,excerpt=?,content=?,featured_image=?,image_alt=?,status=?,published_at=?,updated_at=UTC_TIMESTAMP(),version=version+1 WHERE id=? AND version=?');
                $q->execute([...$values,$id,$version]);
                if (!$q->rowCount()) throw new InvalidArgumentException('This article changed in another window. Reload it before saving again.');
            } else {
                $q = $pdo->prepare('INSERT INTO cms_articles (title,slug,excerpt,content,featured_image,image_alt,status,published_at) VALUES (?,?,?,?,?,?,?,?)');
                $q->execute($values); $id = (int) $pdo->lastInsertId();
            }
        } catch (Throwable $e) { if ($newImage && is_file(cmsMediaPath($newImage))) unlink(cmsMediaPath($newImage)); throw $e; }
        adminSavedNotice('Article',$status,$saved['status'],(bool)$newImage);
        header('Location: ' . 'article-edit.php?saved=1&id=' . $id, true, 303); exit;
    }
} catch (InvalidArgumentException $e) { $error = $e->getMessage(); http_response_code(422); }
catch (PDOException $e) { $error = $e->getCode() === '23000' ? 'That URL name is already used. Choose a different one.' : 'Articles are temporarily unavailable. Your changes have not been saved.'; http_response_code($e->getCode() === '23000' ? 422 : 503); }
catch (Throwable $e) { $error = 'Your changes could not be saved. Please try again later.'; http_response_code(503); }
adminHead($id ? 'Edit article' : 'New article');
?>
<?php adminNotice($error); ?>
<div class="page-actions"><p><?php adminBadge($article['status']); ?> <?php if($article['published_at']): ?>First published <?= escapeHtml(adminDate($article['published_at'])) ?><?php else: ?>Save a draft before publishing.<?php endif; ?></p>
<?php if($id): ?><a class="button" href="preview.php?id=<?= (int)$id ?>" target="_blank" rel="noopener">Preview saved version</a><?php endif; ?></div>
<?php adminEditorNav(['article-basics'=>'Basics','article-image'=>'Image','article-content'=>'Content','article-save'=>'Save']); ?>
<form method="post" enctype="multipart/form-data" id="article-editor" data-content-editor>
<input type="hidden" name="csrf_token" value="<?= escapeHtml(cmsToken()) ?>"><input type="hidden" name="version" value="<?= (int) $article['version'] ?>">
<fieldset id="article-basics"><legend>Basic information</legend><label>Title<input name="title" required maxlength="200" value="<?= escapeHtml($article['title']) ?>"></label>
<label>URL name<input name="slug" maxlength="190" value="<?= escapeHtml($article['slug']) ?>"<?= $article['published_at'] ? ' readonly' : '' ?> aria-describedby="slug-help"></label><p id="slug-help" class="help">Leave blank to generate from the title. Published URLs stay fixed.</p>
<label>Short summary<textarea name="excerpt" maxlength="500" rows="3"><?= escapeHtml($article['excerpt']) ?></textarea></label><p class="help">Used on the News page and in search and sharing previews.</p>
</fieldset><fieldset id="article-image"><legend>Featured image</legend><?php adminImagePicker('existing_image',$images,$selectedImage); ?>
<label>Upload image<input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label><p class="help">JPEG, PNG or WebP. Up to 5 MB, 12 megapixels and 6000 pixels per side.</p>
<label>Image description<input name="image_alt" maxlength="250" value="<?= escapeHtml($article['image_alt']) ?>"></label></fieldset>
<section id="article-content"><h2>Article content</h2><p class="help">Choose a block type. For lists, write one item per line. Select text and use Bold, Italic or Link. HTML is displayed as text.</p>
<div id="content-blocks"><?php foreach ($blocks ?: [['type'=>'p','text'=>'']] as $i => $block): ?><fieldset class="content-block"><legend>Content block</legend><label>Block type<select name="blocks[<?= $i ?>][type]"><?php foreach (['p'=>'Paragraph','h2'=>'Section heading','h3'=>'Subsection heading','ul'=>'Bullet list','ol'=>'Numbered list'] as $type=>$label): ?><option value="<?= $type ?>"<?= $block['type'] === $type ? ' selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select></label><label>Text<textarea name="blocks[<?= $i ?>][text]" rows="5" maxlength="10000"><?= escapeHtml($block['text']) ?></textarea></label></fieldset><?php endforeach; ?></div>
<button type="button" id="add-block" hidden>Add content block</button>
</section><div class="actions editor-actions" id="article-save"><button name="action" value="draft"><?= $article['status'] === 'published' ? 'Unpublish and save as draft' : 'Save draft' ?></button><button name="action" value="publish" class="primary"><?= $article['status'] === 'published' ? 'Update published article' : 'Publish' ?></button></div>
<p class="help">Update published article saves directly to the public website. Unpublish first to prepare changes privately. Preview opens the saved version and does not save this form.</p>
</form>
<?php if($id): ?>
<details class="retirement"><summary>Retire or delete this article</summary>
<p><strong><?= escapeHtml($article['title']) ?></strong></p><p>Unpublish to remove the article from the website without losing it. Permanent deletion is available only for drafts and cannot be undone. Images are retained.</p>
<form method="post" action="article-delete.php"><input type="hidden" name="csrf_token" value="<?= escapeHtml(cmsToken()) ?>"><input type="hidden" name="id" value="<?= (int)$id ?>"><input type="hidden" name="version" value="<?= (int)$article['version'] ?>">
<label>Type the URL name to confirm deletion: <?= escapeHtml($article['slug']) ?><input name="confirm_slug" required maxlength="190" autocomplete="off"></label><button class="destructive">Delete article permanently</button></form>
</details><?php endif; adminFoot(); ?>
