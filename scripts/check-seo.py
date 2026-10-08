"""Read-only local SEO QA. Requires Python requests and beautifulsoup4; no form submissions."""
import collections
import json
from pathlib import Path
import re
import sys
import urllib.parse as url
import xml.etree.ElementTree as ET

import requests
from bs4 import BeautifulSoup

BASE = sys.argv[1] if len(sys.argv) > 1 else 'http://localhost/SalaamCenter/'
session = requests.Session()
session.trust_env = False
cache = {}


def get(path):
    address = url.urljoin(BASE, path)
    if address not in cache:
        cache[address] = session.get(address, timeout=20)
    return cache[address]


def soup(response):
    return BeautifulSoup(response.content.decode('utf-8', errors='strict'), 'html.parser')


sitemap = get('sitemap.xml')
assert sitemap.status_code == 200
locations = [node.text for node in ET.fromstring(sitemap.content).findall('{*}url/{*}loc')]
assert len(locations) == len(set(locations)) >= 1
public = soup(get('index.php')).select_one('link[rel=canonical]')['href'].rstrip('/')
routes = {address.removeprefix(public + '/') or 'index.php': address for address in locations}
variant_counts = {}
for detail, listing, expected in [('course', 'courses.php', None), ('category', 'categories.php', 6), ('event', 'events.php', 2)]:
    listed = {a['href'] for a in soup(get(listing)).select('a[href]') if a['href'].startswith(detail + '.php?slug=')}
    exported = {route for route in routes if route.startswith(detail + '.php?slug=')}
    assert listed == exported and (expected is None or len(listed) == expected), detail
    variant_counts[detail + '_pages'] = len(listed)
titles, descriptions, images, schemas, links = set(), set(), set(), collections.Counter(), set()
for route, canonical in routes.items():
    response = get(route)
    assert response.status_code == 200, route
    doc = soup(response)
    assert doc.html['lang'] == 'en' and doc.select_one('meta[charset]')['charset'].upper() == 'UTF-8'
    assert len(doc.select('h1')) == 1, route
    levels = [int(h.name[1]) for h in doc.select('main h1, main h2, main h3, main h4, main h5, main h6')]
    assert all(b <= a + 1 for a, b in zip(levels, levels[1:])), (route, levels)
    title = doc.title.get_text()
    description = doc.select_one('meta[name=description]')['content']
    assert title and description and title not in titles and description not in descriptions, route
    titles.add(title)
    descriptions.add(description)
    assert doc.select_one('link[rel=canonical]')['href'] == canonical, route
    assert doc.select_one('meta[property="og:url"]')['content'] == canonical
    assert doc.select_one('meta[property="og:title"]')['content'] == title
    assert doc.select_one('meta[property="og:description"]')['content'] == description
    assert doc.select_one('meta[name=robots]')['content'] == 'index, follow'
    head = str(doc.head)
    for bad in ['localhost', '127.0.0.1', 'C:\\wamp64', 'trycloudflare', '\ufffd', 'Ã', 'Â', 'â€']:
        assert bad not in head, (route, bad)
    assert not any(bad in head.lower() for bad in ['lorem ipsum', 'placeholder', 'fake ratings', 'fake reviews'])
    assert not any(bad in response.text for bad in ['\ufffd', 'Ã', 'Â', 'â€']), route
    assert not re.search(r'(?:Warning|Fatal error|Notice):', response.text), route
    graph = json.loads(doc.select_one('script[type="application/ld+json"]').string)['@graph']
    for entry in graph:
        schemas[entry['@type']] += 1
        assert 'aggregateRating' not in entry and 'review' not in entry
    for image in doc.select('img'):
        assert 'alt' in image.attrs
        images.add(image['src'])
    social_image = doc.select_one('meta[property="og:image"]')['content']
    images.add(social_image.removeprefix(public + '/'))
    for element, attribute in [('a', 'href'), ('script', 'src'), ('link[rel=stylesheet]', 'href'), ('source', 'src')]:
        for item in doc.select(element + '[' + attribute + ']'):
            target = item[attribute]
            if not url.urlsplit(target).scheme and not target.startswith('//'):
                links.add(target)

for target in images | links:
    path, fragment = url.urldefrag(target)
    if not path:
        continue
    response = get(path)
    if path == 'blog.php' and response.status_code == 503:
        assert soup(response).select_one('meta[name=robots]')['content'] == 'noindex, follow'
        continue  # CMS setup/unavailability is checked separately, not a legacy-page failure.
    assert response.status_code == 200, (target, response.status_code)
    if target in images:
        assert response.headers.get('Content-Type', '').startswith('image/'), target
    if fragment:
        assert soup(response).find(id=url.unquote(fragment)), target

for kind in ['course', 'category', 'event']:
    for query in ['', '?slug=not-a-real-record', '?slug[]=bad']:
        response = get(kind + '.php' + query)
        doc = soup(response)
        assert response.status_code == 404
        assert doc.select_one('meta[name=robots]')['content'] == 'noindex, follow'
        assert not doc.select('link[rel=canonical], script[type="application/ld+json"]')

for route in ['index.php?utm_source=test', 'contact.php?space=training-room', 'course.php?slug=financial-analysis&utm_source=test']:
    clean = route.split('?')[0] if 'course.php' not in route else 'course.php?slug=financial-analysis'
    assert soup(get(route)).select_one('link[rel=canonical]')['href'] == soup(get(clean)).select_one('link[rel=canonical]')['href']
robots = get('robots.txt')
assert robots.status_code == 200 and 'Disallow: /' not in robots.text
assert 'Sitemap: ' + public + '/sitemap.xml' in robots.text
root = Path(__file__).resolve().parent.parent
for path in [*root.glob('*.php'), *root.glob('includes/*.php'), *root.glob('data/*.php')]:
    if path.name == 'config.local.php':
        continue
    raw = path.read_bytes()
    assert not raw.startswith(b'\xef\xbb\xbf'), path
    text = raw.decode('utf-8', errors='strict')
    assert not any(bad in text for bad in ['\ufffd', 'Ã', 'Â', 'â€']), path
print(json.dumps({'pages': len(routes), 'unique_titles': len(titles), 'unique_descriptions': len(descriptions),
    **variant_counts,
    'unique_image_paths': len(images), 'local_link_and_asset_targets': len(links), 'schemas': schemas,
    'invalid_routes_checked': 9, 'canonical_parameter_checks': 3, 'sitemap_urls': len(locations)}, indent=2))
