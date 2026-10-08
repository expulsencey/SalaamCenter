"""Optional read-only QA against a locally started Chromium CDP endpoint on port 9223.
Requires requests and websocket-client. Never submits the contact form.
"""
import base64
import json
from pathlib import Path
import time
import requests
import websocket

session = requests.Session()
session.trust_env = False
target = session.get('http://127.0.0.1:9223/json', timeout=3).json()[0]
ws = websocket.create_connection(target['webSocketDebuggerUrl'], origin='http://127.0.0.1:9223', timeout=15)
counter = 0
errors = []


def call(method, params=None):
    global counter
    counter += 1
    ws.send(json.dumps({'id': counter, 'method': method, 'params': params or {}}))
    while True:
        result = json.loads(ws.recv())
        if result.get('method') == 'Runtime.exceptionThrown':
            errors.append(result['params']['exceptionDetails'].get('text'))
        if result.get('id') == counter:
            assert 'error' not in result, result
            return result.get('result', {})


def evaluate(expression):
    result = call('Runtime.evaluate', {'expression': expression, 'returnByValue': True})
    assert 'exceptionDetails' not in result, result
    return result.get('result', {}).get('value')


def navigate(route):
    call('Page.navigate', {'url': 'http://localhost/SalaamCenter/' + route})
    for _ in range(50):
        time.sleep(.1)
        if evaluate('document.readyState') == 'complete':
            break


call('Page.enable')
call('Runtime.enable')
routes = ['index.php', 'about.php', 'courses.php', 'categories.php', 'events.php', 'partners.php', 'contact.php',
    'sc-business.php', 'blog.php', 'course.php?slug=financial-analysis', 'category.php?slug=information-technology',
    'event.php?slug=strategic-commercial-development-ports']
results = []
for width, height in [(1920, 1080), (1440, 900), (1366, 768), (1024, 768), (768, 1024), (390, 844)]:
    call('Emulation.setDeviceMetricsOverride', {'width': width, 'height': height, 'deviceScaleFactor': 1, 'mobile': False})
    for route in routes:
        navigate(route)
        metrics = evaluate('''JSON.parse(JSON.stringify({width: innerWidth, scroll: document.documentElement.scrollWidth,
            h1: document.querySelectorAll('h1').length, font: getComputedStyle(document.body).fontSize,
            broken: [...document.images].filter(i => i.complete && i.naturalWidth === 0).map(i => i.getAttribute('src'))}))''')
        assert metrics['scroll'] <= width + 1 and metrics['h1'] == 1 and not metrics['broken'], (route, width, metrics)
        results.append([route, width])
navigate('index.php')
evaluate("document.querySelector('.menu-toggle').click()")
assert evaluate("document.querySelector('.menu-toggle').getAttribute('aria-expanded')") == 'true'
evaluate("document.querySelector('.courses-menu summary').click()")
assert evaluate("document.querySelector('.courses-menu').open") is True
evaluate("document.querySelector('.menu-toggle').click()")
assert evaluate("document.querySelector('.menu-toggle').getAttribute('aria-expanded')") == 'false'
assert evaluate("document.querySelectorAll('#display-currency,[data-course-price],.course-price').length") == 0
before = evaluate("document.querySelector('.slide.is-active img').getAttribute('src')")
time.sleep(6)
assert before != evaluate("document.querySelector('.slide.is-active img').getAttribute('src')")
shot = call('Page.captureScreenshot', {'format': 'png'})
Path('.seo-browser.tmp/mobile.png').write_bytes(base64.b64decode(shot['data']))
navigate('about.php')
before = evaluate("document.querySelector('.about-slide.is-current').getAttribute('src')")
time.sleep(6)  # Allow the existing 4-second interval and 1.2-second dissolve to finish.
assert before != evaluate("document.querySelector('.about-slide.is-current').getAttribute('src')")
call('Emulation.setEmulatedMedia', {'features': [{'name': 'prefers-reduced-motion', 'value': 'reduce'}]})
time.sleep(1.5)
before = evaluate("document.querySelector('.about-slide.is-current').getAttribute('src')")
time.sleep(5)
assert before == evaluate("document.querySelector('.about-slide.is-current').getAttribute('src')")
for route in ['event.php?slug=strategic-commercial-development-ports', 'event.php?slug=administrative-management-training']:
    navigate(route)
    evaluate("document.querySelector('video').load()")
    for _ in range(30):
        if evaluate("document.querySelector('video').readyState >= 1"): break
        time.sleep(.2)
    assert evaluate("!document.querySelector('video').error && document.querySelector('video').videoWidth > 0"), route
assert not errors, errors
print(json.dumps({'responsive_page_checks': len(results), 'widths': [1920, 1440, 1366, 1024, 768, 390], 'javascript_exceptions': errors,
    'event_videos': 'metadata loaded without media error',
    'mobile_menu': 'passed', 'courses_dropdown': 'passed', 'course_pricing_absent': 'passed',
    'hero_autoplay': 'passed', 'about_autoplay_and_reduced_motion': 'passed'}))
call('Browser.close')
