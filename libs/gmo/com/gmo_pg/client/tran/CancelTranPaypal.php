<?php
require_once ('com/gmo_pg/client/output/CancelTranPaypalOutput.php');
require_once ('com/gmo_pg/client/tran/BaseTran.php');

/**
 * <b>Paypal払い戻し　実行クラス</b>
 *
 * @package com.gmo_pg.client
 * @subpackage tran
 * @see tranPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 12-22-2009 00:00:00
 */
class CancelTranPaypal extends BaseTran {

	/**
	 * コンストラクタ
	 */
	function CancelTranPaypal() {
		parent::__construct();
	}

	/**
	 * 払い戻しを実行する
	 *
	 * @param CancelTranPaypalInput $input    入力パラメータ
	 * @return CancelTranPaypalOutput 出力パラメータ
	 */
	function exec(&$input) {
		// プロトコル呼び出し・結果取得
		$resultMap = $this->callProtocol($input->toString());

		// 戻り値がnullの場合、nullを戻す
		if (is_null($resultMap)) {
			return null;
		}

		// CancelTranPaypalOutput作成
		return new CancelTranPaypalOutput($resultMap);
	}
}