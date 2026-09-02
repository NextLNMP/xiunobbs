<?php

!defined('DEBUG') AND exit('Access Denied.');

include _include(XIUNOPHP_PATH.'xn_send_mail.func.php');

$action = param(1);

is_numeric($action) AND $action = '';

// hook user_start.php

if(empty($action)) {

        // hook user_index_start.php

        $_uid = param(1, 0);
        empty($_uid) AND $_uid = $uid;
        $_user = user_read($_uid);

	empty($_user) AND message(-1, lang('user_not_exists'));

        $header['title'] = $_user['username'];
        $header['mobile_title'] = $_user['username'];

        // hook user_index_end.php

	include _include(APP_PATH.'view/htm/user.htm');

} elseif($action == 'thread') {

        // hook user_thread_start.php

        $_uid = param(2, 0);
        empty($_uid) AND $_uid = $uid;
        $_user = user_read($_uid);

        empty($_user) AND message(-1, lang('user_not_exists'));
        $header['title'] = $_user['username'];
        $header['mobile_title'] = $_user['username'];

        $page = param(3, 1);
        $pagesize = 20;
        $totalnum = $_user['threads'];
        $pagination = pagination(url("user-thread-$_uid-{page}"), $totalnum, $page, $pagesize);
        $threadlist = mythread_find_by_uid($_uid, $page, $pagesize);
        thread_list_access_filter($threadlist, $gid);

        // hook user_thread_end.php

	include _include(APP_PATH.'view/htm/user_thread.htm');

} elseif($action == 'login') {

	// hook user_login_get_post.php

	if($method == 'GET') {

		// hook user_login_get_start.php

		$referer = user_http_referer();

		$header['title'] = lang('user_login');

		// hook user_login_get_end.php

		include _include(APP_PATH.'view/htm/user_login.htm');

	} else if($method == 'POST') {

		// hook user_login_post_start.php

		$email = param('email');			// 邮箱或者手机号 / email or mobile
		$password = param('password');
		empty($email) AND message('email', lang('email_is_empty'));

		// 登录失败限流：同一用户名+IP 15 分钟内失败 10 次拒绝登录
		$loginfail_file = APP_PATH.'tmp/loginfail_'.md5($email.$longip).'.txt';
		$loginfail = 0;
		$loginfail_start = $time;
		if(is_file($loginfail_file)) {
			$arr = explode("\t", file_get_contents($loginfail_file));
			// 超过 15 分钟窗口重置计数并回收文件
			if(isset($arr[1]) && $time - intval($arr[1]) < 900) {
				$loginfail = intval($arr[0]);
				$loginfail_start = intval($arr[1]);
			} else {
				unlink($loginfail_file);
			}
		}
		$loginfail >= 10 AND message(-1, '登录失败次数过多，请 15 分钟后再试');
		// 低概率顺手回收过期的限流小文件，防 tmp/ 无限增长（GLOB_BRACE 部分系统不可用）
		if(random_int(0, 49) == 0) {
			$_files = array_merge((array)glob(APP_PATH.'tmp/loginfail_*.txt'), (array)glob(APP_PATH.'tmp/sendcode_*.txt'), (array)glob(APP_PATH.'tmp/sendcodeip_*.txt'));
			foreach($_files as $_f) {
				$_f && $time - filemtime($_f) > 3600 AND unlink($_f);
			}
		}

		if(is_email($email, $err)) {
			$_user = user_read_by_email($email);
		} else {
			$_user = user_read_by_username($email);
		}

		!is_password($password, $err) AND message('password', $err);
		// 用户不存在与密码错误返回同一报错，避免用户名枚举
		$check = !empty($_user) && (md5($password.$_user['salt']) == $_user['password']);
		// hook user_login_post_password_check_after.php
		if(!$check) {
			file_put_contents($loginfail_file, ($loginfail + 1)."\t".$loginfail_start);
			message('password', lang('password_incorrect'));
		}
		is_file($loginfail_file) AND unlink($loginfail_file);

		// 更新登录时间和次数
		// update login times
		user_update($_user['uid'], array('login_ip'=>$longip, 'login_date' =>$time , 'logins+'=>1));

		// 全局变量 $uid 会在结束后，在函数 register_shutdown_function() 中存入 session (文件: model/session.func.php)
		// global variable $uid will save to session in register_shutdown_function() (file: model/session.func.php)
		$uid = $_user['uid'];

		$_SESSION['uid'] = $uid;

		user_token_set($_user['uid']);

		// hook user_login_post_end.php

		// 设置 token，下次自动登陆。

		message(0, lang('user_login_successfully'));

	}

} elseif($action == 'create') {

	// hook user_create_get_post.php

	empty($conf['user_create_on']) AND message(-1, lang('user_create_not_on'));

	if($method == 'GET') {

		// hook user_create_get_start.php

		$referer = user_http_referer();
		$header['title'] = lang('create_user');

		// hook user_create_get_end.php

		include _include(APP_PATH.'view/htm/user_create.htm');

	} else if($method == 'POST') {

		// hook user_create_post_start.php

		$email = param('email');
		$username = param('username');
		$password = param('password');
		$code = param('code');
		empty($email) AND message('email', lang('please_input_email'));
		empty($username) AND message('username', lang('please_input_username'));
		empty($password) AND message('password', lang('please_input_password'));

		if($conf['user_create_email_on']) {
			$sess_email = _SESSION('user_create_email');
			$sess_code = _SESSION('user_create_code');
			empty($sess_code) AND message('code', lang('click_to_get_verify_code'));
			empty($sess_email) AND message('code', lang('click_to_get_verify_code'));
			$email != $sess_email AND message('code', lang('verify_code_incorrect'));
			$code != $sess_code AND message('code', lang('verify_code_incorrect'));
		}

		!is_email($email, $err) AND message('email', $err);
		$_user = user_read_by_email($email);
		$_user AND message('email', lang('email_is_in_use'));

		!is_username($username, $err) AND message('username', $err);
		$_user = user_read_by_username($username);
		$_user AND message('username', lang('username_is_in_use'));

		!is_password($password, $err) AND message('password', $err);

		$salt = xn_rand(16);
		$pwd = md5($password.$salt);
		$gid = 101;
		$_user = array (
			'username' => $username,
			'email' => $email,
			'password' => $pwd,
			'salt' => $salt,
			'gid' => $gid,
			'create_ip' => $longip,
			'create_date' => $time,
			'logins' => 1,
			'login_date' => $time,
			'login_ip' => $longip,
		);
		$uid = user_create($_user);
		$uid === FALSE AND message('email', lang('user_create_failed'));
		$user = user_read($uid);

		// 更新 session

		unset($_SESSION['user_create_email']);
		unset($_SESSION['user_create_code']);
		$_SESSION['uid'] = $uid;
		user_token_set($uid);

		$extra = array('token'=>user_token_gen($uid));

		// hook user_create_post_end.php

		message(0, lang('user_create_sucessfully'), $extra);
	}

} elseif($action == 'logout') {

	// hook user_logout_start.php

	$uid = 0;
	$_SESSION['uid'] = $uid;
	user_token_clear();

	// hook user_logout_end.php

	message(0, jump(lang('logout_successfully'), http_referer(), 1));
	//message(0, jump('退出成功', './', 1));

// 重设密码第 1 步 | reset password first step
} elseif($action == 'resetpw') {

	// hook user_resetpw_get_post.php

	!$conf['user_resetpw_on'] AND message(-1, '未开启密码找回功能！');

	if($method == 'GET') {

		// hook user_resetpw_get_start.php

		$header['title'] = lang('resetpw');

		// hook user_resetpw_get_end.php

		include _include(APP_PATH.'view/htm/user_resetpw.htm');

	} else if($method == 'POST') {

		// hook user_resetpw_post_start.php

		$email = param('email');
		empty($email) AND message('email', lang('please_input_email'));
		!is_email($email, $err) AND message('email', $err);

		$_user = user_read_by_email($email);
		!$_user AND message('email', lang('email_is_not_in_use'));

		$code = param('code');
		empty($code) AND message('code', lang('please_input_verify_code'));

		$sess_email = _SESSION('user_resetpw_email');
		$sess_code = _SESSION('user_resetpw_code');
		$sess_code_time = intval(_SESSION('user_resetpw_code_time'));
		$sess_code_tries = intval(_SESSION('user_resetpw_code_tries'));
		empty($sess_code) AND message('code', lang('click_to_get_verify_code'));
		empty($sess_email) AND message('code', lang('click_to_get_verify_code'));
		// 验证码 10 分钟过期、错 5 次作废，须重新发码
		if($time - $sess_code_time > 600 || $sess_code_tries >= 5) {
			unset($_SESSION['user_resetpw_email']);
			unset($_SESSION['user_resetpw_code']);
			unset($_SESSION['user_resetpw_code_time']);
			unset($_SESSION['user_resetpw_code_tries']);
			message('code', lang('click_to_get_verify_code'));
		}
		if($email != $sess_email || $code != $sess_code) {
			$_SESSION['user_resetpw_code_tries'] = $sess_code_tries + 1;
			message('code', lang('verify_code_incorrect'));
		}

		$_SESSION['resetpw_verify_email'] = $sess_email;

		// hook user_resetpw_post_end.php

		message(0, lang('check_ok_to_next_step'));
	}

// 重设密码第 3 步 | reset password step 3
} elseif($action == 'resetpw_complete') {

	// hook user_resetpw_get_post.php

	// 校验数据
	$email = _SESSION('user_resetpw_email');
	$resetpw_verify_email = _SESSION('resetpw_verify_email');
	(empty($email) || empty($resetpw_verify_email)) AND message(-1, lang('data_empty_to_last_step'));

	($resetpw_verify_email != $email) AND message(-1, lang('data_empty_to_last_step'));

	$_user = user_read_by_email($email);
	empty($_user) AND message(-1, lang('email_not_exists'));
	$_uid = $_user['uid'];

	if($method == 'GET') {

		// hook user_resetpw_get_start.php

		$header['title'] = lang('resetpw');

		// hook user_resetpw_get_end.php

		include _include(APP_PATH.'view/htm/user_resetpw_complete.htm');

	} else if($method == 'POST') {

		// hook user_resetpw_post_start.php

		$password = param('password');
		empty($password) AND message('password', lang('please_input_password'));
		// 先校验原始口令，再加盐入库
		!is_password($password, $err) AND message('password', $err);

		$salt = $_user['salt'];
		$password = md5($password.$salt);

		user_update($_uid, array('password'=>$password));

		unset($_SESSION['user_resetpw_email']);
		unset($_SESSION['user_resetpw_code']);
		unset($_SESSION['user_resetpw_code_time']);
		unset($_SESSION['user_resetpw_code_tries']);
		unset($_SESSION['resetpw_verify_email']);
		unset($_SESSION['resetpw_verify_ok']);

		// hook user_resetpw_post_end.php

		message(0, lang('modify_successfully'));

	}

// 发送验证码
} elseif($action == 'send_code') {

	$method != 'POST' AND message(-1, lang('method_error'));

	// hook user_sendcode_start.php

	$action2 = param(2);

	// 创建用户
	if($action2 == 'user_create') {

		$email = param('email');

		empty($email) AND message('email', lang('please_input_email'));
		!is_email($email, $err) AND message('email', $err);
		empty($conf['user_create_email_on']) AND message(-1, lang('email_verify_not_on'));
		$_user = user_read_by_email($email);
		!empty($_user) AND message('email', lang('email_is_in_use'));

		$code = random_int(100000, 999999);
		$_SESSION['user_create_email'] = $email;
		$_SESSION['user_create_code'] = $code;


	// 重置密码，往老地址发送
	} elseif($action2 == 'user_resetpw') {

		$email = param('email');

		empty($email) AND message('email', lang('please_input_email'));
		!is_email($email, $err) AND message('email', $err);
		$_user = user_read_by_email($email);
		empty($_user) AND message('email', lang('email_is_not_in_use'));

		empty($conf['user_resetpw_on']) AND message(-1, lang('resetpw_not_on'));

		// 同会话发码间隔 60 秒
		$sess_code_time = intval(_SESSION('user_resetpw_code_time'));
		$time - $sess_code_time < 60 AND message(-1, '发送太频繁，请 60 秒后重试');

		$code = random_int(100000, 999999);
		$_SESSION['user_resetpw_email'] = $email;
		$_SESSION['user_resetpw_code'] = $code;
		$_SESSION['user_resetpw_code_time'] = $time;
		$_SESSION['user_resetpw_code_tries'] = 0;

	} else {
		message(-1, 'action2 error');
	}


	// 发码限频存服务端，session 侧限频可被换会话绕过：单邮箱 60 秒一封，单 IP 每小时 15 封
	$sendmail_file = APP_PATH.'tmp/sendcode_'.md5($email).'.txt';
	$sendip_file = APP_PATH.'tmp/sendcodeip_'.md5($longip).'.txt';
	is_file($sendmail_file) && $time - intval(file_get_contents($sendmail_file)) < 60 AND message(-1, '发送太频繁，请 60 秒后重试');
	$sendip_n = 0;
	$sendip_start = $time;
	if(is_file($sendip_file)) {
		$arr = explode("\t", file_get_contents($sendip_file));
		if(isset($arr[1]) && $time - intval($arr[1]) < 3600) {
			$sendip_n = intval($arr[0]);
			$sendip_start = intval($arr[1]);
		}
	}
	$sendip_n >= 15 AND message(-1, '发送太频繁，请稍后重试');
	file_put_contents($sendmail_file, $time);
	file_put_contents($sendip_file, ($sendip_n + 1)."\t".$sendip_start);

	$subject = lang('send_code_template', array('rand'=>$code, 'sitename'=>$conf['sitename']));
	$message = $subject;

	$smtpfile = APP_PATH.'conf/smtp.conf.php';
	!is_file($smtpfile) AND $smtpfile = APP_PATH.'conf/smtp.conf.default.php';
	$smtplist = include $smtpfile; // 纯配置无 hook，不走 _include 的 tmp 缓存
	(!is_array($smtplist) || empty($smtplist)) AND message(-1, '站点未配置 SMTP，无法发送验证码');
	$n = array_rand($smtplist);
	$smtp = $smtplist[$n];

	// hook user_send_code_before.php
	$r = xn_send_mail($smtp, $conf['sitename'], $email, $subject, $message);
	// hook user_send_code_after.php

	if($r === TRUE) {
		message(0, lang('send_successfully'));
	} else {
		xn_log($errstr, 'send_mail_error');
		message(-1, $errstr);
	}

// 简单的同步登陆实现：| sync login implement simply
/*
	将用户信息通过 token 传递给其他系统 | send user information to other system by token
	两边系统将 auth_key 设置为一致，用 xn_encrypt() xn_decrypt() 加密解密。all subsystem set auth_key to correct by xn_encrypt() xn_decrypt()
*/
} elseif($action == 'synlogin') {

	// 检查过来的 token | check token
	$token = param('token');
	$return_url = param('return_url');

	$s = xn_decrypt($token);
	!$s AND message(-1, lang('unauthorized_access'));
	list($_time, $_useragent) = explode("\t", $s);
	$useragent != $_useragent AND message(-1, lang('authorized_get_failed'));

	empty($_SESSION['return_url']) AND $_SESSION['return_url'] = $return_url;
	if(!$uid) {
		http_location(url('user-login'));
	} else {
		$return_url = _SESSION('return_url');

		empty($return_url) AND message(-1, lang('request_synlogin_again'));
		unset($_SESSION['return_url']);

		$arr = array(
			'uid'=>$user['uid'],
			'gid'=>$user['gid'],
			'username'=>$user['username'],
			'avatar_url'=>$user['avatar_url'],
			'email'=>$user['email'],
			'mobile'=>$user['mobile'],
		);
		$s = xn_json_encode($arr);
		$s = xn_encrypt($s);

		// 将 token 附加到 URL，跳转回去 | add token into URL, jump back
		// return_url 只允许站内相对路径，防止开放跳转 | only allow relative path
		$return_url = xn_urldecode($return_url);
		$safe = preg_match('#^[a-zA-Z/]#', $return_url)
			&& substr($return_url, 0, 2) != '//'
			&& strpos($return_url, '://') === FALSE
			&& strpos($return_url, '\\') === FALSE
			&& !preg_match('#^[a-zA-Z][a-zA-Z0-9+.\-]*:#', $return_url);
		!$safe AND $return_url = './';
		$url = $return_url.'?token='.$s;
		//$url = xn_url_add_arg($return_url, 'token', $s);
		http_location($url);
	}

} else {

}

// hook user_end.php


?>
