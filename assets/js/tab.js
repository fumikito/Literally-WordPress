jQuery(document).ready(function($){
	$('.nav-tab-wrapper a').each(function(index, elt){
		if(!$(elt).hasClass('nav-tab-active')){
			$($(elt).attr('href')).css('display', 'none');
		}
	}).click(function(e){
		e.preventDefault();
		$('div[id^=tab]').css('display', 'none');
		$('.nav-tab-wrapper a').removeClass('nav-tab-active');
		$(this).addClass('nav-tab-active');
		$($(this).attr('href')).fadeIn();
	});
});