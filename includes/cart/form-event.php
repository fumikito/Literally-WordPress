<?php
/**
 * Abstract for event endpoint
 * 
 * This class handles all event ticket.
 * Event has different methods like ticket list, cancellation, etc.
 * 
 * To handle request like that, this class has handle_* methods.
 * All methods must be proteced.
 * 
 * @since 0.9.3.1
 * 
 */
abstract class LWP_Form_Event extends LWP_Form_Backend{
	
	
	/**
	 * Show list of tickets to cancel
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param boolean $is_sandbox 
	 */
	protected function handle_ticket_cancel($is_sandbox = false){
		global $lwp, $wpdb;
		//First of all, user must be logged in.
		$this->kill_anonymous_user();
		if($is_sandbox){
			$event = $this->get_random_event();
			$this->show_form('cancel-ticket', array(
				'tickets' => array($this->get_random_ticket()),
				'event' => $event->ID,
				'event_type' => get_post_type_object($event->post_type)->labels->name,
				'limit' => date(get_option('date_format')),
				'ratio' => '80%',
				'total' => 2,
				'current' => 1
			));
		}
		//Get Event ID and get ticket list
		$event_id = isset($_REQUEST['lwp-event']) ? intval($_REQUEST['lwp-event']) : false;
		//If event dosen't exist, stop processing.
		if(!$event_id || !$wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE ID = %d AND post_status = 'publish'", $event_id))){
			$this->kill($this->_('Sorry, but you specified unexistant event.'), 404);
		}
		//Check if currently cancelable
		$limit = $lwp->event->get_current_cancel_condition($event_id);
		$cancel_limit_time = date_i18n(get_option('date_format'), lwp_selling_limit('U', $event_id) - (60 * 60 * 24 * $limit['days']));
		if(!$limit){
			$message = apply_filters('lwp_event_invalid_cancel_request', sprintf($this->_('Sorry, but cancel limit %s is outdated and you cannot cancel.'), $cancel_limit_time), $event_id, get_current_user_id());
			$this->kill($message, 410);
		}
		//Get cancelable ticket
		$tickets = $lwp->event->get_cancelable_tickets(get_current_user_id(), $event_id);
		if(empty($tickets)){
			$this->kill($this->_('Sorry, but you have no ticket to cancel.'), 404);
		}
		//Check condition
		if(preg_match("/%$/", $limit['ratio'])){
			$ratio = $limit['ratio'];
		}elseif(preg_match("/^-[0-9]+$/", $limit['ratio'])){
			$ratio = sprintf($this->_('%s charged.'), preg_replace("/[^0-9]/", '', $limit['ratio']).' '.lwp_currency_code());
		}else{
			$ratio = preg_replace("/[^0-9]/", '', $limit['ratio']).' '.lwp_currency_code();
		}
		//Show Form
		$this->show_form('cancel-ticket', array(
			'tickets' => $tickets,
			'event' => $event_id,
			'event_type' => get_post_type_object($wpdb->get_var($wpdb->prepare("SELECT post_type FROM {$wpdb->posts} WHERE ID = %d", $event_id)))->labels->name,
			'limit' => $cancel_limit_time,
			'ratio' => $ratio,
			'total' => 2,
			'current' => 1
		));
		
	}
	
	
	
	
	/**
	 * Cancel ticket
	 * @param type $is_sandbox 
	 */
	protected function handle_ticket_cancel_complete($is_sandbox = false){
		global $wpdb, $lwp;
		//First of all, user must be logged in
		$this->kill_anonymous_user();
		if($is_sandbox){
			$event = $this->get_random_event();
			$item_name = $this->get_item_name($event);
			$message = apply_filters('lwp_refund_message', 
					sprintf($this->_('Refund request for <strong>%1$s x %2$d</strong> has been accepted. Please wait for our refund process finished.'),
							$item_name,
							2),
					1, LWP_Payment_Status::REFUND_REQUESTING, $item_name, 2);
			$this->show_form('cancel-ticket-success', array(
				'link' => get_permalink($event->ID),
				'event' => get_the_title($event->ID),
				'message' => $message
			));
		}
		//Check nonce
		if(!isset($_REQUEST['_wpnonce'], $_REQUEST['ticket_id']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'lwp_ticket_cancel')){
			$this->kill($this->_('Sorry, but You might make unexpected action.').' '.$this->_('Cannot pass security check.'), 400);
		}
		//Get ticket id to cancel
		$ticket_id = (int) $_REQUEST['ticket_id'];
		//Get transaction
		$sql = <<<EOS
			SELECT * FROM {$lwp->transaction}
			WHERE ID = %d AND user_id = %d AND ( (status = %s) OR (method = %s AND status = %s))
EOS;
		$transaction = $wpdb->get_row($wpdb->prepare($sql, $ticket_id, get_current_user_id(), LWP_Payment_Status::SUCCESS, LWP_Payment_Methods::SOFTBANK_CC, LWP_Payment_Status::AUTH));
		//Check if cancelable transaction exists
		$event_id = $wpdb->get_var($wpdb->prepare("SELECT post_parent FROM {$wpdb->posts} WHERE ID = %d", $transaction->book_id));
		$current_condition = $lwp->event->get_current_cancel_condition($event_id);
		if(!$transaction || empty($current_condition)){
			$this->kill($this->_('Sorry, but the tikcet id you specified is not cancelable.'), 400);
		}
		//Get refund price
		$refund_price = lwp_ticket_refund_price($transaction);
		//Now, let's start refund action
		$status = false;
		if($refund_price == 0){
			$status = LWP_Payment_Status::REFUND;
		}else{
			$status = LWP_Payment_Status::REFUND_REQUESTING;
			switch($transaction->method){
				case LWP_Payment_Methods::PAYPAL:
					if(PayPal_Statics::is_refundable($transaction->updated) && PayPal_Statics::do_refund($transaction->transaction_id, $refund_price)){
						//Do paypal refunding. In case of refund 0, just change status
						$status = LWP_Payment_Status::REFUND;
					}
					break;
				case LWP_Payment_Methods::SOFTBANK_CC:
					switch($transaction->status){
						case LWP_Payment_Status::SUCCESS:
							if(($refund_price == $transaction->price) && $lwp->softbank->cancel_credit_transaction($transaction->ID)){
								//Total Refund, do refund
								$status = LWP_Payment_Status::REFUND;
							}
							break;
						case LWP_Payment_Status::AUTH:
							if($lwp->softbank->capture_authorized_transaction($transaction->ID,  ($transaction->price - $refund_price))){
								$status = LWP_Payment_Status::REFUND;
							}
							break;
					}
					break;
			}
		}
		//Update transaction status
		$wpdb->update(
			$lwp->transaction,
			array(
				'status' => $status,
				'refund' => $refund_price,
				'updated' => gmdate('Y-m-d H:i:s')
			),
			array('ID' => $transaction->ID),
			array('%s', '%d', '%s'),
			array('%d')
		);
		do_action('lwp_update_transaction', $transaction->ID);
		$item_name = $this->get_item_name($transaction->book_id);
		$message = (false !== array_search($status , array(LWP_Payment_Status::REFUND)))
					? sprintf($this->_('You have successfully canceled <strong>%1$s x %2$d</strong>.'), $item_name, $transaction->num)
					: sprintf($this->_('Refund request for <strong>%1$s x %2$d</strong> has been accepted. Please wait for our refund process finished.'), $item_name, $transaction->num);
		
		//Show Form
		$this->show_form('cancel-ticket-success', array(
			'link' => get_permalink($event_id),
			'event' => get_the_title($event_id),
			'message' => apply_filters('lwp_refund_message', $message, $transaction, $status, $item_name, $transaction->num)
		));
	}
	
	
	
	
	/**
	 * Shows ticket list user bought
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param boolean $is_sandbox 
	 */
	protected function handle_ticket_list($is_sandbox = false){
		global $lwp, $wpdb;
		//First of all, user must be logged in
		if(!is_user_logged_in()){
			auth_redirect();
		}
		$headers = apply_filters('lwp_ticket_list_headers', array(
			'name' => $this->_('Ticket Name'),
			'date' => $this->_('Bought'),
			'price' => $this->_('Price'),
			'quantity' => $this->_('Quantity'),
			'consumed' => $this->_('Consumed')
		));
		if($is_sandbox){
			$event = $this->get_random_event();
			$check_url = lwp_ticket_check_url(get_current_user_id(), $event);
			$this->show_form('event-tickets', array(
				'title' => $event->post_title,
				'limit' => date('Y-m-d', current_time('timestamp')),
				'link' => get_permalink($event->ID),
				'headers' => $headers,
				'post_type' => get_post_type_object($event->post_type)->labels->name,
				'token' => $lwp->event->generate_token($event->ID, get_current_user_id()),
				'tickets' => array($this->get_random_ticket()),
				'check_url' => $check_url,
				'qr_src' => $lwp->event->get_qrcode($check_url, 200),
				'footer_note' => $this->_('A footer note which can be set on each post will be displayed here.')
			));
		}
		//Chcek if event id is set
		if(
			!isset($_GET['lwp-event'])
				||
			!($event = get_post($_GET['lwp-event']) )
				||
			(false === array_search($event->post_type, $lwp->event->post_types))
		){
			$this->kill($this->_('Sorry, but no event is specified.'), 404);
		}
		$event_type = get_post_type_object($event->post_type);
		//Get tickets
		$sql = <<<EOS
			SELECT t.*, p.post_title, p.post_parent FROM {$lwp->transaction} AS t
			INNER JOIN {$wpdb->posts} AS p
			ON t.book_id = p.ID
			WHERE p.post_parent = %d AND t.user_id = %d AND t.status = %s
			ORDER BY t.updated DESC
EOS;
		$tickets = $wpdb->get_results($wpdb->prepare($sql, $event->ID, get_current_user_id(), LWP_Payment_Status::SUCCESS));
		//Check if ticket found.
		if(empty($tickets)){
			$this->kill($this->_('Sorry, but you have no tikcet to display.'), 404);
		}
		$check_url = lwp_ticket_check_url(get_current_user_id(), $event);
		$this->show_form('event-tickets', array(
			'title' => $event->post_title,
			'limit' => get_post_meta($event->ID, $lwp->event->meta_selling_limit, true),
			'link' => $this->strip_ssl(get_permalink($event->ID)),
			'headers' => $headers,
			'post_type' => $event_type->labels->name,
			'token' => $lwp->event->generate_token($event->ID, get_current_user_id()),
			'tickets' => $tickets,
			'check_url' => $check_url,
			'qr_src' => $lwp->event->get_qrcode($check_url, 200),
			'footer_note' => $lwp->event->get_footer_note($event->ID)
		));
	}
	
	
	
	
	/**
	 * Show form to edit ticket status.
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param boolean $is_sandbox 
	 */
	protected function handle_ticket_consume($is_sandbox = false){
		global $lwp, $wpdb;
		//First of all, user must be logged in
		if(!is_user_logged_in()){
			auth_redirect();
		}
		if($is_sandbox){
			$event = $this->get_random_event();
			$this->show_form('event-tickets-consume', array(
				'updated' => true,
				'action' => lwp_ticket_check_url($user->ID, $event),
				'tickets' => array($this->get_random_ticket()),
				'user' => get_userdata(get_current_user_id()),
				'link' => get_permalink($event->ID),
				'title' => apply_filters('the_title', $event->post_title),
				'post_type' => get_post_type_object($event->post_type)->labels->name
			));
		}
		//Get event object
		$event = isset($_GET['lwp-event']) ? get_post($_GET['lwp-event']) : false;
		if(!$event){
			$this->kill($this->_('Sorry, but no event is specified.'), 404);
		}
		//Get user
		$user_hash = isset($_GET['u']) ? $_GET['u'] : '';
		$user = get_user_by_email(base64_decode($user_hash));
		if(!$user){
			$this->kill($this->_('Sorry, but specified user is not found.'), 404);
		}
		//Check if current user has capability
		if(!current_user_can('edit_others_posts') && get_current_user_id() != $event->post_author){
			$this->kill($this->_('Sorry, but you have no capability to consume ticket.'), 403);
		}
		//if nonce is ok, update
		if(isset($_POST['_wpnonce'], $_POST['ticket']) && is_array($_POST['ticket']) && wp_verify_nonce($_POST['_wpnonce'], 'lwp_ticket_consume_'.get_current_user_id())){
			foreach($_POST['ticket'] as $ticket_id => $consumed){
				$consumed = min($wpdb->get_var($wpdb->prepare("SELECT num FROM {$lwp->transaction} WHERE ID = %d", $ticket_id)), absint($consumed));
				$wpdb->update(
					$lwp->transaction,
					array('consumed' => $consumed),
					array('ID' => $ticket_id),
					array('%d'),
					array('%d')
				);
			}
			$updated = true;
		}else{
			$updated = false;
		}
		//Now let's get tickets
		$sql = <<<EOS
			SELECT t.*, p.post_title FROM {$lwp->transaction} AS t
			INNER JOIN {$wpdb->posts} AS p
			ON t.book_id = p.ID
			WHERE p.post_parent = %d AND t.user_id = %d AND t.status = %s
EOS;
		$tickets = $wpdb->get_results($wpdb->prepare($sql, $event->ID, $user->ID, LWP_Payment_Status::SUCCESS));
		if(empty($tickets)){
			$this->kill(sprintf($this->_('Sorry, but %s has no ticket on this event.'), $user->display_name), 404);
		}
		//Show Form
		$this->show_form('event-tickets-consume', array(
			'updated' => $updated,
			'action' => lwp_ticket_check_url($user->ID, $event),
			'tickets' => $tickets,
			'user' => $user,
			'link' => get_permalink($event->ID),
			'title' => apply_filters('the_title', $event->post_title),
			'post_type' => get_post_type_object($event->post_type)->labels->name
		));
	}
	
	
	
	
	/**
	 * Displays form to find ticket owner
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param boolean $is_sandbox 
	 */
	protected function handle_ticket_owner($is_sandbox = false){
		global $lwp, $wpdb;
		//First of all, user must be logged in
		$this->kill_anonymous_user();
		if($is_sandbox){
			$event = $this->get_random_event();
			$this->show_form('event-user', array(
				'error' => false,
				'event_id' => $event->ID,
				'title' => apply_filters('the_title', $event->post_title),
				'post_type' => get_post_type_object($event->post_type)->labels->name,
				'link' => get_permalink($event->ID),
				'action' => lwp_endpoint('ticket-owner', array('event_id' => $event->ID)),
			));
		}
		$event_id = isset($_GET['event_id']) ? $_GET['event_id'] : 0;
		$event = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE ID = %d", $event_id));
		if(!$event || false === array_search($event->post_type, $lwp->event->post_types)){
			$this->kill($this->_('Sorry, but event is not found.'), 404);
		}
		//Check user capability
		if(!current_user_can('edit_others_posts') && get_current_user_id() != $event->post_author){
			$this->kill($this->_('Sorry, but you have no permission.'), 403);
		}
		//Check if Error occurs
		$error = false;
		if(isset($_REQUEST['_wpnonce'], $_REQUEST['code']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'lwp_ticket_owner_'.$event->ID)){
			$user_id = $lwp->event->parse_token($event->ID, $_REQUEST['code']);
			if($user_id){
				header('Location: '.lwp_ticket_check_url($user_id, $event));
				die();
			}else{
				$error = true;
			}
		}
		//Event is found. Show inter face 
		$this->show_form('event-user', array(
			'error' => $error,
			'event_id' => $event->ID,
			'title' => apply_filters('the_title', $event->post_title),
			'post_type' => get_post_type_object($event->post_type)->labels->name,
			'link' => get_permalink($event->ID),
			'action' => lwp_endpoint('ticket-owner', array('event_id' => $event_id)),
		));
	}
	
	
	
	
	/**
	 * Handle cancel waiting 
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param boolean $is_sandbox
	 */
	protected function handle_ticket_awaiting($is_sandbox = false){
		global $wpdb, $lwp;
		//First of all, user must be logged in
		if(!is_user_logged_in()){
			auth_redirect();
		}
		//Get random ticket
		if($is_sandbox){
			$ticket = $this->get_random_ticket();
			$this->show_form('event-tickets-awaiting', array(
				'ticket' => $this->_('Event Name').' '.$ticket->post_title,
				'link' => admin_url('themes.php?page=lwp-form-check'),
				'message' => apply_filters('lwp_event_awaiting_message', $lwp->event->awaiting_message),
				'deregister' => false
			));
		}
		//Get ticket
		$sql = "SELECT * FROM {$wpdb->posts} WHERE ID = %d AND post_type = %s AND post_status = 'publish'";
		if(!isset($_REQUEST['ticket_id']) || !($ticket = $wpdb->get_row($wpdb->prepare($sql, $_REQUEST['ticket_id'], $lwp->event->post_type)))){
			$this->kill($this->_('Specified ticket doesn\'t exist.'), 404, true);
		}
		$ticket_name = get_the_title($ticket->post_parent).' '.$ticket->post_title;
		//Check if ticket can be sold
		if(lwp_get_ticket_stock(false, $ticket) > 0){
			$this->kill(sprintf($this->_('%s has stock to sell.'), $ticket_name), 403, true);
		}
		//Check if tikcet can be buy
		if(!lwp_is_event_available($ticket->post_parent)){
			$this->kill(sprintf($this->_('Sorry, but selling limit of %s has been outdated.'), $ticket_name), 403, true);
		}
		//Check if ticket can be waited for canellation.
		if(!$lwp->event->has_cancel_list($ticket->ID)){
			$this->kill(sprintf($this->_('Sorry, but %s dosen\'t have waiting list.'), $ticket_name), 403, true);
		}
		//If user has already waiting
		if(lwp_is_user_waiting($ticket)){
			$this->kill(sprintf($this->_('You are already on waiting list of %s.'), $ticket_name), 403, true);
		}
		//Enqueue user to cancel list
		$insert = $wpdb->insert($lwp->transaction, array(
				"user_id" => get_current_user_id(),
				"book_id" => $ticket->ID,
				"price" => 0,
				"status" => LWP_Payment_Status::WAITING_CANCELLATION,
				"transaction_key" => $lwp->event->generate_waiting_list_hash(get_current_user_id(), $ticket->ID),
				"registered" => gmdate('Y-m-d H:i:s'),
				"updated" => gmdate('Y-m-d H:i:s')
			),array("%d", "%d", "%f", "%s", "%s", "%s", "%s"));
		if(!$insert){
			$this->kill($this->_('Sorry, but failed to save data. Please retry later.'), 500, true);
		}
		do_action('lwp_create_transaction', $wpdb->insert_id);
		//All green
		$this->show_form('event-tickets-awaiting', array(
			'ticket' => $ticket_name,
			'link' => get_permalink($ticket->post_parent),
			'message' => apply_filters('lwp_event_awaiting_message', $lwp->event->awaiting_message),
			'deregister' => false
		));
	}
	
	
	
	
	/**
	 * Handle action for deregister cancel list.
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param boolean $is_sandbox
	 */
	protected function handle_ticket_awaiting_deregister($is_sandbox = false){
		global $lwp, $wpdb;
		//First of all, user must be logged in
		$this->kill_anonymous_user(false);
		//Check nonce
		if(!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'lwp_deregistere_cancel_list_'.get_current_user_id())){
			$this->kill($this->_('Sorry, but you took a wrong action.'), 403);
		}
		//Get waiting transaction
		$sql = "SELECT * FROM {$wpdb->posts} WHERE ID = %d AND post_type = %s AND post_status = 'publish'";
		if(!isset($_REQUEST['ticket_id']) || !($ticket = $wpdb->get_row($wpdb->prepare($sql, $_REQUEST['ticket_id'], $lwp->event->post_type)))){
			$this->kill($this->_('Specified ticket doesn\'t exist.'), 404);
		}
		$ticket_name = get_the_title($ticket->post_parent).' '.$ticket->post_title;
		//Check if user has transaction
		$transaction = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$lwp->transaction} WHERE user_id = %d AND book_id = %d AND status = %s",
				get_current_user_id(), $ticket->ID, LWP_Payment_Status::WAITING_CANCELLATION));
		if(!$transaction){
			$this->kill(sprintf($this->_('Sorry, but you are not on cancel list of %s'), $ticket_name), 404, true);
		}
		//If event limit is exceeded, user can't change.
		if(!lwp_is_event_available($ticket->post_parent)){
			$this->kill(sprintf($this->_('Sorry, but %s is already outdated and you can\'t change satus.'), $ticket_name), 403, true);
		}
		//All gree, now status will be changed.
		$update = $wpdb->update($lwp->transaction, array(
			'status' => LWP_Payment_Status::QUIT_WAITNG_CANCELLATION,
			'updated' => gmdate('Y-m-d H:i:s')
		), array('ID' => $transaction->ID), array('%s', '%s'), array('%d'));
		if(!$update){
			$this->kill($this->_('Sorry, but failed to change transaction satatus. Please try again later.'), 500);
		}
		do_action('lwp_deregister_cancel_list', $transaction->ID, get_current_user_id(), $ticket_name);
		$this->show_form('event-tickets-awaiting', array(
			'link' => get_permalink($ticket->post_parent),
			'message' => apply_filters('lwp_deregister_event_awaiting_message', sprintf($this->_('You are now deregistered from the cancel list of <strong>%s</strong>.'), $ticket_name), $transaction->ID, $ticket, get_current_user_id()),
			'deregister' => true
		));
	}
	
	
	
	
	/**
	 * Contact to participants
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param boolean $is_sandbox 
	 */
	protected function handle_ticket_contact($is_sandbox = false){
		global $lwp, $wpdb;
		//First of all, user must be logged in
		$this->kill_anonymous_user();
		//Create mail options
		$current_user = get_userdata(get_current_user_id());
		$options = array(
			'admin' => sprintf($this->_('Admin email <%s>'), get_option('admin_email')),
			'you' => sprintf($this->_('Your email <%s>'), $current_user->user_email)
		);
		//Pass variables to JS
		$this->add_script_labels(array(
			'labelConfirm' => $this->_('Are you sure to send this mail? You can\'t cancel this action.'),
			'labelSending' => $this->_('Sending&hellip;'),
			'labelInvalidFrom' => $this->_('Please specify mail from.'),
			'labelInvalidSubject' => $this->_('Subject is empty.'),
			'labelInvalidBody' => $this->_('Mail body is empty.'),
			'labelInvalidTo' => $this->_('Recipients not set.'),
			'labelSent' => $this->_('Send')
		));
		//Do sandbox
		if($is_sandbox){
			$event = $this->get_random_event();
			if(!$event){
				$this->kill($this->_('Sorry, but event is not found.'), 404);
			}
			$this->show_form('event-contact', array(
				'participants' => 10,
				'post_type' => get_post_type_object($event->post_type)->labels->name,
				'event_id' => $event->ID,
				'title' => apply_filters('the_title', $event->post_title),
				'signature' => wpautop($lwp->event->get_signature()),
				'options' => $options,
				'loader' => '<img class="indicator" alt="Loading..." style="display:none;" width="16" height="16" src="'.$this->url.'assets/indicator-postbox.gif" />',
				'link' => admin_url('post.php?post='.$event->ID.'&action=edit')
			));
		}
		//Get event ID
		if(!isset($_GET['event_id']) || !($event = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE ID = %d", $_GET['event_id'])))){
			$this->kill($this->_('Sorry, but no event is specified.'), 404);
		}
		//Check user capability
		if(!current_user_can('edit_others_posts') && $event->post_author != get_current_user_id()){
			$this->kill($this->_('You do not have capability to contact participants.'), 403);
		}
		//Add Post owner
		if(current_user_can('edit_others_posts')){
			$post_author = get_userdata($event->post_author);
			$options['author'] = sprintf($this->_('Post author email <%s>'), $post_author->user_email);
		}
		//Apply filters
		$options = apply_filters('lwp_contact_from', $options);
		//Recipiets list
		$participants = lwp_participants_number($event);
		$waiting = lwp_participants_number($event, true);
		if($participants + $waiting < 1){
			$this->kill($this->_('There is no one to contact.'), 404, true);
		}
		$recipients = array(
			'event_success' => sprintf($this->_('All Participants(%s people)'), number_format($participants))
		);
		if($waiting > 0){
			$recipients['event_waiting'] = sprintf($this->_('All waitings(%s people)'), number_format($waiting));
		}
		//Get tickets user
		$sql = <<<EOS
			SELECT ID, post_title FROM {$wpdb->posts} WHERE post_parent = %d AND post_type = %s
EOS;
		$participant_sql = "SELECT count(DISTINCT user_id) FROM {$lwp->transaction} WHERE book_id = %d AND status = %s";
		foreach($wpdb->get_results($wpdb->prepare($sql, $event->ID, $lwp->event->post_type)) as $ticket){
			$users_count = $wpdb->get_var($wpdb->prepare($participant_sql, $ticket->ID, LWP_Payment_Status::SUCCESS));
			if($users_count < 1){
				continue;
			}
			$users = array(
				'ticket_participants_'.$ticket->ID => sprintf($this->_('Participants(%s people)'), number_format($users_count))
			);
			$ticket_waiting = $wpdb->get_var($wpdb->prepare($participant_sql, $ticket->ID, LWP_Payment_Status::WAITING_CANCELLATION));
			if($ticket_waiting > 0){
				$users['ticket_waiting_'.$ticket->ID] = sprintf($this->_('Waitings(%s people)'), number_format($ticket_waiting));
			}
			$recipients[$ticket->post_title] = $users;
		}
		$recipients = apply_filters('lwp_contact_recipients', $recipients);
		//Show form
		$this->show_form('event-contact', array(
			'participants' => lwp_participants_number($event),
			'post_type' => get_post_type_object($event->post_type)->labels->name,
			'event_id' => $event->ID,
			'title' => apply_filters('the_title', $event->post_title),
			'signature' => wpautop($lwp->event->get_signature()),
			'options' => $options,
			'recipients' => $recipients,
			'loader' => '<img class="indicator" alt="Loading..." style="display:none;" width="16" height="16" src="'.$this->url.'assets/indicator-postbox.gif" />',
			'link' => get_permalink($event->ID)
		));
	}
	
	
	
	
}