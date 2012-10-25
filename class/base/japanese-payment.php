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
	 * If PayEasy is enabled
	 * @var boolean
	 */
	public $payeasy = false;
	
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
	protected function get_user_default($user_id){
		$user_info = array();
		$meta_keys = apply_filters('lwp_user_default_key', array('last_name','first_name','last_name_kana','first_name_kana',
			'zipcode','prefecture','city','street','office','tel'));
		foreach($meta_keys as $key){
			$user_info[$key] = get_user_meta($user_id, $key, true);
		}
		return $user_info;
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
		$keys = array('last_name','first_name','last_name_kana','first_name_kana',
			'zipcode','prefecture','city','street','office','tel');
		foreach($keys as $key){
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
	 * Description for Web CVS
	 * @param string $cvs
	 * @param boolean $requirements If set to true, returns array of required information
	 * @return string|array
	 */
	public function get_cvs_howtos($cvs){
		switch($cvs){
			case 'seven-eleven':
				return '決済後、画面に表示された「払込票番号」を印刷またはメモし、それをセブンイレブンへ持ち込みお支払いを行います。';
				break;
			case 'lawson':
				return !'店頭のLoppi端末にて、決済を選択後「受付番号」「確認番号」を入力、その後、端末から出力された申込券をレジに持参してお支払いを行います。';
				break;
			case 'circle-k':
			case 'sunkus':
			case 'ministop':
			case 'daily-yamazaki':
				return 'レジのタッチパネルにて決済番号を入力し、お支払いを行います。';
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
}