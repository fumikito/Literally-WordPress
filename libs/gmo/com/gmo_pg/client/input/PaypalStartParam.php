<?php
/**
 * <b>Paypalリダイレクトページ生成用パラメータホルダー</b>
 *
 * Paypalリダイレクトページを生成するときに、RedirectUtilに渡すパラメータを保持するクラス
 *
 * @package com.gmo_pg.client
 * @subpackage input
 * @see inputPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 12-24-2009 00:00:00
 */
class PaypalStartParam {

	/**
	 * @var string GMO-PGが発行する、PGマルチペイメントサービス中で加盟店様を識別するID
	 */
	var $shopId;

	/**
	 * @var string 取引ID。GMO-PGが払い出した、取引を特定するID
	 */
	var $accessId;

	/**
	 * ショップId取得
	 * @return string ショップId
	 */
	function getShopId(){
		return $this->shopId;
	}

	/**
	 * 取引ID取得
	 * @return string 取引ID
	 */
	function getAccessId() {
		return $this->accessId;
	}

	/**
	 * ショップIdの設定
	 * @param $shopId ショップId
	 */
	function setShopId( $shopId ){
		$this->shopId = $shopId;
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
