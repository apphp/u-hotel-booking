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

if($objLogin->IsLoggedInAs('owner','mainadmin') && Modules::IsModuleInstalled('booking')){

	$action 	= MicroGrid::GetParameter('action');
	$rid    	= MicroGrid::GetParameter('rid');
	$mode   	= 'view';
	$msg 		= '';
	
	$objCurrencies = new Currencies();
	
	if($action=='add'){		
		$mode = 'add';
	}elseif($action=='create'){
		if($objCurrencies->AddRecord()){
			$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objCurrencies->error, false);
			$mode = 'add';
		}
	}elseif($action=='edit'){
		$mode = 'edit';
	}elseif($action=='update'){
		if($objCurrencies->UpdateRecord($rid)){
			$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objCurrencies->error, false);
			$mode = 'edit';
		}		
	}elseif($action=='delete'){
		if($objCurrencies->DeleteRecord($rid)){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objCurrencies->error, false);
		}
		$mode = 'view';
	}elseif($action=='details'){		
		$mode = 'details';		
	}elseif($action=='cancel_add'){		
		$mode = 'view';		
	}elseif($action=='cancel_edit'){				
		$mode = 'view';
	}elseif($action=='update_rates'){
		if($objCurrencies->UpdateCurrencyRates()){
			$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED.$objCurrencies->alert, false);
		}else{
			$msg = draw_important_message($objCurrencies->error, false);
		}
		
		$objSession->SetMessage('notice', $msg);
		redirect_to('index.php?admin=mod_booking_currencies');
	}
	
	// Start main content
	draw_title_bar(prepare_breadcrumbs(array(_BOOKINGS=>'',_SETTINGS=>'',_CURRENCIES_MANAGEMENT=>'',ucfirst($action)=>'')));
    	
	if($objSession->IsMessage('notice')) $msg = $objSession->GetMessage('notice');
	if($mode == 'view' && $msg == ''){
		$msg = draw_message(_CURRENCIES_DEFAULT_ALERT, false);		
	}
	echo $msg;

	draw_content_start();	
	if($mode == 'view'){		
		$objCurrencies->DrawOperationLinks(prepare_permanent_link('index.php?admin=mod_booking_currencies&mg_action=update_rates', '[ '._UPDATE_CURRENCY_RATE.' ]'));
		$objCurrencies->DrawViewMode();	
	}elseif($mode == 'add'){		
		$objCurrencies->DrawAddMode();		
	}elseif($mode == 'edit'){		
		$objCurrencies->DrawEditMode($rid);		
	}elseif($mode == 'details'){		
		$objCurrencies->DrawDetailsMode($rid);		
	}
	draw_content_end();	

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

