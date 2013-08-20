<?php
/**
 * Class to treat LWP's capabilities
 * 
 * @since 0.9.3
 */
class LWP_Capabilities{
	
	const SEE_TRANSACTION = 'see_transaction';
	
	const SEE_SUMMARY = 'see_summary';
	
	const DOWNLOAD_TRANSACTION_CSV = 'download_transaction_csv';
	
	const DOWNLOAD_EVENT_CSV = 'download_event_csv';
	
	/**
	 * Returns default capability
	 * 
	 * @param string $capability
	 * @return string
	 */
	private function get_default_cap($capability){
		switch($capability){
			case self::DOWNLOAD_TRANSACTION_CSV:
			case self::DOWNLOAD_EVENT_CSV:
				return 'edit_others_posts';
				break;
			default:
				return 'manage_options';
				break;
		}
	}
	
	/**
	 * Returns if specified user can
	 * 
	 * @param int $user_id
	 * @param string $capability
	 * @return boolean
	 */
	public function can($user_id, $capability){
		$default_cap = $this->get_default_cap($capability);
		$arguments = func_get_args();
		$third_arg = isset($arguments[2]) ? $arguments[2] : null;
		return apply_filters('lwp_can_'.$capability, user_can($user_id, $default_cap), $user_id, $third_arg);
	}
	
	/**
	 * Returns if current user can
	 * @param string $capability
	 * @return boolean
	 */
	public function current_user_can($capability){
		$arguments = func_get_args();
		if(empty($arguments)){
			return false;
		}
		return call_user_func_array(array($this, 'can'), array_merge(array(get_current_user_id()), $arguments));
	}
}