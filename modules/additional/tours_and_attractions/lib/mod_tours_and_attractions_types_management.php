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

    $action 	= MicroGrid::GetParameter('action');
    $rid    	= MicroGrid::GetParameter('rid');
    $mode   	= 'view';
    $msg 		= '';

    $objToursTypes = new ToursTypes();

    if($action=='add'){
        $mode = 'add';
    }elseif($action=='create'){
        if($objToursTypes->AddRecord()){
            $msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
            $mode = 'view';
        }else{
            $msg = draw_important_message($objToursTypes->error, false);
            $mode = 'add';
        }
    }elseif($action=='edit'){
        $mode = 'edit';
    }elseif($action=='update'){
        if($objToursTypes->UpdateRecord($rid)){
            $msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
            $mode = 'view';
        }else{
            $msg = draw_important_message($objToursTypes->error, false);
            $mode = 'edit';
        }
    }elseif($action=='delete'){
        if($objToursTypes->DeleteRecord($rid)){
            $msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
        }else{
            $msg = draw_important_message($objToursTypes->error, false);
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
    draw_title_bar(prepare_breadcrumbs(array(_TOURS_AND_ATTRACTIONS=>'',_TOUR_TYPES_MANAGEMENT=>'',ucfirst($action)=>'')));

    if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
    echo $msg;

    draw_content_start();
    if($mode == 'view'){
        $objToursTypes->DrawOperationLinks(
            prepare_permanent_link('index.php?admin=mod_tours_and_attractions_management', '[ ' . _TOURS_MANAGEMENT . ' ]') . ' &nbsp; ' .
            prepare_permanent_link('index.php?admin=mod_tours_and_attractions_agencies_management', '[ ' . _TOUR_AGENCIES . ' ]') . ' &nbsp; ' .
            '[ <b>' . _TOUR_TYPES . '</b> ]' . ' &nbsp; ' .
            prepare_permanent_link('index.php?admin=mod_tours_and_attractions_locations_management', '[ ' . _TOUR_LOCATIONS . ' ]') . ' &nbsp; ' .
            prepare_permanent_link('index.php?admin=mod_tours_and_attractions_reviews_management', '[ ' . _REVIEWS . ' ]') . ' &nbsp; ' .
            prepare_permanent_link('index.php?admin=mod_tours_and_attractions_orders_management', '[ ' . _RESERVATIONS . ' ]')
        );
        $objToursTypes->DrawViewMode();

    }elseif($mode == 'add'){
        $objToursTypes->DrawAddMode();
    }elseif($mode == 'edit'){
        $objToursTypes->DrawEditMode($rid);
    }elseif($mode == 'details'){
        $objToursTypes->DrawDetailsMode($rid);
    }
    draw_content_end();

}else{
    draw_title_bar(_ADMIN);
    draw_important_message(_NOT_AUTHORIZED);
}
