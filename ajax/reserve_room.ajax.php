<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site https://www.hotel-booking-script.com
* @license http://hotel-booking-script.com/license.php
*/

define('APPHP_EXEC', 'access allowed');
define('APPHP_CONNECT', 'direct');
require_once('../include/base.inc.php');
require_once('../include/connection.php');

if(Modules::IsModuleInstalled('booking')){
	if(ModulesSettings::Get('booking', 'is_active') == 'global' ||
	   ModulesSettings::Get('booking', 'is_active') == 'front-end' ||
	  (ModulesSettings::Get('booking', 'is_active') == 'back-end' && $objLogin->IsLoggedInAsAdmin())	
	){		
        $widget = '';
		$room_id       = isset($_POST['room_id']) ? (int)$_POST['room_id'] : '0';
		$from_date     = isset($_POST['from_date']) ? prepare_input($_POST['from_date']) : date('Y-m-d');
		$to_date       = isset($_POST['to_date']) ? prepare_input($_POST['to_date']) : date('Y-m-d', time() + 24 * 60 * 60);
		$nights_post   = isset($_POST['nights']) ? (int)$_POST['nights'] : '1';
		$adults        = isset($_POST['adults']) ? (int)$_POST['adults'] : '0';
		$children      = isset($_POST['children']) ? (int)$_POST['children'] : '0';
		$type          = isset($_POST['type']) && strtolower($_POST['type']) == 'remove' ? 'remove' : 'reserve';
		$is_widget     = isset($_POST['is_widget']) && (int)$_POST['is_widget'] == 1 ? true : false;
        $base_url      = rtrim(get_base_url(), '/'); // http://your_site/ajax
        $w_homeurl     = substr($base_url, 0, strrpos($base_url, '/')).'/widgets/hotels/ipanel-center/'; // http://your_site/widgets/hotels/ipanel-center
		$arr           = array();

        if(isset($_POST['lang']) && prepare_input($_POST['lang']) != ''){
            Application::Set('lang', prepare_input($_POST['lang']));
        }

		if(empty($room_id)){
			printError(_WRONG_PARAMETER_PASSED);
        }elseif($type == 'remove'){
			$objReservation = new Reservation();
            $objReservation->RemoveReservation($room_id);
			if($objReservation->error){
				if(strpos($objReservation->error, 'msg success')){
                    $toastr_message = '';
                    // Get Reservation Info
                    $reservation_info = !empty($_SESSION[INSTALLATION_KEY.'reservation']) ? $_SESSION[INSTALLATION_KEY.'reservation'] : array();
                    if(!empty($reservation_info)){
                        $res_rooms = 0;
                        $res_price = 0;
                        foreach($reservation_info as $val){
                            $res_rooms += $val['rooms'];
                            $res_price += $val['price'];
                        }

                        if(!empty($res_rooms) && !$is_widget){
                            $currency_format  = get_currency_format();
                            $currency_rate = Session::Get('currency_rate');
                            $currency_rate = !empty($currency_rate) ? $currency_rate : 1;
                            $currency_symbol = Session::Get('currency_symbol');
                            $currency_symbol_place = Session::Get('symbol_placement');
                            $decimal_points = Session::Get('currency_decimals');
                            $res_price_format = Currencies::PriceFormat($res_price * $currency_rate, $currency_symbol, $currency_symbol_place, $currency_format, $decimal_points);
                            $toastr_message   = '<a href="index.php?page=booking">'.htmlspecialchars(str_replace(array('_NUMBER_ROOMS_', '_ROOMS_', '_PRICE_'), array($res_rooms, $res_rooms == 1 ? _ROOM : _ROOMS, $res_price_format), _YOU_HAVE_RESERVED)).'</a>';
                        }
                    }
                    if($is_widget){
                        $widget = $objReservation->DrawWidgetReservationCart($w_homeurl, false);
                    }
					// Success
                    if($is_widget){
                        $message = strip_tags($objReservation->error);
                        $m_message = '"message": "'.str_replace(array('"',"\r\n","\n","\t"),array("'",'','',''),$message).'"';
                        $m_widget = ', "widget_html": "'.str_replace(array('"',"\r\n","\n","\t"),array("'",'','',''),$widget).'"';
    					echo '{'.$m_message.$m_widget.'}';
                    }else{
    					echo '{"message":"'.str_replace(array('"',"\r\n","\n","\t"),array("'",'','',''),$objReservation->error).'", "toastr_message": "'.str_replace(array('"',"\r\n","\n","\t"),array("'",'','',''),$toastr_message).'"}';
                    }
				}else{
					printError(strip_tags($objReservation->error));
				}
			}else{
				printError(_WRONG_PARAMETER_PASSED);
			}
        }else{
			$objReservation = new Reservation();
			
			$operation_allowed = true;

			// [#001 - 08.12.2013] added to verify post data
			// -----------------------------------------------------------------
			$checkinParts = explode('-', $from_date);
			$checkin_year = isset($checkinParts[0]) ? $checkinParts[0] : '';
			$checkin_month = isset($checkinParts[1]) ? $checkinParts[1] : '';
			$checkin_day = isset($checkinParts[2]) ? $checkinParts[2] : '';
			$checkoutParts = explode('-', $to_date);
			$checkout_year = isset($checkoutParts[0]) ? $checkoutParts[0] : '';
			$checkout_month = isset($checkoutParts[1]) ? $checkoutParts[1] : '';
			$checkout_day = isset($checkoutParts[2]) ? $checkoutParts[2] : '';
			$nights = nights_diff($checkin_year.'-'.$checkin_month.'-'.$checkin_day, $checkout_year.'-'.$checkout_month.'-'.$checkout_day);
			$params = array(
				'from_date' => $checkin_year.'-'.$checkin_month.'-'.$checkin_day,
				'to_date' => $checkout_year.'-'.$checkout_month.'-'.$checkout_day,
				'from_year' => $checkin_year,
				'from_month' => $checkin_month,
				'from_day' => $checkin_day,
				'to_year' => $checkout_year,
				'to_month' => $checkout_month,
				'to_day' => $checkout_day,
				'max_adults' => $adults,
				'max_children' => $children,
			);

			if($nights_post != $nights){			
				printError(_WRONG_PARAMETER_PASSED);
			}
			// -----------------------------------------------------------------

			$meal_plan_id = isset($_POST['meal_plans']) ? (int)$_POST['meal_plans'] : '';
			$hotel_id = isset($_POST['hotel_id']) ? (int)$_POST['hotel_id'] : '0';
			$available_rooms  = isset($_POST['available_rooms']) ? prepare_input($_POST['available_rooms']) : '';
			$available_rooms_parts = explode('-', $available_rooms);
			$rooms = isset($available_rooms_parts[0]) ? (int)$available_rooms_parts[0] : '';
			// -----------------------------------------------------------------
			$min_rooms_packages = Packages::GetMinimumRooms($checkin_year.'-'.$checkin_month.'-'.$checkin_day, $checkout_year.'-'.$checkout_month.'-'.$checkout_day, $hotel_id, false);
			$max_rooms_packages = Packages::GetMaximumRooms($checkin_year.'-'.$checkin_month.'-'.$checkin_day, $checkout_year.'-'.$checkout_month.'-'.$checkout_day, $hotel_id, false);

			$maximum_rooms = !empty($max_rooms_packages['maximum_rooms']) ? $max_rooms_packages['maximum_rooms'] : 0;
			$minimum_rooms = !empty($min_rooms_packages['minimum_rooms']) ? $min_rooms_packages['minimum_rooms'] : 0;
			if(TYPE_FILTER_TO_NUMBER_ROOMS == 'hotel'){
				$minimum_rooms = 1;
				$count_rooms = $objReservation->GetCountReservationRooms($hotel_id);
				if(!empty($maximum_rooms) && $count_rooms > $maximum_rooms){
					printError(str_replace('_NUM_ROOMS_', $maximum_rooms, _CANNOT_BOOKING_MORE));
				}
			}else{
				if(!empty($maximum_rooms) && $rooms > $maximum_rooms){
					printError(str_replace('_NUM_ROOMS_', $maximum_rooms, _CANNOT_BOOKING_MORE));
				}elseif(!empty($minimum_rooms) && $rooms < $minimum_rooms){
					printError(str_replace('_NUM_ROOMS_', $minimum_rooms, _CONTINUE_MUST_BOOK_MIN));
				}
			}
			
			// -----------------------------------------------------------------
			$price_post = isset($available_rooms_parts[1]) ? (float)$available_rooms_parts[1] : '';
			$price = Rooms::GetRoomPrice($room_id, $hotel_id, $params) * $rooms;
			
			// We don't need this check because we can select less/more guest and get/leave discount
			//if(abs($price_post - $price) >= 0.01){
			//	printError(_WRONG_PARAMETER_PASSED);
			//}		
			
			// -----------------------------------------------------------------			
			$available_extra_beds = isset($_POST['available_extra_beds']) ? prepare_input($_POST['available_extra_beds']) : '';
			$available_extra_beds_parts = explode('-', $available_extra_beds);
			$extra_beds = isset($available_extra_beds_parts[0]) ? (int)$available_extra_beds_parts[0] : '';
			
			// -----------------------------------------------------------------
			$extra_bed_charge_post = isset($available_extra_beds_parts[1]) ? (float)$available_extra_beds_parts[1] : '';		
			$extra_bed_charge = Rooms::GetRoomExtraBedsPrice($room_id, $params) * $extra_beds;
			if($extra_bed_charge_post != $extra_bed_charge){
				printError(_WRONG_PARAMETER_PASSED);
			}		
			
			// -----------------------------------------------------------------			
			if($type == 'reserve'){
				$objReservation->AddToReservation($room_id, $from_date, $to_date, $nights, $rooms, $price, $adults, $children, $meal_plan_id, $hotel_id, $extra_beds, $extra_bed_charge);
			}

			if($objReservation->error){
				if(strpos($objReservation->error, 'msg success')){
                    $toastr_message = '';
                    // Get Reservation Info
                    $reservation_info = !empty($_SESSION[INSTALLATION_KEY.'reservation']) ? $_SESSION[INSTALLATION_KEY.'reservation'] : array();
                    if(!empty($reservation_info)){
                        $res_rooms = 0;
                        $res_price = 0;
                        foreach($reservation_info as $val){
                            $res_rooms += $val['rooms'];
                            $res_price += $val['price'];
                        }

                        if(!empty($res_rooms)){
                            $currency_format  = get_currency_format();
                            $currency_rate = Session::Get('currency_rate');
                            $currency_rate = !empty($currency_rate) ? $currency_rate : 1;
                            $currency_symbol = Session::Get('currency_symbol');
                            $currency_symbol_place = Session::Get('symbol_placement');
                            $decimal_points = Session::Get('currency_decimals');
                            $res_price_format = Currencies::PriceFormat($res_price * $currency_rate, $currency_symbol, $currency_symbol_place, $currency_format, $decimal_points);
                            $toastr_message   = '<a href="index.php?page=booking">'.htmlspecialchars(str_replace(array('_NUMBER_ROOMS_', '_ROOMS_', '_PRICE_'), array($res_rooms, $res_rooms == 1 ? _ROOM : _ROOMS, $res_price_format), _YOU_HAVE_RESERVED)).'</a>';
                        }
                    }
					
                    if($is_widget){
                        $widget = $objReservation->DrawWidgetReservationCart($w_homeurl, false);
                    }
					
					// Success
                    if($is_widget){
                        $message = strip_tags($objReservation->error);
                        $m_message = '"message": "'.str_replace(array('"',"\r\n","\n","\t"),array("'",'','',''),$message).'"';
                        $m_widget = ', "widget_html": "'.str_replace(array('"',"\r\n","\n","\t"),array("'",'','',''),$widget).'"';
    					echo '{'.$m_message.$m_widget.'}';
                    }else{
    					echo '{"message":"'.str_replace(array('"',"\r\n","\n","\t"),array("'",'','',''),$objReservation->error).'", "toastr_message": "'.str_replace(array('"',"\r\n","\n","\t"),array("'",'','',''),$toastr_message).'"}';
                    }
				}else{
					printError($objReservation->error);
				}
			}else{
				printError(_WRONG_PARAMETER_PASSED);
			}
		}
	}else{
		printError(_NOT_AUTHORIZED);
	}    
}else{
	printError(_NOT_AUTHORIZED);
}

function printError($message)
{
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');   // Date in the past
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
	header('Pragma: no-cache'); // HTTP/1.0
	header('Content-Type: application/json');

	echo '{"error":"1", "message":"'.str_replace(array('"',"\r\n","\n","\t"),array("'",'','',''),$message).'"}';

	exit;
}
