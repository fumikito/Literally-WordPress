<?php
require_once 'com/gmo_pg/client/output/EntryExecTranAuOutput.php';
require_once 'com/gmo_pg/client/tran/EntryTranAu.php';
require_once 'com/gmo_pg/client/tran/ExecTranAu.php';
/**
 * <b>auかんたん決済登録・決済一括実行　実行クラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage tran
 * @see tranPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 2012/02/15
 */
class EntryExecTranAu {
	/**
	 * @var Log ログ
	 */
	var $log;

	/**
	 * @var GPayException 例外
	 */
	var $exception;

	/**
	 * コンストラクタ
	 */
	function EntryExecTranAu() {
		$this->__construct();
	}

	/**
	 * コンストラクタ
	 */
	function __construct() {
		$this->log = new Log(get_class($this));
	}

	/**
	 * 例外の発生を判定する
	 *
	 * @param mixed $target    判定対象
	 */
	function errorTrap(&$target) {
		if (is_null($target->exception)) {
			return false;
		}
		$this->exception = $target->exception;
		return true;
	}

	/**
	 * 例外の発生を判定する
	 *
	 * @return  boolean 判定結果(true=エラーアリ)
	 */
	function isExceptionOccured() {
		return false == is_null($this->exception);
	}

	/**
	 * 例外を返す
	 *
	 * @return  GPayException 例外
	 */
	function &getException() {
		return $this->exception;
	}

	/**
	 * auかんたん決済登録・決済を実行する
	 *
	 * @param EntryExecTranAuInput $input    auかんたん決済登録・決済入力パラメータ
	 * @return  EntryExecTranAuOutput auかんたん決済登録・決済出力パラメータ
	 * @exception GPayException
	 */
	function exec(&$input) {
		// auかんたん決済取引登録入力パラメータを取得
		$entryTranAuInput =& $input->getEntryTranAuInput();
		// auかんたん決済決済実行入力パラメータを取得
		$execTranAuInput =& $input->getExecTranAuInput();

		// auかんたん決済登録・決済出力パラメータを生成
		$output = new EntryExecTranAuOutput();

		// 取引ID、取引パスワードを取得
		$accessId = $execTranAuInput->getAccessId();
		$accessPass = $execTranAuInput->getAccessPass();

		// 取引ID、取引パスワードが設定されていないとき
		if (is_null($accessId) || 0 == strlen($accessId) || is_null($accessPass)) {
			// auかんたん決済取引登録を実行
			$this->log->debug("auかんたん決済取引登録実行");
			$entryTranAu = new EntryTranAu();
			$entryTranAuOutput = $entryTranAu->exec($entryTranAuInput);

			if ($this->errorTrap($entryTranAu)) {
				return $output;
			}

			// 取引ID、取引パスワードを決済実行用のパラメータに設定
			$accessId = $entryTranAuOutput->getAccessId();
			$accessPass = $entryTranAuOutput->getAccessPass();
			$execTranAuInput->setAccessId($accessId);
			$execTranAuInput->setAccessPass($accessPass);

			$output->setEntryTranAuOutput($entryTranAuOutput);
		}

		$this->log->debug("取引ID : [$accessId]  取引パスワード : [$accessPass]");

		// 取引登録でエラーが起きたとき決済を実行せずに戻る
		if ($output->isEntryErrorOccurred()) {
			$this->log->debug("<<<取引登録失敗>>>");
			return $output;
		}

		// 決済実行
		$this->log->debug("決済実行");
		$execTranAu = new ExecTranAu();
		$execTranAuOutput = $execTranAu->exec($execTranAuInput);

		$output->setExecTranAuOutput($execTranAuOutput);

		$this->errorTrap($execTranAu);

		return $output;
	}
	

}
?>