/**
 *   set focus on selected form element
 */

function appSetFocus(el_id){
    if(document.getElementById(el_id)){
        document.getElementById(el_id).focus();
    }    
}

/**
 *   Change location (go to another page)
 */
function appGoTo(page, params){
	var params_ = (params != null) ? params : "";
    window.location.href = 'index.php?'+page + params_;
}

/**
 *   Change location (go to another page)
 */
function appGoToPage(page, params, method){
	var params_ = (params != null) ? params : "";
	var method_ = (method != null) ? method : "";
	
	if(method_ == "post"){		
		var m_form = document.createElement('form');
			m_form.setAttribute('id', 'frmTemp');
			m_form.setAttribute('action', page);
			m_form.setAttribute('method', 'post');
		document.body.appendChild(m_form);
		
		params_ = params_.replace("?", "");
		var vars = params_.split("&");
		var pair = "";
		for(var i=0;i<vars.length;i++) { 
			pair = vars[i].split("="); 
			var input = document.createElement('input');
				input.setAttribute('type', 'hidden');
				input.setAttribute('name', pair[0]);
				input.setAttribute('id', pair[0]);
				input.setAttribute('value', unescape(pair[1]));
			document.getElementById('frmTemp').appendChild(input);
		}
		document.getElementById('frmTemp').submit();
	}else{
		window.location.href = page + params_;		
	}
}

/**
 *   Change location (go to current page)
 */
function appGoToCurrent(page, params){
	var page_  = (page != null) ? page : "index.php";
	var params_ = (params != null) ? params : "";
    window.location.href = page_ + params_;
}

/**
 *   Change location (go to current page)
 */
function appSetNewCurrency(page, params){
	var page_   = (page != null) ? page : "index.php";
	var params_ = (params != null) ? params : "";
	window.location.href = page_.replace("__CUR__", params_);	
}

/**
 *   Open popup window
 */
function appOpenPopup(page){
	new_window = window.open(page, "blank", "location=1,status=1,scrollbars=1,width=400,height=300");
    new_window.moveTo(100,100);
	if(window.focus) new_window.focus();
}

/**
 *   set cookie
 */
function appSetCookie(name, value, days) {
    if (days){
        var date = new Date();
        date.setTime(date.getTime()+(days*24*60*60*1000));
        var expires = '; expires='+date.toGMTString();
    }
    else var expires = '';
    document.cookie = name+'='+value+expires+'; path=/';
}

/**
 *   get cookie
 */
function appGetCookie(name) {
	if (document.cookie.length > 0){
		start_c = document.cookie.indexOf(name + "=");
		if(start_c != -1){
			start_c += (name.length + 1);
			end_c = document.cookie.indexOf(";", start_c);
			if(end_c == -1) end_c = document.cookie.length;
			return unescape(document.cookie.substring(start_c,end_c));
		}
	}	
	return "";
}

/**
 *   get menu status
 */
function appGetMenuStatus(ind){
	var status = document.getElementById("side_box_content_"+ind).style.display;
	if(status == "none"){			
		return "none";
	}else{
		return "";
	}
}

/**
 *   toggle viewing of element
 */
function appToggleElementView(current_val, target_val, el, status1, status2){
	var status1 = (status1 != null) ? status1 : "none";
	var status2 = (status2 != null) ? status2 : "";
    if(!document.getElementById(el)){
		return false;
	}else{	
        if(current_val == target_val) document.getElementById(el).style.display = status1;
		else document.getElementById(el).style.display = status2;
    }  
}

/**
 *   toggle rss
 */
function appToggleRss(val){
	if(val == 1){
		if(document.getElementById("rss_feed_type")){
			document.getElementById("rss_feed_type").disabled = false;
		}
	}else{
		if(document.getElementById("rss_feed_type")){
			document.getElementById("rss_feed_type").disabled = true;
		}
	}
}

/**
 *   email validation
 */
function appIsEmail(str){
	var at="@";
	var dot=".";
	var lat=str.indexOf(at);
	var lstr=str.length;
	var ldot=str.indexOf(dot);
	if(str.indexOf(at)==-1) return false; 

	if(str.indexOf(at)==-1 || str.indexOf(at)==0 || str.indexOf(at)==lstr) return false;
	if(str.indexOf(dot)==-1 || str.indexOf(dot)==0 || str.indexOf(dot)==lstr) return false;
	if(str.indexOf(at,(lat+1))!=-1) return false;
	if(str.substring(lat-1,lat)==dot || str.substring(lat+1,lat+2)==dot) return false;
	if(str.indexOf(dot,(lat+2))==-1) return false;
	if(str.indexOf(" ")!=-1) return false;

 	return true;
}

/**
 *  submit site search
 */
function appPerformSearch(page, kwd){
	if(kwd != null) document.forms['frmQuickSearch'].keyword.value = kwd;
	document.forms['frmQuickSearch'].p.value = page;
	document.forms['frmQuickSearch'].submit();
}

/**
 *  submit coupon
 */
function appSubmitCoupon(status){
	jQuery('#checkout-form input[name=submition_type]').val(status);
	jQuery('#checkout-form').attr('action', 'index.php?page=booking_checkout');
	jQuery('#checkout-form').submit();
}

/**
 *   toggle element
 */
function appToggleElement(key){
	jQuery("#"+key).toggle("fast");
}

/**
 *   hide element
 */
function appHideElement(key){	
	if(key.indexOf('#') !=-1 || key.indexOf('.') !=-1){
		jQuery(key).hide('fast');
	}else{
		jQuery('#'+key).hide('fast');
	}		
}

/**
 *   show element
 */
function appShowElement(key){	
	if(key.indexOf('#') !=-1 || key.indexOf('.') !=-1){
		jQuery(key).show('fast');	
	}else{
		jQuery('#'+key).show('fast');	
	}		
}

/**
 *  toggle by jQuery
 */
function appToggleJQuery(el){
	jQuery('.'+el).toggle("fast");
}

/**
 *  toggle by class
 */
function appToggleByClass(el){
	jQuery('.'+el).toggle("fast");
}

/**
 *  submit form 
 */
function appFormSubmit(frm_name_id, vars){
	if(document.getElementById(frm_name_id)){
		if(vars != null){
			var vars_pairs = vars.split('&');
			var pair = '';
			for(var i=0; i<vars_pairs.length; i++){ 
				pair = vars_pairs[i].split('=');
				for(var j=0; j<pair.length; j+=2) {
					if(document.getElementById(pair[j])) document.getElementById(pair[j]).value = pair[j+1];
				}				
			}
		}	
		document.getElementById(frm_name_id).submit();					
	}									
}

/**
 *  submit form and correction action
 */
function appEditActionFormSubmit(frm_name_id, url_str){
	if(document.getElementById(frm_name_id)){
		if(url_str != null){
            var statpart = document.getElementById(frm_name_id).action;
			document.getElementById(frm_name_id).action = statpart + '&' + url_str;
		}	
		document.getElementById(frm_name_id).submit();					
	}									
    return false;
}

/**
 *  submit site quick search
 */
function appQuickSearch(){
	var keyword = document.frmQuickSearch.keyword.value;
	if(keyword == '' || keyword.indexOf("...") != -1){
		return false;
	}else{
		document.frmQuickSearch.submit();
		return true;
	}
}

/////////////////////////////////////////////////////////////////////////////

/**
 *   toggle tabs
 */
function appToggleTabs(key, all_keys){
	jQuery("#content"+key).show("fast");
	jQuery("#tab"+key).attr("style", "font-weight:bold");
	
	for(var i = 0; i < all_keys.length; i++) {
		if(all_keys[i] != key){
			jQuery("#content"+all_keys[i]).hide("fast");
			jQuery("#tab"+all_keys[i]).attr("style", "font-weight:normal");
		}
	} 	
}

/**
 *   toggle readonly state of element
 */
function appToggleElementReadonly(current_val, target_val, el, target_status, default_status, is_readonly){
	var target_status = (target_status != null) ? target_status : false;
	var default_status = (default_status != null) ? default_status : false;
	var is_readonly = (is_readonly != null) ? is_readonly : true;
    if(!document.getElementById(el)){
		return false;
	}else{
		//alert(current_val +"=="+ target_val+target_status);
		
		//alert(document.getElementById(el).readOnly);
		if(is_readonly){
			if(current_val == target_val) document.getElementById(el).readOnly = target_status;
			else document.getElementById(el).readOnly = default_status;
		}else{
			if(current_val == target_val) document.getElementById(el).disabled = target_status;
			else document.getElementById(el).disabled = default_status;
		}
    }  
}

//--------------------------------------------------------------------------
// invoice preview (used for admin and client)
function appPreview(mode){
	var template_file = "";
	var div_id = "";
	var caption = "";
	var css_style = "";
	
	if(mode == "invoice"){
		template_file = "invoice.tpl.html";
		div_id = "divInvoiceContent";
		caption = "INVOICE";
	}else if(mode == "booking"){
		template_file = "description.tpl.html";
		div_id = "divDescriptionContent";
		caption = "BOOKING";		
	}else if(mode == "description"){
		template_file = "description.tpl.html";
		div_id = "divDescriptionContent";
		caption = "DESCRIPTION";
	}

	var new_window = window.open('html/templates/'+template_file,'blank','location=0,status=0,toolbar=0,height=480,width=680,scrollbars=yes,resizable=1,screenX=100,screenY=100');
	if(window.focus) new_window.focus();

	var message = document.getElementById(div_id).innerHTML;

	// remove html tags: <form>,<input>,<label>,
	message = message.replace(/<[//]{0,1}(FORM|INPUT|LABEL)[^><]*>/g,'');

	css_style = '<style>';
	css_style += 'TABLE.tblReservationDetails { border:1px solid #d1d2d3 }';
	css_style += 'TABLE.tblReservationDetails THEAD TR { background-color:#e1e2e3;font-weight:bold;font-size:13px; }';
	css_style += 'TABLE.tblReservationDetails TR TD SPAN { background-color:#e1e2e3; }';
	css_style += 'TABLE.tblExtrasDetails { border:1px solid #d1d2d3 }';
	css_style += 'TABLE.tblExtrasDetails THEAD TR { background-color:#e1e2e3;font-weight:bold;font-size:13px; }';
	css_style += 'TABLE.tblExtrasDetails TR TD SPAN { background-color:#e1e2e3; }';
	css_style += 'INPUT, SELECT, IMG { display:none; }';
    css_style += '@media print { .non-printable { display:none; } }	'; 
	css_style += '</style>';
	message = '<html><head>'+css_style+'</head><body><div class=\"non-printable\" style=\"width:99%;height:24px;margin:0px;padding:4px 5px;background-color:#e1e2e3;\"><div style=\"float:left;\">'+caption+'</div><div style=\"float:right;\">[ <a href=\"javascript:void(0);\" onclick=\"javascript:window.print();\">Print</a> ] [ <a href=\"javascript:void(0);\" onclick=\"javascript:window.close();\">Close</a> ]</div></div>' + message + '</body></html>';

	new_window.document.open();
	new_window.document.write(message);
	new_window.document.close();
}

/**
 *  Show Popup window
 */
function appPopupWindow(template_file, element_id, use_replacement){
	var element_id = (element_id != null) ? element_id : false;
	var use_replacement = (use_replacement != null) ? use_replacement : true;
	var new_window = window.open('html/'+template_file,'PopupWindow','height=500,width=600,toolbar=0,location=0,menubar=0,scrollbars=yes,screenX=100,screenY=100');
	if(window.focus) new_window.focus();
	if(element_id){
		var el = document.getElementById(element_id);		
		if(el.type == undefined){
			var message = el.innerHTML;	
		}else{
			var message = el.value;	
		}		
		if(use_replacement){
			var reg_x = /\n/gi;
			var replace_string = '<br> \n';
			message = message.replace(reg_x, replace_string);
		}
		new_window.document.open();
		new_window.document.write(message);
		new_window.document.close();
	}
}

function appShowTermsAndConditions(){
    document.getElementById('light').style.display='block';
    document.getElementById('fade').style.display='block';
}

function appCloseTermsAndConditions(){
    document.getElementById('light').style.display='none';
    document.getElementById('fade').style.display='none';
}

/**
 * Change properties
 */
function appChangeProperties(){
	jQuery('#hotel_sel_loc_id').val('');
	jQuery('#hotel_sel_id').empty();
}

/**
 * Change hotel location
 */
function appReloadHotels(val, pt_id, fill_el, token, dir, lang, empty_value){
	var dir_ = (dir != null) ? dir : '';
	var token_ = (token != null) ? token : '';
	var lang_ = (lang != null) ? lang : '';
	var empty_value_ = (empty_value != null) ? empty_value : '-- All --';

	jQuery.ajax({
		url: dir_+"ajax/hotels.ajax.php",
		global: false,
		type: "POST",
		data: ({location_id : val, property_type_id : pt_id, check_key : "apphphs", token : token_, lang : lang_}),
		dataType: "html",
		async:false,
		error: function(html){
			alert("AJAX: cannot connect to the server or server response error! Please try again later.");
		},
		success: function(html){
			var obj = jQuery.parseJSON(html), template_name;				
			if(obj[0].status == "1"){
				if(obj.length > 0){
					jQuery("#"+fill_el).empty();
					jQuery("<option />", {val: '', text: empty_value_}).appendTo("#"+fill_el);				
					for(var i = 1; i < obj.length; i++){
						template_name = obj[i].template_name;
						jQuery("<option />", {val: obj[i].id, html: template_name}).appendTo("#"+fill_el);				
					}
					jQuery("#"+fill_el).trigger("chosen:updated");
				}
			}else{
				//alert("An error occurred while receiving hotel data! Please try again later.");
			}
		}
	});	
}

/**
 * Change country
 */
function appChangeCountry(val, fill_el, fill_val, token, dir){
	var dir_ = (dir != null) ? dir : '';
	var token_ = (token != null) ? token : '';
	jQuery.ajax({
		url: dir_+"ajax/countries.ajax.php",
		global: false,
		type: "POST",
		data: ({country_code : val, check_key : "apphphs", token : token_}),
		dataType: "html",
		async:false,
		error: function(html){
			alert("AJAX: cannot connect to the server or server response error! Please try again later.");
		},
		success: function(html){
			var obj = jQuery.parseJSON(html);            			
			if(obj[0].status == "1"){
				if(obj.length > 0){					
					if(obj.length > 1){					
						jQuery("#"+fill_el).replaceWith('<select class="form-control mgrid_select" id="'+fill_el+'" name="'+fill_el+'"></select>');
						jQuery("#"+fill_el).empty(); 
						for(var i = 1; i < obj.length; i++){
							if(obj[i].abbrv == fill_val && fill_val != ''){
								jQuery("<option />", {val: obj[i].abbrv, text: obj[i].name, selected: true}).appendTo("#"+fill_el);					
							}else{
								jQuery("<option />", {val: obj[i].abbrv, text: obj[i].name}).appendTo("#"+fill_el);					
							}							
						}
					}else{
						jQuery("#"+fill_el).replaceWith('<input class="mgrid_text" type="text" id="'+fill_el+'" name="'+fill_el+'" size="32" maxlength="64" value="'+fill_val+'" />');
					}
				}
			}else{
				//alert("An error occurred while receiving hotel data! Please try again later.");
			}
		}
	});	
}

/**
 * Change vehicle type according to selected agency
 */
function appChangeCarAgency(val, fill_el, fill_val, token, dir){
	var dir_ = (dir != null) ? dir : '';
	var token_ = (token != null) ? token : '';
	jQuery.ajax({
		url: dir_+"ajax/car_agencies.ajax.php",
		global: false,
		type: "POST",
		data: ({agency_id : val, check_key : "apphphs", token : token_}),
		dataType: "html",
		async:false,
		error: function(html){
			alert("AJAX: cannot connect to the server or server response error! Please reload page and try again.");
		},
		success: function(html){
			var obj = jQuery.parseJSON(html);            			
			if(obj[0].status == "1"){
				if(obj.length > 0){					
					jQuery("#"+fill_el).replaceWith('<select class="form-control" id="'+fill_el+'" name="'+fill_el+'"></select>');
					jQuery("#"+fill_el).empty(); 
					for(var i = 1; i < obj.length; i++){
						if(obj[i].abbrv == fill_val && fill_val != ''){
							jQuery("<option />", {val: obj[i].abbrv, text: obj[i].name, selected: true}).appendTo("#"+fill_el);					
						}else{
							jQuery("<option />", {val: obj[i].abbrv, text: obj[i].name}).appendTo("#"+fill_el);					
						}							
					}
				}
			}else{
				jQuery("#"+fill_el).replaceWith('<select class="form-control" id="'+fill_el+'" name="'+fill_el+'"></select>');
				jQuery("#"+fill_el).empty(); 
				//alert("An error occurred while receiving hotel data! Please try again later.");
			}
		}
	});	
}

function appFavoriteItem(el, item_type, item_id, customer_id, token){
	var token_ = (token != null) ? token : '';
	var item_type_ = (item_type != null) ? item_type : '';
	var item_id_ = (item_id != null) ? item_id : '';
	var customer_id_ = (customer_id != null) ? customer_id : '';
	var action_ = jQuery(el).data('action');
	
	jQuery.ajax({
		url: 'ajax/favorite_items.ajax.php',
		global: false,
		type: "POST",
		data: ({item_type : item_type_, item_id : item_id_, customer_id : customer_id_, action : action_, token : token_}),
		dataType: "html",
		async:false,
		error: function(html){
			alert("AJAX: cannot connect to the server or server response error! Please try again later.");
		},
		success: function(html){
			var obj = jQuery.parseJSON(html);
			if(obj[0].status == "1"){
				jQuery(el).attr('title', '');
				if(action_ == 'add'){
					jQuery(el).find('span').removeClass('fav-icon-add').addClass('fav-icon-remove');
					jQuery(el).data('action', 'remove');
				}else{
					jQuery(el).find('span').removeClass('fav-icon-remove').addClass('fav-icon-add');
					jQuery(el).data('action', 'add');
				}
				myAlert(decodeEntities(obj[1].message));				
			}else{
				myAlert(decodeEntities(obj[1].message));
			}
		}
	});	
}

function decodeEntities(encodedString) {
    var textArea = document.createElement('textarea');
    jQuery(textArea).html(encodedString);
    return textArea.value;
}

/**
 * Scrolls page to given element
 * @param string id
*/
function scrollToElement(id, speed) {
	var speed_ = speed != null ? speed : 500;
    jQuery('html, body').animate({
        scrollTop: parseInt(jQuery('#'+id).offset().top)
    }, speed_);	
}


/**
 * Create and display messages
 * @param string message
 * @param string title
 */
function myAlert(message, title) {
    // validation input params
    if(message === null){
        console.error('empty message in function myAlert');
        return false;
    }

    var id = randString(10);

    // Create DOM
    // <p></p>
    var dialogP = jQuery("<p/>");

    // <p><span id="dialog-alert-image-..." class="..." style="..."></span></p>
    var dialogImage = jQuery("<span/>", {
        id: 'dialog-alert-image-' + id,
        class: 'ui-icon ui-icon-alert',
        css: {
            display: "none",
            float: "left",
            margin: "2px 5px 0px 0"
        }
    }).appendTo(dialogP);

    // <p><span id="dialog-alert-image-..." class="..." style="..."></span><span id="dialog-message-...">__message__</span></p>
    var dialogMessage = jQuery("<span/>", {
        id: 'dialog-message-' + id,
        text: message,
        css: {
            "font-size": "14px"
        }
    }).appendTo(dialogP);

    // <div id="dialog-alert-..." title="__title__">
    //    <p><span id="dialog-alert-image-..." class="..." style="..."></span><span id="dialog-message-...">__message__</span></p>
    // </div>
    var dialogBox = jQuery("<div/>",{
        id: 'dialog-alert-' + id,
        title: title,
        css: {
            display: "none"
        }
    }).append(dialogP);


    // Insert created DOM in html page
    jQuery("body").append(dialogBox);
    dialog = jQuery( "#dialog-alert-" + id ).dialog({
        autoOpen: true,
        buttons: {
            "Ok": function() {
                dialog.dialog("close");
                dialogBox.remove();
            }
        }
    });
}

/**
 * Random Alpha Numeric String
 * source - http://jsfiddle.net/greatbigmassive/PJwg8/
 * @param int x
 * */
function randString(x){
    var s = "";
    
    while(s.length<x&&x>0){
        var r = Math.random();
        s+= (r<0.1?Math.floor(r*100):String.fromCharCode(Math.floor(r*26) + (r>0.5?97:65)));
    }

    return s;
}

jQuery(document).ready(function(){
	var $ = jQuery;

	//------------------------------
	// Swipe handler
	//------------------------------
	$(".swipe-table")
		.on('swipeleft', function(){
			$(this).removeClass('swipe-table');
			$(this).find('.swipe-icon-wrapper').remove();
		})	
        .on('swiperight', function(){
			$(this).removeClass('swipe-table');
			$(this).find('.swipe-icon-wrapper').remove();
		})

	//------------------------------
	// Form Validation
	//------------------------------
	$('#reservation-form,#cars-reservation-form').find('[data-required]').on('keyup', function(){
		if($(this).val() != ''){
			$(this).parent().find('.error-message').remove();
		}
	});
	
	$('#reservation-form,#cars-reservation-form').on('submit', function(){
		var frm = $(this),
			is_error = false;
		
		$('.error-message').remove();
		frm.find('[data-required]').each(function() {
			if($(this).val() == '' && !is_error){
				$(this).focus();
				$(this).after('<div class="error-message">'+$(this).data('required-message')+'</div>');
				is_error = true;
			}
		});

		return is_error ? false : true;
	});

	/**
	 * Send Email Need Assistance
	 */
	$('#btn_need_assistance').on('click', function (el) {

		var token 		 = $('#frm_need_assistance').find('input[name=token]').val();
		var email_hotels = $('#email_hotels').val();
		var name 		 = $('#name').val();
		var message 	 = $('#message').val();

		if($(el).attr('disabled')){
			return false;
		}

		$(el).attr('disabled', 'disabled');

		$.ajax({
			url: "ajax/need_assistance.ajax.php",
			global: false,
			type: "POST",
			data: ({
				email_hotels : email_hotels ,
				name  		 : name ,
				message 	 : message,
				check_key	 : "apphphs",
				token	 	 : token
			}),
			dataType: "html",
			async:false,
			error: function(html){
				alert("AJAX: cannot connect to the server or server response error! Please try again later.");
			},
			success: function(html){
				var obj = $.parseJSON(html);
				if(obj[0].status == "1"){
					$('#frm_need_assistance').hide();
					$('#message_need_assistance_error').hide();
					$('#message_need_assistance_success').show();
				}else{
					if(obj[0].status == "maxCountEmail") {
						alert(obj[1].error);
					}else if(obj[0].status == "emptyName"){
						$('#name').focus();
						$('#message_need_assistance_error').show();
						$('#message_need_assistance_error p').text(obj[1].error);
					}else if(obj[0].status == "emptyMessage"){
						$('#message_need_assistance_error').show();
						$('#message_need_assistance_error p').text(obj[1].error);
						$('#message').focus();
					}
					$(el).removeAttr('disabled');
				}
			}
		});

	});

	$('#frm_need_assistance').keydown(function(event){
        if(event.keyCode == 13) {
            event.preventDefault();
            return false;
        }
    });
});

//------------------------------
// Mobile Filters
//------------------------------
function toggleNav() {
	var width = jQuery('#side-nav').css('width');
	if(width == '0' || width == '0px') {
        jQuery('#side-nav').css('width', '270px');
        jQuery('#content-main').addClass('filter-popover');
	}else{
        jQuery('#side-nav').css('width', '0');
        jQuery('#content-main').removeClass('filter-popover');
	}
}

function closeNav() {
    jQuery('#side-nav').css('width', '0');
    jQuery('#content-main').removeClass('filter-popover');
}

function goBack() {
    window.history.back();
}

// Allow to show UI tooltips as HTML
$(function() {
	// Allow to show UI tooltips as HTML
	jQuery( document ).tooltip({
		content:function(){
			return this.getAttribute("title");
		}
	});
});

function checkAvailabilitySearchSuggestion(el, token, rtl){
	var dialog = null, table, key;
    var objButton = el;
    var room_id = $(el).data("room-id");
    var type = $(el).data("type");

    if($(objButton).hasClass("ajax-working") || $(objButton).attr('disabled')){
        return false;
    }

    var arr_forms = $(".suggestion-wrapper").find("form").toArray();

    for(i = 0, max_count_forms = arr_forms.length; i < max_count_forms; i++){

        if($(arr_forms[i]).find("input")['length'] > 0){
            var arr_input = $(arr_forms[i]).find("input").toArray();
            arr_input = arr_input.concat($(arr_forms[i]).find("select").toArray());
        }else{
            var arr_input = $(arr_forms[i]).parents("tr").find("input").toArray();
            arr_input = arr_input.concat($(arr_forms[i]).parents().find("select").toArray());
        }


        var data = {};
        for(j = 0, max_count_inputs = arr_input.length; j < max_count_inputs; j++){
            val = $(arr_input[j]).val();
            key = $(arr_input[j]).attr("name");
            if(key != undefined){
                data[key] = val;
            }
        }

        data.act = "send";
        data.type = type;

        $(objButton).addClass("ajax-working");
        jQuery.ajax({
            url: "ajax/reserve_room.ajax.php",
            global: false,
            type: "POST",
            data: (data),
            dataType: "json",
            async: true,
            beforeSend: function(){ $(objButton).addClass("ajax-working"); },
            complete: function(){ $(objButton).removeClass("ajax-working"); },
            error: function(html){ console.log("AJAX: cannot connect to the server or server response error! Please try again later."); },
            success: function(data){
                $(objButton).removeClass("ajax-working");
                $(objButton).attr('disabled','disabled');
                var error = data.error !== undefined ? data.error : false;
                var message = data.message;
                if(data.toastr_message != undefined){
                    if(typeof toastr != "undefined"){
                        toastr.remove();
                    }
                    if(data.toastr_message != ""){
                        jQuery(document).ready(function(){
                            if(typeof toastr != "undefined"){
                                // Init
                                toastr.options = {
                                    "closeButton": true,
                                    "debug": false,
                                    "newestOnTop": false,
                                    "progressBar": false,
                                    "positionClass": "toast-bottom-left",
                                    "preventDuplicates": false,
                                    "onclick": null,
                                    "showDuration": "300",
                                    "hideDuration": "1000",
                                    "timeOut": "0",
                                    "extendedTimeOut": "0",
                                    "showEasing": "swing",
                                    "hideEasing": "linear",
                                    "showMethod": "fadeIn",
                                    "hideMethod": "fadeOut",
                                    "rtl": rtl
                                }

                                toastr.success(data.toastr_message);
                            }
                        });
                    }
                }

                if(message.length > 0){
                    // dialog
                    // if(error == false){
                    //     if(type == "reserve"){
                    //         $(objButton).removeClass("green");
                    //         $(objButton).addClass("red");
                    //         $(objButton).data("type", "remove");
                    //     }else{
                    //         $(objButton).removeClass("red");
                    //         $(objButton).addClass("green");
                    //         $(objButton).data("type", "reserve");
                    //     }
                    // }
                    $("#dialog-message").html(message);
                    dialog = jQuery( "#dialog-alert" ).dialog({
                        autoOpen: true,
                        width: 500,
                        buttons: {
                            "Continue Reservation": function(){//'.str_replace('"', '\\"', htmlspecialchars(_CONTINUE_RESERVATION)).'
                        window.location.href = "index.php?page=booking";
                    },
                    "Ok": function() {
                        dialog.dialog("close");
                        if(type == "reserve" && typeof toastr != "undefined"){
                            if(data.toastr_message != undefined){
                                // Init
                                toastr.options = {
                                    "closeButton": true,
                                    "debug": false,
                                    "newestOnTop": false,
                                    "progressBar": false,
                                    "positionClass": "toast-bottom-right",
                                    "preventDuplicates": false,
                                    "onclick": null,
                                    "showDuration": "300",
                                    "hideDuration": "1000",
                                    "timeOut": "0",
                                    "extendedTimeOut": "0",
                                    "showEasing": "swing",
                                    "hideEasing": "linear",
                                    "showMethod": "fadeIn",
                                    "hideMethod": "fadeOut",
                                    "rtl": rtl
                                }
                                toastr.success("You can go to the order by clicking the \"booking\" link in the footer menu");//'.str_replace('"', '\\"', _MESSAGE_INFO_LINK_BOOKING).'
                            }
                        }
                    }
                },
                    open: function() {
                        $(".ui-dialog-buttonpane").find("button:contains(\"Ok\")").css("float", "right");
                        $(".ui-dialog-buttonset").css({"float": "left", "margin-left": "5px", "width":"100%"});
                    }
                });
                }
            }
        });
    }

    return false;
}
