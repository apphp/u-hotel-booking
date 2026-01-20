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

if($objLogin->IsLoggedInAs('owner','mainadmin','hotelowner') && Modules::IsModuleInstalled('channel_manager')){

	$hid = isset($_REQUEST['hid']) ? (int)$_REQUEST['hid'] : '';
	$chid = isset($_REQUEST['chid']) ? (int)$_REQUEST['chid'] : '';
	$task = isset($_REQUEST['task']) ? prepare_input($_REQUEST['task']) : '';
	
	$objHotels = new Hotels();
	$hotel_info = $objHotels->GetHotelFullInfo($hid);

	if(!empty($hid) && $objLogin->AssignedToHotel($hid) && count($hotel_info) > 0){
		
		// Start main content
		draw_title_bar(
            (defined('FLATS_INSTEAD_OF_HOTELS') && FLATS_INSTEAD_OF_HOTELS
			    ? prepare_breadcrumbs(array(_FLAT_MANAGEMENT=>'',_FLATS_INFO=>'',$hotel_info['name']=>'',_CHANNEL_MANAGER=>'',_ROOMS=>'',ucfirst($task)=>''))
                : prepare_breadcrumbs(array(_HOTEL_MANAGEMENT=>'',_HOTELS_INFO=>'',$hotel_info['name']=>'',_CHANNEL_MANAGER=>'',_ROOMS=>'',ucfirst($task)=>''))
            ),
			prepare_permanent_link('index.php?admin=mod_channel_manager_hotels&hid='.$hid.'&chid='.$chid, _BUTTON_BACK)
		);
	
		$objChannelHotelRooms = new ChannelHotelRooms($hid, $chid);
		
		if($task == 'update'){
			if($objChannelHotelRooms->UpdateRooms()){
				draw_success_message(_UPDATING_OPERATION_COMPLETED);	
			}		
		}
		
		if(!empty($objChannelHotelRooms->error)){
			draw_important_message($objChannelHotelRooms->error);
		}
	
		if($hid > 0){
			draw_content_start();
			$objChannelHotelRooms->DrawRoomsForm();			
			draw_content_end();		
		}else{
			draw_important_message(_WRONG_PARAMETER_PASSED);
		}
	}else{
		draw_title_bar(
			prepare_breadcrumbs(array(_ROOMS_MANAGEMENT=>'',_ROOMS=>'',_CHANNEL_MANAGER=>'')),
			prepare_permanent_link('index.php?admin=mod_channel_manager_rooms', _BUTTON_BACK)
		);
		draw_important_message(defined('FLATS_INSTEAD_OF_HOTELS') && FLATS_INSTEAD_OF_HOTELS ? _OWNER_NOT_ASSIGNED_TO_FLAT : _OWNER_NOT_ASSIGNED_TO_HOTEL);
	}
}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}
