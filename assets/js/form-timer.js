jQuery(document).ready(function($){
	if($('.lwp-timer').length > 0){
		
		var strToTimestamp = function(object){
			var time = 0;
			if($(object).find(".day").length > 0){
				time += parseInt($(object).find('.day').text()) * 60 * 60 * 24;
			}
			time += parseInt($(object).find('.hour').text()) * 60 * 60;
			time += parseInt($(object).find('.minutes').text()) * 60;
			time += parseInt($(object).find('.seconds').text());
			return time;
		};
		
		var timeStampToString = function(object, timestamp){
			var days = Math.floor(timestamp / (60 * 60 * 24));
			timestamp -= days * 60 * 60 * 24;
			var hours = Math.floor(timestamp / (60 * 60));
			timestamp -= hours * 60 * 60;
			var minutes = Math.floor(timestamp / 60);
			var seconds = timestamp - minutes * 60;
			
			if($(object).find('.days').length > 0){
				$(object).find('.days').text(days);
			}
			$(object).find('.hour').text(("0" + hours).slice(-2));
			$(object).find('.minutes').text(("0" + minutes).slice(-2));
			$(object).find('.seconds').text(("0" + seconds).slice(-2));
		}
		
		$('.lwp-timer').each(function(index, object){
			var timer = setInterval(function(){
				last = strToTimestamp(object) - 1;
				if(last < 1){
					clearInterval(timer);
					window.location.reload();
				}
				timeStampToString(object, last);
			}, 1000);
		});
	}
	//Google Analytics
	$('a.lwp-buynow, a.lwp-dl').click(function(e){
		if(pageTracker){
			pageTracker._trackPageview(this.href.replace(/https?:\/\/[^\/]+\//, "/"));
		}
	});
});
