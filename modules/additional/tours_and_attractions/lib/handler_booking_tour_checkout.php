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
	//--------------------------------------------------------------------------
	// *** redirect if reservation cart is empty
	if($objToursReservations->IsCartEmpty()){
		redirect_to('index.php?page=book_now_car', '', '<p>if your browser doesn\'t support redirection please click <a href="index.php?page=book_now_car">here</a>.</p>');
	}
}

