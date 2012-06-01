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
			$action = 'handle_'.$this->get_current_action();
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
		if(!($book = wp_get_single_post ($book_id)) || false === array_search($book->post_type, $lwp->option['payable_post_types'])){
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
			$total = $book->post_type == $this->subscription->post_type ? 4 : 3;
			$current = $book->post_type == $this->subscription->post_type ? 2 : 1;
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
								SELECT * FROM {$this->transaction}
								WHERE user_id = %d AND book_id = %d AND status = %s AND DATE_ADD(registered, INTERVAL %d DAY) > NOW()
EOS;
							$transaction = $wpdb->get_row($wpdb->prepare($sql, get_current_user_id(), $book_id, LWP_Payment_Status::START, $lwp->option['notification_limit']));
							if(!$transaction){
								//Register transaction with transfer
								$wpdb->insert(
									$lwp->transaction,
									array(
										"user_id" => $user_ID,
										"book_id" => $book_id,
										"price" => lwp_price($book_id),
										"transaction_key" => sprintf("%08d", $user_ID),
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
								$transaction = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->transaction} WHERE ID = %d", $wpdb->insert_id));
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
						if(!$lwp->start_transaction($user_ID, $_GET['lwp-id'], $billing)){
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
	
	
	private function handle_confirm($is_sandbox = false){
		global $wpdb, $lwp;
		//First of all, user must be login
		if(!is_user_logged_in()){
			wp_die($this->_('You must be logged in to process transaction.'), sprintf($this->_("Transaction Error : %s"), get_bloginfo('name')), array('back_link' => true, 'response' => 500));
		}
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
					"transaction" => $transaction,
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
				$post_id = $wpdb->get_var($wpdb->prepare("SELECT book_id FROM {$this->transaction} WHERE transaction_id = %s", $_POST["TOKEN"])); 
				$tran_id = $wpdb->update(
					$this->transaction,
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
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param boolean $is_sandbox 
	 */
	private function handle_success($is_sandbox = false){
		global $lwp, $wpdb;
		//First of all, user must be login
		if(!is_user_logged_in()){
			wp_die($this->_('You must be logged in to process transaction.'), sprintf($this->_("Transaction Error : %s"), get_bloginfo('name')), array('back_link' => true, 'response' => 500));
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
		//First of all, user must be login
		if(!is_user_logged_in()){
			wp_die($this->_('You must be logged in to process transaction.'), sprintf($this->_("Transaction Error : %s"), get_bloginfo('name')), array('back_link' => true, 'response' => 500));
		}

		$token = isset($_REQUEST['token']) ? $_REQUEST['token'] : null;
		if(!$token){
			$token = isset($_REQUEST['TOKEN']) ? $_REQUEST['TOKEN'] : null;
		}
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
	 * 
	 * @global Literally_WordPress $lwp
	 * @param type $is_sandbox 
	 */
	private function handle_file($is_sandbox = false){
		//First of all, user must be login
		if(!is_user_logged_in()){
			wp_die($this->_('You must be logged in to process transaction.'), sprintf($this->_("Transaction Error : %s"), get_bloginfo('name')), array('back_link' => true, 'response' => 500));
		}
		global $lwp;
		$lwp->print_file($_REQUEST["lwp_file"], get_current_user_id());
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
}