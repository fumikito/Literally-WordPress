<?php
/**
 * Controller for event
 *
 * @package Literally WordPress
 */
class LWP_Event extends Literally_WordPress_Common {
	
	/**
	 * Post type to assign event
	 * @var array
	 */
	public $post_types = array();
	
	/**
	 * Setup option 
	 * 
	 * @see Literally_WordPress_Common
	 * @param array $option 
	 */
	public function set_option($option){
		$option = shortcode_atts(array(
			'event_post_types' => array()
		), $option);
		$this->post_types = array_map('intval', $option['event_post_types']);
		$this->enabled = !empty($option['event_post_types']);
	}
	
	/**
	 * Executed on constructor
	 * @see Literally_WordPress_Common 
	 */
	public function on_construct(){
		
	}
}
