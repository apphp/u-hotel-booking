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

if($objLogin->IsLoggedInAs('owner','mainadmin') && Modules::IsModuleInstalled('tours_and_attractions')){

    $action = MicroGrid::GetParameter('action');
    $rid    = MicroGrid::GetParameter('rid');
    $mode   = 'view';
    $msg 	= '';

	$objToursAgencies = new ToursAgencies();

	if($action=='add'){		
		$mode = 'add';
	}elseif($action=='create'){
		if($objToursAgencies->AddRecord()){
			$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objToursAgencies->error, false);
			$mode = 'add';
		}
	}elseif($action=='edit'){
		$mode = 'edit';
	}elseif($action=='update'){
		if($objToursAgencies->UpdateRecord($rid)){
			$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objToursAgencies->error, false);
			$mode = 'edit';
		}		
	}elseif($action=='delete'){
		if($objToursAgencies->DeleteRecord($rid)){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objToursAgencies->error, false);
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
    draw_title_bar(prepare_breadcrumbs(array(_TOURS_AND_ATTRACTIONS=>'',_TOUR_AGENCIES=>'',ucfirst($action)=>'')));
	
	if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
	echo $msg;

	draw_content_start();	

    if($mode == 'view'){

        $objToursAgencies->DrawOperationLinks(
            prepare_permanent_link('index.php?admin=mod_tours_and_attractions_management', '[ ' . _TOURS_MANAGEMENT . ' ]') . ' &nbsp; ' .
            '[ <b>' . _TOUR_AGENCIES . '</b> ]' . ' &nbsp; ' .
            prepare_permanent_link('index.php?admin=mod_tours_and_attractions_types_management', '[ ' . _TOUR_TYPES . ' ]') . ' &nbsp; ' .
            prepare_permanent_link('index.php?admin=mod_tours_and_attractions_locations_management', '[ ' . _TOUR_LOCATIONS . ' ]') . ' &nbsp; ' .
            prepare_permanent_link('index.php?admin=mod_tours_and_attractions_reviews_management', '[ ' . _REVIEWS . ' ]') . ' &nbsp; ' .
            prepare_permanent_link('index.php?admin=mod_tours_and_attractions_orders_management', '[ ' . _RESERVATIONS . ' ]')
        );

        $objToursAgencies->DrawViewMode();
    }elseif($mode == 'add'){
        $objToursAgencies->DrawAddMode();
    }elseif($mode == 'edit'){
        $objToursAgencies->DrawEditMode($rid);
    }elseif($mode == 'details'){
        $objToursAgencies->DrawDetailsMode($rid);
    }

	draw_content_end();

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}
