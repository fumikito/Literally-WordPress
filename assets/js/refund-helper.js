jQuery(document).ready(function($){
	$('a.about-refund-link').click(function(e){
		e.preventDefault();
		$(LWPRefund.message).dialog({
			modal: true,
			resizable: false,
			buttons: {
                Ok: function() {
                    $( this ).dialog( "close" );
                }
            }
		});
	});
	$('a.fix-refund-price').click(function(e){
		if(!confirm(LWPRefund.confirm)){
			e.preventDefault();
		}
	});
	$('a.done-refund-price').click(function(e){
		if(!confirm(LWPRefund.done)){
			e.preventDefault();
		}
	});
	$('a.refund-account-request').click(function(e){
		if(!confirm(LWPRefund.request)){
			e.preventDefault();
		}
	});
	$('a.refund-message-preview').click(function(e){
		e.preventDefault();
		var message = $(this).parents('td').find('textarea').val();
		$('#refund-place-holders input').each(function(index, elt){
			var placeHolder = '%' + $(elt).attr('name') + '%';
			message = message.split(placeHolder).join($(this).val());
		});
		message = message.replace(/\n/g, '<br />');
		var title = $.trim($(this).parents('tr').find('th').text());
		$('<div title="' + title + '"><p>' + message + '</p></div>').dialog({
			modal: true,
			resizable: false,
			buttons:{
				Ok: function(){
					$(this).dialog("close");
				}
			}
		});
	});
});