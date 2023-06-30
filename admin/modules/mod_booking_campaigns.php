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

if($objLogin->IsLoggedInAs('owner','mainadmin','hotelowner','regionalmanager') && Modules::IsModuleInstalled('booking')){

	$action 	= MicroGrid::GetParameter('action');
	$rid    	= MicroGrid::GetParameter('rid');
	$mode   	= 'view';
	$msg 		= '';
	
	$objCampaigns = new Campaigns();
	
	// Check hotel owner has permissions to edit this hotel's info
	if($objLogin->IsLoggedInAs('hotelowner', 'regionalmanager')){
		$hotel_id = null;
		if(in_array($action, array('create', 'update'))){
			$hotel_id = MicroGrid::GetParameter('hotel_id', false);	
		}elseif(in_array($action, array('edit', 'details', 'delete'))){
			$info = $objCampaigns->GetInfoByID($rid);
			$hotel_id = isset($info['hotel_id']) ? $info['hotel_id'] : '';
			if(empty($hotel_id)){
				$hotel_id = '-99';
			}
		}
		
        if(!empty($hotel_id)){
            if($objLogin->IsLoggedInAs('hotelowner')){
                if(!in_array($hotel_id, $objLogin->AssignedToHotels())){
                    $msg = draw_important_message(_WRONG_PARAMETER_PASSED, false);
                }
            }else{
                if(!in_array($hotel_id, AccountLocations::GetHotels($objLogin->GetLoggedId()))){
                    $msg = draw_important_message(_WRONG_PARAMETER_PASSED, false);
                }
            }
            if(!empty($msg)){
                $action = '';
                $mode = 'view';
            }
        }
	}

	if($action=='add'){		
		$mode = 'add';
	}elseif($action=='create'){
		if($objCampaigns->AddRecord()){
			$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objCampaigns->error, false);
			$mode = 'add';
		}
	}elseif($action=='edit'){
		$mode = 'edit';
	}elseif($action=='update'){
		if($objCampaigns->UpdateRecord($rid)){
			$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objCampaigns->error, false);
			$mode = 'edit';
		}		
	}elseif($action=='delete'){
		if($objCampaigns->DeleteRecord($rid)){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objCampaigns->error, false);
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
	draw_title_bar(prepare_breadcrumbs(array(_BOOKINGS=>'',_PROMO_AND_DISCOUNTS=>'',_CAMPAIGNS_MANAGEMENT=>'',ucfirst($action)=>'')));
    	
	if($objSession->IsMessage('notice')) $msg = $objSession->GetMessage('notice');
	echo $msg;

	draw_content_start();	

	// Check if hotel owner is not assigned to any hotel
	$allow_viewing = true;
	if($objLogin->IsLoggedInAs('hotelowner')){
		$hotels_list = implode(',', $objLogin->AssignedToHotels());
		if(empty($hotels_list)){
			$allow_viewing = false;
			echo draw_important_message(_OWNER_NOT_ASSIGNED, false);
		}
    }elseif($objLogin->IsLoggedInAs('regionalmanager')){
        $all_hotels = AccountLocations::GetHotels($objLogin->GetLoggedId());
        if(empty($all_hotels)){
			echo draw_default_message(FLATS_INSTEAD_OF_HOTELS ? _REGIONAL_NOT_FLATS : _REGIONAL_NOT_HOTELS);
        }
	}
	
	if($allow_viewing){
		if($mode == 'view'){		
			$objCampaigns->DrawViewMode();	
		}elseif($mode == 'add'){		
			$objCampaigns->DrawAddMode();
		}elseif($mode == 'edit'){		
			$objCampaigns->DrawEditMode($rid);		
		}elseif($mode == 'details'){		
			$objCampaigns->DrawDetailsMode($rid);		
		}
		
		if(in_array($mode, array('add', 'edit'))){
			echo '<script type="text/javascript">
				function set_discount_type(){
					if($("#discount_type").val() == 1){
						$(".discount_percent").show();
						$(".discount_currency").hide();
					}else{
						$(".discount_currency").show();
						$(".discount_percent").hide();
					}
				}
				$(document).ready(function(){
					$("#discount_type").on("change", function(){
						set_discount_type();
					});
					set_discount_type();
				});
			</script>';
			
			echo '<style>
				.discount_currency { display: none;}
				.discount_percent { display: none;}
			</style>';
		}
	}
	draw_content_end();	

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

