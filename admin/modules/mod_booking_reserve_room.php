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

if($objLogin->IsLoggedInAs('owner','mainadmin','admin','hotelowner') &&
   Modules::IsModuleInstalled('booking') &&
   in_array(ModulesSettings::Get('booking', 'is_active'), array('global', 'back-end')) 
){
	// Start main content
	draw_title_bar(prepare_breadcrumbs(array(_BOOKINGS=>'',_BOOKINGS_MANAGEMENT=>'',_RESERVATION=>'')));

	draw_content_start();
	$allow_viewing = true;
	if($objLogin->IsLoggedInAs('hotelowner')){
		$hotels_list = implode(',', $objLogin->AssignedToHotels());
		if(empty($hotels_list)){
			$allow_viewing = false;
			echo draw_important_message(_OWNER_NOT_ASSIGNED, false);
		}
	}
	if($allow_viewing){
        echo '<input class="mgrid_button" type="button" name="btnAddNew" value="'._RESERVATION_CART.'" onclick="javascript:appGoTo(\'page=booking\');"></a> &nbsp;';
        echo '<input class="mgrid_button" type="button" name="btnAddNew" value="'._CHECKOUT.'" onclick="javascript:appGoTo(\'page=booking_checkout\');"></a> <br /><br />';
	
        // Draw availability calendar for admins
        Rooms::DrawSearchAvailabilityBlock(true, '', '', 8, 3, 'main-vertical', '', '', true, true, true, '', true);
        draw_content_end();	
        
        Rooms::DrawSearchAvailabilityFooter();
    }else{
        draw_content_end();	
    }
	
}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

