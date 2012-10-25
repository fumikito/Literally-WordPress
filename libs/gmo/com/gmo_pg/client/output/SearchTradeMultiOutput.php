<?php
require_once ('com/gmo_pg/client/output/BaseOutput.php');
/**
 * <b>取引照会マルチ　出力パラメータクラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage output
 * @see outputPackageInfo.php 
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 2008-07-15
 */
class SearchTradeMultiOutput extends BaseOutput {

	/**
	 * @var string オーダーID
	 */
	var $orderId;
	
	/**
	 * @var string 取引ステータス
	 */
	var $status;
	
	/**
	 * @var string 処理日時
	 */
	var $processDate;
	
	/**
	 * @var string 処理区分
	 */
	var $jobCd;
	
	/**
	 * @var string 取引ID
	 */
	var $accessId;
	
	/**
	 * @var string 取引パスワード
	 */
	var $accessPass;
	
	/**
	 * @var  string 商品コード
	 */
	var $itemCode;
	
	/**
	 * @var string 通貨コード
	 */
	var $currency;
	
	/**
	 * @var integer 利用金額
	 */
	var $amount;
	
	/**
	 * @var integer 税送料
	 */
	var $tax;
	
	/**
	 * @var string サイトID
	 */
	var $siteId;
	
	/**
	 * @var string 会員ID
	 */
	var $memberId;
	
	/**
	 * @var string カード番号
	 */
	var $cardNo;
	
	/**
	 * @var string カード有効期限
	 */
	var $expire;

	/**
	 * @var string 支払い方法
	 */
	var $method;

	/**
	 * @var integer 支払回数
	 */
	var $payTimes;

	/**
	 * @var string 仕向先コード
	 */
	var $forward;
	
	/**
	 * @var string トランザクションID
	 */
	var $transactionId;
	
	/**
	 * @var string 承認番号
	 */
	var $approve;
	
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
	 * 決済方法
	 * 0：クレジット 
	 * 1：モバイルSuica 
	 * 2：モバイルEdy 
	 * 3：コンビニ 
	 * 4：Pay-easy
	 * 5：Paypal
	 * 7：Webmoney
	 * 
	 * @var string
	 */
	var $payType;
	
	var $cvsCode;
	var $cvsConfNo;
	var $cvsReceiptNo;
	var $edyReceiptNo;
	var $edyOrderNo;
	var $suicaReceiptNo;
	var $suicaOrderNo;
	var $custId;
	var $bkCode;
	var $confNo;
	var $paymentTerm;
	var $encryptReceiptNo;

	/**
	 * @var string WebMoney管理番号
	 */
	var $webmoneyMangementNo;

	/**
	 * @var string WebMoney決済コード
	 */
	var $webmoneySettleCode;

	/**
	 * @var string auかんたん決済決済情報番号
	 */
	var $auPayInfoNo;

	/**
	 * @var string auかんたん決済支払方法
	 */
	var $auPayMethod;

	/**
	 * @var string auかんたん決済キャンセル金額
	 */
	var $auCancelAmount;

	/**
	 * @var string auかんたん決済キャンセル税送料
	 */
	var $auCancelTax;

	/**
	 * @var string ドコモ決済番号
	 */
	var $docomoSettlementCode;

	/**
	 * @var string ドコモキャンセル金額
	 */
	var $docomoCancelAmount;

	/**
	 * @var string ドコモキャンセル税送料
	 */
	var $docomoCancelTax;


	/**
	 * コンストラクタ
	 *
	 * @param IgnoreCaseMap $params 出力パラメータ
	 */
	function SearchTradeMultiOutput($params = null) {
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
        $this->setStatus($params->get('Status'));
        $this->setProcessDate($params->get('ProcessDate'));
       	$this->setJobCd($params->get('JobCd'));
       	$this->setAccessId($params->get('AccessID'));
       	$this->setAccessPass($params->get('AccessPass'));
       	$this->setItemCode($params->get('ItemCode'));
       	$this->setCurrency($params->get('Currency'));
       	$tmp = $params->get('Amount');
        if (!is_null($tmp) && 0 != strlen($tmp)) {
            // 数値の場合のみ値をセットする
            $this->setAmount(is_numeric($tmp) ? $tmp : null);
        }         
       	$tmp = $params->get('Tax');
        if (!is_null($tmp) && 0 != strlen($tmp)) {
            // 数値の場合のみ値をセットする
            $this->setTax(is_numeric($tmp) ? $tmp : null);
        }         
        $this->setSiteId($params->get('SiteID'));
        $this->setMemberId($params->get('MemberID'));
        $this->setCardNo($params->get('CardNo'));
        $this->setExpire($params->get('Expire'));
        $this->setMethod($params->get('Method'));
        $times = $params->get('PayTimes');
        if (!is_null($times) && 0 != strlen($times)) {
            // 数値の場合のみ値をセットする
            $this->setPayTimes(is_numeric($times) ? $times : null);
        }
        $this->setForward($params->get('Forward'));
        $this->setTranId($params->get('TranID'));

        $this->setApprovalNo($params->get('Approve'));
        $this->setClientField1($params->get('ClientField1'));
        $this->setClientField2($params->get('ClientField2'));
        $this->setClientField3($params->get('ClientField3'));
        
        $this->setPayType($params->get('PayType'));
		$this->setCvsCode($params->get('CvsCode'));
		$this->setCvsConfNo($params->get('CvsConfNo'));
		$this->setCvsReceiptNo($params->get('CvsReceiptNo'));
		$this->setEdyReceiptNo($params->get('EdyReceiptNo'));
		$this->setEdyOrderNo($params->get('EdyOrderNo'));
		$this->setSuicaReceiptNo($params->get('SuicaReceiptNo'));
		$this->setSuicaOrderNo($params->get('SuicaOrderNo'));
		$this->setCustId($params->get('CustId'));
		$this->setBkCode($params->get('BkCode'));
		$this->setConfNo($params->get('ConfNo'));
		$this->setPaymentTerm($params->get('PaymentTerm'));
		$this->setEncryptReceiptNo($params->get('EncryptReceiptNo'));
		        
		$this->setWebMoneyManagementNo($params->get('WebMoneyManagementNo'));
		$this->setWebMoneySettleCode($params->get('WebMoneySettleCode'));
        
		$this->setAuPayInfoNo($params->get('AuPayInfoNo'));
		$this->setAuPayMethod($params->get('AuPayMethod'));
		$this->setAuCancelAmount($params->get('AuCancelAmount'));
		$this->setAuCancelTax($params->get('AuCancelTax'));

        $this->setDocomoSettlementCode($params->get('DocomoSettlementCode'));
        $this->setDocomoCancelAmount($params->get('DocomoCancelAmount'));
        $this->setDocomoCancelTax($params->get('DocomoCancelTax'));
	}

	/**
	 * オーダーID取得
	 * @return string オーダーID
	 */
	function getOrderId() {
		return $this->orderId;
	}
	
	/**
	 * ステータス取得
	 * @return string ステータス
	 */
	function getStatus(){
		return $this->status;
	}
	
	/**
	 * 処理日時取得
	 * @return string 処理日時
	 */
	function getProcessDate(){
		return $this->processDate;
	}

	/**
	 * 処理区分取得
	 * @return string 処理区分
	 */
	function getJobCd(){
		return $this->jobCd;
	}
	
	/**
	 * 取引ID取得
	 * @return string 取引ID
	 */
	function getAccessId(){
		return $this->accessId;
	}
	
	/**
	 * 取引パスワード取得
	 * @return strig 取引パスワード
	 */
	function getAccessPass(){
		return $this->accessPass;
	}
	
	/**
	 * 商品コード取得
	 * @return string 商品コード
	 */
	function getItemCode(){
		return $this->itemCode;
	}
	/**
	 * 通貨コード取得
	 * @return string 通貨コード
	 */
	function getCurrency() {
	    return $this->currency;
	}
	/**
	 * 利用金額取得
	 * @return integer 利用金額
	 */
	function getAmount(){
		return $this->amount;
	}
	
	/**
	 * 税送料取得
	 * @return integer 税送料
	 */
	function getTax(){
		return $this->tax;
	}
	
	/**
	 * サイトID取得
	 * @return string サイトID
	 */
	function getSiteId(){
		return $this->siteId;
	}
	
	/**
	 * 会員ID
	 * @return string 会員ID
	 */
	function getMemberId(){
		return $this->memberId;
	}
	
	/**
	 * カード番号取得
	 * @return string カード番号(下4桁表示、以外マスク)
	 */
	function getCardNo(){
		return $this->cardNo;
	}
	
	/**
	 * カード有効期限取得
	 * @return string カード有効期限
	 */
	function getExpire(){
		return $this->expire;
	}
	
	/**
	 * 支払い方法取得
	 * @return string 支払方法コード
	 */
	function getMethod() {
		return $this->method;
	}

	/**
	 * 支払回数取得
	 * @return integer 支払回数
	 */
	function getPayTimes() {
		return $this->payTimes;
	}

	/**
	 * 仕向先コード取得
	 * @return string 仕向先コード
	 */
	function getForward(){
		return $this->forward;
	}
	
	/**
	 * トランザクションID取得
	 * @return　string トランザクションID
	 */
	function getTranId(){
		return $this->transactionId;
	}
	
	/**
	 * 承認番号取得
	 * @return string 承認番号
	 */
	function getApprovalNo(){
		return $this->approve;
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
	 * 決済手段取得
	 * @return  　string $string  決済手段
	 */
	function getPayType(){
		return $this->payType ;
	}
	

	/**
	 * 支払先コンビニ会社コード 取得
	 * @return  　string $string  支払先コンビニ会社コード
	 */
	function getCvsCode(){
		return $this->cvsCode ;
	}
	/**
	 * 支払先確認番号 取得
	 * @return  　string $string  支払先確認番号
	 */
	function getCvsConfNo(){
		return $this->cvsConfNo ;
	}
	
	/**
	 * 支払先コンビニ受付番号 取得
	 * @return  　string $string  支払先コンビニ会受付番号
	 */
	function getCvsReceiptNo(){
		return $this->cvsReceiptNo ;
	}
	/**
	 * Edy受付番号 取得
	 * @return  　string $string  Edy受付番号
	 */
	function getEdyReceiptNo(){
		return $this->edyReceiptNo ;
	}
	/**
	 * Edy注文番号 取得
	 * @return  　string $string  Edy注文番号
	 */
	function getEdyOrderNo(){
		return $this->edyOrderNo ;
	}
	/**
	 * Suica受付番号 取得
	 * @return  　string $string  Suica受付番号
	 */
	function getSuicaReceiptNo(){
		return $this->suicaReceiptNo ;
	}
	/**
	 * Suica注文番号 取得
	 * @return  　string $string  Suica注文番号
	 */
	function getSuicaOrderNo(){
		return $this->suicaOrderNo ;
	}
	/**
	 * Pay-easyお客様番号  取得
	 * @return  　string $string  Pay-easyお客様番号
	 */
	function getCustId(){
		return $this->custId ;
	}
	/**
	 * Pay-easy収納機関番号  取得
	 * @return  　string $string  Pay-easy収納機関番号
	 */
	function getBkCode(){
		return $this->bkCode ;
	}
	/**
	 * Pay-easy確認番号  取得
	 * @return  　string $string  Pay-easy確認番号
	 */
	function getConfNo(){
		return $this->confNo ;
	}
	/**
	 * Pay-easy暗号化決済番号  取得
	 * @return  　string $string  Pay-easy暗号化決済番号
	 */
	function getEncryptReceiptNo(){
		return $this->encryptReceiptNo ;
	}
	/**
	 * 支払期限日時  取得
	 * @return  　string $string  支払期限日時
	 */
	function getPaymentTerm(){
		return $this->paymentTerm ;
	}
	/**
	 * WebMoney管理番号 取得
	 * @return  　string $string  WebMoney管理番号
	 */
	function getWebmoneyManagementNo(){
		return $this->webmoneyManagementNo;
	}
	/**
	 * WebMoney決済コード 取得
	 * @return  　string $string  WebMoney決済コード
	 */
	function getWebmoneySettleCode(){
		return $this->webmoneySettleCode;
	}

	/**
	 * auかんたん決済決済情報番号 取得
	 * @return  　string $string  auかんたん決済情報番号
	 */
	function getAuPayInfoNo(){
		return $this->auPayInfoNo;
	}

	/**
	 * auかんたん決済支払方法 取得
	 * @return  　string $string  auかんたん決済支払方法
	 */
	function getAuPayMethod(){
		return $this->auPayMethod;
	}

	/**
	 * auかんたん決済キャンセル金額 取得
	 * @return  　string $string  auかんたん決済キャンセル金額
	 */
	function getAuCancelAmount(){
		return $this->auCancelAmount;
	}

	/**
	 * auかんたん決済キャンセル税送料 取得
	 * @return  　string $string  auかんたん決済キャンセル税送料
	 */
	function getAuCancelTax(){
		return $this->auCancelTax;
	}

	
	/**
	 * オーダーID設定
	 * @param string $orderId オーダーID
	 */
	function setOrderId($orderId) {
		$this->orderId = $orderId;
	}
	
	/**
	 * ステータス設定
	 * @param string $status ステータス
	 */
	function setStatus($status){
		$this->status = $status;
	}
	
	/**
	 * 処理日時設定
	 * @param string $processDate 処理日時
	 */
	function setProcessDate($processDate){
		$this->processDate = $processDate;
	}

	/**
	 * 処理区分設定
	 * @param string $jobCd 処理区分
	 */
	function setJobCd($jobCd){
		$this->jobCd = $jobCd;
	}
	
	/**
	 * 取引ID設定
	 * @param string $accessId 取引ID
	 */
	function setAccessId($accessId){
		$this->accessId = $accessId;
	}
	
	/**
	 * 取引パスワード設定
	 * @param string $accessPass 取引パスワード
	 */
	function setAccessPass($accessPass){
		$this->accessPass = $accessPass;
	}
	
	/**
	 * 商品コード設定
	 * @param string $itemCode 商品コード
	 */
	function setItemCode( $itemCode){
		$this->itemCode = $itemCode;
	}
	/**
	 * 通貨コード設定
	 * @param string 
	 */
	function setCurrency( $currency ) {
	    $this->currency = $currency;
	}
	
	/**
	 * 利用金額設定
	 * @param string $amount 利用金額
	 */
	function setAmount($amount){
		$this->amount = $amount;
	}
	
	/**
	 * 税送料設定
	 * @param string $tax 税送料
	 */
	function setTax($tax){
		$this->tax = $tax;
	}
	
	/**
	 * サイトID設定
	 * @param string $siteId サイトID
	 */
	function setSiteId($siteId){
		$this->siteId = $siteId;
	}
	
	/**
	 * 会員ID設定
	 * @param string $memberId 会員ID
	 */
	function setMemberId($memberId){
		$this->memberId = $memberId;
	}
	
	/**
	 * カード番号設定
	 * @param string $cardNo カード番号(下4桁表示、以外マスク)
	 */
	function setCardNo($cardNo){
		$this->cardNo = $cardNo;
	}
	
	/**
	 * カード有効期限設定
	 * @param string $expire カード有効期限
	 */
	function setExpire( $expire ){
		$this->expire = $expire;
	}
	
	/**
	 * 支払い方法設定
	 * @param string $method 支払方法コード
	 */
	function setMethod($method) {
		$this->method = $method;
	}

	/**
	 * 支払回数設定
	 * @param string $payTimes 支払回数
	 */
	function setPayTimes( $payTimes ) {
		$this->payTimes = $payTimes;
	}
	
	/**
	 * 仕向先コード設定
	 * @param string $forward 仕向け先コード
	 */
	function setForward($forward){
		$this->forward = $forward;
	}

	/**
	 * トランザクションID設定
	 * @param string $transactionId トランザクションID
	 */
	function setTranId($transactionId){
		$this->transactionId = $transactionId;
	}
	
	/**
	 * 承認番号設定
	 * @param string $approve 承認番号
	 */
	function setApprovalNo($approve){
		$this->approve = $approve;
	}
	
	/**
	 * 加盟店自由項目1設定
	 * @param string $clientField1 加盟店自由項目1
	 */
	function setClientField1($clientField1) {
		$this->clientField1 = $clientField1;
	}

	/**
	 * 加盟店自由項目2設定
	 * @param string $clientField2 加盟店自由項目2
	 */
	function setClientField2($clientField2) {
		$this->clientField2 = $clientField2;
	}

	/**
	 * 加盟店自由項目3設定
	 * @param string $clientField3 加盟店自由項目3
	 */
	function setClientField3($clientField3) {
		$this->clientField3 = $clientField3;
	}
	
	/**
	 * 決済手段設定
	 * @param  　string $string  決済手段
	 */
	function setPayType($string){
		$this->payType = $string;
	}
	
	/**
	 * 支払先コンビニ会社コード 設定
	 * @param  　string $string  支払先コンビニ会社コー
	 */
	function setCvsCode($string){
		$this->cvsCode = $string;
	}
	/**
	 * コンビニ確認番号 設定
	 * @param  　string $string  コンビニ確認番号
	 */
	function setCvsConfNo($string){
		$this->cvsConfNo = $string;
	}
	/**
	 * 支払先コンビニ受付番号 設定
	 * @param  　string $string  支払先コンビニ受付番号
	 */
	function setCvsReceiptNo($string){
		$this->cvsReceiptNo = $string;
	}
	/**
	 * Edy受付番号 設定
	 * @param  　string $string  Edy受付番号
	 */
	function setEdyReceiptNo($string){
		$this->edyReceiptNo = $string;
	}
	/**
	 * Edy注文番号   設定
	 * @param  　string $string  Edy注文番号
	 */
	function setEdyOrderNo($string){
		$this->edyOrderNo = $string;
	}
	/**
	 * Suica受付番号 設定
	 * @param  　string $string  Suica受付番号
	 */
	function setSuicaReceiptNo($string){
		$this->suicaReceiptNo = $string;
	}
	/**
	 * Suica注文番号 設定
	 * @param  　string $string  Suica注文番号
	 */
	function setSuicaOrderNo($string){
		$this->suicaOrderNo = $string;
	}
	/**
	 * Pay-easyお客様番号 設定
	 * @param  　string $string  Pay-easyお客様番号
	 */
	function setCustId($string){
		$this->custId = $string;
	}
	/**
	 * Pay-easy収納機関番号 設定
	 * @param  　string $string  Pay-easy収納機関番号
	 */
	function setBkCode($string){
		$this->bkCode = $string;
	}
	/**
	 * Pay-easy確認番号 設定
	 * @param  　string $string  Pay-easy確認番号
	 */
	function setConfNo($string){
		$this->confNo = $string;
	}
	/**
	 * 支払期限日時 設定
	 * @param  　string $string  支払期限日時
	 */
	function setPaymentTerm($string){
		$this->paymentTerm = $string;
	}
	/**
	 * 暗号化決済番号 設定
	 * @param  　string $string  暗号化決済番号
	 */
	function setEncryptReceiptNo($string){
		$this->encryptReceiptNo = $string;
	}
	/**
	 * WebMoney管理番号 設定
	 * @param  　string $string  WebMoney管理番号
	 */
	function setWebmoneyManagementNo($string){
		$this->webmoneyManagementNo = $string;
	}
	/**
	 * WebMoney決済コード 設定
	 * @param  　string $string  WebMoney決済コード
	 */
	function setWebmoneySettleCode($string){
		$this->webmoneySettleCode = $string;
	}

	/**
	 * auかんたん決済決済情報番号 設定
	 * @param  　string $string  auかんたん決済情報番号
	 */
	function setAuPayInfoNo($string){
		$this->auPayInfoNo = $string;
	}

	/**
	 * auかんたん決済支払方法 設定
	 * @param  　string $string  auかんたん決済支払方法
	 */
	function setAuPayMethod($string){
		$this->auPayMethod = $string;
	}

	/**
	 * auかんたん決済キャンセル金額 設定
	 * @param  　string $string  auかんたん決済キャンセル金額
	 */
	function setAuCancelAmount($string){
		$this->auCancelAmount = $string;
	}

	/**
	 * auかんたん決済キャンセル税送料 設定
	 * @param  　string $string  auかんたん決済キャンセル税送料
	 */
	function setAuCancelTax($string){
		$this->auCancelTax = $string;
	}

	/**
	 * ドコモ決済番号取得
	 * @return string ドコモ決済番号
	 */
	function getDocomoSettlementCode() {
		return $this->docomoSettlementCode;
	}

	/**
	 * ドコモキャンセル金額取得
	 * @return integer ドコモキャンセル金額
	 */
	function getDocomoCancelAmount() {
		return $this->docomoCancelAmount;
	}

	/**
	 * ドコモキャンセル税送料取得
	 * @return integer ドコモキャンセル税送料
	 */
	function getDocomoCancelTax() {
		return $this->docomoCancelTax;
	}

	/**
	 * ドコモ決済番号設定
	 *
	 * @param string $docomoSettlementCode
	 */
	function setDocomoSettlementCode($docomoSettlementCode) {
		$this->docomoSettlementCode = $docomoSettlementCode;
	}

	/**
	 * ドコモキャンセル金額設定
	 *
	 * @param integer $docomoCancelAmount
	 */
	function setDocomoCancelAmount($docomoCancelAmount) {
		$this->docomoCancelAmount = $docomoCancelAmount;
	}

	/**
	 * ドコモキャンセル税送料設定
	 *
	 * @param integer $docomoCancelTax
	 */
	function setDocomoCancelTax($docomoCancelTax) {
		$this->docomoCancelTax = $docomoCancelTax;
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
	    $str .= 'Status=' . $this->getStatus();
        $str .= '&';
	    $str .= 'ProcessDate=' . $this->getProcessDate();
        $str .= '&';
	    $str .= 'JobCd=' . $this->getJobCd();
        $str .= '&';
	    $str .= 'AccessID=' . $this->getAccessId();
        $str .= '&';
	    $str .= 'AccessPass=' . $this->getAccessPass();
        $str .= '&';
	    $str .= 'ItemCode=' . $this->getItemCode();
        $str .= '&';
	    $str .= 'Amount=' . $this->getAmount();
        $str .= '&';
	    $str .= 'Tax=' . $this->getTax();
        $str .= '&';
	    $str .= 'SiteID=' . $this->getSiteId();
        $str .= '&';
	    $str .= 'MemberID=' . $this->getMemberId();
        $str .= '&';
	    $str .= 'CardNo=' . $this->getCardNo();
        $str .= '&';
	    $str .= 'Expire=' . $this->getExpire();
        $str .= '&';
        $str .= 'Method=' . $this->getMethod();
        $str .= '&';        
        $str .= 'PayTimes=' . $this->getPayTimes();
        $str .= '&';        
        $str .= 'Forward=' . $this->getForward();
        $str .= '&';
        $str .= 'TranID=' . $this->getTranId();
        $str .= '&';
        $str .= 'Approve=' . $this->getApprovalNo();
        $str .= '&';
        $str .= 'ClientField1=' . $this->getClientField1();
        $str .= '&';
        $str .= 'ClientField2=' . $this->getClientField2();
        $str .= '&';
        $str .= 'ClientField3=' . $this->getClientField3();
		$str .= '&';
        $str .= 'PayType=' . $this->getPayType();
		$str .= '&';
        $str .= 'CvsCode=' . $this->getCvsCode();
		$str .= '&';
        $str .= 'CvsConfNo=' . $this->getCvsConfNo();
		$str .= '&';
        $str .= 'CvsReceiptNo=' . $this->getCvsReceiptNo();
		$str .= '&';
        $str .= 'EdyReceiptNo=' . $this->getEdyReceiptNo();
		$str .= '&';
        $str .= 'EdyOrderNo=' . $this->getEdyOrderNo();
		$str .= '&';
        $str .= 'SuicaReceiptNo=' . $this->getSuicaReceiptNo();
		$str .= '&';
        $str .= 'SuicaOrderNo=' . $this->getSuicaOrderNo();
		$str .= '&';
        $str .= 'CustId=' . $this->getCustId();
		$str .= '&';
        $str .= 'BkCode=' . $this->getBkCode();
		$str .= '&';
        $str .= 'ConfNo=' . $this->getConfNo();
		$str .= '&';
        $str .= 'PaymentTerm=' . $this->getPaymentTerm();
        $str .= '&';
        $str .= 'EncryptReceiptNo=' . $this->getEncryptReceiptNo();
        $str .= '&';
        $str .= 'WebMoneyManagementNo=' . $this->getWebMoneyManagementNo();
        $str .= '&';
        $str .= 'WebMoneySettleCode=' . $this->getWebMoneySettleCode();
        $str .= '&';
		$str .= 'AuPayInfoNo=' . $this->getAuPayInfoNo();
        $str .= '&';
		$str .= 'AuPayMethod=' . $this->getAuPayMethod();
        $str .= '&';
		$str .= 'AuCancelAmount=' . $this->getAuCancelAmount();
        $str .= '&';
		$str .= 'AuCancelTax=' . $this->getAuCancelTax();
	    $str .= '&';
	    $str .= 'DocomoSettlementCode=' . $this->encodeStr($this->getDocomoSettlementCode());
	    $str .= '&';
	    $str .= 'DocomoCancelAmount=' . $this->encodeStr($this->getDocomoCancelAmount());
	    $str .= '&';
	    $str .= 'DocomoCancelTax=' . $this->encodeStr($this->getDocomoCancelTax());
		        
        if ($this->isErrorOccurred()) {
            // エラー文字列を連結して返す
            $errString = parent::toString();
            $str .= '&' . $errString;
        }
        
        return $str;
	}

}
?>
