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

if(Modules::IsModuleInstalled('car_rental') && ModulesSettings::Get('car_rental', 'is_active') == 'yes'){
	$objCarReservations = new CarReservations();
	$objCarReservations->EmptyCart();

	draw_title_bar(prepare_breadcrumbs(array(_CARS=>'',_CAR_BOOKING_COMPLETED=>'')));
	
	draw_content_start();
	draw_success_message(_CAR_WAS_COMPLETED_MSG);
	draw_content_end();		
}else{
	draw_title_bar(_CARS);
    draw_important_message(_NOT_AUTHORIZED);
}
