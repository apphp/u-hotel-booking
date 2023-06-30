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

if(Modules::IsModuleInstalled('booking') && 
  ($objLogin->IsLoggedInAs('owner','mainadmin') || ($objLogin->IsLoggedInAs('hotelowner') && $objLogin->HasPrivileges('edit_hotel_info')))
){

	$action 	= MicroGrid::GetParameter('action');
	$rid    	= MicroGrid::GetParameter('rid');
	$mode   	= 'view';
	$msg 		= '';
	
	$objMealPlans = new MealPlans();
	
	// Check hotel owner has permissions to edit this hotel's info
	if($objLogin->IsLoggedInAs('hotelowner')){
		$hotel_id = null;
		if(in_array($action, array('create', 'update'))){
			$hotel_id = MicroGrid::GetParameter('hotel_id', false);	
		}elseif(in_array($action, array('edit', 'details', 'delete'))){
			$info = $objMealPlans->GetInfoByID($rid);
			$hotel_id = isset($info['hotel_id']) ? $info['hotel_id'] : '';
			if(empty($hotel_id)){
				$hotel_id = '-99';
			}
		}
		
        if(!empty($hotel_id)){
            if($objLogin->IsLoggedInAs('hotelowner')){
                if(!in_array($hotel_id, $objLogin->AssignedToHotels())){
                    $msg = draw_important_message(_WRONG_PARAMETER_PASSED, false);
                }
            }else{
                if(!in_array($hotel_id, AccountLocations::GetHotels($objLogin->GetLoggedId()))){
                    $msg = draw_important_message(_WRONG_PARAMETER_PASSED, false);
                }
            }
            if(!empty($msg)){
                $action = '';
                $mode = 'view';
            }
        }
	}

	if($action=='add'){		
		$mode = 'add';
	}elseif($action=='create'){
		if($objMealPlans->AddRecord()){
			$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objMealPlans->error, false);
			$mode = 'add';
		}
	}elseif($action=='edit'){
		$mode = 'edit';
	}elseif($action=='update'){
		if($objMealPlans->UpdateRecord($rid)){
			$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objMealPlans->error, false);
			$mode = 'edit';
		}		
	}elseif($action=='delete'){
		if($objMealPlans->DeleteRecord($rid)){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objMealPlans->error, false);
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
    if(FLATS_INSTEAD_OF_HOTELS){
    	draw_title_bar(prepare_breadcrumbs(array(_FLATS_MANAGEMENT=>'',_FLATS=>'',_MEAL_PLANS_MANAGEMENT=>'',ucfirst($action)=>'')));
    }else{
    	draw_title_bar(prepare_breadcrumbs(array(_HOTELS_MANAGEMENT=>'',_HOTELS_AND_ROOMS=>'index.php?admin=hotels_info',_MEAL_PLANS_MANAGEMENT=>($action ? 'index.php?admin=mod_booking_meal_plans' : ''),ucfirst($action)=>'')));
    }
    	
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
			$objMealPlans->DrawViewMode();	
		}elseif($mode == 'add'){		
			$objMealPlans->DrawAddMode();		
		}elseif($mode == 'edit'){		
			$objMealPlans->DrawEditMode($rid);		
		}elseif($mode == 'details'){		
			$objMealPlans->DrawDetailsMode($rid);		
		}
	}
	draw_content_end();	

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

