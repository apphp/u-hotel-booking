<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site https://www.hotel-booking-script.com
* @license http://hotel-booking-script.com/license.php
*/

////////////////////////////////////////////////////////////////////////////////
// Authorize.Net Order Notify
// Last modified: 15.11.2011
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

		// --- Get Authorize.Net response
		$objPaymentIPN 		= new PaymentIPN($_REQUEST, 'authorize.net');
		$status 			= $objPaymentIPN->GetPaymentStatus();
		$booking_number 	= $objPaymentIPN->GetParameter('custom');
	    $transaction_number = $objPaymentIPN->GetParameter('x_trans_id');
		$x_method		    = $objPaymentIPN->GetParameter('x_method');
		$total				= $objPaymentIPN->GetParameter('x_amount');
		$reason_text        = $objPaymentIPN->GetParameter('x_response_reason_text');
		
		// Payment Types   : 0 - POA, 1 - Online Order, 2 - PayPal, 3 - 2CO, 4 - Authorize.Net
		// Payment Methods : 0 - Payment Company Account, 1 - Credit Card, 2 - E-Check
		if(strtolower($x_method) == '1' || strtolower($x_method) == 'cc'){
			$payment_method = '1';
		}else{ 
			$payment_method = '2';
		}
		
		if(TEST_MODE){
			$status = '1';
		}
				
		////////////////////////////////////////////////////////////////////////
		if(LOG_MODE){
			if(LOG_TO_FILE){
				$myFile = 'tmp/logs/payment_authorize_net.log';
				$fh = fopen($myFile, 'a') or die('can\'t open file');				
			}
	  
			$log_data .= $nl.$nl.'=== ['.date('Y-m-d H:i:s').'] ==================='.$nl;
			$log_data .= '<br>---------------<br>'.$nl;
			$log_data .= '<br>POST<br>'.$nl;
			foreach($_POST as $key => $value) {
				$log_data .= $key.'='.$value.'<br>'.$nl;        
			}
			$log_data .= '<br>---------------<br>'.$nl;
			$log_data .= '<br>GET<br>'.$nl;
			foreach($_GET as $key=>$value) {
				$log_data .= $key.'='.$value.'<br>'.$nl;        
			}        
		}      
		////////////////////////////////////////////////////////////////////////  

		switch($status)    
		{
			// 1 order approved
			case '1':
				$sql = 'SELECT id, booking_number, booking_description, order_price, vat_fee, payment_sum, currency, rooms_amount, customer_id, is_admin_reservation 
						FROM '.TABLE_BOOKINGS.'
						WHERE booking_number = \''.$booking_number.'\' AND status = 0';
				$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
				if($result[1] > 0){
					write_log($sql);
					
					// check for possible problem or hack attack
                    if(($result[0]['currency'] == 'USD' && abs($total - $result[0]['total_price']) > 1) || $total <= 1){    
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
					
                    $msg = Bookings::UpdateRoomsAvailability($booking_number, 'declined', 'After Payment');
					
                    if(!empty($msg)){
                        $status = '';
                    }else{
                        $sql = 'UPDATE '.TABLE_BOOKINGS.' SET
                                    status = 3,
                                    transaction_number = \''.$transaction_number.'\',
                                    payment_date = \''.date('Y-m-d H:i:s').'\',
                                    payment_type = 4,
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
				}else{
					write_log($sql, 'Error: no records found. '.database_error());
					$status = '';
					$msg = 'Error: no records found';
				}				
				break;
			
			// 2 order declined 
			case '2':
				$msg = 'Transaction Declined. Reason: '.$reason_text;
				break;

			// 3 order error
			case '3':
				$msg = 'Transaction Error. Reason: '.$reason_text;
				break;

			// 4 order held
			case '4':
				$msg = 'Transaction Held for Review. Reason: '.$reason_text;
				break;
			
			// 0 order is not good
			default:
				$msg = 'Unknown Payment Status - please try again. Reason: '.$reason_text;
				break;
		}

		// handle errors
		if($status != '1'){
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
							payment_type = 4,
							payment_method = '.$payment_method.'
						WHERE booking_number = \''.$booking_number.'\'';
				database_void_query($sql);
				
				// send email to user
				$objReservation = new Reservation();
				$objReservation->SendOrderEmail($booking_number, 'payment_error', (int)$result[0]['customer_id']);
				write_log($sql, _ORDER_ERROR.' #2');
			}
		}

		////////////////////////////////////////////////////////////////////////
		if(LOG_MODE){
			$log_data .= '<br>'.$nl.$msg.'<br>'.$nl;    
			if(LOG_TO_FILE){
				fwrite($fh, strip_tags($log_data));
				fclose($fh);        				
			}
			if(LOG_ON_SCREEN){
				echo $log_data;
			}
		}
		////////////////////////////////////////////////////////////////////////

		//if(TEST_MODE){
			redirect_to('index.php?page=booking_return');
		//}
	}	
}

function write_log($sql, $msg = ''){
    global $log_data, $nl;
    if(LOG_MODE){
        $log_data .= '<br />'.$nl.$sql;
        if($msg != '') $log_data .= '<br />'.$nl.$msg;
    }    
}

