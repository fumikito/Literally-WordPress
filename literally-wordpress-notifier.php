<?php
/**
 * Notification Utility
 * @package Literally WordPress
 */

class LWP_Notifier{
	
	/**
	 * @var boolean
	 */
	private $valid = false;
	
	/**
	 *@var int
	 */
	private $frequency = 0;
	
	/**
	 * @var int
	 */
	private $limit = 30;
	
	/**
	 * Constructor
	 * @param boolean $valid
	 * @param int $frequency_per_days
	 * @param int $limit_days
	 */
	public function __construct($valid, $frequency_per_days, $limit_days) {
		$this->valid = (boolean)$valid;
		$this->frequency = (int)$frequency;
		$this->limit = (int)$limit;
		if($this->valid){
			
		}
	}
	
	public function register_post_type(){
		
	}
	
	public function register_cron(){
		
	}
	
	private function get_body($type = 'footer', $args = array()){
		foreach((array)$args as $key => $val){
			
		}
	}
	
	public function update($type, $content){
		
	}
	
	private function get_mail($type){
		
	}
	
	/**
	 * Return if notification is valid
	 * @return boolean
	 */
	public function is_valid(){
		return $this->valid;
	}
}