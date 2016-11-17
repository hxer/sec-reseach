## python3.4.0 `http.cookies`

`cookies.SimpleCookie()` 的 `load()` 方法可以从字符串中解析出 `cookie`, 内部使用正则匹配，如下

```python
#
# Pattern for finding cookie
#
# This used to be strict parsing based on the RFC2109 and RFC2068
# specifications.  I have since discovered that MSIE 3.0x doesn't
# follow the character rules outlined in those specs.  As a
# result, the parsing rules here are less strict.
#

_LegalCharsPatt  = r"[\w\d!#%&'~_`><@,:/\$\*\+\-\.\^\|\)\(\?\}\{\=]"
_CookiePattern = re.compile(r"""
    (?x)                           # This is a verbose pattern
    (?P<key>                       # Start of group 'key'
    """ + _LegalCharsPatt + r"""+?   # Any word of at least one letter
    )                              # End of group 'key'
    (                              # Optional group: there may not be a value.
    \s*=\s*                          # Equal Sign
    (?P<val>                         # Start of group 'val'
    "(?:[^\\"]|\\.)*"                  # Any doublequoted string
    |                                  # or
    \w{3},\s[\w\d\s-]{9,11}\s[\d:]{8}\sGMT  # Special case for "expires" attr
    |                                  # or
    """ + _LegalCharsPatt + r"""*      # Any word or empty string
    )                                # End of group 'val'
    )?                             # End of optional value group
    \s*                            # Any number of spaces.
    (\s+|;|$)                      # Ending either at space, semicolon, or EOS.
    """, re.ASCII)                 # May be removed if safe.


def load(self, rawdata):
        """Load cookies from a string (presumably HTTP_COOKIE) or
        from a dictionary.  Loading cookies from a dictionary 'd'
        is equivalent to calling:
            map(Cookie.__setitem__, d.keys(), d.values())
        """
        if isinstance(rawdata, str):
            self.__parse_string(rawdata)
        else:
            # self.update() wouldn't call our custom __setitem__
            for key, value in rawdata.items():
                self[key] = value
        return

    def __parse_string(self, str, patt=_CookiePattern):
        i = 0            # Our starting point
        n = len(str)     # Length of string
        M = None         # current morsel

        while 0 <= i < n:
            # Start looking for a cookie
            match = patt.search(str, i)
            if not match:
                # No more cookies
                break

            key, value = match.group("key"), match.group("val")
            i = match.end(0)
            ......
```

从以上代码可以看出 `_LegalCharsPatt` 字符集并不包含 `[, ]` 这两个字符, 可以在字符串中使用 `[` or `]` 做为边界字符，截断cookie，如下所示

```python
from http import cookies

# ']' or '['
evail_char = ']'
evail_cookie = 'a=123{}b=321'.format(evail_char)

c = cookies.SimpleCookie()
c.load(evail_cookie)

print(c.output())

>>> Set-Cookie: b=321
```

## fix `_LegalCharsPatt`

```
_LegalKeyChars  = r"\w\d!#%&'~_`><@,:/\$\*\+\-\.\^\|\)\(\?\}\{\="
_LegalValueChars = _LegalKeyChars + '\[\]'
```

