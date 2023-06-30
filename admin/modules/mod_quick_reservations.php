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

if($objLogin->IsLoggedInAs('owner','mainadmin','admin','hotelowner') &&
   Modules::IsModuleInstalled('booking') &&
   in_array(ModulesSettings::Get('booking', 'is_active'), array('global', 'back-end'))
){
    $act		    = isset($_POST['act']) ? prepare_input($_POST['act']) : '';
    $hotel_id		= isset($_POST['hotel_id']) ? (int)$_POST['hotel_id'] : '';
    $room_id        = isset($_POST['room_id']) ? (int)$_POST['room_id'] : '';
    $count_rooms    = isset($_POST['count_rooms']) ? (int)$_POST['count_rooms'] : '';

    $american_format = $objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? true : false;
    $checkin_date   = isset($_POST['checkin_date']) ? prepare_input($_POST['checkin_date']) : date($american_format ? 'm/d/Y' : 'd/m/Y');
    $checkin_parts  = explode('/', $checkin_date);
    $checkin_month  = isset($checkin_parts[$american_format ? 0 : 1]) ? convert_to_decimal((int)$checkin_parts[$american_format ? 0 : 1]) : '';
    $checkin_day    = isset($checkin_parts[$american_format ? 1 : 0]) ? convert_to_decimal((int)$checkin_parts[$american_format ? 1 : 0]) : '';
    $checkin_year   = isset($checkin_parts[2]) ? (int)$checkin_parts[2] : '';

    $checkout_date  = isset($_POST['checkout_date']) ? prepare_input($_POST['checkout_date']) : date($american_format ? 'm/d/Y' : 'd/m/Y', time() + (24 * 60 * 60));
    $checkout_parts = explode('/', $checkout_date);
    $checkout_month = isset($checkout_parts[$american_format ? 0 : 1]) ? convert_to_decimal((int)$checkout_parts[$american_format ? 0 : 1]) : '';
    $checkout_day   = isset($checkout_parts[$american_format ? 1 : 0]) ? convert_to_decimal((int)$checkout_parts[$american_format ? 1 : 0]) : '';
    $checkout_year  = isset($checkout_parts[2]) ? (int)$checkout_parts[2] : '';

	// Start main content
	draw_title_bar(prepare_breadcrumbs(array(_BOOKINGS=>'',_BOOKINGS_MANAGEMENT=>'',_QUICK_RESERVATIONS=>'')));

	draw_content_start();
    $allow_viewing = true;
    $show_message = false;
    $reservation_success = false;
	if($objLogin->IsLoggedInAs('hotelowner')){
		$arr_hotels = $objLogin->AssignedToHotels();
		if(empty($arr_hotels)){
            $allow_viewing = false;
            echo draw_important_message(_OWNER_NOT_ASSIGNED, false);
        }
    }

    if($allow_viewing){
        if($act == 'send'){
            if($objLogin->IsLoggedInAs('hotelowner') && !in_array($hotel_id, $objLogin->AssignedToHotels())){
                $show_message = true;
                echo draw_important_message(_WRONG_PARAMETER_PASSED, false);
            }elseif(!empty($room_id)){
                $room_info = Rooms::GetRoomInfo($room_id);
                if($objLogin->IsLoggedInAs('hotelowner') && !in_array($room_info['hotel_id'], $arr_hotels)){
                    $show_message = true;
                    echo draw_important_message(_WRONG_PARAMETER_PASSED, false);
                }elseif($room_info['hotel_id'] != $hotel_id){
                    $show_message = true;
                    echo draw_important_message(_WRONG_PARAMETER_PASSED, false);
                }elseif(empty($room_info)){
                    $show_message = true;
                    echo draw_important_message(_WRONG_PARAMETER_PASSED, false);
                }elseif(!empty($count_rooms)){
                    $min_max_rooms = Rooms::GetMinMaxCountRooms($checkin_year.'-'.$checkin_month.'-'.$checkin_day, $checkout_year.'-'.$checkout_month.'-'.$checkout_day, $room_id);
                    if(empty($min_max_rooms) || empty($min_max_rooms['max_rooms']) || $min_max_rooms['min_rooms'] > $count_rooms || $min_max_rooms['max_rooms'] < $count_rooms){
                        echo draw_important_message(_WRONG_PARAMETER_PASSED, false);
                    }
                }else{
                    $show_message = true;
                    echo draw_important_message(_PLEASE_SELECT_NUMBER_ROOMS, false);
                }
            }else{
                $show_message = true;
                echo draw_important_message(_PLEASE_SELECT_ROOM, false);
            }

            if(!$show_message){
                $nights = nights_diff($checkin_year.'-'.$checkin_month.'-'.$checkin_day, $checkout_year.'-'.$checkout_month.'-'.$checkout_day);
                $search_availability_period = ModulesSettings::Get('rooms', 'search_availability_period');
                $search_availability_period_in_days = ($search_availability_period * 365) + 1;
                // max date - 2 years
                $max_date_unix = mktime(0, 0, 0, date('m'), date('d'), date('Y') + 2) + (24 * 3600);
                if(ModulesSettings::Get('booking', 'allow_booking_in_past') == 'yes' && $objLogin->IsLoggedInAsAdmin()){
                    $min_date_unix = mktime(0, 0, 0, date('m'), date('d'), date('Y') - 1);
                }else{
                    $min_date_unix = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                }
                if($nights > $search_availability_period_in_days){
                    draw_important_message(str_replace('_DAYS_', $search_availability_period_in_days, _MAXIMUM_PERIOD_ALERT));
                }elseif(empty($checkin_date) || empty($checkout_date)){
                    draw_important_message(_WRONG_PARAMETER_PASSED);
                }elseif($min_date_unix > mktime(0, 0, 0, $checkin_month, $checkin_day, $checkin_year)){
                    draw_important_message(_WRONG_PARAMETER_PASSED);
                }elseif($max_date_unix < mktime(0, 0, 0, $checkout_month, $checkout_day, $checkout_year)){
                    draw_important_message(_WRONG_PARAMETER_PASSED);
                }elseif(!checkdate($checkout_month, $checkout_day, $checkout_year)){
                    draw_important_message(_WRONG_CHECKOUT_DATE_ALERT);
                }elseif(ModulesSettings::Get('booking', 'allow_booking_in_past') != 'yes' && $checkin_year.$checkin_month.$checkin_day < date('Ymd')){
                    draw_important_message(_PAST_TIME_ALERT);		
                }elseif($nights < 1){
                    draw_important_message(_BOOK_ONE_NIGHT_ALERT);
                }else{
                    $objReservation = new Reservation();
                    $objReservation->AddToReservation($room_id, $checkin_year.'-'.$checkin_month.'-'.$checkin_day, $checkout_year.'-'.$checkout_month.'-'.$checkout_day, $nights, $count_rooms, 0, 0, 0, '', $hotel_id, 0, 0);
                    if($objReservation->error && !strpos($objReservation->error, 'msg success')){
                        echo $objReservation->error;
                    }else{
                        // Update the information on the reservation
                        unset($objReservation);
                        $objReservation = new Reservation();
                        $objReservation->DoReservation('quick_reservation');
                        if($objReservation->error && !strpos($objReservation->error, 'msg success')){
                            echo $objReservation->error;
                        }else{
                            $objReservation->EmptyCart();
                            draw_success_message(_BOOKING_WAS_COMPLETED_MSG);
                            $reservation_success = true;
                        }
                    }
                }
            }
        }

    }
    if(!$reservation_success){
        // Draw availability calendar for admins
		draw_default_message(_QUICK_RESERVATIONS_MSG); 
        Rooms::DrawQuickReservationsBlock(true);
        draw_content_end();	
    }
	
}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

