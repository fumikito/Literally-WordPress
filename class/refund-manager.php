<?php
class LWP_Reufund_Manager extends Literally_WordPress_Common {
	
	/**
	 * User meta key for refund contact
	 * @var string
	 */
	public $meta_refund_contact = '_lwp_refund_contact';
	
	public function on_construct() {
		add_action('admin_init', array($this, 'admin_init'));
	}
	
	public function admin_enqueue_scripts() {
		if(isset($_GET['page']) && $_GET['page'] == 'lwp-refund'){
			//jQuery UI Theme
			wp_enqueue_style('jquery-ui-datepicker');
			wp_enqueue_script('lwp-refund-helper', $this->url.'assets/js/refund-helper.js', array('jquery', 'jquery-ui-dialog'), $this->version);
			wp_localize_script('lwp-refund-helper', 'LWPRefund', array(
				'message' => sprintf('<div title="%s"><p>%s</p></div>', sprintf($this->_('About %s'), $this->_('Refunds')), $this->_('Before version 0.9.3, LWP hasn\'t save refund amount. The old values are just detected and can be change in many occasion.<br />For future coinsistence, you had better to save deteceted value as regular one.')),
				'confirm' => $this->_('Are you really sure to fix refund price?')
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
		if(isset($_REQUEST['page'], $_REQUEST['_wpnonce']) && $_REQUEST['page'] == 'lwp-refund' && wp_verify_nonce($_REQUEST['_wpnonce'], 'lwp_refund_price')){
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