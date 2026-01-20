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

	$objChannelManagerLog = new ChannelManagerLog();

	if($action=='add'){
		$mode = 'add';
	}elseif($action=='create'){
		if($objChannelManagerLog->AddRecord()){
			$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objChannelManagerLog->error, false);
			$mode = 'add';
		}
	}elseif($action=='edit'){
		$mode = 'edit';
	}elseif($action=='update'){
		if($objChannelManagerLog->UpdateRecord($rid)){
			$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objChannelManagerLog->error, false);
			$mode = 'edit';
		}
	}elseif($action=='delete'){
		if($objChannelManagerLog->DeleteRecord($rid)){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objChannelManagerLog->error, false);
		}
		$mode = 'view';
	}elseif($action=='delete_all'){
		if($objChannelManagerLog->DeleteAllRecord()){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objChannelManagerLog->error, false);
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
	draw_title_bar(prepare_breadcrumbs(array(_MODULES=>'',_CHANNEL_MANAGER=>'',_CHANNEL_MANAGER_LOG=>'',ucfirst($action)=>'')));

	//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
	echo $msg;

	draw_content_start();
	if($mode == 'view'){
		echo prepare_permanent_link('javascript:void(\'delete_all\')', '[ '._DELETE_ALL.' ]', '', '', '', 'onclick="if(confirm(\''._DELETE_ALL_ALERT.'\')) appGoToPage(\'index.php?admin=mod_channel_manager_log\', \'&mg_action=delete_all&token='.Application::Get('token').'\', \'post\');"');
		$objChannelManagerLog->DrawViewMode();
	}elseif($mode == 'add'){
		$objChannelManagerLog->DrawAddMode();
	}elseif($mode == 'edit'){
		$objChannelManagerLog->DrawEditMode($rid);
	}elseif($mode == 'details'){
		$objChannelManagerLog->DrawDetailsMode($rid);
	}
	draw_content_end();
}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

