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

$hotelId		= isset($_POST['hotelId']) ? (int)$_POST['hotelId'] : '';
$arr_result     = array();
$arr_inventory  = array();
$arr_rooms 		= array();
$hotelsList     = array();
$where_clause   = '';

if(!empty($hotelId)){

    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');   // Date in the past
    header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
    header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
    header('Pragma: no-cache'); // HTTP/1.0
    header('Content-Type: application/json');

    $hotelOwner = $objLogin->IsLoggedInAs('hotelowner');
    $hotelManager = $objLogin->IsLoggedInAs('hotelmanager');

    if($hotelOwner || $hotelManager){
        $hotelsList = $objLogin->AssignedToHotels();
        $where_clause_rooms = (!empty($hotelsList) ? 'r.hotel_id IN ('.(implode(',', $hotelsList)).')' : '1 = 0');
        $where_clause_inventory = (!empty($hotelsList) ? DB_PREFIX.'property_inventory.hotel_id IN ('.(implode(',', $hotelsList)).')' : '1 = 0');
    }else{
        $where_clause_rooms = 'r.hotel_id = '.$hotelId.' AND r.is_active = 1';
        $where_clause_inventory = DB_PREFIX.'property_inventory.hotel_id = '.$hotelId;
    }

    $total_rooms = Rooms::GetAllRooms($where_clause_rooms);
    if(!empty($total_rooms) && is_array($total_rooms)){
        foreach($total_rooms[0] as $key => $val){
            $arr_rooms['room_'.$val['id']] = '"'.$val['id'].'": "'.$val['room_type'].'"';
        }
    }

    $total_inventory = PropertyInventory::GetAllPropertyInventory($where_clause_inventory);
    if(!empty($total_inventory) && is_array($total_inventory)){
        foreach($total_inventory[0] as $key => $val){
            $arr_inventory['inventory_'.$val['id']] = '"'.$val['id'].'": "'.$val['property_name'].'"';
        }
    }

    if(!empty($arr_rooms) && is_array($arr_rooms)){
        $arr_result[] = '"rooms": {'.implode(',', $arr_rooms).'}';
    }
    if(!empty($arr_inventory) && is_array($arr_inventory)){
        $arr_result[] = '"inventory": {'.implode(',', $arr_inventory).'}';
    }
}

echo '{';
echo implode(',', $arr_result);
echo '}';