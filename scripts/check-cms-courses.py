"""Course CMS integration QA on a uniquely named disposable database.
Uses the configured MySQL server, but never edits the owner's tables/accounts.
Requires CREATE DATABASE permission; optional --browser uses CDP localhost:9225.
"""
import os,json,secrets,subprocess,time,sys,re,base64
from pathlib import Path
import requests
from bs4 import BeautifulSoup

ROOT=Path(__file__).resolve().parents[1];QA=ROOT/'storage/cms-qa'
PHP=r'C:\wamp64\bin\php\php8.3.14\php.exe'
name='cms_courses_qa_'+secrets.token_hex(6)
config=QA/'courses-config.php';env=dict(os.environ,SALAAM_CMS_CONFIG=str(config))
password=secrets.token_urlsafe(24);checks=[];uploaded=set();server=None;log=None
def php(code,isolated=True):
    return subprocess.run([PHP,'-d','xdebug.mode=off','-r',code],cwd=ROOT,env=env if isolated else os.environ,capture_output=True,text=True,check=True).stdout
def setup(command,data=None):
    r=subprocess.run([PHP,'-d','xdebug.mode=off','scripts/cms-setup.php',command],cwd=ROOT,env=env,input=json.dumps(data) if data else '',capture_output=True,text=True)
    assert r.returncode==0,r.stderr
    return r.stdout
def check(value,label):
    assert value,label
    checks.append(label)
QA.mkdir(parents=True,exist_ok=True)
php("require 'includes/cms.php';cmsDatabase()->exec('CREATE DATABASE "+name+" CHARACTER SET utf8mb4');$c=require 'config.local.php';$c['db']['database']='"+name+"';file_put_contents('storage/cms-qa/courses-config.php','<?php return '.var_export($c,true).';');",False)
try:
    # Restore the protected pre-change snapshot only into this disposable database.
    # Never rerun the historical course import or migrations.
    restored=json.loads(php("require 'includes/cms.php';$b=json_decode(file_get_contents('storage/cms-qa/phase2-backup.json'),true,512,JSON_THROW_ON_ERROR);$p=cmsDatabase();foreach($b['tables'] as $name=>$t){$p->exec($t['schema']);foreach($t['rows'] as $r){$q=$p->prepare('INSERT INTO `'.$name.'` (`'.implode('`,`',array_keys($r)).'`) VALUES ('.implode(',',array_fill(0,count($r),'?')).')');$q->execute(array_values($r));}}$counts=[];foreach($b['tables'] as $name=>$t){$rows=$p->query('SELECT * FROM `'.$name.'`')->fetchAll();if($rows!==$t['rows'])throw new RuntimeException('Restore differs');$counts[$name]=count($rows);}echo json_encode($counts);"))
    check(restored['cms_courses']==24 and restored['cms_course_sessions']==8,'backup restored exactly in isolated database; no migration rerun')
    setup('create-admin',{'email':'qa@example.invalid','display_name':'Temporary QA','password':password})
    (QA/'courses-router.php').write_text("<?php $p=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);if(preg_match('~^/(storage|database|includes|scripts)/|^/config~',$p)){http_response_code(403);exit;}return false;",encoding='utf-8')
    log=(QA/'courses-http.log').open('w')
    server=subprocess.Popen([PHP,'-d','xdebug.mode=off','-d','opcache.enable=0','-d','session.save_path='+str(QA),'-d','upload_tmp_dir='+str(QA),'-d','upload_max_filesize=6M','-d','post_max_size=8M','-S','127.0.0.1:8095',str(QA/'courses-router.php')],cwd=ROOT,env=env,stdout=log,stderr=log)
    time.sleep(1);base='http://127.0.0.1:8095/'
    s=requests.Session();s.trust_env=False;anon=requests.Session();anon.trust_env=False
    def get(path,client=s):return client.get(base+path,timeout=20)
    def soup(r):return BeautifulSoup(r.content.decode('utf-8'),'html.parser')
    def form(path):
        r=get(path);assert r.status_code==200,(path,r.status_code)
        f=soup(r).select_one('main form[method=post]');data={}
        for el in f.select('input[name],textarea[name],select[name]'):
            if el.get('type') in ['file','submit','button']:continue
            if el.get('type')=='checkbox' and not el.has_attr('checked'):continue
            if el.name=='textarea':value=el.text
            elif el.name=='select':value=(el.select_one('option[selected]') or el.select_one('option')).get('value','')
            else:value=el.get('value','on' if el.get('type')=='checkbox' else '')
            data[el['name']]=value
        return data
    def save(path,data,files=None):return s.post(base+path,data=data,files=files,allow_redirects=False,timeout=20)
    def rows():return json.loads(php("require 'includes/cms.php';echo json_encode(cmsDatabase()->query('SELECT * FROM cms_courses ORDER BY id')->fetchAll());"))
    for route in ['index.php','courses.php','course-edit.php','course-preview.php?id=1','sessions.php','session-edit.php','media.php','references.php','articles.php']:
        check(anon.get(base+'admin/'+route,allow_redirects=False).status_code==303,'route guard '+route)
    data=form('admin/login.php');data.update(email='qa@example.invalid',password=password)
    check(save('admin/login.php',data).status_code==303,'admin login')
    dashboard=soup(get('admin/index.php'))
    check([n.text for n in dashboard.select('.overview-item strong')]==['24','8','0','0','2','20'],'real dashboard counts')
    check(len(soup(get('admin/courses.php?q=POWER')).select('.article-list li'))==1,'course search')
    check(len(soup(get('admin/courses.php?category=safety-security')).select('.article-list li'))==1,'category filter')
    check(len(soup(get('admin/courses.php?status=draft')).select('.article-list li'))==0,'status filter')
    for route in ['course-edit.php','session-edit.php']:
        check(save('admin/'+route,{}).status_code==403,'CSRF '+route)
    power=next(r for r in rows() if r['slug']=='power-bi');id=power['id'];path='admin/course-edit.php?id='+str(id)
    original=form(path);changed=dict(original,name="QA Temporary POWER BI ' OR 1=1 --",description='<script>test</script> Temporary QA description',action='publish')
    check(save(path,changed).status_code==303,'edit published course')
    public=get('course.php?slug=power-bi',anon);doc=soup(public)
    check(changed['name'] in doc.h1.text and changed['name'] in doc.title.text and 'Temporary QA description' in doc.select_one('meta[name=description]')['content'],'public title and SEO synchronized')
    check(not doc.select('main script') and '<script>test</script>' in doc.select_one('main').get_text(),'course XSS escaped')
    check('Course' in ''.join(x.text for x in doc.select('script[type="application/ld+json"]')),'Course JSON-LD retained')
    check(changed['name'] in get('courses.php',anon).text.replace('&#039;',"'") and 'QA Temporary POWER BI' in get('category.php?slug=information-technology',anon).text,'catalogue and category synchronization')
    check(save(path,dict(original,action='publish')).status_code==422,'stale course edit blocked')
    fresh=form(path);fresh.update({k:v for k,v in original.items() if k not in ['csrf_token','version']});fresh['action']='publish';check(save(path,fresh).status_code==303,'course values restored')
    restored_power=json.loads(next(r for r in rows() if r['id']==id)['data'])
    original_power=json.loads(power['data'])
    check(all(restored_power[k]==v for k,v in original_power.items() ),'existing course original values restored: '+repr({k:(v,restored_power.get(k)) for k,v in original_power.items() if restored_power.get(k)!=v}))
    saved_row=next(r for r in rows() if r['id']==id)
    minimal={'csrf_token':form(path)['csrf_token'],'version':str(saved_row['version']),'action':'publish','description':restored_power['description'],'price_source':'999','price_source_currency':'DJF','brand_image':'bad','source_urls':'bad'}
    check(save(path,minimal).status_code==303,'partial update permitted')
    after=json.loads(next(r for r in rows() if r['id']==id)['data'])
    check(after==restored_power,'omitted fields and all hidden metadata preserved; mass assignment ignored')
    check(save(path,dict(form(path),action='preview',name='Must not save')).status_code==422,'preview POST rejected without mutating published course')
    before_preview=next(r for r in rows() if r['id']==id)
    get('admin/course-preview.php?id='+str(id))
    check(next(r for r in rows() if r['id']==id)==before_preview,'GET saved preview has no writes')
    sessions=json.loads(php("require 'includes/cms.php';echo json_encode(cmsDatabase()->query('SELECT * FROM cms_course_sessions ORDER BY id')->fetchAll());"))
    sid=next(x['id'] for x in sessions if x['course_id']==id);sp='admin/session-edit.php?id='+str(sid);before=form(sp)
    check(save(sp,dict(before,price_djf='70000',duration='Temporary QA duration')).status_code==303,'non-price session edit accepts ignored retired input')
    after_sessions=json.loads(php("require 'includes/cms.php';echo json_encode(cmsDatabase()->query('SELECT * FROM cms_course_sessions ORDER BY id')->fetchAll());"))
    check(next(x for x in after_sessions if x['id']==sid)['price_djf']==next(x for x in sessions if x['id']==sid)['price_djf'],'legacy session value preserved')
    check(not soup(get('course.php?slug=power-bi',anon)).select('[data-course-price],.course-price,#display-currency'),'public pricing absent')
    fresh=form(sp);fresh['duration']=before['duration'];check(save(sp,fresh).status_code==303,'session duration restored')
    usd=next(r for r in rows() if r['slug']=='sage-100-accounting');up='admin/course-edit.php?id='+str(usd['id'])
    check(save(up,dict(form(up),description='Temporary USD preservation test',action='publish')).status_code==303,'edit historical USD course')
    usd_after=json.loads(next(r for r in rows() if r['id']==usd['id'])['data'])
    check(usd_after['price_source']==160 and usd_after['price_source_currency']=='USD','historical USD source unchanged by unrelated edit')
    check(save(up,dict(form(up),description=json.loads(usd['data'])['description'] or '',action='publish')).status_code==303,'historical course description restored')
    check(save(sp,dict(form(sp),start_date='2026-02-30')).status_code==422,'invalid date rejected')
    check('price_djf' not in form(sp),'session has no pricing control')
    check(save(sp,dict(form(sp),course_id='999999')).status_code==422,'unknown course rejected')
    new=form('admin/course-edit.php');new.update(name='QA Temporary Français Course',category_slug='information-technology',title_language='fr',description='Temporary integration test only.',image='assets/images/courses/power-bi.png',image_alt='Temporary QA image',action='draft')
    check(save('admin/course-edit.php',dict(new,category_slug='bad')).status_code==422,'invalid category rejected')
    check(save('admin/course-edit.php',dict(new,image='../../config.local.php')).status_code==422,'image traversal rejected')
    check(save('admin/course-edit.php',new,{'upload':('bad.php.jpg',b'<?php echo 1;?>','image/jpeg')}).status_code==422,'executable upload rejected')
    img=ROOT/'assets/images/courses/power-bi.png'
    r=save('admin/course-edit.php',new,{'upload':('../../test.php.png',img.read_bytes(),'image/png')});check(r.status_code==303,'draft creation with safe upload')
    draftid=int(re.search(r'id=(\d+)',r.headers['Location'])[1]);draft=next(r for r in rows() if r['id']==draftid);slug=draft['slug'];uploaded.add(draft['featured_image']);dp='admin/course-edit.php?id='+str(draftid)
    repeat=form(dp);repeat.update({'outcomes[0]':'First verified test item','outcomes[1]':'Second test item','action':'draft'})
    check(save(dp,repeat).status_code==303,'ordered repeatable items saved')
    check(json.loads(next(r for r in rows() if r['id']==draftid)['data'])['outcomes']==['First verified test item','Second test item'],'repeatable order preserved')
    check(save(dp,{'csrf_token':form(dp)['csrf_token'],'version':form(dp)['version'],'action':'draft','lists_present[outcomes]':'1'}).status_code==303,'intentional whole-list clear')
    check(json.loads(next(r for r in rows() if r['id']==draftid)['data'])['outcomes']==[],'intentional empty list stored')
    invalid=dict(form(dp),action='draft');invalid['outcomes']='invalid scalar'
    check(save(dp,invalid).status_code==422,'repeatable server validation')
    check(get('course.php?slug='+slug,anon).status_code==404,'draft course public 404')
    check(slug not in get('sitemap.php',anon).text,'draft omitted sitemap')
    check(get('media.php?file='+draft['featured_image'],anon).status_code==404,'draft uploaded image private')
    r=get('admin/course-preview.php?id='+str(draftid));check(r.status_code==200 and 'noindex, nofollow' in r.headers['X-Robots-Tag'],'authenticated course preview')
    check(save(dp,dict(form(dp),action='publish',featured='on')).status_code==303,'publish new course')
    check(get('course.php?slug='+slug,anon).status_code==200 and slug in get('index.php',anon).text and slug in get('sitemap.php',anon).text,'published course Home and sitemap automatic')
    check(get('media.php?file='+draft['featured_image'],anon).status_code==200,'published uploaded image public')
    check(soup(get('course.php?slug='+slug,anon)).select_one('meta[property="og:image"]')['content'].endswith(draft['featured_image']),'uploaded course social image')
    check(save(dp,dict(form(dp),slug='changed',action='publish')).status_code==422,'published URL stable')
    published_delete={'csrf_token':form(dp)['csrf_token'],'id':str(draftid),'version':form(dp)['version'],'confirm_slug':slug}
    check(save('admin/course-delete.php',dict(published_delete,version='0')).status_code==422,'published course deletion rejects invalid version')
    check(save('admin/course-edit.php',dict(new,slug=slug)).status_code==422,'unique course slug enforced')
    ns=form('admin/session-edit.php');ns.update(course_id=str(draftid),start_date='2099-01-01',duration='2 days',language='English',status='draft');r=save('admin/session-edit.php',ns);check(r.status_code==303,'create draft session without pricing')
    newsid=int(re.search(r'id=(\d+)',r.headers['Location'])[1]);nsp='admin/session-edit.php?id='+str(newsid)
    check('2099' not in get('course.php?slug='+slug,anon).text,'draft session private')
    check(save(nsp,dict(form(nsp),status='upcoming')).status_code==303,'mark session upcoming')
    check('2099' in get('course.php?slug='+slug,anon).text,'upcoming session visible')
    duplicate=dict(ns,status='upcoming')
    check(save('admin/session-edit.php',duplicate).status_code==422,'same course/date duplicate warning')
    r=save('admin/session-edit.php',dict(duplicate,confirm_duplicate='1'));check(r.status_code==303,'intentional duplicate allowed after confirmation')
    duplicateid=int(re.search(r'id=(\d+)',r.headers['Location'])[1])
    php("require 'includes/cms.php';cmsDatabase()->exec('DELETE FROM cms_course_sessions WHERE id="+str(duplicateid)+"');")
    stale_session=form(nsp)
    check(save(nsp,dict(form(nsp),status='completed')).status_code==303,'complete session')
    check(save(nsp,dict(stale_session,status='draft')).status_code==422,'stale session edit blocked')
    check(save(nsp,dict(form(nsp),start_date='2000-01-01',status='upcoming')).status_code==303,'past session can remain marked upcoming')
    check('2000' not in get('course.php?slug='+slug,anon).text and form(nsp)['status']=='upcoming','past session excluded without automatic database status changes')
    check(save(nsp,dict(form(nsp),start_date='2099-01-01',status='completed')).status_code==303,'temporary session completed again')
    check(get('course.php?slug='+slug,anon).status_code==200 and '2099' not in get('course.php?slug='+slug,anon).text,'completed session hidden, permanent course survives')
    # Reuse an existing course upload without duplicating or bypassing media validation.
    media_value='media.php?file='+draft['featured_image']
    af=form('admin/article-edit.php?image='+draft['featured_image'])
    check(af['existing_image']==media_value and form('admin/course-edit.php?image='+draft['featured_image'])['image']==media_value,'Media links preselect shared image in both editors')
    af.update(title='QA Shared media',excerpt='QA shared image summary.',image_alt='QA shared image',action='draft');af['blocks[0][text]']='QA shared content.'
    check(save('admin/article-edit.php',dict(af,existing_image='../../config.local.php')).status_code==422,'article media traversal rejected')
    r=save('admin/article-edit.php',af);check(r.status_code==303,'article reuses course image')
    media_aid=int(re.search(r'id=(\d+)',r.headers['Location'])[1]);media_ap='admin/article-edit.php?id='+str(media_aid)
    check(len(soup(get('admin/media.php?q=QA')).select('.media-grid figure'))==1,'Media deduplicates shared image')
    check('QA Shared media' in get('admin/media.php?q=Shared').text,'Media title search')
    check('No attached uploads' in get('admin/media.php?q=nonexistent-qa').text,'Media empty search guidance')
    if '--browser' in sys.argv:
        import websocket
        target=s.get('http://127.0.0.1:9225/json').json()[0];ws=websocket.create_connection(target['webSocketDebuggerUrl'],origin='http://127.0.0.1:9225',timeout=20);seq=0;errors=[];loaded=set()
        def call(method,params=None):
            global seq
            seq+=1;request_id=seq;ws.send(json.dumps({'id':request_id,'method':method,'params':params or {}}))
            while True:
                message=json.loads(ws.recv())
                if message.get('method')=='Runtime.exceptionThrown':errors.append(message)
                if message.get('method')=='Page.lifecycleEvent' and message['params']['name']=='load':loaded.add(message['params']['loaderId'])
                if message.get('method')=='Page.javascriptDialogOpening':
                    seq+=1;ws.send(json.dumps({'id':seq,'method':'Page.handleJavaScriptDialog','params':{'accept':True}}))
                if message.get('id')==request_id:
                    assert 'error' not in message,message
                    return message.get('result',{})
        def evaluate(code):return call('Runtime.evaluate',{'expression':code,'returnByValue':True})['result'].get('value')
        call('Page.enable');call('Page.setLifecycleEventsEnabled',{'enabled':True});call('Page.navigate',{'url':'about:blank'});time.sleep(.5);call('Runtime.enable');errors.clear();call('Network.enable');call('Network.setCookie',{'name':'SALAAMADMIN','value':s.cookies.get('SALAAMADMIN'),'url':base,'httpOnly':True,'sameSite':'Strict'})
        header_comparison=[]
        for width,height in [(1920,1080),(1440,900),(1366,768),(1024,768),(768,1024),(390,844),(375,812)]:
            call('Emulation.setDeviceMetricsOverride',{'width':width,'height':height,'deviceScaleFactor':1,'mobile':False})
            for route in ['admin/login.php','admin/index.php','admin/courses.php',dp,'admin/sessions.php',nsp,'admin/course-preview.php?id='+str(draftid),'admin/articles.php','admin/article-edit.php',media_ap,'admin/preview.php?id='+str(media_aid),'admin/media.php','admin/references.php']:
                # Clear only the test's unsaved marker before a deliberate navigation.
                # A submitted synthetic event does not submit the form or write content.
                evaluate('[...document.querySelectorAll("[data-content-editor]")].forEach(f=>f.dispatchEvent(new Event("submit")))')
                navigation=call('Page.navigate',{'url':base+route})
                for _ in range(100):
                    evaluate('document.readyState')
                    if navigation.get('loaderId') in loaded:break
                    time.sleep(.1)
                check(navigation.get('loaderId') in loaded,'navigation finished '+str(width)+' '+route)
                check(evaluate('document.documentElement.scrollWidth<=innerWidth+1 && document.querySelectorAll("h1").length===1 && [...document.images].every(i=>i.hidden||!i.complete||i.naturalWidth>0) && [...document.querySelectorAll("input:not([type=hidden]),select,textarea")].every(i=>i.labels.length)'),'browser '+str(width)+' '+route)
                check(evaluate('[...document.styleSheets].some(s=>s.href&&s.href.includes("admin.css?v=")&&s.cssRules.length>0) && [...document.styleSheets].some(s=>s.href&&s.href.includes("style.css?v=")&&s.cssRules.length>0)'), 'loaded versioned shared/admin CSS '+str(width)+' '+route)
                if route==media_ap:
                    before_blocks=evaluate('document.querySelector("#content-blocks").children.length')
                    evaluate('document.querySelector("#add-block").click()')
                    check(evaluate('document.querySelector("#content-blocks").children.length')==before_blocks+1,'article add block '+str(width)+' '+str(evaluate('JSON.stringify({url:location.href,count:document.querySelector("#content-blocks").children.length,ready:document.readyState})')))
                    evaluate('[...document.querySelector("#content-blocks").lastElementChild.querySelectorAll("button")].find(b=>b.textContent==="Move up").click()')
                    check(evaluate('document.querySelector("#content-blocks").firstElementChild.querySelector("textarea").value')=='','article move block '+str(width))
                    evaluate('[...document.querySelector("#content-blocks").firstElementChild.querySelectorAll("button")].find(b=>b.textContent==="Remove block").click()')
                    check(evaluate('document.querySelector("#content-blocks").children.length')==before_blocks,'article remove block '+str(width))
                    check(evaluate('document.querySelector(".unsaved-note").textContent')=='You have unsaved changes.','unsaved change feedback '+str(width))
                    if width in [1366,390]:
                        evaluate('scrollTo(0,0)')
                        (QA/('article-editor-'+str(width)+'.png')).write_bytes(base64.b64decode(call('Page.captureScreenshot',{'format':'png'})['data']))
                if route==dp:
                    before_items=evaluate('document.querySelector(".repeatable-items").children.length')
                    evaluate('document.querySelector("[data-add-item]").click()')
                    check(evaluate('document.querySelector(".repeatable-items").children.length')==before_items+1,'repeatable add '+str(width))
                    evaluate('document.querySelector(".repeatable-items").lastElementChild.querySelector("[data-remove-item]").click()')
                    check(evaluate('document.querySelector(".repeatable-items").children.length')==before_items,'repeatable remove '+str(width))
                    if width in [1366,390]:
                        evaluate('document.querySelector(".repeatable").scrollIntoView()')
                        (QA/('repeatable-'+str(width)+'.png')).write_bytes(base64.b64decode(call('Page.captureScreenshot',{'format':'png'})['data']))
                        evaluate('scrollTo(0,0)')
                if route in ['admin/courses.php',dp] and width in [1366,390]:
                    (QA/('ui-'+('courses' if route=='admin/courses.php' else 'editor')+'-'+str(width)+'.png')).write_bytes(base64.b64decode(call('Page.captureScreenshot',{'format':'png','captureBeyondViewport':False})['data']))
            if True:
                call('Page.navigate',{'url':base+'admin/index.php'});time.sleep(.4)
                (QA/('dashboard-'+str(width)+'.png')).write_bytes(base64.b64decode(call('Page.captureScreenshot',{'format':'png','captureBeyondViewport':False})['data']))
                check(evaluate('document.querySelector(".site-logo img").getBoundingClientRect().width<=240 && document.querySelector(".site-logo img").getBoundingClientRect().height<=72'),'bounded logo '+str(width))
                if width>=1366:
                    check(evaluate('[...document.querySelectorAll("h2")].find(h=>h.textContent==="Recently updated").getBoundingClientRect().bottom<innerHeight && document.querySelector(".recent-list li").getBoundingClientRect().bottom<innerHeight'),'useful dashboard above fold '+str(width))
                metric='JSON.stringify({logoWidth:document.querySelector(".site-logo img").getBoundingClientRect().width,logoHeight:document.querySelector(".site-logo img").getBoundingClientRect().height,headerHeight:document.querySelector(".site-header").getBoundingClientRect().height,left:document.querySelector(".header-content").getBoundingClientRect().left,font:getComputedStyle(document.querySelector(".site-header")).fontFamily,background:getComputedStyle(document.querySelector(".site-header")).backgroundColor})'
                admin_metric=json.loads(evaluate(metric))
                call('Page.navigate',{'url':base+'index.php'});time.sleep(1)
                public_metric=json.loads(evaluate(metric))
                header_comparison.append({'width':width,'admin':admin_metric,'public':public_metric})
                (QA/('public-header-'+str(width)+'.png')).write_bytes(base64.b64decode(call('Page.captureScreenshot',{'format':'png','captureBeyondViewport':False})['data']))
                check(all(abs(admin_metric[k]-public_metric[k])<2 for k in ['logoWidth','logoHeight','headerHeight']) and abs(admin_metric['left']-public_metric['left'])<=8,'public/admin header proportions '+str(width)+' '+str([admin_metric,public_metric]))
                call('Page.navigate',{'url':base+'admin/index.php'});time.sleep(.4)
        (QA/'header-comparison.json').write_text(json.dumps(header_comparison,indent=2))

        check(evaluate('!document.querySelector(".admin-navigation").open'),'mobile navigation initially collapsed')
        evaluate('document.querySelector(".admin-navigation summary").focus();document.querySelector(".admin-navigation summary").click()');check(evaluate('document.querySelector(".admin-navigation").open'),'mobile menu opens')
        (QA/'admin-menu-390.png').write_bytes(base64.b64decode(call('Page.captureScreenshot',{'format':'png'})['data']))
        call('Input.dispatchKeyEvent',{'type':'keyDown','key':'Escape','code':'Escape','windowsVirtualKeyCode':27});check(evaluate('!document.querySelector(".admin-navigation").open'),'mobile menu Escape')
        check(not errors,'no admin JavaScript exceptions: '+json.dumps(errors));ws.close()
    check(save(dp,dict(form(dp),action='draft')).status_code==303,'unpublish course')
    check(get('course.php?slug='+slug,anon).status_code==404 and slug not in get('sitemap.php',anon).text,'unpublish removes public course and sitemap')
    check('noindex' in soup(get('course.php?slug='+slug,anon)).select_one('meta[name=robots]')['content'],'draft course noindex')
    check(get('media.php?file='+draft['featured_image'],anon).status_code==404,'unpublish revokes private upload')
    delete_path='admin/course-delete.php'
    check(s.get(base+delete_path,allow_redirects=False).status_code==405,'course deletion rejects GET')
    check(save(delete_path,{}).status_code==403,'course deletion CSRF')
    deletion={'csrf_token':form(dp)['csrf_token'],'id':str(draftid),'version':form(dp)['version'],'confirm_slug':slug}
    check(save(delete_path,dict(deletion,id='0')).status_code==422,'delete requires valid course ID')
    check(save(delete_path,dict(deletion,version='1')).status_code==422,'stale deletion blocked')
    check(save(delete_path,deletion).status_code==422,'dependent sessions block deletion')
    php("require 'includes/cms.php';cmsDatabase()->exec('DELETE FROM cms_course_sessions WHERE id="+str(newsid)+"');")
    check(save(delete_path,deletion).status_code==303,'draft without sessions deleted')
    check(len(rows())==24,'only original courses remain after temporary course cleanup')
    for route in ['index.php','courses.php','categories.php','category.php?slug=information-technology','course.php?slug=power-bi','admin/courses.php','admin/course-edit.php?id=1','admin/course-preview.php?id=1','admin/sessions.php','admin/session-edit.php?id=1']:
        response=get(route);check(response.status_code==200,'pricing route loads '+route);doc=soup(response);check(not doc.select('[data-course-price],.course-price,#display-currency,[name=price_djf]'),'no active pricing markup '+route)
        for schema in doc.select('script[type="application/ld+json"]'):
            check(not re.search(r'"(?:price|priceCurrency|offers?|lowPrice|highPrice)"',schema.text,re.I),'no pricing schema '+route)
    # Cross the page boundary through CMS, then remove only these temporary drafts.
    pagination_ids=[]
    for n in range(2):
        extra=dict(new,name='QA Pagination '+str(n),slug='',action='draft')
        result=save('admin/course-edit.php',extra);check(result.status_code==303,'additional CMS course '+str(n))
        pagination_ids.append(int(re.search(r'id=(\d+)',result.headers['Location'])[1]))
    check(len(rows())==26 and len(soup(get('admin/courses.php')).select('.article-list li'))==25 and len(soup(get('admin/courses.php?page=2')).select('.article-list li'))==1,'catalogue beyond 24 with real pagination')
    for extra_id in pagination_ids:
        er=next(r for r in rows() if r['id']==extra_id);ef=form('admin/course-edit.php?id='+str(extra_id))
        check(save(delete_path,{'csrf_token':ef['csrf_token'],'id':str(extra_id),'version':ef['version'],'confirm_slug':er['slug']}).status_code==303,'pagination draft cleanup')
    check(len(rows())==24,'all temporary courses removed')
    # Existing Articles workflow remains usable within the new shell.
    article=form('admin/article-edit.php');article.update(title='QA Temporary article',excerpt='Temporary QA article summary.',action='draft');article['blocks[0][text]']='Temporary article content.'
    r=save('admin/article-edit.php',article);check(r.status_code==303,'article draft saved separately')
    check(get('admin/preview.php?id='+re.search(r'id=(\d+)',r.headers['Location'])[1]).status_code==200,'article saved preview works')
    check('QA Temporary article' not in get('blog.php',anon).text,'article draft private')
    aid=int(re.search(r'id=(\d+)',r.headers['Location'])[1]);ap='admin/article-edit.php?id='+str(aid)
    check(save(ap,dict(form(ap),action='publish')).status_code==303,'existing article publishing retained')
    ar=soup(get('article.php?slug=qa-temporary-article',anon))
    check('BlogPosting' in ''.join(x.text for x in ar.select('script[type="application/ld+json"]')) and 'qa-temporary-article' in get('sitemap.php',anon).text,'existing article SEO retained')
    check(save(ap,dict(form(ap),action='preview',title='Must not save')).status_code==422,'article preview POST cannot change live content')
    check('QA Temporary article' in get('article.php?slug=qa-temporary-article',anon).text,'article live title preserved after rejected preview')
    check(save(ap,dict(form(ap),action='draft')).status_code==303,'article unpublish retained')
    check(get('article.php?slug=qa-temporary-article',anon).status_code==404,'article removed publicly')
    # Article deletion: deliberate confirmation and every server-side guard.
    delete_article='admin/article-delete.php'
    check(anon.post(base+delete_article,data={},allow_redirects=False).status_code==303,'article deletion requires authentication')
    check(s.get(base+delete_article,allow_redirects=False).status_code==405,'article deletion rejects GET')
    check(save(delete_article,{}).status_code==403,'article deletion CSRF')
    def article_deletion(editor,article_id,slug):
        f=form(editor);return {'csrf_token':f['csrf_token'],'id':str(article_id),'version':f['version'],'confirm_slug':slug}
    deletion=article_deletion(ap,aid,'qa-temporary-article')
    check(save(delete_article,dict(deletion,confirm_slug='wrong')).status_code==422,'article deletion exact confirmation')
    check(save(delete_article,dict(deletion,version='1')).status_code==422,'article stale deletion blocked')
    check(save(ap,dict(form(ap),action='publish')).status_code==303,'article republish for deletion protection')
    check(save(delete_article,article_deletion(ap,aid,'qa-temporary-article')).status_code==422,'published article deletion blocked')
    check(save(ap,dict(form(ap),action='draft')).status_code==303,'article return to draft for retirement')
    check(save(delete_article,article_deletion(ap,aid,'qa-temporary-article')).status_code==303,'confirmed draft article deleted')
    check('Article permanently deleted' in get('admin/articles.php').text,'article deletion success feedback')
    check('Article permanently deleted' not in get('admin/articles.php').text,'success feedback consumed once')
    check(get('article.php?slug=qa-temporary-article',anon).status_code==404 and 'qa-temporary-article' not in get('sitemap.php',anon).text,'deleted article absent publicly and from sitemap')
    check(save(media_ap,dict(form(media_ap),action='publish')).status_code==303,'shared image article publishes after source course deletion')
    check(get(media_value,anon).status_code==200,'shared upload retained and available through published article')
    check(save(media_ap,dict(form(media_ap),action='draft')).status_code==303,'shared article unpublish')
    check(save(delete_article,article_deletion(media_ap,media_aid,'qa-shared-media')).status_code==303,'shared image article cleanup')
    check((ROOT/'storage/cms-media'/draft['featured_image']).is_file() and get(media_value,anon).status_code==404,'deletion retains orphan upload privately')
    # Exercise actual article page boundaries on isolated content only.
    article_ids=[]
    for n in range(26):
        af=form('admin/article-edit.php');af.update(title='QA Pagination article '+str(n),action='draft')
        r=save('admin/article-edit.php',af);check(r.status_code==303,'create article pagination fixture '+str(n))
        article_ids.append(int(re.search(r'id=(\d+)',r.headers['Location'])[1]))
    check(len(soup(get('admin/articles.php')).select('.article-list li'))==25 and len(soup(get('admin/articles.php?page=2')).select('.article-list li'))==1,'article pagination beyond 25')
    check(len(soup(get('admin/articles.php?q=article+25&status=draft')).select('.article-list li'))==1,'article search with status filter')
    check(not soup(get('admin/articles.php?status=published')).select('.article-list li'),'article published filter excludes drafts')
    for article_id in article_ids:
        editor='admin/article-edit.php?id='+str(article_id);f=form(editor)
        check(save(delete_article,article_deletion(editor,article_id,f['slug'])).status_code==303,'delete article pagination fixture '+str(article_id))
    check(int(php("require 'includes/cms.php';echo cmsDatabase()->query('SELECT COUNT(*) FROM cms_articles')->fetchColumn();"))==restored['cms_articles'],'all temporary articles removed')
    tok=soup(get('admin/index.php')).select_one('[name=csrf_token]')['value']
    check(s.post(base+'admin/logout.php',data={'csrf_token':tok},allow_redirects=False).status_code==303 and s.get(base+'admin/courses.php',allow_redirects=False).status_code==303,'logout guards expanded routes')
    check('Warning:' not in (QA/'courses-http.log').read_text() and 'Fatal error' not in (QA/'courses-http.log').read_text(),'no PHP warnings')
    (QA/'phase2-results.json').write_text(json.dumps({'passed':len(checks),'checks':checks},ensure_ascii=False,indent=2),encoding='utf-8')
    print(json.dumps({'passed':len(checks)}))
finally:
    if server:server.terminate();server.wait(timeout=10)
    if log:log.close()
    for file in uploaded:(ROOT/'storage/cms-media'/file).unlink(missing_ok=True)
    php("require 'includes/cms.php';cmsDatabase()->exec('DROP DATABASE "+name+"');",False)
    config.unlink(missing_ok=True)
