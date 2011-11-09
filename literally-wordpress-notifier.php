<?php
/**
 * Notification Utility
 * @package Literally WordPress
 */

class LWP_Notifier{
	
	/**
	 * @var boolean
	 */
	private $valid = false;
	
	/**
	 *@var int
	 */
	private $frequency = 0;
	
	/**
	 * @var int
	 */
	private $limit = 30;
	
	/**
	 * @var string
	 */
	private $admin_mail = "";
	
	/**
	 * @var string
	 */
	private $post_type = "lwp_notification";
	
	private $parts = array(
		'footer',
		'bank',
		'thanks',
		'validated',
		'reminder',
		'expired'
	);
	
	/**
	 * Constructor
	 * @param boolean $valid
	 * @param int $frequency_per_days
	 * @param int $limit_days
	 */
	public function __construct($valid, $frequency_per_days, $limit_days) {
		$this->valid = (boolean)$valid;
		$this->frequency = (int)$frequency;
		$this->limit = (int)$limit;
		$this->admin_mail = get_option("admin_email");
		if($this->valid){
			//Create Post Type for Mail
			add_action('init', array($this, 'register_post_type'));
			add_action('admin_init', array($this, 'admin_init'));
			add_filter('user_can_richedit', array($this, 'rich_edit'));
		}
	}
	
	/**
	 * @global Literally_WordPress $lwp 
	 */
	public function register_post_type(){
		global $lwp;
		//投稿タイプを設定
		$single = $this->_('Notification');
		$plural = $this->_('Notifications');
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
			'capability_type' => 'post',
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
	 * Create post and controlle admin panel's appearance
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @global int $user_ID 
	 */
	public function admin_init(){
		if(isset($_GET['page']) && false !== strpos($_GET['page'], 'lwp')){
			global $wpdb, $lwp, $user_ID;
			foreach($this->parts as $slug){
				if(!$wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_name = %s", $this->post_type, "lwp-".$slug))){
					switch($slug){
						case 'bank': $name = $this->_('Bank Account'); break;
						case 'thanks': $name = $this->_('Thank you message'); break;
						case 'confirmed': $name = $this->_('Comfirmed'); break;
						case 'reminder': $name = $this->_('Reminder'); break;
						case 'expired': $name = $this->_('Expired'); break;
						default: $name = $this->_('Footer'); break;
					}
					wp_insert_post(array(
						'post_title' => $name,
						'post_name' => "lwp-".$slug,
						'post_author' => $user_ID,
						'post_type' => $this->post_type,
						'post_status' => 'publish'
					));
				}
			}
		}
	}
	
	/**
	 * Callback for register Meta box
	 */
	public function register_meta_box(){
		add_meta_box('lwp-notification', $this->_('Notification Variables'), array($this, 'meta_box'), $this->post_type, 'side', 'high');
	}
	
	/**
	 * Create Meta box
	 * @param object $post
	 * @param object $metabox 
	 */
	public function meta_box($post, $metabox){
		switch($post->post_name){
			case 'lwp-footer':
				$vars = array('url', 'site_name');
				$desc = $this->_('This text will be used on footer of every mail to you user.');
				break;
			case 'lwp-bank':
				$vars = array();
				$desc = $this->_('This won\'t be displayed individually, but is used for other notifications.');
				break;
			case 'lwp-thanks':
				$vars = array();
				$desc = $this->_('This messages is displayed on thank-you page on your site and thank-you mail sent on success of the transaction.');
				break;
			case 'lwp-confirmed':
				$vars = array();
				$desc = $this->_('This messages will be sent via email when you change transfer status to success on admin panel.');
				break;
			case 'lwp-reminder':
				$vars = array('name', 'ordered', 'expires', 'price', 'bank');
				if($this->frequency > 0){
					$desc = sprintf($this->_('This message is sent via email every %s days to users who do not transfer the deposit.'), $this->frequency);
				}else{
					$desc = $this->_('You set reminder frequency to 0, so you don\'t have to edit this message.');
				}
				break;
			case 'lwp-expired':
				$vars = array('expired', 'name', 'orderd', 'past');
				$desc = $this->_('This message will be sent when your customer miss the transaction time limit.');
				break;
		}
		$var = implode(', ', array_map(create_function('$row', 'return "<strong>%".$row."%</strong>";'), $vars));
		?>
		<p><?php printf($this->_('You can use these variables: %s'), $var); ?></p>
		<p class="description">
			<?php echo $desc; ?>
		</p>
		<?php
	}
	
	/**
	 * Stop RichEditor on Notification edit page.
	 * @param boolean $bool
	 * @return boolean
	 */
	public function rich_edit($bool){
		if(get_post_type() == $this->post_type){
			return false;
		}else{
			return $bool;
		}
	}
	
	public function register_cron(){
		
	}
	
	private function get_body($type = 'footer', $args = array()){
		foreach((array)$args as $key => $val){
			
		}
	}
	
	public function update($type, $content){
		
	}
	
	private function get_mail($type){
		
	}
	
	/**
	 * Return if notification is valid
	 * @return boolean
	 */
	public function is_valid(){
		return $this->valid;
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