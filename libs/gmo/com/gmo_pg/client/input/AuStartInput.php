<?php
require_once ('com/gmo_pg/client/input/BaseInput.php');
/**
 * <b>auかんたん決済OpenID連携　入力パラメータクラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage input
 * @see inputPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 2012/02/15
 */
class AuStartInput extends BaseInput {

	/**
	 * @var string 支払手続き開始IFのURL
	 */
	var $startURL;

	/**
	 * @var string 取引ID
	 */
	var $accessID;

	/**
	 * @var string 決済トークン
	 */
	var $token;

	
	/**
	 * コンストラクタ
	 *
	 * @param array $params 入力パラメータ
	 */
	function AuStartInput($params = null) {
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
	 * 支払手続き開始IFのURL取得
	 * @return string 支払手続き開始IFのURL
	 */
	function getStartURL() {
		return $this->startURL;
	}

	/**
	 * 取引ID取得
	 * @return string 取引ID
	 */
	function getAccessID() {
		return $this->accessID;
	}

	/**
	 * 決済トークン取得
	 * @return string 決済トークン
	 */
	function getToken() {
		return $this->token;
	}

	/**
	 * 支払手続き開始IFのURL設定
	 *
	 * @param string $startURL
	 */
	function setStartURL($startURL) {
		$this->startURL = $startURL;
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
	 * 決済トークン設定
	 *
	 * @param string $token
	 */
	function setToken($token) {
		$this->token = $token;
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
	    
	    $this->setStartURL($this->getStringValue($params, 'StartURL', $this->getStartURL()));
	    $this->setAccessID($this->getStringValue($params, 'AccessID', $this->getAccessID()));
	    $this->setToken($this->getStringValue($params, 'Token', $this->getToken()));
	}

	/**
	 * 文字列表現
	 * @return string 接続文字列表現
	 */
	function toString() {
	    $str .= 'StartURL=' . $this->encodeStr($this->getStartURL());
	    $str .= '&';
	    $str .= 'AccessID=' . $this->encodeStr($this->getAccessID());
	    $str .= '&';
	    $str .= 'Token=' . $this->encodeStr($this->getToken());
	    
	    return $str;
	}


}
?>