<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site http://www.hotel-booking-script.com
* @license http://hotel-booking-script.com/license.php
*/

// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------
if(Modules::IsModuleInstalled('tours_and_attractions') && ModulesSettings::Get('tours_and_attractions', 'is_active') == 'yes'){

	draw_title_bar(prepare_breadcrumbs(array(_TOURS_AND_ATTRACTIONS=>'',_PAYPAL_ORDER=>'')));
	
	draw_content_start();

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

