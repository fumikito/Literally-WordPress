<?php
require_once ('com/gmo_pg/client/output/BaseOutput.php');
/**
 * <b>auかんたん決済決済実行　出力パラメータクラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage output
 * @see outputPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 2012/02/15
 */
class ExecTranAuOutput extends BaseOutput {

	/**
	 * @var string 取引ID
	 */
	var $accessID;

	/**
	 * @var string 決済トークン
	 */
	var $token;

	/**
	 * @var string 呼び出しURL
	 */
	var $startURL;

	/**
	 * @var string 該当トークンの有効期限 YYYYMMDDHHMM
	 */
	var $startLimitDate;

	/**
	 * コンストラクタ
	 *
	 * @param IgnoreCaseMap $params  出力パラメータ
	 */
	function ExecTranAuOutput($params = null) {
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
        $this->setAccessID($params->get('AccessID'));
        $this->setToken($params->get('Token'));
        $this->setStartURL($params->get('StartURL'));
        $this->setStartLimitDate($params->get('StartLimitDate'));
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
	 * 呼び出しURL取得
	 * @return string 呼び出しURL
	 */
	function getStartURL() {
		return $this->startURL;
	}

	/**
	 * 該当トークンの有効期限 YYYYMMDDHHMM取得
	 * @return string 該当トークンの有効期限 YYYYMMDDHHMM
	 */
	function getStartLimitDate() {
		return $this->startLimitDate;
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
	 * 呼び出しURL設定
	 *
	 * @param string $startURL
	 */
	function setStartURL($startURL) {
		$this->startURL = $startURL;
	}

	/**
	 * 該当トークンの有効期限 YYYYMMDDHHMM設定
	 *
	 * @param string $startLimitDate
	 */
	function setStartLimitDate($startLimitDate) {
		$this->startLimitDate = $startLimitDate;
	}

	/**
	 * 文字列表現
	 * <p>
	 *  現在の各パラメータを、パラメータ名=値&パラメータ名=値の形式で取得します。
	 * </p>
	 * @return string 出力パラメータの文字列表現
	 */
	function toString() {
	    $str .= 'AccessID=' . $this->encodeStr($this->getAccessID());
	    $str .= '&';
	    $str .= 'Token=' . $this->encodeStr($this->getToken());
	    $str .= '&';
	    $str .= 'StartURL=' . $this->encodeStr($this->getStartURL());
	    $str .= '&';
	    $str .= 'StartLimitDate=' . $this->encodeStr($this->getStartLimitDate());
	    
	    
	    if ($this->isErrorOccurred()) {
            // エラー文字列を連結して返す
            $errString = parent::toString();
            $str .= '&' . $errString;
        }	    
	    
        return $str;
	}

}
?>