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

if($objLogin->IsLoggedInAs('owner','mainadmin','hotelowner','regionalmanager')){	

	$action = MicroGrid::GetParameter('action');
	$rid    = MicroGrid::GetParameter('rid');
	$hotel_id = MicroGrid::GetParameter('hid', false);
	$mode   = 'view';
	$msg    = '';
	
	$objHotels = new Hotels();
	$hotel_info = $objHotels->GetHotelFullInfo($hotel_id);

	if(!empty($hotel_id) && $objLogin->AssignedToHotel($hotel_id) && count($hotel_info) > 0){
		
		$objHotelImages = new HotelImages($hotel_id);
		
		if($action=='add'){		
			$mode = 'add';
		}elseif($action=='create'){
			if($objHotelImages->AddRecord()){		
				$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
				$mode = 'view';
			}else{
				$msg = draw_important_message($objHotelImages->error, false);
				$mode = 'add';
			}
		}elseif($action=='edit'){
			$mode = 'edit';
		}elseif($action=='update'){
			if($objHotelImages->UpdateRecord($rid)){
				$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
				$mode = 'view';
			}else{
				$msg = draw_important_message($objHotelImages->error, false);
				$mode = 'edit';
			}		
		}elseif($action=='delete'){
			if($objHotelImages->DeleteRecord($rid)){
				$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
			}else{
				$msg = draw_important_message($objHotelImages->error, false);
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
            (FLATS_INSTEAD_OF_HOTELS
                ? prepare_breadcrumbs(array(_FLAT_MANAGEMENT=>'',_FLATS_INFO=>'',$hotel_info['name']=>'',_IMAGES=>'',ucfirst($action)=>''))
                : prepare_breadcrumbs(array(_HOTEL_MANAGEMENT=>'',_HOTELS_INFO=>'index.php?admin=hotels_info',$hotel_info['name']=>'index.php?admin=hotels_info',_IMAGES=>'index.php?admin=hotel_upload_images&hid='.$hotel_id,ucfirst($action)=>''))
            ),
			prepare_permanent_link('index.php?admin=hotels_info', _BUTTON_BACK)
		);
	
		//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
		echo $msg;
	
		draw_content_start();
		if($mode == 'view'){		
			$objHotelImages->DrawViewMode();	
		}elseif($mode == 'add'){		
			$objHotelImages->DrawAddMode();		
		}elseif($mode == 'edit'){		
			$objHotelImages->DrawEditMode($rid);		
		}elseif($mode == 'details'){		
			$objHotelImages->DrawDetailsMode($rid);		
		}
		draw_content_end();		
	}else{
		draw_title_bar(
            (FLATS_INSTEAD_OF_HOTELS
                ? prepare_breadcrumbs(array(_FLAT_MANAGEMENT=>'',_FLATS_INFO=>'',_IMAGES=>''))
                : prepare_breadcrumbs(array(_HOTEL_MANAGEMENT=>'',_HOTELS_INFO=>'',_IMAGES=>''))
            ),
			prepare_permanent_link('index.php?admin=hotels_info', _BUTTON_BACK)
		);
		draw_important_message(FLATS_INSTEAD_OF_HOTELS ? _OWNER_NOT_ASSIGNED_TO_FLAT : _OWNER_NOT_ASSIGNED_TO_HOTEL);
	}
}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}
