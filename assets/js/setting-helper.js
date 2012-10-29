jQuery(document).ready(function($){
	$('#lwp-tab').tabs();
	//split JS
	for(prop in LWPDatePicker){
		var propToSplit = ['monthNames','dayNames','monthNamesShort','dayNamesMin','dayNamesShort'];
		if($.inArray(prop, propToSplit) > -1){
			LWPDatePicker[prop] = LWPDatePicker[prop].split(',');
		}
	}
	$('.hour-picker').timepicker(LWPDatePicker);
});