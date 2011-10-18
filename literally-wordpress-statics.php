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
	 * @return array
	 */
	public static function get_all_methods(){
		return array(
			self::PAYPAL,
			self::CAMPAIGN,
			self::PRESENT,
			self::TRANSFER
		);
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
	 * Transaction has started and is waiting for transfered.
	 */
	const PENDING = 'PENDING';
	
	/**
	 * Transaction has completed but refunded.
	 */
	const REFUND = 'REFUND';
}