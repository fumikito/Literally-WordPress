<?php
require_once 'com/gmo_pg/client/output/ErrHolder.php';

/**
 * <b>API返却パラメータ文字列パーサ</b>
 * 
 * GMO-PGの決済サーバーから返却された文字列をパースするためのクラス
 * 
 * @package com.gmo_pg.client
 * @subpackage common
 * @see commonPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 01-01-2008 00:00:00
 */
class ParamParser {
       
	/**
	 * パラメータ文字列解析
	 *
	 * @param  string $params    パラメータ文字列
	 * @return array paramsMap パラメータ文字列の連想配列
	 */
	function parse($params) {
	    // nullの場合は処理を行わない
	    if (is_null($params)) {
	        return null;
	    }
	    
	    // パラメータ文字列の分割
        $queryArray = explode('&', $params);
        
        // 分割した文字列を解析し、key,valueの形で格納する。
        $paramsMap = array();
	    foreach ($queryArray as $value) {
	        $splitArray = explode('=', $value, 2);  // 要素の最初の'='で2分割を行う  
	        if (2 == count($splitArray)) {
	            $paramsMap[$splitArray[0]] = $splitArray[1];
	            
	        }
	    }   
		return $paramsMap;
	}

	/**
	 * エラー情報解析
	 *
	 * @param  string $errCode  エラーコード文字列
	 * @param  string $errInfo  エラー詳細文字列
	 * @return array errList  errHolderを格納したリスト
	 * 
	 * @see ErrHolder
	 */
	function errParse($errCode, $errInfo) {
	    $unKnown = 'unKnown';
	    
	    // 文字列を'|'で分割
        $errCodeArray = explode("|", $errCode);  // errCodeの配列
        $errInfoArray = explode("|", $errInfo);  // errInfoの配列

	    // 配列の長さを格納
	    $codeLength = count($errCodeArray);
	    $infoLength = count($errInfoArray);
	    
	    // 配列サイズが異なる場合、大きい側をサイズとして扱う
	    $length = ($codeLength >= $infoLength) ? $codeLength : $infoLength;
	    $errList = array();
	    
		for ($i = 0; $i < $length; $i++) {
	        $errHolder = new ErrHolder();
	        
	        // errCode/Infoが不足している場合は'unKnown'文字列で埋める
	        if ($i > $codeLength - 1) {
	            $errHolder->setErrCode($unKnown);
	            $errHolder->setErrInfo($errInfoArray[$i]);
	        } elseif ($i > $infoLength - 1) {
	            $errHolder->setErrCode($errCodeArray[$i]);
	            $errHolder->setErrInfo($unKnown);
	        } else {
	            // 通常は配列値をセットする
	            $errHolder->setErrCode($errCodeArray[$i]);
	            $errHolder->setErrInfo($errInfoArray[$i]);
	        }
            $errList[] = $errHolder;
		}
		
		return $errList;
	}
}
?>