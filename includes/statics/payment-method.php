<?php
/**
 * Static class which has names of Literally Wordpres's Payment Methods.
 *
 * @package literally worrdprss
 * @since 0.8.6
 */
class LWP_Payment_Methods {
	/**
	 * Name of payment method for paypal.
	 */
	const PAYPAL = 'PAYPAL';
	
	/**
	 * Name of payment method for in App purchase
	 */
	const APPLE = 'APPLE';
	
	/**
	 * Name of payment method for in Android
	 */
	const ANDROID = 'ANDROID';
	
	/**
	 * Name of payment method for Softbank Payment's credit card
	 */
	const SOFTBANK_CC = 'SOFTBANK_CC';
	
	/**
	 * Name of payment method for Softbank Payment's PayEasy
	 */
	const SOFTBANK_PAYEASY = 'SOFTBANK_PAYEASY';
	
	/**
	 * Name of payment method for Softbank Payment's Web CVS
	 */
	const SOFTBANK_WEB_CVS = 'SOFTBANK_WEB_CVS';
	
	/**
	 * Name of payment method for GMO Payment Gateway's credit card
	 */
	const GMO_CC = 'GMO_CC';
	
	/**
	 * Name of payment method for Softbank Payment's PayEasy
	 */
	const GMO_PAYEASY = 'GMO_PAYEASY';
	
	/**
	 * Name of payment method for Softbank Payment's Web CVS
	 */
	const GMO_WEB_CVS = 'GMO_WEB_CVS';
	
	/**
	 * Name of payment method for NTT SmartTrade's Chocom e-money
	 */
	const NTT_EMONEY = 'NTT_EMONEY';
	
	/**
	 * Name of Chocom credit card
	 */
	const NTT_CC = 'NTT_CC';
	
	/**
	 * Name of NTT CVS
	 */
	const NTT_CVS = 'NTT_CVS';
	
	/**
	 * Name of payment method for free campaign.
	 */
	const CAMPAIGN = 'CAMPAIGN';
	
	/**
	 * Name of payment method for present.
	 */
	const PRESENT = 'present';
	
	/**
	 * Name for Payment method for transafer.
	 */
	const TRANSFER = 'TRANSFER';
	
	/**
	 * Returns all payment method.
	 * @param boolean $include_admin_method
	 * @return array
	 */
	public static function get_all_methods($include_admin_method = false){
		$methods =  array(
			self::PAYPAL,
			self::CAMPAIGN,
			self::PRESENT,
			self::TRANSFER,
			self::APPLE,
			self::ANDROID,
			self::SOFTBANK_CC,
			self::SOFTBANK_PAYEASY,
			self::SOFTBANK_WEB_CVS,
			self::GMO_CC,
			self::NTT_EMONEY,
			self::NTT_CC,
			self::NTT_CVS,
		);
		return $methods;
	}
	
	/**
	 * Place holder for gettext
	 * @global Literally_WordPress $lwp
	 * @param string $text 
	 */
	private function _($text){
		global $lwp;
		$lwp->_('PAYPAL');
		$lwp->_('CAMPAIGN');
		$lwp->_('present');
		$lwp->_('TRANSFER');
		$lwp->_('APPLE');
		$lwp->_('ANDROID');
		$lwp->_('SOFTBANK_CC');
		$lwp->_('SOFTBANK_PAYEASY');
		$lwp->_('SOFTBANK_WEB_CVS');
		$lwp->_('GMO_CC');
		$lwp->_('GMO_PAYEASY');
		$lwp->_('GMO_WEB_CVS');
		$lwp->_('NTT_EMONEY');
		$lwp->_('NTT_CC');
		$lwp->_('NTT_CVS');
	}
	
	/**
	 * Returns transfer method
	 * @return array
	 */
	public static function get_transfer_methods(){
		return array(
			self::GMO_PAYEASY,
			self::GMO_WEB_CVS,
			self::SOFTBANK_PAYEASY,
			self::SOFTBANK_WEB_CVS,
			self::NTT_CVS,
			self::TRANSFER
		);
	}
	
	/**
	 * Returns if method is transfer
	 * @param string $method
	 * @return boolean
	 */
	public static function is_transfer($method){
		return false !== array_search($method, self::get_transfer_methods());
	}
	
	/**
	 * Returns form elements
	 * 
	 * @param array $posts
	 * @return array
	 */
	public static function get_form_elements($posts){
		global $lwp;
		$methods = self::get_contextual_methods($posts);
		if(empty($methods)){
			return array();
		}
		$forms = array();
		foreach($methods as $method){
			$form = array(
				'slug' => self::lower($method),
				'name' => $lwp->_($method),
				'icon' => self::get_icon_slug($method),
				'stealth' => self::get_stealth_status($method),
				'sandbox' => self::is_sandbox($method),
				'cvs' => self::get_available_cvs($method),
				'cc' => self::get_available_cards($method),
				'description' => self::get_description($method),
				'vendor' => self::get_vendor_name($method),
				'label' => self::get_label($method),
				'title' => self::get_title($method),
				'selectable' => self::is_selectable($method, $posts),
			);
			$forms[] = $form;
		}
		return apply_filters('lwp_payment_method_on_form', $forms, $posts, get_current_user_id());
	}
	
	/**
	 * Get currently specified method
	 * 
	 * @return string
	 */
	public static function get_current_method(){
		return isset($_REQUEST['lwp-method']) ? self::hungalinize($_REQUEST['lwp-method']) : '';
	}
	
	/**
	 * Returns available method
	 * 
	 * @global Literally_WordPress $lwp
	 * @param array $posts
	 * @return array
	 */
	public static function get_contextual_methods($posts){
		global $lwp;
		$context = LWP_Context::get($posts);
		$methods = array();
		switch($context){
			case LWP_Context::EVENT:
				$methods = array_merge(
						self::get_emoney(),
						self::get_cc(),
						self::get_offline_payment()
					);
					break;
			case LWP_Context::SUBSCRIPTION:
				$methods = self::get_addaptive_payment();
				break;
			case LWP_Context::DIGITAL:
				$methods = array_merge(
						self::get_emoney(),
						self::get_cc()
				);
				break;
		}
		$normalized = array();
		foreach($methods as $method){
			if(false === array_search($method, $normalized)){
				$flg = false;
				switch($method){
					case self::PAYPAL:
						$flg = $lwp->is_paypal_enbaled();
						break;
					case self::GMO_CC:
						$flg = $lwp->gmo->is_cc_enabled();
						break;
					case self::GMO_PAYEASY:
						$flg = $lwp->gmo->payeasy;
						break;
					case self::GMO_WEB_CVS:
						$flg = $lwp->gmo->is_cvs_enabled();
						break;
					case self::SOFTBANK_CC:
						$flg = $lwp->softbank->is_cc_enabled();
						break;
					case self::SOFTBANK_PAYEASY:
						$flg = $lwp->softbank->payeasy;
						break;
					case self::SOFTBANK_WEB_CVS:
						$flg = $lwp->softbank->is_cvs_enabled();
						break;
					case self::NTT_EMONEY:
						$flg = $lwp->ntt->is_emoney_enabled();
						break;
					case self::NTT_CC:
						$flg = $lwp->ntt->is_cc_enabled();
						break;
					case self::NTT_CVS:
						$flg = $lwp->ntt->is_cvs_enabled();
						break;
					case self::TRANSFER:
						$flg = $lwp->notifier->is_enabled();
						break;
				}
				if($flg){
					$normalized[] = $method;
				}
			}
		}
		return $normalized;
	}
	
	/**
	 * Return if specified method is chocom
	 * 
	 * @param method $method
	 * @return boolean
	 */
	public static function is_chocom($method){
		return (false !== strpos(self::hungalinize($method), 'NTT_'));
	}
	
	/**
	 * Test specified method is valid for transaction.
	 * 
	 * @param string $method
	 * @param array $posts
	 * @return boolean|false
	 */
	public static function test($method, $posts = array()){
		$method = self::hungalinize($method);
		$availables = self::get_contextual_methods($posts);
		if(false === array_search($method, $availables)){
			return false;
		}
		switch($method){
			case self::GMO_PAYEASY:
			case self::GMO_WEB_CVS:
			case self::SOFTBANK_PAYEASY:
			case self::SOFTBANK_WEB_CVS:
				break;
		}
		return $method;
	}
	
	/**
	 * Return hungalian-style string
	 * @param type $string
	 * @return type
	 */
	private static function hungalinize($string){
		return str_replace('-', '_', strtoupper((string)$string));
	}

	/**
	 * Return hyphationed string
	 * @param string $string
	 * @return string
	 */
	private static function hyphenate($string){
		return str_replace('_', '-', strtolower((string)$string));
	}

	/**
	 * Returns e-money like payment methods
	 * @return array
	 */
	public static function get_emoney(){
		return array(
			self::PAYPAL,
			self::NTT_EMONEY
		);
	}
	
	/**
	 * Returns payment method of cc
	 * @return array
	 */
	public static function get_cc(){
		return array(
			self::GMO_CC,
			self::SOFTBANK_CC,
			self::NTT_CC,
		);
	}
	
	/**
	 * Returns payment method of offline
	 * @return array
	 */
	public static function get_offline_payment(){
		return array(
			self::GMO_WEB_CVS,
			self::GMO_PAYEASY,
			self::SOFTBANK_PAYEASY,
			self::SOFTBANK_WEB_CVS,
			self::NTT_CVS,
			self::TRANSFER
		);
	}
	
	/**
	 * Returns addaptive payment for subscription
	 * @return array
	 */
	public static function get_addaptive_payment(){
		return array(
			self::PAYPAL,
			self::GMO_CC,
			self::SOFTBANK_CC
		);
	}
	
	/**
	 * Returns icon string
	 * 
	 * @param string $method
	 * @return string
	 */
	private static function get_icon_slug($method){
		if(false !== strpos($method, '_CC')){
			return 'creditcard';
		}elseif(false !== strpos($method, 'CVS')){
			return 'cvs';
		}elseif(false !== strpos($method, 'PAYEASY')){
			return 'payeasy';
		}else{
			return self::lower($method);
		}
	}
	
	/**
	 * Stealth mode
	 * @global Literally_WordPress $lwp
	 * @param string $method
	 * @return boolean
	 */
	private static function get_stealth_status($method){
		global $lwp;
		if($method == LWP_Payment_Methods::PAYPAL){
			return $lwp->paypal_is_stealth();
		}elseif(false !== strpos($method, 'GMO_')){
			return $lwp->gmo->is_stealth;
		}elseif(false !== strpos($method, 'SOFTBANK_')){
			return $lwp->softbank->is_stealth;
		}elseif(false !== strpos($method, 'NTT_')){
			return $lwp->ntt->is_stealth;
		}else{
			return false;
		}
	}
	
	/**
	 * Stealth mode
	 * @global Literally_WordPress $lwp
	 * @param string $method
	 * @return boolean
	 */
	private static function is_sandbox($method){
		global $lwp;
		if(false !== strpos($method, 'GMO_')){
			return $lwp->gmo->is_sandbox;
		}elseif(false !== strpos($method, 'SOFTBANK_')){
			return $lwp->softbank->is_sandbox;
		}elseif(false !== strpos($method, 'NTT_')){
			return $lwp->ntt->is_sandbox;
		}elseif($method == self::PAYPAL){
			return $lwp->option['sandbox'];
		}else{
			return false;
		}
	}
	
	/**
	 * Returns available CVS for method
	 * 
	 * @global Literally_WordPress $lwp
	 * @param string $method
	 * @return array
	 */
	private static function get_available_cvs($method){
		global $lwp;
		switch($method){
			case self::GMO_WEB_CVS:
				return $lwp->gmo->get_available_cvs();
				break;
			case self::SOFTBANK_WEB_CVS:
				return $lwp->softbank->get_available_cvs();
				break;
			case self::NTT_CVS:
				return $lwp->ntt->get_available_cvs();
				break;
			default:
				return array();
				break;
		}
	}
	
	/**
	 * Returns available Credit cards for method
	 * 
	 * @global Literally_WordPress $lwp
	 * @param string $method
	 * @return array
	 */
	private static function get_available_cards($method){
		global $lwp;
		switch($method){
			case self::PAYPAL:
				return PayPal_Statics::get_available_cards($lwp->option['country_code']);
				break;
			case self::GMO_CC:
				return $lwp->gmo->get_available_cards();
				break;
			case self::SOFTBANK_CC:
				return $lwp->softbank->get_available_cards();
				break;
			case self::NTT_CC:
				return $lwp->ntt->get_available_cards();
				break;
			default:
				return array();
				break;
		}
	}
	
	
	/**
	 * Get payment method label
	 * 
	 * @global Literally_WordPress $lwp
	 * @param string $method
	 * @return string
	 */
	public static function get_label($method){
		global $lwp;
		switch($method){
			case self::GMO_CC:
			case self::SOFTBANK_CC:
			case self::NTT_CC:
				return $lwp->_('Credit Card');
				break;
			case self::GMO_PAYEASY:
			case self::SOFTBANK_PAYEASY:
				return $lwp->_('PayEasy');
				break;
			case self::GMO_WEB_CVS:
			case self::SOFTBANK_WEB_CVS:
			case self::NTT_CVS:
				return $lwp->_($method);
				break;
			case self::NTT_EMONEY:
				return 'ちょコムeマネー';
				break;
			case self::PAYPAL:
			case self::TRANSFER:
				return $lwp->_($method);
				break;
		}
	}
	
	/**
	 * Get title string for payment method
	 * 
	 * @global Literally_WordPress $lwp
	 * @param string $method
	 * @return string
	 */
	public static function get_title($method){
		global $lwp;
		switch($method){
			case self::GMO_CC:
			case self::SOFTBANK_CC:
			case self::NTT_CC:
				return $lwp->_('You can pay with credit card in secure.');
				break;
			case self::GMO_PAYEASY:
			case self::SOFTBANK_PAYEASY:
				return $lwp->_('You can pay from your bank account via PayEasy.');
				break;
			case self::SOFTBANK_WEB_CVS:
			case self::GMO_WEB_CVS:
			case self::NTT_CVS:
				return $lwp->_('You can pay at CVS below.');
				break;
			case self::PAYPAL:
				return $lwp->_("You can pay with PayPal account or credit cards.");
				break;
			case self::NTT_EMONEY:
				return 'NTTスマートトレードの提供する電子マネーサービス';
				break;
			case self::TRANSFER:
				return $lwp->_('You can pay through specified bank account. The account will be displayed on next page.');
				break;
		}
	}
	
	/**
	 * Get detailed description for paypale
	 * @global Literally_WordPress $lwp
	 * @param type $method
	 * @return type
	 */
	public static function get_description($method){
		global $lwp;
		switch($method){
			case self::PAYPAL:
				return $lwp->_('Clicking \'Next\', You will be redirect to PayPal web site. Logging in or register to PayPal, you will be redirected to this site again. And then, by confirming payment on this site, your transaction will be complete.');
				break;
			case self::GMO_CC:
			case self::SOFTBANK_CC:
				return $lwp->_('Clicking \'Next\', you will enter credit card infomation form.');
				break;
			case self::GMO_WEB_CVS:
			case self::GMO_PAYEASY:
			case self::SOFTBANK_WEB_CVS:
			case self::SOFTBANK_PAYEASY:
			case self::NTT_CVS:
				return $lwp->_('This is offline payment. To finish transaction, you should follow the instruction on next step.');
				break;
			case self::NTT_CC:
				return $lwp->ntt->get_desc('credit');
				break;
			case self::NTT_EMONEY:
	return $lwp->ntt->get_desc('link');
				break;
			case self::TRANSFER:
				return $lwp->_('Transaction will not have been complete, unless you will send deposit to the specified bank account. This means you can\'t get contents immediately.');;
				break;
		}
	}
	
	/**
	 * Returns payment method's vendor name
	 * 
	 * @global Literally_WordPress $lwp
	 * @param string $method
	 * @return string
	 */
	public static function get_vendor_name($method){
		global $lwp;
		switch($method){
			case self::PAYPAL:
				return 'PayPal';
				break;
			case self::GMO_CC:
			case self::GMO_PAYEASY:
			case self::GMO_WEB_CVS:
				return $lwp->gmo->vendor_name();
				break;
			case self::SOFTBANK_CC:
			case self::SOFTBANK_PAYEASY:
			case self::SOFTBANK_WEB_CVS:
				return $lwp->softbank->vendor_name();
				break;
			case self::NTT_EMONEY:
			case self::NTT_CC:
			case self::NTT_CVS:
				return $lwp->ntt->vendor_name();
				break;
			default:
				return '';
				break;
		}
	}
	
	/**
	 * Check if this post type is selectable
	 * 
	 * @param string $method
	 * @param array $posts
	 * @return boolean
	 */
	private static function is_selectable($method, $posts){
		global $lwp;
		switch($method){
			case self::GMO_PAYEASY:
			case self::GMO_WEB_CVS:
				break;
			case self::NTT_CVS:
				$closest = apply_filters('lwp_closest_limit', $lwp->ntt->get_closest_limit($posts), $method);
				return !($closest > 0 && strtotime(date_i18n('Y-m-d 23:59:59')) >= $closest);
				break;
			case self::SOFTBANK_PAYEASY:
			case self::SOFTBANK_WEB_CVS:
				return true;
				break;
			default:
				return true;
				break;
		}
	}
	
	/**
	 * Make form strings to lower
	 * @param string $method_name
	 * @return string
	 */
	public static function lower($method_name){
		return str_replace('_', '-', strtolower($method_name));
	}
}