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

if(Modules::IsModuleInstalled('tours_and_attractions')){
	if(ModulesSettings::Get('tours_and_attractions', 'is_active') == 'yes'){
		draw_title_bar(prepare_breadcrumbs(array(_BOOKINGS=>'',_BOOKING_CANCELED=>'')));		
		draw_content_start();
		draw_message(_BOOKING_WAS_CANCELED_MSG, true, true);
		draw_content_end();		
	}else{
		draw_title_bar(_BOOKINGS);
		draw_important_message(_NOT_AUTHORIZED);
	}	
}else{
	draw_title_bar(_BOOKINGS);
    draw_important_message(_NOT_AUTHORIZED);
}
