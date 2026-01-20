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

if($objLogin->IsLoggedInAs('agencyowner') && ModulesSettings::Get('car_rental', 'is_active') != 'no'){

	$action 	= MicroGrid::GetParameter('action');
	$rid    	= MicroGrid::GetParameter('rid');
	$mode   	= 'view';
	$msg 		= '';
	
	$objCarAgencyPaymentGateways = new CarAgencyPaymentGateways();
    
	if($action=='add'){		
		$mode = 'view';
	}elseif($action=='create'){
        $mode = 'view';
	}elseif($action=='edit'){
		$mode = 'edit';
	}elseif($action=='update'){
        $info = $objCarAgencyPaymentGateways->GetInfoByID($rid);
        $agency_id = isset($info['agency_id']) ? $info['agency_id'] : '';                
        if(in_array($agency_id, $objLogin->AssignedToCarAgencies())){
            if($objCarAgencyPaymentGateways->UpdateRecord($rid)){
                $msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
                $mode = 'view';
            }else{
                $msg = draw_important_message($objCarAgencyPaymentGateways->error, false);
                $mode = 'edit';
            }
        }else{
            $msg = draw_important_message(_WRONG_PARAMETER_PASSED, false);
            $mode = 'view';
        }        
	}elseif($action=='delete'){
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
	draw_title_bar(prepare_breadcrumbs(array(_BOOKINGS=>'',_SETTINGS=>'',_CAR_AGENCY_PAYMENT_GATEWAYS=>'',ucfirst($action)=>'')));
    	
	//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
	echo $msg;

	draw_content_start();	
	if($mode == 'view'){		
		$objCarAgencyPaymentGateways->DrawViewMode();	
	}elseif($mode == 'edit'){		
		$objCarAgencyPaymentGateways->DrawEditMode($rid);		
	}
	draw_content_end();	

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

