<?php
/**
 * Manage LWP's form action and display form
 *
 * @since 0.9.1
 */
class LWP_Form extends Literally_WordPress_Common{
	
	/**
	 * Localize script
	 * @var array 
	 */
	private $_LWP = array();
	
	
	/**
	 * Manage form action to lwp endpoint
	 * 
	 * @return void
	 */
	public function manage_actions(){
		//If action is set, call each method
		if($this->get_current_action() && is_front_page()){
			$action = 'handle_'.$this->make_hungalian($this->get_current_action());
			if(method_exists($this, $action)){
				$sandbox = (isset($_REQUEST['sandbox']) && $_REQUEST['sandbox']);
				if($sandbox && !current_user_can('edit_theme_options')){
					$this->kill('Sorry, but you have no permission.', 403);
				}
				$this->{$action}($sandbox);
			}else{
				$this->kill($this->_('Sorry, but You might make unexpected action.'), 400);
			}
		}
	}
	
	/**
	 * Handle request to price list. 
	 * @param boolean $is_sandbox
	 */
	private function handle_pricelist($is_sandbox = false){
		$this->handle_subscription($is_sandbox, false);
	}
	
	/**
	 * Handle request to subscription list 
	 * @param boolean $is_sandbox
	 * @param boolean $is_subscription
	 */
	private function handle_subscription($is_sandbox = false, $is_subscription = true){
		global $lwp;
		//Filter unexpected action
		if($is_subscription && !is_user_logged_in()){
			auth_redirect($_SERVER["REQUEST_URI"]);
		}elseif($is_subscription && $lwp->subscription->is_subscriber() && !$is_sandbox){
			$this->kill($this->_('You are already subscriber. You don\'t have to buy subscription.'), 409);
		}elseif(!$lwp->subscription->has_plans()){
			$this->kill($this->_('Sorry, but there is no subscription plan.'), 404);
		}
		//All green.
		//Let's show subsctiption lists.
		$parent_url = home_url();
		foreach($this->subscription->post_types as $post_type){
			if($post_type != 'post' && $post_type != 'page' && ($url = get_post_type_archive_link($post_type))){
				$parent_url = $url;
			}
		}
		$this->show_form('subscription', array(
			'subscriptions' => new WP_Query(array(
				'post_type' => $lwp->subscription->post_type,
				'post_status' => 'publish',
				'meta_key' => 'lwp_price',
				'meta_value_num' => 0,
				'meta_compare' => '>=',
				'orderby' => 'meta_value_num',
				'order' => 'asc'
			)),
			'archive' => $lwp->subscription->get_subscription_post_type_page(),
			'url' => $parent_url,
			'total' => $is_subscription ? 4 : 0,
			'current' => $is_subscription ? 1 : 0,
			'transaction' => $is_subscription
		));
	}
	
	/**
	 * Handle buy action
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param boolean $is_sandbox 
	 */
	private function handle_buy($is_sandbox = false){
		global $wpdb, $lwp;
		//First of all, user must be logged in.
		if(!is_user_logged_in()){
			auth_redirect ($_SERVER["REQUEST_URI"]);
			exit;
		}
		//If it's sandbox, just show form
		if($is_sandbox){
			//Get random post
			$book = $this->get_random_post();
			if($book){
				//Find random post, show form
				$this->show_form('selection', array(
					'post_id' => $book->ID,
					'price' => lwp_price($book->ID),
					'item' => $book->post_title,
					'current' => 2,
					'total' => 4
				));
			}else{
				//Post not found
				$this->kill($this->_('Mmm... Cannot find product. Please check if you have payable post.'), 404);
			}
		}
		//Let's start transaction!
		//Get book id
		$book_id = (isset($_GET['lwp-id'])) ? intval($_GET['lwp-id']) : 0;
		$post_types = $lwp->option['payable_post_types'];
		if($lwp->event->enabled){
			$post_types[] = $lwp->event->post_type;
		}
		if($lwp->subscription->enabled){
			$post_types[] = $lwp->subscription->post_type;
		}
		if(!($book = wp_get_single_post ($book_id)) || false === array_search($book->post_type, $post_types)){
			//If specified content doesn't exist, die.
			$this->kill($this->_("No content is specified."), 404);
		}
		//If tickett is specified, check selling limit
		if($book->post_type == $lwp->event->post_type){
			$selling_limit = get_post_meta($book->post_parent, $lwp->event->meta_selling_limit, true);
			if($selling_limit){
				//Selling limit is found, so check if it's oudated
				$limit = strtotime($selling_limit) + 60 * 60 * 24 - 1;
				$current = strtotime(gmdate('Y-m-d H:i:s'));
				if($limit < $current){
					$this->kill($this->_("Selling limit has been past. There is no ticket available."), 404);
				}
			}
		}
    //Let's do action hook to delegate transaction to other plugin
    do_action('lwp_before_transaction', $book);
		//All green, start transaction
		if(lwp_price($book_id) < 1){
			//Content is free
			if(lwp_original_price($book_id) > 0){
				//Original price is not free, temporally free.
				$wpdb->insert(
					$lwp->transaction,
					array(
						"user_id" => get_current_user_id(),
						"book_id" => $book_id,
						"price" => 0,
						"status" => LWP_Payment_Status::SUCCESS,
						"method" => LWP_Payment_Methods::CAMPAIGN,
						"registered" => gmdate('Y-m-d H:i:s'),
						"updated" => gmdate('Y-m-d H:i:s'),
						'expires' => lwp_expires_date($book_id)
					),
					array("%d", "%d", "%d", "%s", "%s", "%s", "%s", "%s")
				);
				//Execute hook.
				do_action('lwp_create_transaction', $wpdb->insert_id);
				do_action('lwp_update_transaction', $wpdb->insert_id);
				//Redirect to success page
				header("Location: ".  lwp_endpoint('success')."&lwp-id={$book_id}");
				exit;
			}else{
				//Item is not available.
				$this->kill($this->_("This itme is not on sale."), 403);
			}
		}else{
			//Current step
			$total = $book->post_type == $lwp->subscription->post_type ? 4 : 3;
			$current = $book->post_type == $lwp->subscription->post_type ? 2 : 1;
			//Start Transaction
      //If payment selection required or nonce isn't corret, show form.
			if(!$this->can_skip_payment_selection() && (!isset($_GET['_wpnonce'], $_GET['lwp-method']) || !wp_verify_nonce($_GET['_wpnonce'], 'lwp_buynow'))){
				//Select Payment Method and show form
				$this->show_form('selection', array(
					'post_id' => $book_id,
					'price' => lwp_price($book_id),
					'item' => $book->post_title,
					'current' => $current,
					'total' => $total
				));
			}else{
				//User selected payment method and start transaction
        $method = isset($_GET['lwp-method']) ? $_GET['lwp-method']: ($this->can_skip_payment_selection() ? 'cc': '' );
				switch($method){
					case 'transfer':
						if($lwp->option['transfer']){
							//Check if there is active transaction
							$sql = <<<EOS
								SELECT * FROM {$lwp->transaction}
								WHERE user_id = %d AND book_id = %d AND status = %s AND DATE_ADD(registered, INTERVAL %d DAY) > NOW()
EOS;
							$transaction = $wpdb->get_row($wpdb->prepare($sql, get_current_user_id(), $book_id, LWP_Payment_Status::START, $lwp->option['notification_limit']));
							if(!$transaction){
								//Register transaction with transfer
								$wpdb->insert(
									$lwp->transaction,
									array(
										"user_id" => get_current_user_id(),
										"book_id" => $book_id,
										"price" => lwp_price($book_id),
										"transaction_key" => sprintf("%08d", get_current_user_id()),
										"status" => LWP_Payment_Status::START,
										"method" => LWP_Payment_Methods::TRANSFER,
										"registered" => gmdate('Y-m-d H:i:s'),
										"updated" => gmdate('Y-m-d H:i:s')
									),
									array("%d", "%d", "%d", "%s", "%s", "%s", "%s", "%s")
								);
								//Execute hook
								do_action('lwp_create_transaction', $wpdb->insert_id);
								//Send Notification
								$transaction = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$lwp->transaction} WHERE ID = %d", $wpdb->insert_id));
								$notification_status = $lwp->notifier->notify($transaction, 'thanks');
							}else{
								$notification_status = 'sent';
							}
							if($wpdb->get_var($wpdb->prepare("SELECT post_type FROM {$wpdb->posts} WHERE ID = %d", $book_id)) == $this->subscription->post_type){
								$url = $this->subscription->get_subscription_archive();
							}elseif($book->post_type == $lwp->event->post_type){
								$url = get_permalink($book->post_parent);
							}else{
								$url = get_permalink($book_id);
							}
							//Show Form
							$this->show_form('transfer', array(
								'post_id' => $book_id,
								'transaction' => $transaction,
								'notification' => $notification_status,
								'link' => $url,
								'total' => $total,
								'current' => $current + 1
							));
						}else{
							$message = $this->_("Sorry, but we can't accept this payment method.");
						}
						break;
					case 'paypal':
					case 'cc':
						$billing = ($method == 'cc');
						if(!$lwp->start_transaction(get_current_user_id(), $book_id, $billing)){
							//Failed to create transaction
							$message = $this->_("Failed to make transaction.");
						}
						break;
					default:
						$message = $this->_("Wrong payment method is specified.");
						break;
				}
			}
		}
		//Here you are... Something is wrong. Just show message and die.
		$this->kill($message);
	}
	
	/**
	 * Handle Confirm action
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param boolean $is_sandbox 
	 */
	private function handle_confirm($is_sandbox = false){
		global $wpdb, $lwp;
		//First of all, user must be login
		$this->kill_anonymous_user();
		//If sandbox, just show form
		if($is_sandbox){
			$post = $this->get_random_post();
			if($post){
				//Post found, show form
				$this->show_form("return", array(
					"info" => array(
						'TOKEN' => '',
						'PAYERID' => '',
						'AMT' => lwp_price($post),
						'INVNUM' => '00000000',
						'EMAIL' => '',
						'LASTNAME' => 'Test',
						'FIRSTNAME' => 'User'
					),
					"transaction" => null,
					"post" => $post,
					'link' => get_permalink($post->ID),
					'total' => 4,
					'current' => 3
				));
			}else{
				//Post not found
				$this->kill($this->_('Mmm... Cannot find product. Please check if you have payable post.'), 404);
			}
		}
		if(!isset($_POST["_wpnonce"]) || !wp_verify_nonce($_POST["_wpnonce"], "lwp_confirm")){
			//Show confirm page
			$message = "";
			//確認画面
			$info = PayPal_Statics::get_transaction_info($_REQUEST['token']);
			if(!$info){
				$message = $this->_("Failed to connect with PayPal.");
			}
			$transaction = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$lwp->transaction} WHERE transaction_id = %s", $_REQUEST['token']));
			if(!$transaction){
				$message = $this->_("Failed to get the transactional information.");
			}
			$post = get_post($transaction->book_id);
			if(empty($message)){
				if($post->post_type == $lwp->subscription->post_type){
					$link = $lwp->subscription->get_subscription_archive();
				}else{
					$link = get_permalink($post->ID);
				}
				$this->show_form("return", array(
					"info" => $info,
					"transaction" => $transaction,
					"post" => $post,
					'link' => $link,
					'total' => ($post->post_type == $lwp->subscription->post_type) ? 4 : 3,
					'current' => ($post->post_type == $lwp->subscription->post_type) ? 3 : 2
				));
			}else{
				$this->kill($message);
			}
		}else{
			if(($transaction_id = PayPal_Statics::do_transaction($_POST))){
				//データを更新
				$post_id = $wpdb->get_var($wpdb->prepare("SELECT book_id FROM {$lwp->transaction} WHERE transaction_id = %s", $_POST["TOKEN"])); 
				$wpdb->update(
					$lwp->transaction,
					array(
						"status" => LWP_Payment_Status::SUCCESS,
						"transaction_key" => $_POST['INVNUM'],
						"transaction_id" => $transaction_id,
						"payer_mail" => $_POST["EMAIL"],
						'updated' => gmdate("Y-m-d H:i:s"),
						'expires' => lwp_expires_date($post_id)
					),
					array(
						"transaction_id" => $_POST["TOKEN"]
					),
					array("%s", "%s", "%s", "%s", "%s", "%s"),
					array("%s")
				);
				//Do action hook on transaction updated
				do_action('lwp_update_transaction', $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$lwp->transaction} WHERE transaction_id = %s", $transaction_id)));
				//サンキューページを表示する
				header("Location: ".  lwp_endpoint('success')."&lwp-id={$post_id}"); 
			}else{
				$this->kill($this->_("Transaction Failed to finish."));
			}
		}
	}

	
	/**
	 * Handle success page
	 * 
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param boolean $is_sandbox 
	 */
	private function handle_success($is_sandbox = false){
		global $lwp, $wpdb;
		//First of all, user must be login
		$this->kill_anonymous_user();
		if($is_sandbox){
			$this->show_form('success', array(
				'link' => get_bloginfo('url'),
				'total' => 4,
				'current' => 4
			));
		}
		//Change transaction status
		if(isset($_REQUEST['lwp-id'])){
			$post_type = $wpdb->get_var($wpdb->prepare("SELECT post_type FROM {$wpdb->posts} WHERE ID = %d", $_REQUEST['lwp-id']));
			if($post_type == $lwp->subscription->post_type){
				$url = $lwp->subscription->get_subscription_archive(true);
			}elseif($post_type == $lwp->event->post_type){
				$url = get_permalink($wpdb->get_var($wpdb->prepare("SELECT post_parent FROM {$wpdb->posts} WHERE ID = %d", $_REQUEST['lwp-id'])));
			}else{
				$url = get_permalink($_REQUEST['lwp-id']);
			}
		}else{
			$url = get_bloginfo('url');
		}
		$this->show_form("success", array(
			'link' => ($this->is_publicly_ssl() ? $url : $this->strip_ssl($url) ),
			'total' => ($post_type == $lwp->subscription->post_type) ? 4 : 3,
			'current' => ($post_type == $lwp->subscription->post_type) ? 4 : 3
		));
	}
	
	
	/**
	 * Handle cancel action
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param boolean $is_sandbox 
	 */
	private function handle_cancel($is_sandbox = false){
		global $wpdb, $lwp;
		//First of all, user must be logged in.
		$this->kill_anonymous_user();
		if($is_sandbox){
			$this->show_form("cancel", array(
				"post_id" => $this->get_random_post()->ID,
				'link' => get_bloginfo('url'),
				'total' => 3,
				'current' => 3
			));
		}
		//Get token from request
		$token = isset($_REQUEST['token']) ? $_REQUEST['token'] : null;
		if(!$token){
			$token = isset($_REQUEST['TOKEN']) ? $_REQUEST['TOKEN'] : null;
		}
		//Update transaction
		if($token){
			$wpdb->update(
				$this->transaction,
				array(
					"status" => LWP_Payment_Status::CANCEL,
					"updated" => gmdate("Y-m-d H:i:s")
				),
				array("transaction_id" => $token),
				array("%s", "%s"),
				array("%s")
			);
			$post_id = $is_sandbox 
				? 0
				:$wpdb->get_var($wpdb->prepare("SELECT book_id FROM {$lwp->transaction} WHERE transaction_id = %s", $token));
			$post = get_post($post_id);
			if($post->post_type == $lwp->subscription->post_type){
				$url = $lwp->subscription->get_subscription_archive();
			}elseif($post->post_type == $lwp->event->post_type){
				$url = get_permalink($post->post_parent);
			}else{
				$url = get_permalink($post->ID);
			}
		}elseif($lwp->option['mypage']){
			$url = get_permalink($lwp->option['mypage']);
		}else{
			$url = get_bloginfo('url');
		}
		$this->show_form("cancel", array(
			"post_id" => $post_id,
			'link' => $url,
			'total' => 3,
			'current' => 3
		));
	}
	
	/**
	 * Output file
	 * @global Literally_WordPress $lwp
	 * @param type $is_sandbox 
	 */
	private function handle_file($is_sandbox = false){
		global $lwp;
		$lwp->print_file($_REQUEST["lwp_file"], get_current_user_id());
	}
	
	/**
	 * Show list of tickets to cancel
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param boolean $is_sandbox 
	 */
	private function handle_ticket_cancel($is_sandbox = false){
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
			$this->kill(sprintf($this->_('Sorry, but cancel limit %s is outdated and you cannot cancel.'), $cancel_limit_time), 410);
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
	private function handle_ticket_cancel_complete($is_sandbox = false){
		global $wpdb, $lwp;
		//First of all, user must be logged in
		$this->kill_anonymous_user();
		if($is_sandbox){
			$event = $this->get_random_event();
			$this->show_form('cancel-ticket-success', array(
				'link' => get_permalink($event->ID),
				'event' => get_the_title($event->ID),
				'ticket' => $this->_('Deleted Ticket'),
				'transfer' => true
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
			WHERE ID = %d AND user_id = %d AND status = %s
EOS;
		$transaction = $wpdb->get_row($wpdb->prepare($sql, $ticket_id, get_current_user_id(), LWP_Payment_Status::SUCCESS));
		//Check if cancelable transaction exists
		$event_id = $wpdb->get_var($wpdb->prepare("SELECT post_parent FROM {$wpdb->posts} WHERE ID = %d", $transaction->book_id));
		$current_condition = $lwp->event->get_current_cancel_condition($event_id);
		if(!$transaction || empty($current_condition)){
			$this->kill($this->_('Sorry, but the tikcet id you specified is not cancelable.'), 400);
		}
		//Check if paypal refund is available
		if($transaction->method == LWP_Payment_Methods::PAYPAL && !PayPal_Statics::is_refundable($transaction->updated)){
			$this->kill(sprintf($this->_('Sorry, but paypal redunding is available only for 60 days. You made transaction at %1$s and it is %2$s today'), mysql2date(get_option('date_format'), $transaction->updated), date_i18n(get_option('date_format'))), 410);
		}
		//Now, let's start refund action
		$refund_price = lwp_ticket_refund_price($transaction);
		$status = false;
		if($refund_price == 0){
			$status = LWP_Payment_Status::REFUND;
		}else{
			if($transaction->method == LWP_Payment_Methods::PAYPAL){
				//Do paypal refunding. In case of refund 0, just change status
				if(PayPal_Statics::do_refund($transaction->transaction_id, $refund_price)){
					$status = LWP_Payment_Status::REFUND;
				}else{
					$this->kill(sprintf($this->_('Sorry, but PayPal denies refunding. Please contact to %1$s administrator.'), get_bloginfo('name')), 500);
				}
			}elseif($transaction->method == LWP_Payment_Methods::TRANSFER){
				$status = LWP_Payment_Status::REFUND_REQUESTING;
			}
		}
		//Update transaction status
		$wpdb->update(
			$lwp->transaction,
			array(
				'status' => $status,
				'updated' => gmdate('Y-m-d H:i:s')
			),
			array('ID' => $transaction->ID),
			array('%s', '%s'),
			array('%d')
		);
		//Show Form
		$this->show_form('cancel-ticket-success', array(
			'link' => get_permalink($event_id),
			'event' => get_the_title($event_id),
			'ticket' => get_the_title($transaction->book_id),
			'transfer' => $transaction->method == LWP_Payment_Methods::TRANSFER
		));
	}
	
	/**
	 * Shows ticket list user bought
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param boolean $is_sandbox 
	 */
	private function handle_ticket_list($is_sandbox = false){
		global $lwp, $wpdb;
		//First of all, user must be logged in
		if(!is_user_logged_in()){
			auth_redirect();
		}
		if($is_sandbox){
			$event = $this->get_random_event();
			$check_url = lwp_ticket_check_url(get_current_user_id(), $event);
			$this->show_form('event-tickets', array(
				'title' => $event->post_title,
				'limit' => date('Y-m-d'),
				'link' => get_permalink($event->ID),
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
			!($event = wp_get_single_post($_GET['lwp-event']))
				||
			(false === array_search($event->post_type, $lwp->event->post_types))
		){
			$this->kill($this->_('Sorry, but no event is specified.'), 404);
		}
		$event_type = get_post_type_object($event->post_type);
		//Get tickets
		$sql = <<<EOS
			SELECT t.*, p.post_title FROM {$lwp->transaction} AS t
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
			'link' => get_permalink($event->ID),
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
	private function handle_ticket_consume($is_sandbox = false){
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
		$event = isset($_GET['lwp-event']) ? wp_get_single_post($_GET['lwp-event']) : false;
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
		if(!user_can_edit_post(get_current_user_id(), $event->ID)){
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
	private function handle_ticket_owner($is_sandbox = false){
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
				'action' => lwp_endpoint('ticket-owner').'&event_id='.$event->ID,
			));
		}
		$event_id = isset($_GET['event_id']) ? $_GET['event_id'] : 0;
		$event = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE ID = %d", $event_id));
		if(!$event || false === array_search($event->post_type, $lwp->event->post_types)){
			$this->kill($this->_('Sorry, but event is not found.'), 404);
		}
		//Check user capability
		if(!user_can_edit_post(get_current_user_id(), $event->ID)){
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
			'action' => lwp_endpoint('ticket-owner').'&event_id='.$event->ID,
		));
	}
	
	/**
	 * Contact to participants
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param boolean $is_sandbox 
	 */
	private function handle_ticket_contact($is_sandbox = false){
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
		$this->_LWP = array(
			'labelConfirm' => $this->_('Are you sure to send this mail?'),
			'labelSending' => $this->_('Sending&hellip;'),
			'labelInvalidFrom' => $this->_('Please specify mail from.'),
			'labelInvalidSubject' => $this->_('Subject is empty.'),
			'labelInvalidBody' => $this->_('Mail body is empty'),
			'labelSent' => $this->_('Send')
		);
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
		if(!user_can_edit_post(get_current_user_id(), $event->ID)){
			$this->kill($this->_('You do not have capability to contact participants.'), 403);
		}
		//Add Post owner
		if(current_user_can('edit_others_posts')){
			$post_author = get_userdata($event->post_author);
			$options['author'] = sprintf($this->_('Post author email <%s>'), $post_author->user_email);
		}
		//Show form
		$this->show_form('event-contact', array(
			'participants' => lwp_participants_number($event),
			'post_type' => get_post_type_object($event->post_type)->labels->name,
			'event_id' => $event->ID,
			'title' => apply_filters('the_title', $event->post_title),
			'signature' => wpautop($lwp->event->get_signature()),
			'options' => $options,
			'loader' => '<img class="indicator" alt="Loading..." style="display:none;" width="16" height="16" src="'.$this->url.'assets/indicator-postbox.gif" />',
			'link' => admin_url('post.php?post='.$event->ID.'&action=edit')
		));
	}
	
	/**
	 * Stop processing transaction of not logged in user. 
	 */
	private function kill_anonymous_user($kill = true){
		if(!is_user_logged_in()){
			if($kill){
				$this->kill($this->_('You must be logged in to process transaction.'), 403);
			}else{
				auth_redirect();
			}
		}
	}
	
	/**
	 * Returns current form action 
	 * @return string
	 */
	private function get_current_action(){
		if(isset($_GET['lwp'])){
			return (string) $_GET['lwp'];
		}else{
			return null;
		}
	}
	
	/**
	 * フォームを出力する
	 * @since 0.9.1
	 * @global Literally_WordPress $lwp
	 * @param string $slug
	 * @param array $args
	 * @return void
	 */
	private function show_form($slug, $args = array()){
		$args['meta_title'] = $this->get_form_title($slug).' : '.get_bloginfo('name');
		$args = apply_filters('lwp_form_args', $args, $slug);
		extract($args);
		$slug = basename($slug);
		$filename = "paypal-{$slug}.php";
		//テーマテンプレートに存在するかどうか調べる
		if(file_exists(get_template_directory().DIRECTORY_SEPARATOR.$filename)){
			//テンプレートがあれば読み込む
			require_once get_template_directory().DIRECTORY_SEPARATOR.$filename;
		}else{
			//なければ自作
			$parent_directory = $this->dir.DIRECTORY_SEPARATOR."form-template".DIRECTORY_SEPARATOR;
			add_action('wp_enqueue_scripts', array($this, 'enqueue_form_scripts'));
			require_once $parent_directory."paypal-header.php";
			do_action('lwp_before_form', $slug, $args);
			require_once $parent_directory.$filename;
			do_action('lwp_after_form', $slug, $args);
			require_once $parent_directory."paypal-footer.php";
		}
		exit;
	}
	
  /**
   * Returns if payment slection can be skipped
   * @global Literally_WordPress $lwp 
   */
  private function can_skip_payment_selection(){
    global $lwp;
    return $lwp->option['skip_payment_selection'] && !($lwp->option['transfer']);
  }
  
	/**
	 * Do enqueue scripts 
	 */
	public function enqueue_form_scripts(){
		global $lwp;
		//Load CSS, JS
		$css = (file_exists(get_template_directory().DIRECTORY_SEPARATOR."lwp-form.css")) ? get_template_directory_uri()."/lwp-form.css" : $lwp->url."assets/lwp-form.css";
		$print_css = (file_exists(get_template_directory().DIRECTORY_SEPARATOR.'lwp-print.css')) ? get_template_directory_uri()."/lwp-print.css" : $lwp->url."assets/lwp-print.css";
		wp_enqueue_style("lwp-form", $css, array(), $lwp->version, 'screen');
		wp_enqueue_style("lwp-form-print", $print_css, array(), $lwp->version, 'print');
		wp_enqueue_script("lwp-form-helper", $this->url."assets/js/form-helper.js", array("jquery-form"), $lwp->version, true);
		if(!empty($this->_LWP)){
			wp_localize_script('lwp-form-helper', 'LWP', $this->_LWP);
		}
    //Do action hook for other plugins
    do_action('lwp_form_enqueue_scripts');
	}
	
	
	/**
	 * Returns is public page is SSL
	 * @return boolean 
	 */
	private function is_publicly_ssl(){
		return ((false !== strpos(get_option('home_url'), 'https')) || (false !== strpos(get_option('site_url'), 'https')));
	}
  
	/**
	 * Make url to http protocol
	 * @param string $url
	 * @return string 
	 */
	private function strip_ssl($url){
		return str_replace('https://', 'http://', $url);
	}
	
	/**
	 * Get post object as event
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @return object 
	 */
	private function get_random_event(){
		global $wpdb, $lwp;
		$post_types = implode(',', array_map(create_function('$row', 'return "\'".$row."\'"; '), $lwp->event->post_types));
		$event = $wpdb->get_row("SELECT * FROM {$wpdb->posts} WHERE post_type IN ({$post_types}) ORDER BY RAND()");
		if(!$event){
			$this->kill($this->_('Sorry, but event is not found.'), 404);
		}
		return $event;
	}
	
	/**
	 * Create pseudo ticket
	 * @global wpdb $wpdb
	 * @return \stdClass 
	 */
	private function get_random_ticket(){
		global $wpdb;
		$ticket = new stdClass();
		$ticket->post_title = $this->_('Dammy Ticket');
		$ticket->updated = date('Y-m-d H:i:s');
		$ticket->price = 1000;
		$ticket->ID = 100;
		$ticket->num = 1;
		$ticket->consumed = 0;
		return $ticket;
	}
	
	/**
	 * Returns random post if exists for sand box
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp 
	 * @return object
	 */
	private function get_random_post(){
		global $wpdb, $lwp;
		$post_types = $lwp->option['payable_post_types'];
		if($lwp->event->is_enabled()){
			$post_types[] = $lwp->event->post_type;
		}
		if($lwp->subscription->is_enabled()){
			$post_types[] = $lwp->subscription->post_type;
		}
		$post_types = implode(',', array_map(create_function('$a', 'return "\'".$a."\'";'), $post_types));
		$sql = <<<EOS
			SELECT p.* FROM {$wpdb->posts} AS p
			INNER JOIN {$wpdb->postmeta} AS pm
			ON p.ID = pm.post_id AND pm.meta_key = 'lwp_price'
			WHERE p.post_status IN ('draft', 'publish', 'future') AND p.post_type IN ({$post_types}) AND CAST(pm.meta_value AS signed) > 0
			ORDER BY RAND()
EOS;
		return $wpdb->get_row($sql);
	}
	
	/**
	 * Change method name to hungalian 
	 * @param string $method
	 * @return string 
	 */
	private function make_hungalian($method){
		return str_replace("-", "_", strtolower(trim($method)));
	}
	
	/**
	 * Returns handle name
	 */
	public function endpoints(){
		$methods = array();
		foreach(get_class_methods($this) as $method){
			if(0 === strpos($method, 'handle_')){
				$methods[] = str_replace('_', '-', str_replace('handle_', '', $method));
			}
		}
		return $methods;
	}
	
	/**
	 * Returns title of form template
	 * @param string $template_slug
	 * @return string 
	 */
	public function get_form_title($template_slug = ''){
		switch($template_slug){
			case 'selection':
				$meta_title = $this->_('Select Payment');
				break;
			case 'transfer':
				$meta_title = $this->_('Transfer Accepted');
				break;
			case 'return':
				$meta_title = $this->_('Payment Confirmation');
				break;
			case 'success':
				$meta_title = $this->_('Transaction Completed');
				break;
			case 'cancel':
				$meta_title = $this->_('Transaciton Canceled');
				break;
			case 'cancel-ticket':
				$meta_title = $this->_('Ticket Cancel');
				break;
			case 'cancel-ticket-success':
				$meta_title = $this->_('Ticket Canceled');
				break;
			case 'event-tickets':
				$meta_title = $this->_('Ticket List');
				break;
			case 'event-tickets-consume':
				$meta_title = $this->_('Ticket Status');
				break;
			case 'event-user':
				$meta_title = $this->_('Find User');
				break;
			case 'event-contact':
				$meta_title = $this->_('Contact to participants');
				break;
			case 'subscription':
				$meta_title = $this->_('Subscrition Plans');
				break;
			default:
				$meta_title = '';
				break;
		}
		return $meta_title;
	}
	
	/**
	 * Returns default template name for action
	 * @param string $action
	 * @return string 
	 */
	public function get_default_form_slug($action = ''){
		switch($action){
			case 'subscription':
			case 'success':
			case 'cancel':
				return $action;
				break;
			case 'pricelist':
				return 'subscription';
				break;
			case 'buy':
				return 'selection';
				break;
			case 'confirm':
				return 'return';
				break;
			case 'ticket-cancel':
				return 'cancel-ticket';
				break;
			case 'ticket-cancel-complete':
				return 'cancel-ticket-success';
				break;
			case 'ticket-list':
				return 'event-tickets';
				break;
			case 'ticket-consume':
				return 'event-tickets-consume';
				break;
			case 'ticket-owner':
				return 'event-user';
				break;
			case 'ticket-contact':
				return 'event-contact';
				break;
			default:
				return '';
				break;
		}
	}
	
	
	/**
	 * Returns form description
	 * @param string $action
	 * @return string 
	 */
	public function get_form_description($action = ''){
		switch($action){
			case 'pricelist':
				return $this->_('Displays subscription plans.');
				break;
			case 'subscription':
				return $this->_('Displays subscription plans.').' '.$this->_('User can select it and go to payment selection page.');
				break;
			case 'success':
				return $this->_('Display thank you message when transaction finished.').' '.$this->_('User will be soon redirected to original event page in 5 seconds.');
				break;
			case 'cancel':
				return $this->_('Display message when user cancels transaction.');
				break;
			case 'buy':
				return $this->_('Displays payment methods. You can skip this form if paypal is the only method available.').
					'<small>（<a href="'.admin_url('admin.php?page=lwp-setting').'">'.$this->_("More &gt;").')</a></small>';
				break;
			case 'confirm':
				return $this->_('Displays form to confirm transaction when user retruns from paypal web site.');
				break;
			case 'ticket-cancel':
				return $this->_('Show list of tickets which user have bought.').' '.$this->_('User can select ticket to cancel.').' '.$this->_('If user has no tickets, wp_die will be executed.');
				break;
			case 'ticket-cancel-complete':
				return $this->_('Displays message to tell user cancel is completed.').' '.$this->_('User will be soon redirected to original event page in 5 seconds.');
				break;
			case 'ticket-list':
				return $this->_('Show list of tickets which user have bought.').' '.$this->_('If user has no tickets, wp_die will be executed.');
				break;
			case 'ticket-consume':
				return $this->_('Displays list of tikcets owned by specified user. You can consume ticket with pulldown menu.');
				break;
			case 'ticket-owner':
				return $this->_('Search tikcet owner from code which have been generared by this plugin. This code is related to particular event.');
				break;
			case 'ticket-contact':
				return $this->_('Show mail form to send emails to event participants.');
				break;
			default:
				return '';
				break;
		}
	}
}