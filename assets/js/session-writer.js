if(window.location.href.match(/_lwpp=[0-9]+/)){
	var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
	for (i = 0, l = hashes.length; i < l; i++){
		var hash = hashes[i].split('=');
		if(hash[0] == '_lwpp'){
			jQuery.post(LWPSESSION.endpoint, {
				action: LWPSESSION.action,
				post_id: LWPSESSION.postId,
				user_id: hash[1],
				referrer: document.referrer
			});
			break;
		}
	}
}