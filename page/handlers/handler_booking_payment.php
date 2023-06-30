<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site https://www.hotel-booking-script.com
* @license http://hotel-booking-script.com/license.php
*/

// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------

$objReservation = new Reservation();

//--------------------------------------------------------------------------
// *** redirect if reservation cart is empty or not correct reserved number of rooms
if($objReservation->IsCartEmpty() || !$objReservation->IsCorrectReservedNumberRooms()){
	redirect_to('index.php?page=booking', '', '<p>if your browser doesn\'t support redirection please click <a href="index.php?page=booking">here</a>.</p>');
}

if(Modules::IsModuleInstalled('booking')){
	if(ModulesSettings::Get('booking', 'is_active') == 'global' ||
	   ModulesSettings::Get('booking', 'is_active') == 'front-end' ||
	  (ModulesSettings::Get('booking', 'is_active') == 'back-end' && $objLogin->IsLoggedInAsAdmin())	
	){
		
		$task			 	 = isset($_POST['task']) ? prepare_input($_POST['task']) : '';
		$selected_user       = isset($_POST['selected_user']) ? prepare_input($_POST['selected_user']) : '';
		$payment_type  	     = isset($_POST['payment_type']) ? prepare_input($_POST['payment_type']) : '';
		$additional_info 	 = isset($_POST['additional_info']) ? substr_by_word(prepare_input(decode_text($_POST['additional_info'])), 1024) : '';
		$extras = array();
		foreach($_POST as $key => $val){
			if(preg_match('/extras_/i', $key)){
				if($val > 0) $extras[str_replace('extras_', '', $key)] = $val;
			}		
		}
		
		$pre_payment_type    = isset($_POST['pre_payment_type']) ? prepare_input($_POST['pre_payment_type']) : 'full price';
		$pre_payment_value   = isset($_POST['pre_payment_value']) ? prepare_input($_POST['pre_payment_value']) : '0';
	
		$cc_params = array();
		$cc_params['cc_type'] 	       = isset($_POST['cc_type']) ? prepare_input($_POST['cc_type']) : '';
		$cc_params['cc_holder_name']   = isset($_POST['cc_holder_name']) ? prepare_input($_POST['cc_holder_name']) : '';
		$cc_params['cc_number'] 	   = isset($_POST['cc_number']) ? prepare_input($_POST['cc_number']) : '';
		$cc_params['cc_expires_month'] = isset($_POST['cc_expires_month']) ? prepare_input($_POST['cc_expires_month']) : '';
		$cc_params['cc_expires_year']  = isset($_POST['cc_expires_year']) ? prepare_input($_POST['cc_expires_year']) : '';
		$cc_params['cc_cvv_code']      = isset($_POST['cc_cvv_code']) ? prepare_input($_POST['cc_cvv_code']) : '';
		
		$online_credit_card_required = ModulesSettings::Get('booking', 'online_credit_card_required');
		$booking_mode = ModulesSettings::Get('booking', 'mode');
		$booking_payment_output = '';
		
		if(empty($task)){
			redirect_to('index.php?page=booking_checkout');
		}
		
		if($task == 'do_booking'){
			$result = $objReservation->DoReservation($payment_type, $additional_info, $extras, $pre_payment_type, $pre_payment_value);
			$booking_payment_output .= $objReservation->error;
			if($result == true){
				$booking_payment_output .= $objReservation->DrawReservation($payment_type, $additional_info, $extras, $pre_payment_type, $pre_payment_value, false);
			}
		}elseif($task == 'place_order'){
			/**
			 * 1. Pay On Arrival
			 * 2. Online Order
			 * 3. Bank Transfer
			 * 4. Pay with Balance
			 */
			if($objLogin->IsLoggedInAsAdmin()){ 
				// if admin makes this reservation
				if($payment_type == 'online' && $cc_params['cc_number'] != ''){
					$result = check_credit_card($cc_params);
				}else{
					$result = '';
				}
				$place_booking = ($result != '') ? false : true;
			}elseif($payment_type == 'online'){ 
				$result = check_credit_card($cc_params);
				$place_booking = ($online_credit_card_required == 'yes' && $result != '') ? false : true;
			}else{
				$place_booking = true;
			}
			
			if($place_booking){
				$objReservation->PlaceBooking($additional_info, $cc_params, $payment_type);	
				$booking_payment_output .= $objReservation->message.'<br />';					
			}else{
				$booking_payment_output .= draw_important_message($result, false);
				$booking_payment_output .= $objReservation->DrawReservation($payment_type, $additional_info, $extras, $pre_payment_type, $pre_payment_value, false);
			}
		}else{
			$booking_payment_output .= draw_important_message(_WRONG_PARAMETER_PASSED, false);
		}
	}
}
