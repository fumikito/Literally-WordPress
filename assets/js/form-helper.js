jQuery(document).ready(function($){
	if($("#lwp-auto-redirect").length > 0){
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
});
