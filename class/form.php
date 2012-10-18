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
			'owned_subscription' => $lwp->subscription->is_subscriber() ? $lwp->subscription->get_subscription_owned_by() : array(),
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
								WHERE user_id = %d AND book_id = %d AND status = %s AND DATE_ADD(registered, INTERVAL %d DAY) > NOW()
EOS;
							$transaction = $wpdb->get_row($wpdb->prepare($sql, get_current_user_id(), $book_id, LWP_Payment_Status::START, $lwp->option['notification_limit']));
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
						$invnum = sprintf("{$lwp->option['slug']}-%08d-%05d-%d", $book_id, get_current_user_id(), time());
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
							if(($tran_id = lwp_is_user_waiting($book_id, get_current_user_id()))){
								unset($data['registered']);
								array_pop($where);
								$wpdb->update($lwp->transaction, $data, array('ID' => $tran_id), $where, array('%d'));
								do_action('lwp_update_transaction', $tran_id);
							}else{
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
					case 'sb-cc':
					case 'sb-cvs':
					case 'sb-payeasy':
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
			'sb-payeasy' => $lwp->softbank->payeasy
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
		$vars = $lwp->softbank->get_default_payment_info(get_current_user_id(), $_REQUEST['lwp-method']);
		if($is_sandbox){
			//Get random post
			$book = $this->get_random_post();
			if($book){
				//Find random post, show form
				$item_name = $this->get_item_name($book);
				$price = lwp_price($book->ID);
				$vars['limit'] = date(get_option('date_format'), time() + 60 * 60 * 24 * 59);
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
		$vars['limit'] = date_i18n(get_option('date_format'), $lwp->softbank->get_payment_limit($book, false, $payment_method));
		//Do transaction
		if(isset($_REQUEST['_wpnonce'])){
			$sb_cvs = $sb_payeasy = false;
			if(wp_verify_nonce($_REQUEST['_wpnonce'], 'lwp_payment_sb_cc')){
				//Softbank Credit card
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
				//All Green Make request
				if(empty($error) && ($transaction_id = $lwp->softbank->do_credit_authorization(get_current_user_id(), $item_name, $book_id, $price, 1, $cc_number, $cc_sec, $cc_year.$cc_month))){
					do_action('lwp_update_transaction', $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$lwp->transaction} WHERE transaction_id = %s", $transaction_id)));
					header('Location: '.lwp_endpoint('success')."&lwp-id={$book_id}");
					die();
				}else{
					$error[] = $this->_('Failed to make transaction.').':'.$lwp->softbank->last_error;
				}
				if(!empty($error)){
					$vars = compact('cc_number', 'cc_month', 'cc_year', 'cc_sec');
				}
			}elseif(
				($sb_cvs = wp_verify_nonce ($_REQUEST['_wpnonce'], 'lwp_payment_sb_cvs'))
					||
				($sb_payeasy = wp_verify_nonce ($_REQUEST['_wpnonce'], 'lwp_payment_sb_payeasy'))
			){
				//Softbank Payeasy or CVS
				//Let's validate
				if($sb_cvs){
					if(!isset($_REQUEST['sb-cvs-name']) || false === array_search($_REQUEST['sb-cvs-name'], $lwp->softbank->get_available_cvs())){
						$error[] = $this->_('CVS is not specified. please select one.');
					}else{
						$vars['cvs'] = $_REQUEST['sb-cvs-name'];
					}
				}
				//shold be specified format
				$zip = $this->convert_numeric($_REQUEST['zipcode']);
				if(!preg_match("/^[0-9]{7}$/", $zip)){
					$error[] = $this->_('Zip code is required and must be 7 digits.');
				}
				$tel = $this->convert_numeric($_REQUEST['tel']);
				if(!preg_match("/^[0-9]{9,11}$/", $tel)){
					$error[] = $this->_('Tel number is required and must be 9 - 11 digits.');
				}
				//not empty
				foreach(array(
					'last_name' => $this->_('Last Name'),
					'first_name' => $this->_('First Name'),
					'prefecture' => $this->_('Prefecture'),
					'city' => $this->_('City'),
					'street' => $this->_('Street')
				) as $name => $value){
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
				foreach(array('last_name', 'first_name', 'prefecture', 'city', 'street', 'office') as $key){
					$vars[$key] = $_REQUEST[$key];
				}
				if($sb_cvs){
					$vars['cvs'] = $_REQUEST['sb-cvs-name'];
				}
				$vars['tel'] = $tel;
				$vars['zipcode'] = $zip;
				$vars['last_name_kana'] = $last_name_kana;
				$vars['first_name_kana'] = $first_name_kana;
				//Make transaction
				if(empty($error)){
					//Try to save userdata
					if($sb_cvs){
						$transaction_id = $lwp->softbank->do_cvs_authorization(get_current_user_id(), $item_name, $book_id, $price, 1, $vars); 
					}elseif($sb_payeasy){
						
					}
					if(($transaction_id)){
						if(isset($_REQUEST['save_info']) && $_REQUEST['save_info']){
							$lwp->softbank->save_payment_info(get_current_user_id(), $vars, $payment_method);
						}
						//Send mail
						header('Location: '.lwp_endpoint('payment-info').'&transaction='.$transaction_id);
						die();
					}else{
						$error[] = $this->_('Failed to make transaction.').':'.$lwp->softbank->last_error;
					}
				}
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
		$methods = implode(', ', array_map(create_function('$m', 'return "\'".$m."\'";'), array(LWP_Payment_Methods::SOFTBANK_PAYEASY, LWP_Payment_Methods::SOFTBANK_WEB_CVS)));
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
		switch($transaction->method){
			case LWP_Payment_Methods::SOFTBANK_WEB_CVS:
				$data = unserialize($transaction->misc);
				$cvs = $lwp->softbank->get_verbose_name($data['cvs']);
				$howto = $lwp->softbank->get_cvs_howtos($data['cvs']);
				$methods = $lwp->softbank->get_cvs_howtos($data['cvs'], true);
				$limit_date = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "$1-$2-$3 23:59:59", $data['bill_date']);
				if($transaction->status == LWP_Payment_Status::START){
					$left_days = floor((strtotime($limit_date) - current_time('timestamp')) / 60 / 60 / 24);
					$request_suffix = sprintf($this->_(' (%d days left)'), $left_days);
					if($left_days < 1){
						$request_suffix = '<span style="color:red;">'.$request_suffix.'</span>';
					}
				}else{
					$request_suffix = '';
				}
				$rows = array(
					array($this->_('Status'), sprintf('<strong style="color:%s;">%s</strong>', $status_color, ($transaction->status == LWP_Payment_Status::START ? $this->_('Waiting for Payment') : $this->_($transaction->status) ) )),
					array($this->_('Price'), number_format($transaction->price).' '.lwp_currency_code()),
					array($this->_('Requested Date'), get_date_from_gmt($transaction->registered, get_option('date_format'))),
					array($this->_('Payment Limit'), mysql2date(get_option('date_format'), $limit_date).' '.$request_suffix),
					array($this->_('CVS'), '<i class="lwp-cvs-small-icon small-icon-'.$data['cvs'].'"></i>'.$cvs),
					array($this->_('How to pay'), $howto)
				);
				for($i = 0, $l = count($methods); $i < $l; $i++){
					$rows[] = array($methods[$i], $data['cvs_pay_data'.($i + 1)]);
				}
				break;
		}
		$this->show_form('payment-info', array(
			'item_name' => $this->get_item_name($book),
			'quantity' => $transaction->num,
			'method_name' => $this->_($transaction->method),
			'link' => $link,
			'rows' => $rows,
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
		if(
			($file->free == 0 && !$lwp->is_owner($file->book_id, get_current_user_id())) //File is production and not file owner
				||
			($file->free == 1 && !is_user_logged_in()) //File requires loogged in user but not logged in
				||
			$file->public != 1 //File is private
		){
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
			WHERE ID = %d AND user_id = %d AND status = %s
EOS;
		$transaction = $wpdb->get_row($wpdb->prepare($sql, $ticket_id, get_current_user_id(), LWP_Payment_Status::SUCCESS));
		//Check if cancelable transaction exists
		$event_id = $wpdb->get_var($wpdb->prepare("SELECT post_parent FROM {$wpdb->posts} WHERE ID = %d", $transaction->book_id));
		$current_condition = $lwp->event->get_current_cancel_condition($event_id);
		if(!$transaction || empty($current_condition)){
			$this->kill($this->_('Sorry, but the tikcet id you specified is not cancelable.'), 400);
		}
		//Get refund price
		$refund_price = lwp_ticket_refund_price($transaction);
		//Check if paypal refund is available
		if($transaction->method == LWP_Payment_Methods::PAYPAL && !PayPal_Statics::is_refundable($transaction->updated)){
			$message = apply_filters('lwp_event_paypal_outdated_refund', sprintf($this->_('Sorry, but paypal redunding is available only for 60 days. You made transaction at %1$s and it is %2$s today'), mysql2date(get_option('date_format'), $transaction->updated), date_i18n(get_option('date_format'))), $transaction, $refund_price);
			$this->kill($message, 410);
		}
		//Now, let's start refund action
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
				'limit' => date('Y-m-d'),
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
			!($event = wp_get_single_post($_GET['lwp-event']) )
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
				'message' => $lwp->event->awaiting_message
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
			'message' => $lwp->event->awaiting_message
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
		if(!user_can_edit_post(get_current_user_id(), $event->ID)){
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
		//Screen CSS
		$css = (file_exists(get_stylesheet_directory().DIRECTORY_SEPARATOR."lwp-form.css")) ? get_stylesheet_directory_uri()."/lwp-form.css" : $lwp->url."assets/lwp-form.css";
		wp_enqueue_style("lwp-form", $css, array(), $lwp->version, 'screen');
		//Print CSS
		$print_css = (file_exists(get_stylesheet_directory().DIRECTORY_SEPARATOR.'lwp-print.css')) ? get_stylesheet_directory_uri()."/lwp-print.css" : $lwp->url."assets/lwp-print.css";
		wp_enqueue_style("lwp-form-print", $print_css, array(), $lwp->version, 'print');
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
	 * Returns only numeric string
	 * @param string $string
	 * @return string
	 */
	private function convert_numeric($string){
		$string = mb_convert_kana($string, 'n', 'utf-8');
		return preg_replace("/[^0-9]/", '', $string);
	}
	
	/**
	 * Returns only kana
	 * @param string $string
	 * @return string
	 */
	private function convert_zenkaka_kana($string){
		$string = mb_convert_kana($string, 'CKV', 'utf-8');
		return preg_replace("/[^ァ-ヴー]/u", "", $string);
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
				$current = time();
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
	 * 
	 * @global Literally_WordPress $lwp
	 * @param string $post
	 * @return string
	 */
	private function get_item_name($post){
		global $lwp;
		$book = get_post($post);
		$item_name = apply_filters('the_title', $book->post_title, $book->ID);
		if($book->post_type == $lwp->event->post_type){
			$item_name = get_the_title($book->post_parent).' '.$item_name;
		}elseif($book->post_type == $lwp->subscription->post_type){
			$item_name = $this->_('Subscription').' '.$item_name;
		}
		return $item_name;
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
			case 'payment':
				$meta_title = $this->_('Payment Information');
				break;
			case 'payment-info':
				$meta_title = $this->_('Payment Information Detail');
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
			default:
				return '';
				break;
		}
	}
}