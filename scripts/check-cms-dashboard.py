"""Phase 2A QA using synthetic records in an EMPTY, pre-provisioned disposable DB.

Requires SALAAM_CMS_CONFIG pointing at cms_phase2a_<hex>, the existing CMS table
structure, PHP, requests, beautifulsoup4; --browser also needs Playwright/Chromium.
Does not create/alter schema, run migrations/imports, use owner data or reset accounts.
All records it inserts are deleted in finally. Use only a dedicated QA instance.
"""
import argparse
import json
import os
from pathlib import Path
import re
import secrets
import subprocess
import time

import requests
from bs4 import BeautifulSoup

ROOT = Path(__file__).resolve().parents[1]
TABLES = ['cms_course_sessions', 'cms_courses', 'cms_articles', 'cms_users', 'cms_login_limits', 'cms_migrations']
WIDTHS = [1440, 1366, 1024, 768, 390, 320]
parser = argparse.ArgumentParser(description=__doc__)
parser.add_argument('--php', default=os.environ.get('PHP_BINARY', 'php'))
parser.add_argument('--port', type=int, default=8096)
parser.add_argument('--browser', action='store_true')
parser.add_argument('--chromium', default='/usr/bin/chromium')
args = parser.parse_args()
assert os.environ.get('SALAAM_CMS_CONFIG'), 'An explicit isolated QA configuration is required.'
QA = ROOT / 'storage/cms-qa/phase2a'
QA.mkdir(parents=True, exist_ok=True)
checks = []
server = None
log = None
owned = False
password = secrets.token_urlsafe(24)
email = 'phase2a-' + secrets.token_hex(6) + '@example.invalid'


def check(condition, label):
    assert condition, label
    checks.append(label)


def php(code, data=None):
    r = subprocess.run([args.php, '-r', "require 'includes/cms.php';" + code], cwd=ROOT,
                       input=json.dumps(data) if data is not None else '', capture_output=True, text=True)
    assert r.returncode == 0, 'Isolated PHP operation failed: ' + r.stderr
    return r.stdout


def db_state():
    return json.loads(php("$p=cmsDatabase();$out=[];foreach(" + json.dumps(TABLES) + " as $t){$out[$t]=['schema'=>$p->query('SHOW CREATE TABLE `'.$t.'`')->fetch(PDO::FETCH_NUM)[1],'rows'=>$p->query('SELECT * FROM `'.$t.'`')->fetchAll()];}echo json_encode($out);"))


def metrics(response):
    return {a.select_one('span').text: int(a.select_one('strong').text)
            for a in BeautifulSoup(response.text, 'html.parser').select('.overview-item')}


try:
    # Refuse an owner DB or any populated database before inserting even one fixture.
    database = php("$c=require getenv('SALAAM_CMS_CONFIG');echo $c['db']['database'];")
    check(bool(re.fullmatch(r'cms_phase2a_[0-9a-f]{12}', database)), 'explicit disposable database identity')
    baseline = db_state()
    check(all(not t['rows'] for t in baseline.values()), 'empty pre-provisioned QA tables; no owner data')
    owned = True
    php("$x=json_decode(stream_get_contents(STDIN),true);$p=cmsDatabase();$q=$p->prepare('INSERT INTO cms_users(email,display_name,password_hash) VALUES (?,?,?)');$q->execute([$x['email'],$x['name'],password_hash($x['password'],PASSWORD_BCRYPT,['cost'=>12])]);$q=$p->prepare('INSERT INTO cms_migrations(name,source_hash) VALUES (?,?)');$q->execute(['courses-v1',str_repeat('0',64)]);",
        {'email': email, 'name': 'Synthetic QA <script>unsafe()</script> ' + 'LongName' * 7, 'password': password})
    # Record existing table definitions, ignoring the insert-generated AUTO_INCREMENT counter.
    def structures():
        return {t: re.sub(r' AUTO_INCREMENT=\d+', '', v['schema']) for t, v in db_state().items()}
    schema_before = structures()
    router = QA / 'router.php'
    router.write_text("<?php $p=rawurldecode(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH));if(preg_match('~(?:^|/)\\.|^/(storage|database|includes|scripts|data|node_modules|docs)/|^/config~i',$p)){http_response_code(403);exit;}if($p==='/sitemap.xml'){require __DIR__.'/../../../sitemap.php';return true;}if($p==='/robots.txt'){require __DIR__.'/../../../robots.php';return true;}return false;", encoding='utf-8')
    log = (QA / 'http.log').open('w')
    server = subprocess.Popen([args.php, '-d', 'session.save_path=' + str(QA), '-S', '127.0.0.1:' + str(args.port), '-t', str(ROOT), str(router)], cwd=ROOT, stdout=log, stderr=log)
    base = 'http://127.0.0.1:' + str(args.port) + '/'
    s = requests.Session(); s.trust_env = False
    anon = requests.Session(); anon.trust_env = False
    for _ in range(30):
        if server.poll() is not None: raise AssertionError('Isolated PHP server exited; check QA log.')
        try:
            if anon.get(base + 'admin/login.php', timeout=1).status_code == 200: break
        except requests.RequestException: pass
        time.sleep(.1)
    def get(path, client=s): return client.get(base + path, timeout=20)
    def soup(r): return BeautifulSoup(r.text, 'html.parser')
    def token(r): return soup(r).select_one('input[name=csrf_token]')['value']
    routes = ['index.php', 'courses.php', 'course-edit.php', 'sessions.php', 'session-edit.php',
              'articles.php', 'article-edit.php', 'media.php', 'references.php']
    for path in routes:
        r = anon.get(base + 'admin/' + path, allow_redirects=False)
        check(r.status_code == 303 and r.headers['Location'] == 'login.php', 'anonymous guard ' + path)
    r = get('admin/login.php')
    check(s.post(base + 'admin/login.php', data={'email': email, 'password': password}).status_code == 403, 'login rejects missing CSRF')
    old_cookie = s.cookies.get('SALAAMADMIN')
    r = s.post(base + 'admin/login.php', data={'email': email, 'password': password, 'csrf_token': token(r)}, allow_redirects=False)
    check(r.status_code == 303 and old_cookie != s.cookies.get('SALAAMADMIN'), 'login and session rotation')
    r = get('admin/index.php')
    check(metrics(r) == {'Total courses': 0, 'Published courses': 0, 'Draft courses': 0,
                         'Total training sessions': 0, 'Upcoming for published courses': 0,
                         'Published articles': 0, 'Draft articles': 0}, 'empty dashboard has real zero counts')
    check(all(text in r.text for text in ['No courses yet.', 'No articles yet.', 'No training sessions yet.', 'Add your first course', 'Create your first article']), 'helpful empty states with creation actions')
    check('<script>unsafe()' not in r.text and '&lt;script&gt;' in r.text, 'account identity escaped')
    check(all(r.headers.get(k) == v for k,v in {'X-Robots-Tag':'noindex, nofollow', 'Cache-Control':'no-store, private', 'X-Frame-Options':'DENY'}.items()), 'private dashboard headers')
    check("script-src 'self'" in r.headers['Content-Security-Policy'], 'self-only script CSP preserved')
    php("require 'includes/course-store.php';$p=cmsDatabase();$q=$p->prepare('INSERT INTO cms_courses(id,slug,status,sort_order,featured,data,published_at,updated_at) VALUES (?,?,?,?,?,?,?,?)');for($i=1;$i<=27;$i++){$d=courseDefaults();$d['name']=$i===1?'Synthetic QA '.str_repeat('LongTitle',20).' <script>unsafe()</script>':'Synthetic QA Course '.$i;$d['category_slug']='information-technology';$d['image']='assets/images/courses/ccna.jpg';$d['image_alt']='Synthetic QA course image';$d['description']='Synthetic test description for isolated QA course '.$i.'.';$q->execute([$i,'synthetic-qa-course-'.$i,$i<=25?'published':'draft',$i,$i===1?1:0,json_encode($d),$i<=25?'2026-01-01 00:00:00':null,'2026-01-'.sprintf('%02d',min($i,27)).' 12:00:00']);}$q=$p->prepare('INSERT INTO cms_course_sessions(id,course_id,start_date,status) VALUES (?,?,?,?)');$sessionId=0;foreach([[1,courseToday(),'upcoming'],[1,(new DateTimeImmutable(courseToday()))->modify('+2 days')->format('Y-m-d'),'upcoming'],[1,(new DateTimeImmutable(courseToday()))->modify('-1 day')->format('Y-m-d'),'upcoming'],[26,(new DateTimeImmutable(courseToday()))->modify('+1 day')->format('Y-m-d'),'upcoming'],[1,null,'draft'],[2,null,'draft'],[2,'2026-01-01','completed'],[2,'2026-01-02','completed']] as $v)$q->execute([++$sessionId,...$v]);$q=$p->prepare('INSERT INTO cms_articles(id,title,slug,excerpt,content,status,published_at) VALUES (?,?,?,?,?,?,?)');foreach(['published','draft','draft'] as $i=>$status)$q->execute([$i+1,'Synthetic QA Article '.($i+1),'synthetic-qa-article-'.($i+1),'Synthetic QA article summary '.($i+1),json_encode([['type'=>'p','text'=>'Synthetic QA article body.']]),$status,$status==='published'?'2026-01-01 00:00:00':null]);")
    r = get('admin/index.php')
    check(r.status_code == 200 and metrics(r) == {'Total courses':27,'Published courses':25,'Draft courses':2,'Total training sessions':8,'Upcoming for published courses':2,'Published articles':1,'Draft articles':2}, 'dynamic mixed-state totals beyond original catalogue size')
    check(len(soup(r).select('.schedule-list li')) == 2, 'upcoming excludes past dates and draft courses; includes today')
    course_activity = soup(r).select('.dashboard-activity')[0]
    check([int(a['href'].split('=')[-1]) for a in course_activity.select('.recent-list a')] == [27,26,25,24], 'recent courses ordered by real update time')
    check(len(soup(r).select('.dashboard-activity')[1].select('.recent-list li')) == 3, 'recent articles include both admin statuses')
    for a in soup(r).select('.overview-item, .dashboard-actions a'):
        check(get('admin/' + a['href']).status_code == 200, 'dashboard destination ' + a['href'])
    # Snapshot records so all read-only layout/browser checks must leave content unchanged.
    populated = db_state()
    expected_sections = {'index.php':'Dashboard','courses.php':'Courses','course-edit.php':'Courses',
                         'sessions.php':'Training Sessions','session-edit.php':'Training Sessions',
                         'articles.php':'Articles','article-edit.php':'Articles','media.php':'Media','references.php':'Events & Partners'}
    for path in routes:
        r=get('admin/' + path)
        check(r.status_code == 200 and len(soup(r).select('h1')) == 1, 'authenticated page ' + path)
        check(soup(r).select_one('.admin-nav-list [aria-current=page]').text == expected_sections[path], 'active section ' + path)
        check(not soup(r).select('[name*=price], [name*=currency]'), 'no price/currency controls ' + path)
    for path,section in [('course-edit.php?id=1','Courses'),('course-preview.php?id=26','Courses'),('session-edit.php?id=1','Training Sessions'),('article-edit.php?id=1','Articles'),('preview.php?id=2','Articles')]:
        r=get('admin/'+path)
        check(r.status_code == 200 and soup(r).select_one('.admin-nav-list [aria-current=page]').text == section, 'existing editor/preview route ' + path)
    r=get('admin/index.php')
    check(anon.get(base+'course.php?slug=synthetic-qa-course-26').status_code==404, 'draft course stays private')
    check(anon.get(base+'article.php?slug=synthetic-qa-article-2').status_code==404, 'draft article stays private')
    sitemap=get('sitemap.xml',anon)
    check(sitemap.status_code==200 and 'synthetic-qa-course-1' in sitemap.text and 'synthetic-qa-course-26' not in sitemap.text and 'synthetic-qa-article-2' not in sitemap.text, 'sitemap synchronization and draft exclusion')
    public_routes=['index.php','about.php','courses.php','course.php?slug=synthetic-qa-course-2','categories.php','category.php?slug=information-technology','events.php','event.php?slug=administrative-management-training','partners.php','sc-business.php','contact.php','blog.php','article.php?slug=synthetic-qa-article-1']
    # Use the repository event slug rather than assuming a historical URL.
    public_routes[7]='event.php?slug='+json.loads(php("$e=require 'data/events.php';echo json_encode(array_values($e)[0]['slug']);"))
    for path in public_routes:
        r=get(path,anon)
        check(r.status_code==200 and soup(r).select_one('link[rel=canonical]') and soup(r).select_one('script[type="application/ld+json"]'), 'public page/SEO ' + path)
    for path in ['index.php','courses.php','course.php?slug=synthetic-qa-course-1']:
        doc=soup(get(path,anon))
        check(not doc.select('.course-price, .currency-control, [name*=price], [name*=currency]'), 'public course pricing absent ' + path)
        graphs=doc.select('script[type="application/ld+json"]')
        check(all(not re.search(r'"(?:price|priceCurrency|offers)"', x.text) for x in graphs), 'no pricing structured data ' + path)
    for path in ['config.example.php','storage/cms-qa/phase2a-config.php','database/cms.sql','scripts/cms-setup.php']:
        check(anon.get(base+path).status_code==403, 'private file denial ' + path)
    browser_results = []
    public_limits = []
    if args.browser:
        from playwright.sync_api import sync_playwright
        with sync_playwright() as pw:
            browser=pw.chromium.launch(executable_path=args.chromium,headless=True,args=['--no-sandbox'])
            context=browser.new_context()
            context.add_cookies([{'name': c.name,'value': c.value,'url':base,'httpOnly':True,'sameSite':'Strict'} for c in s.cookies])
            page=context.new_page(); errors=[]
            page.on('pageerror',lambda error: errors.append(str(error)))
            browser_routes=['admin/'+p for p in routes]+['admin/course-edit.php?id=1','admin/session-edit.php?id=1','admin/article-edit.php?id=1','admin/login.php']
            for width in WIDTHS:
                page.set_viewport_size({'width':width,'height':900 if width>768 else 844})
                for path in browser_routes:
                    response=page.goto(base+path);page.evaluate('document.fonts.ready.then(() => true)')
                    check(response.status==200,'browser HTTP '+str(width)+' '+path)
                    result=page.evaluate("""() => ({overflow:document.documentElement.scrollWidth>innerWidth+1,h1:document.querySelectorAll('h1').length,images:[...document.images].filter(i=>i.getBoundingClientRect().width>0).every(i=>i.complete&&i.naturalWidth>0),fonts:document.fonts.check('16px Poppins'),logo:(()=>{const i=document.querySelector('.admin-brand img'),r=i.getBoundingClientRect();return Math.abs(r.width/r.height-i.naturalWidth/i.naturalHeight)<.02})()})""")
                    check(not result['overflow'] and result['h1']==1 and result['images'] and result['fonts'] and result['logo'],'responsive layout/assets/logo '+str(width)+' '+path+' '+json.dumps(result))
                    check(not errors,'no JavaScript exceptions '+str(width)+' '+path)
                    if path=='admin/index.php':
                        page.screenshot(path=str(QA/f'dashboard-{width}.png'),full_page=True)
                        if width<=1024:
                            summary=page.locator('.admin-navigation summary');check(summary.is_visible(),'mobile menu visible '+str(width))
                            summary.focus();page.keyboard.press('Enter');check(page.locator('.admin-navigation').get_attribute('open') is not None,'keyboard menu opens '+str(width))
                            page.keyboard.press('Tab');check(page.evaluate('document.activeElement.closest("nav")!==null'),'keyboard enters menu '+str(width))
                            page.keyboard.press('Escape');check(page.locator('.admin-navigation').get_attribute('open') is None and summary.evaluate('(el)=>el===document.activeElement'),'Escape closes and restores focus '+str(width))
                            summary.click()
                        check(page.locator('.admin-nav-list [aria-current=page]').count()==1,'one visible active section '+str(width))
                        for selector in ['.admin-nav-list a','.admin-secondary button','.dashboard-actions a']:
                            check(all(h>=44 for h in page.locator(selector).evaluate_all('(els)=>els.map(el=>el.getBoundingClientRect().height)')),'comfortable touch targets '+str(width)+' '+selector)
                        button=page.locator('.admin-secondary button');button.focus()
                        check(button.evaluate('(el)=>parseFloat(getComputedStyle(el).outlineWidth)>=3'),'visible keyboard focus '+str(width))
                    browser_results.append({'width':width,'path':path,'passed':True})
            # Native details is still usable with JavaScript disabled.
            nojs=browser.new_context(java_script_enabled=False,viewport={'width':320,'height':844})
            nojs.add_cookies([{'name':c.name,'value':c.value,'url':base} for c in s.cookies])
            np=nojs.new_page();np.goto(base+'admin/index.php')
            check(np.locator('.admin-nav-list a').first.is_visible(),'navigation available without JavaScript')
            np.locator('.admin-navigation summary').click()
            check(not np.locator('.admin-nav-list a').first.is_visible(),'native mobile disclosure works without JavaScript')
            # Resize while focus is in navigation; no hidden focus remains.
            page.set_viewport_size({'width':1440,'height':900});page.goto(base+'admin/index.php');page.locator('.admin-nav-list a').first.focus()
            page.set_viewport_size({'width':390,'height':844});page.wait_for_timeout(100)
            check(page.locator('.admin-navigation summary').evaluate('(el)=>el===document.activeElement'),'resize returns focus before menu collapse')
            page.set_viewport_size({'width':1440,'height':900});page.wait_for_timeout(100)
            check(page.locator('.admin-nav-list [aria-current=page]').evaluate('(el)=>el===document.activeElement'),'resize restores focus to desktop active section '+str(page.evaluate('({tag:document.activeElement.tagName,classes:document.activeElement.className,open:document.querySelector("details").open})')))
            # Public pages use the same isolated synthetic database, never owner WAMP.
            # Keep the pathological unbroken-title stress outcome distinct from ordinary routes.
            for width in [1440,320]:
                page.set_viewport_size({'width':width,'height':844})
                page.goto(base+'course.php?slug=synthetic-qa-course-1');page.evaluate('document.fonts.ready.then(() => true)')
                stress=page.evaluate('({width:innerWidth,scrollWidth:document.documentElement.scrollWidth})')
                if stress['scrollWidth']>stress['width']+1:
                    public_limits.append({'check':'existing public course detail with 180-character unbroken synthetic title','width':width,'scrollWidth':stress['scrollWidth'],'outcome':'overflow; public templates/CSS unchanged by Phase 2A'})
            for width in WIDTHS:
                page.set_viewport_size({'width':width,'height':844})
                for path in public_routes:
                    page.goto(base+path);page.evaluate('document.fonts.ready.then(() => true)')
                    check(page.evaluate('document.documentElement.scrollWidth<=innerWidth+1'),'public overflow '+str(width)+' '+path)
                    check(not errors,'public JavaScript exceptions '+str(width)+' '+path)
            browser.close()
    check(db_state()==populated,'read-only layout/browser checks preserve all fixture rows')
    # Simulate missing catalogue readiness without changing schema or authentication.
    php("cmsDatabase()->exec(\"UPDATE cms_migrations SET name='qa-unavailable' WHERE name='courses-v1'\");")
    try:
        r=get('admin/index.php')
        check(r.status_code==503 and soup(r).select_one('[role=alert]') and not soup(r).select('.overview-item,.recent-list,.schedule-list'),'query/readiness error is not an empty dashboard')
        check('migration' not in soup(r).select_one('[role=alert]').text.lower() and 'PDOException' not in r.text,'safe setup failure message without instructions to migrate')
    finally: php("cmsDatabase()->exec(\"UPDATE cms_migrations SET name='courses-v1' WHERE name='qa-unavailable'\");")
    php("$p=cmsDatabase();$p->exec(\"INSERT INTO cms_courses(slug,status,sort_order,featured,data) SELECT 'synthetic-qa-extra','published',28,0,data FROM cms_courses LIMIT 1\");")
    r=get('admin/index.php')
    check(metrics(r)['Total courses']==28 and metrics(r)['Published courses']==26,'counts adapt to a later course without code changes')
    check(get('admin/logout.php').status_code==405,'logout requires POST')
    check(s.post(base+'admin/logout.php',data={}).status_code==403,'logout missing CSRF rejected')
    authenticated=s.cookies.get('SALAAMADMIN')
    r=s.post(base+'admin/logout.php',data={'csrf_token':token(get('admin/index.php'))},allow_redirects=False)
    check(r.status_code==303,'valid CSRF logout')
    replay=requests.Session();replay.trust_env=False;replay.cookies.set('SALAAMADMIN',authenticated)
    check(replay.get(base+'admin/index.php',allow_redirects=False).status_code==303,'logout invalidates replayed session')
    check(structures()==schema_before,'existing table structures unchanged')
    report={'passed':len(checks),'checks':checks,'admin_browser_combinations':len(browser_results),'public_browser_combinations':len(public_routes)*len(WIDTHS) if args.browser else 0,'browser_exceptions':0 if args.browser else None,'browser_tests':args.browser,'public_stress_limitations':public_limits}
    (QA/'results.json').write_text(json.dumps(report,indent=2),encoding='utf-8')
    print(json.dumps({k:v for k,v in report.items() if k!='checks'}))
finally:
    if server is not None:
        server.terminate();server.wait(timeout=10)
    if log is not None: log.close()
    if owned:
        # The preflight guarantees these were empty dedicated tables. Delete only our QA data.
        php("$p=cmsDatabase();foreach("+json.dumps(TABLES)+" as $t)$p->exec('DELETE FROM `'.$t.'`');")
