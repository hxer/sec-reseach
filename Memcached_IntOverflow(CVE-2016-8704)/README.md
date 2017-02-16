## Memcached 命令执行漏洞

```
CVE-2016-8704
CVE-2016-8705
CVE-2016-8706
```

* [http://blog.talosintel.com/2016/10/memcached-vulnerabilities.html](http://blog.talosintel.com/2016/10/memcached-vulnerabilities.html)
* [http://paper.seebug.org/95/](http://paper.seebug.org/95/)

## info

memcached 版本`<1.4.33`, 存在三个整数溢出漏洞，该漏洞可触发堆溢出造成远程代码执行。

## docker

```
docker pull janes/memcached:1.4.31_IntOverflow
docker run -d -p 11211:11211 janes/memcached:1.4.31_IntOverflow
```

## poc

* CVE-2016-8704

`python poc_2016_8704.py 127.0.0.1 11211`