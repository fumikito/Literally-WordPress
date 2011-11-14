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
global $wpdb;

//Delete All Tabel
foreach(LWP_Tables::get_tables() as $table){
	$sql = "DROP TABLE `{$table}`";
	$wpdb->query($sql);
}

//Delete Option
delete_option('literally_wordpress_option');