<?php
/*
 * Delete all data for Literally WordPress
 * 
 * @package literally_wordpress
 * @since 0.8.6
 */
//Check whether WordPress is initialized or not.
if(!defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN')){
	exit();
}

//Include Class file
require_once dirname(__FILE__).DIRECTORY_SEPARATOR."literally-wordpress-statics.php";

//Get global scope variable for the precaution.
/* @var $wpdb wpdb*/
global $wpdb;

//Delete All Tables
foreach(LWP_Tables::get_tables() as $table){
	$sql = "DROP TABLE `{$table}`";
	$wpdb->query($sql);
}

//Delete All Notifications
$notification_ids = $wpdb->get_results("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'lwp_notification'");
foreach($notification_ids as $id){
	wp_delete_post($id, true);
}

//Delete Option
delete_option('literally_wordpress_option');