jQuery(document).ready(function($){
	$('#lwp-csv-output-form').submit(function(e){
		$(this).find('input[name=status]').val($(this).next().find('select[name=status]').val());
		$(this).find('input[name=ticket]').val($(this).next().find('select[name=ticket]').val());
		$(this).find('input[name=from]').val($(this).next().find('input[name=from]').val());
		$(this).find('input[name=to]').val($(this).next().find('input[name=to]').val());
	});
	var propToSplit = ['monthNames','dayNames','monthNamesShort','dayNamesMin','dayNamesShort'];
	for(prop in LWP){
		if($.inArray(prop, propToSplit) > -1){
			LWP[prop] = LWP[prop].split(',');
		}
	}
	$('.date-picker').datepicker(LWP);
});