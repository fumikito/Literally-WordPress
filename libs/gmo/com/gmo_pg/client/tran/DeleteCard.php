<?php
require_once ('com/gmo_pg/client/output/DeleteCardOutput.php');
require_once ('com/gmo_pg/client/tran/BaseTran.php');
/**
 * <b>カード削除 実行クラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage tran
 * @see tranPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 01-01-2008 00:00:00
 */
class DeleteCard extends BaseTran {

	/**
	 * コンストラクタ
	 */
	function DeleteCard() {
	    parent::__construct();
	}

	/**
	 * カードを削除する
	 *
	 * @param  DeleteCardInput $input    入力パラメータ
	 * @return DeleteCardOutput 出力パラメータ
	 */
	function exec(&$input) {
        // プロトコル呼び出し・結果取得
        $resultMap = $this->callProtocol($input->toString());
        
        // 戻り値がnullの場合、nullを戻す
        if (is_null($resultMap)) {
		    return null;
        }
				
		// DeleteCardOutputを作成し、戻す
		return new DeleteCardOutput($resultMap);
	}

}
?>