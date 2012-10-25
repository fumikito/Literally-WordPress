<?php
require_once 'com/gmo_pg/client/common/ParamParser.php';
/**
 * @abstract 
 * <b>出力パラメータ 基底クラス</b>
 * 
 * 
 * @package com.gmo_pg.client
 * @subpackage output
 * @see outputPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 01-01-2008 00:00:00
 */
class BaseOutput {

	/**
	 * @var array エラー情報リスト ErrHolderオブジェクトの配列
	 */
	var $errList;

	/**
	 * コンストラクタ
	 *
	 * @param IgnoreCaseMap $params    入力パラメータ
	 */
	function BaseOutput($params = null) {
		$this->__construct($params);
	}

	/**
	 * コンストラクタ
	 *
	 * @param IgnoreCaseMap $params    入力パラメータ
	 */
	function __construct($params = null) {
		$this->errList = array();
		if (is_null($params)) {
			return;
		}

		// エラーパラメータを解析してリストに保持
		$errCode = $params->containsKey('errCode') ? $params->get('errCode') : null;
		$errInfo = $params->containsKey('errInfo') ? $params->get('errInfo') : null;
		if ($errCode && $errInfo) {
			$errCode = preg_replace('/\|$/', '', $errCode);
			$errInfo = preg_replace('/\|$/', '', $errInfo);

			$parser = new ParamParser();
			$this->errList = $parser->errParse($errCode, $errInfo);
		}
	}

	/**
	 * エラー情報リスト取得
	 * @return array エラーリスト
	 */
	function &getErrList() {
		return $this->errList;
	}

	/**
	 * エラー情報リストを設定
	 *
	 * @param array $errList
	 */
	function setErrList(&$errList) {
		$this->errList = $errList;
	}

	/**
	 * エラー発生判定
	 * @return boolean エラー発生フラグ(true=エラーあり)
	 */
	function isErrorOccurred() {
		return 0 != count($this->errList);
	}

	/**
	 * 文字列表現
	 * <p>
	 *  現在の各パラメータを、パラメータ名=値&パラメータ名=値の形式で取得します。
	 * </p>
	 * @return string 出力パラメータの文字列表現
	 */
	function toString() {
		$errCodeBuffer = 'errCode=';
		$errInfoBuffer = 'errInfo=';

		// 各エラーコードとエラー詳細を連結した文字列を生成
		foreach ($this->errList as $errHolder) {
			$errCodeBuffer .= $errHolder->getErrCode() . '|';
			$errInfoBuffer .= $errHolder->getErrInfo() . '|';
		}

		return $errCodeBuffer . '&' . $errInfoBuffer;
	}

}
?>