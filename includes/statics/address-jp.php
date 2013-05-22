<?php
class LWP_Address_JP{
	
	/**
	 * 日本の都道府県の配列
	 * @var array
	 */
	private static $prefs = array(
		"北海道",
		"青森県",
		"岩手県",
		"宮城県",
		"秋田県",
		"山形県",
		"福島県",
		"茨城県",
		"栃木県",
		"群馬県",
		"埼玉県",
		"千葉県",
		"東京都",
		"神奈川県",
		"新潟県",
		"富山県",
		"石川県",
		"福井県",
		"山梨県",
		"長野県",
		"岐阜県",
		"静岡県",
		"愛知県",
		"三重県",
		"滋賀県",
		"京都府",
		"大阪府",
		"兵庫県",
		"奈良県",
		"和歌山県",
		"鳥取県",
		"島根県",
		"岡山県",
		"広島県",
		"山口県",
		"徳島県",
		"香川県",
		"愛媛県",
		"高知県",
		"福岡県",
		"佐賀県",
		"長崎県",
		"熊本県",
		"大分県",
		"宮崎県",
		"鹿児島県",
		"沖縄県"
	);
	
	/**
	 * Reginal list
	 * @var array
	 */
	private static $regioon = array(
		"北海道" => array(0),
		"東北" => array(1, 2, 3, 4, 5, 6),
		"関東" => array(7, 8, 9, 10, 11, 12, 13),
		"中部" => array(14, 15, 16, 17, 18, 19, 20, 21, 22, 23),
		"近畿" => array(24, 25, 26, 27, 28, 29),
		"中国" => array(30, 31, 32, 33, 34),
		"四国" => array(35, 36, 37, 38),
		"九州" => array(39, 40, 41, 42, 43, 44, 45, 46)
	);
	
	/**
	 * Returns all prefs
	 * @return type
	 */
	public static function get_prefs(){
		return self::$prefs;
	}

	/**
	 * Returns address group
	 * @return array
	 */
	public static function get_pref_group(){
		$prefs = array();
		foreach(self::$regioon as $reg => $ps){
			$prefs[$reg] = array();
			foreach($ps as $p){
				$prefs[$reg][] = self::$prefs[$p];
			}
		}
		return $prefs;
	}
}