<?php
require __DIR__.'/../includes/cms-auth.php';require __DIR__.'/../includes/course-store.php';require __DIR__.'/_layout.php';require __DIR__.'/_fields.php';cmsRequireUser();
$rows=[];$error='';$filter=filter_var($_GET['course_id']??0,FILTER_VALIDATE_INT,['options'=>['min_range'=>0]])?:0;
$upcomingOnly=($_GET['upcoming']??'')==='1';
$search=is_string($_GET['q']??null)?trim($_GET['q']):'';
$status=is_string($_GET['status']??null)?$_GET['status']:'';
$page=max(1,(int)(filter_var($_GET['page']??1,FILTER_VALIDATE_INT)?:1));$total=0;$pages=1;
try{
    $where=[];$values=[];
    if($upcomingOnly){$where[]="s.status='upcoming' AND c.status='published' AND s.start_date>=?";$values[]=courseToday();}
    if($filter){$where[]='s.course_id=?';$values[]=$filter;}
    if($status!==''){$where[]='s.status=?';$values[]=$status;}
    if($search!==''){$where[]="LOCATE(?,JSON_UNQUOTE(JSON_EXTRACT(c.data,'$.name')))>0";$values[]=$search;}
    $from=' FROM cms_course_sessions s JOIN cms_courses c ON c.id=s.course_id'.($where?' WHERE '.implode(' AND ',$where):'');
    $q=cmsDatabase()->prepare('SELECT COUNT(*)'.$from);$q->execute($values);$total=(int)$q->fetchColumn();
    $pages=max(1,(int)ceil($total/25));$page=min($page,$pages);$offset=($page-1)*25;
    $q=cmsDatabase()->prepare('SELECT s.id,s.course_id,s.start_date,s.duration,s.language,s.status,c.slug,c.data,c.status AS course_status'.$from.' ORDER BY start_date DESC,s.id DESC LIMIT 25 OFFSET '.$offset);$q->execute($values);$rows=$q->fetchAll();
}catch(Throwable $e){$error='Training sessions are temporarily unavailable.';http_response_code(503);}
adminHead('Training Sessions');adminNotice($error); ?>
<div class="page-actions"><p><?= $total ?> sessions. Courses remain in the catalogue after training ends.</p><a class="button primary" href="session-edit.php<?= $filter?'?course_id='.$filter:'' ?>">Add training session</a></div>
<form method="get" class="filters"><?php if($upcomingOnly): ?><input type="hidden" name="upcoming" value="1"><?php endif; ?><input type="hidden" name="course_id" value="<?= $filter ?>"><?php adminInput('q','Search course titles',$search);adminSelect('status','Status',[''=>'All statuses','draft'=>'Draft','upcoming'=>'Upcoming','completed'=>'Completed'],$status); ?><button>Search</button><a href="sessions.php">Clear</a></form>
<?php if($filter): ?><p><a href="sessions.php">View all sessions</a></p><?php endif; ?>
<ul class="article-list"><?php foreach($rows as $row):$data=json_decode($row['data'],true); ?><li><div><h2><a href="session-edit.php?id=<?= (int)$row['id'] ?>"><?= escapeHtml($data['name']) ?></a></h2><p><?= escapeHtml($row['start_date']?adminDate($row['start_date'],false):'No date entered') ?> · <?php adminBadge($row['status']); ?><?php if($row['status']==='upcoming'&&$row['start_date']<courseToday()): ?> · Date passed; no longer shown as upcoming<?php endif; ?><?php if($row['course_status']==='draft'): ?> · Course is a draft<?php endif; ?></p><p class="help"><?php if($row['duration']): ?>Duration: <?= escapeHtml($row['duration']) ?>. <?php endif; ?><?php if($row['language']): ?>Language: <?= escapeHtml($row['language']) ?>.<?php endif; ?></p></div><div class="actions"><a href="session-edit.php?id=<?= (int)$row['id'] ?>">Edit session</a><a href="course-preview.php?id=<?= (int)$row['course_id'] ?>">View course</a></div></li><?php endforeach; ?></ul>
<?php adminPagination('sessions.php',$page,$pages,['q'=>$search,'status'=>$status,'course_id'=>$filter,'upcoming'=>$upcomingOnly?'1':'']); ?>
<?php if(!$error&&!$rows): ?><div class="empty-state"><h2>No sessions in this view</h2><p>Try a different filter or schedule an existing course.</p><a class="button" href="session-edit.php">Add training session</a></div><?php endif;adminFoot(); ?>
