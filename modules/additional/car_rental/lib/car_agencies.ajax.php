<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site http://www.hotel-booking-script.com
* @license http://hotel-booking-script.com/license.php
*/

define('APPHP_EXEC', 'access allowed');
define('APPHP_CONNECT', 'direct');
require_once('../include/base.inc.php');
require_once('../include/connection.php');

$agency_id = isset($_POST['agency_id']) ? prepare_input($_POST['agency_id']) : '';
$check_key = isset($_POST['check_key']) ? prepare_input($_POST['check_key']) : '';
$token = isset($_POST['token']) ? prepare_input($_POST['token']) : '';
$session_token = isset($_SESSION[INSTALLATION_KEY]['token']) ? prepare_input($_SESSION[INSTALLATION_KEY]['token']) : '';
$arr = array();

if($check_key == 'apphphs' && ($token == $session_token) && $agency_id != ''){

	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');   // Date in the past
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
	header('Pragma: no-cache'); // HTTP/1.0
	header('Content-Type: application/json');
	
	$arr[] = '{"status": "1"}';
    
    $result = CarAgenciesVehicleTypes::GetAllActive(($agency_id != '') ? TABLE_CAR_AGENCY_VEHICLE_TYPES.'.agency_id = \''.$agency_id.'\'' : '');
    for($i=0; $i<$result[1]; $i++){
        $arr[] = '{"abbrv": "'.$result[0][$i]['id'].'", "name": "'.$result[0][$i]['name'].'"}';  
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
