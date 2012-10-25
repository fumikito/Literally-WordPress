<?php
require_once 'com/gmo_pg/client/input/EntryTranInput.php';
require_once 'com/gmo_pg/client/input/ExecTranInput.php';
/**
 * <b>登録・決済一括実行　入力パラメータクラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage input
 * @see inputPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 01-01-2008 00:00:00
 */
class EntryExecTranInput {

	/**
	 * @var EntryTranInput 取引登録入力パラメータ
	 */
	var $entryTranInput;/* @var $entryTranInput EntryTranInput */

	/**
	 * @var ExecTranInput 決済実行入力パラメータ
	 */
	var $execTranInput;/* @var $execTranInput ExecTranInput */

	/**
	 * コンストラクタ
	 *
	 * @param array $params    入力パラメータ
	 */
	function EntryExecTranInput($params = null) {
		$this->__construct($params);
	}

	/**
	 * コンストラクタ
	 *
	 * @param array $params    入力パラメータ
	 */
	function __construct($params = null) {
		$this->entryTranInput = new EntryTranInput($params);
		$this->execTranInput = new ExecTranInput($params);
	}

	/**
	 * 取引登録入力パラメータ取得
	 * 
	 * @return EntryTranInput 取引登録時パラメータ
	 */
	function &getEntryTranInput() {
		return $this->entryTranInput;
	}

	/**
	 * 決済実行入力パラメータ取得
	 * @return ExecTranInput 決済実行時パラメータ
	 */
	function &getExecTranInput() {
		return $this->execTranInput;
	}

	/**
	 * ショップID取得
	 * @return string ショップID
	 */
	function getShopId() {
		return $this->entryTranInput->getShopId();
	}

	/**
	 * ショップパスワード取得
	 * @return string ショップパスワード
	 */
	function getShopPass() {
		return $this->entryTranInput->getShopPass();
	}

	/**
	 * オーダーID取得
	 * @return string オーダーID
	 */
	function getOrderId() {
		return $this->entryTranInput->getOrderId();
	}

	/**
	 * 処理区分取得
	 * @return string 処理区分
	 */
	function getJobCd() {
		return $this->entryTranInput->getJobCd();
	}

	/**
	 * 商品コード取得
	 * @return string 商品コード
	 */
	function getItemCode() {
		return $this->entryTranInput->getItemCode();
	}

	/**
	 * 利用金額取得
	 * @return integer 利用金額
	 */
	function getAmount() {
		return $this->entryTranInput->getAmount();
	}

	/**
	 * 税送料取得
	 * @return integer 税送料
	 */
	function getTax() {
		return $this->entryTranInput->getTax();
	}

	/**
	 * 3Dセキュア使用フラグ取得
	 * @return string 3Dセキュア使用フラグ
	 */
	function getTdFlag() {
		return $this->entryTranInput->getTdFlag();
	}

	/**
	 * 3Dセキュア表示店舗名取得
	 * @return string 3Dセキュア表示店舗名
	 */
	function getTdTenantName() {
		return $this->entryTranInput->getTdTenantName();
	}

	/**
	 * 取引ID取得
	 * @return string 取引ID
	 */
	function getAccessId() {
		return $this->execTranInput->getAccessId();
	}

	/**
	 * 取引パスワード取得
	 * @return string 取引パスワード
	 */
	function getAccessPass() {
		return $this->execTranInput->getAccessPass();
	}

	/**
	 * 支払い方法取得
	 * @return string 支払方法
	 */
	function getMethod() {
		return $this->execTranInput->getMethod();
	}

	/**
	 * 支払回数取得
	 * @return integer 支払回数
	 */
	function getPayTimes() {
		return $this->execTranInput->getPayTimes();
	}

	/**
	 * カード番号取得
	 * @return string カード番号
	 */
	function getCardNo() {
		return $this->execTranInput->getCardNo();
	}

	/**
	 * 有効期限取得
	 * @return string 有効期限
	 */
	function getExpire() {
		return $this->execTranInput->getExpire();
	}

	/**
	 * サイトID取得
	 * @return string サイトID
	 */
	function getSiteId(){
		return $this->execTranInput->getSiteId();
	}
	
	/**
	 * サイトパスワード取得
	 * @return string サイトパスワード
	 */
	function getSitePass(){
		return $this->execTranInput->getSitePass();
	}
	
	/**
	 * 会員ID取得
	 * @return string 会員ID
	 */
	function getMemberId(){
		return $this->execTranInput->getMemberId();
	}
	
	/**
	 * カード登録連番取得
	 * @return integer カード登録連番
	 */
	function getCardSeq(){
		return $this->execTranInput->getCardSeq();
	}
	
	/**
	 * カード連番モード取得
	 * @return string カード連番モード
	 */
	function getSeqMode(){
		return $this->execTranInput->getSeqMode();
	}
	
	/**
	 * カードパスワード取得
	 * @return string カードパスワード
	 */
	function getCardPass(){
		return $this->execTranInput->getCardPass();
	}
	
	/**
	 * セキュリティコード取得
	 * @return string セキュリティコード
	 */
	function getSecurityCode() {
		return $this->execTranInput->getSecurityCode();
	}

	/**
	 * HTTP_ACCEPT取得
	 * @return string HTTP_ACCEPT
	 */
	function getHttpAccept() {
		return $this->execTranInput->getHttpAccept();
	}

	/**
	 * HTTP_USER_AGENT取得
	 * @return string HTTP_USER_AGENT
	 */
	function getHttpUserAgent() {
		return $this->execTranInput->getHttpUserAgent();
	}

	/**
	 * 加盟店自由項目1取得
	 * @return string 加盟店自由項目1
	 */
	function getClientField1() {
		return $this->execTranInput->getClientField1();
	}

	/**
	 * 加盟店自由項目2取得
	 * @return string 加盟店自由項目2
	 */
	function getClientField2() {
		return $this->execTranInput->getClientField2();
	}

	/**
	 * 加盟店自由項目3取得
	 * @return string 加盟店自由項目3
	 */
	function getClientField3() {
		return $this->execTranInput->getClientField3();
	}

	/**
	 * 取引登録入力パラメータ設定
	 *
	 * @param EntryTranInput entryTranInput  取引登録入力パラメータ
	 */
	function setEntryTranInput(&$entryTranInput) {
		$this->entryTranInput = $entryTranInput;
	}

	/**
	 * 決済実行入力パラメータ設定
	 *
	 * @param ExecTranInput  決済実行入力パラメータ
	 */
	function setExecTranInput(&$execTranInput) {
		$this->execTranInput = $execTranInput;
	}

	/**
	 * ショップID設定
	 *
	 * @param string $shopId
	 */
	function setShopId($shopId) {
		$this->entryTranInput->setShopId($shopId);
	}

	/**
	 * ショップパスワード設定
	 *
	 * @param string $shopPass
	 */
	function setShopPass($shopPass) {
		$this->entryTranInput->setShopPass($shopPass);
	}

	/**
	 * オーダーID設定
	 *
	 * @param string $orderId
	 */
	function setOrderId($orderId) {
		$this->entryTranInput->setOrderId($orderId);
		$this->execTranInput->setOrderId($orderId);
	}

	/**
	 * 処理区分設定
	 *
	 * @param string $jobCd
	 */
	function setJobCd($jobCd) {
		$this->entryTranInput->setJobCd($jobCd);
	}

	/**
	 * 商品コード設定
	 *
	 * @param string $itemCode
	 */
	function setItemCode($itemCode) {
		$this->entryTranInput->setItemCode($itemCode);
	}

	/**
	 * 利用金額設定
	 *
	 * @param integer $amount
	 */
	function setAmount($amount) {
		$this->entryTranInput->setAmount($amount);
	}

	/**
	 * 税送料設定
	 *
	 * @param integer $tax
	 */
	function setTax($tax) {
		$this->entryTranInput->setTax($tax);
	}

	/**
	 * 3Dセキュア使用フラグ設定
	 *
	 * @param string $tdFlag
	 */
	function setTdFlag($tdFlag) {
		$this->entryTranInput->setTdFlag($tdFlag);
	}

	/**
	 * 3Dセキュア表示店舗名設定
	 *
	 * @param string $tdTenantName
	 */
	function setTdTenantName($tdTenantName) {
		$this->entryTranInput->setTdTenantName($tdTenantName);
	}

	/**
	 * 取引ID設定
	 *
	 * @param string $accessId
	 */
	function setAccessId($accessId) {
		$this->execTranInput->setAccessId($accessId);
	}

	/**
	 * 取引パスワード設定
	 *
	 * @param string $accessPass
	 */
	function setAccessPass($accessPass) {
		$this->execTranInput->setAccessPass($accessPass);
	}

	/**
	 * 支払い方法設定
	 *
	 * @param string $method
	 */
	function setMethod($method) {
		$this->execTranInput->setMethod($method);
	}

	/**
	 * 支払回数設定
	 *
	 * @param string $payTimes
	 */
	function setPayTimes($payTimes) {
		$this->execTranInput->setPayTimes($payTimes);
	}

	/**
	 * カード番号設定
	 *
	 * @param string $cardNo
	 */
	function setCardNo($cardNo) {
		$this->execTranInput->setCardNo($cardNo);
	}

	/**
	 * 有効期限設定
	 *
	 * @param string $expire
	 */
	function setExpire($expire) {
		$this->execTranInput->setExpire($expire);
	}

	/**
	 * サイトID設定
	 * @param string $siteID
	 */
	function setSiteId($siteID){
		$this->execTranInput->setSiteId($siteID);
	}
	
	/**
	 * サイトパスワード設定
	 * @param string $sitePass
	 */
	function setSitePass($sitePass){
		$this->execTranInput->setSitePass($sitePass);
	}
	
	/**
	 * 会員ID設定
	 * @param string $memberId
	 */
	function setMemberId($memberId){
		$this->execTranInput->setMemberId($memberId);
	}
	
	/**
	 * カード登録連番設定
	 * @param integer $cardseq
	 */
	function setCardSeq($cardseq){
		$this->execTranInput->setCardSeq();
	}
	
	/**
	 * カード連番モード設定
	 * @param string $seqMode
	 */
	function setSeqMode($seqMode){
		$this->execTranInput->setSeqMode($seqMode);
	}
	
	/**
	 * カードパスワード設定
	 * @param string $cardPass
	 */
	function setCardPass($cardPass){
		$this->execTranInput->setCardPass($cardPass);
	}
	
	
	/**
	 * セキュリティコード設定
	 *
	 * @param string $securityCode
	 */
	function setSecurityCode($securityCode) {
		$this->execTranInput->setSecurityCode($securityCode);
	}
	

	/**
	 * HTTP_ACCEPT設定
	 *
	 * @param string $httpAccept
	 */
	function setHttpAccept($httpAccept) {
		$this->execTranInput->setHttpAccept($httpAccept);
	}
	

	/**
	 * HTTP_USER_AGENT設定
	 *
	 * @param string $httpUserAgent
	 */
	function setHttpUserAgent($httpUserAgent) {
		$this->execTranInput->setHttpUserAgent($httpUserAgent);
	}
	

	/**
	 * 加盟店自由項目1設定
	 *
	 * @param string $clientField1
	 */
	function setClientField1($clientField1) {
		$this->execTranInput->setClientField1($clientField1);
	}

	/**
	 * 加盟店自由項目2設定
	 *
	 * @param string $clientField2
	 */
	function setClientField2($clientField2) {
		$this->execTranInput->setClientField2($clientField2);
	}
	

	/**
	 * 加盟店自由項目3設定
	 *
	 * @param string $clientField3
	 */
	function setClientField3($clientField3) {
		$this->execTranInput->setClientField3($clientField3);
	}

}
?>