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

$act                        = isset($_POST['act']) ? $_POST['act'] : '';
$check_key 		            = isset($_POST['check_key']) ? prepare_input($_POST['check_key']) : '';
$token 			            = isset($_POST['token']) ? prepare_input($_POST['token']) : '';

$session_token 	= isset($_SESSION[INSTALLATION_KEY]['token']) ? prepare_input($_SESSION[INSTALLATION_KEY]['token']) : '';
$arr = array();

if($check_key == 'apphphs' && ($token == $session_token) && (ModulesSettings::Get('rooms', 'allow_relocation_to_other_room') == 'yes' || ModulesSettings::Get('rooms', 'allow_relocation_to_other_hotel') == 'yes')){
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');   // Date in the past
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
	header('Pragma: no-cache'); // HTTP/1.0
	header('Content-Type: application/json');

    $objRooms = new Rooms();
    $searchResult = '';
    $arrAvRooms = array();
    $rooms_count = array();
    $params = array();

    $room_id                    = isset($_POST['room_id']) ? prepare_input($_POST['room_id']) : '';
    $from_date                  = isset($_POST['from_date']) ? prepare_input($_POST['from_date']) : '';
    $to_date                    = isset($_POST['to_date']) ? prepare_input($_POST['to_date']) : '';
    $nights                     = isset($_POST['nights']) ? prepare_input($_POST['nights']) : '';
    $from_year                  = isset($_POST['from_year']) ? prepare_input($_POST['from_year']) : '';
    $from_month                 = isset($_POST['from_month']) ? prepare_input($_POST['from_month']) : '';
    $from_day                   = isset($_POST['from_day']) ? prepare_input($_POST['from_day']) : '';
    $to_year                    = isset($_POST['to_year']) ? prepare_input($_POST['to_year']) : '';
    $to_month                   = isset($_POST['to_month']) ? prepare_input($_POST['to_month']) : '';
    $to_day                     = isset($_POST['to_day']) ? prepare_input($_POST['to_day']) : '';
    $max_adults                 = isset($_POST['max_adults']) ? (int)$_POST['max_adults'] : 1;
    $max_children               = isset($_POST['max_children']) ? (int)$_POST['max_children'] : 0;
    $sort_by                    = isset($_POST['sort_by']) ? prepare_input($_POST['sort_by']) : '';
    $hotel_sel_id               = isset($_POST['hotel_sel_id']) ? prepare_input($_POST['hotel_sel_id']) : '';
    $hotel_sel_loc_id           = isset($_POST['hotel_sel_loc_id']) ? prepare_input($_POST['hotel_sel_loc_id']) : '';
    $property_type_id           = isset($_POST['property_type_id']) ? (int)$_POST['property_type_id'] : 0;
    $min_max_hotels             = !empty($_POST['min_max_hotels']) ? unserialize($_POST['min_max_hotels']) : array();
    $sort_rating                = !empty($_POST['sort_rating']) ? prepare_input($_POST['sort_rating']) : null;
    $sort_price                 = isset($_POST['sort_price']) ? prepare_input($_POST['sort_price']) : '';
    $arr_filter_facilities      = !empty($_POST['arr_filter_facilities']) ? unserialize($_POST['arr_filter_facilities']) : array();
    $arr_serialize_facilities   = !empty($_POST['arr_serialize_facilities']) ? unserialize($_POST['arr_serialize_facilities']) : array();
    $arr_filter_rating          = !empty($_POST['arr_filter_rating']) ? unserialize($_POST['arr_filter_rating']) : array();
    $filter_start_distance      = isset($_POST['filter_start_distance']) ? (int)$_POST['filter_start_distance'] : 0;
    $filter_end_distance        = isset($_POST['filter_end_distance']) ? (int)$_POST['filter_end_distance'] : 0;
    $filter_start_price         = isset($_POST['filter_start_price']) ? (int)$_POST['filter_start_price'] : 0;
    $filter_end_price           = isset($_POST['filter_end_price']) ? (int)$_POST['filter_end_price'] : 0;
    $minimum_beds               = isset($_POST['minimum_beds']) ? prepare_input($_POST['minimum_beds']) : '';

    $params = array(
        'room_id'                  => $room_id,
        'max_adults'               => $max_adults,
        'max_children'             => $max_children,
        'sort_by'                  => $sort_by,
        'hotel_sel_id'             => $hotel_sel_id,
        'hotel_sel_loc_id'         => $hotel_sel_loc_id,
        'property_type_id'         => $property_type_id,
        'min_max_hotels'           => $min_max_hotels,
        'sort_rating'              => $sort_rating,
        'sort_price'               => $sort_price,
        'arr_filter_facilities'    => $arr_filter_facilities,
        'arr_serialize_facilities' => $arr_serialize_facilities,
        'arr_filter_rating'        => $arr_filter_rating,
        'filter_start_distance'    => $filter_start_distance,
        'filter_end_distance'      => $filter_end_distance,
        'filter_start_price'       => $filter_start_price,
        'filter_end_price'         => $filter_end_price,
        'minimum_beds'		       => $minimum_beds,
    );

    $rooms_found = false;
    $relocation_to = '';
    $relocation_to_room = false;
    $relocation_to_hotel = false;

    // Search rooms in one hotel
    if (ModulesSettings::Get('rooms', 'allow_relocation_to_other_room') == 'yes' && (!empty($hotel_sel_id))) {

        $objRooms->SetRelocationType('room');
        for ($i = 1; $i < $nights; $i++) {
            // Set Params For Room 1
            $count_nights = $i;
            $count_days = ' + ' . ($count_nights) . ' days';
            $checkin_date = date('Y-m-d', strtotime($from_date));
            $checkout_date = date('Y-m-d', strtotime($from_date . $count_days));

            // Search First The Room
            $result_room_1 = $objRooms->SearchSuggestion($count_nights, $checkin_date, $checkout_date, $params);

            // If the first room is found start the search for the second room.
            if ($result_room_1['rooms'] > 0) {
                // Set Params For Room 2
                $count_nights = $nights - $i;
                $count_days = ' - ' . ($count_nights) . ' days';
                $checkin_date = date('Y-m-d', strtotime($to_date . $count_days));
                $checkout_date = date('Y-m-d', strtotime($to_date));

                // Search Second The Room
                $result_room_2 = $objRooms->SearchSuggestion($count_nights, $checkin_date, $checkout_date, $params);

                // If the second room is also found, prepare an array of parameters for a function DrawSearchResult()
                // And And get out of the loop "for"
                if ($result_room_2['rooms'] > 0) {
                    $params['from_date'] = array(1 => $result_room_1['checkin_date'], 2 => $result_room_2['checkin_date']);
                    $params['to_date'] = array(1 => $result_room_1['checkout_date'], 2 => $result_room_2['checkout_date']);
                    $params['nights'] = array(1 => $result_room_1['count_nights'], 2 => $result_room_2['count_nights'], 'total' => $nights);
                    $params['from_year'] = array(1 => $result_room_1['checkin_year'], 2 => $result_room_2['checkin_year']);
                    $params['from_month'] = array(1 => $result_room_1['checkin_month'], 2 => $result_room_2['checkin_month']);
                    $params['from_day'] = array(1 => $result_room_1['checkin_day'], 2 => $result_room_2['checkin_day']);
                    $params['to_year'] = array(1 => $result_room_1['checkout_year'], 2 => $result_room_2['checkout_year']);
                    $params['to_month'] = array(1 => $result_room_1['checkout_month'], 2 => $result_room_2['checkout_month']);
                    $params['to_day'] = array(1 => $result_room_1['checkout_day'], 2 => $result_room_2['checkout_day']);

                    //Set the flag "Rooms found"
                    $rooms_found = true;
                    $relocation_to_room = true;
                    break;
                } else {
                    $objRooms->SetArrAvailableRooms(array());
                }
            }
        }
    }

    //Search for rooms in one location
    if(!$rooms_found && ModulesSettings::Get('rooms', 'allow_relocation_to_other_hotel') == 'yes' && !empty($hotel_sel_loc_id)){

        // Search hotels in location
        $sql = 'SELECT
					'.TABLE_HOTELS.'.*
				FROM '.TABLE_HOTELS.'
				WHERE hotel_location_id = '.(int)$hotel_sel_loc_id.'
				ORDER BY '.TABLE_HOTELS.'.is_active DESC, '.TABLE_HOTELS.'.priority_order ASC ';

        $hotels = database_query($sql, DATA_AND_ROWS, ALL_ROWS);

        if(is_array($hotels) && $hotels[1] > 0){
            $objRooms->SetRelocationType('room');
            for($i=0; $i < $hotels[1]; $i++){
                // Search rooms in one hotel
                for($j=1; $j<$nights; $j++){
                    $params_location = $params;
                    $params_location['hotel_sel_id'] = isset($hotels[0][$i]['id']) ? $hotels[0][$i]['id'] : '';
                    $params_location['hotel_sel_loc_id'] = 0;

                    // Set Params For Room 1
                    $count_nights   = $j;
                    $count_days     = ' + '.($count_nights).' days';
                    $checkin_date   = date('Y-m-d', strtotime($from_date));
                    $checkout_date  = date('Y-m-d', strtotime($from_date.$count_days));

                    // Search First The Room
                    $result_room_1 = $objRooms->SearchSuggestion($count_nights, $checkin_date, $checkout_date, $params_location);

                    // If the first room is found start the search for the second room.
                    if($result_room_1['rooms'] > 0){
                        // Set Params For Room 2
                        $count_nights   = $nights-$j;
                        $count_days     = ' - '.($count_nights).' days';
                        $checkin_date   = date('Y-m-d', strtotime($to_date.$count_days));
                        $checkout_date  = date('Y-m-d', strtotime($to_date));

                        // Search Second The Room
                        $result_room_2 = $objRooms->SearchSuggestion($count_nights, $checkin_date, $checkout_date, $params_location);

                        // If the second room is also found, prepare an array of parameters for a function DrawSearchResult()
                        // And And get out of the loop "for"
                        if($result_room_2['rooms'] > 0){
                            $params['from_date']  = array(1=>$result_room_1['checkin_date'],   2=>$result_room_2['checkin_date']);
                            $params['to_date']    = array(1=>$result_room_1['checkout_date'],  2=>$result_room_2['checkout_date']);
                            $params['nights']     = array(1=>$result_room_1['count_nights'],   2=>$result_room_2['count_nights'], 'total'=>$nights);
                            $params['from_year']  = array(1=>$result_room_1['checkin_year'],   2=>$result_room_2['checkin_year']);
                            $params['from_month'] = array(1=>$result_room_1['checkin_month'],  2=>$result_room_2['checkin_month']);
                            $params['from_day']   = array(1=>$result_room_1['checkin_day'],    2=>$result_room_2['checkin_day']);
                            $params['to_year']    = array(1=>$result_room_1['checkout_year'],  2=>$result_room_2['checkout_year']);
                            $params['to_month']   = array(1=>$result_room_1['checkout_month'], 2=>$result_room_2['checkout_month']);
                            $params['to_day']     = array(1=>$result_room_1['checkout_day'],   2=>$result_room_2['checkout_day']);

                            //Set the flag "Rooms found"
                            $rooms_found = true;
                            $relocation_to_room = true;

                            break(2);
                        }else{
                            $objRooms->SetArrAvailableRooms(array());
                        }
                    }
                }
            }

            // Search for rooms in several hotels
            $objRooms->SetRelocationType('hotel');
            if(!$rooms_found){
                for($i=0; $i < $hotels[1]; $i++){
                    for($j=1; $j<$nights; $j++){
                        // Set Params For Room 1
                        $count_nights   = $j;
                        $count_days     = ' + '.($count_nights).' days';
                        $checkin_date   = date('Y-m-d', strtotime($from_date));
                        $checkout_date  = date('Y-m-d', strtotime($from_date.$count_days));

                        // Search First The Room
                        $result_room_1 = $objRooms->SearchSuggestion($count_nights, $checkin_date, $checkout_date, $params);

                        // If the first room is found start the search for the second room.
                        if($result_room_1['rooms'] > 0){
                            // Set Params For Room 2
                            $count_nights   = $nights-$j;
                            $count_days     = ' - '.($count_nights).' days';
                            $checkin_date   = date('Y-m-d', strtotime($to_date.$count_days));
                            $checkout_date  = date('Y-m-d', strtotime($to_date));

                            // Search Second The Room
                            $result_room_2 = $objRooms->SearchSuggestion($count_nights, $checkin_date, $checkout_date, $params);

                            // If the second room is also found, prepare an array of parameters for a function DrawSearchResult()
                            // And And get out of the loop "for"
                            if($result_room_2['rooms'] > 0){
                                $params['from_date']  = array(1=>$result_room_1['checkin_date'],   2=>$result_room_2['checkin_date']);
                                $params['to_date']    = array(1=>$result_room_1['checkout_date'],  2=>$result_room_2['checkout_date']);
                                $params['nights']     = array(1=>$result_room_1['count_nights'],   2=>$result_room_2['count_nights'], 'total'=>$nights);
                                $params['from_year']  = array(1=>$result_room_1['checkin_year'],   2=>$result_room_2['checkin_year']);
                                $params['from_month'] = array(1=>$result_room_1['checkin_month'],  2=>$result_room_2['checkin_month']);
                                $params['from_day']   = array(1=>$result_room_1['checkin_day'],    2=>$result_room_2['checkin_day']);
                                $params['to_year']    = array(1=>$result_room_1['checkout_year'],  2=>$result_room_2['checkout_year']);
                                $params['to_month']   = array(1=>$result_room_1['checkout_month'], 2=>$result_room_2['checkout_month']);
                                $params['to_day']     = array(1=>$result_room_1['checkout_day'],   2=>$result_room_2['checkout_day']);

                                //Set the flag "Rooms found"
                                $rooms_found = true;
                                $relocation_to_hotel = true;
                                break(2);
                            }else{
                                $objRooms->SetArrAvailableRooms(array());
                            }
                        }
                    }
                }

                $arrAvailableRooms = $objRooms->GetArrAvailableRooms();
                if (count($arrAvailableRooms) <= 1) {
                    $objRooms->SetArrAvailableRooms(array());
                    $rooms_found = false;
                }
            }
        }
    }


    $arrAvailableRooms = $objRooms->GetArrAvailableRooms();
    if (count($arrAvailableRooms) < 1) {
        $objRooms->SetArrAvailableRooms(array());
        $rooms_found = false;
    }

    if($rooms_found){
        if($relocation_to_room){
            $relocation_to = _MS_RELOCATION_TO_OTHER_ROOM;
        }elseif($relocation_to_hotel){
            $relocation_to = _MS_RELOCATION_TO_OTHER_HOTEL;
        }

        $searchResult .= $objRooms->DrawSearchResult($params, 2, false, true);
        $searchResult = json_encode($searchResult);
        $arr[] = '{"status":"1"}';
        $arr[] = '{"html": '.$searchResult.'}';
        $arr[] = '{"relocation_to":"'.$relocation_to.'"}';
    }else{
       $arr[] = '{"status":"0"}';
    }


	// algoritm I

	// 1. Get list of available rooms in hotel
	// 2. Perform split search between these rooms in selected period

    // Rooms from one hotel //
    // $rooms_count = array
    // (
        // 'rooms' => 2,
        // 'hotels' => 1,
        // 'min_price' => 0,
        // 'min_price_per_hotel' => 55
    // );

    // $params = Array
    // (
        // 'room_id' => '',
        // 'from_date' => array(1=>'2019-05-01',2=>'2019-05-05'),
        // 'to_date' => array(1=>'2019-05-05',2=>'2019-05-10'),
        // 'nights' => array(1=>4, 2=>5, 'total'=>9),
        // 'from_year' => array(1=>'2019',2=>'2019'),
        // 'from_month' => array(1=>'05',2=>'05'),
        // 'from_day' => array(1=>'01',2=>'05'),
        // 'to_year' => array(1=>'2019',2=>'2019'),
        // 'to_month' => array(1=>'05',2=>'05'),
        // 'to_day' => array(1=>'05',2=>'10'),
        // 'max_adults' => 1,
        // 'max_children' => 0,
        // 'sort_by' => '',
        // 'hotel_sel_id' => 2,
        // 'hotel_sel_loc_id' => 2,
        // 'property_type_id' => 1,
        // 'min_max_hotels' => array(),
        // 'sort_rating' => '',
        // 'sort_price' => '',
        // 'arr_filter_facilities' => array(),
        // 'arr_serialize_facilities' => array(),
        // 'arr_filter_rating' => array(),
        // 'filter_start_distance' => 0,
        // 'filter_end_distance' => 1000,
        // 'filter_start_price' => 0,
        // 'filter_end_price' => 10000,
        // 'minimum_beds' => '',
    // );

    // $arrAvRooms = array(
        // '2' => array(
            // '0' => array(
                // 'id' => 5,
                // 'available_rooms' => 12,
                // 'facilities' => 'a:8:{i:0;s:1:"4";i:1;s:1:"6";i:2;s:1:"9";i:3;s:2:"11";i:4;s:2:"12";i:5;s:2:"15";i:6;s:2:"16";i:7;s:2:"17";}',
                // 'lowest_price_per_night' => 55,
                // 'max_adults' => 2
            // ),
            // '1' => array(
                // 'id' => 6,
                // 'available_rooms' => 10,
                // 'facilities' => 'a:8:{i:0;s:1:"4";i:1;s:1:"6";i:2;s:1:"9";i:3;s:2:"11";i:4;s:2:"12";i:5;s:2:"15";i:6;s:2:"16";i:7;s:2:"17";}',
                // 'lowest_price_per_night' => 80,
                // 'max_adults' => 4
            // ),
        // )
    // );

	// Rooms from different hotels //
    //$rooms_count = array
    //(
        //'rooms' => 2,
        //'hotels' => 2,
        //'min_price' => 0,
        //'min_price_per_hotel' => 80
    //);

    //$params = Array
    //(
        //'room_id' => '',
        //'from_date' => array(1=>'2019-05-01',2=>'2019-05-05'),
        //'to_date' => array(1=>'2019-05-05',2=>'2019-05-10'),
        //'nights' => array(1=>4, 2=>5, 'total'=>9),
        //'from_year' => array(1=>'2019',2=>'2019'),
        //'from_month' => array(1=>'05',2=>'05'),
        //'from_day' => array(1=>'01',2=>'05'),
        //'to_year' => array(1=>'2019',2=>'2019'),
        //'to_month' => array(1=>'05',2=>'05'),
        //'to_day' => array(1=>'05',2=>'10'),
        //'max_adults' => 4,
        //'max_children' => 0,
        //'sort_by' => '',
        //'hotel_sel_id' => '',
        //'hotel_sel_loc_id' => '',
        //'property_type_id' => 1,
        //'min_max_hotels' => array(),
        //'sort_rating' => '',
        //'sort_price' => '',
        //'arr_filter_facilities' => array(),
        //'arr_serialize_facilities' => array(),
        //'arr_filter_rating' => array(),
        //'filter_start_distance' => 0,
        //'filter_end_distance' => 1000,
        //'filter_start_price' => 0,
        //'filter_end_price' => 10000,
        //'minimum_beds' => '',
    //);

    //$arrAvRooms = array(
        //'2' => array(
            //'0' => array(
                //'id' => 6,
                //'available_rooms' => 10,
                //'facilities' => 'a:8:{i:0;s:1:"4";i:1;s:1:"6";i:2;s:1:"9";i:3;s:2:"11";i:4;s:2:"12";i:5;s:2:"15";i:6;s:2:"16";i:7;s:2:"17";}',
                //'lowest_price_per_night' => 80,
                //'max_adults' => 4
            //),
        //),
        //'3' => array(
            //'0' => array(
                //'id' => 8,
                //'available_rooms' => 12,
                //'facilities' => 'a:11:{i:0;s:1:"1";i:1;s:1:"3";i:2;s:1:"4";i:3;s:1:"8";i:4;s:1:"9";i:5;s:2:"12";i:6;s:2:"14";i:7;s:2:"15";i:8;s:2:"17";i:9;s:2:"19";i:10;s:2:"22";}',
                //'lowest_price_per_night' => 190,
                //'max_adults' => 3
            //),
        //),
    //);

    // $objRooms->SetArrAvailableRooms($arrAvRooms);

	echo '[';
	echo implode(',', $arr);
	echo ']';
}else{
	// wrong parameters passed!
	$arr[] = '{"status": "0"}';
	echo '[';
	echo implode(',', $arr);
	echo ']';
}
