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

if($objLogin->IsLoggedInAs('owner','mainadmin','hotelowner') && Modules::IsModuleInstalled('booking')){

	$action 	= MicroGrid::GetParameter('action');
	$rid    	= MicroGrid::GetParameter('rid');
	$mode   	= 'view';
	$msg 		= '';
	
	$objExtras = new Extras();
	
	// Check hotel owner has permissions to edit this hotel's info
	if($objLogin->IsLoggedInAs('hotelowner')){
		$hotel_id = null;
		if(in_array($action, array('create', 'update'))){
			$hotel_id = MicroGrid::GetParameter('hotel_id', false);	
		}elseif(in_array($action, array('edit', 'details', 'delete'))){
			$info = $objExtras->GetInfoByID($rid);
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
	
	if($action=='add'){
		$mode = 'add';
	}elseif($action=='create'){
		if($objExtras->AddRecord()){
			$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objExtras->error, false);
			$mode = 'add';
		}
	}elseif($action=='edit'){
		$mode = 'edit';
	}elseif($action=='update'){
		if($objExtras->UpdateRecord($rid)){
			$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objExtras->error, false);
			$mode = 'edit';
		}		
	}elseif($action=='delete'){
		if($objExtras->DeleteRecord($rid)){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objExtras->error, false);
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
	draw_title_bar(prepare_breadcrumbs(array(_BOOKINGS=>'',_SETTINGS=>'',_EXTRAS_MANAGEMENT=>'',ucfirst($action)=>'')));
    	
	if($objSession->IsMessage('notice')) $msg = $objSession->GetMessage('notice');
	echo $msg;

	draw_content_start();	

	// Check if hotel owner is not assigned to any hotel
	$allow_viewing = true;
	if($objLogin->IsLoggedInAs('hotelowner')){
		$hotels_list = implode(',', $objLogin->AssignedToHotels());
		if(empty($hotels_list)){
			$allow_viewing = false;
			echo draw_important_message(_OWNER_NOT_ASSIGNED, false);
		}
	}

	if($allow_viewing){
		if($mode == 'view'){		
			$objExtras->DrawViewMode();	
		}elseif($mode == 'add'){		
			$objExtras->DrawAddMode();		
		}elseif($mode == 'edit'){		
			$objExtras->DrawEditMode($rid);		
		}elseif($mode == 'details'){		
			$objExtras->DrawDetailsMode($rid);		
		}
	}
	draw_content_end();	

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

