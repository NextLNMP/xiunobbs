<?php
// AI 版主 · 后台设置页 (由 admin plugin-setting 机制加载)
!defined('DEBUG') AND exit('Access Denied.');

include APP_PATH.'plugin/ai_moderator/lib.php';

if(method() == 'POST') {
	$c = array(
		'enabled' => param('enabled', 0),
		'scope_thread' => param('scope_thread', 0),
		'scope_post' => param('scope_post', 0),
		'provider' => in_array(param('provider'), array('openai_compatible','anthropic')) ? param('provider') : 'openai_compatible',
		'base_url' => param('base_url'),
		'model' => param('model'),
		'api_key' => param('api_key', '', FALSE),
		'timeout' => max(2, min(30, param('timeout', 5))),
		'policy' => param('policy', '', FALSE),
	);
	kv_set('ai_moderator', $c);
	message(0, '保存成功, AI 版主配置已生效');
}

$c = ai_mod_conf();
$n = db_sql_find_one("SELECT COUNT(*) AS n, SUM(verdict='delete') AS d FROM bbs_ai_mod_log") ?: array('n'=>0,'d'=>0);
?>
<h3>AI 版主</h3>
<p class="text-muted">填一个 API, 版主自动上岗。判定全部留痕于 bbs_ai_mod_log (含被拦原文, 可查证可恢复); API 不可达时放行不误站 (fail-open)。累计判定 <b><?php echo intval($n['n']);?></b> 次, 拦截 <b><?php echo intval($n['d']);?></b> 次。</p>
<form action="admin/index.php?plugin-setting-ai_moderator.htm" method="post">
<div class="row">
  <div class="col-6"><label>总开关</label>
    <select name="enabled" class="form-control"><option value="1" <?php echo $c['enabled']?'selected':'';?>>启用</option><option value="0" <?php echo !$c['enabled']?'selected':'';?>>停用</option></select></div>
  <div class="col-3"><label>审主贴</label><select name="scope_thread" class="form-control"><option value="1" <?php echo $c['scope_thread']?'selected':'';?>>是</option><option value="0" <?php echo !$c['scope_thread']?'selected':'';?>>否</option></select></div>
  <div class="col-3"><label>审回帖</label><select name="scope_post" class="form-control"><option value="1" <?php echo $c['scope_post']?'selected':'';?>>是</option><option value="0" <?php echo !$c['scope_post']?'selected':'';?>>否</option></select></div>
</div>
<div class="row">
  <div class="col-4"><label>接口风格</label>
    <select name="provider" class="form-control">
      <option value="openai_compatible" <?php echo $c['provider']=='openai_compatible'?'selected':'';?>>OpenAI 兼容 (DeepSeek/Qwen/Kimi/OpenAI)</option>
      <option value="anthropic" <?php echo $c['provider']=='anthropic'?'selected':'';?>>Anthropic (Claude)</option>
    </select></div>
  <div class="col-4"><label>Base URL</label><input name="base_url" class="form-control" value="<?php echo htmlspecialchars($c['base_url']);?>" placeholder="如 https://api.deepseek.com/v1"></div>
  <div class="col-4"><label>模型名</label><input name="model" class="form-control" value="<?php echo htmlspecialchars($c['model']);?>" placeholder="如 deepseek-chat"></div>
</div>
<div class="row">
  <div class="col-8"><label>API Key</label><input name="api_key" type="password" class="form-control" value="<?php echo htmlspecialchars($c['api_key']);?>"></div>
  <div class="col-4"><label>判定超时(秒)</label><input name="timeout" class="form-control" value="<?php echo intval($c['timeout']);?>"></div>
</div>
<label>站规 (即 AI 版主的执法依据, 可随时修改)</label>
<textarea name="policy" class="form-control" rows="5"><?php echo htmlspecialchars($c['policy']);?></textarea>
<div class="mt-3"><button type="submit" class="btn btn-primary">保存</button></div>
</form>
