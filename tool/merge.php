<?php

php_sapi_name() != 'cli' AND exit('cli only');

// 合并 XiunoPHP
// Xiuno BBS 5.0: 原版用 substr(8, -2) 魔数剥离首尾标签，对文件头字节数敏感，一改行尾风格就断。
// 改为逐文件正则剥离开头与结尾的 PHP 标签，行为等价且健壮。

$dir = '../xiunophp/';

function xn_strip($file) {
	$s = php_strip_whitespace($file);
	$s = preg_replace('#^<\?php\s*#', '', $s);
	$s = preg_replace('#\?>\s*$#', '', $s);
	return trim($s);
}

$files = array(
	'db_mysql.class.php',
	'db_pdo_mysql.class.php',
	'db_pdo_sqlite.class.php',
	'cache_apc.class.php',
	'cache_memcached.class.php',
	'cache_mysql.class.php',
	'cache_redis.class.php',
	'cache_xcache.class.php',
	'cache_yac.class.php',
	'db.func.php',
	'cache.func.php',
	'image.func.php',
	'array.func.php',
	'xn_encrypt.func.php',
	'misc.func.php',
);

$s = '';
foreach($files as $file) {
	$s .= xn_strip($dir.$file)."\r\n";
}

$xiunophp = file_get_contents($dir.'xiunophp.php');
$before = '// hook xiunophp_include_before.php';
$after = '// hook xiunophp_include_after.php';
$pre = substr($xiunophp, 0, strpos($xiunophp, $before) + 1 + strlen($before));
$suffix = substr($xiunophp, strpos($xiunophp, $after));
$xiunophp_min = trim($pre)."\r\n\r\n".trim($s)."\r\n\r\n".trim($suffix);

file_put_contents($dir.'xiunophp.min.php', $xiunophp_min);

echo 'ok';
