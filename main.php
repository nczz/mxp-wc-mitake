<?php
if (!defined('WPINC')) {
	die;
}

if (!empty($_POST) && wp_verify_nonce($_REQUEST['_wpnonce'], 'mxp-wc-mitake-main-setting-page')) {
	$account = $_POST['mxp_mitake_account'];
	$password = $_POST['mxp_mitake_password'];
	$msg_body = $_POST['mxp_mitake_msg_body'];
	$debug_mode = $_POST['mxp_mitake_debug_mode'];
	if (isset($password) && isset($account) && !empty($password) && !empty($account) && !empty($msg_body) && !empty($debug_mode)) {
		update_option("mxp_mitake_account", $account);
		update_option("mxp_mitake_password", $password);
		update_option("mxp_mitake_msg_body", $msg_body);
		update_option("mxp_mitake_debug_mode", $debug_mode);
		echo "更新完成！</br>";
	}
}

?>
<form action="" method="POST">
	三竹帳號：<input type="text" value="<?php echo get_option("mxp_mitake_account"); ?>" name="mxp_mitake_account" size="20"  /></br>
	三竹密碼：<input type="text" value="<?php echo get_option("mxp_mitake_password"); ?>" name="mxp_mitake_password" size="20"  /></br>
	簡訊文字：<textarea name="mxp_mitake_msg_body" rows="3" cols="40"><?php echo get_option("mxp_mitake_msg_body", ""); ?></textarea></br>
	剩餘點數：<?php echo (get_option("mxp_mitake_account", "") != "" && get_option("mxp_mitake_password", "") != "") ? mxp_mitake_get_points(get_option("mxp_mitake_account"), get_option("mxp_mitake_password"), get_option("mxp_mitake_debug_mode", "no")) : "請先輸入帳密後進行查詢"; ?></br>
	除錯模式：<select name="mxp_mitake_debug_mode"><option value="no" <?php selected(get_option("mxp_mitake_debug_mode"), "no");?>>否</option><option value="yes" <?php selected(get_option("mxp_mitake_debug_mode"), "yes");?>>是</option></select></br>
 <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('mxp-wc-mitake-main-setting-page'); ?>"/>
<p><input type="submit" id="save" value="儲存設定" class="button action" /></p>
</form>
<p>外掛版本：v1.0.0</p>
<p>作者：<a href="https://www.mxp.tw/contact/" target="blank">江弘竣（阿竣）Chun</a></p>
