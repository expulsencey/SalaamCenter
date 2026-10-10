"""Course deletion regression on synthetic records in an isolated database.
Requires a private backup and local MySQL; --browser uses isolated CDP port 9225.
Never authenticates against or modifies the real CMS database.
"""
import os,json,secrets,subprocess,time,sys,re,base64
from pathlib import Path
import requests
from bs4 import BeautifulSoup
ROOT=Path(__file__).resolve().parents[1];QA=ROOT/'storage/cms-qa'
PHP=r'C:\wamp64\bin\php\php8.3.14\php.exe'
backup=QA/'course-deletion-backup-20261010.json'
assert backup.is_file(),'A protected database backup is required before deletion tests.'
baseline=json.loads(backup.read_text(encoding='utf-8'))
assert len(baseline['tables']['cms_courses']['rows'])>=25
name='cms_deletion_qa_'+secrets.token_hex(6)
config=QA/'deletion-config.php';env=dict(os.environ,SALAAM_CMS_CONFIG=str(config))
password=secrets.token_urlsafe(24);checks=[];uploaded=set();server=None;log=None

def php(code,isolated=True):
    r=subprocess.run([PHP,'-d','xdebug.mode=off','-r',code],cwd=ROOT,env=env if isolated else os.environ,capture_output=True,text=True)
    if r.returncode:raise RuntimeError('Private PHP QA operation failed; no credentials printed.')
    return r.stdout

def check(value,label):
    assert value,label
    checks.append(label)

php("require 'includes/cms.php';cmsDatabase()->exec('CREATE DATABASE "+name+" CHARACTER SET utf8mb4');$c=require 'config.local.php';$c['db']['database']='"+name+"';file_put_contents('storage/cms-qa/deletion-config.php','<?php return '.var_export($c,true).';');",False)
try:
    # Copy table definitions only; do not import real courses, sessions or accounts.
    php("require 'includes/cms.php';$b=json_decode(file_get_contents('storage/cms-qa/course-deletion-backup-20261010.json'),true);$p=cmsDatabase();foreach($b['tables'] as $name=>$t)if(str_starts_with($name,'cms_'))$p->exec($t['schema']);$p->prepare('INSERT INTO cms_migrations(name,source_hash) VALUES (?,?)')->execute(['courses-v1',str_repeat('0',64)]);")
    r=subprocess.run([PHP,'-d','xdebug.mode=off','scripts/cms-setup.php','create-admin'],cwd=ROOT,env=env,input=json.dumps({'email':'deletion-qa@example.invalid','display_name':'Deletion QA','password':password}),capture_output=True,text=True)
    assert r.returncode==0,'Isolated test account creation failed'
    (QA/'deletion-router.php').write_text("<?php $p=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);if(preg_match('~^/(storage|database|includes|scripts)/|^/config~',$p)){http_response_code(403);exit;}return false;",encoding='utf-8')
    log=(QA/'deletion-http.log').open('w')
    server=subprocess.Popen([PHP,'-d','xdebug.mode=off','-d','opcache.enable=0','-d','session.save_path='+str(QA),'-d','upload_tmp_dir='+str(QA),'-S','127.0.0.1:8096',str(QA/'deletion-router.php')],cwd=ROOT,env=env,stdout=log,stderr=log)
    time.sleep(1);base='http://127.0.0.1:8096/'
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

    login=form('admin/login.php');login.update(email='deletion-qa@example.invalid',password=password)
    check(save('admin/login.php',login).status_code==303,'isolated account login')
    def create(title,status='draft',upload=False):
        f=form('admin/course-edit.php');f.update(name=title,category_slug='information-technology',title_language='en',description='Synthetic deletion regression content.',image='assets/images/courses/power-bi.png',image_alt='Synthetic test image',action=status,featured='on')
        files={'upload':('test.png',(ROOT/'assets/images/courses/power-bi.png').read_bytes(),'image/png')} if upload else None
        r=save('admin/course-edit.php',f,files);check(r.status_code==303,'create synthetic '+title)
        i=int(re.search(r'id=(\d+)',r.headers['Location'])[1]);row=next(x for x in rows() if x['id']==i)
        if row['featured_image']:uploaded.add(row['featured_image'])
        return row
    def deletion(row):
        f=form('admin/course-edit.php?id='+str(row['id']))
        return {'csrf_token':f['csrf_token'],'id':str(row['id']),'version':f['version']}
    if '--phase2b' in sys.argv:
        import runpy
        runpy.run_path(str(ROOT/'scripts/check-cms-phase2b.py'))['run'](locals())
    draft=create('QA Draft');published=create('QA Published','publish',True);linked=create('QA Linked','publish');unrelated=create('QA Unrelated','publish')
    for route in ['courses.php','category.php?slug=information-technology','index.php','sitemap.php']:
        check(published['slug'] in get(route,anon).text,'published fixture visible before deletion '+route)
    editor=soup(get('admin/course-edit.php?id='+str(published['id'])))
    check(editor.select_one('[data-course-delete-form]') is not None and editor.select_one('input[name=confirm_slug]') is None,'server-rendered confirmation works without a slug field')
    sf=form('admin/session-edit.php');sf.update(course_id=str(linked['id']),start_date='2099-01-01',status='upcoming');check(save('admin/session-edit.php',sf).status_code==303,'create synthetic linked session')
    before=rows();dp='admin/course-delete.php';payload=deletion(published)
    check(anon.post(base+dp,data=payload,allow_redirects=False).status_code==303,'unauthorized deletion refused')
    check(s.get(base+dp,allow_redirects=False).status_code==405,'GET cannot delete')
    check(save(dp,dict(payload,csrf_token='invalid')).status_code==403,'invalid CSRF refused')
    check(save(dp,dict(payload,id='0')).status_code==422,'invalid ID refused')
    check(save(dp,dict(payload,id='999999')).status_code==422,'nonexistent course refused')
    check(save(dp,dict(payload,version='999')).status_code==422,'stale version refused')
    response=save(dp,deletion(linked))
    check(response.status_code==422 and '1 associated training session' in response.text and 'Manage associated sessions' in response.text,'dependency refusal includes session count and management link')
    check('permanently deleted' not in response.text,'no false success on refusal')
    check(rows()==before,'all failed requests preserve course records')
    check(php("require 'includes/cms.php';echo cmsDatabase()->query('SELECT COUNT(*) FROM cms_course_sessions')->fetchColumn();")=='1','linked session retained')
    check(save(dp,deletion(draft)).status_code==303,'A: synthetic draft deleted without slug')
    check(not any(x['id']==draft['id'] for x in rows()),'draft record removed')
    if '--browser' in sys.argv:
        import websocket
        target=s.get('http://127.0.0.1:9225/json').json()[0]
        ws=websocket.create_connection(target['webSocketDebuggerUrl'],origin='http://127.0.0.1:9225',timeout=20);seq=0;errors=[]
        def call(method,params=None):
            global seq
            seq+=1;ws.send(json.dumps({'id':seq,'method':method,'params':params or {}}))
            while True:
                m=json.loads(ws.recv())
                if m.get('method')=='Runtime.exceptionThrown':errors.append(m)
                if m.get('id')==seq:return m.get('result',{})
        def ev(code):return call('Runtime.evaluate',{'expression':code,'returnByValue':True})['result'].get('value')
        call('Page.enable');call('Page.navigate',{'url':'about:blank'});time.sleep(.3);call('Runtime.enable');errors.clear()
        call('Network.enable');call('Network.setCookie',{'name':'SALAAMADMIN','value':s.cookies.get('SALAAMADMIN'),'url':base,'httpOnly':True,'sameSite':'Strict'})
        call('Page.navigate',{'url':base+'admin/course-edit.php?id='+str(published['id'])});time.sleep(1)
        for width,height in [(1440,900),(1366,768),(1024,768),(768,1024),(390,844),(375,812)]:
            call('Emulation.setDeviceMetricsOverride',{'width':width,'height':height,'deviceScaleFactor':1,'mobile':False})
            ev('document.querySelector("[aria-haspopup=dialog]").click()')
            check(ev('document.querySelector("dialog").open && document.activeElement.matches("[data-course-delete-cancel]")'),'dialog opens with Cancel focused '+str(width))
            check(ev('(()=>{let r=document.querySelector("dialog").getBoundingClientRect();return r.left>=0&&r.right<=innerWidth&&r.top>=0&&r.bottom<=innerHeight&&document.documentElement.scrollWidth<=innerWidth+1})()'),'dialog fits viewport '+str(width))
            call('Input.dispatchKeyEvent',{'type':'keyDown','key':'Tab','code':'Tab','windowsVirtualKeyCode':9})
            check(ev('document.activeElement.textContent==="Delete permanently"'),'keyboard reaches Delete '+str(width))
            call('Input.dispatchKeyEvent',{'type':'keyDown','key':'Escape','code':'Escape','windowsVirtualKeyCode':27});time.sleep(.05)
            check(ev('!document.querySelector("dialog").open && document.activeElement.matches("[aria-haspopup=dialog]")'),'Escape returns focus '+str(width))
            ev('document.querySelector("[aria-haspopup=dialog]").click();document.querySelector("[data-course-delete-cancel]").click()')
            check(ev('!document.querySelector("dialog").open'),'Cancel closes without deletion '+str(width))
        check(any(x['id']==published['id'] for x in rows()),'browser cancel retains course')
        ev('document.querySelector("[aria-haspopup=dialog]").click()')
        (QA/'course-deletion-dialog-mobile.png').write_bytes(base64.b64decode(call('Page.captureScreenshot',{'format':'png'})['data']))
        ev('document.querySelector("[data-course-delete-form] button.destructive").click()');time.sleep(.5)
        check(ev('location.pathname==="/admin/courses.php" && document.body.textContent.includes("Course permanently deleted")'),'browser confirms published deletion and success redirect')
        check(not errors,'no dialog JavaScript exceptions');ws.close()
    else:
        check(save(dp,payload).status_code==303,'B: published course directly deleted')
    check(not any(x['id']==published['id'] for x in rows()),'published record removed')
    slug=published['slug']
    for route in ['courses.php','category.php?slug=information-technology','index.php','sitemap.php','courses.php?q='+slug,'admin/courses.php?q='+slug]:
        response=get(route)
        check(not soup(response).select('.article-list li') if route.startswith('admin/') else slug not in response.text,'deleted course absent '+route)
    check(get('course.php?slug='+slug,anon).status_code==404,'old public URL returns 404')
    check((ROOT/'storage/cms-media'/published['featured_image']).is_file(),'uploaded image retained on disk')
    check(get('media.php?file='+published['featured_image'],anon).status_code==404,'orphan image remains private')
    check(next(x for x in rows() if x['id']==unrelated['id'])==unrelated,'unrelated course unchanged')
    check(next(x for x in rows() if x['id']==linked['id'])==linked,'linked course unchanged')
    check('2 courses in this view' in get('admin/courses.php').text,'list count updated')
    check('Warning:' not in (QA/'deletion-http.log').read_text() and 'Fatal error' not in (QA/'deletion-http.log').read_text(),'no PHP warnings')
    print(json.dumps({'passed':len(checks),'checks':checks},indent=2))
    (QA/'course-deletion-results.json').write_text(json.dumps({'passed':len(checks),'checks':checks},indent=2),encoding='utf-8')
finally:
    if server:server.terminate();server.wait(timeout=10)
    if log:log.close()
    for filename in uploaded:(ROOT/'storage/cms-media'/filename).unlink(missing_ok=True)
    php("require 'includes/cms.php';cmsDatabase()->exec('DROP DATABASE "+name+"');",False)
    config.unlink(missing_ok=True)
