<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>admin</title>
<link href="<?php echo ADMIN_THEME?>images/reset.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ADMIN_THEME?>images/system.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ADMIN_THEME?>images/main.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ADMIN_THEME?>images/table_form.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ADMIN_THEME?>images/dialog.css" rel="stylesheet" type="text/css" />
<script language="javascript" src="<?php echo ADMIN_THEME?>js/jquery.min.js"></script>
<script language="javascript" src="<?php echo ADMIN_THEME?>js/dialog.js"></script>
</head>
<body>
<div class="subnav">
  <div class="content-menu ib-a blue line-x" style="padding-top:8px">
  <a href="<?php echo purl("admin/index")?>" class="on"><em>在线升级</em></a>
  </div>
</div>
<div class="bk15"></div>
<div class="pad-lr-10">
    <div class="explain-col">
        <?php
        if ($check) {
            echo '<font color="red">网站跟目录（' . APP_ROOT . '）' . $check . '，请检查读写权限！</font>';
        } else {
            echo "注意：升级程序有可能覆盖模版文件，请注意备份！";
        }
        ?>
    </div>
<div class="table-list">
<div class="bk15"></div>
<form name="myform" action="" method="post" id="myform">
<table width="100%" cellspacing="0">
	<thead>
		<tr>
			<th align="left" width="200">我的版本号</th>
		<th align="left" width="100">我的更新时间</th>
		<th align="left">&nbsp;</th>
		</tr>
	</thead>
<tbody>
    <tr>
		<td align="left"><?php echo CMS_NAME;?> v<?php echo CMS_VERSION;?></td>
		<td align="left"><?php echo CMS_UPDATE;?></td>
		<td align="left"><a href="http://www.dayrui.net/" target="_blank">程序更新记录</a></td>
    </tr>

</tbody>
</table>
<?php if(!empty($data)) {?>
<div class="bk15"></div>
<table width="100%" cellspacing="0">
<thead>
	<tr>
		<th align="left" width="200">可升级版本列表</th>
		<th align="left" width="100">更新时间</th>
		<th align="left"></th>
	</tr>
</thead>
<tbody>
	<?php foreach($data as $t) { ?>
	<tr>
		<td>v<?php echo $t['title'];?></td>
		<td><?php echo date('Y-m-d', $t['inputtime']);?></td>
        <td align="left"><a href="<?php echo $t['url'];?>" target="_blank">程序更新记录</a>
            &nbsp;&nbsp;
            <a href="<?php echo $t['file'];?>" target="_blank">下载补丁</a></td>
	</tr>
	<?php }?>
</tbody>
</table>
    <div class="bk15"></div>
	<?php
    if ($check) {
	    echo '<font color="red">网站无读写权限，请手动下载对应版本的升级程序并上传到网站，若升级包中存在“data.sql”，请将其务必导入数据库中。</font>';
	} else {
	?>
    <input name="submit" type="submit" id="submit" value=" 开始升级程序 " class="button">&nbsp;&nbsp;&nbsp;&nbsp;
	<div class="onShow"><font color="red">如果您修改过模板，一定要将模板备份，否则将会被系统覆盖。</font></div>
	<?php } ?>
<?php } else {?>
<div class="onShow">
您的版本是最新版本。
</div>
<?php } ?>
</form>
</div>
</div>
</body>
</html>
