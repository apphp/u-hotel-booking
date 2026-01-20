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
    $tour_id = MicroGrid::GetParameter('tid', false);
    $mode   = 'view';
    $msg    = '';

    $objTours = new Tours();
    $tour_info = $objTours->GetTourFullInfo($tour_id);

    if(!empty($tour_id) && count($tour_info) > 0){

        $objTourImages = new ToursImages($tour_id);

        if($action=='add'){
            $mode = 'add';
        }elseif($action=='create'){
            if($objTourImages->AddRecord()){
                $msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
                $mode = 'view';
            }else{
                $msg = draw_important_message($objTourImages->error, false);
                $mode = 'add';
            }
        }elseif($action=='edit'){
            $mode = 'edit';
        }elseif($action=='update'){
            if($objTourImages->UpdateRecord($rid)){
                $msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
                $mode = 'view';
            }else{
                $msg = draw_important_message($objTourImages->error, false);
                $mode = 'edit';
            }
        }elseif($action=='delete'){
            if($objTourImages->DeleteRecord($rid)){
                $msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
            }else{
                $msg = draw_important_message($objTourImages->error, false);
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
            prepare_breadcrumbs(
                array(_TOURS_AND_ATTRACTIONS=>'',
                      _TOURS_AND_ATTRACTIONS_MANAGEMENT=>'',
                      $tour_info['name']=>'index.php?admin=mod_tours_and_attractions_management',
                      _IMAGES=>'index.php?admin=mod_tours_and_attractions_upload_images&tid='.$tour_id,
                      ucfirst($action)=>'')
            ),
            prepare_permanent_link('index.php?admin=mod_tours_and_attractions_management', _BUTTON_BACK)
        );

        if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
        echo $msg;

        draw_content_start();
        if($mode == 'view'){
            $objTourImages->DrawOperationLinks(
                '[ <b>' . _TOURS_MANAGEMENT . '</b> ]  &nbsp; ' .
                prepare_permanent_link('index.php?admin=mod_tours_and_attractions_agencies_management', '[ ' . _TOUR_AGENCIES . ' ]') . ' &nbsp; ' .
                prepare_permanent_link('index.php?admin=mod_tours_and_attractions_types_management', '[ ' . _TOUR_TYPES . ' ]') . ' &nbsp; ' .
                prepare_permanent_link('index.php?admin=mod_tours_and_attractions_locations_management', '[ ' . _TOUR_LOCATIONS . ' ]') . ' &nbsp; ' .
                prepare_permanent_link('index.php?admin=mod_tours_and_attractions_reviews_management', '[ ' . _REVIEWS . ' ]') . ' &nbsp; ' .
                prepare_permanent_link('index.php?admin=mod_tours_and_attractions_orders_management', '[ ' . _RESERVATIONS . ' ]')
            );

            $objTourImages->DrawViewMode();
        }elseif($mode == 'add'){
            $objTourImages->DrawAddMode();
        }elseif($mode == 'edit'){
            $objTourImages->DrawEditMode($rid);
        }elseif($mode == 'details'){
            $objTourImages->DrawDetailsMode($rid);
        }
        draw_content_end();
    }else{
        // Start main content
        draw_title_bar(
            prepare_breadcrumbs(
                array(_TOURS_AND_ATTRACTIONS=>'',_TOURS_AND_ATTRACTIONS_MANAGEMENT=>'',ucfirst($action)=>'')
            ),
            prepare_permanent_link('index.php?admin=mod_tours_and_attractions_management', _BUTTON_BACK)
        );

        draw_important_message(_NO_TOURS_FOUND);
    }
}else{
    draw_title_bar(_ADMIN);
    draw_important_message(_NOT_AUTHORIZED);
}
