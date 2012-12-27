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
	 * Whether if user can wait for cancellation
	 * @var boolean
	 */
	public $cancel_list = false;
	
	/**
	 * Key name of post meta for event start
	 * @var string 
	 */
	public $meta_start = '_lwp_event_start';
	
	/**
	 * Key name of post meta for event ends
	 * @var string
	 */
	public $meta_end = '_lwp_event_end';
	
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
	 * Key name of event's cancel list
	 * @var string
	 */
	public $meta_cancel_list = '_lwp_has_cancel_list';
	
	/**
	 * Message to be displayed on cancel list
	 * @var string
	 */
	public $awaiting_message = '';
	
	/**
	 * Footer signature display on mail
	 * @var string
	 */
	private $_signature = '';
	
	/**
	 * Mail body sent on transaction
	 * @var string 
	 */
	private $_mail_body = '';
	
	/**
	 * Place holder to replace mail
	 * @var array
	 */
	private $_place_holders = array();
	
	/**
	 * Setup option 
	 * 
	 * @see Literally_WordPress_Common
	 * @param array $option 
	 */
	public function set_option($option){
		$option = shortcode_atts(array(
			'event_post_types' => array(),
			'event_mail_body' => '',
			'event_signature' => '',
			'event_awaiting' => true,
			'event_awaiting_message' => ''
		), $option);
		$this->post_types = $option['event_post_types'];
		$this->_signature = (string)$option['event_signature'];
		$this->_mail_body = (string)$option['event_mail_body'];
		$this->enabled = !empty($option['event_post_types']);
		$this->cancel_list = (boolean)$option['event_awaiting'];
		$this->awaiting_message = $option['event_awaiting_message'];
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
			add_action('wp_ajax_lwp_contact_participants', array($this, 'contact_participants'));
			add_action('wp_ajax_lwp_event_csv_output', array($this, 'output_csv'));
			add_action('wp_ajax_lwp_ticket_presets', array($this, 'register_presets'));
			add_filter('the_content', array($this, 'show_form'));
			if(!empty($this->_mail_body)){
				add_action('lwp_update_transaction', array($this, 'send_email'));
			}
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
			//Save start and end
			foreach(array('start' => $this->meta_start, 'end' => $this->meta_end) as $key => $key_name){
				if(isset($_REQUEST["event_{$key}_time"]) && preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/", $_REQUEST["event_{$key}_time"])){
					update_post_meta($post_id, $key_name, $_REQUEST["event_{$key}_time"]);
				}else{
					delete_post_meta($post_id, $key_name);
				}
			}
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
			//Save cancel list
			if(isset($_REQUEST['event_awaiting']) && $_REQUEST['event_awaiting']){
				update_post_meta($post_id, $this->meta_cancel_list, true);
			}else{
				update_post_meta($post_id, $this->meta_cancel_list, false);
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
			WHERE t.user_id = %d AND ( (t.status = %s) OR (t.status = %s AND t.method = %s) ) AND p.post_parent = %d
EOS;
		return $wpdb->get_var($wpdb->prepare($sql, $user_id, LWP_Payment_Status::SUCCESS, LWP_Payment_Status::AUTH, LWP_Payment_Methods::SOFTBANK_CC, $post_id));
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
			WHERE p.post_parent = %d AND t.status = %s
EOS;
		return $wpdb->get_var($wpdb->prepare($sql, $event_id, LWP_Payment_Status::SUCCESS));
	}
	
	/**
	 * Returns ticket total sales
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param type $ticket_id
	 * @return type 
	 */
	public function get_ticket_total_sales($ticket_id){
		global $lwp, $wpdb;
		$sql = <<<EOS
			SELECT SUM(t.price) FROM {$lwp->transaction} AS t
			WHERE t.book_id = %d AND t.status = %s
EOS;
		return $wpdb->get_var($wpdb->prepare($sql, $ticket_id, LWP_Payment_Status::SUCCESS));
	}
	
	/**
	 * Returns cancel condition with specified timestamp
	 * @param int $post_id
	 * @param int $timestamp
	 * @return array array which has key 'days' and 'ratio'
	 */
	public function get_current_cancel_condition($post_id, $timestamp = false){
		if(!$timestamp){
			$timestamp = current_time('timestamp');
		}
		$limit = false;
		$selling_limits = get_post_meta($post_id, $this->meta_selling_limit, true);
		$cancel_limits = get_post_meta($post_id, $this->meta_cancel_limits, true);
		if($cancel_limits && $selling_limits && is_array($cancel_limits)){
			usort($cancel_limits, array($this, '_sort_condition'));
			$selling_limits = strtotime($selling_limits.' 23:59:59');
			$left_days = ($selling_limits - $timestamp) / 60 / 60 / 24;
			for($i = 0, $l = count($cancel_limits); $i < $l; $i++){
				if($i == 0){
					//First
					if($cancel_limits[$i]['days'] <= $left_days){
						$limit = $cancel_limits[$i];
						break;
					}
				}else{
					if($cancel_limits[$i - 1]['days'] > $left_days && $cancel_limits[$i]['days'] <= $left_days){
						$limit = $cancel_limits[$i];
						break;
					}
				}
			}
		}
		return $limit;
	}
	
	/**
	 * Sort cancel limits
	 * @param array $var1
	 * @param array $var2
	 * @return int
	 */
	public function _sort_condition($var1, $var2){
		if($var1['days'] < $var2['days']){
			return 1;
		}elseif($var1['days'] > $var2['days']){
			return -1;
		}else{
			return 0;
		}
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
			WHERE p.post_parent = %d AND t.user_id = %d AND ( (t.status = %s) OR (t.method = %s AND t.status = %s))
EOS;
		return $wpdb->get_results($wpdb->prepare($sql, $event_id, $user_id, LWP_Payment_Status::SUCCESS, LWP_Payment_Methods::SOFTBANK_CC, LWP_Payment_Status::AUTH));
	}
	
	
	
	public function is_refundable($ticket_id, $date){
		
	}
	
	/**
	 * Returns ticket prisets.
	 * @param string $post_type
	 * @return array Default: empty array
	 */
	public function get_ticket_prisets($post_type){
		return apply_filters('lwp_ticket_prisets', array(), $post_type);
	}
	
	/**
	 * Return if specified post has preset ticket
	 * @global wpdb $wpdb
	 * @param object $post
	 * @return boolean
	 */
	public function presets_registered($post){
		global $wpdb;
		$presets = $this->get_ticket_prisets($post->post_type);
		$has_presets = false;
		if(!empty($presets)){
			foreach($presets as $preset){
				if($wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = %s AND post_parent = %d", $preset['post_title'], $this->post_type, $post->ID))){
					$has_presets = true;
					break;
				}
			}
		}
		return $has_presets;
	}
	
	/**
	 * Ajax action to regsiter preset
	 * @global wpdb $wpdb
	 */
	public function register_presets(){
		global $wpdb;
		if(isset($_REQUEST['_wpnonce'], $_REQUEST['event_id']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'lwp_ticket_presets')){
			$success = false;
			$message = '';
			$tickets = array();
			$event = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE ID = %d", $_REQUEST['event_id']));
			if(!$event || false === array_search($event->post_type, $this->post_types)){
				$message = $this->_('This post is not event.');
			}elseif(!current_user_can('edit_others_posts') && get_current_user_id() != $event->post_author){
				$message = $this->_('You have no permission to edit this post.');
			}elseif($this->presets_registered($event)){
				$message = $this->_('Preset tickets are already registered.');
			}else{
				$presets = $this->get_ticket_prisets($event->post_type);
				if(empty($presets) || !is_array($presets)){
					$message = $this->_('This post type has no presets.');
				}else{
					//Start registering
					$counter = 0;
					foreach($presets as $preset){
						$counter++;
						$stock = 0;
						$price = 0;
						$preset = wp_parse_args($preset, array(
							'post_title' => sprintf($this->_('Ticket %d'), $counter),
							'post_content' => '-',
							'post_status' => 'publish',
							'post_author' => $event->post_author,
							'post_parent' => $event->ID,
							'post_type' => $this->post_type
						));
						if(isset($preset['price'])){
							$price = (float)$preset['price'];
							unset($preset['price']);
						}
						if(isset($preset['stock'])){
							$stock = absint((string)$preset['stock']);
							unset($preset['stock']);
						}
						$ticket = wp_insert_post($preset, true);
						if(!is_wp_error($ticket)){
							update_post_meta($ticket, 'lwp_price', $price);
							update_post_meta($ticket, $this->meta_stock, $stock);
							$tickets[] = array(
								'post_id' => $ticket,
								'post_title' => $preset['post_title'],
								'post_content' => $preset['post_content'],
								'price' => $price,
								'stock' => $stock
							);
							$success = true;
						}
					}
				}
			}
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array(
				'success' => $success,
				'message' => $message,
				'tickets' => $tickets
			));
		}
		exit;
	}
	
	/**
	 * Update ticket information 
	 * @global wpdb $wpdb
	 */
	public function update_ticket(){
		if(isset($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'lwp_event_detail')){
			global $wpdb;
			$parent = (int) $wpdb->get_var($wpdb->prepare("SELECT post_author FROM {$wpdb->posts} WHERE ID = %d", $_REQUEST['post_parent']));
			if(!current_user_can('edit_others_posts') && !$wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE ID = %d AND post_author = %d", $_REQUEST['post_parent'], get_current_user_id()))){
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
					$post = get_post($post_id);
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
			if(current_user_can('edit_others_posts') || $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE ID = %d AND post_author = %d", $_REQUEST['post_id'], get_current_user_id()))){
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
			$post = get_post($_REQUEST['post_id']);
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
	 * Do ajax mail sending
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb 
	 */
	public function contact_participants(){
		global $lwp, $wpdb;
		header('Content-Type: application/json; charset=utf-8');
		$json = array(
			'success' => false,
			'message' => array(),
			'current' => 0,
			'total' => 0
		);
		//Check nonce
		if(isset($_REQUEST['_wpnonce'], $_REQUEST['event_id'], $_REQUEST['from'], $_REQUEST['subject'], $_REQUEST['body'], $_REQUEST['to'], $_REQUEST['current'], $_REQUEST['total']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'lwp_contact_participants_'.get_current_user_id())){
			//Get Event and check nonce
			$event = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE ID = %d", $_REQUEST['event_id']));
			if($event && false !== array_search($event->post_type, $this->post_types) && (current_user_can('edit_others_posts') || get_current_user_id() == $event->post_author)){
				//Check from and permisson
				if(false !== array_search($_REQUEST['from'], array('admin', 'you', 'author')) && (current_user_can('edit_others_posts') || $_REQUEST['from'] != 'author')){
					switch($_REQUEST['from']){
						case 'you':
							$user = get_userdata(get_current_user_id());
							$from = $user->display_name.' <'.$user->user_email.'>';
							break;
						case 'author':
							$user = get_userdata($event->post_author);
							$from = $user->display_name.' <'.$user->user_email.'>';
							break;
						default:
							$from = get_bloginfo('name').' <'.get_option('admin_email').'>';
							break;
					}
					if(empty($_REQUEST['subject'])){
						$json['message'][] = $this->_('Mail Subject is empty.');
					}
					if(empty($_REQUEST['body'])){
						$json['message'][] = $this->_('Mail body is empty.');
					}
					//By recipients, change where clause
					$ticket_id = preg_replace("/[^0-9]/", "", $_REQUEST['to']);
					$where = array(
						$wpdb->prepare("p.post_parent = %d", $event->ID),
						$wpdb->prepare("p.post_type = %s", $lwp->event->post_type)
					);
					if($_REQUEST['to'] == 'event_success'){
						$where[] = $wpdb->prepare("t.status = %s", LWP_Payment_Status::SUCCESS);
					}elseif($_REQUEST['to'] == 'event_waiting'){
						$where[] = $wpdb->prepare("t.status = %s", LWP_Payment_Status::WAITING_CANCELLATION);
					}elseif(preg_match("/^ticket_participants_/", $_REQUEST['to'])){
						$where[] = $wpdb->prepare("t.status = %s", LWP_Payment_Status::SUCCESS);
						$where[] = $wpdb->prepare("t.book_id = %d", $ticket_id);
					}elseif(preg_match("/^ticket_waiting_/", $_REQUEST['to'])){
						$where[] = $wpdb->prepare("t.status = %s", LWP_Payment_Status::WAITING_CANCELLATION);						
						$where[] = $wpdb->prepare("t.book_id = %d", $ticket_id);
					}else{
						$json['message'][] = $this->_('Recipients not set.');
					}
					if(empty($json['message'])){
						//Now start sending email.
						$current = intval($_REQUEST['current']);
						$total = intval($_REQUEST['total']);
						if($current == 0 && $total == 0){
							$sql = <<<EOS
								SELECT COUNT(DISTINCT u.ID)
								FROM {$wpdb->users} AS u
								INNER JOIN {$lwp->transaction} AS t
								ON u.ID = t.user_id
								INNER JOIN {$wpdb->posts} AS p
								ON t.book_id = p.ID
EOS;
							$total = (int)$wpdb->get_var($sql.' WHERE '.implode(" AND ", $where));
							if($total > 0){
								$json['success'] = true;
							}else{
								$json['message'] = $this->_('No recipients match your criteria.');
							}
						}else{
							//Fisrt, get all participants email.
							$sql = <<<EOS
								SELECT SQL_CALC_FOUND_ROWS 
									DISTINCT u.ID, u.user_email, u.display_name
								FROM {$wpdb->users} AS u
								INNER JOIN {$lwp->transaction} AS t
								ON u.ID = t.user_id
								INNER JOIN {$wpdb->posts} AS p
								ON t.book_id = p.ID
EOS;
							$offset = $current;
							$limit = apply_filters('lwp_contact_mail_amount', 20);
							$limit_clause = sprintf(' LIMIT %d, %d', $offset, $limit);
							$users = $wpdb->get_results($sql.' WHERE '.implode(' AND ', $where).$limit_clause);
							if(empty($users)){
								$json['message'][] = $this->_('Participants not found.');
							}else{
								//Let's send.
								set_time_limit(0);
								$body = (string)$_REQUEST['body'];
								$body = str_replace('%ticket_url%', lwp_ticket_url($event), $body)."\r\n".$this->get_signature();
								$headers = "From: {$from}\r\n";
								foreach($users as $user){
									$replaced_body = str_replace('%code%', $this->generate_token($event->ID, $user->ID), $body);
									$replaced_body = str_replace('%user_email%', $user->user_email, $replaced_body);
									$replaced_body = str_replace('%display_name%', $user->display_name, $replaced_body);
									wp_mail($user->user_email, (string)$_REQUEST['subject'], $replaced_body, $headers);
									$current++;
								}
								$json['success'] = true;
								if($current >= $total){
									$json['message'][] = sprintf($this->_('Finish sending to %1$d users.'), $current);
								}
							}
						}
						$json['current'] = $current;
						$json['total'] = $total;
					}
				}else{
					$json['message'][] = $this->_('You can send email only from Admin email or yours.');
				}
			}else{
				$json['message'][] = $this->_('Cannot send email. You do not have permission or event not found.');
			}
		}else{
			$json['message'][] = $this->_('You have no capability to contact participants');
		}
		echo json_encode($json);
		die();
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
	
	/**
	 * Output CSV
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp 
	 */
	public function output_csv(){
		global $wpdb, $lwp;
		//Check nonce
		if(!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'lwp_event_csv_output')){
			status_header(403);
			die();
		}
		//Get Event
		if(!isset($_REQUEST['event_id']) || !($event = get_post($_REQUEST['event_id'])) || false === array_search($event->post_type, $this->post_types)){
			status_header(404);
			$this->e('Event not found.');
			die();
		}
		//Check permission
		if(!current_user_can('edit_others_posts') && !$wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE ID = %d AND post_author = %d", $_REQUEST['event_id'], get_current_user_id()))){
			status_header(403);
			$this->e('You have no permission.');
			die();
		}
		//Create Query
		$sql = <<<EOS
			SELECT DISTINCT
				t.*, p.post_title, u.user_email, u.display_name
			FROM {$lwp->transaction} AS t
			INNER JOIN {$wpdb->posts} AS p
			ON t.book_id = p.ID
			LEFT JOIN {$wpdb->users} AS u
			ON t.user_id = u.ID
EOS;
		//Detect ticket to retrieve
		if(isset($_REQUEST['ticket']) && is_numeric($_REQUEST['ticket'])){
			$wheres = array($wpdb->prepare("p.ID = %d", $_REQUEST['ticket']));
		}else{
			$wheres = array($wpdb->prepare("p.post_parent = %d", $event->ID));
		}
		//Detct ticket status
		if(isset($_REQUEST['status']) && $_REQUEST['status'] != 'all'){
			$wheres[] = $wpdb->prepare("t.status = %s", $_REQUEST['status']);
		}
		//Limit by date
		foreach(array('from' => '>=', 'to' => '<=') as $key => $operand){
			if(isset($_REQUEST[$key]) && preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $_REQUEST[$key])){
				$time = ($key == 'from') ? '00:00:00' : '23:59:59';
				$wheres[] = $wpdb->prepare("t.updated {$operand} %s", $_REQUEST[$key].' '.$time);
			}
		}
		$sql .= ' WHERE '.implode(' AND ', $wheres);
		//Order by
		$sql .= ' ORDER BY CAST(t.updated AS DATE) DESC, u.ID ASC';
		//Let's get results
		$results = $wpdb->get_results($sql);
		//Check result
		if(empty($results)){
			status_header(404);
			$this->e('No ticket match your qriteria.: '.$wpdb->last_query);
			die();
		}
		//Start output csv
		header('Content-Type: application/x-csv');
		header("Content-Disposition: attachment; filename=".rawurlencode($event->post_title).".csv");
		global $is_IE;
		if($is_IE){
			header("Cache-Control: public");
			header("Pragma:");
		}
		$out = fopen('php://output', 'w');
		$first_row = apply_filters('lwp_output_csv_header', array(
			$this->_('Updated'),
			$this->_('Code'),
			$this->_('User Name'),
			$this->_('Email'),
			$this->_('Ticket Name'),
			$this->_('Price'),
			$this->_('Quantity'),
			$this->_('Consumed'),
			$this->_('Transaction Status')
		));
		mb_convert_variables('sjis-win', 'utf-8', $first_row);
		fputcsv($out, $first_row);
		set_time_limit(0);
		foreach($results as $result){
			$row = apply_filters('lwp_output_csv_row', array(
				$result->updated,
				$this->generate_token($event->ID, $result->user_id), //Token
				($result->display_name ? $result->display_name : $this->_('Deleted User')),
				($result->user_email ? $result->user_email : '-'),
				$result->post_title,
				$result->price,
				$result->num,
				$result->consumed,
				$this->_($result->status)
			), $result);
			mb_convert_variables('sjis-win', 'utf-8', $row);
			fputcsv($out, $row);
		}
		fclose($out);
		die();
	}
	
	/**
	 * Send email on transaction Updated
	 * @param int $transaction_id 
	 * @return boolean
	 */
	public function send_email($transaction_id){
		global $lwp, $wpdb;
		$sql = <<<EOS
			SELECT t.*, p.post_parent, p.post_title FROM {$lwp->transaction} AS t
			INNER JOIN {$wpdb->posts} AS p
			ON t.book_id = p.ID
			WHERE t.ID = %d AND p.post_type = %s AND t.status = %s
EOS;
		$transaction = $wpdb->get_row($wpdb->prepare($sql, $transaction_id, $this->post_type, LWP_Payment_Status::SUCCESS));
		if(!$transaction){
			return false;
		}else{
			return $this->notify($transaction);
		}
	}
	
	/**
	 * Notify user about event transaction
	 * @param object $transaction Must be valid transaction
	 * @return boolean
	 */
	public function notify($transaction){
		$user = get_userdata($transaction->user_id);
		$body = $this->get_mail_body($transaction)."\r\n".$this->get_signature();
		$to = $user->user_email;
		$from = get_bloginfo('name')." <".get_option('admin_email').">";
		$subect = get_bloginfo('name').' : '.$this->_('Thank you for participating');
		$args = apply_filters('lwp_ticket_complete_mail', array(
			'to' => $to,
			'from' => $from,
			'subject' => $subect,
			'body' => $body
		));
		return wp_mail($args['to'], $args['subject'], $args['body'], 'FROM: '.$from."\r\n");
	}
	
	/**
	 * Returns mail body. if transaction set, parse it.
	 * @global $wpdb;
	 * @param object $transaction
	 * @return string
	 */
	public function get_mail_body($transaction = null){
		if(is_null($transaction)){
			return $this->_mail_body;
		}else{
			global $wpdb;
			$body = $this->_mail_body;
			$user = get_userdata($transaction->user_id);
			$event = get_post($wpdb->get_var($wpdb->prepare("SELECT post_parent FROM {$wpdb->posts} WHERE ID = %d", $transaction->book_id)));
			foreach($this->get_place_holders() as $key => $desc){
				switch($key){
					case 'user_name':
						$repl = $user->display_name;
						break;
					case 'user_email':
						$repl = $user->user_email;
						break;
					case 'event_name':
						$repl = apply_filters('the_title', $event->post_title);
						break;
					case 'event_url':
						$repl = get_permalink($event->ID);
						break;
					case 'ticket_name':
						$repl = get_the_title($transaction->book_id);
						break;
					case 'ticket_url':
						$repl = lwp_ticket_url($event);
						break;
					case 'code':
						$repl = $this->generate_token($event->ID, $user->ID);
						break;
					default:
						$repl = false;
						break;
				}
				if($repl){
					$body = str_replace("%{$key}%", $repl, $body);
				}
			}
			return $body;
		}
	}
	
	/**
	 * Returns place holders
	 * @return array 
	 */
	public function get_place_holders(){
		if(empty($this->_place_holders)){
			$this->_place_holders = array(
				'user_name' => $this->_('Display name of user'),
				'user_email' => $this->_('User email'),
				'event_name' => $this->_('Event name'),
				'event_url' => $this->_('Event\'s permalink'),
				'ticket_name' => $this->_('Ticket name'),
				'ticket_url' => $this->_('Ticket list page url'),
				'code' => $this->_('Code to identify user')
			);
		}
		return $this->_place_holders;
	}
	
	/**
	 * Returns if event has cancel list
	 * @global wpdb $wpdb
	 * @param int $post_id
	 * @return boolean
	 */
	public function has_cancel_list($post_id){
		global $wpdb;
		if($wpdb->get_var($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s", $post_id, $this->meta_cancel_list))){
			return (boolean)get_post_meta($post_id, $this->meta_cancel_list, TRUE);
		}else{
			return $this->cancel_list;
		}
	}
	
	/**
	 * Generate hash value for ticket cancellation
	 * @param int $user_id
	 * @param int $ticket_id
	 */
	public function generate_waiting_list_hash($user_id, $ticket_id){
		return md5(uniqid($user_id.'-'.$ticket_id));
	}
	
	/**
	 * Show form if autoload
	 * @global Literally_WordPress $lwp
	 * @param string $content
	 * @return string
	 */
	public function show_form($content){
		global $lwp;
		if($lwp->needs_auto_layout() && false !== array_search(get_post_type(), $this->post_types)){
			$additional_table = apply_filters('lwp_event_condition_table', '', get_the_ID());
			$event_table = <<<EOS
<caption>%s</caption>
<tbody>
<tr>
<th>%s</th>
<td>%s</td>
</tr>
<tr>
<th>%s</th>
<td>%s</td>
</tr>
<tr>
<th>%s</th>
<td>%s</td>
</tr>
%s
</tbody>
EOS;
			$format = get_option('date_format').get_option('time_format');
			$event_ends = lwp_event_ends('U');
			$event_ended = (!$event_ends || current_time('timestamp') < $event_ends) ? '' : '<span class="outdated">'.$this->_('Already ended.').'</span>';
			$outdated = (lwp_is_event_available() ? '' : '<span class="outdated">'.$this->_('Expired').'</span>');
			$table = sprintf($event_table, sprintf($this->_('%s\'s Detail'), get_the_title()),
					$this->_('Starts at'), lwp_event_starts($format).$event_ended,
					$this->_('Ends at'), date_i18n($format, $event_ends), 
					$this->_('Selling Limit'), sprintf($this->_('Untill %s'), lwp_selling_limit()).$outdated,
					$additional_table);
			$content .= '<table class="lwp-event-condition">'.$table.'</table>';
			ob_start();
			lwp_list_tickets();
			lwp_list_cancel_condition();
			$ticket_list = ob_get_contents();
			ob_end_clean();
			$content .= $ticket_list;
		}
		return $content;
	}
}
