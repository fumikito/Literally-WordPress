<?php
/**
 * Static class which has names of Literally Wordpres's Payment Methods.
 *
 * @package literally worrdprss
 */
class LWP_Payment_Methods {
	/**
	 * Name of payment method for paypal.
	 */
	const PAYPAL = 'PAYPAL';
	
	/**
	 * Name of payment method for free campaign.
	 */
	const CAMPAIGN = 'CAMPAIGN';
	
	/**
	 * Name of payment method for present.
	 */
	const PRESENT = 'present';
	
	/**
	 * Name for Payment method for transafer.
	 */
	const TRANSFER = 'TRANSFER';
	
	
	/**
	 * Returns all payment method.
	 * @param boolean $include_admin_method
	 * @return array
	 */
	public static function get_all_methods($include_admin_method = false){
		$methods =  array(
			self::PAYPAL,
			self::CAMPAIGN,
			self::PRESENT,
			self::TRANSFER
		);
		return $methods;
	}
}


/**
 * Static class which has names of Literally Wordpres's Payment Methods.
 *
 * @package literally worrdprss
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
	 * Returns all Status
	 * @return array
	 */
	public static function get_all_status(){
		return array(
			self::START,
			self::CANCEL,
			self::SUCCESS,
			self::REFUND
		);
	}
}