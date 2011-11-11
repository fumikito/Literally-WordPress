jQuery(document).ready(function($){
	$(".date-picker").datetimepicker({
		dateFormat: 'yy-mm-dd',
		timeFormat: 'hh:mm:ss',
		prevText: LWPDatePicker.prevText,
		nextText: LWPDatePicker.nextText,
		monthNames: LWPDatePicker.monthNames.split(','),
		monthNamesShort: LWPDatePicker.monthNamesShort.split(','),
		dayNames: LWPDatePicker.dayNames.split(','),
		dayNamesShort: LWPDatePicker.dayNamesShort.split(','),
		dayNamesMin: LWPDatePicker.dayNamesMin.split(','),
		weekHeader: LWPDatePicker.weekHeader,
		showMonthAfterYear: true,
		yearSuffix: '',
		timeOnlyTitle: LWPDatePicker.timeOnlyTitle,
		timeText: LWPDatePicker.timeText,
		hourText: LWPDatePicker.hourText,
		minuteText: LWPDatePicker.minuteText,
		secondText: LWPDatePicker.secondText,
		currentText: LWPDatePicker.currentText,
		closeText: LWPDatePicker.closeText
	});
});
