<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 电商系统
 */

class Magento extends Common {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $file = FCPATH.'mangento/init.php';
    }


    // 会员信息同步
    public function _member() {

    }


    // 资金数据同步
    public function _price() {

    }


}
