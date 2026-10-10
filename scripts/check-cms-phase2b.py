"""Phase 2B scenarios using the existing isolated deletion harness.
Run: python scripts/check-course-deletion.py --phase2b --browser
No writes to real course/session records.
"""
import json
import re
import time
import base64


def run(qa):
    check, form, save, get, soup = (qa[k] for k in ['check', 'form', 'save', 'get', 'soup'])
    rows, php, anon, root = (qa[k] for k in ['rows', 'php', 'anon', 'ROOT'])
    path = 'admin/course-edit.php'
    data = form(path)
    data.update(name='Phase2B Fran\u00e7ais', title_language='fr', category_slug='',
                subtitle='Saved subtitle', description='Verified synthetic description',
                duration='3 days', language='French', format='Classroom', level='Advanced',
                image='assets/images/courses/power-bi.png', image_alt='Synthetic image',
                sort_order='12', featured='on', action='draft')
    for key in ['outcomes', 'includes', 'certificate', 'requirements', 'exam']:
        data[key+'[0]'] = key+' premi\u00e8re ligne'
        data[key+'[1]'] = key+' second item'
    response = save(path, data)
    doc = soup(response)
    check(response.status_code == 422, '2B invalid category rejected')
    check(doc.select_one('[name="exam[1]"]').text == data['exam[1]'], '2B later repeatable survives early validation error')
    check(doc.select_one('[name=language]')['value'] == 'French' and doc.select_one('[name=featured]').has_attr('checked'), '2B later scalar and checkbox survive error')
    data['category_slug'] = 'information-technology'
    response = save(path, data)
    check(response.status_code == 303, '2B draft creation')
    cid = int(re.search(r'id=(\d+)', response.headers['Location'])[1]); path += '?id='+str(cid)
    record = lambda: next(r for r in rows() if r['id'] == cid)
    contents = json.loads(record()['data'])
    check(all(contents[k] == data[k] for k in ['name','title_language','category_slug','subtitle','description','duration','language','format','level','image','image_alt']), '2B all scalar fields persisted')
    check(all(contents[k] == [data[k+'[0]'],data[k+'[1]']] for k in ['outcomes','includes','certificate','requirements','exam']), '2B all five repeatable lists persisted')
    check(record()['sort_order'] == 12 and record()['featured'] == 1, '2B presentation persisted')
    check(save('admin/course-edit.php', data).status_code == 303 and len(rows()) == 1, '2B replayed creation creates no duplicate')
    slug = record()['slug']
    check(get('course.php?slug='+slug, anon).status_code == 404 and slug not in get('sitemap.php',anon).text, '2B draft private')
    # A generic validation error used to reset all fields following the failed field.
    invalid = dict(form(path), action='draft', language='x'*101, level='Keep this level')
    invalid['exam[0]'] = 'Keep this evaluation'
    response = save(path,invalid); doc=soup(response)
    check(response.status_code == 422 and 'Language:' in response.text, '2B field-specific length error')
    check(doc.select_one('[name=language]')['value'] == 'x'*101 and doc.select_one('[name=level]')['value']=='Keep this level' and doc.select_one('[name="exam[0]"]').text=='Keep this evaluation', '2B entered invalid and later values retained')
    # Genuine upload, then switch selection and fail validation: old upload must not win.
    upload = {'upload':('qa.png',(root/'assets/images/courses/power-bi.png').read_bytes(),'image/png')}
    check(save(path,dict(form(path),action='draft'),upload).status_code == 303, '2B image upload saved')
    media=record()['featured_image']; qa['uploaded'].add(media)
    response=save(path,dict(form(path),name='',image='assets/images/courses/power-bi.png',action='draft'))
    check(soup(response).select_one('[name=image] option[selected]')['value']=='assets/images/courses/power-bi.png', '2B new image selection retained after failed edit')
    response=save(path,dict(form(path),image_alt='',action='publish'),upload)
    check(response.status_code==422 and 'select the upload again' in response.text, '2B failed upload form explains file reselection')
    check(record()['featured_image']==media and (root/'storage/cms-media'/media).is_file(), '2B saved upload preserved on failed publication')
    old=form(path)
    check(save(path,dict(old,action='publish')).status_code==303, '2B publish')
    response=save(path,dict(old,action='draft',subtitle='Stale submitted text'))
    check(response.status_code==422 and soup(response).select_one('[name=version]')['value']==old['version'] and soup(response).select_one('[name=subtitle]')['value']=='Stale submitted text', '2B stale retry retains text and original version')
    check(get('course.php?slug='+slug,anon).status_code==200 and slug in get('sitemap.php',anon).text, '2B publication public synchronization')
    check(not soup(get('course.php?slug='+slug,anon)).select('[data-course-price],.course-price,#display-currency'), '2B no public prices')
    session=form('admin/session-edit.php'); session.update(course_id=str(cid),start_date='2099-02-01',duration='2 days',language='French',status='upcoming')
    response=save('admin/session-edit.php',dict(session,course_id='999999'))
    check(response.status_code==422 and soup(response).select_one('[name=duration]')['value']=='2 days' and soup(response).select_one('[name=start_date]')['value']=='2099-02-01', '2B session error retains entered fields')
    response=save('admin/session-edit.php',session); check(response.status_code==303, '2B session creation')
    sid=int(re.search(r'id=(\d+)',response.headers['Location'])[1]); sp='admin/session-edit.php?id='+str(sid)
    check(save('admin/session-edit.php',session).status_code==303 and php("require 'includes/cms.php';echo cmsDatabase()->query('SELECT COUNT(*) FROM cms_course_sessions')->fetchColumn();")=='1', '2B session replay creates no duplicate')
    for status in ['completed','draft','upcoming']:
        check(save(sp,dict(form(sp),status=status,duration='4 days')).status_code==303, '2B session status '+status)
    check(form(sp)['duration']=='4 days' and form(sp)['language']=='French', '2B session metadata persisted')
    before_session=php("require 'includes/cms.php';echo json_encode(cmsDatabase()->query('SELECT * FROM cms_course_sessions')->fetchAll());")
    check(save(path,dict(form(path),action='draft')).status_code==303, '2B hide published course')
    check(get('course.php?slug='+slug,anon).status_code==404 and slug not in get('sitemap.php',anon).text and slug not in get('courses.php',anon).text, '2B hidden course absent public listings')
    check(php("require 'includes/cms.php';echo json_encode(cmsDatabase()->query('SELECT * FROM cms_course_sessions')->fetchAll());")==before_session and record()['featured_image']==media, '2B hide preserves sessions and images')
    check(save(path,dict(form(path),action='publish')).status_code==303 and get('course.php?slug='+slug,anon).status_code==200, '2B republish')
    for status,count in [('published',1),('draft',0)]:
        check(len(soup(get('admin/courses.php?status='+status)).select('.article-list li'))==count, '2B existing status filter '+status)
    if '--browser' in qa['sys'].argv:
        browser(qa,path)
    # Remove only this scenario's synthetic records in the isolated database.
    php("require 'includes/cms.php';$p=cmsDatabase();$p->prepare('DELETE FROM cms_course_sessions WHERE id=?')->execute(["+str(sid)+"]);")
    check(save('admin/course-delete.php',qa['deletion'](record())).status_code==303, '2B existing deletion after editor changes')


def browser(qa,path):
    import websocket
    check=qa['check'];s=qa['s'];base=qa['base']
    target=s.get('http://127.0.0.1:9225/json').json()[0]
    ws=websocket.create_connection(target['webSocketDebuggerUrl'],origin='http://127.0.0.1:9225',timeout=20)
    seq=0;errors=[]
    def call(method,params=None):
        nonlocal seq
        seq+=1;ws.send(json.dumps({'id':seq,'method':method,'params':params or {}}))
        while True:
            msg=json.loads(ws.recv())
            if msg.get('method')=='Runtime.exceptionThrown':errors.append(msg)
            if msg.get('id')==seq:return msg.get('result',{})
    def ev(code):return call('Runtime.evaluate',{'expression':code,'returnByValue':True})['result'].get('value')
    call('Page.enable');call('Runtime.enable');call('Network.enable')
    call('Network.setCookie',{'name':'SALAAMADMIN','value':s.cookies.get('SALAAMADMIN'),'url':base,'httpOnly':True,'sameSite':'Strict'})
    call('Page.navigate',{'url':base+path});time.sleep(.7)
    check(ev('document.querySelectorAll(".course-steps button").length===5 && document.querySelectorAll("[data-course-step]:not([hidden])").length===1'), '2B five-step editor initialized')
    for width,height in [(1440,900),(1366,768),(768,1024),(390,844)]:
        call('Emulation.setDeviceMetricsOverride',{'width':width,'height':height,'deviceScaleFactor':1,'mobile':False})
        for i in range(5):
            ev(f'document.querySelectorAll(".course-steps button")[{i}].click()')
            check(ev('document.documentElement.scrollWidth<=innerWidth+1 && document.querySelectorAll("[data-course-step]:not([hidden])").length===1'),f'2B step {i+1} fits {width}')
    check(ev('document.querySelector("[data-course-review]").textContent.includes("Saved subtitle")'), '2B review shows actual entered values')
    ev('document.querySelectorAll(".course-steps button")[0].click();document.querySelector(".wizard-navigation button:last-child").click()')
    check(ev('document.querySelector("[aria-current=step]").textContent.startsWith("2.")'), '2B Next advances')
    ev('document.querySelector(".wizard-navigation button:first-child").click()')
    check(ev('document.querySelector("[aria-current=step]").textContent.startsWith("1.")'), '2B Previous returns')
    ev('document.querySelectorAll(".course-steps button")[2].click();document.querySelector("[data-add-item]").click();let a=document.querySelectorAll("[name^=\\\"outcomes[\\\"]");a[a.length-1].value="Browser repeatable item";document.querySelector("[name=subtitle]").value="Browser saved subtitle";document.querySelector("[name=action][value=publish]").click()')
    time.sleep(.6)
    check(ev('document.body.textContent.includes("Course published") && document.querySelector("[name=subtitle]").value==="Browser saved subtitle"'), '2B browser submit preserves publication action and hidden-step values')
    check(ev('[...document.querySelectorAll("textarea")].some(a=>a.value==="Browser repeatable item")'), '2B browser-added repeatable persists')
    ev('document.querySelector("[name=image_alt]").value="";document.querySelectorAll(".course-steps button")[4].click();document.querySelector("[name=action][value=publish]").click()')
    check(ev('document.activeElement.name==="image_alt" && !document.querySelector("#course-image").hidden'), '2B invalid hidden field reveals its step')
    ev('document.querySelector("[name=image_alt]").value="Browser image description";document.querySelectorAll(".course-steps button")[4].click()')
    (qa['QA']/'phase2b-mobile.png').write_bytes(base64.b64decode(call('Page.captureScreenshot',{'format':'png'})['data']))
    check(not errors,'2B no JavaScript exceptions');ws.close()
