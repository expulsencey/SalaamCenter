"""Isolated CMS integration QA. Requires disposable MySQL on 127.0.0.1:3308.
Never connects to the project's configured database. Uses requests/beautifulsoup4.
"""
import os, json, secrets, subprocess, time, re, sys
from pathlib import Path
import requests
from bs4 import BeautifulSoup

ROOT = Path(__file__).resolve().parents[1]
PHP = r'C:\wamp64\bin\php\php8.3.14\php.exe'
QA = ROOT / 'storage/cms-qa'
ENV = dict(os.environ, SALAAM_CMS_CONFIG=str(QA / 'config.php'))
password = secrets.token_urlsafe(24)
def php(code):
    return subprocess.run([PHP, '-d', 'xdebug.mode=off', '-r', code], cwd=ROOT, env=ENV, capture_output=True, text=True, check=True).stdout
def setup(command, data=None):
    return subprocess.run([PHP, '-d', 'xdebug.mode=off', 'scripts/cms-setup.php', command], cwd=ROOT, env=ENV, input=json.dumps(data) if data else '', capture_output=True, text=True, check=True)

QA.mkdir(parents=True, exist_ok=True)
# Only this disposable QA instance is used; never the WAMP database on port 3306.
php("$p=new PDO('mysql:host=127.0.0.1;port=3308','root','');$p->exec('CREATE DATABASE IF NOT EXISTS salaam_cms_qa CHARACTER SET utf8mb4');")
(QA/'config.php').write_text("<?php return ['db'=>['host'=>'127.0.0.1','port'=>3308,'database'=>'salaam_cms_qa','username'=>'root','password'=>'']];", encoding='utf-8')
setup('migrate'); setup('migrate')
setup('import-courses')
php("require 'includes/cms.php';foreach(['cms_articles','cms_users','cms_login_limits'] as $t) cmsDatabase()->exec('DELETE FROM '.$t);")
setup('create-admin', {'email':'qa@example.invalid','display_name':'QA temporary administrator','password':password})
(QA/'router.php').write_text("<?php $p=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);if(preg_match('~^/(storage|database|includes|scripts)/|^/config~',$p)){http_response_code(403);exit;}return false;",encoding='utf-8')
log = (QA/'http.log').open('w')
server = subprocess.Popen([PHP,'-d','xdebug.mode=off','-d','session.save_path='+str(QA),'-d','upload_tmp_dir='+str(QA),'-d','upload_max_filesize=6M','-d','post_max_size=8M','-S','127.0.0.1:8093',str(QA/'router.php')], cwd=ROOT, env=ENV, stdout=log, stderr=log)
base='http://127.0.0.1:8093/'
s=requests.Session(); s.trust_env=False
anon=requests.Session(); anon.trust_env=False
checks=[]; images=set()
def check(value,label):
    assert value,label
    checks.append(label)
def get(path, client=s): return client.get(base+path, timeout=10)
def token(response): return BeautifulSoup(response.text,'html.parser').select_one('[name=csrf_token]')['value']
def form(id=None, **changes):
    r=get('admin/article-edit.php'+('?id='+str(id) if id else ''))
    soup=BeautifulSoup(r.text,'html.parser')
    data={'csrf_token':token(r),'version':soup.select_one('[name=version]')['value'],'title':'QA TEST — Français l’analyse','slug':'','excerpt':'Temporary QA summary, not business content.','image_alt':'Temporary test image','blocks[0][type]':'h2','blocks[0][text]':'QA section','blocks[1][type]':'p','blocks[1][text]':'**Bold** *italic* [Link](https://example.org) <script>alert(1)</script> [bad](javascript:alert(1))','action':'draft'}
    data.update(changes)
    return data
def save(data,id=None,files=None): return s.post(base+'admin/article-edit.php'+('?id='+str(id) if id else ''),data=data,files=files,allow_redirects=False,timeout=15)
try:
    time.sleep(1)
    for page in ['index.php','articles.php','article-edit.php','preview.php?id=1','logout.php']:
        r=anon.get(base+'admin/'+page,allow_redirects=False)
        check(r.status_code==303 and r.headers['Location']=='login.php','guard '+page)
    r=get('admin/login.php'); old=s.cookies.get('SALAAMADMIN')
    check('noindex' in r.headers.get('X-Robots-Tag',''),'login noindex')
    check(s.post(base+'admin/login.php',data={'email':'qa@example.invalid','password':password}).status_code==403,'login CSRF')
    r=s.post(base+'admin/login.php',data={'csrf_token':token(r),'email':"' OR 1=1 --",'password':'wrong'})
    check('Unable to sign in' in r.text,'generic invalid login and SQL input')
    r=s.post(base+'admin/login.php',data={'csrf_token':token(r),'email':'qa@example.invalid','password':password})
    check('Dashboard' in r.text and s.cookies.get('SALAAMADMIN')!=old,'login and session rotation')
    replay=requests.Session();replay.trust_env=False;replay.cookies.set('SALAAMADMIN',old)
    check(replay.get(base+'admin/index.php',allow_redirects=False).status_code==303,'old session invalid')
    check(save({'action':'draft'}).status_code==403,'editor CSRF')
    r=save(form(),files={'image':('attack.php.jpg',b'<?php echo 1; ?>','image/jpeg')})
    check(r.status_code==422,'reject fake image')
    for name,payload,mime in [('vector.svg',b'<svg onload="alert(1)"></svg>','image/svg+xml'),('large.jpg',b'x'*(5*1024*1024+1),'image/jpeg')]:
        check(save(form(),files={'image':(name,payload,mime)}).status_code==422,'reject upload '+name)
    source=ROOT/'assets/images/about/salaam-center-reception.jpg'
    if not source.exists(): source=next((ROOT/'assets/images').rglob('*.jpg'))
    r=save(form(),files={'image':('../../photo.php.jpg',source.read_bytes(),'image/jpeg')})
    check(r.status_code==303,'create draft and valid image')
    id=int(re.search(r'id=(\d+)',r.headers['Location'])[1])
    row=json.loads(php(f"require 'includes/cms.php';echo json_encode(cmsDatabase()->query('SELECT * FROM cms_articles WHERE id={id}')->fetch());"))
    slug=row['slug']; image=row['featured_image']; images.add(image)
    check(bool(re.fullmatch(r'[a-f0-9]{48}\.webp',image)),'generated media filename')
    check(get('article.php?slug='+slug,anon).status_code==404,'draft public 404')
    check(get('media.php?file='+image,anon).status_code==404,'draft media private')
    check(get('media.php?file='+image).headers['Content-Type']=='image/webp','authorized image preview')
    r=get('admin/preview.php?id='+str(id))
    check(r.status_code==200 and 'noindex' in r.headers['X-Robots-Tag'],'private draft preview')
    check(not BeautifulSoup(r.text,'html.parser').select('script:not([src]), iframe, [onerror]'),'XSS escaped')
    check(slug not in get('sitemap.php',anon).text,'draft excluded sitemap')
    stale=form(id,slug=slug)
    check(save(form(id,slug=slug,title="QA TEST Français updated ' OR 1=1 --"),id).status_code==303,'edit draft and literal SQL text')
    check(save(stale,id).status_code==422,'stale edit blocked')
    check(save(form(id,slug=slug,action='publish'),id).status_code==303,'publish')
    r=get('article.php?slug='+slug,anon); soup=BeautifulSoup(r.text,'html.parser')
    check(r.status_code==200 and len(soup.select('h1'))==1,'published article')
    check('Français' in soup.title.text and soup.select_one('meta[name=description]')['content'].startswith('Temporary QA'),'article title and description UTF8')
    check(soup.select_one('link[rel=canonical]')['href'].endswith('article.php?slug='+slug),'article canonical')
    check(soup.select_one('meta[property="og:type"]')['content']=='article','article Open Graph')
    schema=' '.join(x.text for x in soup.select('script[type="application/ld+json"]'))
    check('BlogPosting' in schema and 'datePublished' in schema and 'BreadcrumbList' in schema,'article schema')
    check(slug in get('sitemap.php',anon).text,'published sitemap')
    check(slug in get('blog.php',anon).text,'published listing')
    check(get('media.php?file='+image,anon).status_code==200,'published media')
    if '--browser' in sys.argv:
        import websocket
        target=anon.get('http://127.0.0.1:9223/json').json()[0]
        ws=websocket.create_connection(target['webSocketDebuggerUrl'],origin='http://127.0.0.1:9223',timeout=15)
        sequence=0; exceptions=[]
        def call(method,params=None):
            global sequence
            sequence+=1;ws.send(json.dumps({'id':sequence,'method':method,'params':params or {}}))
            while True:
                event=json.loads(ws.recv())
                if event.get('method')=='Runtime.exceptionThrown': exceptions.append(event)
                if event.get('id')==sequence:
                    assert 'error' not in event,event
                    return event.get('result',{})
        def evaluate(code):
            result=call('Runtime.evaluate',{'expression':code,'returnByValue':True})
            assert 'exceptionDetails' not in result,result
            return result.get('result',{}).get('value')
        call('Page.enable');call('Runtime.enable');call('Network.enable')
        call('Network.setCookie',{'name':'SALAAMADMIN','value':s.cookies.get('SALAAMADMIN'),'url':base,'httpOnly':True,'sameSite':'Strict'})
        for width,height in [(1440,900),(1366,768),(768,1024),(390,844)]:
            call('Emulation.setDeviceMetricsOverride',{'width':width,'height':height,'deviceScaleFactor':1,'mobile':False})
            for route in ['admin/login.php','admin/index.php','admin/articles.php','admin/article-edit.php?id='+str(id),'admin/preview.php?id='+str(id),'blog.php','article.php?slug='+slug]:
                call('Page.navigate',{'url':base+route});time.sleep(.35)
                for _ in range(40):
                    if evaluate('document.readyState')=='complete':break
                    time.sleep(.1)
                metrics=evaluate('({overflow:document.documentElement.scrollWidth>innerWidth+1,h1:document.querySelectorAll("h1").length,broken:[...document.images].some(i=>i.complete&&!i.naturalWidth),unlabelled:[...document.querySelectorAll("input:not([type=hidden]),textarea,select")].some(i=>!i.labels.length)})')
                check(not metrics['overflow'] and metrics['h1']==1 and not metrics['broken'] and not metrics['unlabelled'],f'browser {width} {route}')
        call('Page.navigate',{'url':base+'admin/article-edit.php?id='+str(id)});time.sleep(.5)
        count=evaluate('document.querySelectorAll(".content-block").length')
        evaluate('document.getElementById("add-block").click()')
        check(evaluate('document.querySelectorAll(".content-block").length')==count+1,'browser add block')
        call('Input.dispatchKeyEvent',{'type':'keyDown','key':'Tab','code':'Tab','windowsVirtualKeyCode':9})
        call('Input.dispatchKeyEvent',{'type':'keyUp','key':'Tab','code':'Tab','windowsVirtualKeyCode':9})
        check(evaluate('document.activeElement.tagName')!='BODY','keyboard focus')
        import base64
        (QA/'editor-mobile.png').write_bytes(base64.b64decode(call('Page.captureScreenshot',{'format':'png'})['data']))
        check(not exceptions,'no browser JavaScript exceptions');ws.close()
    check(save(form(id,slug=slug,title='QA TEST updated public title',action='publish'),id).status_code==303,'update published stable slug')
    check(save(form(id,slug='changed-url',action='publish'),id).status_code==422,'published slug stable')
    r=save(form());check(r.status_code==303,'duplicate title draft unique slug')
    duplicate=int(re.search(r'id=(\d+)',r.headers['Location'])[1])
    check(save(form(duplicate,slug=slug),duplicate).status_code==422,'duplicate explicit slug rejected')
    check(save(form(id,slug=slug),id).status_code==303,'unpublish')
    check(get('article.php?slug='+slug,anon).status_code==404 and get('media.php?file='+image,anon).status_code==404,'unpublish revokes article and image')
    check(slug not in get('sitemap.php',anon).text,'unpublish removes sitemap')
    for path in ['article.php?slug=missing','article.php?slug[]=bad','media.php?file=../../config.local.php']:
        check(get(path,anon).status_code==404,'invalid route '+path)
    check(s.post(base+'admin/logout.php',data={}).status_code==403,'logout CSRF')
    r=s.post(base+'admin/logout.php',data={'csrf_token':token(get('admin/index.php'))},allow_redirects=False)
    check(r.status_code==303 and s.get(base+'admin/index.php',allow_redirects=False).status_code==303,'logout invalidates')
    for _ in range(8):
        r=get('admin/login.php');s.post(base+'admin/login.php',data={'csrf_token':token(r),'email':'qa@example.invalid','password':'wrong'})
    r=get('admin/login.php');r=s.post(base+'admin/login.php',data={'csrf_token':token(r),'email':'qa@example.invalid','password':password})
    check('Unable to sign in' in r.text,'login throttled')
    # Exercise an actual lost database configuration on the isolated server only.
    (QA/'config.php').write_text("<?php return [];",encoding='utf-8')
    try:
        for path in ['blog.php','article.php?slug='+slug]:
            r=get(path,anon)
            check(r.status_code==503 and 'noindex' in r.text and not any(x in r.text for x in ['PDOException','Stack trace','mysql:host','C:\\wamp64']),'safe database failure '+path)
        check(get('sitemap.php',anon).status_code==503,'sitemap fails safely without authoritative course storage')
    finally:
        (QA/'config.php').write_text("<?php return ['db'=>['host'=>'127.0.0.1','port'=>3308,'database'=>'salaam_cms_qa','username'=>'root','password'=>'']];",encoding='utf-8')
    print(json.dumps({'passed':len(checks),'checks':checks},ensure_ascii=False))
finally:
    php("require 'includes/cms.php';foreach(['cms_articles','cms_users','cms_login_limits'] as $t) cmsDatabase()->exec('DELETE FROM '.$t);")
    for name in images:
        path=ROOT/'storage/cms-media'/name
        if path.is_file(): path.unlink()
    server.terminate();server.wait(timeout=10);log.close()
    (QA/'config.php').unlink(missing_ok=True)
