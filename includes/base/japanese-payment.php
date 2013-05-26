<?php
/**
 * Common class for Japanese Utility
 * @since 0.9.3
 */
class LWP_Japanese_Payment extends Literally_WordPress_Common {
	
	/**
	 * If this is sandbox
	 * @var type 
	 */
	public $is_sandbox = true;
	
	/**
	 * Last error message
	 * @var string
	 */
	public $last_error = '';

	/**
	 * creditcard list
	 * @var array
	 */
	protected $_creditcard = array(
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
	protected $_webcvs = array(
		'seven-eleven' => false,
		'lawson' => false,
		'circle-k' => false,
		'sunkus' => false,
		'ministop' => false,
		'familymart' => false,
		'daily-yamazaki' => false,
		'seicomart' => false
	);

	/**
	 * CVS code. Must be overriden.
	 * @var array
	 */
	protected $cvs_codes = array();
	
	/**
	 * Limit day to pay with Web CVS
	 * @var int
	 */
	public $cvs_limit = 30;
	
	/**
	 * Limit day to pay with PayEasy
	 * @var int
	 */
	public $payeasy_limit = 30;
	
	/**
	 * If PayEasy is enabled
	 * @var boolean
	 */
	public $payeasy = false;
	
	/**
	 * Offline payment method
	 * @var array
	 */
	protected $offline_context = array();
	
	/**
	 * Stealth mode flg
	 * @var boolean
	 */
	public $is_stealth = false;

	/**
	 * override parent constructor
	 * @param array $option
	 */
	public function __construct($option = array()) {
		parent::__construct($option);
		$this->register_offline_methods();
		//Hook on off line paymen cancelation
		if($this->is_enabled()){
			add_action('lwp_update_transaction', array($this, 'offline_payment_cancelation'));
		}
	}


	/**
	 * Register offline methods to hook on notification
	 */
	protected function register_offline_methods(){}
	
	/**
	 * Returns if CVS is enabled
	 * @return boolean
	 */
	public function is_cvs_enabled(){
		$cvs = $this->get_available_cvs();
		return !empty($cvs);
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
	 * 
	 * @return boolean
	 */
	public function is_enabled() {
		return (boolean)($this->is_cc_enabled() || $this->is_cvs_enabled() || $this->payeasy);
	}
	
	
	/**
	 * Get user default meta data 
	 * @param int $user_id
	 * @return string
	 */
	protected function get_user_default($user_id, $context){
		$user_info = array();
		$default_keys = array();
		switch($context){
			case 'sb-cc':
			case 'gmo-cc':
				$default_keys = array();
				break;
			case 'sb-cvs':
			case 'sb-payeasy':
				$default_keys = array('last_name','first_name','last_name_kana','first_name_kana',
			'zipcode','prefecture','city','street','office','tel');
				break;
			case 'gmo-cvs':
			case 'gmo-payeasy':
				$default_keys = array('last_name','first_name','last_name_kana','first_name_kana','tel');
				break;
			default:
				return array();
				break;
		}
		$meta_keys = apply_filters('lwp_user_default_key', $default_keys, $context);
		foreach($meta_keys as $key){
			$user_info[$key] = get_user_meta($user_id, $key, true);
		}
		return $user_info;
	}
	
	/**
	 * Get default value
	 * @param string $context
	 * @param int $user_id
	 * @param string $context
	 * @return string
	 */
	public function get_default_payment_info($user_id, $context = 'sb-cc'){
		$user_info = $this->get_user_default($user_id, $context);
		return apply_filters('lwp_get_default_payment_info', $user_info, $context, $user_id);
	}
	
	/**
	 * Save payment information on success
	 * @param int $user_id
	 * @param array $post_data
	 * @param string $context
	 */
	public function save_payment_info($user_id, $post_data, $context = 'sb-cc'){
		$func = apply_filters('lwp_save_payment_info_func', array($this, 'save_user_default'), $context);
		if(is_callable($func)){
			call_user_func_array($func, array($user_id, $post_data, $context));
		}
	}
	
	/**
	 * Default function to save user payment information.
	 * @param int $user_id
	 * @param array $post_data
	 * @param string save_user_default$context
	 */
	public function save_user_default($user_id, $post_data, $context = 'sb-cc'){
		$keys = $this->get_default_payment_info($user_id, $context);
		foreach($keys as $key => $value){
			if(isset($post_data[$key])){
				update_user_meta($user_id, $key, $post_data[$key]);
			}
		}
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
			case 'daily-yamazaki':
				return 'デイリーヤマザキ';
				break;
			case 'seicomart':
				return 'セイコーマート';
		}
	}
	
	/**
	 * Returns CVS code
	 * @param string $slug
	 * @return string
	 */
	public function get_cvs_code($slug){
		if(isset($this->cvs_codes[$slug])){
			return $this->cvs_codes[$slug];
		}else{
			return '';
		}
	}
	
	/**
	 * Description for Web CVS
	 * @param string $cvs
	 * @param boolean $requirements If set to true, returns array of required information
	 * @return string|array
	 */
	public function get_cvs_howtos($cvs){
		switch($cvs){
			case 'seven-eleven':
				return '決済後、画面に表示された「払込票番号」を印刷またはメモし、それをセブンイレブンへ持ち込み、レジにてオンライン決済である旨をお伝えください。';
				break;
			case 'lawson':
				return '店頭のLoppi端末にて、決済を選択後「受付番号」「確認番号」を入力、その後、端末から出力された申込券をレジに持参してお支払いを行います。';
				break;
			case 'circle-k':
			case 'sunkus':
			case 'ministop':
			case 'daily-yamazaki':
				return '店頭のカルワザステーション（サークルKサンクスのみ）でオンライン決済番号および確認番号を入力して受付票をレジまでお持ち頂くか、レジにて店員にオンライン決済番号をお伝えください。';
				break;
			case 'familymart':
				return '店頭のFamiポート（またはファミネット）端末にて、決済を選択後「企業コード」と「注文番号」を入力してください。その後、端末から出力された「申込券／収納票」をレジに持参してお支払いを行います。';
				break;
			case 'seicomart':
				return '店頭のクラブステーション端末にて、決済を選択後「受付番号」「確認番号」を入力してください。その後、端末から出力された申込券をレジに持参してお支払いを行います。';
				break;
			default:
				return '';
		}
	}
	
	/**
	 * Returns payeasy notice.
	 * @param type $type
	 * @param string $rtag if set false, raw string returns
	 * @return array|string
	 */
	public function get_payeasy_notice($tag = 'li'){
		$desc = array(
			'notice' => array(
				'お支払いの際、<strong>収納機関番号</strong>、<strong>お客様番号</strong>、<strong>確認番号</strong>が必要です。メモを取るか、このページを印刷してお持ちください。',
				'みずほ銀行、りそな銀行、埼玉りそな銀行、三井住友銀行、ゆうちょ銀行、ちばぎんのATMでお支払いいただけます。',
				'一部時間外手数料が発生する金融機関がございます。詳しくはお取引の金融機関にお問合せください。 ',
				'法令改正のため、2007年1月4日より、<strong>ATMから10万円を超える現金の振込</strong>はできなくなりました。',
				'ご利用明細票が領収書となりますので、お支払い後必ずお受け取りください。',
				'ネットバンキングでのお支払いは金融機関に<strong>あらかじめ口座をお持ちの場合のみ</strong>ご利用いただけます。'
			),
			'atm' => array(
				'上記の金融機関のATMで、<strong>「税金・料金払込み」</strong>を選択してください。',
				'<strong>収納機関番号</strong>を入力し、<strong>「確認」</strong>を選択してください。',
				'<strong>お客様番号</strong>を入力し、<strong>「確認」</strong>を選択してください。',
				'<strong>確認番号</strong>を入力し、<strong>「確認」</strong>を選択してください。',
				'表示される内容を確認のうえ、<strong>「確認」</strong>を選択してください。',
				'<strong>「現金」</strong>または<strong>「キャッシュカード」</strong>を選択し、お支払いください。',
				'ご利用明細票を必ずお受け取りください。',
			),
			'net' => array(
				'ご利用の金融機関の案内に従って、<strong>ペイジーでのお支払い</strong>にお進みください。',
				'<strong>収納機関番号</strong>、<strong>お客様番号</strong>、<strong>確認番号</strong>を入力してください。',
				'お支払い内容を確認のうえ、料金をお支払いください。'
			)
		);
		$return = array();
		if($tag){
			foreach($desc as $key => $d){
				$return[$key] = implode('', array_map(create_function('$var', 'return "<'.$tag.'>".$var."</'.$tag.'>";'), $d));
			}
		}else{
			foreach($desc as $key => $d){
				$return[$key] = array_map('strip_tags', $d);
			}
		}
		return $return;
	}
	
	/**
	 * Returns available CVS group
	 * @param string $cvs
	 * @return array
	 */
	public function get_cvs_group($cvs){
		switch($cvs){
			case 'circle-k':
			case 'sunkus':
			case 'ministop':
			case 'daily-yamazaki':
				$group = array();
				foreach(array('circle-k','sunkus','ministop','daily-yamazaki') as $key){
					if($this->_webcvs[$key]){
						$group[] = $key;
					}
				}
				return $group;
				break;
			default:
				return array($cvs);
				break;
		}
	}
	
	/**
	 * Returns CVS code label
	 * @param string $cvs
	 * @return array
	 */
	public function get_cvs_code_label($cvs){
		switch($cvs){
			case 'seven-eleven':
				return array('払込票番号');
				break;
			case 'lawson':
				return array('受付番号', '確認番号');
				break;
			case 'circle-k':
			case 'sunkus':
			case 'ministop':
			case 'daily-yamazaki':
				return  array('オンライン決済番号');
				break;
			case 'familymart':
				return  array('企業コード', '注文番号');
				break;
			case 'seicomart':
				return  array('受付番号', '確認番号');
				break;
			default:
				return array();
		}
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
				$time += 60 * 60 * 24 * $this->cvs_limit; //TODO: 設定値は変更できる
				break;
			case 'gmo-payeasy':
				$time += 60 * 60 * 24 * $this->payeasy_limit; //TODO: 設定値は変更できる
				break;
			case 'sb-cvs':
				$time += 60 * 60 * 24 * $this->cvs_limit;
				break;
			case 'sb-payeasy':
				$time += 60 * 60 * 24 * $this->payeasy_limit;
				break;
		}
		return $selling_limit ? min($time, $selling_limit) : $time;
	}
	
	
	
	
	/**
	 * Detect allowed payment limit by timestamp.
	 * 
	 * @param array $products
	 * @param string $method
	 * @param int $start timestamp.
	 * @return int
	 */
	protected function detect_payment_limit($products, $method, $start = 0){
		$closest = $this->get_closest_limit($products);
		if(!$start){
			$start = current_time('timestamp');
		}
		if(false !== strpos($method, '_CVS')){
			$limit = $start + 60 * 60 * 24 * $this->cvs_limit;
		}elseif(false !== strpos($method, '_PAYEASY')){
			$limit = $start + 60 * 60 * 24 * $this->payeasy_limit;
		}else{
			$limit = 0;
		}
		if(!$closest){
			return $limit;
		}else{
			return min($closest, $limit);
		}
	}
	
	
	
	/**
	 * If payment limit is not set, return false
	 * @param object $post
	 * @param string $method
	 * @return boolean
	 */
	public function can_pay_with($post, $method = 'sb-cvs'){
		global $lwp;
		$post = get_post($post);
		$limit = $this->get_payment_limit($post, false, $method);
		$now = current_time('timestamp');
		switch($method){
			case 'gmo-cvs':
			case 'gmo-payeasy':
			case 'sb-cvs':
				return ($limit > $now && date('Y-m-d', $limit) != date('Y-m-d', $now));
				break;
			case 'sb-payeasy':
				if($post->post_type == $lwp->event->post_type){
					$limit = get_post_meta($post->post_parent, $lwp->event->meta_selling_limit, TRUE);
					if($limit && preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $limit)){
						$limit = strtotime($limit.' 23:59:59') - (60 * 60 * 24 * $lwp->softbank->payeasy_limit);
						return (($limit - $now) / 60 * 60 * 24) > 1;
					}else{
						return true;
					}
				}else{
					return ($limit > $now && date('Y-m-d', $limit) != date('Y-m-d', $now));
				}
				break;
			default:
				return false;
				break;
		}
	}
	
	/**
	 * Do payment notification on cancelation
	 * @param type $transaciton_id
	 */
	public function offline_payment_cancelation($transaction_id){
		global $lwp, $wpdb;
		$transaction = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$lwp->transaction} WHERE ID = %d", $transaction_id));
		if($transaction->status == LWP_Payment_Status::CANCEL && false !== array_search($transaction->method, $this->offline_context)){
			$subject = sprintf('%s :: %s', $this->_('Transaction was canceled'), get_bloginfo('name'));
			$user = get_userdata($transaction->user_id);
			$item_name = $this->get_item_name($transaction->book_id);
			$price = number_format($transaction->price)." ".lwp_currency_code();
			$method = $this->_($transaction->method);
			$misc = unserialize($transaction->misc);
			$payment_limit = mysql2date(get_option('date_format'), $misc['bill_date']);
			$history_url = lwp_history_url();
			$body = sprintf($this->_('Dear %1$s, 


Your order was canceled because the payment limt has been passed.

--------------
Item Name: %2$s
Price: %3$s
Payment Method: %4$s
Order Date: %9$s
Payment Limit: %5$s
--------------

Please see detail at your purchase histroy.
%6$s

%7$s
%8$s'), $user->display_name, $item_name, $price, $method, $payment_limit, $history_url, get_bloginfo('name'), get_bloginfo('url'), get_date_from_gmt($transaction->registered, get_option('date_format')));
			$mail = apply_filters('lwp_offline_cancel_notification', array(
				'subject' => $subject,
				'body' => $body,
				'headers' => sprintf("From: %s <%s>\\", get_bloginfo('name'), get_option('admin_email'))
				), $method, $user, $transaction);
			if($mail){
				wp_mail($user->user_email, $mail['subject'], $mail['body'], $mail['headers']);
			}
		}
	}
	
	
	
	/**
	 * Returns closest limit by timestamp.
	 * 
	 * @global Literally_WordPress $lwp
	 * @param array $products
	 * @return int Timestamp. if no limit, returns 0.
	 */
	public function get_closest_limit($products){
		global $lwp;
		$closest = 0;
		foreach($products as $post){
			if(false !== array_search($post->post_type, $lwp->event->post_types)){
				// Only event has limit
				$limit = lwp_event_starts('Y-m-d H:i:s', $post);
				if($limit){
					$timestamp = strtotime($limit);
					if($closest < 1 || $closest > $timestamp){
						$closest = $timestamp;
					}
				}
			}
		}
		return $closest;
	}
	
	
	
	/**
	 * Returns vendor name. must be overriden
	 * @return string
	 */
	public function vendor_name(){
		return $this->_('No name');
	}
	
	/**
	 * If current user can go through stealth mode, returns true.
	 * @return boolean
	 */
	public function stealth_check(){
		return !$this->is_stealth || current_user_can('manage_options');
	}
}