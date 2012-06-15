jQuery(document).ready(function($){
	//Check if it is sandbox
	var sandbox = window.location.href.match(/sandbox=true/);
	//Kill every action 
	if(sandbox){
		$('form,a').submit(function(e){
			e.preventDefault();
		});
	}
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
			if(flg){
				$(this).find('.indicator').fadeIn();
				$(this).find('input[type=submit]').val(LWP.labelSending).attr('disabled', true);
				$(this).ajaxSubmit({
					success: function(result){
						$('#lwp-contact-participants-form').find('.indicator').fadeOut();
						$('#lwp-contact-participants-form').find('input[type=submit]').val(LWP.labelSent).attr('disabled', false);
						if(result.success){
							$('#lwp-contact-participants-form').find('input[name=subject]').val('');
							$('#lwp-contact-participants-form').find('textarea[name=body]').val('');
						}
						alert(result.message.join("\n"));
					},
					dataType: 'json'
				});
			}else{
				alert(msg.join("\n"));
			}
		}
	});
});
