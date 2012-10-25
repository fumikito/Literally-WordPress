<?php
require_once ('com/gmo_pg/client/common/Cryptgram.php');
require_once ('com/gmo_pg/client/common/GPayException.php');
require_once ('com/gmo_pg/client/output/EntryTranOutput.php');
require_once ('com/gmo_pg/client/tran/BaseTran.php');
/**
 * <b>取引登録　実行クラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage tran
 * @see tranPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 01-01-2008 00:00:00
 */
class EntryTran extends BaseTran {

	/**
	 * コンストラクタ
	 */
	function EntryTran() {
	    parent::__construct();
	}
	
	/**
	 * プロトコルタイプのURLから戻り値を読み出す。
	 * 文字列を復号化して戻します。
	 *
	 * @param  string $retData プロトコルタイプからの取得文字列
	 * @return string 復号化済みの文字列 
	 */
	function recvData($retData) {
		// データの送受信に失敗しているときは戻る
		if (!$retData) {
			return null;
		}
		
		// 取得データの置き換え処理
        $retData = preg_replace('/^ReturnData=/', '', $retData);
        // rtrim処理(strvalで型をstringに固定)
        // ※rtrimの２つめの引数はPHP4.1.0以降で認識します
        $retData = strval(rtrim($retData, "\r\n"));
        
		return $retData;
	}

	/**
	 * 取引登録を実行する
	 *
	 * @param  EntryTranInput $input  入力パラメータ
	 * @return EntryTranOutput $output 出力パラメータ
	 * @exception GPayException
	 */
	function exec(&$input) {
	    
	    // 元の店舗名を退避
	    $tdTenantName = $input->getTdTenantName();
	    
        // 3Dセキュア店舗名をBASE64暗号化
	    $encrypt = new Cryptgram();
	    $encodeTdTenantName = $encrypt->encodeBase64($input->getTdTenantName());
	    if (!is_null($encodeTdTenantName) && strlen($encodeTdTenantName) > 25) {
	        $this->exception = new GPayException
	            ("3Dセキュア店舗名のBASE64暗号化後の長さが25Byteを超えています。", $this->exception);
	        return null;
	    }
	    // 暗号化文字列を再セット
	    $input->setTdTenantName($encodeTdTenantName);
        // 接続しプロトコル呼び出し・結果取得
        $resultMap = $this->callProtocol($input->toString());
        // 店舗名を退避しておいた元の文字列に戻す
        $input->setTdTenantName($tdTenantName);
	    // 戻り値がnullの場合、nullを戻す
        if (is_null($resultMap)) {
		    return null;
        }
	    
        // EntryTranOutput作成し、戻す
	    return new EntryTranOutput($resultMap);
	}
}
?>