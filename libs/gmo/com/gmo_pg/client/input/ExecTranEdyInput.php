<?php
require_once ('com/gmo_pg/client/input/BaseInput.php');
/**
 * <b>モバイルEdy決済実行　入力パラメータクラス</b>
 *
 * @package com.gmo_pg.client
 * @subpackage input
 * @see inputPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 02-07-2008 00:00:00
 */
class ExecTranEdyInput extends BaseInput {

	/**
	 * @var string 取引ID。GMO-PGが払い出した、取引を特定するID
	 */
	var $accessId;

	/**
	 * @var string 取引パスワード。取引IDと対になるパスワード
	 */
	var $accessPass;

	/**
	 * @var string オーダーID。加盟店様が発番した、取引を表すID
	 */
	var $orderId;

	/**
	 * @var string メールアドレス
	 */
	var $mailAddress;

	/**
	 * @var string 加盟店メールアドレス(正)
	 */
	var $shopMailAddress;

	/**
	 * @var string 決済開始メール付加情報
	 */
	var $EdyAddInfo1;

	/**
	 * @var string 決済完了メール付加情報
	 */
	var $EdyAddInfo2;


	/**
	 * @var integer 支払期限日数
	 */
	var $paymentTermDay;

	/**
	 * @var integer 支払期限秒
	 */
	var $paymentTermSec;

	/**
	 * @var string 加盟店自由項目1
	 */
	var $clientField1;

	/**
	 * @var string 加盟店自由項目
	 */
	var $clientField2;

	/**
	 * @var string 加盟店自由項目3
	 */
	var $clientField3;

	/**
	 * @var string 加盟店自由項目返却フラグ
	 */
	var $clientFieldFlag;

	/**
	 * コンストラクタ
	 *
	 * @param array $params 入力パラメータ
	 */
	function ExecTranEdyInput($params = null) {
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
	 * デフォルト値を設定する
	 */
	function setDefaultValues() {
	    // 加盟店自由項目返却フラグ(固定値)
        $this->clientFieldFlag = "1";
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

	    // 各項目の設定
        $this->setAccessId($this->getStringValue($params, 'AccessID', $this->getAccessId()));
	    $this->setAccessPass($this->getStringValue($params, 'AccessPass', $this->getAccessPass()));
	    $this->setOrderId($this->getStringValue($params, 'OrderID', $this->getOrderId()));

	    $this->setMailAddress($this->getStringValue($params, 'MailAddress', $this->getMailAddress()));
	    // スペルミス対応
	    if(isset($params['ShopMailAddress']))
	    	$this->setShopMailAddress($this->getStringValue($params, 'ShopMailAddress', $this->getShopMailAddress()));
	    else
	    	$this->setShopMailAddress($this->getStringValue($params, 'ShopMailAdress', $this->getShopMailAddress()));
	    $this->setEdyAddInfo1($this->getStringValue($params, 'EdyAddInfo1', $this->getEdyAddInfo1()));
	    $this->setEdyAddInfo2($this->getStringValue($params, 'EdyAddInfo2', $this->getEdyAddInfo2()));
	    $this->setPaymentTermDay($this->getIntegerValue($params, 'PaymentTermDay', $this->getPaymentTermDay()));
	    $this->setPaymentTermSec($this->getIntegerValue($params, 'PaymentTermSec', $this->getPaymentTermSec()));

	    $this->setClientField1($this->getStringValue($params, 'ClientField1', $this->getClientField1()));
	    $this->setClientField2($this->getStringValue($params, 'ClientField2', $this->getClientField2()));
	    $this->setClientField3($this->getStringValue($params, 'ClientField3', $this->getClientField3()));
	}

	/**
	 * 取引ID取得
	 * @return string 取引ID
	 */
	function getAccessId() {
		return $this->accessId;
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
	function getOrderId() {
		return $this->orderId;
	}


	/**
	 * メールアドレスを取得します。
	 *
	 * @return	$String	メールアドレス
	 */
	function getMailAddress() {
		return $this->mailAddress;
	}

	/**
	 * 加盟店メールアドレスを取得します。
	 *
   * @deprecated 下位互換のためのメソッドです。getShopMailAddress()をご利用下さい。
	 * @return	$String	加盟店メールアドレス
	 */
	function getShopMailAdress() {
		return $this->shopMailAddress;
	}


	/**
	 * 加盟店メールアドレスを取得します。
	 *
	 * @return	$String	加盟店メールアドレス(正)
	 */
	function getShopMailAddress() {
		return $this->shopMailAddress;
	}

	/**
	 * 決済開始メール付加情報を取得します。
	 *
	 * @return	$String	決済開始メール付加情報
	 */
	function getEdyAddInfo1() {
		return $this->EdyAddInfo1;
	}

	/**
	 * 決済完了メール付加情報を取得します。
	 *
	 * @return	$String	決済完了メール付加情報
	 */
	function getEdyAddInfo2() {
		return $this->EdyAddInfo2;
	}




	/**
	 * 支払期限日数を取得します。
	 *
	 * @return	$Integer	支払期限日数
	 */
	function getPaymentTermDay() {
		return $this->paymentTermDay;
	}


	/**
	 * 支払期限秒を取得します。
	 *
	 * @return	$Integer	支払期限秒
	 */
	function getPaymentTermSec() {
		return $this->paymentTermSec;
	}



	/**
	 * 加盟店自由項目1取得
	 * @return string 加盟店自由項目1
	 */
	function getClientField1() {
		return $this->clientField1;
	}

	/**
	 * 加盟店自由項目2取得
	 * @return string 加盟店自由項目2
	 */
	function getClientField2() {
		return $this->clientField2;
	}

	/**
	 * 加盟店自由項目3取得
	 * @return string 加盟店自由項目3
	 */
	function getClientField3() {
		return $this->clientField3;
	}

	/**
	 * 取引ID設定
	 *
	 * @param string $accessId 取引ID
	 */
	function setAccessId($accessId) {
		$this->accessId = $accessId;
	}

	/**
	 * 取引パスワードを設定
	 *
	 * @param string $accessPass 取引パスワード
	 */
	function setAccessPass($accessPass) {
		$this->accessPass = $accessPass;
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
	 * メールアドレスを格納します。
	 *
	 * @param	$String	メールアドレス
	 */
	function setMailAddress($String) {
		$this->mailAddress = $String;
	}


	/**
	 * 加盟店メールアドレスを格納します。
	 *
   * @deprecated 下位互換のためのメソッドです。setShopMailAddress()をご利用下さい。
	 * @param	$String	加盟店メールアドレス
	 */
	function setShopMailAdress($String) {
		$this->shopMailAddress = $String;
	}


	/**
	 * 加盟店メールアドレスを格納します。
	 *
	 * @param	$String	加盟店メールアドレス(正)
	 */
	function setShopMailAddress($String) {
		$this->shopMailAddress = $String;
	}


	/**
	 * 決済開始メール付加情報を格納します。
	 *
	 * @param	$String	決済開始メール付加情報
	 */
	function setEdyAddInfo1($String) {
		$this->EdyAddInfo1 = $String;
	}



	/**
	 * 決済完了メール付加情報を格納します。
	 *
	 * @param	$String	決済完了メール付加情報
	 */
	function setEdyAddInfo2($String) {
		$this->EdyAddInfo2 = $String;
	}






	/**
	 * 支払期限日数を格納します。
	 *
	 * @param	$Integer	支払期限日数
	 */
	function setPaymentTermDay($Integer) {
		$this->paymentTermDay = $Integer;
	}


	/**
	 * 支払期限秒を格納します。
	 *
	 * @param	$Integer	支払期限秒
	 */
	function setPaymentTermSec($Integer) {
		$this->paymentTermSec = $Integer;
	}







	/**
	 * 加盟店自由項目1設定
	 *
	 * @param string $clientField1 加盟店自由項目1
	 */
	function setClientField1($clientField1) {
		$this->clientField1 = $clientField1;
	}

	/**
	 * 加盟店自由項目2設定
	 *
	 * @param string $clientField2 加盟店自由項目2
	 */
	function setClientField2($clientField2) {
		$this->clientField2 = $clientField2;
	}

	/**
	 * 加盟店自由項目3設定
	 *
	 * @param string $clientField3 加盟店自由項目3
	 */
	function setClientField3($clientField3) {
		$this->clientField3 = $clientField3;
	}

	/**
	 * 文字列表現
	 * URLのパラメータ文字列の形式の文字列を生成する
	 * @return string 接続文字列表現
	 */
	function toString() {
	    $str .= 'AccessID=' . $this->encodeStr($this->getAccessId());
	    $str .= '&';
	    $str .= 'AccessPass=' . $this->encodeStr($this->getAccessPass());
	    $str .= '&';
	    $str .= 'OrderID=' . $this->encodeStr($this->getOrderId());
	    $str .= '&';
	    $str .= 'MailAddress=' . $this->encodeStr($this->getMailAddress());
	    $str .= '&';
	    $str .= 'ShopMailAddress=' . $this->encodeStr($this->getShopMailAddress());
	    $str .= '&';
	    $str .= 'EdyAddInfo1=' . $this->encodeStr($this->getEdyAddInfo1());
	    $str .= '&';
	    $str .= 'EdyAddInfo2=' . $this->encodeStr($this->getEdyAddInfo2());
	    $str .= '&';
	    $str .= 'PaymentTermDay=' . $this->encodeStr($this->getPaymentTermDay());
	    $str .= '&';
	    $str .= 'PaymentTermSec=' . $this->encodeStr($this->getPaymentTermSec());
	    $str .= '&';
	    $str .= 'ClientField1=' . $this->encodeStr($this->getClientField1());
	    $str .= '&';
	    $str .= 'ClientField2=' . $this->encodeStr($this->getClientField2());
	    $str .= '&';
	    $str .= 'ClientField3=' . $this->encodeStr($this->getClientField3());
	    $str .= '&';
	    $str .= 'ClientFieldFlag=' . $this->clientFieldFlag;

	    return $str;
	}
}
?>