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

$country_code = isset($_POST['country_code']) ? prepare_input($_POST['country_code']) : '';
$check_key = isset($_POST['check_key']) ? prepare_input($_POST['check_key']) : '';
$token = isset($_POST['token']) ? prepare_input($_POST['token']) : '';
$session_token = isset($_SESSION[INSTALLATION_KEY]['token']) ? prepare_input($_SESSION[INSTALLATION_KEY]['token']) : '';
$arr = array();

if($check_key == 'apphphs' && ($token == $session_token) && $country_code != ''){

	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');   // Date in the past
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
	header('Pragma: no-cache'); // HTTP/1.0
	header('Content-Type: application/json');
	
	$arr[] = '{"status": "1"}';
    
    $result = States::GetAllActive(($country_code != '') ? TABLE_COUNTRIES.'.abbrv = \''.$country_code.'\'' : '');
    for($i=0; $i<$result[1]; $i++){
        $arr[] = '{"abbrv": "'.$result[0][$i]['abbrv'].'", "name": "'.$result[0][$i]['name'].'"}';  
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
