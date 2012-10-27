<?php
class LWP_GMO extends LWP_Japanese_Payment {
	
	/**
	 * Shop Password
	 * @var string
	 */
	public $shop_id = '';
	
	/**
	 * Shop Password
	 * @var string
	 */
	public $shop_pass = '';
	
	/**
	 * Setup Option
	 * @param type $option
	 */
	public function set_option($option = array()) {
		$option = shortcode_atts(array(
			'gmo_shop_id' => '',
			'gmo_shop_pass' => '',
			'gmo_sandbox' => true,
			'gmo_creditcard' => array()
		), $option);
		$this->shop_id = (string)$option['gmo_shop_id'];
		$this->shop_pass = (string)$option['gmo_shop_pass'];
		$this->is_sandbox = (boolean)$option['gmo_sandbox'];
		foreach($this->_creditcard as $cc => $bool){
			$this->_creditcard[$cc] = (false !== array_search($cc, (array)$option['gmo_creditcard']));
		}
	}
	
	/**
	 * Do on construct
	 */
	public function on_construct() {
		if($this->is_enabled()){
			set_include_path(get_include_path() . PATH_SEPARATOR . $this->dir.'libs'.DIRECTORY_SEPARATOR.'gmo');
		}
	}
	
	/**
	 * Do transaction and returns transaciton ID
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param int $user_id
	 * @param string $item_name
	 * @param int $post_id
	 * @param int $price
	 * @param int $quantity
	 * @param string $cc_number
	 * @param string $cc_sec
	 * @param string $expiration YYYYMM
	 * @return int
	 */
	public function do_credit_authorization($user_id, $item_name, $post_id, $price, $quantity, $cc_number, $cc_sec, $expiration){
		global $wpdb, $lwp;
		$order_id = $this->generate_order_id();
		$now = gmdate('Y-m-d H:i:s');
		//Include Required files.
		require_once('com/gmo_pg/client/common/Method.php');
		require_once('com/gmo_pg/client/common/JobCode.php');
		require_once('com/gmo_pg/client/input/EntryTranInput.php');
		require_once('com/gmo_pg/client/input/ExecTranInput.php');
		require_once('com/gmo_pg/client/input/EntryExecTranInput.php');
		require_once('com/gmo_pg/client/tran/EntryExecTran.php');

		//取引登録時に必要なパラメータ
		$entryInput = new EntryTranInput();
		$entryInput->setShopId( $this->shop_id );
		$entryInput->setShopPass( $this->shop_pass );
		$entryInput->setJobCd(JOBCODE_CAPTURE);
		$entryInput->setOrderId($order_id);
		//$entryInput->setItemCode( $_POST['ItemCode'] );
		$entryInput->setAmount($price);
		$entryInput->setTax(0);
		$entryInput->setTdFlag(0);
		//$entryInput->setTdTenantName( $_POST['TdTenantName']);
		//決済実行のパラメータ
		$execInput = new ExecTranInput();
		$execInput->setOrderId($order_id);
		$execInput->setMethod( METHOD_IKKATU );
		$execInput->setCardNo($cc_number);
		$execInput->setExpire(substr($expiration, 2, 4));
		$execInput->setSecurityCode($cc_sec);
		//取引登録＋決済実行の入力パラメータクラスをインスタンス化します
		$input = new EntryExecTranInput();
		$input->setEntryTranInput( $entryInput );
		$input->setExecTranInput( $execInput );
		//API通信クラスをインスタンス化します
		$exe = new EntryExecTran();
		//パラメータオブジェクトを引数に、実行メソッドを呼びます。
		//正常に終了した場合、結果オブジェクトが返るはずです。
		$output = $exe->exec( $input );
		//実行後、その結果を確認します。
		if( $exe->isExceptionOccured() ){
			//取引の処理そのものがうまくいかない（通信エラー等）場合、例外が発生します。
			$this->last_error = $this->_('Connection Error');
			return 0;
		}else{
			//例外が発生していない場合、出力パラメータオブジェクトが戻ります。
			if( $output->isErrorOccurred() ){
				//出力パラメータにエラーコードが含まれていないか、チェックしています。
				$msg = array();
				foreach(array_merge($output->getExecErrList(), $output->getEntryErrList()) as $errInfo){
					/* @var $errInfo ErrHolder */
					$msg[] = ((defined('WP_DEBUG') && WP_DEBUG) ? '['.$errInfo->getErrInfo().']' : '').
							$this->get_error_message($errInfo->getErrInfo());
				}
				$this->last_error = implode(' ', $msg);
				return 0;
			}else{
				//例外発生せず、エラーの戻りもなく、3Dセキュアフラグもオフであるので、実行結果を表示します。
				$wpdb->insert($lwp->transaction, array(
					"user_id" => $user_id,
					"book_id" => $post_id,
					"price" => $price,
					"status" => LWP_Payment_Status::SUCCESS,
					"method" => LWP_Payment_Methods::GMO_CC,
					"transaction_key" => $order_id,
					'transaction_id' => $output->getTranId(),
					'payer_mail' => $output->getApprovalNo(),
					"registered" => $now,
					"updated" => $now,
					'misc' => serialize(array(
						'access_id' => $output->getAccessId(),
						'access_pass' => $output->getAccessPass()
					))
				), array('%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ));
				return $wpdb->insert_id;
			}
		}
	
	}
	
	/**
	 * Generate uniq transaction ID
	 * @return string
	 */
	private function generate_order_id(){
		return uniqid(sprintf('%s-%02d-', preg_replace('/[^0-9]/', '', $this->shop_id), rand(0,99)), false);
	}
	
	/**
	 * Returns Errorm message
	 * @param string $code
	 * @return string
	 */
	private function get_error_message($code){
		switch($code){
			case 'E00000000':
				return '特になし';
				break;
			case 'E01010001':
				return 'ショップIDが指定されていません。';
				break;
			case 'E01020001':
				return 'ショップパスワードが指定されていません。';
				break;
			case 'E01030002':
				return '指定されたIDとパスワードのショップが存在しません。';
				break;
			case 'E01040001':
				return 'オーダーIDが指定されていません。';
				break;
			case 'E01040003':
				return 'オーダーIDが最大文字数を超えています。';
				break;
			case 'E01040010':
				return '既にオーダーIDが存在しています。';
				break;
			case 'E01040013':
				return 'オーダーIDに不正な文字が存在します。';
				break;
			case 'E01050001':
				return '処理区分が指定されていません。';
				break;
			case 'E01050002':
				return '指定された処理区分は定義されていません。';
				break;
			case 'E01050004':
				return '指定した処理区分の処理は実行出来ません。';
				break;
			case 'E01060001':
				return '利用金額が指定されていません。';
				break;
			case 'E01060005':
				return '利用金額が最大桁数を超えています。';
				break;
			case 'E01060006':
				return '利用金額に数字以外の文字が含まれています。';
				break;
			case 'E01070005':
				return '税送料が最大桁数を超えています。';
				break;
			case 'E01070006':
				return '税送料に数字以外の文字が含まれています。';
				break;
			case 'E01080007':
				return '3Dセキュア使用フラグに0,1以外の値が指定されています。';
				break;
			case 'E01090001':
				return '取引IDが指定されていません。';
				break;
			case 'E01100001':
				return '取引パスワードが指定されていません。';
				break;
			case 'E01110002':
				return '指定されたIDとパスワードの取引が存在しません。';
				break;
			case 'E01120008':
				return 'カード種別の書式が誤っています。';
				break;
			case 'E01130002':
				return '指定されたカード略称が存在しません。';
				break;
			case 'E01140007':
				return '対応支払方法に0,1以外の値が指定されています。';
				break;
			case 'E01140003':
				return '対応支払方法が最大文字数を超えています。';
				break;
			case 'E01150007':
				return '対応分割回数に0,1以外の値が指定されています。';
				break;
			case 'E01160007':
				return '対応ボーナス分割回数に0,1以外の値が指定されています。';
				break;
			case 'E01170001':
				return 'カード番号が指定されていません。';
				break;
			case 'E01170003':
				return 'カード番号が最大文字数を超えています。';
				break;
			case 'E01170006':
				return 'カード番号に数字以外の文字が含まれています。';
				break;
			case 'E01170011':
				return 'カード番号が10桁～16桁の範囲ではありません。';
				break;
			case 'E01180001':
				return '有効期限が指定されていません。';
				break;
			case 'E01180003':
				return '有効期限が4桁ではありません。';
				break;
			case 'E01180006':
				return '有効期限に数字以外の文字が含まれています。';
				break;
			case 'E01190001':
				return 'サイトIDが指定されていません。';
				break;
			case 'E01200001':
				return 'サイトパスワードが指定されていません。';
				break;
			case 'E01210002':
				return '指定されたIDとパスワードのサイトが存在しません。';
				break;
			case 'E01220001':
				return '会員IDが指定されていません。';
				break;
			case 'E01230001':
				return 'カード登録連番が指定されていません。';
				break;
			case 'E01230006':
				return 'カード登録連番に数字以外の文字が含まれています。';
				break;
			case 'E01230009':
				return 'カード登録連番が最大登録可能数を超えています。';
				break;
			case 'E01240002':
				return '指定されたサイトIDと会員ID、カード連番のカードが存在しません。';
				break;
			case 'E01250010':
				return 'カードパスワードが一致しません。';
				break;
			case 'E01260001':
				return '支払方法が指定されていません。';
				break;
			case 'E01250002':
				return '指定された支払方法が存在しません。';
				break;
			case 'E01260010':
				return '指定された支払方法はご利用できません。';
				break;
			case 'E01270001':
				return '支払回数が指定されていません。';
				break;
			case 'E01270005':
				return '支払回数が1～2桁ではありません。';
				break;
			case 'E01270006':
				return '支払回数の数字以外の文字が含まれています。';
				break;
			case 'E01270010':
				return '指定された支払回数はご利用できません。';
				break;
			case 'E01280012':
				return '加盟店URLの値が最大バイト数を超えています。';
				break;
			case 'E01290001':
				return 'HTTP_ACCEPTが指定されていません。';
				break;
			case 'E01300001':
				return 'HTTP_USER_AGENTが指定されていません。';
				break;
			case 'E01310001':
				return '使用端末が指定されていません。';
				break;
			case 'E01310007':
				return '使用端末に0,1以外の値が指定されています。';
				break;
			case 'E01320012':
				return '加盟店自由項目1の値が最大バイト数を超えています。';
				break;
			case 'E01330012':
				return '加盟店自由項目2の値が最大バイト数を超えています。';
				break;
			case 'E01340012':
				return '加盟店自由項目3の値が最大バイト数を超えています。';
				break;
			case 'E01350001':
				return 'MDが指定されていません。';
				break;
			case 'E01360001':
				return 'PaREsが指定されていません。';
				break;
			case 'E01370012':
				return '3Dセキュア表示店舗名の値が最大バイト数を超えています。';
				break;
			case 'E01380007':
				return '決済方法フラグに0,1以外の値が指定されています。';
				break;
			case 'E01390002':
				return '指定されたサイトIDと会員IDの組み合わせが存在しません。';
				break;
			case 'E01390010':
				return '指定されたサイトIDと会員IDの組み合わせは既に存在しています。';
				break;
			case 'E11010001':
				return 'この取引は既に決済が終了しています。';
				break;
			case 'E11010002':
				return 'この取引は決済が終了していませんので、変更する事が出来ません。';
				break;
			case 'E11010003':
				return 'この取引は指定処理区分処理を行う事が出来ません。';
				break;
			case 'E21010001':
			case 'E21020001':
				return '3Dセキュア認証に失敗しました。もう一度、購入画面からやり直して下さい。';
				break;
			case 'E21020002':
				return '3Dセキュア認証がキャンセルされました。もう一度、購入画面からやり直して下さい。';
				break;
			case 'E41170002':
				return '入力されたカードの会社には対応していません。別のカード番号を入力して下さい。';
				break;
			case 'E41170099':
				return 'カード番号に誤りがあります。再度確認して入力して下さい。';
				break;
			case 'E90010001':
				return '現在処理を行っているのでもうしばらくお待ち下さい。';
				break;
			case 'E61010001':
			case 'E61010002':
			case 'E61010003':
			case 'E91019999':
			case 'E91029999':
			case 'E91099999':
			case '42C010000':
			case '42C030000':
			case '42C120000':
			case '42C130000':
			case '42C140000':
			case '42C150000':
			case '42C500000':
			case '42C510000':
			case '42C530000':
			case '42C540000':
			case '42C550000':
			case '42C560000':
			case '42C570000':
			case '42C580000':
			case '42C600000':
			case '42C700000':
			case '42C710000':
			case '42C720000':
			case '42C730000':
			case '42C740000':
			case '42C750000':
			case '42C760000':
			case '42C770000':
			case '42C780000':
				return '決済処理に失敗しました。申し訳ございませんが、しばらくした後にもう一度購入画面からやり直してください。';
				break;
			case '42G020000':
			case '42G040000':
				return 'カード残高が不足しているために、決済が完了できませんでした。';
				break;
			case '42G030000':
			case '42G050000':
				return 'カード限度額を超えているために、決済が完了できませんでした。';
				break;
			case '42G420000':
				return '暗証番号が誤っていた為に、決済を完了する事が出来ませんでした。';
				break;
			case '42G540000':
				return 'このカードでは取引をする事が出来ません。';
				break;
			case '42G550000':
				return 'カード限度額を超えているために、決済が完了できませんでした。';
				break;
			case '42G650000':
				return 'カード番号に誤りがあるために、決済を完了できませんでした。';
				break;
			case '42G670000':
				return '商品コードに誤りがあるために、決済を完了できませんでした。';
				break;
			case '42G680000':
				return '金額に誤りがあるために、決済を完了できませんでした。';
				break;
			case '42G690000':
				return '税送料に誤りがあるために、決済を完了できませんでした。';
				break;
			case '42G700000':
				return 'ボーナス回数に誤りがあるために、決済を完了できませんでした。';
				break;
			case '42G710000':
				return 'ボーナス月に誤りがあるために、決済を完了できませんでした。';
				break;
			case '42G720000':
				return 'ボーナス額に誤りがあるために、決済を完了できませんでした。';
				break;
			case '42G730000':
				return '支払開始月に誤りがあるために、決済を完了できませんでした。';
				break;
			case '42G740000':
				return '分割回数に誤りがあるために、決済を完了できませんでした。';
				break;
			case '42G750000':
				return '分割金額に誤りがあるために、決済を完了できませんでした。';
				break;
			case '42G760000':
				return '初回金額に誤りがあるために、決済を完了できませんでした。';
				break;
			case '42G770000':
				return '業務区分に誤りがあるために、決済を完了できませんでした。';
				break;
			case '42G780000':
				return '支払区分に誤りがあるために、決済を完了できませんでした。';
				break;
			case '42G790000':
				return '照会区分に誤りがあるために、決済を完了できませんでした。';
				break;
			case '42G800000':
				return '取消区分に誤りがあるために、決済を完了できませんでした。';
				break;
			case '42G810000':
				return '取消取扱区分に誤りがあるために、決済を完了できませんでした。';
				break;
			case '42G830000':
				return '有効期限に誤りがあるために、決済を完了できませんでした。';
				break;
			case '42G950000':
			case '42G120000':
			case '42G220000':
			case '42G300000':
			case '42G560000':
			case '42G600000':
			case '42G610000':
			case '42G960000':
			case '42G970000':
			case '42G980000':
			case '42G990000':
				return 'このカードでは取引をする事が出来ません。';
				break;
			default:
				return '原因不明';
				break;
		}
	}
}