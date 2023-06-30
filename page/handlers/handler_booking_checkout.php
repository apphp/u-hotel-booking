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
		//--------------------------------------------------------------------------
		// *** redirect if reservation cart is empty or not correct reserved number of rooms
		if($objReservation->IsCartEmpty() || !$objReservation->IsCorrectReservedNumberRooms()){
			redirect_to('index.php?page=booking', '', '<p>if your browser doesn\'t support redirection please click <a href="index.php?page=booking">here</a>.</p>');
		}
	}
}

