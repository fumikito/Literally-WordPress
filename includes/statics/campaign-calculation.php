<?php
/**
 * Static class for campaign calcuration
 */
class LWP_Campaign_Calculation{
	
	/**
	 * Special price
	 */
	const SPECIAL_PRICE = 'SPECIAL_PRICE';
	
	/**
	 * use as percent
	 */
	const PERCENT = 'PERCENT';
	
	/**
	 * Discount specified price
	 */
	const DISCOUNT = 'DISCOUNT';
	
	/**
	 * Returns all methods
	 * @return string
	 */
	public static function get_all(){
		return array(
			self::SPECIAL_PRICE,
			self::DISCOUNT,
			self::PERCENT
		);
	}
	
	/**
	 * 
	 * @global type $lwp
	 */
	private function _(){
		global $lwp;
		$lwp->_('SPECIAL_PRICE');
		$lwp->_('PERCENT');
		$lwp->_('DISCOUNT');
	}
	
}