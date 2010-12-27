<?php
/**
 * Literally WordPressのユーザー関数
 *
 * @package Literally WordPress
 * @author  Takahashi Fumiki<takahashi.fumiki@hametuha.co.jp>
 */


/**
 * ユーザーが電子書籍を所有しているか否かを返す
 * 
 * ループ内で引数なしで利用すると、現在ログイン中のユーザーが表示中の
 * 電子書籍を購入済みかどうかを判断します。
 * 
 * @param object|int $post (optional) 投稿オブジェクト
 * @param int $user_id (optional) ユーザーID
 */
function lwp_is_owner($post = null, $user_id = null)
{
	global $lwp;
	return $lwp->is_owner($post, $user_id);
}

/**
 * 対象とする電子書籍が0円かどうかを返す
 * 
 * ループ内で使用した場合は現在の投稿。
 * 
 * @param boolean $original (optional) trueの場合は定価が無料のものを返す。falseの場合はキャンペーン期間を含める
 * @param object $post (optional) 現在の投稿オブジェクト
 * @return boolean
 */
function lwp_is_free($original = false, $post = null)
{
	global $lwp;
	if($original)
		return (lwp_original_price($post) == 0);
	else
		return lwp_price() == 0;
}

/**
 * 指定された電子書籍ファイルオブジェクトが無料のものかどうかを返す
 * 
 * @param object $file
 * @return boolean
 */
function lwp_is_sample($file)
{
	
}

/**
 * 指定された投稿が指定された日付にキャンペーンを行っているかを返す
 * 
 * ループ内で引数なしで使用すると、現在表示してる
 * 
 * @param object|int $post
 * @param string $time
 * @return booelan
 */
function lwp_on_sale($post = null, $time = null)
{
	global $lwp;
	return $lwp->is_on_sale($post, $time);
}


/**
 * キャンペーンの終了日を返す
 * 
 * @param object $post (optional)
 * @param boolean $timestamp (optional) タイムスタンプ型で取得する場合はtrue
 * @return string
 */
function lwp_campaign_end($post = null, $timestamp = false)
{
	global $lwp;
	if(!$post)
		global $post;
	$campaign = $lwp->get_campaign($post->ID, date('Y-m-d H:i:s'));
	if(!$campaign)
		return false;
	else{
		if($timestamp)
			return strtotime($campaign->end);
		else
			return mysql2date(get_option("date_format"), $campaign->end, false);
	}
}

/**
 * 電子書籍の価格を返す
 * 
 * ループ内で引数なしで使用すると、表示中の電子書籍の定価を返します。
 * キャンペーン中はキャンペーン価格を返します。
 * 
 * @param object $post (optional)
 * @return int
 */

function lwp_price($post = null)
{
	if(!$post)
		global $post;
	if(lwp_on_sale($post)){
		global $lwp;
		$campaign = $lwp->get_campaign($post->ID, date('Y-m-d H:i:s'));
		return $campaign->price;
	}else
		return lwp_original_price($post);
}


/**
 * 電子書籍のカスタムフィールドを返す
 * 
 * @param string $key
 * @param int|object (optional) ループ内で使用した場合は現在のポスト
 * @param boolean $single (optional) 配列で取得する場合はfalse
 * @return string
 */
function _lwp_post_meta($key, $post = null, $single = true)
{
	if($post){
		if(is_numeric($post)){
			$post_id = $post;
		}else{
			$post_id = $post->ID;
		}
	}elseif(is_null($post)){
		global $post;
		$post_id = $post->ID;
	}
	if(!is_numeric($post_id))
		return null;
	else
		return get_post_meta($post_id, $key, $single);
}

/**
 * 電子書籍の定価を返す
 * 
 * @param object $post (optional)ループ内で引数なしで使用すると、表示中の電子書籍の定価を返します。
 * @return int
 */
function lwp_original_price($post = null)
{
	return (int) _lwp_post_meta("lwp_price", $post);
}

/**
 * 電子書籍の分量を返す
 * 
 * @param object $post (optional)ループ内で引数なしで使用すると、表示中の電子書籍の分量を返します。
 * @return int
 */
function lwp_ammount($post = null)
{
	return (int) _lwp_post_meta("lwp_number", $post);
}

/**
 * 電子書籍のISBNを返す
 * 
 * @param object $post (optional)ループ内で引数なしで使用すると、表示中の電子書籍のISBNを返します。
 * @return string
 */
function lwp_isbn($post = null)
{
	return _lwp_post_meta("lwp_isbn", $post);
}

/**
 * 投稿に所属するファイルを返す
 * 
 * @param string $accessibility (optional) ファイルのアクセス権限 all, owner, member, any
 * @param object $post (optional)
 * @return array
 */
function lwp_get_files($accessibility = "all", $post = null)
{
	if(!$post)
		global $post;
	global $lwp, $wpdb;
	$sql = "SELECT * FROM {$lwp->files} WHERE book_id = %d AND public = 1 ";
	switch($status){
		case "owner":
			$sql .= "AND free = 0";
			break;
		case "member":
			$sql .= "AND free = 1";
			break;
		case "any":
			$sql .= "AND free = 2";
	}
	return $wpdb->get_results($wpdb->prepare($sql, $post->ID));
}

/**
 * ファイルへのリンクを返す
 * 
 * @param int $file_id
 * @return string
 */
function lwp_file_link($file_id)
{
	$url = get_permalink();
	if(false === strpos($url, "?"))
		$url .= "?ebook_file={$file_id}";
	else
		$url .= "&amp;ebook_file={$file_id}";
	return $url;
}

/**
 * ファイルサイズを取得する
 * 
 * @param object $file
 * @return string
 */
function lwp_get_size($file)
{
	global $lwp;
	$path = $lwp->option["dir"].DS.$file->book_id.DS.$file->file;
	if(file_exists($path)){
		$size = filesize($path);
		if($size > 1000000){
			return round($size / 1000000,1)."MB";
		}elseif($size > 1000){
			return round($size / 1000)."KB";
		}else{
			return $size."B";
		}
	}else
		return "0B";
}

/**
 * ファイルの拡張子を返す
 * 
 * @param object $file
 * @return string
 */
function lwp_get_ext($file)
{
	global $lwp;
	$path = $lwp->option["dir"].DS.$file->book_id.DS.$file->file;
	if(file_exists($path))
		return pathinfo($path, PATHINFO_EXTENSION);
	else
		return "";
}

/**
 * ファイルのアクセス権を返す
 * 
 * @param object $file ファイルオブジェクト
 * @return string owner, member, any, noneのいずれか
 */
function lwp_get_accessibility($file)
{
	switch($file->free){
		case 0:
			return "owner";
			break;
		case 1:
			return "member";
			break;
		case 2:
			return "any";
			break;
		default:
			return "none";
	}
}

/**
 * ファイルの最終更新日を返す
 * 
 * @param object $file ファイルオブジェクト
 * @param boolean $registered (optional) 最終更新日ではなく登録日を欲しい場合はfalse
 * @param boolean $echo (optional) 出力したくない場合はfalse
 * @param 
 */
function lwp_get_date($file, $registered = true, $echo = true)
{
	$date = $registered ? $file->registered : $file->updated;
	$formatted = mysql2date(get_option('date_format'), $date, false);
	if($echo)
		echo $formatted;
	else
		return $formatted;
}

/**
 * 購入ボタンを出力する
 * 
 * @param object $post (optional) 投稿オブェクト。ループ内では指定する必要はありません。
 * @param string $btn_src (optional) 購入ボタンまでのURL
 * @param string $extra (optional) その他、フォーム内に出力したいHTMLタグなど
 * @return void
 */
function lwp_buy_now($post = null, $btn_src = "https://www.paypal.com/ja_JP/JP/i/btn/btn_buynowCC_LG.gif", $extra = "")
{
	global $lwp;
	if(!$post)
		global $post;
	//キャンセル用のURLを設定
	$url = get_permalink();
	if(false === strpos($url, "?")){
		$cancel_url = $url."?lwp_cancel=1";
		$return_url = $url."?lwp_return=1";
	}else{
		$cancel_url = $url."&amp;lwp_cancel=1";
		$return_url = $url."&amp;lwp_return=1";
	}
	?>
	 <form action="https://www.paypal.com/jp/cgi-bin/webscr" method="post"> 
		<input type="hidden" name="cmd" value="_xclick" />
		<input type="hidden" name="amount" value="<?php echo lwp_price(); ?>" />
		<input type="hidden" name="charset" value="utf-8" />
		<input type="hidden" name="business" value="<?php echo $lwp->option["marchant_id"];?>" />
		<input type="hidden" name="item_name" value="<?php the_title(); ?>" />
		<input type="hidden" name="item_number" value="<?php echo $lwp->option["slug"]; ?>-<?php echo $post->ID; ?>" />
		<input type="hidden" name="shipping" value="0" />
		<input type="hidden" name="no_shipping" value="1" />
		<input type="hidden" name="tax" value="0" />
		<input type="hidden" name="quantity" value="1" />
		<input type="hidden" name="lc" value="JP" />
		<input type="hidden" name="currency_code" value="JPY" />
		<input type="hidden" name="page_style" value="primary" />
		<input type="hidden" name="cn" value="<?php bloginfo("name"); ?>へのご意見（オプション）" />
		<input type="hidden" name="return" value="<?php echo $return_url; ?>" />
		<input type="hidden" name="cancel_return" value="<?php echo $cancel_url; ?>" />
		<input type="hidden" name="notify_url" value="<?php echo $lwp->url; ?>/paypal/ipn.php" />
		<input type="hidden" name="cbt" value="<?php bloginfo("name"); ?>へ戻る" />
		<input type="image" src="<?php $lwp->h($btn_src); ?>" name="submit" alt="購入" /><br />
		<?php echo $extra; ?>
	</form>
	<?php
}

/**
 * 購入がキャンセルされたか否かを返す。
 * 
 * @return boolean
 */
function lwp_is_canceled()
{
	if(isset($_GET["lwp_cancel"]))
		return true;
	else
		return false;
}

/**
 * 購入を完了して、成功したか否かを返す
 * 
 * @return boolean
 */
function lwp_is_success()
{
	global $lwp;
	return (isset($_GET["lwp_return"]) && $lwp->on_transaction && $lwp->transaction_status == "SUCCESS");
}

/**
 * 購入処理にエラーがあったか否かを返す
 * 
 * @return boolean
 */
function lwp_is_transaction_error()
{
	global $lwp;
	return (isset($_GET["lwp_return"]) && $lwp->on_transaction && ($lwp->transaction_status == "ERROR" || $lwp->transaction_status == "FAILED"));
}