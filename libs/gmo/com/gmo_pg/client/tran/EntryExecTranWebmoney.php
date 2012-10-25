<?php
require_once 'com/gmo_pg/client/output/EntryExecTranWebmoneyOutput.php';
require_once 'com/gmo_pg/client/tran/EntryTranWebmoney.php';
require_once 'com/gmo_pg/client/tran/ExecTranWebmoney.php';


/**
 * <b>Webmoney取引登録・決済一括実行　実行クラス</b>
 *
 * @package com.gmo_pg.client
 * @subpackage tran
 * @see tranPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 04-10-2010
 */
class EntryExecTranWebmoney {

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
	function EntryExecTranWebmoney() {
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
	 * Webmoney取引登録・決済を実行する
	 *
	 * @param EntryExecTranWebmoneyInput $input    Webmoney取引登録・決済入力パラメータ
	 * @return  EntryExecTranWebmoneyOutput Webmoney取引登録・決済出力パラメータ
	 * @exception GPayException
	 */
	function exec(&$input) {
		// Webmoney取引登録入力パラメータを取得
		$entryTranWebmoneyInput =& $input->getEntryTranWebmoneyInput();
		// Webmoney決済実行入力パラメータを取得
		$execTranWebmoneyInput =& $input->getExecTranWebmoneyInput();

		// Webmoney取引登録・決済出力パラメータを生成
		$output = new EntryExecTranWebmoneyOutput();

		// 取引ID、取引パスワードを取得
		$accessId = $execTranWebmoneyInput->getAccessId();
		$accessPass = $execTranWebmoneyInput->getAccessPass();

		// 取引ID、取引パスワードが設定されていないとき
		if (is_null($accessId) || 0 == strlen($accessId) || is_null($accessPass)) {
			// WebMoney取引登録を実行
			$this->log->debug("Webmoney取引登録実行");
			$entryTranWebmoney = new EntryTranWebmoney();
			$entryTranWebmoneyOutput = $entryTranWebmoney->exec($entryTranWebmoneyInput);

			if ($this->errorTrap($entryTranWebmoney)) {
				return $output;
			}

			// 取引ID、取引パスワードを決済実行用のパラメータに設定
			$accessId = $entryTranWebmoneyOutput->getAccessId();
			$accessPass = $entryTranWebmoneyOutput->getAccessPass();
			$execTranWebmoneyInput->setAccessId($accessId);
			$execTranWebmoneyInput->setAccessPass($accessPass);

			$output->setEntryTranWebmoneyOutput($entryTranWebmoneyOutput);
		}

		$this->log->debug("取引ID : [$accessId]  取引パスワード : [$accessPass]");

		// 取引登録でエラーが起きたとき決済を実行せずに戻る
		if ($output->isEntryErrorOccurred()) {
			$this->log->debug("<<<Webmoney取引登録失敗>>>");
			return $output;
		}

		// 決済実行
		$this->log->debug("決済実行");
		$execTranWebmoney = new ExecTranWebmoney();
		$execTranWebmoneyOutput = $execTranWebmoney->exec($execTranWebmoneyInput);

		$output->setExecTranWebmoneyOutput($execTranWebmoneyOutput);

		$this->errorTrap($execTranWebmoney);

		return $output;
	}
}
