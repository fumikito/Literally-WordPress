jQuery(function(){
	jQuery(".date-picker").datetime({
		showOtherMonths: true,
		selectOtherMonths: true,
		dateFormat: "yy-mm-dd 00:00:00",
		userLang: "ja"
	});
	jQuery("#doaction, #doaction2").click(function(e){
		if(!confirm("本当に削除してよろしいですか？"))
			return false;
	});
});
