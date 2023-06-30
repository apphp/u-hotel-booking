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

if($objLogin->IsLoggedInAs('owner','mainadmin') && Modules::IsModuleInstalled('news')){	

	$action = MicroGrid::GetParameter('action');
	$rid    = MicroGrid::GetParameter('rid');
	$mode   = 'view';
	$msg    = '';
	
	$objNewsSubscribed = new NewsSubscribed();
	
	if($action=='add'){		
		$mode = 'add';
	}elseif($action=='create'){
		if($objNewsSubscribed->AddRecord()){
			$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objNewsSubscribed->error, false);
			$mode = 'add';
		}
	}elseif($action=='edit'){
		$mode = 'edit';
	}elseif($action=='update'){
		if($objNewsSubscribed->UpdateRecord($rid)){
			$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objNewsSubscribed->error, false);
			$mode = 'edit';
		}		
	}elseif($action=='delete'){
		if($objNewsSubscribed->DeleteRecord($rid)){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objNewsSubscribed->error, false);
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
	draw_title_bar(prepare_breadcrumbs(array(_MODULES=>'',_NEWS=>'',_SUBSCRIPTION_MANAGEMENT=>'',ucfirst($action)=>'')));

	echo $msg;

	draw_content_start();
	if($mode == 'view'){		
		$objNewsSubscribed->DrawViewMode();	
	}elseif($mode == 'add'){		
		$objNewsSubscribed->DrawAddMode();		
	}elseif($mode == 'edit'){		
		$objNewsSubscribed->DrawEditMode($rid);		
	}elseif($mode == 'details'){		
		$objNewsSubscribed->DrawDetailsMode($rid);		
	}
	draw_content_end();

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}
