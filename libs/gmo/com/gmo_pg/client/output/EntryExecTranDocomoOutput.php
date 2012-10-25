<?php
require_once 'com/gmo_pg/client/output/EntryTranDocomoOutput.php';
require_once 'com/gmo_pg/client/output/ExecTranDocomoOutput.php';
/**
 * <b>ドコモケータイ払い登録・決済一括実行  出力パラメータクラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage output
 * @see outputPackageInfo.php 
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 2012/06/14
 */
class EntryExecTranDocomoOutput {

	/**
	 * @var EntryTranDocomoOutput ドコモケータイ払い登録出力パラメータ
	 */
	var $entryTranDocomoOutput;/*@var $entryTranDocomoOutput EntryTranDocomoOutput */

	/**
	 * @var ExecTranDocomoOutput ドコモケータイ払い実行出力パラメータ
	 */
	var $execTranDocomoOutput;/*@var $execTranDocomoOutput ExecTranDocomoOutput */

	/**
	 * コンストラクタ
	 *
	 * @param IgnoreCaseMap $params    入力パラメータ
	 */
	function EntryExecTranDocomoOutput($params = null) {
		$this->__construct($params);
	}

	/**
	 * コンストラクタ
	 *
	 * @param IgnoreCaseMap $params    入力パラメータ
	 */
	function __construct($params = null) {
		$this->entryTranDocomoOutput = new EntryTranDocomoOutput($params);
		$this->execTranDocomoOutput = new ExecTranDocomoOutput($params);
	}

	/**
	 * ドコモケータイ払い登録出力パラメータ取得
	 * @return EntryTranDocomoOutput ドコモケータイ払い登録出力パラメータ
	 */
	function &getEntryTranDocomoOutput() {
		return $this->entryTranDocomoOutput;
	}

	/**
	 * ドコモケータイ払い実行出力パラメータ取得
	 * @return ExecTranDocomoOutput ドコモケータイ払い実行出力パラメータ
	 */
	function &getExecTranDocomoOutput() {
		return $this->execTranDocomoOutput;
	}

	/**
	 * ドコモケータイ払い登録出力パラメータ設定
	 *
	 * @param EntryTranDocomoOutput  $entryTranDocomoOutput ドコモケータイ払い登録出力パラメータ
	 */
	function setEntryTranDocomoOutput(&$entryTranDocomoOutput) {
		$this->entryTranDocomoOutput = $entryTranDocomoOutput;
	}

	/**
	 * ドコモケータイ払い決済実行出力パラメータ設定
	 *
	 * @param ExecTranDocomoOutput $execTranDocomoOutput ドコモケータイ払い実行出力パラメータ
	 */
	function setExecTranDocomoOutput(&$execTranDocomoOutput) {
		$this->execTranDocomoOutput = $execTranDocomoOutput;
	}

	/**
	 * 取引ID取得
	 * @return string 取引ID
	 */
	function getAccessID() {
		return $this->entryTranDocomoOutput->getAccessID();
	}

	/**
	 * 取引パスワード取得
	 * @return string 取引パスワード
	 */
	function getAccessPass() {
		return $this->entryTranDocomoOutput->getAccessPass();
	}

	/**
	 * 決済トークン取得
	 * @return string 決済トークン
	 */
	function getToken() {
		return $this->execTranDocomoOutput->getToken();
	}

	/**
	 * 支払手続き開始IFのURL取得
	 * @return string 支払手続き開始IFのURL
	 */
	function getStartURL() {
		return $this->execTranDocomoOutput->getStartURL();
	}

	/**
	 * 支払開始期限日時取得
	 * @return string 支払開始期限日時
	 */
	function getStartLimitDate() {
		return $this->execTranDocomoOutput->getStartLimitDate();
	}

	/**
	 * 取引ID設定
	 *
	 * @param string $accessID
	 */
	function setAccessID($accessID) {
		$this->entryTranDocomoOutput->setAccessID($accessID);
		$this->execTranDocomoOutput->setAccessID($accessID);
	}

	/**
	 * 取引パスワード設定
	 *
	 * @param string $accessPass
	 */
	function setAccessPass($accessPass) {
		$this->entryTranDocomoOutput->setAccessPass($accessPass);
	}

	/**
	 * 決済トークン設定
	 *
	 * @param string $token
	 */
	function setToken($token) {
		$this->execTranDocomoOutput->setToken($token);
	}

	/**
	 * 支払手続き開始IFのURL設定
	 *
	 * @param string $startURL
	 */
	function setStartURL($startURL) {
		$this->execTranDocomoOutput->setStartURL($startURL);
	}

	/**
	 * 支払開始期限日時設定
	 *
	 * @param string $startLimitDate
	 */
	function setStartLimitDate($startLimitDate) {
		$this->execTranDocomoOutput->setStartLimitDate($startLimitDate);
	}

	/**
	 * 取引登録エラーリスト取得
	 * @return  array エラーリスト
	 */
	function &getEntryErrList() {
		return $this->entryTranDocomoOutput->getErrList();
	}

	/**
	 * 決済実行エラーリスト取得
	 * @return array エラーリスト
	 */
	function &getExecErrList() {
		return $this->execTranDocomoOutput->getErrList();
	}

	/**
	 * 取引登録エラー発生判定
	 * @return boolean 取引登録時エラー有無(true=エラー発生)
	 */
	function isEntryErrorOccurred() {
		$entryErrList =& $this->entryTranDocomoOutput->getErrList();
		return 0 < count($entryErrList);
	}

	/**
	 * 決済実行エラー発生判定
	 * @return boolean 決済実行時エラー有無(true=エラー発生)
	 */
	function isExecErrorOccurred() {
		$execErrList =& $this->execTranDocomoOutput->getErrList();
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
?>
