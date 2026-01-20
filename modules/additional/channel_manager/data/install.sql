
INSERT INTO `<DB_PREFIX>modules` (`id`, `name`, `name_const`, `description_const`, `icon_file`, `module_tables`, `dependent_modules`, `settings_page`, `settings_const`, `settings_access_by`, `management_page`, `management_const`, `management_access_by`, `is_installed`, `module_type`, `show_on_dashboard`, `priority_order`) VALUES
(NULL, 'channel_manager', '_CHANNEL_MANAGER', '_MD_CHANNEL_MANAGER', 'channel_manager.png', 'channels,channels_log,channel_hotels,channel_hotel_rooms', '', 'mod_channel_manager_settings', '_CHANNEL_MANAGER_SETTINGS', 'owner,mainadmin,hotelowner', 'mod_channel_manager_management,mod_channel_manager_log', '_CHANNEL_MANAGER_MANAGEMENT,_CHANNEL_MANAGER_LOG', 'owner,mainadmin,hotelowner', 1, 2, 0, 1);


INSERT INTO `<DB_PREFIX>modules_settings` (`id`, `module_name`, `settings_key`, `settings_value`, `settings_name_const`, `settings_description_const`, `key_display_type`, `key_is_required`, `key_display_source`) VALUES
(NULL, 'channel_manager', 'is_active', 'yes', '_MSN_CHANNEL_MANAGER_IS_ACTIVE', '_MS_CHANNEL_MANAGER_IS_ACTIVE', 'yes/no', 1, ''),
(NULL, 'channel_manager', 'is_log_active', 'no', '_MSN_CHANNEL_MANAGER_LOG_IS_ACTIVE', '_MS_CHANNEL_MANAGER_LOG_IS_ACTIVE', 'yes/no', 1, '');


DROP TABLE IF EXISTS `<DB_PREFIX>channels`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>channels` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2;

INSERT INTO `<DB_PREFIX>channels` (`id`, `name`, `description`) VALUES
(1, 'OctoRate', 'Our cloud based channel manager brings all the online distribution channels in a single point of control effortlessly. With channel manager there is no need for manual updates anymore.');


DROP TABLE IF EXISTS `<DB_PREFIX>channels_log`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>channels_log` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `channel_id` int(10) unsigned NOT NULL DEFAULT '0',
  `hotel_id` int(10) unsigned NOT NULL DEFAULT '0',
  `room_id` int(10) unsigned NOT NULL DEFAULT '0',
  `direction` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `log_subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `log_status` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `log_status_description` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `log_content` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `date_created` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;


DROP TABLE IF EXISTS `<DB_PREFIX>channel_hotels`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>channel_hotels` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `hotel_id` int(10) unsigned NOT NULL DEFAULT '0',
  `channel_id` int(10) unsigned NOT NULL DEFAULT '0',
  `cm_auth_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `cm_api_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `cm_hotel_id` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `cm_enabled` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2;

INSERT INTO `<DB_PREFIX>channel_hotels` (`id`, `hotel_id`, `channel_id`, `cm_auth_key`, `cm_api_key`, `cm_hotel_id`, `cm_enabled`) VALUES
(1, 1, 1, '562EC666', '51b698652c64d9b00519abe728axxxxx', 36753, 1);


DROP TABLE IF EXISTS `<DB_PREFIX>channel_hotel_rooms`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>channel_hotel_rooms` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `hotel_id` int(10) unsigned NOT NULL DEFAULT '0',
  `room_id` int(10) unsigned NOT NULL DEFAULT '0',
  `channel_id` int(10) unsigned NOT NULL DEFAULT '0',
  `cm_room_id` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `hotel_room_channel_id` (`hotel_id`,`room_id`,`channel_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3;

INSERT INTO `<DB_PREFIX>channel_hotel_rooms` (`id`, `hotel_id`, `room_id`, `channel_id`, `cm_room_id`) VALUES
(1, 1, 1, 1, 82768),
(2, 1, 2, 1, 60043),
(3, 1, 3, 1, 60044),
(4, 1, 4, 1, 60047);


INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) SELECT NULL, abbreviation, '_CHANNEL', 'Channel' FROM `<DB_PREFIX>languages`;
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) SELECT NULL, abbreviation, '_CHANNEL_NAME', 'Channel Name' FROM `<DB_PREFIX>languages`;
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) SELECT NULL, abbreviation, '_CHANNEL_MANAGER', 'Channel Manager' FROM `<DB_PREFIX>languages`;
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) SELECT NULL, abbreviation, '_MD_CHANNEL_MANAGER', 'This module allows owners to efficiently manage the different online distribution channels that their property sells through.' FROM `<DB_PREFIX>languages`;
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) SELECT NULL, abbreviation, '_CHANNEL_MANAGER_SETTINGS', 'Channel Manager Settings' FROM `<DB_PREFIX>languages`;
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) SELECT NULL, abbreviation, '_CHANNEL_MANAGER_MANAGEMENT', 'Channels Management' FROM `<DB_PREFIX>languages`;
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) SELECT NULL, abbreviation, '_AUTH_KEY', 'Authorization Key' FROM `<DB_PREFIX>languages`;
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) SELECT NULL, abbreviation, '_MSN_CHANNEL_MANAGER_IS_ACTIVE', 'Channel Manager' FROM `<DB_PREFIX>languages`;
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) SELECT NULL, abbreviation, '_MS_CHANNEL_MANAGER_IS_ACTIVE', 'Defines whether Channel Manager module is active or not' FROM `<DB_PREFIX>languages`;
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) SELECT NULL, abbreviation, '_ALL_CHANNELS_ALREADY_ADDED', 'All channels were already added! You cannot add more.' FROM `<DB_PREFIX>languages`;
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) SELECT NULL, abbreviation, '_MSN_CHANNEL_MANAGER_LOG_IS_ACTIVE', 'Channel Manager Log' FROM `<DB_PREFIX>languages`;
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) SELECT NULL, abbreviation, '_MS_CHANNEL_MANAGER_LOG_IS_ACTIVE', 'Defines whether Channel Manager Log is enabled or not' FROM `<DB_PREFIX>languages`;
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) SELECT NULL, abbreviation, '_CHANNEL_MANAGER_LOG', 'Channel Manager Log' FROM `<DB_PREFIX>languages`;
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) SELECT NULL, abbreviation, '_LOG_CONTENT', 'Log Content' FROM `<DB_PREFIX>languages`;
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) SELECT NULL, abbreviation, '_DIRECTION', 'Direction' FROM `<DB_PREFIX>languages`;
