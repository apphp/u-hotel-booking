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
	
if($objLogin->IsLoggedInAs('owner','mainadmin', 'regionalmanager') && Modules::IsModuleInstalled('customers') && ModulesSettings::Get('customers', 'allow_agencies') == 'yes'){
	
	$action 	= MicroGrid::GetParameter('action');
	$rid    	= MicroGrid::GetParameter('rid');
	$email		= MicroGrid::GetParameter('email', false);
	$mode   	= 'view';
	$msg 		= '';
	
	$objCustomers = new Customers('agencies');

	if($action=='add'){		
		$mode = 'add';
	}elseif($action=='create'){
		if($objCustomers->AddRecord()){
			$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objCustomers->error, false);
			$mode = 'add';
		}
	}elseif($action=='edit'){
		$mode = 'edit';
	}elseif($action=='update'){
		if($objCustomers->UpdateRecord($rid)){
			$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objCustomers->error, false);
			$mode = 'edit';
		}		
	}elseif($action=='delete'){
		if($objCustomers->DeleteRecord($rid)){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objCustomers->error, false);
		}
		$mode = 'view';
	}elseif($action=='details'){		
		$mode = 'details';		
	}elseif($action=='cancel_add'){		
		$mode = 'view';		
	}elseif($action=='cancel_edit'){				
		$mode = 'view';
	}elseif($action=='set_status'){
		$mode = 'view';
		$msg = Customers::ChangeStatus($rid, 1);
	}elseif($action=='reset_status'){
		$mode = 'view';
		$msg = Customers::ChangeStatus($rid, 0);
	}elseif($action=='reactivate'){
		if(Customers::Reactivate($email)){	
			$msg = draw_success_message(_EMAIL_SUCCESSFULLY_SENT, false);
		}else{
			$msg = draw_important_message(Customers::GetStaticError(), false);
		}		
		$mode = 'view';
	}
	
	// Start main content
	draw_title_bar(
		prepare_breadcrumbs(array(
			_ACCOUNTS				=> '',
			_CUSTOMERS_MANAGEMENT	=> 'index.php?admin=mod_customers_management',
			_TRAVEL_AGENCIES		=> (empty($action) ? '' : 'index.php?admin=mod_customers_agencies'),
			ucfirst($action)		=> ''
		)),
		prepare_permanent_link('index.php?admin=mod_customers_settings', '<img src="images/settings.png" title="'._CUSTOMERS_SETTINGS.'" />')
	);
	
	//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
	echo $msg;

	draw_content_start();	
	if($mode == 'view'){
		$objCustomers->DrawViewMode();	
	}elseif($mode == 'add'){		
		$objCustomers->DrawAddMode();		
	}elseif($mode == 'edit'){		
		$objCustomers->DrawEditMode($rid);		
	}elseif($mode == 'details'){		
		$objCustomers->DrawDetailsMode($rid);		
	}
	
	draw_content_end();

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}
