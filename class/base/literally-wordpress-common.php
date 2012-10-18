<?php
/**
 * Utility class with common methods
 *
 * @package
 * @since 0.9
 */
class Literally_WordPress_Common {
	
	/**
	 * Root directory path of plugin directory
	 * @var string
	 */
	protected $dir = '';
	
	/**
	 * Root url of this plugin
	 * @var string
	 */
	protected $url = '';
	
	/**
	 * Whether if this module is enabled.
	 * @var boolean
	 */
	protected $enabled = false;
	
	/**
	 * Constructor 
	 */
	public function __construct($option = array()) {
		$this->dir = plugin_dir_path(dirname(dirname(__FILE__)));
		$this->url = plugin_dir_url(dirname(dirname(__FILE__)));
		$this->set_option($option);
		$this->on_construct();
		add_action('init', array($this, '_init_hook'));
		add_action('lwp_update_option', array($this, 'set_option'));
		add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
	}
	
	/**
	 * Executed on constructor
	 * @param type $option 
	 */
	public function set_option($option = array()){
		$this->option = $option;
	}
	
	/**
	 * Enqueue_script on admin panel
	 */
	public function admin_enqueue_scripts(){
		
	}
	
	/**
	 * Executed on construct 
	 */
	protected function on_construct(){}

	/**
	 * Hook on init action
	 * @internal 
	 */
	public function _init_hook(){
		add_action('admin_bar_menu', array($this, 'admin_bar'));
	}
	
	/**
	 * Do admin bar
	 * @param WP_Admin_Bar $admin_bar 
	 */
	public function admin_bar($admin_bar){
		
	}
	
	/**
	 * Alias for gettext
	 * @global Literally_WordPress $lwp
	 * @param string $text 
	 */
	public function e($text){
		global $lwp;
		$lwp->e($text);
	}
	
	/**
	 * Alias for gettext
	 * @global Literally_WordPress $lwp
	 * @param string $text
	 * @return string 
	 */
	public function _($text){
		global $lwp;
		return $lwp->_($text);
	}
	
	/**
	 * Returns if this module is enabled.
	 * @return boolean
	 */
	public function is_enabled(){
		return $this->enabled;
	}
	
	/**
	 * Call wp_die shorthand
	 * @param string|array $message If array, apply sprintf
	 * @param int $status_code HTTP Status code
	 * @param boolean $backlink Default true
	 */
	public function kill($message, $status_code = 400, $backlink = true){
		wp_die(
			(is_array($message) ? call_user_func_array('sprintf', $message) : (string)$message), 
			sprintf("%s : %s", get_status_header_desc($status_code), get_bloginfo('name')),
			array('back_link' => $backlink, 'response' => $status_code)
		);
	}
	
	/**
	 * 
	 * @global Literally_WordPress $lwp
	 * @param string $post
	 * @return string
	 */
	public function get_item_name($post){
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
}