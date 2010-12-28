<?php
/**
 * Literally WordPressの処理を行うクラス
 *
 * @package LIterally WordPress
 * @author  Takahashi Fumiki<takahashi.fumiki@hametuha.co.jp>
 */
class Literally_WordPress
{
	
	/**
	* バージョン
	*
	* @var string
	*/
	private $version = "0.2";
	
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
	public $campaign = "campaign";
	
	/**
	* トランザクションテーブル
	*
	* @var string
	*/
	public $transaction = "transaction";
	
	/**
	* ファイルテーブル
	*
	* @var string
	*/
	public $files = "files";
	
	
	/**
	* 端末テーブル
	*
	* @var string
	*/
	public $devices = "devices";
	
	
	/**
	* ファイルと端末の関係テーブル
	*
	* @var string
	*/
	public $file_relationships = "file_relationships";
	
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
	private $error = false;
	
	/**
	 * エラーメッセージ
	 * 
	 * @var array
	 */
	private $message = array();
	
	
	
		
	//--------------------------------------------
	//
	// 初期化処理
	//
	//--------------------------------------------
	
	/**
	 * コンストラクター
	 * 
	 * @return void
	 */
	public function __construct()
	{
		global $wpdb;
		//初期値の設定
		$this->url = get_bloginfo("url")."/wp-content/plugins/literally-wordpress";
		$this->dir = dirname(__FILE__);
		$this->campaign = $wpdb->prefix."lwp_".$this->campaign;
		$this->transaction = $wpdb->prefix."lwp_".$this->transaction;
		$this->files = $wpdb->prefix."lwp_".$this->files;
		$this->devices = $wpdb->prefix."lwp_".$this->devices;
		$this->file_relationships = $wpdb->prefix."lwp_".$this->file_relationships;
		$this->option = array();
		$saved_option = get_option("literally_wordpress_option");
		$default_option =  array(
        	"marchant_id" => "",
        	"token" => "",
        	"dir" => dirname(__FILE__).DS."contents",
			"slug" => str_replace(".", "", $_SERVER["HTTP_HOST"]),
			"mypage" => 2
		);
		foreach($default_option as $k => $v){
			if(isset($saved_option[$k]))
				$this->option[$k] = $saved_option[$k];
			else
				$this->option[$k] = $v;
		}
		//通貨設定
		setlocale( LC_MONETARY, 'ja_JP' );
		
		//投稿タイプの追加
		add_action("init", array($this, "custom_post"));
	}
	
	/**
	* カスタム投稿タイプを追加する
	*
	* @return void
	*/
	public function custom_post()
	{
		//投稿タイプを設定
		$labels = array(
			'name' => "電子書籍",
			'singular_name' => "一覧",
			'add_new' => "新規登録",
			'add_new_item' => "新規電子書籍",
			'edit_item' => "電子書籍商品編集",
			'new_item' => "新しい電子書籍",
			'view_item' => "電子書籍を表示する",
			'search_items' => "電子書籍を検索",
			'not_found' =>  "電子書籍が見つかりませんでした",
			'not_found_in_trash' => "ゴミ箱に電子書籍はありませんでした", 
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
			'register_meta_box_cb' => array($this, "post_metabox"),
			'supports' => array('title','editor','author','thumbnail','excerpt', 'comments', 'custom-fields'),
			'show_in_nav_menus' => true,
			'menu_icon' => $this->url."/assets/book.png"
		);
		register_post_type("ebook", $args);
	}
	
	/**
	 * プラグインを有効化しても問題がないかどうかチェックする
	 * 
	 * @return void
	 */
	public function validate()
	{
		//ディレクトリの書き込み可否を判断
		if(!is_writable($this->option["dir"])){
			$this->initialized = false;
			$this->message["dir"] = "ディレクトリが書き込みできません。";
		}
		//ファイルのアクセス可否を判断
		if(false !== strpos($this->option["dir"], ABSPATH)){
			//アクセスチェック用ファイルがなければ作成
			if(!file_exists($this->option["dir"].DS."access"))
				touch($this->option["dir"].DS."access");
			//URL経由で取得
			$test_url = str_replace(ABSPATH, get_bloginfo("url")."/", $this->option["dir"]."/access");
			$ch = curl_init($test_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$req = curl_exec($ch);
			//HTTPステータスコードを確認
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if($http_code == 200){
				$this->initialized = false;
				$this->message["access"] = "ディレクトリのファイルにアクセスできてしまいます。";
			}
		}
		//課金できるかどうかチェック
		if(empty($this->option["marchant_id"]) || empty($this->option["token"])){
			$this->initialized = false;
			$this->message["paypal"] = "マーチャントIDとPDTトークンがないと課金できません。";
		}
	}
	
	/**
	 * 管理画面のときにだけ行うフックの登録
	 * 
	 * @return void
	 */
	public function admin_hooks()
	{
		/*--------------
		 * アクションフック
		 */
		//テーブル生成
		add_action("admin_init", array($this, "table_create"));
		//課金有効かどうかの判断
		add_action("admin_init", array($this, "validate"));
		//オプション更新
		if($this->is_admin("setting"))
			add_action("admin_init", array($this, "option_update"));
		//キャンペーン更新
		if($this->is_admin("campaign"))
			add_action("admin_init", array($this, "campaign_update"));
		//トランザクション更新
		if($this->is_admin("management"))
			add_action("admin_init", array($this, "update_transaction"));
		//端末更新
		if($this->is_admin("devices"))
			add_action("admin_init", array($this, "update_devices"));
		//電子書籍のアップデート
		add_action("edit_post", array($this, "post_update"));
		//メニューの追加
		add_action("admin_menu", array($this, "add_menu"));
		//スタイルシート・JSの追加
		if(
			(isset($_GET["post_type"]) && isset($_GET["page"]) && $_GET["post_type"] == "ebook")
			||
			(basename($_SERVER["SCRIPT_FILENAME"]) == "users.php" && $_REQUEST["page"] == "lwp-history") 
		)
			add_action("admin_head", array($this, "assets"));
		//メッセージの出力
		add_action("admin_notice", array($this, "admin_notice"));
		//ファイルアップロード用のタブを追加
		add_action("media_upload_ebook", array($this, "generate_tab"));
		//ユーザーに書籍をプレゼントするフォーム
		add_action("edit_user_profile", array($this, "give_user_form"));
		//書籍プレゼントが実行されたら
		if(basename($_SERVER["SCRIPT_FILENAME"]) == "user-edit.php")
			add_action("profile_update", array($this, "give_user"));
		/*--------------
		 * フィルターフック
		 */
		//ファイルアップロードのタブ生成アクションを追加するフィルター
		add_filter("media_upload_tabs", array($this, "upload_tab"));
		//ファイルアップロード可能な拡張子を追加する
		add_filter("upload_mimes", array($this, "upload_mimes"));
	}
	
	/**
	* テーブル作成
	*
	* @return void
	*/
	public function table_create()
	{
		global $wpdb;
		if(!$wpdb->query("SHOW TABLES LIKE %{$this->files}%")){
			$sql = <<<EOS
				CREATE TABLE  `{$this->files}` (
					`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
					`book_id` BIGINT NOT NULL ,
					`name` VARCHAR( 255 ) NOT NULL ,
					`file` VARCHAR( 255 ) NOT NULL ,
					`desc` TEXT NOT NULL,
					`public` INT NOT NULL DEFAULT 1,
					`free` INT NOT NULL DEFAULT 0,
					`registered` DATETIME NOT NULL ,
					`updated` DATETIME NOT NULL
				) ENGINE = MYISAM
EOS;
			$wpdb->query($sql);
		}
		if(!$wpdb->query("SHOW TABLES LIKE %{$this->transaction}%")){
			$sql = <<<EOS
				CREATE TABLE  `{$this->transaction}` (
					`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
					`user_id` BIGINT NOT NULL ,
					`book_id` BIGINT NOT NULL ,
					`price` BIGINT NOT NULL ,
					`status` VARCHAR( 45 ) NOT NULL ,
					`method` VARCHAR( 100 ) NOT NULL DEFAULT 'PAYPAL',
					`transaction_key` VARCHAR (255) NOT NULL ,
					`payer_mail` VARCHAR (255) NOT NULL,
					`registered` DATETIME NOT NULL ,
					`updated` DATETIME NOT NULL
				) ENGINE = MYISAM
EOS;
			$wpdb->query($sql);
		}
		if(!$wpdb->query("SHOW TABLES LIKE %{$this->campaign}%")){
			$sql = <<<EOS
				CREATE TABLE  `{$this->campaign}` (
					`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
					`book_id` BIGINT NOT NULL ,
					`price` BIGINT NOT NULL ,
					`start` DATETIME NOT NULL ,
					`end` DATETIME NOT NULL
				) ENGINE = MYISAM
EOS;
			$wpdb->query($sql);
		}
/*
--slq for devices
CREATE TABLE `nikkilwp_devices` (
`ID` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` VARCHAR( 255 ) NOT NULL ,
`slug` VARCHAR( 255 ) NOT NULL
) ENGINE = MYISAM ;
*/ 
/*
--slq for file_relationships
CREATE TABLE  `wordpress`.`nikkilwp_file_relationships` (
`file_id` INT NOT NULL ,
`device_id` INT NOT NULL
) ENGINE = MYISAM ;
*/ 
	}
	
	/**
	 * 公開画面で登録するフック
	 * 
	 * @return void
	 */
	public function public_hooks()
	{
		//ヘッダー部分でリクエストの内容をチェックする
		add_action("template_redirect", array($this, "manage_ebook"));
		//本棚ページだったら、投稿内容に購入履歴を出力する
		add_filter('the_content', array($this, "show_history"));
	}
	
	/**
	 * 管理画面にサブメニューを追加する
	 * 
	 * @return void
	 */
	public function add_menu()
	{
		add_submenu_page("edit.php?post_type=ebook", "電子書籍顧客管理", "顧客管理", 10, "lwp-management", array($this, "load"));
		add_submenu_page("edit.php?post_type=ebook", "電子書籍キャンペーン", "キャンペーン", 10, "lwp-campaign", array($this, "load"));
		add_submenu_page("edit.php?post_type=ebook", "電子書籍設定", "設定", 10, "lwp-setting", array($this, "load"));
		add_submenu_page("edit.php?post_type=ebook", "端末設定", "端末", 10, "lwp-devices", array($this, "load"));
		add_submenu_page("profile.php", "電子書籍購入履歴", "購入履歴", 0, "lwp-history", array($this, "load"));
	}
	
	/**
	 * 管理画面用のファイルを読み込む
	 */
	public function load()
	{
		if(isset($_GET["page"]) && isset($_GET["post_type"]) && $_GET["post_type"] == "ebook"){
			$slug = str_replace("lwp-", "", $_GET["page"]);
			if(file_exists($this->dir.DS."admin".DS."{$slug}.php")){
				global $wpdb;
				echo '<div class="wrap">';
				do_action("admin_notice");
				echo "<div class=\"icon32 ebook\"><br /></div>";
				require_once $this->dir.DS."admin".DS."{$slug}.php";
				echo "</div>\n<!-- .wrap ends -->";
			}else
				return;
		}elseif(false !== strpos($_SERVER["REQUEST_URI"], "users.php")){
			global $wpdb;
			echo '<div class="wrap">';
			do_action("admin_notice");
			echo "<div class=\"icon32 ebook\"><br /></div>";
			require_once $this->dir.DS."admin".DS."history.php";
			echo "</div>\n<!-- .wrap ends -->";
		}else
			return;
	}
	
	/**
	 * 管理画面にファイルを登録する
	 * 
	 * @return void
	 */
	public function assets()
	{
		?>
		<link rel="stylesheet" type="text/css" href="<?php echo $this->url; ?>/assets/style.css?version=<?php echo $this->version; ?>" />
		<?php
	}
	
	
	/**
	 * 管理画面でメッセージを発行する
	 */
	public function admin_notice()
	{
		if(!empty($this->message)){
			?>
				<div class="update-nag">
					<ul>
					<?php foreach($this->message as $m): ?>
						<li><?php echo $m; ?></li>
					<?php endforeach; ?>
					</ul>
				</div>
			<?php
		}
	}
	
	/**
	 * ヘルプページのURLを返す
	 * 
	 * @param string $name
	 * return string
	 */
	public function help($name)
	{
		$url = $this->url."/help/?name={$name}";
		if(is_ssl())
			$url = $this->ssl($url);
		return $url;
	}


	
	//--------------------------------------------
	//
	// 設定ページ
	//
	//--------------------------------------------
	
	/**
	 * 設定更新時の処理
	 * 
	 * @return void
	 */
	public function option_update()
	{
		//要素が揃っていたら更新
		if(
			isset($_REQUEST["_wpnonce"]) && isset($_REQUEST["_wp_http_referer"])
			&& false !== strpos($_REQUEST["_wp_http_referer"], "lwp-setting")
			&& wp_verify_nonce($_REQUEST["_wpnonce"], "lwp_update_option")
		){
			$new_option = array(
				"marchant_id" => $_REQUEST["marchant_id"],
				"token" => $_REQUEST["token"],
				"dir" => $_REQUEST["dir"],
				"slug" => $_REQUEST["product_slug"],
				"mypage" => $_REQUEST["mypage"]
			);
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
			if(preg_match("/^[0-9]+$/", $price))
				update_post_meta($_POST["ID"], "lwp_price", $price);
			else{
				$this->message[] = "定価は数字だけにしてください。";
				$this->error = true;
			}
			//ページ数を登録
			$num = (int) preg_replace("/[^0-9]/", "", mb_convert_kana($_REQUEST["lwp_number"], "n"));
			update_post_meta($_POST["ID"], "lwp_number", $num);
			//ISBNがあれば登録
			if(!empty($_POST["lwp_isbn"])){
				update_post_meta($_POST["ID"], "lwp_isbn", $_POST["lwp_isbn"]);
			}
		} 
	}
	
		/**
	* 投稿ページにフォームを追加する
	*
	* @return void
	*/
	public function post_metabox()
	{
		add_meta_box('ebookdetail', "電子書籍の設定", array($this, 'post_metabox_form'), 'ebook', 'side', 'core');
	}
	
	/**
	 * 投稿ページに追加するフォーム
	 * 
	 * @return void
	 */
	public function post_metabox_form()
	{
		$files = $this->get_files($_GET["post"]);
		require_once $this->dir.DS."form-template".DS."edit-detail.php";
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
			$tabs["ebook"] = "電子書籍";
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
		require_once $this->dir.DS."admin".DS."upload.php";
	}
	
	/**
	 * 電子書籍に所属するファイルのリストを返す
	 * 
	 * @param int $book_id
	 * @return array
	 */
	public function get_files($book_id, $file_id = null)
	{
		global $wpdb;
		$query = <<<EOS
			SELECT * FROM {$this->files} WHERE book_id = %d	
EOS;
		if($file_id){
			$query .= " AND ID = %d";
			return $wpdb->get_row($wpdb->prepare($query, $book_id, $file_id));
		}else
			return $wpdb->get_results($wpdb->prepare($query, $book_id));
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
	public function upload_file($book_id, $name, $file, $path, $devices, $desc = "", $public = 1, $free = 0)
	{
		var_dump($devices);
		//ディレクトリの存在確認と作成
		$book_dir = $this->option["dir"].DS.$book_id;
		if(!is_dir($book_dir))
			if(!mkdir($book_dir))
				return false;
		//新しいファイル名の作成
		$file = sanitize_file_name($file);
		//ファイルの移動
		if(!move_uploaded_file($path, $book_dir.DS.$file))
			return false;
		//データベースに書き込み
		global $wpdb;
		$wpdb->insert(
			$this->files,
			array(
				"book_id" => $book_id,
				"name" => $name,
				"desc" => $desc,
				"file" => $file,
				"public" => $public,
				"free" => $free,
				"registered" => date("Y-m-d H:i:s"),
				"updated" => date("Y-m-d H:i:s")
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
				"updated" => date("Y-m-d H:i:s")
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
					var_dump($wpdb->prepare("SELECT * FROM {$this->file_relationships} WHERE file_id = %d AND device_id = %d", $file_id, $d));
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
			if(!unlink($this->option["dir"].DS.$file->book_id.DS.$file->file))
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
                $message = "アップロードされたファイルはphp.iniに記載されたupload_max_filesizeを超えています。"; 
                break; 
            case UPLOAD_ERR_FORM_SIZE: 
                $message = "指定されたサイズより大きなファイルがアップロードされました。"; 
                break; 
            case UPLOAD_ERR_PARTIAL: 
                $message = "ファイルは完全にアップロードされませんでした。インターネット接続等を確認してください。"; 
                break; 
            case UPLOAD_ERR_NO_FILE: 
                $message = "ファイルがアップロードされていません。"; 
                break; 
            case UPLOAD_ERR_NO_TMP_DIR: 
                $message = "一時ディレクトリーが存在しません。サーバ管理者に問い合わせてください。"; 
                break; 
            case UPLOAD_ERR_CANT_WRITE: 
                $message = "ファイルを保存できませんでした。サーバ管理者に問い合わせてください。"; 
                break; 
            case UPLOAD_ERR_EXTENSION: 
                $message = "PHPの機能によってアップロードが中止されました。"; 
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
				$mime = "application/zip+epub";
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
	// 端末登録
	//
	//--------------------------------------------

	/**
	 * 端末を追加・削除する
	 * 
	 * @return void
	 */
	public function update_devices()
	{
		//登録フォームのとき
		if(isset($_REQUEST["_wpnonce"]) && wp_verify_nonce($_REQUEST['_wpnonce'], "lwp_add_device")){
			global $wpdb;
			$req = $wpdb->insert(
				$this->devices,
				array(
					"name" => $_REQUEST["device_name"],
					"slug" => $_REQUEST["device_slug"]
				),
				array("%s", "%s")
			);
			if($req)
				$this->message[] = "端末を追加しました。";
			else
				$this->message[] = "端末の追加に失敗しました。";
		}
		//削除フォームのとき
		if(isset($_REQUEST["_wpnonce"]) && wp_verify_nonce($_REQUEST['_wpnonce'], "lwp_delete_devices") && !empty($_POST['devices'])){
			global $wpdb;
			$ids = implode(',',$_POST['devices']);
			$sql = "DELETE FROM {$this->devices} WHERE ID IN ({$ids})";
			$wpdb->query($sql);
			$sql = "DELETE FROM {$this->file_relationships} WHERE device_id IN ({$ids})";
			$wpdb->query($sql);
			$this->message[] = "端末情報を削除しました。";
		}
	}
	
	/**
	 * デバイス情報を返す
	 * 
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
				WHERE f.file_id = {$file_id}
EOS;
		}else{
			$sql = "SELECT * FROM {$this->devices}";
		}
		return $wpdb->get_results($sql);
	}
	
	
	
	//--------------------------------------------
	//
	// キャンペーン
	//
	//--------------------------------------------

	
	/**
	* キャンペーンページの更新で行われるアクション
	*
	* @return void
	*/
	public function campaign_update()
	{
		global $wpdb;
		//datepickerを読み込み
		wp_enqueue_script(
			'jquery-datepicker',
			$this->url."/assets/datepicker/jquery.datepicker.js",
			array("jquery", "jquery-ui-core"),
			"1.7.3"
		);
		wp_enqueue_script(
			'jquery-slider',
			$this->url."/assets/datepicker/jquery.slider.js",
			array("jquery", "jquery-ui-core"),
			"1.7.3"
		);
		wp_enqueue_script(
			'jquery-effect',
			$this->url."/assets/datepicker/jquery.effects.core.js",
			array("jquery", "jquery-ui-core"),
			"1.7.3"
		);
		wp_enqueue_script(
			'jquery-hilight',
			$this->url."/assets/datepicker/jquery.effects.highlight.js",
			array("jquery", "jquery-ui-core"),
			"1.7.3"
		);
		wp_enqueue_script(
			'jquery-calendar-i18n',
			$this->url."/assets/datepicker/i18n/ui.datepicker-ja.js",
			array("jquery-ui-", "jquery-datepicker"),
			"1.7.3"
		);
		wp_enqueue_script(
			'jquery-timepicker',
			$this->url."/assets/datepicker/timepicker/timepicker.js",
			array("jquery-datepicker"),
			"1.7.3"
		);
		wp_enqueue_script(
			"jquery-datepicker-load",
			$this->url."/assets/js/campaign.js",
			array("jquery-datepicker"),
			$this->version
		);
		wp_enqueue_style(
			'jquery-datepicker-style',
			$this->url."/assets/datepicker/ui-lightness/jquery-ui.css",
			array(),
			"1.7.3"
		);
		wp_enqueue_style(
			'jquery-timepicker-style',
			$this->url."/assets/datepicker/timepicker/style.css",
			array(),
			"1.7.3"
		);
		//キャンペーンの追加
		if(isset($_REQUEST["_wpnonce"]) && wp_verify_nonce($_REQUEST["_wpnonce"], "lwp_add_campaign")){
			//投稿の確認
			if(!is_numeric($_REQUEST["book_id"])){
				$this->error = true;
				$this->message[] = "対象となる電子書籍を選んでください。";
			}
			//価格の確認
			if(!is_numeric(mb_convert_kana($_REQUEST["price"], "n"))){
				$this->error = true;
				$this->message[] = "価格は数値にしてください。";
			}
			//価格の確認
			elseif($_REQUEST["price"] > get_post_meta($_REQUEST["book_id"], "lwp_price", true)){
				$this->error = true;
				$this->message[] = "キャンペーン価格が定価よりも高くなっています。";
			}
			//形式の確認
			if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/", $_REQUEST["start"]) || !preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/", $_REQUEST["end"])){
				$this->error = true;
				$this->message[] = "日付の形式が不正です";
			}
			//開始日と終了日の確認
			elseif(strtotime($_REQUEST["end"]) < time() || strtotime($_REQUEST["end"]) < strtotime($_REQUEST["start"])){
				$this->error = true;
				$this->message[] = "終了日が過去に設定されています。";
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
					$this->message[] = "キャンペーンを追加しました。";
				else{
					$this->error = true;
					$this->message[] = "キャンペーンの追加に失敗しました。";
				}
			}
		}
		//キャンペーンの更新
		elseif(isset($_REQUEST["_wpnonce"]) && wp_verify_nonce($_REQUEST["_wpnonce"], "lwp_update_campaign")){
			//キャンペーンIDの存在を確認
			if(!$wpdb->get_row($wpdb->prepare("SELECT ID FROM {$this->campaign} WHERE ID = %d", $_REQUEST["campaign"]))){
				$this->error = true;
				$this->message[] = "指定されたキャンペーンが存在しません。"; 
			}
			//価格の確認
			if(!is_numeric(mb_convert_kana($_REQUEST["price"], "n"))){
				$this->error = true;
				$this->message[] = "価格は数値にしてください。";
			}elseif($_REQUEST["price"] > get_post_meta($_REQUEST["book_id"], "lwp_price", true)){
				$this->error = true;
				$this->message[] = "キャンペーン価格が定価よりも高くなっています。";
			}
			//形式の確認
			if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/", $_REQUEST["start"]) || !preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/", $_REQUEST["end"])){
				$this->error = true;
				$this->message[] = "日付の形式が不正です";
			}
			//開始日と終了日の確認
			elseif(strtotime($_REQUEST["end"]) < time() || strtotime($_REQUEST["end"]) < strtotime($_REQUEST["start"])){
				$this->error = true;
				$this->message[] = "終了日が過去に設定されています。";
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
					$this->message[] = "更新完了しました。";
				else{
					$this->error = true;
					$this->message[] = "更新に失敗しました。";
				}
			}
		}
		//キャンペーンの削除
		elseif(isset($_REQUEST["_wpnonce"]) && wp_verify_nonce($_REQUEST["_wpnonce"], "lwp_delete_campaign") && is_array($_REQUEST["campaigns"])){
			$sql = "DELETE FROM {$this->campaign} WHERE ID IN (".implode(",", $_REQUEST["campaigns"]).")";
			if($wpdb->query($sql))
				$this->message[] = "キャンペーンを削除しました。";
			else{
				$this->error = true;
				$this->message[] = "キャンペーンの削除に失敗しました。";
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
		}else
			$post_id = $post;
		if(!$time)
			$time = date('Y-m-d H:i:s');
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
	 * プロフィール編集画面にPayPalアドレス用のコンタクトフィールドを追加する
	 * 
	 * @param array $contactmethods
	 * @return array
	 */
	public function add_paypal_mail($contactmethods)
	{
		$contactmethods["paypal"] = "PayPal メールアドレス<br /><span class=\"description\"><small>※登録アドレスと異なる場合は必須</small></span>";
		return $contactmethods;
	}
	
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
		require_once $this->dir.DS."form-template".DS."give-user.php";
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
				$this->message[] = "プレゼントを渡しました";
			else
				$this->message[] = "プレゼントに失敗しました";
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
		}else
			$post_id = $post;
		if(!$user_id){
			global $user_ID;
			$user_id = (int) $user_ID;
		}
		global $wpdb;
		$sql = "SELECT ID FROM {$this->transaction} WHERE user_id = %d AND book_id = %d AND status = %s";
		$req = $wpdb->get_row($wpdb->prepare($sql, $user_id, $post_id, "SUCCESS"));
		return $req != false;
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
	
	/**
	 * 本棚用ページにタグを追加する
	 * 
	 * @param string $content
	 * @return string
	 */
	public function show_history($content)
	{
		//本棚用のタグを作成
		// TODO: タグを自動生成する必要はあるか？
		$book_shelf = "";
		if(is_page($this->option["mypage"])){
			$book_shelf = "";
		}
		return $book_shelf.$content;
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
		if(isset($_REQUEST["_wpnonce"]) && wp_verify_nonce($_REQUEST["_wpnonce"], "lwp_update_transaction")){
			//データの更新
			global $wpdb;
			$req = $wpdb->update(
				$this->transaction,
				array(
					"user_id" => $_REQUEST["user_id"],
					"price" => $_REQUEST["price"],
					"status" => $_REQUEST["status"],
					"method" => $_REQUEST["method"],
					"payer_mail" => $_REQUEST["payer_mail"],
					"registered" => date("Y-m-d H:i:s")
				),
				array("ID" => $_REQUEST["transaction_id"]),
				array("%d", "%d", "%s", "%s", "%s", "%s"),
				array("%d")
			);
			if($req)
				$this->message[] = "購入情報を更新しました。";
			else
				$this->message[] = "購入情報更新に失敗しました";
		}
	}
	
	//--------------------------------------------
	//
	// 公開画面
	//
	//--------------------------------------------
	
	/**
	 * ebookへのリクエストを分類する
	 * 
	 * @return void
	 */
	public function manage_ebook()
	{
		//電子書籍の場合だけ実行
		if(is_single() && "ebook" == get_post_type()){
			//ファイル取得の場合
			if(isset($_REQUEST["ebook_file"]) && is_user_logged_in()){
				global $post, $user_ID;
				$this->print_file($_REQUEST["ebook_file"], $post->ID, $user_ID);
			}
			//トランザクション完了の場合
			if(isset($_REQUEST["lwp_return"]) && isset($_REQUEST["tx"]) && is_user_logged_in()){
				$this->on_transaction = true;
				$this->do_transaction();
			}
		}
	}
	
	/**
	 * トランザクションを実行する
	 * 
	 * @return boolean
	 */
	public function do_transaction()
	{
		global $wpdb, $user_ID, $post;
		//値が取得できるかチェックし、なかったらエラー
		if(!(isset($_REQUEST["item_number"]) && isset($_REQUEST["amt"]) && isset($_REQUEST["tx"]) ))
			return false;
		//値が揃っているので、処理を開始
		$item_number = $_REQUEST["item_number"];
		$amount = $_REQUEST["amt"];
		//cURLのセットアップ
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://www.paypal.com/cgi-bin/webscr");
		curl_setopt ($ch,CURLOPT_POST,true);
		//POSTデータのセットアップ
		$post_data = "cmd=_notify-synch&tx={$_REQUEST['tx']}&at={$this->option['token']}";
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		//データの取得
		curl_setopt ($ch,CURLOPT_RETURNTRANSFER, true);
		$req = curl_exec($ch);
		//エラーの取得
		$err = curl_error($ch);
		//データの検証
		if(empty($err)){
			$lines = explode("\n", $req);
			if(false === strpos($lines[0], "SUCCESS")){
				//データが失敗で返ってきた
				$this->transaction_status = "ERROR";
				return false;
			}else{
				//成功
				$val = array();
				foreach($lines as $v){
					if(false !== strpos($v, "=")){
						$pair = explode("=", $v);
						$val[$pair[0]] = rawurldecode($pair[1]);
					}
				}
				//データの検証（即時決済かどうかだけ見る）
				if($val["payment_status"] == "Completed"){
					$this->transaction_status = "SUCCESS";
					$status = "SUCCESS";
				}else{
					$this->transaction_status = "FAILED";
					$status = $val["payment_status"];
				}
				//データを取得する（IPNの方が早かった場合を考慮）
				$tran = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->transaction} WHERE transaction_key = %s", $val["txn_id"]));
				
				//データがあり、ユーザーIDが0か、ステータスがSUCCESSではない場合に更新する
				if($tran){
					$update_var = array();
					$update_prepare = array();
					if($tran->user_id == 0){
						$update_var["user_id"] = $user_ID;
						$update_prepare[] = "%d";
					}
					if($tran->status != "SUCCESS"){
						$update_var["status"] = $status;
						$update_prepare[] = "%s";
					}
					if(!empty($update_var)){
						//更新の必要がある場合は更新
						$update_var["updated"] = date('Y-m-d H:i:s');
						$update_prepare[] = "%s";
						$request = $wpdb->update(
							$this->transaction,
							$update_var,
							array("ID" => $tran->ID),
							$update_prepare,
							array("%d")
						);
						if($request){
							return true;
						}else{
							$this->transaction_status = "ERROR";
							return false;
						}
					}else{
						//更新の必要がなかったらそのまま
						return true;
					}
				}else{
					//データの登録
					$result = $wpdb->insert(
						$this->transaction,
						array(
							"user_id" => $user_ID,
							"book_id" => $post->ID,
							"price" => $val["mc_gross"],
							"status" => $status ,
							"method" => "PAYPAL",
							"transaction_key" => $val["txn_id"],
							"payer_mail" => $val["payer_email"],
							"registered" => date('Y-m-d H:i:s'),
							"updated" => date('Y-m-d H:i:s')
						),
						array("%d", "%d", "%d", "%s", "%s", "%s", "%s", "%s", "%s")
					);
					if($wpdb->insert_id){
						return true;
					}else{
						$this->transaction_status = "ERROR";
						return false;
					}
				}
			}
		}else{
			//完全にエラー
			$this->transaction_status = "ERROR";
			return false;
		}
	}
	
	/**
	 * ファイルを出力する
	 * 
	 * @return void
	 */
	private function print_file($file_id, $post_id, $user_id)
	{
		$error = false;
		$file = $this->get_files($post_id, $file_id);
		//ファイルの取得を確認
		if(!$file)
			$error = true;
		//ユーザーの所有権を確認
		if(
			($file->free == 0 && !$this->is_owner($post_id, $user_id)) //購入必須にも関わらずファイルを購入していない
			||
			($file->free == 1 && !is_user_logged_in()) //ログイン必須にも関わらずログインしていない
		)
			$error = true;
		//ファイルの所在を確認
		$path = $this->option["dir"].DS.$post_id.DS.$file->file;
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
		}
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
				return (isset($_GET["post_type"]) && isset($_GET["page"]) && $_GET["post_type"] == "ebook" && $_GET["page"] == "lwp-{$page_name}");
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
}
