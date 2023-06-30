-- 08/10/2017 --
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FLATS_ADMIN_WELCOME_TEXT', '<p>Welcome to Administrator Control Panel that allows you to add, edit or delete site content. With this Administrator Control Panel you can easy manage customers, reservations and perform a full hotel site management.</p><p><b>&#8226;</b> There are some modules for you: Backup & Restore, News. Installation or un-installation of them is possible from <a href=''index.php?admin=modules''>Modules Menu</a>.</p><p><b>&#8226;</b> In <a href=''index.php?admin=languages''>Languages Menu</a> you may add/remove language or change language settings and edit your vocabulary (the words and phrases, used by the system).</p><p><b>&#8226;</b> <a href=''index.php?admin=settings''>Settings Menu</a> allows you to define important settings for the site.</p><p><b>&#8226;</b> In <a href=''index.php?admin=my_account''>My Account</a> there is a possibility to change your info.</p><p><b>&#8226;</b> <a href=''index.php?admin=menus''>Menus</a> and <a href=''index.php?admin=pages''>Pages Management</a> are designed for creating and managing menus, links and pages.</p><p><b>&#8226;</b> To create and edit room types, seasons, prices, bookings and other flats info, use <a href=''index.php?admin=hotels_info''>Flats Management</a> and <a href=''index.php?admin=mod_booking_bookings''>Bookings</a> menus.</p>' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FLATS_AGENT_COMMISION', 'Flats Owner/Agent Commision' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FLATS_ALERT_SAME_HOTEL_ROOMS', 'You can add only one flats to this basket!' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_COUPON_FOR_SINGLE_FLAT_ALERT', 'This discount coupon can be applied only for single flat!' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FLATS_DIRECT_RESERVATION', 'Direct reservation with flats, No booking fees' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FLATS_DISCOUNT_CAMPAIGN_TEXT', '<span class=campaign_header>Super discount campaign!</span><br /><br />\r\nEnjoy special price cuts right now<br />_FROM_ _TO_:<br /> \r\n<b>_PERCENT_</b> on booking our apartment!' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_DISTANCE_OF_FLAT_FROM_CENTER_POINT', 'Distance of the flat to the {name_center_point} is {distance_center_point}' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_EX_FLAT_OR_LOCATION', 'e.g. flat, landmark or location' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FOUND_FLATS', 'Found Flats' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FLAT', 'Flat' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FLATS_HOTELOWNER_WELCOME_TEXT', 'Welcome to Flats Owner Control Panel! With this Control Panel you can easily manage your flats, customers, reservations and perform a full hotel site management.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FLATS', 'Flats' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FLATS_INFO', 'Flats Info' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FLATS_MANAGEMENT', 'Flats Management' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FLAT_DELETE_ALERT', 'Are you sure you want to delete this flat? Remember: after completing this action all related data to this flat could not be restored!' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FLAT_DESCRIPTION', 'Flat Description' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FLAT_FACILITIES', 'Flats Facilities' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FLAT_INFO', 'Flats Info' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FLAT_MANAGEMENT', 'Flats Management' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FLAT_OWNER', 'Flat Owner' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FLAT_OWNERS', 'Flat Owners' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FLAT_PAYMENT_GATEWAYS', 'Flats Payment Gateways' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FLAT_RESERVATION_ID', 'Flat Reservation ID' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_LAST_FLAT_ALERT', 'You cannot delete last active flats record!' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_LAST_FLAT_PROPERTY_ALERT', 'This property type cannot be deleted, because it is participating in one property at least.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TARGET_FLAT', 'Target Flat' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_YOU_NOT_REVIEW_THIS_FLAT', 'Sorry, but you can''t leave a review for this flat, you''ve never been there' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_SELECT_FLAT', 'Select Flat' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_SELECT_DESTINATION_OR_FLAT', 'Destination / Flat Name' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FLAT_WITH_BEST_PRICE', 'Flat with the best price' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_LAST_BOOKING_FLAT_WAS', 'Last booking for this flat was {type_booking}' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_THIS_FLAT', 'this flat' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_OUR_FLATS', 'our flats' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_DEFAULT_FLAT_DELETE_ALERT', 'You cannot delete default flat!' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_OWNER_NOT_ASSIGNED_TO_FLAT', 'Wrong flat ID or you have no access permissions to view it.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_PC_FLAT_INFO_TEXT', 'information about flat: name, address, telephone, fax etc.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_REGIONAL_NOT_FLATS', 'There are no flats found in your location. You cannot see reports.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MOST_POPULAR_FLATS', 'Most Popular Flats');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FLAT_COMFORT', 'Flats Comfort' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FLAT_AREA', 'Apartment Area' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MESSAGE_INFO_LINK_BOOKING', 'You can go to the order by clicking the "booking" link in the footer menu' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FLAT_WAS_ADDED', 'Flat has been successfully added to your reservation!' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FLAT_WAS_REMOVED', 'Selected flat has been removed from your Reservation Cart!' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FLAT_NOT_FOUND', 'Flat has not been found!' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_SELECTED_FLATS', 'Selected Flats' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_DELETE_ALL', 'Delete all' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_DELETE_ALL_ALERT', 'Are you sure you want to delete all records? Remember: after the completion of this action, the data cannot be restored!' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MAIL_LOG', 'Mail Log' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_DELETE_ALL', 'Delete all' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_DELETE_ALL_ALERT', 'Are you sure you want to delete all records? Remember: after the completion of this action, the data cannot be restored!' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_HOTEL_RESTRICTIONS_MIN_MAX_ROOMS', 'When booking in <b>_HOTEL_NAME_</b> hotel there are restrictions in the number of rooms reserved.<br> They must be at least <b>_MIN_ROOMS_</b> and not more than <b>_MAX_ROOMS_</b>' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_HOTEL_RESTRICTIONS_MIN_ROOMS', 'When booking in <b>_HOTEL_NAME_</b> hotel there are restrictions in the number of rooms reserved.<br> They must be at least <b>_MIN_ROOMS_</b>' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_HOTEL_RESTRICTIONS_MAX_ROOMS', 'When booking in <b>_HOTEL_NAME_</b> hotel there are restrictions in the number of rooms reserved.<br> They must be at not more than <b>_MAX_ROOMS_</b>' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CANNOT_BOOKING_MORE', 'You cannot order more than <b>_NUM_ROOMS_</b> rooms.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CONTINUE_MUST_BOOK_MIN', 'In booking to continue you must book a minimum of <b>_NUM_ROOMS_</b> rooms' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_UPDATE_CURRENCY_RATE', 'Update Currency Rates' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CURRENCY_PREVIOUS_RATE', 'Previous Rate' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CURRENCY_NEW_RATE', 'New Rate' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_NO_CHANGES', 'no changes' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_LAST_UPDATE', 'Last Update' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FIELD_MUST_BE_DATE', '_FIELD_ must be in valid date format! Please re-enter.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_ADMIN_ANSWER', 'Admin Answer' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_IT_IS_YOUR_REVIEW', 'it''s your review' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_QUICK_RESERVATIONS_MSG', 'Quick Reservations feature allows quick reserving rooms without specifying the guest name.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_ERROR', 'Error' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_EMAIL_TEMPLATE', 'Email Template' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_VEHICLE', 'Vehicle' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_VEHICLES', 'Vehicles' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TOKEN_EXPIRED_ALERT', 'Token expired. Please reload page and try again.' FROM `<DB_PREFIX>languages`);

DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value`='_MASS_MAIL_LOG';

-- 09/08/2017 --
RENAME TABLE `<DB_PREFIX>mass_mail_log` TO `<DB_PREFIX>mail_log`;

-- 10/12/2017 --
ALTER TABLE `<DB_PREFIX>reviews` ADD `admin_answer` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL AFTER `negative_comments`;
ALTER TABLE `<DB_PREFIX>customers` ADD `reviews_count` smallint(6) NOT NULL DEFAULT '0' AFTER `rooms_count`;  

ALTER TABLE `<DB_PREFIX>mail_log` ADD `status` tinyint(1) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `<DB_PREFIX>mail_log` ADD `status_description` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';

-- 11/12/2017 --
ALTER TABLE  `<DB_PREFIX>reviews` CHANGE  `positive_comments`  `positive_comments` VARCHAR( 1048 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  '';
ALTER TABLE  `<DB_PREFIX>reviews` CHANGE  `negative_comments`  `negative_comments` VARCHAR( 1048 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  '';
ALTER TABLE  `<DB_PREFIX>reviews` CHANGE  `admin_answer`  `admin_answer` VARCHAR( 1048 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  '';
