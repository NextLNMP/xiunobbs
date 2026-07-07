// AI 版主: 主贴入库前判定 (Xiuno BBS 5.0)
$ai_mod_c = function_exists('ai_mod_conf') ? NULL : include APP_PATH.'plugin/ai_moderator/lib.php';
$ai_mod_c = ai_mod_conf();
if($ai_mod_c['enabled'] && $ai_mod_c['scope_thread']) {
	$ai_mod_r = ai_mod_judge($subject, $message, array('uid'=>$uid, 'gid'=>$gid, 'fid'=>$fid, 'user_threads'=>isset($user['threads']) ? $user['threads'] : 0));
	ai_mod_log('thread', $uid, $fid, $subject, $message, $ai_mod_r);
	if($ai_mod_r['verdict'] == 'delete') {
		message(-1, 'AI 版主未放行: '.($ai_mod_r['reason'] ? $ai_mod_r['reason'] : '内容涉嫌违反站规').' (判定已留痕, 如有异议请联系管理员)');
	}
}
