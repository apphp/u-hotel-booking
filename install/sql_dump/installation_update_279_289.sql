-- 18/03/2018 --
INSERT INTO `<DB_PREFIX>modules` (`id`, `name`, `name_const`, `description_const`, `icon_file`, `module_tables`, `dependent_modules`, `settings_page`, `settings_const`, `settings_access_by`, `management_page`, `management_const`, `management_access_by`, `is_installed`, `module_type`, `show_on_dashboard`, `priority_order`) VALUES
(NULL, 'loyalty', '_LOYALTY', '_MD_LOYALTY', 'loyalty.png', '', '', 'mod_loyalty_settings', '_LOYALTY_SETTINGS', 'owner,mainadmin', '', '', '', 1, 1, 0, 11);

-- 25/03/2018 --
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_INACTIVE', 'inactive' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MINIMUM_BEDS_SHORT', 'Min. Number of Beds' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CSS_CLASS_NAME', 'The CSS class name' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_LOYALTY', 'Loyalty' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_LOYALTY_SETTINGS', 'Loyalty Settings' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MD_LOYALTY', 'This module allows site owners to manage and modify the points of customer loyalty. Customers can be rewarded by points after they complete their booking and redeem these points in future reservations.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_ITEM_ALREADY_SELECTED', 'This item has been already selected!' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MIN_ADULTS', 'Min Adults' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FILTER', 'Filter by' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_STATUS_DESCRIPTION', 'Status Description' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_NOT_DEFINED', 'Not Defined' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_DISCOUNT_STD_CAMPAIGN_TEXT_EXT', 'available when ordering for _MIN_ _MAX_ adults' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_SWIPE_ICON_ALERT', 'Swipe to view more...' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MSN_POINT_RATE', 'Loyalty Point Rate' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MS_POINT_RATE', 'Specifies rate of each loyalty point in default currency' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MSN_POINTS_PER_RESERVATION', 'Loyalty Points per Reservation' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MS_POINTS_PER_RESERVATION', 'Specifies amount of loyalty points that customer gets for each reservation' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_RESERVATIONS_AND_PAYMENTS', 'Reservations and Payments' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_AWARD_POINTS', 'Award Points' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_GROUP_DISCOUNT', 'Group Discount' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_APPLY_PER', 'Apply Per' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_APPLY_PER_DESCRIPTION', 'Specifies whether to apply discount for room or for the whole hotel (for group discounts)' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_NAME_EMPTY_ALERT', 'Name cannot be empty! Please re-enter.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MSN_MAX_EMAIL_NEED_ASSISTANCE', 'Max. emails for Need Assistance' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_SOMEONE_FROM_SITE_CONTACTED_YOU', 'Someone from {SITE} contacted you' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MAX_EMAIL_NEED_ASSISTANCE_ERROR', 'You have reached the maximum allowed number of emails per day' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_HOTEL_OWNER_LOGIN', 'Hotel Owner Login' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_HOTEL_EMPTY_ALERT', 'Field Select Hotel cannot be empty!' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_HOTEL_NOT_EXISTS', 'The hotel with such name does not exist, or it is not active. Please try again.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_HOTEL_OWNERS_ACCOUNT_CREATED_CONF_LINK', 'Already confirmed your registration? Click <a href=index.php?admin=login&type=hotel_owners>here</a> to proceed.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_HOTEL_OWNERS_CONFIRMED_UPDATE_MSG', 'Thank you for confirming your registration! <br /><br />Please, to complete the registration, fill in the fields below on the page.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_HOTEL_OWNERS_CONFIRMED_SUCCESS_MSG', 'You may now log into your account. Click <a href=''index.php?admin=login&type=hotel_owners''>here</a> to proceed.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_HOTEL_OWNERS_CONFIRMED_ALREADY_MSG', 'Your account has already been confirmed! <br /><br />Click <a href=''index.php?admin=login&type=hotel_owners''>here</a> to continue.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CREATE_HOTEL_OWNER_ACCOUNT', 'Create Hotel Owner Account' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TYPE_HOTEL_OR_LOCATION', 'Type here hotel name, landmark or location' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MSN_SEND_ORDER_COPY_TO_HOTEL_OWNER', 'Send Order Copy To Hotel Owner' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MS_SEND_ORDER_COPY_TO_HOTEL_OWNER', 'Specifies whether to allow sending a copy of order to hotel owner' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MY_CUSTOMERS', 'My Customers' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_HOTEL_EMAIL_ALERT', 'Used to reservation notifications' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_DATES_CHANGED_ALERT', 'Dates were changed. To be sure these changes make effect please click on "Apply Changes" link.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_PAYMENT_GATEWAYS', 'Payment Gateways' FROM `<DB_PREFIX>languages`);

-- 01/04/2018 --
ALTER TABLE  `<DB_PREFIX>accounts` CHANGE  `companies`  `companies` VARCHAR( 1024 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  '';

-- 05/04/2018 --
ALTER TABLE `<DB_PREFIX>campaigns` ADD `minimum_adults` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `<DB_PREFIX>campaigns` ADD `maximum_adults` smallint(6) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `<DB_PREFIX>campaigns` ADD `discount_type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '0 - fixed price, 1 - percentage';
ALTER TABLE `<DB_PREFIX>campaigns` CHANGE  `discount_percent`  `discount_value` DECIMAL( 5, 2 ) NOT NULL;
ALTER TABLE `<DB_PREFIX>campaigns` ADD `apply_per` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - room, 1 - hotel';

ALTER TABLE `<DB_PREFIX>coupons` ADD `discount_type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '0 - fixed price, 1 - percentage';
ALTER TABLE `<DB_PREFIX>coupons` CHANGE  `discount_percent`  `discount_value` DECIMAL( 5, 2 ) NOT NULL;

ALTER TABLE `<DB_PREFIX>bookings` ADD `discount_type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '0 - fixed price, 1 - percentage';
ALTER TABLE `<DB_PREFIX>bookings` CHANGE  `discount_percent`  `discount_value` DECIMAL( 5, 2 ) UNSIGNED NOT NULL DEFAULT  '0.00';

-- 09/05/2018 --
INSERT INTO `<DB_PREFIX>modules_settings` (`id`, `module_name`, `settings_key`, `settings_value`, `settings_name_const`, `settings_description_const`, `key_display_type`, `key_is_required`, `key_display_source`) VALUES
(NULL, 'loyalty', 'points_per_reservation', '1', '_MSN_POINTS_PER_RESERVATION', '_MS_POINTS_PER_RESERVATION', 'positive integer', 0, ''),
(NULL, 'loyalty', 'redeem_rate', '5', '_MSN_POINT_RATE', '_MS_POINT_RATE', 'positive float', 0, '');

-- 10/05/2018 --
ALTER TABLE `<DB_PREFIX>customers` ADD `award_points` smallint(6) NOT NULL DEFAULT '0';

-- 04/07/2018 --
INSERT INTO `<DB_PREFIX>modules_settings` (`id`, `module_name`, `settings_key`, `settings_value`, `settings_name_const`, `settings_description_const`, `key_display_type`, `key_is_required`, `key_display_source`) VALUES
(NULL, 'rooms', 'max_email_need_assistance', '5', '_MSN_MAX_EMAIL_NEED_ASSISTANCE', '_MS_MAX_EMAIL_NEED_ASSISTANCE', 'positive integer', 1, '');

ALTER TABLE `<DB_PREFIX>customers` ADD `need_assistance_count` smallint(6) NOT NULL DEFAULT '0';
ALTER TABLE `<DB_PREFIX>customers` ADD `last_need_assistance` date NULL DEFAULT NULL;


ALTER TABLE `<DB_PREFIX>accounts` ADD `registration_code` varchar(20) CHARACTER SET latin1 NOT NULL;

INSERT INTO `<DB_PREFIX>email_templates` (`id`, `language_id`, `template_code`, `template_name`, `template_subject`, `template_content`, `is_system_template`) VALUES
(NULL, 'en', 'new_hotel_owner_account_created_confirm', 'Email for new hotel owner (email confirmation required)', 'Your account has been created (confirmation required)', 'Dear hotel owner of <b>{HOTEL_NAME}</b>!\r\n\r\nIn order to become authorized member, you will need to confirm your registration. You may follow the link below to access the confirmation page:\r\n<a href="{BASE_URL}index.php?admin=hotel_owners_confirm_registration&c={REGISTRATION_CODE}">Confirm Registration</a>\r\n\r\nP.S. Remember, we will never sell your personal information or email address.\r\n\r\nEnjoy!\r\n-\r\nSincerely,\r\nAdministration', 1);


-- 12/07/2018 --
DROP TABLE IF EXISTS `<DB_PREFIX>customer_award_points`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>customer_award_points` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) NOT NULL DEFAULT '0',
  `admin_id` int(10) NOT NULL DEFAULT '0',
  `award_points` int(10) NOT NULL DEFAULT '0',
  `comments` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `date_added` datetime NULL DEFAULT NULL,
  `removed_by` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `removed_comments` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `removal_date` datetime NULL DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;


-- 17/07/2018
ALTER TABLE `<DB_PREFIX>accounts` CHANGE  `registration_code`  `registration_code` varchar(20) CHARACTER SET latin1 NULL DEFAULT '';

DROP TABLE IF EXISTS `<DB_PREFIX>privileges`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>privileges` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `code` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=17 ;

INSERT INTO `<DB_PREFIX>privileges` (`id`, `code`, `name`, `description`) VALUES
(1, 'add_menus', 'Add Menus', 'Add Menus on the site'),
(2, 'edit_menus', 'Edit Menus', 'Edit Menus on the site'),
(3, 'delete_menus', 'Delete Menus', 'Delete Menus from the site'),
(4, 'add_pages', 'Add Pages', 'Add Pages on the site'),
(5, 'edit_pages', 'Edit Pages', 'Edit Pages on the site'),
(6, 'delete_pages', 'Delete Pages', 'Delete Pages from the site'),
(7, 'add_hotel_info', 'Add Hotels', 'Add new hotels'),
(8, 'edit_hotel_info', 'Manage Hotels', 'See and modify the hotels info (view, edit and delete)'),
(9, 'edit_hotel_rooms', 'Manage Hotel Rooms', 'See and modify the hotel rooms info (view, add, edit and delete)'),
(10, 'view_hotel_reports', 'See Hotel Reports', 'See only reports related to assigned hotel'),
(11, 'add_bookings', 'Add Bookings', 'Add bookings to the site'),
(12, 'edit_bookings', 'Edit Bookings', 'Edit bookings on the site'),
(13, 'cancel_bookings', 'Cancel Bookings', 'Cancel bookings on the site'),
(14, 'delete_bookings', 'Delete Bookings', 'Delete bookings from the site'),
(15, 'view_hotel_payments', 'See Hotel Payment Gateways', 'Allow management of payment gateways for assigned properties'),
(16, 'edit_hotel_payments', 'Edit Hotel Payment Gateways', 'Edit payment gateways for the account');


DROP TABLE IF EXISTS `<DB_PREFIX>role_privileges`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>role_privileges` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `role_id` int(5) NOT NULL,
  `privilege_id` int(5) NOT NULL,
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=49 ;

INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES
(1, 1, 1, 1),
(2, 1, 2, 1),
(3, 1, 3, 1),
(4, 1, 4, 1),
(5, 1, 5, 1),
(6, 1, 6, 1),
(7, 1, 11, 1),
(8, 1, 12, 1),
(9, 1, 13, 1),
(10, 1, 14, 1),
(11, 2, 1, 1),
(12, 2, 2, 1),
(13, 2, 3, 1),
(14, 2, 4, 1),
(15, 2, 5, 1),
(16, 2, 6, 1),
(17, 2, 11, 1),
(18, 2, 12, 1),
(19, 2, 13, 1),
(20, 2, 14, 1),
(21, 3, 1, 0),
(22, 3, 2, 1),
(23, 3, 3, 0),
(24, 3, 4, 1),
(25, 3, 5, 1),
(26, 3, 6, 0),
(27, 3, 11, 1),
(28, 3, 12, 1),
(29, 3, 13, 0),
(30, 3, 14, 0),
(31, 4, 7, 1),
(32, 4, 8, 1),
(33, 4, 9, 1),
(34, 4, 10, 1),
(35, 4, 11, 1),
(36, 4, 12, 1),
(37, 4, 13, 1),
(38, 4, 14, 1),
(39, 4, 15, 1),
(40, 4, 16, 1),
(41, 5, 7, 1),
(42, 5, 8, 1),
(43, 5, 9, 1),
(44, 5, 10, 0),
(45, 5, 11, 0),
(46, 5, 12, 0),
(47, 5, 13, 0),
(48, 5, 14, 0);


-- 05/09/2018
INSERT INTO `<DB_PREFIX>modules_settings` (`id`, `module_name`, `settings_key`, `settings_value`, `settings_name_const`, `settings_description_const`, `key_display_type`, `key_is_required`, `key_display_source`) VALUES
(NULL, 'booking', 'send_order_copy_to_hotel_owner', 'yes', '_MSN_SEND_ORDER_COPY_TO_HOTEL_OWNER', '_MS_SEND_ORDER_COPY_TO_HOTEL_OWNER', 'yes/no', 0, '');


-- 31.10.2018
ALTER TABLE `<DB_PREFIX>hotels` ADD `video_link` varchar(125) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';
ALTER TABLE `<DB_PREFIX>hotels` ADD `registration_number` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';

ALTER TABLE `<DB_PREFIX>rooms` ADD `suitable_for_kids` tinyint(1) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `<DB_PREFIX>rooms` ADD `room_floor` varchar(70) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';
ALTER TABLE `<DB_PREFIX>rooms` ADD `single_beds` tinyint(1) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `<DB_PREFIX>rooms` ADD `double_beds` tinyint(1) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `<DB_PREFIX>rooms` ADD `folding_sofa_single_beds` tinyint(1) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `<DB_PREFIX>rooms` ADD `folding_sofa_double_beds` tinyint(1) unsigned NOT NULL DEFAULT '0';

ALTER TABLE `<DB_PREFIX>accounts` ADD `registration_type` varchar(20) CHARACTER SET latin1 NULL DEFAULT '';

INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_VIDEO_LINK', 'Video Link' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_VIDEO_LINK_TOOLTIP', 'Copy the video link and paste it into the field.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_HOTEL_REGISTRATION_NUMBER', 'Hotel Registration Number' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FLOOR', 'Floor' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_SUITABLE_FOR_KIDS', 'Suitable For Kids' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_SINGLE_BEDS', 'Single Beds' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_DOUBLE_BEDS', 'Double Beds' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FOLDING_SOFA_SINGLE_BEDS', 'Folding Sofa Single Beds' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FOLDING_SOFA_DOUBLE_BEDS', 'Folding Sofa Double Beds' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TOTAL_NUMBER_OF_BEDS', 'Total Number Of Beds' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MSN_HOTEL_OWNER_REGISTRATION_TYPE', 'Hotel owner registration type' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MS_HOTEL_OWNER_REGISTRATION_TYPE', 'Specifies the registration method for the hotel owner, Advanced/Standard.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MD_ACCOUNTS', 'The Accounts module allows easy accounts management on your site. Administrator could create, edit or delete accounts.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_ACCOUNTS_SETTINGS', 'Accounts Settings' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MSN_HOTEL_OWNER_ALLOW_REGISTRATION', 'Hotel Owner Allow Registration' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MS_HOTEL_OWNER_ALLOW_REGISTRATION', 'Specifies whether to allow hotel owner to register' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_ST_FLOOR', 'st Floor' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_GROUND', 'Ground' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_LOCATION_AND_MAP', 'Location & Map' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MSN_ALLOW_SOCIAL_LOGIN', 'Allow Social Login' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MN_ALLOW_SOCIAL_LOGIN', 'Specifies whether to allow social login to customers' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_SWITCH_YEARS', 'Switch Years' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_SWITCH_YEARS_CONFIRM', 'Are you sure you want to switch the years to availability of this rooms?' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_SWITCH_YEARS_SAVED', 'Switch years successfully saved! Please refresh the Home Page to see the results.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MANAGE', 'Manage' FROM `<DB_PREFIX>languages`);

UPDATE `<DB_PREFIX>vocabulary` SET `key_text` = 'Number of Rooms (in the property)' WHERE `key_value`='_ROOMS_COUNT' AND `language_id`='en';

INSERT INTO `<DB_PREFIX>modules` (`id`, `name`, `name_const`, `description_const`, `icon_file`, `module_tables`, `dependent_modules`, `settings_page`, `settings_const`, `settings_access_by`, `management_page`, `management_const`, `management_access_by`, `is_installed`, `module_type`, `show_on_dashboard`, `priority_order`) VALUES
(NULL, 'accounts', '_ACCOUNTS', '_MD_ACCOUNTS', 'accounts.png', 'accounts', '', 'mod_accounts_settings', '_ACCOUNTS_SETTINGS', 'owner,mainadmin', '', '', '', 1, 0, 0, 16);

ALTER TABLE `<DB_PREFIX>modules` CHANGE `settings_page` `settings_page` varchar(60) CHARACTER SET latin1 NOT NULL;

INSERT INTO `<DB_PREFIX>modules_settings` (`id`, `module_name`, `settings_key`, `settings_value`, `settings_name_const`, `settings_description_const`, `key_display_type`, `key_is_required`, `key_display_source`) VALUES
(NULL, 'accounts', 'hotel_owner_registration_type', 'standard', '_MSN_HOTEL_OWNER_REGISTRATION_TYPE', '_MS_HOTEL_OWNER_REGISTRATION_TYPE', 'enum', 1, 'advanced,standard'),
(NULL, 'accounts', 'hotel_owner_allow_registration', 'yes', '_MSN_HOTEL_OWNER_ALLOW_REGISTRATION', '_MS_HOTEL_OWNER_ALLOW_REGISTRATION', 'yes/no', 1, ''),
(NULL, 'customers', 'allow_social_login', 'yes', '_MSN_ALLOW_SOCIAL_LOGIN', '_MN_ALLOW_SOCIAL_LOGIN', 'yes/no', 1, ''),
(NULL, 'booking', 'gst_value', '0', '_MSN_GST_VALUE', '_MS_GST_VALUE', 'yes/no', 0, '');


UPDATE `<DB_PREFIX>modules_settings` SET `settings_value` = 'unitegallery', `key_display_source` = 'lytebox,unitegallery' WHERE `settings_key`='image_gallery_type';
UPDATE `<DB_PREFIX>modules_settings` SET `settings_value` = 'unitegallery', `key_display_source` = 'unitegallery' WHERE `settings_key`='video_gallery_type';


INSERT INTO `<DB_PREFIX>email_templates` (`id`, `language_id`, `template_code`, `template_name`, `template_subject`, `template_content`, `is_system_template`) VALUES
(NULL, 'en', 'new_hotel_owner_created_standard_type', 'Email for new hotel owner for standard registration (email confirmation required)', 'Your account has been created (confirmation required)', 'In order to become authorized member, you will need to confirm your registration. You may follow the link below to access the confirmation page:\r\n<a href="{BASE_URL}index.php?admin=hotel_owners_confirm_registration&c={REGISTRATION_CODE}">Confirm Registration</a>\r\n\r\nP.S. Remember, we will never sell your personal information or email address.\r\n\r\nEnjoy!\r\n-\r\nSincerely,\r\nAdministration', 1);

UPDATE `<DB_PREFIX>privileges` SET `description` = 'See and modify the hotels info (edit and delete)' WHERE `id`= 8;
UPDATE `<DB_PREFIX>privileges` SET `description` = 'See and modify the hotel rooms info (add, edit and delete)' WHERE `id`= 9;

-- 30.01.2019
ALTER TABLE `<DB_PREFIX>accounts` MODIFY `account_type` enum('owner','mainadmin','admin','hotelowner','agencyowner','regionalmanager','hotelmanager') CHARACTER SET latin1 NOT NULL DEFAULT 'mainadmin';