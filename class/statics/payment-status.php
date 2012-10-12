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
	 * Transaction is on 
	 */
	const WAITING_CANCELLATION = 'WAITING_CANCELLATION';
	
	/**
	 * Returns all Status
	 * @return array
	 */
	public static function get_all_status(){
		return array(
			self::START,
			self::CANCEL,
			self::SUCCESS,
			self::REFUND,
			self::REFUND_REQUESTING,
			self::WAITING_CANCELLATION
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
		$lwp->_('Cancel');
		$lwp->_('START');
		$lwp->_('REFUND');
		$lwp->_('REFUND_REQUESTING');
		$lwp->_('WAITING_CANCELLATION');
	}
}