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
$country_id		= isset($_POST['country_id']) ? prepare_input($_POST['country_id'], true) : '';
$arr_search 	= isset($search) ? explode(' ', $search, 2) : array();
$token 			= isset($_POST['token']) ? prepare_input($_POST['token']) : '';
$lang 			= isset($_POST['lang']) ? prepare_input($_POST['lang']) : Application::Get('lang');
$session_token 	= isset($_SESSION[INSTALLATION_KEY]['token']) ? prepare_input($_SESSION[INSTALLATION_KEY]['token']) : '';
$arr 			= array();
$account_type   = Session::Get('session_account_type');

if($act == 'send' && ($token == $session_token) && !empty($arr_search) && in_array($account_type, array('owner', 'mainadmin', 'admin'))){

	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');   // Date in the past
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
	header('Pragma: no-cache'); // HTTP/1.0
	header('Content-Type: application/json');

	if(count($arr_search) > 1){
		$first_name = $arr_search[0];
		$last_name = $arr_search[1];
	}else{
		$first_name = $search;
		$last_name = $search;
	}

	// TRAVEL AGENCY / COUNTRY
	$sql = "SELECT
				cs.id,
				CONCAT(cs.first_name, ' ', cs.last_name) as full_name,
				cs.email,
				cs.user_name
            FROM ".TABLE_CUSTOMERS." as cs
                LEFT OUTER JOIN ".TABLE_COUNTRIES." as c ON c.abbrv = cs.b_country AND c.is_active = 1
				LEFT OUTER JOIN ".TABLE_COUNTRIES_DESCRIPTION." as cd ON c.id = cd.country_id AND cd.language_id = '$lang'
			WHERE 
				cs.customer_type = 1 AND 
				c.abbrv = '$country_id' AND
				cs.is_active = 1 AND 
				cs.is_removed = 0 AND (
				cs.first_name LIKE '$first_name%' ".(count($arr_search) > 1 ? "AND" : "OR")."
				cs.last_name LIKE '$last_name%' OR 
				cd.name LIKE '%$search%'
			   	".(count($arr_search) == 1 ? "OR
                (
					cs.company LIKE  '%$search%' OR
					cs.email LIKE  '$search%' OR
					cs.user_name LIKE '$search%'
				)" : '').")
            LIMIT 15";
//	$arr[] = str_replace(array("\n\r","\n","\t"), ' ', $sql);
	$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
	//echo database_error();
	if($result[1] > 0){
	    for($i = 0; $i < $result[1]; $i++){
			$arr[$result[0][$i]['id']] = '{"full_name": "'.$result[0][$i]['full_name'].'", "travel_agency_id": "'.$result[0][$i]['id'].'", "label": "'.$result[0][$i]['full_name'].' ('.$result[0][$i]['user_name'].', '.$result[0][$i]['email'].')"}';  
	    }    
	}	
	
	echo '[';
	echo implode(',', $arr);
	echo ']';
}    
