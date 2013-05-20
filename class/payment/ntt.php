<?php

class LWP_NTT extends LWP_Japanese_Payment{
	
	/**
	 * @var string
	 */
	public $shop_id = '';
	
	/**
	 * @var string
	 */
	public $access_key = '';
	
	/**
	 * 
	 * @param array $option
	 */
	public function set_option($option = array()){
		$option = shortcode_atts(array(
			'ntt_shop_id' => '',
			'ntt_access_key' => '',
			'ntt_sandbox' => true,
			'ntt_stealth' => false,
		), $option);
		$this->shop_id = (string)$option['ntt_shop_id'];
		$this->access_key = (string)$option['ntt_access_key'];
		$this->is_sandbox = (boolean)$option['ntt_sandbox'];
		$this->is_stealth = (boolean)$option['ntt_stealth'];
	}
	
	
	public function is_emoney_enabled(){
		
	}
	
	/**
	 * Returns if service is enabled
	 * @return boolean
	 */
	public function is_enabled(){
		return (boolean)(
				( $this->is_cc_enabled() || $this->is_cvs_enabled() || $this->is_emoney_enabled())
					&&
				(!empty($this->access_key) && !empty($this->shop_id) )
		);
	}
}