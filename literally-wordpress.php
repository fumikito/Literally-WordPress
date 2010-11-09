<?php
/**
 * Plugin Name: Litteraly WordPress
 * Plugin URI: http://hametuha.co.jp/plugins/literally-wordpress
 * Description: Making WordPress E-Book Store.
 * Author: Takahashi Fumiki<takahashi.fumiki@hametuha.co.jp>
 * Version: 0.2
 * Author URI: http://hametuha.co.jp/
 * Package WordPress
 * License: GPLv2
 * 
 * This program is free software; you can redistribute it and/or modify 
 * it under the terms of the GNU General Public License as published by 
 * the Free Software Foundation; version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
 * GNU General Public License for more details. 
 * 
 * You should have received a copy of the GNU General Public License 
 * along with this program; if not, write to the Free Software 
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA 
 */

/**
 * ディレクトリーセパレータ
 */
if(!defined("DS"))
	define("DS", DIRECTORY_SEPARATOR); 

//コアクラスをグローバル変数に格納
global $lwp;

//インストール要件を満たしているかを確認
if(literally_wordpress_check_version() && function_exists("curl_init")){
		
	//クラスファイル読み込み
	require_once dirname(__FILE__)."/literally-wordpress.class.php";
	
	/**
	 * Literally_WordPressのインスタンス変数
	 *
	 * @var LIterally_WordPress
	 */
	$lwp = new Literally_WordPress();
	
	//投稿タイプの追加
	add_action("init", array($lwp, "custom_post"));
	
	//管理画面でのみ行うフック
	if(is_admin()){
		/*--------------
		 * アクションフック
		 */
		//テーブル生成
		add_action("admin_init", array($lwp, "table_create"));
		//課金有効かどうかの判断
		add_action("admin_init", array($lwp, "validate"));
		//オプション更新
		if(isset($_GET["post_type"]) && isset($_GET["page"]) && $_GET["post_type"] == "ebook" && $_GET["page"] == "lwp-setting")
			add_action("admin_init", array($lwp, "option_update"));
		//キャンペーン更新
		if(isset($_GET["post_type"]) && isset($_GET["page"]) && $_GET["post_type"] == "ebook" && $_GET["page"] == "lwp-campaign")
			add_action("admin_init", array($lwp, "campaign_update"));
		//電子書籍のアップデート
		add_action("edit_post", array($lwp, "edit_post"));
		//メニューの追加
		add_action("admin_menu", array($lwp, "add_menu"));
		//スタイルシート・JSの追加
		if(isset($_GET["post_type"]) && isset($_GET["page"]) && $_GET["post_type"] == "ebook")
			add_action("admin_head", array($lwp, "assets"));
		//メッセージの出力
		add_action("admin_notice", array($lwp, "admin_notice"));
		//ファイルアップロード用のタブを追加
		add_action("media_upload_ebook", array($lwp, "generate_tab"));
		//ユーザーのコンタクトメソッドにPayPalアカウントを登録する
		add_filter('user_contactmethods',array($lwp, "add_paypal_mail"),12,1);
		//ユーザーに書籍をプレゼントするフォーム
		add_action("edit_user_profile", array($lwp, "give_user_form"));
		//書籍プレゼントが実行されたら
		if(basename($_SERVER["SCRIPT_FILENAME"]) == "user-edit.php")
			add_action("profile_update", array($lwp, "give_user"));
		/*--------------
		 * フィルターフック
		 */
		//ファイルアップロードのタブ生成アクションを追加するフィルター
		add_filter("media_upload_tabs", array($lwp, "upload_tab"));
		//ファイルアップロード可能な拡張子を追加する
		add_filter("upload_mimes", array($lwp, "upload_mimes"));
	}
	//公開画面でのみ行うフック
	else{
		//ヘッダー部分でリクエストの内容をチェックする
		add_action("template_redirect", array($lwp, "manage_ebook"));
	}
	
	//ユーザー関数の読み込み
	require_once dirname(__FILE__).DS."functions.php";
}else{
	add_action("admin_notice", "literally_WordPress_failed");
}


/**
 * インストール要件を満たしていないときに実装する関数
 *
 * @return void
 */
function literally_WordPress_failed()
{
	?>
		<div class='update-nag'>
			<ul>
				<li>Literally WordPressは有効化されていますが、利用できません。PHPのバージョンが5以上でないとダメです。現在のPHPバージョンは<?php echo phpversion(); ?>です。</li>
				<?php if(!function_exists("curl_init")): ?>
				<li>このプラグインはcURLの利用を前提としています。サーバ管理者にPHPでのcURL利用が可能かどうか、確認してください。</li>
				<?php endif; ?>
			</ul>
		</div>
	<?php
}

/**
 * インストール要件を満たしているかをチェック
 *
 * @return boolean
 */
function literally_wordpress_check_version()
{
	$version = explode(".", PHP_VERSION);
	if($version[0] > 4)
		return true;
	else
		return false;	
}