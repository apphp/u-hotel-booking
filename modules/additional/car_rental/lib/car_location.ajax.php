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

$act 			= isset($_POST['act']) ? $_POST['act'] : '';
$search 		= isset($_POST['search']) ? trim(prepare_input($_POST['search'], true)) : '';
$arr_search 	= isset($search) ? explode(',', $search, 2) : array();
$token 			= isset($_POST['token']) ? prepare_input($_POST['token']) : '';
$lang 			= isset($_POST['lang']) ? prepare_input($_POST['lang']) : Application::Get('lang');
$session_token 	= isset($_SESSION[INSTALLATION_KEY]['token']) ? prepare_input($_SESSION[INSTALLATION_KEY]['token']) : '';
$arr 			= array();

if($act == 'send' && ($token == $session_token) && !empty($arr_search)){

	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');   // Date in the past
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
	header('Pragma: no-cache'); // HTTP/1.0
	header('Content-Type: application/json');
	
	$sql = 'SELECT
				al.id,
                ald.name as location_name,
                cd.name as country_name
            FROM '.TABLE_CAR_AGENCIES_LOCATIONS.' al
                INNER JOIN '.TABLE_CAR_AGENCIES_LOCATIONS_DESCRIPTION.' ald ON ald.agency_location_id = al.id AND ald.language_id = \''.$lang.'\'
                LEFT OUTER JOIN '.TABLE_COUNTRIES.' c ON c.abbrv = al.country_id AND c.is_active = 1
				LEFT OUTER JOIN '.TABLE_COUNTRIES_DESCRIPTION.' cd ON c.id = cd.country_id AND cd.language_id = \''.$lang.'\'
            WHERE 
                al.is_active = 1 AND
                (
					ald.name LIKE  \'%'.$search.'%\' OR
					cd.name LIKE \'%'.$search.'%\' 
					'.(count($arr_search) == 2 ? ' OR (cd.name LIKE \'%'.trim($arr_search[0]).'%\' AND ald.name LIKE \'%'.trim($arr_search[1]).'%\')' : '').'
				)			
            LIMIT 15';
//    $arr[] = '{"sql":"'.htmlentities($sql).'"}';
	$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
	if($result[1] > 0){
	    for($i = 0; $i < $result[1]; $i++){
		    $arr[] = '{"id": "'.$result[0][$i]['id'].'", "label": "'.$result[0][$i]['location_name'].', '.$result[0][$i]['country_name'].'"}';  
	    }    
	}

	echo '[';
	echo implode(',', $arr);
	echo ']';
}else{
	echo htmlspecialchars(_TOKEN_EXPIRED_ALERT);
}