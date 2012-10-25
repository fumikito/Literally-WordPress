<?php
require_once ('com/gmo_pg/client/output/TdVerifyOutput.php');
require_once ('com/gmo_pg/client/tran/BaseTran.php');
/**
 * <b>3D認証後決済　実行クラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage tran
 * @see tranPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 01-01-2008 00:00:00
 */
class TdVerify extends BaseTran {

	/**
	 * コンストラクタ
	 */
	function TdVerify() {
	    parent::__construct();
	}

	/**
	 * 3D認証決済を実行する
	 *
	 * @param TdVerifyInput $input    入力パラメータ
	 * @return TdVerityOutput 出力パラメータ
	 */
	function exec(&$input) {
        // プロトコル呼び出し・結果取得
        $resultMap = $this->callProtocol($input->toString());
        
        // 戻り値がnullの場合、nullを戻す
        if (is_null($resultMap)) {
		    return null;
        }
				
		// TdVerifyOutput作成
		return new TdVerifyOutput($resultMap);
	}
}
?>