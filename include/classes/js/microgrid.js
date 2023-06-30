////////////////////////////////////////////
// LAST MODIFIED: 09.10.2017 18:15
////////////////////////////////////////////

/**
 *  add listener
 */
function __mgAddListener(element, event, listener, bubble) {
	if(element.addEventListener){
		if(typeof(bubble) == "undefined") bubble = false;
		element.addEventListener(event, listener, bubble);
	}else if(this.attachEvent){
		element.attachEvent("on" + event, listener);
	}
}

/**
 *   set cookie
 */
function __mgSetCookie(name,value,days) {
    if (days){
        var date = new Date();
        date.setTime(date.getTime()+(days*24*60*60*1000));
        var expires = '; expires='+date.toGMTString();
    }
    else var expires = '';
    document.cookie = name+'='+value+expires+'; path=/';
}

/**
 *   read cookie
 */
function __mgReadCookie(name) {
   var nameEQ = name + '=';
   var ca = document.cookie.split(';');
   for(var i=0;i < ca.length;i++) {
      var c = ca[i];
      while (c.charAt(0)==' ') c = c.substring(1,c.length);
      if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
   }
   return null;
}

function __mgSetFocus(el){
    // we need undefined, because IE8 doesn't move focus on textarea
    if(document.getElementById(el) && !document.getElementById(el).disabled && document.getElementById(el).type != "hidden" && document.getElementById(el).type != undefined){
		try{
		   document.getElementById(el).focus();    
		}catch(e){
		   // cannot move focus
		}      
    }
}

function __mgDoPostBack(suffix, action, rid, sorting_fields, sorting_types, page, operation, operation_type, operation_field)
{   
    var suffix         = (suffix != null) ? suffix : '';
    var frmObj         = document.getElementById('frmMicroGrid_'+suffix);
   
    var action         = (action != null) ? action : '';
    var rid            = (rid != null) ? rid : frmObj.mg_rid.value;
    var sorting_fields = (sorting_fields != null && sorting_fields != "") ? sorting_fields : frmObj.mg_sorting_fields.value;
    var sorting_types  = (sorting_types != null && sorting_types != "") ? sorting_types : frmObj.mg_sorting_types.value;
    var page           = (page != null && page != "") ? page : frmObj.mg_page.value;
   
    // for additional operations
	if(action == 'refresh'){
		var operation      = '';
		var operation_type = '';
		var operation_field = '';		
	}else{
		var operation      = (operation != null) ? operation : frmObj.mg_operation.value;
		var operation_type = (operation_type != null) ? operation_type : frmObj.mg_operation_type.value;
		var operation_field = (operation_field != null) ? operation_field : frmObj.mg_operation_field.value;   
	}
   
    if(action == "delete" && !confirm(Vocabulary._MSG["alert_delete_record"])){
        return false;
    }else{
		// handle custom alerts
		if(action == "update" && window['__mgDoModeAlert']){
			if(!window['__mgDoModeAlert']()){ return false; }
		}

        if(operation == "filtering"){
            frmObj.mg_search_status.value = 'active';
        }else if(operation == "reset_filtering"){
            frmObj.mg_search_status.value = '';
        }
        
        frmObj.mg_action.value = action;
        frmObj.mg_rid.value = rid;
        
        frmObj.mg_sorting_fields.value = sorting_fields;
        frmObj.mg_sorting_types.value = sorting_types;
        frmObj.mg_page.value = page;
        
        frmObj.mg_operation.value = operation;
        frmObj.mg_operation_field.value = operation_field;
        frmObj.mg_operation_type.value = operation_type;
        
        frmObj.submit();
    }			
}


// set calendar datetime for ddl's
function setCalendarDate(frm, date_field, datetime_format, date_value, year_start, is_default, is_reset)
{
    var year    = (document.getElementById(date_field+'__nc_year')   != null) ? document.getElementById(date_field+'__nc_year').value : '';
    var month   = (document.getElementById(date_field+'__nc_month')  != null) ? document.getElementById(date_field+'__nc_month').value : '';
    var day     = (document.getElementById(date_field+'__nc_day')    != null) ? document.getElementById(date_field+'__nc_day').value : '';
    var hour    = (document.getElementById(date_field+'__nc_hour')   != null) ? document.getElementById(date_field+'__nc_hour').value : '';
    var minute  = (document.getElementById(date_field+'__nc_minute') != null) ? document.getElementById(date_field+'__nc_minute').value : '';
    var second  = (document.getElementById(date_field+'__nc_second') != null) ? document.getElementById(date_field+'__nc_second').value : '';
    var meridiem = (document.getElementById(date_field+'__nc_meridiem') != null) ? document.getElementById(date_field+'__nc_meridiem').value : '';
    date_value = (date_value != null) ? date_value : '';
    year_start = (year_start != null) ? year_start : '0';
    is_default = (is_default != null) ? is_default : true;
	is_reset = (is_reset != null) ? is_reset : false;
	
    if(date_value != ''){
		if(datetime_format == 'm-d-Y'){
			month   = date_value.substring(0,2);
			day     = date_value.substring(3,5);
			year    = date_value.substring(6,10);
			date_value = year+'-'+month+'-'+day;
		}else if(datetime_format == 'd-m-Y'){
			day     = date_value.substring(0,2);
			month   = date_value.substring(3,5);
			year    = date_value.substring(6,10);
			date_value = year+'-'+month+'-'+day;
		}else{
			year    = date_value.substring(0,4);
			month   = date_value.substring(5,7);
			day     = date_value.substring(8,10);        
		}
		hour     = date_value.substring(11,13);
		minute   = date_value.substring(14,16);
		second   = date_value.substring(17,19);

        // if time format
		if(datetime_format == 'H:i:s'){
			hour     = date_value.substring(0,2);
			minute   = date_value.substring(3,5);
			second   = date_value.substring(6,8);			
		}else if(datetime_format == 'H:i'){
			hour     = date_value.substring(0,2);
			minute   = date_value.substring(3,5);
			second   = '00';			
			date_value = hour+':'+minute+':'+second;
		}else if(datetime_format == 'g:i:s A'){
			hour     = '0'+date_value.substring(0,1);
			minute   = date_value.substring(2,4);
			second   = date_value.substring(5,7);
			date_value = hour+':'+minute+':'+second;
		}else if(datetime_format == 'g:i A'){
			hour     = '0'+date_value.substring(0,1);
			minute   = date_value.substring(2,4);
			second   = '00';			
			date_value = hour+':'+minute+':'+second;
		}

		document.getElementById(date_field).value = date_value;
  
		var days_in_month = 32 - new Date(year, month-1, 32).getDate();
		if(day > days_in_month) day = days_in_month;  

        if((datetime_format == 'Y-m-d') || (datetime_format == 'd-m-Y')){
            document.getElementById(date_field+'__nc_year').selectedIndex = parseInt(year - year_start) + parseInt('1');
            document.getElementById(date_field+'__nc_month').selectedIndex = month;
            document.getElementById(date_field+'__nc_day').selectedIndex = day;
            ///alert("b");
        }else if((datetime_format == 'Y-m-d H:i:s') || (datetime_format == 'd-m-Y H:i:s') || (datetime_format == 'd-m-Y H:i') || (datetime_format == 'Y-m-d H:i')){         
            document.getElementById(date_field+'__nc_year').selectedIndex = parseInt(year - year_start) + parseInt('1');
            document.getElementById(date_field+'__nc_month').selectedIndex = month;
            document.getElementById(date_field+'__nc_day').selectedIndex = day;
            document.getElementById(date_field+'__nc_hour').selectedIndex = parseInt(trimNumber(hour)) + parseInt('1');
            document.getElementById(date_field+'__nc_minute').selectedIndex = parseInt(trimNumber(minute)) + parseInt('1');
            if(datetime_format != 'd-m-Y H:i' && datetime_format != 'Y-m-d H:i') document.getElementById(date_field+'__nc_second').selectedIndex = parseInt(second) + parseInt('1');
            //alert("c" + hour+' '+trimNumber(hour));            
            document.getElementById(date_field).value = year+'-'+month+'-'+day+' '+hour+':'+minute+':'+second;
        }else if(datetime_format == 'm-d-Y'){
            ///alert("m-d-Y "+ month+' '+day+' '+year);
            document.getElementById(date_field+'__nc_year').selectedIndex = parseInt(year - year_start) + parseInt('1');
            document.getElementById(date_field+'__nc_month').selectedIndex = month;
            document.getElementById(date_field+'__nc_day').selectedIndex = day;
		}else if(datetime_format == 'H:i:s'){
            document.getElementById(date_field+'__nc_hour').selectedIndex = parseInt(trimNumber(hour)) + parseInt('1');
            document.getElementById(date_field+'__nc_minute').selectedIndex = parseInt(trimNumber(minute)) + parseInt('1');
            document.getElementById(date_field+'__nc_second').selectedIndex = parseInt(trimNumber(second)) + parseInt('1');	
		}else if(datetime_format == 'H:i'){
            document.getElementById(date_field+'__nc_hour').selectedIndex = parseInt(trimNumber(hour)) + parseInt('1');
            document.getElementById(date_field+'__nc_minute').selectedIndex = parseInt(trimNumber(minute)) + parseInt('1');			
		}else if(datetime_format == 'g:i A'){
            document.getElementById(date_field+'__nc_hour').selectedIndex = parseInt(trimNumber(hour)) - parseInt('1');
            document.getElementById(date_field+'__nc_minute').selectedIndex = parseInt(trimNumber(minute));
			document.getElementById(date_field+'__nc_meridiem').selectedIndex = parseInt((hour < 12) ? 0 : 1);
		}else if(datetime_format == 'g:i:s A'){
            document.getElementById(date_field+'__nc_hour').selectedIndex = parseInt(trimNumber(hour)) - parseInt('1');
            document.getElementById(date_field+'__nc_minute').selectedIndex = parseInt(trimNumber(minute));
			document.getElementById(date_field+'__nc_second').selectedIndex = parseInt(trimNumber(second)) + parseInt('1');	
			document.getElementById(date_field+'__nc_meridiem').selectedIndex = parseInt((hour < 12) ? 0 : 1);
        }else{
            document.getElementById(date_field+'__nc_year').selectedIndex = parseInt(year - year_start) + parseInt('1');
            document.getElementById(date_field+'__nc_month').selectedIndex = month;
            document.getElementById(date_field+'__nc_day').selectedIndex = day;
        }
    }else{      
		var days_in_month = 32 - new Date(year, month-1, 32).getDate();
		if(day > days_in_month) day = days_in_month;
		
		// Set date value as empty if we in 'reset' action
		if(is_reset){
            document.getElementById(date_field+'__nc_year').selectedIndex = 0;
            document.getElementById(date_field+'__nc_month').selectedIndex = 0;
            document.getElementById(date_field+'__nc_day').selectedIndex = 0;
			year = month = day = '';
		}
		
        // Set date if ddl was changed                    
        if(datetime_format == 'Y-m-d'){
            document.getElementById(date_field).value = year+'-'+month+'-'+day;
        }else if(datetime_format == 'd-m-Y'){
            document.getElementById(date_field).value = year+'-'+month+'-'+day;
        }else if(datetime_format == 'm-d-Y'){
            document.getElementById(date_field).value = year+'-'+month+'-'+day;
        }else if(datetime_format == 'Y-m-d H:i:s'){
            document.getElementById(date_field).value = year+'-'+month+'-'+day+' '+hour+':'+minute+':'+second;
        }else if(datetime_format == 'Y-m-d H:i'){
            document.getElementById(date_field).value = year+'-'+month+'-'+day+' '+hour+':'+minute;
        }else if(datetime_format == 'd-m-Y H:i:s'){
            document.getElementById(date_field).value = year+'-'+month+'-'+day+' '+hour+':'+minute+':'+second;
        }else if(datetime_format == 'd-m-Y H:i'){
            document.getElementById(date_field).value = year+'-'+month+'-'+day+' '+hour+':'+minute;
        }else if(datetime_format == 'H:i:s'){
			document.getElementById(date_field).value = hour+':'+minute+':'+second;
        }else if(datetime_format == 'H:i'){
			document.getElementById(date_field).value = hour+':'+minute+':00';
		}else if(datetime_format == 'g:i:s A'){
			if(meridiem == 'pm' && hour != '12') hour = parseInt(trimNumber(hour)) + parseInt('12'); 
			else if(meridiem == 'am' && hour == '12') hour = '00';
			document.getElementById(date_field).value = hour+':'+minute+':'+second;
		}else if(datetime_format == 'g:i A'){
			if(meridiem == 'pm' && hour != '12') hour = parseInt(trimNumber(hour)) + parseInt('12'); 
			else if(meridiem == 'am' && hour == '12') hour = '00';
			document.getElementById(date_field).value = hour+':'+minute+':00';
        }else{
            document.getElementById(date_field).value = year+'-'+month+'-'+day;
        }
    }
	
	// Clear date field if was entered date empty
    if((document.getElementById(date_field).value.length != 8) &&
	   (document.getElementById(date_field).value.length != 10) &&
	   (document.getElementById(date_field).value.length != 19) &&
	   (document.getElementById(date_field).value.length != 16)){
        document.getElementById(date_field).value = '';
    }

    // refill days in month
    if(datetime_format != 'H:i:s' && datetime_format != 'H:i' && datetime_format != 'g:i:s A' && datetime_format != 'g:i A'){
		refillDaysInMonth(date_field, year, month);
    }   
}

// refill days according ion selected month
function refillDaysInMonth(date_field, year, month)
{
	var dayDDL = (document.getElementById(date_field+'__nc_day') != null) ? document.getElementById(date_field+'__nc_day') : false;
	var days_in_month = 32 - new Date(year, month-1, 32).getDate();
	//alert(days_in_month);
	if(dayDDL && month != ""){
		for(i = 1; i <= 31; i++){
			if(i > days_in_month) dayDDL.options[i].disabled = true;
			else dayDDL.options[i].disabled = false;
		}
		if(dayDDL.options[dayDDL.selectedIndex].disabled){
			dayDDL.selectedIndex = days_in_month;
		}
    }else if(month == ""){
		for(i = 1; i <= 31; i++){
			dayDDL.options[i].disabled = false;
		}      
    }   
}

// remove leading zero
function trimNumber(s) {
    while (s.substr(0,1) == '0' && s.length>1) { s = s.substr(1,9999); }
    return s;
}

// handle <TR> mouseover event
function __mgTrOnMouseOver(el, dir){
	var dir_ = (dir != null) ? dir : "right";
	if(el.firstChild.style){
		if(dir_ == "left") el.firstChild.style.borderLeft = "1px dotted #d1d1d1";		
		else el.firstChild.style.borderRight = "1px dotted #d1d1d1";		
	}
	el.style.color = "#000000";		
}

// handle <TR> mouseout event
function __mgTrOnMouseOut(el, dir){
	var dir_ = (dir != null) ? dir : "right";
	if(el.firstChild.style){
		if(dir_ == "left") el.firstChild.style.borderLeft = "0px dotted #cccccc";
		else el.firstChild.style.borderRight = "0px dotted #cccccc";
	}
	el.style.color = "#222222";
}

// generate and set username
function __mgGenerateRandom(gen_type, el){

	var length = (gen_type == 'password') ? '10' : '8';	
	var special = (gen_type == 'password') ? true : false;	
    var result = "";
	var i = 0;
    var randomNumber = "";
	
    while(i < length){
        randomNumber = (Math.floor((Math.random() * 100)) % 94) + 33;
		if(randomNumber == 32 || randomNumber == 34 || randomNumber == 39 || randomNumber == 96 || randomNumber > 126) { continue; }
        if(!special){
            if((randomNumber >=33) && (randomNumber <=47)) { continue; }
            if((randomNumber >=58) && (randomNumber <=64)) { continue; }
            if((randomNumber >=91) && (randomNumber <=96)) { continue; }
            if((randomNumber >=123) && (randomNumber <=126)) { continue; }
        }
		i++;
        result += String.fromCharCode(randomNumber);
    }
	if(gen_type == 'password'){
		document.getElementById('random-password-div').style.display = '';
		document.getElementById(el).innerHTML = result;		
	}else{
		document.getElementById(el).value = result.toLowerCase();		
	}
}

function __mgUseThisPassword(el){
	document.getElementById(el).value = document.getElementById('random-password').innerHTML;
	document.getElementById('random-password').innerHTML = "";
	document.getElementById('random-password-div').style.display = "none";	
}

