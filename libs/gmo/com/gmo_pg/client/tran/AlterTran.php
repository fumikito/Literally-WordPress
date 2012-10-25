<?php
require_once ('com/gmo_pg/client/output/AlterTranOutput.php');
require_once ('com/gmo_pg/client/tran/BaseTran.php');
/**
 * <b>取引変更 実行クラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage tran
 * @see tranPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 01-01-2008 00:00:00
 */
class AlterTran extends BaseTran {

	/**
	 * コンストラクタ
	 */
	function AlterTran() {
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
	 * @param AlterTranInput $input 入力パラメータ
	 * @return AlterTranOutput 出力パラメータ
	 */
	function exec(&$input) {
        // プロトコル呼び出し・結果取得
        $resultMap = $this->callProtocol($input->toString());
        
	    // 戻り値がnullの場合、nullを戻す
        if (is_null($resultMap)) {
		    return null;
        }
	    
        // AlterTranOutput作成し、戻す    
	    return new AlterTranOutput($resultMap);
	}
}
?>