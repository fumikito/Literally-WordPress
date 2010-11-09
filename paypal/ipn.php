<?php
//リクエストがなかったら何もしない
if(empty($_REQUEST)){
	header("HTTP/1.0 404 Not Found");	
	die();
}

//WordPressを読み込み
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).DIRECTORY_SEPARATOR."wp-load.php";

//エンドポイントの設定
$endpoint = "https://www.paypal.com/cgi-bin/webscr";

//ログ書き込み用ファイルを設定
$path = $lwp->option["dir"].DS."log";

//ログデータのセットアップ
$time = date("Y-m-d H:i:s");
$log = <<<EOS


[{$time}]
------Request From IPN---------

EOS;

//リクエストを記録
ob_start();
var_dump($_REQUEST);
$log .= ob_get_contents();
ob_end_clean();

//ポストバック用のデータを構築
$post_data = "cmd=_notify-validate";
foreach($_REQUEST as $key => $value)
	$post_data .= "&{$key}=".rawurlencode(stripslashes($value));

//ラインを書く
$log  .= <<<EOS

------Result of Post Back------

EOS;


//cURLのセットアップ
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $endpoint);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

//ポストバックを送信
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$req = curl_exec($ch);
$err = curl_error($ch);

//データの検証
if(!empty($err)){
	//エラー
	$log .= $err;
}else{
	$log .= $req;
	//通信成功
	if(false !== strpos($req, "VERIFIED")){
		//商品IDを取得
		$book_id = str_replace($lwp->option["slug"]."-", "", $_REQUEST["item_number"]);
		//payer_emailからユーザーIDを取得
		require_once ABSPATH.WPINC."/registration.php";
		$user_id = email_exists($_REQUEST["payer_email"]);
		if(!$user_id){
			//取得できなければ、user_metaからの取得を試みる
			$usermeta = $wpdb->get_row($wpdb->prepare("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'paypal' AND meta_value = %s", $_REQUEST["payer_email"]));
			if($usermeta)
				$user_id = $usermeta->user_id;
			else
				$user_id = 0;
		}
		//該当するデータを取得する、アップデートの必要性をチェック
		$tran = $wpdb->get_row($wpdb->prepare("SELECT ID,status FROM {$lwp->transaction} WHERE transaction_key = %s", $_REQUEST["txn_id"]));
		//支払い状況に応じてステータスを変更
		$status = ($_REQUEST["payment_status"] == "Completed") ? "SUCCESS" : $_REQUEST["payment_status"];
		//該当データが存在する場合はステータスをアップデート
		if($tran){
			$wpdb->update(
				$lwp->transaction,
				array(
					"status" => $status,
					"updated" => date('Y-m-d H:i:s')
				),
				array("ID" => $tran->ID),
				array("%s", "%s"),
				array("%d")
			);
		}else{
			//データが存在しなければ、新規にトランザクションを登録
			$wpdb->insert(
				$lwp->transaction,
				array(
					"user_id" => $user_id,
					"book_id" => $book_id,
					"price" => $_REQUEST["mc_gross"],
					"status" => $status,
					"method" => "PAYPAL",
					"transaction_key" => $_REQUEST["txn_id"],
					"payer_mail" => $_REQUEST["payer_email"],
					"registered" => date('Y-m-d H:i:s'),
					"updated" => date('Y-m-d H:i:s')
				),
				array("%d", "%d", "%d", "%s", "%s", "%s", "%s", "%s", "%s")
			);
		}
	}
}


//ログ終了
$log .= <<<EOS

------IPN  ENDS --------------

EOS;

//ログがなかったら作る
if(!file_exists($path) && is_writable(dirname($path)))
	touch($path);
	
//ログを記録
if(is_writable($path))
	file_put_contents($path, $log, FILE_APPEND);