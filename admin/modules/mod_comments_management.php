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

if($objLogin->IsLoggedInAs('owner','mainadmin') && Modules::IsModuleInstalled('comments')){	
	
    $pid    = isset($_REQUEST['pid']) ? prepare_input($_REQUEST['pid']) : '';
	$action = MicroGrid::GetParameter('action');
	$rid    = MicroGrid::GetParameter('rid');
	$mode   = 'view';
	$msg    = '';
	
	$objComments = new Comments($pid);
	$page_name = $objComments->GetPageName($pid); 
	
	if($action=='add'){		
		$mode = 'add';
	}elseif($action=='create'){
		if($objComments->AddRecord()){
			$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objComments->error, false);
			$mode = 'add';
		}
	}elseif($action=='edit'){
		$mode = 'edit';
	}elseif($action=='update'){
		if($objComments->UpdateRecord($rid)){
			$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objComments->error, false);
			$mode = 'edit';
		}		
	}elseif($action=='delete'){
		if($objComments->DeleteRecord($rid)){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objComments->error, false);
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
	draw_title_bar(prepare_breadcrumbs(array(_MODULES=>'',_COMMENTS=>'',_COMMENTS_MANAGEMENT=>'',(($page_name)?$page_name:'')=>'',ucfirst($action)=>'')));
	echo $msg;

	draw_content_start();
	if($mode == 'view'){		
		$objComments->DrawViewMode();	
	}elseif($mode == 'add'){		
		$objComments->DrawAddMode();		
	}elseif($mode == 'edit'){		
		$objComments->DrawEditMode($rid);		
	}elseif($mode == 'details'){		
		$objComments->DrawDetailsMode($rid);		
	}
	draw_content_end();

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}
