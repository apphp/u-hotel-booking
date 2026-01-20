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

if(Modules::IsModuleInstalled('tours_and_attractions') && ModulesSettings::Get('tours_and_attractions', 'is_active') == 'yes'){

    $m   = isset($_REQUEST['m']) ? prepare_input($_REQUEST['m']) : '';
    $act = isset($_POST['act']) ? prepare_input($_POST['act']) : '';
    $discount_coupon = isset($_POST['discount_coupon']) ? prepare_input($_POST['discount_coupon']) : '';
    $submition_type = isset($_POST['submition_type']) ? prepare_input($_POST['submition_type']) : '';
    $payment_type = isset($_POST['payment_type']) ? prepare_input($_POST['payment_type']) : '';
    $msg = '';

    draw_title_bar(_TOUR_RESERVATION);

    draw_content_start();
    draw_reservation_tour_bar('reservation');

    // test mode alert
	if(ModulesSettings::Get('tours_and_attractions', 'mode') == 'TEST MODE'){
        $msg = draw_message(_TEST_MODE_ALERT_SHORT, false, true);
    }

    if($m == '1'){
        if(ModulesSettings::Get('tours_and_attractions', 'allow_reservation_without_account') == 'no'){
            $msg = draw_success_message(_ACCOUNT_WAS_CREATED, false);
        }
    }elseif($m == '2'){
        if(ModulesSettings::Get('tours_and_attractions', 'allow_reservation_without_account') == 'no'){
            $msg = draw_success_message(_ACCOUNT_WAS_UPDATED, false);
        }else{
            $msg = draw_success_message(_BILLING_DETAILS_UPDATED, false);
        }
    }

    if($msg != '') echo $msg;

    $objToursReservations->ShowCheckoutInfo();
    draw_content_end();

}else{
    draw_title_bar(_BOOKINGS);
    draw_important_message(_NOT_AUTHORIZED);
}	

