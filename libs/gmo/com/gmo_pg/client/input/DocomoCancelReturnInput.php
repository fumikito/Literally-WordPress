<?php
require_once ('com/gmo_pg/client/input/BaseInput.php');
/**
 * <b>ドコモケータイ払い決済取消　入力パラメータクラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage input
 * @see inputPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 2012/06/14
 */
class DocomoCancelReturnInput extends BaseInput {

	/**
	 * @var string ショップID
	 */
	var $shopID;

	/**
	 * @var string ショップパスワード
	 */
	var $shopPass;

	/**
	 * @var string 取引ID
	 */
	var $accessID;

	/**
	 * @var string 取引パスワード
	 */
	var $accessPass;

	/**
	 * @var string オーダーID
	 */
	var $orderID;

	/**
	 * @var string キャンセル金額
	 */
	var $cancelAmount;

	/**
	 * @var string キャンセル税送料
	 */
	var $cancelTax;

	
	/**
	 * コンストラクタ
	 *
	 * @param array $params 入力パラメータ
	 */
	function DocomoCancelReturnInput($params = null) {
		$this->__construct($params);
	}

	/**
	 * コンストラクタ
	 *
	 * @param array $params 入力パラメータ
	 */
	function __construct($params = null) {
		parent::__construct($params);
	}

	
	/**
	 * ショップID取得
	 * @return string ショップID
	 */
	function getShopID() {
		return $this->shopID;
	}

	/**
	 * ショップパスワード取得
	 * @return string ショップパスワード
	 */
	function getShopPass() {
		return $this->shopPass;
	}

	/**
	 * 取引ID取得
	 * @return string 取引ID
	 */
	function getAccessID() {
		return $this->accessID;
	}

	/**
	 * 取引パスワード取得
	 * @return string 取引パスワード
	 */
	function getAccessPass() {
		return $this->accessPass;
	}

	/**
	 * オーダーID取得
	 * @return string オーダーID
	 */
	function getOrderID() {
		return $this->orderID;
	}

	/**
	 * キャンセル金額取得
	 * @return integer キャンセル金額
	 */
	function getCancelAmount() {
		return $this->cancelAmount;
	}

	/**
	 * キャンセル税送料取得
	 * @return integer キャンセル税送料
	 */
	function getCancelTax() {
		return $this->cancelTax;
	}

	/**
	 * ショップID設定
	 *
	 * @param string $shopID
	 */
	function setShopID($shopID) {
		$this->shopID = $shopID;
	}

	/**
	 * ショップパスワード設定
	 *
	 * @param string $shopPass
	 */
	function setShopPass($shopPass) {
		$this->shopPass = $shopPass;
	}

	/**
	 * 取引ID設定
	 *
	 * @param string $accessID
	 */
	function setAccessID($accessID) {
		$this->accessID = $accessID;
	}

	/**
	 * 取引パスワード設定
	 *
	 * @param string $accessPass
	 */
	function setAccessPass($accessPass) {
		$this->accessPass = $accessPass;
	}

	/**
	 * オーダーID設定
	 *
	 * @param string $orderID
	 */
	function setOrderID($orderID) {
		$this->orderID = $orderID;
	}

	/**
	 * キャンセル金額設定
	 *
	 * @param integer $cancelAmount
	 */
	function setCancelAmount($cancelAmount) {
		$this->cancelAmount = $cancelAmount;
	}

	/**
	 * キャンセル税送料設定
	 *
	 * @param integer $cancelTax
	 */
	function setCancelTax($cancelTax) {
		$this->cancelTax = $cancelTax;
	}


	/**
	 * デフォルト値設定
	 */
	function setDefaultValues() {
	   
	}

	/**
	 * 入力パラメータ群の値を設定する
	 *
	 * @param IgnoreCaseMap $params 入力パラメータ
	 */
	function setInputValues($params) {
		// 入力パラメータがnullの場合は設定処理を行わない
	    if (is_null($params)) {
	        return;
	    }
	    
	    $this->setShopID($this->getStringValue($params, 'ShopID', $this->getShopID()));
	    $this->setShopPass($this->getStringValue($params, 'ShopPass', $this->getShopPass()));
	    $this->setAccessID($this->getStringValue($params, 'AccessID', $this->getAccessID()));
	    $this->setAccessPass($this->getStringValue($params, 'AccessPass', $this->getAccessPass()));
	    $this->setOrderID($this->getStringValue($params, 'OrderID', $this->getOrderID()));
	    $this->setCancelAmount($this->getStringValue($params, 'CancelAmount', $this->getCancelAmount()));
	    $this->setCancelTax($this->getStringValue($params, 'CancelTax', $this->getCancelTax()));
	}

	/**
	 * 文字列表現
	 * @return string 接続文字列表現
	 */
	function toString() {
	    $str .= 'ShopID=' . $this->encodeStr($this->getShopID());
	    $str .= '&';
	    $str .= 'ShopPass=' . $this->encodeStr($this->getShopPass());
	    $str .= '&';
	    $str .= 'AccessID=' . $this->encodeStr($this->getAccessID());
	    $str .= '&';
	    $str .= 'AccessPass=' . $this->encodeStr($this->getAccessPass());
	    $str .= '&';
	    $str .= 'OrderID=' . $this->encodeStr($this->getOrderID());
	    $str .= '&';
	    $str .= 'CancelAmount=' . $this->encodeStr($this->getCancelAmount());
	    $str .= '&';
	    $str .= 'CancelTax=' . $this->encodeStr($this->getCancelTax());
	    return $str;
	}


}
?>
