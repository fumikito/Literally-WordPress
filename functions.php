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
 * @since 0.2
 * @param object|int $post (optional) 投稿オブジェクト
 * @param int $user_id (optional) ユーザーID
 * @return boolean
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
		return lwp_price($post) == 0;
}

/**
 * @deprecated
 * @param type $post
 * @return type 
 */
function lwp_ammount($post = null)
{
	return (int) _lwp_post_meta("lwp_number", $post);
}

/**
 * 電子書籍のISBNを返す
 * @deprecated
 * @param object $post (optional)ループ内で引数なしで使用すると、表示中の電子書籍のISBNを返します。
 * @return string
 */
function lwp_isbn($post = null)
{
	return _lwp_post_meta("lwp_isbn", $post);
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
 * ループ内で引数なしで使用すると、現在表示してるいる投稿がキャンペーン中か否かを示す
 * 
 * @global Literally_WordPress $lwp
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
	$campaign = $lwp->get_campaign($post->ID, gmdate('Y-m-d H:i:s'));
	if(!$campaign)
		return false;
	else{
		if($timestamp)
			return strtotime(get_date_from_gmt($campaign->end));
		else
			return mysql2date(get_option("date_format"), get_date_from_gmt ($campaign->end), false);
	}
}

/**
 * 電子書籍の価格を返す
 * 
 * ループ内で引数なしで使用すると、表示中の電子書籍の定価を返します。
 * キャンペーン中はキャンペーン価格を返します。
 * 
 * @global object $post
 * @global Literally_WordPress $lwp
 * @param object $post (optional)
 * @return int
 */
function lwp_price($post = null)
{
	if(!$post){
		global $post;
	}else{
		$post = get_post($post);
	}
	if(lwp_on_sale($post)){
		global $lwp;
		$campaign = $lwp->get_campaign($post->ID, date('Y-m-d H:i:s'));
		return $campaign->price;
	}else{
		return lwp_original_price($post);
	}
}

/**
 * 現在設定されている通貨記号を返す
 * 
 * @since 0.8
 * @global Literally_WordPress $lwp
 * @return string
 */
function lwp_currency_code(){
	global $lwp;
	return $lwp->option['currency_code'];
}

/**
 * 現在設定されている通貨の実態を返す
 * @global Literally_WordPress $lwp
 * @return string
 */
function lwp_currency_symbol(){
	global $lwp;
	return PayPal_Statics::currency_entity(lwp_currency_code());
}

/**
 * 現在の価格を通貨記号付きで返す
 * 
 * @since 0.8
 * @param object $post
 * @return void
 */
function lwp_the_price($post = null){
	echo lwp_currency_symbol().number_format(lwp_price());
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
 * @since 0.3
 * @param int $file_id
 * @return string
 */
function lwp_file_link($file_id)
{
	return get_bloginfo('url')."?lwp=file&lwp_file={$file_id}";
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
 * 電子書籍のデバイス登録情報を返す
 * 
 * @param object $post(optional) 投稿オブジェクト。指定しない場合は現在の投稿
 * @return array デバイス情報の配列。各要素はname(string), slug(string), valid(boolean)のキーを持つ
 */
function lwp_get_devices($post = null)
{
	global $lwp, $wpdb;
	if(!$post)
		global $post;
	//デバイスの一覧を取得
	$devices = $wpdb->get_results("SELECT * FROM {$lwp->devices}");
	
	//登録されたファイルの一覧を取得
	$sql = <<<EOS
		SELECT * FROM {$lwp->file_relationships} as r
		LEFT JOIN {$lwp->files} as f
		ON r.file_id = f.ID
		WHERE f.book_id = {$post->ID}
EOS;
	$files = $wpdb->get_results($sql);
	
	//登録されたデバイスIDの一覧を配列に変換
	$registered_devices = array();
	foreach($files as $f){
		$registered_devices[] = $f->device_id;
	}
	//リストの照合
	$arr = array();
	foreach($devices as $d){
		if(false !==  array_search($d->ID, $registered_devices)){
			$arr[] = array(
				"name" => $d->name,
				"slug" => $d->slug,
				"valid" => true
			);
		}else{
			$arr[] = array(
				"name" => $d->name,
				"slug" => $d->slug,
				"valid" => false
			);
		}
	}
	return $arr;
}

/**
 * ファイルオブジェクトを受け取り、対応しているデバイスを返す
 * 
 * @param object $file
 * @param boolean $slug(optional) デバイスのスラッグが欲しい場合はtrue
 * @return array デバイス名の配列。$slugをtrueにした場合、各要素は文字列ではなくnameとslugをキーに持つ配列となる。
 */
function lwp_get_file_devices($file, $slug = false)
{
	global $wpdb,$lwp;
	$sql = <<<EOS
		SELECT * FROM {$lwp->file_relationships} as r
		LEFT JOIN {$lwp->devices} as d
		ON r.device_id = d.ID
		WHERE r.file_id = {$file->ID}
EOS;
	$results = $wpdb->get_results($sql);
	if(empty($results))
		return array();
	else{
		$array = array();
		foreach($results as $r){
			if($slug){
				$array[] = array(
					"name" => $r->name,
					"slug" => $r->slug
				);
			}else{
				$array[] = $r->name;
			}
		}
		return $array;
	}
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
 * ファイルオブジェクトを受け取り、それが現在のユーザーにとって
 * アクセス可能かを返す
 * 
 * @param object $file
 * @return boolean
 */
function lwp_file_accessible($file){
	switch(lwp_get_accessibility($file)){
		case "owner":
			return lwp_is_owner();
			break;
		case "member":
			return is_user_logged_in();
			break;
		case "any":
			return true;
			break;
		default:
			return false;
			break;
	}
}

/**
 * ファイルの最終更新日を返す
 * 
 * @param object $file ファイルオブジェクト
 * @param boolean $registered (optional) 最終更新日ではなく登録日を欲しい場合はfalse
 * @param boolean $echo (optional) 出力したくない場合はfalse
 * @return string|void 
 */
function lwp_get_date($file, $registered = true, $echo = true)
{
	$date = $registered ? $file->registered : $file->updated;
	$formatted = mysql2date(get_option('date_format'), get_date_from_gmt($date), false);
	if($echo)
		echo $formatted;
	else
		return $formatted;
}

/**
 * 登録されているファイルのリストを返す
 * @global Literally_WordPress $lwp
 * @param string $accessibility
 * @param object $post
 * @return string
 */
function lwp_get_file_list($accessibility = "all", $post = null){
	global $lwp;
	$tag = "<!-- Literally WordPress {$lwp->version} --><div class=\"lwp-files\"><h3>".$lwp->_('Registered Files')."</h3>";
	$tag .= "<table class=\"lwp-file-table\">";
	$tag .= "
		<thead>
			<tr>
				<th class=\"name\">".$lwp->_('File Name')."</th>
				<th>".$lwp->_('Description')."</th>
				<th>".$lwp->_('Available with')."</th>
				<th>".$lwp->_('Download')."</th>
			</tr>
		</thead>
		<tbody>
";
	foreach(lwp_get_files($accessibility, $post) as $file){
		$ext = lwp_get_ext($file);
		$desc = wpautop($file->desc);
		$button = lwp_file_accessible($file) ? "<a class=\"button lwp-dl\" href=\"".lwp_file_link($file->ID)."\">".$lwp->_('download')."</a>"
											 : "<a class=\"button disabled\">".$lwp->_('Unavailable')."</a>";
		$size = sprintf($lwp->_("File Size: %s"), lwp_get_size($file));
		$published = sprintf($lwp->_("Published: %s"), lwp_get_date($file, true, false));
		$updated = sprintf($lwp->_("Updated: %s"), lwp_get_date($file, false, false));
		$devices = implode(", ", lwp_get_file_devices($file));
		$tag .= <<<EOS
				<tr>
					<td class="{$ext}">{$file->name}</td>
					<td>
						{$desc}
						<p class="desc">{$published}<br />{$updated}</p>
					</td>
					<td>{$devices}</td>
					<td><p class="lwp-button">{$button}</p><span class="lwp-file-size">{$size}</span></td>
				</tr>
EOS;
	}
	$tag .= "</tbody></table></div>";
	return $tag;
}

/**
 * 対応端末のテーブルを返す
 * @since 0.8
 * @global Literally_WordPress $lwp
 * @param object $post
 * @return string
 */
function lwp_get_device_table($post = null){
	global $lwp;
	$tag = "<!-- Literally WordPress {$lwp->version} --><div class=\"lwp-devices\"><h3>".$lwp->_('Devices Available With')."</h3>";
	$tag .= "<table class=\"lwp-device-table\">";
	$tag .= "
		<thead>
			<tr>
				<th class=\"slug\">".$lwp->_('Device Name')."</th>
				<th>".$lwp->_('Avalability')."</th>
			</tr>
		</thead>
		<tbody>
";
	foreach(lwp_get_devices($post) as $device){
		$validity = $device['valid'] ? $lwp->_("Available") :  $lwp->_("Unconfirmed");
		$class = $device['valid'] ? "available" : "unconfirmed";
		$tag .= "
			<tr>
				<td class=\"".$device['slug']."\">".$device['name']."</td>
				<td class=\"{$class}\">{$validity}</td>
			</tr>";
	}
	$tag .= "</tbody></table></div>";
	return $tag;
}


/**
 * 購入した電子書籍のリストを返す
 * 
 * @param string $status (optional) SUCCESS, Cancel, Errorのいずれか
 * @param int $user_id (optional) 指定しない場合は現在のユーザー
 * @return array
 */
function lwp_bought_books($status = "SUCCESS", $user_id = null)
{
	global $lwp;
	if(!$user_id){
		global $user_ID;
		$user_id = $user_ID;
	}
	//トランザクションを取得
	$trans = $lwp->get_transaction(null, $user_id, $status);
	$book_ids = array();
	foreach($trans as $t){
		$book_ids[] = $t->book_id;
	}
	//投稿オブジェクトを取得
	if(empty($book_ids))
		return array();
	else
		return get_posts(
			array(
				"post_type" => "ebook",
				"post__in" => $book_ids
			)
		);
}

/**
 * 投稿に紐づいたユーザーの購入詳細を返す
 * 
 * @param int $book_id
 * @param int $user_id (optional) 指定しない場合は現在のユーザー
 * @return object
 */
function lwp_get_tran($book_id, $user_id = null)
{
	global $lwp;
	if(!$user_id){
		global $user_ID;
		$user_id = $user_ID;
	}
	return $lwp->get_transaction($book_id, $user_id, "SUCCESS");
}

/**
 * ユーザーがこれまでに購入した総額を返す
 */
function lwp_user_bought_price($user_id = null)
{
	global $lwp, $wpdb;
	if(!$user_id){
		global $user_ID;
		$user_id = $user_ID;
	}
	$sql = <<<EOS
		SELECT user_id, SUM(price) FROM {$lwp->transaction}
		WHERE user_id = %d AND status = 'SUCCESS'
		GROUP BY user_id
EOS;
	$req = $wpdb->get_row($wpdb->prepare($sql, $user_id));
	return ($req) ? $req->{'SUM(price)'} : 0;
}

/**
 * 購入ボタンを出力する
 * 
 * @since 0.3
 * @param mixed $post (optional) 投稿オブェクトまたは投稿ID。ループ内では指定する必要はありません。
 * @param string $btn_src (optional) 購入ボタンまでのURL nullを渡すと画像ではなくaタグになる
 * @return void
 */
function lwp_buy_now($post = null, $btn_src = false)
{
	global $lwp;
	//投稿オブジェクトを取得
	if(!$post){
		global $post;
		$post_id = $post->ID;
	}elseif(is_numeric($post)){
		$post_id = $post;
	}elseif(is_object($post) && isset($post->ID)){
		$post_id = $post->ID;
	}else{
		return;
	}
	//購入可能か判別
	if(lwp_is_free(true, $post)){
		return;
	}
	if(is_null($btn_src)){
		$tag = $lwp->_('Buy Now');
	}else{
		if(!is_string($btn_src)){
			$btn_src = "https://www.paypal.com/ja_JP/JP/i/btn/btn_buynowCC_LG.gif";
		}
		$tag = "<img src=\"".htmlspecialchars($btn_src, ENT_QUOTES, 'utf-8')."\" alt=\"".$lwp->_('Buy Now')."\" />";
	}
	return "<a class=\"lwp-buynow\" href=\"".get_bloginfo('url')."?lwp=buy&lwp_id={$post_id}\">{$tag}</a>";
}

/**
 * キャンペーンの終了日時をタグにして返す
 * @global object $post
 * @global Literally_WordPress $lwp
 * @param object $post
 * @return string
 */
function lwp_campaign_timer($post = null, $prefix = null){
	if(!$post){
		global $post;
	}
	if(lwp_on_sale($post)){
		global $lwp;
		if(!$prefix){
			$prefix = $lwp->_('Left time: ');
		}
		//終了日を取得
		$end = lwp_campaign_end($post, true);
		if(!$end){
			return false;
		}
		//残り時間
		$last = $end - time();
		$days = floor($last / (60 * 60 * 24));
		$last -= $days * 60 * 60 * 24;
		$hours = floor($last / (60 * 60));
		$last -= $hours * 60 * 60;
		$minutes = floor($last / 60);
		$last -= $minutes * 60;
		$seconds = $last;
		//タグを作成
		$tag = "";
		if($days > 0){
			$unit = $days == 1 ? $lwp->_('day') : $lwp->_('days');
			$tag .= sprintf($lwp->_('<span class="day">%1$d</span>%2$s'), $days, $unit)." ";
		}
		$tag .= sprintf($lwp->_('<span class="hour">%02d</span>h '), $hours);
		$tag .= sprintf($lwp->_('<span class="minutes">%02d</span>m '), $minutes);
		$tag .= sprintf($lwp->_('<span class="seconds">%02d</span>s'), $seconds);
		return "<p class=\"lwp-timer\"><span class=\"prefix\">{$prefix}</span>{$tag}</p>";
	}else{
		return "";
	}
}

/**
 * セール中の値引き率を返す
 * @since 0.8
 * @global Literally_WordPress $lwp
 * @param object $post
 * @return string
 */
function lwp_discout_rate($post = null){
	global $lwp;
	if(lwp_on_sale($post)){
		$orig_price = lwp_original_price($post);
		$current_price = lwp_price($post);
		return sprintf($lwp->_("%d%% Off"), floor((1 - $current_price / $orig_price) * 100));
	}else{
		return "";
	}
}

/**
 * 購入用のフォームを返す
 * 
 * @global Literally_WordPress $lwp
 * @global object $post
 * @param object $post
 * @param string $btn_src (optional) Src of "buy now" button.
 * @return string
 */
function lwp_show_form($post = null, $btn_src = null){
	global $lwp;
	if(!$post){
		global $post;
	}
	if(!$post){
		return "";
	}
	$timer = lwp_campaign_timer($post);
	if(!empty($timer)){
		$timer = '<p class="lwp-campaign-caption">'.sprintf($lwp->_('On SALE till %s'), lwp_campaign_end($post)).''.$timer;
	}
	$currency_code = $lwp->option['currency_code'];
	$currency_symbol = PayPal_Statics::currency_entity($currency_code);
	if(lwp_on_sale($post)){
		//セール中の場合
		$original_price = lwp_original_price($post);
		$current_price = lwp_price($post);
		$price_tag = "<p class=\"lwp-price\"><small>({$currency_code})</small><del>{$currency_symbol} ".number_format($original_price)."</del><span class=\"price\">{$currency_symbol} ".number_format($current_price)."</span><span class=\"lwp-off\">".  lwp_discout_rate($post)."</span></p>";
		$class = "lwp-form onsale";
	}elseif(lwp_original_price() > 0){
		//売り物だけどセール中じゃない場合
		$price_tag = "<p class=\"lwp-price\"><small>({$currency_code})</small><span class=\"price\">{$currency_symbol} ".  number_format(lwp_price($post))."</span></p>";
		$class = "lwp-form";
	}
	if(is_user_logged_in()){
		$button = $btn_src ? lwp_buy_now($post, $btn_src) : lwp_buy_now($post);
		$button = "<p class=\"lwp-button\">{$button}</p>";
	}else{
		$button = "<p class=\"lwp-button\"><a class=\"button login\" href=\"".wp_login_url(get_bloginfo('url')."?lwp=buy&lwp_id={$post->ID}")."\">".__("Log in")."</a>".str_replace("<a", "<a class=\"button\"", wp_register('', '', false))."</p>";
	}
	return <<<EOS
<!-- Literally WordPress {$lwp->version} -->
<div class="{$class}">
	{$timer}
	{$price_tag}
	{$button}
</div>
EOS;
}


/**
 * 購入がキャンセルされたか否かを返す。
 * 
 * @deprecated
 * @return boolean
 */
function lwp_is_canceled()
{
	if(isset($_GET["lwp"]) && $_GET['lwp'] == "cancel")
		return true;
	else
		return false;
}

/**
 * 購入を完了して、成功したか否かを返す
 * 
 * @deprecated
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
 * @deprecated
 * @return boolean
 */
function lwp_is_transaction_error()
{
	global $lwp;
	return (isset($_GET["lwp_return"]) && $lwp->on_transaction && ($lwp->transaction_status == "ERROR" || $lwp->transaction_status == "FAILED"));
}