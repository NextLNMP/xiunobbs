// AI 版主: 回帖入库前判定 (Xiuno BBS 5.0)
// 此钩子点位于宿主取参之前, 自行预取 message 判定; 宿主随后重复取参无副作用。
$ai_mod_c = function_exists('ai_mod_conf') ? NULL : include APP_PATH.'plugin/ai_moderator/lib.php';
$ai_mod_c = ai_mod_conf();
if($ai_mod_c['enabled'] && $ai_mod_c['scope_post']) {
	$ai_mod_msg = param('message', '', FALSE);
	if($ai_mod_msg !== '') {
		$ai_mod_r = ai_mod_judge('Re: '.$thread['subject'], $ai_mod_msg, array('uid'=>$uid, 'gid'=>$gid, 'fid'=>$thread['fid']));
		ai_mod_log('post', $uid, $thread['fid'], 'Re: '.$thread['subject'], $ai_mod_msg, $ai_mod_r);
		if($ai_mod_r['verdict'] == 'delete') {
			message(-1, 'AI 版主未放行: '.($ai_mod_r['reason'] ? $ai_mod_r['reason'] : '内容涉嫌违反站规').' (判定已留痕, 如有异议请联系管理员)');
		}
	}
}
