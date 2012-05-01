setTimeout(function(){
	if(location.hash){
		window.scrollTo(0, 0);
	}
}, 1);

jQuery(document).ready(function($){
	//Parse URI and if find hash, change active tab
	if(location.href.match(/#tab-([0-9])/)){
		$('.nav-tab-wrapper a').removeClass('nav-tab-active');
		$('.nav-tab-wrapper a[href=#tab-' + RegExp.$1 + ']').addClass('nav-tab-active');
	}
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
	$('#lwp-setting-form').submit(function(e){
		//Get currently opend tab
		var id = $('.nav-tab-wrapper .nav-tab-active').attr('href');
		if(id.match(/^#tab-[2-4]$/)){
			$(this).attr('action', $(this).attr('action') + id);
		}
	});
});