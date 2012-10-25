<?php
require_once ('com/gmo_pg/client/output/BaseOutput.php');

/**
 * <b>Paypal払い戻し 出力パラメータクラス</b>
 *
 *
 * @package com.gmo_pg.client
 * @subpackage output
 * @see outputPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 12-24-2009 00:00:00
 */
class CancelTranPaypalOutput extends BaseOutput {

	/**
	 * @var string オーダID
	 */
	var $orderId;

	/**
	 * @var string トランザクションID
	 */
	var $tranId;

	/**
	 * @var string 決済日付
	 */
	var $tranDate;

	/**
	 * コンストラクタ
	 *
	 * @param IgnoreCaseMap $params  出力パラメータ
	 */
	function CancelTranPaypalOutput($params = null) {
		$this->__construct($params);
	}

	/**
	 * コンストラクタ
	 *
	 * @param IgnoreCaseMap $params  出力パラメータ
	 */

	function __construct($params = null) {
		parent::__construct($params);

		// 引数がない場合は戻る
		if(is_null($params)) {
			return;
		}

		// マップの展開
		$this->setOrderId($params->get('OrderID'));
		$this->setTranId($params->get('TranID'));
		$this->setTranDate($params->get('TranDate'));
	}


	/**
	 * オーダIDを取得する
	 * @return string オーダID
	 */
	function getOrderId(){
		return $this->orderId;
	}

	/**
	 * トランザクションIDを取得する
	 * @return string トランザクションID
	 */
	function getTranId(){
		return $this->tranId;
	}

	/**
	 * 決済日付を取得する
	 * @return string 決済日付
	 */
	function getTranDate(){
		return $this->tranDate;
	}

	/**
	 * オーダIDを設定する
	 * @param $orderId オーダID
	 */
	function setOrderId( $orderId ){
		$this->orderId = $orderId;
	}

	/**
	 * トランザクションIDを設定する
	 * @param string $tranId トランザクションID
	 */
	function setTranId( $tranId ){
		$this->tranId = $tranId;
	}

	/**
	 * 決済日付を設定する
	 * @param string $tranDate 決済日付
	 */
	function setTranDate( $tranDate ){
		$this->tranDate = $tranDate;
	}


	/**
	 * 文字列表現
	 * <p>
	 *  現在の各パラメータを、パラメータ名=値&パラメータ名=値の形式で取得します。
	 * </p>
	 * @return string 出力パラメータの文字列表現
	 */
	function toString() {
		$str  = 'OrderID=' . $this->getOrderId();
		$str .= '&';
		$str .= 'TranID=' . $this->getAccessId();
		$str .= '&';
		$str .= 'TranDate=' . $this->getAccessPass();
			
		if ($this->isErrorOccurred()) {
			// エラー文字列を連結して返す
			$errString = parent::toString();
			$str .= '&' . $errString;
		}
		return $str;
	}
}