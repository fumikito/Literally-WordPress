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
	 * Key name of event's post meta
	 * @var string
	 */
	public $meta_footer_note = '_lwp_event_footer_note';
	
	/**
	 * Footer signature display on mail
	 * @var string
	 */
	private $_signature = '';
	
	public $ticket_detal = array(
		'site_name', 'site_url', ''
	);
	
	/**
	 * Setup option 
	 * 
	 * @see Literally_WordPress_Common
	 * @param array $option 
	 */
	public function set_option($option){
		$option = shortcode_atts(array(
			'event_post_types' => array(),
			'event_signature' => ''
		), $option);
		$this->post_types = $option['event_post_types'];
		$this->_signature = (string)$option['event_signature'];
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
			add_action('wp_ajax_lwp_get_ticket', array($this, 'get_ticket'));
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
			//Save footer note
			if(isset($_REQUEST['event_footer_note']) && !empty($_REQUEST['event_footer_note'])){
				update_post_meta($post_id, $this->meta_footer_note, array(
					'text' => (string) $_REQUEST['event_footer_note'],
					'autop' => (boolean) (isset($_REQUEST['event_footer_note_autop']) && $_REQUEST['event_footer_note_autop'])
				));
			}else{
				delete_post_meta($post_id, $this->meta_footer_note);
			}
		}
	}
	
	/**
	 * Returns if specified post has tickets
	 * @global wpdb $wpdb
	 * @param int $post_id
	 * @return int
	 */
	public function has_tickets($post_id){
		global $wpdb;
		$sql = <<<EOS
			SELECT COUNT(ID) FROM {$wpdb->posts}
			WHERE post_type = %s AND post_parent = %d AND post_status = 'publish'
EOS;
		return (int) $wpdb->get_var($wpdb->prepare($sql, $this->post_type, $post_id));
	}
	
	/**
	 * Returns ticket id of specified post
	 * @global wpdb $wpdb
	 * @param int $event_id
	 * @return array 
	 */
	public function get_chicket_ids($event_id){
		global $wpdb;
		$chicket_ids = array();
		$sql = <<<EOS
			SELECT ID FROM {$wpdb->posts}
			WHERE post_type = %s AND post_parent = %d
			ORDER BY post_date DESC
EOS;
		foreach($wpdb->get_results($wpdb->prepare($sql, $this->post_type, $event_id)) as $ticket){
			$chicket_ids[] = $ticket->ID;
		}
		return $chicket_ids;
	}
	
	/**
	 * Returns if user is participating 
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param int $user_id
	 * @param int $post_id
	 * @return boolean
	 */
	public function is_participating($user_id, $post_id){
		global $wpdb, $lwp;
		$sql = <<<EOS
			SELECT t.ID FROM {$lwp->transaction} AS t
			INNER JOIN {$wpdb->posts} AS p
			ON t.book_id = p.ID
			WHERE t.user_id = %d AND t.status = %s AND p.post_parent = %d
EOS;
		return $wpdb->get_var($wpdb->prepare($sql, $user_id, LWP_Payment_Status::SUCCESS,$post_id));
	}
	
	/**
	 * Get event id from ticket's id
	 * @global wpdb $wpdb
	 * @param int $ticket_id
	 * @return int 
	 */
	public function get_event_from_ticket_id($ticket_id){
		global $wpdb;
		return (int)$wpdb->get_var($wpdb->prepare("SELECT post_parent FROM {$wpdb->posts} WHERE ID = %d", $ticket_id));
	}
	
	/**
	 * Returns event total sales
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param int $event_id
	 * @return int 
	 */
	public function get_event_transaction_total($event_id){
		global $lwp, $wpdb;
		$sql = <<<EOS
			SELECT SUM(t.price) FROM {$lwp->transaction} AS t
			INNER JOIN {$wpdb->posts} AS p
			ON t.book_id = p.ID
			WHERE p.post_parent = %d
EOS;
		return $wpdb->get_var($wpdb->prepare($sql, $event_id));
	}
	
	/**
	 * Returns cancel condition with specified timestamp
	 * @param int $post_id
	 * @param int $timestamp
	 * @return array array which has key 'days' and 'ratio'
	 */
	public function get_current_cancel_condition($post_id, $timestamp = false){
		if(!$timestamp){
			$timestamp = time();
		}
		$limit = false;
		$selling_limits = get_post_meta($post_id, $this->meta_selling_limit, true);
		$cancel_limits = get_post_meta($post_id, $this->meta_cancel_limits, true);
		if($cancel_limits && $selling_limits && is_array($cancel_limits)){
			$selling_limits = strtotime($selling_limits);
			for($i = count($cancel_limits) - 1; $i >= 0; $i--){
				//Check if current time doesn't exceed limit
				if($selling_limits - $cancel_limits[$i]['days'] * 60 * 60 * 24 < $timestamp){
					continue;
				}else{
					$limit = $cancel_limits[$i];
					break;
				}
			}
		}
		return $limit;
	}
	
	
	
	/**
	 * Returns ticket id from event's ID
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param int $user_id
	 * @param int $event_id
	 * @return array 
	 */
	public function get_cancelable_tickets($user_id, $event_id){
		global $wpdb, $lwp;
		$sql = <<<EOS
			SELECT t.*, p.post_title FROM {$lwp->transaction} AS t
			INNER JOIN {$wpdb->posts} AS p
			ON t.book_id = p.ID
			WHERE p.post_parent = %d AND t.user_id = %d AND t.status = %s
EOS;
		return $wpdb->get_results($wpdb->prepare($sql, $event_id, $user_id, LWP_Payment_Status::SUCCESS));
	}
	
	
	public function is_refundable($ticket_id, $date){
		
	}
	
	/**
	 * Update ticket information 
	 * @global wpdb $wpdb
	 */
	public function update_ticket(){
		if(isset($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'lwp_event_detail')){
			global $wpdb;
			$parent = (int) $wpdb->get_var($wpdb->prepare("SELECT post_author FROM {$wpdb->posts} WHERE ID = %d", $_REQUEST['post_parent']));
			if(!user_can_edit_post(get_current_user_id(), $_REQUEST['post_parent'])){
				$status = false;
			}else{
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
					$post_id = wp_update_post($post_arr);
					if(!$post_id){
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
			}
			header("Content-Type: application/json; charset=utf-8");
			$json = array(
				'status' => $status,
				'mode' => $mode,
				'message' => $this->_('Failed to edit ticket'),
				'post_id' => $status ? $post->ID : 0,
				'post_title' => $status ? $post->post_title : '',
				'post_content' => $status ? mb_substr($post->post_content, 0, 20, 'utf-8').'...' : '',
				'price' => $status ? number_format(get_post_meta($post->ID, 'lwp_price', true)) : 0,
				'stock' => $status ? number_format(get_post_meta($post->ID, $this->meta_stock, true)) : 0
			);
			echo json_encode($json);
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
	
	/**
	 * Returns ticket information via Ajax 
	 */
	public function get_ticket(){
		if(isset($_REQUEST['_wpnonce'], $_REQUEST['post_id']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'lwp_event_detail')){
			$json = array(
				'status' => false,
				'message' => ''
			);
			$post = wp_get_single_post($_REQUEST['post_id']);
			if($post){
				$json['status'] = true;
				$json['post_title'] = $post->post_title;
				$json['post_content'] = $post->post_content;
				$json['price'] = get_post_meta($post->ID, 'lwp_price', true);
				$json['stock'] = get_post_meta($post->ID, $this->meta_stock, true);
			}else{
				$json['message'] = $this->_('You have no permission to edit this ticket.');
			}
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode($json);
			die();
		}
	}
	
	/**
	 * Retruns url of QR Code
	 * @param string $url
	 * @param int $size 
	 * @param string $protocol null, http or https. Default null.
	 */
	public function get_qrcode($url, $size = 200, $protocol = null){
		if(is_null($protocol)){
			$p = '//';
		}else{
			$p = $protocol.'://';
		}
		$size = intval($size);
		return "{$p}chart.googleapis.com/chart?chs={$size}x{$size}&cht=qr&chl=".rawurlencode($url);
	}
	
	/**
	 * Generate token for particular user
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param int $event_id
	 * @param int $user_id
	 * @return string 
	 */
	public function generate_token($event_id, $user_id){
		global $wpdb, $lwp;
		$salt = hexdec($this->get_salt());
		$salt *= $event_id;
		$salt *= $user_id;
		return strtoupper(base_convert($salt, 10, 36));
	}
	
	/**
	 * Returns userid by parsing token
	 * @global wpdb $wpdb
	 * @param int $event_id
	 * @param string $token
	 * @return int
	 */
	public function parse_token($event_id, $token){
		global $wpdb;
		$origin = base_convert(strtolower($token), 36, 10);
		$salt = hexdec($this->get_salt());
		$user_id = $origin / $salt / $event_id;
		return (int)$wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->users} WHERE ID = %d", $user_id));
	}
	
	/**
	 * Get salt.
	 * @return string 
	 */
	private function get_salt(){
		return substr(md5(defined('SECURE_AUTH_SALT') ? SECURE_AUTH_SALT : 'literallywordpress'), 0, 4);
	}
	
	/**
	 * Returns footer note text for event.
	 * @param int $post_id
	 * @param boolean $raw
	 * @return string 
	 */
	public function get_footer_note($post_id, $raw = false){
		$footer_note = get_post_meta($post_id, $this->meta_footer_note, true);
		if($footer_note && isset($footer_note['text'])){
			if($raw){
				return $footer_note['text'];
			}else{
				$footer_note_text = $footer_note['text'];
				foreach($this->ticket_detal as $key){
					
				}
				return ($this->footer_note_needs_autop($post_id))
						? wpautop($footer_note_text)
						: $footer_note_text;
			}
		}else{
			return '';
		}
	}
	
	/**
	 * Returns is footer notes needs autop
	 * @param int $post_id
	 * @return boolean 
	 */
	public function footer_note_needs_autop($post_id){
		$footer_note = get_post_meta($post_id, $this->meta_footer_note, true);
		return isset($footer_note['autop']) && $footer_note['autop'];
	}
	
	/**
	 * Returns signature
	 * @return string 
	 */
	public function get_signature(){
		return (string)$this->_signature;
	}
}
