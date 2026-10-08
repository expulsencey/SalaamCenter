"""Phase 1 only. Requires the disposable QA MySQL instance on port 3308.
Does not use config.local.php or exercise article management. Optional --browser
uses a prestarted isolated Chromium CDP endpoint on port 9224.
"""
import os, json, secrets, subprocess, time, sys
from pathlib import Path
import requests
from bs4 import BeautifulSoup

ROOT=Path(__file__).resolve().parents[1]
QA=ROOT/'storage/cms-qa'
PHP=r'C:\wamp64\bin\php\php8.3.14\php.exe'
ENV=dict(os.environ,SALAAM_CMS_CONFIG=str(QA/'auth-config.php'))
database='cms_auth_qa_'+secrets.token_hex(6)
password=secrets.token_urlsafe(54)  # 72 ASCII bytes; test bcrypt's exact boundary.
email='qa@example.invalid'
checks=[]
def check(value,label):
    assert value,label
    checks.append(label)
def php(code):
    return subprocess.run([PHP,'-d','xdebug.mode=off','-r',code],cwd=ROOT,env=ENV,capture_output=True,text=True,check=True).stdout
def setup(action,data=None):
    return subprocess.run([PHP,'-d','xdebug.mode=off','scripts/cms-setup.php',action],cwd=ROOT,env=ENV,input=json.dumps(data) if data else '',capture_output=True,text=True)
QA.mkdir(parents=True,exist_ok=True)
php("$p=new PDO('mysql:host=127.0.0.1;port=3308','root','');$p->exec('CREATE DATABASE "+database+" CHARACTER SET utf8mb4');")
config="<?php return ['db'=>['host'=>'127.0.0.1','port'=>3308,'database'=>'"+database+"','username'=>'root','password'=>'']];"
(QA/'auth-config.php').write_text(config,encoding='utf-8')
server=None;log=None
try:
    check(setup('migrate').returncode==0,'initial migration')
    check(setup('import-courses').returncode==0,'course fixture import for current dashboard')
    user={'email':email,'display_name':'QA Français <script>test</script>','password':password}
    check(setup('create-admin',dict(user,password='short')).returncode!=0,'weak password rejected')
    check(setup('create-admin',user).returncode==0,'first administrator creation')
    check(setup('create-admin',user).returncode!=0,'duplicate account refused')
    php("require 'includes/cms.php';cmsDatabase()->exec('CREATE TABLE qa_unrelated (id INT PRIMARY KEY)');cmsDatabase()->exec('INSERT INTO qa_unrelated VALUES (7)');")
    check(setup('migrate').returncode==0,'repeat migration')
    result=json.loads(php("require 'includes/cms.php';echo json_encode([cmsDatabase()->query('SELECT COUNT(*) FROM cms_users')->fetchColumn(),cmsDatabase()->query('SELECT id FROM qa_unrelated')->fetchColumn(),cmsDatabase()->query('SELECT password_hash FROM cms_users')->fetchColumn()]);"))
    check(result[0]==1 and result[1]==7,'migration preserves users and unrelated table')
    check(result[2]!=password and result[2].startswith('$2y$12$'),'only bcrypt hash stored')
    (QA/'auth-router.php').write_text("<?php $p=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);if(preg_match('~^/(storage|database|includes|scripts)/|^/config~',$p)){http_response_code(403);exit;}return false;",encoding='utf-8')
    log=(QA/'auth-http.log').open('w')
    server=subprocess.Popen([PHP,'-d','xdebug.mode=off','-d','opcache.enable=0','-d','session.save_path='+str(QA),'-S','127.0.0.1:8094',str(QA/'auth-router.php')],cwd=ROOT,env=ENV,stdout=log,stderr=log)
    time.sleep(1)
    base='http://127.0.0.1:8094/'
    s=requests.Session();s.trust_env=False
    def get(path):return s.get(base+path,timeout=10)
    def token(r):return BeautifulSoup(r.text,'html.parser').select_one('[name=csrf_token]')['value']
    def login(pw,who=email):
        r=get('admin/login.php')
        return s.post(base+'admin/login.php',data={'csrf_token':token(r),'email':who,'password':pw},allow_redirects=False,timeout=10)
    for route in ['index.php','articles.php','article-edit.php','preview.php?id=1','logout.php']:
        r=s.get(base+'admin/'+route,allow_redirects=False)
        check(r.status_code==303 and r.headers.get('Location')=='login.php','unauthenticated guard '+route)
    r=get('admin/login.php'); old=s.cookies.get('SALAAMADMIN')
    check('HttpOnly' in r.headers.get('Set-Cookie','') or s.cookies.get('SALAAMADMIN') is not None,'session cookie established')
    cookie=next(c for c in s.cookies if c.name=='SALAAMADMIN')
    check('HttpOnly' in cookie._rest and cookie._rest.get('SameSite')=='Strict' and not cookie.secure,'HTTP development cookie flags')
    check('noindex, nofollow' in r.headers.get('X-Robots-Tag',''),'login noindex')
    check(s.post(base+'admin/login.php',data={'email':email,'password':password}).status_code==403,'login CSRF rejected')
    known=login('wrong');unknown=login('wrong','missing@example.invalid');injection=login('wrong',"' OR 1=1 --")
    def error(r):return BeautifulSoup(r.text,'html.parser').select_one('[role=alert]').text
    check(error(known)==error(unknown)==error(injection),'generic failures and SQL injection blocked')
    check(login(password+'x').status_code==200,'overlong password cannot authenticate by truncation')
    check(login(password).status_code==303,'valid login')
    authenticated=s.cookies.get('SALAAMADMIN')
    check(authenticated!=old,'session id regenerated')
    replay=requests.Session();replay.trust_env=False;replay.cookies.set('SALAAMADMIN',old)
    check(replay.get(base+'admin/index.php',allow_redirects=False).status_code==303,'old session cannot authenticate')
    r=get('admin/index.php');doc=BeautifulSoup(r.text,'html.parser')
    check(r.status_code==200 and 'QA Français <script>test</script>' in doc.get_text() and not doc.select('script:not([src])'),'dashboard name escaped and UTF8')
    check('noindex, nofollow' in r.headers['X-Robots-Tag'] and 'no-store' in r.headers['Cache-Control'],'dashboard private headers')
    if '--browser' in sys.argv:
        import websocket
        target=s.get('http://127.0.0.1:9224/json').json()[0]
        ws=websocket.create_connection(target['webSocketDebuggerUrl'],origin='http://127.0.0.1:9224',timeout=15)
        seq=0;errors=[]
        def call(method,params=None):
            global seq
            seq+=1;ws.send(json.dumps({'id':seq,'method':method,'params':params or {}}))
            while True:
                message=json.loads(ws.recv())
                if message.get('method')=='Runtime.exceptionThrown':errors.append(message)
                if message.get('id')==seq:
                    assert 'error' not in message
                    return message.get('result',{})
        def evaluate(code):return call('Runtime.evaluate',{'expression':code,'returnByValue':True})['result'].get('value')
        call('Page.enable');call('Runtime.enable');call('Network.enable')
        call('Network.setCookie',{'name':'SALAAMADMIN','value':authenticated,'url':base,'httpOnly':True,'sameSite':'Strict'})
        for width,height in [(1440,900),(1366,768),(768,1024),(390,844)]:
            call('Emulation.setDeviceMetricsOverride',{'width':width,'height':height,'deviceScaleFactor':1,'mobile':False})
            for route in ['admin/login.php','admin/index.php']:
                call('Page.navigate',{'url':base+route});time.sleep(.5)
                check(evaluate('document.documentElement.scrollWidth<=innerWidth+1 && document.querySelectorAll("h1").length===1 && [...document.querySelectorAll("input:not([type=hidden])")].every(i=>i.labels.length)'),'responsive labels '+str(width)+' '+route)
                call('Input.dispatchKeyEvent',{'type':'keyDown','key':'Tab','code':'Tab','windowsVirtualKeyCode':9})
                call('Input.dispatchKeyEvent',{'type':'keyUp','key':'Tab','code':'Tab','windowsVirtualKeyCode':9})
                check(evaluate('document.activeElement.tagName!=="BODY" && getComputedStyle(document.activeElement).outlineStyle!=="none"'),'keyboard focus '+str(width)+' '+route)
        check(not errors,'browser no JS exceptions');ws.close()
    check(get('admin/logout.php').status_code==405,'logout requires POST')
    check(s.post(base+'admin/logout.php',data={}).status_code==403,'logout CSRF rejected')
    r=s.post(base+'admin/logout.php',data={'csrf_token':token(get('admin/index.php'))},allow_redirects=False)
    check(r.status_code==303 and s.get(base+'admin/index.php',allow_redirects=False).status_code==303,'logout blocks dashboard')
    replay.cookies.set('SALAAMADMIN',authenticated)
    check(replay.get(base+'admin/index.php',allow_redirects=False).status_code==303,'logged-out session replay blocked')
    check(login(password).status_code==303,'login after logout')
    replacement=secrets.token_urlsafe(24)
    check(setup('reset-password',dict(user,password=replacement)).returncode==0,'CLI password reset')
    check(s.get(base+'admin/index.php',allow_redirects=False).status_code==303,'password reset revokes session')
    for _ in range(8):login('wrong')
    check(login(replacement).status_code==200,'throttling blocks further valid attempts')
    (QA/'auth-config.php').write_text('<?php return [];',encoding='utf-8')
    r=login('anything')
    check(r.status_code==503 and not any(x in r.text for x in ['PDOException','Stack trace','mysql:host',database,str(ROOT)]),'safe database failure')
    check('Warning:' not in (QA/'auth-http.log').read_text() and 'Fatal error' not in (QA/'auth-http.log').read_text(),'no PHP warnings')
    print(json.dumps({'passed':len(checks),'checks':checks},ensure_ascii=False))
finally:
    if server:server.terminate();server.wait(timeout=10)
    if log:log.close()
    # Drop only the unique database created by this run, never the site database.
    php("$p=new PDO('mysql:host=127.0.0.1;port=3308','root','');$p->exec('DROP DATABASE "+database+"');")
    (QA/'auth-config.php').unlink(missing_ok=True)
