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
}