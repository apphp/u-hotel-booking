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
	if(jQuery('#reservation_vat')){
		jQuery('#reservation_vat').html(currency_symbol+appSetDecimalPoint(vat_sum.toFixed(2)));
		jQuery('#reservation_vat').animate({opacity: 0.25}, 70).animate({opacity: 1}, 70);
	}

	jQuery('#reservation_total').html(currency_symbol+appSetDecimalPoint(cart_total_sum.toFixed(2)));
	jQuery('#reservation_total').animate({opacity: 0.25}, 70).animate({opacity: 1}, 70);
}

/**
 *   Go to Booking Details page
 */
function appGoToBookingDetails(url, self) {
    var is_valid = true,
        go_to_url = url != null ? url : '',
        frm = $(self).closest('form');

    jQuery('.from_date').each(function() {
        if($(this).val() != $(this).data('value')){
            is_valid = false;
            return true;
        }
    });

    jQuery('.to_date').each(function() {
        if($(this).val() != $(this).data('value')){
            is_valid = false;
            return true;
        }
    });

    if(is_valid){
        if(go_to_url){
            appGoTo(go_to_url);
        }else{
            frm.submit();
        }
    }else{
        $("#dialog-alert").dialog({
            resizable: false,
            height: "auto",
            width: 400,
            modal: true,
            buttons: {
                "Proceed without changes": function() {
                    $(this).dialog("close");
                    if(go_to_url){
                        appGoTo(go_to_url);
                    }else{
                        frm.submit();
                    }
                },
                "OK. I will do": function() {
                    $(this).dialog("close");
                }
            }
        });
    }
}
