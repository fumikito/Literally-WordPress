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
	public $version = "0.8.8";
	
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
	 * キャンペーンテーブル
	 * 
	 * @var string
	 */
	public $campaign = "";
	
	/**
	* トランザクションテーブル
	*
	* @var string
	*/
	public $transaction = "";
	
	/**
	* ファイルテーブル
	*
	* @var string
	*/
	public $files = "";
	
	
	/**
	* 端末テーブル
	*
	* @var string
	*/
	public $devices = "";
	
	
	/**
	* ファイルと端末の関係テーブル
	*
	* @var string
	*/
	public $file_relationships = "";
	
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
	 * Notification Utility
	 * @var LWP_Notifier
	 */
	public $notifier = null;
	
		
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
	public function __construct()
	{
		global $wpdb;
		//初期値の設定
		$this->url = plugin_dir_url(__FILE__);
		$this->dir = dirname(__FILE__);
		//テーブル名の設定
		$this->campaign = LWP_Tables::campaign();
		$this->transaction = LWP_Tables::transaction();
		$this->files = LWP_Tables::files();
		$this->devices = LWP_Tables::devices();
		$this->file_relationships = LWP_Tables::file_relationships();
		//テキストドメインを設定する
		load_plugin_textdomain($this->domain, false, basename($this->dir).DIRECTORY_SEPARATOR."language");
		////オプションの設定
		$this->option = array();
		$saved_option = get_option("literally_wordpress_option");
		$default_option =  array(
			"db_version" => 0,
			"sandbox" => false,
        	"user_name" => "",
			"password" => "",
			"signature" => "",
        	"token" => "",
			"subscription" => false,
			"subscription_post_types" => array(),
			'subscription_format' => 'all',
			'transfer' => false,
			"notification_frequency" => 0,
			"notification_limit" => 30,
			"slug" => str_replace(".", "", $_SERVER["HTTP_HOST"]),
			"currency_code" => '',
			"country_code" => '',
			"mypage" => 0,
			"custom_post_type" => array(),
			"payable_post_types" => array(),
			"show_form" => true,
			"load_assets" => 2
		);
		//Set up upload directory
		$upload_dir = wp_upload_dir();
		$default_option['dir'] = $upload_dir['basedir'].DIRECTORY_SEPARATOR."lwp";
		foreach($default_option as $k => $v){
			if(isset($saved_option[$k]))
				$this->option[$k] = $saved_option[$k];
			else
				$this->option[$k] = $v;
		}
		// 作成したカスタムポストタイプが
		// Payableオプションに入っていなかったら追加する
		// あと、単数形が指定されていなかったら同じにする
		if(!empty($this->option['custom_post_type'])){
			if(empty($this->option['custom_post_type']['singular'])){
				$this->option['custom_post_type']['singular'] = $this->option['custom_post_type']['name'];
			}
			if(false === array_search($this->option['custom_post_type']['slug'], $this->option['payable_post_types'])){
				array_push($this->option['payable_post_types'], $this->option['custom_post_type']['slug']);
			}
		}
		
		//オプション更新
		if($this->is_admin("setting")){
			add_action('init', array($this, 'update_option'));
		}
		
		//Add Custom Post Type
		add_action("init", array($this, "custom_post"));
		//Register Script Library
		add_action('init', array($this, 'register_assets'));
		//ウィジェットの登録
		add_action('widgets_init', array($this, 'widgets'));
		//ショートコードの追加
		add_shortcode("lwp", array($this, "shortcode_capability"));
		//いますぐ購入ボタンのショートコード
		add_shortcode('buynow', array($this, 'shortcode_buynow'));
		//Initialize Notification Utility
		$this->notifier = new LWP_Notifier($this->option['transfer'], $this->option['notification_frequency'], $this->option['notification_limit']);
	}
	
	/**
	* カスタム投稿タイプを追加する
	*
	* @return void
	*/
	public function custom_post(){
		if(!empty($this->option['custom_post_type'])){
			//投稿タイプを設定
			$labels = array(
				'name' => $this->option['custom_post_type']['name'],
				'singular_name' => $this->option['custom_post_type']['singular'],
				'add_new' => $this->_('Add New'),
				'add_new_item' => sprintf($this->_('Add New %s'), $this->option['custom_post_type']['singular']),
				'edit_item' => sprintf($this->_("Edit %s"), $this->option['custom_post_type']['name']),
				'new_item' => sprintf($this->_('Add New %s'), $this->option['custom_post_type']['singular']),
				'view_item' => sprintf($this->_('View %s'), $this->option['custom_post_type']['singular']),
				'search_items' => sprintf($this->_("Search %s"), $this->option['custom_post_type']['name']),
				'not_found' =>  sprintf($this->_('No %s was found.'), $this->option['custom_post_type']['singular']),
				'not_found_in_trash' => sprintf($this->_('No %s was found in trash.'), $this->option['custom_post_type']['singular']), 
				'parent_item_colon' => ''
			);
			$args = array(
				'labels' => $labels,
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true, 
				'query_var' => true,
				'rewrite' => true,
				'capability_type' => 'post',
				'hierarchical' => true,
				'menu_position' => 9,
				'has_archive' => true,
				'supports' => array('title','editor','author','thumbnail','excerpt', 'comments', 'custom-fields'),
				'show_in_nav_menus' => true,
				'menu_icon' => $this->url."/assets/book.png"
			);
			register_post_type($this->option['custom_post_type']['slug'], $args);
		}
	}
	
	/**
	 * Register Assets for this plugin.
	 * @return void
	 */
	public function register_assets(){
		wp_register_script("jquery-ui-slider", $this->url."/assets/datepicker/jquery-ui-slider.js", array("jquery-ui-core", "jquery-ui-widget", "jquery-ui-mouse"), "1.8.12", !is_admin());
		wp_register_script("jquery-ui-datepicker", $this->url."/assets/datepicker/jquery-ui-datepicker.js",array("jquery-ui-core") ,"1.8.12", !is_admin());
		wp_register_script("jquery-ui-timepicker", $this->url."/assets/datepicker/jquery-ui-timepicker.js",array("jquery-ui-datepicker", 'jquery-ui-slider') ,"0.9.7", !is_admin());
		wp_register_style("jquery-ui-datepicker", $this->url."/assets/datepicker/smoothness/jquery-ui.css", array(), "1.8.9");
	}
	
	
	
	/**
	 * 管理画面のときにだけ行うフックの登録
	 * 
	 * @return void
	 */
	public function admin_hooks(){
		/*--------------
		 * アクションフック
		 */
		//Check table and 
		add_action('plugins_loaded', array($this, 'table_create'));
		//課金有効かどうかの判断
		add_action("admin_init", array($this, "validate"));
		//スタイルシート・JSの追加
		if(isset($_GET["page"]) && false !== strpos($_GET["page"], "lwp-")){
			add_action("admin_init", array($this, "admin_assets"));
		}
		//キャンペーン更新
		if($this->is_admin("campaign")){
			add_action("admin_init", array($this, "update_campaign"));
		}
		//トランザクション更新
		if($this->is_admin("management")){
			add_action("admin_init", array($this, "update_transaction"));
		}
		//端末更新
		if($this->is_admin("devices")){
			add_action("admin_init", array($this, "update_devices"));
		}
		//電子書籍のアップデート
		add_action("edit_post", array($this, "post_update"));
		//メニューの追加
		add_action("admin_menu", array($this, "add_menu"), 1);
		//メッセージの出力
		add_action("admin_notice", array($this, "admin_notice"));
		//ファイルアップロード用のタブを追加
		add_action("media_upload_ebook", array($this, "generate_tab"));
		//ユーザーに書籍をプレゼントするフォーム
		add_action("edit_user_profile", array($this, "give_user_form"));
		//書籍プレゼントが実行されたら
		if(basename($_SERVER["SCRIPT_FILENAME"]) == "user-edit.php"){
			add_action("profile_update", array($this, "give_user"));
		}
		
		/*--------------
		 * フィルターフック
		 */
		//ファイルアップロードのタブ生成アクションを追加するフィルター
		add_filter("media_upload_tabs", array($this, "upload_tab"));
		//ファイルアップロード可能な拡張子を追加する
		add_filter("upload_mimes", array($this, "upload_mimes"));
		//tinyMCEにボタンを追加する
		add_filter("mce_external_plugins", array($this, "mce_plugin"));
		add_filter("mce_external_languages", array($this, "mce_lang"));
		add_filter("mce_buttons_2", array($this, "mce_button"));
	}
	
	
	
	/**
	 * プラグインを有効化しても問題がないかどうかチェックする
	 * 
	 * @return void
	 */
	public function validate(){
		//Check directory's existance and if not, try to careate
		if(!is_dir($this->option['dir']) || !file_exists($this->option['dir'])){
			if(!mkdir($this->option['dir'], true)){
				$this->initialized = false;
				$this->message[] = sprintf($this->_('Can\'t make directory. Check parmissin of "%s"'), dirname($this->option['dir']));
				$this->error = true;
			}else{
				@chmod($this->option['dir'], 0700);
			}
		}
		//Check if directory is writable.
		if(!is_writable($this->option["dir"])){
			$this->initialized = false;
			$this->message["dir"] = $this->_('Directory isn\'t writable.');
			$this->error = true;
		}
		//Check if Directory is outside of plugin
		if(0 === strpos(dirname(__FILE__), $this->option['dir'])){
			$this->message[] = $this->_("Your contents directory is inside plugins folder. Strongly recommended to place it outside of plugin folder to prevent it from being deleted on updating.");
		}
		//If contents folder is in document root tree, check it's accessibility
		if(false !== strpos($this->option["dir"], ABSPATH)){
			//Create access check file if not exists.
			$access_check_file = $this->option["dir"].DIRECTORY_SEPARATOR."access";
			if(!file_exists($access_check_file)){
				@file_put_contents($access_check_file, $this->_('Warning! This file is accessible!'));
			}
			//Create .htaccess if not exists
			$htaccess_path = $this->option["dir"].DIRECTORY_SEPARATOR.".htaccess";
			$htaccess = <<<EOS
<FilesMatch ".*$">
        Order allow,deny
        deny from all
</FilesMatch>

EOS;
			if(!file_exists($htaccess_path)){
				@file_put_contents($htaccess_path, $htaccess);
			}
			//Try to access via HTTP
			$test_url = str_replace(ABSPATH, get_bloginfo("url")."/", $access_check_file);
			$ch = curl_init($test_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$req = curl_exec($ch);
			//Check HTTP status code
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if($http_code == 200){
				$this->initialized = false;
				$this->message["access"] = $this->_('Directory is publically accessible via HTTP');
				$this->error = true;
			}
		}
		//課金できるかどうかチェック
		if(empty($this->option["user_name"]) || empty($this->option["token"])){
			$this->initialized = false;
			$this->message["paypal"] = $this->_('Marchand ID and PDT Token required for transaction');
			$this->error = true;
		}
		//通貨と国が設定されているかをチェック
		if(false == array_key_exists($this->option['currency_code'], PayPal_Statics::currency_codes())){
			$this->initialized = false;
			$this->message["currency"] = $this->_("Currency code is invalid.");
			$this->error = true;
		}
		if(false == array_key_exists($this->option['country_code'], PayPal_Statics::country_codes())){
			$this->initialized = false;
			$this->message["country"] = $this->_("Country code is invalid.");
			$this->error = true;
		}
		//ユーザーが登録可能かチェック
		if(!get_option("users_can_register")){
			$this->message['registration'] = sprintf($this->_("User can't register. Go to <a href=\"%s\">setting page</a> and allow user to register."), admin_url('options-general.php'));
		}
	}
	
	
	
	/**
	* テーブル作成
	*
	* @return void
	*/
	public function table_create(){
		global $wpdb;
		//バージョンの確認
		if($this->version > $this->option['db_version']){
			$wpdb->show_errors();
			//Change Field name because desc is 
			$row = null;
			foreach($wpdb->get_results("DESCRIBE {$this->files}") as $field){
				if($field->Field == 'desc'){
					$row = $field;
					break;
				}
			}
			if($row){
				$wpdb->query("ALTER TABLE {$this->files} CHANGE COLUMN `desc` `detail` TEXT NOT NULL");
			}
			$char = defined("DB_CHARSET") ? DB_CHARSET : "utf8";
			$sql = array();
			$sql[] = <<<EOS
				CREATE TABLE {$this->files} (
					ID INT NOT NULL AUTO_INCREMENT,
					book_id BIGINT NOT NULL,
					name VARCHAR(255) NOT NULL,
					detail TEXT NOT NULL,
					file VARCHAR(255) NOT NULL,
					public INT NOT NULL DEFAULT 1,
					free INT NOT NULL DEFAULT 0,
					registered DATETIME NOT NULL,
					updated DATETIME NOT NULL,
					PRIMARY KEY  (ID)
				) ENGINE = MYISAM DEFAULT CHARSET = {$char};
EOS;
			$sql[] = <<<EOS
				CREATE TABLE {$this->transaction} (
					ID BIGINT NOT NULL AUTO_INCREMENT,
					user_id BIGINT NOT NULL,
					book_id BIGINT NOT NULL,
					price BIGINT NOT NULL,
					status VARCHAR(45) NOT NULL,
					method VARCHAR(100) NOT NULL DEFAULT 'PAYPAL',
					transaction_key VARCHAR (255) NOT NULL,
					transaction_id VARCHAR (255) NOT NULL,
					payer_mail VARCHAR (255) NOT NULL,
					registered DATETIME NOT NULL,
					updated DATETIME NOT NULL,
					expires DATETIME NOT NULL, 
					PRIMARY KEY  (ID)
				) ENGINE = MYISAM DEFAULT CHARSET = {$char};
EOS;
			$sql[] = <<<EOS
				CREATE TABLE {$this->campaign} (
					ID INT NOT NULL AUTO_INCREMENT,
					book_id BIGINT NOT NULL,
					price BIGINT NOT NULL,
					start DATETIME NOT NULL,
					end DATETIME NOT NULL,
					PRIMARY KEY  (ID)
				) ENGINE = MYISAM DEFAULT CHARSET = {$char};
EOS;
			$sql[] = <<<EOS
				CREATE TABLE {$this->devices} (
					ID BIGINT NOT NULL AUTO_INCREMENT,
					name VARCHAR(255) NOT NULL,
					slug VARCHAR(255) NOT NULL,
					PRIMARY KEY  (ID)
				) ENGINE = MYISAM DEFAULT CHARSET = {$char};
EOS;
			$sql[] = <<<EOS
				CREATE TABLE {$this->file_relationships} (
					ID BIGINT NOT NULL AUTO_INCREMENT,
					file_id INT NOT NULL,
					device_id INT NOT NULL,
					PRIMARY KEY  (ID)
				) ENGINE = MYISAM DEFAULT CHARSET = {$char};
EOS;
			//テーブルの作成
			require_once ABSPATH."wp-admin/includes/upgrade.php";
			foreach($sql as $s){
				dbDelta($s);
			}
			$this->option['db_version'] = $this->version;
			update_option("literally_wordpress_option", $this->option);
		}
	}
	
	/**
	 * 公開画面で登録するフック
	 * 
	 * @return void
	 */
	public function public_hooks()
	{
		//ヘッダー部分でリクエストの内容をチェックする
		add_action("template_redirect", array($this, "manage_actions"));
		//the_contentにフックをかける
		add_filter('the_content', array($this, "the_content"));
		//設定でJSを出力するようになっていたら出力
		if($this->option['load_assets'] > 0){
			wp_enqueue_script("lwp-timer", $this->url."assets/js/form-timer.js", array('jquery'), $this->version, true);
		}
		//設定でCSSを出力するようになっていたら出力
		if($this->option['load_assets'] > 1){
			wp_enqueue_style("lwp-timer", $this->url."assets/lwp-buynow.css", array(), $this->version);
		}
	}
	
	/**
	 * 管理画面にサブメニューを追加する
	 * 
	 * @return void
	 */
	public function add_menu()
	{
		//設定ページの追加
		add_menu_page("Literally WordPress", "Literally WP", 5, "lwp-setting", array($this, "load"), $this->url."/assets/book.png");
		add_submenu_page("lwp-setting", $this->_("General Setting"), $this->_("General Setting"), 'manage_options', "lwp-setting", array($this, "load"));
		add_submenu_page("lwp-setting", $this->_("Transaction Management"), $this->_("Transaction Management"), 'edit_posts', "lwp-management", array($this, "load"));
		if($this->option['transfer']){
			add_submenu_page("lwp-setting", $this->_("Transfer Management"), $this->_("Transfer Management"), 'edit_posts', "lwp-transfer", array($this, "load"));
		}
		add_submenu_page("lwp-setting", $this->_("Campaing Management"), $this->_("Campaing Management"), 'edit_posts', "lwp-campaign", array($this, "load"));
		add_submenu_page("lwp-setting", $this->_("Device Setting"), $this->_("Device Setting"), 'edit_others_posts', "lwp-devices", array($this, "load"));
		//顧客の購入履歴確認ページ
		add_submenu_page("profile.php", $this->_("Purchase History"), $this->_("Purchase"), 0, "lwp-history", array($this, "load"));
		//メタボックスの追加
		foreach($this->option['payable_post_types'] as $post){
			add_meta_box('lwp-detail', $this->_("Literally WordPress Setting"), array($this, 'post_metabox_form'), $post, 'side', 'core');
		}
	}
	
	/**
	 * 管理画面用のファイルを読み込む
	 * 
	 * @return void
	 */
	public function load()
	{
		if(isset($_GET["page"]) && (false !== strpos($_GET["page"], "lwp-"))){
			$slug = str_replace("lwp-", "", $_GET["page"]);
			if(file_exists($this->dir.DIRECTORY_SEPARATOR."admin".DIRECTORY_SEPARATOR."{$slug}.php")){
				global $wpdb;
				echo '<div class="wrap">';
				do_action("admin_notice");
				echo "<div class=\"icon32 ebook\"><br /></div>";
				require_once $this->dir.DIRECTORY_SEPARATOR."admin".DIRECTORY_SEPARATOR."{$slug}.php";
				require_once $this->dir.DIRECTORY_SEPARATOR."admin".DIRECTORY_SEPARATOR."donate.php";
				echo "</div>\n<!-- .wrap ends -->";
			}else
				return;
		}elseif(false !== strpos($_SERVER["REQUEST_URI"], "users.php")){
			global $wpdb;
			echo '<div class="wrap">';
			do_action("admin_notice");
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
		wp_enqueue_style("lwp-admin", $this->url."/assets/style.css", array(), $this->version);
		wp_enqueue_style("thickbox");
		wp_enqueue_script("thickbox");
		//In case management or campaign, load datepicker.
		if(($this->is_admin('management') && isset($_REQUEST['transaction_id'])) || $this->is_admin('campaign')){
			//datepickerを読み込み
			wp_enqueue_style('jquery-ui-datepicker');
			wp_enqueue_script(
				"lwp-datepicker-load",
				$this->url."/assets/js/campaign.js",
				array("jquery-ui-timepicker"),
				$this->version
			);
			for($i = 1; $i <= 12; $i++){
				if(!$monthNames) $monthNames = array();
				if(!$monthNamesShort) $monthNamesShort = array();
				$month = gmmktime(0, 0, 0, $i, 1, 2011);
				$monthNames[] = date_i18n('F', $month);
				$monthNamesShort[] = date_i18n('M', $month);
			}
			$dayNames = array(__('Sunday'), __('Monday'), __('Tuesday'), __('Wednesday'), __('Thursday'), __('Friday'), __('Saturday'));
			$dayNamesShort = array(__('Sun'), __('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat'));
			wp_localize_script('lwp-datepicker-load', 'LWPDatePicker', array(
				'closeText' => $this->_('Close'),
				'prevText' => $this->_('Prev'),
				'nextText' => $this->_('Next'),
				'monthNames' => implode(',', $monthNames),
				'monthNamesShort' => implode(',', $monthNamesShort),
				'dayNames' => implode(',', $dayNames),
				'dayNamesShort' => implode(',', $dayNamesShort),
				'dayNamesMin' => implode(',', $dayNamesShort),
				'weekHeader' => $this->_('Week'),
				'timeOnlyTitle' => $this->_('Time'),
				'timeText' => $this->_('Time'),
				'hourText' => $this->_('Hour'),
				'minuteText' => $this->_('Minute'),
				'secondText' => $this->_('Second'),
				'currentText' => $this->_('Now')
			));
		}
	}
	
	
	/**
	 * 管理画面でメッセージを発行する
	 * 
	 * @since 0.3
	 * @return void
	 */
	public function admin_notice()
	{
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
	}
	
	/**
	 * ヘルプページのURLを返す
	 * 
	 * @since 0.3
	 * @param string $name
	 * @param string $title
	 * return string
	 */
	public function help($name, $title)
	{
		$url = $this->url."help/?name={$name}";
		if(is_ssl()){
			$url = $this->ssl($url);
		}
		$title_attr = $this->_('Literally WordPress Help'); 
		$tag = "<a class=\"thickbox\" href=\"{$url}&amp;TB_ifrmae=1\" title=\"{$title_attr}\">{$title}</a>";
		return $tag;
	}


	
	//--------------------------------------------
	//
	// 設定ページ
	//
	//--------------------------------------------
	
	/**
	 * 設定更新時の処理
	 * 
	 * @since 0.3
	 * @return void
	 */
	public function update_option()
	{
		//要素が揃っていたら更新
		if(
			isset($_REQUEST["_wpnonce"], $_REQUEST["_wp_http_referer"])
			&& false !== strpos($_REQUEST["_wp_http_referer"], "lwp-setting")
			&& wp_verify_nonce($_REQUEST["_wpnonce"], "lwp_update_option")
		){
			$new_option = array(
				"user_name" => $_REQUEST["user_name"],
				"password" => $_REQUEST["marchand_pass"],
				'signature' => $_REQUEST['signature'],
				"token" => $_REQUEST["token"],
				"transfer" => (boolean)$_REQUEST['transfer'],
				"notification_frequency" => (int) $_REQUEST['notification_frequency'],
				"notification_limit" => (int) $_REQUEST['notification_limit'],
				"dir" => $_REQUEST["dir"],
				"slug" => $_REQUEST["product_slug"],
				"mypage" => $_REQUEST["mypage"],
				"currency_code" => $_REQUEST["currency_code"],
				"country_code" => $_REQUEST["country_code"],
				"show_form" => (boolean)($_REQUEST["show_form"] == 1),
				"load_assets" => (int)$_REQUEST["load_assets"],
				"subscription" => (boolean)$_REQUEST['subscription'],
				"subscription_post_types" => (array)$_REQUEST['subscription_post_types'],
				'subscription_format' => (string)$_REQUEST['subscription_format'],
			);
			//sandbox
			$new_option['sandbox'] = isset($_REQUEST['sandbox']) ? true : false;
			if(!empty($_REQUEST['custom_post_type_name']) && !empty($_REQUEST['custom_post_type_slug'])){
				$new_option['custom_post_type'] = array(
					"name" => $_REQUEST['custom_post_type_name'],
					"slug" => $_REQUEST['custom_post_type_slug']
				);
				$new_option['custom_post_type']['singular'] = empty($_REQUEST['custom_post_type_singular'])
															  ? $_REQUEST['custom_post_type_name']
															  : $_REQUEST['custom_post_type_singular'];
			}else{
				$new_option['custom_post_type'] = array();
			}
			$new_option['payable_post_types'] = array();
			if(!empty($_REQUEST['payable_post_types'])){
				foreach($_REQUEST['payable_post_types'] as $post_type){
					array_push($new_option['payable_post_types'], $post_type);
				}
			}
			update_option("literally_wordpress_option", $new_option);
			$this->option = $new_option;
		}
	}
	
	
	//--------------------------------------------
	//
	// 電子書籍登録ページ
	//
	//--------------------------------------------
	
	/**
	 * 投稿更新時の処理
	 * 
	 * @return void
	 */
	public function post_update()
	{
		if(isset($_REQUEST["_lwpnonce"]) && wp_verify_nonce($_REQUEST["_lwpnonce"], "lwp_price")){
			//価格を登録（必須のため、なければエラー）
			$price = preg_replace("/[^0-9]/", "", mb_convert_kana($_REQUEST["lwp_price"], "n"));
			if(preg_match("/^[0-9]+$/", $price)){
				update_post_meta($_POST["ID"], "lwp_price", $price);
			}else{
				$this->message[] = $this->_("Price must be numeric.");
				$this->error = true;
			}
		} 
	}
	
	/**
	 * 投稿ページに追加するフォーム
	 * 
	 * @return void
	 */
	public function post_metabox_form()
	{
		$files = isset($_GET['post']) ? $this->get_files($_GET["post"]) : array();
		require_once $this->dir.DIRECTORY_SEPARATOR."form-template".DIRECTORY_SEPARATOR."edit-detail.php";
	}
	
	//--------------------------------------------
	//
	// 電子書籍ファイルアップロード
	//
	//--------------------------------------------
	
	/**
	 * アップローダーにタブ生成アクションを追加する
	 * 
	 * @param array $tabs
	 * @return array
	 */
	public function upload_tab($tabs)
	{
		if($this->initialized)
			$tabs["ebook"] = $this->_('Literally WordPress');
		return $tabs;
	}
	
	/**
	 * 追加されたタブを出力する
	 * 
	 * @return void
	 */
	public function generate_tab()
	{
		return wp_iframe(array($this, "media_iframe"));
	}
	
	/**
	 * アップロード用iframeの中身を返すコールバック
	 * 
	 * @return string
	 */
	public function media_iframe()
	{
		media_upload_header();
		require_once $this->dir.DIRECTORY_SEPARATOR."admin".DIRECTORY_SEPARATOR."upload.php";
	}
	
	/**
	 * 電子書籍に所属するファイルのリストを返す
	 * 
	 * @since 0.3
	 * @param int $book_id (optional)
	 * @param int $file_id (optional)
	 * @return array|object
	 */
	public function get_files($book_id = null, $file_id = null)
	{
		global $wpdb;
		if($book_id && $file_id){
			return array();
		}
		$query = "SELECT * FROM {$this->files} WHERE";
		if($file_id){
			$query .= " ID = %d";
			return $wpdb->get_row($wpdb->prepare($query, $file_id));
		}else{
			$query .= "book_id = %d";
			return $wpdb->get_results($wpdb->prepare($query, $book_id));
		}
	}
	
	/**
	 * ファイルをアップロードする
	 * 
	 * @param int $book_id
	 * @param string $name
	 * @param string $file
	 * @param string $path
	 * @return boolean
	 */
	public function upload_file($book_id, $name, $file, $path, $devices, $desc = "", $public = 1, $free = 0){
		//ディレクトリの存在確認と作成
		$book_dir = $this->option["dir"].DIRECTORY_SEPARATOR.$book_id;
		if(!is_dir($book_dir))
			if(!mkdir($book_dir))
				return false;
		//新しいファイル名の作成
		$file = sanitize_file_name($file);
		//ファイルの移動
		if(!move_uploaded_file($path, $book_dir.DIRECTORY_SEPARATOR.$file))
			return false;
		//データベースに書き込み
		global $wpdb;
		$wpdb->insert(
			$this->files,
			array(
				"book_id" => $book_id,
				"name" => $name,
				"detail" => $desc,
				"file" => $file,
				"public" => $public,
				"free" => $free,
				"registered" => gmdate("Y-m-d H:i:s"),
				"updated" => gmdate("Y-m-d H:i:s")
			),
			array("%d", "%s", "%s", "%s", "%d", "%d", "%s", "%s")
		);
		//デバイスを登録
		$inserted_id = $wpdb->insert_id;
		if($inserted_id && !empty($devices)){
			foreach($devices as $d){
				$wpdb->insert(
					$this->file_relationships,
					array(
						"file_id" => $inserted_id,
						"device_id" => $d
					),
					array("%d", "%d")
				);
			}
		}
		return $wpdb->insert_id;
	}
	
	/**
	* ファイルテーブルを更新する
	*
	* @return boolean
	*/
	private function update_file($file_id, $name, $devices, $desc, $public = 1, $free = 0)
	{
		global $wpdb;
		$req = $wpdb->update(
			$this->files,
			array(
				"name" => $name,
				"desc" => $desc,
				"public" => $public,
				"free" => $free,
				"updated" => gmdate("Y-m-d H:i:s")
			),
			array("ID" => $file_id),
			array("%s", "%s", "%d", "%d", "%s"),
			array("%d")
		);
		if($req){
			if(!empty($devices)){
				$deleted_ids = array();
				//このファイルに登録されたデバイスIDをすべて取得
				foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->file_relationships} WHERE file_id = %d", $file_id)) as $registered){
					if(false === array_search($registered->device_id, $devices)){
						//登録されたデバイスIDがPOSTされた値の中に見つからなかったら削除
						$wpdb->query($wpdb->prepare("DELETE FROM {$this->file_relationships} WHERE ID = %d", $registered->ID));
						$deleted_ids[] = $registered->device_id;
					}
				}
				foreach($devices as $d){
					//デバイスIDが削除済みにも登録済みにも存在しない場合
					if(false === array_search($d, $deleted_ids) && !$wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->file_relationships} WHERE file_id = %d AND device_id = %d", $file_id, $d))){
						$wpdb->insert(
							$this->file_relationships,
							array(
								"file_id" => $file_id,
								"device_id" => $d
							),
							array("%d","%d")
						);
					}
				}
			}else{
				$wpdb->query($wpdb->parepare("DELETE FROM {$this->file_relationships} WHERE file_id = %d", $file_id));
			}
			return true;
		}else
			return false;
	}
	
	/**
	* 指定されたファイルを削除する
	*
	* @param int $file_id 
	* @return boolean
	*/
	private function delete_file($file_id)
	{
		global $wpdb;
		$file = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->files} WHERE ID = %d", $file_id));
		if(!$file){
			return false;
		}else{
			//ファイルを削除する
			if(!unlink($this->option["dir"].DIRECTORY_SEPARATOR.$file->book_id.DIRECTORY_SEPARATOR.$file->file))
				return false;
			else{
				if($wpdb->query("DELETE FROM {$this->files} WHERE ID = {$file->ID}")){
					$wpdb->query($wpdb->prepare("DELETE FROM {$this->file_relationships} WHERE file_id = %d", $file_id));
					return true;
				}else
					return false;
			}
		}
	}
	
	/**
	 * アップロードしたファイルにエラーがないか調べる
	 * 
	 * @param array $info
	 * @return boolean
	 */
	private function file_has_error($info)
	{
		$message;
		switch($info["error"]){
			 case UPLOAD_ERR_INI_SIZE: 
                $message = $this->_("Uploaded file size exceeds the &quot;upload_max_filesize&quot; value defined in php.ini"); 
                break; 
            case UPLOAD_ERR_FORM_SIZE: 
                $message = $this->_("Uploaded file size exceeds"); 
                break; 
            case UPLOAD_ERR_PARTIAL: 
                $message = $this->_("File has been uploaded incompletely. Check your internet connection."); 
                break; 
            case UPLOAD_ERR_NO_FILE: 
                $message = $this->_("No file was uploaded."); 
                break; 
            case UPLOAD_ERR_NO_TMP_DIR: 
                $message = $this->_("No tmp directory exists. Contact to your server administrator."); 
                break; 
            case UPLOAD_ERR_CANT_WRITE: 
                $message = $this->_("Failed to save the uploaded file. Contact to your server administrator.");; 
                break; 
            case UPLOAD_ERR_EXTENSION: 
                $message = $this->_("PHP stops uploading."); 
                break;
			case UPLOAD_ERR_OK:
				$message = false;
				break;
		}
		return $message;
	}
	
	/**
	 * ファイル名から拡張子を推測して返す
	 * 
	 * @param string $file
	 * @return string
	 */
	public function detect_mime($file)
	{
		$mime = false;
		$ext = pathinfo($file, PATHINFO_EXTENSION);
		switch($ext){
			case "epub":
				$mime = "application/epub+zip";
				break;
			case "azw":
				$mime = "application/octet-stream";
				break;
		}
		if(!$mime){
			foreach(get_allowed_mime_types() as $e => $m){
				if(false !== strpos($e, $ext)){
					$mime = $m;
					break;
				}
			}
		}
		return $mime;
	}
	
	/**
	 * アップロード可能な拡張子を追加する
	 * 
	 * @param array $mimes
	 * @return array
	 */
	public function upload_mimes($mimes)
	{
		//epub
		$mimes["epub"] = "application/epub+zip";
		//AZW
		$mimes["azw"] = "application/octet-stream";
		return $mimes;
	}
	
	
	
	
	
	//--------------------------------------------
	//
	// Device
	//
	//--------------------------------------------

	/**
	 * CRUD interface for device
	 * @global wpdb $wpdb
	 * @return void
	 */
	public function update_devices(){
		global $wpdb;
		//Registere form
		if(isset($_REQUEST["_wpnonce"]) && wp_verify_nonce($_REQUEST['_wpnonce'], "lwp_add_device")){
			$req = $wpdb->insert(
				$this->devices,
				array(
					"name" => $_REQUEST["device_name"],
					"slug" => $_REQUEST["device_slug"]
				),
				array("%s", "%s")
			);
			if($req)
				$this->message[] = $this->_("Device added.");
			else
				$this->message[] = $this->_("Failed to add device.");
		}
		//Bulk action
		if(isset($_GET['devices'], $_REQUEST["_wpnonce"]) && wp_verify_nonce($_REQUEST['_wpnonce'], "bulk-devices") && !empty($_GET['devices'])){
			switch($_GET['action']){
				case "delete":
					$ids = implode(',', array_map('intval', $_GET['devices']));
					$sql = "DELETE FROM {$this->devices} WHERE ID IN ({$ids})";
					$wpdb->query($sql);
					$sql = "DELETE FROM {$this->file_relationships} WHERE device_id IN ({$ids})";
					$wpdb->query($sql);
					$this->message[] = $this->_("Device deleted.");
					break;
			}
		}
		//Update
		if(isset($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'edit_device')){
			$wpdb->update(
				$this->devices,
				array(
					'name' => (string)$_POST['device_name'],
					'slug' => (string)$_POST['device_slug']
				),
				array('ID' => $_POST['device_id']),
				array('%s', '%s'),
				array('%d')
			);
			$this->message[] = $this->_('Device updated.');
		}
	}
	
	/**
	 * Return device information
	 * 
	 * @since 0.3
	 * @param object $file (optional) 指定した場合はファイルに紐づけられた端末を返す
	 * @return array
	 */
	public function get_devices($file = null)
	{
		global $wpdb;
		if(is_numeric($file)){
			$file_id = $file;
		}elseif(is_object($file)){
			$file_id = $file->ID;
		}
		if(!is_null($file)){
			$sql = <<<EOS
				SELECT * FROM {$this->devices} as d
				LEFT JOIN {$this->file_relationships} as f
				ON d.ID = f.device_id
				WHERE f.file_id = %d
EOS;
			$sql = $wpdb->prepare($sql, $file_id);
		}else{
			$sql = "SELECT * FROM {$this->devices}";
		}
		return $wpdb->get_results($sql);
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
					array("%d", "%d", "%s", "%s")
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
	public function is_on_sale($post = null, $time = null)
	{
		global $wpdb;
		if(!$post){
			global $post;
			$post_id = $post->ID;
		}elseif(is_object($post)){
			$post_id = (int)$post->ID;
		}else{
			$post_id = $post;
		}
		if(!$time){
			$time = date_i18n('Y-m-d H:i:s');
		}
		$sql = "SELECT ID FROM {$this->campaign} WHERE book_id = %d AND start <= %s AND end >= %s";
		$req = $wpdb->get_row($wpdb->prepare($sql, $post_id, $time, $time));
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
	public function get_campaign($post_id, $time = false, $multi = false)
	{
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
	// ユーザー
	//
	//--------------------------------------------
	
	/**
	 *  ユーザープロフィール編集画面にプレゼント用のフォームを追加する
	 * 
	 * @return void
	 */
	public function give_user_form()
	{
		global $wpdb;
		$user_id = $_GET["user_id"];
		$sql = <<<EOS
			SELECT ID, post_title FROM {$wpdb->posts}
			WHERE post_type = 'ebook' AND post_status = 'publish'
			AND ID NOT IN (
				SELECT book_id FROM {$this->transaction} WHERE user_id = %d
			)
EOS;
		$ebooks = $wpdb->get_results($wpdb->prepare($sql, $user_id));
		require_once $this->dir.DIRECTORY_SEPARATOR."form-template".DIRECTORY_SEPARATOR."give-user.php";
	}
	
	/**
	 * ユーザーにプレゼントを渡す
	 * 
	 * @param int $user_id
	 * @return void
	 */
	public function give_user($user_id)
	{
		if(isset($_REQUEST["ebook_id"]) && is_numeric($_REQUEST["ebook_id"])){
			global $wpdb;
			$data = get_userdata($user_id);
			
			$wpdb->insert(
				$this->transaction,
				array(
					"user_id" => $user_id,
					"book_id" => $_REQUEST["ebook_id"],
					"price" => 0,
					"status" => "SUCCESS",
					"method" => "present",
					"transaction_key" => "",
					"payer_mail" => $data->user_email,
					"registered" => date('Y-m-d H:i:s'),
					"updated" => date('Y-m-d H:i:s')
				),
				array("%d", "%d", "%d", "%s", "%s", "%s", "%s", "%s", "%s")
			);
			if($wpdb->insert_id)
				$this->message[] = $this->_("You've kindly given a present.");
			else
				$this->message[] = $this->_("Failed to give a present.");
		}
	}
	
	/**
	 * ユーザーが電子書籍を所有しているかを返す
	 * 
	 * @param object|int $post (optional)
	 * @param int $user_id (optional)
	 * @return boolean
	 */
	public function is_owner($post = null, $user_id = null)
	{
		if(!$post){
			global $post;
			$post_id = $post->ID;
		}elseif(is_object($post)){
			$post_id = (int) $post->ID;
		}else{
			$post_id = $post;
		}
		if(!$user_id){
			global $user_ID;
			$user_id = (int) $user_ID;
		}
		global $wpdb;
		$sql = "SELECT ID FROM {$this->transaction} WHERE user_id = %d AND book_id = %d AND status = %s";
		$req = $wpdb->get_row($wpdb->prepare($sql, $user_id, $post_id, "SUCCESS"));
		return (boolean) $req != false;
	}
	
	/**
	 * ユーザーの購入履歴を返す
	 * 
	 * @param int $user_ID
	 * @param int $offset 何ページ目かを返す。0開始
	 * @param int $num_page 一回のリクエストで表示する数
	 * @return array 購入履歴からなる配列
	 */
	private function get_history($user_ID, $offset = 0, $num_page = 10)
	{
		return $this->get_transaction(null, $user_ID, null, $offset, $num_page);
	}
	
	/**
	 * ユーザーがこれまでに購入した件数を返す
	 * 
	 * @param int $user_ID
	 * @param int $book_id (optional) 指定しない場合はすべてのブックが対象
	 * @return int
	 */
	private function get_total_bought($user_ID, $book_id = null)
	{
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
	 * 取引情報を更新する
	 * 
	 * @return void
	 */
	public function update_transaction()
	{
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
					$diff = floor((int)(strtotime(gmdate('Y-m-d H:i:s')) - strtotime($transaction->updated)) / 60 / 60 / 24);
					if($diff > 60){ //Unrefundable
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
	 * コンテンツに対するアクションを処理
	 * 
	 * @return void
	 */
	public function manage_actions(){
		//電子書籍の場合だけ実行
		if(is_front_page() && isset($_GET["lwp"])){
			global $user_ID, $wpdb;
			switch($_GET["lwp"]){
				case "buy": //リダイレクトかwp_die
				case "transfer":
					//そもそも購入可能かチェック
					$book_id = (isset($_GET['lwp-id'])) ? intval($_GET['lwp-id']) : 0;
					if(!is_user_logged_in()){
						//ユーザーがログインしていない
						auth_redirect ($_SERVER["REQUEST_URI"]);
						exit;
					}elseif(!wp_get_single_post ($book_id)){
						//コンテンツが指定されていない
						$message = $this->_("No content is specified.");
					}elseif(lwp_price($book_id) < 1){
						//セール中のため無料だが、本来は有料。トランザクションの必要がない
						if(lwp_original_price($book_id) > 0){
							//購入済みにする
							$wpdb->insert(
								$this->transaction,
								array(
									"user_id" => $user_ID,
									"book_id" => $book_id,
									"price" => 0,
									"status" => LWP_Payment_Status::SUCCESS,
									"method" => LWP_Payment_Methods::CAMPAIGN,
									"registered" => gmdate('Y-m-d H:i:s'),
									"updated" => gmdate('Y-m-d H:i:s')
								),
								array("%d", "%d", "%d", "%s", "%s", "%s", "%s")
							);
							//サンキューページを表示する
							header("Location: ".  lwp_endpoint('success')."&lwp-id={$book_id}");
							exit;
						}else{
							//コンテンツが購入可能じゃない
							$message = $this->_("This contents is not on sale.");
						}
					}else{
						//この時点で購入可能
						if($_GET['lwp'] == 'transfer'){
							if($this->option['transfer']){
								//送金トランザクションを登録
								$wpdb->insert(
									$this->transaction,
									array(
										"user_id" => $user_ID,
										"book_id" => $book_id,
										"price" => lwp_price($book_id),
										"transaction_key" => sprintf("%08d", $user_ID),
										"status" => LWP_Payment_Status::START,
										"method" => LWP_Payment_Methods::TRANSFER,
										"registered" => gmdate('Y-m-d H:i:s'),
										"updated" => gmdate('Y-m-d H:i:s')
									),
									array("%d", "%d", "%d", "%s", "%s", "%s", "%s", "%s")
								);
								//Send Notification
								$transaction = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->transaction} WHERE ID = %d", $wpdb->insert_id));
								$notification_status = $this->notifier->notify($transaction, 'thanks');
								//Show Form
								$this->show_form('transfer', array(
									'post_id' => $book_id,
									'transaction' => $transaction,
									'notification' => $notification_status
								));
							}else{
								$message = $this->_("Sorry, we can't accept this payment method.");
							}
						}else{
							if(!$this->start_transaction($user_ID, $_GET['lwp-id'])){
								//トランザクション作成に失敗
								$message = $this->_("Failed to make transaction.");
							}
						}
					}
					//エラーが起きた場合はwp_die
					wp_die($message, sprintf($this->_("Transaction Error : %s"), get_bloginfo('name')), array('backlink' => true));
					break;
				case "confirm": //コンファームかwp_die
					if(isset($_POST["_wpnonce"]) && wp_verify_nonce($_POST["_wpnonce"], "lwp_confirm")){
						if(($transaction_id = PayPal_Statics::do_transaction($_POST))){
							//データを更新
							$post_id = $wpdb->get_var($wpdb->prepare("SELECT book_id FROM {$this->transaction} WHERE transaction_id = %s", $_POST["TOKEN"])); 
							$tran_id = $wpdb->update(
								$this->transaction,
								array(
									"status" => LWP_Payment_Status::SUCCESS,
									"transaction_key" => $_POST['INVNUM'],
									"transaction_id" => $transaction_id,
									"payer_mail" => $_POST["EMAIL"],
									'updated' => gmdate("Y-m-d H:i:s")
								),
								array(
									"transaction_id" => $_POST["TOKEN"]
								),
								array("%s", "%s", "%s", "%s", "%s"),
								array("%s")
							);
							//サンキューページを表示する
							header("Location: ".  lwp_endpoint('success')."&lwp-id={$post_id}"); 
						}else{
							wp_die($this->_("Transaction Failed to finish."), $this->_("Failed"), array("backlink" => true));
						}
					}else{
						$message = "";
						//確認画面
						$info = PayPal_Statics::get_transaction_info($_REQUEST['token']);
						if(!$info){
							$message = $this->_("Failed to connect with PayPal.");
						}
						$transaction = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->transaction} WHERE transaction_id = %s", $_REQUEST['token']));
						if(!$transaction){
							$message = $this->_("Failed to get the transactional information.");
						}
						$post = get_post($transaction->book_id);
						if(empty($message)){
							$this->show_form("return", array(
								"info" => $info,
								"transaction" => $transaction,
								"post" => $post
							));
						}else{
							wp_die($message, $this->_("Failed"), array("backlink" => true));
						}
					}
					break;
				case "success":
					if(isset($_REQUEST['lwp-id'])){
						$url = get_permalink($_REQUEST['lwp-id']);
					}else{
						$url = get_bloginfo('url');
					}
					$this->show_form("success", array('link' => $url));
					break;
				case "cancel":
					$post_id = $this->option['mypage'];
					$token = isset($_REQUEST['token']) ? $_REQUEST['token'] : null;
					if(!$token){
						$token = isset($_REQUEST['TOKEN']) ? $_REQUEST['TOKEN'] : null;
					}
					if($token){
						$wpdb->update(
							$this->transaction,
							array(
								"status" => LWP_Payment_Status::CANCEL,
								"updated" => gmdate("Y-m-d H:i:s")
							),
							array("transaction_id" => $token),
							array("%s", "%s"),
							array("%s")
						);
						$post_id = $wpdb->get_var($wpdb->prepare("SELECT book_id FROM {$this->transaction} WHERE transaction_id = %s", $token));
					}
					$this->show_form("cancel", array("post_id" => $post_id));
					break;
				case "file":
					$this->print_file($_REQUEST["lwp_file"], $user_ID);
					break;
			}
		}
		if(is_page($this->option['mypage'])){
			if(!is_user_logged_in()){
				auth_redirect();
				die();
			}
		}
	}
	
	/**
	 * トランザクションを開始する
	 * 
	 * @since 0.8
	 * @global wpdb $wpdb
	 * @param int $user_id
	 * @param int $post_id
	 * @return boolean 失敗した時だけfalseを返す
	 */
	public function start_transaction($user_id, $post_id){
		global $wpdb;
		//トランザクションを作る
		$price = lwp_price($post_id);
		//トークンを取得
		$invnum = sprintf("{$this->option['slug']}-%08d-%05d-%d", $post_id, $user_id, time());
		$token = PayPal_Statics::get_transaction_token($price, $invnum, lwp_endpoint('confirm'), lwp_endpoint('cancel'));
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
			//PayPalにリダイレクト
			PayPal_Statics::redirect($token);
			exit;
		}else{
			//トークンが帰ってこなかったら、エラー
			return false;
		}
	}
	
	/**
	 * フォームを出力する
	 * @since 0.8
	 * @param type $slug
	 * @param type $args
	 * @return void
	 */
	private function show_form($slug, $args = array()){
		extract($args);
		$filename = "paypal-{$slug}.php";
		//テーマテンプレートに存在するかどうか調べる
		if(file_exists(TEMPLATEPATH.DIRECTORY_SEPARATOR.$filename)){
			//テンプレートがあれば読み込む
			require_once TEMPLATEPATH.DIRECTORY_SEPARATOR.$filename;
		}else{
			//なければ自作
			$parent_directory = $this->dir.DIRECTORY_SEPARATOR."form-template".DIRECTORY_SEPARATOR;
			//CSS-js読み込み
			$css = (file_exists(TEMPLATEPATH.DIRECTORY_SEPARATOR."lwp-form.css")) ? get_bloginfo("template_directory")."/lwp-form.css" : $this->url."/assets/lwp-form.css";
			wp_enqueue_style("lwp-form", $css, array(), $this->version);
			wp_enqueue_script("lwp-form-helper", $this->url."/assets/js/form-helper.js", array("jquery"), $this->version, true);
			require_once $parent_directory."paypal-header.php";
			require_once $parent_directory.$filename;
			require_once $parent_directory."paypal-footer.php";
		}
		exit;
	}
	
	/**
	 * ファイルを出力する
	 * @param int $file_id
	 * @param int $user_id
	 * @return void
	 */
	private function print_file($file_id, $user_id)
	{
		$error = false;
		$file = $this->get_files(null, $file_id);
		//ファイルの取得を確認
		if(!$file){
			$error = true;
		}
		//ユーザーの所有権を確認
		if(
			($file->free == 0 && !$this->is_owner($file->book_id, $user_id)) //購入必須にも関わらずファイルを購入していない
			||
			($file->free == 1 && !is_user_logged_in()) //ログイン必須にも関わらずログインしていない
			||
			$file->public != 1 //ファイルが公開されていない
		){
			$error = true;
		}
		//ファイルの所在を確認
		$path = $this->option["dir"].DIRECTORY_SEPARATOR.$file->book_id.DIRECTORY_SEPARATOR.$file->file;
		if(!file_exists($path)){
			$error = true;
		}
		if(!$error){
			$mime = $this->detect_mime($file->file);
			$size = filesize($path);
			$kb = 1024; //1kb
			header("Content-Type: {$mime}");
			header("Content-Disposition: attachment; filename=\"{$file->file}\"");
			header("Content-Length: {$size}");
			flush();
			$handle = fopen($path, "r");
			while(!feof($handle)){
				//指定したバイト数だけ出力
				echo fread($handle, 100 * $kb);
				//出力
				flush();
				//1秒休む
				sleep(1);
			}
			//ファイルを閉じる
			fclose($handle);
			//終了
			exit;
		}else{
			wp_die($this-_('You have no permission to access this file.'), $this->_('Permission Error'), array("response" => 403, "backlink" => true));
		}
	}
	
	
	/**
	 * the_contentへのフック
	 * 
	 * @since 0.3
	 * @global object $post
	 * @param string $content
	 * @return string
	 */
	public function the_content($content)
	{
		global $post, $wpdb, $user_ID;
		//本棚用のタグを作成
		// TODO: タグを自動生成する必要はあるか？
		if($this->option["mypage"] > 0 && is_page($this->option["mypage"])){
			$book_shelf = "";
			$sql = <<<EOS
				SELECT * FROM {$this->transaction} AS t
				LEFT JOIN {$wpdb->posts} AS p
				ON t.book_id = p.ID
				WHERE t.user_id = %d AND t.status = 'SUCCESS'
				ORDER BY t.updated DESC
EOS;
			$histories = $wpdb->get_results($wpdb->prepare($sql, $user_ID));
			if(!empty($histories)){
				$book_name = $this->_('Name');
				$bought_data = $this->_('Date');
				$price = $this->_('Price');
				$method = $this->_('Payment Method');
				$book_shelf .= <<<EOS
					<table class="lwp-table form-table">
						<thead>
							<tr>
								<th>{$book_name}</th>
								<th>{$bought_data}</th>
								<th>{$method}</th>
								<th>{$price}</th>
							</tr>
						</thead>
EOS;
				$total = 0;
				$tbody = '';
				foreach($histories as $h){
					$title = '<a href="'.  get_permalink($h->ID).'">'.apply_filters('the_title', $h->post_title).'</a>';
					$date = mysql2date(get_option('date_format'), $h->updated);
					$price = lwp_currency_symbol().number_format($h->price);
					$total += $h->price;
					switch(strtolower($h->method)){
						case 'paypal':
							$method = 'PayPal';
							break;
						case 'present':
							$method = $this->_('Present');
							break;
						case 'campaign':
							$method = $this->_('Free Campaign');
							break;
					}
					$tbody .= <<<EOS
						<tr>
							<td>{$title}</td>
							<td>{$date}</td>
							<td>{$method}</td>
							<td>{$price}</td>
						</tr>	
EOS;
				}
				$total = lwp_currency_symbol().number_format($total);
				$book_shelf .= "<tfoot><td>&nbsp;</td><td>&nbsp;</td><td>".$this->_('Total: ')."</td><td>{$total}</td></tfoot>";
				$book_shelf .= "<tbody>{$tbody}</tbody></table>";
			}else{
				$book_shelf = '<p class="message error">'.$this->_('You have no transaction history. Try to get any!').'</p>';
			}
			return $book_shelf.$content;
		}elseif(false !== array_search(get_post_type(), $this->option['payable_post_types']) && $this->option['show_form']){
			$content .= lwp_show_form();
			//ダウンロード可能なファイルがあったらテーブルを出力
			if($wpdb->get_var($wpdb->prepare("SELECT COUNT(ID) FROM {$this->files} WHERE book_id = %d", $post->ID))){
				$content .= lwp_get_device_table().lwp_get_file_list();
			}
			return $content;
		}else{
			return $content;
		}
	}
	
	//--------------------------------------------
	//
	// TinyMCE
	//
	//--------------------------------------------
	
	/**
	 * ショートコードを追加する
	 * 
	 * @since 0.8
	 * @param array $atts
	 * @param string $contents
	 * @return string
	 */
	public function shortcode_capability($atts, $contents = null){
		//属性値を抽出
		extract(shortcode_atts(array("user" => "owner"), $atts));
		//省略形を優先する
		if(isset($atts[0])){
			$user = $atts[0];
		}
		//属性値によって返す値を検討
		switch($user){
			case "owner": //オーナーの場合
				return $this->is_owner() ? wpautop($contents) : "";
				break;
			case "subscriber": //登録済ユーザーの場合
				return is_user_logged_in() ? wpautop($contents) : "";
				break;
			case "non-owner": //オーナーではない場合
				return $this->is_owner() ? "" : wpautop($contents);
				break;
			case "non-subscriber": //登録者ではない場合
				return is_user_logged_in() ? "" : wpautop($contents);
				break;
			default:
				return wpautop($contents);
		}
	}
	
	/**
	 * BuyNowボタンを出力する
	 * 
	 * @param type $atts
	 * @return string
	 */
	public function shortcode_buynow($atts){
		if(!isset($atts[0]) || !$atts[0]){
			return lwp_buy_now(null, false);
		}elseif($atts[0] == 'link'){
			return lwp_buy_now(null, null);
		}else{
			return lwp_buy_now(null, $atts[0]);
		}
	}
	
	/**
	 * TinyMCEにプラグインを登録する
	 * @param array $plugin_array
	 * @return array
	 */
	public function mce_plugin($plugin_array){
		$plugin_array['lwpShortCode'] = plugin_dir_url(__FILE__)."/assets/js/tinymce.js";
		return $plugin_array;
	}
	
	/**
	 * TinyMCEの言語ファイルを追加する
	 * @param array $languages
	 * @return array
	 */
	public function mce_lang($languages){
		$languages["lwpShortCode"] = $this->dir.DIRECTORY_SEPARATOR."assets".DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."tinymce-lang.php";
		return $languages;
	}
	
	/**
	 * TinyMCEのボタンを追加する
	 * @param array $buttons
	 * @return array
	 */
	public function mce_button($buttons){
		array_push($buttons, "lwpListBox", "separator");
		array_push($buttons, 'lwpBuyNow', "separator");
		return $buttons;
	}
	
	//--------------------------------------------
	//
	// ユーティリティ
	//
	//--------------------------------------------	
	
	/**
	 * 管理画面の該当するページか否かを返す
	 * 
	 * @param string $page_name ページ名
	 * @return boolean
	 */
	private function is_admin($page_name)
	{
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
	* 文字列をhtmlspecislcharsにして返す
	*
	* @param string $str
	* @return void|string
	*/
	public function h($str, $echo = true)
	{
		$str = htmlspecialchars($str, ENT_QUOTES, "utf-8");
		if($echo)
			echo $str;
		else
			return $str;
	}
	
	/**
	 * URLをSSL化して返す
	 * 
	 * @param string $url
	 * return string
	 */
	public function ssl($url)
	{
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
