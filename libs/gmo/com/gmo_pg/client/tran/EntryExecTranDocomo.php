<?php
require_once 'com/gmo_pg/client/output/EntryExecTranDocomoOutput.php';
require_once 'com/gmo_pg/client/tran/EntryTranDocomo.php';
require_once 'com/gmo_pg/client/tran/ExecTranDocomo.php';
/**
 * <b>ドコモケータイ払い登録・決済一括実行　実行クラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage tran
 * @see tranPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 2012/06/14
 */
class EntryExecTranDocomo {
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
	function EntryExecTranDocomo() {
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
	 * ドコモケータイ払い登録・決済を実行する
	 *
	 * @param EntryExecTranDocomoInput $input    ドコモケータイ払い登録・決済入力パラメータ
	 * @return  EntryExecTranDocomoOutput ドコモケータイ払い登録・決済出力パラメータ
	 * @exception GPayException
	 */
	function exec(&$input) {
		// ドコモケータイ払い取引登録入力パラメータを取得
		$entryTranDocomoInput =& $input->getEntryTranDocomoInput();
		// ドコモケータイ払い決済実行入力パラメータを取得
		$execTranDocomoInput =& $input->getExecTranDocomoInput();

		// ドコモケータイ払い登録・決済出力パラメータを生成
		$output = new EntryExecTranDocomoOutput();

		// 取引ID、取引パスワードを取得
		$accessId = $execTranDocomoInput->getAccessId();
		$accessPass = $execTranDocomoInput->getAccessPass();

		// 取引ID、取引パスワードが設定されていないとき
		if (is_null($accessId) || 0 == strlen($accessId) || is_null($accessPass)) {
			// ドコモケータイ払い取引登録を実行
			$this->log->debug("ドコモケータイ払い取引登録実行");
			$entryTranDocomo = new EntryTranDocomo();
			$entryTranDocomoOutput = $entryTranDocomo->exec($entryTranDocomoInput);

			if ($this->errorTrap($entryTranDocomo)) {
				return $output;
			}

			// 取引ID、取引パスワードを決済実行用のパラメータに設定
			$accessId = $entryTranDocomoOutput->getAccessId();
			$accessPass = $entryTranDocomoOutput->getAccessPass();
			$execTranDocomoInput->setAccessId($accessId);
			$execTranDocomoInput->setAccessPass($accessPass);

			$output->setEntryTranDocomoOutput($entryTranDocomoOutput);
		}

		$this->log->debug("取引ID : [$accessId]  取引パスワード : [$accessPass]");

		// 取引登録でエラーが起きたとき決済を実行せずに戻る
		if ($output->isEntryErrorOccurred()) {
			$this->log->debug("<<<取引登録失敗>>>");
			return $output;
		}

		// 決済実行
		$this->log->debug("決済実行");
		$execTranDocomo = new ExecTranDocomo();
		$execTranDocomoOutput = $execTranDocomo->exec($execTranDocomoInput);

		$output->setExecTranDocomoOutput($execTranDocomoOutput);

		$this->errorTrap($execTranDocomo);

		return $output;
	}
	

}
?>
