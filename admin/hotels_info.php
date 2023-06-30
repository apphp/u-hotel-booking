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
	
if($objLogin->IsLoggedInAs('owner','mainadmin','hotelowner','regionalmanager')){
	
	$action = MicroGrid::GetParameter('action');
	$rid    = MicroGrid::GetParameter('rid');
	$mode   = 'view';
	$msg 	= '';
	
	$objHotels = new Hotels();

	if($objLogin->IsLoggedInAs('hotelowner')){
		$arr_hotels_list = $objLogin->AssignedToHotels();
		if(!empty($rid) && !in_array($rid, $arr_hotels_list)){
			if(!$objLogin->HasPrivileges('add_hotel_info') && $action !== 'add'){
				$msg = draw_important_message(_WRONG_PARAMETER_PASSED, false);
				$action = '';
			}
		}
	}

	if($action=='add'){		
		$mode = 'add';
	}elseif($action=='create'){
		if($objHotels->AddRecord()){
			if($objLogin->IsLoggedInAs('hotelowner') && $objLogin->HasPrivileges('add_hotel_info')){
				$objSession->SetMessage('notice', draw_success_message(_ADDING_OPERATION_COMPLETED, false));
				redirect_to('index.php?admin=hotels_info');
			}else{
				$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
			}			
			$mode = 'view';
		}else{
			$msg = draw_important_message($objHotels->error, false);
			$mode = 'add';
		}
	}elseif($action=='edit'){
		$mode = 'edit';
	}elseif($action=='update'){
		if($objHotels->UpdateRecord($rid)){
			$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objHotels->error, false);
			$mode = 'edit';
		}		
	}elseif($action=='delete'){
		if($objHotels->DeleteRecord($rid)){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objHotels->error, false);
		}
		$mode = 'view';
	}elseif($action=='details'){		
		$mode = 'details';		
	}elseif($action=='cancel_add'){		
		$mode = 'view';		
	}elseif($action=='cancel_edit'){				
		$mode = 'view';
	}
	
	// Start main content
	draw_title_bar(
		prepare_breadcrumbs(array(
			(FLATS_INSTEAD_OF_HOTELS ? _FLATS_MANAGEMENT : _HOTELS_MANAGEMENT)	=> 'index.php?admin=hotels_info',
			(FLATS_INSTEAD_OF_HOTELS ? _FLATS : _HOTELS_AND_ROOMS)	=> 'index.php?admin=hotels_info',
			(FLATS_INSTEAD_OF_HOTELS ? _FLATS_INFO : _HOTELS_INFO)	=> (empty($action) ? '' : 'index.php?admin=hotels_info'),
			ucfirst($action)		=> ''
		)
	));
	
	if($objSession->IsMessage('notice')) $msg = $objSession->GetMessage('notice');
	echo $msg;

	draw_content_start();
	
	// Check if hotel owner is not assigned to any hotel
	$allow_viewing = true;
//	if($objLogin->IsLoggedInAs('hotelowner')){
//		$hotels_list = implode(',', $arr_hotels_list);
//		if(empty($hotels_list)){
//			$allow_viewing = false;
//			echo draw_important_message(_OWNER_NOT_ASSIGNED, false);
//		}
//	}

	if($allow_viewing){
		if($mode == 'view'){
			if($objLogin->IsLoggedInAs('owner','mainadmin')) $objHotels->DrawOperationLinks(
				prepare_permanent_link('index.php?admin=hotels_locations', '[ '._LOCATIONS.' ]') . ' &nbsp; ' .
				prepare_permanent_link('index.php?admin=hotels_property_types', '[ '._PROPERTY_TYPES.' ]')				
			);
			$objHotels->SetAlerts(array('delete'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT_DELETE_ALERT : _HOTEL_DELETE_ALERT));
			$objHotels->DrawViewMode();	
		}elseif($mode == 'add'){		
			$objHotels->DrawAddMode();		
		}elseif($mode == 'edit'){		
			$objHotels->DrawEditMode($rid);		
		}elseif($mode == 'details'){		
			$objHotels->DrawDetailsMode($rid);		
		}
	}
	draw_content_end();

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

