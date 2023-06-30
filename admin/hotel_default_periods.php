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

if($objLogin->IsLoggedInAs('owner','mainadmin','hotelowner') && ModulesSettings::Get('rooms', 'allow_default_periods') == 'yes'){	

	$action = MicroGrid::GetParameter('action');
	$rid    = MicroGrid::GetParameter('rid');
	$hotel_id = MicroGrid::GetParameter('hid', false);
	$mode   = 'view';
	$msg    = '';
	
	$objHotels = new Hotels();
	$hotel_info = $objHotels->GetHotelFullInfo($hotel_id);
	
	if(!empty($hotel_id) && $objLogin->AssignedToHotel($hotel_id) && count($hotel_info) > 0){
		
		$objHotelPeriods = new HotelPeriods($hotel_id);
		
		if($action=='add'){		
			$mode = 'add';
		}elseif($action=='create'){
			if($objHotelPeriods->AddRecord()){		
				$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
				$mode = 'view';
			}else{
				$msg = draw_important_message($objHotelPeriods->error, false);
				$mode = 'add';
			}
		}elseif($action=='edit'){
			$mode = 'edit';
		}elseif($action=='update'){
			if($objHotelPeriods->UpdateRecord($rid)){
				$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
				$mode = 'view';
			}else{
				$msg = draw_important_message($objHotelPeriods->error, false);
				$mode = 'edit';
			}		
		}elseif($action=='delete'){
			if($objHotelPeriods->DeleteRecord($rid)){
				$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
			}else{
				$msg = draw_important_message($objHotelPeriods->error, false);
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
		draw_title_bar(
			prepare_breadcrumbs(array(
				(FLATS_INSTEAD_OF_HOTELS ? _FLATS_MANAGEMENT : _HOTELS_MANAGEMENT)	=> 'index.php?admin=hotels_info',
				(FLATS_INSTEAD_OF_HOTELS ? _FLATS_INFO : _HOTELS_INFO)	=> 'index.php?admin=hotels_info',
				$hotel_info['name']=>'',
				_PERIODS=>($action ? 'index.php?admin=hotel_default_periods&hid='.$hotel_id : ''),
				ucfirst($action)		=> ''
			),
			prepare_permanent_link('index.php?admin=hotels_info', _BUTTON_BACK)
		));

		//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
		if($mode == 'view' && $msg == ''){
			$msg = draw_message(_DEFAULT_PERIODS_ALERT, false);
		}
		echo $msg;
	
		draw_content_start();
		if($mode == 'view'){		
			$objHotelPeriods->DrawViewMode();	
		}elseif($mode == 'add'){		
			$objHotelPeriods->DrawAddMode();		
		}elseif($mode == 'edit'){		
			$objHotelPeriods->DrawEditMode($rid);		
		}elseif($mode == 'details'){		
			$objHotelPeriods->DrawDetailsMode($rid);		
		}
		draw_content_end();		
	}else{
		draw_title_bar(
            (FLATS_INSTEAD_OF_HOTELS
                ? prepare_breadcrumbs(array(_FLAT_MANAGEMENT=>'',_FLATS_INFO=>'',_PERIODS=>''))
                : prepare_breadcrumbs(array(_HOTEL_MANAGEMENT=>'',_HOTELS_INFO=>'',_PERIODS=>''))
            ),
			prepare_permanent_link('index.php?admin=hotels_info', _BUTTON_BACK)
		);
		draw_important_message(FLATS_INSTEAD_OF_HOTELS ? _OWNER_NOT_ASSIGNED_TO_FLAT : _OWNER_NOT_ASSIGNED_TO_HOTEL);
	}
}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}
