<?php
/**
 * Manage LWP's form action and display form
 *
 * Only handle_* method can be implemented.
 * All methods must be protected.
 * 
 * @since 0.9.1
 */
class LWP_Form extends LWP_Form_Event{
	
	
	
	
	/**
	 * Handle request to price list. 
	 * @param boolean $is_sandbox
	 */
	protected function handle_pricelist($is_sandbox = false){
		$this->handle_subscription($is_sandbox, false);
	}
	
	
	
	
	/**
	 * Handle request to subscription list 
	 * 
	 * @param boolean $is_sandbox
	 * @param boolean $is_subscription
	 */
	protected function handle_subscription($is_sandbox = false, $is_subscription = true){
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
	 * Handle buy action for single product
	 * 
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param boolean $is_sandbox 
	 */
	protected function handle_buy($is_sandbox = false){
		global $wpdb, $lwp;
		//First of all, user must be logged in.
		$this->kill_anonymous_user(false);
		//If it's sandbox, just show form
		if($is_sandbox){
			//Get random post
			$book = $this->get_random_post();
			if($book){
				//Find random post, show form
				$item_name = $this->get_item_name($book);
				$price = lwp_price($book->ID);
				$this->show_form('selection', array(
					'products' => array($book),
					'payments' => LWP_Payment_Methods::get_form_elements(array($book)),
					'current' => 1,
					'total' => 3,
				));
			}else{
				//Post not found
				$this->kill($this->_('Mmm... Cannot find product. Please check if you have payable post.'), 404);
			}
		}
		
		// Get current product
		$book = $this->get_current_product();
		$price = lwp_price($book);
		$this->test_post_id($book);
		// Test quantity
		if(!$this->test_current_quantity($book)){
			$this->kill($this->_('Item quantity is wrong. Please go back and select item quantity.'), 500);
		}
		//Let's do action hook to delegate transaction to other plugin
		do_action('lwp_before_transaction', $book);
		
		// All green, start transaction
		if($price == 0){
			//Content is free
			$data = array(
				"user_id" => get_current_user_id(),
				"book_id" => $book->ID,
				"status" => LWP_Payment_Status::SUCCESS,
				"price" => 0,
				'num' => $this->get_current_quantity($book),
				"method" => LWP_Payment_Methods::CAMPAIGN,
				"registered" => gmdate('Y-m-d H:i:s'),
				"updated" => gmdate('Y-m-d H:i:s'),
				'expires' => lwp_expires_date($book->ID)
			);
			$where = array("%d", "%d", "%d", "%s", "%s", "%s", "%s", "%s");
			if(($tran_id = lwp_is_user_waiting($book, get_current_user_id()))){
				unset($data['registered']);
				array_pop($where);
				$wpdb->update($lwp->transaction, $data, array('ID' => $tran_id), $where, array('%d'));
				do_action('lwp_update_transaction', $tran_id);
			}else{
				$wpdb->insert( $lwp->transaction, $data, $where );
				do_action('lwp_update_transaction', $wpdb->insert_id);
			}
			//Redirect to success page
			header("Location: ".  lwp_endpoint('success', array('lwp-id' => $book->ID)));
			exit;
		}else{
			// Current step
			$total = $book->post_type == $lwp->subscription->post_type ? 4 : 3;
			$current = $book->post_type == $lwp->subscription->post_type ? 2 : 1;
			$item_name = $this->get_item_name($book);
			//Start Transaction
			if((!isset($_REQUEST['_wpnonce'], $_REQUEST['lwp-method']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'lwp_buynow'))){
				//Select Payment Method and show form
				$this->show_form('selection', array(
					'products' => array($book),
					'current' => $current,
					'total' => $total,
					'payments' => LWP_Payment_Methods::get_form_elements(array($book))
				));
			}else{
				// User selected payment method and start transaction
				// Test payment method
				$posted_method = LWP_Payment_Methods::get_current_method();
				$method = LWP_Payment_Methods::test($posted_method, array($book));
				// Payment method is OK. Let's start transaction.
				switch($method){
					case LWP_Payment_Methods::TRANSFER:
						if($lwp->option['transfer']){
							//Check if there is active transaction
							$sql = <<<EOS
								SELECT * FROM {$lwp->transaction}
								WHERE user_id = %d AND book_id = %d AND status = %s AND method = %s AND DATE_ADD(registered, INTERVAL %d DAY) > NOW()
EOS;
							$transaction = $wpdb->get_row($wpdb->prepare($sql, get_current_user_id(), $book->ID, LWP_Payment_Status::START, LWP_Payment_Methods::TRANSFER, $lwp->option['notification_limit']));
							if(!$transaction){
								//Register transaction with transfer
								$data = array(
									"user_id" => get_current_user_id(),
									"book_id" => $book->ID,
									"price" => lwp_price($book->ID) * $fixed_quantity,
									"transaction_key" => sprintf("%08d", get_current_user_id()),
									"status" => LWP_Payment_Status::START,
									"method" => LWP_Payment_Methods::TRANSFER,
									'num' => $fixed_quantity,
									"registered" => gmdate('Y-m-d H:i:s'),
									"updated" => gmdate('Y-m-d H:i:s')
								);
								$where = array("%d", "%d", "%d", "%s", "%s", "%s", '%d', "%s", "%s");
								if((($tran_id = lwp_is_user_waiting($book->ID, get_current_user_id())))){
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
							if($wpdb->get_var($wpdb->prepare("SELECT post_type FROM {$wpdb->posts} WHERE ID = %d", $book->ID)) == $this->subscription->post_type){
								$url = $this->subscription->get_subscription_archive();
							}elseif($book->post_type == $lwp->event->post_type){
								$url = get_permalink($book->post_parent);
							}else{
								$url = get_permalink($book->ID);
							}
							//Show Form
							$this->show_form('transfer', array(
								'post_id' => $book->ID,
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
					case LWP_Payment_Methods::PAYPAL:
						//Get token
						$invnum = sprintf("{$lwp->option['slug']}-%08d-%08d-%d", $book->ID, get_current_user_id(), current_time('timestamp'));
						//Check is physical
						switch($book->post_type){
							case $lwp->event->post_type:
								$physical = true;
								break;
							default:
								$physical = false;
								break;
						}
						$fixed_quantity = $this->get_current_quantity($book);
						$token = PayPal_Statics::get_transaction_token($fixed_quantity * $price, $invnum, lwp_endpoint('confirm'), lwp_endpoint('cancel'),
								false, array(array(
									'name' => $item_name,
									'amt' => $price,
									'quantity' => $fixed_quantity,
									'physical' => $physical,
									'url' => get_permalink($book->ID)
								)));
						if($token){
							//Get token, save transaction
							$data = array(
								"user_id" => get_current_user_id(),
								"book_id" => $book->ID,
								"price" => $price * $fixed_quantity,
								"status" => LWP_Payment_Status::START,
								"method" => LWP_Payment_Methods::PAYPAL,
								'num' => $fixed_quantity,
								"transaction_key" => $invnum,
								"transaction_id" => $token,
								"registered" => gmdate('Y-m-d H:i:s'),
								"updated" => gmdate('Y-m-d H:i:s')
							);
							$where = array("%d", "%d", "%d", "%s", "%s", '%d', "%s", "%s", "%s", "%s");
							$sql = <<<EOS
								SELECT ID FROM {$lwp->transaction}
								WHERE transaction_key = %s
EOS;
							if($wpdb->get_var($wpdb->prepare($sql, $invnum))){
								//If invnum is same as a recent transaction,
								//This transaction may be created in very few seconds.
								//Nothing to do and just skip.
							}elseif(($tran_id = lwp_is_user_waiting($book->ID, get_current_user_id()))){
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
					case LWP_Payment_Methods::NTT_EMONEY:
					case LWP_Payment_Methods::NTT_CC:
					case LWP_Payment_Methods::NTT_CVS:
						$args = array('lwp-method' => $posted_method, 'lwp-id' => $book->ID);
						if(isset($_REQUEST['quantity']) && is_array($_REQUEST['quantity'])){
							foreach($_REQUEST['quantity'] as $id => $quantity){
								if($quantity > 1){
									$args['quantity['.$id.']'] = $quantity;
								}
							}
						}
						header('Location: '.lwp_endpoint('chocom', $args));
						exit;
						break;
					case LWP_Payment_Methods::SOFTBANK_PAYEASY:
					case LWP_Payment_Methods::SOFTBANK_WEB_CVS:
						if(!$lwp->softbank->can_pay_with($book)){
							$this->kill(sprintf($this->_('You can\'t select %s because selling limit is today.'),
								( ($method == LWP_Payment_Methods::SOFTBANK_WEB_CVS) ? $this->_('Web CVS') : 'PayEasy')
							), 403);
						}else{
							header('Location: '.lwp_endpoint('payment', array('lwp-method' => $posted_method, 'lwp-id' => $book->ID)));
							exit();
						}
						break;
					case LWP_Payment_Methods::GMO_WEB_CVS:
					case LWP_Payment_Methods::GMO_PAYEASY:
					case LWP_Payment_Methods::GMO_CC:
					case LWP_Payment_Methods::SOFTBANK_CC:
						header('Location: '.lwp_endpoint('payment', array('lwp-method' => $posted_method, 'lwp-id' => $book->ID)));
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
	protected function handle_payment($is_sandbox = false){
		global $lwp, $wpdb;
		// Shut down not logged in user.
		$this->kill_anonymous_user();
		//
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
					'method' => LWP_Payment_Methods::SOFTBANK_CC,
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
		// Get post
		// TODO: カートのときにどうするか
		$book_id = isset($_REQUEST['lwp-id']) ? intval($_REQUEST['lwp-id']) : 0;
		// Test content
		$book = $this->test_post_id($book_id);
		// Get method
		$posted_method = isset($_REQUEST['lwp-method']) ? $_REQUEST['lwp-method'] : '';
		$payment_method = LWP_Payment_Methods::test($posted_method, array($book));
		if(!$payment_method){
			$this->kill($this->_('Specified payment method doesn\'t exist.'), 404, true);
		}
		//Set default values.
		$error = array();
		switch($payment_method){
			case LWP_Payment_Methods::SOFTBANK_CC:
			case LWP_Payment_Methods::SOFTBANK_PAYEASY:
			case LWP_Payment_Methods::SOFTBANK_WEB_CVS:
				$vars = $lwp->softbank->get_default_payment_info(get_current_user_id(), $payment_method);
				break;
			case LWP_Payment_Methods::GMO_CC:
			case LWP_Payment_Methods::GMO_PAYEASY:
			case LWP_Payment_Methods::GMO_WEB_CVS:
				$vars = $lwp->gmo->get_default_payment_info(get_current_user_id(), $payment_method);
				break;
			default:
				$vars = array();
				break;
		}
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
				case LWP_Payment_Methods::GMO_CC:
				case LWP_Payment_Methods::SOFTBANK_CC:
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
								header('Location: '.lwp_endpoint('success', array('lwp-id' => $book_id)));
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
				case LWP_Payment_Methods::GMO_WEB_CVS:
				case LWP_Payment_Methods::GMO_PAYEASY:
				case LWP_Payment_Methods::SOFTBANK_PAYEASY:
				case LWP_Payment_Methods::SOFTBANK_WEB_CVS:
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
								header('Location: '.lwp_endpoint('payment-info', array('transaction' => $transaction_id)));
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
			'link' => lwp_endpoint('buy', array('lwp-id' => $book_id)),
			'current' => 3,
			'total' => 4
		));
	}
	
	
	/**
	 * 
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 */
	protected function handle_chocom(){
		global $lwp, $wpdb;
		// Check product
		$products = $this->get_current_products();
		if(empty($products)){
			$this->kill($this->_('No item is selectd.'), 404);
		}
		// Test payment method
		$method = LWP_Payment_Methods::get_current_method();
		if(!LWP_Payment_Methods::test($method, $products) || !LWP_Payment_Methods::is_chocom($method)){
			$this->kill($this->_('You specified wrong payment method.'), 403);
		}
		// Test Quantity
		foreach($products as $product){
			if(!$this->test_current_quantity($product)){
				$this->kill($this->_('Item quantity is wrong. Please go back and select item quantity.'), 500);
			}
		}
		// Everything OK.
		$msg = $lwp->ntt->get_instruction($method);
		if(count($products) > 1){
			// TODO: 商品を複数買う場合
		}else{
			$back_link = lwp_endpoint('buy', array('lwp-id' => $products[0]->ID));
		}
		$this->add_script_label('onError', $this->_('Failed to connect to server. Please try again later.'));
		$this->show_form('chocom', array(
			'action' => admin_url('admin-ajax.php'),
			'products' => $products,
			'method' => $method,
			'message' => $msg,
			'link' => $back_link,
			'total' => 3,
			'current' => 2
		));
	}
	
	
	/**
	 * Retrieve chocom cancel and error request
	 * 
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 */
	protected function handle_chocom_cancel(){
		global $lwp, $wpdb;
		// Force user to login.
		$this->kill_anonymous_user(false);
		// Check transaction
		if(!($transaction = $lwp->ntt->get_transaction_by_request())){
			$this->kill($this->_('Sorry, but specified transaction does not exist.'), 404);
		}
		// OK check query.
		$update = false;
		switch($transaction->status){
			case LWP_Payment_Status::START:
				$msg = $this->_('Your transaciton is successfully canceled.');
				$update = true;
				break;
			case LWP_Payment_Status::SUCCESS:
				$msg = sprintf($this->_('You seem to have finished this transaction, so your transaction hasn\'t been canceled. To cancel it, please contact to the administrator and calim for it with transaction id: <strong>%s</strong>'), $transaction->transaction_key);
				$date = date_i18n('Y-m-d(D) H:i:s', current_time('timestamp'));
				$transaction_detail = var_export($transaction, true);
				$user = var_export(wp_get_current_user(), true);
				$message = <<<EOS
サイト管理者様

一度完了した決済がキャンセルされようとしました。
{$date}

調査のため、下記データを保存しておいてください。

-----------------------------

【決済データ】
{$transaction_detail}

【ユーザー情報】
{$user}

【サーバ情報】
URL
{$_SERVER['REQUEST_URI']}
メソッド
{$_SERVER['REQUEST_METHOD']}
IPアドレス
{$_SERVER["REMOTE_ADDR"]}
リファラ
{$_SERVER['HTTP_REFERER']}

-----------------------------

EOS;
				wp_mail(get_option('admin_email'), '決済に対する不正なキャンセルが試みられました', $message);
				break;
			case LWP_Payment_Status::CANCEL:
				$msg = $this->_('Your transaction satatus is already canceled. Nothing done.');
				break;
			default:
				$msg = sprintf($this->_('Your tarnsaction status is %s, so you can\'t change anything.'), $this->_($transaction->status));
				break;
		}
		if($update){
			// Do update transaction
			$updated = $wpdb->update($lwp->transaction, array(
				'status' => LWP_Payment_Status::CANCEL,
				'updated' => gmdate('Y-m-d H:i:s')
			), array(
				'ID' => $transaction->ID
			), array('%s', '%s'), array('%d'));
			if($updated){
				do_action('lwp_update_transaction', $transaction->ID);
			}else{
				$msg = sprintf($this->_('Failed to update transaction. Please contact to the administrator of <a href="%s">%s</a>.',
						home_url('/', 'http'), get_bloginfo('name')));
			}
		}
		$link = lwp_history_url();
		$link_text = $this->_("Go to Payment Histroy");
		// This is error sequence
		if(isset($_REQUEST['error'])){
			$msg = $lwp->ntt->get_error_msg($_REQUEST['error']).
					sprintf('<br />しばらくたってもエラーが発生する場合は、管理者までお問い合わせください。<br />お問い合わせ番号：<strong>%s</strong>', $transaction->transaction_key);
			if($transaction->book_id > 0){
				$link = lwp_endpoint('buy', array('lwp-id' => $transaction->book_id));
				$link_text = $this->_('Return');
			}
		}
		$this->show_form("chocom-cancel", array(
			'message' => $msg,
			'link' => $link,
			'link_text' => $link_text,
			'total' => 3,
			'current' => 3
		));
	}
	
	
	
	
	/**
	 * Handle request
	 * 
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 */
	protected function handle_chocom_result(){
		global $lwp;
		// Force user to login.
		$this->kill_anonymous_user(false);
		// Check transaction.
		if(!($transaction = $lwp->ntt->get_transaction_by_request())){
			$this->kill($this->_('Sorry, but specified transaction does not exist.'), 404);
		}
		// Check transaction
		$redirect_url = $lwp->ntt->finish_transaction($transaction);
		if($redirect_url){
			header('Location: '.$redirect_url);
			exit;
		}else{
			$this->kill(sprintf($this->_('Sorry, but your transaction has failed. Please contact to administrator of <a href="%s">%s</a>.'), home_url('', 'http'), get_bloginfo('name') ),500, false);
		}
	}



	/**
	 * 
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param boolean $is_sandbox
	 */
	protected function handle_payment_info($is_sandbox = true){
		global $lwp, $wpdb;
		$this->kill_anonymous_user();
		$transaction_id = isset($_REQUEST['transaction']) ? intval($_REQUEST['transaction']) : 0;
		$methods = implode(', ', array_map(create_function('$m', 'return "\'".$m."\'";'), array(LWP_Payment_Methods::SOFTBANK_PAYEASY, LWP_Payment_Methods::SOFTBANK_WEB_CVS, LWP_Payment_Methods::TRANSFER, LWP_Payment_Methods::GMO_WEB_CVS, LWP_Payment_Methods::GMO_PAYEASY, LWP_Payment_Methods::NTT_CVS)));
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
				$status_color = 'darkgray';
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
					$request_suffix = sprintf($this->_(' (%d days past)'), number_format_i18n(absint($left_days)));
				}else{
					$request_suffix = sprintf($this->_(' (%d days left)'), number_format_i18n($left_days));
				}
				if($left_days < 3){
					$request_suffix = '<span style="color:red;">'.$request_suffix.'</span>';
				}
			}else{
				$request_suffix = '';
			}
			$rows[] = array($this->_('Payment Limit'), mysql2date(get_option('date_format'), $limit_date).' '.$request_suffix);
			switch($transaction->method){
				case LWP_Payment_Methods::NTT_CVS:
					$label = $lwp->ntt->get_cvs_code_label($data['cvs_name']);
					$rows =  array_merge($rows, array(
						array($this->_('CVS'), sprintf('<label class="cvs-container"><i class="lwp-cvs-small-icon small-icon-%s"></i><br />%s</label>',
								$data['cvs_name'], $lwp->ntt->get_verbose_name($data['cvs_name']))),
						array($this->_('How to pay'), nl2br($lwp->ntt->get_cvs_howtos($data['cvs_name']))),
					));
					if($data['cvs_name'] == 'familymart'){
						$rows[] = array('注意点', '「企業コード」「注文番号」はちょコムより送信された受付完了メールをご確認下さい。');
					}else{
						$rows[] = array($label[0], $data['receipt_no']);
					}
					break;
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
	protected function handle_confirm($is_sandbox = false){
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
			if(empty($message)){
				//Get itemss
				$transactions = $lwp->get_transaction_items($transaction);
				$items = array();
				$include_subscription = false;
				foreach($transactions as $tran){
					$post = get_post($tran->book_id);
					if($post->post_type == $lwp->subscription->post_type){
						$include_subscription = true;
					}
					$item_data = array(
						'post_id' => $tran->book_id,
						'price' => $tran->price,
						'quantity' => $tran->num
					);
					switch($post->post_type){
						case $lwp->event->post_type:
							$item_data['name'] = get_the_title($post->post_parent).' '.$post->post_title;
							break;
						case $lwp->subscription->post_type:
							$item_data['name'] = $this->_('Subscription').' '.$post->post_title;
							break;
						default:
							$item_data['name'] = $post->post_title;
							break;
					}
					$items[] = $item_data;
				}
				$post = get_post($transaction->book_id);
				$this->show_form("return", array(
					"info" => $info,
					"transaction" => $transaction,
					"items" => $items,
					'total' => $include_subscription ? 4 : 3,
					'current' => $include_subscription ? 3 : 2,
				));
			}else{
				$this->kill(apply_filters('lwp_confirm_failure_message', $message.sprintf($this->_(' Please contact to Administrator of <strong>%s</strong>'), get_bloginfo('name'))), 500);
			}
		}else{
			// Check if response is valid or not.
			// Sometimes, it returns wrong status
			if(($response = PayPal_Statics::do_transaction($_POST)) && ($status = PayPal_Statics::transaction_result_status($response))){
				//データを更新
				$transaction_id = $response['PAYMENTINFO_0_TRANSACTIONID'];
				$post_id = $wpdb->get_var($wpdb->prepare("SELECT book_id FROM {$lwp->transaction} WHERE transaction_key = %s", $_POST["INVNUM"])); 
				$sql = <<<EOS
					UPDATE {$lwp->transaction}
					SET status = %s, transaction_id = %s, payer_mail = %s, updated = %s, expires = %s
					WHERE user_id = %d AND transaction_key = %s
					LIMIT 1
EOS;
				$wpdb->query($wpdb->prepare(
						$sql, 
						$status, $transaction_id, $_POST['EMAIL'], current_time('mysql', true), lwp_expires_date($post_id),
						get_current_user_id(), $_POST['INVNUM']));
				//Do action hook on transaction updated
				do_action('lwp_update_transaction', $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$lwp->transaction} WHERE user_id = %d AND transaction_id = %s LIMIT 1", get_current_user_id(), $transaction_id)));
				// Go to thank you page.
				header("Location: ".  lwp_endpoint('success', array('lwp-id' => $post_id, 'status' => $status))); 
				exit;
			}else{
				$this->kill(sprintf($this->_('Transaction failed to finish. Please return to previous page and try it again. If this occurs again, contact to the administrator of <a href="%1$s">%2$s</a> and confirm your current transaction status.'),
					home_url('/', 'http'), get_bloginfo('name')));
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
	protected function handle_success($is_sandbox = false){
		global $lwp, $wpdb;
		//First of all, user must be login
		$this->kill_anonymous_user();
		if($is_sandbox){
			$this->show_form('success', array(
				'link' => get_bloginfo('url'),
				'total' => 4,
				'current' => 4,
				'msg' => apply_filters('lwp_thankyou_message', $this->_("Thank you for purchasing."), LWP_Payment_Status::SUCCESS)
			));
		}
		//Change return url
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
		$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
		switch($status){
			case LWP_Payment_Status::CANCEL:
				$msg = $this->_('Your transaction has been canceled. If this is an unexpected result, please contact to the administrator of <a href="%1$s">%2$s</a>');
				break;
			case LWP_Payment_Status::WAITING_REVIEW:
				$msg = $this->_('Your transaction is now under processing or review. If this is an unexpected result, please contact to the administrator of <a href="%1$s">%2$s</a>');
				break;
			default:
				$msg = $this->_("Thank you for purchasing.");
				break;
		}
		$this->show_form("success", array(
			'link' => ($this->is_publicly_ssl() ? $url : $this->strip_ssl($url) ),
			'total' => ($post_type == $lwp->subscription->post_type) ? 4 : 3,
			'current' => ($post_type == $lwp->subscription->post_type) ? 4 : 3,
			'msg' => apply_filters('lwp_thankyou_message', $msg, $status),
		));
	}
	
	
	
	
	/**
	 * Handle cancel action
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param boolean $is_sandbox 
	 */
	protected function handle_cancel($is_sandbox = false){
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
	 * Register refund information
	 * 
	 * @global Literally_WordPress $lwp
	 * @param type $is_sandbox
	 */
	protected function handle_refund_account($is_sandbox = false){
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
}