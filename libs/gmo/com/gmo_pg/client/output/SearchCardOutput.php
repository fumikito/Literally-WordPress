<?php
require_once ('com/gmo_pg/client/output/BaseOutput.php');
/**
 * <b>カード照会　出力パラメータクラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage output
 * @see outputPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 01-01-2008 00:00:00
 */
class SearchCardOutput extends BaseOutput {

	/**
	 * @var array 登録カード連番配列 カード登録連番を要素に持つ一次元配列
	 *
	 */
	var $cardSeq;

	/**
	 * @var array 洗替・継続課金フラグ配列  洗替・継続課金フラグを要素にもつ一次元配列 '1'=洗替・継続課金時に利用されるカード
	 * 
	 */
	var $defaultFlag;
	
	/**
	 * @var array  カード会社略称配列 カード会社略称を要素にもつ一次元配列
	 */
	var $cardName;
	
	/**
	 * @var array カード番号（下四桁表示、以上マスク） カード番号を要素にもつ一次元配列
	 */
	var $cardNo;

	/**
	 * @var array 有効期限 有効期限を要素にもつ一次元配列
	 */
	var $expire;
	
	/**
	 * @var array カード名義人 カード名義人を要素にもつ一次元配列
	 */
	var $holderName;
	
	/**
	 * @var array  削除フラグ  削除フラグを配列にもつ一次元配列 '1'=削除カード
	 */
	var $deleteFlag;

	/**
	 * @var array カードの配列。カード情報の連想配列が繰り返される、二次元配列。例：
	 * 
	 *	<code>
	 *	$cardList =
	 * 		array(
	 *			array(
	 *				'CardSeq' =>	1 ,
	 *				'DefaultFlag'	=>	'1',
	 * 				'CardName'	=>	'SUMITOMO'
	 *	 			'CardNo'	=>	'************1111',
	 * 				'Expire'	=>	'1308',
	 * 				'HolderName'	=>	'MEIGI NIN',
	 * 				'DeleteFlag'	=>	'0'
	 *			),
	 *			array(
	 *				'CardSeq' =>	2 ,
	 *				'DefaultFlag'	=>	'0',
	 * 				'CardName'	=>	'DINERS'
	 * 				'CardNo'	=>	'************2222',
	 *	 			'Expire'	=>	'0812',
	 * 				'HolderName'	=>	'MEIGI NIN',
	 * 				'DeleteFlag'	=>	'0'
	 *			),
	 *  	)
	 * </code>
	 * 
	 */
	var $cardList = null;
	
	
	
	/**
	 * コンストラクタ
	 *
	 * @param IgnoreCaseMap $params  出力パラメータ
	 */
	function SearchCardOutput($params = null) {
		
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
        //カードは複数返るので、全てマップに展開
        $cardArray = null;
        $tmp =  $params->get('CardSeq');
        $cardSeq = $params->get('CardSeq');
        $default = $params->get('DefaultFlag');
        $cardName	=	$params->get('CardName');
        $cardNo		=	$params->get('CardNo');
        $expire		=	$params->get('Expire');
        $holderName	=	$params->get('HolderName');
        $delete		=	$params->get('DeleteFlag');
        
        if( is_null( $cardSeq ) ){
        	return;
        }
        //項目ごとに配列として設定
        if( !is_null( $cardSeq ) ){
        	$this->setCardSeq(	explode('|'	,$cardSeq ) );
        }
        if( !is_null( $default ) ){
        	$this->setDefaultFlag(	explode('|'	,$default ) );
        }
        if( !is_null( $cardName ) ){
        	$this->setCardName(explode('|',$cardName ) );
        }
        if( !is_null( $cardNo ) ){
        	$this->setCardNo(explode('|',$cardNo ) );
        }
        if( !is_null( $expire ) ){
        	$this->setExpire(explode('|',$expire ) );
        }
        if( !is_null( $holderName ) ){
        	$this->setHolderName(explode('|',$holderName ) );
        }
        if( !is_null( $delete ) ){
        	$this->setDeleteFlag(explode('|',$delete ) );
        }
        //カード配列を作成
        $cardList = null;
        $count = count( $this->cardSeq );
        for( $i = 0 ; $i < $count; $i++ ){
        	$tmp = null;
        	$tmp['CardSeq']		=	$this->cardSeq[$i];
        	$tmp['DefaultFlag']	=	$this->defaultFlag[$i];
        	$tmp['CardName']	=	$this->cardName[$i];
        	$tmp['CardNo']		=	$this->cardNo[$i];
        	$tmp['Expire']		=	$this->expire[$i];
        	$tmp['HolderName']	=	$this->holderName[$i];
        	$tmp['DeleteFlag']	=	$this->deleteFlag[$i];
        	$cardList[]	=	$tmp;
        }
        $this->cardList = $cardList;
	}

	/**
	 * カード登録連番配列の配列取得
	 * @return array カード登録連番
	 */
	function getCardSeq() {
		return $this->cardSeq;
	}

	/**
	 * 洗替・継続課金対象フラグの配列取得
	 * @return array 洗替・継続課金対象フラグ
	 */
	function getDefaultFlag(){
		return $this->defaultFlag;
	}
	
	/**
	 * カード会社略称の配列取得
	 * @return array カード会社略称
	 */
	function getCardName(){
		return $this->cardName;
	}
	
	/**
	 * カード番号の配列取得
	 * @return array カード番号
	 */
	function getCardNo() {
		return $this->cardNo;
	}
	
	/**
	 * 有効期限配列取得
	 * @return array 有効期限(YYMM)
	 */
	function getExpire() {
		return $this->expire;
	}
	
	/**
	 * カード名義人名の配列取得
	 * @return array カード名義人
	 */
	function getHolderName() {
		return $this->holderName;
	}
	
	/**
	 * 削除フラグ配列取得
	 * @return array 削除フラグ
	 */
	function getDeleteFlag() {
		return $this->deleteFlag;
	}
	
	/**
	 * カードリスト取得
	 * <p>
	 * 	　$cardListを返します
	 * </p>
	 * @return array カードリスト
	 */
	function getCardList() {
		return $this->cardList;
	}
	
	/**
	 * カード登録連番設定
	 * @param array $cardSeq カード登録連番
	 */
	function setCardSeq( $cardSeq) {
		$this->cardSeq =$cardSeq ;
	}

	/**
	 * 洗替・継続課金対象フラグ設定
	 *
	 * @param array $defaultFlag 洗替・継続課金対象フラグ
	 */
	function setDefaultFlag($defaultFlag) {
		$this->defaultFlag = $defaultFlag;
	}
	
	/**
	 * カード会社略称設定
	 *
	 * @param array $cardName カード会社略称
	 */
	function setCardName($cardName) {
		$this->cardName = $cardName;
	}
	
	/**
	 * カード番号設定
	 * @param array $cardNo カード番号
	 */
	function setCardNo( $cardNo) {
		$this->cardNo = $cardNo;
	}

	/**
	 * 有効期限設定
	 *
	 * @param array $expire 有効期限(YYMM)
	 */
	function setExpire($expire) {
		$this->expire = $expire;
	}

	/**
	 * カード名義人設定
	 *
	 * @param array $holderName カード名義人
	 */
	function setHolderName($holderName) {
		$this->holderName = $holderName;
	}
	
	/**
	 * 削除フラグ設定
	 * @param array $deleteFlag 削除フラグ
	 */
	function setDeleteFlag($deleteFlag) {
		$this->deleteFlag = $deleteFlag;
	}

	/**
	 * カードリスト設定
	 * @param array $cardList カードリスト設定
	 */
	function setCardList($cardList) {
		$this->cardList = $cardList;
	}
	
	/**
	 * 文字列表現
	 * <p>
	 *  現在の各パラメータを、パラメータ名=値&パラメータ名=値の形式で取得します。
	 * </p>
	 * @return string 出力パラメータの文字列表現
	 */
	function toString() {
	    $str  = 'CardSeq='		.	(is_null($this->cardSeq)? '' : implode('|',$this->cardSeq));
	    $str .= '&';
	    $str .= 'DefaultFlag='	.	 (is_null($this->defaultFlag)? '' : implode('|',$this->defaultFlag));
	    $str .= '&';
		$str .= 'CardName='		.	(is_null($this->cardName)?'':implode('|',$this->cardName));
	    $str .= '&';
	    $str .= 'CardNo='		.	(is_null($this->cardNo)?'':implode('|',$this->cardNo));
	    $str .= '&';
		$str .= 'Expire='		.	(is_null($this->expire)?'':implode('|',$this->expire));
	    $str .= '&';
		$str .= 'HolderName='	.	(is_null($this->holderName)?'':implode('|' ,$this->holderName));
	    $str .= '&';
	    $str .= 'DeleteFlag='	.	(is_null($this->deleteFlag)?'':implode('|',$this->deleteFlag));
	    
	    if ($this->isErrorOccurred()) {
            // エラー文字列を連結して返す
            $errString = parent::toString();
            $str .= '&' . $errString;
        }	    
	    
        return $str;
	}

}
?>