jQuery(document).ready(function($){
	$(".date-picker").datetimepicker({
		dateFormat: "yy-mm-dd",
		timeFormat: "hh:mm:00",
		timeOnlyTitle: '時刻の指定',
		timeText: '時刻',
		hourText: '時',
		minuteText: '分',
		secondText: '秒',
		currentText: '現在',
		closeText: '閉じる'
	});
	$("#doaction, #doaction2").click(function(e){
		if(!confirm("本当に削除してよろしいですか？"))
			return false;
	});
});
