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

if(Modules::IsModuleInstalled('rooms') && 
  ($objLogin->IsLoggedInAs('owner','mainadmin','regionalmanager') || ($objLogin->IsLoggedInAs('hotelowner') && $objLogin->HasPrivileges('edit_hotel_rooms')))
){

	$rid = isset($_REQUEST['rid']) ? (int)$_REQUEST['rid'] : '';
	$room_name = '';
	$room_type = Rooms::GetRoomInfo($rid);
	
	if(isset($room_type['hotel_name']) && isset($room_type['room_type'])){
		$room_name = (($room_type['hotel_name'] != '') ? $room_type['hotel_name'].' &raquo; ' : '').$room_type['room_type'];
	}
	
	$task = isset($_REQUEST['task']) ? prepare_input($_REQUEST['task']) : '';
	$rpid  = isset($_POST['rpid']) ? (int)$_POST['rpid'] : '';
	
	$hotel_id = Rooms::GetRoomInfo($rid, 'hotel_id');	
	if(!empty($hotel_id) && $objLogin->AssignedToHotel($hotel_id)){

		draw_title_bar(
			prepare_breadcrumbs(array(
				_ROOMS_MANAGEMENT => 'index.php?admin=mod_rooms_management',
				$room_name => '',
				_PRICES => ''
			),
			prepare_permanent_link('index.php?admin=mod_rooms_management', _BUTTON_BACK)
		));

		$objRoom = new Rooms();
	
		if($task == 'add_new'){
			if($objRoom->AddRoomPrices($rid)){
				draw_success_message(_ROOM_PRICES_WERE_ADDED);	
			}else{
				draw_important_message($objRoom->error);
			}		
		}elseif($task == 'update'){
			if($objRoom->UpdateRoomPrices($rid)){
				draw_success_message(_CHANGES_WERE_SAVED);	
			}else{
				draw_important_message($objRoom->error);
			}		
		}elseif($task == 'delete'){
			if($objRoom->DeleteRoomPrices($rpid)){
				draw_success_message(_RECORD_WAS_DELETED_COMMON);	
			}else{
				draw_important_message($objRoom->error);
			}				
		}elseif($task == 'add_default_periods'){
			if($objRoom->AddDefaultPeriods($rid)){
				draw_success_message(_DEFAULT_PERIODS_WERE_ADDED);
			}else{
				draw_important_message($objRoom->error);
			}				
		}elseif($task == 'refresh'){	
			unset($_POST);
		}

		// *** Channel manager sender
		// -----------------------------------------------------------------------------
		if($objRoom->error == '' && Modules::IsModuleInstalled('channel_manager') && (ModulesSettings::Get('channel_manager', 'is_active') != 'no')){
			require_once('modules/additional/channel_manager/sender.php');

			// Clear POST data
			if(isset($_POST)){
				unset($_POST);	
			}
		}

		if($rid > 0){
			draw_content_start();
			$objRoom->DrawRoomPricesForm($rid);		
			draw_content_end();		
		}else{
			draw_important_message(_WRONG_PARAMETER_PASSED);
		}
	}else{
		draw_title_bar(
			prepare_breadcrumbs(array(_ROOMS_MANAGEMENT=>'',_PRICES=>''))
		);
		draw_important_message(_WRONG_PARAMETER_PASSED);		
	}	
}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}
