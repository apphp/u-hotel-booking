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

if($objLogin->IsLoggedInAs('owner','mainadmin','admin')){

	$action = MicroGrid::GetParameter('action');
	$rid    = MicroGrid::GetParameter('rid');
	$mode   = 'view';
	$msg    = '';	

	$objLanguages = new Languages();
	
	if($action=='add'){		
		$mode = 'add';
	}elseif($action=='create'){
		if($objLanguages->AddRecord()){		
			$msg = draw_success_message(_LANGUAGE_ADDED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objLanguages->error, false);
			$mode = 'add';
		}
	}elseif($action=='edit'){
		$mode = 'edit';
	}elseif($action=='update'){
		if($objLanguages->UpdateRecord($rid)){
			$msg = draw_success_message(_LANGUAGE_EDITED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objLanguages->error, false);
			$mode = 'edit';
		}		
	}elseif($action=='delete'){
		if($objLanguages->DeleteRecord($rid)){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objLanguages->error, false);
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
	draw_title_bar(prepare_breadcrumbs(array(_LANGUAGES_SETTINGS=>'',_LANGUAGES=>'',ucfirst($action)=>'')));

	echo $msg;

	draw_content_start();
	if($mode == 'view'){		
		$objLanguages->DrawViewMode();	
	}elseif($mode == 'add'){		
		$objLanguages->DrawAddMode();		
	}elseif($mode == 'edit'){		
		$objLanguages->DrawEditMode($rid);		
	}elseif($mode == 'details'){		
		$objLanguages->DrawDetailsMode($rid);		
	}
	draw_content_end();

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}
