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
	$mode   	= 'view';
	$msg 		= '';
	
	$objAdmins = new AdminsAccounts($objLogin->GetLoggedType(), 'site_admins');
	
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
	draw_title_bar(prepare_breadcrumbs(array(
			_ACCOUNTS				=> '',
			_ADMINS_MANAGEMENT		=> (!empty($action) ? 'index.php?admin=admins_management' : ''),
			_ADMINS					=> '',
			ucfirst($action)		=> ''
		)
	));
	
	//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
	echo $msg;

	draw_content_start();	
	if($mode == 'view'){		
		$objAdmins->DrawViewMode();
	}elseif($mode == 'add'){		
		$objAdmins->DrawAddMode();		
	}elseif($mode == 'edit'){		
		// It is necessary to work around a problem with the display html via SQL
		echo '<script type="text/javascript">
			function confirm_recreate(){
				return confirm("'.htmlspecialchars(_MESSAGE_FOR_RECREATE).'");
			}
		</script>';
		$objAdmins->DrawEditMode($rid);		
	}elseif($mode == 'details'){		
		$objAdmins->DrawDetailsMode($rid);		
	}
	draw_content_end();	

}else{	
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

