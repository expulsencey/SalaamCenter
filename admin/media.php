<?php
require __DIR__.'/../includes/cms-auth.php';require __DIR__.'/_layout.php';require __DIR__.'/_fields.php';cmsRequireUser();
$items=[];$error='';$search=is_string($_GET['q']??null)?trim($_GET['q']):'';
try {
    $rows=cmsDatabase()->query("SELECT featured_image,title AS label,'article-edit.php' AS editor,id,status FROM cms_articles WHERE featured_image IS NOT NULL UNION ALL SELECT featured_image,JSON_UNQUOTE(JSON_EXTRACT(data,'$.name')),'course-edit.php',id,status FROM cms_courses WHERE featured_image IS NOT NULL")->fetchAll();
    foreach($rows as $row) $items[$row['featured_image']][]=$row;
    if($search!=='') $items=array_filter($items,fn($uses)=>mb_stripos(implode(' ',array_column($uses,'label')),$search)!==false);
}catch(Throwable $e){$error='Media is temporarily unavailable.';http_response_code(503);}
$total=count($items);$pages=max(1,(int)ceil($total/18));$page=min($pages,max(1,(int)(filter_var($_GET['page']??1,FILTER_VALIDATE_INT)?:1)));
$items=array_slice($items,($page-1)*18,18,true);
adminHead('Media');adminNotice($error); ?>
<p>Choose an uploaded image to reuse in a new course or article. To upload a new image, open either editor and use its image section.</p>
<div class="page-actions"><p><?= $total ?> uploaded images in this view</p><div class="actions"><a class="button" href="course-edit.php#course-image">Add course image</a><a class="button" href="article-edit.php#article-image">Add article image</a></div></div>
<form class="filters" method="get"><?php adminInput('q','Search by content title',$search); ?><button>Search</button><a href="media.php">Clear</a></form>
<?php if(!$error&&!$items): ?><div class="empty-state"><h2>No attached uploads in this view</h2><p>Images appear here after you save them with a course or article. Existing course photos are also available in the course editor. Allowed uploads: JPEG, PNG or WebP, up to 5 MB and 12 megapixels.</p></div><?php endif; ?>
<div class="media-grid"><?php foreach($items as $file=>$uses): ?><figure><img class="editor-image" src="../media.php?file=<?= escapeHtml($file) ?>" alt="" loading="lazy"><figcaption><h2>Used in <?= count($uses) ?> <?= count($uses)===1?'item':'items' ?></h2><ul><?php foreach($uses as $use): ?><li><a href="<?= $use['editor'] ?>?id=<?= (int)$use['id'] ?>"><?= escapeHtml($use['label']) ?></a> <?php adminBadge($use['status']); ?></li><?php endforeach; ?></ul><div class="actions"><a class="button" href="course-edit.php?image=<?= rawurlencode($file) ?>#course-image">Use for new course</a><a class="button" href="article-edit.php?image=<?= rawurlencode($file) ?>#article-image">Use for new article</a></div></figcaption></figure><?php endforeach; ?></div>
<p class="help">This view lists attached uploads. Replaced images are retained privately for recovery. An upload becomes public only when used by published content.</p>
<?php adminPagination('media.php',$page,$pages,['q'=>$search]);adminFoot(); ?>
