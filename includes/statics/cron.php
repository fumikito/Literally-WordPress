<?php

/**
 * Cron hook constants
 * 
 * This class has cron name as contstants.
 * 
 * @since 0.9.3.1
 */
class LWP_Cron{
	
	/**
	 * Daily cron for clear outdated cvs transaction
	 */
	const CHOCOM_CVS_BATCH = 'lwp_chocom_cvs_batch';
	
	/**
	 * Daily cron for send messsage to transfer users.
	 */
	const NOTIFY_USER = 'lwp_daily_notification';
	
	/**
	 * Unregister all cron
	 */
	public static function deactivate(){
		wp_clear_scheduled_hook(self::CHOCOM_CVS_BATCH);
		wp_clear_scheduled_hook(self::NOTIFY_USER);
	}
}