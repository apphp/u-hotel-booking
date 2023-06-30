<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site https://www.hotel-booking-script.com
* @license http://hotel-booking-script.com/license.php
*/

// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------

if(Modules::IsModuleInstalled('booking')){
	if(ModulesSettings::Get('booking', 'is_active') == 'global' ||
	   ModulesSettings::Get('booking', 'is_active') == 'front-end' ||
	  (ModulesSettings::Get('booking', 'is_active') == 'back-end' && $objLogin->IsLoggedInAsAdmin())	
	){
		
		if($payment_type == 'bank.transfer'){
			$title_desc = _BANK_TRANSFER;
		}elseif($payment_type == 'paypal'){
			$title_desc = _PAYPAL_ORDER;
		}elseif($payment_type == '2co'){
			$title_desc = _2CO_ORDER;
		}elseif($payment_type == 'authorize.net'){
			$title_desc = _AUTHORIZE_NET_ORDER;
		}elseif($payment_type == 'poa'){
			$title_desc = _PAY_ON_ARRIVAL;
		}elseif($payment_type == 'account.balance'){
			$title_desc = _PAY_WITH_BALANCE;
		}else{			
			$title_desc = _ONLINE_ORDER;
		}
				
		draw_title_bar(prepare_breadcrumbs(array(_BOOKINGS=>'',$title_desc=>'')));
		
		draw_content_start();
		draw_reservation_bar('payment');

		// test mode alert
		if($booking_mode == 'TEST MODE'){
			draw_message(_TEST_MODE_ALERT_SHORT, true, true);
		}        		
		
		echo $booking_payment_output;
		draw_content_end();
		
	}else{
		draw_title_bar(_BOOKINGS);
		draw_important_message(_NOT_AUTHORIZED);
	}	
}else{
	draw_title_bar(_BOOKINGS);
    draw_important_message(_NOT_AUTHORIZED);
}

