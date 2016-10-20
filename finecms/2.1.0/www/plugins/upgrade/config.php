<?php
if (!defined('IN_FINECMS')) exit('No permission resources');

return array(
    'name'    => '在线升级',
    'author'  => 'finecms',
    'version' => '3.0',
    'typeid'  => 1,
    'description' => "升级程序有可能覆盖模版文件，请注意备份！linux服务器需检查文件所有者权限和组权限，确保WEB SERVER用户有文件写入权限",
);