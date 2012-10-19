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
});