<?php
require_once ('com/gmo_pg/client/output/EntryTranPaypalOutput.php');
require_once ('com/gmo_pg/client/tran/BaseTran.php');

/**
 * <b>Paypal取引登録　実行クラス</b>
 *
 * @package com.gmo_pg.client
 * @subpackage tran
 * @see tranPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 12-22-2009 00:00:00
 */
class EntryTranPaypal extends BaseTran {

	/**
	 * コンストラクタ
	 */
	function EntryTranPaypal() {
		parent::__construct();
	}

	/**
	 * プロトコルタイプのURLから戻り値を読み出す。
	 * 文字列を復号化して戻します。
	 *
	 * @param  string $retData プロトコルタイプからの取得文字列
	 * @return string 復号化済みの文字列 
	 */
	function recvData($retData) {
		// データの送受信に失敗しているときは戻る
		if (!$retData) {
			return null;
		}

		// 取得データの置き換え処理
		$retData = preg_replace('/^ReturnData=/', '', $retData);
		// rtrim処理(strvalで型をstringに固定)
		// ※rtrimの２つめの引数はPHP4.1.0以降で認識します
		$retData = strval(rtrim($retData, "\r\n"));

		return $retData;
	}

	/**
	 * Paypal取引登録を実行する
	 *
	 * @param  EntryTranPaypalInput $input  入力パラメータ
	 * @return EntryTranPaypalOutput $output 出力パラメータ
	 * @exception GPayException
	 */
	function exec($input) {
		$resultMap = $this->callProtocol($input->toString());

		// 戻り値がnullの場合、nullを戻す
		if(is_null($resultMap)) {
			return null;
		}

		//EntryTranPaypalOutputを作成し、戻す
		return new EntryTranPaypalOutput($resultMap);

	}

}