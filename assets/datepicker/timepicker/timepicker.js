// änderungen / 090521:
// OK beim einlesen im american mode zeit umrechnen
// OK datum einlesen
// OK beim ändern des input feldes widget anpassen / neu einlesen
// OK >> wenn keine stunde/minute angegeben ist 00 angeben
// OK sprache datepicker
// OK sprache durch parameter reingeben
// OK mehrere felder bedienen
// OK datepicker als klickbar machen

/** fixes / 090521:
	- convert widget time to am/pm if american mode is on
	- show correct date from input
	- on change at input field modify widget
	- if there is no hour/minute given in input, show 00
	- start datepicker in given language
	- new parameter for language
	- able to work on many fields
	- write date from datepicker
*/
jQuery.fn.datetime = function() {

	var userLang 		= arguments[0]['userLang'] || 'en';
	var b24Hour			= !(arguments[0]['americanMode'] || false);	
	var markerClass		= 'hasDateTime';

				
	return this.each(function(){
			 
				var datepicker_def 	= {
							changeMonth: true,
							changeYear: true,
							dateFormat: 'yy-mm-dd',
							showButtonPanel: true, 
							onSelect: writeDate						
				};			
		
				var lang = {};

				lang['en'] = {
								time: 	'Time',
								hour:	'Hour',
								minute:	'Minute',
								close:	'Close'			
							};
							
				lang['de'] = {
								time: 	'Zeit',
								hour:	'Stunde',
								minute:	'Minute',
								close:	'Schließen'			
							};
				lang['ja'] = {
								time: 	'時間',
								hour:	'時',
								minute:	'分',
								close:	'閉じる'			
							};
				
				jQuery(this).data('sets',datepicker_def);
				jQuery(this).data('userLang',userLang);
				jQuery(this).data('b24Hour',b24Hour);
				
				function renderPickerPlug(b24Hour_,lang_) {
					var loadedLang = lang[lang_] || lang['en'];
					
					if (!jQuery('#pickerplug').length) {
					
						var htmlins = '<ul id="pickerplug">';
						htmlins += '<li>';
						htmlins += '<div id="datepicker"></div>';
						htmlins += '</li>';
						htmlins += '<li>';
						htmlins += '<div id="timepicker" class="ui-corner-all ui-widget-content">';
						htmlins += '<h3 id="tpSelectedTime" class="ui-widget-header datepicker-header ui-corner-all">';
						htmlins += '	<span id="text_time"></span>';
						htmlins += '	<span class="selHrs" >00</span>';
						htmlins += '	<span class="delim" >:</span>';
						htmlins += '	<span class="selMins">00</span>';
						htmlins += '	<span class="dayPeriod">am</span>';
						htmlins += '</h3>';			
						htmlins += '<ul id="sliderContainer">';	
						htmlins += '	<li>';
						htmlins += '        <h4 id="text_hour"></h4>';
						htmlins += '        <div id="hourSlider" class="slider"></div>';
						htmlins += '	</li>';
						htmlins += '	<li>';
						htmlins += '        <h4 id="text_minute"></h4>';				
						htmlins += '        <div id="minuteSlider" class="slider"></div>';
						htmlins += '	</li>';
						htmlins += '</ul>';
						htmlins += '<button type="button" class="ui-datepicker-close ui-state-default ui-priority-primary ui-corner-all" id="text_close"></button>';				
						htmlins += '</div>';
						htmlins += '</li>';				
						htmlins += '</ul>';
						jQuery('body').append(htmlins);	
						
						jQuery('#datepicker').datepicker();
						jQuery(document).mousedown(closePickPlug);			
						jQuery('#pickerplug .ui-datepicker-close').click(closePickPlug);							

	 // Slider
						jQuery('#hourSlider').slider({
							orientation: "vertical",   
							range: 'min',                 
							min: 0,
							max: 23,
							step: 1,
							slide: function(event, ui) {
								writeDate(writeTime(ui.value,'hour'),'time');
								
							},
							change: function(event, ui) {
								jQuery('#tpSelectedTime .selHrs').effect('highlight', 1000);
							}
						});
						// Slider
						jQuery('#minuteSlider').slider({
							orientation: "vertical",      
							range: 'min',                                  
							min: 0,
							max: 55,
							step: 5,
							slide: function(event, ui) {                   
								writeDate(writeTime(ui.value,'minute'),'time');                                           
							},
							change: function(event, ui) {
								jQuery('#tpSelectedTime .selMins').effect('highlight', 1000);
							}
						});
					
		//Inline editor bind
						jQuery('#tpSelectedTime .selHrs').keyup(function(e){
							if((e.which <= 57 && e.which >= 48) && (jQuery(this).text() >=1 && jQuery(this).text() <=12 ) ){
							//console.log("Which: "+e.which);
						   jQuery('#hourSlider').slider('value', parseInt(jQuery(this).text()));
							//console.log("Val: "+parseInt(jQuery(this).val()))
							}else{
								jQuery(this).val(jQuery(this).text().slice(0, -1));
							}
							//if(jQuery(this).val() < 1){
							//    jQuery(this).val(1);
							//}
						});
						
		//Inline editor bind
						jQuery('#tpSelectedTime .selMins').keyup(function(e){
							if((e.which <= 57 && e.which >= 48) && (jQuery(this).text() >=0 && jQuery(this).text() <=59 ) ){
							//console.log("Which: "+e.which);
						   jQuery('#minuteSlider').slider('value', parseInt(jQuery(this).text()));
							//console.log("Val: "+parseInt(jQuery(this).val()))
							}else{
								jQuery(this).text(jQuery(this).text().slice(0, -1));
							}
							//if(jQuery(this).val() < 1){
							//    jQuery(this).val(1);
							//}
						});					
					}

					jQuery('.dayPeriod').toggle(!b24Hour);
					jQuery('#text_time').text(loadedLang['time']);
					jQuery('#text_hour').text(loadedLang['hour']);
					jQuery('#text_minute').text(loadedLang['minute']);
					jQuery('#text_close').text(loadedLang['close']);
					
					jQuery('#pickerplug').data('userLang',lang_);
					jQuery('#pickerplug').data('b24Hour',b24Hour_);	
				}
				
				jQuery(this).bind('focus',function(){ 
					
					var top 	= jQuery(this).offset().top+jQuery(this).outerHeight(); 
					var left 	= jQuery(this).offset().left;
					
					if (jQuery(this).data('userLang') 	!= jQuery('#pickerplug').data('userLang') || 
						jQuery(this).data('b24Hour') 	!= jQuery('#pickerplug').data('userLang') ) {
						renderPickerPlug(jQuery(this).data('b24Hour'),jQuery(this).data('userLang'));
					}
					
					jQuery('#pickerplug').css({
										left: left+'px',
										top: top+'px'
										}).show('normal');						
					
					if (jQuery(this).data('userLang')!='en' && lang[jQuery(this).data('userLang')]) {
						jQuery('#datepicker').datepicker('option', jQuery.extend({},
												jQuery.datepicker.regional[jQuery(this).data('userLang')]));	
						jQuery('#datepicker').datepicker('option', jQuery.extend(jQuery(this).data('sets')));													
					} else {
						jQuery('#datepicker').datepicker('option', jQuery.extend({},
												jQuery.datepicker.regional['']));	
						jQuery('#datepicker').datepicker('option', jQuery.extend(jQuery(this).data('sets')));												
					}					

					parseTime(this);
					
					if (jQuery('#pickerplug').css('display') == 'none') { 											
						jQuery('#pickerplug').show('normal');
					}
					
					jQuery(this).bind('keyup',parseTime);
					//jQuery(this).bind('slider',writeTime);

					jQuery(this).addClass(markerClass);

					jQuery('#pickerplug').data('inputfield',this);
				});

				function parseTime (obj) {

					var time = (jQuery(obj).val() || jQuery(this).val()).split(" ");
					
					if (time.length < 2) {
						var date = new Date();
						var year = date.getFullYear();
						var month = (date.getMonth() < 9) ? "0" + (date.getMonth() + 1).toString() : date.getMonth() + 1;
						var day = (date.getDate() < 10) ? "0" + date.getDate().toString() : date.getDate();
						time = [year + "-" + month + "-" + day];
						time.push('00:00:00');
					}
					jQuery('#pickerplug').data('lastdate',time[0]);	//lastdate = time[0];
					jQuery('#pickerplug').data('lasttime',time[1]);  //lasttime = time[1];					
					time = time[1].split(":");					
					
					if (time.length < 2) {
						time = ['00','00','00'];
					}
					
					var hour	= time[0] || '00';
					var minute 	= time[1] || '00';
				
					writeTime(hour,'hour');
					writeTime(minute,'minute');

					jQuery('#hourSlider').slider('option', 'value', hour);
					jQuery('#minuteSlider').slider('option', 'value', minute);
					jQuery('#datepicker').datepicker(
											'setDate', 
											jQuery.datepicker.parseDate(
													datepicker_def['dateFormat'], 
													jQuery('#pickerplug').data('lastdate')
												));
				}
				
				function writeTime(fragment,type) {
					var time = '';
					
					switch (type) {
						case 'hour':
	                    	var hours = parseInt(fragment,10);
								
	                    	if (!jQuery('#pickerplug').data('b24Hour') && hours > 11) {                    		
	                    		hours -= 12;
	                    		jQuery('.dayPeriod').text('pm');
	                    		
	                    	} else if (!jQuery('#pickerplug').data('b24Hour')) {
	                    		jQuery('.dayPeriod').text('am');
	                    	} 
	                    	
	                    	if (hours < 10) {
	                    		hours = '0'.concat(hours);
	                    	}
	                    	if (fragment < 10) {
	                    		fragment = '0'.concat(parseInt(fragment));
	                    	}
	                    	
	                    	jQuery('#tpSelectedTime .selHrs').text(hours);
	                    	
	                    	time = fragment+':'+jQuery('#tpSelectedTime .selMins').text();						
							break;
						case 'minute':
	                    	minutes = ((fragment < 10) ? '0' :'') + parseInt(fragment,10);
	                    	jQuery('#tpSelectedTime .selMins').text(minutes);
	                   
	                        time = jQuery('#hourSlider').slider('option', 'value')+':'+minutes;  						
							break;
					}
					return time;
				}				
				
				function writeDate (text,type) {

					switch (type) {
						case 'time':
							jQuery('#pickerplug').data('lasttime',text+':00');						
							break;	
						default:
							jQuery('#pickerplug').data('lastdate',text);												
					}
					
					jQuery(jQuery('#pickerplug').data('inputfield')).val(
								jQuery('#pickerplug').data('lastdate')+' '+jQuery('#pickerplug').data('lasttime')
					);
				}
				
				function closePickPlug (event) {

					if ((jQuery(event.target).parents('#pickerplug').length ||
						jQuery(event.target).hasClass(markerClass)) &&
						!jQuery(event.target).hasClass('ui-datepicker-close')) {					
						return;
					}
					
					jQuery('#pickerplug').hide('normal');		
					jQuery(this).unbind('click',closePickPlug);
					jQuery(this).unbind('keyup',parseTime);
					jQuery(this).removeClass(markerClass);
				}
								
            });
            
           }
