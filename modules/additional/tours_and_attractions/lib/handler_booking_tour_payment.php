<?php
/**
 * @project uHotelBooking
 * @copyright (c) 2019 ApPHP
 * @author ApPHP <info@apphp.com>
 * @site http://www.hotel-booking-script.com
 * @license http://hotel-booking-script.com/license.php
 */

// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------

$objToursReservations = new ToursReservations();
//--------------------------------------------------------------------------
// *** redirect if reservation cart is empty
if($objToursReservations->IsCartEmpty()){
    redirect_to('index.php?page=book_now_tour', '', '<p>if your browser doesn\'t support redirection please click <a href="index.php?page=book_now_tour">here</a>.</p>');
}

if(Modules::IsModuleInstalled('tours_and_attractions') && ModulesSettings::Get('tours_and_attractions', 'is_active') == 'yes'){
    $task			 	 = isset($_POST['task']) ? prepare_input($_POST['task']) : '';
    $selected_user       = isset($_POST['selected_user']) ? prepare_input($_POST['selected_user']) : '';
    $payment_type  	     = isset($_POST['payment_type']) ? prepare_input($_POST['payment_type']) : '';
    $additional_info 	 = isset($_POST['additional_info']) ? substr_by_word(prepare_input(decode_text($_POST['additional_info'])), 1024) : '';

    $pre_payment_type    = isset($_POST['pre_payment_type']) ? prepare_input($_POST['pre_payment_type']) : 'full price';
    $pre_payment_value   = isset($_POST['pre_payment_value']) ? prepare_input($_POST['pre_payment_value']) : '0';

    $cc_params = array();
    $cc_params['cc_type'] 	       = isset($_POST['cc_type']) ? prepare_input($_POST['cc_type']) : '';
    $cc_params['cc_holder_name']   = isset($_POST['cc_holder_name']) ? prepare_input($_POST['cc_holder_name']) : '';
    $cc_params['cc_number'] 	   = isset($_POST['cc_number']) ? prepare_input($_POST['cc_number']) : '';
    $cc_params['cc_expires_month'] = isset($_POST['cc_expires_month']) ? prepare_input($_POST['cc_expires_month']) : '';
    $cc_params['cc_expires_year']  = isset($_POST['cc_expires_year']) ? prepare_input($_POST['cc_expires_year']) : '';
    $cc_params['cc_cvv_code']      = isset($_POST['cc_cvv_code']) ? prepare_input($_POST['cc_cvv_code']) : '';

    $online_credit_card_required = ModulesSettings::Get('tours_and_attractions', 'online_credit_card_required');
    $booking_mode = ModulesSettings::Get('tours_and_attractions', 'mode');
    $booking_payment_output = '';

    if(empty($task)){
        redirect_to('index.php?page=booking_tour_checkout');
    }

    if($task == 'do_booking'){
        $result = $objToursReservations->DoReservation($payment_type, $additional_info, $pre_payment_type, $pre_payment_value);
        $booking_payment_output .= $objToursReservations->error;
        if($result == true){
            $booking_payment_output .= $objToursReservations->DrawReservation($payment_type, $additional_info, $pre_payment_type, $pre_payment_value, false);
        }
    }elseif($task == 'place_order'){
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
            $objToursReservations->PlaceBooking($additional_info, $cc_params, $payment_type);
            $booking_payment_output .= $objToursReservations->message.'<br />';
        }else{
            $booking_payment_output .= draw_important_message($result, false);
            $booking_payment_output .= $objToursReservations->DrawReservation($payment_type, $additional_info, $pre_payment_type, $pre_payment_value, false);
        }
    }else{
        $booking_payment_output .= draw_important_message(_WRONG_PARAMETER_PASSED, false);
    }
}
