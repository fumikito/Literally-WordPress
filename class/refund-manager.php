<?php
class LWP_Reufund_Manager extends Literally_WordPress_Common {
	
	/**
	 * User meta key for refund contact
	 * @var string
	 */
	public $meta_refund_contact = '_lwp_refund_contact';
	
	/**
	 * User function
	 * @var array
	 */
	public $message = array();
	
	/**
	 * Common Placeholder name
	 * @var array
	 */
	private $common_placeholders = array('display_name', 'user_email', 'site_url',
		'site_name', 'purchase_history');
	
	/**
	 * 
	 */
	private $place_holders = array(
		'succeeded' => array('item_name', 'refund_price', 'paid_price'),
		'accepted' => array('item_name', 'refund_price', 'paid_price'),
		'required' => array('account_url')
	);
	
	public function on_construct() {
		add_action('admin_init', array($this, 'admin_init'));
		add_action('init', array($this, 'init'));
	}
	
	/**
	 * Init Action
	 */
	public function init(){
		$this->message = get_option('lwp_refund_message', array(
			'succeeded' => $this->_("Dear %display_name%,\n\nRefund for %item_name% is succeeded. %refund_price% has been paid back.\nPlease see purchase history for detail.\n%purchase_history%\n\n%site_name%\n%site_url%"),
			'accepted' => $this->_("Dear %display_name%,\n\nRefund for %item_name% is accepted.  %refund_price% will be paid back. \nPlease be patient until pay back process will be finished.\nPlease see purchase history for detail.\n%purchase_history%\n\n%site_name%\n%site_url%"),
			'required' => $this->_("Dear %display_name%,\n\nTo finish refund, you have to fullfill pay back account information.\nPlease visite link below and fill it all.\n%account_url%\nPlease see purchase history for detail.\n%purchase_history%\n\n%site_name%\n%site_url%")
		));
	}
	
	/**
	 * Returns placeholders.
	 * @param string $key succeeded, accepted, required
	 * @return array
	 */
	public function get_place_holders($key = 'succeeded'){
		if(array_key_exists($key, $this->place_holders)){
			return array_merge($this->common_placeholders, $this->place_holders[$key]);
		}else{
			return array();
		}
	}
	
	public function notify($key, $id){
		global $wpdb, $lwp;
		$placeholders = $this->get_place_holders('required');
		if(empty($placeholders)){
			return false;
		}
		//Filter
		switch($key){
			case 'succeeded':
			case 'accepted':
				$transaction = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$lwp->transaction} WHERE ID = %d", $id));
				$user = get_userdata($transaction->user_id);
				if(!$transaction || !$user){
					return false;
				}
				break;
			case 'required':
				$user = get_userdata($id);
				break;
		}
		$message = $this->message[$key];
		foreach($placeholders as $ph){
			switch($ph){
				case 'display_name':
					$repl = $user->display_name;
					break;
				case 'user_email':
					$repl = $user->user_email;
					break;
				case 'site_url':
					$repl = home_url();
					break;
				case 'site_name':
					$repl = get_bloginfo('name');
					break;
				case 'purchase_history':
					$repl = lwp_history_url();
					break;
				case 'item_name':
					$repl = $this->get_item_name($transaction->book_id);
					break;
				case 'refund_price':
					$repl = number_format_i18n($this->detect_refund_price($transaction)).' '.  lwp_currency_code();
					break;
				case 'paid_price':
					$repl = number_format_i18n($transaction->price).' '.  lwp_currency_code();
					break;
				case 'account_url':
					$repl = lwp_refund_account_url();
					break;
			}
			$message = str_replace("%{$ph}%", $repl, $message);
		}
		$headers = apply_filters('lwp_refund_message_header', "From: ".get_bloginfo('name')." <".get_option('admin_email').">\r\n", $key);
		switch($key){
			case 'succeeded':
				$subject = $this->_('Refund is succeeded');
				break;
			case 'accepted':
				$subject = $this->_('Refund is accepted');
				break;
			case 'required':
				$subject = $this->_('Refund account is required');
				break;
		}
		$subject = apply_filters('lwp_refund_message_subject', $subject.' : '.get_bloginfo('name'), $key);
		return wp_mail($user->user_email, $subject, $message, $headers);
	}
	
	public function admin_enqueue_scripts() {
		if(isset($_GET['page']) && $_GET['page'] == 'lwp-refund'){
			//jQuery UI Theme
			wp_enqueue_style('jquery-ui-datepicker');
			wp_enqueue_script('lwp-refund-helper', $this->url.'assets/js/refund-helper.js', array('jquery', 'jquery-ui-dialog'), $this->version);
			wp_localize_script('lwp-refund-helper', 'LWPRefund', array(
				'message' => sprintf('<div title="%s"><p>%s</p></div>', sprintf($this->_('About %s'), $this->_('Refunds')), $this->_('Before version 0.9.3, LWP hasn\'t save refund amount. The old values are just detected and can be change in many occasion.<br />For future coinsistence, you had better to save deteceted value as regular one.')),
				'confirm' => $this->_('Are you really sure to fix refund price?'),
				'done' => $this->_('Are you really sure to make this transaction refunded?'),
				'request' => $this->_('Are you really sure to send request message to complete refund account?')
			));
		}
	}
	
	/**
	 * Save refund price
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 */
	public function admin_init(){
		global $lwp, $wpdb;
		if(isset($_REQUEST['page'], $_REQUEST['_wpnonce']) && $_REQUEST['page'] == 'lwp-refund'){
			if(wp_verify_nonce($_REQUEST['_wpnonce'], 'lwp_refund_price')){
				$transaction = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$lwp->transaction} WHERE ID = %d", $_REQUEST['transaction_id']));
				if(!current_user_can('edit_others_posts')){
					$this->kill($this->_('You don\'t have permission.'), 403, true);
				}
				if(!$transaction){
					$this->kill($this->_('Specified transaction doesn\'t exist.'), 404, true);
				}
				if(false === array_search($transaction->status, array(LWP_Payment_Status::REFUND_REQUESTING, LWP_Payment_Status::REFUND)) ){
					$this->kill($this->_('This transaction is not with refund status.'), 403, true);
				}
				if(!($transaction->price > 0 && $transaction->refund == 0)){
					$lwp->error = true;
					$lwp->message[] = $this->_('This transaction\'s refund price seem to be valid.');
				}else{
					$refund = $this->detect_refund_price($transaction);
					$wpdb->update($lwp->transaction,
							array('refund' => $refund),
							array('ID' => $transaction->ID),
							array('%d'), array('%d'));
					$lwp->message[] = $this->_('Transaction\'s refund price was updated.');
				}
			}elseif(wp_verify_nonce($_REQUEST['_wpnonce'], 'lwp_refund_done')){
				
			}elseif(wp_verify_nonce($_REQUEST['_wpnonce'], 'lwp_refund_account_request')){
				if(!current_user_can('edit_others_posts')){
					$this->kill($this->_('You don\'t have permission.'), 403, true);
				}
				$user = get_userdata($_REQUEST['user_id']);
				if(!$user){
					$this->kill($this->_('Specified user doesn\'t exist.'), 404, true);
				}
				if($this->did_user_register_account($user->ID)){
					$this->kill($this->_('This user has already registered refund account.'), 500, true);
				}
				if($this->notify('required', $user->ID)){
					$lwp->message[] = $this->_('Request to compete refund account was sent.');
				}else{
					$lwp->error = true;
					$lwp->message[] = $this->_('Failed to send email. Please try again later or contact to user dirctory.');
				}
			}
		}
	}
	
	/**
	 * Retunrns awaiting refunds
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @return int
	 */
	public function on_queue_count(){
		global $lwp, $wpdb;
		$sql = <<<EOS
			SELECT COUNT(ID) FROM {$lwp->transaction}
			WHERE status = %s
EOS;
		return $wpdb->get_var($wpdb->prepare($sql, LWP_Payment_Status::REFUND_REQUESTING));
	}
	
	/**
	 * Returns user bank account.
	 * @param int $user_id
	 * @param string $line_break If set to false, returns raw array
	 * @return string|array
	 */
	public function get_user_account($user_id, $line_break = '<br />'){
		$account = get_user_meta($user_id, $this->meta_refund_contact, true);
		if(!$account || !is_array($account)){
			return false;
		}
		if($line_break === false){
			return $account;
		}else{
			return implode($line_break, $account);
		}
	}
	
	/**
	 * Returns if user has registered account
	 * @param int $user_id
	 * @return boolean
	 */
	public function did_user_register_account($user_id){
		return (boolean)$this->get_user_account($user_id, false);
	}
	
	/**
	 * Returns if refund price is suspicious
	 * @since 0.9.3
	 * @param object $transaction
	 * @return boolean
	 */
	public function is_suspicious_transaction($transaction){
		return ($transaction->price > 0 && $transaction->refund == 0);
	}
	
	/**
	 * Returns detected refund price
	 * @global Literally_WordPress $lwp
	 * @param object $transaction
	 * @return int
	 */
	public function detect_refund_price($transaction){
		global $lwp;
		if($this->is_suspicious_transaction($transaction)){
			//This transaction is old one.
			//If this is post, total price
			if(get_post_type($transaction->book_id) == $lwp->event->post_type){
				$condition = $lwp->event->get_current_cancel_condition($lwp->event->get_event_from_ticket_id($transaction->book_id), strtotime($transaction->updated));
				if(!$condition){
					return 0;
				}else{
					$ratio = $condition['ratio'];
					if(preg_match("/^[0-9]+%$/", $ratio)){
						return round($transaction->price * preg_replace("/[^0-9]/", '', $ratio) / 100);
					}elseif(preg_match("/^-[0-9]+$/", $ratio)){
						return $transaction->price - preg_replace("/[^0-9]/", '', $ratio);
					}else{
						return preg_replace("/[^0-9]/", "", $ratio);
					}
				}
			}else{
				return $transaction->price;
			}
		}else{
			return $transaction->refund;
		}
	}
}