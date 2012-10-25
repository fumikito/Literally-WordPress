<?php
/**
 * <b>Webmoneyリダイレクトページ生成用パラメータホルダー</b>
 *
 * Webmoneyリダイレクトページを生成するときに、RedirectUtilに渡すパラメータを保持するクラス
 *
 * @package com.gmo_pg.client
 * @subpackage input
 * @see inputPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 04-08-2010
 */
class WebmoneyStartParam {

	/**
	 * @var string 取引ID。GMO-PGが払い出した、取引を特定するID
	 */
	var $accessId;

	/**
	 * 取引ID取得
	 * @return string 取引ID
	 */
	function getAccessId() {
		return $this->accessId;
	}

	/**
	 * 取引ID設定
	 *
	 * @param string $accessId 取引ID
	 */
	function setAccessId($accessId) {
		$this->accessId = $accessId;
	}

}
