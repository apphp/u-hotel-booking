ALTER TABLE `<DB_PREFIX>accounts` CHANGE `email` `email` VARCHAR(100) CHARACTER SET latin1 NOT NULL;
ALTER TABLE `<DB_PREFIX>affiliates` CHANGE `aff_code` `affiliate_id` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';
ALTER TABLE `<DB_PREFIX>banners_description` CHANGE `image_text` `image_text` varchar(2048) COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `<DB_PREFIX>bookings` ADD `guests_discount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' AFTER `discount_fee`;
ALTER TABLE `<DB_PREFIX>bookings` ADD `cancel_payment_date` date NULL DEFAULT NULL AFTER `payment_date`;
ALTER TABLE `<DB_PREFIX>bookings_rooms` ADD `email_notify` tinyint(1) NOT NULL DEFAULT '0' AFTER `meal_plan_price`;
ALTER TABLE `<DB_PREFIX>comments` CHANGE `user_email` `user_email` VARCHAR(100) CHARACTER SET latin1 NOT NULL;
ALTER TABLE `<DB_PREFIX>customers` CHANGE `email` `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `<DB_PREFIX>customer_funds` CHANGE `comments` `comments` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';
ALTER TABLE `<DB_PREFIX>events_registered` CHANGE `email` `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `<DB_PREFIX>hotels` ADD `cancel_reservation_day` tinyint(4) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Specifies the number of days before customers may cancel a reservation for free' AFTER `facilities`;
ALTER TABLE `<DB_PREFIX>hotels` ADD `number_of_views` smallint(6) unsigned NOT NULL DEFAULT '0' AFTER `priority_order`;
ALTER TABLE `<DB_PREFIX>hotels` CHANGE `email` `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';
ALTER TABLE `<DB_PREFIX>hotels` DROP `lowest_price`;
ALTER TABLE `<DB_PREFIX>mass_mail_log` ADD `email_template_subject` varchar(125) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' AFTER `email_template_code`,
                                       ADD `email_template_content` text COLLATE utf8_unicode_ci NOT NULL AFTER `email_template_subject`,
                                       ADD `emails_total` smallint(6) unsigned NOT NULL DEFAULT '0' AFTER `email_template_content`,
                                       ADD `emails_sent` smallint(6) unsigned NOT NULL DEFAULT '0' AFTER `emails_total`;
ALTER TABLE `<DB_PREFIX>modules_settings` CHANGE `key_display_type` `key_display_type` ENUM('string','alpha','email','numeric','unsigned float','integer','positive integer','unsigned integer','enum','yes/no','html size','text') CHARACTER SET latin1 NOT NULL;
ALTER TABLE `<DB_PREFIX>rooms` CHANGE `discount_type` `discount_night_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - fixed price, 1 - percentage';
ALTER TABLE `<DB_PREFIX>rooms` ADD `discount_guests_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - fixed price, 1 - percentage' AFTER `discount_night_5`,
                               ADD `discount_guests_3` decimal(10,2) NOT NULL DEFAULT '0.00' AFTER `discount_guests_type`,
                               ADD `discount_guests_4` decimal(10,2) NOT NULL DEFAULT '0.00' AFTER `discount_guests_3`,
                               ADD `discount_guests_5` decimal(10,2) NOT NULL DEFAULT '0.00' AFTER `discount_guests_4`,
                               ADD `refund_money_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - first night, 1 - fixed price, 2 - percentage' AFTER `discount_guests_5`,
                               ADD `refund_money_value` decimal(10,2) NOT NULL DEFAULT '0.00' AFTER `refund_money_type`,
                               ADD `number_of_views` smallint(6) unsigned NOT NULL DEFAULT '0' AFTER `priority_order`;
ALTER TABLE `<DB_PREFIX>settings` CHANGE `admin_email` `admin_email` varchar(100) COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `<DB_PREFIX>reviews` ADD `travel_type` tinyint(1) NOT NULL DEFAULT '2' COMMENT '0 - Work, 1 - Tourismus, 2 - Other' AFTER `evaluation`,
                                 ADD `passenger_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 - An individual, 1 - A young couple, 2 - An old couple, 3 - Family with young children, 4 - Family with older children, 5 - Groups of Friends' AFTER `travel_type`;
ALTER TABLE `<DB_PREFIX>testimonials` CHANGE `author_email` `author_email` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
