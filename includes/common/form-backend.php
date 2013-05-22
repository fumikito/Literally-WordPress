<?php

/**
 * Abstract for backend endpoint
 * 
 * This class handles all backend request.
 * Typically, PayPal sends IPN request which requires to post-back response.
 * To handle request like that, this class has handle_* methods.
 * All methods must be proteced.
 * 
 * @since 0.9.3.1
 * 
 */
abstract class LWP_Form_Backend extends LWP_Form_Template{
	
	
	
	
	
	
	
	
	/**
	 * Handle request from PayPal IPN
	 */
	protected function handle_paypal_ipn(){
		PayPal_Statics::handle_ipn();
		die();
	}
	
	
	
	
	/**
	 * Handle request from NTT Chocom server
	 * @global Literally_WordPress $lwp
	 */
	protected function handle_ntt_smarttrade(){
		global $lwp;
		$lwp->ntt->parse_request();
		die();
	}
	
	
	
	
	/**
	 * Handle request from GMO
	 * @global Literally_WordPress $lwp
	 * @return
	 */
	protected function handle_gmo_payment(){
		global $lwp;
		echo intval(!(boolean)$lwp->gmo->parse_notification($_POST));
		die();
	}
	
	
	
	
	/**
	 * Parse XML Data from Softbank Payment
	 * @global Literally_WordPress $lwp
	 */
	protected function handle_sb_payment($is_sandbox){
		global $lwp, $wpdb;
		if($_SERVER["REQUEST_METHOD"] != "POST"){
			$this->kill_anonymous_user();
			if(!current_user_can('manage_options')){
				$this->kill($this->_('You have no permission to see this URL.'), 403);
			}
			//Request
			$response = false;
			if(isset($_GET['sb_transaction'], $_GET['sb_status'])){
				$xml = mb_convert_encoding($lwp->softbank->make_pseudo_request($_GET['sb_transaction'], $_GET['sb_status']), 'utf-8', 'sjis-win');
				$response_xml = simplexml_load_string($xml);
				$response = '';
				if($response_xml->res_err_code){
					$response .= "Error: \n".mb_convert_encoding(base64_decode(strval($response_xml->res_err_code)), 'utf-8', 'sjis-win')."\n\n----------------\n\n";
				}
				$response .= $this->_("Parsed Data: \n").var_export($response_xml, true)."\n\n----------------\n\n";
				$response .= "XML: \n". $xml;
			}
			//Transaction to be change
			$sql = <<<EOS
				SELECT * FROM {$lwp->transaction}
				WHERE method IN (%s, %s) AND status = %s
EOS;
			$this->show_form('sb-check', array(
				'transactions' => $wpdb->get_results($wpdb->prepare($sql, LWP_Payment_Methods::SOFTBANK_PAYEASY, LWP_Payment_Methods::SOFTBANK_WEB_CVS, LWP_Payment_Status::START)),
				'action' => lwp_endpoint('sb-payment'),
				'message' =>  $lwp->softbank->is_sandbox
					? '<p class="message">'.sprintf($this->_('This page confirm whether your endpoint <code>%s</code> works in order. Please select transaction to be finished.'), lwp_endpoint('sb-payment')).'</p>'
					: '<p class="message error">'.$this->_('This is not sandbox environment. Are you sure to change status?').'</p>',
				'link' => admin_url('admin.php?page=lwp-setting&view=payment#setting-softbank'),
				'response' => $response
			), false);
			exit;
		}else{
			$xml_data = file_get_contents('php://input');
			header('Content-Type: text/xml; charset=Shift_JIS');
			echo $lwp->softbank->parse_request($xml_data);
		}
		die();
	}
}