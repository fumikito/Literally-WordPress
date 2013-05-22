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
	
	private function make_request(){
		
	}
	
	public function parse_request(){
		var_dump('リクエストを受け取ったよ！　ありがとう！');
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