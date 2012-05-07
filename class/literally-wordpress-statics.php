<?php
/**
 * Static class which holds table names.
 * 
 * @package literally_wordpress
 * @since 0.8.6
 */
class LWP_Tables{
	
	/**
	 * Table version 
	 */
	const VERSION = '0.9';
	
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
	 * Returns reward_logs table
	 * @return string
	 */
	public static function reward_logs(){
		return self::get_name('reward_logs');
	}
	
	/**
	 * Returns promotion_logs table
	 * @return string
	 */
	public static function promotion_logs(){
		return self::get_name('promotion_logs');
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
	
	/**
	 * Alter table if required (because db_delta is buggy)
	 * @global wpdb $wpdb 
	 */
	public static function alter_table($current_version){
		global $wpdb;
		//Change Field name because desc is reserved words for MySQL.
		//since 0.8.8
		if(version_compare('0.8.9', $current_version) > 0){
			$row = null;
			foreach($wpdb->get_results("DESCRIBE ".self::files()) as $field){
				if($field->Field == 'desc'){
					$row = $field;
					break;
				}
			}
			if($row){
				$wpdb->query("ALTER TABLE ".self::files()." CHANGE COLUMN `desc` `detail` TEXT NOT NULL");
			}
		}
	}
	
	/**
	 * Create tables 
	 */
	public static function create(){
		$char = defined("DB_CHARSET") ? DB_CHARSET : "utf8";
		$sql = array();
		//Create files table
		$files = self::files();
		$sql[] = <<<EOS
			CREATE TABLE {$files} (
				ID INT NOT NULL AUTO_INCREMENT,
				book_id BIGINT NOT NULL,
				name VARCHAR(255) NOT NULL,
				detail TEXT NOT NULL,
				file VARCHAR(255) NOT NULL,
				public INT NOT NULL DEFAULT 1,
				free INT NOT NULL DEFAULT 0,
				registered DATETIME NOT NULL,
				updated DATETIME NOT NULL,
				PRIMARY KEY  (ID)
			) ENGINE = MYISAM DEFAULT CHARSET = {$char};
EOS;
		//Create transactios table
		$transactions = self::transaction();
		$sql[] = <<<EOS
			CREATE TABLE {$transactions} (
				ID BIGINT NOT NULL AUTO_INCREMENT,
				user_id BIGINT NOT NULL,
				book_id BIGINT NOT NULL,
				price BIGINT NOT NULL,
				status VARCHAR(45) NOT NULL,
				method VARCHAR(100) NOT NULL DEFAULT 'PAYPAL',
				transaction_key VARCHAR (255) NOT NULL,
				transaction_id VARCHAR (255) NOT NULL,
				payer_mail VARCHAR (255) NOT NULL,
				registered DATETIME NOT NULL,
				updated DATETIME NOT NULL,
				expires DATETIME NOT NULL, 
				PRIMARY KEY  (ID)
			) ENGINE = MYISAM DEFAULT CHARSET = {$char};
EOS;
		//Create campaign table
		$campaign = self::campaign();
		$sql[] = <<<EOS
			CREATE TABLE {$campaign} (
				ID INT NOT NULL AUTO_INCREMENT,
				book_id BIGINT NOT NULL,
				price BIGINT NOT NULL,
				start DATETIME NOT NULL,
				end DATETIME NOT NULL,
				PRIMARY KEY  (ID)
			) ENGINE = MYISAM DEFAULT CHARSET = {$char};
EOS;
		//Create device table
		$devices = self::devices();
		$sql[] = <<<EOS
			CREATE TABLE {$devices} (
				ID BIGINT NOT NULL AUTO_INCREMENT,
				name VARCHAR(255) NOT NULL,
				slug VARCHAR(255) NOT NULL,
				PRIMARY KEY  (ID)
			) ENGINE = MYISAM DEFAULT CHARSET = {$char};
EOS;
		//Create relationships table
		$relationships = self::file_relationships();
		$sql[] = <<<EOS
			CREATE TABLE {$relationships} (
				ID BIGINT NOT NULL AUTO_INCREMENT,
				file_id INT NOT NULL,
				device_id INT NOT NULL,
				PRIMARY KEY  (ID)
			) ENGINE = MYISAM DEFAULT CHARSET = {$char};
EOS;
		//Create promotion log
		$promotion_logs = self::promotion_logs();
		$sql[] = <<<EOS
			CREATE TABLE {$promotion_logs} (
				ID BIGINT NOT NULL AUTO_INCREMENT,
				transaction_id BIGINT NOT NULL,
				user_id BIGINT NOT NULL,
				reason VARCHAR(25) NOT NULL,
				estimated_reward BIGINT NOT NULL,
				start_post_id BIGINT NOT NULL,
				referrer TEXT NOT NULL,
				PRIMARY KEY (ID),
				INDEX promoter(user_id, reason)
			) ENGINE = MYISAM DEFAULT CHARSET = {$char}
EOS;
		//Create reward table
		$reward = self::reward_logs();
		$sql[] = <<<EOS
			CREATE TABLE {$reward} (
				ID BIGINT NOT NULL AUTO_INCREMENT,
				user_id BIGINT NOT NULL,
				price INT NOT NULL,
				status VARCHAR(20) NOT NULL,
				registered DATETIME NOT NULL,
				updated DATETIME NOT NULL,
				PRIMARY KEY (ID),
				INDEX requester(user_id, updated)
			) ENGINE = MYISAM DEFAULT CHARSET = {$char}
EOS;
		//Do dbDelta with praying...
		require_once ABSPATH."wp-admin/includes/upgrade.php";
		foreach($sql as $s){
			dbDelta($s);
		}
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

/**
 * Static class for Promotion log
 * @since 0.9
 */
class LWP_Promotion_TYPE{
	/**
	 * Promoted by user 
	 */
	const PROMOTION = 'PROMOTION';
	
	/**
	 * Sold by author himself 
	 */
	const SELL = 'SELL';
	
	/**
	 * Returns all type name
	 * @return array
	 */
	public static function get_all_type(){
		return array(
			self::PROMOTION,
			self::SELL
		);
	}
	
	/**
	 * For gettext scraping 
	 * @global Literally_WordPress $lwp
	 */
	private function _(){
		global $lwp;
		$lwp->_('PROMOTION');
		$lwp->_('SELL');
	}
}

/**
 * Static Class for datepicker strings. 
 * @since 0.9
 */
class LWP_Datepicker_Helper{
	
	/**
	 * Returns translated montnames array
	 * @return array
	 */
	public static function get_month_names(){
		$month_names = array();
		for($i = 1; $i <= 12; $i++){
			$month = gmmktime(0, 0, 0, $i, 1, 2011);
			$month_names[] = date_i18n('F', $month);
		}
		return $month_names;
	}
	
	/**
	 * Returns translated month short names array
	 * @return array
	 */
	public static function get_month_short_names(){
		$month_names_short = array();
		for($i = 1; $i <= 12; $i++){
			$month = gmmktime(0, 0, 0, $i, 1, 2011);
			$month_names_short[] = date_i18n('M', $month);
		}
		return $month_names_short;
	}
	
	/**
	 * Returns translated day names array
	 * @return array 
	 */
	public static function get_day_names(){
		return array(__('Sunday'), __('Monday'), __('Tuesday'), __('Wednesday'), __('Thursday'), __('Friday'), __('Saturday'));
	}
	
	/**
	 * Returns transalated day short names array
	 * @return array
	 */
	public static function get_day_short_names(){
		return array(__('Sun'), __('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat'));
	}
	
	/**
	 * Returns typical config array for datepciker
	 * @global Literally_WordPress $lwp
	 * @return array
	 */
	public static function get_config_array(){
		global $lwp;
		return array(
			'dateFormat' => 'yy-mm-dd',
			'timeFormat' => 'hh:mm:ss',
			'closeText' => $lwp->_('Close'),
			'prevText' => $lwp->_('Prev'),
			'nextText' => $lwp->_('Next'),
			'monthNames' => implode(',', self::get_month_names()),
			'monthNamesShort' => implode(',', self::get_month_short_names()),
			'dayNames' => implode(',', self::get_day_names()),
			'dayNamesShort' => implode(',', self::get_day_short_names()),
			'dayNamesMin' => implode(',', self::get_day_short_names()),
			'weekHeader' => $lwp->_('Week'),
			'timeOnlyTitle' => $lwp->_('Time'),
			'timeText' => $lwp->_('Time'),
			'hourText' => $lwp->_('Hour'),
			'minuteText' => $lwp->_('Minute'),
			'secondText' => $lwp->_('Second'),
			'currentText' => $lwp->_('Now'),
			'showMonthAfterYear' => (boolean)(get_locale() == 'ja'),
			'yearSuffix' => (get_locale() == 'ja') ? '年' : '',
			'changeYear' => true,
			'alertOldStart' => $lwp->_('Start date must be earlier than end date.')
		);
	}
}