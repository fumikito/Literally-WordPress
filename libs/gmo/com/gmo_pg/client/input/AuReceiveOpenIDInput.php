<?php
require_once ('com/gmo_pg/client/input/BaseInput.php');
/**
 * <b>auかんたん決済OpenID認証結果取得　入力パラメータクラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage input
 * @see inputPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 2012/02/15
 */
class AuReceiveOpenIDInput extends BaseInput {

	/**
	 * @var string openid.claimed_id
	 */
	var $openidClaimedId;

	
	/**
	 * コンストラクタ
	 *
	 * @param array $params 入力パラメータ
	 */
	function AuReceiveOpenIDInput($params = null) {
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
	 * openid.claimed_id取得
	 * @return string openid.claimed_id
	 */
	function getOpenidClaimedId() {
		return $this->openidClaimedId;
	}

	/**
	 * openid.claimed_id設定
	 *
	 * @param string $openidClaimedId
	 */
	function setOpenidClaimedId($openidClaimedId) {
		$this->openidClaimedId = $openidClaimedId;
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
	    
	    $this->setOpenidClaimedId($this->getStringValue($params, 'openid.claimed_id', $this->getOpenidClaimedId()));
	}

	/**
	 * 文字列表現
	 * @return string 接続文字列表現
	 */
	function toString() {
	    $str .= 'openid.claimed_id=' . $this->encodeStr($this->getOpenidClaimedId());
	    
	    return $str;
	}


}
?>