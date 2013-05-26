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
	
	
	
	public function __construct($option = array()) {
		parent::__construct($option);
		add_action('wp_ajax_chocom_generate_transaction', array($this, 'ajax_chocom'));
	}
	
	
	
	/**
	 * Endpoint for Chocom transaction
	 * 
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 */
	public function ajax_chocom(){
		global $lwp, $wpdb;
		$json = array(
			'success' => false,
			'message' => $this->_('Sorry, but failed to make transaction. Please try again later.'),
		);
		if(isset($_REQUEST['_wpnonce'], $_REQUEST['product'], $_REQUEST['quantity']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'chocom_generate_transaction_'.get_current_user_id())){
			// Check product
			$products = array();
			foreach((array)$_REQUEST['product'] as $post_id){
				$products[] = get_post($post_id);
			}
			if(empty($products)){
				$json['message'] = $this->_('No item is selectd.');
			}else{
				// Test payment method
				$method = LWP_Payment_Methods::get_current_method();
				if(!LWP_Payment_Methods::test($method, $products) || !LWP_Payment_Methods::is_chocom($method)){
					$json['message'] = $this->_('You specified wrong payment method.');
				}else{
					// Test Quantity
					$flg = true;
					$quantities = array();
					foreach($products as $product){
						if(!$this->test_current_quantity($product)){
							$flg = false;
						}else{
							$quantities[$product->ID] = $this->get_current_quantity($product);
						}
					}
					if(!$flg){
						$json['message'] = $this->_('Item quantity is wrong. Please go back and select item quantity.');
					}else{
						//Everything is OK. Let's make transaction.
						$form = $lwp->ntt->get_form(get_current_user_id(), $method, $products, $quantities);
						if($form){
							$json['success'] = true;
							$json['message'] = $form;
						}
					}
				}
			}
		}else{
			$json['message'] = $this->_('Wrong request. you might be logged out on session. Please try again later.');
		}
		header('Content-Type: application/json');
		echo json_encode($json);
		exit;
	}
	
	
	
	/**
	 * Output file
	 * @global Literally_WordPress $lwp
	 * @global boolean $is_IE
	 * @param type $is_sandbox 
	 */
	protected function handle_file(){
		global $lwp;
		//Get file object
		$file = isset($_REQUEST['lwp_file']) ? $lwp->post->get_files(null, $_REQUEST["lwp_file"]) : null;
		if(!$file){
			$this->kill($this->_('Specified file does not exist.'), 404);
		}
		//Check user permission
		if(!lwp_user_can_download($file, get_current_user_id())){
			$this->kill($this->_('You have no permission to access this file.'), 403);
		}
		//Try Print file
		$lwp->post->print_file($file);
	}
	
	
	/**
	 * Handle request from PayPal IPN
	 */
	protected function handle_paypal_ipn(){
		PayPal_Statics::handle_ipn();
		die();
	}
	
	
	
	
	/**
	 * Handle request from NTT Chocom server
	 * 
	 * @global Literally_WordPress $lwp
	 */
	protected function handle_chocom_cc(){
		global $lwp;
		if(!$lwp->ntt->check_ip() || !$lwp->ntt->parse_request(LWP_Payment_Methods::NTT_CC)){
			$this->kill($this->_('You have no permission to see this page.'), 403);
		}
	}
	
	/**
	 * Handle request from NTT DATA emoney
	 * 
	 * @global Literally_WordPress $lwp
	 */
	protected function handle_chocom_emoney(){
		global $lwp;
		if(!$lwp->ntt->check_ip() || !$lwp->ntt->parse_request(LWP_Payment_Methods::NTT_EMONEY)){
			$this->kill($this->_('You have no permission to see this page.'), 403);
		}
	}
	
	/**
	 * Handle request from NTT DATA CVS
	 * 
	 * @global Literally_WordPress $lwp
	 */
	protected function handle_chocom_cvs(){
		global $lwp;
		if(!$lwp->ntt->check_ip() || !$lwp->ntt->parse_request(LWP_Payment_Methods::NTT_CVS)){
			$this->kill($this->_('You have no permission to see this page.'), 403);
		}
	}
	
	
	
	/**
	 * Handle request from NTT CVS finished.
	 * 
	 * @global Literally_WordPress $lwp
	 */
	protected function handle_chocom_cvs_complete(){
		global $lwp;
		if(!$lwp->ntt->check_ip() || !$lwp->ntt->parse_request(LWP_Payment_Methods::NTT_CVS.'_COMPLETE')){
			$this->kill($this->_('You have no permission to see this page.'), 403);
		}
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
	protected function handle_sb_payment(){
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