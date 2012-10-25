<?php
require_once ('com/gmo_pg/client/output/BaseOutput.php');
/**
 * <b>取引後カード登録　出力パラメータクラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage output
 * @see outputPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 01-01-2008 00:00:00
 */
class TradedCardOutput extends BaseOutput {

	/**
	 * @var integer 登録カード連番
	 *
	 */
	var $cardSeq;

	/**
	 * @var string カード番号（下四桁表示、以上マスク）
	 */
	var $cardNo;

	/**
	 * @var string 仕向先コード
	 *
	 */
	var $forward;
	
	/**
	 * コンストラクタ
	 *
	 * @param IgnoreCaseMap $params  出力パラメータ
	 */
	function TradedCardOutput($params = null) {
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
        $this->setCardSeq($params->get('CardSeq'));
        $this->setCardNo($params->get('CardNo'));
        $this->setForward($params->get('Forward'));
	}

	/**
	 * カード登録連番取得
	 * @return integer カード登録連番
	 */
	function getCardSeq() {
		return $this->cardSeq;
	}

	/**
	 * カード番号取得
	 * @return string カード番号
	 */
	function getCardNo() {
		return $this->cardNo;
	}

	/**
	 * 仕向先コード取得
	 * @return string 仕向先コード
	 */
	function getForward() {
		return $this->forward;
	}

	/**
	 * カード登録連番設定
	 * @param integer $cardSeq カード登録連番
	 */
	function setCardSeq( $cardSeq) {
		$this->cardSeq =$cardSeq ;
	}

	/**
	 * カード番号設定
	 * @param string $cardNo カード番号
	 */
	function setCardNo( $cardNo) {
		$this->cardNo = $cardNo;
	}

	/**
	 * 仕向先コード取得
	 * @param string $forward 仕向先コード
	 */
	function setForward($forward) {
		$this->forward = $forward;
	}
	
	/**
	 * 文字列表現
	 * <p>
	 *  現在の各パラメータを、パラメータ名=値&パラメータ名=値の形式で取得します。
	 * </p>
	 * @return string 出力パラメータの文字列表現
	 */
	function toString() {
	    $str  = 'CardSeq=' . $this->getCardSeq();
	    $str .= '&';
	    $str .= 'CardNo=' . $this->getCardNo();
	    $str .= '&';
	    $str .= 'Forward=' . $this->getForward();
	    
	    if ($this->isErrorOccurred()) {
            // エラー文字列を連結して返す
            $errString = parent::toString();
            $str .= '&' . $errString;
        }	    
	    
        return $str;
	}

}
?>