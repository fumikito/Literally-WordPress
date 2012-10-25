<?php
require_once ('com/gmo_pg/client/output/BaseOutput.php');

/**
 * <b>Webmoney取引登録　出力パラメータクラス</b>
 *
 * @package com.gmo_pg.client
 * @subpackage output
 * @see outputPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 04-08-2010
 */
class EntryTranWebmoneyOutput extends BaseOutput {

	/**
	 * @var string オーダID
	 */
	var $orderId;

	/**
	 * @var string 取引ID
	 */
	var $accessId;

	/**
	 * @var string 取引パスワード
	 */
	var $accessPass;

	/**
	 * コンストラクタ
	 *
	 * @param IgnoreCaseMap $params  出力パラメータ
	 */
	function EntryTranWebmoneyOutput($params = null) {
		$this->__construct($params);
	}

	function __construct($params = null) {
		parent::__construct($params);

		// 引数がない場合は戻る
		if(is_null($params)) {
			return;
		}

		// マップの展開
		$this->setOrderId($params->get('OrderID'));
		$this->setAccessId($params->get('AccessID'));
		$this->setAccessPass($params->get('AccessPass'));
	}


	/**
	 * オーダIDを取得する
	 * @return string オーダID
	 */
	function getOrderId(){
		return $this->orderId;
	}

	/**
	 * アクセスIDを取得する。
	 * @return string アクセスID
	 */
	function getAccessId(){
		return $this->accessId;
	}

	/**
	 * アクセスパスワードを取得する
	 * @return string アクセスパスワード
	 */
	function getAccessPass(){
		return $this->accessPass;
	}

	/**
	 * オーダIDを設定する
	 * @param $orderId オーダID
	 */
	function setOrderId( $orderId ){
		$this->orderId = $orderId;
	}

	/**
	 * アクセスIDを設定する
	 * @param $accessId アクセスID
	 */
	function setAccessId( $accessId ){
		$this->accessId = $accessId;
	}

	/**
	 * アクセスパスワードを設定する
	 * @param $accessPass
	 * @return $accessPass アクセスパスワード
	 */
	function setAccessPass( $accessPass ){
		$this->accessPass = $accessPass;
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
		$str  = 'AccessID=' . $this->getAccessId();
		$str .= '&';
		$str .= 'AccessPass=' . $this->getAccessPass();
			
		if ($this->isErrorOccurred()) {
			// エラー文字列を連結して返す
			$errString = parent::toString();
			$str .= '&' . $errString;
		}
		return $str;
	}
}