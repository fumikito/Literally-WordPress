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
	const VERSION = '0.9.3';
	
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
	 * Returns file log table
	 * @return string
	 */
	public static function file_logs(){
		return self::get_name('file_logs');
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
		//Create file log table
		$file_logs = self::file_logs();
		$sql[] = <<<EOS
			CREATE TABLE {$file_logs} (
				ID INT NOT NULL AUTO_INCREMENT,
				file_id BIGINT NOT NULL,
				user_id BIGINT NOT NULL,
				user_agent VARCHAR(255) NOT NULL,
				ip_address VARCHAR(255) NOT NULL,
				updated DATETIME NOT NULL,
				PRIMARY KEY  (ID),
				INDEX  file(file_id)
			) ENGINE = MYISAM DEFAULT CHARSET = {$char};
EOS;
		//Create transactios table
		$transactions = self::transaction();
		$method = LWP_Payment_Methods::PAYPAL;
		$sql[] = <<<EOS
			CREATE TABLE {$transactions} (
				ID BIGINT NOT NULL AUTO_INCREMENT,
				user_id BIGINT NOT NULL,
				book_id BIGINT NOT NULL,
				price BIGINT NOT NULL,
				refund BIGINT NOT NULL DEFAULT 0,
				num INT NOT NULL DEFAULT 1,
				consumed INT NOT NULL DEFAULT 0,
				status VARCHAR(45) NOT NULL,
				method VARCHAR(100) NOT NULL DEFAULT '{$method}',
				campaign_id BIGINT NOT NULL DEFAULT 0,
				transaction_key VARCHAR (255) NOT NULL,
				transaction_id VARCHAR (255) NOT NULL,
				payer_mail VARCHAR (255) NOT NULL,
				registered DATETIME NOT NULL,
				updated DATETIME NOT NULL,
				expires DATETIME NOT NULL,
				misc TEXT NOT NULL,
				PRIMARY KEY  (ID)
			) ENGINE = MYISAM DEFAULT CHARSET = {$char};
EOS;
		//Create campaign table
		$campaign = self::campaign();
		$type = LWP_Campaign_Type::SINGULAR;
		$calculation = LWP_Campaign_Calculation::SPECIAL_PRICE;
		$sql[] = <<<EOS
			CREATE TABLE {$campaign} (
				ID INT NOT NULL AUTO_INCREMENT,
				book_id BIGINT NOT NULL,
				price BIGINT NOT NULL,
				start DATETIME NOT NULL,
				end DATETIME NOT NULL,
				method VARCHAR(45) NOT NULL,
				type VARCHAR(45) NOT NULL DEFAULT '{$type}',
				calculation VARCHAR(45) NOT NULL DEFAULT '{$calculation}',
				coupon VARCHAR (255) NOT NULL,
				key_name VARCHAR(45) NOT NULL,
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
				PRIMARY KEY  (ID),
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
				PRIMARY KEY  (ID),
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