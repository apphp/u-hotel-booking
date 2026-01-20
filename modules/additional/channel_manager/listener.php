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

if (isset($_GET['cm_listener']) && $_GET['cm_listener'] == 'reservation_notification') {
    //If a new/modification/cancellation booking happened from channel manager.
    //This URL is the listener url, where we store it in our system to send the reservation notifications from channel manager. 
    //For each client we need to change the domain, but the controller must stay the same (index.php?cm_listener=reservation_notification)
    require_once('modules/additional/channel_manager/cm_reservation_notification.php');
    die();
}
