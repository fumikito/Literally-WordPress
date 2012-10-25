<?php
require_once 'com/gmo_pg/client/input/EntryTranEdyInput.php';
require_once 'com/gmo_pg/client/input/ExecTranEdyInput.php';
/**
 * <b>モバイルEdy登録・決済一括実行　入力パラメータクラス</b>
 *
 * @package com.gmo_pg.client
 * @subpackage input
 * @see inputPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 03-07-2008 00:00:00
 */
class EntryExecTranEdyInput {

	/**
	 * @var EntryTranEdyInput モバイルEdy取引登録入力パラメータ
	 */
	var $entryTranEdyInput;/* @var $entryTranInput EntryTranEdyInput */

	/**
	 * @var ExecTranEdyInput モバイルEdy決済実行入力パラメータ
	 */
	var $execTranEdyInput;/* @var $execTranInput ExecTranEdyInput */

	/**
	 * コンストラクタ
	 *
	 * @param array $params    入力パラメータ
	 */
	function EntryExecTranEdyInput($params = null) {
		$this->__construct($params);
	}

	/**
	 * コンストラクタ
	 *
	 * @param array $params    入力パラメータ
	 */
	function __construct($params = null) {
		$this->entryTranEdyInput = new EntryTranEdyInput($params);
		$this->execTranEdyInput = new ExecTranEdyInput($params);
	}

	/**
	 * モバイルEdy取引登録入力パラメータ取得
	 *
	 * @return EntryTranEdyInput 取引登録時パラメータ
	 */
	function &getEntryTranEdyInput() {
		return $this->entryTranEdyInput;
	}

	/**
	 * モバイルEdy決済実行入力パラメータ取得
	 * @return ExecTranEdyInput 決済実行時パラメータ
	 */
	function &getExecTranEdyInput() {
		return $this->execTranEdyInput;
	}

	/**
	 * ショップID取得
	 * @return string ショップID
	 */
	function getShopId() {
		return $this->entryTranEdyInput->getShopId();
	}

	/**
	 * ショップパスワード取得
	 * @return string ショップパスワード
	 */
	function getShopPass() {
		return $this->entryTranEdyInput->getShopPass();
	}

	/**
	 * オーダーID取得
	 * @return string オーダーID
	 */
	function getOrderId() {
		return $this->entryTranEdyInput->getOrderId();
	}

		/**
	 * 利用金額取得
	 * @return integer 利用金額
	 */
	function getAmount() {
		return $this->entryTranEdyInput->getAmount();
	}

	/**
	 * 税送料取得
	 * @return integer 税送料
	 */
	function getTax() {
		return $this->entryTranEdyInput->getTax();
	}

	/**
	 * 取引ID取得
	 * @return string 取引ID
	 */
	function getAccessId() {
		return $this->execTranEdyInput->getAccessId();
	}

	/**
	 * 取引パスワード取得
	 * @return string 取引パスワード
	 */
	function getAccessPass() {
		return $this->execTranEdyInput->getAccessPass();
	}



	/**
	 * メールアドレスを取得します。
	 *
	 * @return	$String	メールアドレス
	 */
	function getMailAddress() {
		return $this->execTranEdyInput->getMailAddress();
	}

	/**
	 * 加盟店メールアドレスを取得します。
	 *
     * @deprecated 下位互換のためのメソッドです。getShopMailAddress()をご利用下さい。
	 * @return	$String	加盟店メールアドレス
	 */
	function getShopMailAdress() {
		return $this->execTranEdyInput->getShopMailAddress();
	}

	/**
	 * 加盟店メールアドレスを取得します。
	 *
	 * @return	$String	加盟店メールアドレス(正)
	 */
	function getShopMailAddress() {
		return $this->execTranEdyInput->getShopMailAddress();
	}

	/**
	 * 決済開始メール付加情報を取得します。
	 *
	 * @return	$String	決済開始メール付加情報
	 */
	function getEdyAddInfo1() {
		return $this->execTranEdyInput->getEdyAddInfo1();
	}

	/**
	 * 決済完了メール付加情報を取得します。
	 *
	 * @return	$String	決済完了メール付加情報
	 */
	function getEdyAddInfo2() {
		return $this->execTranEdyInput->getEdyAddInfo2();
	}




	/**
	 * 支払期限日数を取得します。
	 *
	 * @return	$Integer	支払期限日数
	 */
	function getPaymentTermDay() {
		return $this->execTranEdyInput->getPaymentTermDay();
	}


	/**
	 * 支払期限秒を取得します。
	 *
	 * @return	$Integer	支払期限秒
	 */
	function getPaymentTermSec() {
		return $this->execTranEdyInput->getPaymentTermSec();
	}


	/**
	 * 加盟店自由項目1取得
	 * @return string 加盟店自由項目1
	 */
	function getClientField1() {
		return $this->execTranEdyInput->getClientField1();
	}

	/**
	 * 加盟店自由項目2取得
	 * @return string 加盟店自由項目2
	 */
	function getClientField2() {
		return $this->execTranEdyInput->getClientField2();
	}

	/**
	 * 加盟店自由項目3取得
	 * @return string 加盟店自由項目3
	 */
	function getClientField3() {
		return $this->execTranEdyInput->getClientField3();
	}

	/**
	 * モバイルEdy取引登録入力パラメータ設定
	 *
	 * @param EntryTranEdyInput entryTranEdyInput  モバイルEdy取引登録入力パラメータ
	 */
	function setEntryTranEdyInput(&$entryTranEdyInput) {
		$this->entryTranEdyInput = $entryTranEdyInput;
	}

	/**
	 * モバイルEdy決済実行入力パラメータ設定
	 *
	 * @param ExecTranEdyInput  execTranEdyInput   モバイルEdy決済実行入力パラメータ
	 */
	function setExecTranEdyInput(&$execTranEdyInput) {
		$this->execTranEdyInput = $execTranEdyInput;
	}

	/**
	 * ショップID設定
	 *
	 * @param string $shopId
	 */
	function setShopId($shopId) {
		$this->entryTranEdyInput->setShopId($shopId);
	}

	/**
	 * ショップパスワード設定
	 *
	 * @param string $shopPass
	 */
	function setShopPass($shopPass) {
		$this->entryTranEdyInput->setShopPass($shopPass);
	}

	/**
	 * オーダーID設定
	 *
	 * @param string $orderId
	 */
	function setOrderId($orderId) {
		$this->entryTranEdyInput->setOrderId($orderId);
		$this->execTranEdyInput->setOrderId($orderId);
	}

	/**
	 * 利用金額設定
	 *
	 * @param integer $amount
	 */
	function setAmount($amount) {
		$this->entryTranEdyInput->setAmount($amount);
	}

	/**
	 * 税送料設定
	 *
	 * @param integer $tax
	 */
	function setTax($tax) {
		$this->entryTranEdyInput->setTax($tax);
	}

	/**
	 * 取引ID設定
	 *
	 * @param string $accessId
	 */
	function setAccessId($accessId) {
		$this->entryTranEdyInput->setAccessId($accessId);
	}

	/**
	 * 取引パスワード設定
	 *
	 * @param string $accessPass
	 */
	function setAccessPass($accessPass) {
		$this->entryTranEdyInput->setAccessPass($accessPass);
	}



	/**
	 * メールアドレスを格納します。
	 *
	 * @param	$String	メールアドレス
	 */
	function setMailAddress($String) {
		$this->execTranEdyInput->setMailAddress($String);
	}

	/**
	 * 加盟店メールアドレスを格納します。
	 *
     * @deprecated 下位互換のためのメソッドです。setShopMailAddress()をご利用下さい。
	 * @param	$String	加盟店メールアドレス
	 */
	function setShopMailAdress($String) {
		$this->execTranEdyInput->setShopMailAddress($String);
	}

	/**
	 * 加盟店メールアドレスを格納します。
	 *
	 * @param	$String	加盟店メールアドレス(正)
	 */
	function setShopMailAddress($String) {
		$this->execTranEdyInput->setShopMailAddress($String);
	}

	/**
	 * 決済開始メール付加情報を格納します。
	 *
	 * @param	$String	決済開始メール付加情報
	 */
	function setEdyAddInfo1($String) {
		$this->execTranEdyInput->setEdyAddInfo1($string);
	}

	/**
	 * 決済完了メール付加情報を格納します。
	 *
	 * @param	$String	決済完了メール付加情報
	 */
	function setEdyAddInfo2($String) {
		$this->execTranEdyInput->setEdyAddInfo2($string);
	}






	/**
	 * 支払期限日数を格納します。
	 *
	 * @param	$Integer	支払期限日数
	 */
	function setPaymentTermDay($Integer) {
		$this->execTranEdyInput->setPaymentTermDay($Integer);
	}


	/**
	 * 支払期限秒を格納します。
	 *
	 * @param	$Integer	支払期限秒
	 */
	function setPaymentTermSec($Integer) {
		$this->execTranEdyInput->setPaymentTermSec($Integer);
	}



	/**
	 * 加盟店自由項目1設定
	 *
	 * @param string $clientField1
	 */
	function setClientField1($clientField1) {
		$this->execTranEdyInput->setClientField1($clientField1);
	}

	/**
	 * 加盟店自由項目2設定
	 *
	 * @param string $clientField2
	 */
	function setClientField2($clientField2) {
		$this->execTranEdyInput->setClientField2($clientField2);
	}


	/**
	 * 加盟店自由項目3設定
	 *
	 * @param string $clientField3
	 */
	function setClientField3($clientField3) {
		$this->execTranEdyInput->setClientField3($clientField3);
	}

}
?>