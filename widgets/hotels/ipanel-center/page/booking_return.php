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

if(Modules::IsModuleInstalled('booking')){
	if(ModulesSettings::Get('booking', 'is_active') == 'global' ||
	   ModulesSettings::Get('booking', 'is_active') == 'front-end' ||
	  (ModulesSettings::Get('booking', 'is_active') == 'back-end' && $objLogin->IsLoggedInAsAdmin())	
	){
        echo '<h4>'._BOOKING_COMPLETED.'</h4>';
        echo '<hr/>';
        echo '<div class="center_box_content" id="block-result">';

		$objReservation = new Reservation();
		$objReservation->EmptyCart();

		draw_success_message(_BOOKING_WAS_COMPLETED_MSG);

        echo '</div>';
	}else{
        echo '<h4>'._BOOKINGS.'</h4>';
        echo '<hr/>';
        echo '<div class="center_box_content" id="block-result">';
		draw_important_message(_NOT_AUTHORIZED);
        echo '</div>';
	}	
}else{
    echo '<h4>'._BOOKINGS.'</h4>';
    echo '<hr/>';
    echo '<div class="center_box_content" id="block-result">';
    draw_important_message(_NOT_AUTHORIZED);
    echo '</div>';
}
