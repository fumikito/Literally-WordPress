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



/**
 * Instance of Literally_WordPress
 *
 * @var Literally_WordPress
 */
$lwp = null;



// Don't allow plugin to be loaded directory
defined( 'ABSPATH' ) OR exit;



// Add action after plugins are loaded.
add_action( 'plugins_loaded', '_lwp_setup_after_plugins_loaded');


// Add deactivation hook.
register_deactivation_hook( __FILE__, '_lwp_deactivation_hook');



/**
 * Initialize function
 * 
 * @since 0.9.3
 * @global Literally_WordPress $lwp
 */
function _lwp_setup_after_plugins_loaded(){
	global $lwp;
	
	$errors = array();
	
	// Load all files
	$class_base = dirname(__FILE__).'/includes/';
	
	// Main class
	require $class_base.'literally-wordpress.php';
	
	// Load all classes
	foreach(array(
		'statics'  => array('address-jp', 'campaign-calculation', 'campaign-type', 'context', 'cron', 'datepicker', 'payment-method', 'payment-status', 'paypal', 'promotion-type', 'table'),
		'base'     => array('literally-wordpress-common', 'japanese-payment'),
		'common'   => array('campaign', 'capability', 'rewrite', 'refund-manager', 'reward'), 
		'cart'     => array('cart', 'form-template', 'form-backend', 'form-event', 'form'),
		'products' => array('post', 'notifier', 'subscription', 'event', 'ios'),
		'payment'  => array('softbank-payment', 'gmo', 'ntt'),
	) as $base => $files){
		foreach($files as $file){
			$path = $class_base.$base.'/'.$file.'.php';
			if(file_exists($path)){
				require $path;
			}else{
				if(!isset($errors['file'])){
					$errors['file'] = array();
				}
				$errors['file'][] = $path;
			}
		}
	}

	// Check curl is available
	if(!function_exists('curl_init')){
		$errors['curl'] = true;
	}
	
	if(empty($errors)){
		// All green. let's instantiate LWP!!!
		$lwp = new Literally_WordPress();
		// Load user functions.
		require_once dirname(__FILE__).DIRECTORY_SEPARATOR."functions.php";
		//For poedit scraping. It won't be executed.
		if(false){
			$lwp->_('Literally WordPress is activated but is not available. This plugin needs PHP version 5<. Your PHP version is %1$s.');
			$lwp->_(' Furthermore, this plugin needs cURL module.');
			$lwp->_(' Please contact to your server administrator to change server configuration.');
			$lwp->_('This plugin make your WordPress post object payable via PayPal and so on. ePub, PDF, MP3, Live ticket, Web-Magazine... What you sell is up to you.');
		}
	}else{
		// Error occurred.
		load_plugin_textdomain('literally-wordpress', false, basename(__FILE__).'/language');
		$error_msg = array(sprintf('<strong>%s</strong>', esc_attr('Literally WordPress Error:')));
		if(isset($errors['curl'])){
			$error_msg[] = esc_attr(__('This plugin needs cURL module. Please contact to your server administrator to change server configuration.', 'literally-wordpress'));
		}
		if(isset($errors['file'])){
			$error_msg[] = esc_attr(sprintf(__('Failed to load %d files. Please check if plugin files are uploaded correctly.', 'literally-wordpress'), count($errors['file'])));
			$error_msg[] = sprintf('<code>%s</code>', esc_attr(implode(', ', $errors['file'])));
		}
		add_action('admin_notices', create_function('', 'echo "<div id=\"message\" class=\"error\"><p>'.implode('<br />', $error_msg).'</p></div>"; '));
	}
}





/**
 * Fired when plugin is deactivated
 * 
 * @since 0.9.3
 */
function _lwp_deactivation_hook(){
	// Load cron class and clear.
	require_once dirname(__FILE__).'/includes/statics/cron.php';
	LWP_Cron::deactivate();
}