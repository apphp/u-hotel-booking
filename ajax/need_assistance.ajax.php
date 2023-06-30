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

define('BASE_DIR', '../');

require_once(BASE_DIR.'include/base.inc.php');
require_once(BASE_DIR.'include/connection.php');

$email_hotels   = isset($_POST['email_hotels']) ? prepare_input($_POST['email_hotels']) : '';
$name           = isset($_POST['name']) ? prepare_input($_POST['name']) : '';
$message        = isset($_POST['message']) ? prepare_input($_POST['message']) : '';
$check_key 	    = isset($_POST['check_key']) ? prepare_input($_POST['check_key']) : '';
$token 		    = isset($_POST['token']) ? prepare_input($_POST['token']) : '';
$session_token  = isset($_SESSION[INSTALLATION_KEY]['token']) ? prepare_input($_SESSION[INSTALLATION_KEY]['token']) : '';
$email_customer = $objLogin->GetLoggedEmail();
$arr 		    = array();
//$property_type_id 	= isset($_POST['property_type_id']) ? (int)$_POST['property_type_id'] : '';
//$lang				= isset($_POST['lang']) ? prepare_input($_POST['lang']) : '';

if($check_key == 'apphphs' && ($token == $session_token) && $objLogin->IsLoggedInAsCustomer()){
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');   // Date in the past
    header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
    header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
    header('Pragma: no-cache'); // HTTP/1.0
    header('Content-Type: application/json');

    $rid 		= $objLogin->GetLoggedID();
    $sql_select = 'SELECT '.TABLE_CUSTOMERS.'.*
				  FROM '.TABLE_CUSTOMERS.'
				  WHERE '.TABLE_CUSTOMERS.'.id = '.(int)$rid;
    $customer 	= database_query($sql_select, DATA_ONLY, FIRST_ROW_ONLY);
    $current_day = date('Y-m-d');
    $max_email_need_assistance = ModulesSettings::Get('rooms', 'max_email_need_assistance');
    if(!empty($customer) && $customer['need_assistance_count'] >= $max_email_need_assistance && $customer['last_need_assistance'] == $current_day){
        $error = _MAX_EMAIL_NEED_ASSISTANCE_ERROR;
        $arr[] = '{"status": "maxCountEmail"}';
        $arr[] = '{"error": "'.$error.'"}';
    }elseif(empty($email_hotels) || empty($email_customer)){
        $error = _TRY_LATER;
        $arr[] = '{"status": "0"}';
        $arr[] = '{"error": "'.$error.'"}';
    }elseif(empty($name)){
        $error = _NAME_EMPTY_ALERT;
        $arr[] = '{"status": "emptyName"}';
        $arr[] = '{"error": "'.$error.'"}';
    }elseif(empty($message)){
        $error = _MESSAGE_EMPTY_ALERT;
        $arr[] = '{"status": "emptyMessage"}';
        $arr[] = '{"error": "'.$error.'"}';
    }else{
        $body = _NAME.': '.str_replace('\\', '', $name).'<br />'._MESSAGE.': '.str_replace('\\', '', $message);
        $send_email = Hotels::SendEmailNeedAssistance($email_hotels, $email_customer, $body);
        if($send_email){
            $arr[] = '{"status": "1"}';   
        }else{
            $error = _EMAIL_SEND_ERROR;
            $arr[] = '{"status": "0"}';
            $arr[] = '{"error": "'.$error.'"}';
        }
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

