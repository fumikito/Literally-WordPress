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
	
	public function avoid_caonnical_redirect(){
		if(!is_admin() && isset($_GET['lwp'])){
			remove_action('template_redirect', 'redirect_canonical');
		}
	}
	
	/**
	 * Manage form action to lwp endpoint
	 * 
	 * @return void
	 */
	public function manage_actions(){
		//If action is set, call each method
		if($this->get_current_action() && is_front_page()){
			//Avoid WP redirect
			$action = 'handle_'.$this->make_hungalian($this->get_current_action());
			if(method_exists($this, $action)){
				$sandbox = (isset($_REQUEST['sandbox']) && $_REQUEST['sandbox']);
				if($sandbox && !current_user_can('edit_theme_options')){
					$this->kill('Sorry, but you have no permission.', 403);
				}
				if(!apply_filters('lwp_before_display_form', true, $this->get_current_action())){
					$this->kill($this->_('You cannot access here.'), 403, true);
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
		foreach($lwp->subscription->post_types as $post_type){
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
			'owned_subscription' => $lwp->subscription->is_subscriber() ? $lwp->subscription->get_subscription_owned_by() : array(),
			'archive' => $lwp->subscription->get_subscription_post_type_page(),
			'url' => $parent_url,
			'total' => $is_subscription ? 4 : 0,
			'current' => $is_subscription ? 1 : 0,
			'transaction' => $is_subscription,
			'on_icon' => apply_filters('lwp_subscription_icon', sprintf('<img src="%s" width="32" heigth="32" alt="ON" />', $this->url.'assets/icon-check-on.png'), 'ON'),
			'off_icon' => apply_filters('lwp_subscription_icon', sprintf('<img src="%s" width="32" heigth="32" alt="OFF" />', $this->url.'assets/icon-check-off.png'), 'OFF')
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
			auth_redirect();
			exit;
		}
		//If it's sandbox, just show form
		if($is_sandbox){
			//Get random post
			$book = $this->get_random_post();
			if($book){
				//Find random post, show form
				$item_name = $this->get_item_name($book);
				$price = lwp_price($book->ID);
				$this->show_form('selection', array(
					'prices' => array($book->ID => $price),
					'items' => array($book->ID => $item_name),
					'quantities' => array($book->ID => 1),
					'total_price' => $price,
					'post_id' => $book->ID,
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
		$book = $this->test_post_id($book_id);
		//Let's do action hook to delegate transaction to other plugin
		do_action('lwp_before_transaction', $book);
		//All green, start transaction
		if(lwp_price($book_id) == 0){
			//Content is free
			if(lwp_original_price($book_id) > 0){
				//Original price is not free, temporally free.
				$data = array(
					"user_id" => get_current_user_id(),
					"book_id" => $book_id,
					"price" => 0,
					"status" => LWP_Payment_Status::SUCCESS,
					"method" => LWP_Payment_Methods::CAMPAIGN,
					"registered" => gmdate('Y-m-d H:i:s'),
					"updated" => gmdate('Y-m-d H:i:s'),
					'expires' => lwp_expires_date($book_id)
				);
				$where = array("%d", "%d", "%d", "%s", "%s", "%s", "%s", "%s");
				if(($tran_id = lwp_is_user_waiting($book_id, get_current_user_id()))){
					unset($data['registered']);
					array_pop($where);
					$wpdb->update($lwp->transaction, $data, array('ID' => $tran_id), $where, array('%d'));
					do_action('lwp_update_transaction', $tran_id);
				}else{
					$wpdb->insert( $lwp->transaction, $data, $where );
					do_action('lwp_create_transaction', $wpdb->insert_id);
				}
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
			$item_name = $this->get_item_name($book);
			//Start Transaction
			//If payment selection required or nonce isn't corret, show form.
			if(!$this->can_skip_payment_selection() && (!isset($_GET['_wpnonce'], $_GET['lwp-method']) || !wp_verify_nonce($_GET['_wpnonce'], 'lwp_buynow'))){
				//Select Payment Method and show form
				$price = lwp_price($book_id);
				$this->show_form('selection', array(
					'prices' => array($book_id => $price),
					'items' => array($book_id => $item_name),
					'quantities' => array($book_id => 1),
					'total_price' => $price,
					'post_id' => $book_id,
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
								WHERE user_id = %d AND book_id = %d AND status = %s AND method = %s AND DATE_ADD(registered, INTERVAL %d DAY) > NOW()
EOS;
							$transaction = $wpdb->get_row($wpdb->prepare($sql, get_current_user_id(), $book_id, LWP_Payment_Status::START, LWP_Payment_Methods::TRANSFER, $lwp->option['notification_limit']));
							if(!$transaction){
								//Register transaction with transfer
								$data = array(
									"user_id" => get_current_user_id(),
									"book_id" => $book_id,
									"price" => lwp_price($book_id),
									"transaction_key" => sprintf("%08d", get_current_user_id()),
									"status" => LWP_Payment_Status::START,
									"method" => LWP_Payment_Methods::TRANSFER,
									"registered" => gmdate('Y-m-d H:i:s'),
									"updated" => gmdate('Y-m-d H:i:s')
								);
								$where = array("%d", "%d", "%d", "%s", "%s", "%s", "%s", "%s");
								if((($tran_id = lwp_is_user_waiting($book_id, get_current_user_id())))){
									unset($data['registered']);
									array_pop($where);
									$wpdb->update($lwp->transaction, $data, array('ID' => $tran_id), $where, array('%d'));
									do_action('lwp_update_transaction', $tran_id);
								}else{
									$wpdb->insert( $lwp->transaction, $data, $where);
									$tran_id = $wpdb->insert_id;
									//Execute hook
									do_action('lwp_create_transaction', $wpdb->insert_id);
								}
								//Send Notification
								$transaction = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$lwp->transaction} WHERE ID = %d", $tran_id));
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
								'notification' => $notification_status,
								'link' => $url,
								'thankyou' => $lwp->notifier->get_thankyou($transaction),
								'total' => $total,
								'current' => $current + 1
							));
						}else{
							$message = $this->_("Sorry, but we can't accept this payment method.");
						}
						break;
					case 'paypal':
					case 'cc':
						//Create transaction
						$price = lwp_price($book_id);
						//Get token
						$invnum = sprintf("{$lwp->option['slug']}-%08d-%08d-%d", $book_id, get_current_user_id(), current_time('timestamp'));
						//Check is physical
						switch($book->post_type){
							case $lwp->event->post_type:
								$physical = true;
								break;
							default:
								$physical = false;
								break;
						}
						$token = PayPal_Statics::get_transaction_token($price, $invnum, lwp_endpoint('confirm'), lwp_endpoint('cancel'),
								($method == 'cc'), array(array(
									'name' => $item_name,
									'amt' => $price,
									'quantity' => 1,
									'physical' => $physical,
									'url' => get_permalink($book_id)
								)));
						if($token){
							//Get token, save transaction
							$data = array(
								"user_id" => get_current_user_id(),
								"book_id" => $book_id,
								"price" => $price,
								"status" => LWP_Payment_Status::START,
								"method" => LWP_Payment_Methods::PAYPAL,
								"transaction_key" => $invnum,
								"transaction_id" => $token,
								"registered" => gmdate('Y-m-d H:i:s'),
								"updated" => gmdate('Y-m-d H:i:s')
							);
							$where = array("%d", "%d", "%d", "%s", "%s", "%s", "%s", "%s", "%s");
							$sql = <<<EOS
								SELECT ID FROM {$lwp->transaction}
								WHERE transaction_key = %s
EOS;
							if($wpdb->get_var($wpdb->prepare($sql, $invnum))){
								//If invnum is same as a recent transaction,
								//This transaction may be created in very few seconds.
								//Nothing to do and just skip.
							}elseif(($tran_id = lwp_is_user_waiting($book_id, get_current_user_id()))){
								// User is waiting cancellation on event selling.
								// So reuse transaction.
								unset($data['registered']);
								array_pop($where);
								$wpdb->update($lwp->transaction, $data, array('ID' => $tran_id), $where, array('%d'));
								do_action('lwp_update_transaction', $tran_id);
							}else{
								// Virgin session, let's create transaction.
								$wpdb->insert( $lwp->transaction, $data, $where);
								//Execute hook
								do_action('lwp_create_transaction', $wpdb->insert_id);
							}
							//Redirect to Paypal
							PayPal_Statics::redirect($token);
							exit;
						}else{
							//No response from PayPal
							$message = $this->_("Failed to make transaction.");
						}
						break;
					case 'sb-cvs':
					case 'sb-payeasy':
						if(!$lwp->softbank->can_pay_with($book)){
							$this->kill(sprintf($this->_('You can\'t select %s because selling limit is today.'),
								( ($method == 'sb-cvs') ? $this->_('Web CVS') : 'PayEasy')
							), 403);
						}else{
							header('Location: '.lwp_endpoint('payment').'&lwp-method='.$method.'&lwp-id='.$book_id);
							exit();
						}
						break;
					case 'gmo-cvs':
					case 'gmo-payeasy':
						
					case 'sb-cc':
					case 'gmo-cc':
						header('Location: '.lwp_endpoint('payment').'&lwp-method='.$method.'&lwp-id='.$book_id);
						exit();
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
	 * Handle payment information
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param boolean $is_sandbox
	 */
	private function handle_payment($is_sandbox = false){
		global $lwp, $wpdb;
		//Shut down not logged in user.
		$this->kill_anonymous_user();
		//Filter payment method
		$allowed_methods = array(
			'sb-cc' => $lwp->softbank->is_cc_enabled(),
			'sb-cvs' => $lwp->softbank->is_cvs_enabled(),
			'sb-payeasy' => $lwp->softbank->payeasy,
			'gmo-cc' => $lwp->gmo->is_cc_enabled(),
			'gmo-cvs' => $lwp->gmo->is_cvs_enabled(),
			'gmo-payeasy' => $lwp->gmo->payeasy,
		);
		if(isset($_REQUEST['lwp-method'])){
			$payment_method = $_REQUEST['lwp-method'];
		}else{
			$payment_method = $is_sandbox ? 'sb-cc' : '';
		}
		if(!array_key_exists($payment_method, $allowed_methods) || !$allowed_methods[$payment_method]){
			$this->kill($this->_('Specified payment method doesn\'t exist.'), 404, true);
		}
		//Set default values.
		$error = array();
		switch($payment_method){
			case 'sb-cc':
			case 'sb-cvs':
			case 'sb-payeasy':
				$vars = $lwp->softbank->get_default_payment_info(get_current_user_id(), $payment_method);
				break;
			case 'gmo-cc':
			case 'gmo-cvs':
			case 'gmo-payeasy':
				$vars = $lwp->gmo->get_default_payment_info(get_current_user_id(), $payment_method);
				break;
			default:
				$vars = array();
				break;
		}
		if($is_sandbox){
			//Get random post
			$book = $this->get_random_post();
			if($book){
				//Find random post, show form
				$item_name = $this->get_item_name($book);
				$price = lwp_price($book->ID);
				$vars['limit'] = date(get_option('date_format'), current_time('timestamp') + 60 * 60 * 24 * 59);
				$this->show_form('payment', array(
					'prices' => array($book->ID => $price),
					'items' => array($book->ID => $item_name),
					'quantities' => array($book->ID => 1),
					'total_price' => $price,
					'post_id' => $book->ID,
					'method' => $payment_method,
					'vars' => $vars,
					'link' => '#',
					'action' => '#',
					'error' => array(),
					'current' => 3,
					'total' => 4
				));
			}else{
				$this->kill($this->_('Mmm... Cannot find product. Please check if you have payable post.'), 404);
			}
		}
		//Get Items
		$book_id = isset($_REQUEST['lwp-id']) ? intval($_REQUEST['lwp-id']) : 0;
		//Test content
		$book = $this->test_post_id($book_id);
		$item_name = $this->get_item_name($book);
		$price = lwp_price($book);
		//Get payment limit
		if(false !== array_search($payment_method, array('sb-payeasy', 'sb-cvs'))){
			$vars['limit'] = date_i18n(get_option('date_format'), $lwp->softbank->get_payment_limit($book, false, $payment_method));
		}elseif(false !== array_search($payment_method, array('gmo-payeasy', 'gmo-cvs'))){
			$vars['limit'] = date_i18n(get_option('date_format'), $lwp->gmo->get_payment_limit($book, false, $payment_method));
		}
		//Do transaction
		if(isset($_REQUEST['_wpnonce'])){
			$sb_cvs = $sb_payeasy = false;
			switch($payment_method){
				case 'sb-cc':
				case 'gmo-cc':
					if(
						($is_sb_cc = wp_verify_nonce($_REQUEST['_wpnonce'], 'lwp_payment_sb_cc'))
							||
						($is_gmo_cc = wp_verify_nonce($_REQUEST['_wpnonce'], 'lwp_payment_gmo_cc'))
					){
						//If samecard flg is not set, validate Card number
						$use_same_card = (boolean)(isset($_REQUEST['same_card']) && $_REQUEST['same_card']);
						if(!$use_same_card){
							//Credit card
							$cc_number = $this->convert_numeric((string)$_REQUEST['cc_number']);
							$cc_month = $this->convert_numeric((string)$_REQUEST['cc_month']);
							$cc_year = $this->convert_numeric((string)$_REQUEST['cc_year']);
							$cc_sec = $this->convert_numeric((string)$_REQUEST['cc_sec']);
							//Validate
							if(empty($cc_number) || !preg_match("/^[0-9]{14,16}$/", $cc_number)){
								$error[] = $this->_('Credit card number should be 14 - 16 digits and is required.');
							}
							$this_year = date('Y');
							if($cc_year < $this_year || ($cc_year == $this_year && $cc_month < date('m'))){
								$error[] = $this->_('Credit card is expired.');
							}
							if(!preg_match("/^[0-9]{3,4}$/", $cc_sec)){
								$error[] = $this->_('Security code is wrong. ').$this->_('Security code is 3 or 4 digits written near the card number on the credit card.');
							}
							//Save Payment information 
							$save_customer_info = (boolean)(isset($_REQUEST['save_cc_number']) && $_REQUEST['save_cc_number']);
						}else{
							$cc_number = $cc_sec = $cc_year = $cc_month = '';
							$save_customer_info = false;
						}
						//All Green Make request
						if(empty($error)){
							$transaction_id = 0;
							$error_msg = '';
							if($is_sb_cc){
								$transaction_id = $lwp->softbank->do_credit_authorization(get_current_user_id(), $item_name, $book_id, $price, 1, $cc_number, $cc_sec, $cc_year.$cc_month, $save_customer_info, $use_same_card);
								if($transaction_id){
									if($lwp->softbank->capture_authorized_transaction($transaction_id)){
										$wpdb->update($lwp->transaction, array('status' => LWP_Payment_Status::SUCCESS), array('ID' => $transaction_id), array('%s'), array('%d'));
									}else{
										$transaction_id = 0;
									}
								}
								$error_msg = $lwp->softbank->last_error;
							}elseif($is_gmo_cc){
								$transaction_id = $lwp->gmo->do_credit_authorization(get_current_user_id(), $item_name, $book_id, $price, 1, $cc_number, $cc_sec, $cc_year.$cc_month);
								$error_msg = $lwp->gmo->last_error;
							}
							if($transaction_id){
								do_action('lwp_update_transaction', $transaction_id);
								header('Location: '.lwp_endpoint('success')."&lwp-id={$book_id}");
								die();
							}else{
								$error[] = $this->_('Failed to make transaction.').':'.$error_msg;
							}
						}
						if(!empty($error)){
							$vars = compact('cc_number', 'cc_month', 'cc_year', 'cc_sec');
						}
					}
					break;
				case 'gmo-cvs':
				case 'gmo-payeasy':
				case 'sb-cvs':
				case 'sb-payeasy':
					$gmo_cvs = $gmo_payeasy = $sb_cvs = $sb_payeasy = false;
					if(
						($sb_cvs = wp_verify_nonce ($_REQUEST['_wpnonce'], 'lwp_payment_sb_cvs'))
							||
						($sb_payeasy = wp_verify_nonce ($_REQUEST['_wpnonce'], 'lwp_payment_sb_payeasy'))
							||
						($gmo_cvs = wp_verify_nonce ($_REQUEST['_wpnonce'], 'lwp_payment_gmo_cvs'))
							||
						($gmo_payeasy = wp_verify_nonce ($_REQUEST['_wpnonce'], 'lwp_payment_gmo_payeasy'))
					){
						//Let's validate
						//Validate CVS name.
						if($sb_cvs || $gmo_cvs){
							$cvss = ($gmo_cvs) ? $lwp->gmo->get_available_cvs() : $lwp->softbank->get_available_cvs();
							if(!isset($_REQUEST['cvs-name']) || false === array_search($_REQUEST['cvs-name'], $cvss)){
								$error[] = $this->_('CVS is not specified. please select one.');
							}else{
								$vars['cvs'] = $_REQUEST['cvs-name'];
							}
							if($_REQUEST['cvs-name'] == 'seven-eleven' && $price < 200){
								$error[] = $this->_('Seven Eleven doesn\'t accept payment amount less than 200 JPY. Please select other payment method.');
							}	
						}
						//Zip No.
						if($sb_cvs || $sb_payeasy){
							$zip = $this->convert_numeric($_REQUEST['zipcode']);
							if(!preg_match("/^[0-9]{7}$/", $zip)){
								$error[] = $this->_('Zip code is required and must be 7 digits.');
							}
						}
						//Tel No.
						$tel = $this->convert_numeric($_REQUEST['tel']);
						if(!preg_match("/^[0-9]{9,11}$/", $tel)){
							$error[] = $this->_('Tel number is required and must be 9 - 11 digits.');
						}
						//Validate required String
						$not_empty = array(
							'last_name' => $this->_('Last Name'),
							'first_name' => $this->_('First Name'),
						);
						if($sb_cvs || $sb_payeasy){
							$not_empty = array_merge($not_empty, array(
								'prefecture' => $this->_('Prefecture'),
								'city' => $this->_('City'),
								'street' => $this->_('Street')
							));
						}
						foreach($not_empty as $name => $value){
							if(empty($_REQUEST[$name])){
								$error[] = sprintf('%s is empty.', $value);
							}
						}
						//must be kana
						$last_name_kana = $this->convert_zenkaka_kana($_REQUEST['last_name_kana']);
						if(empty($last_name_kana)){
							$error[] = sprintf('%s must be zenkaku kana.', $this->_('Last Name Kana'));
						}
						$first_name_kana = $this->convert_zenkaka_kana($_REQUEST['first_name_kana']);
						if(empty($first_name_kana)){
							$error[] = sprintf('%s must be zenkaku kana.', $this->_('First Name Kana'));
						}
						//Set entered information
						$key_to_set = array('last_name', 'first_name');
						if($sb_cvs || $sb_payeasy){
							$key_to_set = array_merge($key_to_set, array(
								'prefecture', 'city', 'street', 'office'
							));
						}
						foreach($key_to_set as $key){
							$vars[$key] = $_REQUEST[$key];
						}
						if($sb_cvs || $gmo_cvs){
							$vars['cvs'] = $_REQUEST['cvs-name'];
						}
						$vars['tel'] = $tel;
						$vars['last_name_kana'] = $last_name_kana;
						$vars['first_name_kana'] = $first_name_kana;
						if($sb_cvs || $sb_payeasy){
							$vars['zipcode'] = $zip;
						}
						//Make transaction
						if(empty($error)){
							$transaction_id = 0;
							//Do transaction
							switch(true){
								case $sb_cvs:
									$transaction_id = $lwp->softbank->do_cvs_authorization(get_current_user_id(), $item_name, $book_id, $price, 1, $vars); 
									break;
								case $sb_payeasy:
									$transaction_id = $lwp->softbank->do_payeasy_authorization(get_current_user_id(), $item_name, $book_id, $price, 1, $vars);
									break;
								case $gmo_cvs:
									$transaction_id = $lwp->gmo->do_cvs_authorization(get_current_user_id(), $item_name, $book_id, $price, 1, $vars); 
									break;
								case $gmo_payeasy:
									$transaction_id = $lwp->gmo->do_payeasy_authorization(get_current_user_id(), $item_name, $book_id, $price, 1, $vars);
									break;
							}
							if(($transaction_id)){
								//Save payment information
								if(isset($_REQUEST['save_info']) && $_REQUEST['save_info']){
									switch(true){
										case $sb_cvs:
										case $sb_payeasy:
											$lwp->softbank->save_payment_info(get_current_user_id(), $vars, $payment_method);
											break;
										case $gmo_cvs:
										case $gmo_payeasy:
											$lwp->gmo->save_payment_info(get_current_user_id(), $vars, $payment_method);
											break;
									}
								}
								//Send mail
								header('Location: '.lwp_endpoint('payment-info').'&transaction='.$transaction_id);
								die();
							}else{
								$error[] = $this->_('Failed to make transaction.').':'.(($sb_cvs || $sb_payeasy) ? $lwp->softbank->last_error : $lwp->gmo->last_error);
							}
						}
					}
					break;
			}
		}
		$this->show_form('payment', array(
			'prices' => array($book->ID => $price),
			'items' => array($book->ID => $item_name),
			'quantities' => array($book->ID => 1),
			'total_price' => $price,
			'post_id' => $book->ID,
			'action' => lwp_endpoint('payment'),
			'method' => $payment_method,
			'error' => $error,
			'vars' => $vars,
			'link' => lwp_endpoint().'&lwp-id='.$book_id,
			'current' => 3,
			'total' => 4
		));
	}
	
	/**
	 * 
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param boolean $is_sandbox
	 */
	private function handle_payment_info($is_sandbox = true){
		global $lwp, $wpdb;
		$this->kill_anonymous_user();
		$transaction_id = isset($_REQUEST['transaction']) ? intval($_REQUEST['transaction']) : 0;
		$methods = implode(', ', array_map(create_function('$m', 'return "\'".$m."\'";'), array(LWP_Payment_Methods::SOFTBANK_PAYEASY, LWP_Payment_Methods::SOFTBANK_WEB_CVS, LWP_Payment_Methods::TRANSFER, LWP_Payment_Methods::GMO_WEB_CVS, LWP_Payment_Methods::GMO_PAYEASY)));
		$sql = <<<EOS
			SELECT * FROM {$lwp->transaction} WHERE user_id = %d AND method IN ({$methods}) AND ID = %d
EOS;
		$transaction = $wpdb->get_row($wpdb->prepare($sql, get_current_user_id(), $transaction_id));
		if(!$transaction){
			$this->kill($this->_('Specified transaction is not found.'), 404, true);
		}
		$book = get_post($transaction->book_id);
		switch($transaction->status){
			case LWP_Payment_Status::START:
			case LWP_Payment_Status::REFUND_REQUESTING:
				$status_color = 'red';
				break;
			case LWP_Payment_Status::SUCCESS:
				$status_color = 'green';
				break;
			default:
				$status_color = 'dark-grey';
				break;
		}
		$rows = array(
			array($this->_('Status'), sprintf('<strong style="color:%s;">%s</strong>', $status_color, ($transaction->status == LWP_Payment_Status::START ? $this->_('Waiting for Payment') : $this->_($transaction->status) ) )),
			array($this->_('Price'), number_format($transaction->price).' '.lwp_currency_code()),
			array($this->_('Requested Date'), get_date_from_gmt($transaction->registered, get_option('date_format'))),
		);
		if($transaction->method == LWP_Payment_Methods::TRANSFER){
			$rows = array_merge($rows, array(
				array($this->_('Payment Limit'), $lwp->notifier->get_limit_date($transaction->registered, get_option('date_format')).' ('.sprintf($this->_('%d days left'), $lwp->notifier->get_left_days($transaction->registered)).')'),
				array($this->_('Bank Account'), $lwp->notifier->get_bank_account())
			));
		}else{
			$data = unserialize($transaction->misc);
			if(preg_match("/^SB_/", $transaction->method)){
				$limit_date = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "$1-$2-$3 23:59:59", $data['bill_date']);
			}else{
				$limit_date = $data['bill_date'];
			}
			if($transaction->status == LWP_Payment_Status::START){
				$left_days = ceil((strtotime($limit_date) - current_time('timestamp')) / 60 / 60 / 24);
				if($left_days < 0){
					$request_suffix = sprintf($this->_(' (%d days past)'), $left_days);
				}else{
					$request_suffix = sprintf($this->_(' (%d days left)'), $left_days);
				}
				if($left_days < 3){
					$request_suffix = '<span style="color:red;">'.$request_suffix.'</span>';
				}
			}else{
				$request_suffix = '';
			}
			$rows[] = array($this->_('Payment Limit'), mysql2date(get_option('date_format'), $limit_date).' '.$request_suffix);
			switch($transaction->method){
				case LWP_Payment_Methods::SOFTBANK_WEB_CVS:
					$methods = $lwp->softbank->get_cvs_code_label($data['cvs']);
					$cvss = $lwp->softbank->get_cvs_group($data['cvs']);
					$cvs_icons = '';
					foreach($cvss as $cvs){
						$cvs_icons .= sprintf('<label class="cvs-container"><i class="lwp-cvs-small-icon small-icon-%s"></i><br />%s</label>',
								$cvs, $lwp->softbank->get_verbose_name($cvs));
					}
					$rows =  array_merge($rows, array(
						array($this->_('CVS'), $cvs_icons),
						array($this->_('How to pay'), $lwp->softbank->get_cvs_howtos($data['cvs']))
					));
					for($i = 0, $l = count($methods); $i < $l; $i++){
						$rows[] = array($methods[$i], $data['cvs_pay_data'.($i + 1)]);
					}
					break;
				case LWP_Payment_Methods::GMO_WEB_CVS:
					$methods = $lwp->gmo->get_cvs_code_label($data['cvs']);
					$cvss = $lwp->gmo->get_cvs_group($data['cvs']);
					$cvs_icons = '';
					foreach($cvss as $cvs){
						$cvs_icons .= sprintf('<label class="cvs-container"><i class="lwp-cvs-small-icon small-icon-%s"></i><br />%s</label>',
								$cvs, $lwp->gmo->get_verbose_name($cvs));
					}
					$rows =  array_merge($rows, array(
						array($this->_('CVS'), $cvs_icons),
						array($this->_('How to pay'), $lwp->gmo->get_cvs_howtos($data['cvs'])),
						array($methods[0], $data['receipt_no'])
					));
					if(!empty($data['conf_no'])){
						$rows[] = array('確認番号', $data['conf_no']);
					}
					break;
				case LWP_Payment_Methods::GMO_PAYEASY:
					$notice = $lwp->gmo->get_payeasy_notice();
					$rows = array_merge($rows, array(
						array('収納機関番号', esc_html($data['bkcode'])),
						array('お客様番号', esc_html($data['cust_id'])),
						array('確認番号', esc_html($data['conf_no'])),
						array('<i class="lwp-cc-icon icon-payeasy"></i><br />PayEasy注意事項', "<ul>{$notice['notice']}</ul>"),
						array('ATMお支払い', "<ol>{$notice['atm']}</ol>"),
						array('ネットバンキング', "<ol>{$notice['net']}</ol>")
					));
					break;
				case LWP_Payment_Methods::SOFTBANK_PAYEASY:
					$cust_number = explode('-', $data['cust_number']);
					$rows = array_merge($rows, array(
						array($this->_('Invoice No.'), esc_html($data['invoice_no'])),
						array('収納機関番号', esc_html($data['skno'])),
						array('お客様番号', esc_html($cust_number[0])),
						array('確認番号', esc_html($cust_number[1]))
					));
					break;
			}
		}
		switch(get_post_type($transaction->book_id)){
			case $lwp->event->post_type:
				$parent = $wpdb->get_var($wpdb->prepare("SELECT post_parent FROM {$wpdb->posts} WHERE ID = %d", $transaction->book_id));
				$link = get_permalink($parent);
				break;
			case $lwp->subscription->post_type:
				$link = $lwp->subscription->get_subscription_archive();
				break;
			default:
				$link = get_permalink($transaction->book_id);
				break;
		}
		$this->show_form('payment-info', array(
			'item_name' => $this->get_item_name($book),
			'quantity' => $transaction->num,
			'method_name' => $this->_($transaction->method),
			'link' => $link,
			'rows' => apply_filters('lwp_payment_info_tbody', $rows, $transaction),
			'back' => lwp_history_url()
		));
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
			switch($post->post_type){
				case $lwp->event->post_type:
					$item_name = get_the_title($post->post_parent).' '.$post->post_title;
					break;
				case $lwp->subscription->post_type:
					$item_name = $this->_('Subscription').' '.$post->post_title;
					break;
				default:
					$item_name = $post->post_title;
					break;
			}
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
					"item_name" => $item_name,
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
			$transaction = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$lwp->transaction} WHERE user_id = %d AND transaction_key = %s", get_current_user_id(), $info['INVNUM']));
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
				switch($post->post_type){
					case $lwp->event->post_type:
						$item_name = get_the_title($post->post_parent).' '.$post->post_title;
						break;
					case $lwp->subscription->post_type:
						$item_name = $this->_('Subscription').' '.$post->post_title;
						break;
					default:
						$item_name = $post->post_title;
						break;
				}
				$this->show_form("return", array(
					"info" => $info,
					"transaction" => $transaction,
					"item_name" => $item_name,
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
				$post_id = $wpdb->get_var($wpdb->prepare("SELECT book_id FROM {$lwp->transaction} WHERE transaction_key = %s", $_POST["INVNUM"])); 
				$sql = <<<EOS
					UPDATE {$lwp->transaction}
					SET status = %s, transaction_id = %s, payer_mail = %s, updated = %s, expires = %s
					WHERE user_id = %d AND transaction_key = %s
					LIMIT 1
EOS;
				$wpdb->query($wpdb->prepare(
						$sql, 
						LWP_Payment_Status::SUCCESS, $transaction_id, $_POST['EMAIL'], current_time('mysql', true), lwp_expires_date($post_id),
						get_current_user_id(), $_POST['INVNUM']));
				//Do action hook on transaction updated
				do_action('lwp_update_transaction', $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$lwp->transaction} WHERE user_id = %d AND transaction_id = %s LIMIT 1", get_current_user_id(), $transaction_id)));
				//サンキューページを表示する
				header("Location: ".  lwp_endpoint('success')."&lwp-id={$post_id}"); 
				exit;
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
				$lwp->transaction,
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
	 * @global boolean $is_IE
	 * @param type $is_sandbox 
	 */
	private function handle_file($is_sandbox = false){
		global $lwp;
		//Get file object
		$file = isset($_REQUEST['lwp_file']) ? $lwp->post->get_files(null, $_REQUEST["lwp_file"]) : null;
		if(!$file){
			$this->kill($this->_('Specified file does not exist.'), 404);
		}
		//Check user permission
		if(!lwp_user_can_download($file, get_current_user_id())){
			$this->kill($this->_('You have no permission to access this file.'), 403);
		}
		//Try Print file
		$lwp->post->print_file($file);
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
		//Show Form
		$this->show_form('cancel-ticket-success', array(
			'link' => get_permalink($event_id),
			'event' => get_the_title($event_id),
			'message' => (false !== array_search($status , array(LWP_Payment_Status::REFUND)))
					? sprintf($this->_('You have successfully canceled <strong>%1$s x %2$d</strong>.'), $this->get_item_name($transaction->book_id), $transaction->num)
					: sprintf($this->_('Refund request for <strong>%1$s x %2$d</strong> has been accepted. Please wait for our refund process finished.'), $this->get_item_name($transaction->book_id), $transaction->num)
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
			'link' => get_permalink($event->ID),
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
			'action' => lwp_endpoint('ticket-owner').'&event_id='.$event->ID,
		));
	}
	
	/**
	 * Handle cancel waiting 
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param boolean $is_sandbox
	 */
	private function handle_ticket_awaiting($is_sandbox = false){
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
	private function handle_ticket_awaiting_deregister($is_sandbox = false){
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
			'labelConfirm' => $this->_('Are you sure to send this mail? You can\'t cancel this action.'),
			'labelSending' => $this->_('Sending&hellip;'),
			'labelInvalidFrom' => $this->_('Please specify mail from.'),
			'labelInvalidSubject' => $this->_('Subject is empty.'),
			'labelInvalidBody' => $this->_('Mail body is empty.'),
			'labelInvalidTo' => $this->_('Recipients not set.'),
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
	
	/**
	 * Register refund information
	 * @global Literally_WordPress $lwp
	 * @param type $is_sandbox
	 */
	private function handle_refund_account($is_sandbox = false){
		global $lwp;
		//Auth redirect
		$this->kill_anonymous_user(false);
		$account = shortcode_atts(array(
			'bank_name' => '',
			'bank_code' => '',
			'branch_name' => '',
			'branch_no' => '',
			'account_type' => 'normal',
			'account_no' => '',
			'account_holder' => ''
		), $lwp->refund_manager->get_user_account(get_current_user_id(), false));
		$error = array();
		if(isset($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'lwp_update_refund_account_'.get_current_user_id())){
			$bank_code = $this->convert_numeric($_REQUEST['bank_code']);
			$branch_no = $this->convert_numeric($_REQUEST['branch_no']);
			$account_no = $this->convert_numeric($_REQUEST['account_no']);
			$account = array(
				'bank_name' => (string)$_REQUEST['bank_name'],
				'bank_code' => $bank_code,
				'branch_name' => (string)$_REQUEST['branch_name'],
				'branch_no' => $branch_no,
				'account_type' => (!isset($_REQUEST['account_type']) || $_REQUEST['account_type'] != 'checking') ? 'normal' : 'checking',
				'account_no' => $account_no,
				'account_holder' => (string)$_REQUEST['account_holder']
			);
			update_user_meta(get_current_user_id(), $lwp->refund_manager->meta_refund_contact, $account);
		}
		//Validation
		//Check not empty
		foreach(array(
			'bank_name' => $this->_('Bank Name'),
			'branch_name' => $this->_('Branch Name'),
			'account_no' => $this->_('Account No.'),
			'account_holder' => $this->_('Account Holder')
		) as $key => $name){
			if(empty($account[$key])){
				$error[] = sprintf($this->_('%s is empty.'), $name);
			}
		}
		$this->show_form('refund-account', array(
			'error' => $error,
			'account' => $account,
			'back' => lwp_history_url()
		));
	}
	
	/**
	 * Handle request from GMO
	 * @global Literally_WordPress $lwp
	 * @return
	 */
	private function handle_gmo_payment(){
		global $lwp;
		echo intval(!(boolean)$lwp->gmo->parse_notification($_POST));
		die();
	}
	
	/**
	 * Parse XML Data from Softbank Payment
	 * @global Literally_WordPress $lwp
	 */
	private function handle_sb_payment($is_sandbox){
		global $lwp, $wpdb;
		if($_SERVER["REQUEST_METHOD"] != "POST"){
			$this->kill_anonymous_user();
			if(!current_user_can('manage_options')){
				$this->kill($this->_('You have no permission to see this URL.'), 403);
			}
			//Request
			$response = false;
			if(isset($_GET['sb_transaction'], $_GET['sb_status'])){
				$xml = mb_convert_encoding($lwp->softbank->make_pseudo_request($_GET['sb_transaction'], $_GET['sb_status']), 'utf-8', 'sjis-win');
				$response_xml = simplexml_load_string($xml);
				$response = '';
				if($response_xml->res_err_code){
					$response .= "Error: \n".mb_convert_encoding(base64_decode(strval($response_xml->res_err_code)), 'utf-8', 'sjis-win')."\n\n----------------\n\n";
				}
				$response .= $this->_("Parsed Data: \n").var_export($response_xml, true)."\n\n----------------\n\n";
				$response .= "XML: \n". $xml;
			}
			//Transaction to be change
			$sql = <<<EOS
				SELECT * FROM {$lwp->transaction}
				WHERE method IN (%s, %s) AND status = %s
EOS;
			$this->show_form('sb-check', array(
				'transactions' => $wpdb->get_results($wpdb->prepare($sql, LWP_Payment_Methods::SOFTBANK_PAYEASY, LWP_Payment_Methods::SOFTBANK_WEB_CVS, LWP_Payment_Status::START)),
				'action' => lwp_endpoint('sb-payment'),
				'message' =>  $lwp->softbank->is_sandbox
					? '<p class="message">'.sprintf($this->_('This page confirm whether your endpoint <code>%s</code> works in order. Please select transaction to be finished.'), lwp_endpoint('sb-payment')).'</p>'
					: '<p class="message error">'.$this->_('This is not sandbox environment. Are you sure to change status?').'</p>',
				'link' => admin_url('admin.php?page=lwp-setting&view=payment#setting-softbank'),
				'response' => $response
			), false);
			exit;
		}else{
			$xml_data = file_get_contents('php://input');
			header('Content-Type: text/xml; charset=Shift_JIS');
			echo $lwp->softbank->parse_request($xml_data);
		}
		die();
	}
	
	/**
	 * Stop processing transaction of not logged in user. 
	 * @param boolean $kill if set to false, user will be auth_redirec-ed.
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
	 */
	private function show_form($slug, $args = array(), $die = true){
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
			add_action('wp_head', array($this, 'form_wp_head'));
			require_once $parent_directory."paypal-header.php";
			do_action('lwp_before_form', $slug, $args);
			require_once $parent_directory.$filename;
			do_action('lwp_after_form', $slug, $args);
			require_once $parent_directory."paypal-footer.php";
		}
		if($die){
			exit;
		}
	}
	
	/**
	 * Avoid Form to be crowled.
	 */
	public function form_wp_head(){
		echo '<meta name="robots" content="noindex,nofollow" />';
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
		//Screen CSS
		$css = apply_filters('lwp_css', (file_exists(get_stylesheet_directory().DIRECTORY_SEPARATOR."lwp-form.css")) ? get_stylesheet_directory_uri()."/lwp-form.css" : $lwp->url."assets/compass/stylesheets/lwp-form.css", 'form');
		if($css){
			wp_enqueue_style("lwp-form", $css, array(), $lwp->version, 'screen');
		}
		//Print CSS
		$print_css = apply_filters('lwp_css', (file_exists(get_stylesheet_directory().DIRECTORY_SEPARATOR.'lwp-print.css')) ? get_stylesheet_directory_uri()."/lwp-print.css" : $lwp->url."assets/compass/stylesheets/lwp-print.css", 'print');
		if($print_css){
			wp_enqueue_style("lwp-form-print", $print_css, array(), $lwp->version, 'print');
		}
		//JS for form helper
		wp_enqueue_script("lwp-form-helper", $this->url."assets/js/form-helper.js", array("jquery-form", 'jquery-effects-highlight'), $lwp->version, true);
		//Add Common lables
		$this->_LWP['labelProcessing'] = $this->_('Processing&hellip;');
		wp_localize_script('lwp-form-helper', 'LWP', $this->_LWP);
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
	 * Check if specified post can be bought
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param int $id
	 * @return object
	 */
	private function test_post_id($id){
		global $lwp, $wpdb;
		$post_types = $lwp->post->post_types;
		if($lwp->event->is_enabled()){
			$post_types[] = $lwp->event->post_type;
		}
		if($lwp->subscription->is_enabled()){
			$post_types[] = $lwp->subscription->post_type;
		}
		$book = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE ID = %d", $id));
		if(!$book || false === array_search($book->post_type, $post_types)){
			//If specified content doesn't exist, die.
			$this->kill($this->_("No content is specified."), 404);
		}
		//If ticket is specified, check selling limit
		if($book->post_type == $lwp->event->post_type){
			$selling_limit = get_post_meta($book->post_parent, $lwp->event->meta_selling_limit, true);
			if($selling_limit && preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $selling_limit)){
				//Selling limit is found, so check if it's oudated
				$limit = strtotime($selling_limit.' 23:59:59');
				$current = current_time('timestamp');
				if($limit < $current){
					$this->kill($this->_("Selling limit has been past. There is no ticket available."), 404);
				}
			}
			//Check if stock is enough
			$stock = lwp_get_ticket_stock(false, $book);
			if($stock <= 0){
				$this->kill($this->_("Sorry, but this ticket is sold out."), 403);
			}
		}
		return $book;
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
				$handle = str_replace('_', '-', str_replace('handle_', '', $method));
				if(false === array_search($handle, array('gmo-payment', 'sb-payment', 'ticket-awaiting-deregister'))){
					$methods[] = $handle;
				}
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
			case 'payment':
				$meta_title = $this->_('Payment Information');
				break;
			case 'payment-info':
				$meta_title = $this->_('Payment Information Detail');
				break;
			case 'refund-account':
				$meta_title = $this->_('Refund Account');
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
			case 'event-tickets-awaiting':
				$meta_title = $this->_('Waiting for ticket cancellation');
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
			case 'sb-check':
				$meta_title = $this->_('Softbank Payment Service Notification Check');
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
			case 'payment':
			case 'payment-info':
			case 'refund-account':
			case 'sb-payment':
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
			case 'ticket-awaiting':
				return 'event-tickets-awaiting';
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
			case 'payment':
				return $this->_('Display form to fulfill payment information like Web CVS, CreditCard and so on.');
				break;
			case 'payment-info':
				return $this->_('Display currently quueued transaction. Especially for Web CVS or PayEasy.');
				break;
			case 'refund-account':
				return $this->_('Display form of refund account which is required to complete refund process.');
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
			case 'ticket-awaiting':
				return $this->_('Displayed when user choose to wait for cancellation.');
				break;
			case 'sb-payment':
				return $this->_('Check endpoint availability. Use only on sandbox.');
				break;
			default:
				return '';
				break;
		}
	}
}