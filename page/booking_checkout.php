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

if(Modules::IsModuleInstalled('booking')){
	if(ModulesSettings::Get('booking', 'is_active') == 'global' ||
	   ModulesSettings::Get('booking', 'is_active') == 'front-end' ||
	  (ModulesSettings::Get('booking', 'is_active') == 'back-end' && $objLogin->IsLoggedInAsAdmin())	
	){

		$m   = isset($_REQUEST['m']) ? prepare_input($_REQUEST['m']) : '';
		$act = isset($_POST['act']) ? prepare_input($_POST['act']) : '';
		$discount_coupon = isset($_POST['discount_coupon']) ? prepare_input(trim($_POST['discount_coupon'])) : '';
		$submition_type = isset($_POST['submition_type']) ? prepare_input($_POST['submition_type']) : '';
		$payment_type = isset($_POST['payment_type']) ? prepare_input($_POST['payment_type']) : ''; 
		$rid = isset($_POST['rid']) ? prepare_input($_POST['rid']) : ''; 
		$from_date = isset($_POST['from_date']) ? prepare_input($_POST['from_date']) : ''; 
		$to_date = isset($_POST['to_date']) ? prepare_input($_POST['to_date']) : ''; 
		$msg = '';		

		draw_content_start();
		draw_reservation_bar('reservation');

		// test mode alert
		if(Modules::IsModuleInstalled('booking')){
			if(ModulesSettings::Get('booking', 'mode') == 'TEST MODE'){
				$msg = draw_message(_TEST_MODE_ALERT_SHORT, false, true);
			}        
		}
		
		if($m == '1'){
			if(ModulesSettings::Get('booking', 'allow_booking_without_account') == 'no'){
				$msg = draw_success_message(_ACCOUNT_WAS_CREATED, false);
			}
		}elseif($m == '2'){
			if(ModulesSettings::Get('booking', 'allow_booking_without_account') == 'no'){
				$msg = draw_success_message(_ACCOUNT_WAS_UPDATED, false);
			}else{
				$msg = draw_success_message(_BILLING_DETAILS_UPDATED, false);
			}
		}
		
		if($submition_type == 'apply_coupon' && $discount_coupon != ''){
			if($objReservation->ApplyDiscountCoupon($discount_coupon)){
				$msg = draw_success_message(str_replace('_COUPON_CODE_', '<b>'.$discount_coupon.'</b>', _COUPON_WAS_APPLIED), false);
			}else{
				$msg = draw_important_message($objReservation->error, false);
			}
		}elseif($submition_type == 'remove_coupon' && $discount_coupon != ''){
			if($objReservation->RemoveDiscountCoupon($discount_coupon)){
				$msg = draw_success_message(str_replace('_COUPON_CODE_', '<b>'.$discount_coupon.'</b>', _COUPON_WAS_REMOVED), false);
			}else{
				$msg = draw_important_message(_WRONG_COUPON_CODE, false);
			}			
		}elseif($submition_type == 'update'){
			// Update from_date and to_date
			$room_id   = isset($_POST['room_id'])   ? prepare_input($_POST['room_id'])   : '';
			$from_date = isset($_POST['from_date']) ? prepare_input($_POST['from_date']) : '';
			$to_date   = isset($_POST['to_date'])   ? prepare_input($_POST['to_date'])   : '';
			$info_reservation = $objReservation->GetInfoByRoomID($room_id);

			if(!empty($room_id) && !empty($info_reservation)){
                $american_format = $objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? true : false;

                $checkin_parts          = explode('/', $from_date);
                $checkin_month          = isset($checkin_parts[$american_format ? 0 : 1]) ? $checkin_parts[$american_format ? 0 : 1] : '';
                $checkin_day            = isset($checkin_parts[$american_format ? 1 : 0]) ? $checkin_parts[$american_format ? 1 : 0] : '';
                $checkin_year           = isset($checkin_parts[2]) ? $checkin_parts[2] : '';

                $checkout_parts         = explode('/', $to_date);
                $checkout_month         = isset($checkout_parts[$american_format ? 0 : 1]) ? $checkout_parts[$american_format ? 0 : 1] : '';
                $checkout_day           = isset($checkout_parts[$american_format ? 1 : 0]) ? $checkout_parts[$american_format ? 1 : 0] : '';
                $checkout_year          = isset($checkout_parts[2]) ? $checkout_parts[2] : '';
				
				$checkin_year_month 	= $checkin_year.'-'.(int)$checkin_month;
				$checkout_year_month 	= $checkout_year.'-'.(int)$checkout_month;

				$checkin_date           = $checkin_year.'-'.$checkin_month.'-'.$checkin_day;
				$checkout_date          = $checkout_year.'-'.$checkout_month.'-'.$checkout_day;

				// max date - 2 years
				$max_date_unix = mktime(0, 0, 0, date('m'), date('d'), date('Y') + 2) + (24 * 3600);
				$min_date_unix = mktime(0, 0, 0, date('m'), date('d'), date('Y'));

				$nights = nights_diff($checkin_date, $checkout_date);
				$search_availability_period = ModulesSettings::Get('rooms', 'search_availability_period');
				$search_availability_period_in_days = ($search_availability_period * 365) + 1;

				// Apply parameters for hotel owner - leave only hotel ID field
				if($objLogin->IsLoggedInAs('hotelowner') && Application::Get('template') == 'admin'){
					$hotel_sel_id      = isset($_POST['hotel_sel_id']) ? prepare_input($_POST['hotel_sel_id']) : '';
					$property_type_id  = '';
				}

				draw_title_bar(_AVAILABLE_ROOMS);

				// Check if there is a page
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
				}elseif(Modules::IsModuleInstalled('booking')){

					$min_nights = ModulesSettings::Get('booking', 'minimum_nights');
					$max_nights = ModulesSettings::Get('booking', 'maximum_nights');
					$hotel_id = $info_reservation['hotel_id'];
					$maximal_rooms = $info_reservation['rooms'];
					$adults = $info_reservation['adults'];
					$children = $info_reservation['children'];
					$extra_beds = $info_reservation['extra_beds'];
					$packages = Packages::GetAllActiveByDate($checkin_date, $checkout_date);

					// check if package min/max stays is available for this hotel
					$hotel_data = isset($packages['hotel_id'][$hotel_id]) ? $packages['hotel_id'][$hotel_id] : null;

					if($nights < $min_nights || (!empty($hotel_data) && $nights < $hotel_data['minimum_nights'])){		
						echo draw_important_message(str_replace('_DAYS_', $min_nights, _MINIMUM_PERIOD_ALERT));
					}elseif($nights > $max_nights || (!empty($hotel_data) && $nights > $hotel_data['maximum_nights'])){
						echo draw_important_message(str_replace('_DAYS_', $max_nights, _MAXIMUM_PERIOD_ALERT));
					}else{
						$max_booked_rooms = '0';
						$sql = 'SELECT
									MAX('.TABLE_BOOKINGS_ROOMS.'.rooms) as max_booked_rooms
								FROM '.TABLE_BOOKINGS.'
									INNER JOIN '.TABLE_BOOKINGS_ROOMS.' ON '.TABLE_BOOKINGS.'.booking_number = '.TABLE_BOOKINGS_ROOMS.'.booking_number
								WHERE
									('.TABLE_BOOKINGS.'.status = 2 OR '.TABLE_BOOKINGS.'.status = 3) AND
									'.TABLE_BOOKINGS_ROOMS.'.room_id = '.(int)$room_id.' AND
									(
										(\''.$checkin_date.'\' <= checkin AND \''.$checkout_date.'\' > checkin) 
										OR
										(\''.$checkin_date.'\' < checkout AND \''.$checkout_date.'\' >= checkout)
										OR
										(\''.$checkin_date.'\' >= checkin  AND \''.$checkout_date.'\' < checkout)
									)';
						$rooms_booked = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
						if($rooms_booked[1] > 0){
							$max_booked_rooms = (int)$rooms_booked[0]['max_booked_rooms'];
						}
						
						// this is only a simple check if there is at least one room wirh available num > booked rooms
						$available_rooms = (int)($maximal_rooms - $max_booked_rooms);
						// echo '<br> Room ID: '.$rooms[0][$i]['id'].' Max: '.$maximal_rooms.' Booked: '.$max_booked_rooms.' Av:'.$available_rooms;

						// this is advanced check that takes in account max availability for each specific day is selected period of time
                        if($available_rooms >= 0){
							$available_rooms_updated = Rooms::CheckAvailabilityForPeriod($room_id, $checkin_date, $checkout_date, $available_rooms);
							if($available_rooms_updated){
								$params = array(
									'from_date'    => $checkin_date,
									'to_date'      => $checkout_date,
									'from_year'    => $checkin_year,
									'from_month'   => $checkin_month,
									'from_day'     => $checkin_day,
									'to_year'      => $checkout_year,
									'to_month'     => $checkout_month,
									'to_day'       => $checkout_day,
									'max_adults'   => $adults,
									'max_children' => $children,
								);
								$price = Rooms::GetRoomPrice($room_id, $hotel_id, $params) * $maximal_rooms;
								$extra_bed_charge = Rooms::GetRoomExtraBedsPrice($room_id, $params) * $extra_beds;
								$extra_beds_charge = number_format($extra_bed_charge * $nights * $maximal_rooms, 2, '.', '');

                                $objReservation->EditToReservation($room_id, array(
                                    'from_date'         => $checkin_date,
                                    'to_date'           => $checkout_date,
                                    'nights'            => $nights,
                                    'price'             => $price,
                                    'extra_beds_charge' => $extra_beds_charge
                                ));

                                // refresh discount info
                                $objReservation->LoadDiscountInfo();

                                echo draw_success_message(_CHANGES_SAVED);
							}else{
								if($american_format){
									$date_from = get_month_local($checkin_month).' '.$checkin_day.', '.$checkin_year;
									$date_to   = get_month_local($checkout_month).' '.$checkout_day.', '.$checkout_year;
								}else{
									$date_from = $checkin_day.' '.get_month_local($checkin_month).' '.$checkin_year;
									$date_to   = $checkout_day.' '.get_month_local($checkout_month).' '.$checkout_year;
								}
								$message = str_replace(array('_DATE_FROM_', '_DATE_TO_'), array($date_from, $date_to), _THERE_NO_AVAILABLE_ROOMS);
                                echo draw_important_message($message);
                            }
                        }else{
							if($american_format){
								$date_from = get_month_local($checkin_month).' '.$checkin_day.', '.$checkin_year;
								$date_to   = get_month_local($checkout_month).' '.$checkout_day.', '.$checkout_year;
							}else{
								$date_from = $checkin_day.' '.get_month_local($checkin_month).' '.$checkin_year;
								$date_to   = $checkout_day.' '.get_month_local($checkout_month).' '.$checkout_year;
							}
							$message = str_replace(array('_DATE_FROM_', '_DATE_TO_'), array($date_from, $date_to), _THERE_NO_AVAILABLE_ROOMS);
							echo draw_important_message($message);
						}
					}
				}
			}else{
				echo draw_important_message(_WRONG_PARAMETER_PASSED);
			}
		}

		if($msg != '') echo $msg;			
		
		$objReservation->ShowCheckoutInfo();
		draw_content_end();
		
	}else{
		draw_title_bar(_BOOKINGS);
		draw_important_message(_NOT_AUTHORIZED);
	}	
}else{
	draw_title_bar(_BOOKINGS);
    draw_important_message(_NOT_AUTHORIZED);
}

