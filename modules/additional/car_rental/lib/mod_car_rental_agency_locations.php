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
	
	$action 		= MicroGrid::GetParameter('action');
	$rid    		= MicroGrid::GetParameter('rid');
	$agency_id  	= MicroGrid::GetParameter('agency_id', false);
	$mode   		= 'view';
	$msg 			= '';
	$allow_viewing 	= true;

	// Check if valid agency ID
	$objCarAgencies = new CarAgencies();
	$agency_info = $objCarAgencies->GetAgencyFullInfo($agency_id);
	if(empty($agency_id) || !count($agency_info)){
		$allow_viewing = false;
	}
    
	// Check if current owner is assigned to agency ID	
	if($objLogin->IsLoggedInAs('agencyowner')){
		if(!empty($rid) && $rid != '-1'){
			$result = CarAgencyLocations::GetAgencyLocations($rid, $agency_id);
			$record_agency_id = isset($result[0]['agency_id']) ? $result[0]['agency_id'] : null;
			if(!$objLogin->AssignedToCarAgency($record_agency_id)){
				$allow_viewing = false;
			}
		}else{
			if(!$objLogin->AssignedToCarAgency($agency_id)){
				$allow_viewing = false;
			}			
		}
	}
	
	if($allow_viewing){
		$objAgencyLocations = new CarAgencyLocations($agency_id);
	
		if($action=='add'){		
			$mode = 'add';
		}elseif($action=='create'){
			if($objAgencyLocations->AddRecord()){
				$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
				$mode = 'view';
			}else{
				$msg = draw_important_message($objAgencyLocations->error, false);
				$mode = 'add';
			}
		}elseif($action=='edit'){
			$mode = 'edit';
		}elseif($action=='update'){
			if($objAgencyLocations->UpdateRecord($rid)){
				$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
				$mode = 'view';
			}else{
				$msg = draw_important_message($objAgencyLocations->error, false);
				$mode = 'edit';
			}		
		}elseif($action=='delete'){
			if($objAgencyLocations->DeleteRecord($rid)){
				$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
			}else{
				$msg = draw_important_message($objAgencyLocations->error, false);
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

		$agency_name = isset($agency_info['name']) ? $agency_info['name'] : '';
	
		// Start main content
		draw_title_bar(
			prepare_breadcrumbs(array(_CAR_RENTAL=>'',_SETTINGS=>'',$agency_name=>'',_LOCATIONS=>'',ucfirst($action)=>'')),
			prepare_permanent_link('index.php?admin=mod_car_rental_agencies', _BUTTON_BACK)
		);

		//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
		echo $msg;
	
		draw_content_start();	
		if($mode == 'view'){		
			$objAgencyLocations->DrawViewMode();	
		}elseif($mode == 'add'){		
			$objAgencyLocations->DrawAddMode();		
		}elseif($mode == 'edit'){		
			$objAgencyLocations->DrawEditMode($rid);		
		}elseif($mode == 'details'){		
			$objAgencyLocations->DrawDetailsMode($rid);		
		}
		draw_content_end();
	}else{
		draw_title_bar(_ADMIN);
		draw_important_message(_WRONG_PARAMETER_PASSED);		
	}	
}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}
