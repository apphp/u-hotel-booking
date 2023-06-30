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

$room_id = isset($_POST['room_id']) ? prepare_input($_POST['room_id']) : '';

if (CALENDAR_HOTEL == 'new') {
    $american_format = $objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? true : false;

    $checkin_date = isset($_POST['checkin_date']) ? prepare_input($_POST['checkin_date']) : date($american_format ? 'm/d/Y' : 'd/m/Y');
    $checkin_parts = explode('/', $checkin_date);
    $checkin_month = isset($checkin_parts[$american_format ? 0 : 1]) ? convert_to_decimal((int)$checkin_parts[$american_format ? 0 : 1]) : '';
    $checkin_day = isset($checkin_parts[$american_format ? 1 : 0]) ? convert_to_decimal((int)$checkin_parts[$american_format ? 1 : 0]) : '';
    $checkin_year = isset($checkin_parts[2]) ? (int)$checkin_parts[2] : '';

    $checkout_date = isset($_POST['checkout_date']) ? prepare_input($_POST['checkout_date']) : date($american_format ? 'm/d/Y' : 'd/m/Y', time() + (24 * 60 * 60));
    $checkout_parts = explode('/', $checkout_date);
    $checkout_month = isset($checkout_parts[$american_format ? 0 : 1]) ? convert_to_decimal((int)$checkout_parts[$american_format ? 0 : 1]) : '';
    $checkout_day = isset($checkout_parts[$american_format ? 1 : 0]) ? convert_to_decimal((int)$checkout_parts[$american_format ? 1 : 0]) : '';
    $checkout_year = isset($checkout_parts[2]) ? (int)$checkout_parts[2] : '';

    $checkin_year_month = $checkin_year . '-' . (int)$checkin_month;
    $checkout_year_month = $checkout_year . '-' . (int)$checkout_month;

    // max date - 2 years
    $max_date_unix = mktime(0, 0, 0, date('m'), date('d'), date('Y') + 2) + (24 * 3600);
    if (ModulesSettings::Get('booking', 'allow_booking_in_past') == 'yes' && $objLogin->IsLoggedInAsAdmin()) {
        $min_date_unix = mktime(0, 0, 0, date('m'), date('d'), date('Y') - 1);
    } elseif (ModulesSettings::Get('booking', 'customer_booking_in_past') == 'yes') {
        $min_date_unix = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
    } else {
        $min_date_unix = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
    }
} else {
    $checkin_year_month = isset($_POST['checkin_year_month']) ? prepare_input($_POST['checkin_year_month']) : date('Y') . '-' . (int)date('m');
    $checkin_year_month_parts = explode('-', $checkin_year_month);
    $checkin_year = isset($checkin_year_month_parts[0]) ? $checkin_year_month_parts[0] : '';
    $checkin_month = isset($checkin_year_month_parts[1]) ? convert_to_decimal($checkin_year_month_parts[1]) : '';
    $checkin_day = isset($_POST['checkin_monthday']) ? convert_to_decimal($_POST['checkin_monthday']) : date('d');

    $curr_date = mktime(0, 0, 0, date('m'), date('d') + 1, date('y'));
    $checkout_year_month = isset($_POST['checkout_year_month']) ? prepare_input($_POST['checkout_year_month']) : date('Y') . '-' . (int)date('m');
    $checkout_year_month_parts = explode('-', $checkout_year_month);
    $checkout_year = isset($checkout_year_month_parts[0]) ? $checkout_year_month_parts[0] : '';
    $checkout_month = isset($checkout_year_month_parts[1]) ? convert_to_decimal($checkout_year_month_parts[1]) : '';
    $checkout_day = isset($_POST['checkout_monthday']) ? convert_to_decimal($_POST['checkout_monthday']) : date('d', $curr_date);
}

if (isset($_POST['checkin_date'])) {
    Session::Set('availability_checkin_unix', mktime(0, 0, 0, $checkin_month, $checkin_day, $checkin_year));
}
if (isset($_POST['checkout_date'])) {
    Session::Set('availability_checkout_unix', mktime(0, 0, 0, $checkout_month, $checkout_day, $checkout_year));
}

$arr_evaluation = array(0 => _NOT_RECOMMENDED, 1 => _NOT_GOOD, 2 => _NEUTRAL, 3 => _GOOD, 4 => _VERY_GOOD, 5 => _WONDERFUL);

$max_adults = isset($_POST['max_adults']) ? (int)$_POST['max_adults'] : '1';
$max_children = isset($_POST['max_children']) ? (int)$_POST['max_children'] : '';
$sort_by = isset($_POST['sort_by']) && in_array($_POST['sort_by'], array('stars-1-5', 'stars-5-1', 'name-a-z', 'name-z-a', 'price-l-h', 'price-h-l')) ? prepare_input($_POST['sort_by']) : '';
$additional_sort_by = isset($_POST['additional_sort_by']) && in_array($_POST['additional_sort_by'], array('stars-1-5', 'stars-5-1', 'name-a-z', 'name-z-a', 'price-l-h', 'price-h-l', 'distance-asc', 'distance-desc', 'review-asc', 'review-desc')) ? prepare_input($_POST['additional_sort_by']) : '';
$hotel_sel_loc = isset($_POST['hotel_sel_loc']) ? prepare_input($_POST['hotel_sel_loc']) : '';
$hotel_sel_id = isset($_POST['hotel_sel_id']) && (!empty($hotel_sel_loc) || HOTEL_SELECT_LOCATION == 'dropdownlist') ? prepare_input($_POST['hotel_sel_id']) : '';
$hotel_sel_loc_id = isset($_POST['hotel_sel_loc_id']) && (!empty($hotel_sel_loc) || HOTEL_SELECT_LOCATION == 'dropdownlist') ? prepare_input($_POST['hotel_sel_loc_id']) : '';
$property_type_id = isset($_REQUEST['property_type_id']) ? (int)$_REQUEST['property_type_id'] : '';
$sort_facilities = isset($_POST['sort_facilities']) ? (int)$_POST['sort_facilities'] : '';
$sort_rating = isset($_POST['sort_rating']) && in_array($_POST['sort_rating'], array_keys($arr_evaluation)) && $_POST['sort_rating'] !== '' ? (int)$_POST['sort_rating'] : null;
$sort_price = isset($_POST['sort_price']) && in_array(strtolower($_POST['sort_price']), array('asc', 'desc')) ? strtolower($_POST['sort_price']) : '';
$arr_filter_rating = isset($_POST['filter_rating_star']) && is_array($_POST['filter_rating_star']) ? $_POST['filter_rating_star'] : array();
$arr_filter_price = isset($_POST['filter_price']) && strpos($_POST['filter_price'], ';') ? explode(';', $_POST['filter_price']) : array();
$arr_filter_distance = isset($_POST['filter_distance']) && strpos($_POST['filter_distance'], ';') ? explode(';', $_POST['filter_distance']) : array();
$arr_filter_facilities = isset($_POST['filter_facilities']) && is_array($_POST['filter_facilities']) ? $_POST['filter_facilities'] : array();
$minimum_beds = isset($_POST['minimum_beds']) && $_POST['minimum_beds'] > 0 ? (int)$_POST['minimum_beds'] : '';
$current_currency = isset($_POST['current_currency']) ? prepare_input($_POST['current_currency'], true) : Application::Get('currency_code');
$arr_serialize_facilities = array();

if ($sort_by != $additional_sort_by && empty($sort_by)) {
    $sort_by = $additional_sort_by;
}

// Prepare values for retrieval in the database
foreach ($arr_filter_facilities as $facilities) {
    $i_facilities = (int)$facilities;
    $arr_serialize_facilities[$i_facilities] = serialize($facilities);
}

// Checking the input data (arr_filter_rating)
if (!empty($arr_filter_rating)) {
    $tmp_arr = array();
    foreach ($arr_filter_rating as $value) {
        if (!in_array($value, $tmp_arr)) {
            $tmp_arr[] = (int)$value;
        }
    }
    $arr_filter_rating = $tmp_arr;
}

$currency_rate = Application::Get('currency_rate');

if (!empty($arr_filter_price) && count($arr_filter_price) == 2) {
    $filter_start_price = $arr_filter_price[0];
    $filter_end_price = $arr_filter_price[1];

    $currency_code = Application::Get('currency_code');
    if ($currency_code != $current_currency) {
        $current_currency_info = Currencies::GetCurrencyInfo($current_currency);
        if ($current_currency_info['code'] != $currency_code) {
            $filter_start_price = ceil($filter_start_price * ($currency_rate / $current_currency_info['rate']));
            $filter_end_price = ceil($filter_end_price * ($currency_rate / $current_currency_info['rate']));
        }
    }
} else {
    $filter_start_price = 0;
    $filter_end_price = MAX_PRICE_FILTER * $currency_rate;
}

if (!empty($arr_filter_distance) && count($arr_filter_distance) == 2) {
    $filter_start_distance = $arr_filter_distance[0];
    $filter_end_distance = $arr_filter_distance[1];
} else {
    $filter_start_distance = 0;
    $filter_end_distance = 1000;
}

// Prepare property ID
$property_types = Application::Get('property_types');
if (!in_sub_array('id', $property_type_id, $property_types)) {
    //$property_type_id = isset($property_types[0]['id']) ? $property_types[0]['id'] : 0;
	$property_type_id = '';
}

$nights = nights_diff($checkin_year . '-' . $checkin_month . '-' . $checkin_day, $checkout_year . '-' . $checkout_month . '-' . $checkout_day);
$search_availability_period = ModulesSettings::Get('rooms', 'search_availability_period');
$search_availability_period_in_days = ($search_availability_period * 365) + 1;

// Apply parameters for hotel owner - leave only hotel ID field
if ($objLogin->IsLoggedInAs('hotelowner') && Application::Get('template') == 'admin') {
    $hotel_sel_id = isset($_POST['hotel_sel_id']) ? prepare_input($_POST['hotel_sel_id']) : '';
    $property_type_id = '';
}

draw_title_bar(_AVAILABLE_ROOMS);

$query_not_find = false;
if (empty($hotel_sel_id) && empty($hotel_sel_loc_id) && !empty($hotel_sel_loc)) {
    $search = trim(prepare_input($hotel_sel_loc, true));
    $lang = Application::Get('lang');

    // LOCATION / COUNTRY
    $result = HotelsLocations::FindLocationCountry($search, $lang, 1);
    //echo database_error();
    if ($result[1] > 0) {
        $hotel_sel_loc_id = $result[0]['id'];
    } else {
        // HOTEL / LOCATION / COUNTRY
        $result = HotelsLocations::FindHotelLocationCountry($search, '', $lang, 1);
        //echo database_error();
        if ($result[1] > 0) {
            $hotel_sel_id = $result[0]['id'];
        } else {
            $query_not_find = true;
        }
    }
}

// Check if there is a page
if ($query_not_find) {
    draw_message(str_replace('_QUERY_', htmlentities($hotel_sel_loc), _SORRY_WE_COULD_NOT_FIND));
    echo '<script>jQuery(document).ready(function(){jQuery("#hotel_sel_loc").val("");})</script>';
} elseif ($nights > $search_availability_period_in_days) {
    draw_important_message(str_replace('_DAYS_', $search_availability_period_in_days, _MAXIMUM_PERIOD_ALERT));
} elseif ((CALENDAR_HOTEL == 'new' && (empty($checkin_date) || empty($checkout_date))) || CALENDAR_HOTEL != 'new' && ($checkin_year_month == '0' || $checkin_day == '0' || $checkout_year_month == '0' || $checkout_day == '0')) {
    draw_important_message(_WRONG_PARAMETER_PASSED);
} elseif (CALENDAR_HOTEL == 'new' && $min_date_unix > mktime(0, 0, 0, $checkin_month, $checkin_day, $checkin_year)) {
    draw_important_message(_PAST_TIME_ALERT);
} elseif (CALENDAR_HOTEL == 'new' && $max_date_unix < mktime(0, 0, 0, $checkout_month, $checkout_day, $checkout_year)) {
    draw_important_message(_WRONG_PARAMETER_PASSED);
} elseif (!checkdate($checkout_month, $checkout_day, $checkout_year)) {
    draw_important_message(_WRONG_CHECKOUT_DATE_ALERT);
} elseif (CALENDAR_HOTEL == 'old' && ModulesSettings::Get('booking', 'allow_booking_in_past') != 'yes' && $checkin_year . $checkin_month . $checkin_day < date('Ymd')) {
    draw_important_message(_PAST_TIME_ALERT);
} elseif ($nights < 1) {
    draw_important_message(_BOOK_ONE_NIGHT_ALERT);
} elseif (Modules::IsModuleInstalled('booking')) {

    $min_nights = ModulesSettings::Get('booking', 'minimum_nights');
    $max_nights = ModulesSettings::Get('booking', 'maximum_nights');

    if ($nights < $min_nights) {
        echo draw_important_message(str_replace('_DAYS_', $min_nights, _MINIMUM_PERIOD_ALERT));
    } elseif ($nights > $max_nights) {
        echo draw_important_message(str_replace('_DAYS_', $max_nights, _MAXIMUM_PERIOD_ALERT));
    } else {
        $min_max_hotels = array();

        // -----------------------------------------------------
        // Find general min night via all packages
        // -----------------------------------------------------
        $min_nights_packages = Packages::GetMinimumNights($checkin_year . '-' . $checkin_month . '-' . $checkin_day, $checkout_year . '-' . $checkout_month . '-' . $checkout_day, $hotel_sel_id, true);
        ///dbug($min_nights_packages);
        if (!empty($min_nights_packages) && is_array($min_nights_packages)) {
            $packages_min_nights = '';
            foreach ($min_nights_packages as $key => $package) {
                if (!empty($package['hotel_id'])) {
                    if ($package['minimum_nights'] < $packages_min_nights || $packages_min_nights === '') {
                        $packages_min_nights = (int)$package['minimum_nights'];
                    }
                    $min_max_hotels[$package['hotel_id']] = array(
                        'package_name' => $package['package_name'],
                        'minimum_nights' => $package['minimum_nights'],
                        'maximum_nights' => '',
                        'minimum_rooms' => '',
                        'maximum_rooms' => '',
                        'partial_nights' => '',
                        'start_date' => $package['start_date'],
                        'finish_date' => $package['finish_date'],
                        'hotel_name' => $package['hotel_name'],
                    );
                }
            }

            if (!empty($packages_min_nights) && $packages_min_nights < $min_nights) {
                $min_nights = $packages_min_nights;
            }
        }

        // -----------------------------------------------------
        // Find general max night via all packages
        // -----------------------------------------------------
        $max_nights_packages = Packages::GetMaximumNights($checkin_year . '-' . $checkin_month . '-' . $checkin_day, $checkout_year . '-' . $checkout_month . '-' . $checkout_day, $hotel_sel_id, true);
        //dbug($max_nights_packages,1);
        if (!empty($max_nights_packages) && is_array($max_nights_packages)) {
            $packages_max_nights = '';
            foreach ($max_nights_packages as $key => $package) {
                if (!empty($package['hotel_id'])) {
                    if ($package['maximum_nights'] > $packages_max_nights || $packages_max_nights === '') {
                        $packages_max_nights = (int)$package['maximum_nights'];
                    }

                    if (!isset($min_max_hotels[$package['hotel_id']])) {
                        $min_max_hotels[$package['hotel_id']] = array(
                            'package_name' => $package['package_name'],
                            'minimum_nights' => '',
                            'maximum_nights' => $package['maximum_nights'],
                            'minimum_rooms' => '',
                            'maximum_rooms' => '',
                            'partial_nights' => '',
                            'start_date' => $package['start_date'],
                            'finish_date' => $package['finish_date'],
                            'hotel_name' => $package['hotel_name'],
                        );
                    } else {
                        $min_max_hotels[$package['hotel_id']]['maximum_nights'] = $package['maximum_nights'];
                    }
                }
            }

            if (!empty($packages_max_nights) && $packages_max_nights > $max_nights) {
                $max_nights = $packages_max_nights;
            }
        }

        // -----------------------------------------------------
        // Find general min rooms via all packages
        // -----------------------------------------------------
        $min_rooms_packages = Packages::GetMinimumRooms($checkin_year . '-' . $checkin_month . '-' . $checkin_day, $checkout_year . '-' . $checkout_month . '-' . $checkout_day, $hotel_sel_id, true);
        ///dbug($min_nights_packages);
        if (!empty($min_rooms_packages) && is_array($min_rooms_packages)) {
            $packages_min_rooms = '';
            foreach ($min_rooms_packages as $key => $package) {
                if (!empty($package['hotel_id'])) {
                    if ($package['minimum_rooms'] < $packages_min_rooms || $packages_min_rooms === '') {
                        $packages_min_rooms = (int)$package['minimum_rooms'];
                    }
                    if (!isset($min_max_hotels[$package['hotel_id']])) {
                        $min_max_hotels[$package['hotel_id']] = array(
                            'package_name' => $package['package_name'],
                            'minimum_nights' => '',
                            'maximum_nights' => '',
                            'minimum_rooms' => $package['minimum_rooms'],
                            'maximum_rooms' => '',
                            'start_date' => $package['start_date'],
                            'finish_date' => $package['finish_date'],
                            'hotel_name' => $package['hotel_name'],
                        );
                    } else {
                        $min_max_hotels[$package['hotel_id']]['minimum_rooms'] = $package['minimum_rooms'];
                    }
                }
            }
        }

        // -----------------------------------------------------
        // Find general max rooms via all packages
        // -----------------------------------------------------
        $max_rooms_packages = Packages::GetMaximumRooms($checkin_year . '-' . $checkin_month . '-' . $checkin_day, $checkout_year . '-' . $checkout_month . '-' . $checkout_day, $hotel_sel_id, true);
        //dbug($max_nights_packages,1);
        if (!empty($max_rooms_packages) && is_array($max_rooms_packages)) {
            $packages_max_rooms = '';
            foreach ($max_rooms_packages as $key => $package) {
                if (!empty($package['hotel_id'])) {
                    if ($package['maximum_rooms'] > $packages_max_rooms || $packages_max_rooms == '-1') {
                        $packages_max_rooms = (int)$package['maximum_rooms'];
                    }

                    if (!isset($min_max_hotels[$package['hotel_id']])) {
                        $min_max_hotels[$package['hotel_id']] = array(
                            'package_name' => $package['package_name'],
                            'minimum_nights' => '',
                            'maximum_nights' => '',
                            'minimum_rooms' => '',
                            'maximum_rooms' => $package['maximum_rooms'],
                            'start_date' => $package['start_date'],
                            'finish_date' => $package['finish_date'],
                            'hotel_name' => $package['hotel_name'],
                        );
                    } else {
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
        if ($check_partially_overlapping == 'yes') {
            foreach ($min_nights_packages as $key => $package) {
                if (empty($package['hotel_id'])) {
                    continue;
                }
                $from_part_package = strtotime($package['start_date']);
                $to_part_package = strtotime($package['finish_date']);

                $partial_nights = 0;
                for ($i = 0; $i < $nights; $i++) {
                    $part_reservation_date = strtotime($checkin_year . '-' . $checkin_month . '-' . $checkin_day . '+' . $i . 'day');
                    if ($from_part_package <= $part_reservation_date && $to_part_package >= $part_reservation_date) {
                        $partial_nights++;
                    }
                }
                $min_max_hotels[$package['hotel_id']]['partial_nights'] = $partial_nights;
            }
        }


        foreach ($min_max_hotels as $key => $min_max_hotel) {

            $min_max_alert = '';
            if (!empty($min_max_hotel['minimum_nights']) && $nights < $min_max_hotel['minimum_nights']) {
                $min_max_alert = draw_important_message(
                    str_replace(array('_IN_HOTEL_', '_PACKAGE_NAME_', '_NIGHTS_', '_FROM_', '_TO_'),
                        array($min_max_hotel['hotel_name'] . '. ', $min_max_hotel['package_name'], '<b>' . $min_max_hotel['minimum_nights'] . '</b>', '<b>' . format_date($min_max_hotel['start_date']) . '</b>', '<b>' . format_date($min_max_hotel['finish_date']) . '</b>'),
                        _MINIMUM_NIGHTS_ALERT
                    ), false
                );
            } elseif (!empty($min_max_hotel['maximum_nights']) && $nights > $min_max_hotel['maximum_nights']) {
                $min_max_alert = draw_important_message(
                    str_replace(array('_IN_HOTEL_', '_PACKAGE_NAME_', '_NIGHTS_', '_FROM_', '_TO_'),
                        array($min_max_hotel['hotel_name'] . '. ', $min_max_hotel['package_name'], '<b>' . $min_max_hotel['maximum_nights'] . '</b>', '<b>' . format_date($min_max_hotel['start_date']) . '</b>', '<b>' . format_date($min_max_hotel['finish_date']) . '</b>'),
                        _MAXIMUM_NIGHTS_ALERT
                    ), false
                );
            } elseif ($check_partially_overlapping == 'yes' && !empty($min_max_hotel['partial_nights']) && $min_max_hotel['partial_nights'] < $min_max_hotel['minimum_nights']) {
                // [#002] force check for patial package period of time
                $min_max_alert = draw_important_message(
                    str_replace(array('_IN_HOTEL_', '_PACKAGE_NAME_', '_NIGHTS_', '_FROM_', '_TO_'),
                        array($min_max_hotel['hotel_name'] . '. ', $min_max_hotel['package_name'], '<b>' . $min_max_hotel['minimum_nights'] . '</b>', '<b>' . format_date($min_max_hotel['start_date']) . '</b>', '<b>' . format_date($min_max_hotel['finish_date']) . '</b>'),
                        _MINIMUM_NIGHTS_ALERT
                    ), false
                );
            }

            $min_max_hotels[$key]['alert'] = !empty($min_max_alert) ? $min_max_alert : '';
        }

        $nights_text = ($nights > 1) ? $nights . ' ' . _NIGHTS : $nights . ' ' . _NIGHT;

        draw_content_start();
        if ($objSettings->GetParameter('date_format') == 'mm/dd/yyyy') {
            draw_sub_title_bar(_FROM . ': ' . get_month_local($checkin_month) . ' ' . $checkin_day . ', ' . $checkin_year . ' ' . (is_mobile() ? '<br>' : '') . _TO . ': ' . get_month_local($checkout_month) . ' ' . $checkout_day . ', ' . $checkout_year . ' (' . $nights_text . ')', true, 'h4');
        } else {
            draw_sub_title_bar(_FROM . ': ' . $checkin_day . ' ' . get_month_local($checkin_month) . ' ' . $checkin_year . ' ' . (is_mobile() ? '<br>' : '') . _TO . ': ' . $checkout_day . ' ' . get_month_local($checkout_month) . ' ' . $checkout_year . ' (' . $nights_text . ')', true, 'h4');
        }

        $objRooms = new Rooms();
        $params = array(
            'room_id' => $room_id,
            'from_date' => $checkin_year . '-' . $checkin_month . '-' . $checkin_day,
            'to_date' => $checkout_year . '-' . $checkout_month . '-' . $checkout_day,
            'nights' => $nights,
            'from_year' => $checkin_year,
            'from_month' => $checkin_month,
            'from_day' => $checkin_day,
            'to_year' => $checkout_year,
            'to_month' => $checkout_month,
            'to_day' => $checkout_day,
            'max_adults' => $max_adults,
            'max_children' => $max_children,
            'sort_by' => $sort_by,
            'hotel_sel_id' => $hotel_sel_id,
            'hotel_sel_loc_id' => $hotel_sel_loc_id,
            'property_type_id' => $property_type_id,
            'min_max_hotels' => $min_max_hotels,
            'sort_rating' => $sort_rating,
            'sort_price' => $sort_price,
            'arr_filter_facilities' => $arr_filter_facilities,
            'arr_serialize_facilities' => $arr_serialize_facilities,
            'arr_filter_rating' => $arr_filter_rating,
            'filter_start_distance' => $filter_start_distance,
            'filter_end_distance' => $filter_end_distance,
            'filter_start_price' => $filter_start_price,
            'filter_end_price' => $filter_end_price,
            'minimum_beds' => $minimum_beds
        );

        $rooms_count = $objRooms->SearchFor($params);

        if (AUTOMATIC_RE_SEARCH_ROOM && $rooms_count['rooms'] == 0 && $max_adults > 1) {
            $a_max_adults = $params['max_adults'];
            $a_max_children = $params['max_children'];
            $params['minimum_beds'] = $a_max_adults;
            while ($rooms_count['rooms'] == 0 && $a_max_adults > 1) {
                if ($a_max_adults > 1) {
                    $a_max_adults = (int)($a_max_adults / 2);
                }
                if ($a_max_children > 1) {
                    $a_max_children = (int)($a_max_children / 2);
                }
                $params['max_adults'] = $a_max_adults;
                $params['max_children'] = $a_max_children;

                $rooms_count = $objRooms->SearchFor($params);
            }
            if ($rooms_count > 0) {
                draw_important_message(_NO_ROOMS_FOUND_AUTOMATIC_RE_SEARCH_ROOM);
            }
        }

        if (HOTEL_BUTTON_RESERVE_AJAX) {
            $reservation_info = !empty($_SESSION[INSTALLATION_KEY . 'reservation']) ? $_SESSION[INSTALLATION_KEY . 'reservation'] : array();
            if (!empty($reservation_info)) {
                $res_rooms = 0;
                $res_price = 0;
                foreach ($reservation_info as $val) {
                    $res_rooms += $val['rooms'];
                    $res_price += $val['price'];
                }

                if (!empty($res_rooms)) {
                    $currency_rate = Application::Get('currency_rate');
                    $currency_format = get_currency_format();
                    $res_price_format = Currencies::PriceFormat($res_price * $currency_rate, '', '', $currency_format);
                    echo '<script>
                        jQuery(document).ready(function(){
                            if(typeof(toastr) != "undefined"){
                                // Init
                                toastr.options = {
                                  "closeButton": true,
                                  "debug": false,
                                  "newestOnTop": false,
                                  "progressBar": false,
                                  "positionClass": "toast-bottom-left",
                                  "preventDuplicates": false,
                                  "onclick": null,
                                  "showDuration": "300",
                                  "hideDuration": "1000",
                                  "timeOut": "0",
                                  "extendedTimeOut": "0",
                                  "showEasing": "swing",
                                  "hideEasing": "linear",
                                  "showMethod": "fadeIn",
                                  "hideMethod": "fadeOut",
                                  "rtl": ' . (Application::Get('lang_dir') == 'rtl' ? 'true' : 'false') . '
                                }
                
                                toastr.success("<a href=\"index.php?page=booking\">' . htmlspecialchars(str_replace(array('_NUMBER_ROOMS_', '_ROOMS_', '_PRICE_'), array($res_rooms, $res_rooms == 1 ? _ROOM : _ROOMS, $res_price_format), _YOU_HAVE_RESERVED)) . '</a>");
								toastr.success("' . str_replace('"', '\\"', _MESSAGE_INFO_LINK_BOOKING) . '");
                            }
                        });
                    </script>';
                }
            }
        }

        echo '<div class="line-hor"></div>';
        if ($rooms_count['rooms'] > 0) {
            $objRooms->DrawSearchResult($params, $rooms_count['rooms']);
        } else {
            $important_message = _NO_ROOMS_FOUND;
            $message = _SEARCH_ROOM_TIPS;

            if (ModulesSettings::Get('rooms', 'allow_relocation_to_other_hotel') == 'yes') {
                $important_message = _MS_RELOCATION_GET_DISCOUNT;
                $message = _MS_RELOCATION_HOTEL_PROGRAM;
            } elseif (ModulesSettings::Get('rooms', 'allow_relocation_to_other_room') == 'yes') {
                $important_message = _MS_RELOCATION_GET_DISCOUNT;
                $message = _MS_RELOCATION_ROOM_PROGRAM;

            }

            draw_important_message($important_message);
            draw_message($message);

            // ADVANCES SEARCH
            if (ModulesSettings::Get('rooms', 'allow_relocation_to_other_room') == 'yes' || ModulesSettings::Get('rooms', 'allow_relocation_to_other_hotel') == 'yes') {
                echo '<div class="suggestion-wrapper">
                      <div class="suggestion-wrapper-image">
					  <br>&nbsp;' . _PERFORM_ADDITIONAL_SEARCH . '
					  <br><img src="images/ajax_loader_long.gif" alt="" />
					  </div>
					  </div>';

                echo '<script>
					jQuery.ajax({
						url: "ajax/search_suggestion.ajax.php",
						global: false,
						type: "POST",
						data: ({
							token:                      "' . Application::Get('token') . '",
							check_key :                 "apphphs",
							act:                        "send",
							room_id:                    "' . $room_id . '",
                            from_date:                  "' . $checkin_year . '-' . $checkin_month . '-' . $checkin_day . '",
                            to_date:                    "' . $checkout_year . '-' . $checkout_month . '-' . $checkout_day . '",
                            nights:                     "' . $nights . '",
                            from_year:                  "' . $checkin_year . '",
                            from_month:                 "' . $checkin_month . '",
                            from_day:                   "' . $checkin_day . '",
                            to_year:                    "' . $checkout_year . '",
                            to_month:                   "' . $checkout_month . '",
                            to_day:                     "' . $checkout_day . '",
                            max_adults:                 "' . $max_adults . '",
                            max_children:               "' . $max_children . '",
                            sort_by:                    "' . $sort_by . '",
                            hotel_sel_id:               "' . $hotel_sel_id . '",
                            hotel_sel_loc_id:           "' . $hotel_sel_loc_id . '",
                            property_type_id:           "' . $property_type_id . '",
                            min_max_hotels:             "' . (!empty($min_max_hotels) ? serialize($min_max_hotels) : '') . '",
                            sort_rating:                "' . $sort_rating . '",
                            sort_price:                 "' . $sort_price . '",
                            arr_filter_facilities:      "' . (!empty($arr_filter_facilities) ? serialize($arr_filter_facilities) : '') . '",
                            arr_serialize_facilities:   "' . (!empty($arr_serialize_facilities) ? serialize($arr_serialize_facilities) : '') . '",
                            arr_filter_rating:          "' . (!empty($arr_filter_rating) ? serialize($arr_filter_rating) : '') . '",
                            filter_start_distance:      "' . $filter_start_distance . '",
                            filter_end_distance:        "' . $filter_end_distance . '",
                            filter_start_price:         "' . $filter_start_price . '",
                            filter_end_price:           "' . $filter_end_price . '",
                            minimum_beds:               "' . $minimum_beds . '"
						}),
						dataType: "html",
						async: true,
						error: function(html){
						    jQuery(".suggestion-wrapper-image").hide();
							console.error("AJAX: cannot connect to the server or server response error! Please try again later.");
						},
						success: function(data){
							//console.log(data);
							var obj = jQuery.parseJSON(data);
 
							if(obj[0].status == "1"){
							    if(obj[2].relocation_to !== ""){
							        jQuery(".center_box_heading").append(" ("+obj[2].relocation_to+")");
                                }
							    //console.log(obj[1].html);
								jQuery(".suggestion-wrapper-image").hide();
								jQuery(".fail").hide();
								jQuery(".notice").hide();
								jQuery(".suggestion-wrapper").append(obj[1].html);
							}else{
								jQuery(".suggestion-wrapper-image").hide();
								jQuery(".msg fail").show();
								jQuery(".msg notice").show();
							}
						}
					});
				</script>';
            } elseif (ModulesSettings::Get('rooms', 'allow_relocation_to_other_room') == 'no' && ModulesSettings::Get('rooms', 'allow_relocation_to_other_hotel') == 'no') {
                Rooms::DrawTrySystemSuggestionForm($room_id, $checkin_day, $checkin_year_month, $checkout_day, $checkout_year_month, $max_adults, $max_children);
            }
        }

        draw_content_end();
    }
}


?>