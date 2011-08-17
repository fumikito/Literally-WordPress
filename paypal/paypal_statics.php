<?php

/**
 * PayPalとのインターフェースを作るクラス
 *
 * @package Literally WordPress
 * @since 0.8
 */
class PayPal_Statics {
	
	/**
	 * APIのバージョン
	 * @var string
	 */
	const VERSION = "74.0";
	
	/**
	 * ExpressCheckoutで行う支払いアクション
	 * @var string
	 */
	const PAYMENT_ACTION = "Sale";
	
	/**
	 * トランザクションを発生させ、トークンを取得する
	 * 
	 * @global Literally_WordPress $lwp
	 * @param int $paymentAmount
	 * @param string $invoice_number
	 * @param string $return_url
	 * @param string $cancel_url
	 * @return string 
	 */
	public function get_transaction_token($paymentAmount, $invoice_number, $return_url, $cancel_url) 
	{
		global $lwp;
		self::log(var_export($return_url, true));
		self::log(var_export($cancel_url, true));
		$return_url = rawurlencode($return_url);
		$cancel_url = rawurlencode($cancel_url);
		//SetExpressCheckout APIに投げる値を作成
		$nvpstr = "&AMT={$paymentAmount}&PAYMENTACTION=".self::PAYMENT_ACTION.
			"&RETURNURL={$return_url}&CANCELURL={$cancel_url}".
			"&CURRENCYCODE={$lwp->option['currency_code']}&LOCALECODE={$lwp->option['country_code']}".
			"&NOSHIPPING=1&LANDINGPAGE=Billing&ALLOWNOTE=1&INVNUM={$invoice_number}";
		//リクエストを取得
		$resArray = self::hash_call("SetExpressCheckout", $nvpstr);
		//レスポンスをチェック
		$ack = strtoupper($resArray["ACK"]);
		if($ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING"){
			return urldecode($resArray["TOKEN"]);
		}else{
			self::log(var_export($resArray, true));
			self::log(var_export($nvpstr, true));
			return false;
		}
	}
	
	/**
	 * 戻ってきたユーザーのトークンから詳細情報を取得する
	 * 
	 * @param string $token PayPalから戻ってきたときのトークン
	 * @return array
	 */
	public function get_transaction_info( $transaction_token )
	{
		$nvpstr = "&TOKEN=".$transaction_token;
		$resArray = self::hash_call("GetExpressCheckoutDetails",$nvpstr);
	    $ack = strtoupper($resArray["ACK"]);
		if($ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING"){	
			return $resArray;
		}else{
			return false;
		}
	}
	
	/**
	 * トランザクションを完了させる
	 * 
	 * @param array $transaction_info get_transaction_infoで取得した配列
	 * @return boolean
	 */
	public static function do_transaction($transaction_info)
	{
		$nvpstr  = '&TOKEN=' . $transaction_info['TOKEN'] . '&PAYERID=' . $transaction_info['PAYERID'] . '&PAYMENTACTION=' . self::PAYMENT_ACTION. '&AMT=' . $transaction_info['AMT'];
		$nvpstr .= '&CURRENCYCODE=' . $transaction_info['CURRENCYCODE'];
		$resArray = self::hash_call("DoExpressCheckoutPayment",$nvpstr);
		$ack = strtoupper($resArray["ACK"]);
		if( $ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING" ){
			return true;
		}else{
			self::log(var_export($resArray, true));
			return false;
		}
	}
	
	/**
	 * API認証を利用してPaypalに対してAPIコールを行う
	 * 
	 * @since 0.8
	 * @global Literally_WordPress $lwp
	 * @param string $methodName
	 * @param string $nvpStr
	 * @return array
	 */
	public static function hash_call($methodName, $nvpStr)
	{
		/** @var $lwp Literally_WordPress */
		global $lwp;
		//カールを初期化
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::api_endpoint());
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		//turning off the server and peer verification(TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		//cUrlの出力を消す
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		//POSTメソッドに設定
		curl_setopt($ch, CURLOPT_POST, 1);
		//サーバに送信するNVPリクエストを設定
		$nvpreq = "METHOD=" . urlencode($methodName) . "&VERSION=" . urlencode(self::VERSION) . "&PWD=" . urlencode($lwp->option['password']) . "&USER=" . urlencode($lwp->option['user_name']) . "&SIGNATURE=" . urlencode($lwp->option['signature']) . $nvpStr;
		//CurlのPOSTフィールドに入るように$nvpreqを設定
		curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);
		//サーバからのレスポンスを取得
		$response = curl_exec($ch);
		//NVP形式のレスポンスを連想配列に変換
		$nvpResArray = self::deformat_nvp($response);
		if (curl_errno($ch)){
			// エラーがあった場合はfalseを返す
			return false;
		}else {
			//エラーがなければCurlを終了して値を返す
			curl_close($ch);
			return $nvpResArray;
		}
	}
	
	/**
	 * NVP形式の文字列を連想配列に変換
	 * 
	 * @since 0.8
	 * @param string $nvpstr
	 * @return array
	 */
	private static function deformat_nvp($nvpstr)
	{
		$intial = 0;
	 	$nvpArray = array();
		while(strlen($nvpstr)){
			//postion of Key
			$keypos = strpos($nvpstr,'=');
			//position of value
			$valuepos = strpos($nvpstr,'&') ? strpos($nvpstr,'&'): strlen($nvpstr);
			/*getting the Key and Value values and storing in a Associative Array*/
			$keyval = substr($nvpstr,$intial,$keypos);
			$valval = substr($nvpstr,$keypos+1,$valuepos-$keypos-1);
			//decoding the respose
			$nvpArray[urldecode($keyval)] = urldecode( $valval);
			$nvpstr = substr($nvpstr,$valuepos+1,strlen($nvpstr));
	     }
		return $nvpArray;
	}
	
	/**
	 * APIエンドポイントのURLを返す
	 * 
	 * @since 0.8
	 * @global Literally_WordPress $lwp
	 * @return string 
	 */
	public static function api_endpoint(){
		global $lwp;
		return $lwp->option['sandbox'] ? "https://api-3t.sandbox.paypal.com/nvp"
									  : "https://api-3t.paypal.com/nvp";
	}
	
	/**
	 * PayPalのExpress Checkoutへリダイレクトする
	 * @param type $token 
	 */
	public static function redirect($token = ""){
		header("Location: ".self::url().$token);
	}
	
	/**
	 * PayPalへのURLを返す
	 * 
	 * @since 0.8
	 * @global Literally_WordPress $lwp
	 * @return string
	 */
	private static function url(){
		global $lwp;
		return $lwp->option['sandbox'] ? "https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token="
									  : "https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=";
	}
	
	/**
	 * PayPalで利用されている国別コードを返す
	 * 
	 * @since 0.8
	 * @see https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_country_codes
	 * @return array
	 */
	public static function country_codes(){
		return array(
			"AX" => self::_("ÅLAND ISLANDS"),
			"AL" => self::_("ALBANIA"),
			"DZ" => self::_("ALGERIA"),
			"AS" => self::_("AMERICAN SAMOA"),
			"AD" => self::_("ANDORRA"),
			"AI" => self::_("ANGUILLA"),
			"AQ" => self::_("ANTARCTICA"),
			"AG" => self::_("ANTIGUA AND BARBUDA"),
			"AR" => self::_("ARGENTINA"),
			"AM" => self::_("ARMENIA"),
			"AW" => self::_("ARUBA"),
			"AU" => self::_("AUSTRALIA"),
			"AT" => self::_("AUSTRIA"),
			"BS" => self::_("BAHAMAS"),
			"BH" => self::_("BAHRAIN"),
			"BB" => self::_("BARBADOS"),
			"BE" => self::_("BELGIUM"),
			"BZ" => self::_("BELIZE"),
			"BJ" => self::_("BENIN"),
			"BM" => self::_("BERMUDA"),
			"BT" => self::_("BHUTAN"),
			"BW" => self::_("BOTSWANA"),
			"BV" => self::_("BOUVET ISLAND"),
			"BR" => self::_("BRAZIL"),
			"IO" => self::_("BRITISH INDIAN OCEAN TERRITORY"),
			"BN" => self::_("BRUNEI DARUSSALAM"),
			"BG" => self::_("BULGARIA"),
			"BF" => self::_("BURKINA FASO"),
			"CA" => self::_("CANADA"),
			"CV" => self::_("CAPE VERDE"),
			"KY" => self::_("CAYMAN ISLANDS"),
			"CF" => self::_("CENTRAL AFRICAN REPUBLIC"),
			"CL" => self::_("CHILE"),
			"CN" => self::_("CHINA"),
			"CX" => self::_("CHRISTMAS ISLAND"),
			"CC" => self::_("COCOS (KEELING) ISLANDS"),
			"CO" => self::_("COLOMBIA"),
			"CK" => self::_("COOK ISLANDS"),
			"CR" => self::_("COSTA RICA"),
			"CY" => self::_("CYPRUS"),
			"CZ" => self::_("CZECH REPUBLIC"),
			"DK" => self::_("DENMARK"),
			"DJ" => self::_("DJIBOUTI"),
			"DM" => self::_("DOMINICA"),
			"DO" => self::_("DOMINICAN REPUBLIC"),
			"EG" => self::_("EGYPT"),
			"SV" => self::_("EL SALVADOR"),
			"EE" => self::_("ESTONIA"),
			"FK" => self::_("FALKLAND ISLANDS (MALVINAS)"),
			"FO" => self::_("FAROE ISLANDS"),
			"FJ" => self::_("FIJI"),
			"FI" => self::_("FINLAND"),
			"FR" => self::_("FRANCE"),
			"GF" => self::_("FRENCH GUIANA"),
			"PF" => self::_("FRENCH POLYNESIA"),
			"TF" => self::_("FRENCH SOUTHERN TERRITORIES"),
			"GM" => self::_("GAMBIA"),
			"GE" => self::_("GEORGIA"),
			"DE" => self::_("GERMANY"),
			"GH" => self::_("GHANA"),
			"GI" => self::_("GIBRALTAR"),
			"GR" => self::_("GREECE"),
			"GL" => self::_("GREENLAND"),
			"GD" => self::_("GRENADA"),
			"GP" => self::_("GUADELOUPE"),
			"GU" => self::_("GUAM"),
			"GG" => self::_("GUERNSEY"),
			"HM" => self::_("HEARD ISLAND AND MCDONALD ISLANDS"),
			"VA" => self::_("HOLY SEE (VATICAN CITY STATE)"),
			"HN" => self::_("HONDURAS"),
			"HK" => self::_("HONG KONG"),
			"HU" => self::_("HUNGARY"),
			"IS" => self::_("ICELAND"),
			"IN" => self::_("INDIA"),
			"ID" => self::_("INDONESIA"),
			"IE" => self::_("IRELAND"),
			"IM" => self::_("ISLE OF MAN"),
			"IL" => self::_("ISRAEL"),
			"IT" => self::_("ITALY"),
			"JM" => self::_("JAMAICA"),
			"JP" => self::_("JAPAN"),
			"JE" => self::_("JERSEY"),
			"JO" => self::_("JORDAN"),
			"KZ" => self::_("KAZAKHSTAN"),
			"KI" => self::_("KIRIBATI"),
			"KR" => self::_("KOREA, REPUBLIC OF"),
			"KW" => self::_("KUWAIT"),
			"KG" => self::_("KYRGYZSTAN"),
			"LV" => self::_("LATVIA"),
			"LS" => self::_("LESOTHO"),
			"LI" => self::_("LIECHTENSTEIN"),
			"LT" => self::_("LITHUANIA"),
			"LU" => self::_("LUXEMBOURG"),
			"MO" => self::_("MACAO"),
			"MW" => self::_("MALAWI"),
			"MY" => self::_("MALAYSIA"),
			"MT" => self::_("MALTA"),
			"MH" => self::_("MARSHALL ISLANDS"),
			"MQ" => self::_("MARTINIQUE"),
			"MR" => self::_("MAURITANIA"),
			"MU" => self::_("MAURITIUS"),
			"YT" => self::_("MAYOTTE"),
			"MX" => self::_("MEXICO"),
			"FM" => self::_("MICRONESIA, FEDERATED STATES OF"),
			"MD" => self::_("MOLDOVA, REPUBLIC OF"),
			"MC" => self::_("MONACO"),
			"MN" => self::_("MONGOLIA"),
			"MS" => self::_("MONTSERRAT"),
			"MA" => self::_("MOROCCO"),
			"MZ" => self::_("MOZAMBIQUE"),
			"NA" => self::_("NAMIBIA"),
			"NR" => self::_("NAURU"),
			"NP" => self::_("NEPAL"),
			"NL" => self::_("NETHERLANDS"),
			"AN" => self::_("NETHERLANDS ANTILLES"),
			"NC" => self::_("NEW CALEDONIA"),
			"NZ" => self::_("NEW ZEALAND"),
			"NI" => self::_("NICARAGUA"),
			"NE" => self::_("NIGER"),
			"NU" => self::_("NIUE"),
			"NF" => self::_("NORFOLK ISLAND"),
			"MP" => self::_("NORTHERN MARIANA ISLANDS"),
			"NO" => self::_("NORWAY"),
			"OM" => self::_("OMAN"),
			"PW" => self::_("PALAU"),
			"PA" => self::_("PANAMA"),
			"PY" => self::_("PARAGUAY"),
			"PE" => self::_("PERU"),
			"PH" => self::_("PHILIPPINES"),
			"PN" => self::_("PITCAIRN"),
			"PL" => self::_("POLAND"),
			"PT" => self::_("PORTUGAL"),
			"PR" => self::_("PUERTO RICO"),
			"QA" => self::_("QATAR"),
			"RE" => self::_("REUNION"),
			"RO" => self::_("ROMANIA"),
			"SH" => self::_("SAINT HELENA"),
			"KN" => self::_("SAINT KITTS AND NEVIS"),
			"LC" => self::_("SAINT LUCIA"),
			"PM" => self::_("SAINT PIERRE AND MIQUELON"),
			"VC" => self::_("SAINT VINCENT AND THE GRENADINES"),
			"WS" => self::_("SAMOA"),
			"SM" => self::_("SAN MARINO"),
			"ST" => self::_("SAO TOME AND PRINCIPE"),
			"SA" => self::_("SAUDI ARABIA"),
			"SN" => self::_("SENEGAL"),
			"SC" => self::_("SEYCHELLES"),
			"SG" => self::_("SINGAPORE"),
			"SK" => self::_("SLOVAKIA"),
			"SI" => self::_("SLOVENIA"),
			"SB" => self::_("SOLOMON ISLANDS"),
			"ZA" => self::_("SOUTH AFRICA"),
			"GS" => self::_("SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS"),
			"ES" => self::_("SPAIN"),
			"SR" => self::_("SURINAME"),
			"SJ" => self::_("SVALBARD AND JAN MAYEN"),
			"SZ" => self::_("SWAZILAND"),
			"SE" => self::_("SWEDEN"),
			"CH" => self::_("SWITZERLAND"),
			"TW" => self::_("TAIWAN, PROVINCE OF CHINA"),
			"TZ" => self::_("TANZANIA, UNITED REPUBLIC OF"),
			"TH" => self::_("THAILAND"),
			"TK" => self::_("TOKELAU"),
			"TO" => self::_("TONGA"),
			"TT" => self::_("TRINIDAD AND TOBAGO"),
			"TN" => self::_("TUNISIA"),
			"TR" => self::_("TURKEY"),
			"TC" => self::_("TURKS AND CAICOS ISLANDS"),
			"TV" => self::_("TUVALU"),
			"UA" => self::_("UKRAINE"),
			"AE" => self::_("UNITED ARAB EMIRATES"),
			"GB" => self::_("UNITED KINGDOM"),
			"US" => self::_("UNITED STATES"),
			"UM" => self::_("UNITED STATES MINOR OUTLYING ISLANDS"),
			"UY" => self::_("URUGUAY"),
			"VN" => self::_("VIET NAM"),
			"VG" => self::_("VIRGIN ISLANDS, BRITISH"),
			"VI" => self::_("VIRGIN ISLANDS, U.S."),
			"WF" => self::_("WALLIS AND FUTUNA"),
			"ZM" => self::_("ZAMBIA")
		);
	}
	
	/**
	 * 通貨コードを返す
	 * 
	 * @see https://www.x.com/docs/DOC-1156
	 * @since 0.8
	 * @return array
	 */
	public static function currency_codes(){
		return array(
			"AUD" => self::_("Australian Dollar"),
			"BRL" => self::_("Brazilian Real"),
			"CAD" => self::_("Canadian Dollar"),
			"CZK" => self::_("Czech Koruna"),
			"DKK" => self::_("Danish Krone"),
			"EUR" => self::_("Euro"),
			"HKD" => self::_("Hong Kong Dollar"),
			"HUF" => self::_("Hungarian Forint"),
			"ILS" => self::_("Israeli New Sheqel"),
			"JPY" => self::_("Japanese Yen"),
			"MYR" => self::_("Malaysian Ringgit"),
			"MXN" => self::_("Mexican Peso"),
			"NOK" => self::_("Norwegian Krone"),
			"NZD" => self::_("New Zealand Dollar"),
			"PHP" => self::_("Philippine Peso"),
			"PLN" => self::_("Polish Zloty"),
			"GBP" => self::_("Pound Sterling"),
			"SGD" => self::_("Singapore Dollar"),
			"SEK" => self::_("Swedish Krona"),
			"CHF" => self::_("Swiss Franc"),
			"TWD" => self::_("Taiwan New Dollar"),
			"THB" => self::_("Thai Baht"),
			"USD" => self::_("U.S. Dollar")
		);
	}
	
	/**
	 * 通貨記号をHTMLエンティティで返す
	 * 
	 * @see http://webdesign.about.com/od/localization/l/blhtmlcodes-cur.htm
	 * @since 0.8
	 * @param string $currency_code
	 * @return string
	 */
	public static function currency_entity($currency_code){
		switch($currency_code){
			case "JPY":
				return "&yen;";
				break;
			case "AUD":
			case "USD":
			case "NZD":
			case "SGD":
			case "TWD":
			case "CAD":
			case "HKD":
				return "$";
				break;
			case "EUR":
				return "&euro;";
				break;
			case "GBP":
				return "&pound;";
				break;
			case "CHF":
				return "&#8355;";
				break;
			default:
				return "&curren;";
				break;
		}
	}
	
	/**
	 * gettextのエイリアス
	 * @global Literally_WordPress $lwp
	 * @param string $string
	 * @return string 
	 */
	private static function _($string){
		/** @var $lwp Literally_WordPress*/
		global $lwp;
		return __($string, $lwp->domain);
	}
	
	/**
	 * ログを書き込む
	 * @param string $string
	 * @return void
	 */
	private static function log($string){
		//ファイルの存在を確認
		$dir = dirname(__FILE__);
		$file = $dir.DIRECTORY_SEPARATOR."log.txt";
		if(file_exists($file)){
			if(!is_writable($file)){
				return false;
			}
		}else{
			if(is_writable($dir)){
				file_put_contents($file, '');
			}else{
				return false;
			}
		}
		//ログを書き込み
		$date = date('Y-m-d H:i:s');
		$string = "[{$date}]\n".$string."\n\n";
		file_put_contents($file, $string, FILE_APPEND);
	}
}

?>
