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

if($objLogin->IsLoggedInAsCustomer()){

    draw_title_bar(prepare_breadcrumbs(array(_GENERAL=>'',_CUSTOMER_PANEL=>'')));

	draw_content_start();

		Campaigns::DrawCampaignBanner('standard');
		if(GLOBAL_CAMPAIGNS == 'enabled'){
			Campaigns::DrawCampaignBanner('global');
		}
		
		Bookings::NotifyCustomerAfterStayed();
		$msg = '<div style="padding:9px;min-height:250px">';
        $welcome_text = ($objLogin->GetCustomerType() == 1) ? _WELCOME_AGENCY_TEXT : _WELCOME_CUSTOMER_TEXT;
        $welcome_text = str_replace('_FIRST_NAME_', $objLogin->GetLoggedFirstName(), $welcome_text);
		$welcome_text = str_replace('_LAST_NAME_', $objLogin->GetLoggedLastName(), $welcome_text);
        $welcome_text = str_replace('_TODAY_', _TODAY.': <b>'.format_datetime(@date('Y-m-d H:i:s'), '', '', true).'</b>', $welcome_text);
		$welcome_text = str_replace('_LAST_LOGIN_', _LAST_LOGIN.': <b>'.format_datetime($objLogin->GetLastLoginTime(), '', _NEVER, true).'</b>', $welcome_text);


		$user_id = (int)$objLogin->GetLoggedID();
		$result = Bookings::GetLastBooking(1, TABLE_BOOKINGS.'.customer_id = '.$user_id);
		if($result[1] > 0){
			$unix_time_created = strtotime($result[0][0]['created_date']);
			$href = prepare_link(FLATS_INSTEAD_OF_HOTELS ? 'flats' : 'hotels', FLATS_INSTEAD_OF_HOTELS ? 'fid' : 'hid', $result[0][0]['hotel_id'], $result[0][0]['hotel_name'], $result[0][0]['hotel_name'], '', _CLICK_TO_VIEW, true);
			$count_hours = (int)((time() - $unix_time_created) / 3600);
			if($count_hours < 24){
				$type_booking = $count_hours < 1 ? _JUST_BOOKED : ($count_hours > 1 ? $count_hours.' '._HOURS : _HOUR).' '._AGO;
			}else{
				$count_days = (int)($count_hours / 24);
				if($count_days < 366){
					$type_booking = strtolower(($count_days == 1 ? '1 '._DAY : $count_days.' '._DAYS).' '._AGO);
				}else{
					$type_booking = _MORE_YEAR_AGO;
				}
			}
			$count_rooms = (FLATS_INSTEAD_OF_HOTELS ? '' : ($result[0][0]['rooms_count'] > 1 ? $result[0][0]['rooms_count'].' '._ROOMS : _ROOM));
			$hotel_name = '<a href="'.$href.'" class="dark">'.$result[0][0]['hotel_name'].'</a>';
			$link_more = '<a href="index.php?customer=my_bookings">'._MORE.'...</a>';
			$message = str_replace(array('{type_booking}', '{count_rooms}', '{hotel_name}', '{hotel_location}'), array($type_booking, $count_rooms, $hotel_name, $result[0][0]['location_name']), _MY_LAST_BOOKINGS_MESSAGE);
			$welcome_text .= '<b>'._YOUR_LAST_BOOKING_WAS.': '.$message.' '.$link_more.'</b><br/>';
		}
	
		if(Reviews::CheckNewReviewsAfterStayed($user_id)){
			$welcome_text .= '<b>'._YOU_CAN_REVIEW.' <a href="index.php?customer=my_reviews&mg_action=add">'._ADD_REVIEW.'...</a></b><br/>';
		}

		if($objLogin->GetCustomerType() == 1 && $objLogin->GetAgencyLogo()){
			$msg .= '<img width="100px" style="float:right;" src="images/travel_agencies/'.$objLogin->GetAgencyLogo().'" alt="" />';
		}
		
        $msg .= $welcome_text;
        $msg .= '</div>';
		
		draw_message($msg, true, false);
	
	draw_content_end();		

}elseif($objLogin->IsLoggedIn()){
    draw_title_bar(prepare_breadcrumbs(array(_GENERAL=>'')));
    draw_important_message(_NOT_AUTHORIZED);
}else{
    draw_title_bar(prepare_breadcrumbs(array(_CUSTOMERS=>'')));
    draw_important_message(_MUST_BE_LOGGED);
}
