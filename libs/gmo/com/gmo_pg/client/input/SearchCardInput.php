<?php
require_once ('com/gmo_pg/client/input/BaseInput.php');

/**
 * <b>カード照会　入力パラメータクラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage input
 * @see inputPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 01-01-2008 00:00:00
 */
class SearchCardInput extends BaseInput {

	/**
	 * @var string サイトID GMOPG発行のサイト識別ID
	 */
	var $siteId;

	/**
	 * @var string サイトパスワード
	 */
	var $sitePass;

	/**
	 * @var string 会員ID
	 */
	var $memberId;

	/**
	 * @var string カード登録連番指定モード
	 */
	var $seqMode;
	
	/**
	 * @var integer カード登録連番
	 */
	var $cardSeq;
	
	/**
	 * コンストラクタ
	 *
	 * @param array $params 入力パラメータ
	 */
	function SearchCardInput($params = null) {
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
	 * サイトID取得
	 * @return string サイトID
	 */
	function getSiteId() {
		return $this->siteId;
	}

	/**
	 * サイトパスワード取得
	 * @return string サイトパスワード
	 */
	function getSitePass() {
		return $this->sitePass;
	}

	/**
	 * 会員ID取得
	 * @return string 会員ID
	 */
	function getMemberId() {
		return $this->memberId;
	}

	/**
	 * カード連番指定モード取得
	 * @return string カード連番指定モード
	 */
	function getSeqMode(){
		return $this->seqMode;
	}
	
	/**
	 * 登録カード連番取得
	 * @return integer 登録カード連番
	 */
	function getCardSeq(){
		return $this->cardSeq;
	}
	
	
	/**
	 * サイトID設定
	 *
	 * @param string $siteId サイトID
	 */
	function setSiteId($siteId) {
		$this->siteId = $siteId;
	}

	/**
	 * サイトパスワード設定
	 *
	 * @param string $sitePass サイトパスワード
	 */
	function setSitePass($sitePass) {
		$this->sitePass = $sitePass;
	}

	/**
	 * 会員ID設定
	 *
	 * @param string $memberId 会員ID
	 */
	function setMemberId($memberId) {
		$this->memberId = $memberId;
	}

	/**
	 * カード連番指定モード設定
	 * @param string $seqMode カード連番指定モード
	 */
	function setSeqMode($seqMode){
		$this->seqMode = $seqMode;
	}
	
	/**
	 * 登録カード連番設定
	 * @param integer $cardSeq 登録カード連番
	 */
	function setCardSeq($cardSeq){
		$this->cardSeq = $cardSeq;
	}
	
	/**
	 * デフォルト値設定する
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
	    
	    // 各項目の設定(CardSeqは値が数値でないものは無効とする)
	    $this->setSiteId($this->getStringValue($params, 'SiteID', $this->getSiteId()));
	    $this->setSitePass($this->getStringValue($params, 'SitePass', $this->getSitePass()));
        $this->setMemberId($this->getStringValue($params, 'MemberID', $this->getMemberId()));
        $this->setSeqMode($this->getStringValue($params , 'SeqMode' , $this->getSeqMode()));
        $this->setCardSeq($this->getIntegerValue($params , 'CardSeq' , $this->getCardSeq()));
        
	}

	/**
	 * 文字列表現
	 * URLのパラメータ文字列の形式の文字列を生成する
	 * @return string 接続文字列表現
	 */
	function toString() {
	    
	    $str  = 'SiteID=' . $this->encodeStr($this->getSiteId());
	    $str .= '&';
	    $str .= 'SitePass=' . $this->encodeStr($this->getSitePass());
	    $str .= '&';
	    $str .= 'MemberID=' . $this->encodeStr($this->getMemberId());
	    $str .= '&';
	    $str .= 'SeqMode=' . $this->encodeStr($this->getSeqMode());
	    $str .= '&';
	    $str .= 'CardSeq=' . $this->encodeStr($this->getCardSeq());
	    
	    return $str;   
	}

}
?>