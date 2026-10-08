<?php
require __DIR__.'/../includes/cms-auth.php';require __DIR__.'/../includes/course-store.php';require __DIR__.'/_layout.php';require __DIR__.'/_fields.php';cmsRequireUser();
$id=filter_var($_GET['id']??0,FILTER_VALIDATE_INT,['options'=>['min_range'=>0]]);if($id===false){http_response_code(404);exit('Session not found.');}
$row=['course_id'=>filter_var($_GET['course_id']??0,FILTER_VALIDATE_INT)?:0,'start_date'=>'','duration'=>'','language'=>'','status'=>'draft','version'=>0];$choices=[];$error='';$duplicate=false;
try{
    courseStoreReady();$pdo=cmsDatabase();foreach($pdo->query('SELECT id,data FROM cms_courses ORDER BY sort_order,id') as $c)$choices[$c['id']]=json_decode($c['data'],true)['name'];
    if($id){$q=$pdo->prepare('SELECT id,course_id,start_date,duration,language,status,version FROM cms_course_sessions WHERE id=?');$q->execute([$id]);$row=$q->fetch();if(!$row){http_response_code(404);exit('Session not found.');}}
    if($_SERVER['REQUEST_METHOD']==='POST'){
        cmsCheckCsrf();$previousStatus=$row['status'];$row['course_id']=filter_var($_POST['course_id']??$row['course_id'],FILTER_VALIDATE_INT);
        if(!isset($choices[$row['course_id']]))throw new InvalidArgumentException('Choose an existing course.');
        foreach(['start_date'=>10,'duration'=>250,'language'=>100,'status'=>15] as $key=>$limit)if(array_key_exists($key,$_POST)) $row[$key]=cmsText($_POST[$key],$limit);
        if(!in_array($row['status'],['draft','upcoming','completed'],true))throw new InvalidArgumentException('Choose a valid status.');
        $date=$row['start_date'];$parsed=DateTimeImmutable::createFromFormat('!Y-m-d',$date);
        if($date!==''&&(!$parsed||$parsed->format('Y-m-d')!==$date||$date<'1000-01-01'))throw new InvalidArgumentException('Enter a valid date.');
        if($row['status']!=='draft'&&$date==='')throw new InvalidArgumentException('A verified date is required for upcoming or completed training.');
        if ($date!=='') {
            $q=$pdo->prepare('SELECT id FROM cms_course_sessions WHERE course_id=? AND start_date=? AND id<>? LIMIT 1');
            $q->execute([$row['course_id'],$date,$id]); $duplicate=(bool)$q->fetchColumn();
            if ($duplicate && ($_POST['confirm_duplicate']??'')!=='1') throw new InvalidArgumentException('Another session already uses this course and date. Confirm below if this is intentional.');
        }
        $values=[$row['course_id'],$date?:null,$row['duration']?:null,$row['language']?:null,$row['status']];
        if($id){$q=$pdo->prepare('UPDATE cms_course_sessions SET course_id=?,start_date=?,duration=?,language=?,status=?,version=version+1,updated_at=UTC_TIMESTAMP() WHERE id=? AND version=?');$q->execute([...$values,$id,filter_var($_POST['version']??null,FILTER_VALIDATE_INT)]);if(!$q->rowCount())throw new InvalidArgumentException('This session changed in another window. Reload before saving.');}
        else{$q=$pdo->prepare('INSERT INTO cms_course_sessions(course_id,start_date,duration,language,status) VALUES (?,?,?,?,?)');$q->execute($values);$id=(int)$pdo->lastInsertId();}
        adminSavedNotice('Training session',$row['status'],$previousStatus);
        header('Location: session-edit.php?saved=1&id='.$id,true,303);exit;
    }
}catch(InvalidArgumentException $e){$error=$e->getMessage();http_response_code(422);}catch(Throwable $e){$error='Training sessions are temporarily unavailable. Changes were not saved.';http_response_code(503);}
adminHead($id?'Edit training session':'Add training session');adminNotice($error); ?>
<p><?php adminBadge($row['status']); ?></p><p>Schedule an existing course. Only upcoming sessions dated today or later appear publicly, and only when the course is published.</p>
<form method="post" data-content-editor><input type="hidden" name="csrf_token" value="<?= escapeHtml(cmsToken()) ?>"><input type="hidden" name="version" value="<?= (int)$row['version'] ?>">
<fieldset><legend>Course and schedule</legend><?php adminSelect('course_id','Course',[''=>'Choose a course']+$choices,$row['course_id']); ?><div class="form-columns"><?php adminInput('start_date','Verified start date',$row['start_date'],'date');adminSelect('status','Status',['draft'=>'Draft','upcoming'=>'Upcoming','completed'=>'Completed'],$row['status']);adminInput('duration','Session duration (optional)',$row['duration']);adminSelect('language','Session language',[''=>'Use course language','French'=>'French','English'=>'English','Arabic'=>'Arabic','English / Arabic'=>'English / Arabic','Arabic / English / French'=>'Arabic / English / French']+($row['language']?[$row['language']=>$row['language']]:[]),$row['language']); ?></div></fieldset>
<p class="help">Duration and language fall back to course information when blank. Past dates remain stored but are not advertised as upcoming.</p>
<?php if($duplicate): ?><label class="check"><input type="checkbox" name="confirm_duplicate" value="1"> I confirm that another session for this course on this date is intentional.</label><?php endif; ?>
<div class="actions editor-actions"><button class="primary">Save session</button><a href="sessions.php">Return to sessions</a></div></form>
<?php if($row['course_id']): ?><p><a href="course-preview.php?id=<?= (int)$row['course_id'] ?>">View associated course</a></p><?php endif; adminFoot(); ?>
