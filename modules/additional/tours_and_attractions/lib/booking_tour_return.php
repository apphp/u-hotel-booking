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
	$objToursReservations = new ToursReservations();
	$objToursReservations->EmptyCart();

	draw_title_bar(prepare_breadcrumbs(array(_TOURS_AND_ATTRACTIONS=>'',_TOUR_BOOKING_COMPLETED=>'')));
	
	draw_content_start();
	draw_success_message(_TOUR_WAS_COMPLETED_MSG);
	draw_content_end();		
}else{
	draw_title_bar(_TOURS_AND_ATTRACTIONS);
    draw_important_message(_NOT_AUTHORIZED);
}
