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
	 * Key name of post meta for Selling limit
	 * @var string
	 */
	public $meta_selling_limit = '_lwp_event_limit';
	
	/**
	 * Key name of post meta for cancel limit
	 * @var string
	 */
	public $meta_cancel_limits = '_lwp_event_cancel_limits';

	/**
	 * Key name of post meta for ticket stock
	 * @var string
	 */
	public $meta_stock = '_lwp_ticket_stock';

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
			add_action('save_post', array($this, 'save_post'));
			add_action('wp_ajax_lwp_edit_ticket', array($this, 'update_ticket'));
			add_action('wp_ajax_lwp_delete_ticket', array($this, 'delete_ticket'));
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
	
	/**
	 * Save event meta information
	 * @param int $post_id 
	 */
	public function save_post($post_id){
		if(isset($_REQUEST['_lwpeventnonce']) && wp_verify_nonce($_REQUEST['_lwpeventnonce'], 'lwp_event_detail')){
			//Save selling limit
			if(isset($_REQUEST['event_selling_limit']) && preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/u", $_REQUEST['event_selling_limit'])){
				update_post_meta($post_id, $this->meta_selling_limit, $_REQUEST['event_selling_limit']);
			}else{
				delete_post_meta($post_id, $this->meta_selling_limit);
			}
			//Get refund limit
			if(isset($_REQUEST['cancel_limit_day'], $_REQUEST['cancel_limit_ratio']) && is_array($_REQUEST['cancel_limit_day']) && is_array($_REQUEST['cancel_limit_ratio'])){
				$cancel_limits = array();
				for($i = 0, $l = count($_REQUEST['cancel_limit_ratio']); $i < $l; $i++){
					$cancel_limits[] = array(
						'days' => $_REQUEST['cancel_limit_day'][$i],
						'ratio' => $_REQUEST['cancel_limit_ratio'][$i]
					);
				}
				$func = '
					if($a["days"] == $b["days"]){
						return 0;
					}else{
						return ($a["days"] < $b["days"]) ? -1 : 1;
					}
				';
				usort($cancel_limits, create_function('$a,$b', $func));
				update_post_meta($post_id, $this->meta_cancel_limits, $cancel_limits);
			}else{
				delete_post_meta($post_id, $this->meta_cancel_limits);
			}
		}
	}
	
	/**
	 * Update ticket information 
	 * @global wpdb $wpdb
	 */
	public function update_ticket(){
		if(isset($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'lwp_event_detail')){
			global $wpdb;
			$parent = (int) $wpdb->get_var($wpdb->prepare("SELECT post_author FROM {$wpdb->posts} WHERE ID = %d", $_REQUEST['post_parent']));
			$post_arr =  array(
				'post_title' => (string) $_REQUEST['post_title'],
				'post_content' => (string) $_REQUEST['post_content'],
				'post_parent' => (int) $_REQUEST['post_parent'],
				'post_author' => $parent,
				'post_status' => 'publish',
				'post_type' => $this->post_type
			);
			$status = true;
			if(intval($_REQUEST['post_id']) > 0){
				$post_arr['ID'] = intval($_REQUEST['post_id']);
				$result = wp_update_post($post_arr);
				if(!$result){
					$status = false;
				}
				$mode = 'update';
			}else{
				$post_id = wp_insert_post($post_arr, true);
				if(is_wp_error($post_id)){
					$status = false;
				}
				$mode = 'insert';
			}
			if($status){
				$post = wp_get_single_post($post_id);
				update_post_meta($post->ID, $this->meta_stock, intval($_REQUEST['stock']));
				update_post_meta($post->ID, 'lwp_price', $_REQUEST['price']);
			}
			header("Content-Type: application/json; charset=utf-8");
			echo json_encode(array(
				'status' => $status,
				'mode' => $mode,
				'message' => $this->_('Failed to edit ticket'),
				'post_id' => $post->ID,
				'post_title' => $post->post_title,
				'post_content' => mb_substr($post->post_title, 0, 20, 'utf-8'),
				'price' => numberformat(get_post_meta($post->ID, 'lwp_price', true)),
				'stock' => numberformat(get_post_meta($post->ID, $this->meta_stock, true))
			));
			die();
		}else{
			echo 'hoge';
			die();
		}
	}
	
	/**
	 * チケットを削除する 
	 */
	public function delete_ticket(){
		if(isset($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'lwp_event_detail')){
			$json = array(
				'status' => true,
				'message' => ''
			);
			if(user_can_edit_post(get_current_user_id(), $_REQUEST['post_id'])){
				wp_delete_post($_REQUEST['post_id']);
			}else{
				$json['status'] = false;
				$json['message'] = $this->_('You have no permission to edit this ticket.');
			}
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode($json);
			die();
		}
	}
}
