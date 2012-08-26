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
		}
	}
	
	/**
	 * Register Post Type
	 */
	public function register_post_type(){
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
}