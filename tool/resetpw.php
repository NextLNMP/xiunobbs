<?php
exit; // 如果要使用请注释掉该行
define('SKIP_ROUTE', 1);
include '../index.php';

$uid = 1; //要修改的用户UID
$ps = 1; //要修改的密码明文
$user = user_read($uid);
$salt = 'k9keks';
$password = md5(md5($ps).$salt);
$update = array('password'=>$password, 'salt'=>$salt);
user_update($uid, $update);
echo $user['username']." 密码已经重设为：$ps";
?>