<?php
require_once 'com/gmo_pg/client/input/EntryTranAuInput.php';
require_once 'com/gmo_pg/client/input/ExecTranAuInput.php';
/**
 * <b>auかんたん決済登録・決済一括実行　入力パラメータクラス</b>
 *
 * @package com.gmo_pg.client
 * @subpackage input
 * @see inputPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 2012/02/15
 */
class EntryExecTranAuInput {

	/**
	 * @var EntryTranAuInput auかんたん決済登録入力パラメータ
	 */
	var $entryTranAuInput;/* @var $entryTranInput EntryTranAuInput */

	/**
	 * @var ExecTranAuInput auかんたん決済実行入力パラメータ
	 */
	var $execTranAuInput;/* @var $execTranInput ExecTranAuInput */

	/**
	 * コンストラクタ
	 *
	 * @param array $params    入力パラメータ
	 */
	function EntryExecTranAuInput($params = null) {
		$this->__construct($params);
	}

	/**
	 * コンストラクタ
	 *
	 * @param array $params    入力パラメータ
	 */
	function __construct($params = null) {
		$this->entryTranAuInput = new EntryTranAuInput($params);
		$this->execTranAuInput = new ExecTranAuInput($params);
	}

	/**
	 * auかんたん決済取引登録入力パラメータ取得
	 *
	 * @return EntryTranAuInput 取引登録時パラメータ
	 */
	function &getEntryTranAuInput() {
		return $this->entryTranAuInput;
	}

	/**
	 * auかんたん決済実行入力パラメータ取得
	 * @return ExecTranAuInput 決済実行時パラメータ
	 */
	function &getExecTranAuInput() {
		return $this->execTranAuInput;
	}

	/**
	 * ショップID取得
	 * @return string ショップID
	 */
	function getShopID() {
		return $this->entryTranAuInput->getShopID();
	}

	/**
	 * ショップパスワード取得
	 * @return string ショップパスワード
	 */
	function getShopPass() {
		return $this->entryTranAuInput->getShopPass();
	}

	/**
	 * オーダーID取得
	 * @return string オーダーID
	 */
	function getOrderID() {
		return $this->entryTranAuInput->getOrderID();
	}

	/**
	 * 処理区分取得
	 * @return string 処理区分
	 */
	function getJobCd() {
		return $this->entryTranAuInput->getJobCd();
	}

	/**
	 * 利用金額取得
	 * @return string 利用金額
	 */
	function getAmount() {
		return $this->entryTranAuInput->getAmount();
	}

	/**
	 * 税送料取得
	 * @return string 税送料
	 */
	function getTax() {
		return $this->entryTranAuInput->getTax();
	}

	/**
	 * 取引ID取得
	 * @return string 取引ID
	 */
	function getAccessID() {
		return $this->execTranAuInput->getAccessID();
	}

	/**
	 * 取引パスワード取得
	 * @return string 取引パスワード
	 */
	function getAccessPass() {
		return $this->execTranAuInput->getAccessPass();
	}

	/**
	 * サイトID取得
	 * @return string サイトID
	 */
	function getSiteID() {
		return $this->execTranAuInput->getSiteID();
	}

	/**
	 * サイトパスワード取得
	 * @return string サイトパスワード
	 */
	function getSitePass() {
		return $this->execTranAuInput->getSitePass();
	}

	/**
	 * 会員ID取得
	 * @return string 会員ID
	 */
	function getMemberID() {
		return $this->execTranAuInput->getMemberID();
	}

	/**
	 * 会員名取得
	 * @return string 会員名
	 */
	function getMemberName() {
		return $this->execTranAuInput->getMemberName();
	}

	/**
	 * 会員作成フラグ取得
	 * @return string 会員作成フラグ
	 */
	function getCreateMember() {
		return $this->execTranAuInput->getCreateMember();
	}

	/**
	 * 自由項目１取得
	 * @return string 自由項目１
	 */
	function getClientField1() {
		return $this->execTranAuInput->getClientField1();
	}

	/**
	 * 自由項目２取得
	 * @return string 自由項目２
	 */
	function getClientField2() {
		return $this->execTranAuInput->getClientField2();
	}

	/**
	 * 自由項目３取得
	 * @return string 自由項目３
	 */
	function getClientField3() {
		return $this->execTranAuInput->getClientField3();
	}

	/**
	 * 摘要取得
	 * @return string 摘要
	 */
	function getCommodity() {
		return $this->execTranAuInput->getCommodity();
	}

	/**
	 * 決済結果戻しURL取得
	 * @return string 決済結果戻しURL
	 */
	function getRetURL() {
		return $this->execTranAuInput->getRetURL();
	}

	/**
	 * 決済結果URL有効期限秒取得
	 * @return string 決済結果URL有効期限秒
	 */
	function getPaymentTermSec() {
		return $this->execTranAuInput->getPaymentTermSec();
	}

	/**
	 * 表示サービス名取得
	 * @return string 表示サービス名
	 */
	function getServiceName() {
		return $this->execTranAuInput->getServiceName();
	}

	/**
	 * 表示電話番号取得
	 * @return string 表示電話番号
	 */
	function getServiceTel() {
		return $this->execTranAuInput->getServiceTel();
	}

	/**
	 * auかんたん決済取引登録入力パラメータ設定
	 *
	 * @param EntryTranAuInput entryTranAuInput  取引登録入力パラメータ
	 */
	function setEntryTranAuInput(&$entryTranAuInput) {
		$this->entryTranAuInput = $entryTranAuInput;
	}

	/**
	 * auかんたん決済実行入力パラメータ設定
	 *
	 * @param ExecTranAuInput  execTranAuInput   決済実行入力パラメータ
	 */
	function setExecTranAuInput(&$execTranAuInput) {
		$this->execTranAuInput = $execTranAuInput;
	}

	/**
	 * ショップID設定
	 *
	 * @param string $shopID
	 */
	function setShopID($shopID) {
		$this->entryTranAuInput->setShopID($shopID);
		$this->execTranAuInput->setShopID($shopID);
	}

	/**
	 * ショップパスワード設定
	 *
	 * @param string $shopPass
	 */
	function setShopPass($shopPass) {
		$this->entryTranAuInput->setShopPass($shopPass);
		$this->execTranAuInput->setShopPass($shopPass);
	}

	/**
	 * オーダーID設定
	 *
	 * @param string $orderID
	 */
	function setOrderID($orderID) {
		$this->entryTranAuInput->setOrderID($orderID);
		$this->execTranAuInput->setOrderID($orderID);
	}

	/**
	 * 処理区分設定
	 *
	 * @param string $jobCd
	 */
	function setJobCd($jobCd) {
		$this->entryTranAuInput->setJobCd($jobCd);
	}

	/**
	 * 利用金額設定
	 *
	 * @param string $amount
	 */
	function setAmount($amount) {
		$this->entryTranAuInput->setAmount($amount);
	}

	/**
	 * 税送料設定
	 *
	 * @param string $tax
	 */
	function setTax($tax) {
		$this->entryTranAuInput->setTax($tax);
	}

	/**
	 * 取引ID設定
	 *
	 * @param string $accessID
	 */
	function setAccessID($accessID) {
		$this->execTranAuInput->setAccessID($accessID);
	}

	/**
	 * 取引パスワード設定
	 *
	 * @param string $accessPass
	 */
	function setAccessPass($accessPass) {
		$this->execTranAuInput->setAccessPass($accessPass);
	}

	/**
	 * サイトID設定
	 *
	 * @param string $siteID
	 */
	function setSiteID($siteID) {
		$this->execTranAuInput->setSiteID($siteID);
	}

	/**
	 * サイトパスワード設定
	 *
	 * @param string $sitePass
	 */
	function setSitePass($sitePass) {
		$this->execTranAuInput->setSitePass($sitePass);
	}

	/**
	 * 会員ID設定
	 *
	 * @param string $memberID
	 */
	function setMemberID($memberID) {
		$this->execTranAuInput->setMemberID($memberID);
	}

	/**
	 * 会員名設定
	 *
	 * @param string $memberName
	 */
	function setMemberName($memberName) {
		$this->execTranAuInput->setMemberName($memberName);
	}

	/**
	 * 会員作成フラグ設定
	 *
	 * @param string $createMember
	 */
	function setCreateMember($createMember) {
		$this->execTranAuInput->setCreateMember($createMember);
	}

	/**
	 * 自由項目１設定
	 *
	 * @param string $clientField1
	 */
	function setClientField1($clientField1) {
		$this->execTranAuInput->setClientField1($clientField1);
	}

	/**
	 * 自由項目２設定
	 *
	 * @param string $clientField2
	 */
	function setClientField2($clientField2) {
		$this->execTranAuInput->setClientField2($clientField2);
	}

	/**
	 * 自由項目３設定
	 *
	 * @param string $clientField3
	 */
	function setClientField3($clientField3) {
		$this->execTranAuInput->setClientField3($clientField3);
	}

	/**
	 * 摘要設定
	 *
	 * @param string $commodity
	 */
	function setCommodity($commodity) {
		$this->execTranAuInput->setCommodity($commodity);
	}

	/**
	 * 決済結果戻しURL設定
	 *
	 * @param string $retURL
	 */
	function setRetURL($retURL) {
		$this->execTranAuInput->setRetURL($retURL);
	}

	/**
	 * 決済結果URL有効期限秒設定
	 *
	 * @param string $paymentTermSec
	 */
	function setPaymentTermSec($paymentTermSec) {
		$this->execTranAuInput->setPaymentTermSec($paymentTermSec);
	}

	/**
	 * 表示サービス名設定
	 *
	 * @param string $serviceName
	 */
	function setServiceName($serviceName) {
		$this->execTranAuInput->setServiceName($serviceName);
	}

	/**
	 * 表示電話番号設定
	 *
	 * @param string $serviceTel
	 */
	function setServiceTel($serviceTel) {
		$this->execTranAuInput->setServiceTel($serviceTel);
	}

}
?>