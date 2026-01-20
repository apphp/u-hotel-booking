<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site http://www.hotel-booking-script.com
* @license http://hotel-booking-script.com/license.php
*/

////////////////////////////////////////////////////////////////////////////////
// PayPal Order Notify
// Last modified: 28.10.2013
////////////////////////////////////////////////////////////////////////////////

// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------

if(Modules::IsModuleInstalled('car_rental') && ModulesSettings::Get('car_rental', 'is_active') == 'yes'){
	$mode = ModulesSettings::Get('car_rental', 'mode');
	
	//----------------------------------------------------------------------
	define('LOG_MODE', true);
	define('LOG_TO_FILE', true);
	define('LOG_ON_SCREEN', true);
	
	define('TEST_MODE', ($mode == 'TEST MODE') ? true : false);
	$log_data = '';
	$msg      = '';
	$nl       = "\n";

	// --- Get PayPal response
	$objPaymentIPN 		= new PaymentIPN($_REQUEST, 'paypal');
	$status 			= $objPaymentIPN->GetPaymentStatus();
	$all_values			= $objPaymentIPN->GetParameter('custom');
	$transaction_number = $objPaymentIPN->GetParameter('txn_id');
	$payer_status		= $objPaymentIPN->GetParameter('payer_status');
	$pp_payment_type    = $objPaymentIPN->GetParameter('payment_type');
	$total 				= $objPaymentIPN->GetParameter('mc_gross');

	$values_arr = explode('|', base64_decode($all_values));

	$customer_id = !empty($values_arr[0]) ? $values_arr[0] : '0';
	$car_id = !empty($values_arr[1]) ? $values_arr[1] : '0';
	$picking_up = !empty($values_arr[2]) ? $values_arr[2] : '0';
	$dropping_off = !empty($values_arr[3]) ? $values_arr[3] : '0';
	$pick_up_date_time = isset($values_arr[4]) ? $values_arr[4] : '';
	$drop_off_date_time = isset($values_arr[5]) ? $values_arr[5] : '';
	//$price = !empty($values_arr[6]) ? $values_arr[6] : 0;


	$pick_up_unix_time = strtotime($pick_up_date_time);
	$drop_off_unix_time = strtotime($drop_off_date_time);

	$diff = $drop_off_unix_time - $pick_up_unix_time;
	$hours_to_seconds = $diff % (24 * 60 * 60);
	$days = ($diff - $hours_to_seconds) / (24 * 60 * 60);
	$hours = $hours_to_seconds / (60 * 60);

	$pick_up_date = date('Y-m-d', $pick_up_unix_time);
	$pick_up_time = date('H:i:s', $pick_up_unix_time);
	$drop_off_date = date('Y-m-d', $drop_off_unix_time);
	$drop_off_time = date('H:i:s', $drop_off_unix_time);

	
	// Payment Types   : 0 - POA, 1 - Online Order, 2 - PayPal, 3 - 2CO, 4 - Authorize.Net
	// Payment Methods : 0 - Payment Company Account, 1 - Credit Card, 2 - E-Check
	if($status == 'Completed'){
		if($payer_status == 'verified'){
			$payment_method = '0';
		}else{
			$payment_method = '1';
		}			
	}else{
		$payment_method = ($pp_payment_type == 'echeck') ? '2' : '0'; 
	}
			
	if(TEST_MODE){
		$status = 'Completed';
	}

	////////////////////////////////////////////////////////////////////////
	if(LOG_MODE){
		if(LOG_TO_FILE){
			$myFile = 'tmp/logs/payment_paypal.log';
			$fh = fopen($myFile, 'a') or die('can\'t open file');				
		}
  
		$log_data .= $nl.$nl.'=== ['.date('Y-m-d H:i:s').'] ==================='.$nl;
		$log_data .= '<br />---------------<br />'.$nl;
		$log_data .= '<br />POST<br />'.$nl;
		foreach($_POST as $key=>$value) {
			$log_data .= $key.'='.$value.'<br />'.$nl;        
		}
		$log_data .= '<br />---------------<br />'.$nl;
		$log_data .= '<br />VERIFIED<br />'.$nl;

		$log_data .= 'values        = '.base64_decode($all_values).$nl;
		$log_data .= 'customer_id    = '.$customer_id.$nl;
		$log_data .= 'car_id         = '.$car_id.$nl;
		$log_data .= 'picking_up     = '.$picking_up.$nl;
		$log_data .= 'dropping_off   = '.$dropping_off.$nl;
		$log_data .= 'pick_up_date   = '.$pick_up_date.$nl;
		$log_data .= 'drop_off_date  = '.$drop_off_date.$nl;
		$log_data .= 'price          = '.$price.$nl;
		$log_data .= 'payment_method = '.$payment_method.$nl;
		$log_data .= 'status         = '.$status.$nl;
		
		$log_data .= '<br />---------------<br />'.$nl;
		$log_data .= '<br />GET<br />'.$nl;
		foreach($_GET as $key=>$value) {
			$log_data .= $key.'='.$value.'<br />'.$nl;        
		}        
	}      
	////////////////////////////////////////////////////////////////////////  

	$car_where_clause = 'av.id = '.(int)$car_id.' AND ';

	$sql = 'SELECT
			av.id, 
			av.agency_id, 
			av.make, 
			av.model, 
			av.vehicle_type_id,
			av.price_per_day, 
			av.price_per_hour, 
			av.default_distance, 
			av.distance_extra_price, 
			av.transmission,
			vt.image, 
			vt.image_thumb, 
			vt.passengers, 
			vt.luggages, 
			vt.doors
		FROM '.TABLE_CAR_AGENCY_VEHICLES.' av
			INNER JOIN '.TABLE_CAR_AGENCY_VEHICLE_TYPES.' vt ON av.vehicle_type_id = vt.id
		WHERE
			'.$car_where_clause.'
			av.is_active = 1 AND
			vt.is_active = 1
		LIMIT 1';
	$cars = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
	if(is_array($cars) && $cars[1] > 0){
		$car = $cars[0];
		$agency_id = $car['agency_id'];
		$type_id = $car['vehicle_type_id'];

		$price = $car['price_per_day'] * $days + $car['price_per_hour'] * $hours;

		switch($status)    
		{
			// 1 order pending
			case 'Pending':
				$pending_reason = $objPaymentIPN->GetParameter('pending_reason');
				$msg = 'Pending Payment - '.$pending_reason;

				$sql = 'SELECT 
							c.first_name,
							c.last_name,
							c.user_name as customer_name,
							c.preferred_language,
							c.email
					FROM '.TABLE_CAR_AGENCY_RESERVATIONS.' cr
						LEFT OUTER JOIN '.TABLE_CUSTOMERS.' c ON b.customer_id = c.id
					WHERE
						b.reservation_number = "'.$transaction_number.'"';

				$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
				if($result[1] > 0){

					$recipient = $result[0]['email'];
					$sender = $objSettings->GetParameter('admin_email');			
					$email_text = '<b>Dear Customer!</b><br />
					Thank you for purchasing from our site!
					Your order has been placed in our system.
					Current status: PENDING.<br />
					  
					Payments from PayPal using an eCheck (electronic funds transfer from your bank account) will be
					credited to your account when your bank clears the transaction. Your PayPal account will show
					an estimated clearing date for the transaction. Once the transaction is cleared, the booked
					rooms will be credited to your account in a few minutes.<br /><br />
					
					If you don\'t see any changes on your account during 72 hours,
					please contact us to: '.$sender;
					
					////////////////////////////////////////////////////////////
					send_email_wo_template(
						$recipient,
						$sender,
						'Order placed (eCheck payment in progress - '.$objSiteDescription->GetParameter('header_text').')',
						$email_text
					);
					////////////////////////////////////////////////////////////
				}

				break;
			case 'Completed':
				// 2 order completed					
				$sql = 'SELECT id, reservation_number, reservation_description, reservation_price, vat_fee, reservation_total_price, currency, customer_id, status
						FROM '.TABLE_CAR_AGENCY_RESERVATIONS.'
						WHERE reservation_number = \''.$transaction_number.'\' AND (status = 0 OR status = 2 OR status = 3)';
				$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
				if($result[1] > 0){
					write_log($sql);
					
					if($result[0]['status'] == '2' || $result[0]['status'] == '3'){
						
						$sql = 'UPDATE '.TABLE_CAR_AGENCY_RESERVATIONS.' SET
									transaction_number = \''.$transaction_number.'\',
									payment_date = \''.date('Y-m-d H:i:s').'\',
									payment_type = 2,
									payment_method = '.$payment_method.',
									reservation_price = \''.$price.'\',
									reservation_total_price = \''.$total.'\',
									reservation_paid = \''.$total.'\',
									car_agency_id = '.$agency_id.'
									car_vehicle_type_id = '.$type_id.'
									car_vehicle_id = '.$car_id.',
									location_from_id = '.$picking_up.',
									location_to_id = '.$dropping_off.',
									date_from = \''.$pick_up_date.'\',
									time_from = \''.$pick_up_time.'\',
									date_to = \''.$drop_off_date.'\',
									time_to = \''.$drop_off_time.'\'
								WHERE reservation_number = \''.$transaction_number.'\'';
						if(database_void_query($sql)){
							$objCarReservations = new CarReservations();						
							// send email to user
							$objCarReservations->SendOrderEmail($transaction_number, 'completed', (int)$result[0]['customer_id']);
							write_log($sql, _ORDER_PLACED_MSG);    
							$objCarReservations->EmptyCart();
						}else{
							write_log($sql, database_error());
						}                        
						
					}else{    
						// check for possible problem or hack attack
						if($total <= 1){
							$ip_address = (isset($_SERVER['HTTP_X_FORWARD_FOR']) && $_SERVER['HTTP_X_FORWARD_FOR']) ? $_SERVER['HTTP_X_FORWARD_FOR'] : $_SERVER['REMOTE_ADDR'];
							$message  = 'From IP: '.$ip_address.'<br />'.$nl;
							$message .= 'Status: '.$status.'<br />'.$nl;
							$message .= 'Possible Attempt of Hack Attack? <br />'.$nl;
							$message .= 'Please check this order: <br />'.$nl;
							$message .= 'Order Price: '.$result[0]['payment_sum'].' <br />'.$nl;
							$message .= 'Payment Processing Gross Price: '.$total.' <br />'.$nl;
							write_log($message);
							break;            
						}
	
						
						$sql = 'UPDATE '.TABLE_CAR_AGENCY_RESERVATIONS.' SET
									status = 3,
									transaction_number = \''.$transaction_number.'\',
									payment_date = \''.date('Y-m-d H:i:s').'\',
									payment_type = 2,
									payment_method = '.$payment_method.'
									reservation_price = \''.$price.'\',
									reservation_total_price = \''.$total.'\',
									reservation_paid = \''.$total.'\',
									car_agency_id = '.$agency_id.'
									car_vehicle_type_id = '.$type_id.'
									car_vehicle_id = '.$car_id.',
									location_from_id = '.$picking_up.',
									location_to_id = '.$dropping_off.',
									date_from = \''.$pick_up_date.'\',
									time_from = \''.$pick_up_time.'\',
									date_to = \''.$drop_off_date.'\',
									time_to = \''.$drop_off_time.'\'
								WHERE reservation_number = \''.$transaction_number.'\'';
						if(database_void_query($sql)){
							$objCarReservations = new CarReservations();						    
							// send email to user
							$objCarReservations->SendOrderEmail($transaction_number, 'completed', (int)$result[0]['customer_id']);
							write_log($sql, _ORDER_PLACED_MSG);    
							$objCarReservations->EmptyCart();
						}else{
							write_log($sql, database_error());
						}                        
					}
				}else{
					$sql = 'INSERT INTO '.TABLE_CAR_AGENCY_RESERVATIONS.' (transaction_number, payment_date, payment_type, payment_method, reservation_price, reservation_total_price, car_agency_id, car_vehicle_type_id, car_vehicle_id, location_from_id, location_to_id, date_from, time_from, date_to, time_to) VALUES
						(\''.$transaction_number.'\', \''.date('Y-m-d H:i:s').'\', 2, '.$payment_method.', \''.$price.'\', \''.$total.'\', '.$agency_id.', '.$type_id.', '.$car_id.', '.$picking_up.', '.$dropping_off.', \''.$pick_up_date.'\', \''.$pick_up_time.'\', \''.$drop_off_date.'\', \''.$drop_off_time.'\');';
					if(database_void_query($sql)){
						$objCarReservations = new CarReservations();						
						// send email to user
						$objCarReservations->SendOrderEmail($transaction_number, 'completed', (int)$result[0]['customer_id']);
						write_log($sql, _ORDER_PLACED_MSG);    
						$objCarReservations->EmptyCart();
					}else{
						write_log($sql, database_error());
					}                        
				}				
				break;
			case 'Updated':
				// 3 updated already
				$msg = 'Thank you for your order!<br /><br />';
				break;
			case 'Failed':
				// 4 this will only happen in case of echeck.
				$msg = 'Payment Failed';
				break;
			case 'Denied':
				// 5 denied payment by us
				$msg = 'Payment Denied';
				break;
			case 'Refunded':
				// 6 payment refunded by us
				$msg = 'Payment Refunded';			
				break;
			case 'Canceled':
				/* 7 reversal cancelled
				 mark the payment as dispute cancelled */
				$msg = 'Cancelled reversal';
				break;	
			default:
				// 0 order is not good
				$msg = 'Unknown Payment Status - please try again.';
				// . $objPaymentIPN->GetPaymentStatus();
				break;
		}

		if($status != 'Completed'){
			if($status == 'Pending'){
				$sql = 'UPDATE '.TABLE_CAR_AGENCY_RESERVATIONS.' SET
							status = 0,
							status_description = \''.$msg.'\'
						WHERE reservation_number = \''.$transaction_number.'\'';
				database_void_query($sql);				
			}else{
				$sql = 'SELECT id, customer_id
						FROM '.TABLE_CAR_AGENCY_RESERVATIONS.'
						WHERE reservation_number = \''.$transaction_number.'\' AND status = 0';
				$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
				if($result[1] > 0){
					write_log($sql, _ORDER_ERROR.' #1');
					
					$sql = 'UPDATE '.TABLE_CAR_AGENCY_RESERVATIONS.' SET
								status = 5,
								status_description = \''.$msg.'\',
								transaction_number = \''.$transaction_number.'\',
								payment_date = \''.date('Y-m-d H:i:s').'\',
								payment_type = 2,
								payment_method = '.$payment_method.'
								reservation_price = \''.$price.'\',
								reservation_total_price = \''.$total.'\',
								car_agency_id = '.$agency_id.'
								car_vehicle_type_id = '.$type_id.'
								car_vehicle_id = '.$car_id.',
								location_from_id = '.$picking_up.',
								location_to_id = '.$dropping_off.',
								date_from = \''.$pick_up_date.'\',
								time_from = \''.$pick_up_time.'\',
								date_to = \''.$drop_off_date.'\',
								time_to = \''.$drop_off_time.'\'
							WHERE reservation_number = \''.$transaction_number.'\'';
					database_void_query($sql);
					
					// send email to user
					$objCarReservations = new CarReservations();						
					$objCarReservations->SendOrderEmail($transaction_number, 'payment_error', (int)$result[0]['customer_id']);
					write_log($sql, _ORDER_ERROR.' #2');
				}				
			}			
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
		redirect_to('index.php?page=cars_return');
	}
}

function write_log($sql, $msg = ''){
    global $log_data, $nl;
    if(LOG_MODE){
        $log_data .= '<br />'.$nl.$sql;
        if($msg != '') $log_data .= '<br />'.$nl.$msg;
    }    
}

