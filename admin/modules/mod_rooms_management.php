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
	
if(Modules::IsModuleInstalled('rooms') && 
  ($objLogin->IsLoggedInAs('owner','mainadmin','regionalmanager', 'hotelowner'))
){
	
	$action 	= MicroGrid::GetParameter('action');
	$rid    	= MicroGrid::GetParameter('rid');
	$mode   	= 'view';
	$msg 		= '';
	
	$hotel_id = Rooms::GetRoomInfo($rid, 'hotel_id');	
    $objRooms = new Rooms();
    
    // Check hotel owner has permissions to edit this hotel's info
    if($objLogin->IsLoggedInAs('hotelowner', 'regionalmanager')){
        $hotel_id = null;
        if(in_array($action, array('create', 'update'))){
            $hotel_id = MicroGrid::GetParameter('hotel_id', false);	
        }elseif(in_array($action, array('edit', 'details', 'delete'))){
            $info = $objRooms->GetInfoByID($rid);
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
        if($objRooms->AddRecord()){
            $msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
            $mode = 'view';
        }else{
            $msg = draw_important_message($objRooms->error, false);
            $mode = 'add';
        }
    }elseif($action=='edit'){
        $mode = 'edit';
    }elseif($action=='update'){
        if($objRooms->UpdateRecord($rid)){
            $msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
            $mode = 'view';
        }else{
            $msg = draw_important_message($objRooms->error, false);
            $mode = 'edit';
        }		
    }elseif($action=='delete'){
        if($objRooms->DeleteRecord($rid)){
            $msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
        }else{
            $msg = draw_important_message($objRooms->error, false);
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
	draw_title_bar(
		prepare_breadcrumbs(array(
			(FLATS_INSTEAD_OF_HOTELS ? _FLATS_MANAGEMENT : _HOTELS_MANAGEMENT)	=> 'index.php?admin=hotels_info',
			(FLATS_INSTEAD_OF_HOTELS ? _FLATS : _HOTELS_AND_ROOMS)	=> 'index.php?admin=hotels_info',
			_ROOMS_MANAGEMENT => ($action ? 'index.php?admin=mod_rooms_management' : ''),
			ucfirst($action) => ''
		)
	));

    //if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
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
    }
    
    if($allow_viewing){
        if($mode == 'view'){		
            $objRooms->DrawViewMode();				
        }elseif($mode == 'add'){
            $objRooms->DrawAddMode();		
        }elseif($mode == 'edit'){		
            $objRooms->DrawEditMode($rid);				
        }elseif($mode == 'details'){		
            $objRooms->DrawDetailsMode($rid);		
        }
        
        // Discount type pre/postfix handler
        if(in_array($mode, array('add', 'edit', 'details'))){
            echo '<script type="text/javascript">
                    $(document).ready(function(){
                        setDiscountType("'.$objRooms->GetFieldInfo('discount_guests_type').'", "guests");
                        $("#discount_guests_type").change(function(){
                            setDiscountType($(this).val(), "guests");
                        });							
                        setDiscountType("'.$objRooms->GetFieldInfo('discount_night_type').'", "night");
                        $("#discount_night_type").change(function(){
                            setDiscountType($(this).val(), "night");
                        });							
                        setRefundMoneyType("'.$objRooms->GetFieldInfo('refund_money_type').'");
                        $("#refund_money_type").change(function(){
                            setRefundMoneyType($(this).val());
                        });							
                        function setDiscountType(dtype, className){
                            if(dtype == "1"){
                                $(".discount-" + className + "-price").html("");
                                $(".discount-" + className + "-percent").html("%");
                            }else{
                                $(".discount-" + className + "-price").html($(".discount-" + className + "-price").data("currency"));
                                $(".discount-" + className + "-percent").html("");									
                            }								
                        }
                        function setRefundMoneyType(dtype){
                            if(dtype == "2"){
                                $("#mg_row_refund_money_value").show();
                                $(".refund-money-price").html("");
                                $(".refund-money-percent").html("%");
                            }
							else if(dtype == "1"){
                                $("#mg_row_refund_money_value").show();
                                $(".refund-money-price").html($(".refund-money-price").data("currency"));
                                $(".refund-money-percent").html("");
                            }
							else{
                                $("#mg_row_refund_money_value").hide();
                            }			
                        }
                    });
            </script>';
        }
    }
    draw_content_end();
}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

