<?php
/**
 * Plugin Name: Litteraly WordPress
 * Plugin URI: http://lwper.info
 * Description: This plugin make your WordPress post object payable via PayPal and so on. ePub, PDF, MP3, Live ticket, Web-Magazine... What you sell is up to you.
 * Author: Takahashi Fumiki<takahashi.fumiki@hametuha.co.jp>
 * Version: 0.9.3.1
 * Author URI: http://takahashifumiki.com
 * Text Domain: literally-wordpress
 * Domain Path: /language/
 */

//Check requirements.
if(version_compare(PHP_VERSION, '5.0') >= 0 && function_exists('curl_init')){
		
	//Main class
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR."literally-wordpress.php";
	
	//Static
	foreach(scandir(dirname(__FILE__).DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR.'statics') as $file){
		if(preg_match("/^[^\.].*\.php/", $file)){
			require_once dirname(__FILE__).DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR.'statics'.DIRECTORY_SEPARATOR.$file;
		}
	}
	
	//Base class
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR."base".DIRECTORY_SEPARATOR."literally-wordpress-common.php";
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR."base".DIRECTORY_SEPARATOR."japanese-payment.php";
	
	// Common components
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR.'common'.DIRECTORY_SEPARATOR."capability.php";
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR.'common'.DIRECTORY_SEPARATOR."rewrite.php";
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR.'common'.DIRECTORY_SEPARATOR."refund-manager.php";
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR.'common'.DIRECTORY_SEPARATOR."reward.php";
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR.'common'.DIRECTORY_SEPARATOR."campaign.php";
	
	// Cart related classes
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR.'cart'.DIRECTORY_SEPARATOR."cart.php";
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR.'cart'.DIRECTORY_SEPARATOR."form-template.php";
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR.'cart'.DIRECTORY_SEPARATOR."form-backend.php";
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR.'cart'.DIRECTORY_SEPARATOR."form-event.php";
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR.'cart'.DIRECTORY_SEPARATOR."form.php";
	
	// Subclass
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR.'products'.DIRECTORY_SEPARATOR."post.php";
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR.'products'.DIRECTORY_SEPARATOR."notifier.php";
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR.'products'.DIRECTORY_SEPARATOR."subscription.php";
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR.'products'.DIRECTORY_SEPARATOR."event.php";
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR.'products'.DIRECTORY_SEPARATOR."ios.php";
	
	// Payment Class
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR."payment".DIRECTORY_SEPARATOR."softbank-payment.php";
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR."payment".DIRECTORY_SEPARATOR."gmo.php";
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR."payment".DIRECTORY_SEPARATOR."ntt.php";
	
	/**
	 * Instance of Literally_WordPress
	 *
	 * @var Literally_WordPress
	 */
	$lwp = new Literally_WordPress();

	//Load user functions.
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."functions.php";
	
	//For poedit scraping. It won't be executed.
	if(false){
		$lwp->_('Literally WordPress is activated but is not available. This plugin needs PHP version 5<. Your PHP version is %1$s.');
		$lwp->_(' Furthermore, this plugin needs cURL module.');
		$lwp->_(' Please contact to your server administrator to change server configuration.');
		$lwp->_('This plugin make your WordPress post object payable via PayPal and so on. ePub, PDF, MP3, Live ticket, Web-Magazine... What you sell is up to you.');
	}
	
}else{
	
	load_plugin_textdomain('literally-wordpress', false, basename(__FILE__).DIRECTORY_SEPARATOR."language");
	$error_msg = sprintf(__('Literally WordPress is activated but is not available. This plugin needs PHP version 5<. Your PHP version is %1$s.', 'literally-wordpress'), phpversion());
	if(!function_exists('curl_init')){
		$error_msg .= __(' Furthermore, this plugin needs cURL module.', 'literally-wordpress');
	}
	$error_msg .= __(' Please contact to your server administrator to change server configuration.');
	add_action('admin_notices', create_function('', 'echo "<div id=\"message\" class=\"error\"><p><strong>'.$error_msg.'</strong></p></div>"; '));
}