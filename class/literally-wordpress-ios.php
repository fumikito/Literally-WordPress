<?php

class LWP_iOS extends Literally_WordPress_Common{
	
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
			'ios_public' => false,
			'ios_available' => false,
			'ios_force_ssl' => 0
		), $option);
		$this->enabled = (boolean) $option['ios'];
		$this->web_available = (boolean) $option['ios_available'];
		$this->post_type_public = (boolean) $option['ios_public'];
		$this->force_ssl = (int)$option['ios_force_ssl'];
		if($this->is_enabled()){
			add_filter('lwp_payable_post_types', array($this, 'add_post_type'));
		}
	}
	
	/**
	 * @see Literally_WordPress_Common
	 */
	public function on_construct() {
		if($this->is_enabled()){
			add_action('init', array($this, 'register_post_type'), 20);
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
		$singular = $this->_('iOS Product');
		$plural = $this->_('iOS Products');
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
			wp_nonce_field('lwp_ios_product_id', '_lwpnonce_ios_id');
			?>
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
			<?php
		}
	}
	
	/**
	 * 投稿保存時に実行される
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
			if(preg_match("/^ios_/", $method)){
				$xmlrpc_method = 'lwp.ios.'.preg_replace('/_(\w)/e', 'ucfirst(\\1)', preg_replace("/^ios_/", "", $method));
				$methods[$xmlrpc_method] = array($this, $method);
			}
		}
		$this->methods = array_keys($methods);
		return $methods;
	}
	
	/**
	 * Returns registered methods
	 * @global wp_xmlrpc_server $wp_xmlrpc_server
	 */
	public function ios_methods(){
		return $this->methods;
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
		$args = wp_parse_args($args, array(
			'term_taxonomy_id' => 0,
			'orderby' => 'pm1.meta_value',
			'order' => 'ASC',
			'status' => 'publish'
		));
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
		$result = $wpdb->get_results($wpdb->prepare($sql, $this->product_id, $lwp->price_meta_key), ARRAY_A);
		for($i = 0, $l = count($result); $i < $l; $i++){
			$result[$i]['post_id'] = intval($result[$i]['post_id']);
			$result[$i]['price'] = (float)$result[$i]['price'];
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
	 * Returns list of product group
	 * @see http://codex.wordpress.org/Function_Reference/get_terms
	 * @param array $args same arguments as get_terms
	 * @return array
	 */
	public function ios_product_group($args){
		return get_terms($this->taxonomy, (is_array($args) ? $args : '' ));
	}
	
	/**
	 * Returns file list
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param array $args
	 * @return array
	 */
	public function ios_file_list($args = 0){
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
			$result[$i]['free'] = (boolean)$result[$i]['free'];
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
	public function ios_get_file($args){
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
					WHERE user_id = %d AND status = %s AND book_id = %d
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
		return array(
			'hash' => $hash,
			'size' => $size,
			'data' => new IXR_Base64(file_get_contents($path))
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
			$this->kill(srpintf($this->_('Product ID:%s does not exists.'), $receipt->product_id), 404);
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
			return (boolean)$wpdb->insert($lwp->transaction, array(
				'user_id' => get_current_user_id(),
				'book_id' => $post->ID,
				'price' => $price,
				'status' => LWP_Payment_Status::SUCCESS,
				'method' => LWP_Payment_Methods::APPLE,
				'transaction_key' => $receipt->original_transaction_id,
				'registered' => date('Y-m-d H:i:s', intval($receipt->original_purchase_date_ms / 1000) ),
				'updated' => gmdate('Y-m-d H:i:s'),
				'num' => $receipt->quantity
			), array('%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%d') );
		}
	}
	
	/**
	 * Returns user inforamtion
	 * @param array $args username, password
	 * @return WP_User
	 */
	public function ios_get_user_info($args){
		$user = $this->xmlrpc_login($args);
		return apply_filters('lwp_ios_user', $user);
	}
	
	/**
	 * Returns user transaction
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
		return array(
			'transactions' => $transactions,
			'total' => $wpdb->get_var("SELECT FOUND_ROWS()"),
			'page' => $index
		);
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
	 * Test login credential
	 * @global wp_xmlrpc_server $wp_xmlrpc_server
	 * @param array $args
	 * @param boolean $die_failur if set true, die when login failed
	 * @param int $username_index
	 * @param int $password_index
	 * @return WP_User|false
	 */
	private function xmlrpc_login($args = array(), $die_failur = true, $username_index = 0, $password_index = 1){
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
	private function is_ssl_forced(){
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
}