<?php
require_once ('com/gmo_pg/client/output/ExecTranWebmoneyOutput.php');
require_once ('com/gmo_pg/client/tran/BaseTran.php');

/**
 * <b>Webmoney決済実行　実行クラス</b>
 *
 * @package com.gmo_pg.client
 * @subpackage tran
 * @see tranPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 04-08-2010
 */
class ExecTranWebmoney extends BaseTran {

	/**
	 * コンストラクタ
	 */
	function ExecTranWebmoney() {
		parent::__construct();
	}

	/**
	 * 決済を実行する
	 *
	 * @param  ExecTranWebmoneyInput $input    入力パラメータ
	 * @return ExecTranWebmoneyOuput 出力パラメータ
	 */
	function exec(&$input) {

		// プロトコル呼び出し・結果取得
		$resultMap = $this->callProtocol($input->toString());

		// 戻り値がnullの場合、nullを戻す
		if (is_null($resultMap)) {
			return null;
		}

		// ExecTranPayEasyOutputを作成し、戻す
		return new ExecTranWebmoneyOutput($resultMap);
	}
}
