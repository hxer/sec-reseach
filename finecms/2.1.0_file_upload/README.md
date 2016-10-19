## info

FineCMS 任意文件上传

| app | version | vuln |
| --- | ------ | ----- |
| finecms | 2.1.0 | file upload |

## detail

`ajaxswfuploadAction` 函数过滤不严, 导致任意文件上传漏洞

```php
/**
* Swf上传
*/
public function ajaxswfuploadAction() {
    if ($this->post('submit')) {
        $_type = explode(',', $this->post('type'));
        if (empty($_type)) {
            exit('0,' . lang('att-6'));
        }
        $size = (int)$this->post('size');
        if (empty($size)) {
            exit('0,' . lang('att-5'));
        }
        $data = $this->upload('Filedata', $_type, $size, null, null, $this->post('admin'), 'swf', null, $this->post('document'));
        if ($data['result']) {
            exit('0,' . $data['result']);
        }
        //唯一ID,文件全路径,扩展名,文件名称
        exit(time() . rand(0, 999) . ',' . $data['path'] . ',' . $data['ext'] . ',' . str_replace('|', '_', $data['file']));
    } else {
        exit('0,' . lang('att-4'));
    }
}
```

post 请求 `type` 参数未过滤, 表示上传文件类型, 作为上传文件的后缀名。`upload` 函数做了黑名单过滤，如下：

```php
$ext = $upload->fileext();
if (stripos($ext, 'php') !== FALSE
    || stripos($ext, 'asp') !== FALSE
    || stripos($ext, 'aspx') !== FALSE
    ) {
    return array('result' => '文件格式被系统禁止');
```

## docker env

* manual build 

```
docker build -t finecms210_file_upload .
docker run --name finecms210_file_upload -p 8000:80 finecms_file_upload
```

visit [http://127.0.0.1:8000/index.php](http://127.0.0.1:8000/index.php)

![](install.png)

![](config.png)

保持如上配置(可更改管理员帐号和密码), 数据服务器不能是`localhost`, 安装。

## poc

* manual check

visit [http://127.0.0.1:8000/upload.html](http://127.0.0.1:8000/upload.html), then `uplaod 123.phtml` file.

* python

```
python poc.py
```

## 防御

* 上传文件类型过滤

* 增强访问控制，禁止为授权访问