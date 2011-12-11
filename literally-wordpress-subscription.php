<?php
/**
 * Subscription Utility
 *
 * @author Takahashi Fumiki
 * @package literally_wordpress
 */
class LWP_Subscription {
	
	/**
	 * @var boolean
	 */
	public $enabled = false;
	
	/**
	 * @var string
	 */
	private $post_type = 'lwp_subscription';
	
	/**
	 * @var array
	 */
	public $post_types = array();
	
	/**
	 * @var string
	 */
	private $format = 'all';
	
	/**
	 * @var string
	 */
	private $invitation_slug = 'lwp-invitation';
	
	/**
	 * Constructor
	 * @param array $option
	 */
	public function __construct($option) {
		$option = shortcode_atts(array(
			'subscription' => false,
			'subscription_post_type' => array(),
			'subscription_format' => 'all'
		), $option);
		$this->enabled = (boolean)$option['subscription'];
		$this->post_types = (array)$option['subscription_post_type'];
		switch($option['subscription_format']){
			case 'more':
			case 'nextpage':
				$this->format = $option['subscription_fomrat'];
				break;
			default:
				$this->format = 'all';
				break;
		}
		if($this->enabled){
			add_action('init', array($this, 'register_post_type'));
			add_action('admin_init', array($this, 'admin_init'));
			add_action('edit_post', array($this, 'edit_post'));
		}
	}
	
	/**
	 * Register Post type
	 * @global Literally_WordPress $lwp 
	 */
	public function register_post_type(){
		global $lwp;
		$single = $this->_('Subscription');
		$plural = $this->_('Subscriptions');
		$labels = array(
			'name' => $plural,
			'singular_name' => $single,
			'add_new' => $this->_('Add New'),
			'add_new_item' => sprintf($this->_('Add New %s'), $single),
			'edit_item' => sprintf($this->_("Edit %s"), $single),
			'new_item' => sprintf($this->_('Add New %s'), $single),
			'view_item' => sprintf($this->_('View %s'), $single),
			'search_items' => sprintf($this->_("Search %s"), $plural),
			'not_found' =>  sprintf($this->_('No %s was found.'), $single),
			'not_found_in_trash' => sprintf($this->_('No %s was found in trash.'), $single), 
			'parent_item_colon' => ''
		);
		$args = array(
			'labels' => $labels,
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => true,
			'show_in_menu' => 'lwp-setting',
			'query_var' => false,
			'rewrite' => false,
			'capability_type' => 'page',
			'hierarchical' => false,
			'menu_position' => 100,
			'has_archive' => false,
			'supports' => array('title','editor'),
			'show_in_nav_menus' => false,
			'menu_icon' => $lwp->url."/assets/book.png",
			'register_meta_box_cb' => array($this, 'register_meta_box')
		);
		register_post_type($this->post_type, $args);
	}
	
	/**
	 * Create message page for subscription
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @global int $user_ID 
	 */
	public function admin_init(){
		if(isset($_GET['page']) && false !== strpos($_GET['page'], 'lwp')){
			global $wpdb, $lwp, $user_ID;
			if(!$wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_name = %s", $this->post_type, $this->invitation_slug))){					
				wp_insert_post(array(
					'post_title' => $this->_('Invitation for subscription'),
					'post_name' => $this->invitation_slug,
					'post_author' => $user_ID,
					'post_type' => $this->post_type,
					'post_status' => 'publish',
					'post_content' => $this->_("This contents is for subscribers only.")
				));
			}
		}
	}
	
	/**
	 * Register metabox
	 */
	public function register_meta_box(){
		add_meta_box('lwp-subscription', $this->_('Subscription Setting'), array($this, 'metabox_subscription'), $this->post_type, 'side', 'high');
	}
	
	/**
	 * Create metabox
	 * @global Literally_WordPress $lwp
	 * @param object $post
	 * @param array $metabox 
	 */
	public function metabox_subscription($post, $metabox){
		switch($post->post_name){
			case $this->invitation_slug:
				?>
				<p><?php $this->e('This contents will be displayed when your user access to page for subscribers only. <strong>YOU CAN CHANGE POST\'S TITLE.</strong>'); ?></p>
				<?php
				break;
			default:
				global $lwp;
				wp_nonce_field('lwp_subscription_setting', '_lwpnonce', false);
				?>
				<table class="form-table">
					<tbody>
						<tr>
							<th><label for="subscription_price"><?php $this->e('Price'); ?>(<?php echo $lwp->option['currency_code']; ?>)</label></th>
							<td><input style="width:5em;" type="text" name="subscription_price" id="subscription_price" value="<?php echo (int)get_post_meta($post->ID, '_lwp_subscription_price', true);  ?>" /></td>
						</tr>
						<tr>
							<th><label for="subscription_expires"><?php $this->e('Expires'); ?>(<?php $this->e("Days"); ?>)</label></th>
							<td><input style="width:3em;" type="text" name="subscription_expires" id="subscription_expires" value="<?php echo (int)get_post_meta($post->ID, '_lwp_subscription_expires', true);  ?>" /></td>
						</tr>
					</tbody>
				</table>
				<?php
				break;
		}
	}
	
	/**
	 * Save subsctiption setting
	 * @param int $post_id 
	 */
	public function edit_post($post_id){
		if(isset($_REQUEST['_lwpnonce']) && wp_verify_nonce($_REQUEST['_lwpnonce'], 'lwp_subscription_setting')){
			update_post_meta($post_id, '_lwp_subscription_price', (int)$_REQUEST['subscription_price']);
			update_post_meta($post_id, '_lwp_subscription_expires', (int)$_REQUEST['subscription_expires']);
		}
	}
	
	/**
	 * Alias for gettext
	 * @global Literally_WordPress $lwp
	 * @param string $text 
	 * @return string
	 */
	private function _($text){
		global $lwp;
		return $lwp->_($text);
	}
	
	/**
	 * Alias for gettext
	 * @global Literally_WordPress $lwp
	 * @param string $text 
	 */
	private function e($text){
		global $lwp;
		$lwp->e($text);
	}
}