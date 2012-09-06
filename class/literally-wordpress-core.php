<?php
/**
 * Literally WordPressの処理を行うクラス
 *
 * @package literally_Wordpress
 * @author  Takahashi Fumiki<takahashi.fumiki@hametuha.co.jp>
 */
class Literally_WordPress{
	
	/**
	* バージョン
	*
	* @var string
	*/
	public $version = "0.9.2.3";
	
	/**
	 * 翻訳用ドメイン名
	 * @var string
	 */
	public $domain = "literally-wordpress";
	
	/**
	 * オプション
	 *
	 * @var array
	 */
	public $option;
	
	/**
	 * Campaign table name
	 * @var string
	 */
	public $campaign = "";
	
	/**
	 * Transaction table name
	 * @var string
	 */
	public $transaction = "";
	
	/**
	 * File table
	 * @var string
	 */
	public $files = "";
	
	/**
	 * File log table
	 * @var string
	 */
	public $file_logs = '';
	
	/**
	 * Relationships between files and devices
	 * @var string
	 */
	public $file_relationships = "";
	
	/**
	 * Device tables
	 * @var string
	 */
	public $devices = "";
	
	
	/**
	 * Table of promotion log
	 * @var string
	 */
	public $promotion_logs = "";
	
	/**
	 * Table of reward log
	 * @var string 
	 */
	public $reward_logs = '';
	
	/**
	 * このプラグインディレクトリへのURL
	 * 
	 * var string
	 */
	public $url = "";
	
	/**
	* このプラグインディレクトリへの絶対パス
	*
	* @var string
	*/
	public $dir = "";
	
	/**
	 * post meta key of price
	 * @var string
	 */
	public $price_meta_key = 'lwp_price';
	
	/**
	 * Paypalから返ってきたところかどうか
	 * 
	 * @var boolean
	 */
	public $on_transaction = false;
	
	/**
	 * トランザクションが成功しているかどうか
	 * 
	 * @var string
	 * @deprecated
	 */
	public $transaction_status = "FAILED";
	
	/**
	 * プラグインが使えるかどうか
	 * 
	 * @var boolean
	 */
	private $initialized = true;
	
	/**
	 * エラーの有無
	 * 
	 * @var boolean
	 */
	public $error = false;
	
	/**
	 * エラーメッセージ
	 * 
	 * @var array
	 */
	public $message = array();
	
	/**
	 * Post sell utility
	 * @var LWP_Post
	 */
	public $post = null;
	
	/**
	 * iOS Utility
	 * @var LWP_iOS
	 */
	public $ios = null;
	
	/**
	 * Form utility
	 * @var LWP_Form
	 */
	public $form = null;
	
	/**
	 * Notification Utility
	 * @var LWP_Notifier
	 */
	public $notifier = null;
	
	/**
	 * Subscription Utility
	 * @var LWP_Subscription
	 */
	public $subscription = null;
	
	/**
	 * Reward Utility
	 * @var LWP_Reward 
	 */
	public $reward = null;
	
	/**
	 * Event Controller
	 * @var LWP_Event
	 */
	public $event = null;
	
	//--------------------------------------------
	//
	// 初期化処理
	//
	//--------------------------------------------
	
	/**
	 * コンストラクター
	 * 
	 * @global wpdb $wpdb
	 * @return void
	 */
	public function __construct(){
		//Set plugin directory
		$this->url = plugin_dir_url(dirname(__FILE__));
		$this->dir = dirname(dirname(__FILE__));
		//Get talbe names
		$this->campaign = LWP_Tables::campaign();
		$this->transaction = LWP_Tables::transaction();
		$this->files = LWP_Tables::files();
		$this->file_relationships = LWP_Tables::file_relationships();
		$this->file_logs = LWP_Tables::file_logs();
		$this->devices = LWP_Tables::devices();
		$this->reward_logs = LWP_Tables::reward_logs();
		$this->promotion_logs = LWP_Tables::promotion_logs();
		//Load text domain
		load_plugin_textdomain($this->domain, false, basename($this->dir).DIRECTORY_SEPARATOR."language");
		//Get Option
		//Set up upload directory
		$upload_dir = wp_upload_dir();
		$this->option = wp_parse_args(
			get_option("literally_wordpress_option", array()),
			array(
				"db_version" => 0,
				"dir" => $upload_dir['basedir'].DIRECTORY_SEPARATOR."lwp",
				"sandbox" => false,
				"user_name" => "",
				"password" => "",
				"signature" => "",
				"token" => "",
				"skip_payment_selection" => false,
				'ios' => false,
				'ios_public' => false,
				'ios_available' => false,
				'ios_force_ssl' => 0,
				"subscription" => false,
				"subscription_post_types" => array(),
				'subscription_format' => 'all',
				'transfer' => false,
				"notification_frequency" => 0,
				"notification_limit" => 30,
				"reward_promoter" => false,
				"reward_promotion_margin" => 0,
				"reward_promotion_max" => 90,
				"reward_author" => false,
				"reward_author_margin" => 0,
				"reward_author_max" => 90,
				"reward_minimum" => 0,
				"reward_request_limit" => 10,
				"reward_pay_at" => 25,
				"reward_pay_after_month" => 0,
				"reward_notice" => '',
				"reward_contact" => '',
				"use_proxy" => false,
				'event_post_types' => array(),
				'event_mail_body' => '',
				'event_signature' => get_bloginfo('name')."\n".get_bloginfo('url')."\n".get_option('admin_email'),
				"slug" => str_replace(".", "", $_SERVER["HTTP_HOST"]),
				"currency_code" => '',
				"country_code" => '',
				"mypage" => 0,
				"custom_post_type" => array(),
				"payable_post_types" => array(),
				"show_form" => true,
				"load_assets" => 2
			)
		);
		//Initialize iOS
		$this->ios = new LWP_iOS($this->option);
		//Register form action
		$this->form = new LWP_Form($this->option);
		//Initialize Notification Utility
		$this->notifier = new LWP_Notifier($this->option);
		//Initialize Subscription
		$this->subscription = new LWP_Subscription($this->option);
		//Initialize Reward
		$this->reward = new LWP_Reward($this->option);
		//Initialize Event
		$this->event = new LWP_Event($this->option);
		//Initialize sell post
		$this->post = new LWP_Post($this->option);
		//Register hooks
		$this->register_hooks();
	}
	
	/**
	 * Register all hooks. 
	 */
	private function register_hooks(){
		//Register Script Library
		add_action('init', array($this, 'register_assets'));
		//ウィジェットの登録
		add_action('widgets_init', array($this, 'widgets'));
		//CSV Download Ajax
		add_action('wp_ajax_lwp_transaction_csv_output', array($this, 'ouput_csv'));
		//Hook on adminbar
		add_action('admin_bar_menu', array($this, 'admin_bar'));
		//Ajax action to list transactions
		add_action('wp_ajax_lwp_transaction_chart', array($this, 'ajax_transaction_chart'));
		if(is_admin()){ //Hooks only for admin panels.
			//Add hook to update option
			if($this->is_admin("setting")){
				add_action('init', array($this, 'update_option'));
			}
			//Check table and create if not exits
			add_action('admin_init', array($this, 'check_table'));
			//スタイルシート・JSの追加
			add_action("admin_enqueue_scripts", array($this, "admin_assets"));
			//キャンペーン更新
			if($this->is_admin("campaign")){
				add_action("admin_init", array($this, "update_campaign"));
			}
			//トランザクション更新
			if($this->is_admin("management")){
				add_action("admin_init", array($this, "update_transaction"));
			}
			//メニューの追加
			add_action("admin_menu", array($this, "add_menu"), 1);
			//メッセージの出力
			add_action("admin_notices", array($this, "admin_notice"));
			//ユーザーに書籍をプレゼントするフォーム
			add_action("edit_user_profile", array($this, "give_user_form"));
			//書籍プレゼントが実行されたら
			if(basename($_SERVER["SCRIPT_FILENAME"]) == "user-edit.php"){
				add_action("profile_update", array($this, "give_user"));
			}
			////Add Action links on plugin lists.
			add_filter('plugin_action_links', array($this, 'plugin_page_link'), 500, 2);
		}else{ //Hooks only for public area
			//Highjack frontpage request if lwp is set
			add_action("template_redirect", array($this->form, "manage_actions"));
			//Redirect to auth page if user is not logged in
			add_action("template_redirect", array($this, "protect_user_page"));
			//Output purchase history
			add_filter('the_content', array($this, "the_content"));
			//Load public assets
			add_action('wp_enqueue_scripts', array($this, 'load_public_assets'));
		}
	}
	
	/**
	 * Register Assets for this plugin.
	 * @return void
	 */
	public function register_assets(){
		wp_register_script("jquery-ui-timepicker", $this->url."assets/datepicker/jquery-ui-timepicker.js",array("jquery-ui-datepicker", 'jquery-ui-slider') ,"0.9.7", !is_admin());
		wp_register_style("jquery-ui-datepicker", $this->url."assets/datepicker/smoothness/jquery-ui.css", array(), "1.8.9");
		wp_register_script('google-jsapi', 'https://www.google.com/jsapi', array(), null, !is_admin());
	}
	
	
	/**
	 * Is PayPal valid
	 * @return void
	 */
	public function paypal_warning(){
		//課金できるかどうかチェック
		if(empty($this->option["user_name"]) || empty($this->option["token"])){
			return $this->_('Marchand ID and PDT Token required for transaction');
		}
		//通貨と国が設定されているかをチェック
		if(false == array_key_exists($this->option['currency_code'], PayPal_Statics::currency_codes())){
			return $this->_("Currency code is invalid.");
		}
		if(false == array_key_exists($this->option['country_code'], PayPal_Statics::country_codes())){
			return $this->_("Country code is invalid.");
		}
		return false;
	}
	
	
	/**
	 * Create table if not exist
	 *
	 * @return void
	 */
	public function check_table(){
		//Check if table needs update
		if(version_compare(LWP_Tables::VERSION, $this->option['db_version']) > 0){
			//Alter table if required
			LWP_Tables::alter_table($this->option['db_version']);
			//Create table
			LWP_Tables::create();
			//Save version number
			$this->option['db_version'] = LWP_Tables::VERSION;
			update_option("literally_wordpress_option", $this->option);
			//Show message if current user is admin
			if(current_user_can('manage_options')){
				$message = str_replace("'", '\'', $this->_('Literally WordPress successfully upgrades database.'));
				add_action('admin_notices', create_function('$a', "echo '<div id=\"message\" class=\"updated\"><p>{$message}</p></div>';"));
			}
		}
	}
	
	/**
	 * Load assets for public page 
	 */
	public function load_public_assets(){
		//JS
		if($this->option['load_assets'] > 0){
			wp_enqueue_script("lwp-timer", $this->url."assets/js/form-timer.js", array('jquery'), $this->version, true);
		}
		//CSS
		if($this->option['load_assets'] > 1){
			wp_enqueue_style("lwp-timer", $this->url."assets/lwp-buynow.css", array(), $this->version);
		}
	}
	
	/**
	 * Add submenues to Admin Panel
	 * 
	 * @return void
	 */
	public function add_menu(){
		//Setting Pagees
		add_menu_page("Literally WordPress", "Literally WP", 5, "lwp-setting", array($this, "load"), $this->url."/assets/book.png");
		add_submenu_page("lwp-setting", $this->_("General Setting"), $this->_("General Setting"), 'manage_options', "lwp-setting", array($this, "load"));
		//Transaction list
		add_submenu_page("lwp-setting", $this->_("Transaction Management"), $this->_("Transaction Management"), 'edit_posts', "lwp-management", array($this, "load"));
		//Transfer Page if enabled
		if($this->notifier->is_enabled()){
			add_submenu_page("lwp-setting", $this->_("Transfer Management"), $this->_("Transfer Management"), 'edit_posts', "lwp-transfer", array($this, "load"));
		}
		//Campaign setting
		add_submenu_page("lwp-setting", $this->_("Campaign Management"), $this->_("Campaign Management"), 'edit_posts', "lwp-campaign", array($this, "load"));
		if($this->post->is_enabled()){
			//Download Log
			add_submenu_page('lwp-setting', $this->_('Download logs'), $this->_('Download logs'), 'manage_options', 'lwp-download-logs', array($this, 'load'));
			//Device setting
			add_submenu_page("lwp-setting", $this->_("Device Setting"), $this->_("Device Setting"), 'edit_others_posts', "lwp-devices", array($this, "load"));
			
		}
		//Purchase history
		add_submenu_page("profile.php", $this->_("Purchase History"), $this->_("Purchase History"), 0, "lwp-history", array($this, "load"));
		//iOS Manual if enabled
		if($this->ios->is_enabled()){
			add_submenu_page("edit.php?post_type=".$this->ios->post_type, $this->_('API Manual'), $this->_('API Manual'), 'edit_posts', "lwp-ios-api", array($this, 'load'));
		}
		//Reward Page if enabled
		if($this->reward->is_enabled()){
			//admin
			add_submenu_page("lwp-setting", $this->_("Reward Management"), $this->_('Reward Management'), 'edit_posts', "lwp-reward", array($this, 'load'));
			//Personal
			if($this->reward->promotable || ($this->reward->rewardable && current_user_can('edit_posts'))){
				add_users_page($this->_("Reward"), $this->_("Reward"), 'read', "lwp-personal-reward", array($this, 'load'));
			}
		}
		//Event page if enabled
		if($this->event->is_enabled()){
			add_submenu_page("lwp-setting", $this->_('Event Management'), $this->_('Event Management'), 'edit_posts', "lwp-event", array($this, 'load'));
		}
		//Theme option
		add_theme_page($this->_("LWP Form Check"), $this->_('LWP Form Check'), 'edit_theme_options','lwp-form-check', array($this, 'load'));
	}
	
	/**
	 * Loads template for admin panel
	 * 
	 * @return void
	 */
	public function load(){
		if(isset($_GET["page"]) && (false !== strpos($_GET["page"], "lwp-"))){
			$slug = str_replace("lwp-", "", $_GET["page"]);
			global $wpdb;
			echo '<div class="wrap lwp-wrap">';
			$class_name = (basename($_SERVER['SCRIPT_FILENAME']) == 'users.php') ? 'icon-users' : 'ebook';
			echo "<div class=\"icon32 {$class_name} icon32-{$slug}\"><br /></div>";
			if(file_exists($this->dir.DIRECTORY_SEPARATOR."admin".DIRECTORY_SEPARATOR."{$slug}.php")){
				require_once $this->dir.DIRECTORY_SEPARATOR."admin".DIRECTORY_SEPARATOR."{$slug}.php";
			}else{
				$error = $this->_('This page does not exist. Template not found.'); 
				echo '<h2>Error</h2><div class="error"><p>'.$error.'</p></div>';
			}
			require_once $this->dir.DIRECTORY_SEPARATOR."admin".DIRECTORY_SEPARATOR."donate.php";
			echo "</div>\n<!-- .wrap ends -->";
		}elseif(false !== strpos($_SERVER["REQUEST_URI"], "users.php")){
			global $wpdb;
			echo '<div class="wrap">';
			echo "<div class=\"icon32 ebook\"><br /></div>";
			require_once $this->dir.DIRECTORY_SEPARATOR."admin".DIRECTORY_SEPARATOR."history.php";
			echo "</div>\n<!-- .wrap ends -->";
		}else{
			return;
		}
	}
	
	/**
	 * ウィジェットを登録する
	 */
	public function widgets(){
		require_once $this->dir."/widgets/buynow.php";
		register_widget('lwpBuyNow');
	}
	
	/**
	 * 管理画面にファイルを登録する
	 * 
	 * @return void
	 */
	public function admin_assets(){
		wp_enqueue_style("lwp-admin", $this->url."assets/style.css", array(), $this->version);
		wp_enqueue_style("thickbox");
		wp_enqueue_script("thickbox");
		//In case management or campaign, load datepicker.
		if(($this->is_admin('management') && isset($_REQUEST['transaction_id'])) || $this->is_admin('campaign')){
			//datepickerを読み込み
			wp_enqueue_style('jquery-ui-datepicker');
			wp_enqueue_script(
				"lwp-datepicker-load",
				$this->url."assets/js/campaign.js",
				array("jquery-ui-timepicker"),
				$this->version
			);
			wp_localize_script('lwp-datepicker-load', 'LWPDatePicker', array_merge(LWP_Datepicker_Helper::get_config_array(), array(
					'pieChartTitle' => $this->_('Reward Amount Summary'),
					'pieChartLabel' => $this->_('Status'),
					'pieChartUnit' => lwp_currency_code(),
					'pieChartFixed' => $this->_('Fixed'),
					'pieChartStart' => $this->_('Unfixed'),
					'pieChartLost' => $this->_('Lost'),
					'areaChartTitle' => $this->_('Daily Report'),
					'areaChartLabel' => $this->_('Date')
			)));
			
		}
		//In management page, do csv output
		if($this->is_admin('management') && !isset($_REQUEST['transaction_id'])){
			wp_enqueue_style('jquery-ui-datepicker');
			wp_enqueue_script('lwp-output-csv-transaction', $this->url.'assets/js/management-helper.js', array('jquery-ui-datepicker'), $this->version);
			wp_localize_script('lwp-output-csv-transaction', 'LWP', array_merge(LWP_Datepicker_Helper::get_config_array(), array(
					'pieChartTitle' => $this->_('Sales per post type'),
					'pieChartLabel' => $this->_('Post type'),
					'pieChartUnit' => lwp_currency_code(),
					'areaChartTitle' => $this->_('Daily Report'),
					'areaChartSales' => $this->_('Sales'),
					'areaChartLabel' => $this->_('Date')
			)));
			if(!isset($_GET['view'])){
				wp_enqueue_script(
					'lwp-transaction', 
					$this->url.'assets/js/transaction-summary.js',
					array('google-jsapi', 'jquery-form', 'jquery-ui-tabs'),
					$this->version
				);
			}

		}
		//Incase Reward dashboard, Load datepicker and tab UI
		if(isset($_GET['page']) && ( ($_GET['page'] == 'lwp-reward' && !isset($_GET['tab']))|| ($_GET['page'] == 'lwp-personal-reward' && !isset($_GET['tab'])) ) ){
			//Load Datepicker
			wp_enqueue_style('jquery-ui-datepicker');
			wp_enqueue_script('lwp-reward-summary', $this->url.'assets/js/reward-summary.js', array('google-jsapi', 'jquery-form', 'jquery-ui-datepicker', 'jquery-ui-tabs'), $this->version);
			wp_localize_script('lwp-reward-summary', 'LWP', array_merge(
				LWP_Datepicker_Helper::get_config_array(),
				array(
					'pieChartTitle' => $this->_('Reward Amount Summary'),
					'pieChartLabel' => $this->_('Status'),
					'pieChartUnit' => lwp_currency_code(),
					'pieChartFixed' => $this->_('Fixed'),
					'pieChartStart' => $this->_('Unfixed'),
					'pieChartLost' => $this->_('Lost'),
					'areaChartTitle' => $this->_('Daily Report'),
					'areaChartLabel' => $this->_('Date')
				)
			));
		}
		//Load event management helper
		if(isset($_GET['page']) && $_GET['page'] == 'lwp-event'){
			wp_enqueue_script('lwp-event', $this->url.'assets/js/event-manager.js', array('jquery'), $this->version);
		}
		//Add event helper on post edit page
		if(false !== array_search(basename($_SERVER['SCRIPT_FILENAME']), array('post.php', 'post-new.php') )){
			wp_enqueue_style('jquery-ui-datepicker');
			wp_enqueue_script('lwp-event-helper', $this->url.'assets/js/event-helper.js', array('jquery-effects-highlight', 'jquery-ui-timepicker'), $this->version);
			wp_localize_script('lwp-event-helper', 'LWP', array_merge(LWP_Datepicker_Helper::get_config_array(), array(
				'endpoint' => admin_url('admin-ajax.php'),
				'cancelLimitPlaceHolder' => $this->_('Cacnelable till %1$s days before, %2$s'),
				'deleteButtonLabel' => $this->_('Delete'),
				'deleteConfirmation' => $this->_('Are you sure to delete this ticket?'),
				'editButtonLabel' => $this->_('Edit'),
				'wrongRatio' => $this->_('Refund must be number(negative or positive) or percentage(ex. 50%)')
			)));
		}
	}
	
	
	/**
	 * Show message on admin panel
	 * 
	 * @since 0.3
	 * @return void
	 */
	public function admin_notice(){
		if(!empty($this->message)){
			$class = $this->error ? 'error' : 'updated';
			?>
				<div class="<?php echo $class; ?>">
					<ul>
					<?php foreach($this->message as $m): ?>
						<li><p><?php echo $m; ?></p></li>
					<?php endforeach; ?>
					</ul>
				</div>
			<?php
		}
		//Check user registration
		if(!get_option("users_can_register")){
			?>
				<div class="updated">
					<p><?php printf($this->_("User can't register. Go to <a href=\"%s\">setting page</a> and allow user to register."), admin_url('options-general.php')); ?></p>
				</div>
			<?php
		}
	}
	
	/**
	 * Add action link on plugin lists
	 * @param array $links
	 * @param string $file
	 * @return string 
	 */
	public function plugin_page_link($links, $file){
		if(false !== strpos($file, "literally-wordpress")){
			$link = '<a href="'.admin_url('admin.php?page=lwp-setting').'">'.__('Settings').'</a>';
			array_unshift( $links, $link);
		}
		return $links;
	}
	
	/**
	 * ヘルプページのURLを返す
	 * 
	 * @since 0.3
	 * @param string $name
	 * @param string $title
	 * return string
	 */
	public function help($name, $title){
		$url = $this->url."help/?name={$name}";
		if(is_ssl()){
			$url = $this->ssl($url);
		}
		$title_attr = $this->_('Literally WordPress Help'); 
		$tag = "<a class=\"thickbox\" href=\"{$url}&amp;TB_iframe=true\" title=\"{$title_attr}\">{$title}</a>";
		return $tag;
	}

	/**
	 * Customize admin bar
	 * @param WP_Admin_Bar $wp_admin_bar 
	 */
	public function admin_bar($wp_admin_bar){
		//Get purchase page url and title
		if(is_user_logged_in()){
			$wp_admin_bar->add_menu(array(
				'parent' => 'my-account',
				'id' => 'lwp-history',
				'title' => ($this->option['mypage'] ? get_the_title($this->option['mypage']) : $this->_('Purchase history')),
				'href' => lwp_history_url()
			));
		}
	}
	
	//--------------------------------------------
	//
	// 設定ページ
	//
	//--------------------------------------------
	
	/**
	 * Update option on Admin panel
	 * 
	 * @since 0.3
	 * @return void
	 */
	public function update_option(){
		if(
			isset($_REQUEST["_wpnonce"], $_REQUEST["_wp_http_referer"])
			&& false !== strpos($_REQUEST["_wp_http_referer"], "lwp-setting")
			&& wp_verify_nonce($_REQUEST["_wpnonce"], "lwp_update_option")
		){
			switch((isset($_REQUEST['view']) ? $_REQUEST['view'] : '')){
				case 'payment':
					$option = array(
						'sandbox' => isset($_REQUEST['sandbox']) ? true : false,
						"user_name" => $_REQUEST["user_name"],
						"password" => $_REQUEST["marchand_pass"],
						'signature' => $_REQUEST['signature'],
						"token" => $_REQUEST["token"],
						"slug" => $_REQUEST["product_slug"],
						"skip_payment_selection" => isset($_REQUEST['skip_payment_selection']) && (boolean)$_REQUEST['skip_payment_selection'],
						"transfer" => (boolean)$_REQUEST['transfer'],
						"notification_frequency" => (int) $_REQUEST['notification_frequency'],
						"notification_limit" => (int) $_REQUEST['notification_limit'],
						"currency_code" => $_REQUEST["currency_code"],
						"country_code" => $_REQUEST["country_code"],
					);
					break;
				case 'post':
					$option = array(
						"dir" => $_REQUEST["dir"],
						'ios' => (boolean)$_REQUEST['ios'],
						'ios_public' => (boolean)$_REQUEST['ios_public'],
						'ios_available' => (boolean)$_REQUEST['ios_available'],
						'ios_force_ssl' => (int)$_REQUEST['ios_force_ssl']
					);
					if(!empty($_REQUEST['custom_post_type_name']) && !empty($_REQUEST['custom_post_type_slug'])){
						$option['custom_post_type'] = array(
							"name" => $_REQUEST['custom_post_type_name'],
							"slug" => $_REQUEST['custom_post_type_slug']
						);
						$option['custom_post_type']['singular'] = empty($_REQUEST['custom_post_type_singular'])
																	  ? $_REQUEST['custom_post_type_name']
																	  : $_REQUEST['custom_post_type_singular'];
					}else{
						$option['custom_post_type'] = array();
					}
					$option['payable_post_types'] = array();
					if(!empty($_REQUEST['payable_post_types'])){
						foreach($_REQUEST['payable_post_types'] as $post_type){
							array_push($option['payable_post_types'], $post_type);
						}
					}
					break;
				case 'subscription':
					$option = array(
						"subscription" => (boolean)$_REQUEST['subscription'],
						"subscription_post_types" => (array)$_REQUEST['subscription_post_types'],
						'subscription_format' => (string)$_REQUEST['subscription_format']
					);
					break;
				case 'event':
					$option = array(
						'event_post_types' => (array) $_REQUEST['event_post_types'],
						'event_mail_body' => (string) $_REQUEST['event_mail_body'],
						'event_signature' => (string) $_REQUEST['event_signature']
					);
					break;
				case 'reward':
					$option = array(
						"reward_promoter" => (int) $_REQUEST['reward_promoter'],
						"reward_promotion_margin" => (int) $_REQUEST['reward_promotion_margin'],
						"reward_promotion_max" => (int) $_REQUEST['reward_promotion_max'],
						"reward_author" => (int) $_REQUEST['reward_author'],
						"reward_author_margin" => (int) $_REQUEST['reward_author_margin'],
						"reward_author_max" => (int) $_REQUEST['reward_author_max'],
						"reward_minimum" => (int) $_REQUEST['reward_minimum'],
						"reward_request_limit" => (int) $_REQUEST['reward_request_limit'],
						"reward_pay_at" => (int) $_REQUEST['reward_pay_at'],
						"reward_pay_after_month" => (int) $_REQUEST['reward_pay_after_month'],
						"reward_notice" => (string) $_REQUEST['reward_notice'],
						"reward_contact" => (string) $_REQUEST['reward_contact']
					);
					break;
				case 'misc':
					$option = array(
						"mypage" => (int) $_REQUEST["mypage"],
						"show_form" => (boolean)($_REQUEST["show_form"] == 1),
						"load_assets" => (int)$_REQUEST["load_assets"],
						"use_proxy" => (boolean) $_REQUEST['use_proxy']
					);
					break;
			}
			$new_option = shortcode_atts($this->option, $option);
			if(update_option("literally_wordpress_option", $new_option)){
				$this->message[] = $this->_('Option updated.');
			}else{
				$this->error = true;
				$this->message[] = $this->_('Failed to updated options.');
			}
			$this->option = $new_option;
			do_action('lwp_update_option', $this->option);
		}
	}
	
	
	//--------------------------------------------
	//
	// Campaign
	//
	//--------------------------------------------

	
	/**
	 * CRUD interface for Campaign
	 * @global wpdb $wpdb 
	 * @return void
	 */
	public function update_campaign(){
		global $wpdb;
		//キャンペーンの追加
		if(isset($_REQUEST["_wpnonce"]) && wp_verify_nonce($_REQUEST["_wpnonce"], "lwp_add_campaign")){
			//投稿の確認
			if(!is_numeric($_REQUEST["book_id"])){
				$this->error = true;
				$this->message[] = $this->_("Please select item.");
			}
			//価格の確認
			if(!is_numeric(mb_convert_kana($_REQUEST["price"], "n"))){
				$this->error = true;
				$this->message[] = $this->_("Price must be numeric.");
			}
			//価格の確認
			elseif($_REQUEST["price"] > get_post_meta($_REQUEST["book_id"], "lwp_price", true)){
				$this->error = true;
				$this->message[] = $this->_("Price is higher than original price.");
			}
			//形式の確認
			if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/", $_REQUEST["start"]) || !preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/", $_REQUEST["end"])){
				$this->error = true;
				$this->message[] = $this->_("Date format is invalid.");
			}
			//開始日と終了日の確認
			elseif(strtotime($_REQUEST["end"]) < time() || strtotime($_REQUEST["end"]) < strtotime($_REQUEST["start"])){
				$this->error = true;
				$this->message[] = $this->_("End date was past.");
			}
			//エラーがなければ登録
			if(!$this->error){
				global $wpdb;
				$wpdb->insert(
					$this->campaign,
					array(
						"book_id" => $_REQUEST["book_id"],
						"price" => mb_convert_kana($_REQUEST["price"], "n"),
						"start" => $_REQUEST["start"],
						"end" => $_REQUEST["end"]
					),
					array("%d", "%f", "%s", "%s")
				);
				if($wpdb->insert_id)
					$this->message[] = $this->_("Campaign added.");
				else{
					$this->error = true;
					$this->message[] = $this->_("Failed to add campaign.");
				}
			}
		}
		//キャンペーンの更新
		elseif(isset($_REQUEST["_wpnonce"]) && wp_verify_nonce($_REQUEST["_wpnonce"], "lwp_update_campaign")){
			//キャンペーンIDの存在を確認
			if(!$wpdb->get_row($wpdb->prepare("SELECT ID FROM {$this->campaign} WHERE ID = %d", $_REQUEST["campaign"]))){
				$this->error = true;
				$this->message[] = $this->_("Specified campaing doesn't exist");
			}
			//価格の確認
			if(!is_numeric(mb_convert_kana($_REQUEST["price"], "n"))){
				$this->error = true;
				$this->message[] = $this->_("Price should be numeric.");
			}elseif($_REQUEST["price"] > get_post_meta($_REQUEST["book_id"], "lwp_price", true)){
				$this->error = true;
				$this->message[] = $this->_("Campgin price is higher than original price.");
			}
			//形式の確認
			if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/", $_REQUEST["start"]) || !preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/", $_REQUEST["end"])){
				$this->error = true;
				$this->message[] = $this->_("Date format is invalid.");
			}
			//開始日と終了日の確認
			elseif(strtotime($_REQUEST["end"]) < time() || strtotime($_REQUEST["end"]) < strtotime($_REQUEST["start"])){
				$this->error = true;
				$this->message[] = $this->_("End date is earlier than start date.");
			}
			//エラーがなければ更新
			if(!$this->error){
				$req = $wpdb->update(
					$this->campaign,
					array(
						"price" => mb_convert_kana($_REQUEST["price"], "n"),
						"start" => $_REQUEST["start"],
						"end" => $_REQUEST["end"]
					),
					array("ID" => $_REQUEST["campaign"]),
					array("%d", "%s", "%s"),
					array("%d")
				);
				if($req)
					$this->message[] = $this->_("Successfully Updated.");
				else{
					$this->error = true;
					$this->message[] = $this->_('Update Failed.');
				}
			}
		}
		//キャンペーンの削除
		elseif(isset($_REQUEST["_wpnonce"]) && wp_verify_nonce($_REQUEST["_wpnonce"], "bulk-campaigns") && is_array($_REQUEST["campaigns"])){
			$sql = "DELETE FROM {$this->campaign} WHERE ID IN (".implode(",", $_REQUEST["campaigns"]).")";
			if($wpdb->query($sql))
				$this->message[] = $this->_("Campaign was deleted.");
			else{
				$this->error = true;
				$this->message[] = $this->_("Failed to delete campaign.");
			}
		}
	}
	
	/**
	 * 指定された投稿が指定された日付にキャンペーンを行っているかを返す
	 * 
	 * @param object|int $post
	 * @param string $time (optional) 指定しなければ今日の日付
	 * @return booelan
	 */
	public function is_on_sale($post = null, $time = null){
		global $wpdb;
		$post = get_post($post);
		if(!$time){
			$time = date_i18n('Y-m-d H:i:s');
		}
		$sql = "SELECT ID FROM {$this->campaign} WHERE book_id = %d AND start <= %s AND end >= %s";
		$req = $wpdb->get_row($wpdb->prepare($sql, $post->ID, $time, $time));
		return $req != false;
	}
	
	/**
	 * キャンペーンを取得する
	 * 
	 * $timeを指定しない場合はすべてのキャンペーンを返す
	 * 
	 * @param int $post_id
	 * @param string $time
	 * @param boolean $multi
	 * @return object|array 
	 */
	public function get_campaign($post_id, $time = false, $multi = false){
		global $wpdb;
		$sql = "SELECT * FROM {$this->campaign} WHERE book_id = %d";
		if($time)
			$sql .= " AND start <= %s AND end >= %s";
		$sql .= " ORDER BY `end` DESC";
		if($time)
			$sql = $wpdb->prepare($sql, $post_id, $time, $time);
		else
			$sql = $wpdb->prepare($sql, $post_id);
		if($multi)
			return $wpdb->get_results($sql);
		else
			return $wpdb->get_row($sql);
	}
	
	
	
	//--------------------------------------------
	//
	// User
	//
	//--------------------------------------------
	
	/**
	 * Redirect user if not logged in on my pages 
	 */
	public function protect_user_page(){
		if($this->option['mypage'] && is_page($this->option['mypage'])){
			if(!is_user_logged_in()){
				auth_redirect();
				die();
			}
		}
	}
	
	/**
	 *  ユーザープロフィール編集画面にプレゼント用のフォームを追加する
	 * 
	 * @global wpdb $wpdb
	 * @return void
	 */
	public function give_user_form(){
		global $wpdb;
		$user_id = $_GET["user_id"];
		$post_types = implode(',', array_map(create_function('$a', 'return "\'".$a."\'";'), $this->option['payable_post_types']));
		if(empty($post_types)){
			return array();
		}
		$sql = <<<EOS
			SELECT
				p.ID, p.post_title, p.post_type, CAST(pm.meta_value AS SIGNED) as price
			FROM {$wpdb->posts} AS p
			INNER JOIN {$wpdb->postmeta} AS pm
			ON pm.post_id = p.ID
			WHERE p.post_type IN ({$post_types})
			  AND pm.meta_key = 'lwp_price'
			  AND p.post_status = 'publish'
			  AND CAST(pm.meta_value AS SIGNED) > 0
			  AND p.ID NOT IN (
					SELECT book_id FROM {$this->transaction} WHERE user_id = %d AND status != %s
				  )
EOS;
		$ebooks = $wpdb->get_results($wpdb->prepare($sql, $user_id, LWP_Payment_Status::SUCCESS));
		require_once $this->dir.DIRECTORY_SEPARATOR."form-template".DIRECTORY_SEPARATOR."give-user.php";
	}
	
	/**
	 * ユーザーにプレゼントを渡す
	 * 
	 * @param int $user_id
	 * @return void
	 */
	public function give_user($user_id){
		if(isset($_REQUEST["ebook_id"]) && is_numeric($_REQUEST["ebook_id"])){
			global $wpdb;
			$data = get_userdata($user_id);
			$wpdb->insert(
				$this->transaction,
				array(
					"user_id" => $user_id,
					"book_id" => $_REQUEST["ebook_id"],
					"price" => 0,
					"status" => LWP_Payment_Status::SUCCESS,
					"method" => LWP_Payment_Methods::PRESENT,
					"transaction_key" => "",
					"payer_mail" => $data->user_email,
					"registered" => date('Y-m-d H:i:s'),
					"updated" => date('Y-m-d H:i:s')
				),
				array("%d", "%d", "%d", "%s", "%s", "%s", "%s", "%s", "%s")
			);
			if($wpdb->insert_id){
				$this->message[] = $this->_("You've kindly given a present.");
				//Do hook
				do_action('lwp_create_transaction', $wpdb->insert_id);
				do_action('lwp_update_transaction', $wpdb->insert_id);
			}else{
				$this->message[] = $this->_("Failed to give a present.");
			}
		}
	}
	
	/**
	 * Return if user has transaction
	 * @param object|int $post (optional)
	 * @param int $user_id (optional)
	 * @return boolean
	 */
	public function is_owner($post = null, $user_id = null){
		$post = get_post($post);
		if(!$user_id){
			$user_id = get_current_user_id();
		}
		global $wpdb;
		$sql = "SELECT ID FROM {$this->transaction} WHERE user_id = %d AND book_id = %d AND status = %s";
		return (boolean) $wpdb->get_row($wpdb->prepare($sql, $user_id, $post->ID, LWP_Payment_Status::SUCCESS));
	}
	
	/**
	 * ユーザーの購入履歴を返す
	 * 
	 * @param int $user_ID
	 * @param int $offset 何ページ目かを返す。0開始
	 * @param int $num_page 一回のリクエストで表示する数
	 * @return array 購入履歴からなる配列
	 */
	private function get_history($user_ID, $offset = 0, $num_page = 10){
		return $this->get_transaction(null, $user_ID, null, $offset, $num_page);
	}
	
	/**
	 * ユーザーがこれまでに購入した件数を返す
	 * 
	 * @param int $user_ID
	 * @param int $book_id (optional) 指定しない場合はすべてのブックが対象
	 * @return int
	 */
	private function get_total_bought($user_ID, $book_id = null){
		return count($this->get_transaction($book_id, $user_ID, null));
	}
	
	
	
	//--------------------------------------------
	//
	// トランザクション
	//
	//--------------------------------------------
	
	/**
	 * 購入情報を取得する
	 * 
	 * @param int $book_id
	 * @param int $user_id
	 * @param int $status
	 * @param int $offset
	 * @param int $num
	 * @return array
	 */
	public function get_transaction($book_id = null, $user_id = null, $status = null, $offset = null, $num = 10)
	{
		global $wpdb;
		$sql = "SELECT * FROM {$this->transaction} ";
		if($user_id || $status || $book_id)
			$sql .= "WHERE ";
		$flg = false;
		if($book_id){
			$sql .= $wpdb->prepare("book_id = %d ", $book_id);
			$flg = true;
		}
		if($user_id){
			if(!$flg)
				$flg = true;
			else
				$sql .= " AND ";
			$sql .= $wpdb->prepare("user_id = %d ", $user_id);
		}
		if($status){
			if($flg)
				$sql .= " AND ";
			$sql .= $wpdb->prepare("status = %s ", $status);
		}
		$sql .= "ORDER BY `registered` DESC ";
		if(is_numeric($offset))
			if($offset == 0)
				$sql .= "LIMIT {$num} ";
			else
				$sql .= "LIMIT ".$offset * $num.", {$num} ";
		return $wpdb->get_results($sql);
	}
	
	/**
	 * Update transaction
	 * @return void
	 */
	public function update_transaction(){
		//Check nonce If this is a 
		if(isset($_REQUEST["_wpnonce"], $_REQUEST['transaction_id']) && wp_verify_nonce($_REQUEST["_wpnonce"], "lwp_update_transaction")){
			//Update Data
			global $wpdb;
			$req = false;
			if(isset($_REQUEST['status']) && false !== array_search($_REQUEST['status'], LWP_Payment_Status::get_all_status())){
				//If to make it refunded on paypal transaction, 
				//change must be done in 60 days.
				$flg = true;
				$transaction = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->transaction} WHERE ID = %d", $_POST['transaction_id']));
				if($_POST['status'] == LWP_Payment_Status::REFUND && $transaction->method == LWP_Payment_Methods::PAYPAL && $transaction->status == LWP_Payment_Status::SUCCESS){
					//Check if refundable
					if(!PayPal_Statics::is_refundable($transaction->updated)){ //Unrefundable
						$this->message[] = $this->_("You can't refund via PayPal because 60 days have past since the transaction occurred.");
						$this->error = true;
						$flg = false;
					}else{ //Refundable
						if(PayPal_Statics::do_refund($transaction->transaction_id)){
							$this->message[] = $this->_("Refund succeeded.");
						}else{
							$this->message[] = $this->_("Sorry, but PayPal denied.");
							$this->error = true;
							$flg = false;
						}
					}
				}
				if($flg){
					$req = $wpdb->update(
						$this->transaction,
						array(
							'status' => $_POST['status'],
							'updated' => gmdate('Y-m-d H:i:s')
						),
						array('ID' => $_POST['transaction_id']),
						array('%s', '%s'),
						array('%d')
					);
				}
			}
			if(isset($_REQUEST['expires']) && preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/", $_REQUEST['expires'])){
				$req = $wpdb->update(
					$this->transaction,
					array(
						'expires' => $_POST['expires'],
						'updated' => gmdate('Y-m-d H:i:s')
					),
					array('ID' => $_POST['transaction_id']),
					array('%s', '%s'),
					array('%d')
				);
			}
			if($req){
				$this->message[] = $this->_("Transaction was updated.");
			}else{
				$this->message[] = $this->_("Failed to update transaction.");
			}
		}
	}
	
	
	//--------------------------------------------
	//
	// 公開画面
	//
	//--------------------------------------------
	

	
	/**
	 * トランザクションを開始する
	 * 
	 * @since 0.8
	 * @global wpdb $wpdb
	 * @param int $user_id
	 * @param int $post_id
	 * @param boolean $billing
	 * @return boolean 失敗した時だけfalseを返す
	 */
	public function start_transaction($user_id, $post_id, $billing){
		global $wpdb;
		//トランザクションを作る
		$price = lwp_price($post_id);
		//トークンを取得
		$invnum = sprintf("{$this->option['slug']}-%08d-%05d-%d", $post_id, $user_id, time());
		$token = PayPal_Statics::get_transaction_token($price, $invnum, lwp_endpoint('confirm'), lwp_endpoint('cancel'), $billing);
		if($token){
			//トークンが帰ってきたら、データベースに保存
			$wpdb->insert(
				$this->transaction,
				array(
					"user_id" => $user_id,
					"book_id" => $post_id,
					"price" => $price,
					"status" => "START",
					"method" => "PAYPAL",
					"transaction_key" => $invnum,
					"transaction_id" => $token,
					"registered" => gmdate('Y-m-d H:i:s'),
					"updated" => gmdate('Y-m-d H:i:s')
				),
				array("%d", "%d", "%d", "%s", "%s", "%s", "%s", "%s", "%s")
			);
			//Execute hook
			do_action('lwp_create_transaction', $wpdb->insert_id);
			//Redirect to Paypal
			PayPal_Statics::redirect($token);
			exit;
		}else{
			//No response from Paypal
			return false;
		}
	}
	
	/**
	 * Hook the_content to display purchase history
	 * @since 0.3
	 * @global object $post
	 * @param string $content
	 * @return string
	 */
	public function the_content($content){
		if($this->option["mypage"] > 0 && is_page($this->option["mypage"])){
			if(!class_exists('WP_List_Table')){
				$path = ABSPATH.'wp-admin/includes/class-wp-list-table.php';
				if(!file_exists($path)){
					return $content;
				}else{
					require_once $path;
				}
			}
			require_once $this->dir.DIRECTORY_SEPARATOR."tables".DIRECTORY_SEPARATOR."list-history.php";
			ob_start();
			$table = new LWP_List_History();
			$table->prepare_items();
			do_action("admin_notices");
			$table->display();
			$book_shelf = ob_get_contents();
			ob_end_clean();
			$content = '<form id="book-shelf" method="get" action="'.get_permalink().'">'.$book_shelf.'</form>'.$content;
		}
		return $content;
	}
	
	/**
	 * Output transaction CSV 
	 * @global wpdb $wpdb
	 */
	public function ouput_csv(){
		if(!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'lwp_transaction_csv_output')){
			status_header(403);
			die();
		}
		//If current user can get CSV
		$cap = current_user_can('edit_others_posts');
		if(!apply_filters('lwp_transaction_csv', $cap)){
			status_header(403);
			die();
		}
		//Now let's start output csv
		global $wpdb;
		$sql = <<<EOS
			SELECT DISTINCT
				t.*, p.post_title, u.user_login, u.display_name
			FROM {$this->transaction} AS t
			INNER JOIN {$wpdb->posts} AS p
			ON t.book_id = p.ID
			LEFT JOIN {$wpdb->users} AS u
			ON t.user_id = u.ID
EOS;
		$wheres = array();
		//Detect where
		if(isset($_REQUEST['status']) && $_REQUEST['status'] != 'all'){
			$wheres[] = $wpdb->prepare("t.status = %s", $_REQUEST['status']);
		}
		if(isset($_REQUEST['post_type']) && $_REQUEST['post_type'] != 'all'){
			$wheres[] = $wpdb->prepare("p.post_type = %s", $_REQUEST['post_type']);
		}
		if(isset($_REQUEST['from']) && preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $_REQUEST['from'])){
			$wheres[] = $wpdb->prepare("t.registered >= %s", $_REQUEST['from'].' 00:00:00');
		}
		if(isset($_REQUEST['to']) && preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $_REQUEST['to'])){
			$wheres[] = $wpdb->prepare("t.registered <= %s", $_REQUEST['to'].' 23:59:59');
		}
		if(!empty($wheres)){
			$sql .= ' WHERE '.implode(' AND ', $wheres);
		}
		//Order by
		$sql .= ' ORDER BY t.registered ASC';
		$results = $wpdb->get_results($sql);
		//Nothing found, tell so
		if(empty($results)){
			status_header(404);
			$this->e('No ticket match your qriteria.: '.$wpdb->last_query);
			die();
		}
		//Start output csv
		header('Content-Type: application/x-csv');
		header("Content-Disposition: attachment; filename=".rawurlencode(get_bloginfo('name').date('YmdHis')).".csv");
		global $is_IE;
		if($is_IE){
			header("Cache-Control: public");
			header("Pragma:");
		}
		$out = fopen('php://output', 'w');
		$first_row = apply_filters('lwp_transaction_csv_header', array(
			$this->_('Item Name'),
			$this->_('Quantity'),
			$this->_('Price'),
			$this->_('Payment Method'),
			$this->_('Invoice Num'),
			$this->_('Transaction ID'),
			$this->_('User Name'),
			$this->_('Registered'),
			$this->_('Updated'),
			$this->_('Transaction Status')
		));
		mb_convert_variables('sjis-win', 'utf-8', $first_row);
		fputcsv($out, $first_row);
		set_time_limit(0);
		foreach($results as $result){
			$row = apply_filters('lwp_transaction_csv_row', array(
				$result->post_title,
				$result->num,
				$result->price,
				$this->_($result->method),
				(string)$result->transaction_key,
				(string)$result->transaction_id,
				($result->display_name ? $result->display_name.'('.$result->user_login.')' : $this->_('Deleted User')),
				$result->registered,
				$result->updated,
				$this->_($result->status)
			),$result);
			mb_convert_variables('sjis-win', 'utf-8', $row);
			fputcsv($out, $row);
		}
		fclose($out);
		die();
	}
	
	/**
	 * 決済情報を取得する
	 */
	public function ajax_transaction_chart(){
		if(isset($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'lwp_area_chart')){
			global $wpdb;
			$from = isset($_REQUEST['from']) ? $_REQUEST['from'] : date('Y-m-d', time() - 60 * 60 * 24 * 30 );
			$to = isset($_REQUEST['to']) ? $_REQUEST['to'] : date('Y-m-d');
			$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : 'all';
			$post_type = isset($_REQUEST['post_type']) ? $_REQUEST['post_type'] : 'all';
			$sql = <<<EOS
				SELECT SUM(t.price) AS total, DATE(t.updated) AS date
				FROM {$this->transaction} AS t
				LEFT JOIN {$wpdb->posts} AS p
				ON t.book_id = p.ID
EOS;
			$wheres = array(
				$wpdb->prepare("t.updated >= %s", $from),
				$wpdb->prepare("t.updated <= %s", $to)
			);
			if($status != 'all'){
				$wheres[] = $wpdb->prepare("t.status = %s", $status);
			}
			if($post_type != 'all'){
				$wheres[] = $wpdb->prepare("p.post_type = %s", $status);
			}
			$sql .= ' WHERE '.implode(' AND ', $wheres);
			$sql .= <<<EOS
				GROUP BY date
				ORDER BY date ASC
EOS;
			$result = $wpdb->get_results($sql, ARRAY_A);
			$date_array = array();
			$cur_date = $from;
			do{
				$array = array(
					'date' => $cur_date,
					'total' => 0
				);
				foreach($result as $r){
					if($r['date'] == $cur_date){
						$array = $r;
						break;
					}
				}
				$date_array[] = $array;
				$cur_date = date('Y-m-d', strtotime($cur_date) + 60 * 60 * 24);
			}while(strtotime($cur_date) <= strtotime($to));
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode($date_array);
			exit;
		}
	}
	
	/**
	 * Returns if form should be output automatically
	 * @return boolean
	 */
	public function needs_auto_layout(){
		return (boolean)$this->option['show_form'];
	}
	
	/**
	 * 管理画面の該当するページか否かを返す
	 * 
	 * @param string $page_name ページ名
	 * @return boolean
	 */
	private function is_admin($page_name){
		switch($page_name){
			case "campaign":
			case "setting":
			case "management":
			case "devices":
			case "transfer":
				return (isset($_GET["page"]) && $_GET["page"] == "lwp-{$page_name}");
				break;
			case "history":
				return (basename($_SERVER["SCRIPT_FILENAME"]) == "users.php" && $_REQUEST["page"] == "lwp-history");
				break;
		}
	}
		
	/**
	 * Return url to ssl
	 * @param string $url
	 * @return string
	 */
	public function ssl($url){
		return str_replace("http:", "https:", $url);
	}
	
	/**
	 * gettextのエイリアス
	 * 
	 * @param string $text
	 * @return void
	 */
	public function e($text){
		echo _e($text, $this->domain);
	}
	
	/**
	 * gettextのエイリアス
	 * 
	 * @param string $text
	 * @return string
	 */
	public function _($text){
		return __($text, $this->domain);
	}
	
	/**
	 * 翻訳対象にならないものPoeditでひっかけるため
	 * @return void
	 */
	private function ___(){
		$this->_('This plugin make your WordPress post payable. Registered users can buy your post via PayPal. You can provide several ways to reward their buying. Add rights to download private file, to accesss private post and so on.');
	}
}
