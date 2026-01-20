<?php
/*
 * Changes rooms availability in Channel Manager
 * after manually changing rooms availability on the website
 *
 */

// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------

if(Modules::IsModuleInstalled('channel_manager') && (ModulesSettings::Get('channel_manager', 'is_active') != 'no')){

	//Check if all required data are POSTed
	if (isset($_POST['rid']) && $_POST['rid'] != '' && isset($_POST['task']) && $_POST['task'] == 'update' && $_POST['ids_list']) {
		//Check if channel manager is enabled and all required data are not missing
		$room_id = (int)$_POST['rid'];

		// Get hotel room data
		$hotel_result = ChannelHotelRooms::GetHotelRoomData($room_id);

		if (!empty($hotel_result[1]) && $hotel_result[0]['cm_enabled'] == '1' && $hotel_result[0]['cm_auth_key'] != '' && $hotel_result[0]['cm_api_key'] != '' && $hotel_result[0]['cm_hotel_id'] != '' && $hotel_result[0]['cm_room_id'] != '') {
			//Store these data to send HTTP request to channel manager
			$cm_auth_key = $hotel_result[0]['cm_auth_key'];
			$cm_api_key = $hotel_result[0]['cm_api_key'];
			$cm_hotel_id = $hotel_result[0]['cm_hotel_id'];
			$cm_room_id = $hotel_result[0]['cm_room_id'];


			$ids_list = isset($_POST['ids_list']) ? prepare_input($_POST['ids_list']) : '';
			$ids_list_array = explode(',', $ids_list);

			$year = !empty($_POST['year']) ? $_POST['year'] : '';
			if ($year == 'current') {
				//$current_month is used to minify the year's data. If is current year, no need to update past months.
				//This happens because the admin has the opportunity to update all year availabilities for a room.
				$custom_year = date('Y');
				$current_month = ltrim(date('m'), '0');
			} elseif ($year == 'next') {
				$custom_year = date('Y', strtotime('+1 year'));
				$current_month = 1;
			} else {
				die('wrong year');
			}

			//The posted availability data must be a json array with date ranges from,to and the availability value, for the given room.
			//Below you can check the schema of the array.
			$month = 1;
			$counter = 0;
			$aval = array();

			foreach ($ids_list_array as $key) {
				//Check if month is equal/greater than current month. This happeps to minify the posted availability if in current year some months past.
				if ($month >= $current_month) {
					if ($month < 10) {
						$custom_month = '0' . $month;
					} else {
						$custom_month = $month;
					}

					for ($day = 1; $day <= 31; $day ++) {
						if ($day < 10) {
							$custom_day = '0' . $day;
						} else {
							$custom_day = $day;
						}

						$availability_value = isset($_POST['aval_' . $key . '_' . $day]) ? $_POST['aval_' . $key . '_' . $day] : '0';

						$aval['availability']['date_ranges'][$counter]['from'] = $custom_year . '-' . $custom_month . '-' . $custom_day;
						$aval['availability']['date_ranges'][$counter]['to'] = $custom_year . '-' . $custom_month . '-' . $custom_day;
						$aval['availability']['date_ranges'][$counter]['availability'] = $availability_value;
						$counter++;
					}
				}
				$month++;
			}

			//POST all the required data to https://cmapp.hotelook.gr/api/rooms/multi_data
			$availability_json = json_encode($aval);
			$data = array(
				'auth_key' => $cm_auth_key,
				'api_key' => $cm_api_key,
				'hotel_id' => $cm_hotel_id,
				'room_id' => $cm_room_id,
				'availability' => $availability_json
			);

            $sent_status = true;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://cmapp.hotelook.gr/api/rooms/multi_data');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			$output = curl_exec($ch);
            $log_error = curl_error($ch);

			// Add log record
			// -------------------------------------------
            $log_subject = 'Changing room availability';
            if(!empty($log_error)){
				$sent_status = false;
                $log_status_description = 'Error sending data to the server: '.$log_error;
            }

			if($sent_status){
				$result_to_xml = new SimpleXMLElement($output);
				$result_status = (string)$result_to_xml->status;
				$result_message = (string)$result_to_xml->message;
				$result_error = (string)$result_to_xml->error;

				if($result_status == 'true'){
					$log_status_description = 'Operation completed successfully';
				}else{
					$error_message = $result_message != '' ? $result_message : $result_error;
                    $sent_status = false;
					$log_status_description = $error_message;
				}
			}
			$log_content = 'Data sent: ';
			$log_content .= '<pre style=min-width:800px;width:100%;>';
            $log_content .= 'Hotel ID - '.$cm_hotel_id."\r\n";
			$log_content .= 'Room ID - '.$cm_room_id."\r\n";
			$log_content .= str_replace(' ', '', print_r($aval, 1));			
			$log_content .= '</pre>';

			if($sent_status && $result_status == 'true'){
				draw_success_message('Room availability have been successfully updated in Channel Manager');	
			}else{
				draw_important_message('An error occurred while updating room availability in Channel Manager. See <a href="index.php?admin=mod_channel_manager_log" target="_blank" rel="noopener noreferrer">Channel Manager Log</a> for more details.');
			}

            ChannelManagerLog::LogAddRecord($hotel_result[0]['hotel_id'], $hotel_result[0]['room_id'], $log_subject, $sent_status == true ? 'OK' : 'ERROR', $log_status_description, $log_content);

			curl_close($ch);
		}
	}
}
