<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Dayrui Website Management System
 *
 * @since		version 2.5.0
 * @author		Dayrui <dayrui@gmail.com>
 * @license     http://www.dayrui.com/license
 * @copyright   Copyright (c) 2011 - 9999, Dayrui.Com, Inc.
 */
	
class Sms extends Admin {

    public $file;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->file = FCPATH.'config/sms.php';
    }
	
	/**
     * 账号
     */
    public function index() {

		if (IS_POST) {
			$data = $this->input->post('data');
			if (strlen($data['note']) > 30 ) {
                $this->adminMsg('短信签名太长');
            }
			$size = file_put_contents($this->file, array2string($data));
			if (!$size) {
                $this->adminMsg('config目录无权限写入');
            }
			$this->adminMsg('保存成功', url('admin/sms/index'), 3, 1, 1);
		}

		$this->template->assign(array(
			'data' => is_file($this->file) ? string2array(file_get_contents($this->file)) : array(),
		));
		$this->template->display('admin/sms_index.html');
    }
	
	/**
     * 发送
     */
    public function send() {
		$this->template->display('admin/sms_send.html');
    }
	
	/**
     * 发送
     */
    public function ajaxsend() {

		$data = $this->input->post('data', true);
		if (strlen($data['content']) > 150) {
            exit(dr_json(0, '短信数量太长，保持在70个字内'));
        }
		
		$mobile = $data['mobile'];
		if ($data['mobiles'] && !$data['mobile']) {
			$mobile = str_replace(array(PHP_EOL, chr(13), chr(10)), ',', $data['mobiles']);
			$mobile = str_replace(',,', ',', $mobile);
			$mobile = trim($mobile, ',');
		}
		if (substr_count($mobile, ',') > 40) {
            exit(dr_json(0, '手机号码太多，不能超过40个'));
        }

		$result = fn_sendsms($mobile, $data['content']);
		if ($result === FALSE) {
			 exit(dr_json(0, '验证发送失败'));
		} else {
			 exit(dr_json($result['status'], $result['msg']));
		}
    }
	
}