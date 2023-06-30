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

	$where_clause = 'is_active = 1 ';
	$params = array();
	$full_name = explode(' ', $search, 2);
	if(!empty($full_name)){
		if(count($full_name) == 1){
			$full_name[0] = strip_tags(prepare_input($full_name[0], true));
			if(is_numeric($full_name[0])){
				$params['id']      = $full_name[0];
			}
			$params['first_name'] = $full_name[0].'%';
			$params['last_name']  = $full_name[0].'%';

			$where_clause .= 'AND (first_name LIKE \''.$params['first_name'].'\' OR last_name LIKE \''.$params['last_name'].'\''.(!empty($params['id']) ? ' OR id = '.$params['id'] : '').')';
		}else{
			$fullName[0] = strip_tags(prepare_input($fullName[0], 1));
			$fullName[1] = strip_tags(prepare_input($fullName[1], 1));
			$params['first_name_1'] = $fullName[1].'%';
			$params['last_name_1']  = $fullName[0].'%';
			$params['first_name_2'] = $fullName[0].'%';
			$params['last_name_2']  = $fullName[1].'%';

			$where_clause .= 'AND ((first_name LIKE \''.$params['first_name_1'].'\' OR last_name LIKE \''.$params['last_name_1'].'\') OR (first_name LIKE \''.$params['first_name_2'].'\' AND last_name LIKE \''.$params['last_name_2'].'\'))';
		}

		$result = database_query('SELECT * FROM '.TABLE_CUSTOMERS.' WHERE '.$where_clause, DATA_AND_ROWS);

		//echo database_error();
		if($result[1] > 0){
			for($i = 0; $i < $result[1]; $i++){
				$arr['customer_'.$result[0][$i]['id']] = '{"id": '.$result[0][$i]['id'].', "label": "'.$result[0][$i]['first_name'].' '.$result[0][$i]['last_name'].'"}';  
			}    
		}	
	}
	
	echo '[';
	echo implode(',', $arr);
	echo ']';
}
