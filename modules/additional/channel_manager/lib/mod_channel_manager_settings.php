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

if($objLogin->IsLoggedInAs('owner','mainadmin') && Modules::IsModuleInstalled('channel_manager')){

	$action = MicroGrid::GetParameter('action');
	$rid    = MicroGrid::GetParameter('rid');
	$mode   = 'view';
	$msg    = '';
	
	$objChannelManager = new ModulesSettings('channel_manager');
	
	if($action=='add'){		
		$mode = 'add';
	}elseif($action=='create'){
		if($objChannelManager->AddRecord()){
			$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objChannelManager->error, false);
			$mode = 'add';
		}
	}elseif($action=='edit'){
		$mode = 'edit';
	}elseif($action=='update'){
		if($objChannelManager->UpdateRecord($rid)){
			$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objChannelManager->error, false);
			$mode = 'edit';
		}		
	}elseif($action=='delete'){
		if($objChannelManager->DeleteRecord($rid)){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objChannelManager->error, false);
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
	draw_title_bar(prepare_breadcrumbs(array(_MODULES=>'',_CHANNEL_MANAGER=>'',_CHANNEL_MANAGER_SETTINGS=>'',ucfirst($action)=>'')));
    echo '<br />';
	
	//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
	echo $msg;

	draw_content_start();
	if($mode == 'view'){		
		$objChannelManager->DrawViewMode();	
	}elseif($mode == 'add'){		
		$objChannelManager->DrawAddMode();		
	}elseif($mode == 'edit'){		
		$objChannelManager->DrawEditMode($rid);		
	}elseif($mode == 'details'){ 
		$objChannelManager->DrawDetailsMode($rid);		
	}
	draw_content_end();

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

