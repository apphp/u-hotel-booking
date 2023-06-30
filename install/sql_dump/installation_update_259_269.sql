
-- 22.03.2017 --
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_NO_ROOMS_FOUND_AUTOMATIC_RE_SEARCH_ROOM', 'Sorry, there are no rooms match your search criteria. But we''ve found hotels where you can stay in several rooms' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_DISCOUNT_FOR_FIVE_OR_MORE_ROOMS', 'Discount for 5th+ rooms' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_DISCOUNT_FOR_FOUR_ROOMS', 'Discount for 4th rooms' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_DISCOUNT_FOR_THREE_ROOMS', 'Discount for 3rd rooms' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_ROOMS_3', '3rd rooms' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_ROOMS_4', '4th rooms' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_ROOMS_5', '5th rooms' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_ROOMS_DISCOUNT', 'Room Discount' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MESSAGE_DISCOUNTS_ROOMS', 'The price of the room depends on the total number of rooms when ordering.<br/>Price per room when ordering for 3 rooms _PRICE_3_PEOPLE_<br/>Price per room when ordering for 4 rooms _PRICE_4_PEOPLE_<br/>Price per room when ordering for 5 or more rooms _PRICE_5_PEOPLE_' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MSN_CUSTOMER_BOOKING_IN_PAST', 'Booking in the Past for customer' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MS_CUSTOMER_BOOKING_IN_PAST', 'Specifies whether to allow booking in the past for customers and guests (one day ago)' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MESSAGE_DISCOUNTS_NIGHT', 'The price of the room depends on the total of night reserved.<br/>Price per room when ordering for 3 nights is _PRICE_3_NIGHT_<br/>Price per room when ordering for 4 nights is _PRICE_4_NIGHT_<br/>Price per room when ordering for 5 or more nights is _PRICE_5_NIGHT_' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FIELD_INCORRECT_VALUE', 'The field _FIELD_ has incorrect value! Please re-enter.' FROM `<DB_PREFIX>languages` FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_THERE_NO_AVAILABLE_ROOMS', 'There are no available rooms from _DATE_FROM_ to _DATE_TO_. Try changing the reservation date or room type' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MIN_BEDS', 'Min. Beds' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_COUPON_DISCOUNT', 'Coupon Discount' FROM `<DB_PREFIX>languages`);

UPDATE `<DB_PREFIX>vocabulary` SET  `key_text` = 'Booking in the Past for admin' WHERE `language_id` = 'en' AND `key_value` = '_MSN_ADMIN_BOOKING_IN_PAST';

ALTER TABLE `<DB_PREFIX>modules` CHANGE `name` `name` varchar(40) COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `<DB_PREFIX>modules` CHANGE `name_const` `name_const` varchar(40) CHARACTER SET latin1 NOT NULL;
ALTER TABLE `<DB_PREFIX>modules` CHANGE `settings_page` `settings_page` varchar(40) CHARACTER SET latin1 NOT NULL;
ALTER TABLE `<DB_PREFIX>modules` CHANGE `settings_const` `settings_const` varchar(40) CHARACTER SET latin1 NOT NULL;

-- 12.04.2017 --
INSERT INTO `<DB_PREFIX>modules_settings` (`id`, `module_name`, `settings_key`, `settings_value`, `settings_name_const`, `settings_description_const`, `key_display_type`, `key_is_required`, `key_display_source`) VALUES
(NULL, 'booking', 'customer_booking_in_past', 'no', '_MSN_CUSTOMER_BOOKING_IN_PAST', '_MS_CUSTOMER_BOOKING_IN_PAST', 'yes/no', 1, '');

-- 26.04.2017 --
ALTER TABLE `<DB_PREFIX>accounts` CHANGE `room_numbers` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';

-- 24.05.2017 --
ALTER TABLE `<DB_PREFIX>bookings` ADD `nights_discount` DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT '0.00' AFTER `guests_discount`;

-- 14.06.2017 --
ALTER TABLE `<DB_PREFIX>bookings_rooms` ADD `discount` DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT '0.00' AFTER `price`;

-- 16.06.2017 --
ALTER TABLE  `<DB_PREFIX>accounts` CHANGE  `oa_date_created`  `oa_date_created` DATETIME NULL DEFAULT NULL;
ALTER TABLE  `<DB_PREFIX>accounts` CHANGE  `date_created`  `date_created` DATETIME NULL DEFAULT NULL;
ALTER TABLE  `<DB_PREFIX>accounts` CHANGE  `date_lastlogin`  `date_lastlogin` DATETIME NULL DEFAULT NULL;

ALTER TABLE  `<DB_PREFIX>bookings` CHANGE  `created_date`  `created_date` DATETIME NULL DEFAULT NULL;
ALTER TABLE  `<DB_PREFIX>bookings` CHANGE  `payment_date`  `payment_date` DATETIME NULL DEFAULT NULL;
ALTER TABLE  `<DB_PREFIX>bookings` CHANGE  `cancel_payment_date`  `cancel_payment_date` DATE NULL DEFAULT NULL;
ALTER TABLE  `<DB_PREFIX>bookings` CHANGE  `status_changed`  `status_changed` DATETIME NULL DEFAULT NULL;

ALTER TABLE  `<DB_PREFIX>bookings_rooms` CHANGE  `checkin`  `checkin` DATE NULL DEFAULT NULL;
ALTER TABLE  `<DB_PREFIX>bookings_rooms` CHANGE  `checkout`  `checkout` DATE NULL DEFAULT NULL;

ALTER TABLE  `<DB_PREFIX>campaigns` CHANGE  `start_date`  `start_date` DATE NULL DEFAULT NULL;
ALTER TABLE  `<DB_PREFIX>campaigns` CHANGE  `finish_date`  `finish_date` DATE NULL DEFAULT NULL;

ALTER TABLE  `<DB_PREFIX>comments` CHANGE  `date_created`  `date_created` DATETIME NULL DEFAULT NULL;
ALTER TABLE  `<DB_PREFIX>comments` CHANGE  `date_published`  `date_created` DATETIME NULL DEFAULT NULL;

ALTER TABLE  `<DB_PREFIX>coupons` CHANGE  `date_started`  `date_started` DATE NULL DEFAULT NULL;
ALTER TABLE  `<DB_PREFIX>coupons` CHANGE  `date_finished`  `date_finished` DATE NULL DEFAULT NULL;

ALTER TABLE  `<DB_PREFIX>customers` CHANGE  `birth_date`  `birth_date` DATE NULL DEFAULT NULL;
ALTER TABLE  `<DB_PREFIX>customers` CHANGE  `date_created`  `date_created` DATETIME NULL DEFAULT NULL;
ALTER TABLE  `<DB_PREFIX>customers` CHANGE  `date_lastlogin`  `date_lastlogin` DATETIME NULL DEFAULT NULL;
ALTER TABLE  `<DB_PREFIX>customers` CHANGE  `notification_status_changed`  `notification_status_changed` DATETIME NULL DEFAULT NULL;

ALTER TABLE  `<DB_PREFIX>customer_funds` CHANGE  `date_added`  `date_added` DATETIME NULL DEFAULT NULL;
ALTER TABLE  `<DB_PREFIX>customer_funds` CHANGE  `removal_date`  `removal_date` DATETIME NULL DEFAULT NULL;

ALTER TABLE  `<DB_PREFIX>events_registered` CHANGE  `date_registered`  `date_registered` DATETIME NULL DEFAULT NULL;

ALTER TABLE  `<DB_PREFIX>hotel_periods` CHANGE  `start_date`  `start_date` DATE NULL DEFAULT NULL;
ALTER TABLE  `<DB_PREFIX>hotel_periods` CHANGE  `finish_date`  `finish_date` DATE NULL DEFAULT NULL;

ALTER TABLE  `<DB_PREFIX>mass_mail_log` CHANGE  `date_created`  `date_created` DATETIME NULL DEFAULT NULL;

ALTER TABLE  `<DB_PREFIX>news` CHANGE  `date_created`  `date_created` DATETIME NULL DEFAULT NULL;

ALTER TABLE  `<DB_PREFIX>news_subscribed` CHANGE  `date_subscribed`  `date_subscribed` DATETIME NULL DEFAULT NULL;

ALTER TABLE  `<DB_PREFIX>packages` CHANGE  `start_date`  `start_date` DATE NULL DEFAULT NULL;
ALTER TABLE  `<DB_PREFIX>packages` CHANGE  `finish_date`  `finish_date` DATE NULL DEFAULT NULL;

ALTER TABLE  `<DB_PREFIX>pages` CHANGE  `date_created`  `date_created` DATETIME NULL DEFAULT NULL;
ALTER TABLE  `<DB_PREFIX>pages` CHANGE  `date_updated`  `date_updated` DATETIME NULL DEFAULT NULL;
ALTER TABLE  `<DB_PREFIX>pages` CHANGE  `status_changed`  `status_changed` DATETIME NULL DEFAULT NULL;
ALTER TABLE  `<DB_PREFIX>pages` CHANGE  `finish_publishing`  `finish_publishing` DATE NULL DEFAULT NULL;

ALTER TABLE  `<DB_PREFIX>reviews` CHANGE  `date_created`  `date_created` DATETIME NULL DEFAULT NULL;

ALTER TABLE  `<DB_PREFIX>rooms_prices` CHANGE  `date_from`  `date_from` DATE NULL DEFAULT NULL;
ALTER TABLE  `<DB_PREFIX>rooms_prices` CHANGE  `date_to`  `date_to` DATE NULL DEFAULT NULL;

ALTER TABLE  `<DB_PREFIX>wishlist` CHANGE  `date_added`  `date_added` DATETIME NULL DEFAULT NULL;
