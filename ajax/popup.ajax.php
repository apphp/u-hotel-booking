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

$param = isset($_POST['param']) ? prepare_input($_POST['param']) : '';
$id = isset($_POST['id']) ? prepare_input($_POST['id']) : '';
$check_key = isset($_POST['check_key']) ? prepare_input($_POST['check_key']) : '';
$customer_type = isset($_POST['customer_type']) && $_POST['customer_type'] == 1 ? 'agencies' : '';
$token = isset($_POST['token']) ? prepare_input($_POST['token']) : '';
$session_token = isset($_SESSION[INSTALLATION_KEY]['token']) ? prepare_input($_SESSION[INSTALLATION_KEY]['token']) : '';
$arr = array();
$content = '';

if($objLogin->IsLoggedInAs('owner','mainadmin','hotelowner','agencyowner') && $check_key == 'apphphs' && ($token == $session_token) && !empty($param) && !empty($id)){
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');   // Date in the past
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
	header('Pragma: no-cache'); // HTTP/1.0
	header('Content-Type: application/json');
	
	$arr[] = '"status": "1"';

	if($param == 'customer'){

		// Check hotel owner has permissions to access this customer info
		if($objLogin->IsLoggedInAs('hotelowner')){
			$objCustomer = new Customers($customer_type);
			if(!$objCustomer->AssignedToHotelOwner($id, $objLogin->GetLoggedID())){
				$content = draw_important_message(utf8_encode(_WRONG_PARAMETER_PASSED), false);
			}
		}

		if ( empty($content) ) {
			ob_start();
			$objCustomer = new Customers($customer_type);
			$objCustomer->DrawDetailsMode((int)$id, array('back'=>false));
			// save the contents of output buffer to the string
			$result = ob_get_contents();
			ob_end_clean();
			$content = $result;
		}

		$arr[] = '"content": '.json_encode(utf8_encode($content));
	}
	else if($param == 'admin'){

		$objAdmin = new Accounts((int)$id);
		
		$result = '<fieldset style="padding:5px;margin-left:5px;margin-right:10px;">
		<legend>'._PERSONAL_DETAILS.'</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="2" class="mgrid_table">
		<tbody>
		<tr>
			<td width="27%" align="left">'._FIRST_NAME.':</td>
			<td style="text-align:left;padding-left:6px;"><label class="mgrid_label mgrid_wrapword" title="">'.$objAdmin->GetParameter('first_name').'</label></td>
		</tr>
		<tr>
			<td width="27%" align="left">'._LAST_NAME.':</td>
			<td style="text-align:left;padding-left:6px;"><label class="mgrid_label mgrid_wrapword" title="">'.$objAdmin->GetParameter('last_name').'</label></td>
		</tr>
		<tr>
			<td width="27%" align="left">'._ACCOUNT_TYPE.':</td>
			<td style="text-align:left;padding-left:6px;"><label class="mgrid_label mgrid_wrapword" title="">'.$objAdmin->GetParameter('account_type').'</label></td>
		</tr>
		</table>
		</fieldset>';
		
		$arr[] = '"content": '.json_encode(utf8_encode($result));		
	}
	
	echo '{';
	echo implode(',', $arr);
	echo '}';
}else{
	// wrong parameters passed!
	$arr[] = '"status": "0"';
	echo '{';
	echo implode(',', $arr);
	echo '}';
}    
