<?php
require_once ('com/gmo_pg/client/input/BaseInput.php');

/**
 * <b>Paypal取引登録　入力パラメータクラス</b>
 *
 * @package com.gmo_pg.client
 * @subpackage input
 * @see inputPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 12-22-2009 00:00:00
 */
class EntryTranPaypalInput extends BaseInput {

	/**
	 * @var string GMO-PGが発行する、PGマルチペイメントサービス中で加盟店様を識別するID
	 */
	var $shopId;

	/**
	 * @var string ショップIDと対になるパスワード
	 */
	var $shopPass;

	/**
	 * @var string 加盟店様が発行する、オーダー取引を識別するID
	 */
	var $orderId;

	/**
	 * @var string 処理区分
	 */
	var $jobCd;

	/**
	 * @var string 通貨コード
	 */
	var $currency;
	
	/**
	 * @var string 利用金額
	 */
	var $amount;

	/**
	 * @var string 税送料
	 */
	var $tax;

	/**
	 * コンストラクタ
	 *
	 * @param array $params 入力パラメータ
	 */
	function EntryTranPaypalInput($params = null) {
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
	 * ショップId取得
	 * @return string ショップId
	 */
	function getShopId(){
		return $this->shopId;
	}

	/**
	 * ショップパスワードの取得
	 * @return string ショップパスワード
	 */
	function getShopPass(){
		return $this->shopPass;
	}

	/**
	 * オーダIDの取得
	 * @return string オーダId
	 */
	function getOrderId(){
		return $this->orderId;
	}

	/**
	 * 処理区分の取得
	 * @return string 処理区分
	 */
	function getJobCd(){
		return $this->jobCd;
	}

	/**
	 * 通貨コードの取得
	 * @return string 通貨コード
	 */
	function getCurrency() {
	    return $this->currency;
	}
	
	/**
	 * 金額の取得
	 * @return string 金額
	 */
	function getAmount(){
		return $this->amount;
	}

	/**
	 * 税送料の取得
	 * @return string 税送料
	 */
	function getTax(){
		return $this->tax;
	}

	/**
	 * ショップIdの設定
	 * @param $shopId ショップId
	 */
	function setShopId( $shopId ){
		$this->shopId = $shopId;
	}
	/**
	 * ショップパスワードの設定
	 * @param $shopPass ショップパスワード
	 */
	function setShopPass( $shopPass ){
		$this->shopPass = $shopPass;
	}


	/**
	 * オーダIDの設定
	 * @param $orderid - オーダID
	 */
	function setOrderId( $orderId ){
		$this->orderId = $orderId;
	}

	/**
	 * 処理区分の設定
	 * @param $jobCd 処理区分
	 */
	function setJobCd( $jobCd ){
		$this->jobCd = $jobCd;
	}

	/**
	 * 通貨コードの設定
	 * @param string 通貨コード
	 */
    function setCurrency( $currency ) {
        $this->currency = $currency;
    }
	
	/**
	 * 金額の設定
	 * @param $amount 金額
	 */
	function setAmount( $amount ){
		$this->amount = $amount;
	}

	/**
	 * 税送料の設定
	 * @param $tax 税送料
	 */
	function setTax( $tax ){
		$this->tax = $tax;
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

		if(is_null($params)) {
			return;
		}

		$this->setShopId($this->getStringValue($params, 'ShopID', $this->getShopId()));
		$this->setShopPass($this->getStringValue($params, 'ShopPass', $this->getShopPass()));
		$this->setOrderId($this->getStringValue($params, 'OrderID', $this->getOrderId()));
		$this->setJobCd($this->getStringValue($params, 'JobCd', $this->getJobCd()));
		$this->setCurrency($this->getStringValue($params, 'Currency', $this->getCurrency()));
		$this->setAmount($this->getIntegerValue($params, 'Amount', $this->getAmount()));
		$this->setTax($this->getIntegerValue($params, 'Tax', $this->getTax()));
	}

	/**
	 * 文字列表現
	 * @return string 接続文字列表現
	 */
	function toString() {
		$str  = 'ShopID=' . $this->encodeStr($this->getShopId());
		$str .= '&';
		$str .= 'ShopPass=' . $this->encodeStr($this->getShopPass());
		$str .= '&';
		$str .= 'OrderID=' . $this->encodeStr($this->getOrderId());
		$str .= '&';
		$str .= 'JobCd=' . $this->encodeStr($this->getJobCd());
		$str .= '&';
		$str .= 'Currency=' . $this->encodeStr($this->getCurrency());
		$str .= '&';
		$str .= 'Amount=' . $this->encodeStr($this->getAmount());
		$str .= '&';
		$str .= 'Tax=' . $this->encodeStr($this->getTax());

		return $str;
	}
}