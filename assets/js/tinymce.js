/**
 * TinyMCEにボタンを追加する
 * @package Literally WordPress
 */

(function() {
	
    tinymce.create(
		'tinymce.plugins.lwpShortCode', //プラグイン関数名
		{
			/**
			 * プラグインの情報
			 * @var {Function}
			 */
			getInfo : function() {
		        return {
		            longname : 'Literally WordPress ShortCode',
		            author : 'Hametuha inc.',
		            authorurl : 'http://hametuha.co.jp',
		            infourl : 'http://hametuha.co.jp/plugins/literally-wordpress',
		            version : "0.8"
	            };
	        },

			/**
			 * コントロールを作成する
			 * @var {Function}
			 */
			createControl: function(name, controlManager){
				var listBox = null;
				switch(name){
					case "lwpListBox":
						listBox = controlManager.createListBox('lwpListBox', {
							title: 'lwpShortCode.title',
							onselect: function(val){
								var current = tinyMCE.activeEditor.selection.getContent();
								tinyMCE.activeEditor.selection.setContent("[lwp " + val + "]" + current + "[/lwp]");
							}
						});
						listBox.add('lwpShortCode.owner', "owner");
						listBox.add('lwpShortCode.subscriber', "subscriber");
						listBox.add('lwpShortCode.nonOwner', "non-owner");
						listBox.add('lwpShortCode.nonSubscriber', "non-subscriber");
						return listBox;
						break;
					case "lwpBuyNow":
						listBox = controlManager.createListBox('lwpBuyNow', {
							title: 'lwpShortCode.buyNow',
							onselect: function(val){
								var button = null;
								switch(val){
									case 'image':
										button = '[buynow ' + tinyMCE.activeEditor.getLang('lwpShortCode.src_message') + ']';
										break;
									case 'noimage':
										button = '[buynow link]';
										break;
									default:
										button = '[buynow]';
										break;
								}
								tinyMCE.activeEditor.selection.setContent(button);
							}
						});
						listBox.add('lwpShortCode.deault', "default");
						listBox.add('lwpShortCode.noimage', "noimage");
						listBox.add('lwpShortCode.image', "image");
						return listBox;
						break;
				}
				return null;
			}
        }
	);
	
    tinymce.PluginManager.add('lwpShortCode', tinymce.plugins.lwpShortCode);
})();