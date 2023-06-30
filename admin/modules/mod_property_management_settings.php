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

if($objLogin->IsLoggedInAs('owner','mainadmin') && Modules::IsModuleInstalled('property_management')){

	$action = MicroGrid::GetParameter('action');
	$rid    = MicroGrid::GetParameter('rid');
	$mode   = 'view';
	$msg    = '';
	
	$objPropertyManagementSettings = new ModulesSettings('property_management');
	
	if($action=='add'){		
		$mode = 'add';
	}elseif($action=='create'){
		if($objPropertyManagementSettings->AddRecord()){
			$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objPropertyManagementSettings->error, false);
			$mode = 'add';
		}
	}elseif($action=='edit'){
		$mode = 'edit';
	}elseif($action=='update'){
		if($objPropertyManagementSettings->UpdateRecord($rid)){
			$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objPropertyManagementSettings->error, false);
			$mode = 'edit';
		}		
	}elseif($action=='delete'){
		if($objPropertyManagementSettings->DeleteRecord($rid)){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objPropertyManagementSettings->error, false);
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
	draw_title_bar(prepare_breadcrumbs(array(
		_MODULES						=> '',
		_PROPERTY_MANAGEMENT			=> '',
		_PROPERTY_MANAGEMENT_SETTINGS	=> '',
		ucfirst($action)	=> ''
	)));
    echo '<br />';
	
	//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
	echo $msg;

	draw_content_start();
	if($mode == 'view'){		
		if($objLogin->IsLoggedInAs('owner','mainadmin')){
			$objPropertyManagementSettings->DrawOperationLinks(
				prepare_permanent_link('index.php?admin=mod_property_management_inventory', '[ ' . _PROPERTY_INVENTORY . ' ]') . ' &nbsp; ' .
				prepare_permanent_link('index.php?admin=mod_property_management_expenses', '[ ' . _PROPERTY_EXPENSES . ' ]') . ' &nbsp; ' .
				prepare_permanent_link('index.php?admin=mod_property_management_managers', '[ ' . _PROPERTY_MANAGERS . ' ]')
			);
		}
		$objPropertyManagementSettings->DrawViewMode();
	}elseif($mode == 'add'){		
		$objPropertyManagementSettings->DrawAddMode();
	}elseif($mode == 'edit'){		
		$objPropertyManagementSettings->DrawEditMode($rid);
	}elseif($mode == 'details'){ 
		$objPropertyManagementSettings->DrawDetailsMode($rid);
	}
	draw_content_end();

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

