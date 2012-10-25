<?php
require_once ('com/gmo_pg/client/common/Cryptgram.php');
require_once ('com/gmo_pg/client/common/GPayException.php');
require_once ('com/gmo_pg/client/output/DocomoCancelReturnOutput.php');
require_once ('com/gmo_pg/client/tran/BaseTran.php');
/**
 * <b>ドコモケータイ払い決済取消　実行クラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage tran
 * @see tranPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 2012/06/14
 */
class DocomoCancelReturn extends BaseTran {

	/**
	 * コンストラクタ
	 */
	function DocomoCancelReturn() {
	    parent::__construct();
	}
	
	/**
	 * 決済取消を実行する
	 *
	 * @param  DocomoCancelReturnInput $input  入力パラメータ
	 * @return DocomoCancelReturnOutput $output 出力パラメータ
	 * @exception GPayException
	 */
	function exec(&$input) {
	    
        // 接続しプロトコル呼び出し・結果取得
        $resultMap = $this->callProtocol($input->toString());
	    // 戻り値がnullの場合、nullを戻す
        if (is_null($resultMap)) {
		    return null;
        }
	    
        // DocomoCancelReturnOutput作成し、戻す
	    return new DocomoCancelReturnOutput($resultMap);
	}
}
?>
