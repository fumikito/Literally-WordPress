<?php
require_once 'com/gmo_pg/client/input/EntryTranPayEasyInput.php';
require_once 'com/gmo_pg/client/input/ExecTranPayEasyInput.php';
/**
 * <b>PayEasy登録・決済一括実行　入力パラメータクラス</b>
 *
 * @package com.gmo_pg.client
 * @subpackage input
 * @see inputPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 03-07-2008 00:00:00
 */
class EntryExecTranPayEasyInput {

	/**
	 * @var EntryTranPayEasyInput PayEasy取引登録入力パラメータ
	 */
	var $entryTranPayEasyInput;/* @var $entryTranInput EntryTranPayEasyInput */

	/**
	 * @var ExecTranPayEasyInput PayEasy決済実行入力パラメータ
	 */
	var $execTranPayEasyInput;/* @var $execTranInput ExecTranPayEasyInput */

	/**
	 * コンストラクタ
	 *
	 * @param array $params    入力パラメータ
	 */
	function EntryExecTranPayEasyInput($params = null) {
		$this->__construct($params);
	}

	/**
	 * コンストラクタ
	 *
	 * @param array $params    入力パラメータ
	 */
	function __construct($params = null) {
		$this->entryTranPayEasyInput = new EntryTranPayEasyInput($params);
		$this->execTranPayEasyInput = new ExecTranPayEasyInput($params);
	}

	/**
	 * PayEasy取引登録入力パラメータ取得
	 *
	 * @return EntryTranPayEasyInput 取引登録時パラメータ
	 */
	function &getEntryTranPayEasyInput() {
		return $this->entryTranPayEasyInput;
	}

	/**
	 * PayEasy決済実行入力パラメータ取得
	 * @return ExecTranPayEasyInput 決済実行時パラメータ
	 */
	function &getExecTranPayEasyInput() {
		return $this->execTranPayEasyInput;
	}

	/**
	 * ショップID取得
	 * @return string ショップID
	 */
	function getShopId() {
		return $this->entryTranPayEasyInput->getShopId();
	}

	/**
	 * ショップパスワード取得
	 * @return string ショップパスワード
	 */
	function getShopPass() {
		return $this->entryTranPayEasyInput->getShopPass();
	}

	/**
	 * オーダーID取得
	 * @return string オーダーID
	 */
	function getOrderId() {
		return $this->entryTranPayEasyInput->getOrderId();
	}

	/**
	 * 利用金額取得
	 * @return integer 利用金額
	 */
	function getAmount() {
		return $this->entryTranPayEasyInput->getAmount();
	}

	/**
	 * 税送料取得
	 * @return integer 税送料
	 */
	function getTax() {
		return $this->entryTranPayEasyInput->getTax();
	}

	/**
	 * 取引ID取得
	 * @return string 取引ID
	 */
	function getAccessId() {
		return $this->execTranPayEasyInput->getAccessId();
	}

	/**
	 * 取引パスワード取得
	 * @return string 取引パスワード
	 */
	function getAccessPass() {
		return $this->execTranPayEasyInput->getAccessPass();
	}

	/**
	 * 氏名を取得します。
	 *
	 * @return	$String	氏名
	 */
	function getCustomerName() {
		return $this->execTranPayEasyInput->getCustomerName();
	}


	/**
	 * フリガナを取得します。
	 *
	 * @return	$String	フリガナ
	 */
	function getCustomerKana() {
		return $this->execTranPayEasyInput->getCustomerKana();
	}


	/**
	 * 電話番号を取得します。
	 *
	 * @return	$String	電話番号
	 */
	function getTelNo() {
		return $this->execTranPayEasyInput->getTelNo();
	}

	/**
	 * メールアドレスを取得します。
	 *
	 * @return	$String	メールアドレス
	 */
	function getMailAddress() {
		return $this->execTranPayEasyInput->getMailAddress();
	}


	/**
	 * 加盟店メールアドレスを取得します。
	 *
	 * @return	$String	加盟店メールアドレス
     * @deprecated 下位互換のためのメソッドです。getShopMailAddress()をご利用下さい。
	 */
	function getShopMailAdress() {
		return $this->execTranPayEasyInput->getShopMailAddress();
	}

	/**
	 * 加盟店メールアドレスを取得します。
	 *
	 * @return	$String	加盟店メールアドレス(正)
	 */
	function getShopMailAddress() {
		return $this->execTranPayEasyInput->getShopMailAddress();
	}


	/**
	 * 支払期限日数を取得します。
	 *
	 * @return	$Integer	支払期限日数
	 */
	function getPaymentTermDay() {
		return $this->execTranPayEasyInput->getPaymentTermDay();
	}

	/**
	 * フリースペース1を取得します。
	 *
	 * @return	$String	フリースペース1
	 */
	function getRegisterDisp1() {
		return $this->execTranPayEasyInput->getRegisterDisp1();
	}


	/**
	 * フリースペース2を取得します。
	 *
	 * @return	$String	フリースペース2
	 */
	function getRegisterDisp2() {
		return $this->execTranPayEasyInput->getRegisterDisp2();
	}


	/**
	 * フリースペース3を取得します。
	 *
	 * @return	$String	フリースペース3
	 */
	function getRegisterDisp3() {
		return $this->execTranPayEasyInput->getRegisterDisp3();
	}

	/**
	 * フリースペース4を取得します。
	 *
	 * @return	$String	フリースペース4
	 */
	function getRegisterDisp4() {
		return $this->execTranPayEasyInput->getRegisterDisp4();
	}


	/**
	 * フリースペース5を取得します。
	 *
	 * @return	$String	フリースペース5
	 */
	function getRegisterDisp5() {
		return $this->execTranPayEasyInput->getRegisterDisp5();
	}

	/**
	 * フリースペース6を取得します。
	 *
	 * @return	$String	フリースペース6
	 */
	function getRegisterDisp6() {
		return $this->execTranPayEasyInput->getRegisterDisp6();
	}

	/**
	 * フリースペース7を取得します。
	 *
	 * @return	$String	フリースペース7
	 */
	function getRegisterDisp7() {
		return $this->execTranPayEasyInput->getRegisterDisp7();
	}

	/**
	 * フリースペース8を取得します。
	 *
	 * @return	$String	フリースペース8
	 */
	function getRegisterDisp8() {
		return $this->execTranPayEasyInput->getRegisterDisp8();
	}


	/**
	 * お客様へのご案内1を取得します。
	 *
	 * @return	$String	お客様へのご案内1
	 */
	function getReceiptsDisp1() {
		return $this->execTranPayEasyInput->getReceiptsDisp1();
	}



	/**
	 * お客様へのご案内2を取得します。
	 *
	 * @return	$String	お客様へのご案内2
	 */
	function getReceiptsDisp2() {
		return $this->execTranPayEasyInput->getReceiptsDisp2();
	}



	/**
	 * お客様へのご案内3を取得します。
	 *
	 * @return	$String	お客様へのご案内3
	 */
	function getReceiptsDisp3() {
		return $this->execTranPayEasyInput->getReceiptsDisp3();
	}


	/**
	 * お客様へのご案内4を取得します。
	 *
	 * @return	$String	お客様へのご案内4
	 */
	function getReceiptsDisp4() {
		return $this->execTranPayEasyInput->getReceiptsDisp4();
	}



	/**
	 * お客様へのご案内5を取得します。
	 *
	 * @return	$String	お客様へのご案内5
	 */
	function getReceiptsDisp5() {
		return $this->execTranPayEasyInput->getReceiptsDisp5();
	}


	/**
	 * お客様へのご案内6を取得します。
	 *
	 * @return	$String	お客様へのご案内6
	 */
	function getReceiptsDisp6() {
		return $this->execTranPayEasyInput->getReceiptsDisp6();
	}


	/**
	 * お客様へのご案内7を取得します。
	 *
	 * @return	$String	お客様へのご案内7
	 */
	function getReceiptsDisp7() {
		return $this->execTranPayEasyInput->getReceiptsDisp7();
	}



	/**
	 * お客様へのご案内8を取得します。
	 *
	 * @return	$String	お客様へのご案内8
	 */
	function getReceiptsDisp8() {
		return $this->execTranPayEasyInput->getReceiptsDisp8();
	}


	/**
	 * お客様へのご案内9を取得します。
	 *
	 * @return	$String	お客様へのご案内9
	 */
	function getReceiptsDisp9() {
		return $this->execTranPayEasyInput->getReceiptsDisp9();
	}


	/**
	 * お客様へのご案内10を取得します。
	 *
	 * @return	$String	お客様へのご案内10
	 */
	function getReceiptsDisp10() {
		return $this->execTranPayEasyInput->getReceiptsDisp10();
	}

	/**
	 * お問合せ先を取得します。
	 *
	 * @return	$String	お問合せ先
	 */
	function getReceiptsDisp11() {
		return $this->execTranPayEasyInput->getReceiptsDisp11();
	}

	/**
	 * お問合せ先電話番号を取得します。
	 *
	 * @return	$String	お問合せ先電話番号
	 */
	function getReceiptsDisp12() {
		return $this->execTranPayEasyInput->getReceiptsDisp12();
	}

	/**
	 * お問合せ先受付時間を取得します。
	 *
	 * @return	$String	お問合せ先受付時間
	 */
	function getReceiptsDisp13() {
		return $this->execTranPayEasyInput->getReceiptsDisp13();
	}


	/**
	 * 加盟店自由項目1取得
	 * @return string 加盟店自由項目1
	 */
	function getClientField1() {
		return $this->execTranPayEasyInput->getClientField1();
	}

	/**
	 * 加盟店自由項目2取得
	 * @return string 加盟店自由項目2
	 */
	function getClientField2() {
		return $this->execTranPayEasyInput->getClientField2();
	}

	/**
	 * 加盟店自由項目3取得
	 * @return string 加盟店自由項目3
	 */
	function getClientField3() {
		return $this->execTranPayEasyInput->getClientField3();
	}

	/**
	 * PayEasy取引登録入力パラメータ設定
	 *
	 * @param EntryTranPayEasyInput entryTranPayEasyInput  PayEasy取引登録入力パラメータ
	 */
	function setEntryTranPayEasyInput(&$entryTranPayEasyInput) {
		$this->entryTranPayEasyInput = $entryTranPayEasyInput;
	}

	/**
	 * PayEasy決済実行入力パラメータ設定
	 *
	 * @param ExecTranPayEasyInput  execTranPayEasyInput   PayEasy決済実行入力パラメータ
	 */
	function setExecTranPayEasyInput(&$execTranPayEasyInput) {
		$this->execTranPayEasyInput = $execTranPayEasyInput;
	}

	/**
	 * ショップID設定
	 *
	 * @param string $shopId
	 */
	function setShopId($shopId) {
		$this->entryTranPayEasyInput->setShopId($shopId);
	}

	/**
	 * ショップパスワード設定
	 *
	 * @param string $shopPass
	 */
	function setShopPass($shopPass) {
		$this->entryTranPayEasyInput->setShopPass($shopPass);
	}

	/**
	 * オーダーID設定
	 *
	 * @param string $orderId
	 */
	function setOrderId($orderId) {
		$this->entryTranPayEasyInput->setOrderId($orderId);
		$this->execTranPayEasyInput->setOrderId($orderId);
	}

	/**
	 * 利用金額設定
	 *
	 * @param integer $amount
	 */
	function setAmount($amount) {
		$this->entryTranPayEasyInput->setAmount($amount);
	}

	/**
	 * 税送料設定
	 *
	 * @param integer $tax
	 */
	function setTax($tax) {
		$this->entryTranPayEasyInput->setTax($tax);
	}

	/**
	 * 取引ID設定
	 *
	 * @param string $accessId
	 */
	function setAccessId($accessId) {
		$this->entryTranPayEasyInput->setAccessId($accessId);
	}

	/**
	 * 取引パスワード設定
	 *
	 * @param string $accessPass
	 */
	function setAccessPass($accessPass) {
		$this->entryTranPayEasyInput->setAccessPass($accessPass);
	}


	/**
	 * 氏名を格納します。
	 *
	 * @param	$String	氏名
	 */
	function setCustomerName($String) {
		$this->entryTranPayEasyInput->setConvenience($String);
	}

	/**
	 * フリガナを格納します。
	 *
	 * @param	$String	フリガナ
	 */
	function setCustomerKana($String) {
		$this->entryTranPayEasyInput->setConvenience($String);
	}

	/**
	 * 電話番号を格納します。
	 *
	 * @param	$String	電話番号
	 */
	function setTelNo($String) {
		$this->entryTranPayEasyInput->setConvenience($String);
	}
	/**
	 * メールアドレスを格納します。
	 *
	 * @param	$String	メールアドレス
	 */
	function setMailAddress($String) {
		$this->execTranPayEasyInput->setMailAddress($String);
	}

	/**
	 * 加盟店メールアドレスを格納します。
	 *
     * @deprecated 下位互換のためのメソッドです。setShopMailAddress()をご利用下さい。
	 * @param	$String	加盟店メールアドレス
	 */
	function setShopMailAdress($String) {
		$this->execTranPayEasyInput->setShopMailAddress($String);
	}

	/**
	 * 加盟店メールアドレスを格納します。
	 *
	 * @param	$String	加盟店メールアドレス(正)
	 */
	function setShopMailAddress($String) {
		$this->execTranPayEasyInput->setShopMailAddress($String);
	}


	/**
	 * 支払期限日数を格納します。
	 *
	 * @param	$Integer	支払期限日数
	 */
	function setPaymentTermDay($Integer) {
		$this->execTranPayEasyInput->setPaymentTermDay($Integer);
	}

	/**
	 * フリースペース1を格納します。
	 *
	 * @param	$String	フリースペース1
	 */
	function setRegisterDisp1($String) {
		$this->execTranPayEasyInput->setRegisterDisp1($String);
	}

	/**
	 * フリースペース2を格納します。
	 *
	 * @param	$String	フリースペース2
	 */
	function setRegisterDisp2($String) {
		$this->execTranPayEasyInput->setRegisterDisp2($String);
	}

	/**
	 * フリースペース3を格納します。
	 *
	 * @param	$String	フリースペース3
	 */
	function setRegisterDisp3($String) {
		$this->execTranPayEasyInput->setRegisterDisp3($String);
	}

	/**
	 * フリースペース4を格納します。
	 *
	 * @param	$String	フリースペース4
	 */
	function setRegisterDisp4($String) {
		$this->execTranPayEasyInput->setRegisterDisp4($String);
	}

	/**
	 * フリースペース5を格納します。
	 *
	 * @param	$String	フリースペース5
	 */
	function setRegisterDisp5($String) {
		$this->execTranPayEasyInput->setRegisterDisp5($String);
	}

	/**
	 * フリースペース6を格納します。
	 *
	 * @param	$String	フリースペース6
	 */
	function setRegisterDisp6($String) {
		$this->execTranPayEasyInput->setRegisterDisp6($String);
	}

	/**
	 * フリースペース7を格納します。
	 *
	 * @param	$String	フリースペース7
	 */
	function setRegisterDisp7($String) {
		$this->execTranPayEasyInput->setRegisterDisp7($String);
	}

	/**
	 * フリースペース8を格納します。
	 *
	 * @param	$String	フリースペース8
	 */
	function setRegisterDisp8($String) {
		$this->execTranPayEasyInput->setRegisterDisp8($String);
	}

	/**
	 * お客様へのご案内1を格納します。
	 *
	 * @param	$String	お客様へのご案内1
	 */
	function setReceiptsDisp1($String) {
		$this->execTranPayEasyInput->setReceiptsDisp1($String);
	}

	/**
	 * お客様へのご案内2を格納します。
	 *
	 * @param	$String	お客様へのご案内2
	 */
	function setReceiptsDisp2($String) {
		$this->execTranPayEasyInput->setReceiptsDisp2($String);
	}

	/**
	 * お客様へのご案内3を格納します。
	 *
	 * @param	$String	お客様へのご案内3
	 */
	function setReceiptsDisp3($String) {
		$this->execTranPayEasyInput->setReceiptsDisp3($String);
	}

	/**
	 * お客様へのご案内4を格納します。
	 *
	 * @param	$String	お客様へのご案内4
	 */
	function setReceiptsDisp4($String) {
		$this->execTranPayEasyInput->setReceiptsDisp4($String);
	}

	/**
	 * お客様へのご案内5を格納します。
	 *
	 * @param	$String	お客様へのご案内5
	 */
	function setReceiptsDisp5($String) {
		$this->execTranPayEasyInput->setReceiptsDisp5($String);
	}

	/**
	 * お客様へのご案内6を格納します。
	 *
	 * @param	$String	お客様へのご案内6
	 */
	function setReceiptsDisp6($String) {
		$this->execTranPayEasyInput->setReceiptsDisp6($String);
	}

	/**
	 * お客様へのご案内7を格納します。
	 *
	 * @param	$String	お客様へのご案内7
	 */
	function setReceiptsDisp7($String) {
		$this->execTranPayEasyInput->setReceiptsDisp7($String);
	}

	/**
	 * お客様へのご案内8を格納します。
	 *
	 * @param	$String	お客様へのご案内8
	 */
	function setReceiptsDisp8($String) {
		$this->execTranPayEasyInput->setReceiptsDisp8($String);
	}

	/**
	 * お客様へのご案内9を格納します。
	 *
	 * @param	$String	お客様へのご案内9
	 */
	function setReceiptsDisp9($String) {
		$this->execTranPayEasyInput->setReceiptsDisp9($String);
	}

	/**
	 * お客様へのご案内10を格納します。
	 *
	 * @param	$String	お客様へのご案内10
	 */
	function setReceiptsDisp10($String) {
		$this->execTranPayEasyInput->setReceiptsDisp10($String);
	}

	/**
	 * お問合せ先を格納します。
	 *
	 * @param	$String	お問合せ先
	 */
	function setReceiptsDisp11($String) {
		$this->execTranPayEasyInput->setReceiptsDisp11($String);
	}

	/**
	 * お問合せ先電話番号を格納します。
	 *
	 * @param	$String	お問合せ先電話番号
	 */
	function setReceiptsDisp12($String) {
		$this->execTranPayEasyInput->setReceiptsDisp12($String);
	}

	/**
	 * お問合せ先受付時間を格納します。
	 *
	 * @param	$String	お問合せ先受付時間
	 */
	function setReceiptsDisp13($String) {
		$this->execTranPayEasyInput->setReceiptsDisp13($String);
	}




	/**
	 * 加盟店自由項目1設定
	 *
	 * @param string $clientField1
	 */
	function setClientField1($clientField1) {
		$this->execTranPayEasyInput->setClientField1($clientField1);
	}

	/**
	 * 加盟店自由項目2設定
	 *
	 * @param string $clientField2
	 */
	function setClientField2($clientField2) {
		$this->execTranPayEasyInput->setClientField2($clientField2);
	}


	/**
	 * 加盟店自由項目3設定
	 *
	 * @param string $clientField3
	 */
	function setClientField3($clientField3) {
		$this->execTranPayEasyInput->setClientField3($clientField3);
	}

}
?>