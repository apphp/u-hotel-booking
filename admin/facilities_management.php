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

if($objLogin->IsLoggedInAs('owner','mainadmin')){

	$action 	= MicroGrid::GetParameter('action');
	$rid    	= MicroGrid::GetParameter('rid');
	$mode   	= 'view';
	$msg 		= '';
	
	$objRoomFacilities = new RoomFacilities();
	
	if($action=='add'){		
		$mode = 'add';
	}elseif($action=='create'){
		if($objRoomFacilities->AddRecord()){
			$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objRoomFacilities->error, false);
			$mode = 'add';
		}
	}elseif($action=='edit'){
		$mode = 'edit';
	}elseif($action=='update'){
		if($objRoomFacilities->UpdateRecord($rid)){
			$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objRoomFacilities->error, false);
			$mode = 'edit';
		}		
	}elseif($action=='delete'){
		if($objRoomFacilities->DeleteRecord($rid)){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objRoomFacilities->error, false);
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
	draw_title_bar(prepare_breadcrumbs(array((FLATS_INSTEAD_OF_HOTELS ? _FLAT_MANAGEMENT : _HOTEL_MANAGEMENT)=>'',_SETTINGS=>'',_FACILITIES_MANAGEMENT=>'',ucfirst($action)=>'')));
    	
	//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
	echo $msg;

	draw_content_start();	
	if($mode == 'view'){		
		$objRoomFacilities->DrawViewMode();	
	}elseif($mode == 'add'){		
		$objRoomFacilities->DrawAddMode();		
	}elseif($mode == 'edit'){		
		$objRoomFacilities->DrawEditMode($rid);		
	}elseif($mode == 'details'){		
		$objRoomFacilities->DrawDetailsMode($rid);		
	}
	draw_content_end();	

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

