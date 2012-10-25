<?php
require_once 'com/gmo_pg/client/input/EntryTranDocomoInput.php';
require_once 'com/gmo_pg/client/input/ExecTranDocomoInput.php';
/**
 * <b>ドコモケータイ払い登録・決済一括実行　入力パラメータクラス</b>
 *
 * @package com.gmo_pg.client
 * @subpackage input
 * @see inputPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 2012/06/14
 */
class EntryExecTranDocomoInput {

	/**
	 * @var EntryTranDocomoInput ドコモケータイ払い登録入力パラメータ
	 */
	var $entryTranDocomoInput;/* @var $entryTranInput EntryTranDocomoInput */

	/**
	 * @var ExecTranDocomoInput ドコモケータイ払い実行入力パラメータ
	 */
	var $execTranDocomoInput;/* @var $execTranInput ExecTranDocomoInput */

	/**
	 * コンストラクタ
	 *
	 * @param array $params    入力パラメータ
	 */
	function EntryExecTranDocomoInput($params = null) {
		$this->__construct($params);
	}

	/**
	 * コンストラクタ
	 *
	 * @param array $params    入力パラメータ
	 */
	function __construct($params = null) {
		$this->entryTranDocomoInput = new EntryTranDocomoInput($params);
		$this->execTranDocomoInput = new ExecTranDocomoInput($params);
	}

	/**
	 * ドコモケータイ払い取引登録入力パラメータ取得
	 *
	 * @return EntryTranDocomoInput 取引登録時パラメータ
	 */
	function &getEntryTranDocomoInput() {
		return $this->entryTranDocomoInput;
	}

	/**
	 * ドコモケータイ払い実行入力パラメータ取得
	 * @return ExecTranDocomoInput 決済実行時パラメータ
	 */
	function &getExecTranDocomoInput() {
		return $this->execTranDocomoInput;
	}

	/**
	 * ショップID取得
	 * @return string ショップID
	 */
	function getShopID() {
		return $this->entryTranDocomoInput->getShopID();
	}

	/**
	 * ショップパスワード取得
	 * @return string ショップパスワード
	 */
	function getShopPass() {
		return $this->entryTranDocomoInput->getShopPass();
	}

	/**
	 * オーダーID取得
	 * @return string オーダーID
	 */
	function getOrderID() {
		return $this->entryTranDocomoInput->getOrderID();
	}

	/**
	 * 処理区分取得
	 * @return string 処理区分
	 */
	function getJobCd() {
		return $this->entryTranDocomoInput->getJobCd();
	}

	/**
	 * 利用金額取得
	 * @return string 利用金額
	 */
	function getAmount() {
		return $this->entryTranDocomoInput->getAmount();
	}

	/**
	 * 税送料取得
	 * @return string 税送料
	 */
	function getTax() {
		return $this->entryTranDocomoInput->getTax();
	}

	/**
	 * 取引ID取得
	 * @return string 取引ID
	 */
	function getAccessID() {
		return $this->execTranDocomoInput->getAccessID();
	}

	/**
	 * 取引パスワード取得
	 * @return string 取引パスワード
	 */
	function getAccessPass() {
		return $this->execTranDocomoInput->getAccessPass();
	}

	/**
	 * 加盟店自由項目1取得
	 * @return string 加盟店自由項目1
	 */
	function getClientField1() {
		return $this->execTranDocomoInput->getClientField1();
	}

	/**
	 * 加盟店自由項目2取得
	 * @return string 加盟店自由項目2
	 */
	function getClientField2() {
		return $this->execTranDocomoInput->getClientField2();
	}

	/**
	 * 加盟店自由項目3取得
	 * @return string 加盟店自由項目3
	 */
	function getClientField3() {
		return $this->execTranDocomoInput->getClientField3();
	}

	/**
	 * ドコモ表示項目1取得
	 * @return string ドコモ表示項目1
	 */
	function getDocomoDisp1() {
		return $this->execTranDocomoInput->getDocomoDisp1();
	}

	/**
	 * ドコモ表示項目2取得
	 * @return string ドコモ表示項目2
	 */
	function getDocomoDisp2() {
		return $this->execTranDocomoInput->getDocomoDisp2();
	}

	/**
	 * 決済結果戻しURL取得
	 * @return string 決済結果戻しURL
	 */
	function getRetURL() {
		return $this->execTranDocomoInput->getRetURL();
	}

	/**
	 * 支払開始期限秒取得
	 * @return string 支払開始期限秒
	 */
	function getPaymentTermSec() {
		return $this->execTranDocomoInput->getPaymentTermSec();
	}

	/**
	 * ドコモケータイ払い取引登録入力パラメータ設定
	 *
	 * @param EntryTranDocomoInput entryTranDocomoInput  取引登録入力パラメータ
	 */
	function setEntryTranDocomoInput(&$entryTranDocomoInput) {
		$this->entryTranDocomoInput = $entryTranDocomoInput;
	}

	/**
	 * ドコモケータイ払い実行入力パラメータ設定
	 *
	 * @param ExecTranDocomoInput  execTranDocomoInput   決済実行入力パラメータ
	 */
	function setExecTranDocomoInput(&$execTranDocomoInput) {
		$this->execTranDocomoInput = $execTranDocomoInput;
	}

	/**
	 * ショップID設定
	 *
	 * @param string $shopID
	 */
	function setShopID($shopID) {
		$this->entryTranDocomoInput->setShopID($shopID);
		$this->execTranDocomoInput->setShopID($shopID);
	}

	/**
	 * ショップパスワード設定
	 *
	 * @param string $shopPass
	 */
	function setShopPass($shopPass) {
		$this->entryTranDocomoInput->setShopPass($shopPass);
		$this->execTranDocomoInput->setShopPass($shopPass);
	}

	/**
	 * オーダーID設定
	 *
	 * @param string $orderID
	 */
	function setOrderID($orderID) {
		$this->entryTranDocomoInput->setOrderID($orderID);
		$this->execTranDocomoInput->setOrderID($orderID);
	}

	/**
	 * 処理区分設定
	 *
	 * @param string $jobCd
	 */
	function setJobCd($jobCd) {
		$this->entryTranDocomoInput->setJobCd($jobCd);
	}

	/**
	 * 利用金額設定
	 *
	 * @param string $amount
	 */
	function setAmount($amount) {
		$this->entryTranDocomoInput->setAmount($amount);
	}

	/**
	 * 税送料設定
	 *
	 * @param string $tax
	 */
	function setTax($tax) {
		$this->entryTranDocomoInput->setTax($tax);
	}

	/**
	 * 取引ID設定
	 *
	 * @param string $accessID
	 */
	function setAccessID($accessID) {
		$this->execTranDocomoInput->setAccessID($accessID);
	}

	/**
	 * 取引パスワード設定
	 *
	 * @param string $accessPass
	 */
	function setAccessPass($accessPass) {
		$this->execTranDocomoInput->setAccessPass($accessPass);
	}

	/**
	 * 加盟店自由項目1設定
	 *
	 * @param string $clientField1
	 */
	function setClientField1($clientField1) {
		$this->execTranDocomoInput->setClientField1($clientField1);
	}

	/**
	 * 加盟店自由項目2設定
	 *
	 * @param string $clientField2
	 */
	function setClientField2($clientField2) {
		$this->execTranDocomoInput->setClientField2($clientField2);
	}

	/**
	 * 加盟店自由項目3設定
	 *
	 * @param string $clientField3
	 */
	function setClientField3($clientField3) {
		$this->execTranDocomoInput->setClientField3($clientField3);
	}

	/**
	 * ドコモ表示項目1設定
	 *
	 * @param string $docomoDisp1
	 */
	function setDocomoDisp1($docomoDisp1) {
		$this->execTranDocomoInput->setDocomoDisp1($docomoDisp1);
	}

	/**
	 * ドコモ表示項目2設定
	 *
	 * @param string $docomoDisp2
	 */
	function setDocomoDisp2($docomoDisp2) {
		$this->execTranDocomoInput->setDocomoDisp2($docomoDisp2);
	}

	/**
	 * 決済結果戻しURL設定
	 *
	 * @param string $retURL
	 */
	function setRetURL($retURL) {
		$this->execTranDocomoInput->setRetURL($retURL);
	}

	/**
	 * 支払開始期限秒設定
	 *
	 * @param string $paymentTermSec
	 */
	function setPaymentTermSec($paymentTermSec) {
		$this->execTranDocomoInput->setPaymentTermSec($paymentTermSec);
	}

}
?>
