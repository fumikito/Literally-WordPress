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
	}
	
	/**
	 * Executed on constructor
	 * @param type $option 
	 */
	protected function set_option($option = array()){}
	
	/**
	 * Executed on construct 
	 */
	protected function on_construct(){}

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
}