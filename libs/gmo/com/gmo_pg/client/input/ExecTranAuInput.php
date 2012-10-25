<?php
require_once ('com/gmo_pg/client/input/BaseInput.php');
/**
 * <b>auかんたん決済決済実行　入力パラメータクラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage input
 * @see inputPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 2012/02/15
 */
class ExecTranAuInput extends BaseInput {

	/**
	 * @var string ショップID
	 */
	var $shopID;

	/**
	 * @var string ショップパスワード
	 */
	var $shopPass;

	/**
	 * @var string 取引ID
	 */
	var $accessID;

	/**
	 * @var string 取引パスワード
	 */
	var $accessPass;

	/**
	 * @var string オーダーID
	 */
	var $orderID;

	/**
	 * @var string サイトID
	 */
	var $siteID;

	/**
	 * @var string サイトパスワード
	 */
	var $sitePass;

	/**
	 * @var string 会員ID
	 */
	var $memberID;

	/**
	 * @var string 会員名
	 */
	var $memberName;

	/**
	 * @var string 会員作成フラグ
	 */
	var $createMember;

	/**
	 * @var string 自由項目１
	 */
	var $clientField1;

	/**
	 * @var string 自由項目２
	 */
	var $clientField2;

	/**
	 * @var string 自由項目３
	 */
	var $clientField3;

	/**
	 * @var string 摘要
	 */
	var $commodity;

	/**
	 * @var string 決済結果戻しURL
	 */
	var $retURL;

	/**
	 * @var string 決済結果URL有効期限秒
	 */
	var $paymentTermSec;

	/**
	 * @var string 表示サービス名
	 */
	var $serviceName;

	/**
	 * @var string 表示電話番号
	 */
	var $serviceTel;

	
	/**
	 * コンストラクタ
	 *
	 * @param array $params 入力パラメータ
	 */
	function ExecTranAuInput($params = null) {
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
	 * ショップID取得
	 * @return string ショップID
	 */
	function getShopID() {
		return $this->shopID;
	}

	/**
	 * ショップパスワード取得
	 * @return string ショップパスワード
	 */
	function getShopPass() {
		return $this->shopPass;
	}

	/**
	 * 取引ID取得
	 * @return string 取引ID
	 */
	function getAccessID() {
		return $this->accessID;
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
	function getOrderID() {
		return $this->orderID;
	}

	/**
	 * サイトID取得
	 * @return string サイトID
	 */
	function getSiteID() {
		return $this->siteID;
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
	function getMemberID() {
		return $this->memberID;
	}

	/**
	 * 会員名取得
	 * @return string 会員名
	 */
	function getMemberName() {
		return $this->memberName;
	}

	/**
	 * 会員作成フラグ取得
	 * @return string 会員作成フラグ
	 */
	function getCreateMember() {
		return $this->createMember;
	}

	/**
	 * 自由項目１取得
	 * @return string 自由項目１
	 */
	function getClientField1() {
		return $this->clientField1;
	}

	/**
	 * 自由項目２取得
	 * @return string 自由項目２
	 */
	function getClientField2() {
		return $this->clientField2;
	}

	/**
	 * 自由項目３取得
	 * @return string 自由項目３
	 */
	function getClientField3() {
		return $this->clientField3;
	}

	/**
	 * 摘要取得
	 * @return string 摘要
	 */
	function getCommodity() {
		return $this->commodity;
	}

	/**
	 * 決済結果戻しURL取得
	 * @return string 決済結果戻しURL
	 */
	function getRetURL() {
		return $this->retURL;
	}

	/**
	 * 決済結果URL有効期限秒取得
	 * @return integer 決済結果URL有効期限秒
	 */
	function getPaymentTermSec() {
		return $this->paymentTermSec;
	}

	/**
	 * 表示サービス名取得
	 * @return string 表示サービス名
	 */
	function getServiceName() {
		return $this->serviceName;
	}

	/**
	 * 表示電話番号取得
	 * @return string 表示電話番号
	 */
	function getServiceTel() {
		return $this->serviceTel;
	}

	/**
	 * ショップID設定
	 *
	 * @param string $shopID
	 */
	function setShopID($shopID) {
		$this->shopID = $shopID;
	}

	/**
	 * ショップパスワード設定
	 *
	 * @param string $shopPass
	 */
	function setShopPass($shopPass) {
		$this->shopPass = $shopPass;
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
	 * 取引パスワード設定
	 *
	 * @param string $accessPass
	 */
	function setAccessPass($accessPass) {
		$this->accessPass = $accessPass;
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
	 * サイトID設定
	 *
	 * @param string $siteID
	 */
	function setSiteID($siteID) {
		$this->siteID = $siteID;
	}

	/**
	 * サイトパスワード設定
	 *
	 * @param string $sitePass
	 */
	function setSitePass($sitePass) {
		$this->sitePass = $sitePass;
	}

	/**
	 * 会員ID設定
	 *
	 * @param string $memberID
	 */
	function setMemberID($memberID) {
		$this->memberID = $memberID;
	}

	/**
	 * 会員名設定
	 *
	 * @param string $memberName
	 */
	function setMemberName($memberName) {
		$this->memberName = $memberName;
	}

	/**
	 * 会員作成フラグ設定
	 *
	 * @param string $createMember
	 */
	function setCreateMember($createMember) {
		$this->createMember = $createMember;
	}

	/**
	 * 自由項目１設定
	 *
	 * @param string $clientField1
	 */
	function setClientField1($clientField1) {
		$this->clientField1 = $clientField1;
	}

	/**
	 * 自由項目２設定
	 *
	 * @param string $clientField2
	 */
	function setClientField2($clientField2) {
		$this->clientField2 = $clientField2;
	}

	/**
	 * 自由項目３設定
	 *
	 * @param string $clientField3
	 */
	function setClientField3($clientField3) {
		$this->clientField3 = $clientField3;
	}

	/**
	 * 摘要設定
	 *
	 * @param string $commodity
	 */
	function setCommodity($commodity) {
		$this->commodity = $commodity;
	}

	/**
	 * 決済結果戻しURL設定
	 *
	 * @param string $retURL
	 */
	function setRetURL($retURL) {
		$this->retURL = $retURL;
	}

	/**
	 * 決済結果URL有効期限秒設定
	 *
	 * @param integer $paymentTermSec
	 */
	function setPaymentTermSec($paymentTermSec) {
		$this->paymentTermSec = $paymentTermSec;
	}

	/**
	 * 表示サービス名設定
	 *
	 * @param string $serviceName
	 */
	function setServiceName($serviceName) {
		$this->serviceName = $serviceName;
	}

	/**
	 * 表示電話番号設定
	 *
	 * @param string $serviceTel
	 */
	function setServiceTel($serviceTel) {
		$this->serviceTel = $serviceTel;
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
	    
	    $this->setShopID($this->getStringValue($params, 'ShopID', $this->getShopID()));
	    $this->setShopPass($this->getStringValue($params, 'ShopPass', $this->getShopPass()));
	    $this->setAccessID($this->getStringValue($params, 'AccessID', $this->getAccessID()));
	    $this->setAccessPass($this->getStringValue($params, 'AccessPass', $this->getAccessPass()));
	    $this->setOrderID($this->getStringValue($params, 'OrderID', $this->getOrderID()));
	    $this->setSiteID($this->getStringValue($params, 'SiteID', $this->getSiteID()));
	    $this->setSitePass($this->getStringValue($params, 'SitePass', $this->getSitePass()));
	    $this->setMemberID($this->getStringValue($params, 'MemberID', $this->getMemberID()));
	    $this->setMemberName($this->getStringValue($params, 'MemberName', $this->getMemberName()));
	    $this->setCreateMember($this->getStringValue($params, 'CreateMember', $this->getCreateMember()));
	    $this->setClientField1($this->getStringValue($params, 'ClientField1', $this->getClientField1()));
	    $this->setClientField2($this->getStringValue($params, 'ClientField2', $this->getClientField2()));
	    $this->setClientField3($this->getStringValue($params, 'ClientField3', $this->getClientField3()));
	    $this->setCommodity($this->getStringValue($params, 'Commodity', $this->getCommodity()));
	    $this->setRetURL($this->getStringValue($params, 'RetURL', $this->getRetURL()));
	    $this->setPaymentTermSec($this->getStringValue($params, 'PaymentTermSec', $this->getPaymentTermSec()));
	    $this->setServiceName($this->getStringValue($params, 'ServiceName', $this->getServiceName()));
	    $this->setServiceTel($this->getStringValue($params, 'ServiceTel', $this->getServiceTel()));
	}

	/**
	 * 文字列表現
	 * @return string 接続文字列表現
	 */
	function toString() {
	    $str .= 'ShopID=' . $this->encodeStr($this->getShopID());
	    $str .= '&';
	    $str .= 'ShopPass=' . $this->encodeStr($this->getShopPass());
	    $str .= '&';
	    $str .= 'AccessID=' . $this->encodeStr($this->getAccessID());
	    $str .= '&';
	    $str .= 'AccessPass=' . $this->encodeStr($this->getAccessPass());
	    $str .= '&';
	    $str .= 'OrderID=' . $this->encodeStr($this->getOrderID());
	    $str .= '&';
	    $str .= 'SiteID=' . $this->encodeStr($this->getSiteID());
	    $str .= '&';
	    $str .= 'SitePass=' . $this->encodeStr($this->getSitePass());
	    $str .= '&';
	    $str .= 'MemberID=' . $this->encodeStr($this->getMemberID());
	    $str .= '&';
	    $str .= 'MemberName=' . $this->encodeStr($this->getMemberName());
	    $str .= '&';
	    $str .= 'CreateMember=' . $this->encodeStr($this->getCreateMember());
	    $str .= '&';
	    $str .= 'ClientField1=' . $this->encodeStr($this->getClientField1());
	    $str .= '&';
	    $str .= 'ClientField2=' . $this->encodeStr($this->getClientField2());
	    $str .= '&';
	    $str .= 'ClientField3=' . $this->encodeStr($this->getClientField3());
	    $str .= '&';
	    $str .= 'Commodity=' . $this->encodeStr($this->getCommodity());
	    $str .= '&';
	    $str .= 'RetURL=' . $this->encodeStr($this->getRetURL());
	    $str .= '&';
	    $str .= 'PaymentTermSec=' . $this->encodeStr($this->getPaymentTermSec());
	    $str .= '&';
	    $str .= 'ServiceName=' . $this->encodeStr($this->getServiceName());
	    $str .= '&';
	    $str .= 'ServiceTel=' . $this->encodeStr($this->getServiceTel());
	    
	    return $str;
	}


}
?>