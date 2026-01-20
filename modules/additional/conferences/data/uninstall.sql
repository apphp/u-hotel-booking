
DROP TABLE IF EXISTS `<DB_PREFIX>conferences`;
DROP TABLE IF EXISTS `<DB_PREFIX>conferences_translations`;

DROP TABLE IF EXISTS `<DB_PREFIX>conference_types`;
DROP TABLE IF EXISTS `<DB_PREFIX>conference_types_translations`;

DROP TABLE IF EXISTS `<DB_PREFIX>conference_participants`;

DROP TABLE IF EXISTS `<DB_PREFIX>specialities`;
DROP TABLE IF EXISTS `<DB_PREFIX>specialities_description`;

DROP TABLE IF EXISTS `<DB_PREFIX>conference_tuitions`;

DELETE FROM `<DB_PREFIX>modules` WHERE `name` = 'conferences';

DELETE FROM `<DB_PREFIX>modules_settings` WHERE `module_name` = 'conferences';

DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CONFERENCES';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CONFERENCE_SETTINGS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_MD_CONFERENCES';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CONFERENCE_NAME';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CONFERENCE_TYPES';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CONFERENCE_TYPE';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CONFERENCE_PARTICIPANTS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_PARTICIPANTS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CONFERENCES_MANAGEMENT';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_FULL_DESCRIPTION';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_WORKSHOP_DATES';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CONFERENCE_REGESTRATION_LINK';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_MSN_CONFERENCES_IS_ACTIVE';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_MS_CONFERENCES_IS_ACTIVE';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_SPECIALTY';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_I_AM_A';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_COURSE_CANCELLATION_FEE';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CONFERENCES_SELECT_OPTION_ALERT';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_COURSE_REGISTRATION';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CONFERENCES_SELECT_COURSE_ALERT';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_SPECIALITIES_MANAGEMENT';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_OTHER_NOT_LISTED';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CONFIRM_EMAIL_ADDRESS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CONF_EMAIL_MATCH';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CONFIRM_EMAIL_EMPTY_ALERT';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_LOCATION_DESCRIPTION';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TARGET_AUDIENCE';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_PROGRAM_PURPOSE';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_OBJECTIVES';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_PROGRAM_SCHEDULE';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_FACULTY';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_ACCREDITATION';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_FAP';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_ACCOMMODATIONS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_PROGRAM';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CONFERENCE_PRICE_TYPE_1';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CONFERENCE_PRICE_TYPE_2';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CONFERENCE_PRICE_TYPE_3';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CONFERENCE_TYPE_1';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CONFERENCE_TYPE_2';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CONFERENCE_TYPE_3';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CONFERENCE_TUITIONS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TUITIONS';
