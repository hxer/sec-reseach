## WEBLOGIC 密码破解

老版Weblogic采用3DES加密，新版采用AES加密。读weblogic的`config.xml`文件可以读到用户名和加密后的密码，解密该密码就可以登录weblogic后台。

如果能获取到weblogic的`SerializedSystemIni.dat`文件，可以利用工具[WebLogicPasswordDecryptor](https://github.com/NetSPI/WebLogicPasswordDecryptor/)进行解密

## WebLogic 中的"域"

域环境下可以多个 WebLogic Server 或者 WebLogic Server 群集。域是由单个管理 服务器管理的 WebLogic Server 实例的集合。

Weblogic10++域默认是安装完成后由用 户创建。帐号密码也在创建域的时候设置，所以这里并不存在默认密码。当一个域创建完 成后配置文件和 Web 应用在:Weblogic12/user_projects/domains”域名”。而 Weblogic 12c 采用了 AES 对称加密方式，但是 AES 的 key 并不在这文件里面。

默认 的管理密码文件存放于:
`Weblogic12/user_projects/domains/base_domain/servers/AdminServer/security/boot.properties`(base_domain 是默认的”域名”)
另外 weblogic 还有/u01/app/oracle 这种目录 `/u01/app/oracle/user_projects/domains/base_domain/servers/AdminServer/security/boot.properties`

## usage of WebLogicPasswordDecryptor

下面用环境以mac为例，`java jdk 1.8.0_112-b16`, 下载[bcprov-ext-jdk15on-155.jar](http://www.bouncycastle.org/latest_releases.html), 将其移动到，目录`/Library/Java/JavaVirtualMachines/jdk1.8.0_112.jdk/Contents/Home/jre/lib/ext`， 然后修改`/Library/Java/JavaVirtualMachines/jdk1.8.0_112.jdk/Contents/Home/jre/lib/ecurity/java.security`文件，增加一行如下

```
#
# List of providers and their preference orders (see above):
#
security.provider.1=sun.security.provider.Sun
security.provider.2=sun.security.rsa.SunRsaSign
security.provider.3=sun.security.ec.SunEC
security.provider.4=com.sun.net.ssl.internal.ssl.Provider
security.provider.5=com.sun.crypto.provider.SunJCE
security.provider.6=sun.security.jgss.SunProvider
security.provider.7=com.sun.security.sasl.Provider
security.provider.8=org.jcp.xml.dsig.internal.dom.XMLDSigRI
security.provider.9=sun.security.smartcardio.SunPCSC
security.provider.10=apple.security.AppleProvider

# add
security.provider.11=org.bouncycastle.jce.provider.BouncyCastleProvider
```

转到`WebLogicPasswordDecryptor`目录下,

```
# 生成 WebLogicPasswordDecryptor.class
$ javac WebLogicPasswordDecryptor.java

# 解密
# cliper_data, eg "{AES}8/rTjIuC4mwlrlZgJK++LKmAThcoJMHyigbcJGIztug=", "{3DES}JMRazF/vClP1WAgy1czd2Q=="
$ java WebLogicPasswordDecryptor "some_path/SerializedSystemIni.dat" "cliper_data"
```
### 加密方式

通过修改`SerializedSystemIni.dat`文件的第六个byte可以更换加密方式。

* 当字符为`02`时，启用AES加密：
![](aes.png)
* 当修改为`01`时，启用3DES加密：
![](3des.png)

## 利用

破解weblogic密码需要获取`config.xml`文件和`SerializedSystemIni.dat`文件。

`config.xml`位于[DOMAIN_NAME/config/config.xml 路径](https://docs.oracle.com/cd/E13222_01/wls/docs90/domain_config/config_files.html), 而[weblogic 12.2.12 默认的 Domain home 路径为`ORACLE_HOME/user_projects/domains/DOMAIN_NAME`](http://docs.oracle.com/middleware/12212/lcm/BTWOB/GUID-16F78BFD-4095-45EE-9C3B-DB49AD5CBAAD.htm#GUID-DEB0CBA4-D19D-4AB8-A43F-48EFDC3B00F9)

剩下的问题就是获取`DOMAIN_NAME`, 官方默认的安装目录是`/u01/oracle/middleware/user_projects/domains/base_domain` 或 `/u01/oracle/user_projects/domains/base_domain`. 于是`config.xml`路径为

```
/u01/oracle/middleware/user_projects/domains/base_domain/config/config.xml
/u01/oracle/user_projects/domains/base_domain/config/config.xml
```

秘钥文件`SerializedSystemIni.dat`文件位于`DOMAIN_NAME/security/SerializedSystemIni.dat`路径

## Ref

* [解密WebLogic的密码](http://bobao.360.cn/learning/detail/337.html)
