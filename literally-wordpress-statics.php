<?php
/**
 * Static class which holds table names.
 * 
 * @package literally_wordpress
 * @since 0.8.6
 */
class LWP_Tables{
	
	/**
	 * Table prefix for this plugin
	 */
	const PREFIX = 'lwp_';
	
	/**
	 * Returns Campaign table name
	 * @return string
	 */
	public static function campaign(){
		return self::get_name('campaign');
	}
	
	/**
	 * Returns transaction table name
	 * @return string
	 */
	public static function transaction(){
		return self::get_name('transaction');
	}
	
	/**
	 * Returns file table name
	 * @return string
	 */
	public static function files(){
		return self::get_name('files');
	}
	
	/**
	 * Returns device table name
	 * @return string
	 */
	public static function devices(){
		return self::get_name('devices');
	}
	
	/**
	 * Returns file_relationships table
	 * @return string
	 */
	public static function file_relationships(){
		return self::get_name('file_relationships');
	}

	/**
	 * Create table name with prefix.
	 * @global wpdb $wpdb
	 * @param string $name
	 * @return string
	 */
	public static function get_name($name){
		global $wpdb;
		return $wpdb->prefix.self::PREFIX.$name;
	}
	
	/**
	 * Returns table name
	 * @return array
	 */
	public static function get_tables(){
		$tables = array();
		foreach(get_class_methods('LWP_Tables') as $method){
			if(!preg_match('/^get_/', $method)){
				$tables[] = self::$method();
			}
		}
		return $tables;
	}
}

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
	
	private function _($text){
		global $lwp;
		$lwp->_('PAYPAL');
		$lwp->_('CAMPAIGN');
		$lwp->_('present');
		$lwp->_('TRANSFER');
	}
}


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
	}
}