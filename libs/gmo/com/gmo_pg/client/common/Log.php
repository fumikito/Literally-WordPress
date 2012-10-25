<?php
require_once ('com/gmo_pg/client/common/LogFormatter.php');

/**
 * <b>独自ログクラス</b>
 * 
 * GMO-PG独自のログクラス
 * 
 * @package com.gmo_pg.client
 * @subpackage common
 * @see commonPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 01-01-2008 00:00:00
 */
class Log {

	/**
	 * @var string クラス名
	 */
	var $className_;

	/**
	 * @var integer ログレベル
	 */
	var $level_;

	/**
	 * @var strign ログファイル名
	 */
	var $fileName_;

	/**
	 * @var string ログタイプ
	 */
	var $type_;

	/**
	 * @var LogFormatter ログフォーマッタ
	 */
	var $logFormatter_;

	/**
	 * コンストラクタ
	 * 
	 * @param string $className クラス名称
	 */
	function Log($className) {
		$this->__construct($className);
	}

	/**
	 * コンストラクタ
	 * 
	 * @param string $className クラス名称
	 */
	function __construct($className) {
		$this->className_ = $className;
		$this->logFormatter_ = new LogFormatter();

		// 各種デフォルト値を設定
		$this->level_ = 'INFO';
		$this->fileName_ = 'gpayclient.log';
		$this->type_ = 3;

		// 初期化
		$this->init();
	}

	/**
	 * 初期化
	 */
	function init() {
		// INCLUDEパスの内容を取得
        //※get_include_path()はPHP4.3.0以降で動作します。
		$inc_pathes = split(PATH_SEPARATOR, get_include_path());
		// 定義ファイルをINCLUDEパス配下から検索
		$prop_file = null;
		$prop_name = 'conf/log.properties';
		foreach ($inc_pathes as $inc_path) {
			if (file_exists("$inc_path/$prop_name")) {
				$prop_file = "$inc_path/$prop_name";
				break;
			}
		}
		// 定義ファイルが見つからないときは戻る
		if (is_null($prop_file)) {
			return;
		}

		// 定義ファイルの内容の解析
		$props = parse_ini_file($prop_file);

		// ログレベルの設定
		if (array_key_exists('level', $props)) {
			$this->level_ = $props['level'];
		}
		// ログファイル名の設定
		if (array_key_exists('fileName', $props)) {
			$this->fileName_ = $props['fileName'];
		}
		// ログタイプの設定
		if (array_key_exists('type', $props)) {
			$types = array('CONSOLE'=>0, 'FILE'=>3);
			$this->type_ = array_key_exists($props['type'], $types) ? $types[$props['type']] : 3;
		}
	}

	/**
	 * ログファイルの最終更新日付判定
	 *
	 * @param string $log_file  ログファイル名
	 */
	function ensure($log_file) {
		// ログファイルが存在しないときは戻る
		if (false == file_exists($log_file)) {
			return;
		}

		// ログファイルの最終更新日付を取得
		$mtime = date('Ymd', filemtime($log_file));
		// 実行時の日付を取得
		$now = date('Ymd');

		// 最終更新日付が実行時日付より前のときはリネームする
		if (intval($now) > intval($mtime)) {
			rename($log_file, "$log_file.$mtime");
		}
	}

	/**
	 * ログ出力
	 *
	 * @param integer $level     ログレベル
	 * @param string $message   ログ内容
	 * @param mixed $params    置き換えパラメータ
	 */
	function logging($level, $message, $params = null) {
		// ログファイルの日付のチェック
		$this->ensure($this->fileName_);
		// ログの内容の構築
		$log = $this->logFormatter_->format($this->className_, $level, $message, $params);
		// ログの出力
		error_log($log, $this->type_, $this->fileName_);
	}

	/**
	 * DEBUGレベルログ出力
	 *
	 * @param string $message    ログ内容
	 * @param mixed $params    置き換えパラメータ
	 */
	function debug($message, $params = null) {
		if ($this->isDebugEnabled()) {
			$this->logging('DEBUG', $message, $params);
		}
	}

	/**
	 * WARNレベルログ出力
	 *
	 * @param string $message    ログ内容
	 * @param mixed $params    置き換えパラメータ
	 */
	function warn($message, $params = null) {
		if ($this->isWarnEnabled()) {
			$this->logging('WARN', $message, $params);
		}
	}

	/**
	 * INFOレベルログ出力
	 *
	 * @param string $message    ログ内容
	 * @param mixed $params    置き換えパラメータ
	 */
	function info($message, $params = null) {
		if ($this->isInfoEnabled()) {
			$this->logging('INFO', $message, $params);
		}
	}

	/**
	 * ERRORレベルログ出力
	 *
	 * @param string $message    ログ内容
	 * @param mixed $params    置き換えパラメータ
	 */
	function error($message, $params = null) {
		if ($this->isErrorEnabled()) {
			$this->logging('ERROR', $message, $params);
		}
	}

	/**
	 * ログレベルの有効性判定
	 * 
	 * <p>
	 *   パラメータのログレベルが有効であるかチェックし、判定結果のフラグが返る
	 * </p>
	 * @param integer $level ログレベル
	 * @return boolean ログ有効フラグ(true=有効、false=無効)
	 */
	function isLogEnabled($level) {
		$levels = array('ALL'=>0, 'TRACE'=>1, 'DEBUG'=>2, 'INFO'=>3, 'WARN'=>4, 'ERROR'=>5);

		// 設定されているログのレベルを取得
		$log_level = array_key_exists($this->level_, $levels) ? $levels[$this->level_] : 0;
		// 指定のログのレベルを取得
		$info_level = array_key_exists($level, $levels) ? $levels[$level] : 0;

		// INFOのログのレベルが設定されているログのレベル以上か否か判定する
		return $log_level <= $info_level;
	}

	/**
	 * DEBUGレベルの有効性判定
	 * @return boolean debugログ有効フラグ(true=有効、false=無効)
	 */
	function isDebugEnabled() {
		return $this->isLogEnabled('DEBUG');
	}

	/**
	 * WARNレベルの有効性判定
	 * @return boolean warnログ有効フラグ(true=有効、false=無効) 
	 */
	function isWarnEnabled() {
		return $this->isLogEnabled('WARN');
	}

	/**
	 * INFOレベルの有効性判定
	 * @return boolean infoログ有効フラグ(true=有効、false=無効)
	 */
	function isInfoEnabled() {
		return $this->isLogEnabled('INFO');
	}

	/**
	 * ERRORレベルの有効性判定
	 * @return boolean errorログ有効フラグ(true=有効、false=無効) 
	 */
	function isErrorEnabled() {
		return $this->isLogEnabled('ERROR');
	}

}
?>