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

    $operation_allowed = true;
    $objToursReservations = new ToursReservations();
    $post_act       = isset($_POST['act']) ? $_POST['act'] : '';
    $act			= isset($_GET['act']) ? prepare_input($_GET['act']) : '';

    if ($act == 'remove') {
        $rid = isset($_GET['rid']) ? (int)$_GET['rid'] : '';
        $objToursReservations->RemoveReservation($rid);
    }
    elseif ($post_act == 'add') {

        $post_act       = isset($_POST['act']) ? $_POST['act'] : '';
        $tour_id        = isset($_POST['tour_id']) ? (int)$_POST['tour_id'] : '0';
        $tour_location  = isset($_POST['tour_location']) ? (int)$_POST['tour_location'] : '';
        $tour_price     = isset($_POST['price']) ? prepare_input($_POST['price']) : 0;
        $tour_from_date = isset($_POST['tour_from_date']) ? prepare_input($_POST['tour_from_date']) : '';
        $tour_to_date   = isset($_POST['tour_to_date']) ? prepare_input($_POST['tour_to_date']) : '';

        $tour_from_date_unix = strtotime($tour_from_date);
        $tour_to_date_unix = strtotime($tour_to_date);

        $current_date      = strtotime(date('Y-m-d'));

        if(empty($tour_from_date)){
            draw_important_message(_WRONG_EMPTY_FROM_DATE);
        }elseif(empty($tour_to_date)) {
            draw_important_message(_WRONG_EMPTY_TO_DATE);
        }elseif(($tour_from_date_unix < $current_date) || ($tour_to_date_unix < $current_date) || ($tour_to_date_unix < $tour_from_date_unix)) {
            draw_important_message(_PAST_TIME_ALERT);
        }

        $params = array(
            'tour_id'   => $tour_id,
            'tour_location' => $tour_location,
            'tour_from_date' => $tour_from_date,
            'tour_to_date' => $tour_to_date,
            'days' => nights_diff($tour_from_date, $tour_to_date)
        );

        // -----------------------------------------------------------------
        $price = Tours::GetTourPrice($tour_id, $params);

        if($tour_price != $price || $price == 0){
            draw_important_message(_WRONG_PARAMETER_PASSED);
            $operation_allowed = false;
        }
    }else{
        $operation_allowed = false;
    }

    // -----------------------------------------------------------------

    if($operation_allowed){
        if($post_act == 'add'){
            $objToursReservations->AddToReservation($tour_id, $tour_location, $tour_from_date, $tour_to_date, $tour_price);
        }
    }

    if($objLogin->IsLoggedInAsAdmin()) draw_title_bar(prepare_breadcrumbs(array(_BOOKING=>'')));

    draw_title_bar(_TOUR_RESERVATION);

    draw_content_start();
    draw_reservation_tour_bar('selected_tours');

    // Test mode alert
    if(Modules::IsModuleInstalled('tours_and_attractions')){
        if(ModulesSettings::Get('tours_and_attractions', 'mode') == 'TEST MODE'){
            draw_message(_TEST_MODE_ALERT_SHORT, true, true);
        }
    }

    if($objToursReservations->error) echo $objToursReservations->error;
    $objToursReservations->ShowReservationInfo();
    draw_content_end();

}else{
    draw_title_bar(_BOOKINGS);
    draw_important_message(_NOT_AUTHORIZED);
}

