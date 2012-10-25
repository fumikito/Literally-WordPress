<?php
require_once ('com/gmo_pg/client/output/SearchMemberOutput.php');
require_once ('com/gmo_pg/client/tran/BaseTran.php');
/**
 * <b>会員照会　実行クラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage tran
 * @see tranPackageInfo.php 
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 01-01-2008 00:00:00
 */
class SearchMember extends BaseTran {

	/**
	 * コンストラクタ
	 */
	function SearchMember() {
	    parent::__construct();
	}

	/**
	 * 会員を照会する
	 *
	 * @param  SearchMemberInput　$input    入力パラメータ
	 * @return SearchMemberOutput 出力パラメータ
	 */
	function exec(&$input) {
        // プロトコル呼び出し・結果取得
        $resultMap = $this->callProtocol($input->toString());
        
        // 戻り値がnullの場合、nullを戻す
        if (is_null($resultMap)) {
		    return null;
        }
				
		// SearchMemberOutputを作成し、戻す
		return new SearchMemberOutput($resultMap);
	}

}
?>