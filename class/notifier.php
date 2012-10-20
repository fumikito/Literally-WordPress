<?php
/**
 * Notification Utility
 * @package Literally WordPress
 */

class LWP_Notifier extends Literally_WordPress_Common{
	
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
	public $post_type = "lwp_notification";
	
	/**
	 * @var array
	 */
	private $parts = array(
		'footer' => array('site_url', 'site_name'),
		'bank' => array(),
		'thanks' => array('user_name', 'price', 'item_name', 'item_url', 'bank', 'expires', 'code'),
		'confirmed' => array('user_name', 'price', 'item_name', 'item_url', 'ordered', 'confirmed'),
		'reminder' => array('user_name', 'price', 'item_name', 'item_url', 'bank', 'ordered', 'expires', 'past', 'code'),
		'expired' => array('user_name', 'item_name', 'item_url', 'expired', 'ordered', 'past', 'code')
	);
	
	/**
	 * Setup option
	 * @see Literally_WordPress_Common
	 * @param array $option
	 */
	public function set_option($option) {
		$option = shortcode_atts(array(
			'transfer' => false,
			"notification_frequency" => 0,
			"notification_limit" => 30,
		), $option);
		$this->enabled = (boolean)$option['transfer'];
		$this->frequency = (int)$option['notification_frequency'];
		$this->limit = (int)$option['notification_limit'];
		$this->admin_mail = get_option("admin_email");
	}
	
	/**
	 * Register hooks
	 * @see Literally_WordPress_Common 
	 */
	protected function on_construct() {
		if($this->enabled){
			//Create Post Type for Mail
			add_action('init', array($this, 'register_post_type'));
			add_action('admin_init', array($this, 'admin_init'));
			add_action('admin_head', array($this, 'admin_head'));
			add_filter('user_can_richedit', array($this, 'rich_edit'));
			//Register Cron
			if ( !wp_next_scheduled( 'lwp_daily_notification' ) ) {
				wp_schedule_event(time(), 'daily', 'lwp_daily_notification');
			}
			add_action('lwp_daily_notification', array($this, 'daily_cron'));
		}
	}
	
	/**
	 * @global Literally_WordPress $lwp 
	 */
	public function register_post_type(){
		global $lwp;
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
	 * Create post and controlle admin panel's appearance
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @global int $user_ID 
	 */
	public function admin_init(){
		if(isset($_GET['page']) && false !== strpos($_GET['page'], 'lwp')){
			global $wpdb, $user_ID;
			foreach($this->parts as $slug => $vars){
				if(!$wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_name = %s", $this->post_type, "lwp-".$slug))){
					switch($slug){
						case 'bank':
							$name = $this->_('Bank Account');
							$default = $this->_('Bank Name: Hametu Bank
Branch: Aoyama (023)
Account: Hametu Tarou
No: 0000000
');
							break;
						case 'thanks':
							$name = $this->_('Thank you message');
							$default = $this->_('Dear %user_name%,

Thank you for ordering %item_name%.
Please transfer deposit %price% to account below.

%bank%

Enter the transfer code below if required.
%code% 

This transaction will be expires at %expires%.

See Item detail at:
%item_url%
');
							break;
						case 'confirmed':
							$name = $this->_('Comfirmed');
							$default = $this->_('
Dear %user_name%,


You ordered %item_name% at %ordered% and
we confirmed your deposit. 

Now you can access your item at:
%item_url%
');
							break;
						case 'reminder':
							$name = $this->_('Reminder');
							$default = $this->_('Dear %user_name%,


You orderd %item_name% at %ordered%,
but we have not confirmed the transfered deposit.

Please transfer deposit to our account below:

%bank%

Enter the transfer code below if required.
%code% 

This transaction will be expires at %expires%.

See Item detail at:
%item_url%
');
							break;
						case 'expired':
							$name = $this->_('Expired');
							$default = $this->_('Dear %user_name%,

Your order has expired because %past% days has past.

Item: %item_name%
Orderd: %ordered%

You can also get in touch this item again:
%item_url%
');
							break;
						default:
							$name = $this->_('Footer');
							$default = $this->_('
								

------------------------
%site_name%
%site_url%
');
							break;
					}
					wp_insert_post(array(
						'post_title' => $name,
						'post_name' => "lwp-".$slug,
						'post_author' => $user_ID,
						'post_type' => $this->post_type,
						'post_status' => 'publish',
						'post_content' => $default
					));
				}
			}
		}
	}
	
	public function admin_head(){
		?>
<style type="text/css" id="lwp-notification-style">
	dl.description dt{
		font-weight: bold;
		font-size:1.2em;
	}
	dl.description dd{
		margin-bottom:1em;
	}
</style>
		<?php
	}
	
	/**
	 * Returns waiting transfer count
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @return int
	 */
	public function on_queue_count(){
		global $lwp, $wpdb;
		$sql = <<<EOS
			SELECT COUNT(ID) FROM {$lwp->transaction}
			WHERE method = %s AND status = %s
EOS;
		return (int)$wpdb->get_var($wpdb->prepare($sql, LWP_Payment_Methods::TRANSFER, LWP_Payment_Status::START));
	}
	
	/**
	 * Returns limit date for specified time
	 * @param string $gmt_datetime Datetime format
	 * @param string $format
	 * @return string
	 */
	public function get_limit_date($gmt_datetime, $format = 'Y-m-d H:i:s'){
		return get_date_from_gmt(date('Y-m-d H:i:s', strtotime($gmt_datetime) + $this->limit * 60 * 60 * 24), $format);
	}
	
	/**
	 * Returns left days to limit
	 * @param string $gmt_ordered
	 * @param int $now timestamp format
	 * @return int
	 */
	public function get_left_days($gmt_ordered, $now = false){
		if(!$now){
			$now = strtotime((gmdate('Y-m-d H:i:s')));
		}
		return floor( (strtotime($gmt_ordered) - $now + 60 * 60 * 24 * $this->limit ) / 60 / 60 / 24);
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
				$desc = $this->_('This text will be used on footer of every mail to your user.');
				break;
			case 'lwp-bank':
				$desc = $this->_('This won\'t be displayed individually, but is used for other notifications.');
				break;
			case 'lwp-thanks':
				$desc = $this->_('This messages is displayed on thank-you page on your site and thank-you mail sent on success of the transaction.');
				break;
			case 'lwp-confirmed':
				$desc = $this->_('This messages will be sent via email when you change transfer status to success on admin panel.');
				break;
			case 'lwp-reminder':
				if($this->frequency > 0){
					$desc = sprintf($this->_('This message is sent via email every %s days to users who do not transfer the deposit.'), $this->frequency);
				}else{
					$desc = $this->_('You set reminder frequency to 0, so you don\'t have to edit this message.');
				}
				break;
			case 'lwp-expired':
				$desc = $this->_('This message will be sent when your customer miss the transaction time limit.');
				break;
		}
		$var = implode(', ', array_map(create_function('$row', 'return "<strong>%".$row."%</strong>";'), $this->parts[str_replace('lwp-', '', $post->post_name)]));
		?>
		<dl class="description">
			<dt><?php $this->e('About this notification'); ?></dt>
			<dd><?php echo $desc; ?></dd>
			<dt><?php $this->e('Mail Subject'); ?></dt>
			<dd><?php $this->e('Title will be ussed as mail subject. You can change it as you like.'); ?></dd>
			<dt><?php $this->e('Variants'); ?></dt>
			<dd><?php printf($this->_('You can use these variables: %s'), $var); ?></dd>
			<dt><?php $this->e('Caution'); ?></dt>
			<dd><?php $this->e('Notification mail will be sent as plain text. <strong>DO NOT USE HTML</strong>.'); ?></dd>
		</dl>
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
	
	/**
	 * Check daily
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb 
	 */
	public function daily_cron(){
		global $lwp, $wpdb;
		if($this->enabled){
			//Retrieve expired transactions
			$expired_date = date('Y-m-d H:i:s', strtotime(gmdate('Y-m-d H:i:s')) - $this->limit * 24 * 60 * 60);
			$sql = $wpdb->prepare("SELECT * FROM {$lwp->transaction} WHERE registered <= %s AND method = %s AND status = %s", $expired_date, LWP_Payment_Methods::TRANSFER, LWP_Payment_Status::START);
			$expired = $wpdb->get_results($sql);
			foreach($expired as $e){
				$wpdb->update(
					$lwp->transaction,
					array(
						'status' => LWP_Payment_Status::CANCEL,
						'updated' => gmdate('Y-m-d H:i:s')
					),
					array('ID' => $e->ID),
					array('%s', '%s'),
					array('%d')
				);
				$this->notify($e, 'expired');
			}
			
			//Retrieve reminder
			if($this->frequency > 0){
				$sql = $wpdb->prepare("SELECT * FROM {$lwp->transaction} WHERE status = %s AND method = %s AND MOD(DATEDIFF(now(), registered), 7) = 0", LWP_Payment_Status::START, LWP_Payment_Methods::TRANSFER);
				$reminded = $wpdb->get_results($sql);
				foreach($reminded as $r){
					$this->notify($r, 'reminder');
				}
			}
		}
	}
	
	/**
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param object $transaction
	 * @param string $type
	 * @return string
	 */
	private function get_body($transaction, $type = 'footer'){
		global $lwp, $wpdb;
		$body = $wpdb->get_var($wpdb->prepare("SELECT post_content FROM {$wpdb->posts} WHERE post_type = %s AND post_name = %s", $this->post_type, "lwp-".$type));
		foreach($this->parts[$type] as $key){
			switch ($key) {
				case 'user_name':
					$replaced = get_userdata($transaction->user_id)->display_name;
					break;
				case 'site_url':
					$replaced = get_bloginfo('url');
					break;
				case 'site_name':
					$replaced = get_bloginfo('name');
					break;
				case 'price':
					$replaced = number_format($transaction->price);
					break;
				case 'item_name':
					$replaced = $wpdb->get_var($wpdb->prepare("SELECT post_title FROM {$wpdb->posts} WHERE ID = %d", $transaction->book_id));
					break;
				case 'item_url':
					if($wpdb->get_var($wpdb->prepare("SELECT post_type FROM {$wpdb->posts} WHERE ID = %d", $transaction->book_id)) == $lwp->subscription->post_type){
						$replaced = $lwp->subscription->get_subscription_archive();
					}else{
						$replaced = get_permalink($transaction->book_id);
					}
					break;
				case 'ordered':
					$replaced = mysql2date(get_option('date_format'), $transaction->registered, true);
					break;
				case 'expires':
					$time = date('Y-m-d H:i:s', $this->limit * 60 * 60 * 24 + strtotime($transaction->registered));
					$replaced = mysql2date(get_option('date_format'), $time, true);
					break;
				case 'past':
					$replaced = ceil((strtotime(gmdate('Y-m-d H:i:s')) - strtotime($transaction->registered)) / 60 / 60 / 24);
					break;
				case 'code':
					$replaced = $transaction->transaction_key;
					break;
				case 'bank':
					$replaced = $this->get_body($transaction, 'bank');
				default:
					break;
			}
			$body = str_replace("%{$key}%", $replaced, $body);
		}
		return $body;
	}
	
	private function get_mail($type){
		
	}
	
	/**
	 * Send notification
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param object $transaction
	 * @param string $type 
	 * @return boolean
	 */
	public function notify($transaction, $type = 'confirmed'){
		global $lwp, $wpdb;
		$subject = get_bloginfo('name').' :: '.$wpdb->get_var($wpdb->prepare("SELECT post_title FROM {$wpdb->posts} WHERE post_type = %s AND post_name = %s", $this->post_type, "lwp-".$type));
		$to = $wpdb->get_var($wpdb->prepare("SELECT user_email FROM {$wpdb->users} WHERE ID = %d", $transaction->user_id));
		$from = get_bloginfo('name')." <{$this->admin_mail}>";
		$body = $this->get_body($transaction, $type).$this->get_body($transaction, 'footer');
		$mail = apply_filters('lwp_notify', compact('subject', 'to', 'from', 'body'), $type);
		if(is_array($mail) && !empty($mail)){
			return (boolean)wp_mail($mail['to'], $mail['subject'], $mail['body'], "From: {$mail['from']}\r\n\\");
		}else{
			return true;
		}
	}
	
	/**
	 * Returns Thank you message
	 * @param object $transaction
	 * @return string
	 */
	public function get_thankyou($transaction){
		$body = (string) $this->get_body($transaction, 'thanks');
		$body = apply_filters('lwp_show_thank_you', $body, $transaction);
		return wpautop($body);
	}
	
	/**
	 * Returns bank accoun
	 * @global wpdb $wpdb
	 * @param boolean $pee
	 * @return string
	 */
	public function get_bank_account($pee = true){
		global $wpdb;
		$bank = $wpdb->get_var($wpdb->prepare("SELECT post_content FROM {$wpdb->posts} WHERE post_type = %s AND post_name = %s", $this->post_type, "lwp-bank"));
		if($pee){
			$bank = wpautop($bank);
		}
		return $bank;
	}
}