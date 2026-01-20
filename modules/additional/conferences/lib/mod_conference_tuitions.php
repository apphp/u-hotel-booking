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

if($objLogin->IsLoggedInAs('owner','mainadmin') && Modules::IsModuleInstalled('conferences')){

	$action = MicroGrid::GetParameter('action');
	$rid    = MicroGrid::GetParameter('rid');
	$conference_id = MicroGrid::GetParameter('cid', false);
	$mode   = 'view';
	$msg    = '';
	
		$objConferenceTuition = new ConferenceTuitions($conference_id);
	
	if($action=='add'){
		$mode = 'add';
	}elseif($action=='create'){
		if($objConferenceTuition->AddRecord()){		
			$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objConferenceTuition->error, false);
			$mode = 'add';
		}
	}elseif($action=='edit'){
		$mode = 'edit';
	}elseif($action=='update'){
		if($objConferenceTuition->UpdateRecord($rid)){
			$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objConferenceTuition->error, false);
			$mode = 'edit';
		}		
	}elseif($action=='delete'){
		if($objConferenceTuition->DeleteRecord($rid)){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objConferenceTuition->error, false);
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
	draw_title_bar(
		prepare_breadcrumbs(array(_MODULES=>'index.php?admin=modules',_CONFERENCES=>'index.php?admin=mod_conference_types',_CONFERENCES_MANAGEMENT=>'index.php?admin=mod_conferences_management',_CONFERENCE_TUITIONS=>'',ucfirst($action)=>'')),
		prepare_permanent_link(($mode == 'view' ? 'index.php?admin=mod_conferences_management' : 'index.php?admin=mod_conference_tuitions&cid='.(int)$conference_id), _BUTTON_BACK)
	);

	//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
	echo $msg;

	draw_content_start();
	if($mode == 'view'){		
		$objConferenceTuition->DrawViewMode();	
	}elseif($mode == 'add'){		
		$objConferenceTuition->DrawAddMode();		
	}elseif($mode == 'edit'){		
		$objConferenceTuition->DrawEditMode($rid);		
	}elseif($mode == 'details'){		
		$objConferenceTuition->DrawDetailsMode($rid);		
	}
	draw_content_end();		
}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

