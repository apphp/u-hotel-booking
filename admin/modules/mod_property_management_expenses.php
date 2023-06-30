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


    if(class_exists('PropertyExpenses')){
        $objPropertyExpenses = new PropertyExpenses();

        $hotel_id  = $objPropertyExpenses->GetSelectedHotelId();

        $allow_viewing = true;
        if($objLogin->IsLoggedInAs('hotelowner')){
            $hotel_id = null;
            if(in_array($action, array('create', 'update'))){
                $hotel_id = MicroGrid::GetParameter('hotel_id', false);
            }elseif(in_array($action, array('edit', 'details', 'delete'))){
                $info = $objPropertyExpenses->GetInfoByID($rid);
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
            if($objPropertyExpenses->AddRecord()){
                $msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
                $mode = 'view';
            }else{
                $msg = draw_important_message($objPropertyExpenses->error, false);
                $mode = 'add';
            }
        }elseif($action=='edit'){
            $mode = 'edit';
        }elseif($action=='update'){
            if($objPropertyExpenses->UpdateRecord($rid)){
                $msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
                $mode = 'view';
            }else{
                $msg = draw_important_message($objPropertyExpenses->error, false);
                $mode = 'edit';
            }
        }elseif($action=='delete'){
            if($objPropertyExpenses->DeleteRecord($rid)){
                $msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
            }else{
                $msg = draw_important_message($objPropertyExpenses->error, false);
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
                prepare_breadcrumbs(array(_MODULES=>'index.php?admin=modules',  _PROPERTY_MANAGEMENT=>'index.php?admin=mod_property_management_settings', _PROPERTY_EXPENSES=>($mode == 'view' ? '' : 'index.php?admin=mod_property_management_expenses'.(!empty($hotel_id) ? '&filter_by_uhb_property_expenseshotel_id='.(int)$hotel_id : '')),ucfirst($action)=>'')),
                prepare_permanent_link(($mode == 'view' ? 'index.php?admin=modules' : 'index.php?admin=mod_property_management_expenses'.(!empty($hotel_id) ? '&filter_by_uhb_property_expenseshotel_id='.(int)$hotel_id : '')), _BUTTON_BACK)
            );
        }

        echo $msg;

        if(empty($hotel_id)){
            $property_inventory_link = prepare_permanent_link('index.php?admin=mod_property_management_inventory', '[ ' . _PROPERTY_INVENTORY . ' ]');
            $property_managers_link = prepare_permanent_link('index.php?admin=mod_property_management_managers', '[ ' . _PROPERTY_MANAGERS . ' ]');
        }else{
            $property_inventory_link = '<a href="javascript:void();" onclick="javascript:appGoToPage(\'index.php?admin=mod_property_management_inventory\',\'&amp;mg_action=view&amp;mg_operation=filtering&amp;mg_search_status=active&amp;token='.Application::Get('token').'&amp;filter_by_uhb_property_inventoryhotel_id='.$hotel_id.'\',\'post\')">[ '._PROPERTY_INVENTORY.' ]</a>';
            $property_managers_link = prepare_permanent_link('index.php?admin=mod_property_management_managers&hid='.$hotel_id, '[ ' . _PROPERTY_MANAGERS . ' ]');
        }

        draw_content_start();
        if($allow_viewing){
            if($mode == 'view'){
                if($objLogin->IsLoggedInAs('owner','mainadmin','hotelowner','hotelmanager')) {
                    $objPropertyExpenses->DrawOperationLinks(
                        $property_inventory_link . ' &nbsp; ' .
                        '[ <b>' . _PROPERTY_EXPENSES . '</b> ] &nbsp; ' .
                        ($objLogin->IsLoggedInAs('owner','mainadmin','hotelowner') ? $property_managers_link : '')

                    );
                }
                $objPropertyExpenses->DrawViewMode();
            }elseif($mode == 'add'){
                $objPropertyExpenses->DrawAddMode();
                echo '<script type="text/javascript" src="templates/admin/js/propertymanagement.js"></script>';
            }elseif($mode == 'edit'){
                $objPropertyExpenses->DrawEditMode($rid);
                echo '<script type="text/javascript" src="templates/admin/js/propertymanagement.js"></script>';
            }elseif($mode == 'details'){
                $objPropertyExpenses->DrawDetailsMode($rid);
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
