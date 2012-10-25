<?php
require_once 'com/gmo_pg/client/output/EntryExecTranSuicaOutput.php';
require_once 'com/gmo_pg/client/input/TdVerifyInput.php';
require_once 'com/gmo_pg/client/input/AcsParam.php';
require_once 'com/gmo_pg/client/tran/EntryTranSuica.php';
require_once 'com/gmo_pg/client/tran/ExecTranSuica.php';
require_once 'com/gmo_pg/client/tran/TdVerify.php';
require_once 'com/gmo_pg/client/common/RedirectUtil.php';
/**
 * <b>モバイルSuica取引登録・決済一括実行　実行クラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage tran
 * @see tranPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 03-07-2008 00:00:00
 */
class EntryExecTranSuica {
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
	function EntryExecTranSuica() {
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
	 * モバイルSuica登録・決済を実行する
	 *
	 * @param EntryExecTranSuicaInput $input    モバイルSuica登録・決済入力パラメータ
	 * @return  EntryExecTranSuicaOutput モバイルSuica登録・決済出力パラメータ
	 * @exception GPayException
	 */
	function exec(&$input) {
		// モバイルSuica取引登録入力パラメータを取得
		$entryTranSuicaInput =& $input->getEntryTranSuicaInput();
		// モバイルSuica決済実行入力パラメータを取得
		$execTranSuicaInput =& $input->getExecTranSuicaInput();

		// モバイルSuica登録・決済出力パラメータを生成
		$output = new EntryExecTranSuicaOutput();

		// 取引ID、取引パスワードを取得
		$accessId = $execTranSuicaInput->getAccessId();
		$accessPass = $execTranSuicaInput->getAccessPass();

		// 取引ID、取引パスワードが設定されていないとき
		if (is_null($accessId) || 0 == strlen($accessId) || is_null($accessPass)) {
			// モバイルSuica取引登録を実行
			$this->log->debug("モバイルSuica取引登録実行");
			$entryTranSuica = new EntryTranSuica();
			$entryTranSuicaOutput = $entryTranSuica->exec($entryTranSuicaInput);

			if ($this->errorTrap($entryTranSuica)) {
				return $output;
			}

			// 取引ID、取引パスワードを決済実行用のパラメータに設定
			$accessId = $entryTranSuicaOutput->getAccessId();
			$accessPass = $entryTranSuicaOutput->getAccessPass();
			$execTranSuicaInput->setAccessId($accessId);
			$execTranSuicaInput->setAccessPass($accessPass);

			$output->setEntryTranSuicaOutput($entryTranSuicaOutput);
		}

		$this->log->debug("取引ID : [$accessId]  取引パスワード : [$accessPass]");

		// 取引登録でエラーが起きたとき決済を実行せずに戻る
		if ($output->isEntryErrorOccurred()) {
			$this->log->debug("<<<取引登録失敗>>>");
			return $output;
		}

		// 決済実行
		$this->log->debug("決済実行");
		$execTranSuica = new ExecTranSuica();
		$execTranSuicaOutput = $execTranSuica->exec($execTranSuicaInput);

		$output->setExecTranSuicaOutput($execTranSuicaOutput);

		$this->errorTrap($execTranSuica);

		return $output;
	}
	

}
?>