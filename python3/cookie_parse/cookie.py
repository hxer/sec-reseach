# -*- coding: utf-8 -*-

from http import cookies

# ']' or '['
evail_char = '\x09'
evail_cookie = 'a=123{}b=321'.format(evail_char)

c = cookies.SimpleCookie()
c.load(evail_cookie)

print(c.output())