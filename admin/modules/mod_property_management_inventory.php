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

if($objLogin->IsLoggedInAs('owner','mainadmin','hotelowner','hotelmanager') && Modules::IsModuleInstalled('property_management')){

    $action = MicroGrid::GetParameter('action');
    $rid    = MicroGrid::GetParameter('rid');
    $mode   = 'view';
    $msg    = '';

    if(class_exists('PropertyInventory')){
        $objPropertyInventory = new PropertyInventory();

        $hotel_id  = $objPropertyInventory->GetSelectedHotelId();

        $allow_viewing = true;
        if($objLogin->IsLoggedInAs('hotelowner')){
            $hotel_id = null;
            if(in_array($action, array('create', 'update'))){
                $hotel_id = MicroGrid::GetParameter('hotel_id', false);
            }elseif(in_array($action, array('edit', 'details', 'delete'))){
                $info = $objPropertyInventory->GetInfoByID($rid);
                $hotel_id = isset($info['hotel_id']) ? $info['hotel_id'] : '';
                if(empty($hotel_id)){
                    $hotel_id = '-99';
                }
            }

            $assignedToHotels = $objLogin->AssignedToHotels();
            if(!empty($hotel_id)){
                    if(!in_array($hotel_id, $assignedToHotels)){
                        $msg = draw_important_message(_WRONG_PARAMETER_PASSED, false);
                    }
                if(!empty($msg)){
                    $action = '';
                    $mode = 'view';
                }
            }

            $hotels_list = implode(',', $assignedToHotels);
            if(empty($hotels_list)){
                $allow_viewing = false;
                echo draw_important_message(_OWNER_NOT_ASSIGNED, false);
            }
        }

        if($action=='add'){
            $mode = 'add';
        }elseif($action=='create'){
            if($objPropertyInventory->AddRecord()){
                $msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
                $mode = 'view';
            }else{
                $msg = draw_important_message($objPropertyInventory->error, false);
                $mode = 'add';
            }
        }elseif($action=='edit'){
            $mode = 'edit';
        }elseif($action=='update'){
            if($objPropertyInventory->UpdateRecord($rid)){
                $msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
                $mode = 'view';
            }else{
                $msg = draw_important_message($objPropertyInventory->error, false);
                $mode = 'edit';
            }
        }elseif($action=='delete'){
            if($objPropertyInventory->DeleteRecord($rid)){
                $msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
            }else{
                $msg = draw_important_message($objPropertyInventory->error, false);
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
        if($objLogin->IsLoggedInAs('owner','mainadmin','hotelowner')){
            draw_title_bar(
                prepare_breadcrumbs(array(_MODULES=>'index.php?admin=modules',  _PROPERTY_MANAGEMENT=>'index.php?admin=mod_property_management_settings', _PROPERTY_INVENTORY=>($mode == 'view' ? '' : 'index.php?admin=mod_property_management_inventory'.(!empty($hotel_id) ? '&filter_by_uhb_property_inventoryhotel_id='.(int)$hotel_id : '')),ucfirst($action)=>'')),
                prepare_permanent_link(($mode == 'view' ? 'index.php?admin=modules' : 'index.php?admin=mod_property_management_inventory'.(!empty($hotel_id) ? '&filter_by_uhb_property_inventoryhotel_id='.(int)$hotel_id : '')), _BUTTON_BACK)
            );
        }

        //if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
        echo $msg;

        if(empty($hotel_id)){
            $property_expenses_link = prepare_permanent_link('index.php?admin=mod_property_management_expenses', '[ ' . _PROPERTY_EXPENSES . ' ]');
            $property_managers_link = prepare_permanent_link('index.php?admin=mod_property_management_managers', '[ ' . _PROPERTY_MANAGERS . ' ]');
        }else{
            $property_expenses_link = '<a href="javascript:void();" onclick="javascript:appGoToPage(\'index.php?admin=mod_property_management_expenses\',\'&amp;mg_action=view&amp;mg_operation=filtering&amp;mg_search_status=active&amp;token='.Application::Get('token').'&amp;filter_by_uhb_property_expenseshotel_id='.(!empty($hotel_id) ? $hotel_id : '' ).'\',\'post\')">[ '._PROPERTY_EXPENSES.' ]</a>';
            $property_managers_link = prepare_permanent_link('index.php?admin=mod_property_management_managers&hid='.$hotel_id, '[ ' . _PROPERTY_MANAGERS . ' ]');
        }


        draw_content_start();
        if($allow_viewing){
            if($mode == 'view'){
                if($objLogin->IsLoggedInAs('owner','mainadmin','hotelowner','hotelmanager')){
                    $objPropertyInventory->DrawOperationLinks(
                        '[ <b>' . _PROPERTY_INVENTORY . '</b> ]  &nbsp; ' .
                        $property_expenses_link . ' &nbsp; ' .
                        ($objLogin->IsLoggedInAs('owner','mainadmin','hotelowner') ? $property_managers_link : '')
                    );
                }
                $objPropertyInventory->DrawViewMode();
            }elseif($mode == 'add'){
                $objPropertyInventory->DrawAddMode();
                echo '<script type="text/javascript" src="templates/admin/js/propertymanagement.js"></script>';
            }elseif($mode == 'edit'){
                $objPropertyInventory->DrawEditMode($rid);
                echo '<script type="text/javascript" src="templates/admin/js/propertymanagement.js"></script>';
            }elseif($mode == 'details'){
                $objPropertyInventory->DrawDetailsMode($rid);
            }
        }
        draw_content_end();
    }else{
        draw_important_message(_MODULE_NOT_FOUND, true);
    }
}else{
    draw_title_bar(_ADMIN);
    draw_important_message(_NOT_AUTHORIZED);
}
