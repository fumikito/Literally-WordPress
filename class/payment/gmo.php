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
	
}