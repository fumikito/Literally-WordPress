<?php
/**
 * <b>リダイレクトページ生成用パラメータホルダー</b>
 * 
 * リダイレクトページを生成するときに、RedirectUtilに渡すパラメータを保持するクラス
 * 
 * @package com.gmo_pg.client
 * @subpackage input
 * @see inputPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 01-01-2008 00:00:00
 */
class AcsParam {

	/**
	 * @var string リダイレクト先AcsURL
	 */
	var $acsUrl;

	/**
	 * @var string 3Dセキュア認証要求電文
	 */
	var $paReq;

	/**
	 * @var string 認証結果受け取りURL
	 */
	var $termUrl;

	/**
	 * @var string 取引データ
	 */
	var $md;

	/**
	 * ACS（発行元カード会社）URL取得
	 * @return string AcsURL
	 */
	function getAcsUrl() {
		return $this->acsUrl;
	}

	/**
	 * 取引ID取得
	 * @return string 取引データ
	 */
	function getMd() {
		return $this->md;
	}

	/**
	 * 3Dセキュア認証要求電文取得
	 * @return string 3Dセキュア認証要求電文
	 */
	function getPaReq() {
		return $this->paReq;
	}

	/**
	 * 結果受取用URL取得
	 * @return string 結果受け取りURL
	 */
	function getTermUrl() {
		return $this->termUrl;
	}

	/**
	 * ACS（発行元カード会社）URL設定
	 *
	 * <p>
	 *  ExecTranOutputまたはEntryExecTranOutputの、getAcsURL()の戻り値を設定してください。
	 * </p>
	 * 
	 * @param string $acsUrl
	 */
	function setAcsUrl($acsUrl) {
		$this->acsUrl = $acsUrl;
	}

	/**
	 * 取引ID設定
	 * 
	 * @param string $md 取引ID
	 */
	function setMd($md) {
		$this->md = $md;
	}
	

	/**
	 * 3Dセキュア認証要求電文設定
	 * 
	 * <p>
	 *   ExecTranOutputまたはEntryExecTranOutputの、getPaReq()の戻り値を設定してください。
	 * </p>
	 *
	 * @param string $paReq 3Dセキュア認証要求電文
	 */
	function setPaReq($paReq) {
		$this->paReq = $paReq;
	}
	

	/**
	 * 結果受取用URLを設定
	 *
	 * <p>
	 *   3D認証結果を受け取る、加盟店様システムのURLを、http(s)から完全修飾で設定してください。
	 * </p>
	 * @param string $termUrl 結果受け取りURL
	 */
	function setTermUrl($termUrl) {
		$this->termUrl = $termUrl;
	}

}
?>