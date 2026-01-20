<?php

/*
 * This file is the route of channel manager.
 * It is included in main controller of the application (index.php) and checks the active page.
 * If the active page is admin==mod_room_availability || admin==mod_room_prices || cm_listener==reservation_notification it performs actions
 * 
 * The index.php?cm_listener=reservation_notification is a new script where channel manager posts data when:
 *  1. A customer places a new booking through any channel.
 *  2. A customer modificates his older booking through channel manager. In some channels he can change the checkin-checkout dates.
 *  3. A customer cancels his older booking.
 * 
 * 
 * I saw that you created a function UpdateRoomsAvailability in Bookings.class.php and i added some functionality there. 
 * I left some comments there too.
 * You need to test it with your functionality too.
 * 
 * 
 * In database i altered:
 *  -- 4 fields in prefix_hotels:
 *      cm_enabled (0,1) 0=disabled, 1=enabled
 *      cm_auth_key (the auth_key for authorization to our channel manager)
 *      cm_api_key (the api_key for authentication to our channel manager)
 *      cm_hotel_id (the hotel ID in channel manager)
 * 
 *  -- 1 field in prefix_rooms:
 *      cm_room_id (the ID of the room in channel manager)
 */

if (Application::Get('admin') != '' && Application::Get('admin') == 'mod_room_availability') {
    //If the hotel admin updates the availability of a room through uHB
    require_once('modules/additional/channel_manager/cm_mod_room_availability.php');
} elseif (Application::Get('admin') != '' && Application::Get('admin') == 'mod_room_prices') {
    //If the hotel admin updates the prices of a room through uHB
    require_once('modules/additional/channel_manager/cm_mod_room_prices.php');
}
