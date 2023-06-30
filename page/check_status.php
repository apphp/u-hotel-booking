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

draw_title_bar(_BOOKING_STATUS);
	
if(!$objLogin->IsLoggedIn() && Modules::IsModuleInstalled('booking')){
	if(ModulesSettings::Get('booking', 'show_booking_status_form') == 'yes'){		
		$booking_number = isset($_POST['booking_number']) ? prepare_input($_POST['booking_number']) : '';
		$task = isset($_POST['task']) ? prepare_input($_POST['task']) : '';
		$objBookings = new Bookings();
	
		draw_content_start();
		if($task == 'check_status' && !empty($booking_number)){
			$objBookings->DrawBookingStatus($booking_number);
			echo draw_line();
			if(!$objBookings->error){
				echo '<input class="form_button" type="button" value="'._START_OVER.'" onclick="javascript:appGoTo(\'page=check_status\')"></input><br><br>';
			}else{
				$objBookings->DrawBookingStatusBlock();				
			}
		}else{
			if(SITE_MODE == 'demo'){
				draw_important_message(_WRONG_BOOKING_NUMBER.' <br>Here the test number: WHJANH7BLN');
			}
			$objBookings->DrawBookingStatusBlock();
		}
		echo '<script type="text/javascript">appSetFocus("frmCheckBooking_booking_number");</script>';
		draw_content_end();			
	}else{
		draw_important_message(_NOT_AUTHORIZED);
	}
}else{
	draw_important_message(_NOT_AUTHORIZED);
}

