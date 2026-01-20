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

if(Modules::IsModuleInstalled('car_rental') && ModulesSettings::Get('car_rental', 'is_active') == 'yes'){
	$operation_allowed = true;
	$objCarReservations = new CarReservations();
	$post_act       = isset($_POST['act']) ? $_POST['act'] : '';
	$act			= isset($_GET['act']) ? prepare_input($_GET['act']) : '';
	if($act == 'remove'){
        $rid = isset($_GET['rid']) ? (int)$_GET['rid'] : '';

		$objCarReservations->RemoveReservation($rid);
	}elseif($post_act == 'add'){
		$post_act      = isset($_POST['act']) ? $_POST['act'] : '';
		$car_id        = isset($_POST['car_id']) ? (int)$_POST['car_id'] : '0';
		$picking_up    = isset($_POST['picking_up_id']) ? (int)$_POST['picking_up_id'] : '';
		$dropping_off  = isset($_POST['dropping_off_id']) ? (int)$_POST['dropping_off_id'] : '';
		$pick_up_date  = isset($_POST['pick_up_date_time']) ? prepare_input($_POST['pick_up_date_time']) : '';
		$drop_off_date = isset($_POST['drop_off_date_time']) ? prepare_input($_POST['drop_off_date_time']) : '';
		$price_post    = isset($_POST['price']) ? prepare_input($_POST['price']) : 0;

		$pick_up_date_unix = strtotime($pick_up_date);
		$drop_off_date_unix = strtotime($drop_off_date);
		$diff = $drop_off_date_unix - $pick_up_date_unix;
		$hours_to_second = $diff % (24 * 60 * 60);
		$days = ($diff - $hours_to_second) / (24 * 60 * 60);
		$hours = $hours_to_second / (60 * 60);

		// 1800 sec == 30 min
		// $diff % 1800 and $pick_up_date_unix % 1800 - check on the multiplicity by half an hour
		if($diff < 1800 || $diff % 1800 || $pick_up_date_unix % 1800){
			draw_important_message(_WRONG_PARAMETER_PASSED);
			$operation_allowed = false;
		}

		$params = array(
			'picking_up' => $picking_up,
			'dropping_off' => $dropping_off,
			'pick_up_date' => $pick_up_date,
			'drop_off_date' => $drop_off_date,
			'days' => $days,
			'hours' => $hours,
		);

		// -----------------------------------------------------------------

		$price = Vehicles::GetCarPrice($car_id, $params);
		if($price_post != $price || $price == 0){
			draw_important_message(_WRONG_PARAMETER_PASSED);
			$operation_allowed = false;
		}		
	}else{
		$operation_allowed = false;
	}

	// -----------------------------------------------------------------    
    
    if($operation_allowed){
        if($post_act == 'add'){
            $objCarReservations->AddToReservation($car_id, $picking_up, $dropping_off, $pick_up_date, $drop_off_date, $price);
        }
    }        
    
    if($objLogin->IsLoggedInAsAdmin()) draw_title_bar(prepare_breadcrumbs(array(_BOOKING=>'')));
    
    //draw_title_bar(_BOOKING);
    
    draw_content_start();
    draw_reservation_car_bar('selected_cars');

	// TODO: replace by cars 
    // test mode alert
    if(Modules::IsModuleInstalled('car_rental')){
        if(ModulesSettings::Get('car_rental', 'mode') == 'TEST MODE'){
            draw_message(_TEST_MODE_ALERT_SHORT, true, true);
        }        
    }

    //Campaigns::DrawCampaignBanner('standard');
    //Campaigns::DrawCampaignBanner('global');

    if($objCarReservations->error) echo $objCarReservations->error;
    $objCarReservations->ShowReservationInfo();
    draw_content_end();
    
}else{
    draw_title_bar(_BOOKINGS);
    draw_important_message(_NOT_AUTHORIZED);	
}

