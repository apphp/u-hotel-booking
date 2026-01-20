<?php

// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------

if(Modules::IsModuleInstalled('channel_manager') && (ModulesSettings::Get('channel_manager', 'is_active') != 'no')){

    /*
     * When a final customer creates/modificates/cancels a booking through any channel like booking.com, expedia etc..., 
     * our system posts to a given URL the changes the below data
     * 
     * All fields have always data. 
     *  1. propertyid is the hotel_id
     *  2. reservationid is the booking_number
     *  3. site is the name of the channel
     *  4. siteid is the ID of the channel
     *  5. status is the new status of the reservation. It can be new,modification,cancellation.
     * 
     * The notification just sends those data to notify. 
     * You need to send another HTTP Request to get the details of the booking, with the reservationid we've sent you
     * 
     * 
     * In order to perform some test bookings, i will give you access to the channel manager GUI where you can add manually a reservation.
     * 
     * 
     *          ** ! ** IMPORTANT ** ! **
     *                OUR SYSTEM WAITS FOR A SIMPLE echo 'success'; IN ORDER TO BE COMPLETED. 
     *                AFTER ALL THE FUNCTIONALITY BELOW, YOU NEED TO DIE() IT AND ECHO 'success' IF EVERYTHING IS OK
     *                OTHERWISE IT WILL TRY 10 MORE TIMES UNTIL YOU ECHO 'success'
     *          ** ! ** IMPORTANT ** ! **
     */

    // GET POSTED DATA reservation id, hotel id, site, siteid, status FROM CHANNEL MANAGER
    $property_id	= isset($_POST['propertyid']) ? trim($_POST['propertyid']) : '';
    $reservation_id = isset($_POST['reservationid']) ? trim($_POST['reservationid']) : '';
    $site 			= isset($_POST['site']) ? trim($_POST['site']) : '';
    $siteid 		= isset($_POST['siteid']) ? trim($_POST['siteid']) : '';
    $status 		= isset($_POST['status']) ? trim($_POST['status']) : '';
	$apphp_hotel_id	= -1;
	$apphp_room_id	= -1;

	$log_content = '
		Hotel ID: '.($property_id ? $property_id : '- missing -').'<br>
		Reservation ID: '.($reservation_id ? $reservation_id : '- missing -').'<br>
		Site: '.($site ? $site : '- missing -').'<br>
		Site ID: '.($siteid ? $siteid : '- missing -').'<br>
		Action: '.($status ? $status : '- missing -').'
	';

    // CHECK POSTED DATA. ALL DATA MUST HAVE VALUES
    if($property_id != '' && $reservation_id != '' && $site != '' && $siteid != '' && $status != ''){

		$receive_status = true;
		$log_subject = 'Octorate Notification<br>('.$status.')';
		$log_status_description = '';

        // Get hotel data
        $hotel_result = ChannelHotels::GetHotelData($property_id);

        // CHECK IF HOTEL HAS CHANNEL MANAGER ENABLED
        if($hotel_result[0]['cm_enabled'] == '1' && $hotel_result[0]['cm_auth_key'] != '' && $hotel_result[0]['cm_api_key'] != '' && $hotel_result[0]['cm_hotel_id'] != '') {
			
			// Save website hotel ID
			$apphp_hotel_id = $hotel_result[0]['hotel_id'];

            /*
             *      ** ! ** IMPORTANT ** ! **
             *          This echo 'success'; must be placed only once, if everything is ok.
             *          For tests i placed it here so we dont have to send again and again the same reservation notification
             *      ** ! ** IMPORTANT ** ! **
             */
            echo 'success';
            /*
             *      ** ! ** IMPORTANT ** ! **
             *          This echo 'success'; must be placed only once, if everything is ok.
             *          For tests i placed it here so we dont have to send again and again the same reservation notification
             *      ** ! ** IMPORTANT ** ! **
             */



            /*
             * Get the booking details of the reservation with ID, the posted reservationid
             * This is a GET HTTP Request.
             */
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,
				'https://cmapp.hotelook.gr/api/bookings/get_booking_by_id?auth_key='.$hotel_result[0]['cm_auth_key'].
				'&api_key='.$hotel_result[0]['cm_api_key'].
				'&hotel_id='.$hotel_result[0]['cm_hotel_id'].
				'&reservation_id='.$reservation_id
			);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $reservation = curl_exec($ch);

            $booking = new SimpleXMLElement($reservation);

            /*
             * Use SimpleXMLElement to parse the booking details
             * You can print_r | var_dump the $booking to check all the posted data.
             * Some fields are not sent, regarding some channels. For example expedia sends only first_name, last_name of client but booking.com sends more information.
             */

            // Check if status=='true'. Status can be false if something is wrong.
                        
            if(isset($booking->status) && $booking->status == 'true'){
                // Store the XML nodes to further use
                $booking_info = $booking->bookings->booking;
                $client_info = $booking_info->customers->customer;
                $rooms = $booking_info->rooms;
                
                // IF RESERVATION IS NEW ADD THE CLIENT. IN MODIFICATION AND CANCELLATION CLIENT IS ALREADY INSERTED
                if($status == 'new'){
                    //Store client data and insert in database
                    $client_data['first_name'] = $client_info->first_name;
                    $client_data['last_name'] = $client_info->last_name;
                    $client_data['email'] = $client_info->email;
                    $client_data['phone'] = $client_info->phone;
                    if($client_info->city != ''){
                        $client_data['city'] = $client_info->city;
                    }else{
                        $client_data['city'] = $client_info->country;
                    }

					$sql = "SELECT * FROM ".TABLE_CUSTOMERS." WHERE email = '".$client_data['email']."'";
					$result = database_query($sql, DATA_AND_ROWS);
					if($result[1] > 0){
						// UPDATE CLIENT'S DATA
						$sql = "UPDATE ".TABLE_CUSTOMERS." SET
								first_name = '".$client_data['first_name']."',
								last_name = '".$client_data['last_name']."'
								".(!empty($client_data['city']) ? ", b_city = '".$client_data['city']."'" : '')."
								".(!empty($client_data['phone']) ? ", phone = '".$client_data['phone']."'" : '')."
								
							WHERE id = '".$result[0][0]['id']."'";
							
						if(database_void_query($sql) > 0){
							$all_info['client_id'] = $result[0][0]['id'];
						}
					}else{
						// ADD NEW CLIENT
						$sql = "INSERT INTO ".TABLE_CUSTOMERS." (first_name, last_name, b_city, phone, email,date_created)
							VALUES(
								'".$client_data['first_name']."',
								'".$client_data['last_name']."',
								'".$client_data['city']."',
								'".$client_data['phone']."',
								'".$client_data['email']."',
								'".date('Y-m-d H:i:s')."')";
	
						if(database_void_query($sql) > 0){
							$all_info['client_id'] = database_insert_id();
						}
					}
                }

                $all_info['booking_from'] = $booking_info->channel_from;
                $all_info['booking_number'] = $booking_info->reservation_id;
                $all_info['additional_info'] = $client_info->note;

                //If the client has booked more than 1 room
                foreach($rooms as $eachroom){
                    $eachroom = $eachroom->room;
                    //number of rooms for a room type booked
                    $number_of_rooms = $eachroom->units;
                    $checkin = $eachroom->checkin;
                    $checkout = $eachroom->checkout;

                    $all_info['total_price'] = $eachroom->price;
                    $all_info['adults'] = $eachroom->pax;
                    $all_info['status'] = 3;

                    if($eachroom->refundable == 'Yes'){
                        $all_info['refund'] = 'refundable';
                    }else{
                        $all_info['refund'] = 'non_refundable';
                    }

                    //get room_id from uHB via cm_room_id
                    $room_sql = 'SELECT * FROM '.DB_PREFIX.'channel_hotel_rooms r WHERE r.cm_room_id='.$eachroom->room_type->room_id;
                    $room_result = database_query($room_sql, ALL_ROWS);                    
                    $apphp_room_id = $room_result[0]['room_id'];
                                        
                    if($status == 'new'){
                        //If status is new insert the booking to TABLE_BOOKINGS and TABLE_BOOKINGS_ROOMS
                        $booking_sql = 'INSERT INTO '.TABLE_BOOKINGS.'(booking_number,booking_description,customer_id,order_price,payment_sum,created_date,status)
						VALUES(
							\''.$all_info['booking_number'].'\', \''.$all_info['additional_info'].'\',
							\''.$all_info['client_id'].'\', \''.$all_info['total_price'].'\',
							\''.$all_info['total_price'].'\', \''.date('Y-m-d H:i:s').'\', \''.$all_info['status'].'\')';
                        if(database_void_query($booking_sql)){
                            $booking_room_sql = 'INSERT INTO '.TABLE_BOOKINGS_ROOMS.'(booking_number,hotel_id,room_id,checkin,checkout,adults,rooms)
						VALUES(
							\''.$all_info['booking_number'].'\', \''.$hotel_result[0]['hotel_id'].'\',
							\''.$room_result[0]['id'].'\', \''.$checkin.'\',
							\''.$checkout.'\', \''.$all_info['adults'].'\', \''.$number_of_rooms.'\')';
                            
							if(database_void_query($booking_room_sql)){
                                $checkin_array = explode('-', $checkin);
                                $checkin_date = $checkin_array[2].'-'.$checkin_array[1].'-'.$checkin_array[0];
                                $checkin_str = strtotime($checkin_date);

                                $checkout_array = explode('-', $checkout);
                                $checkout_date = $checkout_array[2].'-'.$checkout_array[1].'-'.$checkout_array[0];
                                $checkout_str = strtotime($checkout_date);

                                /*
                                 * FOR EACH DAY FROM CHECKIN TO CHECKOUT DECREASE AVAILABILITY FOR NUMBER OF ROOMS
                                 * In channel manager availability is already decreased, before the reservation notification. 
                                 * You dont have to decrease the availability to channel manager again. Only decrease it in uHB
                                 */
                                while ($checkin_str < $checkout_str) {
                                    $y = date('Y', $checkin_str);
                                    if($y == date('Y')){
                                        $y = 0;
                                    }else{
                                        $y = 1;
                                    }
                                    $m = date('m', $checkin_str);
                                    $d = date('d', $checkin_str);

                                    $m = ltrim($m, '0');
                                    $m = (int) $m;

                                    $d = ltrim($d, '0');
                                    $d = (int) $d;

                                    $availability_sql = 'UPDATE '.TABLE_ROOMS_AVAILABILITIES.' SET d'.$d.'=d'.$d.'-'.$number_of_rooms.' WHERE room_id='.$apphp_room_id.' AND y='.$y.' AND m='.$m;
                                    database_void_query($availability_sql);
                                    $checkin_str = strtotime('+1 day', $checkin_str);
                                }
                            }
                        }
                    }elseif($status == 'modification'){
                        /*
                         * Some channels allows to the customer to change checkin and checkout dates.
                         * In that case we have to increase the availability of the previous booking and decrease the availability for the new bookings
                         * You can do some tests here if the old checkin-checkout is different of the new checkin-checkout of booking
                         * 
                         */

                        $prev_booking_sql = "SELECT * FROM ".TABLE_BOOKINGS_ROOMS." WHERE booking_number='".$reservation_id."'";
                        $prev_booking_result = database_query($prev_booking_sql);

                        $checkin_array = explode('-', $prev_booking_result[0]['checkin']);
                        $checkin_date = $checkin_array[2].'-'.$checkin_array[1].'-'.$checkin_array[0];
                        $checkin_str = strtotime($checkin_date);

                        $checkout_array = explode('-', $prev_booking_result[0]['checkout']);
                        $checkout_date = $checkout_array[2].'-'.$checkout_array[1].'-'.$checkout_array[0];
                        $checkout_str = strtotime($checkout_date);

                        while ($checkin_str < $checkout_str) {
                            $y = date('Y', $checkin_str);
                            if($y == date('Y')){
                                $y = 0;
                            }else{
                                $y = 1;
                            }
                            $m = date('m', $checkin_str);
                            $d = date('d', $checkin_str);

                            $m = ltrim($m, '0');
                            $m = (int) $m;

                            $d = ltrim($d, '0');
                            $d = (int) $d;

                            $prev_availability_sql = 'UPDATE '.TABLE_ROOMS_AVAILABILITIES.' SET d'.$d.'=d'.$d.'+'.$number_of_rooms.' WHERE room_id='.$apphp_room_id.' AND y='.$y.' AND m='.$m;
                            database_void_query($prev_availability_sql);
                            $checkin_str = strtotime('+1 day', $checkin_str);
                        }

                        $update_sql = "UPDATE ".TABLE_BOOKINGS_ROOMS." SET checkin='".$checkin."', checkout='".$checkout."' WHERE booking_number='".$reservation_id."'";
                        database_query($update_sql);

                        //DECREASE NEW BOOKING AVAILABILITY
                        $checkin_array = explode('-', $checkin);
                        $checkin_date = $checkin_array[2].'-'.$checkin_array[1].'-'.$checkin_array[0];
                        $checkin_str = strtotime($checkin_date);

                        $checkout_array = explode('-', $checkout);
                        $checkout_date = $checkout_array[2].'-'.$checkout_array[1].'-'.$checkout_array[0];
                        $checkout_str = strtotime($checkout_date);

                        while ($checkin_str < $checkout_str) {
                            $y = date('Y', $checkin_str);
                            if($y == date('Y')){
                                $y = 0;
                            }else{
                                $y = 1;
                            }
                            $m = date('m', $checkin_str);
                            $d = date('d', $checkin_str);

                            $m = ltrim($m, '0');
                            $m = (int) $m;

                            $d = ltrim($d, '0');
                            $d = (int) $d;

                            $new_availability_sql = 'UPDATE '.TABLE_ROOMS_AVAILABILITIES.' SET d'.$d.'=d'.$d.'-'.$number_of_rooms.' WHERE room_id='.$apphp_room_id.' AND y='.$y.' AND m='.$m;
                            database_void_query($new_availability_sql);
                            $checkin_str = strtotime('+1 day', $checkin_str);
                        }
                    }elseif($status == 'cancellation'){
                        //If is cancellation, change the status of the booking and increase the availability of the room
                        $booking_sql = "UPDATE ".TABLE_BOOKINGS." SET status=6 WHERE booking_number='".$reservation_id."'";
                        if(database_void_query($booking_sql)){
                            $checkin_array = explode('-', $checkin);
                            $checkin_date = $checkin_array[2].'-'.$checkin_array[1].'-'.$checkin_array[0];
                            $checkin_str = strtotime($checkin_date);

                            $checkout_array = explode('-', $checkout);
                            $checkout_date = $checkout_array[2].'-'.$checkout_array[1].'-'.$checkout_array[0];
                            $checkout_str = strtotime($checkout_date);

                            while ($checkin_str < $checkout_str) {
                                $y = date('Y', $checkin_str);
                                if($y == date('Y')){
                                    $y = 0;
                                }else{
                                    $y = 1;
                                }
                                $m = date('m', $checkin_str);
                                $d = date('d', $checkin_str);

                                $m = ltrim($m, '0');
                                $m = (int) $m;

                                $d = ltrim($d, '0');
                                $d = (int) $d;

                                $availability_sql = 'UPDATE '.TABLE_ROOMS_AVAILABILITIES.' SET d'.$d.'=d'.$d.'+'.$number_of_rooms.' WHERE room_id='.$apphp_room_id.' AND y='.$y.' AND m='.$m;
                                database_void_query($availability_sql);
                                $checkin_str = strtotime('+1 day', $checkin_str);
                            }
                        }
                    }
                }
				
				$log_status_description = 'Operation completed successfully';
            }else{
				$log_status_description = 'Error in operation. Status: '.$booking->status;
			}			
        }
    }else{
		$receive_status = false;
		$log_subject = 'Octorate Notification';
		$log_status_description = 'Wrong parameters received from Octorate';
	}

	ChannelManagerLog::LogAddRecord($apphp_hotel_id, $apphp_room_id, $log_subject, $receive_status == true ? 'OK' : 'ERROR', $log_status_description, $log_content);

}