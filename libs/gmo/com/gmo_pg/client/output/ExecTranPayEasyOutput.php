<?php
require_once ('com/gmo_pg/client/output/BaseOutput.php');
/**
 * <b>PayEasy決済実行　出力パラメータクラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage output
 * @see outputPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 03-07-2008 00:00:00
 */
class ExecTranPayEasyOutput extends BaseOutput {

	/**
	 * @var string オーダーID
	 */
	var $orderId;
	
	/**
	 * お客様番号
	 * 
	 */
	var $custID;

	/** 確認番号
	 * 
	 */
	var $confNo;
	/**
	 * 収納機関番号
	 * 
	 */
	var $bkCode;
	/**
	 * 暗号化決済番号
	 * 
	 */
	var $encryptReceiptNo;
	/**
	 * 支払期限日時
	 * 
	 */
	var $paymentTerm = null;
	/**
	 * @var string 決済日付（yyyyMMddHHmmss）
	 */
	var $tranDate;
	/**
	 * @var string MD5ハッシュ（orderId～tranDate + ショップパスワードのMD5ハッシュ）
	 */
	var $checkString;

	/**
	 * @var string 加盟店自由項目1
	 */
	var $clientField1;

	/**
	 * @var string 加盟店自由項目2
	 */
	var $clientField2;

	/**
	 * @var string 加盟店自由項目3
	 */
	var $clientField3;
	
	/**
	 * コンストラクタ
	 *
	 * @param IgnoreCaseMap $params 出力パラメータ
	 */
	function ExecTranPayEasyOutput($params = null) {
		$this->__construct($params);
	}

	/**
	 * コンストラクタ
	 *
	 * @param IgnoreCaseMap $params 出力パラメータ
	 */
	function __construct($params = null) {
		parent::__construct($params);
		
		// 引数が無い場合は戻る
        if (is_null($params)) {
            return;
        }
        
        // マップの展開
        $this->setOrderId($params->get('OrderID'));
        $this->setCustId($params->get('CustID'));
        $this->setConfNo($params->get('ConfNo'));
        $this->setBkCode($params->get('BkCode'));
        $this->setEncryptReceiptNo($params->get('EncryptReceiptNo'));
        $this->setPaymentTerm($params->get('PaymentTerm'));
        $this->setTranDate($params->get('TranDate'));
        $this->setCheckString($params->get('CheckString'));
        $this->setClientField1($params->get('ClientField1'));
        $this->setClientField2($params->get('ClientField2'));
        $this->setClientField3($params->get('ClientField3'));
        
	}

	/**
	 * オーダーID取得
	 * @return string オーダーID
	 */
	function getOrderId() {
		return $this->orderId;
	}

	/**
	 * お客様番号を取得します。
	 * 
	 * @return	$String	お客様番号
	 */
	function getCustId() {
		return $this->custID;
	}
	
	/**
	 * 確認番号を取得します。
	 * 
	 * @return	$String	確認番号
	 */
	function getConfNo() {
		return $this->confNo;
	}
	
	/**
	 * 収納機関番号を取得します。
	 * 
	 * @return	$String	収納機関番号
	 */
	function getBkCode() {
		return $this->bkCode;
	}

	/**
	 * 暗号化決済番号を取得します。
	 * 
	 * @return	$String	暗号化決済番号
	 */
	function getEncryptReceiptNo() {
		return $this->encryptReceiptNo;
	}

	/**
	 * 支払期限日時を取得します。
	 * 
	 * @return	$timestamp	支払期限日時
	 */
	function getPaymentTerm() {
		return $this->paymentTerm;
	}

	/**
	 * 決済日付取得
	 * @return string 決済日付(yyyyMMddHHmmss形式)
	 */
	function getTranDate() {
		return $this->tranDate;
	}

	/**
	 * MD5ハッシュ取得
	 * @return string MD5ハッシュ
	 */
	function getCheckString() {
		return $this->checkString;
	}

	/**
	 * 加盟店自由項目1取得
	 * @return string 加盟店自由項目1
	 */
	function getClientField1() {
		return $this->clientField1;
	}

	/**
	 * 加盟店自由項目2取得
	 * @return string 加盟店自由項目2
	 */
	function getClientField2() {
		return $this->clientField2;
	}

	/**
	 * 加盟店自由項目3取得
	 * @return string 加盟店自由項目3
	 */
	function getClientField3() {
		return $this->clientField3;
	}

	/**
	 * オーダーID設定
	 *
	 * @param string $orderId オーダーID
	 */
	function setOrderId($orderId) {
		$this->orderId = $orderId;
	}

	/**
	 * お客様番号を格納します。
	 * 
	 * @param	$String	お客様番号
	 */
	function setCustId($String) {
		$this->custID = $String;
	}
	
	/**
	 * 確認番号を格納します。
	 * 
	 * @param	$String	確認番号
	 */
	function setConfNo($String) {
		$this->confNo = $String;
	}

	/**
	 * 支払期限日時を格納します。
	 * 
	 * @param	$timestamp	支払期限日時
	 */
	function setPaymentTerm($timestamp) {
		$this->paymentTerm = $timestamp;
	}

	/**
	 * 暗号化決済番号を格納します。
	 * 
	 * @param	$String	暗号化決済番号
	 */
	function setEncryptReceiptNo($String) {
		$this->encryptReceiptNo = $String;
	}

	/**
	 * 収納機関番号を格納します。
	 * 
	 * @param	$String	収納機関番号
	 */
	function setBkCode($String) {
		$this->bkCode = $String;
	}
	

	/**
	 * 決済日付設定
	 *
	 * @param string $tranDate 決済日付(yyyyMMddHHmmss形式)
	 */
	function setTranDate($tranDate) {
		$this->tranDate = $tranDate;
	}

	/**
	 * MD5ハッシュ設定
	 *
	 * @param string $checkString MD5ハッシュ
	 */
	function setCheckString($checkString) {
		$this->checkString = $checkString;
	}

	/**
	 * 加盟店自由項目1設定
	 *
	 * @param string $clientField1 加盟店自由項目1
	 */
	function setClientField1($clientField1) {
		$this->clientField1 = $clientField1;
	}

	/**
	 * 加盟店自由項目2設定
	 *
	 * @param string $clientField2 加盟店自由項目2
	 */
	function setClientField2($clientField2) {
		$this->clientField2 = $clientField2;
	}

	/**
	 * 加盟店自由項目3設定
	 *
	 * @param string $clientField3 加盟店自由項目3
	 */
	function setClientField3($clientField3) {
		$this->clientField3 = $clientField3;
	}

	
	/**
	 * 文字列表現
	 * <p>
	 *  現在の各パラメータを、パラメータ名=値&パラメータ名=値の形式で取得します。
	 * </p>
	 * @return 出力パラメータ文字列表現
	 */
	function toString() {
	    $str .= 'OrderID=' . $this->getOrderId();
        $str .= '&';
	    $str .= 'CustID=' . $this->getCustId();
        $str .= '&';
        $str .= 'BkCode=' . $this->getBkCode();
	    $str .= '&';
        $str .= 'ConfNo=' . $this->getConfNo();
        $str .= '&';
        $str .= 'EncryptReceiptNo=' . $this->getEncryptReceiptNo();
        $str .= '&';
        $str .= 'PaymentTerm=' . $this->getPaymentTerm();
        $str .= '&';        
        $str .= 'TranDate=' . $this->getTranDate();
        $str .= '&';        
        $str .= 'CheckString=' . $this->getCheckString();
        $str .= '&';
        $str .= 'ClientField1=' . $this->getClientField1();
        $str .= '&';
        $str .= 'ClientField2=' . $this->getClientField2();
        $str .= '&';
        $str .= 'ClientField3=' . $this->getClientField3();
            
        if ($this->isErrorOccurred()) {
            // エラー文字列を連結して返す
            $errString = parent::toString();
            $str .= '&' . $errString;
        }
        
        
        return $str;
	}

}
?>