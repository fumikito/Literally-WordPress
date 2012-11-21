<?php
class LWP_GMO extends LWP_Japanese_Payment {
	
	/**
	 * Shop Password
	 * @var string
	 */
	public $shop_id = '';
	
	/**
	 * Shop Password
	 * @var string
	 */
	public $shop_pass = '';
	
	/**
	 * CVS list
	 * @var array 
	 */
	protected $_webcvs = array(
		'seven-eleven' => false,
		'lawson' => false,
		'circle-k' => false,
		'sunkus' => false,
		'ministop' => false,
		'familymart' => false,
		'daily-yamazaki' => false,
	);
	
	/**
	 * CVS code name
	 * @var array
	 */
	protected $cvs_codes = array(
		'lawson' => '00001',
		'familymart' => '00002',
		'sunkus' => '00003',
		'circle-k' => '00004',
		'ministop' => '00005',
		'daily-yamazaki' => '00006',
		'seven-eleven' => '00007',
	);
	
	/**
	 * Contact tel No.
	 * @var string
	 */
	public $tel_no = '';
	
	/**
	 * Contact start time.(HH:MM)
	 * @var string
	 */
	public $contact_starts = '';
	
	/**
	 * Contact end time.(HH:MM)
	 * @var string
	 */
	public $contact_ends = '';
	
	/**
	 * Setup Option
	 * @param type $option
	 */
	public function set_option($option = array()) {
		$option = shortcode_atts(array(
			'gmo_shop_id' => '',
			'gmo_shop_pass' => '',
			'gmo_webcvs' => array(),
			'gmo_payeasy' => false,
			'gmo_sandbox' => true,
			'gmo_creditcard' => array(),
			'gmo_tel' => '',
			'gmo_contact_starts' => '',
			'gmo_contact_ends' => ''
		), $option);
		$this->shop_id = (string)$option['gmo_shop_id'];
		$this->shop_pass = (string)$option['gmo_shop_pass'];
		$this->is_sandbox = (boolean)$option['gmo_sandbox'];
		foreach($this->_creditcard as $cc => $bool){
			$this->_creditcard[$cc] = (false !== array_search($cc, (array)$option['gmo_creditcard']));
		}
		foreach($this->_webcvs as $cvs => $bool){
			$this->_webcvs[$cvs] = (false !== array_search($cvs, (array)$option['gmo_webcvs']));
		}
		$this->payeasy = (boolean)$option['gmo_payeasy'];
		$this->tel_no = (string)$option['gmo_tel'];
		$this->contact_starts = (string)$option['gmo_contact_starts'];
		$this->contact_ends = (string)$option['gmo_contact_ends'];
	}
		
	public function on_construct() {
		if($this->is_enabled()){
			require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'gmo_endpoints.php';
		}
	}
	
	/**
	 * Do transaction and returns transaciton ID
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param int $user_id
	 * @param string $item_name
	 * @param int $post_id
	 * @param int $price
	 * @param int $quantity
	 * @param string $cc_number
	 * @param string $cc_sec
	 * @param string $expiration YYYYMM
	 * @return int
	 */
	public function do_credit_authorization($user_id, $item_name, $post_id, $price, $quantity, $cc_number, $cc_sec, $expiration){
		global $wpdb, $lwp;
		$order_id = $this->generate_order_id();
		$now = gmdate('Y-m-d H:i:s');
		$result = GMO_Endpoints::exec_tran_cc($this->is_sandbox, array(
			'ShopID' => $this->shop_id,
			'ShopPass' => $this->shop_pass,
			'OrderID' => $order_id,
			'JobCd' => JOBCODE_CAPTURE,
			'Amount' => $price,
			'Tax' => 0,
			'TdFlag' => 0,
			'Method' => METHOD_IKKATU,
			'PayTimes' => 1,
			'CardNo' => $cc_number,
			'Expire' => substr($expiration, 2, 4),
			'SecurityCode' => $cc_sec
		));
		
		//Check result
		if(!$result['success']){
			//Error occurred
			$this->last_error = implode(' ', $result['message']);
			return 0;
		}else{
			//Success
			$wpdb->insert($lwp->transaction, array(
				"user_id" => $user_id,
				"book_id" => $post_id,
				"price" => $price,
				"status" => LWP_Payment_Status::SUCCESS,
				"method" => LWP_Payment_Methods::GMO_CC,
				"transaction_key" => $order_id,
				'transaction_id' => $result['params']['TranID'],
				'payer_mail' => $result['params']['Approve'],
				"registered" => $now,
				"updated" => $now,
				'misc' => serialize(array(
					'access_id' => $result['params']['AccessID'],
					'access_pass' => $result['params']['AccessPass']
				))
			), array('%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ));
			return $wpdb->insert_id;
			
		}
	}
	
	/**
	 * Do CVS transaction and save result.
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param int $user_id
	 * @param string $item_name
	 * @param int $post_id
	 * @param price $price
	 * @param int $quantity
	 * @param array $creds
	 * @return int
	 */
	public function do_cvs_authorization($user_id, $item_name, $post_id, $price, $quantity, $creds){
		global $lwp, $wpdb;
		$order_id = $this->generate_order_id();
		$now = gmdate('Y-m-d H:i:s');
		$user = get_userdata($user_id);
		$order_ids = explode('-', $order_id);
		$reserve_no = $order_ids[1].'-'.$order_ids[2];
		$blog_name = $this->convert_sjis(trim(mb_convert_kana(get_bloginfo('name'), 'ASKV', 'utf-8')));
		$result = GMO_Endpoints::exec_tran_cvs($this->is_sandbox, array(
			'ShopID' => $this->shop_id,
			'ShopPass' => $this->shop_pass,
			'OrderID' => $order_id,
			'Amount' => $price,
			'Tax' => 0,
			'Convenience' => $this->get_cvs_code($creds['cvs']),
			'CustomerName' => $this->convert_sjis( trim($creds['last_name'].$creds['first_name'])),
			'CustomerKana' => $this->convert_sjis( trim($creds['last_name_kana'].$creds['first_name_kana'])),
			'TelNo' => $this->convert_numeric($creds['tel']),
			'MailAddress' => $user->user_email,
			'ReserveNo' => $reserve_no,
			'MemberNo' => sprintf('%08d', $user_id),
			'PaymentTermDay' => floor(($this->get_payment_limit($post_id, false, 'gmo-cvs') - current_time('timestamp')) / 60 / 60 / 24),
			'RegisterDisp1' => $blog_name,
			'ReceiptsDisp1' => $this->convert_sjis(trim(mb_convert_kana($this->_('Thank you for your order.'), 'ASKV', 'utf-8'))),
			'ReceiptsDisp11' => $blog_name,
			'ReceiptsDisp12' => $this->convert_numeric($this->tel_no),
			'ReceiptsDisp13' => $this->contact_starts.'-'.$this->contact_ends
		));
		//Check result
		if(!$result['success']){
			//Error occurred
			$this->last_error = implode(' ', $result['message']);
			return 0;
		}else{
			$inserted = $wpdb->insert($lwp->transaction,array(
				"user_id" => $user_id,
				"book_id" => $post_id,
				"price" => $price,
				"status" => LWP_Payment_Status::START,
				"method" => LWP_Payment_Methods::GMO_WEB_CVS,
				"transaction_key" => $order_id,
				"registered" => $now,
				"updated" => $now,
				'misc' => serialize(array(
					'cvs' => $creds['cvs'],
					'access_id' => $result['params']['AccessID'],
					'access_pass' => $result['params']['AccessPass'],
					'bill_date' => preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "$1-$2-$3 $4:$5:$6", $result['params']['PaymentTerm']),
					'conf_no' => $result['params']['ConfNo'],
					'receipt_no' => $result['params']['ReceiptNo'],
				))
			), array('%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s'));
			if($inserted){
				return $wpdb->insert_id;
			}else{
				$this->last_error = $this->_('Payment requeist is succeeded, but failed to finish transaction. Please contact to Administrator.');
				return 0;
			}
		}
	}
	
	
	/**
	 * Do PayEasy transaction and save result.
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param int $user_id
	 * @param string $item_name
	 * @param int $post_id
	 * @param price $price
	 * @param int $quantity
	 * @param array $creds
	 * @return int
	 */
	public function do_payeasy_authorization($user_id, $item_name, $post_id, $price, $quantity, $creds){
		global $lwp, $wpdb;
		$order_id = $this->generate_order_id();
		$now = gmdate('Y-m-d H:i:s');
		$user = get_userdata($user_id);
		$blog_name = $this->convert_sjis(mb_convert_kana(get_bloginfo('name'), 'ASKV', 'utf-8'));
		$result = GMO_Endpoints::exec_tran_payeasy($this->is_sandbox, array(
			'ShopID' => $this->shop_id,
			'ShopPass' => $this->shop_pass,
			'OrderID' => $order_id,
			'Amount' => $price,
			'Tax' => 0,
			'Convenience' => $this->get_cvs_code($creds['cvs']),
			'CustomerName' => $this->convert_sjis( trim($creds['last_name'].$creds['first_name'])),
			'CustomerKana' => $this->convert_sjis( trim($creds['last_name_kana'].$creds['first_name_kana'])),
			'TelNo' => $this->convert_numeric($creds['tel']),
			'MailAddress' => $user->user_email,
			'PaymentTermDay' => floor(($this->get_payment_limit($post_id, false, 'gmo-payeasy') - current_time('timestamp')) / 60 / 60 / 24),
			'RegisterDisp1' => $blog_name,
			'ReceiptsDisp1' => $this->convert_sjis(trim(mb_convert_kana($this->_('Thank you for your order.'), 'ASKV', 'utf-8'))),
			'ReceiptsDisp11' => $blog_name,
			'ReceiptsDisp12' => $this->convert_numeric($this->tel_no),
			'ReceiptsDisp13' => $this->contact_starts.'-'.$this->contact_ends
		));
		//Check result
		if(!$result['success']){
			//Error occurred
			$this->last_error = implode(' ', $result['message']);
			return 0;
		}else{
			//例外発生せず、エラーの戻りもなく、3Dセキュアフラグもオフであるので、実行結果を表示します。
			$inserted = $wpdb->insert($lwp->transaction,array(
				"user_id" => $user_id,
				"book_id" => $post_id,
				"price" => $price,
				"status" => LWP_Payment_Status::START,
				"method" => LWP_Payment_Methods::GMO_PAYEASY,
				"transaction_key" => $order_id,
				"registered" => $now,
				"updated" => $now,
				'misc' => serialize(array(
					'access_id' => $result['params']['AccessID'],
					'access_pass' => $result['params']['AccessPass'],
					'bill_date' => preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "$1-$2-$3 $4:$5:$6", $result['params']['PaymentTerm']),
					'conf_no' => $result['params']['ConfNo'],
					'bkcode' => $result['params']['BkCode'],
					'cust_id' => $result['params']['CustID'],
					'encrypted_receipt_no' => $result['params']['EncryptReceiptNo']
				))
			), array('%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s'));
			if($inserted){
				return $wpdb->insert_id;
			}else{
				$this->last_error = $this->_('Payment requeist is succeeded, but failed to finish transaction. Please contact to Administrator.');
				return 0;
			}
		}
	}
	
	/**
	 * Parse Request from GMO Payment
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param array $data
	 * @return boolean
	 */
	public function parse_notification($post){
		global $lwp, $wpdb;
		//Parse request from GMO
		//Check Proper request
		if(!isset($post['PayType'], $post['Status'], $post['ShopID'], $post['AccessID'], $post['OrderID'])){
			return false;
		}
		if($post['ShopID'] != $this->shop_id){
			return false;
		}
		//Check order exists
		$transaction = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$lwp->transaction} WHERE transaction_key = %s", $post['OrderID']));
		if(!$transaction){
			return false;
		}
		//Check access ID
		$data = unserialize($transaction->misc);
		if(!isset($data['access_id']) || $data['access_id'] != $post['AccessID']){
			return false;
		}
		//Check Payment type
		switch($post['PayType']){
			case '0': //Credit card
				if(!isset($post['TranID']) || $post['TranID'] != $transaction->transaction_id){
					return false;
				}
				break;
			case '3': //CVS
				if(!isset($post['CvsConfNo']) || $post['CvsReceiptNo'] != $data['receipt_no']){
					return false;
				}
				break;
			case '4': //PayEasy
				if(!isset($post['EncryptReceiptNo']) || $post['EncryptReceiptNo'] != $data['encrypted_receipt_no']){
					return false;
				}
				break;
			default:
				return false;
				break;
		}
		//Check status
		switch($post['Status']){
			case 'UNPROCESSED':
			case 'REQSUCCESS':
				return false;
				break;
			case 'AUTH':
			case 'SAUTH':
				return false;
				break;
			case 'CAPTURE':
			case 'PAYSUCCESS':
			case 'SALES':
				$status = LWP_Payment_Status::SUCCESS;
				break;
			case 'CANCEL':
			case 'EXPIRED':
			case 'PAYFAIL':
			case 'VOID':
				$status = LWP_Payment_Status::CANCEL;
				break;
			case 'RETURN':
			case 'RETURNX':
				$status = LWP_Payment_Status::REFUND;
				break;
			case 'AUTHENTICATED':
			case 'AUTHPROCESS':
			case 'CHECK':
			default:
				return false;
				break;
		}
		if($status != $transaction->status){
			//Status was changed.
			$updated = $wpdb->update($lwp->transaction, array(
				'status' => $status,
				'updated' => gmdate('Y-m-d H:i:s')
			), array('ID' => $transaction->ID), array('%s', '%s'), array('%d'));
			if($updated){
				do_action('lwp_update_transaction', $transaction->ID);
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	/**
	 * Generate uniq transaction ID
	 * @return string
	 */
	private function generate_order_id(){
		return uniqid(sprintf('%s-%02d-', preg_replace('/[^0-9]/', '', $this->shop_id), rand(0,99)), false);
	}
	
	
	/**
	 * Convert Encoding to SJIS
	 * @param type $string
	 * @return type
	 */
	private function convert_sjis($string){
		return mb_convert_encoding($string, 'sjis-win', 'utf-8');
	}
	
}