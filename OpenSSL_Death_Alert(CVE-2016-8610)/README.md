## opessl death alert

在OpenSSL针对SSL/TLS协议握手过程的实现中，允许客户端重复发送打包的 "SSL3_RT_ALERT" -> "SSL3_AL_WARNING" 类型明文`未定义警告包`，且OpenSSL在实现中遇到`未定义警告包`时仍选择忽略并继续处理接下来的通信内容（如果有的话）。攻击者可以容易的利用该缺陷在一个消息中打包大量`未定义类型警告包`，使服务或进程陷入无意义的循环，从而导致占用掉服务或进程100%的CPU使用率。(by [360 CVE-2016-8610: “SSL Death Alert“漏洞公告](http://bobao.360.cn/learning/detail/3137.html))

个人理解: 

大致原理就是SSL未做接收Alert Message数据的限制处理，而持续处理收到的Alert Message会占用很高的cpu，也就造成的拒绝服务攻击，利用起来并不复杂，客户端完成握手协议第一步以后，持续发Alert Message，就可以造成拒绝服务攻击了.

而发送的Alert Message既可以是`未定义的警告包`, 也可以是定义的 `Warning` not `Fatal`, 如RFC中描述的no_certificate_RESERVED(41),bad_certificate(42)等。

![](openssl.png)

## Alert Message 

根据 [RFC5246](https://tools.ietf.org/html/rfc5246) 文档中 SSL ALERT MESSAGE 定义格式如下：

```
Alert Messages

   enum { warning(1), fatal(2), (255) } AlertLevel;

   enum {
       close_notify(0),
       unexpected_message(10),
       bad_record_mac(20),
       decryption_failed_RESERVED(21),
       record_overflow(22),
       decompression_failure(30),
       handshake_failure(40),
       no_certificate_RESERVED(41),
       bad_certificate(42),
       unsupported_certificate(43),
       certificate_revoked(44),
       certificate_expired(45),
       certificate_unknown(46),
       illegal_parameter(47),
       unknown_ca(48),
       access_denied(49),
       decode_error(50),
       decrypt_error(51),
       export_restriction_RESERVED(60),
       protocol_version(70),
       insufficient_security(71),
       internal_error(80),
       user_canceled(90),
       no_renegotiation(100),
       unsupported_extension(110),           /* new */
       (255)
   } AlertDescription;

   struct {
       AlertLevel level;
       AlertDescription description;
   } Alert;
```

未在 `AlertDescription` 中定义的Alert均为 `Encrypted Alert`, 例如: 0x01, 0x02, ...

openssl-1.0.1b 版本定义了Alert 协议中部分数据，如下所示
```
> ssl/ssl3.sh

#define SSL3_AL_WARNING			1
#define SSL3_AL_FATAL			2

#define SSL3_AD_CLOSE_NOTIFY		 0
#define SSL3_AD_UNEXPECTED_MESSAGE	10	/* fatal */
#define SSL3_AD_BAD_RECORD_MAC		20	/* fatal */
#define SSL3_AD_DECOMPRESSION_FAILURE	30	/* fatal */
#define SSL3_AD_HANDSHAKE_FAILURE	40	/* fatal */
#define SSL3_AD_NO_CERTIFICATE		41
#define SSL3_AD_BAD_CERTIFICATE		42
#define SSL3_AD_UNSUPPORTED_CERTIFICATE	43
#define SSL3_AD_CERTIFICATE_REVOKED	44
#define SSL3_AD_CERTIFICATE_EXPIRED	45
#define SSL3_AD_CERTIFICATE_UNKNOWN	46
#define SSL3_AD_ILLEGAL_PARAMETER	47	/* fatal */
```

## nginx 配置https

install `nginx`, run `gencert.sh` , 输入域名，例如: test.com, 然后输入四个相同的口令, 会在当前路径生成

```
test.com.crt：自签名的证书
test.com.csr：证书的请求
test.com.key：不带口令的Key
test.com.origin.key：带口令的Key
```

复制 `test.com.key`, `test.com.crt` 到 `/etc/nginx/ssl/` 目录， nginx.conf 相关配置如下

```
server {
        listen 443 ssl;
        ssl_certificate     /etc/nginx/ssl/test.com.crt;
        ssl_certificate_key /etc/nginx/ssl/test.com.key;
}
```

## dockerhub env

[janes/ssl-death-alert](https://hub.docker.com/r/janes/ssl-death-alert/)
