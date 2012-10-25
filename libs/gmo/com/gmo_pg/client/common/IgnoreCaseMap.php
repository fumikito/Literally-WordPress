<?php
require_once 'com/gmo_pg/client/common/Log.php';

/**
 * <b>連想配列(キーの大小文字無視)</b>
 * 
 * キー大文字小文字を意識しない、連想配列クラスです。
 * 
 * @package com.gmo_pg.client
 * @subpackage common
 * @author GMO PaymentGateway
 * @see commonPackageInfo.php 
 * @version 1.0
 * @created 01-01-2008 00:00:00
 */
class IgnoreCaseMap {

	/**
	 * @var array このクラスがラップする配列
	 */
	var $map;
	
	/**
	 * @var Log 独自ログクラス
	 */
	var $log;
	
	
	/**
	 * コンストラクタ
	 *
	 * @param array $src  このクラスがラップする配列
	 */
	function IgnoreCaseMap($src = null) {
		$this->__construct($src);
	}

	/**
	 * コンストラクタ
	 *
	 * @param array $src  このクラスがラップする配列
	 */
	function __construct($src = null) {
	    $this->log = new Log(get_class($this));
	    $this->map = array();
	    
        if (isset($src)) {
            // 引数が渡された場合、キーを全て小文字にしたマップを作成
            $this->putAll($src);
        }
	}

	/**
	 * 要素設定
	 *
	 * <p>
	 *  指定された$key=>$valueの組み合わせで、自身の$mapプロパティに要素を追加します。
	 * </p>
	 * 
	 * @param mixed $key    キーとなる値
	 * @param mixed $value    設定する値
	 *
	 */
	function put($key, $value) {
	    $this->map[$this->toLowerKey($key)] = $value;
	}

	/**
	 * 要素取得
	 *
	 * @param mixed $key 検索キー
	 * @return mixed キーに結びつく要素(存在しない場合はnull)
	 */
	function get($key) {
	    // キー値の存在確認
	    if (!($this->ensure($key))) {
	        return null;
	    }
	    // 要素の取得     
	    return $this->map[$this->toLowerKey($key)];
	}

	/**
	 * キー値存在判定
	 * <p>
	 * 指定のキー値が存在するかどうかを判定する
	 * </p>
	 * @param mixed $key    キー値
	 * @return boolean 存在する場合true
	 */
	function containsKey($key) {
	    // ※array_key_exists()はPHP4.1.0以降で動作します
        return array_key_exists($this->toLowerKey($key), $this->map);
	}

	/**
	 * 要素削除
	 *
	 * <p>
	 *  指定されたキーとその値を削除します。
	 * </p>
	 * @param mixed $key    キー値
	 */
	function remove($key) {
	    // キーが存在しない場合は処理をしない
		if (!($this->ensure($key))) {
	        return;
	    }
	    // 要素の削除
        unset($this->map[$this->toLowerKey($key)]);
	}

	/**
	 * 要素一式追加
	 * 
	 * <p>
	 *   パラメータで渡した配列を、自身の$mapオブジェクトとマージします。
	 * </p>
	 * @param array $other    別のマップ
	 */
	function putAll($other) {
	    if (!isset($other)) {
	        return;
	    }
	    
	    // 追加するマップのキーを全て小文字に変換
        // ※array_change_key_case()はPHP4.2.0以降で動作します。
	    $addmap = array_change_key_case($other, CASE_LOWER);
	    // 要素の追加
	    $this->map = array_merge($this->map, $addmap);
	}
	
	/**
	 * マップのサイズ取得
	 * 
	 * @return integer 保持しているマップのサイズ
	 */
	function size() {
	    return count($this->map);
	}
	
	
	/**
	 * キー値の存在確認
	 * 
	 * <p>
	 * containsKeyとの違い：指定のキー値が存在しないときは警告ログを出力します。
	 * </p>
	 * @param mixed $key キー値
	 * @return boolean 存在する場合true
	 */
	function ensure($key) {
	    if (false == $this->containsKey($key)) {
	        $this->log->debug("指定のキーは存在しません: key[ $key ]");
	        return false;
	    }
	    return true;
	}
	
	/**
	 * 文字列表現の小文字変換
	 * 
	 * @param string $key 指定のキー値
	 * @return string 文字列表現を小文字に変換した値
	 */
	function toLowerKey($key) {
		if (is_null($key)) {
			return null;
		}
		return strtolower($key);
	}
}
?>