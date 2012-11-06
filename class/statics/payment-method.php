<?php
/**
 * Static class which has names of Literally Wordpres's Payment Methods.
 *
 * @package literally worrdprss
 * @since 0.8.6
 */
class LWP_Payment_Methods {
	/**
	 * Name of payment method for paypal.
	 */
	const PAYPAL = 'PAYPAL';
	
	/**
	 * Name of payment method for in App purchase
	 */
	const APPLE = 'APPLE';
	
	/**
	 * Name of payment method for in Android
	 */
	const ANDROID = 'ANDROID';
	
	/**
	 * Name of payment method for Softbank Payment's credit card
	 */
	const SOFTBANK_CC = 'SOFTBANK_CC';
	
	/**
	 * Name of payment method for Softbank Payment's PayEasy
	 */
	const SOFTBANK_PAYEASY = 'SOFTBANK_PAYEASY';
	
	/**
	 * Name of payment method for Softbank Payment's Web CVS
	 */
	const SOFTBANK_WEB_CVS = 'SOFTBANK_WEB_CVS';
	
	/**
	 * Name of payment method for GMO Payment Gateway's credit card
	 */
	const GMO_CC = 'GMO_CC';
	
	/**
	 * Name of payment method for Softbank Payment's PayEasy
	 */
	const GMO_PAYEASY = 'GMO_PAYEASY';
	
	/**
	 * Name of payment method for Softbank Payment's Web CVS
	 */
	const GMO_WEB_CVS = 'GMO_WEB_CVS';
	
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
			self::TRANSFER,
			self::APPLE,
			self::ANDROID,
			self::SOFTBANK_CC,
			self::SOFTBANK_PAYEASY,
			self::SOFTBANK_WEB_CVS,
			self::GMO_CC,
		);
		return $methods;
	}
	
	/**
	 * Place holder for gettext
	 * @global Literally_WordPress $lwp
	 * @param string $text 
	 */
	private function _($text){
		global $lwp;
		$lwp->_('PAYPAL');
		$lwp->_('CAMPAIGN');
		$lwp->_('present');
		$lwp->_('TRANSFER');
		$lwp->_('APPLE');
		$lwp->_('ANDROID');
		$lwp->_('SOFTBANK_CC');
		$lwp->_('SOFTBANK_PAYEASY');
		$lwp->_('SOFTBANK_WEB_CVS');
		$lwp->_('GMO_CC');
		$lwp->_('GMO_PAYEASY');
		$lwp->_('GMO_WEB_CVS');
	}
	
	/**
	 * Returns transfer method
	 * @return array
	 */
	public static function get_transfer_methods(){
		return array(
			self::GMO_PAYEASY,
			self::GMO_WEB_CVS,
			self::SOFTBANK_PAYEASY,
			self::SOFTBANK_WEB_CVS,
			self::TRANSFER
		);
	}
	
	/**
	 * Returns if method is transfer
	 * @param string $method
	 * @return boolean
	 */
	public static function is_transfer($method){
		return false !== array_search($method, self::get_transfer_methods());
	}
}