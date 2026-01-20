
INSERT INTO `<DB_PREFIX>modules` (`id`, `name`, `name_const`, `description_const`, `icon_file`, `module_tables`, `dependent_modules`, `settings_page`, `settings_const`, `settings_access_by`, `management_page`, `management_const`, `management_access_by`, `is_installed`, `module_type`, `show_on_dashboard`, `priority_order`) VALUES
(NULL, 'tours_and_attractions', '_TOURS_AND_ATTRACTIONS_SHORT', '_MD_TOURS_AND_ATTRACTIONS', 'tours_and_attractions.png', 'tours_and_attractions', '', 'mod_tours_and_attractions_settings', '_TOURS_AND_ATTRACTIONS_SETTINGS', 'owner,mainadmin', 'mod_tours_and_attractions_management', '_TOURS_AND_ATTRACTIONS_MANAGEMENT', 'owner,mainadmin', 1, 2, 0, 0);


INSERT INTO `<DB_PREFIX>modules_settings` (`id`, `module_name`, `settings_key`, `settings_value`, `settings_name_const`, `settings_description_const`, `key_display_type`, `key_is_required`, `key_display_source`) VALUES
(NULL, 'tours_and_attractions', 'is_active', 'yes', '_MSN_ACTIVATE_TOURS_AND_ATTRACTIONS', '_MS_TOURS_AND_ATTRACTIONS', 'yes/no', '1', ''),
(NULL, 'tours_and_attractions', 'show_tab_on_homepage', 'yes', '_MSN_SHOW_TAB_ON_HOMEPAGE', '_MS_SHOW_TAB_ON_HOMEPAGE', 'yes/no', 0, ''),
(NULL, 'tours_and_attractions', 'review_enabled', 'yes', '_MSN_ENABLE_REVIEWS', '_MS_ENABLE_REVIEWS', 'yes/no', '1', ''),
(NULL, 'tours_and_attractions', 'payment_type_poa', 'Frontend & Backend', '_MSN_PAYMENT_TYPE_POA', '_MS_PAYMENT_TYPE_POA', 'enum', 1, 'No,Frontend Only,Backend Only,Frontend & Backend'),
(NULL, 'tours_and_attractions', 'payment_type_online', 'Frontend & Backend', '_MSN_PAYMENT_TYPE_ONLINE', '_MS_PAYMENT_TYPE_ONLINE', 'enum', 1, 'No,Frontend Only,Backend Only,Frontend & Backend'),
(NULL, 'tours_and_attractions', 'online_credit_card_required', 'yes', '_MSN_ONLINE_CREDIT_CARD_REQUIRED', '_MS_ONLINE_CREDIT_CARD_REQUIRED', 'yes/no', 0, ''),
(NULL, 'tours_and_attractions', 'payment_type_bank_transfer', 'Frontend & Backend', '_MSN_PAYMENT_TYPE_BANK_TRANSFER', '_MS_PAYMENT_TYPE_BANK_TRANSFER', 'enum', 1, 'No,Frontend Only,Backend Only,Frontend & Backend'),
(NULL, 'tours_and_attractions', 'bank_transfer_info', 'Bank name: {BANK NAME HERE}\r\nSwift code: {CODE HERE}\r\nRouting in Transit# or ABA#: {ROUTING HERE}\r\nAccount number *: {ACCOUNT NUMBER HERE}\r\n\r\n*The account number must be in the IBAN format which may be obtained from the branch handling the customer''s account or may be seen at the top the customer''s bank statement\r\n', '_MSN_BANK_TRANSFER_INFO', '_MS_BANK_TRANSFER_INFO', 'text', 0, ''),
(NULL, 'tours_and_attractions', 'payment_type_paypal', 'Frontend & Backend', '_MSN_PAYMENT_TYPE_PAYPAL', '_MS_PAYMENT_TYPE_PAYPAL', 'enum', 1, 'No,Frontend Only,Backend Only,Frontend & Backend'),
(NULL, 'tours_and_attractions', 'paypal_email', 'paypal@example.com', '_MSN_PAYPAL_EMAIL', '_MS_PAYPAL_EMAIL', 'email', 1, ''),
(NULL, 'tours_and_attractions', 'payment_type_2co', 'Frontend & Backend', '_MSN_PAYMENT_TYPE_2CO', '_MS_PAYMENT_TYPE_2CO', 'enum', 0, 'No,Frontend Only,Backend Only,Frontend & Backend'),
(NULL, 'tours_and_attractions', 'two_checkout_vendor', 'Your 2CO Vendor ID here', '_MSN_TWO_CHECKOUT_VENDOR', '_MS_TWO_CHECKOUT_VENDOR', 'string', 1, ''),
(NULL, 'tours_and_attractions', 'payment_type_authorize', 'Frontend & Backend', '_MSN_PAYMENT_TYPE_AUTHORIZE', '_MS_PAYMENT_TYPE_AUTHORIZE', 'enum', 1, 'No,Frontend Only,Backend Only,Frontend & Backend'),
(NULL, 'tours_and_attractions', 'authorize_login_id', 'Your API Login ID here', '_MSN_AUTHORIZE_LOGIN_ID', '_MS_AUTHORIZE_LOGIN_ID', 'string', 1, ''),
(NULL, 'tours_and_attractions', 'authorize_transaction_key', 'Your Transaction Key here', '_MSN_AUTHORIZE_TRANSACTION_KEY', '_MS_AUTHORIZE_TRANSACTION_KEY', 'string', 1, ''),
(NULL, 'tours_and_attractions', 'default_payment_system', 'paypal', '_MSN_DEFAULT_PAYMENT_SYSTEM', '_MS_DEFAULT_PAYMENT_SYSTEM', 'enum', 1, 'poa,online,bank transfer,paypal,2co,authorize.net'),
(NULL, 'tours_and_attractions', 'pre_payment_type', 'percentage', '_MSN_PRE_PAYMENT_TYPE', '_MS_PRE_PAYMENT_TYPE', 'enum', 1, 'full price,first night,fixed sum,percentage'),
(NULL, 'tours_and_attractions', 'pre_payment_value', '10', '_MSN_PRE_PAYMENT_VALUE', '_MS_PRE_PAYMENT_VALUE', 'enum', 0, '1,2,3,4,5,6,7,8,9,10,15,20,25,30,35,40,45,50,55,60,65,70,75,80,85,90,95,99,100,125,150,175,200,250,500,750,1000'),
(NULL, 'tours_and_attractions', 'vat_value', '0', '_MSN_VAT_VALUE', '_MS_VAT_VALUE', 'unsigned float', 0, ''),
(NULL, 'tours_and_attractions', 'mode', 'REAL MODE', '_MSN_BOOKING_MODE', '_MS_BOOKING_MODE', 'enum', 1, 'TEST MODE,REAL MODE'),
(NULL, 'tours_and_attractions', 'show_agency_in_search_result', 'yes', '_MSN_AGENCY_IN_SEARCH_RESULT', '_MS_AGENCY_IN_SEARCH_RESULT', 'yes/no', 0, ''),
(NULL, 'tours_and_attractions', 'customers_cancel_reservation', '7', '_MSN_CUSTOMERS_CANCEL_RESERVATION', '_MS_CUSTOMERS_CANCEL_RESERVATION', 'enum', 1, '0,1,2,3,4,5,6,7,10,14,21,30,45,60'),
(NULL, 'tours_and_attractions', 'vat_included_in_price', 'no', '_MSN_VAT_INCLUDED_IN_PRICE', '_MS_VAT_INCLUDED_IN_PRICE', 'yes/no', 1, ''),
(NULL, 'tours_and_attractions', 'allow_reservation_without_account', 'yes', '_MSN_ALLOW_RESERVATION_WITHOUT_ACCOUNT', '_MS_ALLOW_RESERVATION_WITHOUT_ACCOUNT', 'yes/no', 0, ''),
(NULL, 'tours_and_attractions', 'allow_payment_with_balance', 'yes', '_MSN_ALLOW_PAYMENT_WITH_BALANCE', '_MS_ALLOW_PAYMENT_WITH_BALANCE', 'yes/no', 1, ''),
(NULL, 'tours_and_attractions', 'prebooking_orders_timeout', '2', '_MSN_PREBOOKING_ORDERS_TIMEOUT', '_MS_PREBOOKING_ORDERS_TIMEOUT', 'enum', 1, '0,1,2,3,4,5,6,7,8,9,10,12,14,16,18,20,22,24,36,48,72'),
(NULL, 'tours_and_attractions', 'reservation_expired_alert', 'no', '_MSN_RESERVATION_EXPIRED_ALERT', '_MS_RESERVATION EXPIRED_ALERT', 'yes/no', 1, ''),
(NULL, 'tours_and_attractions', 'send_order_copy_to_admin', 'yes', '_MSN_SEND_ORDER_COPY_TO_ADMIN', '_MS_SEND_ORDER_COPY_TO_ADMIN', 'yes/no', 0, ''),
(NULL, 'tours_and_attractions', 'reservation_number_type', 'random', '_MSN_RESERVATION_NUMBER_TYPE', '_MS_RESERVATION_NUMBER_TYPE', 'enum', 1, 'random,sequential');



DROP TABLE IF EXISTS `<DB_PREFIX>tours`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>tours` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `tour_agency_id` int(10) unsigned NOT NULL DEFAULT '0',
  `tour_type_id` int(10) unsigned NOT NULL DEFAULT '0',
  `tour_location_id` int(10) unsigned NOT NULL DEFAULT '0',
  `price_per_person` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `map_code` text COLLATE utf8_unicode_ci NOT NULL,
  `latitude` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `longitude` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `number_of_views` smallint(6) unsigned NOT NULL DEFAULT '0',
  `priority_order` smallint(6) unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

INSERT INTO `<DB_PREFIX>tours` (`id`, `tour_agency_id`, `tour_type_id`, `tour_location_id`, `price_per_person`, `map_code`, `latitude`, `longitude`, `number_of_views`, `priority_order`, `is_active`) VALUES
(1, 1, 8, 1, '99.0', '<iframe src="http://maps.google.com/maps?f=q&source=s_q&hl=en&geocode=&q=Hotel+Circle+South,+San+Diego,+California,+United+States&aq=1&sll=33.981232,-84.173813&sspn=0.12868,0.307274&ie=UTF8&hq=&hnear=Hotel+Cir+S,+San+Diego,+California&ll=32.759,-117.177036&spn=0.017215,0.038409&z=14&output=embed" width="100%" height="450" frameborder="0" style="border:0"></iframe>', '32.7742488', '-117.1411815', 0, 0, 1),
(2, 1, 2, 2, '44.0', '<iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d2948.0965968856144!2d-71.0666208!3d42.361780599999996!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89e3709a3c7713c5%3A0x922cf2c02c6d4e5a!2s5+Blossom+St%2C+Charles+River+Plaza+Shopping+Center%2C+Boston%2C+MA+02114!5e0!3m2!1sen!2s!4v1405938488556" width="100%" height="450" frameborder="0" style="border:0"></iframe>', '', '', 0, 1, 1),
(3, 2, 3, 2, '55.0', '<iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d3021.7983721254104!2d-73.9780617!3d40.7664592!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c258f72e9171bb%3A0x3adf1f82b9f2dc62!2sCentral+Park+S%2C+New+York%2C+NY+10019!5e0!3m2!1sen!2s!4v1406544095540" width="100%" height="450" frameborder="0" style="border:0"></iframe>', '', '', 0, 2, 1);



DROP TABLE IF EXISTS `<DB_PREFIX>tours_description`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>tours_description` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `tour_id` int(10) unsigned NOT NULL DEFAULT '0',
  `language_id` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(125) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(225) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tour_id` (`tour_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

INSERT INTO `<DB_PREFIX>tours_description` (`id`, `tour_id`, `language_id`, `name`, `address`, `description`) (SELECT NULL, 1, abbreviation, 'Aquaventure Waterpark', '5 Blossom Street, Boston, MA 02114', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum nec semper lectus. Suspendisse placerat enim mauris, eget lobortis nisi egestas et. Donec elementum metus et mi aliquam eleifend. Suspendisse volutpat egestas rhoncus.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>tours_description` (`id`, `tour_id`, `language_id`, `name`, `address`, `description`) (SELECT NULL, 2, abbreviation, '4x4 Sunset Desert Safari Dinner', 'San Diego, CA 92108, USA', 'Vivamus quis odio posuere, fringilla enim sit amet, elementum nulla. Vestibulum nec semper lectus. Suspendisse placerat enim mauris, eget lobortis nisi egestas et. Donec elementum metus et mi aliquam eleifend. Suspendisse volutpat egestas rhoncus.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>tours_description` (`id`, `tour_id`, `language_id`, `name`, `address`, `description`) (SELECT NULL, 3, abbreviation, 'Yas Waterworld', '', 'Pellentesque vel blandit est. Aenean neque augue, fringilla ac mauris. Vestibulum nec semper lectus. Suspendisse placerat enim mauris, eget lobortis nisi egestas et. Donec elementum metus et mi aliquam eleifend. Suspendisse volutpat egestas rhoncus.' FROM `<DB_PREFIX>languages`);



DROP TABLE IF EXISTS `<DB_PREFIX>tours_agencies`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>tours_agencies` (
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

INSERT INTO `<DB_PREFIX>tours_agencies` (`id`, `phone`, `fax`, `email`, `logo_image`, `map_code`, `priority_order`, `is_active`) VALUES
(1, '1-800-123-4567', '1-800-123-6789', 'info@tour_agency1.com', 'company_logo_1.png', '<iframe width="100%" height="400" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://maps.google.com/maps?f=q&source=s_q&hl=en&geocode=&q=Hotel+Circle+South,+San+Diego,+California,+United+States&aq=1&sll=33.981232,-84.173813&sspn=0.12868,0.307274&ie=UTF8&hq=&hnear=Hotel+Cir+S,+San+Diego,+California&ll=32.759,-117.177036&spn=0.017215,0.038409&z=14&output=embed"></iframe>', 0, 1),
(2, '1-800-123-2222', '1-800-123-2223', 'info@tour_agency2.com', 'company_logo_2.png', '<iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d2948.0965968856144!2d-71.0666208!3d42.361780599999996!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89e3709a3c7713c5%3A0x922cf2c02c6d4e5a!2s5+Blossom+St%2C+Charles+River+Plaza+Shopping+Center%2C+Boston%2C+MA+02114!5e0!3m2!1sen!2s!4v1405938488556" width="100%" height="450" frameborder="0" style="border:0"></iframe>', 0, 1);



DROP TABLE IF EXISTS `<DB_PREFIX>tours_agencies_description`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>tours_agencies_description` (
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

INSERT INTO `<DB_PREFIX>tours_agencies_description` (`id`, `agency_id`, `language_id`, `name`, `address`, `description`, `terms_and_conditions`) (SELECT NULL, 1, abbreviation, 'Tour Agency 1', 'address here...', '<h3>We give you the following for free</h3><div class="col-md-6"><ul class="checklist"><li>Amendments</li><li>Theft Protection</li><li>Direct-dial phone</li><li>Collision Damage Waiver</li></ul></div><div class="col-md-6"><ul class="checklist"><li>Unlimited Kilometres for ALL car groups</li><li>Local Taxes</li><li>PRoad Fee</li><li>First Additional Driver</li></ul></div>', '<h3>Excess Protection - Excellent Value, Best Cover, Peace of Mind</h3><p>When you pick up your car a deposit is usually held on your card for your excess which may be charged if the vehicle is damaged. Take our Damage Excess Refund and if you have any bumps or scrapes we''ll refund any deductions from your excess.<br /><br /></p><div class="hpadding20"><ul class="checklist"><li><span class="bold green">You''re protected</span> - Damage Excess Refund protects you when things go wrong and you are required to pay out from your excess</li><li><span class="bold green">Accidents covered</span> - Your excess will be protected if your vehicle is damaged in an accident</li><li><span class="bold green">Damage cover</span> - Damage to bodywork, vehicle parts and accessories incurred during most driving incidents is covered.</li></ul></div>' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>tours_agencies_description` (`id`, `agency_id`, `language_id`, `name`, `address`, `description`, `terms_and_conditions`) (SELECT NULL, 2, abbreviation, 'Tour Agency 2', 'address here...', '<h3>We give you the following for free</h3><div class="col-md-6"><ul class="checklist"><li>Amendments</li><li>Theft Protection</li><li>Direct-dial phone</li><li>Collision Damage Waiver</li></ul></div><div class="col-md-6"><ul class="checklist"><li>Unlimited Kilometres for ALL car groups</li><li>Local Taxes</li><li>PRoad Fee</li><li>First Additional Driver</li></ul></div>', '<h3>Excess Protection - Excellent Value, Best Cover, Peace of Mind</h3><p>When you pick up your car a deposit is usually held on your card for your excess which may be charged if the vehicle is damaged. Take our Damage Excess Refund and if you have any bumps or scrapes we''ll refund any deductions from your excess.<br /><br /></p><div class="hpadding20"><ul class="checklist"><li><span class="bold green">You''re protected</span> - Damage Excess Refund protects you when things go wrong and you are required to pay out from your excess</li><li><span class="bold green">Accidents covered</span> - Your excess will be protected if your vehicle is damaged in an accident</li><li><span class="bold green">Damage cover</span> - Damage to bodywork, vehicle parts and accessories incurred during most driving incidents is covered.</li></ul></div>' FROM `<DB_PREFIX>languages`);



DROP TABLE IF EXISTS `<DB_PREFIX>tours_types`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>tours_types` (
   `id` int(10) NOT NULL AUTO_INCREMENT,
   `priority_order` smallint(6) unsigned NOT NULL DEFAULT '0',
   `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
   PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=11 ;

INSERT INTO `<DB_PREFIX>tours_types` (`id`, `priority_order`, `is_active`) VALUES
(1, 0, 1),
(2, 1, 1),
(3, 2, 1),
(4, 3, 1),
(5, 4, 1),
(6, 5, 1),
(7, 6, 1),
(8, 7, 1),
(9, 8, 1),
(10, 9, 1);


DROP TABLE IF EXISTS `<DB_PREFIX>tours_types_description`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>tours_types_description` (
   `id` int(10) NOT NULL AUTO_INCREMENT,
   `tour_type_id` int(10) unsigned NOT NULL DEFAULT '0',
   `language_id` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
   `name` varchar(125) COLLATE utf8_unicode_ci NOT NULL,
   `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
   PRIMARY KEY (`id`),
   KEY `tour_type_id` (`tour_type_id`),
   KEY `language_id` (`language_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=11 ;

INSERT INTO `<DB_PREFIX>tours_types_description` (`id`, `tour_type_id`, `language_id`, `name`, `description`) (SELECT NULL, 1, abbreviation, 'Special Offers', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>tours_types_description` (`id`, `tour_type_id`, `language_id`, `name`, `description`) (SELECT NULL, 2, abbreviation, 'Tours and Sightseeing', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>tours_types_description` (`id`, `tour_type_id`, `language_id`, `name`, `description`) (SELECT NULL, 3, abbreviation, 'Theme Parks', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>tours_types_description` (`id`, `tour_type_id`, `language_id`, `name`, `description`) (SELECT NULL, 4, abbreviation, 'Ski Offerings', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>tours_types_description` (`id`, `tour_type_id`, `language_id`, `name`, `description`) (SELECT NULL, 5, abbreviation, 'Overnight Excursions', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>tours_types_description` (`id`, `tour_type_id`, `language_id`, `name`, `description`) (SELECT NULL, 6, abbreviation, 'Air and Land Adventures', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>tours_types_description` (`id`, `tour_type_id`, `language_id`, `name`, `description`) (SELECT NULL, 7, abbreviation, 'Water Activities and Cruises', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>tours_types_description` (`id`, `tour_type_id`, `language_id`, `name`, `description`) (SELECT NULL, 8, abbreviation, 'Attractions', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>tours_types_description` (`id`, `tour_type_id`, `language_id`, `name`, `description`) (SELECT NULL, 9, abbreviation, 'Restaurants and Nightlife', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>tours_types_description` (`id`, `tour_type_id`, `language_id`, `name`, `description`) (SELECT NULL, 10, abbreviation, 'Ground Transportation', '' FROM `<DB_PREFIX>languages`);


DROP TABLE IF EXISTS `<DB_PREFIX>tours_locations`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>tours_locations` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `country_id` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `priority_order` smallint(6) unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
PRIMARY KEY (`id`),
KEY `country_id` (`country_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

INSERT INTO `<DB_PREFIX>tours_locations` (`id`, `country_id`, `priority_order`, `is_active`) VALUES
(1, 'US', 2, 1),
(2, 'US', 3, 1);


DROP TABLE IF EXISTS `<DB_PREFIX>tours_locations_description`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>tours_locations_description` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `tour_location_id` int(10) unsigned NOT NULL DEFAULT '0',
  `language_id` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(125) COLLATE utf8_unicode_ci NOT NULL,
PRIMARY KEY (`id`),
KEY `tour_location_id` (`tour_location_id`),
KEY `language_id` (`language_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

INSERT INTO `<DB_PREFIX>tours_locations_description` (`id`, `tour_location_id`, `language_id`, `name`) VALUES
(1, 1, 'en', 'Boston'),
(2, 2, 'en', 'New York City');


DROP TABLE IF EXISTS `<DB_PREFIX>tours_reviews`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>tours_reviews` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `tour_id` smallint(6) unsigned NOT NULL DEFAULT '0',
    `customer_id` int(11) NOT NULL DEFAULT '0',
    `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    `positive_comments` varchar(1048) COLLATE utf8_unicode_ci DEFAULT '' NOT NULL,
    `negative_comments` varchar(1048) COLLATE utf8_unicode_ci DEFAULT '' NOT NULL,
    `admin_answer` varchar(1048) COLLATE utf8_unicode_ci DEFAULT '' NOT NULL,
    `rating_location` float(2,1) NOT NULL DEFAULT '0.0',
    `rating_service` float(2,1) NOT NULL DEFAULT '0.0',
    `rating_price` float(2,1) NOT NULL DEFAULT '0.0',
    `evaluation` tinyint(1) NOT NULL DEFAULT '2' COMMENT '0 - Not Recommend, 1 - Not Good, 2 - Neutral, 3 - Good, 4 - Very Good, 5 - Wonderful',
    `travel_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 - Not Defined, 1 - Work, 2 - Tourism, 3 - Other',
    `passenger_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 - Not Defined, 1 - An individual, 2 - A young couple, 3 - An old couple, 4 - Family with young children, 5 - Family with older children, 6 - Groups of Friends',
    `image_file_1` varchar(50) COLLATE utf8_unicode_ci DEFAULT '' NOT NULL,
    `image_file_1_thumb` varchar(70) COLLATE utf8_unicode_ci DEFAULT '' NOT NULL,
    `image_file_2` varchar(50) COLLATE utf8_unicode_ci DEFAULT '' NOT NULL,
    `image_file_2_thumb` varchar(70) COLLATE utf8_unicode_ci DEFAULT '' NOT NULL,
    `image_file_3` varchar(50) COLLATE utf8_unicode_ci DEFAULT '' NOT NULL,
    `image_file_3_thumb` varchar(70) COLLATE utf8_unicode_ci DEFAULT '' NOT NULL,
    `date_created` datetime NULL DEFAULT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT '0',
    `priority_order` smallint(5) unsigned NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `tour_id` (`tour_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

INSERT INTO `<DB_PREFIX>tours_reviews` (`id`, `tour_id`, `customer_id`, `title`, `positive_comments`, `negative_comments`, `admin_answer`, `rating_location`, `rating_service`, `rating_price`, `evaluation`, `travel_type`, `passenger_type`, `image_file_1`, `image_file_1_thumb`, `image_file_2`, `image_file_2_thumb`, `image_file_3`, `image_file_3_thumb`, `date_created`, `is_active`, `priority_order`) VALUES
(1, 1, 1, 'Great experience', 'It is close to everything but if you go in the lower season the pool won''t be ready even though the temperature was quiet high already.', 'Noisy at evenings, especially when lots of people arrive.', 'Thank you for this review! We''ll be glad to see you again in our hotel.', 4.2, 4.0, 4.0, 5, 0, 2, '', '', '', '', '', '', '2013-01-02 12:03:00', 1, 0),
(2, 1, 2, 'I liked it very much!', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec venenatis orci eget odio tincidunt mollis. Donec id felis efficitur, tincidunt nulla a, semper massa.', 'Ut iaculis bibendum lorem, vel luctus orci commodo eget. Pellentesque pharetra scelerisque feugiat.', '', 5.0, 4.0, 4.0, 5, 1, 1, '', '', '', '', '', '', '2013-01-04 13:04:00', 1, 1),
(3, 1, 3, 'Excellent hotel, very good!', 'Donec vitae nibh eu metus pharetra rhoncus. Praesent lobortis lorem vitae nunc consectetur fringilla in eu diam. Vivamus tincidunt tempor diam. Aliquam erat volutpat.', 'Fusce ut hendrerit eros.', '', 4.3, 4.0, 3.0, 3, 2, 0, '', '', '', '', '', '' , '2013-01-04 15:04:00', 1, 2),
(4, 1, 4, 'Great experience', 'Nulla eu laoreet nisl. Morbi id urna nisi. Nunc vitae tellus risus. Pellentesque rhoncus aliquam bibendum. Vestibulum id nisi leo. Sed vel sagittis velit.', '', '', 4.0, 5.0, 4.0, 4, 3, 1, '', '', '', '', '', '', '2013-01-05 12:05:00', 1, 3),
(5, 2, 1, 'Super, great experience', 'Etiam viverra rhoncus dolor. Nulla id nibh vel metus pharetra maximus. Suspendisse nec ornare lacus. Donec viverra in risus in suscipit.', 'Nulla tincidunt tempor risus vel egestas...', '', 2.5, 5.0, 4.0, 3, 4, 2, '', '', '', '', '', '', '2013-01-06 16:05:00', 1, 4);


DROP TABLE IF EXISTS `<DB_PREFIX>tours_images`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>tours_images` (
    `id` int(10) NOT NULL AUTO_INCREMENT,
    `tour_id` int(11) NOT NULL DEFAULT '0',
    `item_file` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `item_file_thumb` varchar(70) COLLATE utf8_unicode_ci NOT NULL,
    `image_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `priority_order` smallint(6) NOT NULL DEFAULT '0',
    `is_active` tinyint(1) NOT NULL DEFAULT '1',
    PRIMARY KEY (`id`),
    KEY `tour_id` (`tour_id`),
    KEY `priority_order` (`priority_order`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7 ;

INSERT INTO `<DB_PREFIX>tours_images` (`id`, `tour_id`, `item_file`, `item_file_thumb`, `image_title`, `priority_order`, `is_active`) VALUES
(NULL, 1, 'tour_11.jpg', 'tour_11_thumb.jpg', '', 1, 1),
(NULL, 1, 'tour_12.jpg', 'tour_12_thumb.jpg', '', 2, 1),
(NULL, 1, 'tour_13.jpg', 'tour_13_thumb.jpg', '', 3, 1),
(NULL, 1, 'tour_14.jpg', 'tour_14_thumb.jpg', '', 4, 1),
(NULL, 2, 'tour_21.jpg', 'tour_21_thumb.jpg', '', 1, 1),
(NULL, 3, 'tour_31.jpg', 'tour_31_thumb.jpg', '', 1, 1);



DROP TABLE IF EXISTS `<DB_PREFIX>tours_reservations`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>tours_reservations` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `reservation_number` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
    `tour_type_id` int(11) NOT NULL DEFAULT '0',
    `tour_id` int(11) NOT NULL DEFAULT '0',
    `location_id` int(11) NOT NULL DEFAULT '0',
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
    KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

INSERT INTO `<DB_PREFIX>tours_reservations` (`id`, `reservation_number`, `tour_type_id`, `tour_id`, `location_id`, `date_from`, `time_from`, `date_to`, `time_to`, `reservation_description`, `reservation_price`, `pre_payment_type`, `pre_payment_value`, `vat_fee`, `vat_percent`, `extras`, `extras_fee`, `reservation_total_price`, `reservation_paid`, `currency`, `additional_payment`, `customer_id`, `is_admin_reservation`, `payment_date`, `payment_type`, `payment_method`, `transaction_number`, `additional_info`, `cc_type`, `cc_holder_name`, `cc_number`, `cc_expires_month`, `cc_expires_year`, `cc_cvv_code`, `status`, `status_changed`, `status_description`, `email_sent`, `created_date`) VALUES
(1, 'R5WU33DD99', 1, 1, 1, '2020-03-11', '12:00:00', '2020-03-11', '15:00:00', 'Tour Reservation', '42.00', 'full price', '0.00', '0.00', '0.000', '', '0.00', '42.00', '0.00', 'USD', '0.00', 1, 0, NULL, 0, 0, '', '', '', '', '', '', '', '', 1, NULL, '', 0, '2020-03-09 00:00:00'),
(2, '45G5TDDD67', 2, 2, 2, '2020-03-12', '11:00:00', '2020-03-12', '12:00:00', 'Tour Reservation', '42.00', 'full price', '0.00', '0.00', '0.000', '', '0.00', '42.00', '0.00', 'USD', '0.00', 1, 0, NULL, 0, 0, '', '', '', '', '', '', '', '', 1, NULL, '', 0, '2020-03-09 00:00:00');



INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TOURS_AND_ATTRACTIONS_SHORT', 'Tours' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TOURS_AND_ATTRACTIONS', 'Tours and Attractions' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TOURS_AND_ATTRACTIONS_MANAGEMENT', 'Tours Management' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TOURS_AND_ATTRACTIONS_SETTINGS', 'Tours Settings' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MD_TOURS_AND_ATTRACTIONS', 'Tours and Attractions Module for uHotelBooking script allows you to manage sightseeing tours, attractions and other activities, book tickets for them via website.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MSN_ACTIVATE_TOURS_AND_ATTRACTIONS', 'Activate Tours and Attractions' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MS_TOURS_AND_ATTRACTIONS', 'Specifies whether tours and attractions module is active or inactive' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MSN_ENABLE_REVIEWS', 'Enable Reviews' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MS_ENABLE_REVIEWS', 'Specifies whether to enable reviews on tours and attractions' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TOUR_TYPE', 'Tour Type' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TOUR_TYPES', 'Tour Types' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TOURS_MANAGEMENT', 'Tours Management' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TOUR_LOCATIONS', 'Locations' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TOURS', 'Tours' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TOUR_INFO', 'Tour Info' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TOUR_TYPES_MANAGEMENT', 'Tour Types Management' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TOUR_REVIEWS', 'Tour Reviews' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TOUR_REVIEWS_MANAGEMENT', 'Tour Reviews Management' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_AVAILABLE_TOURS', 'Available Tours' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_NO_TOURS_FOUND', 'No results found for your search conditions' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TOUR', 'Tour' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_YOU_NOT_REVIEW_THIS_TOUR', 'Sorry, it looks like your already left your review to this tour or you''ve never reserved it.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_LEGEND_TOUR_CANCELED', 'Order is canceled by admin and tour is available again in search' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_LEGEND_TOUR_PENDING', 'The tour reservation has been created, but not yet been confirmed and reserved' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_LEGEND_TOUR_REFUNDED', 'Order has been refunded and tour is available again in search' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_LEGEND_TOUR_RESERVED', 'Tour is reserved, but order is not paid yet (pending)' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MSN_SHOW_TAB_ON_HOMEPAGE', 'Show Tab on Homepage' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MS_SHOW_TAB_ON_HOMEPAGE', 'Specifies whether to show tours search tab on homepage' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FIND_TOUR_OR_ATTRACTION_NOTICE', 'To find a tour or attraction, please select a required location and dates:' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_WRONG_EMPTY_FROM_DATE', 'Field Date From is empty. Please select appropriate date.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_WRONG_EMPTY_TO_DATE', 'Field Date To is empty. Please select appropriate date.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MY_TOUR_REVIEWS', 'My Tour Reviews' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FOUND_TOURS', 'Found Tours' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TOTAL_TOURS', 'Total Tours' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_PERSON_PER_DAY', 'Person/Day' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TOUR_DETAILS', 'Tour Details' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TOUR_AGENCIES', 'Tour Agencies' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TOUR_AGENCY', 'Tour Agency' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FOUND_TOUR_AGENCIES', 'Found Agencies' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TOUR_NOT_REVIEWED_YET', 'Not yet reviewed by any member... You can be the FIRST one to write a review for _TOUR_NAME_.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TOUR_WAS_ADDED', 'Tour has been successfully added to your reservation!' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TOUR_WAS_COMPLETED_MSG', 'Thank you for reservation tour! Your booking has been completed.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TOUR_WAS_REMOVED', 'Selected tour has been successfully removed from your Reservation Cart!' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_SELECTED_TOURS', 'Selected Tours' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TOUR_RESERVATION', 'Tour Reservation' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TOUR_REMOVE_TOUR_FROM_CART', 'Remove from cart' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TOUR_RESERVATIONS', 'Tour Reservations' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MY_TOUR_RESERVATIONS', 'My Tour Reservations' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TOUR_BOOKING_COMPLETED', 'Tour booking was completed' FROM `<DB_PREFIX>languages`);
