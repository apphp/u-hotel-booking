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

echo '<h4>'._AVAILABLE_ROOMS.'</h4>';
echo '<hr/>';
echo '<div class="center_box_content" id="block-result">';


if(CALENDAR_HOTEL == 'new'){
    $american_format = $objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? true : false;

	$checkin_date		 	= isset($_POST['checkin_date']) ? prepare_input($_POST['checkin_date']) : date($american_format ? 'm/d/Y' : 'd/m/Y');
	$checkin_parts			= explode('/', $checkin_date);
	$checkin_month 			= isset($checkin_parts[$american_format ? 0 : 1]) ? convert_to_decimal((int)$checkin_parts[$american_format ? 0 : 1]) : '';
	$checkin_day 			= isset($checkin_parts[$american_format ? 1 : 0]) ? convert_to_decimal((int)$checkin_parts[$american_format ? 1 : 0]) : '';
	$checkin_year 			= isset($checkin_parts[2]) ? (int)$checkin_parts[2] : '';

	$checkout_date			= isset($_POST['checkout_date']) ? prepare_input($_POST['checkout_date']) : date($american_format ? 'm/d/Y' : 'd/m/Y', time() + (24 * 60 * 60));
	$checkout_parts			= explode('/', $checkout_date);
	$checkout_month 		= isset($checkout_parts[$american_format ? 0 : 1]) ? convert_to_decimal((int)$checkout_parts[$american_format ? 0 : 1]) : '';
	$checkout_day			= isset($checkout_parts[$american_format ? 1 : 0]) ? convert_to_decimal((int)$checkout_parts[$american_format ? 1 : 0]) : '';
	$checkout_year 			= isset($checkout_parts[2]) ? (int)$checkout_parts[2] : '';
	
	$checkin_year_month 	= $checkin_year.'-'.(int)$checkin_month;
	$checkout_year_month 	= $checkout_year.'-'.(int)$checkout_month;

	// max date - 2 years
	$max_date_unix = mktime(0, 0, 0, date('m'), date('d'), date('Y') + 2) + (24 * 3600);
    if(ModulesSettings::Get('booking', 'allow_booking_in_past') == 'yes' && $objLogin->IsLoggedInAsAdmin()){
    	$min_date_unix = mktime(0, 0, 0, date('m'), date('d'), date('Y') - 1);
    }else{
    	$min_date_unix = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
    }
}else{
	$checkin_year_month 	= isset($_POST['checkin_year_month']) ? prepare_input($_POST['checkin_year_month']) : date('Y').'-'.(int)date('m');
	$checkin_year_month_parts = explode('-', $checkin_year_month);
	$checkin_year 			= isset($checkin_year_month_parts[0]) ? $checkin_year_month_parts[0] : '';
	$checkin_month 			= isset($checkin_year_month_parts[1]) ? convert_to_decimal($checkin_year_month_parts[1]) : '';
	$checkin_day 			= isset($_POST['checkin_monthday']) ? convert_to_decimal($_POST['checkin_monthday']) : date('d');

	$curr_date 				= mktime(0, 0, 0, date('m'), date('d')+1, date('y'));
	$checkout_year_month 	= isset($_POST['checkout_year_month']) ? prepare_input($_POST['checkout_year_month']) : date('Y').'-'.(int)date('m');
	$checkout_year_month_parts = explode('-', $checkout_year_month);
	$checkout_year 			= isset($checkout_year_month_parts[0]) ? $checkout_year_month_parts[0] : '';
	$checkout_month 		= isset($checkout_year_month_parts[1]) ? convert_to_decimal($checkout_year_month_parts[1]) : '';
	$checkout_day 			= isset($_POST['checkout_monthday']) ? convert_to_decimal($_POST['checkout_monthday']) : date('d', $curr_date);
}

$arr_evaluation = array(0=>_NOT_RECOMMENDED, 1=>_NOT_GOOD, 2=>_NEUTRAL, 3=>_GOOD, 4=>_VERY_GOOD, 5=>_WONDERFUL);

$hotel_sel_loc         = isset($_POST['hotel_sel_loc']) ? prepare_input($_POST['hotel_sel_loc']) : '';
$hotel_sel_id          = isset($_POST['hotel_sel_id']) && !empty($hotel_sel_loc) ? prepare_input($_POST['hotel_sel_id']) : '';
$hotel_sel_loc_id      = isset($_POST['hotel_sel_loc_id']) && !empty($hotel_sel_loc) ? prepare_input($_POST['hotel_sel_loc_id']) : '';
$max_adults            = isset($_POST['max_adults']) ? (int)$_POST['max_adults'] : '1';
$max_children          = isset($_POST['max_children']) ? (int)$_POST['max_children'] : '';
$property_type_id      = '';///isset($_POST['property_type_id']) ? (int)$_POST['property_type_id'] : '';
$minimum_beds		   = isset($_POST['minimum_beds']) && $_POST['minimum_beds'] > 0 ? (int)$_POST['minimum_beds'] : '';
$current_currency      = isset($_POST['current_currency']) ? prepare_input($_POST['current_currency'], true) : Application::Get('currency_code');

$currency_rate = Application::Get('currency_rate');
// Prepare property ID
// Currently we want to show all properties in this widget
//$property_types = Application::Get('property_types');
//$property_types = !empty($property_types) ? $property_types : array();
//if(!in_sub_array('id', $property_type_id, $property_types)){
//	$property_type_id = isset($property_types[0]['id']) ? $property_types[0]['id']: 0;
//}

$nights = nights_diff($checkin_year.'-'.$checkin_month.'-'.$checkin_day, $checkout_year.'-'.$checkout_month.'-'.$checkout_day);
$search_availability_period = ModulesSettings::Get('rooms', 'search_availability_period');
$search_availability_period_in_days = ($search_availability_period * 365) + 1;

$query_not_find = false;
$lang 			= Application::Get('lang');

// Check if there is a page
if($query_not_find){
    draw_message(str_replace('_QUERY_', htmlentities($hotel_sel_loc), _SORRY_WE_COULD_NOT_FIND));
}elseif($nights > $search_availability_period_in_days){
	draw_important_message(str_replace('_DAYS_', $search_availability_period_in_days, _MAXIMUM_PERIOD_ALERT));
}elseif((CALENDAR_HOTEL == 'new' && (empty($checkin_date) || empty($checkout_date))) || CALENDAR_HOTEL != 'new' && ($checkin_year_month == '0' || $checkin_day == '0' || $checkout_year_month == '0' || $checkout_day == '0')){
	draw_important_message(_WRONG_PARAMETER_PASSED);
}elseif(CALENDAR_HOTEL == 'new' && $min_date_unix > mktime(0, 0, 0, $checkin_month, $checkin_day, $checkin_year)){
	draw_important_message(_WRONG_PARAMETER_PASSED);
}elseif(CALENDAR_HOTEL == 'new' && $max_date_unix < mktime(0, 0, 0, $checkout_month, $checkout_day, $checkout_year)){
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

	if($nights < $min_nights){		
		echo draw_important_message(str_replace('_DAYS_', $min_nights, _MINIMUM_PERIOD_ALERT));
	}elseif($nights > $max_nights){
		echo draw_important_message(str_replace('_DAYS_', $max_nights, _MAXIMUM_PERIOD_ALERT));
	}else{		
		$min_max_hotels = array();
	
		// -----------------------------------------------------
		// Find general min night via all packages
		// -----------------------------------------------------
		$min_nights_packages = Packages::GetMinimumNights($checkin_year.'-'.$checkin_month.'-'.$checkin_day, $checkout_year.'-'.$checkout_month.'-'.$checkout_day, '', true);
		if(!empty($min_nights_packages) && is_array($min_nights_packages)){
			$packages_min_nights = '';
			foreach($min_nights_packages as $key => $package){
				if(!empty($package['hotel_id'])){				
					if($package['minimum_nights'] < $packages_min_nights || $packages_min_nights === ''){
						$packages_min_nights = (int)$package['minimum_nights'];
					}
					$min_max_hotels[$package['hotel_id']] = array(
						'package_name' 		=> $package['package_name'],
						'minimum_nights' 	=> $package['minimum_nights'],
						'maximum_nights' 	=> '',
						'minimum_rooms' 	=> '',
						'maximum_rooms' 	=> '',
						'partial_nights'	=> '',
						'start_date'		=> $package['start_date'],
						'finish_date'		=> $package['finish_date'],
						'hotel_name' 		=> $package['hotel_name'],
					);
				}
			}
			
			if(!empty($packages_min_nights) && $packages_min_nights < $min_nights){
				$min_nights = $packages_min_nights;
			}
		}
		
		// -----------------------------------------------------
		// Find general max night via all packages
		// -----------------------------------------------------
		$max_nights_packages = Packages::GetMaximumNights($checkin_year.'-'.$checkin_month.'-'.$checkin_day, $checkout_year.'-'.$checkout_month.'-'.$checkout_day, '', true);
		if(!empty($max_nights_packages) && is_array($max_nights_packages)){
			$packages_max_nights = '';
			foreach($max_nights_packages as $key => $package){
				if(!empty($package['hotel_id'])){
					if($package['maximum_nights'] > $packages_max_nights || $packages_max_nights === ''){
						$packages_max_nights = (int)$package['maximum_nights'];
					}
					
					if(!isset($min_max_hotels[$package['hotel_id']])){
						$min_max_hotels[$package['hotel_id']] = array(
							'package_name' 		=> $package['package_name'],
							'minimum_nights' 	=> '',
							'maximum_nights' 	=> $package['maximum_nights'],
							'minimum_rooms' 	=> '',
							'maximum_rooms' 	=> '',
							'partial_nights'	=> '',
							'start_date'		=> $package['start_date'],
							'finish_date'		=> $package['finish_date'],
							'hotel_name' 		=> $package['hotel_name'],					
						);					
					}else{
						$min_max_hotels[$package['hotel_id']]['maximum_nights'] = $package['maximum_nights'];
					}
				}
			}
			
			if(!empty($packages_max_nights) && $packages_max_nights > $max_nights){
				$max_nights = $packages_max_nights;
			}
		}

		// -----------------------------------------------------
		// Find general min rooms via all packages
		// -----------------------------------------------------
		$min_rooms_packages = Packages::GetMinimumRooms($checkin_year.'-'.$checkin_month.'-'.$checkin_day, $checkout_year.'-'.$checkout_month.'-'.$checkout_day, '', true);
		if(!empty($min_rooms_packages) && is_array($min_rooms_packages)){
			$packages_min_rooms = '';
			foreach($min_rooms_packages as $key => $package){
				if(!empty($package['hotel_id'])){				
					if($package['minimum_rooms'] < $packages_min_rooms || $packages_min_rooms === ''){
						$packages_min_rooms = (int)$package['minimum_rooms'];
					}
					if(!isset($min_max_hotels[$package['hotel_id']])){
						$min_max_hotels[$package['hotel_id']] = array(
							'package_name' 		=> $package['package_name'],
							'minimum_nights' 	=> '',
							'maximum_nights' 	=> '',
							'minimum_rooms' 	=> $package['minimum_rooms'],
							'maximum_rooms' 	=> '',
							'start_date'		=> $package['start_date'],
							'finish_date'		=> $package['finish_date'],
							'hotel_name' 		=> $package['hotel_name'],
						);
					}else{
						$min_max_hotels[$package['hotel_id']]['minimum_rooms'] = $package['minimum_rooms'];
					}
				}
			}
		}
		
		// -----------------------------------------------------
		// Find general max rooms via all packages
		// -----------------------------------------------------
		$max_rooms_packages = Packages::GetMaximumRooms($checkin_year.'-'.$checkin_month.'-'.$checkin_day, $checkout_year.'-'.$checkout_month.'-'.$checkout_day, '', true);
		if(!empty($max_rooms_packages) && is_array($max_rooms_packages)){
			$packages_max_rooms = '';
			foreach($max_rooms_packages as $key => $package){
				if(!empty($package['hotel_id'])){
					if($package['maximum_rooms'] > $packages_max_rooms || $packages_max_rooms == '-1'){
						$packages_max_rooms = (int)$package['maximum_rooms'];
					}
					
					if(!isset($min_max_hotels[$package['hotel_id']])){
						$min_max_hotels[$package['hotel_id']] = array(
							'package_name' 		=> $package['package_name'],
							'minimum_nights' 	=> '',
							'maximum_nights' 	=> '',
							'minimum_rooms' 	=> '',
							'maximum_rooms' 	=> $package['maximum_rooms'],
							'start_date'		=> $package['start_date'],
							'finish_date'		=> $package['finish_date'],
							'hotel_name' 		=> $package['hotel_name'],					
						);					
					}else{
						$min_max_hotels[$package['hotel_id']]['maximum_rooms'] = $package['maximum_rooms'];
					}
				}
			}
		}
	
		// -----------------------------------------------------
		// Handle partial settings
		// -----------------------------------------------------
		$check_partially_overlapping = ModulesSettings::Get('rooms', 'check_partially_overlapping');
		$partial_nights = 0;
		// [#001] force check for patial package period of time
		if($check_partially_overlapping == 'yes'){
			foreach($min_nights_packages as $key => $package){
				if(empty($package['hotel_id'])){
					continue;
				}
				$from_part_package = strtotime($package['start_date']);
				$to_part_package = strtotime($package['finish_date']);
				
				$partial_nights = 0;
				for($i = 0; $i < $nights; $i++){
					$part_reservation_date = strtotime($checkin_year.'-'.$checkin_month.'-'.$checkin_day . '+'. $i .'day');
					if($from_part_package <= $part_reservation_date && $to_part_package >= $part_reservation_date){
						$partial_nights++;
					}
				}
				$min_max_hotels[$package['hotel_id']]['partial_nights'] = $partial_nights;
			}			
		}
		
		
		foreach($min_max_hotels as $key => $min_max_hotel){
				
			$min_max_alert = '';
			if(!empty($min_max_hotel['minimum_nights']) && $nights < $min_max_hotel['minimum_nights']){				
				$min_max_alert = draw_important_message(
					str_replace(array('_IN_HOTEL_', '_PACKAGE_NAME_', '_NIGHTS_', '_FROM_', '_TO_'),
								array($min_max_hotel['hotel_name'].'. ', $min_max_hotel['package_name'], '<b>'.$min_max_hotel['minimum_nights'].'</b>', '<b>'.format_date($min_max_hotel['start_date']).'</b>', '<b>'.format_date($min_max_hotel['finish_date']).'</b>'),
								_MINIMUM_NIGHTS_ALERT
					), false
				);
			}elseif(!empty($min_max_hotel['maximum_nights']) && $nights > $min_max_hotel['maximum_nights']){
				$min_max_alert = draw_important_message(
					str_replace(array('_IN_HOTEL_', '_PACKAGE_NAME_', '_NIGHTS_', '_FROM_', '_TO_'),
								array($min_max_hotel['hotel_name'].'. ', $min_max_hotel['package_name'], '<b>'.$min_max_hotel['maximum_nights'].'</b>', '<b>'.format_date($min_max_hotel['start_date']).'</b>', '<b>'.format_date($min_max_hotel['finish_date']).'</b>'),
								_MAXIMUM_NIGHTS_ALERT
					), false
				);
		    }elseif($check_partially_overlapping == 'yes' && !empty($min_max_hotel['partial_nights']) && $min_max_hotel['partial_nights'] < $min_max_hotel['minimum_nights']){
				// [#002] force check for patial package period of time
		        $min_max_alert = draw_important_message(
					str_replace(array('_IN_HOTEL_', '_PACKAGE_NAME_', '_NIGHTS_', '_FROM_', '_TO_'),
								array($min_max_hotel['hotel_name'].'. ', $min_max_hotel['package_name'], '<b>'.$min_max_hotel['minimum_nights'].'</b>', '<b>'.format_date($min_max_hotel['start_date']).'</b>', '<b>'.format_date($min_max_hotel['finish_date']).'</b>'),
								_MINIMUM_NIGHTS_ALERT
					), false
		        );
			}
			
			$min_max_hotels[$key]['alert'] = !empty($min_max_alert) ? $min_max_alert : '';
		}
		
		$nights_text = ($nights > 1) ? $nights.' '._NIGHTS : $nights.' '._NIGHT;
		$children_text = ($max_children > 1) ? $max_children.' '._CHILDREN : $max_children.' '._CHILDREN;
		$adults_text = ($max_adults > 1) ? $max_adults.' '._ADULTS : $max_adults.' '._ADULTS;
		
		draw_content_start();
		if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
			draw_sub_title_bar(_FROM.': '.get_month_local($checkin_month).' '.$checkin_day.', '.$checkin_year.' '._TO.': '.get_month_local($checkout_month).' '.$checkout_day.', '.$checkout_year.' ('.$nights_text.', '.$adults_text.', '.$children_text.')', true, 'h4');
		}else{
			draw_sub_title_bar(_FROM.': '.$checkin_day.' '.get_month_local($checkin_month).' '.$checkin_year.' '._TO.': '.$checkout_day.' '.get_month_local($checkout_month).' '.$checkout_year.' ('.$nights_text.', '.$adults_text.', '.$children_text.')', true, 'h4');
		}
        echo '<hr/>';
		
		$objRooms = new Rooms();
		$params = array(
			'room_id'                  => '',
            'hotel_sel_id'             => !empty($hids) && empty($hotel_sel_id) ? explode(',',$hids) : $hotel_sel_id,
			'hotel_sel_loc_id'         => $hotel_sel_loc_id,
			'from_date'                => $checkin_year.'-'.$checkin_month.'-'.$checkin_day,
			'to_date'                  => $checkout_year.'-'.$checkout_month.'-'.$checkout_day,
			'nights'                   => $nights,
			'from_year'                => $checkin_year,
			'from_month'               => $checkin_month,
			'from_day'                 => $checkin_day,
			'to_year'                  => $checkout_year,
			'to_month'                 => $checkout_month,
			'to_day'                   => $checkout_day,
			'max_adults'               => $max_adults,
			'max_children'             => $max_children,
			'property_type_id'         => $property_type_id,
			'min_max_hotels'           => $min_max_hotels,
			'minimum_beds'		       => $minimum_beds
		);
		$additional_info = array(
			'beds_sample_type' => 'room',
		);

		$rooms_count = $objRooms->SearchFor($params, $additional_info);

		echo '<div class="line-hor"></div>';
		
		if($rooms_count['rooms'] > 0){
			$objRooms->DrawWidgetSearchResult($params, $rooms_count['rooms'], $host);
		}else{
			draw_important_message(_NO_ROOMS_FOUND);
			draw_message(_SEARCH_ROOM_TIPS);
			
			if(ModulesSettings::Get('rooms', 'allow_relocation_to_other_room') == 'no' && ModulesSettings::Get('rooms', 'allow_relocation_to_other_hotel') == 'no'){
				Rooms::DrawTrySystemSuggestionForm('', $checkin_day, $checkin_year_month, $checkout_day, $checkout_year_month, $max_adults, $max_children);
			}
		}
		draw_content_end();	
	}
}
?>
</div>
