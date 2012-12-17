<?php
class LWP_SB_Payment extends LWP_Japanese_Payment {
	
	/**
	 * Endpoint
	 */
	const PAYMENT_ENDPOINT_SANDBOX = 'https://stbfep.sps-system.com/api/xmlapi.do';
	
	const PAYMENT_ENDPOINT_PRODUCTION = 'https://api.sps-system.com/api/xmlapi.do';
	
	/**
	 * Server of Softbank
	 */
	const SOFTBANK_IP_ADDRESS = '61.215.213.47';
	
	/**
	 * Sandbox Marchant ID
	 */
	const SANDBOX_MARCHAND_ID = '30132';
	
	/**
	 * Sandbox Service ID
	 */
	const SANDBOX_SERVICE_ID = '002';
	
	/**
	 * Hash key for Sandbox
	 */
	const SANDBOX_HASH_KEY = '8435dbd48f2249807ec216c3d5ecab714264cc4a';

	const SANDBOX_CC_NUMBER = '5250729026209007';
	const SANDBOX_CC_EXPIRATION = '201103';
	const SANDBOX_CC_SEC_CODE = '798';
	
	/**
	 * Operation IDs
	 */
	const USER_INFO = 'MG02-00104-101';
	
	const PAYMENT_REQUEST_CODE = 'ST01-00101-101';
	
	const PAYMENT_FIX_CODE = 'ST02-00101-101';
	
	const PAYMENT_SALES_CODE = 'ST02-00201-101';
	
	const CANCEL_PAYMENT = 'ST02-00303-101';
	
	const CANCEL_AUTH = 'ST02-00305-101';
	
	const CVS_REQUEST_CODE = 'ST01-00101-701';
	
	const PAYEASY_REQUEST_CODE = 'ST01-00101-703';
	
	const PAYEASY_SUCCESS_RESPONSE_CODE = 'NT01-00103-703';
	
	const PAYEASY_CANCEL_RESPONSE_CODE = 'NT01-00104-703';
	
	const CVS_SUCCESS_RESPONSE_CODE = 'NT01-00103-701';
	
	const CVS_CANCEL_RESPONSE_CODE = 'NT01-00104-701';
	
	/**
	 * Initial Vector for Crypt
	 * @var string
	 */
	public $iv = '';
	
	/**
	 * Crypt password
	 * @var type 
	 */
	public $crypt_key = '';
	
	/**
	 * Prefix for transaction
	 * @var strign
	 */
	public $prefix = '';
	
	/**
	 * Marchant ID
	 * @var string
	 */
	private $_marchant_id = '';
	
	/**
	 * Service ID of this site
	 * @var type 
	 */
	private $_service_id = '';
	
	/**
	 * Hash key to create SHA1 Hash
	 * @var type 
	 */
	private $_hash_key = '';
		
	/**
	 * Blog name to display on PayEasy
	 * @var string
	 */
	public $blogname = '';
	
	/**
	 * Blog description for display on PayEasy
	 */
	public $blogname_kana = '';
	
	/**
	 * CVS code
	 * @var array 
	 */
	protected $cvs_codes = array(
		'seven-eleven' => '001',
		'lawson' => '002',
		'circle-k' => '017',
		'sunkus' => '017',
		'ministop' => '005',
		'familymart' => '016',
		'daily-yamazaki' => '010',
		'seicomart' => '018'
	);
	
	/**
	 * Production endpoint.
	 * @var string
	 */
	private $production_endpoint = false;
	
	/**
	 * Whether if save credit information
	 * @var boolean
	 */
	public $save_cc = true;
	
	/**
	 * User meta key for save credit card number flg
	 * @var string
	 */
	public $meta_cc_number_flg = '_lwp_sb_cc_number';
	
	/**
	 * Tag to be base64 encoded.
	 * @var array
	 */
	private $tag_to_be_base64 = array(
		'item_name', 'free1', 'free2', 'free3', 'dtl_item_name', 'last_name', 'first_name', 'last_name_kana', 'first_name_kana',
		'add1', 'add2', 'add3', 'bill_info', 'bill_info_kana', 'res_err_code'
	);
	
	/**
	 * These tags must be crypted if 3DES flg is true.
	 * @var array 
	 */
	private $tag_to_be_crypt = array(
		'issue_type', 'last_name', 'first_name', 'last_name_kana', 'first_name_kana', 'first_zip', 'second_zip', 'add1', 'add2', 'add3',
		'tel', 'mail', 'seiyakudate', 'webcvstype', 'bill_date', 'payeasy_type', 'terminal_value', 'pay_csv', 'bill_info_kana', 'bill_info', 'bill_notes',
		'cc_number', 'cc_expiration', 'security_code', 'dealings_type', 'divide_times', 'cust_manage_flg'
	);
	
	/**
	 * These tag must be crypted despite 3DES flag.
	 * @var array
	 */
	private $tag_to_be_crypt_force = array('rec_type', 'rec_amount', 'rec_amount_total', 'res_mail', 'rec_extra');
	
	/**
	 * These tag must be decrypted if 3DES flag is true.
	 * @var array
	 */
	private $tag_to_be_decrypt_force = array('rec_type', 'rec_amount', 'rec_amount_total', 'res_mail', 'rec_extra');
	
	/**
	 * These tags must be base64 decoded
	 * @var array
	 */
	private $tag_to_be_base64_decode = array('rec_extra');
	
	/**
	 * Must be 1~60 days
	 * @var int
	 */
	public $cvs_limit = 1;
	
	/**
	 * Must be 1-60 days.
	 * @var int
	 */
	public $payeasy_limit = 1;
	
	/**
	 * @var string
	 */
	private $cron_name = '_lwp_sbpayment_event_cron';
	
	
	/**
	 * Setup Options
	 * @param array $option
	 */
	public function set_option($option = array()) {
		$option = shortcode_atts(array(
			'sb_creditcard' => array(),
			'sb_webcvs' => array(),
			'sb_payeasy' => false,
			'sb_sandbox' => true,
			'sb_stealth' => false,
			'sb_blogname' => '',
			'sb_blogname_kana' => '',
			'sb_marchant_id' => '',
			'sb_service_id' => '',
			'sb_crypt_key' => '',
			'sb_iv' => '',
			'sb_hash_key' => '',
			'sb_prefix' => '',
			'sb_save_cc_number' => true,
			'sb_cvs_limit' => $this->cvs_limit,
			'sb_payeasy_limit' => $this->payeasy_limit
		), $option);
		foreach($this->_creditcard as $cc => $bool){
			$this->_creditcard[$cc] = (false !== array_search($cc, (array)$option['sb_creditcard']));
		}
		foreach($this->_webcvs as $cvs => $bool){
			$this->_webcvs[$cvs] = (false !== array_search($cvs, (array)$option['sb_webcvs']));
		}
		$this->payeasy = (boolean)$option['sb_payeasy'];
		$this->is_sandbox = (boolean)$option['sb_sandbox'];
		$this->is_stealth = (boolean)$option['sb_stealth'];
		$this->_marchant_id = (string)$option['sb_marchant_id'];
		$this->_service_id = (string)$option['sb_service_id'];
		$this->_hash_key = (string)$option['sb_hash_key'];
		$this->prefix = (string)$option['sb_prefix'];
		$this->iv = (string)$option['sb_iv'];
		$this->crypt_key = (string)$option['sb_crypt_key'];
		$this->blogname = (string)mb_convert_kana($option['sb_blogname'], 'ASKV', 'utf-8');
		$this->blogname_kana = $this->convert_zenkaka_kana($option['sb_blogname_kana']);
		$this->cvs_limit = intval($option['sb_cvs_limit']);
		$this->payeasy_limit = intval($option['sb_payeasy_limit']);
		$this->production_endpoint = (!empty($option['sb_endpoint'])) ? (string)$option['sb_endpoint'] : false;
		$this->save_cc = (boolean)$option['sb_save_cc_number'];
	}
	
	public function on_construct() {
		add_action('init', array($this, 'init'));
		//if ( !wp_next_scheduled( $this->cron_name ) ) {
			//wp_schedule_event(current_time('timestamp'), 'daily', $this->cron_name);
		//}
		//add_action($this->cron_name, array($this, 'daily_cron'));
		
	}
	
	/**
	 * @see LWP_Japanese_Payment::register_offline_methods()
	 */
	protected function register_offline_methods(){
		//Set up offline context
		$this->offline_context = array(
			LWP_Payment_Methods::SOFTBANK_PAYEASY,
			LWP_Payment_Methods::SOFTBANK_WEB_CVS
		);
	}
	
	public function init(){
		global $lwp;
		if($lwp->event->is_enabled()){
			//Hook for event notification despite order is authed.
			//add_action('_lwp_event_authorized_for_sb', array($this, 'notify_on_event_auth'));
		}
		//$this->daily_cron();
	}
	
	
	
	/**
	 * Wrapper for sender notification on Event transction
	 * @deprecated since version 0.9.3
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param int $transaction_id
	 */
	public function notify_on_event_auth($transaction_id){
		global $wpdb, $lwp;
		$transaction = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$lwp->transaction} WHERE ID = %d AND status = %s AND method = %s", $transaction_id, LWP_Payment_Status::AUTH, LWP_Payment_Methods::SOFTBANK_CC));
		if($transaction){
			$lwp->event->notify($transaction);
		}
	}
	
	/**
	 * Daily Cron to finish auth
	 * @deprecated since version 0.9.3
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 */
	public function daily_cron(){
		global $lwp, $wpdb;
		if($lwp->event->is_enabled()){
			set_time_limit(0);
			$yesterday = date('Y-m-d', current_time('timestamp') - 60 * 60 * 24);
			$sql = <<<EOS
				SELECT t.* FROM {$lwp->transaction} AS t
				INNER JOIN {$wpdb->posts} AS p
				ON t.book_id = p.ID AND p.post_type = %s
				LEFT JOIN {$wpdb->postmeta} AS pm
				ON p.ID = pm.post_id AND pm.meta_key = %s
EOS;
			$query = $wpdb->prepare($sql, $lwp->event->post_type, $lwp->event->meta_selling_limit);
			$wheres = array(
				$wpdb->prepare("t.status = %s", LWP_Payment_Status::AUTH),
				$wpdb->prepare("pm.meta_value = %s", $yesterday)
			);
			$query .= " WHERE ".implode(' AND ', array_map(create_function('$var', 'return "(".$var.")";'), $wheres));
			//var_dump($query, $wpdb->get_results($query));
			//die();
		}
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
	 * @param boolean $save_cc_number
	 * @param boolean $use_same_card
	 * @return int transaction ID
	 */
	public function do_credit_authorization($user_id, $item_name, $post_id, $price, $quantity, $cc_number, $cc_sec, $expiration, $save_cc_number, $use_same_card = false){
		global $lwp, $wpdb;
		$now = gmdate('Y-m-d H:i:s');
		$order_id = $this->generate_order_id();
		$save_cc_number = ($save_cc_number && $this->save_cc);
		//Create XML
		$xml_array = $this->create_common_xml($order_id, $user_id, $post_id, $item_name, $price, true);
		if(!$use_same_card){
			$xml_array['pay_method_info'] = array(
					'cc_number' =>  $cc_number,
					'cc_expiration' => ($this->is_sandbox && $this->_marchant_id == self::SANDBOX_MARCHAND_ID ) ? '201103' : $expiration, //Change expiration if it is sandbox and before constract.
					'security_code' => $cc_sec,
					'cust_manage_flg' => intval($save_cc_number));
		}else{
			$xml_array['pay_method_info'] = array(
				'cust_manage_flg' => 0
			);
		}
		$xml_array['encrypted_flg'] = intval(!$this->is_sandbox);
		$xml_array['request_date'] = mysql2date("YmdHis", get_date_from_gmt($now));
		$xml_array['limit_second'] = 60;
		$xml_array['sps_hashcode'] = $this->get_hash_key_from_array($xml_array);
		//Do request
		$result = $this->get_request($this->make_xml(self::PAYMENT_REQUEST_CODE, $xml_array));
		if(!$result['success']){
			$this->last_error = $result['message'];
			return false;
		}else{
			if($save_cc_number){
				update_user_meta($user_id, $this->meta_cc_number_flg, true);
			}
			$xml = $result['body'];
			$commit_array = array(
				'merchant_id' => $this->marchant_id(),
				'service_id' => $this->service_id(),
				'sps_transaction_id' => $xml->res_sps_transaction_id,
				'tracking_id' => $xml->res_tracking_id,
				'processing_datetime' => '',
				'request_date' => date('YmdHis', current_time('timestamp')),
				'limit_second' => ''
			);
			$hash_key = $this->get_hash_key_from_array($commit_array);
			$commit_array['sps_hashcode'] = $hash_key;
			$commit_result = $this->get_request($this->make_xml(self::PAYMENT_FIX_CODE, $commit_array));
			if($commit_result['success']){
				$inserted = $wpdb->insert($lwp->transaction,array(
					"user_id" => $user_id,
					"book_id" => $post_id,
					"price" => $price,
					"status" => LWP_Payment_Status::AUTH,
					"method" => LWP_Payment_Methods::SOFTBANK_CC,
					"transaction_key" => $order_id,
					'transaction_id' => $xml->res_sps_transaction_id,
					'payer_mail' => $xml->res_tracking_id,
					"registered" => $now,
					"updated" => $now
				), array('%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ));
				if($inserted){
					return $wpdb->insert_id;
				}else{
					$this->last_error = $this->_('Payment requeist is succeeded, but failed to finish transaction. Please contact to Administrator.');
					return 0;
				}
			}else{
				$this->last_error = $this->_('Cannot commit transaction.');
				return 0;
			}
		}
	}
	
	/**
	 * Do commited transaction status to Sales
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param int $transaction_id
	 * @param int $price
	 * @return boolean
	 */
	public function capture_authorized_transaction($transaction_id, $price = 0){
		global $wpdb, $lwp;
		$tran = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$lwp->transaction} WHERE ID = %d AND status = %s", $transaction_id, LWP_Payment_Status::AUTH));
		if(!$tran){
			return false;
		}
		$price = ($price > 0 && $price <= $tran->price) ? $price : $tran->price;
		$now = current_time('mysql', true);
		$now_local = get_date_from_gmt($now, 'YmdHis');
		$xml_array = array(
			'merchant_id' => $this->marchant_id(),
			'service_id' => $this->service_id(),
			'sps_transaction_id' => $tran->transaction_id,
			'tracking_id' => $tran->payer_mail,
			'processing_datetime' => $now_local,
			'pay_option_manage' => array(
				'amount' => $price
			),
			'request_date' => $now_local
		);
		$xml_array['sps_hashcode'] = $this->get_hash_key_from_array($xml_array);
		$result = $this->get_request($this->make_xml(self::PAYMENT_SALES_CODE, $xml_array));
		if($result['success']){
			return true;
		}else{
			$this->last_error = $result['message'];
			return false;
		}
	}
	
	/**
	 * Cancel Payment 
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param int $transaction_id
	 * @return boolean
	 */
	public function cancel_credit_transaction($transaction_id){
		global $wpdb, $lwp;
		$tran = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$lwp->transaction} WHERE ID = %d", $transaction_id));
		if(!$tran){
			return false;
		}
		switch($tran->status){
			case LWP_Payment_Status::AUTH:
				$action = self::CANCEL_AUTH;
				break;
			case LWP_Payment_Status::SUCCESS:
				$action = self::CANCEL_PAYMENT;
				break;
			default:
				return false;
				break;
		}
		$now = current_time('mysql', true);
		$now_local = get_date_from_gmt($now, 'YmdHis');
		$xml_array = array(
			'merchant_id' => $this->marchant_id(),
			'service_id' => $this->service_id(),
			'sps_transaction_id' => $tran->transaction_id,
			'tracking_id' => $tran->payer_mail,
			'processing_datetime' => $now_local,
			'request_date' => $now_local
		);
		$xml_array['sps_hashcode'] = $this->get_hash_key_from_array($xml_array);
		$result = $this->get_request($this->make_xml($action, $xml_array));
		if($result['success']){
			return true;
		}else{
			$this->last_error = $result['message'];
			return false;
		}

	}
	
	/**
	 * Do CVS authorization
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param int $user_id
	 * @param string $item_name
	 * @param int $post_id
	 * @param int $price
	 * @param int $quantity
	 * @param array $creds
	 * @return int
	 */
	public function do_cvs_authorization($user_id, $item_name, $post_id, $price, $quantity, $creds){
		global $lwp, $wpdb;
		$now = gmdate('Y-m-d H:i:s');
		$limit = $this->get_payment_limit($post_id, false, 'sb-cvs');
		$order_id = $this->generate_order_id();
		//Create common XML
		$xml_array = $this->create_common_xml($order_id, $user_id, $post_id, $item_name, $price);
		//Get zip
		$zip = array();
		preg_match("/^([0-9]{3})([0-9]{4})$/", $creds['zipcode'], $zip);
		//Get Office name
		$office = isset($creds['office']) && !empty($creds['office']) ? $creds['office'] : '';
		//Merge XML
		$xml_array['pay_method_info'] = array(
			'issue_type' => 0,
			'last_name' => $creds['last_name'],
			'first_name' => $creds['first_name'],
			'last_name_kana' => $creds['last_name_kana'],
			'first_name_kana' => $creds['first_name_kana'],
			'first_zip' => $zip[1],
			'second_zip' => $zip[2],
			'add1' => $creds['prefecture'],
			'add2' => mb_convert_kana($creds['city'].$creds['street'], 'AS', 'utf-8'),
			'add3' => mb_convert_kana($office, 'AS', 'utf-8'),
			'tel' => $creds['tel'],
			'mail' => $wpdb->get_var($wpdb->prepare("SELECT user_email FROM {$wpdb->users} WHERE ID = %d", $user_id)),
			'seiyakudate' => get_date_from_gmt($now, 'Ymd'),
			'webcvstype' => $this->get_cvs_code($creds['cvs']),
			'bill_date' => date_i18n('Ymd', $limit),
		);
		$xml_array['encrypted_flg'] = intval(!$this->is_sandbox);
		$xml_array['request_date'] = get_date_from_gmt($now, 'YmdHis');
		$xml_array['limit_second'] = 60;
		$xml_array['sps_hashcode'] = $this->get_hash_key_from_array($xml_array);
		//Do transaction 
		$result = $this->get_request($this->make_xml(self::CVS_REQUEST_CODE, $xml_array));
		if(!$result['success']){
			$this->last_error = $result['message'];
			return 0;
		}else{
			$xml = $result['body'];
			$inserted = $wpdb->insert($lwp->transaction,array(
				"user_id" => $user_id,
				"book_id" => $post_id,
				"price" => $price,
				"status" => LWP_Payment_Status::START,
				"method" => LWP_Payment_Methods::SOFTBANK_WEB_CVS,
				"transaction_key" => $order_id,
				'transaction_id' => (string)$xml->res_sps_transaction_id,
				'payer_mail' => (string)$xml->res_tracking_id,
				"registered" => $now,
				"updated" => $now,
				'misc' => serialize(array(
					'cvs' => $creds['cvs'],
					'invoice_no' => $this->decrypt((string)$xml->res_pay_method_info->invoice_no),
					'bill_date' => preg_replace('/([0-9]{4})([0-9]{2})([0-9]{2})/', '$1-$2-$3 23:59:59', $this->decrypt($xml->res_pay_method_info->bill_date)),
					'cvs_pay_data1' => $this->decrypt((string)$xml->res_pay_method_info->cvs_pay_data1),
					'cvs_pay_data2' => $this->decrypt((string)$xml->res_pay_method_info->cvs_pay_data2),
				))
			), array('%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'));
			if($inserted){
				return $wpdb->insert_id;
			}else{
				$this->last_error = $this->_('Payment requeist is succeeded, but failed to finish transaction. Please contact to Administrator.');
				return 0;
			}
		}
	}
	
	/**
	 * Returns payeasy transactioin request result.
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param int $user_id
	 * @param string $item_name
	 * @param int $post_id
	 * @param int $price
	 * @param int $quantity
	 * @param array $creds
	 * @return int
	 */
	public function do_payeasy_authorization($user_id, $item_name, $post_id, $price, $quantity, $creds){
		global $lwp, $wpdb;
		$now = gmdate('Y-m-d H:i:s');
		$limit = $this->get_payment_limit($post_id, false, 'sb-payeasy');
		$order_id = $this->generate_order_id();
		//Create common XML
		$xml_array = $this->create_common_xml($order_id, $user_id, $post_id, $item_name, $price);
		//Get zip
		$zip = array();
		preg_match("/^([0-9]{3})([0-9]{4})$/", $creds['zipcode'], $zip);
		//Get Office name
		$office = isset($creds['office']) && !empty($creds['office']) ? $creds['office'] : '';
		//Merge XML
		$xml_array['pay_method_info'] = array(
			'issue_type' => 2,
			'last_name' => $creds['last_name'],
			'first_name' => $creds['first_name'],
			'last_name_kana' => $creds['last_name_kana'],
			'first_name_kana' => $creds['first_name_kana'],
			'first_zip' => $zip[1],
			'second_zip' => $zip[2],
			'add1' => $creds['prefecture'],
			'add2' => mb_convert_kana($creds['city'].$creds['street'], 'AS', 'utf-8'),
			'add3' => mb_convert_kana($office, 'AS', 'utf-8'),
			'tel' => $creds['tel'],
			'mail' => $wpdb->get_var($wpdb->prepare("SELECT user_email FROM {$wpdb->users} WHERE ID = %d", $user_id)),
			'seiyakudate' => get_date_from_gmt($now, 'Ymd'),
			'payeasy_type' => 'O',
			'terminal_value' => 'P',
			'bill_info_kana' => $this->blogname_kana,
			'bill_info' => $this->blogname,
		);
		$xml_array['encrypted_flg'] = intval(!$this->is_sandbox);
		$xml_array['request_date'] = get_date_from_gmt($now, 'YmdHis');
		$xml_array['limit_second'] = 60;
		$xml_array['sps_hashcode'] = $this->get_hash_key_from_array($xml_array);
		//Do transaction 
		$result = $this->get_request($this->make_xml(self::PAYEASY_REQUEST_CODE, $xml_array));
		if(!$result['success']){
			$this->last_error = $result['message'];
			return 0;
		}else{
			$xml = $result['body'];
			$inserted = $wpdb->insert($lwp->transaction,array(
				"user_id" => $user_id,
				"book_id" => $post_id,
				"price" => $price,
				"status" => LWP_Payment_Status::START,
				"method" => LWP_Payment_Methods::SOFTBANK_PAYEASY,
				"transaction_key" => $order_id,
				'transaction_id' => $xml->res_sps_transaction_id,
				'payer_mail' => $xml->res_tracking_id,
				"registered" => $now,
				"updated" => $now,
				'misc' => serialize(array(
					'invoice_no' => $this->decrypt($xml->res_pay_method_info->invoice_no),
					'bill_date' => preg_replace('/([0-9]{4})([0-9]{2})([0-9]{2})/', '$1-$2-$3 23:59:59', $this->decrypt($xml->res_pay_method_info->bill_date)),
					'skno' => $this->decrypt($xml->res_pay_method_info->skno),
					'cust_number' => $this->decrypt($xml->res_pay_method_info->cust_number),
				))
			), array('%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'));
			if($inserted){
				return $wpdb->insert_id;
			}else{
				$this->last_error = $this->_('Payment requeist is succeeded, but failed to finish transaction. Please contact to Administrator.');
				return 0;
			}
		}
	}
	
	
	/**
	 * Returns common xml array
	 * @param string $order_id
	 * @param int $user_id
	 * @param int $post_id
	 * @param string $item_name
	 * @param int $price
	 * @return array
	 */
	private function create_common_xml($order_id, $user_id, $post_id, $item_name, $price, $cc = false){
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
			'order_rowno' => 1);
		if($cc){
			$xml_array['sps_cust_info_return_flg'] = 0;
		}
		$xml_array['dtls'] = '';/*array(
				'dtl' => array(
					'dtl_rowno' => 1,
					'dtl_item_id' => $post_id,
					'dtl_item_name' => $item_name,
					'dtl_item_count' => $quantity,
					'dtl_tax' => 0,
					'dtl_amount' => $price
				)
			)*/
		return $xml_array;
	}
	
	
	/**
	 * Get Post data on Endpoint
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param string $post_data
	 */
	public function parse_request($post_data){
		global $lwp, $wpdb;
		$xml_string = preg_replace("/(encoding=\")Shift_JIS(\")/", "$1utf-8$2", mb_convert_encoding($post_data, 'utf-8', 'sjis-win'));
		$xml = simplexml_load_string($xml_string);
		$status = 'NG';
		$message = '';
		if(isset($xml['id'])){
			$transaction = null;
			$sql = "SELECT * FROM {$lwp->transaction} WHERE payer_mail = %s AND method = %s";
			$action = $xml['id'];
			//Get transaction
			switch($action){
				case self::CVS_CANCEL_RESPONSE_CODE:
				case self::CVS_SUCCESS_RESPONSE_CODE:
					$transaction = $wpdb->get_row($wpdb->prepare($sql, $xml->tracking_id, LWP_Payment_Methods::SOFTBANK_WEB_CVS));
					break;
				case self::PAYEASY_CANCEL_RESPONSE_CODE:
				case self::PAYEASY_SUCCESS_RESPONSE_CODE:
					$transaction = $wpdb->get_row($wpdb->prepare($sql, $xml->tracking_id, LWP_Payment_Methods::SOFTBANK_PAYEASY));
					break;
			}
			if(!$transaction){
				$message = $this->_('Specified transaction is not found.');
			}elseif(!$this->check_sum($xml)){
				$message = $this->_('Check sum is wrong.');
			}else{
				//This is valid request, so change status as it request.
				switch($action){
					case self::CVS_CANCEL_RESPONSE_CODE:
					case self::PAYEASY_CANCEL_RESPONSE_CODE:
						if($transaction->status != LWP_Payment_Status::CANCEL){
							$updated = $wpdb->update($lwp->transaction, array(
								'status' => LWP_Payment_Status::CANCEL,
								'updated' => current_time('mysql', true)
							), array('ID' => $transaction->ID), array('%s', '%s'), array('%d'));
							if($updated){
								do_action('lwp_update_transaction', $transaction->ID);
								$status = 'OK';
								$message = $this->_('Transaction was successfully changed.');
							}else{
								$message = $this->_('Failed to update database. Please try again later.');
							}
						}
						break;
					case self::CVS_SUCCESS_RESPONSE_CODE:
					case self::PAYEASY_SUCCESS_RESPONSE_CODE:
						$code = intval($this->decrypt((string)$xml->pay_method_info->rec_type, true));
						$status_to = false;
						switch($code){
							case 1:
							case 3:
								$price = $this->decrypt((string)$xml->pay_method_info->rec_amount, true);
								if($price == $transaction->price){
									$status_to = LWP_Payment_Status::SUCCESS;
								}else{
									$message = $this->_('Reported amount is different from transaction log.');
								}
								break;
							case 2:
							case 4:
								$status_to = LWP_Payment_Status::START;
								break;
						}
						if($status_to){
							if($transaction->status != $status_to){
								$updated = $wpdb->update($lwp->transaction, array(
									'status' => $status_to,
									'updated' => current_time('mysql', true)
								), array('ID' => $transaction->ID), array('%s', '%s'), array('%d'));
								if($updated){
									do_action('lwp_update_transaction', $transaction->ID);
									$status = 'OK';
									$message = $this->_('Transaction was successfully changed.');
								}else{
									$message = $this->_('Failed to update database. Please try again later.');
								}
							}else{
								//No need to update
								$status = 'OK';
								$message = $this->_('Nothing needs update.');
							}
						}else{
							$message = $this->_('Request type is wrong.');
						}
						break;
				}
			}
		}else{
			$message = $this->_('ID is not set.');
			$action = self::CVS_CANCEL_RESPONSE_CODE;
		}
		return $this->make_xml($action, array(
			'res_result' => $status,
			'res_err_code' => $message
		), true);
	}
		
	/**
	 * Make pseudo request to self server
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param int $transaction_id
	 * @param int $status
	 * @return string XML Format
	 */
	public function make_pseudo_request($transaction_id, $status){
		global $wpdb, $lwp;
		$sql = "SELECT * FROM {$lwp->transaction} WHERE ID = %d";
		$transaction = $wpdb->get_row($wpdb->prepare($sql, $transaction_id));
		if(!$transaction){
			return $this->make_xml(self::CVS_CANCEL_RESPONSE_CODE, array(
				'res_result' => 'NG',
				'res_err_code' => $this->_('Specified transaction is not found.')
			), true);
		}elseif(false === array_search ($transaction->method, array(LWP_Payment_Methods::SOFTBANK_WEB_CVS, LWP_Payment_Methods::SOFTBANK_PAYEASY))){
			return $this->make_xml(self::CVS_CANCEL_RESPONSE_CODE, array(
				'res_result' => 'NG',
				'res_err_code' => sprintf($this->_('Specified transaction\'s payment method is %s.'), $this->_($transaction->method))
			), true);
		}else{
			switch($transaction->method){
				case LWP_Payment_Methods::SOFTBANK_WEB_CVS:
					$action = $status ? self::CVS_SUCCESS_RESPONSE_CODE : self::CVS_CANCEL_RESPONSE_CODE;
					break;
				case LWP_Payment_Methods::SOFTBANK_PAYEASY:
					$action = $status ? self::PAYEASY_SUCCESS_RESPONSE_CODE : self::PAYEASY_CANCEL_RESPONSE_CODE;
					break;
			}
			$now = date('YmdHis', current_time('timestamp'));
			$xml_array = array(
				'merchant_id' => $this->marchant_id(),
				'service_id' => $this->service_id(),
				'sps_transaction_id' => $transaction->transaction_id,
				'tracking_id' => $transaction->payer_mail,
				'rec_datetime' => $now
			);
			if($status){
				$user = get_userdata($transaction->user_id);
				$xml_array = array_merge($xml_array, array(
					'pay_method_info' => array(
						'rec_type' => 1,
						'rec_amount' => $transaction->price,
						'rec_amount_total' => $transaction->price,
						'res_mail' => $user->user_email,
						'rec_extra' => ''
					)
				));
			}
			$xml_array['request_date'] = $now;
			$xml_array['sps_hashcode'] = $this->get_hash_key_from_array($xml_array);
			return $this->get_request($this->make_xml($action, $xml_array), true);
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
	 * Returns if this user has registered information
	 * @param int $user_id
	 * @return boolean
	 */
	public function has_saved_cc($user_id){
		return (boolean)  get_user_meta($user_id, $this->meta_cc_number_flg, true);
	}
	
	/**
	 * Returns saved credit card information
	 * @param int $user_id
	 * @return array
	 */
	public function get_cc_information($user_id){
		if(!$this->has_saved_cc($user_id)){
			return array();
		}
		$xml_array = array(
			'merchant_id' => $this->marchant_id(),
			'service_id' => $this->service_id(),
			'cust_code' => $this->generate_user_id($user_id),
			'sps_cust_info_return_flg' => 0,
			'response_info_type' => 2,
			'encrypted_flg' => intval(!$this->is_sandbox),
			'request_date' => date('YmdHis', current_time('timestamp'))
		);
		$xml_array['sps_hashcode'] = $this->get_hash_key_from_array($xml_array);
		$result = $this->get_request($this->make_xml(self::USER_INFO, $xml_array));
		if($result['success']){
			return array(
				'number' => $this->decrypt((string)$result['body']->res_pay_method_info->cc_number),
				'expiration' => preg_replace('/([0-9]{4})([0-9]{2})/', '$1-$2-01 00:00:00', $this->decrypt((string)$result['body']->res_pay_method_info->cc_expiration))
			);
		}else{
			return array();
		}		
	}
	
	/**
	 * Return string to XML and get request.
	 * @param string $xml
	 * @return array contains from success, message, body
	 */
	private function get_request($xml, $pseudo = false){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $pseudo ? lwp_endpoint('sb-payment') : $this->get_endpoint());
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERPWD, $this->marchant_id().$this->service_id().":".$this->hash_key());
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
		curl_setopt($ch, CURLOPT_SSLVERSION, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		$result = curl_exec($ch);
		if($pseudo){
			curl_close($ch);
			return $result;
		}
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
					$response['message'] = $this->_('Transaction is succeeded.');
				}else{
					$code = substr($xml->res_err_code, 3, 2);
					if(defined('WP_DEBUG') && WP_DEBUG){
						$response['message'] = '['.$xml->res_err_code.'] ';
					}
					if(array_key_exists($code, $this->error_msg) && defined('WP_DEBUG') && WP_DEBUG ){
						$response['message'] .= $this->error_msg[$code];
					}else{
						$response['message'] = '['.$xml->res_err_code.'] '.$this->_('Sorry, but connection is failed. Please try again later.');
					}
				}
			}else{
				$response['message'] = $this->_('Sorry, but connection is failed. Please try again later.');
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
	private function make_xml($action, $xml_array, $response = false){
		$tag = $this->make_tag('', $xml_array);
		$xml_type = $response ? 'response' : 'request';
		$xml = '<?xml version="1.0" encoding="Shift_JIS" ?>'."\n".
			sprintf('<sps-api-%s id="%s">', $xml_type, $action)."\n";
		$xml .= $tag."</sps-api-{$xml_type}>";
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
			return empty($key) ? $tag : sprintf('<%1$s>%2$s</%1$s>', $key, $tag)."\n";
		}else{
			if(empty($key)){
				return $value;
			}else{
				if(false !== array_search($key, $this->tag_to_be_base64)){
					$value = base64_encode(mb_convert_encoding($value, 'sjis-win', 'utf-8'));
				}
				
				return sprintf('<%1$s>%2$s</%1$s>', $key, $this->crypt($key, $value))."\n";
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
	 * Check SimpleXMLElement has valid checksum.
	 * @param SimpleXMLElement $xml
	 * @return boolean
	 */
	private function check_sum($xml){
		switch($xml['id']){
			case self::CVS_CANCEL_RESPONSE_CODE:
			case self::PAYEASY_CANCEL_RESPONSE_CODE:
				$hash_source = array($xml->merchant_id, $xml->service_id, $xml->sps_transaction_id, $xml->tracking_id, $xml->rec_datetime, $xml->request_date);
				break;
			case self::CVS_SUCCESS_RESPONSE_CODE:
			case self::PAYEASY_SUCCESS_RESPONSE_CODE:
				$hash_source = array(
					$xml->merchant_id, $xml->service_id, $xml->sps_transaction_id, $xml->tracking_id, $xml->rec_datetime,
					$this->decrypt((string)$xml->pay_method_info->rec_type, true),
					$this->decrypt((string)$xml->pay_method_info->rec_amount, true),
					$this->decrypt((string)$xml->pay_method_info->rec_amount_total, true),
					$this->decrypt((string)$xml->pay_method_info->res_mail, true),
					base64_decode($this->decrypt((string)$xml->pay_method_info->rec_extra, true)),
					$xml->request_date
				);
				break;
			default:
				return false;
				break;
		}
		$hash = sha1(implode('', array_map('trim', array_map('strval', $hash_source))).$this->hash_key());
		return ($hash == (string)$xml->sps_hashcode);
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
			$this->key_store[] = mb_convert_encoding($value, 'sjis-win', 'utf-8');
		}
	}
	
	/**
	 * Crypt data to Softbank way
	 * @param string $key
	 * @param string $string
	 * @return string
	 */
	private function crypt($key, $string){
		if(function_exists('mcrypt_cbc') && !empty($string) && (
			(!$this->is_sandbox && false !== array_search($key, $this->tag_to_be_crypt))
				||
			(false !== array_search($key, $this->tag_to_be_crypt_force))
		)){
			// Padding the text
			$surplus = strlen($string) % 8;
			if($surplus > 0){
				for($i = $surplus; $i < 8; $i++){
					$string .= ' ';
				}
			}
			// Crypting
			$resource = mcrypt_module_open(MCRYPT_3DES, '',  MCRYPT_MODE_CBC, '');;
			mcrypt_generic_init($resource, $this->crypt_key, $this->iv);
			$encrypted_data = mcrypt_generic($resource, $string);
			mcrypt_generic_deinit($resource);
			$string = base64_encode($encrypted_data);
			mcrypt_module_close($resource);
		}
		return $string;
	}
	
	/**
	 * Returns crypted data to decrypt
	 * @param string $string
	 * @return string
	 */
	private function decrypt($string, $force = false){
		if(!empty($string) && function_exists('mcrypt_cbc') && (!$this->is_sandbox || $force) ){
			$resource = mcrypt_module_open(MCRYPT_3DES, '',  MCRYPT_MODE_CBC, '');;
			mcrypt_generic_init($resource, $this->crypt_key, $this->iv);
			$decrypted_data = mdecrypt_generic($resource, base64_decode($string));
			mcrypt_generic_deinit($resource);
			$string = mb_convert_encoding(trim($decrypted_data), 'utf-8', 'sjis-win');
		}
		return strval($string);
	}
	
	/**
	 * check if Softban Payment is enabled.
	 * @return boolean
	 */
	public function is_enabled() {
		return (!empty($this->_marchant_id) && !empty($this->_service_id) && !empty($this->_hash_key) && parent::is_enabled());
	}
	
	/**
	 * Returns Marchand ID
	 * @param boolean $deprecated No longer used.
	 * @return string
	 */
	public function marchant_id($deprecated = false){
		return $this->_marchant_id;
	}
	
	/**
	 * Returns Service ID
	 * @param boolean $deprecated No longer used.
	 * @return string
	 */
	public function service_id($deprecated = false){
		return $this->_service_id;
	}
	
	/**
	 * Returns Hash key
	 * @param boolean $deprecated No longer used.
	 * @return string
	 */
	public function hash_key($deprecated = false){
		return $this->_hash_key;
	}
	
	/**
	 * Returns endpoint
	 * @return string
	 */
	public function get_endpoint($force = false){
		return ($this->is_sandbox && !$force)
			? self::PAYMENT_ENDPOINT_SANDBOX
			: self::PAYMENT_ENDPOINT_PRODUCTION;
	}	
	
	public function vendor_name($short = false){
		return $short
			? $this->_('Softbank Payment')
			: $this->_('SOFTBANK Payment Service corp.');
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
}