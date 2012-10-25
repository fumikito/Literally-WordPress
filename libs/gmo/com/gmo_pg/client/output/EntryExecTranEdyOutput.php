<?php
require_once 'com/gmo_pg/client/output/EntryTranEdyOutput.php';
require_once 'com/gmo_pg/client/output/ExecTranEdyOutput.php';
/**
 * <b>モバイルEdy取引登録・決済一括実行  出力パラメータクラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage output
 * @see outputPackageInfo.php 
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 03-07-2008 00:00:00
 */
class EntryExecTranEdyOutput {

	/**
	 * @var EntryTranEdyOutput モバイルEdy取引登録出力パラメータ
	 */
	var $entryTranEdyOutput;

	/**
	 * @var ExecTranEdyOutput モバイルEdy決済実行出力パラメータ
	 */
	var $execTranEdyOutput;/*@var $execTranEdyOutput ExecTranEdyOutput */

	/**
	 * コンストラクタ
	 *
	 * @param IgnoreCaseMap $params    入力パラメータ
	 */
	function EntryExecTranEdyOutput($params = null) {
		$this->__construct($params);
	}

	/**
	 * コンストラクタ
	 *
	 * @param IgnoreCaseMap $params    入力パラメータ
	 */
	function __construct($params = null) {
		$this->entryTranEdyOutput = new EntryTranEdyOutput($params);
		$this->execTranEdyOutput = new ExecTranEdyOutput($params);
	}

	/**
	 * モバイルEdy取引登録出力パラメータ取得
	 * @return EntryTranEdyOutput モバイルEdy取引登録出力パラメータ
	 */
	function &getEntryTranEdyOutput() {
		return $this->entryTranEdyOutput;
	}

	/**
	 * モバイルEdy決済実行出力パラメータ取得
	 * @return ExecTranEdyOutput モバイルEdy決済実行出力パラメータ
	 */
	function &getExecTranEdyOutput() {
		return $this->execTranEdyOutput;
	}

	/**
	 * 取引パスワード取得
	 * @return string 取引パスワード
	 */
	function getAccessPass() {
		return $this->entryTranEdyOutput->getAccessPass();
	}

	/**
	 * 取引ID取得
	 * @return string 取引ID
	 */
	function getAccessId() {
		return $this->entryTranEdyOutput->getAccessId();
	}
/**
	 * オーダーID取得
	 * @return string オーダーID
	 */
	function getOrderId() {
		return $this->execTranEdyOutput->getOrderId();
	}
	/**
	 * Edy注文番号を取得します。
	 * 
	 * @return	$String	Edy注文番号
	 */
	function getEdyOrderNo() {
		return $this->execTranEdyOutput->getEdyOrderNo();
	}
	
	/**
	 * Edy受付番号を取得します。
	 * 
	 * @return	$String	Edy受付番号
	 */
	function getReceiptNo() {
		return $this->execTranEdyOutput->getReceiptNo();
	}
	
	/**
	 * 支払期限日時を取得します。
	 * 
	 * @return	$timestamp	支払期限日時
	 */
	function getPaymentTerm() {
		return $this->execTranEdyOutput->getPaymentTerm();
	}
	
	/**
	 * 決済日付取得
	 * @return string 決済日付
	 */
	function getTranDate() {
		return $this->execTranEdyOutput->getTranDate();
	}

	/**
	 * MD5ハッシュ取得
	 * @return string チェック文字列
	 */
	function getCheckString() {
		return $this->execTranEdyOutput->getCheckString();
	}

	/**
	 * 加盟店自由項目1取得
	 * @return string 加盟店自由項目1
	 */
	function getClientField1() {
		return $this->execTranEdyOutput->getClientField1();
	}

	/**
	 * 加盟店自由項目2取得
	 * @return string 加盟店自由項目2
	 */
	function getClientField2() {
		return $this->execTranEdyOutput->getClientField2();
	}

	/**
	 * 加盟店自由項目3取得
	 * @return string 加盟店自由項目3
	 */
	function getClientField3() {
		return $this->execTranEdyOutput->getClientField3();
	}

	

	/**
	 * モバイルEdy取引登録出力パラメータ設定
	 *
	 * @param EntryTranEdyOutput  $entryTranEdyOutput モバイルEdy取引登録出力パラメータ
	 */
	function setEntryTranEdyOutput(&$entryTranEdyOutput) {
		$this->entryTranEdyOutput = $entryTranEdyOutput;
	}

	/**
	 * モバイルEdy決済実行出力パラメータ設定
	 *
	 * @param ExecTranOutput $execTranOutput モバイルEdy決済実行出力パラメータ
	 */
	function setExecTranEdyOutput(&$execTranEdyOutput) {
		$this->execTranEdyOutput = $execTranEdyOutput;
	}

	/**
	 * 取引ID設定
	 *
	 * @param string accessId 取引ID
	 */
	function setAccessId($accessId) {
		$this->entryTranEdyOutput->setAccessId($accessId);
	}

	/**
	 * 取引パスワード設定
	 *
	 * @param string accessPass 取引パスワード
	 */
	function setAccessPass($accessPass) {
		$this->entryTranEdyOutput->setAccessPass($accessPass);
	}

	/**
	 * オーダーID設定
	 *
	 * @param string $orderId オーダーID
	 */
	function setOrderId($orderId) {
		$this->entryTranEdyOutput->setOrderId($orderId);
	}

	/**
	 * Edy注文番号を格納します。
	 * 
	 * @param	$String	Edy注文番号
	 */
	function setEdyOrderNo($String) {
		$this->execTranEdyOutput->setEdyOrderNo($String);
	}

	/**
	 * 支払期限日時を格納します。
	 * 
	 * @param	$timestamp	支払期限日時
	 */
	function setPaymentTerm($timestamp) {
		$this->execTranEdyOutput->setPaymentTerm($timestamp);
	}

	/**
	 * Edy受付番号を格納します。
	 * 
	 * @param	$String	Edy受付番号
	 */
	function setReceiptNo($String) {
		$this->execTranEdyOutput->setReceiptNo($String);
	}
	
	/**
	 * 決済日付設定
	 *
	 * @param string $tranDate 決済日付
	 */
	function setTranDate($tranDate) {
		$this->execTranEdyOutput->setTranDate($tranDate);
	}

	/**
	 * MD5ハッシュ設定
	 *
	 * @param string $checkString チェック文字列
	 */
	function setCheckString($checkString) {
		$this->execTranEdyOutput->setCheckString($checkString);
	}

	/**
	 * 加盟店自由項目1設定
	 *
	 * @param string $clientField1 加盟店自由項目1
	 */
	function setClientField1($clientField1) {
		$this->execTranEdyOutput->setClientField1($clientField1);
	}

	/**
	 * 加盟店自由項目2設定
	 *
	 * @param string $clientField2 加盟店自由項目2
	 */
	function setClientField2($clientField2) {
		$this->execTranEdyOutput->setClientField2($clientField2);
	}

	/**
	 * 加盟店自由項目3設定
	 *
	 * @param string $clientField3 加盟店自由項目3
	 */
	function setClientField3($clientField3) {
		$this->execTranEdyOutput->setClientField3($clientField3);
	}

	

	/**
	 * 取引登録エラーリスト取得
	 * @return  array エラーリスト
	 */
	function &getEntryErrList() {
		return $this->entryTranEdyOutput->getErrList();
	}

	/**
	 * 決済実行エラーリスト取得
	 * @return array エラーリスト
	 */
	function &getExecErrList() {
		return $this->execTranEdyOutput->getErrList();
	}

	/**
	 * 取引登録エラー発生判定
	 * @return boolean 取引登録時エラー有無(true=エラー発生)
	 */
	function isEntryErrorOccurred() {
		$entryErrList =& $this->entryTranEdyOutput->getErrList();
		return 0 < count($entryErrList);
	}

	/**
	 * 決済実行エラー発生判定
	 * @return boolean 決済実行時エラー有無(true=エラー発生)
	 */
	function isExecErrorOccurred() {
		$execErrList =& $this->execTranEdyOutput->getErrList();
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