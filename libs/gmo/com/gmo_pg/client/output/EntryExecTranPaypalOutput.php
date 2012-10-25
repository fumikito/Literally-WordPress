<?php
require_once 'com/gmo_pg/client/output/EntryTranPaypalOutput.php';
require_once 'com/gmo_pg/client/output/ExecTranPaypalOutput.php';

/**
 * <b>Paypal取引登録・決済一括実行  出力パラメータクラス</b>
 *
 * @package com.gmo_pg.client
 * @subpackage output
 * @see outputPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 12-24-2009 00:00:00
 */
class EntryExecTranPaypalOutput {

	/**
	 * @var EntryTranPaypalOutput Paypal取引登録出力パラメタ
	 */
	var $entryTranPaypalOutput;

	/**
	 * @var ExecTranPaypalOutput Paypal決済実行出力パラメタ
	 */
	var $execTranPaypalOutput;

	/**
	 * コンストラクタ
	 * @param IgnoreCaseMap $params    入力パラメータ
	 */
	function EntryExecTranPaypalOutput($params = null) {
		$this->__constract($params);
	}

	/**
	 * コンストラクタ
	 * @param IgnoreCaseMap $params    入力パラメータ
	 */
	function __constract($params = null) {
		$this->entryTranPaypalOutput = new EntryTranPaypalOutput($params);
		$this->execTranPaypalOutput = new ExecTranPaypalOutput($params);
	}

	/**
	 * Paypal取引登録出力パラメタ取得
	 * @return EntryTranPaypalOutput Paypal取引登録出力パラメタ
	 */
	function getEntryTranPaypalOutput(){
		return $this->entryTranPaypalOutput;
	}

	/**
	 * Paypal決済実行出力パラメタ取得
	 * @return ExecTranPaypalOutput Paypal決済実行出力パラメタ
	 */
	function getExecTranPaypalOutput(){
		return $this->execTranPaypalOutput;
	}

	/**
	 * オーダID取得
	 * @return string オーダID
	 */
	function getOrderId(){
		return $this->entryTranPaypalOutput->getOrderId();
	}

	/**
	 * アクセスID取得
	 * @return string アクセスID
	 */
	function getAccessId(){
		return $this->entryTranPaypalOutput->getAccessId();
	}

	/**
	 * アクセスパス取得
	 * @return string アクセスパス
	 */
	function getAccessPass(){
		return $this->entryTranPaypalOutput->getAccessPass();
	}

	/**
	 * 加盟店自由項目1取得
	 * @return string 加盟店自由項目1
	 */
	function getClientField1(){
		return $this->execTranPaypalOutput->getClientField1();
	}

	/**
	 * 加盟店自由項目2取得
	 * @return string 加盟店自由項目2
	 */
	function getClientField2(){
		return $this->execTranPaypalOutput->getClientField2();
	}

	/**
	 * 加盟店自由項目3取得
	 * @return string 加盟店自由項目3
	 */
	function getClientField3(){
		return $this->execTranPaypalOutput->getClientField3();
	}

	/**
	 * Paypal取引登録出力パラメータ設定
	 *
	 * @param EntryTranPaypalOutput  $entryTranPaypalOutput Paypal取引登録出力パラメータ
	 */
	function setEntryTranPaypalOutput( $entryTranPaypalOutput ){
		$this->entryTranPaypalOutput = $entryTranPaypalOutput;
	}
	
	/**
	 * Paypal決済実行パラメータ設定
	 *
	 * @param ExecTranPaypalOutput  $execTranPaypalOutput Paypal取引登録出力パラメータ
	 */
	function setExecTranPaypalOutput( $execTranPaypalOutput ){
		$this->execTranPaypalOutput = $execTranPaypalOutput;
	}

	/**
	 * オーダID設定
	 * @param $orderId オーダID
	 */
	function setOrderId( $orderId ){
		$this->entryTranPaypalOutput->setOrderId($orderId);
		$this->execTranPaypalOutput->setOrderId($orderId);
	}

	/**
	 * アクセスID設定
	 * @param $accessId アクセスID
	 */
	function setAccessId( $accessId ){
		$this->entryTranPaypalOutput->setAccessId($accessId);
	}

	/**
	 * アクセスパス設定
	 * @param $accessPass アクセスパス
	 * @return unknown_type
	 */
	function setAccessPass( $accessPass ){
		$this->entryTranPaypalOutput->setAccessPass($accessPass);
	}

	/**
	 * 加盟店自由項目1設定
	 * @param $clientField1 加盟店自由項目1
	 */
	function setClientField1( $clientField1 ){
		$this->execTranPaypalOutput->setClientField1($clientField1);
	}

	/**
	 * 加盟店自由項目2設定
	 * @param $clientField2 加盟店自由項目2
	 */
	function setClientField2( $clientField2 ){
		$this->execTranPaypalOutput->setClientField2($clientField2);
	}

	/**
	 * 加盟店自由項目3設定
	 * @param $clientField3 加盟店自由項目3
	 */
	function setClientField3( $clientField3 ){
		$this->execTranPaypalOutput->setClientField3($clientField3);
	}

	/**
	 * Paypal取引登録エラーリスト取得
	 * @return  array エラーリスト
	 */
	function &getEntryErrList() {
		return $this->entryTranPaypalOutput->getErrList();
	}

	/**
	 * Paypal決済実行エラーリスト取得
	 * @return array エラーリスト
	 */
	function &getExecErrList() {
		return $this->execTranPaypalOutput->getErrList();
	}

	/**
	 * Paypal取引登録エラー発生判定
	 * @return boolean Paypal取引登録時エラー有無(true=エラー発生)
	 */
	function isEntryErrorOccurred() {
		$entryErrList =& $this->entryTranPaypalOutput->getErrList();
		return 0 < count($entryErrList);
	}

	/**
	 * Paypal決済実行エラー発生判定
	 * @return boolean 決済実行時エラー有無(true=エラー発生)
	 */
	function isExecErrorOccurred() {
		$execErrList =& $this->execTranPaypalOutput->getErrList();
		return 0 < count($execErrList);
	}

	/**
	 * エラー発生判定
	 * @return boolean エラー発生有無(true=エラー発生)
	 */
	function isErrorOccurred() {
		return $this->isEntryErrorOccurred() || $this->isExecErrorOccurred();
	}



}