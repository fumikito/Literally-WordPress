<?php
/**
 * Plugin Name: Litteraly WordPress
 * Plugin URI: http://wordpress.org/extend/plugins/literally-wordpress/
 * Description: This plugin make your WordPress post payable. Registered users can buy your post via PayPal. You can provide several ways to reward their buying. Add rights to download private file, to accesss private post and so on.
 * Author: Takahashi Fumiki<takahashi.fumiki@hametuha.co.jp>
 * Version: 0.8.10
 * Author URI: http://takahashifumiki.com
 * Text Domain: literally-wordpress
 * Domain Path: /language/
 * 
 * 
 * @Package WordPress
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

//インストール要件を満たしているかを確認
if(literally_wordpress_check_version()){
		
	//クラスファイル読み込み
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."literally-wordpress.class.php";
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."paypal".DIRECTORY_SEPARATOR."paypal_statics.php";
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'literally-wordpress-statics.php';
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."literally-wordpress-notifier.php";
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."literally-wordpress-subscription.php";
	
	/**
	 * Literally_WordPressのインスタンス変数
	 *
	 * @var Literally_WordPress
	 */
	$lwp = new Literally_WordPress();

	if(is_admin()){
		//管理画面でのみ行うフックを登録
		$lwp->admin_hooks();
	}else{
		//公開画面でのみ行うフック
		$lwp->public_hooks();
	}
	//ユーザー関数の読み込み
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."functions.php";
	
}else{
	add_action("admin_notice", "literally_WordPress_failed");
}


/**
 * インストール要件を満たしていないときに実行する関数
 *
 * @return void
 */
function literally_WordPress_failed(){
	load_plugin_textdomain('literally-wordpress', false, basename(__FILE__).DIRECTORY_SEPARATOR."language");
	?>
		<div class='update-nag'>
			<ul>
				<li><?php printf(__('Literally WordPress is activated but isn\'t available. This plugin needs PHP version 5<. Your PHP version is %1$s', 'literally-wordpress'), phpversion()); ?></li>
				<?php if(!function_exists("curl_init")): ?>
				<li><?php _e('This plugin needs curl module. Please contact to your server administrator to check if cUrl is available.', 'literally-wordpress');?></li>
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
function literally_wordpress_check_version(){
	$version = explode(".", PHP_VERSION);
	if($version[0] > 4)
		if(function_exists("curl_init"))
			return true;
		else
			return false;
	else
		return false;	
}