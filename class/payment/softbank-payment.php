<?php
class LWP_SB_Payment extends Literally_WordPress_Common {
	
	public $creditcard = false;
	
	public $webcvs = false;
	
	public $payeasy = false;
	
	public $is_sandbox = true;
	
	public $prefix = '';
	
	private $_marchant_id = '';
	
	private $_service_id = '';
	
	private $_hash_key = '';
	
	private $_sandbox_marchant_id = '62022';
	
	private $_sandbox_service_id = '001';
	
	private $_sandbox_hash_key = 'd7b6640cc5d286cbceab99b53bf74870cf6bce9c';
	
	public function set_option($option = array()) {
		$option = shortcode_atts(array(
			'sb_creditcard' => false,
			'sb_webcvs' => false,
			'sb_payeasy' => false,
			'sb_sandbox' => true,
			'sb_marchant_id' => '',
			'sb_service_id' => '',
			'sb_hash_key' => '',
			'sb_prefix' => ''
		), $option);
		$this->creditcard = (boolean)$option['sb_creditcard'];
		$this->webcvs = (boolean)$option['sb_webcvs'];
		$this->payeasy = (boolean)$option['sb_payeasy'];
		$this->is_sandbox = (boolean)$option['sb_sandbox'];
		$this->_marchant_id = (string)$option['sb_marchant_id'];
		$this->_service_id = (string)$option['sb_service_id'];
		$this->_hash_key = (string)$option['sb_hash_key'];
		$this->prefix = (string)$option['sb_prefix'];
	}
	
	/**
	 * Returns Marchand ID
	 * @param boolean $force To get original string, pass TRUE.
	 * @return string
	 */
	public function marchant_id($force = false){
		return $this->is_sandbox && !$force ? $this->_sandbox_marchant_id : $this->_marchant_id;
	}
	
	/**
	 * Returns Service ID
	 * @param boolean $force To get original string, pass TRUE.
	 * @return string
	 */
	public function service_id($force = false){
		return $this->is_sandbox && !$force ? $this->_sandbox_service_id : $this->_service_id;
	}
	
	/**
	 * Returns Hash key
	 * @param boolean $force To get original string, pass TRUE.
	 * @return string
	 */
	public function hash_key($force = false){
		return $this->is_sandbox && !$force ? $this->_sandbox_hash_key : $this->_hash_key;
	}
	
	public function is_enabled() {
		return (boolean)($this->creditcard || $this->payeasy || $this->webcvs);
	}
}