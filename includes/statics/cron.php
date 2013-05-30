<?php

/**
 * Cron hook constants
 * 
 * @since 0.9.3.1
 */
class LWP_Cron{
	
	/**
	 * Daily cron for clear outdated cvs transaction
	 */
	const CHOCOM_CVS_BATCH = 'CHOCOM_CVS_BATCH';
	
	/**
	 * Unregister all cron
	 */
	public static function deactivate(){
		
	}
}