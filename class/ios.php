<?php

class LWP_iOS extends Literally_WordPress_Common{
	
	/**
	 * API Version.
	 * @var string
	 */
	public $api_version = '2.0';
	
	/**
	 * API last updated
	 * @var string
	 */
	public $api_last_updated = '2012-10-04 16:35:20';
	
	/**
	 * Is iOS is enabled
	 * @var boolean
	 */
	private $ios_enabled = false;
	
	/**
	 * Is Android enabled
	 * @var boolean
	 */
	private $android_enabled = false;
	
	/**
	 * Public key to verify android receipt
	 * @var string 
	 */
	private $android_pub_key = '';
	
	/**
	 * Whether user can buy from public site
	 * @var boolean
	 */
	private $web_available = false;
	
	/**
	 * Post type
	 * @var string
	 */
	public $post_type = 'ios-product';
	
	/**
	 * Taxonomy Name
	 * @var string
	 */
	public $taxonomy = 'ios-product-group';
	
	/**
	 * Whether post type is public
	 * @var booelan
	 */
	private $post_type_public = false;
	
	/**
	 * whether to force ssl
	 * @var int 
	 */
	private $force_ssl = 0;
	
	/**
	 * post meta key name for product id
	 * @var string 
	 */
	public $product_id = '_lwp_ios_product_id';
	
	/**
	 * Post meta key name for android product_id
	 * @var string
	 */
	public $android_product_id = '_lwp_android_product_id';
	
	/**
	 * Returns all methods name
	 * @var array
	 */
	private $methods = array();
	
	/**
	 * @see Literally_WordPress_Common
	 */
	public function set_option($option) {
		$option = shortcode_atts(array(
			'ios' => false,
			'android' => false,
			'ios_public' => false,
			'ios_available' => false,
			'ios_force_ssl' => 0,
			'android_pub_key' => ''
		), $option);
		$this->enabled = (boolean)( $option['ios'] || $option['android']);
		$this->ios_enabled = (boolean)$option['ios'];
		$this->android_enabled = (boolean)$option['android'];
		$this->web_available = (boolean) $option['ios_available'];
		$this->post_type_public = (boolean) $option['ios_public'];
		$this->force_ssl = (int)$option['ios_force_ssl'];
		$this->android_pub_key = (string)$option['android_pub_key'];
	}
	
	/**
	 * @see Literally_WordPress_Common
	 */
	public function on_construct() {
		if($this->is_enabled()){
			add_action('init', array($this, 'register_post_type'), 20);
			add_filter('lwp_payable_post_types', array($this, 'add_post_type'));
			add_action('lwp_payable_post_type_metabox', array($this, 'edit_form'));
			add_action('save_post', array($this, 'save_post'));
			add_filter('xmlrpc_methods', array($this, 'xmlrpc_methods'));
		}
	}
	
	/**
	 * Register Post Type
	 */
	public function register_post_type(){
		//Post type
		$singular = $this->_('Smartphone Product');
		$plural = $this->_('Smartphone Products');
		$args = apply_filters('lwp_ios_post_type_args', array(
			'labels' => array(
				'name' => $plural,
				'singular_name' => $singular,
				'add_new' => $this->_('Add New'),
				'add_new_item' => sprintf($this->_('Add New %s'), $singular),
				'edit_item' => sprintf($this->_("Edit %s"), $singular),
				'new_item' => sprintf($this->_('Add New %s'), $singular),
				'view_item' => sprintf($this->_('View %s'), $singular),
				'search_items' => sprintf($this->_("Search %s"), $singular),
				'not_found' =>  sprintf($this->_('No %s was found.'), $singular),
				'not_found_in_trash' => sprintf($this->_('No %s was found in trash.'), $singular),
				'parent_item_colon' => ''
			),
			'public' => $this->post_type_public,
			'publicly_queryable' => $this->post_type_public,
			'show_ui' => true, 
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'menu_position' => 10,
			'has_archive' => true,
			'supports' => array('title','editor','author','thumbnail','excerpt', 'comments', 'custom-fields'),
			'show_in_nav_menus' => $this->post_type_public,
			'menu_icon' => $this->url."/assets/icon-iphone.png"
		));
		register_post_type($this->post_type, $args);
		
		//Taxonomy
		$singular_tax = $this->_('Product Group');
		$plular_tax = $this->_('Product Groups');
		$taxonomy_args = apply_filters('lwp_ios_taxonomy_args', array(
			'labels' => array(
				'name' => $plular_tax,
				'singular_name' => $singular_tax,
				'search_items' =>  sprintf($this->_( 'Search %s' ), $plular_tax),
				'popular_items' => sprintf($this->_( 'Popular %s' ), $plular_tax),
				'all_items' => sprintf($this->_( 'All %s' ), $plular_tax),
				'parent_item' => null,
				'parent_item_colon' => null,
				'edit_item' => sprintf($this->_( 'Edit %s' ), $singular_tax), 
				'update_item' => sprintf($this->_( 'Update %s' ), $singular_tax),
				'add_new_item' => sprintf($this->_( 'Add New %s' ), $singular_tax),
				'new_item_name' => sprintf($this->_( 'New %s Name' ), $singular_tax),
				'separate_items_with_commas' => sprintf($this->_( 'Separate %s with commas' ), $plular_tax),
				'add_or_remove_items' => sprintf($this->_( 'Add or remove %s' ), $plular_tax),
				'choose_from_most_used' => sprintf($this->_( 'Choose from the most used %s' ), $plular_tax)
			),
			'hierarchical' => false
		));
		register_taxonomy($this->taxonomy, $this->post_type, $taxonomy_args);
	}
	
	/**
	 * Add post type to sell post
	 * @param array $post_types
	 * @return array
	 */
	public function add_post_type($post_types){
		$post_types[] = $this->post_type;
		return $post_types;
	}
	
	/**
	 * Display metabox for iOS Product id
	 * @param object $post
	 */
	public function edit_form($post){
		if($post->post_type == $this->post_type){
			if($this->is_ios_available()): ?>
				<?php wp_nonce_field('lwp_ios_product_id', '_lwpnonce_ios_id'); ?>
				<hr class="lwp-divider" />
				<table class="lwp-metabox-table">
					<tbody>
						<tr>
							<th colspan="2"><label for="ios-product-id"><?php $this->e('iOS Procut ID'); ?></label></th>
						</tr>
						<tr>
							<td colspan="2"><input style="width:100%;" placeholder="com.takahashifumiki.someApp.someProduct" type="text" id="ios-product-id" name="ios-product-id" value="<?php echo esc_attr(get_post_meta($post->ID, $this->product_id, true)); ?>" /></td>
						</tr>
					</tbody>
				</table>
				<p class="description">
					<?php printf($this->_('Please enter existing Product ID managed at <a href="%s">iTunes Connect</a>'), 'https://itunesconnect.apple.com'); ?>
				</p>
			<?php endif; if($this->is_android_available()): ?>
				<?php wp_nonce_field('lwp_android_product_id', '_lwpnonce_android_id'); ?>
				<hr class="lwp-divider" />
				<table class="lwp-metabox-table">
					<tbody>
						<tr>
							<th colspan="2"><label for="android-product-id"><?php $this->e('Android Procut ID'); ?></label></th>
						</tr>
						<tr>
							<td colspan="2"><input style="width:100%;" placeholder="com.takahashifumiki.someApp.someProduct" type="text" id="android-product-id" name="android-product-id" value="<?php echo esc_attr(get_post_meta($post->ID, $this->android_product_id, true)); ?>" /></td>
						</tr>
					</tbody>
				</table>
				<p class="description">
					<?php printf($this->_('Please enter existing Product ID managed at <a href="%s">Google Play Developer Console</a>'), 'https://play.google.com/apps/publish'); ?>
				</p>
			<?php endif; 
		}
	}
	
	/**
	 * Executed on saving post
	 * @param int $post_id
	 */
	public function save_post($post_id){
		if(isset($_REQUEST['_lwpnonce_ios_id']) && wp_verify_nonce($_REQUEST['_lwpnonce_ios_id'], 'lwp_ios_product_id')){
			if(isset($_REQUEST['ios-product-id']) && !empty($_REQUEST['ios-product-id'])){
				update_post_meta($post_id, $this->product_id, (string)$_REQUEST['ios-product-id']);
			}else{
				delete_post_meta($post_id, $this->product_id);
			}
		}
		if(isset($_REQUEST['_lwpnonce_android_id']) && wp_verify_nonce($_REQUEST['_lwpnonce_android_id'], 'lwp_android_product_id')){
			if(isset($_REQUEST['android-product-id']) && !empty($_REQUEST['android-product-id'])){
				update_post_meta($post_id, $this->android_product_id, (string)$_REQUEST['android-product-id']);
			}else{
				delete_post_meta($post_id, $this->android_product_id);
			}
		}
	}
	
	/**
	 * Override xmlrpc methods
	 * @param array $methods
	 * @return array
	 */
	public function xmlrpc_methods($methods){
		//check SSL if forced
		if($this->is_ssl_forced() && !is_ssl()){
			$error = new IXR_Error(403, $this->_('XML-RPC request must be on SSL.'));
			$xml = '<?xml version="1.0"?>'."\n".$error->getXml();
			$length = strlen($xml);
			header('Connection: close');
			header('Content-Length: '.$length);
			header('Content-Type: text/xml');
			header('Date: '.date('r'));
			echo $xml;
			exit;
		}
		//Register all methods starting with ios_
		foreach(get_class_methods($this) as $method){
			//register all class method starting with 'ios_'
			if(preg_match("/^ios_/", $method) && $this->is_ios_available()){
				$xmlrpc_method = 'lwp.ios.'.preg_replace('/_(\w)/e', 'ucfirst(\\1)', preg_replace("/^ios_/", "", $method));
				$methods[$xmlrpc_method] = array($this, $method);
			}elseif(preg_match("/^android_/", $method) && $this->is_android_available()){
				$xmlrpc_method = 'lwp.android.'.preg_replace('/_(\w)/e', 'ucfirst(\\1)', preg_replace("/^android_/", "", $method));
				$methods[$xmlrpc_method] = array($this, $method);
			}elseif(preg_match("/^common_/", $method) && $this->is_enabled()){
				$method_name = preg_replace('/_(\w)/e', 'ucfirst(\\1)', preg_replace("/^common_/", "", $method));
				if($this->is_android_available()){
					$methods['lwp.android.'.$method_name] = array($this, $method);
				}
				if($this->is_ios_available()){
					$methods['lwp.ios.'.$method_name] = array($this, $method);
				}
			}
				
		}
		$this->methods = array_keys($methods);
		return $methods;
	}
	
	/**
	 * Returns registered methods
	 */
	public function common_methods(){
		return $this->methods;
	}
	
	/**
	 * Returns iOS XML-RPC Information
	 * @global Literally_WordPress $lwp
	 * @param array $args
	 * @return array
	 */
	public function common_get_api_information($args = array()){
		global $lwp, $wp_version;
		return array(
			'api_version' => $this->api_version,
			'last_updated' => $this->api_last_updated,
			'force_ssl' => $this->is_ssl_forced(),
			'lwp_version' => $lwp->version,
			'wp_version' => $wp_version
		);
	}
	
	/**
	 * Returns product's id list
	 * 
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param array $args
	 * @return array
	 */
	public function ios_product_list($args = array()){
		global $lwp, $wpdb;
		//Arguments for query posts
		$args = wp_parse_args((array)$args[0], array(
			'term_taxonomy_id' => 0,
			'orderby' => 'pm1.meta_value',
			'order' => 'ASC',
			'status' => 'publish',
			'key' => $this->product_id
		));
		//whether if file list needed
		$with_file_list = (boolean)(!isset($args[1]) || $args[1]);
		//WHERE clause
		$wheres = array(
			$wpdb->prepare('p.post_type = %s', $this->post_type),
			$wpdb->prepare("p.post_status = %s", $args['status'])
		);
		if($args['term_taxonomy_id']){
			$wheres[] = $wpdb->prepare('t.term_taxonomy_id = %d', $args['term_taxonomy_id']);
		}
		$wheres = 'WHERE '.implode(' AND ', $wheres);
		//ORDER BY clause
		switch($args['orderby']){
			case 'date':
			case 'title':
				$orderby = 'p.post_'.$args['orderby'];
				break;
			case 'ID':
				$orderby = 'p.ID';
				break;
			default:
				$orderby = $args['orderby'];
				break;
		}
		$order = $args['order'] == 'ASC' ? 'ASC' : 'DESC';
		$sql = <<<EOS
			SELECT DISTINCT
				p.ID AS post_id, p.post_title, p.post_content, p.post_excerpt,
				p.post_date, p.post_modified,
				pm1.meta_value AS product_id,
				COALESCE(pm2.meta_value, 0) AS price
			FROM {$wpdb->posts} AS p
			INNER JOIN {$wpdb->postmeta} AS pm1
			ON p.ID = pm1.post_id AND pm1.meta_key = %s
			LEFT JOIN {$wpdb->postmeta} AS pm2
			ON p.ID = pm2.post_id AND pm2.meta_key = %s
			LEFT JOIN {$wpdb->term_relationships} AS t
			ON t.object_id = p.ID
			{$wheres}
			ORDER BY {$orderby} {$order}
EOS;
		$result = $wpdb->get_results($wpdb->prepare($sql, $args['key'], $lwp->price_meta_key), ARRAY_A);
		for($i = 0, $l = count($result); $i < $l; $i++){
			$result[$i]['post_id'] = intval($result[$i]['post_id']);
			$result[$i]['price'] = (float)$result[$i]['price'];
			if($with_file_list){
				$result[$i]['files'] = $this->common_file_list($result[$i]['post_id']);
			}
		}
		return $result;
		//For debug
		/*
		return array(
			'error' => $wpdb->last_error,
			'query' => preg_replace("/(\n|\t)/", " ", $wpdb->last_query)
		);
		/**/
	}
	
	/**
	 * Returns product's id list
	 * @param array $args
	 * @return array
	 */
	public function android_product_list($args = array()){
		if(!isset($args[0])){
			$args[0] = array('key' => $this->android_product_id);
		}elseif(!is_array($args[0])){
			$args[0] = (array)$args[0];
			$args[0]['key'] = $this->android_product_id;
		}else{
			$args[0]['key'] = $this->android_product_id;
		}
		return $this->ios_product_list($args);
	}
	
	/**
	 * Returns list of product group
	 * @see http://codex.wordpress.org/Function_Reference/get_terms
	 * @param array $args same arguments as get_terms
	 * @return array
	 */
	public function common_product_group($args){
		return get_terms($this->taxonomy, (is_array($args) ? $args : '' ));
	}
	
	/**
	 * Returns file list
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param array $args
	 * @return array
	 */
	public function common_file_list($args = 0){
		global $wpdb, $lwp;
		$post_id = intval($args);
		$sql = <<<EOS
			SELECT * FROM {$lwp->files} AS f
			WHERE f.book_id = %d
			ORDER BY f.ID ASC
EOS;
		$result = $wpdb->get_results($wpdb->prepare($sql, $post_id), ARRAY_A);
		$device_query = <<<EOS
			SELECT d.name, d.slug
			FROM {$lwp->devices} AS d
			LEFT JOIN {$lwp->file_relationships} AS r
			ON d.ID = r.device_id
			WHERE r.file_id = %d
EOS;
		for($i = 0, $l = count($result); $i < $l; $i++){
			$result[$i]['ID'] = intval($result[$i]['ID']);
			$result[$i]['public'] = intval($result[$i]['public']);
			$result[$i]['free'] = (int)$result[$i]['free'];
			$result[$i]['devices'] = (array)$wpdb->get_results($wpdb->prepare($device_query, $result[$i]['ID']), ARRAY_A);
		}
		return $result;
	}
	
	/**
	 * Get file
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param array $args
	 */
	public function common_get_file($args){
		global $lwp, $wpdb;
		$file_id = isset($args[2]) ? intval($args[2]) : 0;
		$file = $lwp->post->get_files(null, $file_id);
		//Check file existance
		if(!$file || !($path = $lwp->post->get_file_path($file))){
			$this->kill($this->_('Specified file does not exist.'), 404);
		}
		switch($file->free){
			case 2:
				break;
			case 1:
				$this->xmlrpc_login($args);
				break;
			default:
				$this->xmlrpc_login($args);
				$sql = <<<EOS
					SELECT ID FROM {$lwp->transaction}
					WHERE user_id = %d AND book_id = %d AND status = %s
EOS;
				if(!$wpdb->get_var($wpdb->prepare($sql, get_current_user_id(), $file->book_id, LWP_Payment_Status::SUCCESS))){
					$this->kill($this->_('You have no purchase history. If you have one, please try restoration.'), 403);
				}
				break;
		}
		//File filter
		$path = apply_filters('lwp_file_path', $path, $file, get_current_user_id());
		if(!file_exists($path)){
			$this->kill($this->_('Specified file does not exist.'), 404);
		}
		//All green. Now you can get file
		$hash = md5_file($path);
		$size = filesize($path);
		if($size * .0009765625 * .0009765625 > 128){
			$this->kill($this->_('File is too large. You cannot get file.'), 500);
		}
		ini_set('memory_limit', '128M');
		$data = new IXR_Base64(file_get_contents($path));
		$mime = $lwp->post->detect_mime($path);
		//Save download log
		$lwp->post->save_donwload_log($file->ID);
		//All data is 
		return array(
			'hash' => $hash,
			'size' => $size,
			'mime' => $mime,
			'data' => $data
		);
	}
	
	/**
	 * Register transaction
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param array $args
	 * @return boolean
	 */
	public function ios_register_transaction($args){
		global $wpdb, $lwp;
		$this->xmlrpc_login($args);
		$receipt = $this->parse_receipt($args[2]);
		$post = $this->get_post_from_product_id($receipt->product_id);
		if(!$post){
			$this->kill(sprintf($this->_('Product ID:%s does not exists.'), $receipt->product_id), 404);
		}
		$price = isset($args[3]) ? (float)$args[3] : lwp_price($post);
		$sql = <<<EOS
			SELECT * FROM {$lwp->transaction}
			WHERE book_id = %d AND transaction_key = %s AND method = %s AND status = %s AND user_id = %d
EOS;
		$transaction = $wpdb->get_row($wpdb->prepare($sql, $post->ID, $receipt->original_transaction_id,
				LWP_Payment_Methods::APPLE, LWP_Payment_Status::SUCCESS, get_current_user_id()));
		if($transaction){
			return true;
		}else{
			$uuid = isset($args[4]) ? strval($args[4]) : '';
			$result = (boolean)$wpdb->insert($lwp->transaction, array(
				'user_id' => get_current_user_id(),
				'book_id' => $post->ID,
				'price' => $price,
				'status' => LWP_Payment_Status::SUCCESS,
				'method' => LWP_Payment_Methods::APPLE,
				'transaction_key' => $receipt->original_transaction_id,
				'transaction_id' => $uuid,
				'registered' => date('Y-m-d H:i:s', intval($receipt->original_purchase_date_ms / 1000) ),
				'updated' => gmdate('Y-m-d H:i:s'),
				'num' => $receipt->quantity
			), array('%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d') );
			if($result){
				do_action('lwp_ios_transaction_registered', 
					$wpdb->get_row($wpdb->prepare("SELECT * FROM {$lwp->transaction} WHERE ID = %d", $wpdb->insert_id)),
					$receipt);
			}
			return (boolean) $result;
		}
	}
	
	
	/**
	 * Register transaction with Receipt
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param array $args
	 * @return boolean
	 */
	public function android_register_transaction($args){
		global $wpdb, $lwp;
		$this->xmlrpc_login($args);
		if(!isset($args[2], $args[3]) || !$this->verify_json($args[2], $args[3])){
			$this->kill($this->_('JSON data is not set.'),  403);
		}
		$json = json_decode($args[2]);
		$order = $json->orders[0];
		$sql = <<<EOS
			SELECT p.* FROM {$wpdb->posts} AS p
			INNER JOIN {$wpdb->postmeta} AS pm
			ON p.ID = pm.post_id = pm.meta_key = %s
			WHERE meta_value = %s
EOS;
		$post = $wpdb->get_row($wpdb->prepare($sql, $this->android_product_id, $order->productId));
		if(!$post){
			$this->kill(sprintf($this->_('Product ID:%s does not exists.'), $order->productId), 404);
		}
		//If purchase State is not 0.
		if($json->orders->purchaseState != 0){
			return false;
		}
		$price = isset($args[4]) ? (float)$args[4] : lwp_price($post);
		$sql = <<<EOS
			SELECT * FROM {$lwp->transaction}
			WHERE book_id = %d AND transaction_key = %s AND method = %s AND status = %s AND user_id = %d
EOS;
		$transaction = $wpdb->get_row($wpdb->prepare($sql, $post->ID, $order->orderId,
				LWP_Payment_Methods::ANDROID, LWP_Payment_Status::SUCCESS, get_current_user_id()));
		if($transaction){
			return true;
		}else{
			$uuid = isset($args[5]) ? strval($args[5]) : '';
			$result = (boolean)$wpdb->insert($lwp->transaction, array(
				'user_id' => get_current_user_id(),
				'book_id' => $post->ID,
				'price' => $price,
				'status' => LWP_Payment_Status::SUCCESS,
				'method' => LWP_Payment_Methods::ANDROID,
				'transaction_key' => $order->orderId,
				'transaction_id' => $uuid,
				'payer_mail' => $order->notificationId,
				'registered' => date('Y-m-d H:i:s', intval($order->purchaseTime / 1000) ),
				'updated' => gmdate('Y-m-d H:i:s'),
				'num' => 1
			), array('%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d') );
			if($result){
				do_action('lwp_ios_transaction_registered', 
					$wpdb->get_row($wpdb->prepare("SELECT * FROM {$lwp->transaction} WHERE ID = %d", $wpdb->insert_id)),
					$json);
			}
			return (boolean) $result;
		}
	}
	
	/**
	 * Wraps registerTransaction and getFile.
	 * @param array $args
	 * @return array
	 */
	public function ios_get_file_with_receipt($args){
		if($this->ios_register_transaction($args)){
			//Transaction is OK.
			$username = $args[0];
			$password = $args[1];
			$file_id = isset($args[5]) ? intval($args[5]) : 0; 
			return $this->common_get_file(array($username, $password, $file_id));
		}else{
			//Something is wrong
			$this->kill($this->_('Failed to register transaction.'), 403);
		}
	}
	
	/**
	 * Wraps registerTransaction and getFile
	 * @param array $args
	 * @return array
	 */
	public function android_get_file_with_receipt($args){
		if($this->android_register_transaction($args)){
			//Transaction is OK.
			$username = $args[0];
			$password = $args[1];
			$file_id = isset($args[6]) ? intval($args[6]) : 0; 
			return $this->common_get_file(array($username, $password, $file_id));
		}else{
			//Something is wrong
			$this->kill($this->_('Failed to register transaction.'), 403);
		}
	}
	
	/**
	 * Returns user inforamtion
	 * @param array $args username, password
	 * @return WP_User
	 */
	public function common_get_user_info($args){
		$user = $this->xmlrpc_login($args);
		return apply_filters('lwp_xmlrpc_user', $user);
	}
	
	/**
	 * Returns user transaction for iOS
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param array $args username, password, page, method
	 * @return array
	 */
	public function ios_get_user_transactions($args){
		global $wpdb, $lwp;
		$this->xmlrpc_login($args);
		$index = isset($args[2]) ? intval($args[2]) : 1;
		$method = isset($args[3]) ? strval($args[3]) : LWP_Payment_Methods::APPLE;
		$per_page = get_option('posts_per_page');
		$offset = max(0, $index - 1) * $per_page;
		$sql = <<<EOS
			SELECT SQL_CALC_FOUND_ROWS
				transaction_key AS transaction_id, book_id AS post_id, price, status, registered, updated, num
			FROM {$lwp->transaction}
			WHERE user_id = %d AND method = %s
			ORDER BY registered DESC
			LIMIT {$offset}, {$per_page}
EOS;
		$transactions = $wpdb->get_results($wpdb->prepare($sql, get_current_user_id(), $method));
		for($i = 0, $l = count($transactions); $i < $l; $i++){
			$transactions[$i]['post_id'] = intval($transactions[$i]['post_id']);
			$transactions[$i]['price'] = (float)$transactions[$i]['price'];
			$transactions[$i]['num'] = intval($transactions[$i]['num']);
		}
		return array(
			'transactions' => $transactions,
			'total' => $wpdb->get_var("SELECT FOUND_ROWS()"),
			'page' => $index
		);
	}
	
	/**
	 * Return user transaction for Android
	 * @param array $args
	 * @return array
	 */
	public function android_get_user_transactions($args){
		if(!isset($args[3])){
			$args[3] = LWP_Payment_Methods::ANDROID;
		}
		return $this->ios_get_user_transactions($args);
	}
	
	/**
	 * Return post object from 
	 * @global wpdb $wpdb
	 * @param type $product_id
	 * @return null
	 */
	private function get_post_from_product_id($product_id){
		global $wpdb;
		$post_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s", $this->product_id, $product_id));
		if($post_id){
			return get_post($post_id);
		}else{
			return null;
		}
	}
	
	/**
	 * Get receipt parse
	 * @param string $base64_receipt Base64 encoded receipt
	 */
	private function parse_receipt($base64_receipt){
		//Try base64 decode
		$receipt = base64_decode($base64_receipt);
		if(!$receipt){
			$this->kill($this->_('Invalid Characters. Receipt must be sent as base64 encoded string.'), 400);
		}
		//Check if it is sandbox
		$endpoint = preg_match('/Sandbox/i', $receipt) ? 'https://sandbox.itunes.apple.com/verifyReceipt' : 'https://buy.itunes.apple.com/verifyReceipt';
		//Get AppStore Response
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $endpoint);
		curl_setopt ($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array('receipt-data' => $base64_receipt)));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		$response = curl_exec($ch);
		curl_close($ch);
		//Verify response
		if(!$response){
			//Timed out
			$this->kill($this->_('Request Timeout.'), 408);
		}
		//Parse request to json
		$json = json_decode($response);
		if(!$json){
			//Feiled to Parse JSON
			$this->kill($this->_('Failed to parse Apple\'s response as JSON. Please check if receipt is valid.'), 500);
		}
		//Here you are, json is valid
		if($json->status !== 0){
			//Request is invalid
			$this->kill(sprintf($this->_("Status Code %d: Invalid Receipt"), $json->status), 403);
		}
		return $json->receipt;
	}
	
	/**
	 * Verify JOSN data 
	 * @param string $json
	 * @param string $signature
	 * @return boolean
	 */
	private function verify_json($json, $signature){
		if(!function_exists('openssl_get_publickey') || empty($this->android_pub_key)){
			return false;
		}
		$signature = base64_decode($signature);
		$pubkeyid = openssl_get_publickey(base64_decode($this->android_pub_key));
		return openssl_verify($json, $signature, $pubkeyid);
	}
	
	/**
	 * Test login credential
	 * @global wp_xmlrpc_server $wp_xmlrpc_server
	 * @param array $args
	 * @param boolean $die_failur if set true, die when login failed
	 * @param int $username_index
	 * @param int $password_index
	 * @return WP_User|false
	 */
	public function xmlrpc_login($args = array(), $die_failur = true, $username_index = 0, $password_index = 1){
		global $wp_xmlrpc_server;
		$username = isset($args[$username_index]) ? $args[$username_index] : false ;
		$password = isset($args[$password_index]) ? $args[$password_index] : false ;
		if($username && $password){
			$user = $wp_xmlrpc_server->login($wp_xmlrpc_server->escape($username), $wp_xmlrpc_server->escape($password));
			if(!$user && $die_failur){
				$this->kill($this->_('Failed to login. Wrong username/password.'), 403);
			}
			return $user;
		}elseif($die_failur){
			$this->kill($this->_('This API requires login credentials.'), 403);
		}else{
			return false;
		}
	}
	
	/**
	 * Return if ssl is forced.
	 * @return boolean
	 */
	public function is_ssl_forced(){
		switch($this->force_ssl){
			case 2:
				return true;
				break;
			case 1:
				return (defined('FORCE_SSL_ADMIN') && FORCE_SSL_ADMIN) || (defined('FORCE_SSL_LOGIN') && FORCE_SSL_LOGIN);
				break;
			default:
				return false;
				break;
		}
	}
	
	/**
	 * Return if iOS is enabled
	 * @return boolean
	 */
	public function is_ios_available(){
		return $this->ios_enabled;
	}
	
	/**
	 * Return if android is enabled
	 * @return boolean
	 */
	public function is_android_available(){
		return $this->android_enabled;
	}
}