<?php
require_once 'com/gmo_pg/client/output/EntryTranAuOutput.php';
require_once 'com/gmo_pg/client/output/ExecTranAuOutput.php';
/**
 * <b>auかんたん決済登録・決済一括実行  出力パラメータクラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage output
 * @see outputPackageInfo.php 
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 2012/02/15
 */
class EntryExecTranAuOutput {

	/**
	 * @var EntryTranAuOutput auかんたん決済登録出力パラメータ
	 */
	var $entryTranAuOutput;/*@var $entryTranAuOutput EntryTranAuOutput */

	/**
	 * @var ExecTranAuOutput auかんたん決済実行出力パラメータ
	 */
	var $execTranAuOutput;/*@var $execTranAuOutput ExecTranAuOutput */

	/**
	 * コンストラクタ
	 *
	 * @param IgnoreCaseMap $params    入力パラメータ
	 */
	function EntryExecTranAuOutput($params = null) {
		$this->__construct($params);
	}

	/**
	 * コンストラクタ
	 *
	 * @param IgnoreCaseMap $params    入力パラメータ
	 */
	function __construct($params = null) {
		$this->entryTranAuOutput = new EntryTranAuOutput($params);
		$this->execTranAuOutput = new ExecTranAuOutput($params);
	}

	/**
	 * auかんたん決済登録出力パラメータ取得
	 * @return EntryTranAuOutput auかんたん決済登録出力パラメータ
	 */
	function &getEntryTranAuOutput() {
		return $this->entryTranAuOutput;
	}

	/**
	 * auかんたん決済実行出力パラメータ取得
	 * @return ExecTranAuOutput auかんたん決済実行出力パラメータ
	 */
	function &getExecTranAuOutput() {
		return $this->execTranAuOutput;
	}

	/**
	 * auかんたん決済登録出力パラメータ設定
	 *
	 * @param EntryTranAuOutput  $entryTranAuOutput auかんたん決済登録出力パラメータ
	 */
	function setEntryTranAuOutput(&$entryTranAuOutput) {
		$this->entryTranAuOutput = $entryTranAuOutput;
	}

	/**
	 * auかんたん決済決済実行出力パラメータ設定
	 *
	 * @param ExecTranAuOutput $execTranAuOutput auかんたん決済実行出力パラメータ
	 */
	function setExecTranAuOutput(&$execTranAuOutput) {
		$this->execTranAuOutput = $execTranAuOutput;
	}

	/**
	 * 取引ID取得
	 * @return string 取引ID
	 */
	function getAccessID() {
		return $this->entryTranAuOutput->getAccessID();
	}

	/**
	 * 取引パスワード取得
	 * @return string 取引パスワード
	 */
	function getAccessPass() {
		return $this->entryTranAuOutput->getAccessPass();
	}

	/**
	 * 決済トークン取得
	 * @return string 決済トークン
	 */
	function getToken() {
		return $this->execTranAuOutput->getToken();
	}

	/**
	 * 呼び出しURL取得
	 * @return string 呼び出しURL
	 */
	function getStartURL() {
		return $this->execTranAuOutput->getStartURL();
	}

	/**
	 * 該当トークンの有効期限 YYYYMMDDHHMM取得
	 * @return string 該当トークンの有効期限 YYYYMMDDHHMM
	 */
	function getStartLimitDate() {
		return $this->execTranAuOutput->getStartLimitDate();
	}

	/**
	 * 取引ID設定
	 *
	 * @param string $accessID
	 */
	function setAccessID($accessID) {
		$this->entryTranAuOutput->setAccessID($accessID);
		$this->execTranAuOutput->setAccessID($accessID);
	}

	/**
	 * 取引パスワード設定
	 *
	 * @param string $accessPass
	 */
	function setAccessPass($accessPass) {
		$this->entryTranAuOutput->setAccessPass($accessPass);
	}

	/**
	 * 決済トークン設定
	 *
	 * @param string $token
	 */
	function setToken($token) {
		$this->execTranAuOutput->setToken($token);
	}

	/**
	 * 呼び出しURL設定
	 *
	 * @param string $startURL
	 */
	function setStartURL($startURL) {
		$this->execTranAuOutput->setStartURL($startURL);
	}

	/**
	 * 該当トークンの有効期限 YYYYMMDDHHMM設定
	 *
	 * @param string $startLimitDate
	 */
	function setStartLimitDate($startLimitDate) {
		$this->execTranAuOutput->setStartLimitDate($startLimitDate);
	}

	/**
	 * 取引登録エラーリスト取得
	 * @return  array エラーリスト
	 */
	function &getEntryErrList() {
		return $this->entryTranAuOutput->getErrList();
	}

	/**
	 * 決済実行エラーリスト取得
	 * @return array エラーリスト
	 */
	function &getExecErrList() {
		return $this->execTranAuOutput->getErrList();
	}

	/**
	 * 取引登録エラー発生判定
	 * @return boolean 取引登録時エラー有無(true=エラー発生)
	 */
	function isEntryErrorOccurred() {
		$entryErrList =& $this->entryTranAuOutput->getErrList();
		return 0 < count($entryErrList);
	}

	/**
	 * 決済実行エラー発生判定
	 * @return boolean 決済実行時エラー有無(true=エラー発生)
	 */
	function isExecErrorOccurred() {
		$execErrList =& $this->execTranAuOutput->getErrList();
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