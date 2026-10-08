<?php
require __DIR__.'/../includes/cms-auth.php';require __DIR__.'/../includes/course-store.php';require __DIR__.'/_layout.php';cmsRequireUser();
$id=filter_var($_GET['id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
try{$row=$id?courseRow($id):null;if(!$row){http_response_code(404);exit('Course not found.');}$course=courseRecord($row);}catch(Throwable $e){http_response_code(503);exit('Preview is temporarily unavailable.');}
adminHead('Course preview'); ?>
<p class="notice preview-notice">Private preview · <?= escapeHtml(ucfirst($row['status'])) ?>. Upcoming session values are included. <a href="course-edit.php?id=<?= (int)$id ?>">Return to editor</a></p>
<article class="preview"><p><?= escapeHtml($course['category']) ?></p><h2 lang="<?= escapeHtml($course['title_language']) ?>"><?= escapeHtml($course['name']) ?></h2>
<?php if(!empty($course['subtitle'])): ?><p class="course-subtitle" lang="<?= escapeHtml($course['title_language']) ?>"><?= escapeHtml($course['subtitle']) ?></p><?php endif; ?>
<?php if($course['image']): ?><img class="editor-image" src="../<?= escapeHtml($course['image']) ?>" alt="<?= escapeHtml($course['image_alt']) ?>"><?php endif; ?>
<?php if($course['description']): ?><p><?= escapeHtml($course['description']) ?></p><?php endif; ?>
<dl><?php foreach(['duration'=>'Duration','language'=>'Language','format'=>'Format','level'=>'Level','next_session'=>'Next session'] as $key=>$label):if(!empty($course[$key])): ?><dt><?= $label ?></dt><dd><?= escapeHtml($course[$key]) ?></dd><?php endif;endforeach; ?></dl>
<?php foreach(['outcomes'=>'Learning outcomes','includes'=>'Includes','certificate'=>'Certificate','requirements'=>'Requirements','exam'=>'Exam and evaluation'] as $key=>$label):if($course[$key]): ?><h3><?= $label ?></h3><ul><?php foreach($course[$key] as $text): ?><li><?= escapeHtml($text) ?></li><?php endforeach; ?></ul><?php endif;endforeach; ?></article><?php adminFoot(); ?>
