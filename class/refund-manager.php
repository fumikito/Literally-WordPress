<?php
class LWP_Reufund_Manager extends Literally_WordPress_Common {
	
	/**
	 * User meta key for refund contact
	 * @var string
	 */
	public $meta_refund_contact = '_lwp_refund_contact';
	
	
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