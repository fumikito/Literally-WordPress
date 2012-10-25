<?php
require_once ('com/gmo_pg/client/output/SearchTradeMultiOutput.php');
require_once ('com/gmo_pg/client/tran/BaseTran.php');
/**
 * <b>取引照会MULTI　実行クラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage tran
 * @see tranPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 2008-07-15
 */
class SearchTradeMulti extends BaseTran {

	/**
	 * コンストラクタ
	 */
	function Searchrade() {
	    parent::__construct();
	}

	/**
	 * 取引を照会する
	 *
	 * @param  SearchTradeMultiInput $input    入力パラメータ
	 * @return SearchTradeMultiOutput 出力パラメータ
	 */
	function exec(&$input) {
        // プロトコル呼び出し・結果取得
        $resultMap = $this->callProtocol($input->toString());
        
        // 戻り値がnullの場合、nullを戻す
        if (is_null($resultMap)) {
		    return null;
        }
				
		// SearchTradeMultiOutputを作成し、戻す
		return new SearchTradeMultiOutput($resultMap);
	}

}
?>