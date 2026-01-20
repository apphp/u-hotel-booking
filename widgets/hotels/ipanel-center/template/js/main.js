jQuery(document).ready(function(){
    var $ = jQuery;

    // Allow to show UI tooltips as HTML
    $(function() {
        $( document ).tooltip({
            content:function(){
                return this.getAttribute("title");
            }
        });
    });

    $('form').each(function(indx){
        var action = $(this).attr('action');

        if(action.indexOf('?') != -1){
            action = action + '&' + hash;
        }else{
            action = action + '?' + hash;
        }
        $(this).attr('action', action);
    })

    $("#main-container").on("click", ".reservationcartdiv", function(){
        if($(this).hasClass("margin-right-zero")){
            $(this).removeClass("margin-right-zero");
        }else{
            $(this).addClass("margin-right-zero");
        }
    });
    
    $("#main-container").on("click", ".widget-link", function(){
        var link = $(this).attr('href');
        if(link.indexOf('?') != -1){
            link = link + '&' + hash;
        }else{
            link = link + '?' + hash;
        }
        $(location).attr('href', link);

        return false;
    });

    $("#main-container").on('click', ".destroyeachroomcart", function(){
        var token = $(this).data('token');
        var message_reserve = $(this).data("message-reserve");
        var rtl = $(this).data("rtl");
        var room_id = $(this).data("id");
        var objButton = $('.form_button_middle[data-room-id=' + room_id + ']');
        var type = 'remove';
        var data = {};

        data.act = "send";
        data.token = token;
        data.room_id = room_id;
        data.type = type;
        data.is_widget = 1;

        if(objButton.hasClass('ajax-working')){
            return false;
        }

        objButton.addClass('ajax-working');

        jQuery.ajax({
            url: host + "/ajax/reserve_room.ajax.php",
            global: false,
            type: "POST",
            data: (data),
            dataType: "json",
            async: true,
            error: function(html){
                console.log("AJAX: cannot connect to the server or server response error! Please try again later.");
            },
            success: function(data){
                objButton.removeClass('ajax-working');
                var error = data.error !== undefined ? data.error : false;
                var message = data.message;
                if(data.widget_html != undefined){
                    $(".reservationcartdiv").replaceWith(data.widget_html);
                    $(".reservationcartdiv").addClass("margin-right-zero");
                }
                if(message.length > 0){
                    // dialog
                    objButton.removeClass("red");
                    objButton.addClass("green");
                    objButton.data("type", "reserve");
                    objButton.val(message_reserve);

                    toastr.remove();
                    if(data.message != ""){
                        // Init
                        toastr.options = {
                          "closeButton": true,
                          "debug": false,
                          "newestOnTop": false,
                          "progressBar": false,
                          "positionClass": "toast-top-right",
                          "preventDuplicates": false,
                          "onclick": null,
                          "showDuration": "300",
                          "hideDuration": "1000",
                          "timeOut": "3000",
                          "extendedTimeOut": "0",
                          "showEasing": "swing",
                          "hideEasing": "linear",
                          "showMethod": "fadeIn",
                          "hideMethod": "fadeOut",
                          "rtl": rtl
                        }

                        if(error){
                            toastr.error(data.message);
                        }else{
                            type == "remove" ? toastr.warning(data.message) : toastr.success(data.message);
                        }
                    }
                }
            }
        });
        return false;
    });

    $("#main-container").on('change', ".available_rooms_ddl, .available_meals_ddl", function(){
		var form = $(this).closest('form');
        var token = form.find("input[name=token]").val();
        var room_id = form.find("input[name=room_id]").val()
        var hotel_id = form.find("input[name=hotel_id]").val()
        var from_date = form.find("input[name=from_date]").val()
        var to_date = form.find("input[name=to_date]").val()
        var nights = form.find("input[name=nights]").val()
        var adults = form.find("input[name=adults]").val()
        var children = form.find("input[name=children]").val()
        var checkin_year_month = form.find("input[name=checkin_year_month]").val()
        var checkin_monthday = form.find("input[name=checkin_monthday]").val()
        var checkout_year_month = form.find("input[name=checkout_year_month]").val()
        var checkout_monthday = form.find("input[name=checkout_monthday]").val()
        var available_rooms = form.find("select[name=available_rooms]").val()
        var meal_plans = form.find("select[name=meal_plans]").val()
        var objButton = $('.form_button_middle[data-room-id=' + room_id + ']');
        var message_remove = objButton.data("value-remove");
        var type_button = objButton.data("type");
		var type = 'reserve';
        var data = {};

		if(type_button == 'reserve'){
			return null;
		}

        if(objButton.hasClass('ajax-working')){
            return false;
        }

        objButton.addClass('ajax-working');

        data.act = "send";
        data.token = token;
		data.hotel_id = hotel_id,
        data.from_date = from_date;
        data.to_date = to_date;
        data.room_id = room_id;
        data.nights = nights;
        data.adults = adults;
        data.children = children;
        data.checkin_year_month = checkin_year_month;
        data.checkin_monthday = checkin_monthday;
        data.checkout_year_month = checkout_year_month;
        data.checkout_monthday = checkout_monthday;
        data.available_rooms = available_rooms;
        data.meal_plans = meal_plans;
        data.type = type;
        data.is_widget = 1;

        jQuery.ajax({
            url: host + "/ajax/reserve_room.ajax.php",
            global: false,
            type: "POST",
            data: (data),
            dataType: "json",
            async: true,
            error: function(html){
                console.log("AJAX: cannot connect to the server or server response error! Please try again later.");
            },
            success: function(data){
                objButton.removeClass('ajax-working');
                var error = data.error !== undefined ? data.error : false;
                var message = data.message;
                if(data.widget_html != undefined){
                    $(".reservationcartdiv").replaceWith(data.widget_html);
                    $(".reservationcartdiv").addClass("margin-right-zero");
                }
            }
        });
        return false;
    });
});

/**
 *   Change location (go to current page)
 */
function appSetNewCurrency(page, params){
	var page_   = (page != null) ? page : "index.php";
	var params_ = (params != null) ? params : "";
	window.location.href = page_.replace("__CUR__", params_);	
}

/**
 *   set focus on selected form element
 */

function appSetFocus(el_id){
    if(document.getElementById(el_id)){
        document.getElementById(el_id).focus();
    }    
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
 *   Update total sum of shopping cart
 */	
function appUpdateTotalSum(ind, num, total_extras){
	arrExtrasSelected[ind] = num;
	
	var booking_initial_fee = jQuery('#hid_booking_initial_fee').val();
	var vat_percent = jQuery('#hid_vat_percent').val();
	var vat_sum = 0;
	var guest_tax = jQuery('#guest_tax').data('price');
	var order_price = jQuery('#hid_order_price').val();
	var currency_symbol = jQuery('#hid_currency_symbol').val();
	var extras_total_sum = 0;
	var cart_total_sum = 0;

	// Fix for empty values
    guest_tax   = guest_tax   == null ? 0 : guest_tax;
    vat_percent = vat_percent == null ? 0 : vat_percent;
    order_price = order_price == null ? 0 : order_price;
	
	for(i=0; i < total_extras; i++){
		extras_total_sum += (arrExtrasSelected[i] * arrExtras[i]);
	}
	
	vat_sum = parseFloat((parseFloat(order_price) + parseFloat(booking_initial_fee) + parseFloat(guest_tax) + parseFloat(extras_total_sum)) * parseFloat(vat_percent / 100));
	cart_total_sum = parseFloat((parseFloat(order_price) + parseFloat(booking_initial_fee) + parseFloat(guest_tax) + parseFloat(extras_total_sum)) + parseFloat(vat_sum));
	
	// show effect on changed text
	if(jQuery("#reservation_vat")){
		jQuery("#reservation_vat").html(currency_symbol+appSetDecimalPoint(vat_sum.toFixed(2)));
		jQuery("#reservation_vat").animate({opacity: 0.25}, 70).animate({opacity: 1}, 70);  
	}

	jQuery("#reservation_total").html(currency_symbol+appSetDecimalPoint(cart_total_sum.toFixed(2)));
	jQuery("#reservation_total").animate({opacity: 0.25}, 70).animate({opacity: 1}, 70);  	
}

/**
 *   Set decimal point
 */	
function appSetDecimalPoint(num){
	var num_ = (num != null) ? num.toString() : "";
	var currency_format = document.getElementById("hid_currency_format").value;
	
	if(currency_format == 'european'){
		num_ = num_.replace(".", ",");
		return num_;
	}
	return num_;
}

/**
 *   toggle element
 */
function appToggleElement(key){
	jQuery("#"+key).toggle("fast");
}

/**
 *   Change location (go to another page)
 */
function appGoTo(page, params){
	var params_ = (params != null) ? params : "";
    window.location.href = homeurl + 'index.php?'+page + params_ + '&' + hash;
}

/**
 *  submit coupon
 */
function appSubmitCoupon(status){
	jQuery('#checkout-form input[name=submition_type]').val(status);
	jQuery('#checkout-form').attr('action', homeurl + 'index.php?page=booking_checkout&' + hash);
	jQuery('#checkout-form').submit();
}

/**
 *   Open popup window
 */
function appOpenPopup(page){
	new_window = window.open(page, "blank", "location=1,status=1,scrollbars=1,width=400,height=300");
    new_window.moveTo(100,100);
	if(window.focus) new_window.focus();
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
