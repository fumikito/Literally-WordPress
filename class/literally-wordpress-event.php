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
	 * Post type name
	 * @var string
	 */
	public $post_type = 'lwp-ticket';
	
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
		$this->post_types = $option['event_post_types'];
		$this->enabled = !empty($option['event_post_types']);
	}
	
	/**
	 * Executed on constructor
	 * @see Literally_WordPress_Common 
	 */
	public function on_construct(){
		if($this->enabled){
			//Create Post type
			add_action('init', array($this, 'register_post_type'));
			add_action('admin_menu', array($this, 'register_meta_box'));
		}
	}
	
	/**
	 * Create post type for ticket 
	 */
	public function register_post_type(){
		$single = $this->_('Ticket');
		$plural = $this->_('Tickets');
		register_post_type($this->post_type, array(
			'labels' => array(
				'name' => $plural,
				'singular_name' => $single,
				'add_new' => $this->_('Add New'),
				'add_new_item' => sprintf($this->_('Add New %s'), $single),
				'edit_item' => sprintf($this->_("Edit %s"), $single),
				'new_item' => sprintf($this->_('Add New %s'), $single),
				'view_item' => sprintf($this->_('View %s'), $single),
				'search_items' => sprintf($this->_("Search %s"), $plural),
				'not_found' =>  sprintf($this->_('No %s was found.'), $single),
				'not_found_in_trash' => sprintf($this->_('No %s was found in trash.'), $single), 
				'parent_item_colon' => ''
			),
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => false,
			'query_var' => false,
			'rewrite' => false,
			'capability_type' => 'page',
			'hierarchical' => false,
			'has_archive' => false,
			'show_in_nav_menus' => false
		));
	}
	
	/**
	 * Register metaboxes 
	 */
	public function register_meta_box(){
		foreach($this->post_types as $post){
			add_meta_box('lwp-event-detail', $this->_('Event Setting'), array($this, 'display_metabox'), $post, 'advanced', 'core');
		}
	}
	
	/**
	 * Show metabox
	 * @param type $post 
	 */
	public function display_metabox($post){
		require_once $this->dir.DIRECTORY_SEPARATOR."form-template".DIRECTORY_SEPARATOR."edit-detail-event.php";
	}
}
