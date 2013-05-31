<?php

/**
 * Static class which has names of Literally Wordpres's Payment Methods.
 *
 * @package literally worrdprss
 * @since 0.8.6
 */
class LWP_Payment_Status {
	
	/**
	 * Transaction are completed.
	 */
	const SUCCESS = 'SUCCESS';
	
	/**
	 * Transaction is authorized
	 */
	const AUTH = 'AUTH';
	
	/**
	 * Transaction has been succeeded, but waiting for review.
	 */
	const WAITING_REVIEW = 'WAITING_REVIEW';
	
	/**
	 * Transaction was canceled.
	 */
	const CANCEL = 'Cancel';
	
	/**
	 * Transaction was started.
	 */
	const START = 'START';
	
	/**
	 * Transaction was refunded
	 */
	const REFUND = 'REFUND';
	
	/**
	 * Transation is required to refund 
	 */
	const REFUND_REQUESTING = 'REFUND_REQUESTING';
	
	/**
	 * Transaction is on cancel list
	 */
	const WAITING_CANCELLATION = 'WAITING_CANCELLATION';
	
	/**
	 * Quit from cancel list
	 */
	const QUIT_WAITNG_CANCELLATION = 'QUIT_WAITNG_CANCELLATION';
	
	/**
	 * Returns all Status
	 * @return array
	 */
	public static function get_all_status(){
		return array(
			self::START,
			self::AUTH,
			self::WAITING_REVIEW,
			self::CANCEL,
			self::SUCCESS,
			self::REFUND,
			self::REFUND_REQUESTING,
			self::WAITING_CANCELLATION,
			self::QUIT_WAITNG_CANCELLATION
		);
	}
	
	
	
	/**
	 * For gettext
	 * @global Literally_WordPress $lwp
	 * @param string $text 
	 */
	private function _($text){
		global $lwp;
		$lwp->_('SUCCESS');
		$lwp->_('AUTH');
		$lwp->_('WAITING_REVIEW');
		$lwp->_('Cancel');
		$lwp->_('START');
		$lwp->_('REFUND');
		$lwp->_('REFUND_REQUESTING');
		$lwp->_('WAITING_CANCELLATION');
		$lwp->_('QUIT_WAITNG_CANCELLATION');
	}
	
	
	
	/**
	 * Returns verbose status by payment method
	 * 
	 * @global Literally_WordPress $lwp
	 * @param string $status
	 * @param string $method
	 * @return string
	 */
	public static function verbose_status($status, $method){
		global $lwp;
		switch($status){
			case LWP_Payment_Status::START:
				if(false !== array_search($method, LWP_Payment_Methods::get_offline_payment())){
					return $lwp->_('Waiting for Payment');
				}else{
					return $lwp->_('Abondoned');
				}
				break;
			default:
				return $lwp->_($status);
				break;
		}
	}
	
	
	
	/**
	 * Returns css color value statusn and method, 
	 * 
	 * @param string $status
	 * @param string $method
	 * @return string
	 */
	private static function status_color($status, $method){
		switch($status){
			case LWP_Payment_Status::SUCCESS:
				return 'green';
				break;
			case LWP_Payment_Status::CANCEL:
			case LWP_Payment_Status::REFUND:
			case LWP_Payment_Status::QUIT_WAITNG_CANCELLATION:
				return 'lightgray';
				break;
			case LWP_Payment_Status::START:
				if(false === array_search($method, LWP_Payment_Methods::get_offline_payment())){
					return 'lightgray';
					break;
				}
			case LWP_Payment_Status::REFUND_REQUESTING:
			case LWP_Payment_Status::WAITING_REVIEW:
				return 'red';
				break;
			case LWP_Payment_Status::WAITING_CANCELLATION:
				return 'orange';
				break;
			default:
				return 'black';
				break;
		}
	}
	
	
	/**
	 * Get status tag for list table
	 * 
	 * @param string $status
	 * @param string $method
	 * @return string
	 */
	public static function status_tag($status, $method){
		$tag = 'strong';
		switch($status){
			case LWP_Payment_Status::START:
				if(false !== array_search($method, LWP_Payment_Methods::get_offline_payment())){
					break;
				}
			case LWP_Payment_Status::CANCEL:
			case LWP_Payment_Status::REFUND:
			case LWP_Payment_Status::QUIT_WAITNG_CANCELLATION:
				$tag = 'span';
				break;
		}
		return sprintf('<%1$s style="color:%3$s;">%2$s</%1$s>', $tag, self::verbose_status($status, $method), self::status_color($status, $method));
	}
}