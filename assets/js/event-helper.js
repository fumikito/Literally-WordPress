jQuery(document).ready(function($){
	//Show Datepicker
	var propToSplit = ['monthNames','dayNames','monthNamesShort','dayNamesMin','dayNamesShort'];
	for(prop in LWP){
		if(propToSplit.indexOf(prop) > -1){
			LWP[prop] = LWP[prop].split(',');
		}
	}
	$('.date-picker').datepicker(LWP);
	
	var TicketForm = {
		clear: function(){
			$('input[name=ticket_id]').val(0);
			$('#ticket_price, #ticket_post_title, #ticket_stock').val('');
			$('#ticket_post_content').val('');
		},
		deleteLimit: function(e){
			e.preventDefault();
			$(this).parents('li').remove();
			if($('#cancel-date-list li').length <= 1){
				$('#cancel-date-list').addClass('zero');
			}
		},
		deleteTicket: function(e){
			e.preventDefault();
			$(this).parents('td').addClass('loading');
			var ticketID = parseInt($(this).parents('tr').attr('id').replace(/ticket-/, ''), 10);
			if(confirm(LWP.deleteConfirmation)){
				$.post($(this).attr('href'), {
					action: 'lwp_delete_ticket',
					_wpnonce: $('input[name=_lwpeventnonce]').val(),
					post_id: ticketID
				}, function(result){
					if(result.status){
						TicketForm.clear();
						$('#ticket-' + ticketID).fadeOut('normal', function(){
							$(this).remove();
						});
					}else{
						$(this).parents('td').removeClass('loading');
						alert(result.message);
					}
				});
			}
		},
		editTicket: function(e){
			alert('編集するよ！');
		}
	};
	
	//Clear form
	$('#ticket_cancel').click(function(e){
		e.preventDefault();
		TickerForm.clear();
	});
	
	//Add cancel limit
	$('#lwp-cancel-add').click(function(e){
		e.preventDefault();
		var days = parseInt($('input[name=cancel_limit]').val(), 10);
		var ratio = Math.min(parseInt($('input[name=cancel_ratio]').val(), 10), 100);
		var markup = LWP.cancelLimitPlaceHolder.
			replace(/%1\$s/, '<input type="text" class="small-text" readonly="readonly" name="cancel_limit_day[]" value="' + days + '" />').
			replace(/%2\$s/, '<input type="text" class="small-text" readonly="readonly" name="cancel_limit_ratio[]" value="' + ratio + '" />') + 
			'<a class="button" href="#">' + LWP.deleteButtonLabel + '</a>';
		var li = $('<li>' + markup + '</li>');
		$('#cancel-date-list').removeClass('zero').append(li);
		li.find('a').click(TicketForm.deleteLimit);
		$('input[name=cancel_limit], input[name=cancel_ratio]').val('');
	});
	//Add delete function
	$('#cancel-date-list li a').click(TicketForm.deleteLimit);
	//manage submit form
	$('#ticket_submit').click(function(e){
		e.preventDefault();
		if($(this).parents('p').hasClass('loading')){
			return;
		}else{
			$(this).parents('p').addClass('loading');
		}
		$.post($(this).attr('href'), {
			action: 'lwp_edit_ticket',
			_wpnonce: $('input[name=_lwpeventnonce]').val(),
			post_id: $('input[name=ticket_id]').val(), 
			post_title: $('#ticket_post_title').val(),
			post_content: $('#ticket_post_content').val(),
			post_parent: $('#ticket_parent_id').val(),
			price: $('#ticket_price').val(),
			stock: $('#ticket_stock').val()
		}, function(result){
			console.log(result);
			$('#ticket_submit').parents('p.submit').removeClass('loading');
			if(result.status){
				var tr;
				if(result.mode == 'update'){
					tr = $('#ticket-' + result.post_id);
					tr.find('th').text(result.post_title);
				}else{
					tr = $('<tr id="ticket-' + result.post_id + '"></tr>').css('display', 'none');
					tr.html(
						'<th scope="row">' + result.post_title + '</th>' + 
						'<td>' + result.post_content + '</td>' +
						'<td>' + result.price + '</td>' +
						'<td>' + result.stock + '</td>' +
						'<td><a href="#" class="button ticket-edit">' + LWP.editButtonLabel + '</a></td>' + 
						'<td><a href="' + LWP.endpoint + '" class="button ticket-delete">' + LWP.deleteButtonLabel + '</a></td>'
					);
					$('#ticket-list-table tbody').append(tr);
					tr.fadeIn('normal', function(){
						$(this).find('.ticket-edit').click();
						$(this).find('.ticket-delete').click(TicketForm.deleteTicket);
					});
				}
			}else{
				alert(result.message);
			}
		});
	});
	//Ticket delete
	$('.ticket-delete').click(TicketForm.deleteTicket);
});