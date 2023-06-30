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

if(ModulesSettings::Get('booking', 'allow_separate_gateways') == 'no'){
    redirect_to('index.php?admin=hotel_owners_management');
}elseif(($objLogin->IsLoggedInAs('owner', 'mainadmin')) && Modules::IsModuleInstalled('booking')){

	$action 	        = MicroGrid::GetParameter('action');
	$rid    	        = MicroGrid::GetParameter('rid');
	$mode   	        = 'view';
	$msg 		        = '';
    $hotel_owner_id    	= Application::Get('hotel_owner_id');

    $objHotelPaymentGateways = new HotelPaymentGateways();

    if($action=='edit'){
		$mode = 'edit';
	}elseif($action=='update'){
        $info = $objHotelPaymentGateways->GetInfoByID($rid);
        $hotel_id = isset($info['hotel_id']) ? $info['hotel_id'] : '';
        if(!$objLogin->IsLoggedInAs('owner', 'mainadmin') || in_array($hotel_id, AdminsAccounts::getHotelListForHotelOwner($hotel_owner_id))){
            if($objHotelPaymentGateways->UpdateRecord($rid)){
                $msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
                $mode = 'view';
            }else{
                $msg = draw_important_message($objHotelPaymentGateways->error, false);
                $mode = 'edit';
            }
        }else{
            $msg = draw_important_message(_WRONG_PARAMETER_PASSED, false);
            $mode = 'view';
        }
	}else{
        $action = '';
	}

	// Start main content
    draw_title_bar(prepare_breadcrumbs(array(
            _ACCOUNTS				=> '',
            _ADMINS_MANAGEMENT		=> (!empty($action) ? 'index.php?admin=admins_management' : ''),
            FLATS_INSTEAD_OF_HOTELS ? _FLAT_OWNERS : _HOTEL_OWNERS => '',
            FLATS_INSTEAD_OF_HOTELS ? _FLAT_PAYMENT_GATEWAYS : _HOTEL_PAYMENT_GATEWAYS => '',
            ucfirst($action)		=> ''
        )
    ));

	echo $msg;

	draw_content_start();

	// Check if hotel owner is not assigned to any hotel
	$allow_viewing = true;
	if($objLogin->IsLoggedInAs('owner', 'mainadmin') && !empty($hotel_owner_id)){
		$hotels_list = AdminsAccounts::getHotelListForHotelOwner($hotel_owner_id);
		if(empty($hotels_list)){
			$allow_viewing = false;
			echo draw_important_message(_OWNER_NOT_ASSIGNED, false);
		}
	}

	if($allow_viewing){
		if($mode == 'view'){
			$objHotelPaymentGateways->DrawViewMode();
		}elseif($mode == 'edit'){
			$objHotelPaymentGateways->DrawEditMode($rid);
		}
	}
	draw_content_end();

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

