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
$search 		= isset($_POST['search']) ? trim(prepare_input($_POST['search'], true)) : '';
$property_type 	= isset($_POST['property_type']) ? (int)$_POST['property_type'] : '';
$token 			= isset($_POST['token']) ? prepare_input($_POST['token']) : '';
$lang 			= isset($_POST['lang']) ? prepare_input($_POST['lang']) : Application::Get('lang');
$session_token 	= isset($_SESSION[INSTALLATION_KEY]['token']) ? prepare_input($_SESSION[INSTALLATION_KEY]['token']) : '';
$arr 			= array();

if($act == 'send' && ($token == $session_token) && !empty($search)){

	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');   // Date in the past
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
	header('Pragma: no-cache'); // HTTP/1.0
	header('Content-Type: application/json');

    $where_clause = !empty($property_type) ? 'h.property_type_id = '.$property_type.' AND ' : '';
	// HOTEL / LOCATION / COUNTRY
    $result = HotelsLocations::FindHotelLocationCountry($search, $where_clause, $lang, 10);
	//echo database_error();
	if($result[1] > 0){
	    for($i = 0; $i < $result[1]; $i++){
			$arr['hot_'.$result[0][$i]['id']] = '{"location_id": "'.$result[0][$i]['location_id'].'", "hotel_id": "'.$result[0][$i]['id'].'", "label": "'.$result[0][$i]['hotel_name'].(!$result[0][$i]['is_active'] ? ' ['.htmlspecialchars(_INACTIVE).']' : '').', '.$result[0][$i]['location_name'].', '.$result[0][$i]['country_name'].'"}';  
	    }    
	}	

	// LOCATION / COUNTRY
    $result = HotelsLocations::FindLocationCountry($search, $lang, 10);
	//echo database_error();
	if($result[1] > 0){
	    for($i = 0; $i < $result[1]; $i++){
			$arr['loc_'.$result[0][$i]['id']] = '{"location_id": "'.$result[0][$i]['id'].'", "hotel_id": "", "label": "'.$result[0][$i]['location_name'].', '.$result[0][$i]['country_name'].'"}';  
	    }    
	}
	
	echo '[';
	echo implode(',', $arr);
	echo ']';
}
