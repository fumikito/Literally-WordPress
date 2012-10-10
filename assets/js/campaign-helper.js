jQuery(document).ready(function($){
	var timer = null;
	var currentChar = '';
	var onloading = false;
	var container = $('#campaign-post-list');
	var area = $('#post-container');
	
	function addListItem(id, name){
		var list = $('<li class="ui-menu-item"><a href="#' + id + '" class="ui-corner-all">' + name + '</a></li>');
		container.append(list);
		list.find('a').hover(function(e){
			$(this).addClass('ui-state-focus');
		}, function(e){
			$(this).removeClass('ui-state-focus');
		}).click(function(e){
			e.preventDefault();
			addButton(id, name);
		});
	}
	
	function removeItem(e){
		e.preventDefault();
		removeId($(this).attr('id').replace(/[^0-9]/m, ''));
		$(this).parents('span');
	}
	
	function addButton(id, name){
		var button = $('<span><a id="menu_item' + id + '">X</a>' + name + '</span>');
		if(area.find('a[id=menu_item' + id + ']').length == 0){
			area.append(button);
			addId(id);
			button.find('a').click(function(e){
				e.preventDefault();
				button.remove();
				removeId(id);
				area.effect('highlight');
			});
			area.effect('highlight');
		}
	}
	
	function addId(id){
		id = parseInt($.trim(id), 10);
		var ids = $.trim($('#book_id').val());
		if(ids == ''){
			ids = [];
		}else{
			ids = ids.split(',');
		}
		if($.inArray(id, ids) == -1){
			ids.push(id);
		}
		$('#book_id').val(ids.join(','));
	}
	
	function removeId(id){
		id = parseInt($.trim(id), 10);
		var ids = $.trim($('#book_id').val()).split(',');
		var newIds = [];
		$.each(ids, function(index, elt){
			if(parseInt(elt, 10) != id){
				newIds.push(elt);
			}
		});
		$('#book_id').val(newIds.join(','));
	}
	
	
	function getList(){
		onloading = true;
		container.css('display', 'block');
		$.get(LWP.endpoint, {
			action: LWP.action,
			_wpnonce: LWP.nonce,
			query: currentChar
		}, function(result){
			if(result.total > 0){
				container.empty();
				$.each(result.items, function(index, elt){
					addListItem(elt.ID, elt.post_title);
				});
				setTimeout(function(){
					container.css('display', 'none');
				}, 10000);
			}else{
				container.css('display', 'none');
			}
			onloading = false;
			timer = null;
		});
	}
		
	$('#post-picker').keyup(function(e){
        currentChar = $(this).val();
        if(currentChar.length > 2 && !onloading){
            if(timer){
                //Timer exists, reset
                clearTimeout(timer);
            }
            //Enqueue AJAX search
            timer = setTimeout(getList, 1500);
        }
    });
});