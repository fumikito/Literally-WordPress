<?php
/**
 * Manage LWP's form action and display form
 *
 * @since 0.9.1
 */
class LWP_Form extends Literally_WordPress_Common{
	
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
				$this->{$action}();
			}else{
				wp_die($this->_('Sorry, but You might make unexpected action.'), sprintf($this->_("Internal Server Error : %s"), get_bloginfo('name')), array('back_link' => true, 'response' => 500));
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
		}elseif($is_subscription && $lwp->subscription->is_subscriber()){
			wp_die($this->_('You are already subscriber. You don\'t have to buy subscription.'), sprintf($this->_("Transaction Error : %s"), get_bloginfo('name')), array('back_link' => true));
		}elseif(!$lwp->subscription->has_plans()){
			wp_die($this->_('Sorry, but there is no subscription plan.'), sprintf($this->_("Transaction Error : %s"), get_bloginfo('name')), array('back_link' => true));
		}
		//If not redirected, action is proper.
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
					'current' => 4,
					'total' => 2
				));
			}else{
				//Post not found
				wp_die($this->_('Mmm... Cannot find product. Please check if you have payable post.'), sprintf($this->_("Transaction Error : %s"), get_bloginfo('name')), array('response' => 500));
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
			$message = $this->_("No content is specified.");
		}elseif(lwp_price($book_id) < 1){
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
				//Redirect to success page
				header("Location: ".  lwp_endpoint('success')."&lwp-id={$book_id}");
				exit;
			}else{
				//Content is not available.
				$message = $this->_("This contents is not on sale.");
			}
		}else{
			//Current step
			$total = $book->post_type == $lwp->subscription->post_type ? 4 : 3;
			$current = $book->post_type == $lwp->subscription->post_type ? 2 : 1;
			//Start Transaction
			if(!isset($_GET['_wpnonce'], $_GET['lwp-method']) || !wp_verify_nonce($_GET['_wpnonce'], 'lwp_buynow')){
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
				switch($_GET['lwp-method']){
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
						$billing = ($_GET['lwp-method'] == 'cc') ? true : false;
						if(!$lwp->start_transaction(get_current_user_id(), $_GET['lwp-id'], $billing)){
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
		//Her you are... Something is wrong. Just show message and die.
		wp_die($message, sprintf($this->_("Transaction Error : %s"), get_bloginfo('name')), array('back_link' => true, 'response' => 500));
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
				wp_die($this->_('Mmm... Cannot find product. Please check if you have payable post.'), sprintf($this->_("Transaction Error : %s"), get_bloginfo('name')), array('response' => 500));
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
				wp_die($message, sprintf($this->_("Transaction Error : %s"), get_bloginfo('name')), array("back_link" => true));
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
				//サンキューページを表示する
				header("Location: ".  lwp_endpoint('success')."&lwp-id={$post_id}"); 
			}else{
				wp_die($this->_("Transaction Failed to finish."), $this->_("Failed"), array("back_link" => true));
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
			'link' => $url,
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
		//Get Event ID and get ticket list
		$event_id = isset($_REQUEST['lwp-event']) ? intval($_REQUEST['lwp-event']) : false;
		//If event dosen't exist, stop processing.
		if(!$event_id || !$wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE ID = %d AND post_status = 'publish'", $event_id))){
			wp_die($this->_('Sorry, but you specified unexistant event.'), sprintf($this->_("Transaction Error : %s"), get_bloginfo('name')), array('back_link' => true, 'response' => 500));
		}
		//Check if currently cancelable
		$limit = $lwp->event->get_current_cancel_condition($event_id);
		$cancel_limit_time = date_i18n(get_option('date_format'), lwp_selling_limit('U', $event_id) - (60 * 60 * 24 * $limit['days']));
		if(!$limit){
			wp_die(sprintf($this->_('Sorry, but cancel limit %s is outdated and you cannot cancel.'), $cancel_limit_time), sprintf($this->_("Transaction Error : %s"), get_bloginfo('name')), array('back_link' => true, 'response' => 500));
		}
		//Get cancelable ticket
		$tickets = $lwp->event->get_cancelable_tickets(get_current_user_id(), $event_id);
		if(empty($tickets)){
			wp_die($this->_('Sorry, but you have no ticket to cancel.'), sprintf($this->_("Transaction Error : %s"), get_bloginfo('name')), array('back_link' => true, 'response' => 500));
		}
		//Show Form
		$this->show_form('cancel-ticket', array(
			'tickets' => $tickets,
			'event' => $event_id,
			'event_type' => get_post_type_object($wpdb->get_var($wpdb->prepare("SELECT post_type FROM {$wpdb->posts} WHERE ID = %d", $event_id))),
			'limit' => $cancel_limit_time,
			'ratio' => $limit['ratio'],
			'total' => 2,
			'current' => 1
		));
		
	}
	
	/**
	 * 
	 * @param type $is_sandbox 
	 */
	private function handle_ticket_cancel_complete($is_sandbox = false){
		global $wpdb, $lwp;
		//First of all, user must be logged in
		$this->kill_anonymous_user();
		//Check nonce
		if(!isset($_REQUEST['_wpnonce'], $_REQUEST['ticket_id']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'lwp_ticket_cancel')){
			wp_die($this->_('Sorry, but You might make unexpected action.').' '.$this->_('Cannot pass security check.'), sprintf($this->_("Internal Server Error : %s"), get_bloginfo('name')), array('back_link' => true, 'response' => 500));
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
			wp_die($this->_('Sorry, but the tikcet id you specified is not cancelable.'), sprintf($this->_("Internal Server Error : %s"), get_bloginfo('name')), array('back_link' => true, 'response' => 500));
		}
		//Check if paypal refund is available
		if($transaction->method == LWP_Payment_Methods::PAYPAL && !PayPal_Statics::is_refundable($transaction->updated)){
			wp_die(sprintf($this->_('Sorry, but paypal redunding is available only for 60 days. You made transaction at %1$s and it is %2$s today'), mysql2date(get_option('date_format'), $transaction->updated), date_i18n(get_option('date_format'))), sprintf($this->_("Request Time Out : %s"), get_bloginfo('name')), array('back_link' => true, 'response' => 408));
		}
		//Now, let's start refund action
		$bought_price = $transaction->price;
		$refund_price = round($bought_price * $current_condition['ratio'] / 100);
		$status = false;
		if($refund_price == 0){
			$status = LWP_Payment_Status::REFUND;
		}else{
			if($transaction->method == LWP_Payment_Methods::PAYPAL){
				//Do paypal refunding. In case of refund 0, just change status
				if(PayPal_Statics::do_refund($transaction->transaction_id, $refund_price)){
					$status = LWP_Payment_Status::REFUND;
				}else{
					wp_die(sprintf($this->_('Sorry, but PayPal denies refunding. Please contact to %1$s administrator.'), get_bloginfo('name')), sprintf($this->_("Internal Server Error : %s"), get_bloginfo('name')), array('back_link' => true, 'response' => 500));
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
		$this->kill_anonymous_user();
		//Chcek if event id is set
		if(
			!isset($_GET['lwp-event'])
				||
			!($event = wp_get_single_post($_GET['lwp-event']))
				||
			(false === array_search($event->post_type, $lwp->event->post_types))
		){
			wp_die($this->_('Sorry, but no event is specified.'), sprintf($this->_("Internal Server Error : %s"), get_bloginfo('name')), array('back_link' => true, 'response' => 500));
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
			wp_die($this->_('Sorry, but you have no tikcet to display.'), sprintf($this->_("Not Found : %s"), get_bloginfo('name')), array('back_link' => true, 'response' => 404));
		}
		$check_url = lwp_ticket_check_url(get_current_user_id(), $event);
		$this->show_form('event-tickets', array(
			'title' => $event->post_title,
			'limit' => get_post_meta($event->ID, $lwp->event->meta_selling_limit, true),
			'link' => get_permalink($event->ID),
			'post_type' => $event_type->labels->name,
			'tickets' => $tickets,
			'check_url' => $check_url,
			'qr_src' => '//chart.googleapis.com/chart?chs=200x200&cht=qr&chl='.rawurlencode($check_url)
		));
	}
	
	/**
	 * Stop processing transaction of not logged in user. 
	 */
	private function kill_anonymous_user(){
		if(!is_user_logged_in()){
			wp_die($this->_('You must be logged in to process transaction.'), sprintf($this->_("Access Forbidden : %s"), get_bloginfo('name')), array('back_link' => true, 'response' => 403));
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
		global $lwp;
		extract($args);
		$filename = "paypal-{$slug}.php";
		//テーマテンプレートに存在するかどうか調べる
		if(file_exists(TEMPLATEPATH.DIRECTORY_SEPARATOR.$filename)){
			//テンプレートがあれば読み込む
			require_once TEMPLATEPATH.DIRECTORY_SEPARATOR.$filename;
		}else{
			//なければ自作
			$parent_directory = $this->dir.DIRECTORY_SEPARATOR."form-template".DIRECTORY_SEPARATOR;
			//CSS-js読み込み
			$css = (file_exists(TEMPLATEPATH.DIRECTORY_SEPARATOR."lwp-form.css")) ? get_template_directory_uri()."/lwp-form.css" : $lwp->url."assets/lwp-form.css";
			wp_enqueue_style("lwp-form", $css, array(), $lwp->version);
			wp_enqueue_script("lwp-form-helper", $this->url."assets/js/form-helper.js", array("jquery"), $lwp->version, true);
			require_once $parent_directory."paypal-header.php";
			require_once $parent_directory.$filename;
			require_once $parent_directory."paypal-footer.php";
		}
		exit;
	}
	
	/**
	 * Returns random post if exists for sand box
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp 
	 * @return object
	 */
	private function get_random_post(){
		global $wpdb, $lwp;
		$post_types = implode(',', array_map(create_function('$a', 'return "\'".$a."\'";'), $lwp->option['payable_post_types']));
		$sql = <<<EOS
			SELECT p.* FROM {$wpdb->posts} AS p
			INNER JOIN {$wpdb->postmeta} AS pm
			ON p.ID = pm.post_id AND pm.meta_key = 'lwp_price'
			WHERE p.post_status IN ('draft', publish', 'future') AND p.post_type IN ({$post_types}) AND CAST(pm.meta_valu AS signed) > 0
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
}