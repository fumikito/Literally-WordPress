<?php
require_once 'com/gmo_pg/client/output/EntryExecTranCvsOutput.php';
require_once 'com/gmo_pg/client/input/TdVerifyInput.php';
require_once 'com/gmo_pg/client/input/AcsParam.php';
require_once 'com/gmo_pg/client/tran/EntryTranCvs.php';
require_once 'com/gmo_pg/client/tran/ExecTranCvs.php';
require_once 'com/gmo_pg/client/tran/TdVerify.php';
require_once 'com/gmo_pg/client/common/RedirectUtil.php';
/**
 * <b>コンビニ取引登録・決済一括実行　実行クラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage tran
 * @see tranPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 03-07-2008 00:00:00
 */
class EntryExecTranCvs {
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
	function EntryExecTranCvs() {
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
	 * コンビニ登録・決済を実行する
	 *
	 * @param EntryExecTranCvsInput $input    コンビニ登録・決済入力パラメータ
	 * @return  EntryExecTranCvsOutput コンビニ登録・決済出力パラメータ
	 * @exception GPayException
	 */
	function exec(&$input) {
		// コンビニ取引登録入力パラメータを取得
		$entryTranCvsInput =& $input->getEntryTranCvsInput();
		// コンビニ決済実行入力パラメータを取得
		$execTranCvsInput =& $input->getExecTranCvsInput();

		// コンビニ登録・決済出力パラメータを生成
		$output = new EntryExecTranCvsOutput();

		// 取引ID、取引パスワードを取得
		$accessId = $execTranCvsInput->getAccessId();
		$accessPass = $execTranCvsInput->getAccessPass();

		// 取引ID、取引パスワードが設定されていないとき
		if (is_null($accessId) || 0 == strlen($accessId) || is_null($accessPass)) {
			// コンビニ取引登録を実行
			$this->log->debug("コンビニ取引登録実行");
			$entryTranCvs = new EntryTranCvs();
			$entryTranCvsOutput = $entryTranCvs->exec($entryTranCvsInput);

			if ($this->errorTrap($entryTranCvs)) {
				return $output;
			}

			// 取引ID、取引パスワードを決済実行用のパラメータに設定
			$accessId = $entryTranCvsOutput->getAccessId();
			$accessPass = $entryTranCvsOutput->getAccessPass();
			$execTranCvsInput->setAccessId($accessId);
			$execTranCvsInput->setAccessPass($accessPass);

			$output->setEntryTranCvsOutput($entryTranCvsOutput);
		}

		$this->log->debug("取引ID : [$accessId]  取引パスワード : [$accessPass]");

		// 取引登録でエラーが起きたとき決済を実行せずに戻る
		if ($output->isEntryErrorOccurred()) {
			$this->log->debug("<<<取引登録失敗>>>");
			return $output;
		}

		// 決済実行
		$this->log->debug("決済実行");
		$execTranCvs = new ExecTranCvs();
		$execTranCvsOutput = $execTranCvs->exec($execTranCvsInput);

		$output->setExecTranCvsOutput($execTranCvsOutput);

		$this->errorTrap($execTranCvs);

		return $output;
	}
	

}
?>