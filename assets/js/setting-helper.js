jQuery(document).ready(function($){
	//Tabs
	$('#lwp-tab').tabs();
	$('#lwp-tab ul:first-child a').click(function(){
		action = $(this).parents('form').attr('action');
		if(action.match(/#setting/)){
			action = action.replace(/#setting.*$/, '');
		}
		action += $(this).attr('href');
		$(this).parents('form').attr('action', action);
	});
	//Timepicker
	for(prop in LWPDatePicker){
		var propToSplit = ['monthNames','dayNames','monthNamesShort','dayNamesMin','dayNamesShort'];
		if($.inArray(prop, propToSplit) > -1){
			LWPDatePicker[prop] = LWPDatePicker[prop].split(',');
		}
	}
	$('.hour-picker').timepicker(LWPDatePicker);
	//Dialog
	loading = false;
	$('#lwp-pa-contact').dialog({
		 autoOpen: false,
		 modal: true,
		 width: 550,
		 buttons:{
			Cancel: function(){
				$(this).dialog('close');
			},
			OK: function(){
				if(!loading){
					loading = true;
					form = $(this);
					form.find('.ui-state-error').removeClass('ui-state-error');
					$('.validate-tips + p').remove();
					$(this).find('form').ajaxSubmit(function(result){
						loading = false;
						if(result.success){
							$('.ui-state-error').removeClass('ui-state-error');
							$('.validate-tips')
								.after('<p class="ui-state-highlight" style="display: none;">' + result.message + '</p>');
							form.find('textarea,input[type=text], input[type=email]').val('');
							form.find('input:checked').each(function(i, e){
								e.checked = false;
							});
							$('.validate-tips + p')
								.fadeIn('slow', function(){
									p = $(this);
									setTimeout(function(){
										p.fadeOut('slow', function(){
											$(this).remove();
											$('#lwp-pa-contact').dialog('close');
										});
									}, 5000);
								});
						}else{
							message = [];
							$.each(result.errors, function(index, error){
								if(error.selector){
									$('#lwp-pa-contact').find(error.selector).addClass('ui-state-error');
								}
								message.push(error.message);
							});
							$('.validate-tips').after('<p class="ui-state-error" style="display:none;">' + message.join('<br />') + '</p>');
							$('.validate-tips + p').fadeIn('slow');
						}
					});
				}
			}
		 }
	 });
	 $('.ui-dialog-buttonpane button').each(function(index, elt){
		 $(elt).removeClass();
		 switch(index){
			case 0:
				$(elt).addClass('button').html(LWPDatePicker.btnCancel);
				 break;
			case 1:
				$(elt).addClass('button-primary').html(LWPDatePicker.btnSubmit);
				break;
		 }
	 });
	 $('#contact-opener').click(function(e){
		 e.preventDefault();
		 $('#lwp-pa-contact').dialog('open');
	 });
});