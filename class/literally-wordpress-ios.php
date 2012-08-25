<?php

class LWP_iOS extends Literally_WordPress_Common{
	
	/**
	 * Whether user can buy from public site
	 * @var boolean
	 */
	private $web_available = false;
	
	/**
	 * Whether post type is public
	 * @var booelan
	 */
	private $post_type_public = false;
	
	/**
	 * post meta key name for product id
	 * @var string 
	 */
	public $product_id = '_lwp_ios_product_id';
	
	/**
	 * @see Literally_WordPress_Common
	 */
	public function set_option($option) {
		$option = shortcode_atts(array(
			'ios' => false,
			'ios_public' => false,
			'ios_available' => false
		), $option);
		$this->enabled = (boolean) $option['ios'];
		$this->web_available = (boolean) $option['ios_available'];
		$this->post_type_public = (boolean) $option['ios_public'];
	}
	
	/**
	 * @see Literally_WordPress_Common
	 */
	public function on_construct() {
		if($this->is_enabled()){
			add_action('init', array($this, 'register_post_type'), 20);
		}
	}
	
	/**
	 * Register Post Type
	 */
	public function register_post_type(){
		$singular = $this->_('iOS Product');
		$plural = $this->_('iOS Products');
		$args = apply_filters('lwp_ios_post_type_args', array(
			'labels' => array(
				'name' => $plural,
				'singular_name' => $singular,
				'add_new' => $this->_('Add New'),
				'add_new_item' => sprintf($this->_('Add New %s'), $singular),
				'edit_item' => sprintf($this->_("Edit %s"), $singular),
				'new_item' => sprintf($this->_('Add New %s'), $singular),
				'view_item' => sprintf($this->_('View %s'), $singular),
				'search_items' => sprintf($this->_("Search %s"), $singular),
				'not_found' =>  sprintf($this->_('No %s was found.'), $singular),
				'not_found_in_trash' => sprintf($this->_('No %s was found in trash.'), $singular),
				'parent_item_colon' => ''
			),
			'public' => $this->post_type_public,
			'publicly_queryable' => $this->post_type_public,
			'show_ui' => true, 
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'menu_position' => 10,
			'has_archive' => true,
			'supports' => array('title','editor','author','thumbnail','excerpt', 'comments', 'custom-fields'),
			'show_in_nav_menus' => $this->post_type_public,
			'menu_icon' => $this->url."/assets/icon-iphone.png"
		));
		register_post_type('ios-product', $args);
	}
}