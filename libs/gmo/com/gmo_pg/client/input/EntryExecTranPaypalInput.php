<?php
require_once 'com/gmo_pg/client/input/EntryTranPaypalInput.php';
require_once 'com/gmo_pg/client/input/ExecTranPaypalInput.php';

/**
 * <b>Paypal登録・決済一括実行　入力パラメータクラス</b>
 *
 * @package com.gmo_pg.client
 * @subpackage input
 * @see inputPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 12-24-2009 00:00:00
 */
class EntryExecTranPaypalInput {

	/**
	 * @var EntryTranPaypalInput Paypal取引登録入力パラメタ
	 */
	var $entryTranPaypalInput;

	/**
	 * @var ExecTranPaypalInput Paypal決済実行入力パラメタ
	 */
	var $execTranPaypalInput;

	/**
	 * コンストラクタ
	 * @param array $params 入力パラメタ
	 */
	function EntryExecTranPaypalInput($params = null){
		$this->__construct($params);
	}

	/**
	 * コンストラクタ
	 * @param array $params 入力パラメタ
	 */
	function __construct($params = null) {
		$this->entryTranPaypalInput = new EntryTranPaypalInput($params);
		$this->execTranPaypalInput = new ExecTranPaypalInput($params);
	}

	/**
	 * 取引登録入力パラメータ取得
	 *
	 * @return EntryTranInput Paypal取引登録時パラメータ
	 */
	function &getEntryTranPaypalInput(){
		return $this->entryTranPaypalInput;
	}

	/**
	 * 決済実行入力パラメタ
	 * @return ExecTranPaypalInput Paypal決済実行時パラメタ
	 */
	function &getExecTranPaypalInput(){
		return $this->execTranPaypalInput;
	}

	/**
	 * ショップID取得
	 * @return string ショップID
	 */
	function getShopId() {
		return $this->entryTranPaypalInput->getShopId();
	}

	/**
	 * ショップパスワード取得
	 * @return string ショップパスワード
	 */
	function getShopPass(){
		return $this->entryTranPaypalInput->getShopPass();
	}

	/**
	 * アクセスID取得
	 * @return string アクセスID
	 */
	function getAccessId(){
		return $this->execTranPaypalInput->getAccessId();
	}

	/**
	 * アクセスパス取得
	 * @return string アクセスパス
	 */
	function getAccessPass(){
		return $this->execTranPaypalInput->getAccessPass();
	}

	/**
	 * オーダID取得
	 * @return string オーダID
	 */
	function getOrderId() {
		return $this->entryTranPaypalInput->getOrderId();
	}

	/**
	 * 処理区分取得
	 * @return string 処理区分
	 */
	function getJobCd(){
		return $this->entryTranPaypalInput->getJobCd();
	}

	/**
	 * 通貨コード取得
	 * @return string 通貨コード
	 */
	function getCurrency() {
	    return $this->entryTranPaypalInput->getCurrency();
	}
	
	/**
	 * 利用金額取得
	 * @return string 利用金額
	 */
	function getAmount(){
		return $this->entryTranPaypalInput->getAmount();
	}

	/**
	 * 税送料取得
	 * @return string 税送料
	 */
	function getTax(){
		return $this->entryTranPaypalInput->getTax();
	}

	/**
	 * 商品名
	 * @return string 商品名
	 */
	function getItemName(){
		return $this->execTranPaypalInput->getItemName();
	}

	/**
	 * リダイレクトURL
	 * @return string リダイレクトURL
	 */
	function getRedirectURL(){
		return $this->execTranPaypalInput->getRedirectURL();
	}

	/**
	 * 加盟店自由項目1
	 * @return string 加盟店自由項目1
	 */
	function getClientField1(){
		return $this->execTranPaypalInput->getClientField1();
	}

	/**
	 * 加盟店自由項目2
	 * @return string 加盟店自由項目2
	 */
	function getClientField2(){
		return $this->execTranPaypalInput->getClientField2();
	}

	/**
	 * 加盟店自由項目3
	 * @return string 加盟店自由項目3
	 */
	function getClientField3(){
		return $this->execTranPaypalInput->getClientField3();
	}

	/**
	 * Paypal取引登録入力パラメータ設定
	 *
	 * @param EntryTranInput entryTranInput  取引登録入力パラメータ
	 */
	function setEntryTranPaypalInput( &$entryTranPaypalInput ){
		$this->entryTranPaypalInput = $entryTranPaypalInput;
	}

	/**
	 * Paypal決済実行入力パラメタ設定
	 * @param $execTranPaypalInput 決済実行入力パラメタ
	 */
	function setExecTranPaypalInput( &$execTranPaypalInput ){
		$this->execTranPaypalInput = $execTranPaypalInput;
	}

	/**
	 * ショップIDの設定
	 * @param $shopId ショップId
	 */
	function setShopId( $shopId ){
		$this->entryTranPaypalInput->setShopId($shopId);
		$this->execTranPaypalInput->setShopId($shopId);
	}

	/**
	 * ショップパスの設定
	 * @param $shopPass ショップパス
	 */
	function setShopPass( $shopPass ){
		$this->entryTranPaypalInput->setShopPass($shopPass);
		$this->execTranPaypalInput->setShopPass($shopPass);
	}
	
	/**
	 * 取引ID設定
	 *
	 * @param string $accessId
	 */
	function setAccessId($accessId) {
		$this->execTranPaypalInput->setAccessId($accessId);
	}

	/**
	 * 取引パスワード設定
	 *
	 * @param string $accessPass
	 */
	function setAccessPass($accessPass) {
		$this->execTranPaypalInput->setAccessPass($accessPass);
	}

	/**
	 * オーダID設定
	 * @param string $orderId
	 */
	function setOrderId( $orderId ){
		$this->entryTranPaypalInput->setOrderId($orderId);
		$this->execTranPaypalInput->setOrderId($orderId);
	}

	/**
	 * 処理区分設定
	 */
	function setJobCd( $jobCd ){
		$this->entryTranPaypalInput->setJobCd($jobCd);
	}

	/**
	 * 通貨コード設定
	 * @param string 通貨コード
	 */
	function setCurrency( $currency ) {
	    $this->entryTranPaypalInput->setCurrency($currency);
	}
	
	/**
	 * 金額設定
	 * @param $amount 金額
	 */
	function setAmount( $amount ){
		$this->entryTranPaypalInput->setAmount($amount);
	}

	/**
	 * 税送料設定
	 * @param $tax 税送料
	 */
	function setTax( $tax ){
		$this->entryTranPaypalInput->setTax($tax);
	}

	/**
	 * 商品名設定
	 * @param $itemName 商品名
	 */
	function setItemName( $itemName ){
		$this->execTranPaypalInput->setItemName($itemName);
	}

	/**
	 * リダイレクトURL設定
	 * @param $redirectURL リダイレクトURL
	 */
	function setRedirectURL( $redirectURL ){
		$this->execTranPaypalInput->setRedirectURL($redirectURL);
	}

	/**
	 * 加盟店自由項目1設定
	 * @param $clientFiled1 加盟店自由項目1
	 */
	function setClientField1( $clientFiled1 ){
		$this->execTranPaypalInput->setClientField1($clientFiled1);
	}

	/**
	 * 加盟店自由項目2設定
	 * @param $clientFiled2 加盟店自由項目2
	 */
	function setClientField2( $clientFiled2 ){
		$this->execTranPaypalInput->setClientField2($clientFiled2);
	}

	/**
	 * 加盟店自由項目3設定
	 * @param $clientFiled3 加盟店自由項目3
	 */
	function setClientField3( $clientFiled3 ){
		$this->execTranPaypalInput->setClientField3($clientFiled3);
	}
}
