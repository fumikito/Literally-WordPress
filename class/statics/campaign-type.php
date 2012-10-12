<?php
/**
 * Static class for Campaign type string
 */
class LWP_Campaign_Type{
	
	/**
	 * Set of speccified items
	 */
	const SET = 'ITEM_SET';
	
	/**
	 * Particular item
	 */
	const SINGULAR = 'SINGULAR';
	
	/**
	 * Returns all types
	 * @return array
	 */
	public static function get_all(){
		return array(
			self::SINGULAR,
			self::SET
		);
	}
	
	private function _(){
		global $lwp;
		$lwp->_('ITEM_SET');
		$lwp->_('SINGULAR');
		$lwp->_('post_type');
		$lwp->_('Taxonomy');
	}
}