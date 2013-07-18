<?php

class LWP_NTT extends LWP_Japanese_Payment{
	
	/**
	 * Batch action for cron
	 */
	const CRON_NAME = 'lwp_chocom_cvs_batch';
	
	/**
	 * creditcard list
	 * @var array
	 */
	protected $_creditcard = array(
		'visa' => false,
		'master' => false,
		'jcb' => false,
		'diners' => false,
	);
	
	
	
	/**
	 * CVS list
	 * @var array 
	 */
	protected $_webcvs = array(
		'seven-eleven' => false,
		'lawson' => false,
		'ministop' => false,
		'seicomart' => false,
		'familymart' => false,
	);
	
	
	/**
	 * @var string
	 */
	public $shop_id = '';
	
	
	
	/**
	 * @var string
	 */
	public $access_key = '';
	
	
	
	/**
	 * @var string
	 */
	public $shop_id_cc = '';
	
	
	
	/**
	 * @var string
	 */
	public $access_key_cc = '';
	
	
	
	/**
	 * @var string
	 */
	public $shop_id_cvs = '';
	
	
	
	/**
	 * @var string
	 */
	public $access_key_cvs = '';
	
	
	/**
	 * @var boolean
	 */
	private $emoney = false;
	
	
	/**
	 * @var boolean
	 */
	private $cc = false;
	
	
	/**
	 * @var boolean
	 */
	private $cvs = false;
	
	/**
	 * @var int
	 */
	public $cvs_limit = 0;
	
	/**
	 * @var string
	 */
	private $pay_status = '';
	
	
	/**
	 * @var string
	 */
	public $comdisp = '';
	
	
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
		// Ajax action for returns form
		add_action('wp_ajax_chocom_order', array($this, 'return_form'));
		// Register cron
		if(!wp_next_scheduled(LWP_Cron::CHOCOM_CVS_BATCH)){
			// Execute on 15:00 AM on GMT = 24:00 on JP.
			$tommorow = current_time('timestamp');
			$timestamp = mktime('15', '0', '0', date('m', $tommorow), date('d', $tommorow), date('Y', $tommorow));
			wp_schedule_event($timestamp, 'daily', LWP_Cron::CHOCOM_CVS_BATCH);
		}
		add_action(LWP_Cron::CHOCOM_CVS_BATCH, array($this, 'cvs_batch'));
	}
	
	
	
	/**
	 * 
	 * @param array $option
	 */
	public function set_option($option = array()){
		$option = shortcode_atts(array(
			'ntt_shop_id' => '',
			'ntt_access_key' => '',
			'ntt_shop_id_cc' => '',
			'ntt_access_key_cc' => '',
			'ntt_shop_id_cvs' => '',
			'ntt_access_key_cvs' => '',
			'ntt_sandbox' => true,
			'ntt_stealth' => false,
			'ntt_emoney' => false,
			'ntt_creditcard' => false,
			'ntt_webcvs' => false,
			'ntt_comdisp' => '',
			'ntt_cvs_date' => 0,
		), $option);
		$this->shop_id = (string)$option['ntt_shop_id'];
		$this->access_key = (string)$option['ntt_access_key'];
		$this->shop_id_cc = (string)$option['ntt_shop_id_cc'];
		$this->access_key_cc = (string)$option['ntt_access_key_cc'];
		$this->shop_id_cvs = (string)$option['ntt_shop_id_cvs'];
		$this->access_key_cvs = (string)$option['ntt_access_key_cvs'];
		$this->is_sandbox = (boolean)$option['ntt_sandbox'];
		$this->is_stealth = (boolean)$option['ntt_stealth'];
		$this->emoney = (boolean)$option['ntt_emoney'];
		$this->cc = (boolean)$option['ntt_creditcard'];
		$this->cvs = (boolean)$option['ntt_webcvs'];
		$this->comdisp = (string)$option['ntt_comdisp'];
		$this->cvs_limit = (int)$option['ntt_cvs_date'];
	}
	
	/**
	 * Returns vendor name
	 * 
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
	 * 
	 * @return boolean
	 */
	public function is_emoney_enabled(){
			return $this->emoney && !empty($this->shop_id) && !empty($this->access_key);
	}
	
	
	/**
	 *
	 * @return boolean
	 */
	public function is_cc_enabled() {
		return (parent::is_cc_enabled() &&  !empty($this->shop_id_cc) && !empty($this->access_key_cc));
	}
	
	
	
	/**
	 *
	 * @return boolean
	 */
	public function is_cvs_enabled() {
		return (parent::is_cvs_enabled() &&  !empty($this->shop_id_cvs) && !empty($this->access_key_cvs));
	}
	
	
	
	/**
	 * @see LWP_Japanese_Payment::get_available_cards
	 * @param boolean $all
	 * @return array
	 */
	public function get_available_cards($all = false) {
		if($all || $this->cc){
			return array_keys($this->_creditcard);
		}else{
			return array();
		}
	}
	
	
	/**
	 * @see LWP_Japanese_Payment::get_available_cvs
	 * @param boolean $all
	 * @return array
	 */
	public function get_available_cvs($all = false) {
		if($all || $this->cvs){
			return array_keys($this->_webcvs);
		}else{
			return array();
		}
	}
	
	
	/**
	 * Returns if service is enabled
	 * @return boolean
	 */
	public function is_enabled(){
		return (boolean)(
				( $this->is_cc_enabled() || $this->is_cvs_enabled() || $this->is_emoney_enabled())
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
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parsed_data));
		$result = curl_exec($ch);
		if(curl_errno($ch) > 0 || !$result){
			return false;
		}
		$response = array();
		foreach(explode("\n", $result) as $row){
			$row = trim(preg_replace('/<\/?SHOPMSG>/', '', $row));
			if(empty($row)){
				continue;
			}
			$rows = explode('=', $row);
			if(count($rows) < 2){
				continue;
			}
			$response[trim($rows[0])] = trim($rows[1]);
		}
		curl_close($ch);
		$this->log('Request to '.$endpoint, $parsed_data);
		$this->log('Response from '.$endpoint, $result);
		if(empty($response)){
			return false;
		}else{
			if(isset($response['payStatus'])){
				$this->pay_status = $response['payStatus'];
			}
			return $response;
		}
	}
	
	
	
	/**
	 * Finish transaction on online payment.
	 * 
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param object $transaction
	 * @return boolean
	 */
	public function finish_transaction($transaction){
		global $lwp, $wpdb;
		// Transaction should be start.
		if($transaction->status != LWP_Payment_Status::START){
			return false;
		}
		// Make Request
		switch($transaction->method){
			case LWP_Payment_Methods::NTT_EMONEY:
				$response = $this->make_request($this->get_endpoint('emoney-capture'), array(
					'shopId' => $this->shop_id,
					'linked_1' => $transaction->transaction_key,
					'accessKey' => $this->access_key,
					'aid' => $transaction->payer_mail,
					'amount' => $transaction->price,
					'choComGoodsCode' => '0990',
					'flag' => '1'
				));
				break;
			case LWP_Payment_Methods::NTT_CC:
				$response = $this->make_request($this->get_endpoint('cc-capture'), array(
					'shopId' => $this->shop_id_cc,
					'linked_1' => $transaction->transaction_key,
					'accessKey' => $this->access_key_cc,
					'aid' => $transaction->payer_mail,
					'amount' => $transaction->price,
					'choComGoodsCode' => '0990',
					'flag' => '52' //与信51、与信売上52、与信取消60、売上取消61
				));
				break;
			default:
				return false;
				break;
		}
		if(false == $response || !isset($response['payStatus'])){
			return lwp_endpoint('chocom-cancel', array(
				'order_id' => $transaction->transaction_key,
				'error' => 1,
				'hash' => $this->get_hash($transaction->ID),
			));
		}elseif(false === array_search($response['payStatus'], array('C0000000', 'C1000000'))){
			// Error occurred. save error code.
			$wpdb->update($lwp->transaction, array(
				'transaction_id' => $response['payStatus'],
				'misc' => serialize($response)
			), array('ID' => $transaction->ID), array('%s', '%s'), array('%d'));
			return lwp_endpoint('chocom-cancel', array(
				'order_id' => $transaction->transaction_key,
				'error' => $response['payStatus'],
				'hash' => $this->get_hash($transaction->ID),
			));
		}
		//Transaction is OK.
		$result = $wpdb->update($lwp->transaction, array(
			'status' => LWP_Payment_Status::SUCCESS,
			'transaction_id' => $response['centerPayId'],
			'updated' => gmdate('Y-m-d H:i:s'),
		), array('ID' => $transaction->ID), array('%s', '%s', '%s'), array('%d'));
		if($result){
			do_action('lwp_update_transaction', $transaction->ID);
			return lwp_endpoint('success', array('lwp-id' => $transaction->book_id));
		}else{
			return false;
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
	public function parse_request($posted_method){
		global $lwp, $wpdb;
		$data_to_parse = array(
			'date', 'shopId', 'accessKey', 'linked_1'
		);
		$method = $posted_method;
		switch ($posted_method) {
			case LWP_Payment_Methods::NTT_EMONEY:
				$data_to_parse = array_merge($data_to_parse, array('aid', 'trustStatus'));
				$shop_id = $this->shop_id;
				$access_key = $this->access_key;
				break;
			case LWP_Payment_Methods::NTT_CC:
				$data_to_parse = array_merge($data_to_parse, array('aid', 'trustStatus'));
				$shop_id = $this->shop_id_cc;
				$access_key = $this->access_key_cc;
				break;
			case LWP_Payment_Methods::NTT_CVS:
				$data_to_parse = array_merge($data_to_parse, array('cvs_name', 'pay_no'));
				$shop_id = $this->shop_id_cvs;
				$access_key = $this->access_key_cvs;
				break;
			case LWP_Payment_Methods::NTT_CVS.'_COMPLETE':
				// Override method name
				$method = LWP_Payment_Methods::NTT_CVS;
				$data_to_parse = array_merge($data_to_parse, array('amount', 'centerPayId', 'cvs_name', 'cvs_date'));
				$shop_id = $this->shop_id_cvs;
				$access_key = $this->access_key_cvs;
				break;
			default:
				return false;
				break;
		}
		$this->log('Request to '.$posted_method, $_POST);
		if(
			// Test post data
			!$this->test_request($_POST, $data_to_parse)
				||
			// Check Access key and shop ID
			$shop_id != $_POST['shopId']
				||
			$access_key != $_POST['accessKey']
		){
			return false;
		}
		// Get transaction
		$transaction = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$lwp->transaction} WHERE transaction_key = %s", $_POST['linked_1']));
		if(!$transaction || $transaction->method != $method){
			return false;
		}
		$hash = $this->get_hash($transaction->ID);
		$out = array(
			'shopId' => $shop_id,
		);
		switch($posted_method){
			case LWP_Payment_Methods::NTT_EMONEY:
			case LWP_Payment_Methods::NTT_CC:
				// Save aid on payer_mail
				$wpdb->update($lwp->transaction, array('payer_mail' => $_POST['aid']), array('ID' => $transaction->ID), array('%s'), array('%d'));
				if($_POST['trustStatus'] == 'GIVE'){
					$url = lwp_endpoint('chocom-result', array('order_id' => $_POST['linked_1'], 'hash' => $hash));
				}else{
					$url = lwp_endpoint('chocom-cancel', array('order_id' => $_POST['linked_1'], 'hash' => $hash));
				}
				$out['returnURL'] = $url;
				break;
			case LWP_Payment_Methods::NTT_CVS:
				// Save pay_no on payer_mail and cvs name on misc.
				$data = (array)unserialize($transaction->misc);
				$data['cvs_name'] = $this->get_cvs_name($_POST['cvs_name']);
				$data['receipt_no'] = $_POST['pay_no'];
				$wpdb->update($lwp->transaction, array(
					'status' => LWP_Payment_Status::START,
					'payer_mail' => $_POST['pay_no'],
					'misc' => serialize($data)
				), array('ID' => $transaction->ID), array('%s', '%s', '%s'), array('%d'));
				do_action('lwp_update_transaction', $transaction->ID);
				$out['returnURL'] = lwp_endpoint('payment-info', array('transaction' => $transaction->ID));
				break;
			case LWP_Payment_Methods::NTT_CVS.'_COMPLETE':
				$wpdb->update($lwp->transaction, array(
					'transaction_id' => $_POST['centerPayId'],
					'status' => LWP_Payment_Status::SUCCESS,
					'updated' => gmdate('Y-m-d H:i:s')
				), array('ID' => $transaction->ID), array('%s', '%s', '%s'), array('%d'));
				do_action('lwp_update_transaction', $transaction->ID);
				$out['linked_1'] = $transaction->transaction_key;
				$out['returnCode'] = 'OK';
				break;
		}
		$this->log(sprintf('Response on %s will be:', $posted_method), $out);
		header('Content-Type: text/plain; charset=UTF-8');
		echo "<SHOPMSG>\n";
		foreach($out as $key => $val){
			echo "{$key}={$val}\n";
		}
		echo '</SHOPMSG>';
		exit;
	}
	
	
	/**
	 * Cancel transaction made by cc.
	 * 
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param int $transaction_id
	 * @return \WP_Error|true
	 */
	public function cancel_fixed_transaction($transaction_id){
		global $wpdb, $lwp;
		$transaction = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$lwp->transaction} WHERE ID = %d", $transaction_id));
		// Check if transaction is valid
		if(!$transaction || $transaction->method != LWP_Payment_Methods::NTT_CC || $transaction->status != LWP_Payment_Status::SUCCESS){
			return new WP_Error(404, $this->_('Specified transaction is not be refunded.'));
		}
		// Try request
		$response = $this->make_request($this->get_endpoint('cc-cancel'), array(
			'shopId' => $this->shop_id_cc,
			'linked_1' => $transaction->transaction_key,
			'accessKey' => $this->access_key_cc,
			'aid' => $transaction->payer_mail,
			'amount' => $transaction->price,
			'choComGoodsCode' => '0990',
			'flag' => '61' //与信51、与信売上52、与信取消60、売上取消61
		));
		// Check response
		if(!$response){
			return new WP_Error(500, $this->get_error_msg(''));
		}elseif($response['payStatus'] != 'C1000000'){
			return new WP_Error(500, $this->get_error_msg($response['payStatus']));
		}else{
			// Response is OK
			return true;
		}
	}
	
	
	
	/**
	 * Bulk update outdated cvs transaction.
	 * 
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 */
	public function cvs_batch(){
		global $lwp, $wpdb;
		// Is valid
		if(!$this->is_cvs_enabled() || $this->cvs_limit < 1){
			return;
		}
		// Get all transactions
		$sql = <<<EOS
			UPDATE {$lwp->transaction} SET status = %s, updated = %s
			WHERE status = %s AND method = %s AND transaction_id = '' AND (updated + INTERVAL 9 HOUR) < (NOW() - INTERVAL %d DAY)
EOS;
		$query = $wpdb->prepare($sql, LWP_Payment_Status::CANCEL, gmdate('Y-m-d H:i:s'), LWP_Payment_Status::START, LWP_Payment_Methods::NTT_CVS, $this->cvs_limit);
		$result = $wpdb->query($query);
		if(false === $result){
			$this->log($this->_('Failed to updated CVS transaction status: '), $query);
		}
	}
	
	
	
	/**
	 * Returns SJIS value to cvs key name.
	 * 
	 * @param string $post_data
	 * @return string
	 */
	private function get_cvs_name($post_data){
		$cvs = preg_replace("/[^ァ-ヶー]/u", '', mb_convert_encoding($post_data, 'utf-8', 'sjis-win'));
		switch($cvs){
			case 'セブンイレブン':
				return 'seven-eleven';
				break;
			case 'ローソン':
				return 'lawson';
				break;
			case 'セイコーマート':
				return 'seicomart';
				break;
			case 'ミニストップ':
				return 'ministop';
				break;
			case 'ファミリーマート':
				return 'familymart';
				break;
			default:
				return $cvs;
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
				return sprintf('次へをクリックすると、ちょコムのサイトへ移動します。ログインまたは登録してください。その後、eマネー口座からの引き落とし同意画面に金額が表示されます。同意が完了すると、%sに戻って来て決済完了となります。', get_bloginfo('name'));
				break;
			case 'credit':
				return '当社のクレジット決済は、NTTスマートトレード株式会社が提供する安心・安全なちょコムクレジット支払いを採用しています。ちょコムクレジット支払いは、お買い物代金分のちょコムｅマネーをクレジットカードで購入いただき、即時お支払ができるサービスです。ちょコム会員でない方でも、通常のクレジットカードでのお支払いと同様の手続きでお買い物ができます。カードのご利用明細には「ちょコム」と表記されますのでご留意ください。';
				break;
			case 'cvs':
				return 'コンビニエンスストアでお支払いいただける決済サービスです。ちょコム会員でない方でも、お支払いいただけます。コンビニ決済の場合、決済手数料315円が別途かかります。';
				break;
			case 'contract':
				return '1つのショップIDですべての支払いタイプの契約をしている場合でも、ショップID、アクセスキーはそれぞれ入力してください。';
				break;
			case 'emoney':
			default:
				return 'ちょコムeマネーはNTTスマートトレードが提供する安全で便利な、ネットで普及している電子マネーです。 ネット上に自分専用の貯金箱(口座)を開設し、コンビニ(ファミリー マート、ローソン、セブンイレブン、セイコーマート)、 クレジットカード、銀行ATM、インターネット銀行を利用してチャージ(入金)すると、すぐにご利用いただけます。 入会お申し込み・詳細は、<a href="http://www.chocom.jp/" target="_blank">ちょコムeマネー公式ホームページ</a>をご覧ください。';
				break;
		}
	}
	
	
	/**
	 * Description for payment info
	 * 
	 * @param string $cvs
	 * @return string
	 */
	public function get_cvs_howtos($cvs){
		switch($cvs){
			case 'seven-eleven':
				return <<<EOS
レジに「インターネットショッピング払込票」を渡すか、「払込票番号」を提示し「インターネットショッピング代金の支払い」と伝えて、お支払い合計金額を現金でお支払いください。「インターネットショッピング払込領収書」を受け取ります。
EOS;
				break;
			case 'ministop':
			case 'lawson':
				return <<<EOS
店内に設置されているLoppiを操作します。
トップ画面の左上「各種番号をお持ちの方」を押してください。→「受付番号」を入力してください。→「電話番号」を入力してください。
画面での操作が終わると「インターネット受付お支払申込券」が出てきますので、お受け取りください。
レジに「インターネット受付お支払申込券」を渡してお支払い合計金額を現金で支払い、「インターネット受付受領書」を受け取ります。
EOS;
				break;
			case 'seicomart':
				return <<<EOS
店内のクラブステーションを操作します。 
トップ画面の「インターネット受付」を押してください。→「受付番号」を入力してください。→「電話番号」を入力してください。→「印刷」ボタンを押してください。
画面での操作が終わると申込券(決済サービス払込取扱票、払込票兼受領証、領収書) が出てきます。
レジに申込券(決済サービス払込取扱票、払込票兼受領証、領収書) を渡してお支払い合計金額を現金で支払い、「領収書」を受け取ります。
EOS;
				break;
			case 'familymart':
				return <<<EOS
最寄りのファミリーマート店内のFamiポート端末で、Famiポートのトップ画面の左上「代金支払い」を押してください。→「各種番号をお持ちの方はこちら」を入力してください。※「ちょコムのチャージ」ではありません。お気をつけください。
「企業コード」と「注文番号」を入力して、内容を確認してください。画面での操作が終わると「Famiポート申込券」が出てきます。レジに渡してお支払い合計金額を支払い、「取扱明細兼受領書」を受け取ります。
EOS;
				break;
			default:
				return '';
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
			case 'C1000000':
				return '正常に決済処理がおこなわれました。';
				break;
			case 'C0000001':
				return 'お客様の信用要求情報がありません。';
				break;
			case 'C0000002':
			case 'C1000002':
				return 'お客様のデータが存在しません。';
				break;
			case 'C0000003':
				return 'お客様は現在ちょコムeマネーをご利用できない状態です。';
				break;
			case 'C0000004':
				return 'お客様の貯金箱が存在しません。';
				break;
			case 'C0000005':
				return 'お客様のちょコム残高が不足しています。<a href="https://www.chocom.net/user/html/E22Login.html" target="_blank">ちょコムeマネーをチャージ</a>して、もう一度やり直してください。';
				break;
			case 'C0000006':
			case 'C0000007':
			case 'C0000008':
			case 'C0000009':
			case 'C0000010':
			case 'C1000009':
				return '申し訳ございません。ちょコムのシステムが停止中です。';
				break;
			case 'C0000011':
			case 'C0000012':
			case 'C0000013':
			case 'C0000014':
			case 'C0000015':
			case 'C0000016':
			case 'C1000007':
			case 'C1000011':
			case 'C1000012':
			case 'C1000015':
			case 'C1000016':
			case 'C1000021':
				return '申し訳ございません。システムでエラーが発生し、処理を完了できませんでした。';
				break;
			case 'C1000013':
			case 'C1000085':
				return 'この決済はすでに処理を完了しています。購入履歴ページをご覧下さい。';
				break;
			case 'C1000022':
			case 'C1000023':
			case 'C1000024':
			case 'C1000025':
			case 'C1000026':
			case 'C1000027':
			case 'C1000028':
			case 'C1000034':
			case 'C1000035':
			case 'C1000036':
			case 'C1000037':
			case 'C1000038':
			case 'C1000040':
			case 'C1000041':
			case 'C1000050':
			case 'C1000051':
			case 'C1000052':
			case 'C1000060':
			case 'C1000061':
			case 'C1000062':
			case 'C1000063':
				return '申し訳ございません。不正な値が指定されていたため、処理を完了できませんでした。';
				break;
			case 'C1000029':
				return '申し訳ございません。利用できないクレジットカード番号です。';
				break;
			case 'C1000030':
				return '申し訳ございません。1回当たりの決済金額が規定範囲外です。';
				break;
			case 'C1000031':
				return '申し訳ございません。ご指定のクレジットカード番号は一定期間中の決済金額の累計額が上限値を超えています。';
				break;
			case 'C1000032':
				return '申し訳ございません。ご指定のクレジットカード番号は一定期間中の決済回数が上限値を超えています。';
				break;
			case 'C1000033':
			case 'C1000053':
			case 'C1000064':
				return '申し訳ございません。与信処理で通信エラーが発生したため、決済が完了していない可能性があります。';
				break;
			case 'C1000050':
				return '対象の決済は与信取消処理ができません。';
				break;
			case 'C1000051':
				return '与信時の利用者IDと与信取消要求の利用者IDが一致しません。';
				break;
			case 'C1000052':
				return '与信時の決済金額と与信取消金額が一致しません。';
				break;
			case 'C1000053':
				return '与信取消処理で通信エラーが発生したため、与信取消が完了していない可能性があります。';
				break;
			case 'C1000060':
				return '対象の決済は売上取消処理ができません。';
				break;
			case 'C1000061':
				return '売上時の利用者IDと売上取消要求の利用者IDが一致しません。';
				break;
			case 'C1000062':
				return '売上時の決済金額と売上取消金額が一致しません。';
				break;
			case 'C1000063':
				return '加盟店貯金箱残高から売上取消金額を引き落しできません。';
				break;
			case 'C1000064':
				return '売上取消処理で通信エラーが発生したため、売上取消が完了していない可能性があります。';
				break;
			default:
				$match = array();
				if(preg_match("/C1001([0-9a-zA-Z]{3})/", $error_code, $match)){
					return sprintf('エラー%s（カード情報が間違っている、限度額を超えている、有効期限が切れている、など）により、決済を完了できませんでした。詳しくはカード会社までお問い合わせください。', $error_code);
				}else{
					return 'ちょコムでの決済処理の過程でエラーが発生しました。もう一度やり直してください。';
				}
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
				return '上記の内容で決済を進めます。よろしいですか？　ちょコム残高が足りない場合は決済をやり直しになります。足りない場合はあらかじめ<a href="https://www.chocom.net/user/html/E22Login.html" target="_blank">チャージ</a>をしておいてください。';
				break;
			case LWP_Payment_Methods::NTT_CC:
				return '上記の内容で決済を進めます。よろしいですか？　ちょコムのサイトに移動後、クレジットカード情報を入力してください。その後、このサイトに戻ってきます。';
				break;
			case LWP_Payment_Methods::NTT_CVS:
				return '上記の内容で決済を進めます。よろしいですか？　ちょコムのサイトに移動後、コンビニの選択や連絡先の入力を行ってください。その後、このサイトに戻ってきます。';
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
		// Test method
		if(!LWP_Payment_Methods::is_chocom($method)){
			return false;
		}
		// Generate order_id
		$order_id = $this->generate_order_id($user_id, $products);
		// Create transaciton
		if(count($products) > 1){
			// TODO: 商品が複数の場合
			return false;
		}else{
			$product = $products[0];
			if(!isset($quantities[$product->ID]) || $quantities[$product->ID] < 1){
				return false;
			}
			$amount = lwp_price($product) * $quantities[$product->ID];
			$result = $wpdb->insert($lwp->transaction, array(
				"user_id" => $user_id,
				"book_id" => $product->ID,
				"price" => $amount,
				"status" => ($method == LWP_Payment_Methods::NTT_CVS) ? LWP_Payment_Status::CANCEL : LWP_Payment_Status::START,
				"method" => $method,
				'num' => $quantities[$product->ID],
				"transaction_key" => $order_id,
				"registered" => gmdate('Y-m-d H:i:s'),
				"updated" => gmdate('Y-m-d H:i:s')
			), array('%d', '%d', '%d', '%s', '%s', '%d', '%s', '%s', '%s'));
			if(!$result || !($transaction_id = $wpdb->insert_id)){
				// Transaction is not created.
				return false;
			}else{
				$data = array(
					'cancelURL' => lwp_endpoint('chocom-cancel', array('order_id' => $order_id, 'hash' => $this->get_hash($transaction_id))),
					'errorURL' => lwp_endpoint('chocom-cancel', array('order_id' => $order_id, 'error' => 1, 'hash' => $this->get_hash($transaction_id))),
					'linked_1' => $order_id,
				);
			}
		}
		switch($method){
			case LWP_Payment_Methods::NTT_EMONEY:
				$action = $this->get_endpoint('emoney-auth');
				$data['shopId'] = $this->shop_id;
				break;
			case LWP_Payment_Methods::NTT_CC:
				$action = $this->get_endpoint('cc-auth');
				$data['shopId'] = $this->shop_id_cc;
				$data['amount'] = $amount;
				if(!empty($this->comdisp)){
					$data['comdisp'] = $this->comdisp;
				}
				break;
			case LWP_Payment_Methods::NTT_CVS:
				$action = $this->get_endpoint('cvs-auth');
				// Save limit date
				$limit = $this->detect_payment_limit($products, $method);
				$wpdb->update($lwp->transaction, array(
					'misc' => serialize(array(
						'bill_date' => date_i18n('Y-m-d H:i:s', $limit)
					))
				), array('ID' => $transaction_id), array('%s'), array('%d'));
				// Initial Data
				$data = array_merge($data, array(
					'shopId' => $this->shop_id_cvs,
					'amount' => $amount,
					'verify' => md5($this->shop_id_cvs.$order_id.$amount.$this->access_key_cvs),
					'payDate' => date_i18n('Ymd', $limit),
					'mail' => get_user_meta($user_id)
				));
				if(!empty($this->comdisp)){
					$data['comdisp'] = $this->comdisp;
				}
				// Add userdata
				$user = get_userdata($user_id);
				$data['mail'] = $user->user_email;
				$tel = preg_replace('/[^0-9]/', '', (string)get_user_meta($user_id, 'tel', true));
				if(preg_match('/^[0-9]{9,11}$/', $tel)){
					$data['tel'] = $tel;
				}
				foreach(array('name1' => 'last_name_kana', 'name2' => 'first_name_kana') as $key => $meta_key){
					$name = (string)get_user_meta($user_id, $meta_key, true);
					if(!empty($name) && preg_match('/^[ア-ンァ-ォャ-ョ]+$/', $name)){
						$data[$key] = rawurlencode(mb_convert_encoding(mb_substr($name, 0, 10, 'utf-8'), 'sjis-win', 'utf-8'));
					}
				}
				break;
			default:
				return false;
				break;
		}
		if(empty($action)){
			return false;
		}
		$form = sprintf('<form method="post" action="%s">', $action);
		foreach($data as $key => $val){
			$form .= sprintf('<input type="hidden" name="%s" value="%s" />',
					esc_attr($key), esc_attr($val));
		}
		return $form.'</form>';
	}
	
	
	
	/**
	 * Returns endpoint
	 * 
	 * @param string $type
	 * @return string
	 */
	private function get_endpoint($type){
		$pc_base = $this->is_sandbox
					? 'https://pchocom.sinka-dbg.jp/'
					: 'https://www.chocom.net/';
		$mobile_base = $this->is_sandbox
					? 'https://mobilechocom.sinka-dbg.jp/mobile/servlet/'
					: 'https://mobile.chocom.net/mobile/servlet/';
		switch($type){
			case 'emoney-auth':
				if($this->domestic_mobile_phone()){
					return $mobile_base.'EMQShinyo';
				}else{
					return $pc_base.'inq/servlet/E24Shinyo';
				}
				break;
			case 'emoney-capture':
				return $pc_base.'inq/servlet/E2OShopDecision';
				break;
			case 'cc-auth':
				if($this->domestic_mobile_phone()){
					return $mobile_base.'EP4TrustDemand';
				}else{
					return $pc_base.'direct/servlet/EP4TrustDemand';
				}
				break;
			case 'cc-capture':
				return $pc_base.'direct/servlet/EPODirectCredit';
				break;
			case 'cvs-auth':
				if($this->domestic_mobile_phone()){
					return $mobile_base.'EPCCvsEntry';
				}else{
					return $pc_base.'direct/servlet/EPCCvsEntry';
				}
				break;
			case 'cc-cancel':
				return $pc_base.'direct/servlet/EPODirectCredit';
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
			return false;
		}else{
			return $transaction;
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