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

		$objReservation = new Reservation();
		$objReservation->EmptyCart();

		draw_title_bar(prepare_breadcrumbs(array(_BOOKINGS=>'',_BOOKING_COMPLETED=>'')));
		
		draw_content_start();
		draw_success_message(_BOOKING_WAS_COMPLETED_MSG);
		draw_content_end();		
	}else{
		draw_title_bar(_BOOKINGS);
		draw_important_message(_NOT_AUTHORIZED);
	}	
}else{
	draw_title_bar(_BOOKINGS);
    draw_important_message(_NOT_AUTHORIZED);
}
