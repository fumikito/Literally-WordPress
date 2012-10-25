<?php
require_once 'com/gmo_pg/client/output/EntryExecTranEdyOutput.php';
require_once 'com/gmo_pg/client/input/TdVerifyInput.php';
require_once 'com/gmo_pg/client/input/AcsParam.php';
require_once 'com/gmo_pg/client/tran/EntryTranEdy.php';
require_once 'com/gmo_pg/client/tran/ExecTranEdy.php';
require_once 'com/gmo_pg/client/tran/TdVerify.php';
require_once 'com/gmo_pg/client/common/RedirectUtil.php';
/**
 * <b>モバイルEdy取引登録・決済一括実行　実行クラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage tran
 * @see tranPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 03-07-2008 00:00:00
 */
class EntryExecTranEdy {
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
	function EntryExecTranEdy() {
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
	 * モバイルEdy登録・決済を実行する
	 *
	 * @param EntryExecTranEdyInput $input    モバイルEdy登録・決済入力パラメータ
	 * @return  EntryExecTranEdyOutput モバイルEdy登録・決済出力パラメータ
	 * @exception GPayException
	 */
	function exec(&$input) {
		// モバイルEdy取引登録入力パラメータを取得
		$entryTranEdyInput =& $input->getEntryTranEdyInput();
		// モバイルEdy決済実行入力パラメータを取得
		$execTranEdyInput =& $input->getExecTranEdyInput();

		// モバイルEdy登録・決済出力パラメータを生成
		$output = new EntryExecTranEdyOutput();

		// 取引ID、取引パスワードを取得
		$accessId = $execTranEdyInput->getAccessId();
		$accessPass = $execTranEdyInput->getAccessPass();

		// 取引ID、取引パスワードが設定されていないとき
		if (is_null($accessId) || 0 == strlen($accessId) || is_null($accessPass)) {
			// モバイルEdy取引登録を実行
			$this->log->debug("モバイルEdy取引登録実行");
			$entryTranEdy = new EntryTranEdy();
			$entryTranEdyOutput = $entryTranEdy->exec($entryTranEdyInput);

			if ($this->errorTrap($entryTranEdy)) {
				return $output;
			}

			// 取引ID、取引パスワードを決済実行用のパラメータに設定
			$accessId = $entryTranEdyOutput->getAccessId();
			$accessPass = $entryTranEdyOutput->getAccessPass();
			$execTranEdyInput->setAccessId($accessId);
			$execTranEdyInput->setAccessPass($accessPass);

			$output->setEntryTranEdyOutput($entryTranEdyOutput);
		}

		$this->log->debug("取引ID : [$accessId]  取引パスワード : [$accessPass]");

		// 取引登録でエラーが起きたとき決済を実行せずに戻る
		if ($output->isEntryErrorOccurred()) {
			$this->log->debug("<<<取引登録失敗>>>");
			return $output;
		}

		// 決済実行
		$this->log->debug("決済実行");
		$execTranEdy = new ExecTranEdy();
		$execTranEdyOutput = $execTranEdy->exec($execTranEdyInput);

		$output->setExecTranEdyOutput($execTranEdyOutput);

		$this->errorTrap($execTranEdy);

		return $output;
	}
	

}
?>