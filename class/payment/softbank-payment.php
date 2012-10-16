<?php
class LWP_SB_Payment extends Literally_WordPress_Common {
	
	/**
	 * Endpoint
	 */
	const PAYMENT_ENDPOINT = 'https://stbfep.sps-system.com/api/xmlapi.do';
	
	/**
	 * Server of Softbank
	 */
	const SOFTBANK_IP_ADDRESS = '61.215.213.47';
	
	const PAYMENT_REQUEST_CODE = 'ST01-00101-101';
	
	const PAYMENT_FIX_CODE = 'ST02-00101-101';
	
	/**
	 * creditcard list
	 * @var array
	 */
	private $_creditcard = array(
		'visa' => false,
		'master' => false,
		'jcb' => false,
		'amex' => false,
		'diners' => false
	);
	
	/**
	 * CVS list
	 * @var array 
	 */
	private $_webcvs = array(
		'seven-eleven' => false,
		'lawson' => false,
		'circle-k' => false,
		'sunkus' => false,
		'ministop' => false,
		'familymart' => false,
		'seicomart' => false
	);
	
	public $payeasy = false;
	
	public $is_sandbox = true;
	
	public $iv = '';
	
	public $crypt_key = '';
	
	public $prefix = '';
	
	private $_marchant_id = '';
	
	private $_service_id = '';
	
	private $_hash_key = '';
	
	private $_sandbox_marchant_id = '62022';
	
	private $_sandbox_service_id = '001';
	
	private $_sandbox_hash_key = 'd7b6640cc5d286cbceab99b53bf74870cf6bce9c';
	
	private $base64_key = array(
		'item_name', 'free1', 'free2', 'free3', 'dtl_item_name'
	);
	
	/**
	 * Last error message
	 * @var string
	 */
	public $last_error = '';
	
	public function set_option($option = array()) {
		$option = shortcode_atts(array(
			'sb_creditcard' => array(),
			'sb_webcvs' => array(),
			'sb_payeasy' => false,
			'sb_sandbox' => true,
			'sb_marchant_id' => '',
			'sb_service_id' => '',
			'sb_crypt_key' => '',
			'sb_iv' => '',
			'sb_hash_key' => '',
			'sb_prefix' => ''
		), $option);
		foreach($this->_creditcard as $cc => $bool){
			$this->_creditcard[$cc] = (false !== array_search($cc, (array)$option['sb_creditcard']));
		}
		foreach($this->_webcvs as $cvs => $bool){
			$this->_webcvs[$cvs] = (false !== array_search($cvs, (array)$option['sb_webcvs']));
		}
		$this->payeasy = (boolean)$option['sb_payeasy'];
		$this->is_sandbox = (boolean)$option['sb_sandbox'];
		$this->_marchant_id = (string)$option['sb_marchant_id'];
		$this->_service_id = (string)$option['sb_service_id'];
		$this->_hash_key = (string)$option['sb_hash_key'];
		$this->prefix = (string)$option['sb_prefix'];
		$this->iv = (string)$option['sb_iv'];
		$this->crypt_key = (string)$option['sb_crypt_key'];
	}
	
	/**
	 * Create transaction.
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param int $user_id
	 * @param string $item_name
	 * @param int $post_id
	 * @param float $price
	 * @param int $quantity
	 * @param string $cc_number
	 * @param string $cc_sec
	 * @param string $expiration
	 * @return boolean
	 */
	public function do_credit_authorization($user_id, $item_name, $post_id, $price, $quantity, $cc_number, $cc_sec, $expiration){
		global $lwp, $wpdb;
		$now = gmdate('Y-m-d H:i:s');
		$order_id = $this->generate_order_id();
		//Make payment request
		$xml_array = array(
			'merchant_id' => $this->marchant_id(),
			'service_id' => $this->service_id(),
			'cust_code' => $this->generate_user_id($user_id),
			'order_id' => $order_id,
			'item_id' => $this->generate_user_id($post_id),
			'item_name' => $item_name,
			'tax' => 0,
			'amount' => $price,
			'free1' => '',
			'free2' => '',
			'free3' => '',
			'order_rowno' => 1,
			'sps_cust_info_return_flg' => 1,
			'dtls' => ''/*array(
				'dtl' => array(
					'dtl_rowno' => 1,
					'dtl_item_id' => $post_id,
					'dtl_item_name' => $item_name,
					'dtl_item_count' => $quantity,
					'dtl_tax' => 0,
					'dtl_amount' => $price
				)
			)*/,
			'pay_method_info' => array(
				'cc_number' => $cc_number,
				'cc_expiration' => $expiration,
				'security_code' => $cc_sec,
				'cust_manage_flg' => 0
			),
			'encrypted_flg' => intval(!$this->is_sandbox),
			'request_date' => mysql2date("YmdHis", get_date_from_gmt($now)),
			'limit_second' => 60
		);
		$hash_key = $this->get_hash_key_from_array($xml_array);
		$xml_array['sps_hashcode'] = $hash_key;
		$result = $this->get_request($this->make_xml(self::PAYMENT_REQUEST_CODE, $xml_array));
		var_dump($result);
		if(!$result['success']){
			$this->last_error = $result['message'];
			return false;
		}else{
			$xml = $result['body'];
			$commit_array = array(
				'merchant_id' => $this->marchant_id(),
				'service_id' => $this->service_id(),
				'sps_transaction_id' => $xml->res_sps_transaction_id,
				'tracking_id' => $xml->res_tracking_id,
				'processing_datetime' => '',
				'request_date' => date('YmdHis'),
				'limit_second' => ''
			);
			$hash_key = $this->get_hash_key_from_array($commit_array);
			$commit_array['sps_hashcode'] = $hash_key;
			$commit_result = $this->get_request($this->make_xml(self::PAYMENT_FIX_CODE, $commit_array));
			var_dump($commit_result);
			if($commit_result['success']){
				$status = LWP_Payment_Status::SUCCESS;
			}else{
				$status = LWP_Payment_Status::CANCEL;
				$this->last_error = $this->_('Cannot commit transaction.');
			}
			$wpdb->insert($lwp->transaction,array(
				"user_id" => $user_id,
				"book_id" => $post_id,
				"price" => $price,
				"status" => $status,
				"method" => LWP_Payment_Methods::SOFTBANK_CC,
				"transaction_key" => $order_id,
				'transaction_id' => $xml->res_sps_transaction_id,
				'payer_mail' => $xml->res_tracking_id,
				"registered" => $now,
				"updated" => $now
			), array('%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ));
			return $commit_result['success'];
		}
	}
	
	/**
	 * Generate uniq transaction ID
	 * @return string
	 */
	public function generate_order_id(){
		return uniqid(sprintf('%s-%02d-', $this->prefix, rand(0,99)), true);
	}
	
	/**
	 * Generate user ID with prefix
	 * @param int $user_id
	 * @return string
	 */
	public function generate_user_id($user_id){
		return sprintf('%s-%d', $this->prefix, $user_id);
	}
	
	/**
	 * Return string to XML and get request.
	 * @param string $xml
	 * @return array contains from success, message, body
	 */
	private function get_request($xml){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::PAYMENT_ENDPOINT);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERPWD, $this->marchant_id().$this->service_id().":".$this->hash_key());
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
		curl_setopt($ch, CURLOPT_SSLVERSION, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		$result = curl_exec($ch);
		$response = array(
			'success' => false,
			'message' => '',
			'body' => null
		);
		if(curl_errno($ch) > 0){
			$response['message'] = $this->_('Sorry, but connection is failed. Please try again later.')."\n ". curl_errno($ch).": ". curl_error($ch);
		}else{
			$xml_string = preg_replace("/(encoding=\")Shift_JIS(\")/", "$1utf-8$2", mb_convert_encoding($result, 'utf-8', 'sjis-win'));
			$xml = simplexml_load_string($xml_string);
			if($xml){
				if($xml->res_result == 'OK'){
					$response['success'] = true;
					$response['body'] = $xml;
				}else{
					$code = substr($xml->res_err_code, 3, 2);
					if(array_key_exists($code, $this->error_msg)){
						$response['message'] = $this->error_msg[$code];
					}else{
						$response['message'] = '$code'.$this->_('Sorry, but connection is failed. Please try again later.');
					}
				}
			}else{
				$response['message'] = "XML".$this->_('Sorry, but connection is failed. Please try again later.');
			}
		}
		curl_close($ch);
		return $response;
	}
	
	/**
	 * Create XML String
	 * @param string $action
	 * @param array $xml_array
	 * @return string
	 */
	private function make_xml($action, $xml_array){
		$tag = $this->make_tag('', $xml_array);
		$xml = '<?xml version="1.0" encoding="Shift_JIS" ?>'.
			sprintf('<sps-api-request id="%s">', $action);
		$xml .= $tag."</sps-api-request>";
		return mb_convert_encoding($xml, 'sjis-win', 'utf-8');
	}
	
	/**
	 * Convert array to XML string
	 * @param string $key
	 * @param string|array $value
	 * @return string
	 */
	private function make_tag($key, $value){
		if(is_array($value)){
			$tag = '';
			foreach($value as $k => $v){
				$tag .= $this->make_tag($k, $v);
			}
			return empty($key) ? $tag : sprintf('<%1$s>%2$s</%1$s>', $key, $tag);
		}else{
			if(empty($key)){
				return $value;
			}else{
				if(false !== array_search($key, $this->tab_to_be_base64)){
					$value = base64_encode(mb_convert_encoding($value, 'sjis-win', 'utf-8'));
				}
				return sprintf('<%1$s>%2$s</%1$s>', $key, $value);
			}
		}
	}
	
	private $key_store = array();
	
	/**
	 * Create hash key from string
	 * @param array $xml_array
	 * @return string
	 */
	private function get_hash_key_from_array($xml_array){
		//init
		$this->key_store = array();
		$this->get_xml_value('', $xml_array);
		$hash_key = implode('', array_map('trim', $this->key_store)).
				$this->hash_key();
		return sha1($hash_key);
	}
	
	/**
	 * 
	 * @param string $key
	 * @param string|array $value
	 */
	private function get_xml_value($key, $value){
		if(is_array($value)){
			foreach($value as $k => $v){
				$this->get_xml_value($k, $v);
			}
		}else{
			$this->key_store[] = mb_convert_encoding($value, 'sjis-win', 'uf-8');
		}
	}
	
	/**
	 * Crypt data to Softbank way
	 * @param string $string
	 * @return string
	 */
	private function crypt($string){
		if(!$this->is_sandbox && function_exists('mcrypt_cbc')){
			//Padding the text
			$add = strlen($string) % 8;
			for($i = 0; $i < $add; $i++){
				$string .= ' ';
			}
			$string = base64_encode(mcrypt_cbc(MCRYPT_3DES, $this->crypt_key, $string, MCRYPT_ENCRYPT, $this->iv));
		}
		return $string;
	}
		
	/**
	 * Returns Marchand ID
	 * @param boolean $force To get original string, pass TRUE.
	 * @return string
	 */
	public function marchant_id($force = false){
		return $this->is_sandbox && !$force ? $this->_sandbox_marchant_id : $this->_marchant_id;
	}
	
	/**
	 * Returns Service ID
	 * @param boolean $force To get original string, pass TRUE.
	 * @return string
	 */
	public function service_id($force = false){
		return $this->is_sandbox && !$force ? $this->_sandbox_service_id : $this->_service_id;
	}
	
	/**
	 * Returns Hash key
	 * @param boolean $force To get original string, pass TRUE.
	 * @return string
	 */
	public function hash_key($force = false){
		return $this->is_sandbox && !$force ? $this->_sandbox_hash_key : $this->_hash_key;
	}
	
	public function is_enabled() {
		return (boolean)($this->is_cc_enabled() || $this->is_cvs_enabled() || $this->payeasy);
	}
	
	/**
	 * Returns if CC is enabled.
	 * @return boolean
	 */
	public function is_cc_enabled(){
		$cc = $this->get_available_cards();
		return !empty($cc);
	}
	
	/**
	 * Returns if CVS is enabled
	 * @return boolean
	 */
	public function is_cvs_enabled(){
		$cvs = $this->get_available_cvs();
		return !empty($cvs);
	}
	
	/**
	 * Returns available cards
	 * @param boolean $all
	 * @return array
	 */
	public function get_available_cards($all = false){
		$cards = array();
		foreach($this->_creditcard as $card => $bool){
			if($bool || $all){
				$cards[] = $card;
			}
		}
		return $cards;
	}
	
	/**
	 * Returns available cvs
	 * @param boolean $all
	 * @return array
	 */
	public function get_available_cvs($all = false){
		$cvss = array();
		foreach($this->_webcvs as $cvs => $bool){
			if($bool || $all){
				$cvss[] = $cvs;
			}
		}
		return $cvss;
	}
	
	/**
	 * Returns verbose string of sevices
	 * @param string $slug
	 * @return string
	 */
	public function get_verbose_name($slug){
		switch($slug){
			case 'visa':
				return 'Visa';
				break;
			case 'master':
				return 'Master';
				break;
			case 'jcb':
				return 'JCB';
				break;
			case 'amex':
				return 'American Express';
				break;
			case 'diners':
				return 'Diner\'s';
				break;
			case 'seven-eleven':
				return 'セブンイレブン';
				break;
			case 'lawson':
				return 'ローソン';
				break;
			case 'circle-k':
				return 'サークルK';
				break;
			case 'sunkus':
				return 'サンクス';
				break;
			case 'ministop':
				return 'ミニストップ';
				break;
			case 'familymart':
				return 'ファミリーマート';
				break;
			case 'seicomart':
				return 'セイコーマート';
				break;
		}
	}
	
	/**
	 * Error Message
	 * @var array
	 */
	private $error_msg = array(
		'00' => 'XML 形式エラー',
		'01' => '無効な支払方法が指定されました',
		'02' => '無効な API Request ID が存在しません',
		'03' => '必須項目に値が指定されていない場合に発生',
		'04' => '許容文字属性不正(詳細は、格納可能なデータ型の定義を参照)',
		'05' => '許容桁数(バイト数)範囲外',
		'06' => 'フォーマット不正',
		'07' => '定義値外の値が指定された場合に発生',
		'08' => '使用していないエラーコード',
		'09' => 'リクエストハッシュ値不正',
		'10' => '送信されたリクエストの有効期間(デフォルト 10 分)が切れた場合に発生',
		'11' => '指定の処理対象 SBPS トランザクション ID に紐付く決済が存在しない場合に発生',
		'12' => '処理対象トラッキング ID エラー',
		'13' => '再与信にて指定不可のパラメーターに値が設定された場合に限り発生',
		'14' => '指定されたマーチャント ID・サービス ID に紐付くマーチャントプロパティシートが存在しない',
		'20' => '決済センターよりエラーが返ってきました。',
		'21' => '決済センターよりエラーが返ってきました。',
		'22' => 'クレジットカード利用限度額超過',
		'23' => '決済センターよりエラーが返ってきました。',
		'24' => '暗証番号不正',
		'25' => 'クレジットカード利用限度回数超過',
		'26' => 'クレジットカード取扱不可',
		'27' => 'クレジットカード番号・有効期限誤り',
		'28' => '取引内容取扱不可',
		'29' => '指定ボーナス回数利用不可',
		'30' => '指定ボーナス月利用不可',
		'31' => '指定ボーナス金額利用不可',
		'32' => '指定支払開始月利用不可',
		'33' => '指定分割回数利用不可',
		'34' => '指定分割金額利用不可',
		'35' => '指定初回お支払い金額利用不可',
		'36' => 'その他与信エラー',
		'37' => '自動売上が設定されているため、売上要求は不要です。',
		'38' => '既に返金処理中のため、返金処理を中止しました。',
		'39' => '与信結果が存在しないため、売上処理を中止しました。',
		'40' => '与信取消済みのため、売上処理を中止しました。',
		'41' => '売上処理が完了済みのため、処理を中止しました。',
		'42' => '売上処理の処理日時は、与信日から 3 ヶ月目末日まで有効です。',
		'48' => '使用していないエラーコード',
		'50' => '使用していないエラーコード',
		'51' => '与信結果が存在しないため、与信取消処理を中止しました。',
		'52' => '与信取消済みのため、与信取消処理を中止しました。',
		'53' => '使用していないエラーコード',
		'54' => '継続課金中のため、与信取消処理を中止しました。',
		'55' => '使用していないエラーコード',
		'57' => 'ご指定の継続課金は既に解約済みです。',
		'58' => '継続課金使用中エラー',
		'59' => '自動売上(コミットフラグ適用)の場合、コミット実施後、返金処理を実施して下さい。',
		'60' => '既に処理が完了しているため、コミット(取消)を実施出来ません。',
		'61' => 'セキュリティコード誤り',
		'62' => '使用していないエラーコード',
		'63' => '認証アシスト情報必須エラー',
		'64' => 'SmartLink センターエラー',
		'65' => '決済機関判定エラー',
		'66' => '決済機関判定エラー',
		'67' => '決済機関判定エラー',
		'68' => '決済機関判定エラー',
		'69' => '決済機関判定エラー',
		'70' => '決済機関判定エラー',
		'71' => '決済機関判定エラー',
		'72' => '決済機関判定エラー',
		'73' => '決済機関判定エラー',
		'74' => '決済機関判定エラー',
		'75' => '決済機関判定エラー',
		'76' => '決済機関判定エラー',
		'77' => '決済機関判定エラー',
		'78' => '指定された金額が、与信時金額を越えているため',
		'79' => '処理を中止しました。',
		'22' => '決済センターよりエラーが返ってきました。',
		'45' => '売上処理は、本決済では使用できません。',
		'46' => '自動売上が設定されているため、売上処理は不要です。',
		'47' => '与信結果が存在しないため、売上処理を中止しました。',
		'49' => '取消処理済みのため、売上処理を中止しました。',
		'56' => '使用していないエラーコード',
		'80' => 'GW システムエラー',
		'81' => 'API リクエストパラメータエラー',
		'82' => '使用していないエラーコード',
		'83' => 'GW レコード検索エラー',
		'84' => '決済機関レスポンスパラメータエラー',
		'85' => '決済機関接続エラー',
		'86' => '決済機関システムエラー',
		'90' => 'API システムエラー',
		'91' => '使用していないエラーコード',
		'92' => 'GW 接続エラー',
		'93' => '再入力上限回数制限エラー',
		'94' => '決済未完了エラー',
		'95' => '顧客情報整合性エラー',
		'96' => '2 重リクエストエラー'
	);
	
	/**
	 * Tag to be base64 encoded.
	 * @var array
	 */
	private $tab_to_be_base64 = array(
		'item_name', 'free1', 'free2', 'free3', 'dtl_item_name'
	);
}