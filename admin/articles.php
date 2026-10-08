<?php
require __DIR__.'/../includes/cms-auth.php';
require __DIR__.'/_layout.php'; require __DIR__.'/_fields.php'; cmsRequireUser();
$search=is_string($_GET['q']??null)?trim($_GET['q']):'';
$status=is_string($_GET['status']??null)?$_GET['status']:'';
$page=max(1,(int)(filter_var($_GET['page']??1,FILTER_VALIDATE_INT)?:1));
$articles=[]; $error=''; $total=0; $pages=1;
try {
    $where=[]; $values=[];
    if($status!==''){$where[]='status=?';$values[]=$status;}
    if($search!==''){$where[]='(LOCATE(?,title)>0 OR LOCATE(?,slug)>0)';$values[]=$search;$values[]=$search;}
    $condition=$where?' WHERE '.implode(' AND ',$where):'';
    $q=cmsDatabase()->prepare('SELECT COUNT(*) FROM cms_articles'.$condition);$q->execute($values);$total=(int)$q->fetchColumn();
    $pages=max(1,(int)ceil($total/25));$page=min($page,$pages);$offset=($page-1)*25;
    $q=cmsDatabase()->prepare('SELECT id,title,slug,status,published_at,updated_at FROM cms_articles'.$condition.' ORDER BY updated_at DESC,id DESC LIMIT 25 OFFSET '.$offset);
    $q->execute($values);$articles=$q->fetchAll();
}catch(Throwable $e){$error='Articles are temporarily unavailable. Please try again later.';http_response_code(503);}
adminHead('Articles');adminNotice($error);
?>
<div class="page-actions"><p><?= $total ?> articles in this view. Drafts stay private until published.</p><a class="button primary" href="article-edit.php">New article</a></div>
<form class="filters" method="get"><?php adminInput('q','Search articles',$search);adminSelect('status','Status',[''=>'All statuses','draft'=>'Draft','published'=>'Published'],$status); ?><button>Search</button><a href="articles.php">Clear</a></form>
<?php if(!$error&&!$articles): ?><div class="empty-state"><h2>No articles in this view</h2><p>Try a different search or start a new draft. Nothing appears on the website until you publish it.</p><a class="button" href="article-edit.php">Create a draft</a></div><?php endif; ?>
<ul class="article-list"><?php foreach($articles as $article): ?><li><div><h2><a href="article-edit.php?id=<?= (int)$article['id'] ?>"><?= escapeHtml($article['title']) ?></a></h2><p><?php adminBadge($article['status']); ?> Updated <?= escapeHtml(adminDate($article['updated_at'])) ?></p><?php if($article['published_at']): ?><p class="help">First published <?= escapeHtml(adminDate($article['published_at'])) ?></p><?php endif; ?></div><div class="actions"><a href="article-edit.php?id=<?= (int)$article['id'] ?>">Edit</a><a href="preview.php?id=<?= (int)$article['id'] ?>">Preview saved version</a><?php if($article['status']==='published'): ?><a href="../article.php?slug=<?= rawurlencode($article['slug']) ?>" target="_blank" rel="noopener">View on website</a><?php endif; ?></div></li><?php endforeach; ?></ul>
<?php adminPagination('articles.php',$page,$pages,['q'=>$search,'status'=>$status]);adminFoot(); ?>
