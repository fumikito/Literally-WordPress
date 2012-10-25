<?php
require_once ('com/gmo_pg/client/output/SearchCardOutput.php');
require_once ('com/gmo_pg/client/tran/BaseTran.php');
/**
 * <b>カード照会　実行クラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage tran
 * @see tranPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 01-01-2008 00:00:00
 */
class SearchCard extends BaseTran {

	/**
	 * コンストラクタ
	 */
	function SearchCard() {
	    parent::__construct();
	}

	/**
	 * カードを照会する
	 *
	 * @param  SearchCardInput $input    入力パラメータ
	 * @return SearchCardOutput 出力パラメータ
	 */
	function exec(&$input) {
        // プロトコル呼び出し・結果取得
        $resultMap = $this->callProtocol($input->toString());
        
        // 戻り値がnullの場合、nullを戻す
        if (is_null($resultMap)) {
		    return null;
        }
				
		// SearchCardOutputを作成し、戻す
		return new SearchCardOutput($resultMap);
	}

}
?>