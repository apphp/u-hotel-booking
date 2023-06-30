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

$location_id 		= isset($_POST['location_id']) ? prepare_input($_POST['location_id']) : '';
$property_type_id 	= isset($_POST['property_type_id']) ? (int)$_POST['property_type_id'] : '';
$check_key 			= isset($_POST['check_key']) ? prepare_input($_POST['check_key']) : '';
$token 				= isset($_POST['token']) ? prepare_input($_POST['token']) : '';
$lang				= isset($_POST['lang']) ? prepare_input($_POST['lang']) : '';
$session_token 		= isset($_SESSION[INSTALLATION_KEY]['token']) ? prepare_input($_SESSION[INSTALLATION_KEY]['token']) : '';
$arr 				= array();

if($check_key == 'apphphs' && ($token == $session_token)){
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');   // Date in the past
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
	header('Pragma: no-cache'); // HTTP/1.0
	header('Content-Type: application/json');
	
	$arr[] = '{"status": "1"}';
    
    $where_clause = ($location_id) ? TABLE_HOTELS.'.hotel_location_id = '.(int)$location_id : '';    
    $where_clause .= ($property_type_id) ? (($where_clause != '') ? ' AND ' : '' ).TABLE_HOTELS.'.property_type_id = '.(int)$property_type_id : ''; 
    
    $result = Hotels::GetAllActive($where_clause, $lang);
    for($i=0; $i<$result[1]; $i++){
        $arr[] = '{"template_name": "'.htmlentities($result[0][$i]['name']).'", "id": "'.$result[0][$i]['id'].'"}';  
    }
    
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

