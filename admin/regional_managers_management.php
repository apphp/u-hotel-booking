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
	
if($objLogin->IsLoggedInAs('owner','mainadmin')){
	
	$action 	= MicroGrid::GetParameter('action');
	$rid    	= MicroGrid::GetParameter('rid');
	$email		= MicroGrid::GetParameter('email', false);
	$mode   	= 'view';
	$msg 		= '';
	
	$objAdminAccounts = new AdminsAccounts('regionalmanager', 'regional_admins');

	if($action=='add'){		
		$mode = 'add';
	}elseif($action=='create'){
		if($objAdminAccounts->AddRecord()){
			$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objAdminAccounts->error, false);
			$mode = 'add';
		}
	}elseif($action=='edit'){
		$mode = 'edit';
	}elseif($action=='update'){
		if($objAdminAccounts->UpdateRecord($rid)){
			$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objAdminAccounts->error, false);
			$mode = 'edit';
		}		
	}elseif($action=='delete'){
		if($objAdminAccounts->DeleteRecord($rid)){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objAdminAccounts->error, false);
		}
		$mode = 'view';
	}elseif($action=='details'){		
		$mode = 'details';		
	}elseif($action=='cancel_add'){		
		$mode = 'view';		
	}elseif($action=='cancel_edit'){				
		$mode = 'view';
	}elseif($action=='reactivate'){
		if(AdminsAccounts::Reactivate($email)){	
			$msg = draw_success_message(_EMAIL_SUCCESSFULLY_SENT, false);
		}else{
			$msg = draw_important_message(Customers::GetStaticError(), false);
		}		
		$mode = 'view';
	}
	
	// Start main content
	draw_title_bar(prepare_breadcrumbs(array(
			_ACCOUNTS				=> '',
			_ADMINS_MANAGEMENT		=> (!empty($action) ? 'index.php?admin=admins_management' : ''),
			_REGIONAL_MANAGERS 		=> '',
			ucfirst($action)		=> ''
		)
	));

	//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
	echo $msg;

	draw_content_start();	
	if($mode == 'view'){
		$objAdminAccounts->DrawViewMode();	
	}elseif($mode == 'add'){		
		$objAdminAccounts->DrawAddMode();		
	}elseif($mode == 'edit'){		
		$objAdminAccounts->DrawEditMode($rid);		
	}elseif($mode == 'details'){		
		$objAdminAccounts->DrawDetailsMode($rid);		
	}
	
	draw_content_end();

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}
