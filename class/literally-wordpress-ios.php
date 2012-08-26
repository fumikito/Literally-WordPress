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
	 * post meta key name for product id
	 * @var string 
	 */
	public $product_id = '_lwp_ios_product_id';
	
	/**
	 * @see Literally_WordPress_Common
	 */
	public function set_option($option) {
		$option = shortcode_atts(array(
			'ios' => false,
			'ios_public' => false,
			'ios_available' => false
		), $option);
		$this->enabled = (boolean) $option['ios'];
		$this->web_available = (boolean) $option['ios_available'];
		$this->post_type_public = (boolean) $option['ios_public'];
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
		foreach(get_class_methods($this) as $method){
			//register all class method starting with 'ios_'
			if(preg_match("/^ios_/", $method)){
				$xmlrpc_method = 'lwp.ios.'.preg_replace('/_(\w)/e', 'ucfirst(\\1)', preg_replace("/^ios_/", "", $method));
				$methods[$xmlrpc_method] = array($this, $method);
			}
		}
		return $methods;
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
			SELECT
				f.ID, f.name, f.detail, f.free, f.public, f.registered, f.updated
			FROM {$lwp->files} AS f
			WHERE f.book_id = %d
			ORDER BY f.ID ASC
EOS;
		$result = $wpdb->get_results($wpdb->prepare($sql, $post_id), ARRAY_A);
		for($i = 0, $l = count($result); $i < $l; $i++){
			$result[$i]['ID'] = intval($result[$i]['ID']);
			$result[$i]['public'] = intval($result[$i]['public']);
			$result[$i]['free'] = intval($result[$i]['free']);
		}
		return $result;
	}
}