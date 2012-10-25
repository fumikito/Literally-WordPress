<?php
require_once ('com/gmo_pg/client/common/Cryptgram.php');
require_once ('com/gmo_pg/client/common/GPayException.php');
require_once ('com/gmo_pg/client/output/DocomoSalesOutput.php');
require_once ('com/gmo_pg/client/tran/BaseTran.php');
/**
 * <b>ドコモケータイ払い売上確定　実行クラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage tran
 * @see tranPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 2012/06/14
 */
class DocomoSales extends BaseTran {

	/**
	 * コンストラクタ
	 */
	function DocomoSales() {
	    parent::__construct();
	}
	
	/**
	 * 売上確定を実行する
	 *
	 * @param  DocomoSalesInput $input  入力パラメータ
	 * @return DocomoSalesOutput $output 出力パラメータ
	 * @exception GPayException
	 */
	function exec(&$input) {
	    
        // 接続しプロトコル呼び出し・結果取得
        $resultMap = $this->callProtocol($input->toString());
	    // 戻り値がnullの場合、nullを戻す
        if (is_null($resultMap)) {
		    return null;
        }
	    
        // DocomoSalesOutput作成し、戻す
	    return new DocomoSalesOutput($resultMap);
	}
}
?>
