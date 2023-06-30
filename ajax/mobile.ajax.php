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

$act 			= isset($_GET['act']) ? $_GET['act'] : '';
$action 		= isset($_GET['action']) ? $_GET['action'] : '';
$lang 			= isset($_GET['lang']) ? prepare_input($_GET['lang']) : Application::Get('lang');
$arr 			= array();


//is_ajax()

if($act == 'send'){

	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');   // Date in the past
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
	header('Pragma: no-cache'); // HTTP/1.0
	header('Content-Type: application/json');

	if($action == 'languages_get_all'){
		$result = Languages::GetAllActive();
		//echo database_error();
		if($result[1] > 0){
			for($i = 0; $i < $result[1]; $i++){
				$arr[] = '{"lang_abbrev":"'.$result[0][$i]['abbreviation'].'","lang_name":"'.$result[0][$i]['lang_name_en'].'"}';
			}
		}
	}elseif($action == 'currencies_get_all'){
		$result = Currencies::GetAllActive();
		if($result[1] > 0){
			for($i = 0; $i < $result[1]; $i++){
				$arr[] = '{"currency_code":"'.$result[0][$i]['code'].'","currency_name":"'.$result[0][$i]['name'].'","currency_symbol":"'.$result[0][$i]['symbol'].'"}';
			}
		}
	}


	echo '[';
	echo implode(',', $arr);
	echo ']';

}