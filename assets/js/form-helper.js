jQuery(document).ready(function($){
	//Check if it is sandbox
	var sandbox = window.location.href.match(/sandbox=true/);
	//Kill every action 
	if(sandbox){
		$('form').submit(function(e){
			e.preventDefault();
		});
		$('#lwp-invoice a').click(function(e){
			e.preventDefault();
		});
	}
	//Prepend user to double submit
	$('form').submit(function(e){
		$(this).find('input[type=submit]').val(LWP.labelProcessing).attr('disabled', true).addClass('disabled');
	});
	//Auto Redirect
	if(!sandbox && $("#lwp-auto-redirect").length > 0){
		href = $("#lwp-auto-redirect").attr('href');
		left = 5;
		window.myInterval = function(){
			left = left - 1;
			$("#lwp-redirect-indicator").text(left);
			if(left == 0){
				clearInterval(timer);
				window.location.href = href;
			}
		}
		var timer = setInterval("myInterval()", 1000);
	}
	//Bulk Mail
	$('#lwp-contact-participants-form').submit(function(e){
		e.preventDefault();
		if(confirm(LWP.labelConfirm)){
			//Validate
			var flg = true;
			var msg = [];
			if(!($(this).find('select[name=from]').val().match(/^(admin|you|author)$/))){
				flg = false;
				msg.push(LWP.labelInvalidFrom);
			}
			if(!($(this).find('input[name=subject]').val().length > 0)){
				flg = false;
				msg.push(LWP.labelInvalidSubject);
			}
			if(!($(this).find('textarea[name=body]').val().length > 0)){
				flg = false;
				msg.push(LWP.labelInvalidBody);
			}
			if(!($(this).find('select[name=to]').val().length > 0)){
				flg = false;
				msg.push(LWP.labelInvalidTo);
			}
			if(flg){
				$(this).find('#lwp-contact-indicator').effect('highlight');
				$(this).find('.indicator').fadeIn();
				$(this).find('#lwp-contact-indicator').addClass('processing');
				$(this).find('input[type=submit]').val(LWP.labelSending).attr('disabled', true);
				var submitData = {
					current: 0,
					total: 0,
					_wpnonce: $(this).find('input[name=_wpnonce]').val(),
					action: $(this).find('input[name=action]').val(),
					event_id: $(this).find('input[name=event_id]').val(),
					from: $(this).find('select[name=from]').val(),
					to: $(this).find('select[name=to]').val(),
					subject: $(this).find('input[name=subject]').val(),
					body: $(this).find('textarea[name=body]').val(),
					endpoint: $(this).attr('action')
				};
				function finishLoading(){
					$('#lwp-contact-indicator').removeClass('processing').find('.indicator').fadeOut();
					$('#lwp-contact-participants-form').find('input[type=submit]').removeClass('disabled').attr('disabled', false).val(LWP.labelSent);
				}
				function setCurrentStatus(current, total){
					$('#lwp-contact-indicator .done').text(current);
					$('#lwp-contact-indicator .total').text(total);
					var division = 0;
					if(total > 0){
						division = Math.min(100, Math.round(current / total * 100));
					}
					$('#lwp-contact-indicator .indicator-bar').css('width', division + '%');
					$('#lwp-contact-indicator').effect('highlight');
				}
				function ajaxResponseHandler(result){
					if(result.success){
						submitData.current = result.current;
						submitData.total = result.total
						setCurrentStatus(result.current, result.total);
						if(result.current >= result.total){
							alert(result.message.join("\n"));
							finishLoading();
						}else{
							$.post(submitData.endpoint, submitData, ajaxResponseHandler);
						}
					}else{
						alert(result.message.join("\n"));
						$(this).find('input[type=submit]').removeClass('disabled').attr('disabled', false).val(LWP.labelSent);
					}
				}
				$.post(submitData.endpoint, submitData, ajaxResponseHandler);
			}else{
				alert(msg.join("\n"));
				finishLoading();
			}
		}else{
			finishLoading();
		}
	});
});
