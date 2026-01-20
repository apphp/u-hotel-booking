<?php
/**
* @project uHotelBooking
* @copyright (c) 2012 - 2014 ApPHP
* @author ApPHP <info@apphp.com>
* @license http://www.gnu.org/licenses/
*/

// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------

if($objLogin->IsLoggedInAs('owner','mainadmin')){
	
	$action 	= MicroGrid::GetParameter('action');
	$rid    	= MicroGrid::GetParameter('rid');
	$mode   = 'view';
	$msg 	= '';
	
	$objSpecialities = new Specialities();

	if($action=='add'){		
		$mode = 'add';
	}elseif($action=='create'){
		if($objSpecialities->AddRecord()){
			$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objSpecialities->error, false);
			$mode = 'add';
		}
	}elseif($action=='edit'){
		$mode = 'edit';
	}elseif($action=='update'){
		if($objSpecialities->UpdateRecord($rid)){
			$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objSpecialities->error, false);
			$mode = 'edit';
		}		
	}elseif($action=='delete'){
		if($objSpecialities->DeleteRecord($rid)){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objSpecialities->error, false);
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
	draw_title_bar(prepare_breadcrumbs(array(_MODULES=>'',_SPECIALITIES_MANAGEMENT=>'',ucfirst($action)=>'')));
	
	//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
	echo $msg;

	draw_content_start();	
	if($mode == 'view'){		
		$objSpecialities->DrawViewMode();	
	}elseif($mode == 'add'){		
		$objSpecialities->DrawAddMode();		
	}elseif($mode == 'edit'){		
		$objSpecialities->DrawEditMode($rid);		
	}elseif($mode == 'details'){		
		$objSpecialities->DrawDetailsMode($rid);		
	}
	draw_content_end();

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}
?>
