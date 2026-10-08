<?php
require __DIR__ . '/../includes/cms-auth.php'; require __DIR__ . '/../includes/course-store.php';
require __DIR__ . '/_layout.php'; require __DIR__ . '/_fields.php'; cmsRequireUser();
$id=filter_var($_GET['id']??0,FILTER_VALIDATE_INT,['options'=>['min_range'=>0]]);
if($id===false){http_response_code(404);exit('Course not found.');}
$categories=require __DIR__ . '/../data/categories.php';
$row=['id'=>0,'slug'=>'','status'=>'draft','featured'=>0,'sort_order'=>1,'published_at'=>null,'featured_image'=>null,'version'=>0];
$data=courseDefaults(); $error='';$images=[''=>'No image'];
try {
    courseStoreReady(); $pdo=cmsDatabase();
    if($id){$row=courseRow($id);if(!$row){http_response_code(404);exit('Course not found.');}$data=json_decode($row['data'],true,32,JSON_THROW_ON_ERROR)+courseDefaults();}
    else $row['sort_order']=(int)$pdo->query('SELECT COALESCE(MAX(sort_order),0)+1 FROM cms_courses')->fetchColumn();
    $images=adminImageChoices(true);
    if(!$id && $_SERVER['REQUEST_METHOD']==='GET' && is_string($_GET['image']??null) && isset($images['media.php?file='.$_GET['image']])) $data['image']='media.php?file='.$_GET['image'];
    if($_SERVER['REQUEST_METHOD']==='POST'){
        cmsCheckCsrf();$saved=$row;
        foreach(['name'=>200,'title_language'=>2,'category_slug'=>100,'subtitle'=>250,'description'=>10000,'duration'=>250,'language'=>100,'format'=>250,'level'=>250,'image_alt'=>250] as $key=>$limit) if(array_key_exists($key,$_POST)) { $value=cmsText($_POST[$key],$limit); $data[$key]=$value==='' && in_array($key,['subtitle','description','duration','language','format','level'],true)?null:$value; }
        if($data['name']===''||!isset($categories[$data['category_slug']])||!in_array($data['title_language'],['en','fr'],true))throw new InvalidArgumentException('Enter a title, choose a category and select the title language.');
        foreach(['outcomes','includes','certificate','requirements','exam'] as $key) {
            if (isset($_POST['lists_present'][$key])) $data[$key]=courseListInput($_POST[$key]??[]);
        }
        $slug=cmsText($_POST['slug']??$saved['slug'],190);
        if($saved['published_at']!==null && $slug!==$saved['slug'])throw new InvalidArgumentException('Published URLs stay fixed. You can change the title.');
        if($slug===''){$base=cmsSlug($data['name']);if(!$base)throw new InvalidArgumentException('Enter a URL name using letters a-z and numbers.');$slug=$base;$n=2;$q=$pdo->prepare('SELECT id FROM cms_courses WHERE slug=? AND id<>?');while(true){$q->execute([$slug,$id]);if(!$q->fetch())break;$slug=$base.'-'.$n++;}}
        if(!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D',$slug))throw new InvalidArgumentException('Use lowercase letters a-z, numbers and single hyphens in the URL name.');
        $row['slug']=$slug;
        $row['sort_order']=filter_var($_POST['sort_order']??$saved['sort_order'],FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>100000]]);
        if(!$row['sort_order'])throw new InvalidArgumentException('Choose a display order between 1 and 100000.');
        if (isset($_POST['featured_present'])) $row['featured']=isset($_POST['featured'])?1:0;
        $action=$_POST['action']??'';if(!in_array($action,['draft','publish'],true))throw new InvalidArgumentException('Choose Save draft or Publish.');
        $status=$action==='publish'?'published':'draft';
        $selected=cmsText($_POST['image']??($saved['featured_image']?'media.php?file='.$saved['featured_image']:$data['image']),250);if(array_key_exists('image',$_POST)&&!array_key_exists($selected,$images))throw new InvalidArgumentException('Choose an available image or upload one.');
        $newImage=null;
        try{
            $newImage=cmsUpload($_FILES['upload']??[]);
            $media=$newImage??(str_starts_with($selected,'media.php?file=')?substr($selected,15):null);
            $data['image']=$media?'media.php?file='.$media:$selected;
            if($status==='published' && ($data['image']===''||$data['image_alt']===''))throw new InvalidArgumentException('Choose an image and describe it before publishing.');
            $published=$status==='published'?($saved['published_at']??gmdate('Y-m-d H:i:s')):$saved['published_at'];
            $values=[$slug,$status,$row['sort_order'],$row['featured'],json_encode($data,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR),$media,$published];
            if($id){$q=$pdo->prepare('UPDATE cms_courses SET slug=?,status=?,sort_order=?,featured=?,data=?,featured_image=?,published_at=?,updated_at=UTC_TIMESTAMP(),version=version+1 WHERE id=? AND version=?');$q->execute([...$values,$id,filter_var($_POST['version']??null,FILTER_VALIDATE_INT)]);if(!$q->rowCount())throw new InvalidArgumentException('This course changed in another window. Reload before saving.');}
            else{$q=$pdo->prepare('INSERT INTO cms_courses(slug,status,sort_order,featured,data,featured_image,published_at) VALUES (?,?,?,?,?,?,?)');$q->execute($values);$id=(int)$pdo->lastInsertId();}
        }catch(Throwable $e){if($newImage&&is_file(cmsMediaPath($newImage)))unlink(cmsMediaPath($newImage));throw $e;}
        adminSavedNotice('Course',$status,$saved['status'],(bool)$newImage);
        header('Location: '.'course-edit.php?saved=1&id='.$id,true,303);exit;
    }
}catch(InvalidArgumentException $e){$error=$e->getMessage();http_response_code(422);}
catch(PDOException $e){$error=$e->getCode()==='23000'?'That URL name is already used. Choose another one.':'Courses are temporarily unavailable. Changes were not saved.';http_response_code($e->getCode()==='23000'?422:503);}
catch(Throwable $e){$error='Courses are temporarily unavailable. Changes were not saved.';http_response_code(503);}
adminHead($id?'Edit course':'Add course');adminNotice($error);
?>
<p><?php adminBadge($row['status']); ?> Save as a draft to prepare content privately.</p>
<?php if($id): ?><div class="page-actions"><p class="help">Last saved <?= escapeHtml(adminDate($row['updated_at']??null)) ?></p><a class="button" href="course-preview.php?id=<?= (int)$id ?>" target="_blank" rel="noopener">Preview saved version</a></div><?php endif; ?>
<?php adminEditorNav(['course-basics'=>'Basics','course-image'=>'Image','course-information'=>'Information','course-content'=>'Content','course-presentation'=>'Presentation','course-save'=>'Save']); ?>
<form method="post" enctype="multipart/form-data" data-content-editor><input type="hidden" name="csrf_token" value="<?= escapeHtml(cmsToken()) ?>"><input type="hidden" name="version" value="<?= (int)$row['version'] ?>">
<fieldset id="course-basics"><legend>Course essentials</legend><?php adminInput('name','Course title',$data['name'],'text',200); ?><label>URL name<input name="slug" maxlength="190" value="<?= escapeHtml($row['slug']) ?>"<?= $row['published_at']?' readonly':'' ?>></label><p class="help">Leave blank to generate a URL. Published URLs stay fixed.</p><div class="form-columns"><?php adminSelect('category_slug','Category',[''=>'Choose a category']+array_map(fn($c)=>$c['label'],$categories),$data['category_slug']);adminSelect('title_language','Official title language',['en'=>'English','fr'=>'French'],$data['title_language']); ?></div><?php adminInput('subtitle','Official subtitle (optional)',$data['subtitle']);adminArea('description','Course description',$data['description']); ?></fieldset>
<fieldset id="course-image"><legend>Course image</legend><?php adminImagePicker('image',$images,$row['featured_image']?'media.php?file='.$row['featured_image']:$data['image']); ?><label>Or upload a new image<input type="file" name="upload" accept="image/jpeg,image/png,image/webp"></label><p class="help">JPEG, PNG or WebP, up to 5 MB and 12 megapixels. Images are validated and stored privately.</p><?php adminInput('image_alt','Image description',$data['image_alt']); ?></fieldset>
<fieldset id="course-information"><legend>General information</legend><div class="form-columns"><?php foreach(['duration'=>'General duration','language'=>'Teaching language','format'=>'Training format','level'=>'Level'] as $key=>$label)adminInput($key,$label,$data[$key],'text',$key==='language'?100:250); ?></div><p class="help">Leave unverified information blank. Write duration units in English. Session-specific information is managed under Training Sessions.</p>
</fieldset>
<fieldset id="course-content"><legend>Course details</legend><p class="help">Add one item per row. Empty rows are omitted. Preserve verified wording and leave unknown details blank.</p><?php foreach(['outcomes'=>'Learning outcomes','includes'=>'Course includes','certificate'=>'Certification information','requirements'=>'Requirements','exam'=>'Exam and evaluation'] as $key=>$label)adminRepeatable($key,$label,$data[$key]); ?></fieldset>
<fieldset id="course-presentation"><legend>Presentation</legend><?php adminInput('sort_order','Display order',$row['sort_order'],'number'); ?><input type="hidden" name="featured_present" value="1"><label class="check"><input type="checkbox" name="featured"<?= $row['featured']?' checked':'' ?>> Feature on the homepage</label></fieldset>
<div class="actions editor-actions" id="course-save"><button name="action" value="draft"><?= $row['status']==='published'?'Unpublish and save as draft':'Save draft' ?></button><button class="primary" name="action" value="publish"><?= $row['status']==='published'?'Update published course':'Publish' ?></button></div><p class="help">Update published course saves directly to the public website. To prepare changes privately, unpublish first. Preview opens the saved version and does not save this form.</p>
</form>
<?php if($id): ?>
<p><a class="button" href="course-preview.php?id=<?= (int)$id ?>">Preview saved version</a> <a href="sessions.php?course_id=<?= (int)$id ?>">View training sessions</a></p>
<details class="retirement"><summary>Retire or delete this course</summary>
<p><strong><?= escapeHtml($data['name']) ?></strong></p><p>Unpublish and save as draft is the normal way to retire a course. Permanent deletion cannot be undone. It is available only for drafts without training sessions. Images are retained.</p>
<form method="post" action="course-delete.php"><input type="hidden" name="csrf_token" value="<?= escapeHtml(cmsToken()) ?>"><input type="hidden" name="id" value="<?= (int)$id ?>"><input type="hidden" name="version" value="<?= (int)$row['version'] ?>">
<label>Type the URL name to confirm deletion: <?= escapeHtml($row['slug']) ?><input name="confirm_slug" required autocomplete="off" maxlength="190"></label><button class="destructive">Delete course permanently</button></form></details>
<?php endif; adminFoot(); ?>
