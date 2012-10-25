<?php
require_once ('com/gmo_pg/client/output/SaveCardOutput.php');
require_once ('com/gmo_pg/client/tran/BaseTran.php');
/**
 * <b>カード登録　実行クラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage tran
 * @see tranPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 01-01-2008 00:00:00
 */
class SaveCard extends BaseTran {

	/**
	 * コンストラクタ
	 */
	function SaveCard() {
	    parent::__construct();
	}

	/**
	 * カードを登録する
	 *
	 * @param  SaveCardInput $input    入力パラメータ
	 * @return SaveCardOutput 出力パラメータ
	 */
	function exec(&$input) {
        // プロトコル呼び出し・結果取得
        $resultMap = $this->callProtocol($input->toString());
        
        // 戻り値がnullの場合、nullを戻す
        if (is_null($resultMap)) {
		    return null;
        }
				
		// SaveCardOutputを作成し、戻す
		return new SaveCardOutput($resultMap);
	}

}
?>