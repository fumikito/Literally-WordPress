<?php
require_once ('com/gmo_pg/client/input/BaseInput.php');
/**
 * <b>取引照会マルチ　入力パラメータクラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage input
 * @see inputPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 2008-07-15
 */
class SearchTradeMultiInput extends BaseInput {

	/**
	 * @var ショップID GMOPG発行の、加盟店識別ID
	 */
	var $shopId;

	/**
	 * @var string ショップパスワード
	 */
	var $shopPass;

	/**
	 * @var string オーダーID 加盟店様が発番する、取引のID
	 */
	var $orderId;
	
	/**
	 * 決済方法
	 * 0：クレジット 
	 * 1：モバイルSuica 
	 * 2：モバイルEdy 
	 * 3：コンビニ 
	 * 4：Pay-easy
	 * 5：Paypal
	 * 7：WebMoney 
	 * 8：auかんたん決済
	 * 9：ドコモケータイ払い
	 * 
	 * @var string
	 */
	var $payType;
	/**
	 * コンストラクタ
	 *
	 * @param array $params 入力パラメータ
	 */
	function SearchTradeMultiInput($params = null) {
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
	function getShopId() {
		return $this->shopId;
	}

	/**
	 * ショップパスワード取得
	 * @return string ショップパスワード
	 */
	function getShopPass() {
		return $this->shopPass;
	}

	/**
	 * オーダーID
	 * @return string オーダーID
	 */
	function getOrderId() {
		return $this->orderId;
	}
	
	/**
	 * 決済手段取得
	 * @return  　string $string  決済手段
	 */
	function getPayType(){
		return $this->payType ;
	}
	/**
	 * ショップID設定
	 *
	 * @param string $shopId ショップID
	 */
	function setShopId($shopId) {
		$this->shopId = $shopId;
	}

	/**
	 * ショップパスワード設定
	 *
	 * @param string $shopPass ショップパスワード
	 */
	function setShopPass($shopPass) {
		$this->shopPass = $shopPass;
	}

	/**
	 * オーダーID設定
	 *
	 * @param string $orderId オーダーID
	 */
	function setOrderId($orderId) {
		$this->orderId = $orderId;
	}
	
	/**
	 * 決済手段設定
	 * @param  　string $string  決済手段
	 */
	function setPayType($string){
		$this->payType = $string;
	}
	
	
	/**
	 * デフォルト値を設定する
	 */
	function setDefaultValues() {
	}

	/**
	 * 入力パラメータ群の値を設定する
	 *
	 * @param IgnoreCaseMap params 入力パラメータ
	 */
	function setInputValues($params) {
		// 入力パラメータがnullの場合は設定処理を行わない
	    if (is_null($params)) {
	        return;
	    }
	    
	    // 各項目の設定(CardSeqは値が数値でないものは無効とする)
	    $this->setShopId($this->getStringValue($params, 'ShopID', $this->getShopId()));
	    $this->setShopPass($this->getStringValue($params, 'ShopPass', $this->getShopPass()));
        $this->setOrderId($this->getStringValue($params, 'OrderID', $this->getOrderId()));
        $this->setPayType($this->getStringValue($params, 'PayType', $this->getPayType()));
        
	}

	/**
	 * 文字列表現
	 * URLのパラメータ文字列の形式の文字列を生成する
	 * @return string 接続文字列表現
	 */
	function toString() {
	    
	    $str  = 'ShopID=' . $this->encodeStr($this->getShopId());
	    $str .= '&';
	    $str .= 'ShopPass=' . $this->encodeStr($this->getShopPass());
	    $str .= '&';
	    $str .= 'OrderID=' . $this->encodeStr($this->getOrderId());
	    $str .= '&';
	    $str .= 'PayType=' . $this->encodeStr($this->getPayType());
	    
	    return $str;   
	}

}
?>
