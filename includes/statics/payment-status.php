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
}