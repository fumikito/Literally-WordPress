<?php

require_once 'com/gmo_pg/client/common/GPayException.php';
require_once 'com/gmo_pg/client/common/ConnectUrlMap.php';

/**
 * <b>リダイレクトページ生成</b>
 * 
 * Acsにリダイレクトするページを生成します。決済実行して、Acsフラグがオンで返却された場合に
 * 利用します。
 * 
 * @package com.gmo_pg.client
 * @subpackage common
 * @see commonPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 01-01-2008 00:00:00
 */
class RedirectUtil {

    /**
     * @var GPayException 例外
     */
    var $exception;

    /**
     * コンストラクタ
     */
    function RedirectUtil() {
    }

    /**
     * リダイレクトページの内容を作成する
     *
     * @param string $pagePath    雛形ページファイルへのパス
     * @param AcsParam param    ACSパラメータ
     * @param string $encode    雛形ページファイルの文字コード
     * @reutnr string 雛形htmlの文字列
     */
    function createRedirectPage($pagePath, $param, $encode = null) {
        $acsUrl = $param->getAcsUrl();
        $paReq = $param->getPaReq();
        $termUrl = $param->getTermUrl();
        $md = $param->getMd();

        if (empty($acsUrl) || empty($md) || empty($paReq) || empty($termUrl)) {
            $this->exception =
                new GPayException("必須ACSパラメータに値が入っていません。", $this->exception);
            return null;
        }

        // 雛形ページファイル読込
        // ※file_get_contents()はPHP4.3.0以降で動作します。
        $strPage = file_get_contents($pagePath, true);
        if (!$strPage) {
            $this->exception =
                new GPayException("リダイレクトページの作成に失敗しました。", $this->exception);
            return null;
        }

        // $encodeが指定されていれば指定文字コードへ変換
        if (!is_null($encode)) {
            $strPage = mb_convert_encoding($strPage, $encode, 'EUC-JP,UTF-8,SJIS,ASCII');
        }

        // 雛形ページ中のパラメータ項目を置き換え
        $strPage = str_replace('${ACSUrl}', $acsUrl, $strPage);
        $strPage = str_replace('${PaReq}', $paReq, $strPage);
        $strPage = str_replace('${TermUrl}', $termUrl, $strPage);
        $strPage = str_replace('${MD}', $md, $strPage);

        return $strPage;
    }
    
    /**
     * リダイレクトページの内容を作成する
     *
     * @param string $pagePath    雛形ページファイルへのパス
     * @param PaypalStartParam param    Paypal支払開始パラメタ
     * @param string $encode    雛形ページファイルの文字コード
     * @reutnr string 雛形htmlの文字列
     */
    function paypalStart($pagePath, $param, $encode = null) {
    	
        $shopId = $param->getShopId();
        $accessId = $param->getAccessId();

        if (empty($shopId) || empty($accessId)) {
            $this->exception =
                new GPayException("必須Paypal支払開始パラメータに値が入っていません。", $this->exception);
            return null;
        }

        // 雛形ページファイル読込
        // ※file_get_contents()はPHP4.3.0以降で動作します。
        $strPage = file_get_contents($pagePath, true);
        if (!$strPage) {
            $this->exception =
                new GPayException("リダイレクトページの作成に失敗しました。", $this->exception);
            return null;
        }

        // $encodeが指定されていれば指定文字コードへ変換
        if (!is_null($encode)) {
            $strPage = mb_convert_encoding($strPage, $encode, 'EUC-JP,UTF-8,SJIS,ASCII');
        }

  		$urlMap = new ConnectUrlMap();
		$url = $urlMap->getUrl('PaypalStart');
        // 雛形ページ中のパラメータ項目を置き換え
        $strPage = str_replace('${PaypalStartUrl}', $url, $strPage);
        $strPage = str_replace('${ShopID}', $shopId, $strPage);
        $strPage = str_replace('${AccessID}', $accessId, $strPage);

        return $strPage;
    }

    /**
     * リダイレクトページの内容を作成する
     *
     * @param string $pagePath    雛形ページファイルへのパス
     * @param WebmoneyStartParam param    Webmoney支払開始パラメタ
     * @param string $encode    雛形ページファイルの文字コード
     * @reutnr string 雛形htmlの文字列
     */
    function webmoneyStart($pagePath, $param, $encode = null) {
    	
        $accessId = $param->getAccessId();

        if (empty($accessId)) {
            $this->exception =
                new GPayException("必須Webmoney支払開始パラメータに値が入っていません。", $this->exception);
            return null;
        }

        // 雛形ページファイル読込
        // ※file_get_contents()はPHP4.3.0以降で動作します。
        $strPage = file_get_contents($pagePath, true);
        if (!$strPage) {
            $this->exception =
                new GPayException("リダイレクトページの作成に失敗しました。", $this->exception);
            return null;
        }

        // $encodeが指定されていれば指定文字コードへ変換
        if (!is_null($encode)) {
            $strPage = mb_convert_encoding($strPage, $encode, 'EUC-JP,UTF-8,SJIS,ASCII');
        }

  		$urlMap = new ConnectUrlMap();
		$url = $urlMap->getUrl('WebmoneyStart');
        // 雛形ページ中のパラメータ項目を置き換え
        $strPage = str_replace('${WebmoneyStartUrl}', $url, $strPage);
        $strPage = str_replace('${AccessID}', $accessId, $strPage);

        return $strPage;
    }

    /**
     * リダイレクトページの内容を作成する
     *
     * @param string $pagePath    雛形ページファイルへのパス
     * @param string $startURL    支払手続き開始IFのURL
     * @param string $accessID    取引ID
     * @param string $token       トークン
     * @param string $encode    雛形ページファイルの文字コード
     * @reutnr string 雛形htmlの文字列
     */
    function auStart($pagePath, $startURL, $accessID, $token, $encode = null) {
		if (
			empty($startURL) ||
			empty($accessID) ||
			empty($token)
		) {
            $this->exception =
                new GPayException("必須auかんたん決済OpenID連携パラメータに値が入っていません。", $this->exception);
            return null;
        }

        // 雛形ページファイル読込
        // ※file_get_contents()はPHP4.3.0以降で動作します。
        $strPage = file_get_contents($pagePath, true);
        if (!$strPage) {
            $this->exception =
                new GPayException("リダイレクトページの作成に失敗しました。", $this->exception);
            return mb_convert_encoding("リダイレクトページの作成に失敗しました。", 'SJIS', 'UTF-8');
        }

        // $encodeが指定されていれば指定文字コードへ変換
        if (!is_null($encode)) {
            $strPage = mb_convert_encoding($strPage, $encode, 'EUC-JP,UTF-8,SJIS,ASCII');
        }

  		$urlMap = new ConnectUrlMap();
		$url = $urlMap->getUrl('AuStart');
        // 雛形ページ中のパラメータ項目を置き換え
		$strPage = str_replace('${StartURL}', $startURL, $strPage);
		$strPage = str_replace('${AccessID}', $accessID, $strPage);
		$strPage = str_replace('${Token}', $token, $strPage);

        return $strPage;
    }

	/**
	 * リダイレクトページの内容を作成する
	 *
	 * @param string $pagePath    雛形ページファイルへのパス
	 * @param DocomoStartInput param    支払手続き開始パラメタ
	 * @param string $encode    雛形ページファイルの文字コード
	 * @reutnr string 雛形htmlの文字列
	 */
	function docomoStart($pagePath, $param, $encode = null) {
		$accessID = $param->getAccessID();
		$token = $param->getToken();

		if (
			empty($accessID) ||
			empty($token)
		) {
			$this->exception =
				new GPayException("必須ドコモケータイ払い支払手続き開始パラメータに値が入っていません。", $this->exception);
			return null;
		}

		// 雛形ページファイル読込
		// ※file_get_contents()はPHP4.3.0以降で動作します。
		$strPage = file_get_contents($pagePath, true);
		if (!$strPage) {
			$this->exception =
				new GPayException("リダイレクトページの作成に失敗しました。", $this->exception);
			return mb_convert_encoding("リダイレクトページの作成に失敗しました。", 'SJIS', 'UTF-8');
		}

		// $encodeが指定されていれば指定文字コードへ変換
		if (!is_null($encode)) {
			$strPage = mb_convert_encoding($strPage, $encode, 'EUC-JP,UTF-8,SJIS,ASCII');
		}

		$urlMap = new ConnectUrlMap();
		$url = $urlMap->getUrl('DocomoStart');
		// 雛形ページ中のパラメータ項目を置き換え
		$strPage = str_replace('${DocomoStartURL}', $url, $strPage);
		$strPage = str_replace('${AccessID}', $accessID, $strPage);
		$strPage = str_replace('${Token}', $token, $strPage);

		return $strPage;
	}


	/**
	 * 例外の発生を判定する
	 *
	 * @return  判定結果
	 */
	function isExceptionOccured() {
		return false == is_null($this->exception);
	}

}
?>
