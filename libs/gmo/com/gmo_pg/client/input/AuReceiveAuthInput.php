<?php
require_once ('com/gmo_pg/client/input/BaseInput.php');
/**
 * <b>auかんたん決済オーソリ認証結果取得　入力パラメータクラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage input
 * @see inputPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 2012/02/15
 */
class AuReceiveAuthInput extends BaseInput {

	/**
	 * @var string 取引ID
	 */
	var $accessID;

	/**
	 * @var string トランザクションID
	 */
	var $transactionId;

	/**
	 * @var string 支払方法
	 */
	var $paymtd;

	/**
	 * @var string X-ResultCd
	 */
	var $xResultCd;

	/**
	 * @var string プロセス日
	 */
	var $processDay;

	/**
	 * @var string プロセス時刻
	 */
	var $processTime;

	
	/**
	 * コンストラクタ
	 *
	 * @param array $params 入力パラメータ
	 */
	function AuReceiveAuthInput($params = null) {
		$this->__construct($params);
	}

	/**
	 * コンストラクタ
	 *
	 * @param array $params 入力パラメータ
	 */
	function __construct($params = null) {
		parent::__construct($params);
	}

	
	/**
	 * 取引ID取得
	 * @return string 取引ID
	 */
	function getAccessID() {
		return $this->accessID;
	}

	/**
	 * トランザクションID取得
	 * @return string トランザクションID
	 */
	function getTransactionId() {
		return $this->transactionId;
	}

	/**
	 * 支払方法取得
	 * @return string 支払方法
	 */
	function getPaymtd() {
		return $this->paymtd;
	}

	/**
	 * X-ResultCd取得
	 * @return string X-ResultCd
	 */
	function getXResultCd() {
		return $this->xResultCd;
	}

	/**
	 * プロセス日取得
	 * @return string プロセス日
	 */
	function getProcessDay() {
		return $this->processDay;
	}

	/**
	 * プロセス時刻取得
	 * @return string プロセス時刻
	 */
	function getProcessTime() {
		return $this->processTime;
	}

	/**
	 * 取引ID設定
	 *
	 * @param string $accessID
	 */
	function setAccessID($accessID) {
		$this->accessID = $accessID;
	}

	/**
	 * トランザクションID設定
	 *
	 * @param string $transactionId
	 */
	function setTransactionId($transactionId) {
		$this->transactionId = $transactionId;
	}

	/**
	 * 支払方法設定
	 *
	 * @param string $paymtd
	 */
	function setPaymtd($paymtd) {
		$this->paymtd = $paymtd;
	}

	/**
	 * X-ResultCd設定
	 *
	 * @param string $xResultCd
	 */
	function setXResultCd($xResultCd) {
		$this->xResultCd = $xResultCd;
	}

	/**
	 * プロセス日設定
	 *
	 * @param string $processDay
	 */
	function setProcessDay($processDay) {
		$this->processDay = $processDay;
	}

	/**
	 * プロセス時刻設定
	 *
	 * @param string $processTime
	 */
	function setProcessTime($processTime) {
		$this->processTime = $processTime;
	}


	/**
	 * デフォルト値設定
	 */
	function setDefaultValues() {
	   
	}

	/**
	 * 入力パラメータ群の値を設定する
	 *
	 * @param IgnoreCaseMap $params 入力パラメータ
	 */
	function setInputValues($params) {
		// 入力パラメータがnullの場合は設定処理を行わない
	    if (is_null($params)) {
	        return;
	    }
	    
	    $this->setAccessID($this->getStringValue($params, 'AccessID', $this->getAccessID()));
	    $this->setTransactionId($this->getStringValue($params, 'transactionId', $this->getTransactionId()));
	    $this->setPaymtd($this->getStringValue($params, 'paymtd', $this->getPaymtd()));
	    $this->setXResultCd($this->getStringValue($params, 'X-ResultCd', $this->getXResultCd()));
	    $this->setProcessDay($this->getStringValue($params, 'processDay', $this->getProcessDay()));
	    $this->setProcessTime($this->getStringValue($params, 'processTime', $this->getProcessTime()));
	}

	/**
	 * 文字列表現
	 * @return string 接続文字列表現
	 */
	function toString() {
	    $str .= 'AccessID=' . $this->encodeStr($this->getAccessID());
	    $str .= '&';
	    $str .= 'transactionId=' . $this->encodeStr($this->getTransactionId());
	    $str .= '&';
	    $str .= 'paymtd=' . $this->encodeStr($this->getPaymtd());
	    $str .= '&';
	    $str .= 'X-ResultCd=' . $this->encodeStr($this->getXResultCd());
	    $str .= '&';
	    $str .= 'processDay=' . $this->encodeStr($this->getProcessDay());
	    $str .= '&';
	    $str .= 'processTime=' . $this->encodeStr($this->getProcessTime());
	    
	    return $str;
	}


}
?>