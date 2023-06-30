<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site https://www.hotel-booking-script.com
* @license http://hotel-booking-script.com/license.php
*/

////////////////////////////////////////////////////////////////////////////////
// PayPal Order Notify
// Last modified: 28.10.2013
////////////////////////////////////////////////////////////////////////////////

// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------

if(Modules::IsModuleInstalled('booking')){
	$mode = ModulesSettings::Get('booking', 'mode');

	if(ModulesSettings::Get('booking', 'is_active') != 'no'){
		
		//----------------------------------------------------------------------
		define('LOG_MODE', false);
		define('LOG_TO_FILE', false);
		define('LOG_ON_SCREEN', false);
		
		define('TEST_MODE', ($mode == 'TEST MODE') ? true : false);
		$log_data = '';
		$msg      = '';
		$nl       = "\n";

		// --- Get PayPal response
		$objPaymentIPN 		= new PaymentIPN($_REQUEST, 'paypal');
		$status 			= $objPaymentIPN->GetPaymentStatus();
		$booking_number		= $objPaymentIPN->GetParameter('custom');
	    $transaction_number = $objPaymentIPN->GetParameter('txn_id');
		$payer_status		= $objPaymentIPN->GetParameter('payer_status');
		$pp_payment_type    = $objPaymentIPN->GetParameter('payment_type');
		$total 				= $objPaymentIPN->GetParameter('mc_gross');
		
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
			$log_data .= '<br />GET<br />'.$nl;
			foreach($_GET as $key=>$value) {
				$log_data .= $key.'='.$value.'<br />'.$nl;        
			}        
		}      
		////////////////////////////////////////////////////////////////////////  

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
					FROM '.TABLE_BOOKINGS.' b
						LEFT OUTER JOIN '.TABLE_CUSTOMERS.' c ON b.customer_id = c.id
					WHERE
						b.booking_number = "'.$booking_number.'"';

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
				$sql = 'SELECT id, booking_number, booking_description, order_price, vat_fee, payment_sum, currency, rooms_amount, customer_id, is_admin_reservation, status
						FROM '.TABLE_BOOKINGS.'
						WHERE booking_number = \''.$booking_number.'\' AND (status = 0 OR status = 2 OR status = 3)';
				$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
				if($result[1] > 0){
                    write_log($sql);
                    
                    if($result[0]['status'] == '2' || $result[0]['status'] == '3'){
                        
                        $sql = 'UPDATE '.TABLE_BOOKINGS.' SET
                                    transaction_number = \''.$transaction_number.'\',
                                    payment_date = \''.date('Y-m-d H:i:s').'\',
                                    payment_type = 2,
                                    payment_method = '.$payment_method.',
                                    additional_payment = additional_payment + \''.$total.'\'
                                WHERE booking_number = \''.$booking_number.'\'';
                        if(database_void_query($sql)){
                            $objReservation = new Reservation();						
                            // send email to user
                            $objReservation->SendOrderEmail($booking_number, 'completed', (int)$result[0]['customer_id']);
                            write_log($sql, _ORDER_PLACED_MSG);    
                            $objReservation->EmptyCart();
                        }else{
                            write_log($sql, database_error());
                        }                        
                        
                    }else{    
                        // check for possible problem or hack attack
                        if($total <= 1 || abs($total - $result[0]['payment_sum']) > 1){
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
    
                        // update customer orders/reservations amount
                        if($result[0]['is_admin_reservation'] == '0'){
                            $sql = 'UPDATE '.TABLE_CUSTOMERS.' SET
                                        orders_count = orders_count + 1,
                                        rooms_count = rooms_count + '.(int)$result[0]['rooms_amount'].'
                                    WHERE id = '.(int)$result[0]['customer_id'];
                            database_void_query($sql);
                            write_log($sql);
                        }
						
                        $msg = Bookings::UpdateRoomsAvailability($booking_number, 'decrease', 'After Payment');
						
                        if(!empty($msg)){
                            $status = '';
                        }else{
                            $sql = 'UPDATE '.TABLE_BOOKINGS.' SET
                                        status = 3,
                                        transaction_number = \''.$transaction_number.'\',
                                        payment_date = \''.date('Y-m-d H:i:s').'\',
                                        payment_type = 2,
                                        payment_method = '.$payment_method.'
                                    WHERE booking_number = \''.$booking_number.'\'';
                            if(database_void_query($sql)){
                                $objReservation = new Reservation();						    
                                // send email to user
                                $objReservation->SendOrderEmail($booking_number, 'completed', (int)$result[0]['customer_id']);
                                write_log($sql, _ORDER_PLACED_MSG);    
                                $objReservation->EmptyCart();
                            }else{
                                write_log($sql, database_error());
                            }                        
                        }
                    }
				}else{
					write_log($sql, 'Error: no records found. '.database_error());
					$status = '';
					$msg = 'Error: no records found';
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
				$sql = 'UPDATE '.TABLE_BOOKINGS.' SET
							status = 0,
							status_description = \''.$msg.'\'
						WHERE booking_number = \''.$booking_number.'\'';
				database_void_query($sql);				
			}else{
				$sql = 'SELECT id, customer_id
						FROM '.TABLE_BOOKINGS.'
						WHERE booking_number = \''.$booking_number.'\' AND status = 0';
				$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
				if($result[1] > 0){
					write_log($sql, _ORDER_ERROR.' #1');
					
					$sql = 'UPDATE '.TABLE_BOOKINGS.' SET
								status = 5,
								status_description = \''.$msg.'\',
								transaction_number = \''.$transaction_number.'\',
								payment_date = \''.date('Y-m-d H:i:s').'\',
								payment_type = 2,
								payment_method = '.$payment_method.'
							WHERE booking_number = \''.$booking_number.'\'';
					database_void_query($sql);
					
					// send email to user
					$objReservation = new Reservation();						
					$objReservation->SendOrderEmail($booking_number, 'payment_error', (int)$result[0]['customer_id']);
					write_log($sql, _ORDER_ERROR.' #2');
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
			redirect_to('index.php?page=booking_return');
		}
	}	
}

function write_log($sql, $msg = ''){
    global $log_data, $nl;
    if(LOG_MODE){
        $log_data .= '<br />'.$nl.$sql;
        if($msg != '') $log_data .= '<br />'.$nl.$msg;
    }    
}

