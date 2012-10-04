jQuery(document).ready(function($){
	//Create index
	$('.lwp-api-list h4 span').each(function(index, elt){
		var key = '';
		if($(elt).hasClass('apple')){
			key = 'apple';
		}else if($(elt).hasClass('android')){
			key = 'android';
		}
		var id = 'lwp-index'+index;
		$(elt).attr('id', id);
		$('#' + key + '-index').append('<li><a href="#' + id + '">' + $(elt).text() + '</a></li>');
	});
	//Create tab action
	$('#xml-api-switcher a').click(function(e){
		e.preventDefault();
		$('#xml-api-switcher a').removeClass('current');
		$(this).addClass('current');
		switch($(this).attr('href')){
			case '#all':
				$('.api-section').css('display', 'block');
				$('.api-section h4').removeClass('apple').removeClass('android');
				break;
			case '#apple':
			case '#android':
				var vendor = $(this).attr('href').replace(/#/, '');
				verseVendor = vendor == 'apple' ? 'andorid' : 'apple';
				$('.api-section').each(function(index, elt){
					if($(elt).find('h4 .' + vendor).length > 0){
						$(elt).css('display', 'block');
						$(elt).find('h4').removeClass(verseVendor).addClass(vendor);
					}else{
						$(elt).css('display', 'none');
					}
				});
				break;
		}
	});
});