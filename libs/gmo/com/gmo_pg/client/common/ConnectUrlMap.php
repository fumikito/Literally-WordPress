<?php
require_once 'com/gmo_pg/client/common/IgnoreCaseMap.php';
require_once 'com/gmo_pg/client/common/Log.php';

/**
 * <b>接続先URLマップ</b>
 * 
 *   各種APIがGMO-PGのサーバーへ通信する時に、APIと対応したURLを取得します。<br />
 * conf/connectors.propertiesから取得します。
 * 
 * @package com.gmo_pg.client
 * @subpackage common
 * @see commonPackageInfo.php
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 01-01-2008 00:00:00
 */
class ConnectUrlMap {

	/**
	 * @var IgnoreCaseMap conf/connectors.propertiesをパースした結果のマップ
	 */
	var $urlMap;
	
	/**
	 * @var Log GMO-PG独自のログクラス
	 */
	var $log;   

	/**
	 * コンストラクタ
	 */
	function ConnectUrlMap() {
		$this->__construct();
	}
	

	/**
	 * コンストラクタ
	 */
	function __construct() {
		// 初期化
        $this->log = new Log(get_class($this));
        $this->init();
	}
	

	/**
	 * 初期化
	 * 
	 * <p>conf/connectors.propertiesを読み込み、$urlMapプロパティに格納します。</p>
	 */
	function init() {
	    // include_pathを区切り文字で分割
        // ※get_include_path()はPHP4.3.0以降で動作します。
        $pathArray = split(PATH_SEPARATOR, get_include_path());

        $prop_path = "conf/connector.properties";
        $prop = null;

        foreach ($pathArray as $value) {
            // プロパティファイルが存在する場合は読込を行う
            if (file_exists("$value/$prop_path")) {
                $prop = parse_ini_file("$value/$prop_path");
                break;
            }
        }
        
        // 読込処理の結果がnullの場合、処理を打ち切る。
        if (is_null($prop)) {
            $this->log->warn("プロパティファイルが見つかりません。");
			return;	
        }

        // キーを全て小文字に変換したマップクラスとして保持
        $this->urlMap = new IgnoreCaseMap($prop);
	}

    /**
     * 接続文字列取得
     *
     * <p>
     *  引数で渡されたキーで、自身の$urlMapプロパティを検索し、接続URLを取得します。<br/>
     * 該当するキーが存在しない場合、nullを返します。
     * </p>
     * 
     * @param string $key 接続API名称(トランザクションクラスの名称)
     * 
     * @return string 接続先URL
     */
    function getUrl($key) {
        if (is_null($this->urlMap)) {
            $this->log->warn("URL情報マップが存在しません。");
            return null;
        }
        return $this->urlMap->get($key);
    }
}
?>