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
	
	/**
	 * Do on construct
	 */
	public function on_construct() {
		if($this->is_enabled()){
			set_include_path(get_include_path() . PATH_SEPARATOR . $this->dir.'libs'.DIRECTORY_SEPARATOR.'gmo');
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
		//Include Required files.
		require_once('com/gmo_pg/client/common/Method.php');
		require_once('com/gmo_pg/client/common/JobCode.php');
		require_once('com/gmo_pg/client/common/ErrorHAndler.php');
		require_once('com/gmo_pg/client/input/EntryTranInput.php');
		require_once('com/gmo_pg/client/input/ExecTranInput.php');
		require_once('com/gmo_pg/client/input/EntryExecTranInput.php');
		require_once('com/gmo_pg/client/tran/EntryExecTran.php');

		//取引登録時に必要なパラメータ
		$entryInput = new EntryTranInput();
		$entryInput->setShopId( $this->shop_id );
		$entryInput->setShopPass( $this->shop_pass );
		$entryInput->setJobCd(JOBCODE_CAPTURE);
		$entryInput->setOrderId($order_id);
		//$entryInput->setItemCode( $_POST['ItemCode'] );
		$entryInput->setAmount($price);
		$entryInput->setTax(0);
		$entryInput->setTdFlag(0);
		//$entryInput->setTdTenantName( $_POST['TdTenantName']);
		//決済実行のパラメータ
		$execInput = new ExecTranInput();
		$execInput->setOrderId($order_id);
		$execInput->setMethod( METHOD_IKKATU );
		$execInput->setCardNo($cc_number);
		$execInput->setExpire(substr($expiration, 2, 4));
		$execInput->setSecurityCode($cc_sec);
		//取引登録＋決済実行の入力パラメータクラスをインスタンス化します
		$input = new EntryExecTranInput();
		$input->setEntryTranInput( $entryInput );
		$input->setExecTranInput( $execInput );
		//API通信クラスをインスタンス化します
		$exe = new EntryExecTran();
		//パラメータオブジェクトを引数に、実行メソッドを呼びます。
		//正常に終了した場合、結果オブジェクトが返るはずです。
		$output = $exe->exec( $input );
		//実行後、その結果を確認します。
		if( $exe->isExceptionOccured() ){
			//取引の処理そのものがうまくいかない（通信エラー等）場合、例外が発生します。
			$this->last_error = $this->_('Connection Error');
			return 0;
		}else{
			//例外が発生していない場合、出力パラメータオブジェクトが戻ります。
			if( $output->isErrorOccurred() ){
				//出力パラメータにエラーコードが含まれていないか、チェックしています。
				$msg = array();
				foreach(array_merge($output->getExecErrList(), $output->getEntryErrList()) as $errInfo){
					/* @var $errInfo ErrHolder */
					$msg[] = ((defined('WP_DEBUG') && WP_DEBUG) ? '['.$errInfo->getErrInfo().']' : '').
							GMOErrorHandler::get_error_message($errInfo->getErrInfo());
				}
				$this->last_error = implode(' ', $msg);
				return 0;
			}else{
				//例外発生せず、エラーの戻りもなく、3Dセキュアフラグもオフであるので、実行結果を表示します。
				$wpdb->insert($lwp->transaction, array(
					"user_id" => $user_id,
					"book_id" => $post_id,
					"price" => $price,
					"status" => LWP_Payment_Status::SUCCESS,
					"method" => LWP_Payment_Methods::GMO_CC,
					"transaction_key" => $order_id,
					'transaction_id' => $output->getTranId(),
					'payer_mail' => $output->getApprovalNo(),
					"registered" => $now,
					"updated" => $now,
					'misc' => serialize(array(
						'access_id' => $output->getAccessId(),
						'access_pass' => $output->getAccessPass()
					))
				), array('%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ));
				return $wpdb->insert_id;
			}
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
		require_once('com/gmo_pg/client/common/ErrorHAndler.php');
		require_once('com/gmo_pg/client/input/EntryTranCvsInput.php');
		require_once('com/gmo_pg/client/input/ExecTranCvsInput.php');
		require_once('com/gmo_pg/client/input/EntryExecTranCvsInput.php');
		require_once('com/gmo_pg/client/tran/EntryExecTranCvs.php');
		$order_id = $this->generate_order_id();
		$now = gmdate('Y-m-d H:i:s');
		$user = get_userdata($user_id);
		$order_ids = explode('-', $order_id);
		$reserve_no = $order_ids[1].'-'.$order_ids[2];
		//取引登録時に必要なパラメータ
		$entryInput = new EntryTranCvsInput();
		$entryInput->setShopId($this->shop_id);
		$entryInput->setShopPass($this->shop_pass);
		$entryInput->setOrderId($order_id);
		$entryInput->setAmount($price);
		$entryInput->setTax(0);
		//決済実行のパラメータ
		$execInput = new ExecTranCvsInput();
		$execInput->setOrderId($order_id);
		//メールアドレス
		$execInput->setConvenience($this->get_cvs_code($creds['cvs']));
		$execInput->setCustomerName($this->convert_sjis( $creds['last_name'].$creds['first_name']));
		$execInput->setCustomerKana($this->convert_sjis( $creds['last_name_kana'].$creds['first_name_kana']) );
		$execInput->setTelNo($this->convert_numeric($creds['tel']));
		$execInput->setMailAddress($user->user_email);
		$execInput->setReserveNo($reserve_no);
		$execInput->setMemberNo(sprintf('%08d', $user_id));
		//支払い期限日
		$left_days = floor(($this->get_payment_limit($post_id, false, 'gmo-cvs') - time()) / 60 / 60 / 24);
		$execInput->setPaymentTermDay($left_days);
		//表示項目・問い合わせ情報
		$blog_name = $this->convert_sjis(mb_convert_kana(get_bloginfo('name'), 'ASKV', 'utf-8'));
		$execInput->setRegisterDisp1($blog_name);
		$execInput->setReceiptsDisp1($this->convert_sjis(mb_convert_kana($this->_('Thank you for your order.'), 'ASKV', 'utf-8')));
		$execInput->setReceiptsDisp11($blog_name);
		$execInput->setReceiptsDisp12($this->convert_numeric($this->tel_no));
		$execInput->setReceiptsDisp13($this->contact_starts.'-'.$this->contact_ends);
		//取引登録＋決済実行の入力パラメータクラスをインスタンス化します
		/* @var $input EntryExecTranCvsInput */
		$input = new EntryExecTranCvsInput();
		$input->setEntryTranCvsInput( $entryInput );
		$input->setExecTranCvsInput( $execInput );
		//API通信クラスをインスタンス化します
		/* @var $exec EntryExecTranCvs */
		$exe = new EntryExecTranCvs();
		//パラメータオブジェクトを引数に、実行メソッドを呼びます。
		//正常に終了した場合、結果オブジェクトが返るはずです。
		/* @var $output EntryExecTranCvsOutput */
		$output = $exe->exec( $input );
		//実行後、その結果を確認します。
		if( $exe->isExceptionOccured() ){
			//取引の処理そのものがうまくいかない（通信エラー等）場合、例外が発生します。
			$this->last_error = $this->_('Connection Error');
			return 0;
		}else{
			//例外が発生していない場合、出力パラメータオブジェクトが戻ります。
			if( $output->isErrorOccurred() ){//出力パラメータにエラーコードが含まれていないか、チェックしています。
				$msg = array();
				foreach(array_merge($output->getExecErrList(), $output->getEntryErrList()) as $errInfo){
					/* @var $errInfo ErrHolder */
					$msg[] = ((defined('WP_DEBUG') && WP_DEBUG) ? '['.$errInfo->getErrInfo().']' : '').
							GMOErrorHandler::get_error_message($errInfo->getErrInfo());
				}
				$this->last_error = implode(' ', $msg);
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
						'access_id' => $output->getAccessId(),
						'access_pass' => $output->getAccessPass(),
						'bill_date' => preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "$1-$2-$3 $4:$5:$6", $output->getPaymentTerm()),
						'conf_no' => $output->getConfNo(),
						'receipt_no' => $output->getReceiptNo(),
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
	}
	
	/**
	 * Generate uniq transaction ID
	 * @return string
	 */
	private function generate_order_id(){
		return uniqid(sprintf('%s-%02d-', preg_replace('/[^0-9]/', '', $this->shop_id), rand(0,99)), false);
	}
	
	/**
	 * Returns payment limit for specified post
	 * @global Literally_WordPress $lwp
	 * @param int|object $post
	 * @param int $time
	 * @param string $context
	 * @return int
	 */
	public function get_payment_limit($post, $time = false, $context = 'gmo-cvs'){
		global $lwp;
		if(!$time){
			$time = current_time('timestamp');
		}
		$post = get_post($post);
		if($post->post_type == $lwp->event->post_type){
			$selling_limit = get_post_meta($post->post_parent, $lwp->event->meta_selling_limit, TRUE);
			if($selling_limit && preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $selling_limit)){
				$selling_limit = strtotime($selling_limit.' 23:59:59');
			}else{
				$selling_limit = false;
			}
		}else{
			$selling_limit = false;
		}
		switch($context){
			case 'gmo-cvs':
				$time += (60 * 60 * 24 * 30) - 1; //TODO: 設定値は変更できる
				break;
			case 'gmo-payeasy':
				$time += (60 * 60 * 24 * 30) - 1; //TODO: 設定値は変更できる
				break;
		}
		return $selling_limit ? min($time, $selling_limit) : $time;
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