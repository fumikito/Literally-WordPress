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
	 * 指定された投稿が指定された日付にキャンペーンを行っているかを返す
	 * 
	 * @param object|int $post
	 * @param string $time
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
		if($book_id)
			$sql .= $wpdb->prepare("book_id = %d ", $book_id);
		if($user_id)
			$sql .= $wpdb->prepare("user_id = %d ", $user_id);
		if($status)
			$sql .= $wpdb->prepare("status = %s ", $status);
		$sql .= "ORDER BY `registered` DESC ";
		if(is_numeric($offset))
			if($offset == 0)
				$sql .= "LIMIT {$num} ";
			else
				$sql .= "LIMIT ".$offset * $num.", {$num} ";
		return $wpdb->get_results($sql);
	}
	
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
			'register_meta_box_cb' => array($this, "add_metabox"),
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
	
	/**
	 * 投稿更新時の処理
	 * 
	 * @return void
	 */
	public function edit_post()
	{
		if(isset($_REQUEST["_lwpnonce"]) && wp_verify_nonce($_REQUEST["_lwpnonce"], "lwp_price")){
			$price = preg_replace("/[^0-9]/", "", mb_convert_kana($_REQUEST["lwp_price"], "n"));
			if(preg_match("/^[0-9]+$/", $price))
				update_post_meta($_POST["ID"], "lwp_price", $price);
			else{
				$this->message[] = "定価は数字だけにしてください。";
				$this->error = true;
			}
		} 
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
		add_submenu_page("profile.php", "電子書籍購入履歴", "購入履歴", 0, "lwp-history", array($this, "load"));
	}
	
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
			'jquery-calendar-i18n',
			$this->url."/assets/datepicker/i18n/ui.datepicker-ja.js",
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
		}elseif(false !== strpos($_SERVER["REQUEST_URI"], "profile.php")){
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
	public function upload_file($book_id, $name, $file, $path, $desc = "", $public = 1, $free = 0)
	{
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
		return $wpdb->insert_id;
	}
	
	/**
	* ファイルテーブルを更新する
	*
	* @return boolean
	*/
	private function update_file($file_id, $name, $desc, $public = 1, $free = 0)
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
		if($req)
			return true;
		else
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
				if($wpdb->query("DELETE FROM {$this->files} WHERE ID = {$file->ID}"))
					return true;
				else
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
	
	/**
	 * ファイルを出力する
	 */
	private function print_file($file_id, $post_id, $user_id)
	{
		$error = false;
		$file = $this->get_files($post_id, $file_id);
		//ファイルの取得を確認
		if(!$file)
			$error = true;
		//ユーザーの所有権を確認
		if(!$file->free && !$this->is_owner($post_id, $user_id) )
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
	
	/**
	* 投稿ページにフォームを追加する
	*
	* @return void
	*/
	public function add_metabox()
	{
		add_meta_box('ebookdetail', "電子書籍の設定", array($this, 'detail_metabox'), 'ebook', 'side', 'core');
	}
	
	/**
	 * 投稿ページに追加するフォーム
	 * 
	 * @return void
	 */
	public function detail_metabox()
	{
		$files = $this->get_files($_GET["post"]);
		require_once $this->dir.DS."form-template".DS."edit-detail.php";
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
	
	public function assets()
	{
		?>
		<link rel="stylesheet" type="text/css" href="<?php echo $this->url; ?>/assets/style.css?version=<?php echo $this->version; ?>" />
		<?php
	}
}
