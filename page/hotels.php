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

$hotel_id = isset($_GET['hid']) ? (int)$_GET['hid'] : '';

$show_hotel = false;
$all_hotels = Hotels::GetAllActive();
foreach($all_hotels[0] as $one_hotel){
    if($one_hotel['id'] == $hotel_id){
        $show_hotel = true;
        break;
    }
}

if($show_hotel){	
	Hotels::DrawHotelDescription($hotel_id); 
}else{
	draw_title_bar(FLATS_INSTEAD_OF_HOTELS ? _FLAT_DESCRIPTION : _HOTEL_DESCRIPTION);
	draw_important_message(_WRONG_PARAMETER_PASSED);		
}
	
