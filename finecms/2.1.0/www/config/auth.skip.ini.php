<?php 
if (!defined('IN_FINECMS')) exit();

/**
 * 不需要权限验证的模块
 */
return array(
    'defalut'=>array(),
	'admin'=>array(
        'index-index',
        'index-main',
        'login'
    )
);

?>