
INSERT INTO `<DB_PREFIX>modules` (`id`, `name`, `name_const`, `description_const`, `icon_file`, `module_tables`, `dependent_modules`, `settings_page`, `settings_const`, `settings_access_by`, `management_page`, `management_const`, `management_access_by`, `is_installed`, `module_type`, `show_on_dashboard`, `priority_order`) VALUES
(NULL, 'property_management', '_PROPERTY_MANAGEMENT_SHORT', '_MD_PROPERTY_MANAGEMENT', 'property_management.png', 'property_management', '', 'mod_property_management_settings', '_PROPERTY_MANAGEMENT_SETTINGS', 'owner,mainadmin', 'mod_property_management_inventory,mod_property_management_expenses,mod_property_management_managers', '_PROPERTY_MANAGEMENT', 'owner,mainadmin,hotelowner,hotelmanager', 1, 2, 0, 0);

INSERT INTO `<DB_PREFIX>modules_settings` (`id`, `module_name`, `settings_key`, `settings_value`, `settings_name_const`, `settings_description_const`, `key_display_type`, `key_is_required`, `key_display_source`) VALUES
(NULL, 'property_management', 'is_active', 'yes', '_MSN_PROPERTY_MANAGEMENT_IS_ACTIVE', '_MS_PROPERTY_MANAGEMENT_IS_ACTIVE', 'yes/no', 1, ''),
(NULL, 'property_management', 'multi_hotels_for_managers', 'no', '_MSN_MULTI_HOTELS_FOR_MANAGERS', '_MS_MULTI_HOTELS_FOR_MANAGERS', 'yes/no', 1, '');


INSERT INTO `<DB_PREFIX>roles` (`id`, `code`, `name`, `description`) VALUES
(NULL, 'hotelmanager', 'Concierge', 'The "Concierge" is the person who responsible property inventory and expenses in the hotels assigned to.');

INSERT INTO `<DB_PREFIX>privileges` (`id`, `code`, `name`, `description`) VALUES (NULL, 'add_property_inventory', 'Add Property Inventory', 'Add property inventory on the site');
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'owner'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'mainadmin'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'admin'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelowner'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelmanager'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 0);

INSERT INTO `<DB_PREFIX>privileges` (`id`, `code`, `name`, `description`) VALUES (NULL, 'edit_property_inventory', 'Edit Property Inventory', 'Edit property inventory on the site');
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'owner'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'mainadmin'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'admin'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelowner'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelmanager'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 0);

INSERT INTO `<DB_PREFIX>privileges` (`id`, `code`, `name`, `description`) VALUES (NULL, 'delete_property_inventory', 'Delete Property Inventory', 'Delete property inventory on the site');
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'owner'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'mainadmin'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'admin'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelowner'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelmanager'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 0);

INSERT INTO `<DB_PREFIX>privileges` (`id`, `code`, `name`, `description`) VALUES (NULL, 'add_property_expenses', 'Add Property Expenses', 'Add property expenses on the site');
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'owner'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'mainadmin'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'admin'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelowner'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelmanager'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 0);

INSERT INTO `<DB_PREFIX>privileges` (`id`, `code`, `name`, `description`) VALUES (NULL, 'edit_property_expenses', 'Edit Property Expenses', 'Edit property expenses on the site');
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'owner'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'mainadmin'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'admin'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelowner'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelmanager'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 0);

INSERT INTO `<DB_PREFIX>privileges` (`id`, `code`, `name`, `description`) VALUES (NULL, 'delete_property_expenses', 'Delete Property Expenses', 'Delete property expenses on the site');
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'owner'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'mainadmin'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'admin'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelowner'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelmanager'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 0);

INSERT INTO `<DB_PREFIX>privileges` (`id`, `code`, `name`, `description`) VALUES (NULL, 'add_property_managers', 'Add Property Managers', 'Add property managers on the site');
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'owner'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'mainadmin'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'admin'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelowner'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelmanager'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 0);

INSERT INTO `<DB_PREFIX>privileges` (`id`, `code`, `name`, `description`) VALUES (NULL, 'edit_property_managers', 'Edit Property Managers', 'Edit property managers on the site');
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'owner'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'mainadmin'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'admin'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelowner'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelmanager'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 0);

INSERT INTO `<DB_PREFIX>privileges` (`id`, `code`, `name`, `description`) VALUES (NULL, 'delete_property_managers', 'Delete Property Managers', 'Delete property managers on the site');
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'owner'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'mainadmin'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'admin'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelowner'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 1);
INSERT INTO `<DB_PREFIX>role_privileges` (`id`, `role_id`, `privilege_id`, `is_active`) VALUES (NULL, (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelmanager'), (SELECT MAX(id) FROM `<DB_PREFIX>privileges`), 0);


DROP TABLE IF EXISTS `<DB_PREFIX>property_inventory`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>property_inventory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hotel_id` int(11) unsigned NOT NULL DEFAULT 0,
  `room_id` int(11) unsigned NOT NULL DEFAULT 0,
  `property_name` varchar(125) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(2048) COLLATE utf8_unicode_ci NOT NULL,
  `qty` int(11) NOT NULL DEFAULT '0',
  `price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `created_at` datetime NULL DEFAULT NULL,
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '0 - inactive, 1 - active',
  PRIMARY KEY (`id`),
  KEY `hotel_id` (`hotel_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3;

INSERT INTO `<DB_PREFIX>property_inventory` (`id`, `hotel_id`, `room_id`, `property_name`, `description`, `qty`, `price`, `created_at`, `is_active`) VALUES
(1, 1, 1, 'TEE CUP', '', 10, '50.00', NOW(), 1),
(2, 2, 5, 'TV 42” SONY', '', 2, '300.00', NOW(), 1);


DROP TABLE IF EXISTS `<DB_PREFIX>property_expenses`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>property_expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hotel_id` int(11) unsigned NOT NULL DEFAULT 0,
  `room_id` int(11) unsigned NOT NULL DEFAULT 0,
  `inventory_id` int(11) unsigned NOT NULL DEFAULT 0,
  `expenses_name` varchar(125) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(2048) COLLATE utf8_unicode_ci NOT NULL,
  `qty` int(11) NOT NULL DEFAULT '0',
  `price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `created_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hotel_id` (`hotel_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3;

INSERT INTO `<DB_PREFIX>property_expenses` (`id`, `hotel_id`, `room_id`, `inventory_id`, `expenses_name`, `description`, `qty`, `price`, `created_at`) VALUES
(1, 1, 1, 1, 'Water Pump Repair', '', 1, '10.00', NOW()),
(2, 2, 5, 0, 'TV 42” SONY Repair', '', 1, '200.00', NOW());


INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_PROPERTY_MANAGEMENT', 'Property Management' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_PROPERTY_MANAGEMENT_SHORT', 'PMS' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_PROPERTY_MANAGEMENT_SETTINGS', 'Property Management Settings' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MD_PROPERTY_MANAGEMENT', 'The Property Management module provides a web based solution for....' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_PROPERTY_INVENTORY', 'Property Inventory' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_PROPERTY_EXPENSES', 'Property Expenses' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_PROPERTY_MANAGERS', 'Property Concierges' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_PROPERTY_INVENTORY_NAME', 'Inventory Name' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_PROPERTY_EXPENSE_NAME', 'Expense Name' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_PROPERTY_MANAGER_NAME', 'Concierge Name' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MSN_PROPERTY_MANAGEMENT_IS_ACTIVE', 'Activate Property Management' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MS_PROPERTY_MANAGEMENT_IS_ACTIVE', 'Defines whether Property Management Module is active or not' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MSN_ADMIN_CHANGE_MANAGER_PASSWORD', 'Admin changes concierge password' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MS_ADMIN_CHANGE_MANAGER_PASSWORD', 'Specifies whether to allow changing concierge password by Admin' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FLAT_MANAGERS', 'Flat Managers' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_HOTEL_MANAGERS', 'Hotel Managers' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_HOTEL_MANAGER_LOGIN', 'Hotel Manager Login' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_HOTEL_MANAGER', 'Hotel Manager' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FLAT_MANAGER', 'Flat Manager' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MSN_MULTI_HOTELS_FOR_MANAGERS', 'Multi Hotel For Concierges' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MS_MULTI_HOTELS_FOR_MANAGERS', 'Defines allow concierge to add more than one hotel or not' FROM `<DB_PREFIX>languages`);