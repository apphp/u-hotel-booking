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

if(Modules::IsModuleInstalled('car_rental') &&
	($objLogin->IsLoggedInAs('owner','mainadmin') ||
	($objLogin->IsLoggedInAs('agencyowner') && ModulesSettings::Get('car_rental', 'is_active') != 'no'))
){	

	$action 	= MicroGrid::GetParameter('action');
	$rid    	= MicroGrid::GetParameter('rid');
	$mode   	= 'view';
	$msg 		= '';
	
	$objCarAgenciesVehicleTypes = new CarAgenciesVehicleTypes();

	if($action=='add'){		
		$mode = 'add';
	}elseif($action=='create'){
		if($objCarAgenciesVehicleTypes->AddRecord()){
			$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objCarAgenciesVehicleTypes->error, false);
			$mode = 'add';
		}
	}elseif($action=='edit'){
		$mode = 'edit';
	}elseif($action=='update'){
		if($objCarAgenciesVehicleTypes->UpdateRecord($rid)){
			$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objCarAgenciesVehicleTypes->error, false);
			$mode = 'edit';
		}		
	}elseif($action=='delete'){
		if($objCarAgenciesVehicleTypes->DeleteRecord($rid)){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objCarAgenciesVehicleTypes->error, false);
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
	draw_title_bar(prepare_breadcrumbs(array(_CAR_RENTAL=>'',_TRANSPORTATION_AGENCIES=>'',_AGENCY_CAR_TYPES=>'',ucfirst($action)=>'')));	
	
	if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
	echo $msg;

	draw_content_start();	
	$allow_viewing = true;
	if($objLogin->IsLoggedInAs('agencyowner')){
		$agencies_list = implode(',', $objLogin->AssignedToCarAgencies());
		if(empty($agencies_list)){
			$allow_viewing = false;
			echo draw_important_message(_CAR_OWNER_NOT_ASSIGNED, false);
		}
	}
	
	if($allow_viewing){
		if($mode == 'view'){
			$objCarAgenciesVehicleTypes->DrawViewMode();	
		}elseif($mode == 'add'){		
			$objCarAgenciesVehicleTypes->DrawAddMode();		
		}elseif($mode == 'edit'){		
			$objCarAgenciesVehicleTypes->DrawEditMode($rid);		
		}elseif($mode == 'details'){		
			$objCarAgenciesVehicleTypes->DrawDetailsMode($rid);		
		}
	}
	draw_content_end();

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}
