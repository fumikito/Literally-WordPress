/**
 * Support Ajax graph rendering
 * 
 * @since 0.9
 */
jQuery(document).ready(function($){
	//Show Datepicker
	for(prop in LWP){
		var propToSplit = ['monthNames','dayNames','monthNamesShort','dayNamesMin','dayNamesShort'];
		if($.inArray(prop, propToSplit) > -1){
			LWP[prop] = LWP[prop].split(',');
		}
	}
	$('.date-picker').datepicker(LWP);
	//Check if start date is valid.
	$('#date-changer').submit(function(e){
		var from = $(this).find('input[name=from]').val().split('-');
		var to = $(this).find('input[name=to]').val().split('-');
		var fromDate = new Date(from[0], from[1] - 1, from[2]);
		var toDate = new Date(to[0], to[1] - 1, to[2]);
		if(fromDate >= toDate){
			e.preventDefault();
			alert(LWP.alertOldStart);
		}
	});
	//Enable tabs
	$('#lwp-dashboard-ranking').tabs();
});

//Draw Pie Chart
google.load('visualization', '1.0', {packages:['corechart']});
google.setOnLoadCallback(function(){
	var $ = jQuery;
	//PieChart
	var dataSrc = [
		[LWP.pieChartLabel, LWP.pieChartUnit],
		[LWP.pieChartFixed, parseInt($('#lwp-dashboard-amount input[name=reward_fixed]').val())],
		[LWP.pieChartStart, parseInt($('#lwp-dashboard-amount input[name=reward_start]').val())],
		[LWP.pieChartLost, parseInt($('#lwp-dashboard-amount input[name=reward_lost]').val())]
	];
	var data = new google.visualization.arrayToDataTable(dataSrc);
	var pieChart = new google.visualization.PieChart($('#lwp-dashboard-amount .pie-chart')[0]);
	var currencyFormatter = new google.visualization.NumberFormat({
		suffix: LWP.pieChartUnit
	});
	currencyFormatter.format(data, 1);
	pieChart.draw(data, {
		title: LWP.pieChartTitle
	});
	//Area Chart
	$('#lwp-dashboard-daily form').ajaxSubmit({
		dataType: 'json',
		success: function(data){
			if(data){
				var areaSrc = [[LWP.areaChartLabel, LWP.pieChartFixed, LWP.pieChartStart]];
				for(i = 0, l = data.length; i < l; i++){
					areaSrc.push([
						(data[i].date.match(/-01$/)) ? data[i].date.replace(/[0-9]{4}-/, '') : data[i].date.replace(/[0-9]{4}-[0-9]{2}-/, ''),
						parseInt(data[i].fixed), parseInt(data[i].unfixed)
					]);
				}
				var areaData = new google.visualization.arrayToDataTable(areaSrc);
				var areaChart = new google.visualization.SteppedAreaChart($('#lwp-dashboard-daily .area-chart')[0]);
				currencyFormatter.format(areaData, 1);
				currencyFormatter.format(areaData, 2);
				areaChart.draw(areaData, {
					title: LWP.areaChartTitle,
					isStacked: true
				});
			}
		}
	});
});