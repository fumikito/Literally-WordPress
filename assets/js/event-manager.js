jQuery(document).ready(function($){
	$('#lwp-csv-output-form').submit(function(e){
		$(this).find('input[name=status]').val($(this).next().find('select[name=status]').val());
		$(this).find('input[name=ticket]').val($(this).next().find('select[name=ticket]').val());
	});
});