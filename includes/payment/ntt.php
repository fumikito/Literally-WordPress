<?php

class LWP_NTT extends LWP_Japanese_Payment{
	
	/**
	 * @var string
	 */
	public $shop_id = '';
	
	/**
	 * @var string
	 */
	public $access_key = '';
	
	/**
	 * @var boolean
	 */
	private $emoney = false;
	
	/**
	 * @var string
	 */
	private $pay_status = '';
	
	/**
	 * @var array
	 */
	private $allowed_ips = array(
		'122.1.80.21', //ちょコム検証環境値
		'61.213.155.75', //ちょコム商用環境値
		'61.213.155.76',
		'221.184.240.31', //弊社ネットワーク環境（品質試験での接続のため）
		'127.0.0.1', //デバッグ用
	);
	
	/**
	 * 
	 * @param array $option
	 */
	public function __construct($option = array()) {
		parent::__construct($option);
		add_action('wp_ajax_chocom_order', array($this, 'return_form'));
	}
	
	/**
	 * 
	 * @param array $option
	 */
	public function set_option($option = array()){
		$option = shortcode_atts(array(
			'ntt_shop_id' => '',
			'ntt_access_key' => '',
			'ntt_sandbox' => true,
			'ntt_stealth' => false,
			'ntt_emoney' => false,
		), $option);
		$this->shop_id = (string)$option['ntt_shop_id'];
		$this->access_key = (string)$option['ntt_access_key'];
		$this->is_sandbox = (boolean)$option['ntt_sandbox'];
		$this->is_stealth = (boolean)$option['ntt_stealth'];
		$this->emoney = (boolean)$option['ntt_emoney'];
	}
	
	/**
	 * Returns vendor name
	 * @param boolean $short
	 * @return string
	 */
	public function vendor_name($short = false){
		return $short
			? $this->_('NTT SmatTrade')
			: $this->_('NTT SmartTrade inc.');
	}
	
	/**
	 * Returns if emoney is enabled
	 * @return type
	 */
	public function is_emoney_enabled(){
			return $this->emoney;
	}
	
	
	
	/**
	 * Returns if service is enabled
	 * @return boolean
	 */
	public function is_enabled(){
		return (boolean)(
				( $this->is_cc_enabled() || $this->is_cvs_enabled() || $this->is_emoney_enabled())
					&&
				(!empty($this->access_key) && !empty($this->shop_id) )
		);
	}
	
	
	
	/**
	 * Returns if current IP address is OK
	 * 
	 * @return boolean
	 */
	public function check_ip(){
		return false !== array_search($_SERVER['REMOTE_ADDR'], $this->allowed_ips);
	}
	
	
	
	/**
	 * Redirect user to endpoint
	 * 
	 * @param string $order_id
	 */
	public function create_emoney_request($order_id){
		$order_id = $this->generate_order_id($user_id, $products);
		$cancel_url = lwp_endpoint('cancel', array('chocom' => 'emoney', 'order_id' => $order_id));
		$error_url = lwp_endpoint('cancel', array('chocom' => 'emoney', 'order_id' => $order_id, 'status' => 'error'));
		$args = $this->domestic_mobile_phone()
				? array(
					's' => $this->shop_id,
					'c' => $cancel_url,
					'e' => $error_url,
					'p' => 'C',
					'linked_1' => $order_id
				)
				: array(
					'shopId' => $this->shop_id,
					'cancelURL' => $cancel_url,
					'errorURL' => $error_url,
					'linked_1' => $order_id
				);
		$query_string = array();
		foreach($args as $key => $val){
			$query_string[] = "{$key}=".rawurlencode($val);
		}
		header('Location: '. $this->get_endpoint('emoney-auth').'?'. implode('&', $query_string));
		exit;
	}
	
	
	
	/**
	 * Returns order id for tarnsaction key
	 * 
	 * @global Literally_WordPress $lwp
	 * @param int $user_id
	 * @return string
	 */
	public function generate_order_id($user_id, $posts = array()){
		global $lwp;
		$post_id = count($posts) > 1 ? 0 : $posts[0]->ID ;
		return sprintf('%s-%08d-%08d-%d', $lwp->slug(), $post_id, $user_id, current_time('timestamp'));
	}
	
	
	
	/**
	 * Test post data
	 * @param array $post_data
	 * @param string $keys
	 * @return boolean
	 */
	private function test_request($post_data, $keys){
		foreach((array)$keys as $key){
			if(!isset($post_data[$key])){
				return false;
			}
		}
		return true;
	}
	
	
	
	
	
	/**
	 * Make request and get result as array.
	 * 
	 * @param string $endpoint
	 * @param array $data
	 * @return array|false
	 */
	private function make_request($endpoint, $data = array()){
		// Parse data.
		$parsed_data = $data;
		// Initialize curl.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $endpoint);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_SSLVERSION, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $parsed_data);
		$result = curl_exec($ch);
		if(curl_errno($ch) > 0 || !$result){
			return false;
		}
		$response = array();
		foreach(explode("\n", preg_replace('/<\/?SHOPMSG>/', '', $result)) as $row){
			$row = trim($row);
			if(empty($row)){
				continue;
			}
			$rows = explode('=', $row);
			if(count($row) < 2){
				continue;
			}
			$response[trim($rows[0])] = trim($rows[1]);
		}
		curl_close($ch);
		if(empty($response)){
			return false;
		}else{
			if(isset($response['payStatus'])){
				$this->pay_status = $response['payStatus'];
			}
			return $response;
		}
	}
	
	
	
	public function finish_transaction($transaction){
		global $lwp, $wpdb;
		switch($transaction->method){
			case LWP_Payment_Methods::NTT_EMONEY:
				if($transaction->status != LWP_Payment_Status::START){
					return false;
				}
				$response = $this->make_request($this->get_endpoint('emoney-capture'), array(
					'shopId' => $this->shop_id,
					'linked_1' => $transaction->transaction_key,
					'accessKey' => $this->access_key,
					'aid' => $transaction->payer_mail,
					'amount' => $transaction->price,
					'choComGoodsCode' => '0990',
					'flag' => '1'
				));
				if(false == $response){
					return lwp_endpoint('chocom-cancel', array(
						'order_id' => $transaction->transaction_key,
						'error' => 1,
						'hash' => $this->get_hash($transaction->ID),
					));
				}elseif($response['payStatus'] != 'C0000000'){
					return lwp_endpoint('chocom-cancel', array(
						'order_id' => $transaction->transaction_key,
						'error' => $response['payStatus'],
						'hash' => $this->get_hash($transaction->ID),
					));
				}else{
					//Transaction is OK.
					$result = $wpdb->update($lwp->transaction, array(
						'status' => LWP_Payment_Status::SUCCESS,
						'updated' => gmdate('Y-m-d H:i:s'),
						'misc' => serialize(array(
							'center_pay_id' => $response['centerPayId']
						)),
					), array('ID' => $transaction->ID), array('%s', '%s', '%s'), array('%d'));
					if($result){
						do_action('lwp_update_transaction', $transaction->ID);
						return lwp_endpoint('success', array('lwp-id' => $transaction->book_id));
					}else{
						return false;
					}
				}
				break;
			default:
				return false;
				break;
		}
	}
	
	/**
	 * Parse request from 
	 * 
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param type $method
	 * @return boolean
	 */
	public function parse_request($method){
		global $lwp, $wpdb;
		switch ($method) {
			case LWP_Payment_Methods::NTT_EMONEY:
				// Test post data
				if(!$this->test_request($_POST, array('aid', 'shopId', 'date', 'trustStatus', 'accessKey', 'linked_1'))){
					return false;
				}
				// Check access key and shop ID
				$transaction = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$lwp->transaction} WHERE transaction_key = %s", $_POST['linked_1']));
				$hash = $this->get_hash($transaction->ID);
				$url = '';
				if($transaction && $transaction->transaction_key == $_POST['linked_1'] && $_POST['shopId'] == $this->shop_id && $_POST['accessKey'] == $this->access_key){
					// Save aid on payer_mail
					$wpdb->update($lwp->transaction, array('payer_mail' => $_POST['aid']), array('ID' => $transaction->ID), array('%s'), array('%d'));
					if($_POST['trustStatus'] == 'GIVE'){
						$url = lwp_endpoint('chocom-result', array('order_id' => $_POST['linked_1'], 'hash' => $hash));
					}else{
						$url = lwp_endpoint('chocom-cancel', array('order_id' => $_POST['linked_1'], 'hash' => $hash));
					}
				}else{
					$url = lwp_endpoint('chocom-cancel', array('order_id' => $_POST['linked_1'], 'error' => true, 'hash' => $hash));
				}
				header('Content-Type: text/plain; charset=UTF-8');
				echo <<<EOS
<SHOPMSG>
shopId={$this->shop_id}
returnURL={$url}
</SHOPMSG>
EOS;
				exit;
				break;

			default:
				return false;
				break;
		}
	}
	
	
	
	/**
	 * Returns Service description
	 * 
	 * @param string $type
	 * @return string
	 */
	public function get_desc($type = 'emoney'){
		switch($type){
			case 'general':
				return 'NTTスマートトレードでは、お客様の多様なニーズにお応えするため 様々な決済手段をご提供しています。';
				break;
			case 'link':
				return sprintf('次へをクリックすると、ちょコムeマネーのサイトへ移動します。ログインまたは登録してください。その後、eマネー口座からの引き落とし同意画面に金額が表示されます。同意が完了すると、%sに戻って来て決済完了となります。', get_bloginfo('name'));
				break;
			case 'emoney':
			default:
				return 'ちょコムeマネーはNTTスマートトレードが提供する安全で便利な、ネットで普及している電子マネーです。 ネット上に自分専用の貯金箱(口座)を開設し、コンビニ(ファミリー マート、ローソン、セブンイレブン、セイコーマート)、 クレジットカード、銀行ATM、インターネット銀行を利用してチャージ(入金)すると、すぐにご利用いただけます。 入会お申し込み・詳細は、<a href="http://www.chocom.jp/" target="_blank">ちょコムeマネー公式ホームページ</a>をご覧ください。';
				break;
		}
	}
	
	
	
	/**
	 * Returns error message
	 * 
	 * @param string $error_code
	 * @return string
	 */
	public function get_error_msg($error_code){
		switch($error_code){
			case 'C0000000':
				return '正常に決済処理がおこなわれました。';
			case 'C0000001':
				return 'お客様の信用要求情報がありません。';
			case 'C0000002':
				return 'お客様のデータが存在しません。';
			case 'C0000003':
				return 'お客様は現在ちょコムeマネーをご利用できない状態です。';
			case 'C0000004':
				return 'お客様の貯金箱が存在しません。';
			case 'C0000005':
				return 'お客様のちょコム残高が不足しています。ちょコムeマネーをチャージして、もう一度やり直してください。';
				return '加盟店のデータが存在しません。';
			case 'C0000006':
			case 'C0000007':
			case 'C0000008':
			case 'C0000009':
			case 'C0000010':
				return '申し訳ございません。ちょコムのシステムが停止中です。';
			case 'C0000011':
			case 'C0000012':
			case 'C0000013':
			case 'C0000014':
			case 'C0000015':
			case 'C0000016':
				return '申し訳ございません。システムでエラーが発生し、処理を完了できませんでした。';＾’				
				break;
			default:
				return 'ちょコムでの決済処理の過程でエラーが発生しました。もう一度やり直してください。';
				break;;
		}
	}
	
	/**
	 * Returns login instruction
	 * 
	 * @param string $method
	 * @return string
	 */
	public function get_instruction($method){
		switch ($method) {
			case LWP_Payment_Methods::NTT_EMONEY:
				return '上記の内容で決済を進めます。よろしいですか？　金額はちょコムサイトでログインした後に表示されます。金額の同意なしに引き落とされることはありません。';
				break;
		}
	}
	
	
	/**
	 * Returns form for Ajaxrequest
	 * 
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param type $user_id
	 * @param type $method
	 * @param type $products
	 * @param type $quantities
	 * @return boolean
	 */
	public function get_form($user_id, $method, $products, $quantities){
		global $wpdb, $lwp;
		// Generate order_id
		$order_id = $this->generate_order_id($user_id, $products);
		// Create transaciton
		switch($method){
			case LWP_Payment_Methods::NTT_EMONEY:
				if(count($products) > 1){
						// TODO: 商品が複数の場合
						
					}else{
						$product = $products[0];
						if(!isset($quantities[$product->ID]) || $quantities[$product->ID] < 1){
							return false;
						}
						$result = $wpdb->insert($lwp->transaction, array(
							"user_id" => $user_id,
							"book_id" => $product->ID,
							"price" => lwp_price($product) * $quantities[$product->ID],
							"status" => LWP_Payment_Status::START,
							"method" => LWP_Payment_Methods::NTT_EMONEY,
							'num' => $quantities[$product->ID],
							"transaction_key" => $order_id,
							"registered" => gmdate('Y-m-d H:i:s'),
							"updated" => gmdate('Y-m-d H:i:s')
						), array('%d', '%d', '%d', '%s', '%s', '%d', '%s', '%s', '%s'));
						if($result && ($transaction_id = $wpdb->insert_id)){
							// Transaction is created.
							$form = <<<EOS
<form method="post" action="%s">
	<input type="hidden" name="shopId" value="%s" />
	<input type="hidden" name="cancelURL" value="%s" />
	<input type="hidden" name="errorURL" value="%s" />
	<input type="hidden" name="linked_1" value="%s" />
</form>
EOS;
							return sprintf($form, $this->get_endpoint('emoney-auth'), $this->shop_id, 
								lwp_endpoint('chocom-cancel', array('order_id' => $order_id, 'hash' => $this->get_hash($transaction_id))),
								lwp_endpoint('chocom-cancel', array('order_id' => $order_id, 'error' => 1, 'hash' => $this->get_hash($transaction_id))),
								$order_id);
						}else{
							return false;
						}
					}
				break;
			default:
				return false;
				break;
		}
	}
	
	
	
	/**
	 * Returns endpoint
	 * 
	 * @param string $type
	 * @return string
	 */
	private function get_endpoint($type){
		$pc_base = $this->is_sandbox
					? 'https://pchocom.sinka-dbg.jp/inq/servlet/'
					: 'https://www.chocom.net/inq/servlet/';
		$mobile_base = $this->is_sandbox
					? 'https://mobilechocom.sinka-dbg.jp/mobile/servlet/'
					: 'https://mobile.chocom.net/mobile/servlet/';
		switch($type){
			case 'emoney-auth':
				if($this->domestic_mobile_phone()){
					return $mobile_base.'EMQShinyo';
				}else{
					return $pc_base.'E24Shinyo';
				}
				break;
			case 'emoney-capture':
				return $pc_base.'E2OShopDecision';
				break;
		}
	}
	
	
	
	/**
	 * Returns hash string which is unique by transaction
	 * 
	 * @param int $transaction_id
	 * @return string
	 */
	private function get_hash($transaction_id){
		return sha1($transaction_id.'-'.NONCE_SALT.'-ntt');
	}
	
	
	
	/**
	 * Returns transaction if hash is correct.
	 * 
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param string $order_id
	 * @param string $hash
	 * @return object
	 */
	public function get_transaction_by_hash($order_id, $hash){
		global $lwp, $wpdb;
		$transaction = $wpdb->get_row($wpdb->prepare("SELECT * from {$lwp->transaction} WHERE transaction_key = %s", $order_id));
		if(!$transaction){
			return null;
		}
		return ($this->get_hash($transaction->ID) == $hash) ? $transaction : null;
	}
	
	
	
	/**
	 * Get transaction object on request page.
	 * 
	 * @return Object
	 */
	public function get_transaction_by_request(){
		if(
			 // Required queries
			!isset($_REQUEST['order_id'], $_REQUEST['hash'])
				||
			 // Transaction exists
			!($transaction = $this->get_transaction_by_hash($_REQUEST['order_id'], $_REQUEST['hash']))
				||
			 // User ID
			get_current_user_id() != $transaction->user_id
				||
			// Check chocom method
			!LWP_Payment_Methods::is_chocom($transaction->method)
		){
			return $transaction;
		}else{
			return null;
		}
	}
	
	
	
	/**
	 * Returns if domestic mobile phone
	 * @return boolean
	 */
	private function domestic_mobile_phone(){
		return apply_filters('lwp_is_domesctic_mobile', false);
	}
}