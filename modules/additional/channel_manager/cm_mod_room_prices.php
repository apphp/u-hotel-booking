<?php

// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------

if(Modules::IsModuleInstalled('channel_manager') && (ModulesSettings::Get('channel_manager', 'is_active') != 'no')){

	if (isset($_POST['rid']) && $_POST['rid'] != '' && isset($_POST['task']) && ($_REQUEST['task'] == 'add_new' || $_REQUEST['task'] == 'update' || $_REQUEST['task'] == 'delete' || $_REQUEST['task'] == 'add_default_periods' || $_REQUEST['task'] == 'mod_room_prices')) {
		$room_id = (int) $_POST['rid'];

        $hotel_result = ChannelHotelRooms::GetHotelRoomData($room_id);

        if(!empty($hotel_result[1]) && $hotel_result[0]['cm_enabled'] == '1' && $hotel_result[0]['cm_auth_key'] != '' && $hotel_result[0]['cm_api_key'] != '' && $hotel_result[0]['cm_hotel_id'] != '' && $hotel_result[0]['cm_room_id'] != '') {
			$cm_auth_key = $hotel_result[0]['cm_auth_key'];
			$cm_api_key  = $hotel_result[0]['cm_api_key'];
			$cm_hotel_id = $hotel_result[0]['cm_hotel_id'];
			$cm_room_id  = $hotel_result[0]['cm_room_id'];

			// Returns prices from today + one year
            $arr_room_prices = ChannelManager::GetArrRoomPrices($hotel_result[0]['hotel_id'], $room_id);			

            $room_price_info = array('price'=>array('date_ranges'=>array()));

            $counter = 0;
			$arr_price_date = array();
            foreach ($arr_room_prices as $current_date => $room_price) {
				$arr_price_date['from']  = $current_date;
				$arr_price_date['to']    = $current_date;
				$arr_price_date['price'] = $room_price;

                $room_price_info['price']['date_ranges'][$counter++] = $arr_price_date;
            }

            $price_json = json_encode($room_price_info);
            $data = array(
                'auth_key' => $cm_auth_key,
                'api_key'  => $cm_api_key,
                'hotel_id' => $cm_hotel_id,
                'room_id'  => $cm_room_id,
                'price'    => $price_json
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
			$log_subject = 'Changing room prices';
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
			$log_content .= str_replace(' ', '', print_r($room_price_info, 1));			
			$log_content .= '</pre>';
			
			if($sent_status && $result_status == 'true'){
				draw_success_message('Room prices have been successfully updated in Channel Manager');	
			}else{
				draw_important_message('An error occurred while updating room prices in Channel Manager. See <a href="index.php?admin=mod_channel_manager_log" target="_blank" rel="noopener noreferrer">Channel Manager Log</a> for more details.');
			}
			
            ChannelManagerLog::LogAddRecord($hotel_result[0]['hotel_id'], $hotel_result[0]['room_id'], $log_subject, $sent_status == true ? 'OK' : 'ERROR', $log_status_description, $log_content);
			
            curl_close($ch);			
        }
	}
}
