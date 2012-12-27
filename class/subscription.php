<?php
/**
 * Subscription Utility
 *
 * @author Takahashi Fumiki
 * @package literally_wordpress
 * @since 0.8.8
 */
class LWP_Subscription extends Literally_WordPress_Common{
	
	/**
	 * @var string
	 */
	public $post_type = 'lwp_subscription';
	
	/**
	 * @var string
	 */
	public $free_meta_key = '_lwp_free_subscription';
	
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
	 * Setup option
	 * @see Literally_WordPress_Common
	 * @param array $option
	 */
	public function set_option($option) {
		$option = shortcode_atts(array(
			'subscription' => false,
			'subscription_post_types' => array(),
			'subscription_format' => 'all'
		), $option);
		$this->enabled = (boolean)$option['subscription'];
		$this->post_types = (array)$option['subscription_post_types'];
		switch($option['subscription_format']){
			case 'more':
			case 'nextpage':
				$this->format = $option['subscription_format'];
				break;
			default:
				$this->format = 'all';
				break;
		}
	}
	
	/**
	 * Register actions
	 * @see Literally_WordPress_Common
	 */
	protected function on_construct() {
		if($this->is_enabled()){
			add_action('init', array($this, 'register_post_type'));
			add_action('admin_init', array($this, 'admin_init'));
			add_action('edit_post', array($this, 'edit_post'));
			add_filter('the_content', array($this, 'the_content'));
			add_shortcode('lwp_subscribe', array($this, 'shortcode'));
			add_shortcode('lwp_pricelist', array($this, 'shortcode_pricelist'));
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
	 */
	public function admin_init(){
		if(isset($_GET['page']) && false !== strpos($_GET['page'], 'lwp')){
			global $wpdb;
			if(!$wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_name = %s", $this->post_type, $this->invitation_slug))){					
				wp_insert_post(array(
					'post_title' => $this->_('Invitation for subscription'),
					'post_name' => $this->invitation_slug,
					'post_author' => get_current_user_id(),
					'post_type' => $this->post_type,
					'post_status' => 'publish',
					'post_content' => $this->_("This contents is for subscribers only.")
				));
			}
		}
		if($this->is_enabled()){
			add_action('add_meta_boxes', array($this, 'register_subscription_metabox'));
		}
	}
	
	/**
	 * Register metabox
	 */
	public function register_meta_box(){
		add_meta_box('lwp-subscription', $this->_('Subscription Setting'), array($this, 'metabox_subscription'), $this->post_type, 'side', 'high');
	}
	
	/**
	 * Add metaboxes on edit page 
	 */
	public function register_subscription_metabox(){
		foreach($this->post_types as $p_type){
			add_meta_box('lwp-subscription-option', $this->_('Subscription Option'), array($this, 'metabox_subscription_option'), $p_type, 'side', 'high');
		}
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
				<p><?php $this->e('This contents will be displayed when your user access to page for subscribers only. It will be wrapped with <em>div.lwp-invitation</em>. <strong>YOU CAN CHANGE POST\'S TITLE.</strong>'); ?></p>
				<h4><?php $this->e('Allowed Shortcodes'); ?></h4>
				<dl>
					<dt><strong>[lwp_subscribe]</strong></dt>
					<dd>
						<?php $this->e("Output link to subscription page. Extra attributes are title and class."); ?><br />
						<em class="description">
							ex.<br />
							[lwp_subscbribe title=here class=mylink]
						</em>
					</dd>
					<dt><strong>[lwp_pricelist]</strong></dt>
					<dd>
						<?php $this->e("Output link to subscription price list. Extra attributes are title, class, popup, width and height."); ?><br />
						<em class="description">
							ex.<br />
							[lwp_subscbribe title=here class=mylink popup=false]
						</em>
					</dd>
				</dl>
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
							<td><input style="width:5em;" type="text" name="subscription_price" id="subscription_price" value="<?php echo (int)get_post_meta($post->ID, 'lwp_price', true);  ?>" /></td>
						</tr>
						<tr>
							<th><label for="subscription_expires"><?php $this->e('Expires'); ?>(<?php $this->e("Days"); ?>)</label></th>
							<td><input style="width:3em;" type="text" name="subscription_expires" id="subscription_expires" value="<?php echo (int)get_post_meta($post->ID, '_lwp_expires', true);  ?>" /></td>
						</tr>
					</tbody>
				</table>
				<?php
				do_action('lwp_payable_post_type_metabox', $post, $metabox);
				break;
		}
	}
	
	/**
	 * Subscription Setting
	 * @param object $post
	 * @param array $metabox 
	 */
	public function metabox_subscription_option($post, $metabox){
		wp_nonce_field('lwp_subscription_free', '_lwp_subscription_free', false);
		?>
		<p>
			<label>
				<input type="checkbox" name="lwp_subscription_free" value="1" <?php if(get_post_meta($post->ID, $this->free_meta_key, true)) echo ' checked="checked"'; ?>/>
				<?php $this->e('Anyone can read this post'); ?>
			</label>
		</p>
		<?php
	}
	
	/**
	 * Save subsctiption setting
	 * @param int $post_id 
	 */
	public function edit_post($post_id){
		//Save price
		if(isset($_REQUEST['_lwpnonce']) && wp_verify_nonce($_REQUEST['_lwpnonce'], 'lwp_subscription_setting')){
			update_post_meta($post_id, 'lwp_price', (int)$_REQUEST['subscription_price']);
			update_post_meta($post_id, '_lwp_expires', (int)$_REQUEST['subscription_expires']);
		}
		//Save free setting
		if(isset($_REQUEST['_lwp_subscription_free']) && wp_verify_nonce($_REQUEST['_lwp_subscription_free'], 'lwp_subscription_free')){
			if(isset($_REQUEST['lwp_subscription_free']) && $_REQUEST['lwp_subscription_free']){
				update_post_meta($post_id, $this->free_meta_key, true);
			}else{
				delete_post_meta($post_id, $this->free_meta_key);
			}
		}
	}
	
	
	
	/**
	 * Filter for the_content
	 * @param string $content
	 * @return string
	 */
	public function the_content($content){
		if(!is_admin() && $this->enabled && false !== array_search(get_post_type(), $this->post_types)){
			if(lwp_is_free_subscription()){
				$content .= "\n".'<div class="lwp-invitation">'.apply_filters('lwp_subscription_message', sprintf($this->_('%1$s are subscribers only but this %1$s is free!'), get_post_type_object(get_post_type())->labels->name), 'free').'</div>';
			}elseif(current_user_can('edit_others_posts') || get_current_user_id() == get_the_author_meta('ID')){
				$content .= "\n".'<div class="lwp-invitation">'.apply_filters('lwp_subscription_message', sprintf($this->_('This %1$s is subscribers only but you see whole content because of your capability to edit %1$s.'), get_post_type_object(get_post_type())->labels->name), 'owner').'</div>';
			}elseif($this->is_subscriber(get_current_user_id())){
				$owned_subscription = $this->get_subscription_owned_by(get_current_user_id());
				$message = sprintf($this->_('You have subscription plan \'%s\'.'), $owned_subscription->post_title);
				if($owned_subscription->expires == '0000-00-00 00:00:00'){
					$message .= $this->_('Your subscription is unlimited.');
				}else{
					$message .= sprintf(
						$this->_('You got it at <strong>%s</strong> and it will be expired at <strong>%s</strong>.'),
						mysql2date(get_option("date_format"), get_date_from_gmt($owned_subscription->updated)),
						mysql2date(get_option("date_format"), get_date_from_gmt($owned_subscription->expires))
					);
				}
				$content .= "\n".'<div class="lwp-invitation">'.apply_filters('lwp_subscription_message', $message, 'subscriber').'</div>';
			}else{
				//Get invitation message
				$message = get_page_by_path($this->invitation_slug, 'OBJECT', $this->post_type);
				$append = '<div class="lwp-invitation">'.apply_filters('get_the_content', $message->post_content).'</div>';
				//Get current page infomation
				global $page, $pages;
				if($page > count($pages)){
					$page = count($pages);
				}
				switch($this->format){
					case 'more':
						$more_page = 0;
						if(!empty($pages)){
							foreach($pages as $p){
								$more_page++;
								if(preg_match('/<!--more(.*?)?-->/', $p)){
									break;
								}
							}
							if($page == $more_page && preg_match("/<!--more(.*?)?-->/", $pages[$page - 1])){
								$page_content = preg_split("/<span id=\"more-[0-9]+\"><\/span>/", $content);
								remove_filter('the_content', array($this, 'the_content'));
								$content = apply_filters('get_the_content', $page_content[0]).$append;
								add_filter('the_content', array($this, 'the_content'));
							}elseif($page > $more_page){
								$content = $append;
							}
						}
						break;
					case 'nextpage':
						//if is paged and page is > 1, check if current user is subscriber.
						if($page > 1){
							$content = '';
						}
						$content .= $append;
						break;
					default:
						$content = $append;
						break;
				}
			}
		}
		return $content;
	}
	
	/**
	 * Returns if specified user is subscriber
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @global int $user_ID
	 * @param int $user_ID
	 * @return boolean 
	 */
	public function is_subscriber($user_ID = null){
		global $lwp, $wpdb;
		if(is_null($user_ID)){
			global $user_ID;
		}
		$sql = <<<EOS
			SELECT p.ID FROM {$lwp->transaction} AS t
			INNER JOIN {$wpdb->posts} AS p
			ON t.book_id = p.ID
			WHERE t.status = %s AND t.user_id = %d AND p.post_type = %s
			  AND ((t.expires = '0000-00-00 00:00:00') OR (t.expires > %s))
EOS;
		return (int)$wpdb->get_var($wpdb->prepare($sql, LWP_Payment_Status::SUCCESS, $user_ID, $this->post_type, gmdate('Y-m-d H:i:s')));
	}
	
	/**
	 * Returns specified user's active subscription
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @global int $user_ID
	 * @param int $user_ID
	 * @return object 
	 */
	public function get_subscription_owned_by($user_ID = null){
		global $lwp, $wpdb;
		if(is_null($user_ID)){
			global $user_ID;
		}
		$sql = <<<EOS
			SELECT t.*, p.ID AS post_id, p.post_title, p.post_content
			FROM {$lwp->transaction} AS t
			INNER JOIN {$wpdb->posts} AS p
			ON t.book_id = p.ID
			WHERE t.status = %s AND t.user_id = %d AND p.post_type = %s
			  AND ((t.expires = '0000-00-00 00:00:00') OR (t.expires > %s))
EOS;
		return $wpdb->get_row($wpdb->prepare($sql, LWP_Payment_Status::SUCCESS, $user_ID, $this->post_type, gmdate('Y-m-d H:i:s')));
	}
	
	/**
	 * Returns shortcode
	 * @param array $atts
	 * @param string $content
	 * @return string
	 */
	public function shortcode($atts, $content = ''){
		$atts = shortcode_atts(array(
			'title' => $this->_('Subscribe'),
			'class' => ''
		), $atts);
		$href = lwp_endpoint('subscription');
		return '<a href="'.  esc_attr($href).'" class="'.  esc_attr($atts['class']).'">'.  esc_html($atts['title']).'</a>';
	}
	
	/**
	 * Return shortcode
	 * @param array $atts
	 * @param string $content
	 * @return string
	 */
	public function shortcode_pricelist($atts, $content = ''){
		extract(shortcode_atts(array(
			'title' => '',
			'popup' => true,
			'width' => 800,
			'height' => 600,
			'class' => ''
		), $atts));
		return ($this->enabled) ? lwp_subscription_link($title, $popup, $width, $height, false, $class) : '';
	}
	
	/**
	 * Returns subscription plans
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @return array 
	 */
	public function get_subscription_list(){
		global $wpdb;
		$sql = <<<EOS
			SELECT p.post_title, p.ID, p.post_content, pm.meta_value AS price, pm2.meta_value AS expires
			FROM {$wpdb->posts} AS p
			INNER JOIN {$wpdb->postmeta} AS pm
			ON p.ID = pm.post_id AND pm.meta_key = 'lwp_price'
			INNER JOIN {$wpdb->postmeta} AS pm2
			ON p.ID = pm2.post_id AND pm2.meta_key = '_lwp_expires'
			WHERE p.post_type = %s AND p.post_status = 'publish'
			ORDER BY CAST(pm.meta_value AS UNSIGNED) ASC
EOS;
		return $wpdb->get_results($wpdb->prepare($sql, $this->post_type));
	}
	
	/**
	 * Return whether available subscription plan is registered.
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @return boolean
	 */
	public function has_plans(){
		global $wpdb;
		$sql = <<<EOS
			SELECT DISTINCT count(p.ID)
			FROM {$wpdb->posts} AS p
			INNER JOIN {$wpdb->postmeta} AS pm
			ON p.ID = pm.post_id AND pm.meta_key = 'lwp_price'
			INNER JOIN {$wpdb->postmeta} AS pm2
			ON p.ID = pm2.post_id AND pm2.meta_key = '_lwp_expires'
			WHERE p.post_type = %s AND p.post_status = 'publish'
			AND   CAST(pm.meta_value AS UNSIGNED) > 0
EOS;
		return (boolean)$wpdb->get_var($wpdb->prepare($sql, $this->post_type));
	}
	
	/**
	 * Return subscription price lists page
	 * @param boolean $need_return
	 * @return string
	 */
	public function get_subscription_archive($need_return = false){
		return $need_return ? lwp_endpoint('pricelist').'&back=true' : lwp_endpoint('pricelist');
	}
	
	/**
	 * Returns subscription archive page
	 * @return string
	 */
	public function get_subscription_post_type_page(){
		if(!empty($this->post_types)){
			$url = '';
			foreach($this->post_types as $post_type){
				$url = get_post_type_archive_link($post_type);
			}
			return $url;
		}else{
			return home_url();
		}
	}
}