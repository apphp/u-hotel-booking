
DROP TABLE IF EXISTS `<DB_PREFIX>channels`;

DROP TABLE IF EXISTS `<DB_PREFIX>channels_log`;

DROP TABLE IF EXISTS `<DB_PREFIX>channel_hotels`;

DROP TABLE IF EXISTS `<DB_PREFIX>channel_hotel_rooms`;

DELETE FROM `<DB_PREFIX>modules` WHERE `name` = 'channel_manager';

DELETE FROM `<DB_PREFIX>modules_settings` WHERE `module_name` = 'channel_manager';

DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CHANNEL';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CHANNEL_NAME';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CHANNEL_MANAGER';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_MD_CHANNEL_MANAGER';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CHANNEL_MANAGER_SETTINGS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CHANNEL_MANAGER_MANAGEMENT';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_AUTH_KEY';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_MSN_CHANNEL_MANAGER_IS_ACTIVE';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_MS_CHANNEL_MANAGER_IS_ACTIVE';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_ALL_CHANNELS_ALREADY_ADDED';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_MSN_CHANNEL_MANAGER_LOG_IS_ACTIVE';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_MS_CHANNEL_MANAGER_LOG_IS_ACTIVE';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CHANNEL_MANAGER_LOG';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_LOG_CONTENT';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_DIRECTION';
