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
				switch(name){
					case "lwpListBox":
						var listBox = controlManager.createListBox('lwpListBox', {
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
				}
				return null;
			}
        }
	);
	
    tinymce.PluginManager.add('lwpShortCode', tinymce.plugins.lwpShortCode);
})();