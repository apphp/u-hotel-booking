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
	//--------------------------------------------------------------------------
	// *** redirect if reservation cart is empty
	if($objCarReservations->IsCartEmpty()){
		redirect_to('index.php?page=book_now_car', '', '<p>if your browser doesn\'t support redirection please click <a href="index.php?page=book_now_car">here</a>.</p>');
	}
}

