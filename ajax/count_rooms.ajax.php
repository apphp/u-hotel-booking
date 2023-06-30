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

$act            = isset($_POST['act']) ? $_POST['act'] : '';
$room_id        = isset($_POST['room_id']) ? (int)$_POST['room_id'] : '';

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
$arr            = array();
//if($objLogin->IsLoggedInAs('owner','mainadmin','hotelowner','admin') && !empty($room_id)){
if(!empty($room_id)){
    $where_clause = '';
    if($objLogin->IsLoggedInAs('hotelowner')){
        $hotels_list = implode(',', $objLogin->AssignedToHotels());
        $where_clause = !empty($hotels_list) ? 'r.hotel_id IN ('.$hotels_list.')' : '1=0';
    }

	$sql = 'SELECT
				r.id,
			FROM '.TABLE_ROOMS.' r
			WHERE
				r.id = '.(int)$room_id.(!empty($where_clause) ? ' AND '.$where_clause : '');

	$room_info = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);

    if(!empty($room_info)){
        $result = Rooms::GetMinMaxCountRooms($checkin_year.'-'.$checkin_month.'-'.$checkin_day, $checkout_year.'-'.$checkout_month.'-'.$checkout_day, $room_id);
        $min_rooms = !empty($result['min_rooms']) ? $result['min_rooms'] : 1;
        $max_rooms = !empty($result['max_rooms']) ? $result['max_rooms'] : 0;

        if(TYPE_FILTER_TO_NUMBER_ROOMS != 'rooms'){
            $min_rooms = 1;
        }
        //echo database_error();
        if(!empty($result)){
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');   // Date in the past
            header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
            header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
            header('Pragma: no-cache'); // HTTP/1.0
            header('Content-Type: application/json');

            echo '{"min_rooms": '.$min_rooms.', "max_rooms": '.$max_rooms.'}';
        }
    }
}
