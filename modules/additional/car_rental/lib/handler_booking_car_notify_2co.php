<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site http://www.hotel-booking-script.com
* @license http://hotel-booking-script.com/license.php
*/

////////////////////////////////////////////////////////////////////////////////
// 2CO Order Notify
// Last modified: 15.11.2011
////////////////////////////////////////////////////////////////////////////////

// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------

if(Modules::IsModuleInstalled('car_rental') && ModulesSettings::Get('car_rental', 'is_active') == 'yes'){
	$mode = ModulesSettings::Get('car_rental', 'mode');

	//----------------------------------------------------------------------
	define('LOG_MODE', true);
	define('LOG_TO_FILE', true);
	define('LOG_ON_SCREEN', false);
	
	define('TEST_MODE', ($mode == 'TEST MODE') ? true : false);
	$log_data = '';
	$msg      = '';
	$nl       = "\n";

	// --- Get 2CO response
	$objPaymentIPN 		= new PaymentIPN($_REQUEST, '2co');
	$status 			= $objPaymentIPN->GetPaymentStatus();
	$payment_method		= $objPaymentIPN->GetParameter('pay_method');
	$total				= $objPaymentIPN->GetParameter('total');
	$transaction_number = $objPaymentIPN->GetParameter('order_number');
	$reservation_number	= $objPaymentIPN->GetParameter('custom');		

	// Payment Types   : 0 - POA, 1 - Online Order, 2 - PayPal, 3 - 2CO, 4 - Authorize.Net
	// Payment Methods : 0 - Payment Company Account, 1 - Credit Card, 2 - E-Check
	if($payment_method != ''){			
		$payment_method = '1';
	}else{
		$payment_method = '0';
	}
			
	//----------------------------------------------------------------------
	if(TEST_MODE){
		$status = 'approved';
	}

	////////////////////////////////////////////////////////////////////////
	if(LOG_MODE){
		if(LOG_TO_FILE){
			$myFile = 'tmp/logs/payment_2co.log';
			$fh = fopen($myFile, 'a') or die('can\'t open file');				
		}
  
		$log_data .= $nl.$nl.'=== ['.date('Y-m-d H:i:s').'] ==================='.$nl;
		$log_data .= '<br />---------------<br />'.$nl;
		$log_data .= '<br />POST<br />'.$nl;
		foreach($_POST as $key=>$value) {
			$log_data .= $key.'='.$value.'<br />'.$nl;        
		}
		$log_data .= '<br />---------------<br />'.$nl;
		$log_data .= '<br />GET<br />'.$nl;
		foreach($_GET as $key=>$value) {
			$log_data .= $key.'='.$value.'<br />'.$nl;        
		}        
	}      
	////////////////////////////////////////////////////////////////////////  

	switch($status)    
	{
		case 'approved':
			// 2 order completed					
			$sql = 'SELECT id, reservation_number, reservation_description, reservation_price, vat_fee, reservation_total_price, currency, customer_id, is_admin_reservation 
					FROM '.TABLE_CAR_AGENCY_RESERVATIONS.'
					WHERE reservation_number = \''.$reservation_number.'\' AND status = 0';
			$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
			if($result[1] > 0){
				write_log($sql);					

				// check for possible problem or hack attack
				if(($result[0]['currency'] == 'USD' && abs($total - $result[0]['reservation_price']) > 1) || $total <= 1){    
					$ip_address = (isset($_SERVER['HTTP_X_FORWARD_FOR']) && $_SERVER['HTTP_X_FORWARD_FOR']) ? $_SERVER['HTTP_X_FORWARD_FOR'] : $_SERVER['REMOTE_ADDR'];
					$message  = 'From IP: '.$ip_address.'<br />'.$nl;
					$message .= 'Status: '.$status.'<br />'.$nl;
					$message .= 'Possible Attempt of Hack Attack? <br />'.$nl;
					$message .= 'Please check this order: <br />'.$nl;
					$message .= 'Order Price: '.$result[0]['reservation_total_price'].' <br />'.$nl;
					$message .= 'Payment Processing Gross Price: '.$total.' <br />'.$nl;
					write_log($message);
					break;            
				}

				$sql = 'UPDATE '.TABLE_CAR_AGENCY_RESERVATIONS.' SET
							status = 3,
							transaction_number = \''.$transaction_number.'\',
							payment_date = \''.date('Y-m-d H:i:s').'\',
							payment_type = 3,
							payment_method = '.$payment_method.',
							reservation_paid = '.$total.'
						WHERE reservation_number = \''.$reservation_number.'\'';
				if(database_void_query($sql)){
					$objCarReservations = new CarReservations();

					// send email to user
					$objCarReservations->SendOrderEmail($reservation_number, 'completed', (int)$result[0]['customer_id']);
					write_log($sql, _ORDER_PLACED_MSG);

					$objCarReservations->EmptyCart();
				}else{
					write_log($sql, database_error());
				}
			}else{
				write_log($sql, 'Error: no records found. '.database_error());
				$status = '';
				$msg = 'Error: no records found';
			}				
			break;
		default:
			// 0 order is not good
			$msg = 'Unknown Payment Status - please try again.';
			break;
	}

	// handle errors
	if($status != 'approved'){
		$sql = 'SELECT id, customer_id
				FROM '.TABLE_CAR_AGENCY_RESERVATIONS.'
				WHERE reservation_number = \''.$reservation_number.'\' AND status = 0';
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			write_log($sql, _ORDER_ERROR.' #1');
			
			$sql = 'UPDATE '.TABLE_CAR_AGENCY_RESERVATIONS.' SET
						status = 5,
						status_description = \''.$msg.'\',
						transaction_number = \''.$transaction_number.'\',
						payment_date = \''.date('Y-m-d H:i:s').'\',
						payment_type = 3,
						payment_method = '.$payment_method.'
					WHERE reservation_number = \''.$reservation_number.'\'';
			database_void_query($sql);
			
			// send email to user
			$objCarReservations = new CarReservations();
			$objCarReservations->SendOrderEmail($reservation_number, 'payment_error', (int)$result[0]['customer_id']);
			write_log($sql, _ORDER_ERROR.' #2');
		}
	}

	////////////////////////////////////////////////////////////////////////
	if(LOG_MODE){
		$log_data .= '<br />'.$nl.$msg.'<br />'.$nl;    
		if(LOG_TO_FILE){
			fwrite($fh, strip_tags($log_data));
			fclose($fh);        				
		}
		if(LOG_ON_SCREEN){
			echo $log_data;
		}
	}
	////////////////////////////////////////////////////////////////////////

	if(TEST_MODE){
		redirect_to('index.php?page=booking_car_return');
	}
}	

function write_log($sql, $msg = ''){
    global $log_data, $nl;
    if(LOG_MODE){
        $log_data .= '<br />'.$nl.$sql;
        if($msg != '') $log_data .= '<br />'.$nl.$msg;
    }    
}

