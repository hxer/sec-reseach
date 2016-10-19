<?php

class AdminController extends Plugin {
	
	private $file_url;
	private $data_url;
	private $copyfailnum;
	
    public function __construct() {
        parent::__construct();
		//Admin控制器进行登录验证
        if (!$this->session->is_set('user_id') || !$this->session->get('user_id')) {
            $this->adminMsg('请登录以后再操作', url('admin/login'));
        }
		$this->file_url = 'http://www.dayrui.com/free/';
		$this->data_url = 'http://www.dayrui.com/index.php?c=v1&m=vlist&name='.CMS_VERSION;
		if ($result = fn_check_url()) {
		    $this->adminMsg($result . '<br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://bbs.finecms.net/forum.php?mod=viewthread&tid=100&extra=" target="_blank" style="font-size:14px">单击查看开启方式</a>');exit;
		}
    }
	
	public function indexAction() {
	    if ($this->isPostForm()) {
		    $this->adminMsg('正在为您升级，请不要关闭浏览器 ...', purl('admin/upgrade'), 3, 1, 1);
		}
	    $data = $this->getFileData();
		$this->assign('check', $this->dir_mode_info());
	    $this->assign('data', $data);
	    $this->display('admin_list');
	}
	
	public function upgradeAction() {
		$dir = APP_ROOT . 'cache' . DIRECTORY_SEPARATOR . 'upgrade' . DIRECTORY_SEPARATOR;
		if (!is_dir($dir)) {
		    //创建升级文件临时目录
		    mkdir($dir, 0777);
		}
	    $data = $this->getFileData();
		if ($data) {
			foreach($data as $t) {
                $v = date('Y-m-d', $t['inputtime']);
                $version = $t['title'];
                $upgradezip_url = $t['file'];
				//保存到本地地址
				$upgradezip_path = $dir . $version . '.zip';
				//下载压缩包
				@file_put_contents($upgradezip_path, fn_geturl($upgradezip_url));
				if (filesize($upgradezip_path) == 0) $this->adminMsg('下载升级包失败！');
				//解压缩
		        $zip = $this->instance('pclzip');
		        $zip->PclFile($upgradezip_path);
				//解压文件校验
				$testzip_path = $dir . $version . DIRECTORY_SEPARATOR;
				if ($zip->extract(PCLZIP_OPT_PATH, $testzip_path, PCLZIP_OPT_REPLACE_NEWER) == 0) {
					@unlink($upgradezip_path);
					$this->deletedir($testzip_path);
					$this->adminMsg("Error : " . $zip->errorInfo(true));
				}
				//解压升级包
				if($zip->extract(PCLZIP_OPT_PATH, APP_ROOT, PCLZIP_OPT_REPLACE_NEWER) == 0) {
					@unlink($upgradezip_path);
					$this->adminMsg("Error : " . $zip->errorInfo(true) . "<br>升级文件出错，请联系官方。");
				}
				//执行sql
				$sqlfile = APP_ROOT . 'data.sql';
				if (file_exists($sqlfile)) {
				    $this->sql_execute(file_get_contents($sqlfile));
				}
				//读取版本号写入version.ini.php文件
				$content  = "<?php" . PHP_EOL . PHP_EOL . "return array(" . PHP_EOL . PHP_EOL;
                $content .= "	'CMS'    => 'FineCMS', " . PHP_EOL;
                $content .= "	'name'    => 'FineCMS免费版', " . PHP_EOL;
                $content .= "	'company' => '成都天睿信息技术有限公司', " . PHP_EOL;
                $content .= "	'version' => '" . $version . "', " . PHP_EOL;
                $content .= "	'update'  => '" . $v . "', " . PHP_EOL;
                $content .= PHP_EOL . ");";
				@file_put_contents(APP_ROOT . 'config' . DIRECTORY_SEPARATOR . 'version.ini.php', $content);
				//删除文件
				@unlink($upgradezip_path);
				@unlink(APP_ROOT . 'data.sql');
				@unlink(APP_ROOT . 'md5.php');
			}
			//检查update控制器
			if (is_file(CONTROLLER_DIR . 'UpdateController.php')) $this->adminMsg('正在升级数据，请稍候...', url('update'), 2, 1, 2);
			$this->adminMsg('升级完成！', purl('admin'), 3, 1, 1);
		} else {
		    $this->adminMsg('远程数据不存在', purl('admin'));
		}
	}
	
	/**
	 * 目录权限检查函数
	 */
	private function dir_mode_info() {
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
            /* 测试文件 */
            $test_file = APP_ROOT . 'finecms_test.txt';
			/* 检查目录是否可读 */
			$dir = @opendir(APP_ROOT);
			if ($dir === false) return '无任何权限！'; 
			if (@readdir($dir) === false)  return '不可读！';
			@closedir($dir);
			/* 检查目录是否可写 */
			$fp = @fopen($test_file, 'wb');
			if ($fp === false) return '不可写！'; //如果目录中的文件创建失败，返回不可写。
			if (@fwrite($fp, 'directory access testing.') === false) return '不可写！';
			@fclose($fp);
			@unlink($test_file);
			/* 检查目录是否可修改 */
			$fp = @fopen($test_file, 'ab+');
			if ($fp === false) return '不可修改！';
			if (@fwrite($fp, "modify test.\r\n") === false) return '不可修改！';
			@fclose($fp);
			/* 检查目录下是否有执行rename()函数的权限 */
			if (@rename($test_file, $test_file) === false) return '不可重命名！';
			@unlink($test_file);
        }
		foreach (glob(APP_ROOT . '*') as $dir) {
		   if (is_dir($dir)){
			   if (!@is_readable($dir)) return '不可读！';
			   if (!@is_writable($dir)) return '不可写！';
		   }
		}
        return false;
    }
	
	/**
	 * 递归删除
	 */
	private function deletedir($dirname) {
	    $result = false;
	    if (!is_dir($dirname)) return false;
	    $handle = opendir($dirname); //打开目录
	    while(($file = readdir($handle)) !== false) {
	        if($file != '.' && $file != '..'){ //排除"."和"."
	            $dir = $dirname . DIRECTORY_SEPARATOR . $file;
	            //$dir是目录时递归调用deletedir,是文件则直接删除
	            is_dir($dir) ? $this->deletedir($dir) : unlink($dir);
	        }
	    }
	    closedir($handle);
	    $result = rmdir($dirname) ? true : false;
	    return $result;
	}
	
	/**
	 * 执行SQL
	 */
 	private function sql_execute($sql) {
	    $sqls = $this->sql_split($sql);
		if(is_array($sqls)) {
			foreach($sqls as $sql) {
				if(trim($sql) != '') {
					mysql_query($sql);
				}
			}
		} else {
			mysql_query($sqls);
		}
		return true;
	}

 	private function sql_split($sql) {
	    $cfg = Controller::load_config('database');
		$sql = str_replace("{pre}", $cfg['prefix'], $sql);
		$sql = str_replace("\r", "\n", $sql);
		$ret = array();
		$num = 0;
		$queriesarray = explode(";\n", trim($sql));
		unset($sql);
		foreach($queriesarray as $query) {
			$ret[$num] = '';
			$queries = explode("\n", trim($query));
			$queries = array_filter($queries);
			foreach($queries as $query) {
				$str1 = substr($query, 0, 1);
				if($str1 != '#' && $str1 != '-') $ret[$num] .= $query;
			}
			$num++;
		}
		return($ret);
	}
	
	/**
	 * 获取CMS版本信息
	 */
	private function getFileData() {
	    $string = fn_geturl($this->data_url);
	    $data = $string ? json_decode($string) : array();
		if (empty($data)) {
            return array();
        }
		return $this->object_to_array($data);
	}
	
	/**
	 * 对象转换为数组
	 */
	private function object_to_array($obj) { 
		$_arr = is_object($obj) ? get_object_vars($obj) : $obj; 
		foreach ($_arr as $key => $val) { 
			$val = (is_array($val) || is_object($val)) ? $this->object_to_array($val) : $val; 
			$arr[$key] = $val; 
		}
		return $arr;
	}

}