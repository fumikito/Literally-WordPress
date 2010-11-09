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
			return mysql2date(get_option("date_format"), $campaign->end);
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
 * 電子書籍の定価を返す
 * 
 * ループ内で引数なしで使用すると、表示中の電子書籍の定価を返します。
 * 
 * @param object $post (optional)
 * @return int
 */
function lwp_original_price($post = null)
{
	if(!$post)
		global $post;
	elseif(is_numeric($post))
		$post = wp_get_single_post($post);
	return (int) get_post_meta($post->ID, "lwp_price", true);
}


/**
 * 投稿に所属するファイルを返す
 * 
 * @param boolean $free (optional) 立ち読み用ファイルか否か
 * @param object $post (optional)
 * @return array
 */
function lwp_get_files($free = false, $post = null)
{
	if(!$post)
		global $post;
	global $lwp, $wpdb;
	$sql = "SELECT * FROM {$lwp->files} WHERE book_id = %d AND public = 1 AND free = ";
	$sql .= ($free) ? 1 : 0;
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