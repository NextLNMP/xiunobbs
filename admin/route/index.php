<?php

!defined('DEBUG') AND exit('Access Denied.');

$action = param(1);

// hook admin_index_start.php

if($action == 'login') {

	// hook admin_index_login_get_post.php
	
	if($method == 'GET') {

		// hook admin_index_login_get_start.php
		
		$header['title'] = lang('admin_login');
		
		include _include(ADMIN_PATH."view/htm/index_login.htm");

	} else if($method == 'POST') {

		// hook admin_index_login_post_start.php
		
		$password = param('password');

		// 登录失败限流：同一用户名+IP 15 分钟内失败 10 次拒绝登录
		$loginfail_file = APP_PATH.'tmp/loginfail_'.md5($user['username'].$longip).'.txt';
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

		if(md5($password.$user['salt']) != $user['password']) {
			file_put_contents($loginfail_file, ($loginfail + 1)."\t".$loginfail_start);
			xn_log('password error. uid:'.$user['uid'].' ip:'.$longip, 'admin_login_error');
			message('password', lang('password_incorrect'));
		}

		admin_token_set();

		is_file($loginfail_file) AND unlink($loginfail_file);

		xn_log('login successed. uid:'.$user['uid'], 'admin_login');

		// hook admin_index_login_post_end.php
		
		message(0, jump(lang('login_successfully'), '.'));

	}

} elseif ($action == 'logout') {

	// hook admin_index_logout_start.php
	
	admin_token_clean();
	
	message(0, jump(lang('logout_successfully'), './'));

} elseif ($action == 'phpinfo') {
	
	unset($_SERVER['conf']);
	unset($_SERVER['db']);
	unset($_SERVER['cache']);
	phpinfo();
	exit;
	
} else {

	// hook admin_index_empty_start.php
	
	$header['title'] = lang('admin_page');
	
	$info = array();
	$info['disable_functions'] = ini_get('disable_functions');
	$info['allow_url_fopen'] = ini_get('allow_url_fopen') ? lang('yes') : lang('no');
	$info['safe_mode'] = ini_get('safe_mode') ? lang('yes') : lang('no');
	empty($info['disable_functions']) && $info['disable_functions'] = lang('none');
	$info['upload_max_filesize'] = ini_get('upload_max_filesize');
	$info['post_max_size'] = ini_get('post_max_size');
	$info['memory_limit'] = ini_get('memory_limit');
	$info['max_execution_time'] = ini_get('max_execution_time');
	$info['dbversion'] = $db->version();
	$info['SERVER_SOFTWARE'] = _SERVER('SERVER_SOFTWARE');
	$info['HTTP_X_FORWARDED_FOR'] = _SERVER('HTTP_X_FORWARDED_FOR');
	$info['REMOTE_ADDR'] = _SERVER('REMOTE_ADDR');
	
	
	$stat = array();
	$stat['threads'] = thread_count();
	$stat['posts'] = post_count();
	$stat['users'] = user_count();
	$stat['attachs'] = attach_count();
	$stat['disk_free_space'] = function_exists('disk_free_space') ? humansize(disk_free_space(APP_PATH)) : lang('unknown');
	
	$lastversion = get_last_version($stat);
	
	// hook admin_index_empty_end.php
	
	include _include(ADMIN_PATH.'view/htm/index.htm');

}

// hook admin_index_end.php

function get_last_version($stat) {
	global $conf, $time;
	$last_version = kv_get('last_version');
	if($time - $last_version > 86400) {
		kv_set('last_version', $time);
		$sitename = urlencode($conf['sitename']);
		$sitedomain = urlencode(http_url_path());
		$version = urlencode($conf['version']);
		return '';
	} else {
		return '';
	}
}
?>
