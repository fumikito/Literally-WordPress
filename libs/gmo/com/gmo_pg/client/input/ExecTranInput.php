<?php
require_once ('com/gmo_pg/client/input/BaseInput.php');
/**
 * <b>決済実行　入力パラメータクラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage input
 * @see inputPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 01-01-2008 00:00:00
 */
class ExecTranInput extends BaseInput {

	/**
	 * @var string 取引ID。GMO-PGが払い出した、取引を特定するID
	 */
	var $accessId;

	/**
	 * @var string 取引パスワード。取引IDと対になるパスワード
	 */
	var $accessPass;

	/**
	 * @var string オーダーID。加盟店様が発番した、取引を表すID
	 */
	var $orderId;

	/**
	 * @var string 支払方法
	 */
	var $method;

	/**
	 * @var integer 支払回数
	 */
	var $payTimes;

	/**
	 * @var string カード番号
	 */
	var $cardNo;

	/**
	 * @var string サイトID
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
	 * @var string カード連番モード
	 */
	var $seqMode;
	
	/**
	 * @var integer 登録カード連番
	 */
	var $cardSeq;
	
	/**
	 * @var string カードパスワード
	 */
	var $cardPass;
	
	/**
	 * @var string 有効期限
	 */
	var $expire;

	/**
	 * @var string セキュリティコード
	 */
	var $securityCode;

	/**
	 * @var string HTTP_ACCEPT
	 */
	var $httpAccept;

	/**
	 * @var string HTTP_USER_AGENT
	 */
	var $httpUserAgent;

	/**
	 * @var string 加盟店自由項目1
	 */
	var $clientField1;

	/**
	 * @var string 加盟店自由項目
	 */
	var $clientField2;

	/**
	 * @var string 加盟店自由項目3
	 */
	var $clientField3;

	/**
	 * @var string 加盟店自由項目返却フラグ
	 */
	var $clientFieldFlag;

	/**
     * @var string 使用端末情報
     */
    var $deviceCategory;
	
	/**
	 * コンストラクタ
	 *
	 * @param array $params 入力パラメータ
	 */
	function ExecTranInput($params = null) {
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
	 * デフォルト値を設定する
	 */
	function setDefaultValues() {
	    // 加盟店自由項目返却フラグ(固定値)
        $this->clientFieldFlag = "1";
        // 使用端末情報(固定値)
        $this->deviceCategory = "0";
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
	    
	    // 各項目の設定(PayTimesは値が数値でないものは無効とする)
        $this->setAccessId($this->getStringValue($params, 'AccessID', $this->getAccessId()));
	    $this->setAccessPass($this->getStringValue($params, 'AccessPass', $this->getAccessPass()));
	    $this->setOrderId($this->getStringValue($params, 'OrderID', $this->getOrderId()));
	    $this->setMethod($this->getStringValue($params, 'Method', $this->getMethod()));
	    $this->setPayTimes($this->getIntegerValue($params, 'PayTimes', $this->getPayTimes()));
	    $this->setCardNo($this->getStringValue($params, 'CardNo', $this->getCardNo()));
	    $this->setSiteId($this->getStringValue($params, 'SiteID',$this->getSiteId()));
	    $this->setSitePass($this->getStringValue($params , 'SitePass' , $this->getSitePass()));
	    $this->setMemberId($this->getStringValue($params,'MemberID',$this->getMemberId()));
	    $this->setSeqMode($this->getStringValue($params,'SeqMode',$this->getSeqMode()));
	    $this->setCardSeq($this->getIntegerValue($params,'CardSeq',$this->getCardSeq()));
		$this->setCardPass($this->getStringValue($params,'CardPass',$this->getCardPass()));
	    $this->setExpire($this->getStringValue($params, 'Expire', $this->getExpire()));
	    $this->setSecurityCode($this->getStringValue($params, 'SecurityCode', $this->getSecurityCode()));
	    $this->setHttpAccept($this->getStringValue($params, 'HttpAccept', $this->getHttpAccept()));
	    $this->setHttpUserAgent($this->getStringValue($params, 'HttpUserAgent', $this->getHttpUserAgent()));
	    $this->setClientField1($this->getStringValue($params, 'ClientField1', $this->getClientField1()));
	    $this->setClientField2($this->getStringValue($params, 'ClientField2', $this->getClientField2()));
	    $this->setClientField3($this->getStringValue($params, 'ClientField3', $this->getClientField3()));
	}

	
	/**
	 * 取引ID取得
	 * @return string 取引ID
	 */
	function getAccessId() {
		return $this->accessId;
	}

	/**
	 * 取引パスワード取得
	 * @return string 取引パスワード
	 */
	function getAccessPass() {
		return $this->accessPass;
	}

	/**
	 * オーダーID取得
	 * @return string オーダーID
	 */
	function getOrderId() {
		return $this->orderId;
	}

	/**
	 * 支払回数取得
	 * @return integer 支払回数
	 */
	function getPayTimes() {
		return $this->payTimes;
	}

	/**
	 * カード番号取得
	 * @return string カード番号
	 */
	function getCardNo() {
		return $this->cardNo;
	}

	/**
	 * サイトID取得
	 * @return string サイトID
	 */
	function getSiteId(){
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
	function getMemberId(){
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
	 * カードパスワード取得
	 * @return string カードパスワード
	 */
	function getCardPass(){
		return $this->cardPass;
	}
	
	/**
	 * 支払い方法取得
	 * @return string 支払方法
	 */
	function getMethod() {
		return $this->method;
	}

	/**
	 * 有効期限取得
	 * @return string 有効期限(YYMM)
	 */
	function getExpire() {
		return $this->expire;
	}

	/**
	 * セキュリティコード取得
	 * @return string セキュリティコード
	 */
	function getSecurityCode() {
		return $this->securityCode;
	}

	/**
	 * HTTP_ACCEPT取得
	 * @return string HTTP_ACCEPT
	 */
	function getHttpAccept() {
		return $this->httpAccept;
	}

	/**
	 * HTTP_USER_AGENT取得
	 * @return string HTTP_USER_AGENT
	 */
	function getHttpUserAgent() {
		return $this->httpUserAgent;
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
	 * 取引ID設定
	 *
	 * @param string $accessId 取引ID
	 */
	function setAccessId($accessId) {
		$this->accessId = $accessId;
	}

	/**
	 * 取引パスワードを設定
	 * 
	 * @param string $accessPass 取引パスワード
	 */
	function setAccessPass($accessPass) {
		$this->accessPass = $accessPass;
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
	 * 支払い方法設定
	 *
	 * @param string $method 支払い方法
	 */
	function setMethod($method) {
		$this->method = $method;
	}

	/**
	 * 支払回数設定
	 *
	 * @param integer $payTimes 支払回数
	 */
	function setPayTimes($payTimes) {
		$this->payTimes = $payTimes;
	}

	/**
	 * カード番号設定
	 *
	 * @param string $cardNo カード番号
	 */
	function setCardNo($cardNo) {
		$this->cardNo = $cardNo;
	}

	/**
	 * サイトID設定
	 * @param string $siteId サイトID
	 */
	function setSiteId($siteId){
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
	function setMemberId($memberId){
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
	 * カードパスワード設定
	 * @param string $cardPass カードパスワード
	 */
	function setCardPass($cardPass){
		$this->cardPass=$cardPass;
	}
	
	/**
	 * 有効期限設定
	 *
	 * @param string $expire 有効期限(YYMM)
	 */
	function setExpire($expire) {
		$this->expire = $expire;
	}

	/**
	 * セキュリティコード設定
	 *
	 * @param string $securityCode セキュリティコード
	 */
	function setSecurityCode($securityCode) {
		$this->securityCode = $securityCode;
	}

	/**
	 * HTTP_ACCEPT設定
	 *
	 * @param string $httpAccept HTTP_ACCEPT
	 */
	function setHttpAccept($httpAccept) {
		$this->httpAccept = $httpAccept;
	}

	/**
	 * HTTP_USER_AGENT設定
	 *
	 * @param string $httpUserAgent HTTP_USER_AGENT
	 */
	function setHttpUserAgent($httpUserAgent) {
		$this->httpUserAgent = $httpUserAgent;
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
	 * URLのパラメータ文字列の形式の文字列を生成する
	 * @return string 接続文字列表現
	 */
	function toString() {
	    
	    $str  = 'AccessID=' . $this->encodeStr($this->getAccessId());
	    $str .= '&';
	    $str .= 'AccessPass=' . $this->encodeStr($this->getAccessPass());
	    $str .= '&';
	    $str .= 'OrderID=' . $this->encodeStr($this->getOrderId());
	    $str .= '&';
	    $str .= 'Method=' . $this->encodeStr($this->getMethod());
	    $str .= '&';
	    $str .= 'PayTimes=' . $this->encodeStr($this->getPayTimes());
	    $str .= '&';
	    $str .= 'CardNo=' . $this->encodeStr($this->getCardNo());
	    $str .= '&';
	    $str .= 'SiteID=' . $this->encodeStr($this->getSiteId());
	    $str .= '&';
	    $str .= 'SitePass=' . $this->encodeStr($this->getSitePass());
	    $str .= '&';
	    $str .= 'MemberID=' . $this->encodeStr($this->getMemberId());
	    $str .= '&';
	    $str .= 'SeqMode=' . $this->encodeStr($this->getSeqMode());
	    $str .= '&';
	    $str .= 'CardSeq=' . $this->encodeStr($this->getCardSeq());
	    $str .= '&';
	    $str .= 'CardPass=' . $this->encodeStr($this->getCardPass());
	    $str .= '&';
	    $str .= 'Expire=' . $this->encodeStr($this->getExpire());
	    $str .= '&';
	    $str .= 'SecurityCode=' . $this->encodeStr($this->getSecurityCode());
	    $str .= '&';
	    $str .= 'HttpAccept=' . $this->encodeStr($this->getHttpAccept());
	    $str .= '&';
	    $str .= 'HttpUserAgent=' . $this->encodeStr($this->getHttpUserAgent());
	    $str .= '&';
	    $str .= 'ClientField1=' . $this->encodeStr($this->getClientField1());
	    $str .= '&';
	    $str .= 'ClientField2=' . $this->encodeStr($this->getClientField2());
	    $str .= '&';
	    $str .= 'ClientField3=' . $this->encodeStr($this->getClientField3());
	    $str .= '&';
	    $str .= 'ClientFieldFlag=' . $this->clientFieldFlag;
	    $str .= '&';
	    $str .= 'DeviceCategory=' . $this->deviceCategory;
	    
	    return $str;
	}
}
?>