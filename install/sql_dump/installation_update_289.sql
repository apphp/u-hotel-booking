
-- UPDATE `<DB_PREFIX>modules_settings` SET `settings_value` = 'manual', `key_display_type`='enum', `key_display_source` = 'manual,auto' WHERE `settings_key`='allow_system_suggestion';

INSERT INTO `<DB_PREFIX>modules_settings` (`id`, `module_name`, `settings_key`, `settings_value`, `settings_name_const`, `settings_description_const`, `key_display_type`, `key_is_required`, `key_display_source`) VALUES
(NULL, 'rooms', 'allow_relocation_to_other_room', 'no', '_MSN_ALLOW_RELOCATION_TO_OTHER_ROOM', '_MS_ALLOW_RELOCATION_TO_OTHER_ROOM', 'yes/no', 1, ''),
(NULL, 'rooms', 'allow_relocation_to_other_hotel', 'no', '_MSN_ALLOW_RELOCATION_TO_OTHER_HOTEL', '_MS_ALLOW_RELOCATION_TO_OTHER_HOTEL', 'yes/no', 1, '');

INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MSN_ALLOW_RELOCATION_TO_OTHER_ROOM', 'Allow Relocation To Other Room' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MSN_ALLOW_RELOCATION_TO_OTHER_HOTEL', 'Allow Relocation To Other Hotel' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MS_ALLOW_RELOCATION_TO_OTHER_ROOM', 'Specifies whether allow relocation to other room of the same hotel on empty search results' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MS_ALLOW_RELOCATION_TO_OTHER_HOTEL', 'Specifies whether allow relocation to other hotel on empty search results' FROM `<DB_PREFIX>languages`);

INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_PERFORM_ADDITIONAL_SEARCH', 'Perform additional search, please wait...' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MS_RELOCATION_TO_OTHER_ROOM', 'This Relocation Room' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MS_RELOCATION_TO_OTHER_HOTEL', 'This Relocation Hotel' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MS_ROOM_RELOCATION', 'Room Relocation' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MS_HOTEL_RELOCATION', 'Hotel Relocation' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MS_RELOCATION_GET_DISCOUNT', 'Pay for one and stay in two and get 10% off rate.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MS_RELOCATION_ROOM_PROGRAM', 'Enjoy 10% off when u choose smart relocation program.<br>You can have full stay at same hotel but at different rooms category - just check in & hotel will handle the rest - accept now and get 10%.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MS_RELOCATION_HOTEL_PROGRAM', 'Enjoy 10% off rate when you choose smart relocation program<br>Now get oriented by splitting your holiday in two hotels, same area, same hotel category.<br>And free transfer, accept now and experience the difference.' FROM `<DB_PREFIX>languages`);

ALTER TABLE `<DB_PREFIX>accounts` ADD `room_relocation` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `<DB_PREFIX>accounts` ADD `hotel_relocation` tinyint(1) NOT NULL DEFAULT '1';

ALTER TABLE `<DB_PREFIX>hotels` ADD `room_relocation` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `<DB_PREFIX>hotels` ADD `hotel_relocation` tinyint(1) NOT NULL DEFAULT '1';

-- 28.05.2019
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_PROPERTIES', 'Properties' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_SHOW_ON_MENU', 'Show On Menu' FROM `<DB_PREFIX>languages`);

ALTER TABLE `<DB_PREFIX>hotels_property_types` ADD `show_on_menu` tinyint(1) unsigned NOT NULL DEFAULT '1';

-- 28.06.2019
ALTER TABLE `<DB_PREFIX>rooms` ADD `room_picture_6` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';
ALTER TABLE `<DB_PREFIX>rooms` ADD `room_picture_6_thumb` varchar(70) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';
ALTER TABLE `<DB_PREFIX>rooms` ADD `room_picture_7` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';
ALTER TABLE `<DB_PREFIX>rooms` ADD `room_picture_7_thumb` varchar(70) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';
ALTER TABLE `<DB_PREFIX>rooms` ADD `room_picture_8` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';
ALTER TABLE `<DB_PREFIX>rooms` ADD `room_picture_8_thumb` varchar(70) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';
ALTER TABLE `<DB_PREFIX>rooms` ADD `room_picture_9` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';
ALTER TABLE `<DB_PREFIX>rooms` ADD `room_picture_9_thumb` varchar(70) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';

ALTER TABLE  `<DB_PREFIX>rooms_description` CHANGE COLUMN `room_short_description` `room_short_description` varchar(1024) COLLATE utf8_unicode_ci NOT NULL;

-- 20.09.2019
ALTER TABLE `<DB_PREFIX>customers` ADD `registered_via_provider` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';
ALTER TABLE `<DB_PREFIX>customers` ADD `provider_identifier` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';

-- 10.11.2019
ALTER TABLE `<DB_PREFIX>customers` CHANGE COLUMN `b_address` `b_address` varchar(64) COLLATE utf8_unicode_ci NULL DEFAULT '';
ALTER TABLE `<DB_PREFIX>customers` CHANGE COLUMN `b_address_2` `b_address_2` varchar(64) COLLATE utf8_unicode_ci NULL DEFAULT '';
ALTER TABLE `<DB_PREFIX>customers` CHANGE COLUMN `b_city` `b_city` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';
ALTER TABLE `<DB_PREFIX>customers` CHANGE COLUMN `b_state` `b_state` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';
ALTER TABLE `<DB_PREFIX>customers` CHANGE COLUMN `b_country` `b_country` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';
ALTER TABLE `<DB_PREFIX>customers` CHANGE COLUMN `b_zipcode` `b_zipcode` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';
ALTER TABLE `<DB_PREFIX>customers` CHANGE COLUMN `phone` `phone` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';
ALTER TABLE `<DB_PREFIX>customers` CHANGE COLUMN `registered_from_ip` `registered_from_ip` varchar(15) CHARACTER SET latin1 NOT NULL DEFAULT '';
ALTER TABLE `<DB_PREFIX>customers` CHANGE COLUMN `registration_code` `registration_code` varchar(20) CHARACTER SET latin1 NOT NULL DEFAULT '';

-- 10.06.2020
ALTER TABLE `<DB_PREFIX>modules_settings` CHANGE COLUMN `module_name` `module_name` varchar(25) CHARACTER SET latin1 NOT NULL;

-- 12.07.2020
ALTER TABLE `<DB_PREFIX>hotels_property_types_description` ADD `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';
