<?php

class LWP_Campaign extends Literally_WordPress_Common {
	
	/**
	 * Post meta key name of campaign
	 * @var string
	 */
	public $key_name = '_lwp_campaign_id';
	
	/**
	 * Register hooks
	 */
	public function on_construct() {
		add_action("admin_init", array($this, "update_campaign"));
		add_action("wp_ajax_lwp_campaign_list", array($this, 'campaign_list'));
		add_action('admin_notices', array($this, 'admin_notices'));
	}
	
	/**
	 * Enqueue script on admin panel
	 * @global Literally_WordPress $lwp
	 */
	public function admin_enqueue_scripts() {
		global $lwp;
		//In Campaign page, load helper
		if(isset($_REQUEST['page']) && $_REQUEST['page'] == 'lwp-campaign'){
			wp_enqueue_style('jquery-ui-datepicker');
			wp_enqueue_script('lwp-campaign-helper', $this->url.'assets/js/campaign-helper.js', array('jquery-effects-highlight'), $lwp->version);
			wp_localize_script('lwp-campaign-helper', 'LWP', array(
				'endpoint' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('lwp_campaign_list'),
				'action' => 'lwp_campaign_list',
				'confirm' => $this->_('Are you sure to delete these campaings?')
			));
		}
	}
	/**
	 * CRUD interface for Campaign
	 * @global wpdb $wpdb 
	 * @global Literally_WordPress $lwp
	 * @return void
	 */
	public function update_campaign(){
		global $wpdb, $lwp;
		if(isset($_REQUEST["_wpnonce"], $_REQUEST['page']) && $_REQUEST['page'] == 'lwp-campaign'){
			//Add campaing
			if(wp_verify_nonce($_REQUEST["_wpnonce"], "lwp_add_campaign")){
				//Check post_ids and capability
				$ids = array_map('trim', explode(',', $_REQUEST['book_id']));
				if(false !== array_search(0, $ids)){
					$lwp->error = true;
					$lwp->message[] = $this->_('ID is wrong.');
				}elseif(count($ids) > 1){
					$type = LWP_Campaign_Type::SET;
					foreach($ids as $id){
						if(!current_user_can('edit_others_posts') && $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE ID = %d AND post_author = %d", $id, get_current_user_id()))){
							$lwp->error = true;
							$lwp->message[] = sprintf($this->_('You don\'t have capability to edit %s'), get_the_title($id));
							break;
						}
					}
				}elseif(count($ids) == 1){
					$type = LWP_Campaign_Type::SINGULAR;
					if(!current_user_can('edit_others_posts') && $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE ID = %d AND post_author = %d", $ids[0], get_current_user_id()))){
						$lwp->error = true;
						$lwp->message[] = sprintf($this->_('You don\'t have capability to edit %s'), get_the_title($ids[0]));
					}
				}else{
					$type = LWP_Campaign_Type::SINGULAR;
					$lwp->error = true;
					$lwp->message[] = $this->_("Please select item.");
				}
				//Check calculation
				$calc = (false === array_search($_REQUEST['calcuration'], LWP_Campaign_Calculation::get_all()))
						? LWP_Campaign_Calculation::SPECIAL_PRICE
						: (string) $_REQUEST['calcuration'];
				//Cehck price
				$price = mb_convert_kana($_REQUEST["price"], "n");
				if(!is_numeric($price)){
					//Price is not numeric
					$lwp->error = true;
					$lwp->message[] = $this->_("Price must be numeric.");
				}else{
					switch($calc){
						case LWP_Campaign_Calculation::SPECIAL_PRICE:
							//Check price
							foreach($ids as $id){
								if($price > get_post_meta($id, $lwp->price_meta_key, true)){
									$lwp->error = true;
									$lwp->message[] = $this->_("Price is higher than original price.");
									break;
								}
							}
							break;
						case LWP_Campaign_Calculation::PERCENT:
							if($price > 100){
								$price = 100;
							}
							break;
					}
				}
				//check couopn
				
				$coupon = '';//(isset($_REQUEST['coupon']) && !empty($_REQUEST['coupon'])) ? (string)$_REQUEST['coupon'] : '';
				//Method
				$method = '';
				/*
				if(false !== array_search($_REQUEST['payment_method'], LWP_Payment_Methods::get_all_methods())){
					$method = (string)$_REQUEST['payment_method'];
				}else{
					$method = '';
				}
				*/
				//Date format
				if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/", $_REQUEST["start"]) || !preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/", $_REQUEST["end"])){
					//Date format is invalie
					$lwp->error = true;
					$lwp->message[] = $this->_("Date format is invalid.");
				}elseif(strtotime($_REQUEST["end"]) < get_current_theme('timestamp') || strtotime($_REQUEST["end"]) < strtotime($_REQUEST["start"])){
					//End dat past.
					$lwp->error = true;
					$lwp->message[] = $this->_("End date was past.");
				}
				//Check campaign existance on update.
				if(isset($_REQUEST['campaign']) && !$wpdb->get_var($wpdb->prepare("SELECT ID FROM {$lwp->campaign} WHERE ID = %d", $_REQUEST['campaign']))){
					$lwp->error = true;
					$lwp->message[] = $this->_('Specified campaign does not exists.');
				}
				//If no errors, save campaign
				if(!$lwp->error){
					$posts_arr = array(
						"book_id" => (count($ids) == 1) ? $ids[0] : 0,
						"price" => $price,
						"start" => $_REQUEST["start"],
						"end" => $_REQUEST["end"],
						'method' => $method,
						'type' => $type,
						'calculation' => $calc,
						"coupon" => $coupon
					);
					$where = array("%d", "%f", "%s", "%s", "%s", "%s", "%s", "%s");
					if(!isset($_REQUEST['campaign'])){
						//Insert
						global $wpdb;
						$wpdb->insert(
							$lwp->campaign,
							$posts_arr,
							$where
						);
						if($wpdb->insert_id){
							$campaign_id = $wpdb->insert_id;
							if($type == LWP_Campaign_Type::SET){
								foreach($ids as $id){
									update_post_meta($id, $this->key_name, $campaign_id);
								}
							}
							header('Location: '.admin_url('admin.php?page=lwp-campaign&message=1'));
							die();
						}else{
							$lwp->error = true;
							$lwp->message[] = $this->_("Failed to add campaign.");
						}
					}else{
						//Update
						$req = $wpdb->update(
							$lwp->campaign,
							$posts_arr,
							array("ID" => $_REQUEST["campaign"]),
							$where,
							array("%d")
						);
						if($req){
							$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s", $this->key_name, $_REQUEST['campaign']));
							if($type == LWP_Campaign_Type::SET){
								foreach($ids as $id){
									update_post_meta($id, $this->key_name, $_REQUEST['campaign']);
								}
							}
							header('Location: '.admin_url('admin.php?page=lwp-campaign&message=2&campaign='.$_REQUEST['campaign']));
							exit();
						}
					}
				}
			}elseif(wp_verify_nonce($_REQUEST["_wpnonce"], "bulk-campaigns") && is_array($_REQUEST["campaigns"])){
				//Delete campain
				$sql = "DELETE FROM {$lwp->campaign} WHERE ID IN (".implode(",", $_REQUEST["campaigns"]).")";
				if($wpdb->query($sql)){
					$wpdb->query($wpdb->prepare ("DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value IN (".implode(',', array_map(create_function('$val', 'return "\'".intval($val)."\'";'),$_REQUEST['campaigns'] )).")", $this->key_name));
					header("Location: ".admin_url("admin.php?page=lwp-campaign&message=3"));
					die();
				}else{
					$lwp->error = true;
					$lwp->message[] = $this->_("Failed to delete campaign.");
				}
			}
		}
	}
	
	/**
	 * Show alert on admin panel
	 */
	public function admin_notices(){
		if(isset($_REQUEST['page'], $_REQUEST['message']) && $_REQUEST['page'] == 'lwp-campaign'){
			switch($_REQUEST['message']){
				case 1:
					$message = $this->_("Campaign added.");
					break;
				case 2:
					$message = $this->_('Campaign was successfully updated.');
					break;
				case 3:
					$message = $this->_("Campaign was deleted.");
					break;
			}
			$error = isset($_REQUEST['error']) && $_REQUEST['error'] ? 'error' : 'updated';
			printf('<div class="%s"><p>%s</p></div>', $error, $message);
		}
	}
	
	/**
	 * Incremental seach for Ajax campaign list
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 */
	public function campaign_list(){
		global $wpdb, $lwp;
		if(isset($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'lwp_campaign_list') && current_user_can('edit_posts')){
			$query = isset($_REQUEST['query']) ? (string)$_REQUEST['query'] : '';
			$json = array(
				'items' => array(),
				'total' => 0,
				'query' => $query
			);
			//Creat SQL
			$query = preg_replace("/^'(.*)'$/", "'%$1%'", $wpdb->prepare("%s", $query));
			if(!current_user_can('edit_others_posts')){
				$where[] = $wpdb->prepare("(p.post_author = %d)", get_current_user_id());
			}
			if($lwp->post->is_enabled()){
				$where[] = "(p.post_type IN (".implode(',', array_map(create_function('$var', 'return "\'".$var."\'";'), $lwp->post->post_types)).") AND p.post_title LIKE {$query})";
			}
			//
			if($lwp->subscription->is_enabled()){
				$where[] = "(p.post_type = '{$lwp->subscription->post_type}' AND p.post_title LIKE {$query})";
			}
			if($lwp->event->is_enabled()){
				$where[] = "( p.post_type = '{$lwp->event->post_type}' AND (p2.post_title LIKE {$query} OR p.post_title LIKE {$query}))";
			}
			$where_clause = 'WHERE CAST(pm.meta_value AS UNSIGNED) > 0 AND ('.implode(' OR ', $where).")";
			$sql = <<<EOS
				SELECT SQL_CALC_FOUND_ROWS DISTINCT
					p.ID, p.post_title, p2.post_title AS parent_title, p.post_type
				FROM {$wpdb->posts} AS p
				LEFT JOIN {$wpdb->posts} AS p2
				ON p.post_parent = p2.ID
				LEFT JOIN {$wpdb->postmeta} AS pm
				ON p.ID = pm.post_id AND pm.meta_key = '{$lwp->price_meta_key}'
				{$where_clause}
				LIMIT 10
EOS;
			foreach($wpdb->get_results($sql) as $result){
				$item = array('ID' => $result->ID);
				if($result->post_type == $lwp->subscription->post_type){
					$item['post_title'] = $this->_('Subscription').' '.$result->post_title;
				}elseif($result->post_type == $lwp->event->post_type){
					$item['post_title'] = $result->parent_title." ".$result->post_title;
				}else{
					$item['post_title'] = $result->post_title;
				}
				$item['price'] = number_format_i18n(lwp_original_price($result->ID));
				$json['items'][] = $item;
			}
			$json['total'] = intval($wpdb->get_var('SELECT FOUND_ROWS()'));
			header('Content-Type: application/json');
			echo json_encode($json);
			die();
		}
	}
	
	
	
	/**
	 * Get campaign data of specified post
	 * 
	 * @param int $post_id
	 * @param string $time DATETIME string
	 * @param boolean $multi
	 * @return object|array 
	 */
	public function get_campaign($post_id, $time = false, $multi = false){
		global $wpdb, $lwp;
		$sql = <<<EOS
			SELECT *
			FROM {$lwp->campaign} AS c
			LEFT JOIN {$wpdb->postmeta} AS pm
			ON pm.meta_key = %s AND c.ID = CAST(pm.meta_value AS UNSIGNED)
EOS;
		$wheres = array(
			$wpdb->prepare("(c.book_id = %d OR pm.post_id = %d)", $post_id, $post_id)
		);
		if($time){
			$wheres[] = $wpdb->prepare("c.start <= %s", $time);
			$wheres[] = $wpdb->prepare("c.end >= %s", $time);
		}
		$sql .= " WHERE ".implode(' AND ', $wheres);
		$sql .= " ORDER BY c.`end` DESC";
		$campaigns = $wpdb->get_results($wpdb->prepare($sql, $this->key_name));
		$prices = array();
		$original_price = lwp_original_price($post_id);
		foreach($campaigns as $c){
			$prices[$c->ID] = $this->calculate($original_price, $c->price, $c->calculation);
		}
		arsort($prices);
		$new_order = array();
		foreach($prices as $index => $price){
			foreach($campaigns as $c){
				if($c->ID == $index){
					if(!$multi){
						return $c;
					}else{
						$new_order[] = $c;
					}
				}
			}
		}
		return $new_order;
	}
	
	
	/**
	 * Get post ids relation with campaign
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param int $campaign_id
	 * @return array
	 */
	public function get_campaign_posts($campaign_id){
		global $wpdb, $lwp;
		$sql = <<<EOS
			SELECT post_id FROM {$wpdb->postmeta}
			WHERE meta_key = %s AND meta_value = %d
			ORDER BY post_id ASC
EOS;
		$resutls = $wpdb->get_results($wpdb->prepare($sql, $this->key_name, $campaign_id));
		$ids = array();
		foreach($resutls as $result){
			$ids[] = intval($result->post_id);
		}
		return $ids;
	}
	
	/**
	 * Returns if specified post has campaign
	 * 
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param object|int $post
	 * @param string $time (optional) DATETIME string
	 * @return booelan
	 */
	public function is_on_sale($post = null, $time = null){
		global $wpdb, $lwp;
		$post = get_post($post);
		if(!$time){
			$time = date_i18n('Y-m-d H:i:s');
		}
		$sql = <<<EOS
			SELECT DISTINCT c.ID FROM {$lwp->campaign} AS c
			LEFT JOIN {$wpdb->postmeta} AS pm
			ON pm.meta_key = %s AND c.ID = CAST(pm.meta_value AS UNSIGNED)
			WHERE (c.book_id = %d OR pm.post_id = %d)
			  AND c.start <= %s AND c.end >= %s
EOS;
		$query = $wpdb->prepare($sql, $this->key_name, $post->ID, $post->ID, $time, $time);
		$result = $wpdb->get_var($query);
		return (boolean)$result;
	}
	
	/**
	 * Get campaign adopted price
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param int $post_id
	 * @param string $time DATETIME string
	 * @return int
	 */
	public function get_sale_price($post_id, $time = false){
		global $wpdb, $lwp;
		if(!$time){
			$time = date('Y-m-d H:i:s');
		}
		$sql = <<<EOS
			SELECT DISTINCT c.* FROM {$lwp->campaign} AS c
			LEFT JOIN {$wpdb->postmeta} AS pm
			ON pm.meta_key = %s AND c.ID = CAST(pm.meta_value AS UNSIGNED)
			WHERE (c.book_id = %d OR pm.post_id = %d)
			  AND c.start <= %s AND c.end >= %s
EOS;
		$campaigns = $wpdb->get_results($wpdb->prepare($sql, $this->key_name, $post_id, $post_id, $time, $time));
		$price = lwp_original_price($post_id);
		$sale_prices = array();
		foreach($campaigns as $c){
			$sale_prices[] = $this->calculate($price, $c->price, $c->calculation);
		}
		sort($sale_prices);
		return $sale_prices[0];
	}
	
	/**
	 * Calculate sale price
	 * @param float $price
	 * @param int $campaing_price
	 * @param string $type
	 * @return float
	 */
	private function calculate($price, $campaing_price, $type){
		switch($type){
			case LWP_Campaign_Calculation::DISCOUNT:
				$sale_price = $price - $campaing_price;
				break;
			case LWP_Campaign_Calculation::PERCENT:
				$sale_price = round($price / 100 * $campaing_price);
				break;
			case LWP_Campaign_Calculation::SPECIAL_PRICE:
				$sale_price = $campaing_price;
				break;
		}
		return (float)max(0, min($price, $sale_price));
	}
}