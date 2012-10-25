<?php
require_once 'com/gmo_pg/client/output/EntryExecTranPaypalOutput.php';
require_once 'com/gmo_pg/client/tran/EntryTranPaypal.php';
require_once 'com/gmo_pg/client/tran/ExecTranPaypal.php';


/**
 * <b>Paypal取引登録・決済一括実行　実行クラス</b>
 *
 * @package com.gmo_pg.client
 * @subpackage tran
 * @see tranPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 12-24-2009 00:00:00
 */
class EntryExecTranPaypal {

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
	 * @return unknown_type
	 */
	function EntryExecTranPaypal() {
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
	 * Paypal取引登録・決済を実行する
	 *
	 * @param EntryExecTranPaypalInput $input    Paypal取引登録・決済入力パラメータ
	 * @return  EntryExecTranPaypalOutput Paypal取引登録・決済出力パラメータ
	 * @exception GPayException
	 */
	function exec(&$input) {
		// Paypal取引登録入力パラメータを取得
		$entryTranPaypalInput =& $input->getEntryTranPaypalInput();
		// Paypal決済実行入力パラメータを取得
		$execTranPaypalInput =& $input->getExecTranPaypalInput();

		// Paypal取引登録・決済出力パラメータを生成
		$output = new EntryExecTranPaypalOutput();

		// 取引ID、取引パスワードを取得
		$accessId = $execTranPaypalInput->getAccessId();
		$accessPass = $execTranPaypalInput->getAccessPass();

		// 取引ID、取引パスワードが設定されていないとき
		if (is_null($accessId) || 0 == strlen($accessId) || is_null($accessPass)) {
			// コンビニ取引登録を実行
			$this->log->debug("Paypal取引登録実行");
			$entryTranPaypal = new EntryTranPaypal();
			$entryTranPaypalOutput = $entryTranPaypal->exec($entryTranPaypalInput);

			if ($this->errorTrap($entryTranPaypal)) {
				return $output;
			}

			// 取引ID、取引パスワードを決済実行用のパラメータに設定
			$accessId = $entryTranPaypalOutput->getAccessId();
			$accessPass = $entryTranPaypalOutput->getAccessPass();
			$execTranPaypalInput->setAccessId($accessId);
			$execTranPaypalInput->setAccessPass($accessPass);

			$output->setEntryTranPaypalOutput($entryTranPaypalOutput);
		}

		$this->log->debug("取引ID : [$accessId]  取引パスワード : [$accessPass]");

		// 取引登録でエラーが起きたとき決済を実行せずに戻る
		if ($output->isEntryErrorOccurred()) {
			$this->log->debug("<<<Paypal取引登録失敗>>>");
			return $output;
		}

		// 決済実行
		$this->log->debug("決済実行");
		$execTranPaypal = new ExecTranPaypal();
		$execTranPaypalOutput = $execTranPaypal->exec($execTranPaypalInput);

		$output->setExecTranPaypalOutput($execTranPaypalOutput);

		$this->errorTrap($execTranPaypal);

		return $output;
	}
}
