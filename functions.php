<?php
/**
 * Literally WordPressのユーザー関数
 * 
 * @package Literally WordPress
 * @author  Takahashi Fumiki<takahashi.fumiki@hametuha.co.jp>
 */

//Load internal functions
require_once dirname(__FILE__).DIRECTORY_SEPARATOR."functions-internal.php";


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
 * Returns is specified user is subscriber
 * @global Literally_WordPress $lwp
 * @param int $user_ID
 * @return int
 */
function lwp_is_subscriber($user_ID = null){
	global $lwp;
	return $lwp->subscription->is_subscriber($user_ID);
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
function lwp_on_sale($post = null, $time = null){
	global $lwp;
	return $lwp->campaign_manager->is_on_sale($post, $time);
}


/**
 * キャンペーンの終了日を返す
 * 
 * @param object $post (optional)
 * @param boolean $timestamp (optional) タイムスタンプ型で取得する場合はtrue
 * @return string
 */
function lwp_campaign_end($post = null, $timestamp = false){
	global $lwp;
	$post = get_post($post);
	$campaign = $lwp->campaign_manager->get_campaign($post->ID, date_i18n('Y-m-d H:i:s'));
	if(!$campaign){
		return false;
	}else{
		if($timestamp){
			return strtotime($campaign->end);
		}else{
			return mysql2date(get_option("date_format"), $campaign->end, false);
		}
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
function lwp_price($post = null){
	$post = get_post($post);
	if(lwp_on_sale($post)){
		global $lwp;
		return $lwp->campaign_manager->get_sale_price($post->ID);
	}else{
		return lwp_original_price($post);
	}
}

/**
 * Returns days for expiration
 * @param object $post
 * @return int
 */
function lwp_expires($post = null){
	return (int)_lwp_post_meta('_lwp_expires', $post);
}

/**
 * Returns expires date in GMT
 * @param object $post
 * @return string
 */
function lwp_expires_date($post = null){
	$expires = lwp_expires($post);
	if($expires){
		return date('Y-m-d H:i:s', strtotime(gmdate('Y-m-d H:i:s')) + (lwp_expires($post) * 24 * 60 * 60));
	}else{
		return '0000-00-00 00:00:00';
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
	echo lwp_currency_symbol().number_format(lwp_price($post));
}


/**
 * Returns price
 * 
 * @global Literally_WordPress $lwp
 * @param object $post (optional)ループ内で引数なしで使用すると、表示中の電子書籍の定価を返します。
 * @return int
 */
function lwp_original_price($post = null){
	global $lwp;
	$post = get_post($post);
	return (float) get_post_meta($post->ID, $lwp->price_meta_key, true);
}


/**
 * 投稿に所属するファイルを返す
 * 
 * @param string $accessibility (optional) ファイルのアクセス権限 all, owner, member, any
 * @param object $post (optional)
 * @return array
 */
function lwp_get_files($accessibility = "all", $post = null){
	if(!$post)
		global $post;
	global $lwp, $wpdb;
	$sql = "SELECT * FROM {$lwp->files} WHERE book_id = %d AND public = 1 ";
	switch($accessibility){
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
 * Returns if this post has downloadable contents
 * @global wpdb $wpdb
 * @global Literally_WordPress $lwp
 * @global object $post
 * @param object $post
 * @return boolean 
 */
function lwp_has_files($post = null){
	global $wpdb, $lwp;
	if(is_null($post)){
		global $post;
	}else{
		$post = get_post($post);
	}
	$sql = "SELECT COUNT(ID) FROM {$lwp->files} WHERE book_id = %d AND public = 1 ";
	return (boolean)$wpdb->get_var($wpdb->prepare($sql, $post->ID));
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
	return lwp_endpoint('file')."&lwp_file={$file_id}";
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
	$path = $lwp->option["dir"].DIRECTORY_SEPARATOR.$file->book_id.DIRECTORY_SEPARATOR.$file->file;
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
	$path = $lwp->post->file_directory.DIRECTORY_SEPARATOR.$file->book_id.DIRECTORY_SEPARATOR.$file->file;
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
 * Returns if user can download file
 * @global Literally_WordPress $lwp
 * @global wpdb $wpdb
 * @param object|int $file file object or file ID
 * @param int $user_id
 * @return boolean
 */
function lwp_user_can_download($file, $user_id = null){
	global $lwp, $wpdb;
	if(is_numeric($file)){
		$file = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$lwp->files}", $file));
	}
	if(!$user_id){
		$user_id = get_current_user_id();
	}
	// Global flag
	if(!$file->public){
		return false;
	}else{
		switch($file->free){
			case 0:
				if(!lwp_is_owner($file->book_id, $user_id)){
					return false;
				}
				break;
			case 1:
				if(!is_user_logged_in()){
					return false;
				}
				break;
			default:
				return false;
				break;
		}
	}
	// If no accessiblity, return false.
	$downloadable = true;
	// Get parent post's user meta
	if(!$lwp->post->before_download_limit($file->book_id, $user_id)){
		$downloadable = false;
	}
	// Get file limitation
	if($file->limitation > 0 && $user_id > 0 && $file->limitation <= lwp_user_download_count($file->ID, $user_id)){
		//Get download log
		$downloadable = false;
	}
	return $downloadable;
}

/**
 * Get user donwload count.
 * @global Literally_WordPress $lwp
 * @global wpdb $wpdb
 * @param int $file_id
 * @param int $user_id
 * @return int
 */
function lwp_user_download_count($file_id, $user_id){
	global $lwp, $wpdb;
	$sql = <<<EOS
		SELECT COUNT(ID) FROM {$lwp->file_logs}
		WHERE file_id = %d AND user_id = %d
EOS;
	return (int) $wpdb->get_var($wpdb->prepare($sql, $file_id, $user_id));
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
 * @deprecated 0.9.3
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
		$desc = wpautop($file->detail);
		$button = lwp_user_can_download($file, get_current_user_id())
				? "<a class=\"button lwp-dl\" href=\"".lwp_file_link($file->ID)."\">".$lwp->_('download')."</a>"
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
 * Display File tables
 * @global Literally_WordPress $lwp
 * @param array $args
 * @return boolean|string
 */
function lwp_list_files($args = array()){
	global $lwp;
	$args = wp_parse_args($args, array(
		'list_type' => 'table',
		'list_class' => 'lwp-file-table',
		'echo' => true,
		'callback' => '_lwp_list_files',
		'post' => null,
		'accessibility' => 'all'
	));
	$post = get_post($args['post']);
	switch($args['list_type']){
		case 'table':
			$before = sprintf('<table class="%s"><caption>%s</caption><thead><tr>'.
				'<th class="name">%s</th>'.
				'<th class="description">%s</th>'.
				'<th class="device">%s</th>'.
				'<th class="updated">%s</th>'.
				'<th class="donload">%s</th>'.
				'</tr></thead><tbody>', esc_attr($args['list_class']), $lwp->_('Registered Files'),
				$lwp->_('File Name'), $lwp->_('Description'), $lwp->_('Available with'), $lwp->_('Updated'), $lwp->_('Download'));
			$after = '</tbody></table>';
			break;
		case 'ul':
		case 'dl':
		case 'ol':
			$before = sprintf('<%s class="%s">', $args['list_type'], esc_attr($args['list_class']));
			$after = sprintf('</%s>', $args['list_type']);
			break;
		default:
			$before = '';
			$after = '';
			break;
	}
	switch($args['accessibility']){
		case 'owner':
		case 'member':
			$accessibility = $args['accessibility'];
			break;
		default:
			$accessibility = 'all';
			break;
	}
	$files = lwp_get_files($accessibility, $post);
	if(empty($files)){
		return false;
	}
	$list = '';
	$callback = is_callable($args['callback']) ? $args['callback'] : '_lwp_list_files';
	foreach($files as $file){
		$ext = lwp_get_ext($file);
		$url = lwp_file_link($file->ID);
		$user_can_access = lwp_user_can_download($file);
		$size = lwp_get_size($file);
		$devices = lwp_get_file_devices($file);
		$list .= call_user_func_array($callback, array($file, $user_can_access, $url, $size, $ext, $devices, $args['list_type']));
	}
	if($args['echo']){
		echo $before.$list.$after;
		return true;
	}else{
		return $before.$list.$after;
	}
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
	$tag = "<!-- Literally WordPress {$lwp->version} --><div class=\"lwp-devices\">";
	$tag .= "<table class=\"lwp-device-table\"><caption>".$lwp->_('Devices Available With')."</caption>";
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
function lwp_bought_books($status = "SUCCESS", $user_id = null){
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
	return "<a rel=\"noindex,nofollow\" class=\"lwp-buynow\" href=\"".  lwp_buy_url($post)."\">{$tag}</a>";
}

/**
 * Returns url to buy page
 * @global object $post
 * @param object $post
 * @return string 
 */
function lwp_buy_url($post = null){
	//Get post object
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
	//Check if it is payable
	if(lwp_is_free(true, $post)){
		return;
	}
	return lwp_endpoint('buy').'&lwp-id='.$post_id;
}

/**
 * Return if current settings allows transfer or not
 * @global Literally_WordPress $lwp
 * @return boolean
 */
function lwp_can_transfer(){
	global $lwp;
	return (boolean)$lwp->option['transfer'];
}

/**
 * Returns transfer transaction link
 * @global Literally_WordPress $lwp
 * @global object $post
 * @param object $post
 * @return string
 */
function lwp_transafer_link($post = null){
	global $lwp;
	if(is_null($post)){
		$post = get_post($post);
	}else{
		global $post;
	}
	return lwp_endpoint('transfer&lwp-id='.$post->ID);
}

/**
 * Returns if subscription is enabled
 * @global Literally_WordPress $lwp 
 */
function lwp_is_subscribal(){
	global $lwp;
	return $lwp->subscription->is_enabled();
}

/**
 * Output link to pricelist.
 * @global Literally_WordPress $lwp
 * @param string $text
 * @param boolean $popup
 * @param int $width
 * @param int $height
 * @param boolean $show 
 * @param string $class
 * @return string
 */
function lwp_subscription_link($text = '', $popup = true, $width = 640, $height = 450, $show = true, $class = ''){
	global $lwp;
	if(lwp_is_subscribal()){
		if(empty($text)){
			$text = $lwp->_('Subscription list');
		}else{
			$text = esc_html($text);
		}
		$href = $lwp->subscription->get_subscription_archive().($popup ? '&popup=true' : '');
		$tag = '<a class="'.esc_attr($class).'" href="'.  esc_attr($href).'"';
		if($popup){
			$width = intval($width);
			$height = intval($height);
			$tag .= " onclick=\"if(window.open(this.href, 'lwpPricelist', 'width={$width}, height={$height}, menubar=no, toolbar=no, scrollbars=yes, location=no')) return false;\"";
		}
		$tag .= ">{$text}</a>";
		if($show){
			echo $tag;
		}
		return $tag;
	}
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
		$last = $end - strtotime(date_i18n('Y-m-d H:i:s'));
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
	$post = get_post($post);
	if(!$post || false === array_search($post->post_type, $lwp->post->post_types)){
		return "";
	}
	$timer = lwp_campaign_timer($post);
	if(!empty($timer)){
		$timer = '<p class="lwp-campaign-caption">'.sprintf($lwp->_('On SALE till %s'), lwp_campaign_end($post)).''.$timer;
	}
	$currency_code = $lwp->option['currency_code'];
	$currency_symbol = PayPal_Statics::currency_entity($currency_code);
	$class_name = 'lwp-form';
	if(lwp_on_sale($post)){
		//セール中の場合
		$original_price = lwp_original_price($post);
		$current_price = lwp_price($post);
		$price_tag = "<p class=\"lwp-price\"><small>({$currency_code})</small><del>{$currency_symbol} ".number_format($original_price)."</del><span class=\"price\">{$currency_symbol} ".number_format($current_price)."</span><span class=\"lwp-off\">".  lwp_discout_rate($post)."</span></p>";
		$class_name .= " onsale";
	}elseif(lwp_original_price() > 0){
		//売り物だけどセール中じゃない場合
		$price_tag = "<p class=\"lwp-price\"><small>({$currency_code})</small><span class=\"price\">{$currency_symbol} ".  number_format(lwp_price($post))."</span></p>";
	}else{
		//Free
		$price_tag = "";
	}
	if(is_user_logged_in()){
		$button = sprintf('<p class="lwp-button">%s</p>', 
					($btn_src ? lwp_buy_now($post, $btn_src) : lwp_buy_now($post)));
	}else{
		$button = "<p class=\"lwp-button\"><a class=\"button login\" href=\"".wp_login_url(lwp_endpoint('buy')."&lwp-id={$post->ID}")."\">".__("Log in")."</a>".str_replace("<a", "<a class=\"button\"", wp_register('', '', false))."</p>";
	}
	return <<<EOS
<!-- Literally WordPress {$lwp->version} -->
<div class="{$class_name}">
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
 * Return URL to LWP's endpoint considering SSL
 * 
 * @global Literally_WordPress $lwp
 * @param string $action Defautl 'buy'
 * @param boolean $sandbox
 * @return string 
 */
function lwp_endpoint($action = 'buy', $is_sanbdox = false){
	global $lwp;
	return $lwp->rewrite->endpoint($action, $is_sanbdox);
}

/**
 * 公開ページへのリンクをSSLでなくする
 * @param string $url
 * @return string
 */
function lwp_unsslize($url){
	$home = get_option('siteurl', '');
	if(false !== strpos($home, 'https://')){
		$url = str_replace('https://', 'http://', $url);
	}
	return $url;
}

/**
 * Returns refund account setting page url
 * @return string
 */
function lwp_refund_account_url(){
	return lwp_endpoint('refund-account');
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

/**
 * 購入履歴ページへのリンクを返す
 * @global Literally_WordPress $lwp
 * @return string
 */
function lwp_history_url(){
	global $lwp;
	if($lwp->option['mypage']){
		$url = get_page_link($lwp->option['mypage']);
		if(FORCE_SSL_ADMIN || FORCE_SSL_LOGIN){
			$url = $lwp->ssl($url);
		}
		return $url;
	}else{
		return admin_url('profile.php?page=lwp-history');
	}
}

/**
 * Returns whether if current post is free for subscription
 * @since 0.8.8
 * @global Literally_WordPress $lwp
 * @param int $post_id
 * @return boolean 
 */
function lwp_is_free_subscription($post_id = null){
	global $lwp;
	if(is_null($post_id)){
		$post_id = get_the_ID();
	}
	return (boolean)get_post_meta($post_id, $lwp->subscription->free_meta_key, true);
}


/**
 * Returns if promotable on current settig 
 * @since 0.9
 * @global Literally_WordPress $lwp
 * @return boolean
 */
function lwp_is_promotable(){
	global $lwp;
	return $lwp->reward->promotable;
}

/**
 * Print current reward
 * @global Literally_WordPress $lwp
 * @global object $post
 * @param object $post 
 */
function the_lwp_reward($post = null){
	global $lwp;
	if(is_null($post)){
		global $post;
	}
	$margin = $lwp->reward->get_current_promotion_margin($post);
	if(is_user_logged_in()){
		$margin *= $lwp->reward->get_user_coefficient(get_current_user_id());
	}
	$price = number_format(lwp_price($post) * $margin / 100);
	echo apply_filters('the_lwp_reward', $price.' ('.lwp_currency_code().')', $price);
}

/**
 * Print promotion link
 * @global Literally_WordPress $lwp
 * @global object $post
 * @param object $post 
 */
function the_lwp_promotion_link($post = null){
	global $lwp;
	if(is_null($post)){
		global $post;
	}else{
		$post = get_post($post);
	}
	if(is_user_logged_in()){
		echo $lwp->reward->get_promotion_link($post->ID, get_current_user_id());
	}else{
		echo get_permalink($post->ID);
	}
}

/**
 * Returns personal reward dashboard link
 * @return string
 */
function lwp_reward_link(){
	return admin_url('users.php?page=lwp-personal-reward');
}


/**
 * Returns if post has tikcets.
 * @global Literally_WordPress $lwp
 * @global object $post
 * @param object $post if not specified, use current post.
 * @return boolean 
 */
function lwp_has_ticket($post = null){
	global $lwp;
	if(is_null($post)){
		global $post;
	}
	return $lwp->event->has_tickets($post->ID);
}

/**
 * Returns if event is currently available
 * @global Literally_WordPress $lwp
 * @param object $post
 * @param int $time Timestamp. Default false(= Now)
 * @return boolean
 */
function lwp_is_event_available($post = null, $time = false){
	global $lwp;
	$post = get_post($post);
	$limit = get_post_meta($post->ID, $lwp->event->meta_selling_limit, true);
	if(!$limit || !preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $limit)){
		return false;
	}
	if(!$time){
		$time = current_time('timestamp');
	}elseif(!is_numeric($time)){
		$time = strtotime($time);
	}
	return (boolean)($time <= strtotime($limit.' 23:59:59'));
}

/**
 * Returns if user can wait for cancellation
 * @global Literally_WordPress $lwp
 * @param object $post
 * @return boolean
 */
function lwp_has_cancel_list($post = null){
	global $lwp;
	$post = get_post($post);
	return $lwp->event->has_cancel_list($post->ID);
}


/**
 * Return url of cancel list
 * @global Literally_WordPress $lwp
 * @param object $post
 * @return string
 */
function lwp_cancel_list_url($post = null){
	$post = get_post($post);
	return lwp_endpoint('ticket-awaiting').'&ticket_id='.$post->ID;
}

/**
 * Returns url of deregistere cancel list
 * @param object $post
 * @return string
 */
function lwp_cancel_list_dequeue_url($post = null, $user_id = false){
	$post = get_post($post);
	if(!$user_id){
		$user_id = get_current_user_id();
	}
	return wp_nonce_url(lwp_endpoint('ticket-awaiting-deregister').'&ticket_id='.$post->ID, 'lwp_deregistere_cancel_list_'.$user_id);
}

/**
 * Returns if user is on waiting list
 * @global wpdb $wpdb
 * @global Literally_WordPress $lwp
 * @param object $post
 * @param int $user_id
 * @return int|false Transaction ID
 */
function lwp_is_user_waiting($post = null, $user_id = false){
	global $wpdb, $lwp;
	$post = get_post($post);
	if(!$user_id){
		$user_id = get_current_user_id();
	}
	return $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$lwp->transaction} WHERE book_id = %d AND user_id = %d AND status = %s", $post->ID, $user_id, LWP_Payment_Status::WAITING_CANCELLATION));
}

/**
 * Returns user count
 * @global wpdb $wpdb
 * @global Literally_WordPress $lwp
 * @param object $post
 * @return int
 */
function lwp_waiting_user_count($post = null){
	global $wpdb, $lwp;
	$post = get_post($post);
	$sql = <<<EOS
		SELECT COUNT(DISTINCT user_id) FROM {$lwp->transaction}
		WHERE book_id = %d AND status = %s
EOS;
	return (int)$wpdb->get_var($wpdb->prepare($sql, $post->ID, LWP_Payment_Status::WAITING_CANCELLATION));
}

/**
 * Returns selling limit string
 * @global Literally_WordPress $lwp
 * @global object $post
 * @param string $format Default is WordPress default
 * @param object $post
 * @return string 
 */
function lwp_selling_limit($format = null, $post = null){
	global $lwp;
	if(is_null($post)){
		global $post;
	}else{
		$post = get_post($post);
	}
	if(is_null($format)){
		$format = get_option('date_format');
	}
	$limit = get_post_meta($post->ID, $lwp->event->meta_selling_limit, true);
	if(!$limit){
		return '';
	}
	$limit = (strtotime($limit) + (60 * 60 * 24) - 1);
	return date_i18n($format, $limit);
}

/**
 * Returns if current ticket is cancelable
 * @global Literally_WordPress $lwp
 * @global object $post
 * @param object $post
 * @return boolean
 */
function lwp_is_cancelable($post = null){
	global $lwp;
	if(is_null($post)){
		global $post;
	}else{
		$post = get_post($post);
	}
	$condition = $lwp->event->get_current_cancel_condition($post->ID);
	return (boolean)$condition;
}



/**
 * Returnd current tickets cancel ratio
 * @global Literally_WordPress $lwp
 * @global object $post
 * @param object $post
 * @return int 
 */
function lwp_current_cancel_ratio($post = null){
	global $lwp;
	if(is_null($post)){
		global $post;
	}else{
		$post = get_post($post);
	}
	$condition = $lwp->event->get_current_cancel_condition($post->ID);
	if(isset($condition['ratio'])){
		return $condition['ratio'];
	}else{
		return 0;
	}
}

/**
 * Returns if event has cancel condition
 * @global Literally_WordPress $lwp
 * @global object $post
 * @param object|int $post
 * @return boolean
 */
function lwp_has_cancel_condition($post = null){
	global $lwp;
	if(is_null($post)){
		global $post;
	}else{
		$post = get_post($post);
	}
	$conditions = get_post_meta($post->ID, $lwp->event->meta_cancel_limits, true);
	return !empty($conditions);
}

/**
 * Display list of cancel condition 
 * @global Literally_WordPress $lwp
 * @param array|string $args
 */
function lwp_list_cancel_condition($args = array()){
	global $lwp;
	$args = wp_parse_args($args, array(
		'callback' => '',
		'post' => get_the_ID(),
		'before' => sprintf('<table class="lwp-event-cancel-conditions"><caption>%s</caption><tbody>', $lwp->_('Cancel Condition')),
		'after' => '</tbody></table>',
		'order' => 'desc'
	));
	$limit = get_post_meta($args['post'], $lwp->event->meta_selling_limit, true).' 23:59:59';
	$conditions = get_post_meta($args['post'], $lwp->event->meta_cancel_limits, true);
	if(is_array($conditions)){
		usort($conditions, array($lwp->event, '_sort_condition'));
		if($args['order'] == 'asc'){
			rsort($conditions);
		}
	}
	$current = $lwp->event->get_current_cancel_condition($args['post']);
	if(!empty($conditions)){
		echo $args['before'];
		$counter = 0;
		foreach($conditions as $condition){
			$is_current = (isset($current['days']) && $current['days'] == $condition['days']);
			if(function_exists($args['callback'])){
				call_user_func_array($args['callback'], array($limit, $condition['days'], $condition['ratio'], count($conditions), $counter, $is_current));
			}else{
				_lwp_show_condition($limit, $condition['days'], $condition['ratio'], count($conditions), $counter, $is_current);
			}
			$counter++;
		}
		echo $args['after'];
	}
}

/**
 * Display tickets. Use inside loop
 * @global Literally_WordPress $lwp
 * @param string|array $args 
 */
function lwp_list_tickets($args = ''){
	global $lwp;
	$args = wp_parse_args($args, array(
		'post_id' => get_the_ID(),
		'callback' => '',
		'orderby' => 'date',
		'order' => 'desc',
		'wrap' => 'dl',
		'class' => ''
	));
	$query = array(
		'post_parent' => $args['post_id'],
		'post_type' => $lwp->event->post_type,
		'status' => 'publish',
		'posts_per_page' => -1,
		'orderby' => 'date'
	);
	global $post;
	$old_post = clone $post;
	$parent_id = get_the_ID();
	$new_query = new WP_Query($query);
	if($new_query->have_posts()){
		switch($args['wrap']){
			case 'ul':
			case 'ol':
			case 'div':
				printf('<%s class="lwp-ticket-list %s">', $args['wrap'], esc_attr($args['class']));
				$after = "</{$args['wrap']}>";
				break;
			case 'table':
				printf('<table class="lwp-ticket-list %s"><tbody>', $args['wrap'], esc_attr($args['class']));
				$after = '</tbody></table>';
				break;
			case 'dl':
				printf('<dl class="lwp-ticket-list %s">', esc_attr($args['class']));
				$after = '</dl>';
				break;
			default:
				$after = '';
				break;
		}
		while($new_query->have_posts()){
			$new_query->the_post();
			if(!empty($args['callback']) && function_exists($args['callback'])){
				call_user_func_array($args['callback'], array($parent_id));
			}else{
				_lwp_show_ticket($parent_id);
			}
		}
		echo $after;
		$post = $old_post;
		setup_postdata($old_post);
	}
}


/**
 * Returns ticket stock
 * @global Literally_WordPress $lwp
 * @global wpdb $wpdb
 * @global object $post
 * @param boolean $raw Set true if original stock value required.
 * @param int $post
 * @return int 
 */
function lwp_get_ticket_stock($raw = false, $post = null){
	global $lwp, $wpdb;
	if(is_null($post)){
		global $post;
	}else{
		$post = get_post($post);
	}
	$stock = get_post_meta($post->ID, $lwp->event->meta_stock, true);
	if(!$stock || $raw){
		return (int)$stock;
	}
	//Get bought ticket count
	$sold = lwp_get_ticket_sold($post);
	return $stock - $sold;
}

/**
 * Returns ticket sold. Use inside ticket loop
 * @global Literally_WordPress $lwp
 * @global wpdb $wpdb
 * @global object $post
 * @param mixed $post
 * @return int
 */
function lwp_get_ticket_sold($post = null){
	global $lwp, $wpdb;
	if(is_null($post)){
		global $post;
	}else{
		$post = get_post($post);
	}
	return (int)$wpdb->get_var($wpdb->prepare("SELECT SUM(num) FROM {$lwp->transaction} WHERE book_id = %d AND status = %s", $post->ID, LWP_Payment_Status::SUCCESS));
}

/**
 * Returns tickets consumed total. Use inside ticket loop
 * @global Literally_WordPress $lwp
 * @global wpdb $wpdb
 * @global ojbect $post
 * @param mixed $post
 * @return int 
 */
function lwp_get_ticket_consumed($post = null){
	global $lwp, $wpdb;
	if(is_null($post)){
		global $post;
	}else{
		$post = get_post($post);
	}
	return (int)$wpdb->get_var($wpdb->prepare("SELECT SUM(consumed) FROM {$lwp->transaction} WHERE book_id = %d AND status = %s", $post->ID, LWP_Payment_Status::SUCCESS));
}

/**
 * Show Event start date
 * @global Literally_WordPress $lwp
 * @global object $post
 * @param string $format
 * @param object $post
 * @param boolean $_end 
 * @return string|false 
 */
function lwp_event_starts($format = null, $post = null, $_end = false){
	global $lwp;
	if(is_null($format)){
		$format = get_option('date_format');
	}else{
		$format = (string)$format;
	}
	$post = get_post($post);
	$key = $_end ? $lwp->event->meta_end : $lwp->event->meta_start;
	$meta = get_post_meta($post->ID, $key, true);
	return $meta ? mysql2date($format, $meta) : false;
}

/**
 * Returns event end date if registered.
 * @param string $format Dateformat
 * @param object $post 
 * @return string|false
 */
function lwp_event_ends($format = null, $post = null){
	return lwp_event_starts($format, $post, true);
}

/**
 * Returns true if event is outdated.
 * @global Literally_WordPress $lwp
 * @global object $post
 * @param object $post
 * @return boolean 
 */
function lwp_is_outdated_event($post = null){
	global $lwp;
	if(is_null($post)){
		global $post;
	}else{
		$post = get_post($post);
	}
	$end = get_post_meta($post->ID, $lwp->event->meta_end, true);
	return $end && current_time('timestamp') > strtotime($end);
}

/**
 * Returns true if event is 1 day
 * @global Literally_WordPress $lwp
 * @global object $post
 * @param object $post
 * @return boolean 
 */
function lwp_is_oneday_event($post = null){
	global $lwp;
	if(is_null($post)){
		global $post;
	}else{
		$post = get_post($post);
	}
	$start = get_post_meta($post->ID, $lwp->event->meta_start, true);
	$end = get_post_meta($post->ID, $lwp->event->meta_end, true);
	if($start && $end){
		return (mysql2date('Y-m-d', $start) == mysql2date('Y-m-d', $end));
	}else{
		return false;
	}
}

/**
 * Displays tikcet sold count.
 * @param mixed $post 
 */
function lwp_the_ticket_sold($post = null){
	echo number_format_i18n(lwp_get_ticket_sold($post));
}

/**
 * Displays ticket stock. Use inside ticket loop
 * @param boolean $raw If set to true, displays original stock value.
 * @param object $post 
 */
function lwp_the_ticket_stock($raw = false, $post = null){
	$stock = lwp_get_ticket_stock($raw, $post);
	echo number_format_i18n($stock);
}

/**
 * Returns cancel url for ticket
 * @return string
 */
function lwp_cancel_url($post = null){
	if(is_null($post)){
		global $post;
	}else{
		$post = get_post($post);
	}
	return lwp_endpoint('ticket-cancel').'&lwp-event='.$post->ID;
}

/**
 * Returns ticket list
 * @global object $post
 * @param object $post
 * @return string 
 */
function lwp_ticket_url($post = null){
	if(is_null($post)){
		global $post;
	}else{
		$post = get_post($post);
	}
	return lwp_endpoint('ticket-list').'&lwp-event='.$post->ID;
}

/**
 * Returns form to consume ticket
 * @global object $post
 * @param int $user_id
 * @param string $post
 * @return string 
 */
function lwp_ticket_check_url($user_id = null, $post = null){
	if(is_null($post)){
		global $post;
	}else{
		$post = get_post($post);
	}
	if(is_null($user_id)){
		$user_id = get_current_user_id();
	}
	$user_login = get_userdata($user_id)->user_email;
	return lwp_endpoint('ticket-consume').'&lwp-event='.$post->ID.'&u='.  rawurlencode(base64_encode($user_login));
}

/**
 * Returns ticket price when user bought (use inside ticket loop)
 * @global wpdb $wpdb
 * @global Literally_WordPress $lwp
 * @global object $post
 * @param object $post
 * @param int $user_id
 * @return string 
 */
function lwp_ticket_bought_price($post = null, $user_id = null){
	global $wpdb, $lwp;
	if(is_null($post)){
		global $post;
	}else{
		$post = get_post($post);
	}
	if(is_null($user_id)){
		$user_id = get_current_user_id();
	}
	return $wpdb->get_var($wpdb->prepare("SELECT price FROM {$lwp->transaction} WHERE book_id = %d AND user_id = %d", $post->ID, $user_id));
}

/**
 * Displays ticket quantity
 * @global wpdb $wpdb
 * @global Literally_WordPress $lwp
 * @global type $post
 * @param type $post
 * @param type $user_id 
 */
function lwp_bought_num($post = null, $user_id = null){
	global $wpdb, $lwp;
	if(is_null($post)){
		global $post;
	}else{
		$post = get_post($post);
	}
	if(is_null($user_id)){
		$user_id = get_current_user_id();
	}
	
}

/**
 * Returns currently refundable price
 * @global wpdb $wpdb
 * @global Literally_WordPress $lwp
 * @param object|int $ticket
 * @return int 
 */
function lwp_ticket_refund_price($ticket){
	global $wpdb, $lwp;
	if(is_numeric($ticket)){
		$ticket = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$lwp->transaction} WHERE ID = %d", $ticket));
	}
	$ratio = lwp_current_cancel_ratio($wpdb->get_var($wpdb->prepare("SELECT post_parent FROM {$wpdb->posts} WHERE ID = %d", $ticket->book_id)));
	if(preg_match("/^[0-9]+%$/", $ratio)){
		$return = round($ticket->price * preg_replace("/[^0-9]/", '', $ratio) / 100);
	}elseif(preg_match("/^-[0-9]+$/", $ratio)){
		$return = $ticket->price - preg_replace("/[^0-9]/", '', $ratio);
	}else{
		$return = preg_replace("/[^0-9]/", "", $ratio);
	}
	return $return;
}

/**
 * Returns if current user is participating this event
 * @global Literally_WordPress $lwp
 * @global object $post
 * @param object $post
 * @return boolean 
 */
function lwp_is_participating($post = null){
	global $lwp;
	if(is_null($post)){
		global $post;
	}else{
		$post = get_post($post);
	}
	if(is_user_logged_in()){
		return $lwp->event->is_participating(get_current_user_id(), $post->ID);
	}else{
		return false;
	}
}

/**
 * Returns participants number of event
 * @global Literally_WordPress $lwp
 * @param object $post
 * @return int 
 */
function lwp_participants_number($post = null, $waiting = false){
	global $lwp, $wpdb;
	$post = get_post($post);
	$sql = <<<EOS
		SELECT COUNT(DISTINCT t.user_id) FROM {$lwp->transaction} AS t
		INNER JOIN {$wpdb->posts} AS p
		ON t.book_id = p.ID
		WHERE p.post_parent = %d AND t.status = %s
EOS;
	return (int)$wpdb->get_var($wpdb->prepare($sql, $post->ID, ($waiting ? LWP_Payment_Status::WAITING_CANCELLATION : LWP_Payment_Status::SUCCESS)));
}



/**
 * Display list of participants
 * @global Literally_WordPress $lwp
 * @global wpdb $wpdb
 * @param array $args 
 */
function lwp_list_participants($args = array()){
	global $lwp, $wpdb;
	$args = wp_parse_args($args, array(
		'post_id' => get_the_ID(),
		'callback' => '_lwp_list_participant',
		'per_page' => get_option('posts_per_page'),
		'page' => 1
	));
	$per_page = (int)$args['per_page'];
	$offset = intval(max(0, ($args['page'] - 1)) * $per_page);
	$sql = <<<EOS
		SELECT DISTINCT u.ID FROM {$wpdb->users} AS u
		INNER JOIN {$lwp->transaction} AS t
		ON u.ID = t.user_id
		INNER JOIN {$wpdb->posts} AS p
		ON t.book_id = p.ID
		WHERE p.post_parent = %d AND t.status = %s
		GROUP BY u.ID
		ORDER BY t.updated DESC
EOS;
	if($per_page){
		$sql .= " LIMIT {$offset}, {$per_page}";
	}
	$participants = $wpdb->get_results($wpdb->prepare($sql, $args['post_id'], LWP_Payment_Status::SUCCESS));
	foreach($participants as $user){
		if(function_exists($args['callback'])){
			call_user_func_array($args['callback'], array(get_userdata($user->ID), $args['post_id']));
		}
	}
}

/**
 * Returns token check url
 * @global object $post
 * @param object $post
 * @return string 
 */
function lwp_ticket_token_url($post = null){
	if(is_null($post)){
		global $post;
	}else{
		$post = get_post($post);
	}
	return lwp_endpoint('ticket-owner').'&event_id='.$post->ID;
}

/**
 * Show button to check token
 * @global Literally_WordPress $lwp
 * @global object $post
 * @param object $post 
 */
function lwp_token_chekcer($post = null){
	global $lwp;
	if(is_null($post)){
		global $post;
	}else{
		$post = get_post($post);
	}
	if(current_user_can('edit_others_posts') || get_current_user_id() == $post->post_author){
		echo '<a class="button" href="'.  lwp_ticket_token_url($post).'">'.$lwp->_('Check Token').'</a>';
	}
}

/**
 * Returns sarray with kip number for option tag
 * @param int $max
 * @return array
 */
function lwp_option_steps($max){
	$options = array();
	for($i = 1; $i <= $max; $i++){
		if($i <= 20){
			$options[] = $i;
		}elseif($i <= 100){
			if($i % 10 == 0){
				$options[] = $i;
			}
		}
	}
	return $options;
}