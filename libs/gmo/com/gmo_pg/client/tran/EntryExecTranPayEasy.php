<?php
require_once 'com/gmo_pg/client/output/EntryExecTranPayEasyOutput.php';
require_once 'com/gmo_pg/client/input/TdVerifyInput.php';
require_once 'com/gmo_pg/client/input/AcsParam.php';
require_once 'com/gmo_pg/client/tran/EntryTranPayEasy.php';
require_once 'com/gmo_pg/client/tran/ExecTranPayEasy.php';
require_once 'com/gmo_pg/client/tran/TdVerify.php';
require_once 'com/gmo_pg/client/common/RedirectUtil.php';
/**
 * <b>PayEasy取引登録・決済一括実行　実行クラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage tran
 * @see tranPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 03-07-2008 00:00:00
 */
class EntryExecTranPayEasy {
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
	function EntryExecTranPayEasy() {
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
	 * PayEasy登録・決済を実行する
	 *
	 * @param EntryExecTranPayEasyInput $input    PayEasy登録・決済入力パラメータ
	 * @return  EntryExecTranPayEasyOutput PayEasy登録・決済出力パラメータ
	 * @exception GPayException
	 */
	function exec(&$input) {
		// PayEasy取引登録入力パラメータを取得
		$entryTranPayEasyInput =& $input->getEntryTranPayEasyInput();
		// PayEasy決済実行入力パラメータを取得
		$execTranPayEasyInput =& $input->getExecTranPayEasyInput();

		// PayEasy登録・決済出力パラメータを生成
		$output = new EntryExecTranPayEasyOutput();

		// 取引ID、取引パスワードを取得
		$accessId = $execTranPayEasyInput->getAccessId();
		$accessPass = $execTranPayEasyInput->getAccessPass();

		// 取引ID、取引パスワードが設定されていないとき
		if (is_null($accessId) || 0 == strlen($accessId) || is_null($accessPass)) {
			// PayEasy取引登録を実行
			$this->log->debug("PayEasy取引登録実行");
			$entryTranPayEasy = new EntryTranPayEasy();
			$entryTranPayEasyOutput = $entryTranPayEasy->exec($entryTranPayEasyInput);

			if ($this->errorTrap($entryTranPayEasy)) {
				return $output;
			}

			// 取引ID、取引パスワードを決済実行用のパラメータに設定
			$accessId = $entryTranPayEasyOutput->getAccessId();
			$accessPass = $entryTranPayEasyOutput->getAccessPass();
			$execTranPayEasyInput->setAccessId($accessId);
			$execTranPayEasyInput->setAccessPass($accessPass);

			$output->setEntryTranPayEasyOutput($entryTranPayEasyOutput);
		}

		$this->log->debug("取引ID : [$accessId]  取引パスワード : [$accessPass]");

		// 取引登録でエラーが起きたとき決済を実行せずに戻る
		if ($output->isEntryErrorOccurred()) {
			$this->log->debug("<<<取引登録失敗>>>");
			return $output;
		}

		// 決済実行
		$this->log->debug("決済実行");
		$execTranPayEasy = new ExecTranPayEasy();
		$execTranPayEasyOutput = $execTranPayEasy->exec($execTranPayEasyInput);

		$output->setExecTranPayEasyOutput($execTranPayEasyOutput);

		$this->errorTrap($execTranPayEasy);

		return $output;
	}
	

}
?>