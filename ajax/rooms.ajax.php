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

$act 			= isset($_POST['act']) ? $_POST['act'] : '';
$hotel_id		= isset($_POST['hotel_id']) ? (int)$_POST['hotel_id'] : '';
$lang 			= isset($_POST['lang']) ? prepare_input($_POST['lang'], true) : Application::Get('lang');
$arr 			= array();

// if($objLogin->IsLoggedInAs('owner','mainadmin','hotelowner','admin') && !empty($hotel_id)){

	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');   // Date in the past
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
	header('Pragma: no-cache'); // HTTP/1.0
	header('Content-Type: application/json');

    $hotels_list = ($objLogin->IsLoggedInAs('hotelowner')) ? implode(',', $objLogin->AssignedToHotels()) : '';
    $where_clause = !empty($hotels_list) ? 'r.hotel_id IN ('.$hotels_list.')' : ($objLogin->IsLoggedInAs('hotelowner') ? '1=0' : '1=1');

	$sql = 'SELECT
				r.id,
				rd.room_type
            FROM '.TABLE_ROOMS.' as r
				LEFT OUTER JOIN '.TABLE_ROOMS_DESCRIPTION.' as rd ON rd.room_id = r.id AND rd.language_id = \''.$lang.'\'
                INNER JOIN '.TABLE_HOTELS.' h ON r.hotel_id = h.id AND h.is_active = 1
            WHERE 
                r.is_active = 1 AND
                '.$where_clause.' AND
                r.hotel_id = '.$hotel_id;
            
    $result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
	echo database_error();
	if($result[1] > 0){
	    for($i = 0; $i < $result[1]; $i++){
			$arr['room_'.$result[0][$i]['id']] = '{"id": "'.$result[0][$i]['id'].'", "name": "'.$result[0][$i]['room_type'].'"}';  
	    }    
	}	
	
	echo '[';
	echo implode(',', $arr);
	echo ']';
//}    
