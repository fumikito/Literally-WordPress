<?php
/**
 * Static Class for datepicker strings. 
 * @since 0.9
 */
class LWP_Datepicker_Helper{
	
	/**
	 * Returns translated montnames array
	 * @return array
	 */
	public static function get_month_names(){
		$month_names = array();
		for($i = 1; $i <= 12; $i++){
			$month = gmmktime(0, 0, 0, $i, 1, 2011);
			$month_names[] = date_i18n('F', $month);
		}
		return $month_names;
	}
	
	/**
	 * Returns translated month short names array
	 * @return array
	 */
	public static function get_month_short_names(){
		$month_names_short = array();
		for($i = 1; $i <= 12; $i++){
			$month = gmmktime(0, 0, 0, $i, 1, 2011);
			$month_names_short[] = date_i18n('M', $month);
		}
		return $month_names_short;
	}
	
	/**
	 * Returns translated day names array
	 * @return array 
	 */
	public static function get_day_names(){
		return array(__('Sunday'), __('Monday'), __('Tuesday'), __('Wednesday'), __('Thursday'), __('Friday'), __('Saturday'));
	}
	
	/**
	 * Returns transalated day short names array
	 * @return array
	 */
	public static function get_day_short_names(){
		return array(__('Sun'), __('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat'));
	}
	
	/**
	 * Returns typical config array for datepciker
	 * @global Literally_WordPress $lwp
	 * @return array
	 */
	public static function get_config_array(){
		global $lwp;
		return array(
			'dateFormat' => 'yy-mm-dd',
			'timeFormat' => 'hh:mm:ss',
			'closeText' => $lwp->_('Close'),
			'prevText' => $lwp->_('Prev'),
			'nextText' => $lwp->_('Next'),
			'monthNames' => implode(',', self::get_month_names()),
			'monthNamesShort' => implode(',', self::get_month_short_names()),
			'dayNames' => implode(',', self::get_day_names()),
			'dayNamesShort' => implode(',', self::get_day_short_names()),
			'dayNamesMin' => implode(',', self::get_day_short_names()),
			'weekHeader' => $lwp->_('Week'),
			'timeOnlyTitle' => $lwp->_('Time'),
			'timeText' => $lwp->_('Time'),
			'hourText' => $lwp->_('Hour'),
			'minuteText' => $lwp->_('Minute'),
			'secondText' => $lwp->_('Second'),
			'currentText' => $lwp->_('Now'),
			'showMonthAfterYear' => (boolean)(get_locale() == 'ja'),
			'yearSuffix' => (get_locale() == 'ja') ? '年' : '',
			'changeYear' => true,
			'alertOldStart' => $lwp->_('Start date must be earlier than end date.')
		);
	}
}