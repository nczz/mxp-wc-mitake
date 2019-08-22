<?php
/*
Plugin Name: WooCommerce 三竹簡訊整合
Plugin URI: https://github.com/nczz/mxp-wc-mitake
Description: 設定傳送完成訂單後的簡訊
Author: Chun
Version: 1.0.0
Author URI: https://www.mxp.tw/
 */

/**
 ** @param username 三竹簡訊用戶帳號
 ** @param password 三竹簡訊用戶密碼
 ** @param mobile 台灣手機號碼
 ** @param text 簡訊內容。切勿混用非中英文等其他語言。
 **/

function mxp_mitake_send_sms($username, $password, $mobile, $text, $debug = "no") {
	$package = array(
		'username' => $username,
		'password' => $password,
		'dstaddr' => $mobile,
		'smbody' => $text,
		'encoding' => 'UTF8',
	);

	$url = 'http://smsapi.mitake.com.tw/api/mtk/SmSend?' . http_build_query($package, '', '&', PHP_QUERY_RFC3986);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($ch, CURLOPT_VERBOSE, true);
	if ($debug == "yes") {
		$verbose = fopen('php://temp', 'w+');
		curl_setopt($ch, CURLOPT_STDERR, $verbose);
	}
	$output = curl_exec($ch);
	$verboseLog = "";
	if ($debug) {
		rewind($verbose);
		$verboseLog = stream_get_contents($verbose);
	}
	curl_close($ch);
	return $verboseLog . $output;
}

function mxp_mitake_get_points($username, $password, $debug = "no") {
	$package = array(
		'username' => $username,
		'password' => $password,
	);

	$url = "http://smsapi.mitake.com.tw/api/mtk/SmQuery?" . http_build_query($package, '', '&', PHP_QUERY_RFC3986);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($ch, CURLOPT_VERBOSE, true);
	if ($debug) {
		$verbose = fopen('php://temp', 'w+');
		curl_setopt($ch, CURLOPT_STDERR, $verbose);
	}
	$output = curl_exec($ch);
	$verboseLog = "";
	if ($debug == "yes") {
		rewind($verbose);
		$verboseLog = stream_get_contents($verbose);
	}
	curl_close($ch);
	return $verboseLog . $output;
}

function register_mxp_wc_mitake_custom_submenu_page() {
	add_submenu_page('woocommerce', '三竹簡訊整合', '三竹簡訊整合', 'manage_options', 'mxp-wc-mitake-submenu-page', 'mxp_wc_mitake_submenu_page_callback');
}

function mxp_wc_mitake_submenu_page_callback() {
	include plugin_dir_path(__FILE__) . "/main.php";
}
add_action('admin_menu', 'register_mxp_wc_mitake_custom_submenu_page', 99);

function mxp_sms_payment_complete_hook($order_id) {
	$order = wc_get_order($order_id);
	$user = $order->get_user();
	// $order->get_billing_email()
	$mobile = $order->get_billing_phone();
	// $order->get_billing_first_name()
	// $order->get_billing_last_name()
	if (get_option("mxp_mitake_enable_feature", "no") == "yes") {
		$username = get_option("mxp_mitake_account", "");
		$password = get_option("mxp_mitake_password", "");
		$msg = get_option("mxp_mitake_msg_body", "");
		if (strpos($mobile, '09') === 0 && strlen($mobile) == 10) {
			if ($username != "" && $password != "" && $msg != "") {
				$resp = mxp_mitake_send_sms($username, $password, $mobile, $msg, get_option("mxp_mitake_debug_mode", "no"));
				$order->add_order_note("已傳送簡訊「" . $msg . "」至「" . $order->get_billing_last_name() . $order->get_billing_first_name() . "」手機「" . $order->get_billing_phone() . "」。" . PHP_EOL . "三竹簡訊 API 回應：" . PHP_EOL . $resp);
			} else {
				$order->add_order_note("簡訊設定參數錯誤，無法發送簡訊。");
			}
		} else {
			$order->add_order_note("手機格式錯誤「{$mobile}」，無法發送簡訊。");
		}
	} else {
		$order->add_order_note("提醒：簡訊發送功能尚未開啟，請至 [WooCommerce -> 三竹簡訊整合] 頁面啟用。");
	}
}
add_action('woocommerce_order_status_completed', 'mxp_sms_payment_complete_hook');
