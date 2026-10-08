<?php
require __DIR__ . '/../includes/cms-auth.php';
require __DIR__ . '/../includes/course-store.php';
require __DIR__ . '/_layout.php'; require __DIR__ . '/_fields.php';
cmsRequireUser(); $error=''; $rows=[];
$categories=require __DIR__ . '/../data/categories.php';
$search=is_string($_GET['q']??null)?trim($_GET['q']):'';
$status=is_string($_GET['status']??null)?$_GET['status']:'';
$category=is_string($_GET['category']??null)?$_GET['category']:'';
$page=max(1,(int)(filter_var($_GET['page']??1,FILTER_VALIDATE_INT)?:1)); $total=0; $pages=1;
try {
    courseStoreReady(); $where=[]; $values=[];
    if($status!=='') {$where[]='status=?';$values[]=$status;}
    if($category!=='') {$where[]="JSON_UNQUOTE(JSON_EXTRACT(data,'$.category_slug'))=?";$values[]=$category;}
    if($search!=='') {$where[]="(LOCATE(?,JSON_UNQUOTE(JSON_EXTRACT(data,'$.name')))>0 OR LOCATE(?,slug)>0)";$values[]=$search;$values[]=$search;}
    $condition=$where?' WHERE '.implode(' AND ',$where):'';
    $q=cmsDatabase()->prepare('SELECT COUNT(*) FROM cms_courses'.$condition);$q->execute($values);$total=(int)$q->fetchColumn();
    $pages=max(1,(int)ceil($total/25));$page=min($page,$pages);$offset=($page-1)*25;
    $q=cmsDatabase()->prepare('SELECT * FROM cms_courses'.$condition.' ORDER BY sort_order,id LIMIT 25 OFFSET '.$offset);$q->execute($values);
    foreach($q as $row){$row['course']=courseRecord($row,false);$rows[]=$row;}
} catch(Throwable $e) { http_response_code(503); $error='Courses are temporarily unavailable. Please ask the site owner to check the migration.'; }
adminHead('Courses'); adminNotice($error);
?>
<div class="page-actions"><p><?= $total ?> courses in this view. Edit content here; published changes appear on the website.</p><a class="button primary" href="course-edit.php">Add course</a></div>
<form class="filters" method="get"><?php adminInput('q','Search courses',$search); adminSelect('status','Status',[''=>'All statuses','published'=>'Published','draft'=>'Draft'],$status);adminSelect('category','Category',[''=>'All categories']+array_map(fn($c)=>$c['label'],$categories),$category); ?><button>Search</button><a href="courses.php">Clear</a></form>
<ul class="article-list"><?php foreach($rows as $row): ?><li><div><h2 lang="<?= escapeHtml($row['course']['title_language']) ?>"><a href="course-edit.php?id=<?= (int)$row['id'] ?>"><?= escapeHtml($row['course']['name']) ?></a></h2><p><?= escapeHtml($row['course']['category']) ?> <?php adminBadge($row['status']); ?><?php if($row['featured']): ?> <span class="badge">Homepage</span><?php endif; ?></p><p class="help">Updated <?= escapeHtml(adminDate($row['updated_at'])) ?></p></div><div class="actions"><a href="course-edit.php?id=<?= (int)$row['id'] ?>">Edit</a><a href="course-preview.php?id=<?= (int)$row['id'] ?>">Preview saved version</a><a href="sessions.php?course_id=<?= (int)$row['id'] ?>">Sessions</a><?php if($row['status']==='published'): ?><a href="../course.php?slug=<?= rawurlencode($row['slug']) ?>" target="_blank" rel="noopener">View on website</a><?php endif; ?></div></li><?php endforeach; ?></ul>
<?php adminPagination('courses.php',$page,$pages,['q'=>$search,'status'=>$status,'category'=>$category]); ?>
<?php if(!$error&&!$rows): ?><div class="empty-state"><h2>No courses in this view</h2><p>Try a different search or clear the filters. To add a new course, start with a draft.</p><a class="button" href="course-edit.php">Add course</a></div><?php endif; adminFoot(); ?>
