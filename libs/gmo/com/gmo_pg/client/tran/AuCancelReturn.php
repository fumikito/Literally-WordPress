<?php
require_once ('com/gmo_pg/client/common/Cryptgram.php');
require_once ('com/gmo_pg/client/common/GPayException.php');
require_once ('com/gmo_pg/client/output/AuCancelReturnOutput.php');
require_once ('com/gmo_pg/client/tran/BaseTran.php');
/**
 * <b>auかんたん決済決済取消　実行クラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage tran
 * @see tranPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 2012/02/15
 */
class AuCancelReturn extends BaseTran {

	/**
	 * コンストラクタ
	 */
	function AuCancelReturn() {
	    parent::__construct();
	}
	
	/**
	 * 決済取消を実行する
	 *
	 * @param  AuCancelReturnInput $input  入力パラメータ
	 * @return AuCancelReturnOutput $output 出力パラメータ
	 * @exception GPayException
	 */
	function exec(&$input) {
	    
        // 接続しプロトコル呼び出し・結果取得
        $resultMap = $this->callProtocol($input->toString());
	    // 戻り値がnullの場合、nullを戻す
        if (is_null($resultMap)) {
		    return null;
        }
	    
        // AuCancelReturnOutput作成し、戻す
	    return new AuCancelReturnOutput($resultMap);
	}
}
?>