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
if(Modules::IsModuleInstalled('conferences')){
	global $objLogin, $objSettings;

	$action_message = '';
	$fields = '';
	$alert = '';
	$alert_type = '';
	$text_button_previous = _PREVIOUS;
	$text_button_next = _NEXT;
	$step = Session::Get('conference_step');
	$step = (int)$step > 0 ? (int)$step : 1;
	$getStep = (isset($_GET['step']) && (int)$_GET['step']) > 0 ? (int)$_GET['step'] : 1;
	$activeStep = $getStep <= ($step + 1) ? $getStep : ($step + 1);
	$act = isset($_POST['act']) ? prepare_input($_POST['act']) : '';

    ///$send_updates   = '';
    $first_name     = '';
    $last_name      = '';
    $birth_date     = '';
    $company        = '';
    $b_address      = '';
    $b_address_2    = '';
    $b_city         = '';
    $b_zipcode      = '';
    $b_country      = '';
    $b_state        = '';
    $phone          = '';
    $fax            = '';
    $email          = '';
    $confirm_email  = '';
    $url            = '';
    $user_name      = '';
    $user_password1 = '';
    $user_password2 = '';
    $agree          = '';
	$focus_field    = '';

	if(Session::Get('conference_id') != ''){
		$conference_id = (int)Session::Get('conference_id');
	}else{
		$conference_id = isset($_GET['cid']) ? (int)$_GET['cid'] : 0;
	}
	if(!empty($conference_id)){
		$conference = Conferences::GetConference($conference_id);
		if(!empty($conference) && is_array($conference)){
			switch($activeStep){
				case 8:
					$is_valid = $step == 8 ? true : false;

					if($act == 'send' && $getStep == 8){
						$is_valid = $step >= ($activeStep - 1) ? true : false;
                        $comments = isset($_POST['comments']) ? prepare_input($_POST['comments']) : '';
						Session::Set('conference_comment', $comments);
					}

					// Check input data
					if(!$is_valid){
						$activeStep = 7;
                    }else{
                        $conference_speciality = Session::Get('conference_speciality');
                        ///$send_updates         = Session::Get('conference_send_updates');
                        $first_name           = Session::Get('conference_first_name');
                        $last_name            = Session::Get('conference_last_name');
                        $birth_date           = Session::Get('conference_birth_date');
                        $company              = Session::Get('conference_company');
                        $b_address            = Session::Get('conference_b_address');
                        $b_address_2          = Session::Get('conference_b_address_2');
                        $b_city               = Session::Get('conference_b_city');
                        $b_zipcode            = Session::Get('conference_b_zipcode');
                        $b_country            = Session::Get('conference_b_country');
                        $b_state              = Session::Get('conference_b_state');
                        $phone                = Session::Get('conference_phone');
                        $email                = Session::Get('conference_email');
                        $url                  = Session::Get('conference_url');
                        $user_name            = Session::Get('conference_user_name');
                        $user_password        = Session::Get('conference_user_password1');
                        $agree                = Session::Get('conference_agree');
                        $cc_type              = Session::Get('conference_cc_type');
                        $cc_holder_name       = Session::Get('conference_cc_holder_name');
                        $cc_number            = Session::Get('conference_cc_number');
                        $cc_expires_month     = Session::Get('conference_cc_expires_month');
                        $cc_expires_year      = Session::Get('conference_cc_expires_year');
                        $cc_cvv_code          = Session::Get('conference_cc_cvv_code');
                        $i_need_book          = Session::Get('conference_i_need_book');
						$hotel_id			  = Session::Get('conference_reservation_hotel_id');
						$room_id			  = Session::Get('conference_reservation_room_id');
						$count_rooms		  = Session::Get('conference_reservation_count_rooms');
						$checkin			  = Session::Get('conference_reservation_checkin');
						$checkout			  = Session::Get('conference_reservation_checkout');
						$checkin_date		  = Session::Get('conference_reservation_checkin_date');
						$checkout_date		  = Session::Get('conference_reservation_checkout_date');
						$regular			  = Session::Get('conference_regular');
						$comments			  = Session::Get('conference_comment');

						$customer_id = 0;
						$booking_number = '';

						// Add customer
						if(!PASSWORDS_ENCRYPTION){
							$user_password = '\''.encode_text($user_password1).'\'';
						}else{
							if(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'aes'){					
								$user_password = 'AES_ENCRYPT(\''.encode_text($user_password1).'\', \''.PASSWORDS_ENCRYPT_KEY.'\')';
							}elseif(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'md5'){
								$user_password = 'MD5(\''.encode_text($user_password1).'\')';
							}
						}
						$sql = 'INSERT INTO '.TABLE_CUSTOMERS.'(
									first_name, last_name, birth_date, company,
									b_address, b_address_2, b_city,  b_zipcode, b_country, b_state,
									phone, fax, email, url,
									user_name, user_password, preferred_language,
									date_created, registered_from_ip, last_logged_ip,
									email_notifications, is_active, is_removed, comments, registration_code)
								VALUES(
									\''.encode_text($first_name).'\', \''.encode_text($last_name).'\', '.(!empty($birth_date) ? "'".encode_text($birth_date)."'" : null).', \''.encode_text($company).'\',
									\''.encode_text($b_address).'\', \''.encode_text($b_address_2).'\', \''.encode_text($b_city).'\', \''.encode_text($b_zipcode).'\', \''.encode_text($b_country).'\', \''.encode_text($b_state).'\',
									\''.encode_text($phone).'\', \''.encode_text($fax).'\', \''.encode_text($email).'\', \''.encode_text($url).'\',
									\''.encode_text($user_name).'\', '.$user_password.', \''.Application::Get('lang').'\',
									\''.date('Y-m-d H:i:s').'\', \'\', \'\',
									\'0\', \'1\', 0, \'\', \'\')';
						if(database_void_query($sql) > 0){
							$customer_id = database_insert_id();
						}

						if($i_need_book == 'yes'){
							$american_format = $objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? true : false;

							$checkin_parts			= explode('/', $checkin_date);
							$checkin_month 			= isset($checkin_parts[$american_format ? 0 : 1]) ? convert_to_decimal((int)$checkin_parts[$american_format ? 0 : 1]) : '';
							$checkin_day 			= isset($checkin_parts[$american_format ? 1 : 0]) ? convert_to_decimal((int)$checkin_parts[$american_format ? 1 : 0]) : '';
							$checkin_year 			= isset($checkin_parts[2]) ? (int)$checkin_parts[2] : '';

							$checkout_parts			= explode('/', $checkout_date);
							$checkout_month 		= isset($checkout_parts[$american_format ? 0 : 1]) ? convert_to_decimal((int)$checkout_parts[$american_format ? 0 : 1]) : '';
							$checkout_day			= isset($checkout_parts[$american_format ? 1 : 0]) ? convert_to_decimal((int)$checkout_parts[$american_format ? 1 : 0]) : '';
							$checkout_year 			= isset($checkout_parts[2]) ? (int)$checkout_parts[2] : '';

							$nights = nights_diff($checkin_year.'-'.$checkin_month.'-'.$checkin_day, $checkout_year.'-'.$checkout_month.'-'.$checkout_day);

							// Add booking (rough version)
							$objReservation = new Reservation();
							$objReservation->AddToReservation($room_id, $checkin_year.'-'.$checkin_month.'-'.$checkin_day, $checkout_year.'-'.$checkout_month.'-'.$checkout_day, $nights, $count_rooms, 0, 0, 0, '', $hotel_id, 0, 0);
							if($objReservation->error && !strpos($objReservation->error, 'msg success')){
								$alert_type = 'error';
								$alert = $objReservation->error;
								$activeStep = 7;
							}else{
								$sql = 'INSERT INTO '.TABLE_BOOKINGS.' SET booking_number = \'\'';
								if(database_void_query($sql)){					
									$insert_id = database_insert_id();
									$booking_number = $objReservation->GenerateBookingNumber($insert_id); 
									$sql = 'UPDATE '.TABLE_BOOKINGS.' SET booking_number = \''.$booking_number.'\' WHERE id = '.(int)$insert_id;
									if(!database_void_query($sql)){
										$alert_type = 'error';
										$alert = _ORDER_ERROR;
										$activeStep = 7;
									}else{
										// Update the information on the reservation
										unset($objReservation);
										$objReservation = new Reservation();
										$objReservation->DoReservation('quick_reservation');
										if($objReservation->error && !strpos($objReservation->error, 'msg success')){
											$alert_type = 'error';
											$alert = $objReservation->error;
										}else{
											$objReservation->EmptyCart();
										}
									}
								}else{
									$alert_type = 'error';
									$alert = _ORDER_ERROR;
									$activeStep = 7;
								}
							}
						}

						if($alert_type != 'error'){
							// Add participants
							//
							$sql = 'INSERT INTO '.DB_PREFIX.'conference_participants (
										conference_id, customer_id, speciality_id, is_admin_registration,
										created_by_admin_id, created_date, booking_number, course_registrations, cc_type, cc_holder_name, cc_number,
										cc_expires_month, cc_expires_year, cc_cvv_code, additional_info, email_sent)
									VALUES(
										\''.encode_text($conference_id).'\', \''.encode_text($customer_id).'\', \''.encode_text($conference_speciality).'\', \'0\',
										\'0\', \''.date('Y-m-d H:i:s').'\', \''.$booking_number.'\', \''.encode_text(serialize($regular)).'\', \''.encode_text($cc_type).'\', \''.encode_text($cc_holder_name).'\', AES_ENCRYPT(\''.encode_text($cc_number).'\', \''.PASSWORDS_ENCRYPT_KEY.'\'),
										\''.encode_text($cc_expires_month).'\', \''.encode_text($cc_expires_year).'\', AES_ENCRYPT(\''.encode_text($cc_cvv_code).'\', \''.PASSWORDS_ENCRYPT_KEY.'\'), \''.encode_text($comments).'\', 0)';

							database_void_query($sql);

							// Clear session data
							Session::Set('conference_speciality', '');
							///Session::Set('conference_send_updates', '');
							Session::Set('conference_first_name', '');
							Session::Set('conference_last_name', '');
							Session::Set('conference_birth_date', '');
							Session::Set('conference_company', '');
							Session::Set('conference_b_address', '');
							Session::Set('conference_b_address_2', '');
							Session::Set('conference_b_city', '');
							Session::Set('conference_b_zipcode', '');
							Session::Set('conference_b_country', '');
							Session::Set('conference_b_state', '');
							Session::Set('conference_phone', '');
							Session::Set('conference_email', '');
							Session::Set('conference_url', '');
							Session::Set('conference_user_name', '');
							Session::Set('conference_user_password1', '');
							Session::Set('conference_agree', '');
							Session::Set('conference_cc_type', '');
							Session::Set('conference_cc_holder_name', '');
							Session::Set('conference_cc_number', '');
							Session::Set('conference_cc_expires_month', '');
							Session::Set('conference_cc_expires_year', '');
							Session::Set('conference_cc_cvv_code', '');
							Session::Set('conference_i_need_book', '');
							Session::Set('conference_regular', '');
							Session::Set('conference_reservation_hotel_id', '');
							Session::Set('conference_reservation_room_id', '');
							Session::Set('conference_reservation_count_rooms', '');
							Session::Set('conference_reservation_checkin', '');
							Session::Set('conference_reservation_checkout', '');
							Session::Set('conference_reservation_checkin_date', '');
							Session::Set('conference_reservation_checkout_date', '');
							Session::Set('conference_comment', '');
							Session::Set('conference_step', '');
		
							Session::Set('conference_alert_type', 'success');
							Session::Set('conference_alert', 'You successfully registered for the conference');
							redirect_to('index.php?page=conference_general&cid='.$conference_id);
						}
					}

				case 7:
					$is_valid = $step == 7 ? true : false;

					if($act == 'send' && $getStep == 7){
						$is_valid = $step >= ($activeStep - 1) ? true : false;

						$room_id        = isset($_POST['room_id']) ? (int)$_POST['room_id'] : '';
						$count_rooms    = isset($_POST['count_rooms']) ? (int)$_POST['count_rooms'] : '';

						$american_format = $objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? true : false;
						$checkin_date   = isset($_POST['checkin_date']) ? prepare_input($_POST['checkin_date']) : date($american_format ? 'm/d/Y' : 'd/m/Y');
						$checkin_parts  = explode('/', $checkin_date);
						$checkin_month  = isset($checkin_parts[$american_format ? 0 : 1]) ? convert_to_decimal((int)$checkin_parts[$american_format ? 0 : 1]) : '';
						$checkin_day    = isset($checkin_parts[$american_format ? 1 : 0]) ? convert_to_decimal((int)$checkin_parts[$american_format ? 1 : 0]) : '';
						$checkin_year   = isset($checkin_parts[2]) ? (int)$checkin_parts[2] : '';

						$checkout_date  = isset($_POST['checkout_date']) ? prepare_input($_POST['checkout_date']) : date($american_format ? 'm/d/Y' : 'd/m/Y', time() + (24 * 60 * 60));
						$checkout_parts = explode('/', $checkout_date);
						$checkout_month = isset($checkout_parts[$american_format ? 0 : 1]) ? convert_to_decimal((int)$checkout_parts[$american_format ? 0 : 1]) : '';
						$checkout_day   = isset($checkout_parts[$american_format ? 1 : 0]) ? convert_to_decimal((int)$checkout_parts[$american_format ? 1 : 0]) : '';
						$checkout_year  = isset($checkout_parts[2]) ? (int)$checkout_parts[2] : '';

						if(!empty($room_id)){
							$room_info = Rooms::GetRoomInfo($room_id);
							if(empty($room_info)){
								$alert_type = 'error';
								$alert = _WRONG_PARAMETER_PASSED;
							}elseif(!empty($count_rooms)){
								$min_max_rooms = Rooms::GetMinMaxCountRooms($checkin_year.'-'.$checkin_month.'-'.$checkin_day, $checkout_year.'-'.$checkout_month.'-'.$checkout_day, $room_id);
								if(empty($min_max_rooms) || empty($min_max_rooms['max_rooms']) || $min_max_rooms['min_rooms'] > $count_rooms || $min_max_rooms['max_rooms'] < $count_rooms){
									$alert_type = 'error';
									$alert = _WRONG_PARAMETER_PASSED;
								}else{
									Session::Set('conference_reservation_hotel_id', $conference['hotel_id']);
									Session::Set('conference_reservation_room_id', $room_id);
									Session::Set('conference_reservation_count_rooms', $count_rooms);
									Session::Set('conference_reservation_checkin', $checkin_year.'-'.$checkin_month.'-'.$checkin_day);
									Session::Set('conference_reservation_checkout', $checkout_year.'-'.$checkout_month.'-'.$checkout_day);
									Session::Set('conference_reservation_checkin_date', $checkin_date);
									Session::Set('conference_reservation_checkout_date', $checkout_date);
								}
							}else{
								$alert_type = 'error';
								$alert = _PLEASE_SELECT_NUMBER_ROOMS;
							}
						}else{
							$alert_type = 'error';
							$alert = _PLEASE_SELECT_ROOM;
						}
						if($alert_type == 'error'){
							$is_valid = false;
						}
						if($step < 7){
							Session::Set('conference_step', '7');
						}
					}

					// Check input data
					if(!$is_valid){
						$activeStep = 6;
					}else{
						$comment = Session::Get('conference_comment');
						$fields .= '<div class="logic-block">';
                        $fields .= '<p>
                            <label>'._COMMENTS.'</label>:
                            <br/>
                            <textarea name="comments" cols="50" rows="10" id="comments">'.$comment.'</textarea>
                        </p>';
						$fields .= '</div>';
                        $text_button_next = _SUBMIT;
						break;
					}

				case 6:
					$is_valid = $step >= 6 ? true : false;
					if(Session::Get('conference_i_need_book') == 'no'){
						$is_valid = false;
					}

					if($act == 'send' && $getStep == 6){
						$is_valid = $step >= ($activeStep - 1) ? true : false;
						if($is_valid){
							$i_need_book = isset($_POST['i_need_book']) ? prepare_input($_POST['i_need_book']) : '';
							if($i_need_book == 'no'){
								Session::Set('conference_i_need_book', 'no');
								if($step < 7){
									Session::Set('conference_step', '7');
								}
								redirect_to('index.php?page=conference_registration&step=7&cid='.$conference_id);
							}elseif($i_need_book == 'yes'){
								Session::Set('conference_i_need_book', 'yes');
								if($step < 6){
									Session::Set('conference_step', '6');
								}
							}else{
								$is_valid = false;
								$alert_type = 'error';
								$alert = _CONFERENCES_SELECT_OPTION_ALERT; 
							}
						}
					}

					// Check input data
					if(!$is_valid){
						$activeStep = 5;
					}else{
						$action_url = APPHP_BASE.'index.php?page=conference_registration&step=7&cid='.$conference_id;
						$nl = "\r\n";

						$fields = '<link rel="stylesheet" type="text/css" href="'.APPHP_BASE.'templates/'.Application::Get('template').'/css/calendar.css" />'.$nl;
						
						if($getStep == '6'){
							$american_format = $objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? true : false;

							$room_id = Session::Get('conference_reservation_room_id') != '' ? Session::Get('conference_reservation_room_id') : '';
							$count_rooms = Session::Get('conference_reservation_count_rooms') != '' ? Session::Get('conference_reservation_count_rooms') : '';
							$checkin_date = Session::Get('conference_reservation_checkin_date') != '' ? Session::Get('conference_reservation_checkin_date') : date($american_format ? 'm/d/Y' : 'd/m/Y');
							$checkout_date = Session::Get('conference_reservation_checkout_date') != '' ? Session::Get('conference_reservation_checkout_date') : date($american_format ? 'm/d/Y' : 'd/m/Y', time() + (24 * 60 * 60));

							$checkin_parts			= explode('/', $checkin_date);
							$checkin_month 			= isset($checkin_parts[$american_format ? 0 : 1]) ? convert_to_decimal((int)$checkin_parts[$american_format ? 0 : 1]) : '';
							$checkin_day 			= isset($checkin_parts[$american_format ? 1 : 0]) ? convert_to_decimal((int)$checkin_parts[$american_format ? 1 : 0]) : '';
							$checkin_year 			= isset($checkin_parts[2]) ? (int)$checkin_parts[2] : '';

							$checkout_parts			= explode('/', $checkout_date);
							$checkout_month 		= isset($checkout_parts[$american_format ? 0 : 1]) ? convert_to_decimal((int)$checkout_parts[$american_format ? 0 : 1]) : '';
							$checkout_day			= isset($checkout_parts[$american_format ? 1 : 0]) ? convert_to_decimal((int)$checkout_parts[$american_format ? 1 : 0]) : '';
							$checkout_year 			= isset($checkout_parts[2]) ? (int)$checkout_parts[2] : '';
						}

//!!!					// Retrieve all active hotels according (for owner - related only, for selected location - related only)
						// $hotels_list = ($objLogin->IsLoggedInAs('hotelowner')) ? implode(',', $objLogin->AssignedToHotels()) : '';

						$total_hotels = Hotels::GetAllHotels(
							(!empty($hotels_list) ? TABLE_HOTELS.'.id IN ('.$hotels_list.')' : '')
						);
						$hotels_total_number = $total_hotels[1];

						$output_hotels = '';
						$output_rooms = '';
						$total_rooms = array();
						$output_hotels .= '<input type="hidden" id="conference_hotel_sel_id" name="hotel_id" val="'.$conference['hotel_id'].'" />'.$nl;

						$total_rooms = Rooms::GetAllActive('r.hotel_id = '.(int)$conference['hotel_id']);

						$output_rooms .= '<select class="my-form-control mgrid_select select_rooms" id="conference_room_sel_id" name="room_id">'.$nl;
						$output_rooms .= '<option value="">-- '._SELECT.' --</option>'.$nl;
						$rooms_description = '';
						if(!empty($total_rooms)){
							foreach($total_rooms[0] as $key => $val){
								$output_rooms .= '<option'.((!empty($room_id) && $room_id == $val['id']) ? ' selected="selected"' : '').' value="'.$val['id'].'">'.$val['room_type'].'</option>'.$nl;
								$rooms_description .= '<table>';
								$rooms_description .= '<tr>';
								$rooms_description .= '<td><h4>'.prepare_link('rooms', 'room_id', $val['id'], $val['room_type'], $val['room_type'], '', _CLICK_TO_VIEW).'</h4></td>';
								$rooms_description .= '<td width="200px" align="center" rowspan="3">';
								if($val['room_icon'] != '') $rooms_description .= '<a href="images/rooms/'.$val['room_icon'].'" rel="lyteshow_'.$val['id'].'" title="'._IMAGE.' 1">';
								$rooms_description .= '<img class="room_icon" src="images/rooms/'.$val['room_icon_thumb'].'" width="165px" alt="icon" />';
								if($val['room_icon'] != '') $rooms_description .= '</a>';							
								if($val['room_picture_1'] != '') $rooms_description .= '  <a href="images/rooms/'.$val['room_picture_1'].'" rel="lyteshow_'.$val['id'].'" title="'._IMAGE.' 1"></a>';					
								if($val['room_picture_2'] != '') $rooms_description .= '  <a href="images/rooms/'.$val['room_picture_2'].'" rel="lyteshow_'.$val['id'].'" title="'._IMAGE.' 2"></a>';					
								if($val['room_picture_3'] != '') $rooms_description .= '  <a href="images/rooms/'.$val['room_picture_3'].'" rel="lyteshow_'.$val['id'].'" title="'._IMAGE.' 3"></a>';
								if($val['room_picture_4'] != '') $rooms_description .= '  <a href="images/rooms/'.$val['room_picture_4'].'" rel="lyteshow_'.$val['id'].'" title="'._IMAGE.' 4"></a>';
								if($val['room_picture_5'] != '') $rooms_description .= '  <a href="images/rooms/'.$val['room_picture_5'].'" rel="lyteshow_'.$val['id'].'" title="'._IMAGE.' 5"></a>';
								$rooms_description .= '</td>';
								$rooms_description .= '</tr>';
								$rooms_description .= '<tr><td height="70px">'.$val['room_short_description'].'</td></tr>';
								$rooms_description .= '</table>';
							}
						}
						$output_rooms .= '</select>'.$nl;			


						$min_nights = ModulesSettings::Get('booking', 'minimum_nights');
						$search_availability_period = ModulesSettings::Get('rooms', 'search_availability_period');

						$output1 = '<input class="form-control mySelectCalendar checkin_date" name="checkin_date" id="conference_checkin_date" placeholder="'.$objSettings->GetParameter('date_format').'" type="text" value="'.$checkin_date.'">';
						$selected_date2 = !empty($checkout_date) ? $checkout_date : date($objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? 'm/d/Y' : 'd/m/Y', time() + ((int)$min_nights * 24 * 60 * 60));
						$output2 = '<input class="form-control mySelectCalendar checkout_date" name="checkout_date" id="conference_checkout_date" placeholder="'.$objSettings->GetParameter('date_format').'" type="text" value="'.$selected_date2.'">';

						$max_count_rooms = 0;
						$min_count_rooms = 0;
						$output_room_count = '';
						$arr_min_max_count_rooms = Rooms::GetMinMaxCountRooms($checkin_year.'-'.$checkin_month.'-'.$checkin_day, $checkout_year.'-'.$checkout_month.'-'.$checkout_day, $room_id);
						if(!empty($arr_min_max_count_rooms)){
							$min_count_rooms = $arr_min_max_count_rooms['min_rooms'];
							$max_count_rooms = $arr_min_max_count_rooms['max_rooms'];
						}
						$output_room_count .= '<select class="my-form-control mgrid_select select_count_rooms" id="count_rooms" name="count_rooms">'.$nl;
						$output_room_count .= '<option value="">-- '._SELECT.' --</option>'.$nl;
						if(!empty($max_count_rooms)){
							for($i = 1; $i <= $max_count_rooms; $i++){
								$output_room_count .= '<option'.(($count_rooms == $i) ? ' selected="selected"' : '').' value="'.$i.'">'.$i.'</option>'.$nl;
							}
						}
						$output_room_count .= '</select>'.$nl;			
						
						$output_script = '<script>
						jQuery(document).ready(function(){
							function getCountRooms()
							{
								var room_select = jQuery("#conference_room_sel_id").val();
								var checkin_val = jQuery("#conference_checkin_date").val();
								var checkout_val = jQuery("#conference_checkout_date").val();
								jQuery(".error_message").hide();
								jQuery(".msg.fail:not(.error_message .msg.fail)").hide();
								jQuery("#count_rooms").empty();
								jQuery("#count_rooms").replaceWith(\'<select class="form-control mgrid_select selectpicker" disabled="disabled" id="count_rooms" name="count_rooms"></select>\');
								jQuery("<option />", {val: "", selected: true, text: "'.htmlspecialchars(_LOADING).'..."}).appendTo("#count_rooms");
								jQuery.ajax({
									url: "'.APPHP_BASE.'ajax/count_rooms.ajax.php",
									global: false,
									type: "POST",
									data: ({
										room_id: room_select,
										checkin_date: checkin_val,
										checkout_date: checkout_val
									}),
									dataType: "json",
									async: true,
									error: function(html){
										jQuery("#count_rooms").empty();
										jQuery("#count_rooms").replaceWith(\'<select class="form-control mgrid_select selectpicker" id="count_rooms" name="count_rooms"></select>\');
										jQuery("<option />", {val: "", selected: true, text: "'.htmlspecialchars(_NONE).'"}).appendTo("#count_rooms");
										console.log("AJAX: cannot connect to the server or server response error! Please try again later.");
									},
									success: function(data){
										jQuery("#count_rooms").empty();
										if(data.min_rooms >= 0 && data.max_rooms > 0){
											jQuery("#count_rooms").replaceWith(\'<select class="form-control mgrid_select selectpicker" id="count_rooms" name="count_rooms"></select>\');
											jQuery("<option />", {val: "", selected: true, text: "-- '.htmlspecialchars(_SELECT).' --"}).appendTo("#count_rooms");
											// add empty option
											for(var i = data.min_rooms; i <= data.max_rooms; i++){
												jQuery("<option />", {val: i, text: i}).appendTo("#count_rooms");
											}
										}else{
											jQuery("#count_rooms").replaceWith(\'<select class="form-control mgrid_select selectpicker" disabled="disabled" id="count_rooms" name="count_rooms"></select>\');
											jQuery("<option />", {val: "", selected: true, text: "'.htmlspecialchars(_NONE).'"}).appendTo("#count_rooms");
											jQuery("#error_none_count_rooms").show();
										}
									}
								});
							}
							jQuery(function() {
								var minDate = new Date();
								var maxDate = new Date();
								// min date + 1 day
								minDate.setTime(Date.parse("'.$checkin_date.'") + ('.((int)$min_nights > 0 ? (int)$min_nights.' * ' : '' ).'24 * 60 * 60 * 1000));
								'.(ModulesSettings::Get('booking', 'allow_booking_in_past') == 'yes' && $objLogin->IsLoggedInAsAdmin()
									? 'minDate.setFullYear(minDate.getFullYear() - 1);'
									: '').'
								jQuery("#conference_checkin_date").datepicker({
									'.(ModulesSettings::Get('booking', 'allow_booking_in_past') == 'yes' && $objLogin->IsLoggedInAsAdmin()
										? 'defaultDate: "-1y",minDate: "-1y",'
										: 'minDate: 0,').'
									maxDate: "+'.(int)$search_availability_period.'y",
									numberOfMonths: 2,
									//dateFormat: "'.($objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? 'mm/dd/yy' : 'dd/mm/yy').'"
								});
								jQuery("#conference_checkout_date").datepicker({
									'.(ModulesSettings::Get('booking', 'allow_booking_in_past') == 'yes' && $objLogin->IsLoggedInAsAdmin()
										? 'defaultDate: "-1y",'
										: '').'
									minDate: minDate,
									maxDate: "+'.(int)$search_availability_period.'y +'.((int)$min_nights > 0 ? (int)$min_nights : '1').'d",
									numberOfMonths: 2,
									dateFormat: "'.($objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? 'mm/dd/yy' : 'dd/mm/yy').'"
								});
							});
							jQuery("#conference_checkin_date").change(function(){
								var checkin_val = jQuery("#conference_checkin_date").val();
								'.($objSettings->GetParameter('date_format') == 'dd/mm/yyyy' 
									// make american format
									? 'checkin_val = checkin_val.replace(/^(\d\d)(\/)(\d\d)(\/\d+)$/g,"$3$2$1$4")' 
									: '').'
								var set_date = new Date();

								// checkin_val + 1d
								set_date.setTime(Date.parse(checkin_val) + ('.((int)$min_nights > 0 ? (int)$min_nights.' * ' : '' ).'24 * 60 * 60 * 1000));
								jQuery("#conference_checkout_date").datepicker("option", "minDate", set_date);

								getCountRooms();
							});
							jQuery("#conference_checkout_date").change(function(){
								var checkout_val = jQuery("#conference_checkout_date").val();
								'.($objSettings->GetParameter('date_format') == 'dd/mm/yyyy' 
									// make american format
									? 'checkout_val = checkout_val.replace(/^(\d\d)(\/)(\d\d)(\/\d+)$/g,"$3$2$1$4")' 
									: '').'
								var set_date = new Date();

								// checkout_val - 1d
								set_date.setTime(Date.parse(checkout_val) - ('.((int)$min_nights > 0 ? (int)$min_nights.' * ' : '' ).'24 * 60 * 60 * 1000));
								jQuery("#conference_checkin_date").datepicker("option", "maxDate", set_date);

								getCountRooms();
							});
							jQuery("#conference_hotel_sel_id").change(function(){
								var hotel_select = jQuery(this).val();
								jQuery(".error_message").hide();
								jQuery(".msg.fail:not(.error_message .msg.fail)").hide();
								jQuery("#conference_room_sel_id").empty();
								jQuery("#conference_room_sel_id").replaceWith(\'<select class="form-control mgrid_select selectpicker" disabled="disabled" id="conference_room_sel_id" name="room_id"></select>\');
								jQuery("<option />", {val: "", selected: true, text: "'.htmlspecialchars(_LOADING).'..."}).appendTo("#conference_room_sel_id");
								jQuery.ajax({
									url: "'.APPHP_BASE.'ajax/rooms.ajax.php",
									global: false,
									type: "POST",
									data: ({
										hotel_id: hotel_select
									}),
									dataType: "json",
									async: true,
									error: function(html){
										console.log("AJAX: cannot connect to the server or server response error! Please try again later.");
									},
									success: function(data){
										if(data.length > 0){
											jQuery("#conference_room_sel_id").replaceWith(\'<select class="form-control mgrid_select selectpicker" id="conference_room_sel_id" name="room_id"></select>\');
											jQuery("<option />", {val: "", selected: true, text: "--'.htmlspecialchars(_SELECT).'--"}).appendTo("#conference_room_sel_id");
											// add empty option
											for(var i = 0; i < data.length; i++){
												jQuery("<option />", {val: data[i].id, text: data[i].name}).appendTo("#conference_room_sel_id");
											}
										}else{
											jQuery("#conference_room_sel_id").replaceWith(\'<select class="form-control mgrid_select selectpicker" disabled="disabled" id="conference_room_sel_id" name="room_id"></select>\');
											jQuery("<option />", {val: "", selected: true, text: "'.htmlspecialchars(_NONE).'"}).appendTo("#conference_room_sel_id");
											jQuery("#error_none_hotel_rooms").show();
										}
									}
								});
							});
							jQuery("#frmConferenceSpecialty").on("change", "#conference_room_sel_id", function(){
								getCountRooms();
							});
						});
						</script>';

						$fields .= '<div class="error_message" id="error_none_hotel_rooms" style="display:none;">'.draw_important_message(_THIS_HOTEL_NO_ROOMS, false).'</div>';
						$fields .= '<div class="error_message" id="error_none_count_rooms" style="display:none;">'.draw_important_message(_NO_AVAILABLE_ROOMS, false).'</div>';
						$fields .= $output_hotels;
						$fields .= '<div class="logic-block">';
						$fields .= '<table cellspacing="2" border="0">';
						
						$fields .= '<tr><td><label>'._EXISTING_ROOM_TYPES.'</label></td></tr>';
						$fields .= '<tr><td>'.$output_rooms.'</td></tr>';
						$fields .= '<tr><td><label>'._CHECK_IN.'</label></td></tr>';
						$fields .= '<tr><td>'.$output1.'</td></tr>';
						$fields .= '<tr><td><label>'._CHECK_OUT.'</label></td></tr>';
						$fields .= '<tr><td>'.$output2.'</td></tr>';
						$fields .= '<tr><td><label>'._ROOMS_COUNT.'</label></td></tr>';
						$fields .= '<tr><td>'.$output_room_count.'</td></tr>';


						$fields .= '</table>';
						$fields .= '</div>';
						$fields .= '<br/>';
						$fields .= '<div class="logic-block">';
                        $fields .= '<h3>'._DESCRIPTION.'</h3>';
						$fields .= $rooms_description;
						$fields .= '</div>';
						$fields .= $output_script;
						$text_button_next = _RESERVATION;
                        //$fields .= '<br/>
                            //<h3 style="color:#e35121;text-align:center;"><i>This page is under construction</i></h3>
                            //<br/>';
						break;
					}

				case 5:
					$is_valid = $step >= 5 ? true : false;

					$cc_params = array(
						'cc_type'           => '',
						'cc_holder_name'    => '',
						'cc_number'         => '',
						'cc_expires_month'  => '',
						'cc_expires_year'   => '',
						'cc_cvv_code'       => '',
					);
					$regular = '';


					if($act== 'send' && $getStep == 5){
						$is_valid = $step >= ($activeStep - 1) ? true : false;

                        $regular = isset($_POST['regular']) ? prepare_input($_POST['regular']) : '';
						$cc_params['cc_type']          = isset($_POST['cc_type'])          ? prepare_input($_POST['cc_type'])          : '';
						$cc_params['cc_holder_name']   = isset($_POST['cc_holder_name'])   ? prepare_input($_POST['cc_holder_name'])   : '';
						$cc_params['cc_number']        = isset($_POST['cc_number'])        ? prepare_input($_POST['cc_number'])        : '';
						$cc_params['cc_expires_month'] = isset($_POST['cc_expires_month']) ? prepare_input($_POST['cc_expires_month']) : '';
						$cc_params['cc_expires_year']  = isset($_POST['cc_expires_year'])  ? prepare_input($_POST['cc_expires_year'])  : '';
						$cc_params['cc_cvv_code']      = isset($_POST['cc_cvv_code'])      ? prepare_input($_POST['cc_cvv_code'])      : '';
						if(empty($regular) || !is_array($regular)){
							$is_valid = false;
							$alert_type = 'error';
							$alert = _CONFERENCES_SELECT_COURSE_ALERT;
						}else{
							Session::Set('conference_regular', $regular);
							$alert = check_credit_card($cc_params);
							if(!empty($alert)){
								$is_valid = false;
								$alert_type = 'error';
							}else{
								$is_valid = true;

								Session::Set('conference_cc_type', $cc_params['cc_type']);
								Session::Set('conference_cc_holder_name', $cc_params['cc_holder_name']);
								Session::Set('conference_cc_number', $cc_params['cc_number']);
								Session::Set('conference_cc_expires_month', $cc_params['cc_expires_month']);
								Session::Set('conference_cc_expires_year', $cc_params['cc_expires_year']);
								Session::Set('conference_cc_cvv_code', $cc_params['cc_cvv_code']);

								if($step < 5){
									Session::Set('conference_step', '5');
								}
							}
						}
					}

					// Check input data
					if(!$is_valid){
						$activeStep = 4;
					}else{
						$i_need_book = Session::Get('conference_i_need_book');
						$fields .= '<div class="logic-block">';
                        $fields .= '<p>
                                <label>I need to book accommodations</label>
                                <br/>
                                <input type="radio" name="i_need_book" id="i_need_book_yes" value="yes"'.($i_need_book == 'yes' ? ' checked="checked"' : '').'>
                                <label  for="i_need_book_yes">'._YES.'</label>
								<input type="radio" name="i_need_book" id="i_need_book_no" value="no"'.($i_need_book == 'no' ? ' checked="checked"' : '').'>
                                <label  for="i_need_book_no">'._NO.'</label>
                            </p>';
						$fields .= '</div>';
						break;
					}

				case 4:
					$is_valid = $step >= 4 ? true : false;

					if($act == 'send' && $getStep == 4){
						$is_valid = $step >= ($activeStep - 1) ? true : false;
						$image_verification_allow = ModulesSettings::Get('customers', 'image_verification_allow');
						
						//include_once('modules/captcha/securimage.php');
						//$objImg = new Securimage();
						
						///$send_updates = isset($_POST['send_updates']) ? (int)$_POST['send_updates'] : '1';
						$first_name   = isset($_POST['first_name']) ? prepare_input($_POST['first_name']) : '';
						$last_name    = isset($_POST['last_name']) ? prepare_input($_POST['last_name']) : '';
						
						$birth_date_year  = isset($_POST['birth_date__nc_year']) ? prepare_input($_POST['birth_date__nc_year']) : '';
						$birth_date_month = isset($_POST['birth_date__nc_month']) ? prepare_input($_POST['birth_date__nc_month']) : '';
						$birth_date_day   = isset($_POST['birth_date__nc_day']) ? prepare_input($_POST['birth_date__nc_day']) : '';
						$birth_date       = $birth_date_year.'-'.$birth_date_month.'-'.$birth_date_day;
                        $birth_date       = $birth_date != '--' ? $birth_date : '';

						$company        = isset($_POST['company'])        ? prepare_input($_POST['company'])              : '';
						$b_address      = isset($_POST['b_address'])      ? prepare_input($_POST['b_address'])            : '';
						$b_address_2    = isset($_POST['b_address_2'])    ? prepare_input($_POST['b_address_2'])          : '';
						$b_city         = isset($_POST['b_city'])         ? prepare_input($_POST['b_city'])               : '';
						$b_zipcode      = isset($_POST['b_zipcode'])      ? prepare_input($_POST['b_zipcode'])            : '';
						$b_country      = isset($_POST['b_country'])      ? prepare_input($_POST['b_country'])            : '';
						$b_state        = isset($_POST['b_state'])        ? prepare_input($_POST['b_state'])              : '';
						$phone          = isset($_POST['phone'])          ? prepare_input($_POST['phone'])                : '';
						$fax            = isset($_POST['fax'])            ? prepare_input($_POST['fax'])                  : '';
						$email          = isset($_POST['email'])          ? prepare_input($_POST['email'])                : '';
						$confirm_email  = isset($_POST['confirm_email'])  ? prepare_input($_POST['confirm_email'])        : '';
						$url            = isset($_POST['url'])            ? prepare_input($_POST['url'], false, 'medium') : '';
						$user_name      = isset($_POST['user_name'])      ? prepare_input($_POST['user_name'])            : '';
						$user_password1 = isset($_POST['user_password1']) ? prepare_input($_POST['user_password1'])       : '';
						$user_password2 = isset($_POST['user_password2']) ? prepare_input($_POST['user_password2'])       : '';
						$agree          = isset($_POST['agree'])          ? (int)$_POST['agree']                          : '';
						$captcha_code   = isset($_POST['captcha_code'])   ? prepare_input($_POST['captcha_code'])         : '';
						$focus_field    = 'first_name';
						
						$alert = '';
						
						$account_created = false;
						
                        if($first_name == ''){
                            $alert = _FIRST_NAME_EMPTY_ALERT;
                            $focus_field = 'first_name';
                        }elseif($last_name == ''){
                            $alert = _LAST_NAME_EMPTY_ALERT;
                            $focus_field = 'last_name';
                        }elseif($birth_date != '' && !check_date($birth_date)){
                            $alert = _BIRTH_DATE_VALID_ALERT;
                            $focus_field = 'birth_date';
                        }elseif($b_address == ''){
                            $alert = _ADDRESS_EMPTY_ALERT;
                            $focus_field = 'b_address';
                        }elseif($b_city == ''){
                            $alert = _CITY_EMPTY_ALERT;
                            $focus_field = 'b_city';
                        //}elseif($b_zipcode == ''){
                        //	$alert = _ZIPCODE_EMPTY_ALERT;
                        }elseif($b_country == ''){
                            $alert = _COUNTRY_EMPTY_ALERT;
                            $focus_field = 'b_country';
                        }elseif($phone == ''){
                            $alert = _PHONE_EMPTY_ALERT;
                            $focus_field = 'phone';
                        }elseif($email == ''){
                            $alert = _EMAIL_EMPTY_ALERT;
                            $focus_field = 'email';
                        }elseif(($email != '') && (!check_email_address($email))){
                            $alert = _EMAIL_VALID_ALERT;
                            $focus_field = 'email';
						}elseif($confirm_email == ''){
                            $alert = _CONFIRM_EMAIL_EMPTY_ALERT;
							$focus_field = 'confirm_email';
						}elseif($email != $confirm_email){
							$alert = _CONF_EMAIL_MATCH;
							$focus_field = 'confirm_email';
                        }else{
							if($step == 3){
								if($agree == ''){
									$alert = _CONFIRM_TERMS_CONDITIONS;
									$focus_field = 'agree';
								//}elseif(0 && $image_verification_allow == 'yes' && !$objImg->check($captcha_code)){				
									//$alert = _WRONG_CODE_ALERT;
									//$focus_field = 'captcha_code';
								}
							}
                        }
                        if(!empty($alert)){
                            $alert_type= 'error';
                            $is_valid = false;
                            $activeStep = 3;
                        }else{
                            ///Session::Set('conference_send_updates', (int)$send_updates);
                            Session::Set('conference_first_name', $first_name);
                            Session::Set('conference_last_name', $last_name);
                            Session::Set('conference_birth_date', $birth_date);
                            Session::Set('conference_company', $b_country);
                            Session::Set('conference_b_address', $b_address);
                            Session::Set('conference_b_address_2', $b_address_2);
                            Session::Set('conference_b_city', $b_city);
                            Session::Set('conference_b_zipcode', $b_zipcode);
                            Session::Set('conference_b_country', $b_country);
                            Session::Set('conference_b_state', $b_state);
                            Session::Set('conference_phone', $phone);
                            Session::Set('conference_fax', $fax);
                            Session::Set('conference_email', $email);
                            Session::Set('conference_url', $url);
                            Session::Set('conference_user_name', $user_name);
                            Session::Set('conference_user_password1', $user_password1);
                            Session::Set('conference_agree', $agree);

                            $is_valid = true;
                        }
                    }

                    if(!$is_valid){
						$activeStep = 3;
                    }else{
                        if($getStep == 4){
                            $cc_params = array();
                            $cc_params['cc_type']          = Session::Get('conference_cc_type');
                            $cc_params['cc_holder_name']   = Session::Get('conference_cc_holder_name');
                            $cc_params['cc_number']        = Session::Get('conference_cc_number');
                            $cc_params['cc_expires_month'] = Session::Get('conference_cc_expires_month');
                            $cc_params['cc_expires_year']  = Session::Get('conference_cc_expires_year');
                            $cc_params['cc_cvv_code']      = Session::Get('conference_cc_cvv_code');
							$regular = Session::Get('conference_regular');
                        }
                        $nl = PHP_EOL;
                        $req_mark = '<span class="required">*</span>';

                        $cc_type          = $cc_params['cc_type'];
                        $cc_holder_name   = $cc_params['cc_holder_name'];
                        $cc_number        = $cc_params['cc_number'];
                        $cc_cvv_code      = $cc_params['cc_cvv_code'];
                        $cc_expires_month = $cc_params['cc_expires_month'];
                        $cc_expires_year  = $cc_params['cc_expires_year'];

						$tuition = ConferenceTuitions::GetConferenceTuitions($conference_id);
						$fields .= '<div class="logic-block">';
						if(!empty($tuition[1])){
							$fields .= '<h3>'._COURSE_REGISTRATION.'</h3>';
							$conference_speciality = Session::Get('conference_speciality');
							$currency_rate = Application::Get('currency_rate');
							$currency_format = get_currency_format();
							for($i = 0; $i < $tuition[1]; $i++){
								$price = '';
								// MD/DO
								if(in_array($conference_speciality, array(25,19))){
									$price = Currencies::PriceFormat(($tuition[0][$i]['price_type_1'] * $currency_rate), '', '', $currency_format);
								}elseif(in_array($conference_speciality, array(13,27,1,3))){
									$price = Currencies::PriceFormat(($tuition[0][$i]['price_type_2'] * $currency_rate), '', '', $currency_format);
								}else{
									$price = Currencies::PriceFormat(($tuition[0][$i]['price_type_3'] * $currency_rate), '', '', $currency_format);
								}
								$fields .= '<div class="regulartuition"><input name="regular[]" class="tution_selector_main main_tuition_checkboxes" type="checkbox" value="'.$tuition[0][$i]['id'].'"'.(!empty($regular) && in_array($tuition[0][$i]['id'], $regular) ? ' checked="checked"' : '').' id="regular_tuition"> <strong>'.$tuition[0][$i]['name'].':</strong> '.$price.' </div>';
								$fields .= '<br/>';
							}
						}

						if(!empty($conference['course_cancellation_fee'])){
							$fields .= '<div class="logic-block">';
							$fields .= '<h3>'._COURSE_CANCELLATION_FEE.'</h3>';
							$fields .= $conference['course_cancellation_fee'];
							$fields .= '</div><br/>';
						}

						$fields .= $nl.'<div class="logic-block">';
                        $fields .= $nl.'<h3>'._PAYMENT_METHOD.'</h3>';
						$fields .= $nl.'<table width="100%">';
                        $fields .= $nl.'<tbody>';
                        $fields .= $nl.'<tr><td colspan="3" nowrap height="10px"></td></tr>';
                        $fields .= $nl.'<tr><td colspan="3"><h4>'.str_replace("'", "\'", _CREDIT_CARD).'</h4></td></tr>';
                        $fields .= $nl.'<tr>';
                        $fields .= $nl.'	<td width="20%">'._CREDIT_CARD_TYPE.' '.$req_mark.'</td>';
                        $fields .= $nl.'	<td width="2%"> : </td>';
                        $fields .= $nl.'	<td>';
                        $fields .= $nl.'		<select class="my-form-control" name="cc_type">';
                        $fields .= $nl.'		<option value="Visa" '.(($cc_type == 'Visa') ? 'selected="selected"' : '').'>Visa</option>';
                        $fields .= $nl.'		<option value="MasterCard" '.(($cc_type == 'MasterCard') ? 'selected="selected"' : '').'>MasterCard</option>';
                        $fields .= $nl.'		<option value="American Express" '.(($cc_type == 'American Express') ? 'selected="selected"' : '').'>American Express</option>';
                        $fields .= $nl.'		<option value="Discover" '.(($cc_type == 'Discover') ? 'selected="selected"' : '').'>Discover</option>';
                        $fields .= $nl.'		</select>';
                        $fields .= $nl.'	</td>';
                        $fields .= $nl.'</tr>';
                        $fields .= $nl.'<tr>';
                        $fields .= $nl.'	<td>'._CREDIT_CARD_HOLDER_NAME.' '.$req_mark.'</td>';
                        $fields .= $nl.'	<td> : </td>';
                        $fields .= $nl.'	<td><input type="text" class="cc_holder_name" name="cc_holder_name" size="20" maxlength="50" value="'.$cc_holder_name.'" autocomplete="off" /></td>';
                        $fields .= $nl.'</tr>';
                        $fields .= $nl.'<tr>';
                        $fields .= $nl.'	<td>'._CREDIT_CARD_NUMBER.' '.$req_mark.'</td>';
                        $fields .= $nl.'	<td> : </td>';
                        $fields .= $nl.'	<td><input type="text" class="cc_number" name="cc_number" size="20" maxlength="20" value="'.$cc_number.'" autocomplete="off" /></td>';
                        $fields .= $nl.'</tr>';
                        $fields .= $nl.'<tr>';
                        $fields .= $nl.'	<td>'._CREDIT_CARD_EXPIRES.' '.$req_mark.'</td>';
                        $fields .= $nl.'	<td> : </td>';
                        $fields .= $nl.'	<td>';
                        $fields .= $nl.draw_months_select_box('cc_expires_month', $cc_expires_month, 'my-form-control cc_month', false, false);
                        $fields .= $nl.draw_years_select_box('cc_expires_year', $cc_expires_year, 'my-form-control cc_year', false);
                        $fields .= $nl.'	</td>';
                        $fields .= $nl.'</tr>';
                        $fields .= $nl.'<tr><td>'._CVV_CODE.' '.$req_mark.'</td>';
                        $fields .= $nl.'	<td> : </td>';
                        $fields .= $nl.'	<td><input type="text" class="cc_cvv_code" name="cc_cvv_code" size="4" maxlength="4" value="'.$cc_cvv_code.'" autocomplete="off" /> &nbsp;<a href="javascript:void(0)" onclick="javascript:appOpenPopup(\'html/cvv_description.html\')">'._WHAT_IS_CVV.' [?]</a></td>';
                        $fields .= $nl.'</tr>';
                        $fields .= $nl.'<tr><td colspan="3" nowrap height="10px"></td></tr>';

                        $fields .= $nl.'</tbody>';
                        $fields .= $nl.'</table>';
                        $fields .= $nl.'</div>';

                        if($step < 4){
                            Session::Set('conference_step', '4');
                        }
                        break;
                    }
                    
				
				case 3:
					$is_valid = $step >= 3 ? true : false;

                    $image_verification_allow = ModulesSettings::Get('customers', 'image_verification_allow');

                    if($getStep == 3){
                        ///$send_updates = (int)Session::Get('conference_send_updates');
                        $first_name     = Session::Get('conference_first_name');
                        $last_name      = Session::Get('conference_last_name');
                        $birth_date     = Session::Get('conference_birth_date');
                        $company        = Session::Get('conference_company');
                        $b_address      = Session::Get('conference_b_address');
                        $b_address_2    = Session::Get('conference_b_address_2');
                        $b_city         = Session::Get('conference_b_city');
                        $b_zipcode      = Session::Get('conference_b_zipcode');
                        $b_country      = Session::Get('conference_b_country');
                        $b_state        = Session::Get('conference_b_state');
                        $phone          = Session::Get('conference_phone');
                        $fax            = Session::Get('conference_fax');
                        $email          = Session::Get('conference_email');
                        $url            = Session::Get('conference_url');
                        $user_name      = Session::Get('conference_user_name');
                        $user_password1 = Session::Get('conference_user_password1');
                        $agree          = Session::Get('conference_agree');
                        $confirm_email  = $email;
                        $user_password2 = $user_password1;
						$focus_field    = '';
                    }

					if($act == 'send' && $getStep == 3){
						$is_valid = $step >= ($activeStep - 1) ? true : false;

                        if($step < 3){
                            Session::Set('conference_step', '3');
                        }
					}
					
					// Check input data
					if(!$is_valid){
						$activeStep = 2;
					}else{
						$fields .= '<div class="logic-block">';
						$fields .= '<table width="100%">
                            <tbody>			
                                <tr><td colspan="3"><b>'._PERSONAL_DETAILS.'</b><hr size="1" noshade="noshade" /></td></tr>	
                                <tr>
                                    <td width="30%" align="right">'._FIRST_NAME.'</td>
                                    <td><span class="mandatory_star">*</span></td>
                                    <td nowrap="nowrap"><input type="text" id="first_name" name="first_name" size="32" maxlength="32" value="'.$first_name.'" /></td>
                                </tr>
                                <tr>
                                    <td align="right">'._LAST_NAME.'</td>
                                    <td><span class="mandatory_star">*</span></td>
                                    <td nowrap="nowrap"><input type="text" id="last_name" name="last_name" size="32" maxlength="32" value="'.$last_name.'" /></td>
                                </tr>
                                <tr>
                                    <td align="right">'._BIRTH_DATE.'</td>
                                    <td>&nbsp;</td>
                                    <td nowrap="nowrap">
                                        '.draw_date_select_field('birth_date', $birth_date, '90', '0', false).'
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right">'._COMPANY.'</td>
                                    <td>&nbsp;</td>
                                    <td nowrap="nowrap"><input type="text" id="company" name="company" size="32" maxlength="128" value="'.$company.'" /></td>
                                </tr>
                                <tr>
                                    <td align="right">'._WEB_SITE.'</td>
                                    <td>&nbsp;</td>
                                    <td nowrap="nowrap"><input type="text" id="url" name="url" size="32" maxlength="255" value="'.$url.'" /></td>
                                </tr>                        
                                <tr><td colspan="3"><b>'._BILLING_ADDRESS.'</b><hr size="1" noshade="noshade" /></td></tr>		    
                                <tr>
                                    <td align="right">'._ADDRESS.'</td>
                                    <td><span class="mandatory_star">*</span></td>
                                    <td nowrap="nowrap"><input type="text" id="b_address" name="b_address" size="32" maxlength="64" value="'.$b_address.'" /></td>
                                </tr>	
                                <tr>
                                    <td align="right">'._ADDRESS_2.'</td>
                                    <td>&nbsp;</td>
                                    <td nowrap="nowrap"><input type="text" id="b_address_2" name="b_address_2" size="32" maxlength="64" value="'.$b_address_2.'" /></td>
                                </tr>	
                                <tr>
                                    <td align="right">'._CITY.'</td>
                                    <td><span class="mandatory_star">*</span></td>
                                    <td nowrap="nowrap"><input type="text" id="b_city" name="b_city" size="32" maxlength="64" value="'.$b_city.'" /></td>
                                </tr>	
                                <tr>
                                    <td align="right">'._ZIP_CODE.'</td>
                                    <td><!--span class="mandatory_star">*</span--></td>
                                    <td nowrap="nowrap"><input type="text" id="b_zipcode" name="b_zipcode" size="32" maxlength="32" value="'.$b_zipcode.'" /></td>
                                </tr>
                                <tr>
                                    <td align="right">'._COUNTRY.'</td>
                                    <td><span class="mandatory_star">*</span></td>
                                    <td nowrap="nowrap">
                                        '.Countries::DrawAllCountries('b_country', $b_country, true, "appChangeCountry(this.value,'b_state','','".Application::Get('token')."')", false).'
                                    </td>
                                </tr>	
                                <tr>
                                    <td align="right">'._STATE_PROVINCE.'</td>
                                    <td></td>
                                    <td nowrap="nowrap"><input type="text" id="b_state" name="b_state" size="32" maxlength="64" value="'.$b_state.'" /></td>
                                </tr>					

                                <tr><td height="20" colspan="3"><b>'._CONTACT_INFORMATION.'</b><hr size="1" noshade="noshade" /></td></tr>
                                <tr>
                                    <td align="right">'._PHONE.'</td>
                                    <td><span class="mandatory_star">*</span></td>
                                    <td nowrap="nowrap"><input type="text" id="phone" name="phone" size="32" maxlength="32" value="'.$phone.'" /></td>
                                </tr>
                                <tr>
                                    <td align="right">'._FAX.'</td>
                                    <td></td>
                                    <td nowrap="nowrap"><input type="text" id="fax" name="fax" size="32" maxlength="32" value="'.$fax.'" /></td>
                                </tr>
                                <tr>
                                    <td align="right">'._EMAIL_ADDRESS.'</td>
                                    <td><span class="mandatory_star">*</span></td>
                                    <td nowrap="nowrap">				 
                                        <input type="text" id="email" name="email" size="32" maxlength="70" value="'.$email.'" />
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right">'._CONFIRM_EMAIL_ADDRESS.'</td>
                                    <td><span class="mandatory_star">*</span></td>
                                    <td nowrap="nowrap">				 
                                        <input type="text" id="confirm_email" name="confirm_email" size="32" maxlength="70" value="'.$confirm_email.'" />
                                    </td>
                                </tr>';
                        
                            if(0 && $image_verification_allow == 'yes' && in_array($step, array(2, 3))){
                                $fields .= '<tr><td height="20" colspan="3"><b>'._IMAGE_VERIFICATION.'</b><hr size="1" noshade="noshade" /></td></tr>
                                    <tr valign="top">
                                        <td align="left">'._TYPE_CHARS.'</td>
                                        <td></td>
                                        <td>
                                            <table>
                                            <tr>
                                                <td width="50px"><img style="padding:0px; margin:0px;" id="captcha_image" src="modules/captcha/securimage_show.php?sid='.md5(uniqid(time())).'" /></td>
                                                <td>
                                                    <a href="modules/captcha/securimage_play.php"><img style="padding:0px; margin:0px; border:0px;" id="captcha_image_play" src="modules/captcha/images/audio_icon.gif" title="'._PLAY.'" alt="'._PLAY.'" /></a><br />
                                                    <img style="cursor:pointer; padding:0px; margin:0px; border:0px;" id="captcha_image_reload" src="modules/captcha/images/refresh.gif" style="cursor:pointer;" onclick="document.getElementById(\'captcha_image\').src = \'modules/captcha/securimage_show.php?sid=\' + Math.random(); appSetFocus(\'captcha_code\'); return false;" title="'._REFRESH.'" alt="'._REFRESH.'" />
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    <input type="text" name="captcha_code" id="captcha_code" size="21" value="" />
                                                </td>
                                            </tr>
                                            </table>			    			    
                                        </td>
                                    </tr>';
                            }
                            $fields .= '<tr><td colspan="3" nowrap height="7px"></td></tr>';

                            if(in_array($step, array(2, 3))){                    
								$fields .= '<tr><td colspan="3" nowrap="nowrap" height="5px"></td></tr>
										<tr valign="middle">
											<td align="right"><input type="checkbox" name="agree" id="agree" value="1" '.(($agree == '1') ? 'checked="checked"' : '').'></td>
											<td>&nbsp;</td>
											<td>';
								$objPageTemp = new Pages('terms_and_conditions', true);				
								$page_text = $objPageTemp->DrawText(false);
								if(!empty($page_text)){						
									$fields .= '<a href="index.php?page=conference_registration&step='.$getStep.'&cid='.$conference_id.'#frmConferenceSpecialty" onclick="javascript:appShowTermsAndConditions()">'._AGREE_CONF_TEXT.'</a>
									<div id="fade" class="black_overlay" onclick="javascript:appCloseTermsAndConditions();"></div>
									<div id="light">
										<div class="white_header">
											<div class="title_left">'.$objPageTemp->DrawTitle('', false).'</div>
											<div class="title_right"><a href="javascript:void(0)" onclick="javascript:appCloseTermsAndConditions();">'._CLOSE.'</a></div>			
										</div>
										<div class="white_content">'.$page_text.'</div>
									</div>';		
								}else{
									$fields .= _AGREE_CONF_TEXT;	
								}					
								$fields .= '</td>
										</tr>';
                            }
                            $fields .= '</table>
                                    </td>
                                </tr>		
                            </tbody>
						</table>
						<script type="text/javascript">
							appSetFocus("'.$focus_field.'");
							appChangeCountry(jQuery("#b_country").val(),\'b_state\',\''.decode_text($b_state).'\',\''.Application::Get('token').'\');
						</script>';
						$fields .= '</div>';

                        if(in_array($step, array(2, 3))){
						    $text_button_next = _SUBMIT;
                        }

						break;
					}

				case 2:
					$is_valid = $step >= 2 ? true : false;

					$arr_specialties = Conferences::GetSpecialities();
					$conference_speciality = Session::Get('conference_speciality');
					// Get input data
					if($act == 'send' && $getStep == 2){
						$conference_speciality = !empty($_POST['conference_speciality']) ? prepare_input($_POST['conference_speciality']) : '';
						if(!empty($conference_speciality)){
							$is_valid = isset($arr_specialties[$conference_speciality]) ? true : false;
							if($is_valid){
								Session::Set('conference_speciality', $conference_speciality);
								if($step < 2){
									Session::Set('conference_step', '2');
								}
							}else{
								$alert = 'Invalid data';
								$alert_type = 'error';
							}
						}else{
							$is_valid = false;
							$alert = str_replace('_FIELD_', '<b>'._SPECIALTY.'</b>', _FIELD_CANNOT_BE_EMPTY);
							$alert_type = 'error';
						}
					}
					// Check input data
					if(!$is_valid){
						$activeStep = 1;
					}else{
						// Prepare fields
						$fields .= '<div class="logic-block">';
						$fields .= '<p class="paragraph">You indicated that you are a '.$arr_specialties[$conference_speciality].'.</p>
						<p class="paragraph">Is this correct?</p>';
						$text_button_next = _YES;
						$text_button_previous = _NO;
						$fields .= '</div>';
						break;
					}

				case 1:
					$selected_speciality = Session::Get('conference_speciality');
					$arr_specialties = array(''=>_SELECT) + Conferences::GetSpecialities();

					// Prepare fields
					$fields .= '<div class="logic-block">';
					$fields .= '<label class="i-am">'._I_AM_A.':</label>';
					$fields .= '<select class="form-control" name="conference_speciality">';
					foreach($arr_specialties as $key => $speciality){
						$fields .= '<option value="'.htmlentities($key).'"'.(!empty($selected_speciality) && $selected_speciality == $key ? ' selected="selected"' : '').'>'.$speciality.'</option>';
					}
					$fields .= '</select>';
					$fields .= '</div>';
					break;
			}
		}else{
			redirect_to('index.php?page=conference_general&cid='.$conference_id);
		}
	}else{
		redirect_to('index.php?page=conferences');
	}
}

if(empty($alert) && Session::Get('conference_alert') != '' && Session::Get('conference_alert_type') != ''){
	$alert = Session::Get('conference_alert');
	$alert_type = Session::Get('conference_alert_type');
	Session::Set('conference_alert', '');
	Session::Set('conference_alert_type', '');
}

if(!empty($alert_type) && !empty($alert)){
	$alert = strip_tags($alert);
	if($alert_type == 'success'){
		$action_message = draw_success_message($alert, false);
	}elseif($alert_type == 'warning'){
		$action_message = draw_message($alert, false);
	}elseif($alert_type == 'error'){
		$action_message = draw_important_message($alert, false);
	}else{
		$action_message = draw_default_message($alert, false);
	}
}
