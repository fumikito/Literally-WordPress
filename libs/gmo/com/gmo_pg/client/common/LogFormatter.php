<?php

/**
 * <b>ログフォーマッタ</b>
 * 
 *  ログ文字列をフォーマットします
 * 
 * @package com.gmo_pg.client
 * @subpackage common
 * @see commonPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 01-01-2008 00:00:00
 */
class LogFormatter {

	/**
	 * ログレコードをフォーマットする
	 *
	 * @param string $className クラス名
	 * @param integer $level     ログレベル
	 * @param string $message   ログ内容
	 * @param mixed $params    置き換えパラメータ
	 * @return string フォーマットされたログメッセージ
	 */
	function format($className, $level, $message, $params = null) {
		if (false == is_null($params)) {
			foreach ($params as $param) {
				$message = preg_replace('/\{\d+\}/', $param, $message);
			}
		}
		$log = date('Y/m/d H:i:s') . ' ' . $className . " [$level]: $message\n";
		return $log;
	}

}
?>