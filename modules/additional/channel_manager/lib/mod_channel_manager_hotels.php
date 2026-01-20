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

	$action = MicroGrid::GetParameter('action');
	$rid    = MicroGrid::GetParameter('rid');
	$hotel_id = MicroGrid::GetParameter('hid', false);
	$mode   = 'view';
	$msg    = '';
	
	$objChannelHotels = new ChannelHotels($hotel_id);
	
	// Check hotel owner has permissions to edit this hotel's info
	if($objLogin->IsLoggedInAs('hotelowner')){		
		if(in_array($action, array('update'))){
			if(empty($hotel_id)){
				$hotel_id = '-99';
			}
		}elseif(in_array($action, array('edit', 'details', 'delete'))){
			$info = $objChannelHotels->GetInfoByID($rid);
			$hotel_id = isset($info['hotel_id']) ? $info['hotel_id'] : '';
			if(empty($hotel_id)){
				$hotel_id = '-99';
			}
		}
		if(!empty($hotel_id) && !in_array($hotel_id, $objLogin->AssignedToHotels())){
            $msg = draw_important_message(_WRONG_PARAMETER_PASSED, false);
            $action = '';
			$mode = 'view';
		}
	}

	$objHotels = new Hotels();
	$hotel_info = $objHotels->GetHotelFullInfo($hotel_id);

	if(!empty($hotel_id) && $objLogin->AssignedToHotel($hotel_id) && count($hotel_info) > 0){
		if($action=='add'){		
			$mode = 'add';
		}elseif($action=='create'){
			if($objChannelHotels->AddRecord()){		
				$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
				$mode = 'view';
			}else{
				$msg = draw_important_message($objChannelHotels->error, false);
				$mode = 'add';
			}
		}elseif($action=='edit'){
			$mode = 'edit';
		}elseif($action=='update'){
			if($objChannelHotels->UpdateRecord($rid)){
				$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
				$mode = 'view';
			}else{
				$msg = draw_important_message($objChannelHotels->error, false);
				$mode = 'edit';
			}		
		}elseif($action=='delete'){
			if($objChannelHotels->DeleteRecord($rid)){
				$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
			}else{
				$msg = draw_important_message($objChannelHotels->error, false);
			}
			$mode = 'view';
		}elseif($action=='details'){		
			$mode = 'details';		
		}elseif($action=='cancel_add'){		
			$mode = 'view';		
		}elseif($action=='cancel_edit'){				
			$mode = 'view';
        }else{
            $action = '';
		}

		// Start main content
		draw_title_bar(
            (defined('FLATS_INSTEAD_OF_HOTELS') && FLATS_INSTEAD_OF_HOTELS
                ? prepare_breadcrumbs(array(_FLAT_MANAGEMENT=>'',_FLATS_INFO=>'',$hotel_info['name']=>'',_CHANNEL_MANAGER=>'',ucfirst($action)=>''))
                : prepare_breadcrumbs(array(_HOTEL_MANAGEMENT=>'',_HOTELS_INFO=>'',$hotel_info['name']=>'',_CHANNEL_MANAGER=>'',ucfirst($action)=>''))
            ),
			prepare_permanent_link(($mode == 'view' ? 'index.php?admin=hotels_info' : 'index.php?admin=mod_channel_manager_hotels&hid='.$hotel_id), _BUTTON_BACK)
		);
	
		//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
		echo $msg;
	
		draw_content_start();
		if($mode == 'view'){		
			$objChannelHotels->DrawViewMode();	
		}elseif($mode == 'add'){		
			$objChannelHotels->DrawAddMode();		
		}elseif($mode == 'edit'){		
			$objChannelHotels->DrawEditMode($rid);		
		}elseif($mode == 'details'){		
			$objChannelHotels->DrawDetailsMode($rid);		
		}
		draw_content_end();		
	}else{
		draw_title_bar(
            (defined('FLATS_INSTEAD_OF_HOTELS') && FLATS_INSTEAD_OF_HOTELS
                ? prepare_breadcrumbs(array(_FLAT_MANAGEMENT=>'',_FLATS_INFO=>'',_CHANNEL_MANAGER=>''))
                : prepare_breadcrumbs(array(_HOTEL_MANAGEMENT=>'',_HOTELS_INFO=>'',_CHANNEL_MANAGER=>''))
            ),
			prepare_permanent_link('index.php?admin=hotels_info', _BUTTON_BACK)
		);
		draw_important_message(defined('FLATS_INSTEAD_OF_HOTELS') && FLATS_INSTEAD_OF_HOTELS ? _OWNER_NOT_ASSIGNED_TO_FLAT : _OWNER_NOT_ASSIGNED_TO_HOTEL);
	}
}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}
