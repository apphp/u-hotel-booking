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

if($objLogin->IsLoggedInAs('owner','mainadmin','hotelowner') && Modules::IsModuleInstalled('property_management')){

    $action     = MicroGrid::GetParameter('action');
    $rid        = MicroGrid::GetParameter('rid');
    $mode       = 'view';
    $msg        = '';

    $objAdmins = new AdminsAccounts($objLogin->GetLoggedType(), 'hotel_managers');

    $hotel_id  = isset($_GET['hid']) ?$_GET['hid'] : '';

    if(empty($hotel_id)){
        $property_inventory_link = prepare_permanent_link('index.php?admin=mod_property_management_inventory', '[ ' . _PROPERTY_INVENTORY . ' ]');
        $property_expenses_link = prepare_permanent_link('index.php?admin=mod_property_management_expenses', '[ ' . _PROPERTY_EXPENSES . ' ]');
    }else{
        $property_inventory_link = '<a href="javascript:void();" onclick="javascript:appGoToPage(\'index.php?admin=mod_property_management_inventory\',\'&amp;mg_action=view&amp;mg_operation=filtering&amp;mg_search_status=active&amp;token='.Application::Get('token').'&amp;filter_by_uhb_property_inventoryhotel_id='.$hotel_id.'\',\'post\')">[ '._PROPERTY_INVENTORY.' ]</a>';
        $property_expenses_link = '<a href="javascript:void();" onclick="javascript:appGoToPage(\'index.php?admin=mod_property_management_expenses\',\'&amp;mg_action=view&amp;mg_operation=filtering&amp;mg_search_status=active&amp;token='.Application::Get('token').'&amp;filter_by_uhb_property_expenseshotel_id='.(!empty($hotel_id) ? $hotel_id : '' ).'\',\'post\')">[ '._PROPERTY_EXPENSES.' ]</a>';
    }

    $allow_viewing = true;
    if($objLogin->IsLoggedInAs('hotelowner')){
        $assignedToHotels = $objLogin->AssignedToHotels();

        if(!empty($assignedToHotels)){
            if(in_array($action, array('create', 'update'))){
                $companies = MicroGrid::GetParameter('companies', false);
                if(is_array($companies)) foreach($companies as $k => $v) if($v == '-placeholder-') unset($companies[$k]); /* clear placeholder */
            }elseif(in_array($action, array('edit', 'details', 'delete'))){
                $info = $objAdmins->GetInfoByID($rid);
                $companies = @explode(',', $info['companies']);
            }

            if(!empty($companies)){
                foreach($companies as $company){
                    if(!in_array($company, $assignedToHotels)){
                        $msg = draw_important_message(_WRONG_PARAMETER_PASSED, false);
                    }
                }
            }

            if(!empty($msg)){
                $action = '';
                $mode = 'view';
            }
        }else{
            $allow_viewing = false;
            echo draw_important_message(_OWNER_NOT_ASSIGNED, false);
        }
    }

    if($action=='add'){
        $mode = 'add';
    }elseif($action=='create'){
        if($objAdmins->AddRecord()){
            $msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
            $mode = 'view';
        }else{
            $msg = draw_important_message($objAdmins->error, false);
            $mode = 'add';
        }
    }elseif($action=='edit'){
        $mode = 'edit';
    }elseif($action=='update'){
        if($objAdmins->UpdateRecord($rid)){
            $msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
            $mode = 'view';
        }else{
            $msg = draw_important_message($objAdmins->error, false);
            $mode = 'edit';
        }
    }elseif($action=='delete'){
        if($objAdmins->DeleteRecord($rid)){
            $msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
        }else{
            $msg = draw_important_message($objAdmins->error, false);
        }
        $mode = 'view';
    }elseif($action=='details'){
        $mode = 'details';
    }elseif($action=='recreate_api'){
        $msg = $objAdmins->RecreateApi($rid);
        $mode = 'edit';
    }elseif($action=='cancel_add'){
        $mode = 'view';
    }elseif($action=='cancel_edit'){
        $mode = 'view';
    }
    // Start main content
    draw_title_bar(
        prepare_breadcrumbs(array(_MODULES=>'index.php?admin=modules', _PROPERTY_MANAGEMENT=>'index.php?admin=mod_property_management_settings', _PROPERTY_MANAGERS=>($mode == 'view' ? '' : 'index.php?admin=mod_property_management_managers'.(!empty($hotel_id) ? '&filter_by_uhb_property_managershotel_id='.(int)$hotel_id : '')), ucfirst($action)=>'')),
        prepare_permanent_link(($mode == 'view' ? 'index.php?admin=modules' : 'index.php?admin=mod_property_management_managers'.(!empty($hotel_id) ? '&filter_by_uhb_property_managershotel_id='.(int)$hotel_id : '')), _BUTTON_BACK)
    );

    //if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
    echo $msg;

    draw_content_start();
    if($allow_viewing){
        if($mode == 'view'){
            if($objLogin->IsLoggedInAs('owner','mainadmin','hotelowner')){
                $objAdmins->DrawOperationLinks(
                    $property_inventory_link . ' &nbsp; ' .
                    $property_expenses_link . ' &nbsp; ' .
                    '[ <b>' . _PROPERTY_MANAGERS . '</b> ] '
                );
            }
            $objAdmins->DrawViewMode();
        }elseif($mode == 'add'){
            $objAdmins->DrawAddMode();
        }elseif($mode == 'edit'){
            $objAdmins->DrawEditMode($rid);
        }elseif($mode == 'details'){
            $objAdmins->DrawDetailsMode($rid);
        }
    }
    draw_content_end();
}else{
    draw_title_bar(_ADMIN);
    draw_important_message(_NOT_AUTHORIZED);
}
