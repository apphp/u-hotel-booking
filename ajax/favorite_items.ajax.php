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

$arr_item_types = array('hotel'=>'1', 'room'=>'2', 'car'=>'3');

$action = isset($_POST['action']) && in_array($_POST['action'], array('add', 'remove')) ? $_POST['action'] : '';
$item_type = isset($_POST['item_type']) && isset($arr_item_types[$_POST['item_type']]) ? $arr_item_types[$_POST['item_type']] : '';
$item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : '';
$customer_id = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : '';
$token = isset($_POST['token']) ? prepare_input($_POST['token']) : '';
$session_token = isset($_SESSION[INSTALLATION_KEY]['token']) ? prepare_input($_SESSION[INSTALLATION_KEY]['token']) : '';
$arr = array();

// We're not using here token validation, because we use regictered client validation, it's enough
$use_token = false;

if(($token == $session_token || !$use_token) && !empty($item_type) && !empty($item_id) && !empty($customer_id) && ($customer_id == $objLogin->GetLoggedId()) && !empty($action)){

	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');   // Date in the past
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
	header('Pragma: no-cache'); // HTTP/1.0
	header('Content-Type: application/json');
	
    $result = ($action == 'remove' ? Wishlist::RemoveFromList($item_type, $item_id, $customer_id) : Wishlist::AddToList($item_type, $item_id, $customer_id));
    if($result){
		$arr[] = '{"status": "1"}';
		$arr[] = '{"message": "'.($action == 'add' ? htmlentities(_ADDED_TO_WISHLIST) : htmlentities(_REMOVED_FROM_WISHLIST)).'"}';
	}else{
		$arr[] = '{"status": "0"}';
		$arr[] = '{"message": "'.htmlentities(_TRY_LATER).'"}';
    }    

	echo '[';
	echo implode(',', $arr);
	echo ']';
}else{
	// wrong parameters passed!
	$arr[] = '{"status": "0"}';
	
	if(empty($customer_id)){
		$arr[] = '{"message": "'.htmlentities(_NOT_LOGGED_ALERT).'"}';
	}else{
		$arr[] = '{"message": "'.htmlentities(_TRY_LATER).'"}';
	}
	
	echo '[';
	echo implode(',', $arr);
	echo ']';
}    
