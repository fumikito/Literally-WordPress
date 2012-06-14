jQuery(document).ready(function($){
	//Show Datepicker
	for(prop in LWP){
		var propToSplit = ['monthNames','dayNames','monthNamesShort','dayNamesMin','dayNamesShort'];
		if(propToSplit.indexOf(prop) > -1){
			LWP[prop] = LWP[prop].split(',');
		}
	}
	$('.date-picker').datepicker(LWP);
	
	$('#lwp-csv-output-form').submit(function(e){
		$(this).find('input[name=status]').val($(this).next().find('select[name=status]').val());
		$(this).find('input[name=post_type]').val($(this).next().find('select[name=post_types]').val());
		$(this).find('input[name=from]').val($(this).next().find('input[name=from]').val());
		$(this).find('input[name=to]').val($(this).next().find('input[name=to]').val());
	});
});