<?php

class LWP_Rewrite{
	
	/**
	 * Slug for rewrite rules
	 * @var string
	 */
	private $slug = 'lwp';
	
	/**
	 * Rewrite rules
	 * @var array
	 */
	private $default_rewrites = array(
		'([^/]+)/?' => 'index.php?lwp-action=$matches[1]',
	);
	
	public function __construct(){
		add_action('admin_notices', array($this, 'admin_notices'));
		// Filter rewrite rules.
		add_action('generate_rewrite_rules', array($this, 'generate_rewrite_rules'));
		// Update rewrite rules
		add_action('admin_init', array($this, 'flush_rules'));
		// Add filter for query vars
		add_filter('query_vars', array($this, 'query_vars') );
	}
	
	/**
	 * Register query vars only when permalink is enabled.
	 * 
	 * @param string $query_vars
	 * @return string
	 */
	public function query_vars( $query_vars ){
		if(get_option('rewrite_rules')){
			$query_vars[] = 'lwp-action';
		}
		return $query_vars;
	}
	
	/**
	 * Returns slug for 
	 * @return string
	 */
	private function get_slug(){
		return apply_filters('lwp_rewrite_slug', $this->slug);
	}
	
	/**
	 * Returns rewrite rules array
	 * @return array
	 */
	private function get_rewrites(){
		$new_rewrites = array();
		$all_rewrites = array_merge(apply_filters('lwp_additional_rules', array()),
				$this->default_rewrites);
		foreach($all_rewrites as $rewrite => $reg){
			$new_rewrites[$this->get_slug().'/'.$rewrite] = $reg;
		}
		return $new_rewrites;
	}
	
	/**
	 * Flush rewrite rules
	 * 
	 * @global array $wp_rewrite
	 */
	public function flush_rules(){
		global $wp_rewrite;
		if(isset($_REQUEST['_wpnonce'], $_REQUEST['page'])
				&& $_REQUEST['page'] == 'lwp-setting'
				&& wp_verify_nonce($_REQUEST['_wpnonce'], 'lwp_flush_rewrite_rules')
				&& current_user_can('manage_options')){
			$wp_rewrite->flush_rules();
			add_action('admin_notices', array($this, 'show_updated'));
		}
	}
	
	/**
	 * Override rewrite rules
	 * 
	 * @param WP_Rewrite $wp_rewrite
	 */
	public function generate_rewrite_rules(&$wp_rewrite){
		$new_rewrites = $this->get_rewrites();
		if(!empty($new_rewrites) && !empty($wp_rewrite->rules)){
			$wp_rewrite->rules = $new_rewrites + $wp_rewrite->rules;
		}
	}
	
	/**
	 * Returns current action value
	 * 
	 * @return string
	 */
	public function get_current_action(){
		// Rewrite rules are valid, but for backward compatibility, 
		// check exsitance of lwp query.
		if(!get_option('rewrite_rules') || isset($_GET['lwp'])){
			return isset($_GET['lwp']) && !empty($_GET['lwp']) ? $_GET['lwp'] : false;
		}else{
			$action = get_query_var('lwp-action');
			return !empty($action) ? (string)$action : false;
		}
	}
	
	/**
	 * Returns if rewrite rules are OK
	 * 
	 * @global Literally_WordPress $lwp
	 * @return boolean
	 */
	private function test_rewrites(){
		global $lwp;
		$rewrites = get_option('rewrite_rules');
		if(empty($rewrites)){
			// Permalink is not active.
			// NTT is the only service which requires not queried URL.
			return !$lwp->ntt->is_enabled();
		}else{
			$flg = true;
			foreach($this->get_rewrites() as $rewrite => $replaced){
				if(false === array_key_exists($rewrite, $rewrites)){
					$flg = false;
					break;
				}
			}
			return $flg;
		}
	}
	
	/**
	 * Get endpoint
	 * @param string $action
	 * @param boolean $is_sanbdox
	 * @return string
	 */
	public function endpoint($action = 'buy', $additional_args = '', $is_sanbdox = false){
		
		switch($action){
			case 'ntt-smarttrade':
				return trailingslashit(home_url('/', 'https')).'/ntt-smarttrade/';
				break;
			default:
				$url = home_url();
				if(FORCE_SSL_LOGIN || FORCE_SSL_ADMIN){
					$url = str_replace('http:', 'https:', $url);
				}
				$sandbox = $is_sanbdox ? 'sandbox=true&' : '';
				return apply_filters('lwp_endpoint', untrailingslashit($url)."/?{$sandbox}lwp=".(string)$action, (string)$action);
				break;
		}
	}
	
	/**
	 * If permalink is wrong, show update notification.
	 * @global Literally_WordPress $lwp
	 */
	public function admin_notices(){
		global $lwp;
		if(current_user_can('manage_options') && !$this->test_rewrites()){
			$url = wp_nonce_url(admin_url('admin.php?page=lwp-setting'), 'lwp_flush_rewrite_rules');
			?>
			<div class="error">
				<p>
					<strong>[Literally WordPress]</strong><br />
					<?php $lwp->e('To complate update, you should update rewrite rules and refresh permalink settings. This changes nothing.'); ?>
					&nbsp;<a class="button" href="<?php echo esc_attr($url); ?>"><?php $lwp->e('Update rewrite rules'); ?></a>
				</p>
			</div>
			<?php
		}
	}
	
	/**
	 * Updated message
	 * @global Literally_WordPress $lwp
	 */
	public function show_updated(){
		global $lwp;
		printf('<div class="error"><p>%s</p></div>', $lwp->_('Rewrite rules are successfully updated.'));
	}
}