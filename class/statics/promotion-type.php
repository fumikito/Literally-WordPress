<?php
/**
 * Static class for Promotion log
 * @since 0.9
 */
class LWP_Promotion_TYPE{
	/**
	 * Promoted by user 
	 */
	const PROMOTION = 'PROMOTION';
	
	/**
	 * Sold by author himself 
	 */
	const SELL = 'SELL';
	
	/**
	 * Returns all type name
	 * @return array
	 */
	public static function get_all_type(){
		return array(
			self::PROMOTION,
			self::SELL
		);
	}
	
	/**
	 * For gettext scraping 
	 * @global Literally_WordPress $lwp
	 */
	private function _(){
		global $lwp;
		$lwp->_('PROMOTION');
		$lwp->_('SELL');
	}
}