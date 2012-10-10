jQuery(document).ready(function($){
	//split JS
	for(prop in LWPDatePicker){
		var propToSplit = ['monthNames','dayNames','monthNamesShort','dayNamesMin','dayNamesShort'];
		if($.inArray(prop, propToSplit) > -1){
			LWPDatePicker[prop] = LWPDatePicker[prop].split(',');
		}
	}
	$(".date-picker").datetimepicker(LWPDatePicker);
});
