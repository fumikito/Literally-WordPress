<?php
require_once 'com/gmo_pg/client/common/Log.php';
require_once 'com/gmo_pg/client/common/ParamParser.php';
require_once 'com/gmo_pg/client/common/IgnoreCaseMap.php';
require_once 'com/gmo_pg/client/common/GPayException.php';
require_once 'com/gmo_pg/client/common/ConnectUrlMap.php';
/**
 * @abstract 
 * <b>API 基底クラス</b>
 * 
 * @package com.gmo_pg.client
 * @subpackage tran
 * @see tranPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 01-01-2008 00:00:00
 */
class BaseTran {
	
	var $user = 'MODP-3.108.106';
	var $version = '106';
	
	/**
	 * @var Log 独自ログクラス
	 */
	var $log;

	/**
	 * @var GPayException 独自例外
	 */
	var $exception;

	/**
	 * コンストラクタ
	 */
	function BaseTran() {
		$this->__construct();
	}

	/**
	 * コンストラクタ
	 */
	function __construct() {
		$this->log = new Log(get_class($this));
	}

	/**
	 * プロトコルタイプのURLへ接続する。
	 *
	 * @param string $url    プロトコルタイプへのURL文字列
	 * @exception GPayException
	 */
	function connect($url) {
		// URLを解析
		$url_tokens = parse_url($url);

		// プロトコルを取得
        // ※array_key_exists()はPHP4.1.0以降で動作します
		$protocol = array_key_exists('scheme', $url_tokens) ? $url_tokens['scheme'] : null;

		// 未対応のプロトコルのときはエラーとする
		if (false == preg_match('/^[Hh][Tt][Tt][Pp][Ss]?/', $protocol)) {
			$this->exception =
				new GPayException("未対応のプロトコルが指定されました。[$protocol]", $this->exception);
			return null;
		}

		// HTTP/HTTPS 接続

		// CURLの初期化
		$urlConnect = curl_init();
		// POSTメソッドに設定
		curl_setopt($urlConnect, CURLOPT_POST, 1);
		// URLを設定
		curl_setopt($urlConnect, CURLOPT_URL, $url);
		// 戻り値の取得方法の設定
		curl_setopt($urlConnect, CURLOPT_RETURNTRANSFER, 1);
		// SSL認証の設定
		curl_setopt($urlConnect, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($urlConnect, CURLOPT_SSL_VERIFYPEER, false);	// サーバ証明書の検証を行わない
	//	curl_setopt($urlConnect, CURLOPT_SSL_VERIFYPEER, 2);	// サーバ証明書の検証を行う
	//	curl_setopt($urlConnect, CURLOPT_CAINFO, 'C:/Tmp/localhost.cer');	// サーバ証明書のパス

		$error = curl_error($urlConnect);
		if ($error) {
			$this->exception =
				new GPayException("プロトコルタイプへの接続に失敗しました。[$error]", $this->exception);
			return null;
		}

		return $urlConnect;
	}

	/**
	 * プロトコルタイプのURLへの接続を解除する。
	 *
	 * @param mixed $urlConnect    プロトコルタイプへのURL接続
	 */
	function disconnect(&$urlConnect) {
		if ($urlConnect) {
			curl_close($urlConnect);
		}
	}

	/**
	 * プロトコルタイプのURLへデータを送信する。
	 *
	 * @param mixed $urlConnect    プロトコルタイプへのURL接続
	 * @param string $params    プロトコルタイプへ送信するパラメータ文字列
	 * @exception GPayException
	 */
	function sendData(&$urlConnect, $params) {
		// HTTP/HTTPS 接続に失敗しているときは戻る
		if (!$urlConnect) {
			return null;
		}

		if (is_null($params)) {
			$this->exception = new GPayException("パラメータ文字列がnullです。", $this->exception);
			return null;
		}

		// パラメータを送信
		curl_setopt($urlConnect, CURLOPT_POSTFIELDS, $params);
		$retData = curl_exec($urlConnect);

		if (false == $retData) {
			$error = curl_error($urlConnect);
			$this->exception =
				new GPayException("プロトコルタイプとのデータの送受信に失敗しました。[$error]", $this->exception);
		}

		return $retData;
	}

	/**
	 * プロトコルタイプのURLから戻り値を読み出す。
	 *
	 * @param mixed $retData    プロトコルタイプへのURL接続
	 * @return string 戻り値
	 * @exception GPayException
	 */
	function recvData($retData) {
		// データの送受信に失敗しているときは戻る
		if (!$retData) {
			return null;
		}

		// ※２つめの引数はPHP4.1.0以降で認識します。
		return rtrim($retData, "\r\n");
	}

	/**
	 * プロトコルタイプを呼び出し、結果を返す。
	 *
	 * @param string $url    プロトコルタイプへのURL文字列
	 * @param string $params    プロトコルタイプへ送信するパラメータ文字列
	 * @return IgnoreCaseMap 出力パラメータマップ
	 * @exception GPayException
	 */
	function callProtocol_($url, $params) {

		// プロトコルタイプのURLへの接続
		$urlConnect = $this->connect($url);

		// データの送信
		$retData = $this->sendData($urlConnect, $params);

		// 戻り値の取り出し
		$retData = $this->recvData($retData);

		// プロトコルタイプのURLへの接続を解除
		$this->disconnect($urlConnect);

		$this->log->debug("戻り値 : $retData");

		if (!$retData) {
			return null;
		}

		// 戻り値を解析
		$parser = new ParamParser();
		$resultMap = $parser->parse($retData);
		$resultMap = new IgnoreCaseMap($resultMap);

		return $resultMap;
	}

	/**
	 * プロトコルタイプを呼び出し、結果を返す。
	 * 呼び出し先のURLはクラス名をもとに取得する。
	 *
	 * @param string $params    プロトコルタイプへ送信するパラメータ文字列
	 * @return IgnoreCaseMap 出力パラメータマップ
	 * @exception GPayException
	 */
	function callProtocol($params) {
		// URLを取得
		$urlMap = new ConnectUrlMap();
		$key = get_class($this);
		$url = $urlMap->getUrl($key);

		$this->log->debug("キー値 : $key  取得URL : $url");


		// URLを取得できなかったときはエラーとする
		if (is_null($url)) {
			$this->exception =
				new GPayException("呼び出し先のURLを取得できませんでした。[$key]", $this->exception);
			return null;
		}
		
		//更新者として、製品バージョンを設定
		return $this->callProtocol_($url, $params . '&User=' . $this->user . '&Version=' . $this->version );
	}

	/**
	 * 例外の発生を判定する
	 *
	 * @return boolean 判定結果(true = 例外発生)
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

}
?>