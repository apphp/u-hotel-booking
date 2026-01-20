
DELETE FROM `<DB_PREFIX>modules` WHERE `name` = 'rest_api';

DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_REST_API';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_MD_REST_API';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_REST_API_SETTINGS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_REST_API_MANAGEMENT';

UPDATE `<DB_PREFIX>accounts` SET `oa_consumer_domain` = '', `oa_consumer_key` = '', `oa_consumer_secret` = '', `oa_date_created` = NULL;

