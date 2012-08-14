jQuery(document).ready(function($){
	//Enable tabs
	$('#lwp-dashboard-ranking').tabs();
});

//Draw Pie chart
google.load('visualization', '1.0', {packages:['corechart']});
google.setOnLoadCallback(function(){
	var $ = jQuery;
	//PieChart
	var dataSrc = [
		[LWP.pieChartLabel, LWP.pieChartUnit]
	];
	$('#lwp-dashboard-amount input[name^=sum_]').each(function(index, elt){
		post_type = $(elt).attr('name').replace(/sum_/, '');
		sum = parseFloat($(elt).val());
		post_type_name = $('#lwp-dashboard-amount input[name=post_type_name_' + post_type + ']').val();
		dataSrc.push([post_type_name, sum]);
	});
	if(dataSrc.length > 1){
		var data = new google.visualization.arrayToDataTable(dataSrc);
		var pieChart = new google.visualization.PieChart($('#lwp-dashboard-amount .pie-chart')[0]);
		var currencyFormatter = new google.visualization.NumberFormat({
			suffix: LWP.pieChartUnit
		});
		currencyFormatter.format(data, 1);
		pieChart.draw(data, {
			title: LWP.pieChartTitle
		});
	}
	//Area Chart
	$('#lwp-dashboard-daily form').ajaxSubmit({
		dataType: 'json',
		success: function(data){
			if(data){
				var areaSrc = [[LWP.areaChartLabel, LWP.areaChartSales]];
				for(i = 0, l = data.length; i < l; i++){
					areaSrc.push([
						(data[i].date.match(/-01$/)) ? data[i].date.replace(/[0-9]{4}-/, '') : data[i].date.replace(/[0-9]{4}-[0-9]{2}-/, ''),
						parseFloat(data[i].total)
					]);
				}
				var areaData = new google.visualization.arrayToDataTable(areaSrc);
				var areaChart = new google.visualization.SteppedAreaChart($('#lwp-dashboard-daily .area-chart')[0]);
				currencyFormatter.format(areaData, 1);
				areaChart.draw(areaData, {
					title: LWP.areaChartTitle,
					isStacked: true
				});
			}
		}
	});

});