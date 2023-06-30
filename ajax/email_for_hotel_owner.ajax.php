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
$arr_search 	= isset($search) ? explode(',', $search, 3) : array();
$token 			= isset($_POST['token']) ? prepare_input($_POST['token']) : '';
$lang 			= isset($_POST['lang']) ? prepare_input($_POST['lang']) : Application::Get('lang');
$session_token 	= isset($_SESSION[INSTALLATION_KEY]['token']) ? prepare_input($_SESSION[INSTALLATION_KEY]['token']) : '';
$arr 			= array();

if(ModulesSettings::Get('accounts', 'hotel_owner_allow_registration') == 'yes' && $act == 'send' && ($token == $session_token) && !empty($arr_search)){

	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');   // Date in the past
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
	header('Pragma: no-cache'); // HTTP/1.0
	header('Content-Type: application/json');

	// HOTEL / LOCATION / COUNTRY
	$sql = "SELECT
				h.id,
				h.hotel_location_id as location_id,
				h.email as email,
				hd.name as hotel_name,
                hld.name as location_name,
                cd.name as country_name
            FROM ".TABLE_HOTELS." as h
				INNER JOIN ".TABLE_HOTELS_DESCRIPTION." as hd ON hd.hotel_id = h.id AND hd.language_id = '".$lang."'
				INNER JOIN ".TABLE_HOTELS_LOCATIONS." as hl ON hl.id = h.hotel_location_id
                INNER JOIN ".TABLE_HOTELS_LOCATIONS_DESCRIPTION." as hld ON hld.hotel_location_id = h.hotel_location_id AND hld.language_id = '".$lang."'
                LEFT OUTER JOIN ".TABLE_COUNTRIES." as c ON c.abbrv = hl.country_id AND c.is_active = 1
				LEFT OUTER JOIN ".TABLE_COUNTRIES_DESCRIPTION." as cd ON c.id = cd.country_id AND cd.language_id = '".$lang."'
            WHERE 
				h.is_active = 1 AND                
				(".(!empty($property_type) ? 'property_type_id = '.$property_type.' AND ' : '')."
				REPLACE(hd.name, ' ', '') LIKE '%".str_replace(' ', '', $search)."%') OR
                (
					hld.name LIKE  '%$search%' OR
					cd.name LIKE '%$search%'
					".(count($arr_search) == 2 ? " OR (cd.name LIKE '%".trim($arr_search[1])."%' AND hld.name LIKE '%".trim($arr_search[0])."%')" : '')."
				)			
            LIMIT 15";
	$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
	//echo database_error();
	if($result[1] > 0){
	    for($i = 0; $i < $result[1]; $i++){
	        if(!empty($result[0][$i]['email'])){
                $arr['hot_'.$result[0][$i]['id']] = '{"hotel_name": "'.$result[0][$i]['hotel_name'].'", "hotel_id": "'.$result[0][$i]['id'].'", "hotel_email": "'.$result[0][$i]['email'].'", "label": "'.$result[0][$i]['hotel_name'].', '.$result[0][$i]['location_name'].', '.$result[0][$i]['country_name'].'"}';
            }
	    }    
	}	
	
	echo '[';
	echo implode(',', $arr);
	echo ']';
}    
