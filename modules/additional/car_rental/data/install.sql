INSERT INTO `<DB_PREFIX>modules` (`id`, `name`, `name_const`, `description_const`, `icon_file`, `module_tables`, `dependent_modules`, `settings_page`, `settings_const`, `settings_access_by`, `management_page`, `management_const`, `management_access_by`, `is_installed`, `module_type`, `show_on_dashboard`, `priority_order`) VALUES
(NULL, 'car_rental', '_CAR_RENTAL', '_MD_CAR_RENTAL', 'car_rental.png', 'car_agencies,car_agencies_description', '', '', '', 'owner,mainadmin', 'mod_car_rental_agencies', '', 'owner,mainadmin', 1, 2, 0, 2);


INSERT INTO `<DB_PREFIX>modules_settings` (`id`, `module_name`, `settings_key`, `settings_value`, `settings_name_const`, `settings_description_const`, `key_display_type`, `key_is_required`, `key_display_source`) VALUES
(NULL, 'car_rental', 'is_active', 'yes', '_MSN_ACTIVATE_CAR_RENTAL', '_MS_ACTIVATE_CAR_RENTAL', 'yes/no', '1', ''),
(NULL, 'car_rental', 'allow_separate_gateways', 'yes', '_MSN_TC_SEPARATE_GATEWAYS', '_MS_TC_SEPARATE_GATEWAYS', 'yes/no', '1', ''),
(NULL, 'car_rental', 'payment_type_poa', 'Frontend & Backend', '_MSN_PAYMENT_TYPE_POA', '_MS_PAYMENT_TYPE_POA', 'enum', 1, 'No,Frontend Only,Backend Only,Frontend & Backend'),
(NULL, 'car_rental', 'payment_type_online', 'Frontend & Backend', '_MSN_PAYMENT_TYPE_ONLINE', '_MS_PAYMENT_TYPE_ONLINE', 'enum', 1, 'No,Frontend Only,Backend Only,Frontend & Backend'),
(NULL, 'car_rental', 'online_credit_card_required', 'yes', '_MSN_ONLINE_CREDIT_CARD_REQUIRED', '_MS_ONLINE_CREDIT_CARD_REQUIRED', 'yes/no', 0, ''),
(NULL, 'car_rental', 'payment_type_bank_transfer', 'Frontend & Backend', '_MSN_PAYMENT_TYPE_BANK_TRANSFER', '_MS_PAYMENT_TYPE_BANK_TRANSFER', 'enum', 1, 'No,Frontend Only,Backend Only,Frontend & Backend'),
(NULL, 'car_rental', 'bank_transfer_info', 'Bank name: {BANK NAME HERE}\r\nSwift code: {CODE HERE}\r\nRouting in Transit# or ABA#: {ROUTING HERE}\r\nAccount number *: {ACCOUNT NUMBER HERE}\r\n\r\n*The account number must be in the IBAN format which may be obtained from the branch handling the customer''s account or may be seen at the top the customer''s bank statement\r\n', '_MSN_BANK_TRANSFER_INFO', '_MS_BANK_TRANSFER_INFO', 'text', 0, ''),
(NULL, 'car_rental', 'payment_type_paypal', 'Frontend & Backend', '_MSN_PAYMENT_TYPE_PAYPAL', '_MS_PAYMENT_TYPE_PAYPAL', 'enum', 1, 'No,Frontend Only,Backend Only,Frontend & Backend'),
(NULL, 'car_rental', 'paypal_email', 'paypal@example.com', '_MSN_PAYPAL_EMAIL', '_MS_PAYPAL_EMAIL', 'email', 1, ''),
(NULL, 'car_rental', 'payment_type_2co', 'Frontend & Backend', '_MSN_PAYMENT_TYPE_2CO', '_MS_PAYMENT_TYPE_2CO', 'enum', 0, 'No,Frontend Only,Backend Only,Frontend & Backend'),
(NULL, 'car_rental', 'two_checkout_vendor', 'Your 2CO Vendor ID here', '_MSN_TWO_CHECKOUT_VENDOR', '_MS_TWO_CHECKOUT_VENDOR', 'string', 1, ''),
(NULL, 'car_rental', 'payment_type_authorize', 'Frontend & Backend', '_MSN_PAYMENT_TYPE_AUTHORIZE', '_MS_PAYMENT_TYPE_AUTHORIZE', 'enum', 1, 'No,Frontend Only,Backend Only,Frontend & Backend'),
(NULL, 'car_rental', 'authorize_login_id', 'Your API Login ID here', '_MSN_AUTHORIZE_LOGIN_ID', '_MS_AUTHORIZE_LOGIN_ID', 'string', 1, ''),
(NULL, 'car_rental', 'authorize_transaction_key', 'Your Transaction Key here', '_MSN_AUTHORIZE_TRANSACTION_KEY', '_MS_AUTHORIZE_TRANSACTION_KEY', 'string', 1, ''),
(NULL, 'car_rental', 'default_payment_system', 'paypal', '_MSN_DEFAULT_PAYMENT_SYSTEM', '_MS_DEFAULT_PAYMENT_SYSTEM', 'enum', 1, 'poa,online,bank transfer,paypal,2co,authorize.net'),
(NULL, 'car_rental', 'pre_payment_type', 'percentage', '_MSN_PRE_PAYMENT_TYPE', '_MS_PRE_PAYMENT_TYPE', 'enum', 1, 'full price,first night,fixed sum,percentage'),
(NULL, 'car_rental', 'pre_payment_value', '10', '_MSN_PRE_PAYMENT_VALUE', '_MS_PRE_PAYMENT_VALUE', 'enum', 0, '1,2,3,4,5,6,7,8,9,10,15,20,25,30,35,40,45,50,55,60,65,70,75,80,85,90,95,99,100,125,150,175,200,250,500,750,1000'),
(NULL, 'car_rental', 'vat_value', '0', '_MSN_VAT_VALUE', '_MS_VAT_VALUE', 'unsigned float', 0, ''),
(NULL, 'car_rental', 'mode', 'REAL MODE', '_MSN_BOOKING_MODE', '_MS_BOOKING_MODE', 'enum', 1, 'TEST MODE,REAL MODE'),
(NULL, 'car_rental', 'show_agency_in_search_result', 'yes', '_MSN_AGENCY_IN_SEARCH_RESULT', '_MS_AGENCY_IN_SEARCH_RESULT', 'yes/no', 0, ''),
(NULL, 'car_rental', 'default_contant_email', '', '_MSN_TC_DEFAULT_CONTACT_EMAIL', '_MS_TC_DEFAULT_CONTACT_EMAIL', 'email', 0, ''),
(NULL, 'car_rental', 'default_contant_phone', '', '_MSN_TC_DEFAULT_CONTACT_PHONE', '_MS_TC_DEFAULT_CONTACT_PHONE', 'string', 0, ''),
(NULL, 'car_rental', 'customers_cancel_reservation', '7', '_MSN_CUSTOMERS_CANCEL_RESERVATION', '_MS_CUSTOMERS_CANCEL_RESERVATION', 'enum', 1, '0,1,2,3,4,5,6,7,10,14,21,30,45,60'),
(NULL, 'car_rental', 'vat_included_in_price', 'no', '_MSN_VAT_INCLUDED_IN_PRICE', '_MS_VAT_INCLUDED_IN_PRICE', 'yes/no', 1, ''),
(NULL, 'car_rental', 'allow_reservation_without_account', 'yes', '_MSN_ALLOW_RESERVATION_WITHOUT_ACCOUNT', '_MS_ALLOW_RESERVATION_WITHOUT_ACCOUNT', 'yes/no', 0, ''),
(NULL, 'car_rental', 'allow_payment_with_balance', 'yes', '_MSN_ALLOW_PAYMENT_WITH_BALANCE', '_MS_ALLOW_PAYMENT_WITH_BALANCE', 'yes/no', 1, ''),
(NULL, 'car_rental', 'prebooking_orders_timeout', '2', '_MSN_PREBOOKING_ORDERS_TIMEOUT', '_MS_PREBOOKING_ORDERS_TIMEOUT', 'enum', 1, '0,1,2,3,4,5,6,7,8,9,10,12,14,16,18,20,22,24,36,48,72'),
(NULL, 'car_rental', 'reservation_expired_alert', 'no', '_MSN_RESERVATION_EXPIRED_ALERT', '_MS_RESERVATION EXPIRED_ALERT', 'yes/no', 1, ''),
(NULL, 'car_rental', 'send_order_copy_to_admin', 'yes', '_MSN_SEND_ORDER_COPY_TO_ADMIN', '_MS_SEND_ORDER_COPY_TO_ADMIN', 'yes/no', 0, ''),
(NULL, 'car_rental', 'send_order_copy_to_agency', 'yes', '_MSN_SEND_ORDER_COPY_TO_AGENCY', '_MS_SEND_ORDER_COPY_TO_AGENCY', 'yes/no', 0, ''),
(NULL, 'car_rental', 'reservation_number_type', 'random', '_MSN_RESERVATION_NUMBER_TYPE', '_MS_RESERVATION_NUMBER_TYPE', 'enum', 1, 'random,sequential');


INSERT INTO `<DB_PREFIX>roles` (`id`, `code`, `name`, `description`) VALUES
(NULL, 'agencyowner', 'Car Agency Owner', 'The "Car Agency Owner" is the owner of the car agency, has special privileges to the agency/cars he/she assigned to.');

INSERT INTO `<DB_PREFIX>privileges` (`id`, `code`, `name`, `description`) VALUES (NULL, 'edit_car_agency_info', 'Manage Car Agencies', 'See and modify the car agency info');
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'agencyowner'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>privileges` (`id`, `code`, `name`, `description`) VALUES (NULL, 'edit_car_agency_vehicles', 'Manage Car Agencies Vehicles', 'See and modify the car agency vehicles');
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'agencyowner'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);

INSERT INTO `<DB_PREFIX>privileges` (`id`, `code`, `name`, `description`) VALUES (NULL, 'edit_car_reservations', 'Edit Car Reservations', 'Edit car reservations on the site');
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'owner'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'agencyowner'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);

INSERT INTO `<DB_PREFIX>privileges` (`id`, `code`, `name`, `description`) VALUES (NULL, 'cancel_car_reservations', 'Cancel Car Reservations', 'Cancel car reservations on the site');
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'owner'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'agencyowner'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);

INSERT INTO `<DB_PREFIX>privileges` (`id`, `code`, `name`, `description`) VALUES (NULL, 'delete_car_reservations', 'Delete Car Reservations', 'Delete car reservations from the site');
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'owner'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'agencyowner'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);


DROP TABLE IF EXISTS `<DB_PREFIX>car_agencies`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>car_agencies` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `phone` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `fax` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(70) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `logo_image` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `map_code` text COLLATE utf8_unicode_ci NOT NULL,
  `priority_order` smallint(6) unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

INSERT INTO `<DB_PREFIX>car_agencies` (`id`, `phone`, `fax`, `email`, `logo_image`, `map_code`, `priority_order`, `is_active`) VALUES
(1, '1-800-123-4567', '1-800-123-6789', 'info@car_agency1.com', 'company_logo_1.png', '<iframe width="100%" height="400" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://maps.google.com/maps?f=q&source=s_q&hl=en&geocode=&q=Hotel+Circle+South,+San+Diego,+California,+United+States&aq=1&sll=33.981232,-84.173813&sspn=0.12868,0.307274&ie=UTF8&hq=&hnear=Hotel+Cir+S,+San+Diego,+California&ll=32.759,-117.177036&spn=0.017215,0.038409&z=14&output=embed"></iframe>', 0, 1),
(2, '1-800-123-2222', '1-800-123-2223', 'info@car_agency2.com', 'company_logo_2.png', '<iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d2948.0965968856144!2d-71.0666208!3d42.361780599999996!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89e3709a3c7713c5%3A0x922cf2c02c6d4e5a!2s5+Blossom+St%2C+Charles+River+Plaza+Shopping+Center%2C+Boston%2C+MA+02114!5e0!3m2!1sen!2s!4v1405938488556" width="100%" height="450" frameborder="0" style="border:0"></iframe>', 0, 1);


DROP TABLE IF EXISTS `<DB_PREFIX>car_agencies_description`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>car_agencies_description` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `agency_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `language_id` varchar(2) CHARACTER SET latin1 NOT NULL DEFAULT 'en',
  `name` varchar(125) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(225) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `terms_and_conditions` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `agency_id` (`agency_id`,`language_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

INSERT INTO `<DB_PREFIX>car_agencies_description` (`id`, `agency_id`, `language_id`, `name`, `address`, `description`, `terms_and_conditions`) (SELECT NULL, 1, abbreviation, 'Transportation Agency 1', 'address here...', '<h3>We give you the following for free</h3><div class="col-md-6"><ul class="checklist"><li>Amendments</li><li>Theft Protection</li><li>Direct-dial phone</li><li>Collision Damage Waiver</li></ul></div><div class="col-md-6"><ul class="checklist"><li>Unlimited Kilometres for ALL car groups</li><li>Local Taxes</li><li>PRoad Fee</li><li>First Additional Driver</li></ul></div>', '<h3>Excess Protection - Excellent Value, Best Cover, Peace of Mind</h3><p>When you pick up your car a deposit is usually held on your card for your excess which may be charged if the vehicle is damaged. Take our Damage Excess Refund and if you have any bumps or scrapes we''ll refund any deductions from your excess.<br /><br /></p><div class="hpadding20"><ul class="checklist"><li><span class="bold green">You''re protected</span> - Damage Excess Refund protects you when things go wrong and you are required to pay out from your excess</li><li><span class="bold green">Accidents covered</span> - Your excess will be protected if your vehicle is damaged in an accident</li><li><span class="bold green">Damage cover</span> - Damage to bodywork, vehicle parts and accessories incurred during most driving incidents is covered.</li></ul></div>' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>car_agencies_description` (`id`, `agency_id`, `language_id`, `name`, `address`, `description`, `terms_and_conditions`) (SELECT NULL, 2, abbreviation, 'Transportation Agency 2', 'address here...', '<h3>We give you the following for free</h3><div class="col-md-6"><ul class="checklist"><li>Amendments</li><li>Theft Protection</li><li>Direct-dial phone</li><li>Collision Damage Waiver</li></ul></div><div class="col-md-6"><ul class="checklist"><li>Unlimited Kilometres for ALL car groups</li><li>Local Taxes</li><li>PRoad Fee</li><li>First Additional Driver</li></ul></div>', '<h3>Excess Protection - Excellent Value, Best Cover, Peace of Mind</h3><p>When you pick up your car a deposit is usually held on your card for your excess which may be charged if the vehicle is damaged. Take our Damage Excess Refund and if you have any bumps or scrapes we''ll refund any deductions from your excess.<br /><br /></p><div class="hpadding20"><ul class="checklist"><li><span class="bold green">You''re protected</span> - Damage Excess Refund protects you when things go wrong and you are required to pay out from your excess</li><li><span class="bold green">Accidents covered</span> - Your excess will be protected if your vehicle is damaged in an accident</li><li><span class="bold green">Damage cover</span> - Damage to bodywork, vehicle parts and accessories incurred during most driving incidents is covered.</li></ul></div>' FROM `<DB_PREFIX>languages`);


DROP TABLE IF EXISTS `<DB_PREFIX>car_agencies_locations`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>car_agencies_locations` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `country_id` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `priority_order` smallint(6) unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `country_id` (`country_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

INSERT INTO `<DB_PREFIX>car_agencies_locations` (`id`, `country_id`, `priority_order`, `is_active`) VALUES
(1, 'US', 2, 1),
(2, 'US', 3, 1);


DROP TABLE IF EXISTS `<DB_PREFIX>car_agencies_locations_description`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>car_agencies_locations_description` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `agency_location_id` int(10) unsigned NOT NULL DEFAULT '0',
  `language_id` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(125) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `agency_location_id` (`agency_location_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

INSERT INTO `<DB_PREFIX>car_agencies_locations_description` (`id`, `agency_location_id`, `language_id`, `name`) VALUES
(1, 1, 'en', 'Boston'),
(2, 2, 'en', 'New York City');


DROP TABLE IF EXISTS `<DB_PREFIX>car_agency_locations`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>car_agency_locations` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `agency_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `agency_location_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `priority_order` smallint(6) unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `agency_id` (`agency_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

INSERT INTO `<DB_PREFIX>car_agency_locations` (`id`, `agency_id`, `agency_location_id`, `priority_order`, `is_active`) VALUES
(1, 1, 1, 0, 1),
(2, 1, 2, 1, 1),
(3, 2, 1, 1, 1);


DROP TABLE IF EXISTS `<DB_PREFIX>car_agency_vehicle_categories`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>car_agency_vehicle_categories` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `priority_order` smallint(6) unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=14 ;

INSERT INTO `<DB_PREFIX>car_agency_vehicle_categories` (`id`, `priority_order`, `is_active`) VALUES
(1, 0, 1),
(2, 1, 1),
(3, 2, 1),
(4, 3, 1),
(5, 4, 1),
(6, 5, 1),
(7, 6, 1),
(8, 7, 1),
(9, 8, 1),
(10, 9, 1),
(11, 10, 1),
(12, 11, 1),
(13, 12, 1);


DROP TABLE IF EXISTS `<DB_PREFIX>car_agency_vehicle_categories_description`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>car_agency_vehicle_categories_description` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `agency_vehicle_category_id` int(10) unsigned NOT NULL DEFAULT '0',
  `language_id` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(125) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `agency_vehicle_category_id` (`agency_vehicle_category_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=14 ;

INSERT INTO `<DB_PREFIX>car_agency_vehicle_categories_description` (`id`, `agency_vehicle_category_id`, `language_id`, `name`, `description`) (SELECT NULL, 1, abbreviation, 'Economy', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>car_agency_vehicle_categories_description` (`id`, `agency_vehicle_category_id`, `language_id`, `name`, `description`) (SELECT NULL, 2, abbreviation, 'Compact', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>car_agency_vehicle_categories_description` (`id`, `agency_vehicle_category_id`, `language_id`, `name`, `description`) (SELECT NULL, 3, abbreviation, 'Midsize', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>car_agency_vehicle_categories_description` (`id`, `agency_vehicle_category_id`, `language_id`, `name`, `description`) (SELECT NULL, 4, abbreviation, 'Standard', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>car_agency_vehicle_categories_description` (`id`, `agency_vehicle_category_id`, `language_id`, `name`, `description`) (SELECT NULL, 5, abbreviation, 'Fullsize', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>car_agency_vehicle_categories_description` (`id`, `agency_vehicle_category_id`, `language_id`, `name`, `description`) (SELECT NULL, 6, abbreviation, 'Premium', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>car_agency_vehicle_categories_description` (`id`, `agency_vehicle_category_id`, `language_id`, `name`, `description`) (SELECT NULL, 7, abbreviation, 'Luxury', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>car_agency_vehicle_categories_description` (`id`, `agency_vehicle_category_id`, `language_id`, `name`, `description`) (SELECT NULL, 8, abbreviation, 'Convertible', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>car_agency_vehicle_categories_description` (`id`, `agency_vehicle_category_id`, `language_id`, `name`, `description`) (SELECT NULL, 9, abbreviation, 'Minivan', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>car_agency_vehicle_categories_description` (`id`, `agency_vehicle_category_id`, `language_id`, `name`, `description`) (SELECT NULL, 10, abbreviation, 'Minibus', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>car_agency_vehicle_categories_description` (`id`, `agency_vehicle_category_id`, `language_id`, `name`, `description`) (SELECT NULL, 11, abbreviation, 'Sport Utility', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>car_agency_vehicle_categories_description` (`id`, `agency_vehicle_category_id`, `language_id`, `name`, `description`) (SELECT NULL, 12, abbreviation, 'Sport Car', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>car_agency_vehicle_categories_description` (`id`, `agency_vehicle_category_id`, `language_id`, `name`, `description`) (SELECT NULL, 13, abbreviation, 'Supercar', '' FROM `<DB_PREFIX>languages`);


DROP TABLE IF EXISTS `<DB_PREFIX>car_agency_vehicle_types`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>car_agency_vehicle_types` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `agency_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `vehicle_category_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `image` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `image_thumb` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',  
  `passengers` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `luggages` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `doors` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `priority_order` smallint(6) unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `agency_id` (`agency_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

INSERT INTO `<DB_PREFIX>car_agency_vehicle_types` (`id`, `agency_id`, `vehicle_category_id`, `image`, `image_thumb`, `passengers`, `luggages`, `doors`, `priority_order`, `is_active`) VALUES
(1, 1, 1, 'vehicle_type_1.jpg', 'vehicle_type_1_thumb.jpg', 5, 5, 4, 0, 1),
(2, 1, 2, 'vehicle_type_2.jpg', 'vehicle_type_2_thumb.jpg', 5, 5, 4, 1, 1),
(3, 2, 2, 'vehicle_type_3.jpg', 'vehicle_type_3_thumb.jpg', 5, 4, 4, 2, 1);


DROP TABLE IF EXISTS `<DB_PREFIX>car_agency_vehicles`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>car_agency_vehicles` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `agency_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `vehicle_type_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `make_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `model` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `registration_number` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `mileage` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `fuel_type_id` tinyint(2) NOT NULL,
  `transmission` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',  
  `image` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `image_thumb` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',  
  `price_per_day` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `price_per_hour` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',   
  `default_distance` int(10) unsigned NOT NULL DEFAULT '0',
  `distance_extra_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',     
  `priority_order` smallint(6) unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `agency_id` (`agency_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

INSERT INTO `<DB_PREFIX>car_agency_vehicles` (`id`, `agency_id`, `vehicle_type_id`, `make_id`, `model`, `registration_number`, `mileage`, `fuel_type_id`, `transmission`, `image`, `image_thumb`, `price_per_day`, `price_per_hour`, `default_distance`, `distance_extra_price`, `priority_order`, `is_active`) VALUES
(1, 1, 1, 1, '730i', 'AB 5632', 30000, 1, 'automatic', 'vehicle_1.jpg', 'vehicle_1_thumb.jpg', '312.00', '13.00', 150, '14.00', 0, 1),
(2, 1, 2, 8, '6', '12345-AA', 20000, 1, 'automatic', 'vehicle_2.jpg', 'vehicle_2_thumb.jpg', '288.00', '12.00', 150, '15.00', 1, 1),
(3, 1, 2, 6, 'Fiesta', 'AB1 888C', 30000, 1, 'automatic', 'vehicle_3.jpg', 'vehicle_3_thumb.jpg', '336.00', '14.00', 150, '15.00', 2, 1),
(4, 2, 1, 6, 'Mondeo', 'AB123 888', 20000, 2, 'manual', 'vehicle_4.jpg', 'vehicle_4_thumb.jpg', '336.00', '14.00', 150, '15.00', 3, 1),
(5, 2, 2, 6, 'Ka', 'ABV 123', 250000, 2, 'automatic', 'vehicle_5.jpg', 'vehicle_5_thumb.jpg', '336.00', '14.00', 150, '15.00', 4, 1);


DROP TABLE IF EXISTS `<DB_PREFIX>car_agency_vehicles_description`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>car_agency_vehicles_description` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `agency_vehicle_id` int(10) unsigned NOT NULL DEFAULT '0',
  `language_id` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `agency_vehicle_id` (`agency_vehicle_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

INSERT INTO `<DB_PREFIX>car_agency_vehicles_description` (`id`, `agency_vehicle_id`, `language_id`, `description`) (SELECT NULL, 1, abbreviation, 'Etiam faucibus neque sem. Duis quis orci aliquet, porta sem eu, venenatis leo. Mauris metus justo, rhoncus non ipsum ut, sollicitudin interdum ligula.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>car_agency_vehicles_description` (`id`, `agency_vehicle_id`, `language_id`, `description`) (SELECT NULL, 2, abbreviation, 'Etiam faucibus neque sem. Duis quis orci aliquet, porta sem eu, venenatis leo. Mauris metus justo, rhoncus non ipsum ut, sollicitudin interdum ligula.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>car_agency_vehicles_description` (`id`, `agency_vehicle_id`, `language_id`, `description`) (SELECT NULL, 3, abbreviation, 'Etiam faucibus neque sem. Duis quis orci aliquet, porta sem eu, venenatis leo. Mauris metus justo, rhoncus non ipsum ut, sollicitudin interdum ligula.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>car_agency_vehicles_description` (`id`, `agency_vehicle_id`, `language_id`, `description`) (SELECT NULL, 4, abbreviation, 'Mauris metus justo, rhoncus non ipsum ut, sollicitudin interdum ligula.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>car_agency_vehicles_description` (`id`, `agency_vehicle_id`, `language_id`, `description`) (SELECT NULL, 5, abbreviation, 'Etiam faucibus neque sem. Duis quis orci aliquet, porta sem eu, venenatis leo.' FROM `<DB_PREFIX>languages`);


DROP TABLE IF EXISTS `<DB_PREFIX>car_agency_makes`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>car_agency_makes` (
  `id` smallint (6) NOT NULL AUTO_INCREMENT,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `priority_order` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=40 ;

INSERT INTO `<DB_PREFIX>car_agency_makes` (`id`, `is_active`, `priority_order`) VALUES
(1, 1, 0),
(2, 1, 1),
(3, 1, 2),
(4, 1, 3),
(5, 1, 4),
(6, 1, 5),
(7, 1, 6),
(8, 1, 7),
(9, 1, 8),
(10, 1, 9),
(11, 1, 10),
(12, 1, 11),
(13, 1, 12),
(14, 2, 0),
(15, 2, 1),
(16, 2, 2),
(17, 2, 3),
(18, 2, 4),
(19, 2, 5),
(20, 2, 6),
(21, 2, 7),
(22, 2, 8),
(23, 2, 9),
(24, 2, 10),
(25, 2, 11),
(26, 2, 12),
(27, 3, 0),
(28, 3, 1),
(29, 3, 2),
(30, 3, 3),
(31, 3, 4),
(32, 3, 5),
(33, 3, 6),
(34, 3, 7),
(35, 3, 8),
(36, 3, 9),
(37, 3, 10),
(38, 3, 11),
(39, 3, 12);


DROP TABLE IF EXISTS `<DB_PREFIX>car_agency_makes_description`;
CREATE TABLE `<DB_PREFIX>car_agency_makes_description` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `make_id` smallint(6) NOT NULL,
  `language_id` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `make_id` (`make_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=14;

INSERT INTO `<DB_PREFIX>car_agency_makes_description` (`id`, `make_id`, `language_id`, `name`) (SELECT NULL, 1, abbreviation, 'Audi' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>car_agency_makes_description` (`id`, `make_id`, `language_id`, `name`) (SELECT NULL, 2, abbreviation, 'BMW' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>car_agency_makes_description` (`id`, `make_id`, `language_id`, `name`) (SELECT NULL, 3, abbreviation, 'Dodge' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>car_agency_makes_description` (`id`, `make_id`, `language_id`, `name`) (SELECT NULL, 4, abbreviation, 'GMC' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>car_agency_makes_description` (`id`, `make_id`, `language_id`, `name`) (SELECT NULL, 5, abbreviation, 'Jeep' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>car_agency_makes_description` (`id`, `make_id`, `language_id`, `name`) (SELECT NULL, 6, abbreviation, 'Ford' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>car_agency_makes_description` (`id`, `make_id`, `language_id`, `name`) (SELECT NULL, 7, abbreviation, 'Opel' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>car_agency_makes_description` (`id`, `make_id`, `language_id`, `name`) (SELECT NULL, 8, abbreviation, 'Mazda' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>car_agency_makes_description` (`id`, `make_id`, `language_id`, `name`) (SELECT NULL, 9, abbreviation, 'Mercedes-Benz' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>car_agency_makes_description` (`id`, `make_id`, `language_id`, `name`) (SELECT NULL, 10, abbreviation, 'Mitsubishi' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>car_agency_makes_description` (`id`, `make_id`, `language_id`, `name`) (SELECT NULL, 11, abbreviation, 'Renault' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>car_agency_makes_description` (`id`, `make_id`, `language_id`, `name`) (SELECT NULL, 12, abbreviation, 'Subaru' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>car_agency_makes_description` (`id`, `make_id`, `language_id`, `name`) (SELECT NULL, 13, abbreviation, 'Toyota' FROM `<DB_PREFIX>languages`);


DROP TABLE IF EXISTS `<DB_PREFIX>car_agency_payment_gateways`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>car_agency_payment_gateways` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agency_id` int(11) NOT NULL DEFAULT '0',
  `payment_type` varchar(20) NOT NULL DEFAULT '',
  `payment_type_name` varchar(50) NOT NULL DEFAULT '',
  `api_login` varchar(40) NOT NULL DEFAULT '',
  `api_key` varchar(40) NOT NULL DEFAULT '',
  `payment_info` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

INSERT INTO `<DB_PREFIX>car_agency_payment_gateways` (`id`, `agency_id`, `payment_type`, `payment_type_name`, `api_login`, `api_key`, `payment_info`) VALUES
(1, 1, 'paypal', 'PayPal', '', '', ''),
(2, 1, '2co', '2CO (2 checkout)', '', '', ''),
(3, 1, 'authorize.net', 'Authorize.Net', '', '', ''),
(4, 1, 'bank.transfer', 'Bank Transfer', '', '', 'Bank name: {BANK NAME HERE}\r\nSwift code: {CODE HERE}\r\nRouting in Transit# or ABA#: {ROUTING HERE}\r\nAccount number *: {ACCOUNT NUMBER HERE}\r\n\r\n*The account number must be in the IBAN format which may be obtained from the branch handling the customer''s account or may be seen at the top the customer''s bank statement\r\n'),
(5, 2, 'paypal', 'PayPal', '', '', ''),
(6, 2, '2co', '2CO (2 checkout)', '', '', ''),
(7, 2, 'authorize.net', 'Authorize.Net', '', '', ''),
(8, 2, 'bank.transfer', 'Bank Transfer', '', '', 'Bank name: {BANK NAME HERE}\r\nSwift code: {CODE HERE}\r\nRouting in Transit# or ABA#: {ROUTING HERE}\r\nAccount number *: {ACCOUNT NUMBER HERE}\r\n\r\n*The account number must be in the IBAN format which may be obtained from the branch handling the customer''s account or may be seen at the top the customer''s bank statement\r\n');


DROP TABLE IF EXISTS `<DB_PREFIX>car_reservations`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>car_reservations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reservation_number` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `car_agency_id` int(11) NOT NULL DEFAULT '0',
  `car_vehicle_type_id` int(11) NOT NULL DEFAULT '0',
  `car_vehicle_id` int(11) NOT NULL DEFAULT '0',  
  `location_from_id` int(11) NOT NULL DEFAULT '0',
  `location_to_id` int(11) NOT NULL DEFAULT '0',
  `date_from` date NULL DEFAULT NULL,
  `time_from` time NOT NULL DEFAULT '00:00:00',
  `date_to` date NULL DEFAULT NULL,
  `time_to` time NOT NULL DEFAULT '00:00:00',  
  `reservation_description` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `reservation_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `pre_payment_type` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'full price',
  `pre_payment_value` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `vat_fee` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `vat_percent` decimal(5,3) unsigned NOT NULL DEFAULT '0.000',
  `extras` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `extras_fee` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `reservation_total_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `reservation_paid` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `additional_payment` decimal(10,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `is_admin_reservation` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `payment_date` datetime NULL DEFAULT NULL,
  `payment_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 - POA, 1 - Online Order, 2 - PayPal, 3 - 2CO, 4 - Authorize.Net, 5 - Bank Transfer, 6 - Account Balance',
  `payment_method` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - Payment Company Account, 1 - Credit Card, 2 - E-Check',
  `transaction_number` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `additional_info` text COLLATE utf8_unicode_ci NOT NULL,
  `cc_type` varchar(20) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `cc_holder_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `cc_number` varchar(50) CHARACTER SET latin1 NOT NULL,
  `cc_expires_month` varchar(2) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `cc_expires_year` varchar(4) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `cc_cvv_code` varchar(20) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 - preordering, 1 - pending, 2 - reserved, 3 - completed, 4 - refunded, 5 - payment error, 6 - canceled',
  `status_changed` datetime NULL DEFAULT NULL,
  `status_description` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `email_sent` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `created_date` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_type` (`payment_type`),
  KEY `car_agency_id` (`car_agency_id`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

INSERT INTO `<DB_PREFIX>car_reservations` (`id`, `reservation_number`, `car_agency_id`, `car_vehicle_type_id`, `car_vehicle_id`, `location_from_id`, `location_to_id`, `date_from`, `time_from`, `date_to`, `time_to`, `reservation_description`, `reservation_price`, `pre_payment_type`, `pre_payment_value`, `vat_fee`, `vat_percent`, `extras`, `extras_fee`, `reservation_total_price`, `reservation_paid`, `currency`, `additional_payment`, `customer_id`, `is_admin_reservation`, `payment_date`, `payment_type`, `payment_method`, `transaction_number`, `additional_info`, `cc_type`, `cc_holder_name`, `cc_number`, `cc_expires_month`, `cc_expires_year`, `cc_cvv_code`, `status`, `status_changed`, `status_description`, `email_sent`, `created_date`) VALUES
(1, '12WS34ED99', 1, 1, 1, 1, 1, '2016-03-11', '12:00:00', '2016-03-11', '15:00:00', 'Car Rental', '42.00', 'full price', '0.00', '0.00', '0.000', '', '0.00', '42.00', '0.00', 'USD', '0.00', 1, 0, NULL, 0, 0, '', '', '', '', '', '', '', '', 1, NULL, '', 0, '2016-03-09 00:00:00'),
(2, 'RRW5T4ED67', 2, 2, 2, 1, 1, '2016-03-12', '11:00:00', '2016-03-12', '12:00:00', 'Car Rental', '42.00', 'full price', '0.00', '0.00', '0.000', '', '0.00', '42.00', '0.00', 'USD', '0.00', 1, 0, NULL, 0, 0, '', '', '', '', '', '', '', '', 1, NULL, '', 0, '2016-03-09 00:00:00');


INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CARS', 'Cars' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CARS_IN_RENTAL', 'Cars In Rental' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CARS_RESERVATION', 'Cars Reservation' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CART_WAS_UPDATED', 'Reservation cart has been successfully updated!' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CAR_AGENCIES', 'Car Agencies' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CAR_AGENCY', 'Car Agency' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CAR_AGENCY_OWNER', 'Car Agency Owner' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CAR_AGENCY_OWNERS', 'Car Agency Owners' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CAR_AGENCY_PAYMENT_GATEWAYS', 'Agency Payment Gateways' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CAR_BOOKING_COMPLETED', 'Car booking was completed' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CAR_HALF_HOUR_ALERT', 'Sorry, but you need to reserve a car for at least half an hour.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CAR_INVENTORY', 'Car Inventory' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CAR_NOT_FOUND', 'Car has not been found!' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CAR_OWNER_NOT_ASSIGNED', 'You still has not been assigned to any car agency to see the reports.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CAR_PICKING_UP', 'Car Picking Up' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CAR_RENTAL', 'Car Rental' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CAR_RENTAL_SETTINGS', 'Car Rental Settings' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CAR_RESERVATIONS', 'Car Reservations' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CAR_RETURNING', 'Car Returning' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CAR_TYPE', 'Car Type' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CAR_WAS_ADDED', 'Car has been successfully added to your reservation!' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CAR_WAS_COMPLETED_MSG', 'Thank you for reservation car! Your booking has been completed.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CAR_WAS_REMOVED', 'Selected car has been successfully removed from your Reservation Cart!' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_LEGEND_CAR_CANCELED', 'Order is canceled by admin and vehicle is available again in search' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_LEGEND_CAR_PENDING', 'The car reservation has been created, but not yet been confirmed and reserved' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_LEGEND_CAR_REFUNDED', 'Order has been refunded and vehicle is available again in search' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_LEGEND_CAR_RESERVED', 'Car is reserved, but order is not paid yet (pending)' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MD_CAR_RENTAL', 'The Car Rental module allows customers to search for cars and vehicles in required location, rent them and pay online.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MSN_ACTIVATE_CAR_RENTAL', 'Activate Car Rental' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MS_ACTIVATE_CAR_RENTAL', 'Specifies whether car rental module is active or inactive' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MY_CAR_RENTALS', 'My Car Rentals' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_NO_CARS_FOUND', 'No results found for your search conditions' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_REMOVE_CAR_FROM_CART', 'Remove car from the cart' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_RENT_A_CAR', 'Rent a Car' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_SELECTED_CARS', 'Selected Cars' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TOTAL_CARS', 'Total Cars' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_WIDGET_INTEGRATION_MESSAGE_CARS', 'You may integrate Cars Rental search engine with another existing web site.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_WIDGET_INTEGRATION_MESSAGE_HINT_CARS', '<b>Hint</b>: To list all available car companies, leave hsJsKey value empty or enter company IDs separated by commas, for ex.: var hsAgencyIDs = "2,9,12";' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_AGENCY_CAR_TYPES', 'Agency Car Types' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_AVAILABLE_CARS', 'Available Cars' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_VEHICLE_CATEGORIES', 'Vehicle Categories' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_VEHICLE_CATEGORY', 'Vehicle Category' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_VEHICLE_CATEGORY_DELETE_ALERT', 'You cannot delete this category, because it still includes some vehicles! Please remove all vehicles from this category before deleting operation.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_VEHICLE_DETAILS', 'Vehicle Details' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_VEHICLE_TYPE', 'Vehicle Type' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_VEHICLE_TYPES', 'Vehicle Types' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TRANSMISSION', 'Transmission' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TRANSPORTATION_AGENCIES', 'Transportation Agencies' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TRANSPORTATION_AGENCIES_MANAGEMENT', 'Transportation Agencies Management' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MSN_SEND_ORDER_COPY_TO_AGENCY', 'Send Order Copy To Agency' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MS_SEND_ORDER_COPY_TO_AGENCY', 'Specifies whether to allow sending a copy of order to agency' FROM `<DB_PREFIX>languages`);

