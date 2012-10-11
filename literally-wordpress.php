<?php
/**
 * Plugin Name: Litteraly WordPress
 * Plugin URI: http://wordpress.org/extend/plugins/literally-wordpress/
 * Description: This plugin make your WordPress post payable. Registered users can buy your post via PayPal. You can provide several ways to reward their buying. Add rights to download private file, to accesss private post and so on.
 * Author: Takahashi Fumiki<takahashi.fumiki@hametuha.co.jp>
 * Version: 0.9.2.5
 * Author URI: http://takahashifumiki.com
 * Text Domain: literally-wordpress
 * Domain Path: /language/
 */

//Check requirements.
if(version_compare(PHP_VERSION, '5.0') >= 0 && function_exists('curl_init')){
		
	//Main class
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."literally-wordpress.php";
	
	//Static class
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR.'statics'.DIRECTORY_SEPARATOR.'lwp.php';
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR.'statics'.DIRECTORY_SEPARATOR."paypal.php";
	
	//Base class
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."base".DIRECTORY_SEPARATOR."literally-wordpress-common.php";
	
	//Subclass
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."campaign.php";
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."post.php";
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."form.php";
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."notifier.php";
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."subscription.php";
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."reward.php";
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."event.php";
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."ios.php";
	
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