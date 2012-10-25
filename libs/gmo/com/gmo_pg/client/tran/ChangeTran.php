<?php
require_once ('com/gmo_pg/client/output/ChangeTranOutput.php');
require_once ('com/gmo_pg/client/tran/BaseTran.php');
/**
 * <b>金額変更 実行クラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage tran
 * @see tranPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 01-01-2008 00:00:00
 */
class ChangeTran extends BaseTran {

	/**
	 * コンストラクタ
	 */
	function ChangeTran() {
		$this->__construct();
	}

	/**
	 * コンストラクタ
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * 変更を実行する
	 *
	 * @param ChangeTranInput $input 入力パラメータ
	 * @return ChangeTranOutput 出力パラメータ
	 */
	function exec(&$input) {
        // プロトコル呼び出し・結果取得
        $resultMap = $this->callProtocol($input->toString());
        
	    // 戻り値がnullの場合、nullを戻す
        if (is_null($resultMap)) {
		    return null;
        }
	    
        // ChangeTranOutput作成し、戻す    
	    return new ChangeTranOutput($resultMap);
	}
}
?>