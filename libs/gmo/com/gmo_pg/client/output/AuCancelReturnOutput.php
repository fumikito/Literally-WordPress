<?php
require_once ('com/gmo_pg/client/output/BaseOutput.php');
/**
 * <b>auかんたん決済決済取消　出力パラメータクラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage output
 * @see outputPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 2012/02/15
 */
class AuCancelReturnOutput extends BaseOutput {

	/**
	 * @var string オーダーID
	 */
	var $orderID;

	/**
	 * @var string 現状態
	 */
	var $status;

	/**
	 * @var string 利用金額－キャンセル金額
	 */
	var $amount;

	/**
	 * @var string 税送料－キャンセル税送料
	 */
	var $tax;

	/**
	 * @var string キャンセル金額
	 */
	var $cancelAmount;

	/**
	 * @var string キャンセル税送料
	 */
	var $cancelTax;

	/**
	 * コンストラクタ
	 *
	 * @param IgnoreCaseMap $params  出力パラメータ
	 */
	function AuCancelReturnOutput($params = null) {
		$this->__construct($params);
	}

	
	/**
	 * コンストラクタ
	 *
	 * @param IgnoreCaseMap $params  出力パラメータ
	 */
	function __construct($params = null) {
		parent::__construct($params);
		
		// 引数が無い場合は戻る
		if (is_null($params)) {
            return;
        }
		
        // マップの展開
        $this->setOrderID($params->get('OrderID'));
        $this->setStatus($params->get('Status'));
        $this->setAmount($params->get('Amount'));
        $this->setTax($params->get('Tax'));
        $this->setCancelAmount($params->get('CancelAmount'));
        $this->setCancelTax($params->get('CancelTax'));
	}

	/**
	 * オーダーID取得
	 * @return string オーダーID
	 */
	function getOrderID() {
		return $this->orderID;
	}

	/**
	 * 現状態取得
	 * @return string 現状態
	 */
	function getStatus() {
		return $this->status;
	}

	/**
	 * 利用金額－キャンセル金額取得
	 * @return integer 利用金額－キャンセル金額
	 */
	function getAmount() {
		return $this->amount;
	}

	/**
	 * 税送料－キャンセル税送料取得
	 * @return integer 税送料－キャンセル税送料
	 */
	function getTax() {
		return $this->tax;
	}

	/**
	 * キャンセル金額取得
	 * @return integer キャンセル金額
	 */
	function getCancelAmount() {
		return $this->cancelAmount;
	}

	/**
	 * キャンセル税送料取得
	 * @return integer キャンセル税送料
	 */
	function getCancelTax() {
		return $this->cancelTax;
	}

	/**
	 * オーダーID設定
	 *
	 * @param string $orderID
	 */
	function setOrderID($orderID) {
		$this->orderID = $orderID;
	}

	/**
	 * 現状態設定
	 *
	 * @param string $status
	 */
	function setStatus($status) {
		$this->status = $status;
	}

	/**
	 * 利用金額－キャンセル金額設定
	 *
	 * @param integer $amount
	 */
	function setAmount($amount) {
		$this->amount = $amount;
	}

	/**
	 * 税送料－キャンセル税送料設定
	 *
	 * @param integer $tax
	 */
	function setTax($tax) {
		$this->tax = $tax;
	}

	/**
	 * キャンセル金額設定
	 *
	 * @param integer $cancelAmount
	 */
	function setCancelAmount($cancelAmount) {
		$this->cancelAmount = $cancelAmount;
	}

	/**
	 * キャンセル税送料設定
	 *
	 * @param integer $cancelTax
	 */
	function setCancelTax($cancelTax) {
		$this->cancelTax = $cancelTax;
	}

	/**
	 * 文字列表現
	 * <p>
	 *  現在の各パラメータを、パラメータ名=値&パラメータ名=値の形式で取得します。
	 * </p>
	 * @return string 出力パラメータの文字列表現
	 */
	function toString() {
	    $str .= 'OrderID=' . $this->encodeStr($this->getOrderID());
	    $str .= '&';
	    $str .= 'Status=' . $this->encodeStr($this->getStatus());
	    $str .= '&';
	    $str .= 'Amount=' . $this->encodeStr($this->getAmount());
	    $str .= '&';
	    $str .= 'Tax=' . $this->encodeStr($this->getTax());
	    $str .= '&';
	    $str .= 'CancelAmount=' . $this->encodeStr($this->getCancelAmount());
	    $str .= '&';
	    $str .= 'CancelTax=' . $this->encodeStr($this->getCancelTax());
	    
	    
	    if ($this->isErrorOccurred()) {
            // エラー文字列を連結して返す
            $errString = parent::toString();
            $str .= '&' . $errString;
        }	    
	    
        return $str;
	}

}
?>