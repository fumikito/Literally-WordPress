<?php
//エラーハンドラーを読み込む
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'gmo_error_handler.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'gmo_jobcode.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'gmo_method.php';

/**
 * GMOのエンドポイントを返します
 */
class GMO_Endpoints{
	
	const ENTRY_TRAN = '/EntryTran.idPass';
	
	const EXEC_TRAN = '/ExecTran.idPass';
	
	const ALTER_TRAN = '/AlterTran.idPass';
	
	const TD_VERIFY = '/SecureTran.idPass';
	
	const CHANGE_TRAN = '/ChangeTran.idPass';
	
	const SAVE_CARD = '/SaveCard.idPass';
	
	const DELETE_CARD = '/DeleteCard.idPass';
	
	const SEARCH_CARD = '/SearchCard.idPass';
	
	const TRADED_CARD = '/TradedCard.idPass';
	
	const SAVE_MEMBER = '/SaveMember.idPass';
	
	const DELETE_MEMBER = '/DeleteMember.idPass';
	
	const SEARCH_MEMBER = '/SearchMember.idPass';
	
	const UPDATE_MEMBER = '/UpdateMember.idPass';
	
	const SEARCH_TRADE = '/SearchTrade.idPass';
	
	const ENTRY_TRAN_SUICA = '/EntryTranSuica.idPass';
	
	const EXEC_TRAN_SUICA = '/ExecTranSuica.idPass';
	
	const ENTRY_TRAN_EDY = '/EntryTranEdy.idPass';
	
	const EXEC_TRAN_EDY = '/ExecTranEdy.idPass';
	
	const ENTRY_TRAN_CVS = '/EntryTranCvs.idPass';
	
	const EXEC_TRAN_CVS = '/ExecTranCvs.idPass';
	
	const ENTRY_TRAN_PAYEASY = '/EntryTranPayEasy.idPass';
	
	const EXEC_TRAN_PAYEASY = '/ExecTranPayEasy.idPass';
	
	const ENTRY_TRAN_PAYPAL = '/EntryTranPaypal.idPass';
	
	const EXEC_TRAN_PAYPAL = '/ExecTranPaypal.idPass';
	
	const PAYPAL_START = '/PaypalStart.idPass';
	
	const CANCEL_TRAN_PAYPAL = '/CancelTranPaypal.idPass';
	
	const ENTRY_TRAN_WEBMONEY = '/EntryTranWebmoney.idPass';
	
	const EXEC_TRAN_WEBMONEY = '/ExecTranWebmoney.idPass';
	
	const WEBMONEY_START = '/WebmoneyStart.idPass';
	
	const ENTRY_TRAN_AU = '/EntryTranAu.idPass';
	
	const EXEC_TRAN_AU = '/ExecTranAu.idPass';
	
	const AU_START = '/AuStart.idPass';
	
	const AU_CANCEL_RETURN = '/AuCancelReturn.idPass';
	
	const AU_SALES = '/AuSales.idPass';
	
	const DELETE_AU_OPEN_I_D = '/DeleteAuOpenID.idPass';
	
	const ENTRY_TRAN_DOCOMO = '/EntryTranDocomo.idPass';
	
	const EXEC_TRAN_DOCOMO = '/ExecTranDocomo.idPass';
	
	const DOCOMO_START = '/DocomoStart.idPass';
	
	const DOCOMO_CANCEL_RETURN = '/DocomoCancelReturn.idPass';
	
	const DOCOMO_SALES = '/DocomoSales.idPass';
	//
	const SEARCH_TRADE_MULTI = '/SearchTradeMulti.idPass';
	
	/**
	 * Returns endpoint root
	 * @param boolean $is_sandbox Default true
	 * @return string
	 */
	static private function get_root($is_sandbox = true){
		return $is_sandbox ? 'https://pt01.mul-pay.jp/payment' : 'https://p01.mul-pay.jp/payment';
	}
	
	/**
	 * Return result of request.
	 * @param string $endpoint
	 * @param array $params
	 * @return array If success, param success will be true.
	 */
	private static function get_request($endpoint, $params = array()){
		$params = (array)$params;
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $endpoint,
			CURLOPT_CONNECTTIMEOUT => 90,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => self::build_post_fields($params),
		));
		$result = curl_exec($ch);
		curl_close($ch);
		if(false === $result){
			return array(
				'success' => false,
				'message' => array('決済サーバとの間で接続エラーが発生しました。')
			);
		}else{
			$params = array();
			parse_str($result, $params);
			if(isset($params['ErrInfo'])){
				return array(
					'success' => false,
					'message' => array_map(array(GMO_Error_Handler, 'get_error_message'), explode('|', $params['ErrInfo'])),
					'error_code' => explode('|', $params['ErrInfo'])
				);
			}else{
				return array(
					'success' => true,
					'message' => array(),
					'params' => $params
				);
			}
		}
	}
	
	/**
	 * Returns result of Credit card execution
	 * @param boolean $is_sandbox
	 * @param array $params
	 * @return array Array consists of success, message, params.
	 */
	public static function exec_tran_cc($is_sandbox, $params){
		$entry_params = self::get_specified_params(array('ShopID','ShopPass', 'OrderID', 'JobCd', 'Amount', 'Tax', 'TdFlag'), $params);
		if(!$entry_params){
			return self::get_wrong_params();
		}
		$entry_result_params = self::get_request(self::get_root($is_sandbox).self::ENTRY_TRAN, $entry_params);
		if(!$entry_result_params['success']){
			return $entry_result_params;
		}
		$exec_params = self::get_specified_params(array('OrderID', 'Method', 'PayTimes', 'CardNo', 'Expire', 'SecurityCode'), $params);
		if(!$exec_params){
			return self::get_wrong_params();
		}
		$exec_params = array_merge(array(
			'AccessID' => $entry_result_params['params']['AccessID'],
			'AccessPass' => $entry_result_params['params']['AccessPass']
		), $exec_params);
		$exec_result_params = self::get_request(self::get_root($is_sandbox).self::EXEC_TRAN, $exec_params);
		if(!$exec_result_params['success']){
			return $exec_result_params;
		}
		//Merge Result
		return array(
			'success' => true,
			'message' => array(),
			'params' => array_merge($entry_result_params['params'], $exec_result_params['params'])
		);
	}
	
	/**
	 * Returns result of CVS transaction
	 * @param boolean $is_sandbox
	 * @param array $params
	 * @return array
	 */
	public static function exec_tran_cvs($is_sandbox, $params){
		$entry_params = self::get_specified_params(array('ShopID','ShopPass', 'OrderID', 'Amount', 'Tax'), $params);
		if(!$entry_params){
			return self::get_wrong_params();
		}
		$entry_result_params = self::get_request(self::get_root($is_sandbox).self::ENTRY_TRAN_CVS, $entry_params);
		if(!$entry_result_params['success']){
			return $entry_result_params;
		}
		$exec_params = self::get_specified_params(array(
			'OrderID', 'Convenience', 'CustomerName', 'CustomerKana',
			'TelNo', 'MailAddress', 'ReserveNo', 'MemberNo', 'PaymentTermDay',
			'RegisterDisp1', 'ReceiptsDisp1', 'ReceiptsDisp11', 'ReceiptsDisp12', 'ReceiptsDisp13' 
		), $params);
		if(!$exec_params){
			return self::get_wrong_params();
		}
		$exec_params = array_merge(array(
			'AccessID' => $entry_result_params['params']['AccessID'],
			'AccessPass' => $entry_result_params['params']['AccessPass']
		), $exec_params);
		$exec_result_params = self::get_request(self::get_root($is_sandbox).self::EXEC_TRAN_CVS, $exec_params);
		if(!$exec_result_params['success']){
			return $exec_result_params;
		}
		//Merge Result
		return array(
			'success' => true,
			'message' => array(),
			'params' => array_merge($entry_result_params['params'], $exec_result_params['params'])
		);
	}
	
	/**
	 * Returns result of PayEasy transaction
	 * @param boolean $is_sandbox
	 * @param array $params
	 * @return array
	 */
	public static function exec_tran_payeasy($is_sandbox, $params){
		$entry_params = self::get_specified_params(array('ShopID','ShopPass', 'OrderID', 'Amount', 'Tax'), $params);
		if(!$entry_params){
			return self::get_wrong_params();
		}
		$entry_result_params = self::get_request(self::get_root($is_sandbox).self::ENTRY_TRAN_PAYEASY, $entry_params);
		if(!$entry_result_params['success']){
			return $entry_result_params;
		}
		$exec_params = self::get_specified_params(array(
			'OrderID', 'Convenience', 'CustomerName', 'CustomerKana',
			'TelNo', 'MailAddress', 'PaymentTermDay',
			'RegisterDisp1', 'ReceiptsDisp1', 'ReceiptsDisp11', 'ReceiptsDisp12', 'ReceiptsDisp13' 
		), $params);
		if(!$exec_params){
			return self::get_wrong_params();
		}
		$exec_params = array_merge(array(
			'AccessID' => $entry_result_params['params']['AccessID'],
			'AccessPass' => $entry_result_params['params']['AccessPass']
		), $exec_params);
		$exec_result_params = self::get_request(self::get_root($is_sandbox).self::EXEC_TRAN_PAYEASY, $exec_params);
		if(!$exec_result_params['success']){
			return $exec_result_params;
		}
		//Merge Result
		return array(
			'success' => true,
			'message' => array(),
			'params' => array_merge($entry_result_params['params'], $exec_result_params['params'])
		);
	}
	
	/**
	 * Convert specified key
	 * @param array|string $keys
	 * @param array $store
	 * @return array|false
	 */
	private static function get_specified_params($keys, $store){
		$param = array();
		$keys = (array)$keys;
		foreach($keys as $key){
			if(!isset($store[$key])){
				return false;
				break;
			}else{
				$param[$key] = $store[$key];
			}
		}
		return $param;
	}
	
	/**
	 * Returns array for wrong parameters
	 * @return array
	 */
	private static function get_wrong_params(){
		return array(
			'success' => false,
			'message' => array('与えられたパラメータが不足しています')
		);
	}
	
	/**
	 * Returns array to query string
	 * @param array $params
	 * @return string
	 */
	private static function build_post_fields($params){
		$query = array();
		foreach($params as $key => $value){
			$query[] = $key.'='.urlencode(trim($value));
		}
		return implode('&', $query);
	}
}